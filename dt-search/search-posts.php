<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DT_Search_Posts extends DT_Posts {
    public function __construct() {
        parent::__construct();
    }

    public static function query( $query, $post_type, $offset ): array {
        return self::query_exec( $query, $post_type, $offset );
    }

    private static function query_exec( $query, $post_type, $offset ): array {

        $query_results = array();
        $total_hits    = 0;

        // Search across post types based on incoming filter request
        $post_types = ( $post_type === 'all' ) ? DT_Posts::get_post_types() : [ $post_type ];

        foreach ( $post_types as $post_type ) {
            try {
                if ( $post_type !== 'peoplegroups' ) {
                    $type_results = self::search_by_post( $post_type, [
                            'text'             => $query,
                            'offset'           => $offset,
                            'limit'            => 20,
                            'sort'             => '',
                            'fields_to_search' => [ 'all' ]
                        ]
                    );
                    if ( ! empty( $type_results ) && ( intval( $type_results['total'] ) > 0 ) ) {
                        array_push( $query_results, $type_results );
                        $total_hits += intval( $type_results['total'] );
                    }
                }
            } catch ( Exception $e ) {
                $e->getMessage();
            }
        }

        return [
            "hits"       => $query_results,
            "total_hits" => $total_hits
        ];
    }

    private static function search_by_post( string $post_type, array $query, bool $check_permissions = true ) {
        if ( $check_permissions && ! self::can_access( $post_type ) ) {
            return new WP_Error( __FUNCTION__, "You do not have access to these", [ 'status' => 403 ] );
        }
        $post_types = DT_Posts::get_post_types();
        if ( ! in_array( $post_type, $post_types ) ) {
            return new WP_Error( __FUNCTION__, "$post_type in not a valid post type", [ 'status' => 400 ] );
        }

        //filter in to add or remove query parameters.
        $query = apply_filters( 'dt_search_viewable_posts_query', $query );

        global $wpdb;

        $post_settings = DT_Posts::get_post_settings( $post_type );
        $post_fields   = $post_settings["fields"];

        $search = "";
        if ( isset( $query["text"] ) ) {
            $search = sanitize_text_field( $query["text"] );
            unset( $query["text"] );
        }
        $offset = 0;
        if ( isset( $query["offset"] ) ) {
            $offset = esc_sql( sanitize_text_field( $query["offset"] ) );
            unset( $query["offset"] );
        }
        $limit = 100;
        if ( isset( $query["limit"] ) ) {
            $limit = esc_sql( sanitize_text_field( $query["limit"] ) );
            $limit = MIN( $limit, 1000 );
            unset( $query["limit"] );
        }
        $sort     = "";
        $sort_dir = "asc";
        if ( isset( $query["sort"] ) ) {
            $sort = esc_sql( sanitize_text_field( $query["sort"] ) );
            if ( strpos( $sort, "-" ) === 0 ) {
                $sort_dir = "desc";
                $sort     = str_replace( "-", "", $sort );
            }
            unset( $query["sort"] );
        }
        $fields_to_search = [];
        if ( isset( $query["fields_to_search"] ) ) {
            $fields_to_search = $query["fields_to_search"];
            unset( $query ["fields_to_search"] );
        }

        $post_query = "";
        if ( ! empty( $search ) ) {
            $other_search_fields = apply_filters( "dt_search_extra_post_meta_fields", [] );

            if ( empty( $fields_to_search ) ) {
                $post_query .= "AND ( ( p.post_title LIKE '%" . esc_sql( $search ) . "%' )
                    OR p.ID IN ( SELECT post_id
                                FROM $wpdb->postmeta
                                WHERE meta_key LIKE 'contact_%'
                                AND REPLACE( meta_value, ' ', '') LIKE '%" . esc_sql( str_replace( ' ', '', $search ) ) . "%'
                    )
                ";
            }
            if ( ! empty( $fields_to_search ) ) {
                if ( in_array( 'name', $fields_to_search ) ) {
                    $post_query .= "AND ( ( p.post_title LIKE '%" . esc_sql( $search ) . "%' )
                        OR p.ID IN ( SELECT post_id
                                    FROM $wpdb->postmeta
                                    WHERE meta_key LIKE 'contact_%'
                                    AND REPLACE( meta_value, ' ', '') LIKE '%" . esc_sql( str_replace( ' ', '', $search ) ) . "%'
                        )
                    ";
                } else {
                    $post_query .= "AND ( ";
                }
                if ( in_array( 'all', $fields_to_search ) ) {
                    if ( substr( $post_query, - 6 ) !== 'AND ( ' ) {
                        $post_query .= "OR ";
                    }
                    $post_query .= "( p.post_title LIKE '%" . esc_sql( $search ) . "%' ) OR p.ID IN ( SELECT comment_post_ID
                    FROM $wpdb->comments
                    WHERE comment_content LIKE '%" . esc_sql( $search ) . "%'
                    ) OR p.ID IN ( SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_value LIKE '%" . esc_sql( $search ) . "%'
                    ) ";
                } else {
                    if ( in_array( 'comment', $fields_to_search ) ) {
                        if ( substr( $post_query, - 6 ) !== 'AND ( ' ) {
                            $post_query .= "OR ";
                        }
                        $post_query .= " p.ID IN ( SELECT comment_post_ID
                        FROM $wpdb->comments
                        WHERE comment_content LIKE '%" . esc_sql( str_replace( ' ', '', $search ) ) . "%'
                        ) ";
                    }
                    foreach ( $fields_to_search as $field ) {
                        array_push( $other_search_fields, $field );
                    }
                }
            }
            foreach ( $other_search_fields as $field ) {
                if ( substr( $post_query, - 6 ) !== 'AND ( ' ) {
                    $post_query .= "OR ";
                }
                $post_query .= "p.ID IN ( SELECT post_id
                             FROM $wpdb->postmeta
                             WHERE meta_key LIKE '" . esc_sql( $field ) . "'
                             AND meta_value LIKE '%" . esc_sql( $search ) . "%'
                ) ";
            }
            $post_query .= " ) ";

            if ( $post_type === "peoplegroups" ) {

                $locale = get_user_locale();

                $post_query = " AND (p.post_title LIKE '%" . esc_sql( $search ) . "%' OR p.ID IN ( SELECT post_id
                                  FROM $wpdb->postmeta
                                  WHERE meta_key LIKE '" . esc_sql( $locale ) . "'
                                  AND meta_value LIKE '%" . esc_sql( $search ) . "%' ))";
            }
        }

        $sort_sql = "";
        if ( empty( $sort_sql ) ) {
            $sort_sql = "p.post_title asc";
        }

        $group_by_sql = "";
        if ( strpos( $sort_sql, 'sort.meta_value' ) !== false ) {
            $group_by_sql = ", sort.meta_value";
        }

        $permissions = [
            "shared_with" => [ "me" ]
        ];
        $permissions = apply_filters( "dt_filter_access_permissions", $permissions, $post_type );

        if ( $check_permissions && ! empty( $permissions ) ) {
            $query[] = $permissions;
        }

        $fields_sql = self::fields_to_sql( $post_type, $query );
        if ( is_wp_error( $fields_sql ) ) {
            return $fields_sql;
        }

        // Adjust hit columns, accordingly
        $like_query_sql       = "'%" . esc_sql( $search ) . "%'";
        $additional_hits_cols = ", if(p.post_title LIKE " . $like_query_sql . ", 'Y', 'N') post_hit, if(post_type_comments.comment_content LIKE " . $like_query_sql . ", 'Y', 'N') comment_hit, if(post_type_meta.meta_value LIKE " . $like_query_sql . ", 'Y', 'N') meta_hit, if(post_type_comments.comment_content LIKE " . $like_query_sql . ", post_type_comments.comment_content, '') comment_hit_content, if(post_type_meta.meta_value LIKE " . $like_query_sql . ", post_type_meta.meta_value, '') meta_hit_value";
        $group_by_sql         = ", p.post_title, p.post_date, post_hit, comment_hit, meta_hit, comment_hit_content, meta_hit_value";

        if ( $post_type === "peoplegroups" ) {
            $additional_hits_cols = "";
            $group_by_sql         = "";

        } elseif ( $post_type === "contacts" ) {
            $fields_sql["joins_sql"] = "LEFT JOIN " . $wpdb->comments . " as post_type_comments ON ( post_type_comments.comment_post_ID = p.ID ) LEFT JOIN " . $wpdb->dt_share . " as field_shared_with ON ( field_shared_with.post_id = p.ID ) LEFT JOIN " . $wpdb->postmeta . " as post_type_meta ON ( post_type_meta.post_id = p.ID AND ((post_type_meta.meta_key LIKE 'contact_%') OR (post_type_meta.meta_key LIKE 'nickname')) )";
            $fields_sql["where_sql"] = "( ( field_shared_with.user_id IN ( '1' ) ) OR ( ( post_type_meta.meta_value LIKE '%' ) ) )";

        } else {
            $fields_sql["joins_sql"] = "LEFT JOIN " . $wpdb->comments . " as post_type_comments ON ( post_type_comments.comment_post_ID = p.ID ) LEFT JOIN " . $wpdb->postmeta . " as post_type_meta ON ( post_type_meta.post_id = p.ID )";
            $fields_sql["where_sql"] = "";
        }

        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $query_sql = "
            SELECT SQL_CALC_FOUND_ROWS p.ID, p.post_title, p.post_type, p.post_date" . $additional_hits_cols . "
            FROM $wpdb->posts p " . $fields_sql["joins_sql"] . " WHERE " . $fields_sql["where_sql"] . " " . ( empty( $fields_sql["where_sql"] ) ? "" : " AND " ) . "
            (p.post_status = 'publish') AND p.post_type = '" . esc_sql( $post_type ) . "' " . $post_query . "
            GROUP BY p.ID " . $group_by_sql . "
            ORDER BY " . $sort_sql . "
            LIMIT " . esc_sql( $offset ) . ", " . $limit;
        $posts     = $wpdb->get_results( $query_sql, OBJECT );

        if ( empty( $posts ) && ! empty( $wpdb->last_error ) ) {
            return new WP_Error( __FUNCTION__, "Sorry, we had a query issue.", [ 'status' => 500 ] );
        }

        //search by post_id
        if ( is_numeric( $search ) ) {
            $post = get_post( $search );
            if ( $post && self::can_view( $post_type, $post->ID ) ) {
                $posts[] = $post;
            }
        }

        $post_hits = array();

        //remove duplicated non-hits
        foreach ( $posts as $post ) {
            if ( isset( $post->post_hit ) && isset( $post->comment_hit ) && isset( $post->meta_hit ) ) {
                if ( ! ( ( $post->post_hit === 'N' ) && ( $post->comment_hit === 'N' ) && ( $post->meta_hit === 'N' ) ) ) {
                    $post_hits[] = $post;
                }
            } else {
                $post_hits[] = $post;
            }
        }

        //decode special characters in post titles
        foreach ( $post_hits as $hit ) {
            $hit->post_title = wp_specialchars_decode( $hit->post_title );
        }

        $post_hits_count = count( $post_hits );

        return [
            "post_type" => $post_type,
            "posts"     => $post_hits,
            "total"     => $post_hits_count,
            "offset"    => intval( $offset ) + intval( $post_hits_count ) + 1
        ];
    }
}
