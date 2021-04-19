<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DT_Search_Posts extends DT_Posts {
    public function __construct() {
        parent::__construct();
    }

    public static function query( string $query ): array {
        return self::query_exec( $query );
    }

    private static function query_exec( string $query ): array {

        $query_results = array();
        $total_hits    = 0;

        // Search across all post types with access permission
        $post_types = DT_Posts::get_post_types();

        // todo: just for now, as the intention is to have sql search across multiple post types within a single transaction.
        foreach ( $post_types as $post_type ) {
            try {
                $type_results = self::search_by_post( $post_type, [ 'text' => $query ] );
                if ( ! empty( $type_results ) && ( intval( $type_results['total'] ) > 0 ) ) {
                    array_push( $query_results, $type_results );
                    $total_hits += intval( $type_results['total'] );
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
        /*
        if ( isset( $query["offset"] ) ) {
            $offset = esc_sql( sanitize_text_field( $query["offset"] ) );
            unset( $query["offset"] );
        }
        */
        $limit = 100;
        /*
        if ( isset( $query["limit"] ) ) {
            $limit = esc_sql( sanitize_text_field( $query["limit"] ) );
            $limit = MIN( $limit, 1000 );
            unset( $query["limit"] );
        }
        */
        $sort     = "";
        $sort_dir = "asc";
        /*
        if ( isset( $query["sort"] ) ) {
            $sort = esc_sql( sanitize_text_field( $query["sort"] ) );
            if ( strpos( $sort, "-" ) === 0 ) {
                $sort_dir = "desc";
                $sort     = str_replace( "-", "", $sort );
            }
            unset( $query["sort"] );
        }
        */
        $fields_to_search = [];
        /*
        if ( isset( $query["fields_to_search"] ) ) {
            $fields_to_search = $query["fields_to_search"];
            unset( $query ["fields_to_search"] );
        }
        if ( isset( $query["combine"] ) ) {
            unset( $query["combine"] ); //remove deprecated combine
        }

        if ( isset( $query["fields"] ) ) {
            $query = $query["fields"];
        }
        */
        $joins      = "";
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
                    $post_query .= "p.ID IN ( SELECT comment_post_ID
                    FROM $wpdb->comments
                    WHERE comment_content LIKE '%" . esc_sql( str_replace( ' ', '', $search ) ) . "%'
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

                $post_query = " OR p.ID IN ( SELECT post_id
                                  FROM $wpdb->postmeta
                                  WHERE meta_key LIKE '" . esc_sql( $locale ) . "'
                                  AND meta_value LIKE '%" . esc_sql( $search ) . "%' )";
            }
        }

        $sort_sql = "";
        /*
        if ( $sort === "name" || $sort === "post_title" ) {
            $sort_sql = "p.post_title  " . $sort_dir;
        } elseif ( $sort === "post_date" ) {
            $sort_sql = "p.post_date  " . $sort_dir;
        }
        if ( empty( $sort ) && isset( $query["name"][0] ) ) {
            $sort_sql = "( p.post_title = '" . esc_sql( $query["name"][0] ) . "' ) desc, p.post_title asc";
        }

        if ( empty( $sort_sql ) && isset( $sort, $post_fields[ $sort ] ) ) {
            if ( $post_fields[ $sort ]["type"] === "key_select" ) {
                $keys     = array_keys( $post_fields[ $sort ]["default"] );
                $joins    = "LEFT JOIN $wpdb->postmeta as sort ON ( p.ID = sort.post_id AND sort.meta_key = '$sort')";
                $sort_sql = "CASE ";
                foreach ( $keys as $index => $key ) {
                    $sort_sql .= "WHEN ( sort.meta_value = '" . esc_sql( $key ) . "' ) THEN $index ";
                }
                $sort_sql .= "else 98 end ";
                $sort_sql .= $sort_dir;
            } elseif ( $post_fields[ $sort ]["type"] === "multi_select" && ! empty( $post_fields[ $sort ]["default"] ) ) {
                $sort_sql = "CASE ";
                $joins    = "";
                $keys     = array_reverse( array_keys( $post_fields[ $sort ]["default"] ) );
                foreach ( $keys as $index => $key ) {
                    $alias    = $sort . '_' . esc_sql( $key );
                    $joins    .= "LEFT JOIN $wpdb->postmeta as $alias ON
                    ( p.ID = $alias.post_id AND $alias.meta_key = '$sort' AND $alias.meta_value = '" . esc_sql( $key ) . "') ";
                    $sort_sql .= "WHEN ( $alias.meta_value = '" . esc_sql( $key ) . "' ) THEN $index ";
                }
                $sort_sql .= "else 1000 end ";
                $sort_sql .= $sort_dir;
            } elseif ( $post_fields[ $sort ]["type"] === "connection" ) {
                if ( isset( $post_fields[ $sort ]["p2p_key"], $post_fields[ $sort ]["p2p_direction"] ) ) {
                    if ( $post_fields[ $sort ]["p2p_direction"] === "from" ) {
                        $joins = "LEFT JOIN $wpdb->p2p as sort ON ( sort.p2p_from = p.ID AND sort.p2p_type = '" . esc_sql( $post_fields[ $sort ]["p2p_key"] ) . "' )
                        LEFT JOIN $wpdb->posts as p2p_post ON (p2p_post.ID = sort.p2p_to)";
                    } else {
                        $joins = "LEFT JOIN $wpdb->p2p as sort ON ( sort.p2p_to = p.ID AND sort.p2p_type = '" . esc_sql( $post_fields[ $sort ]["p2p_key"] ) . "' )
                        LEFT JOIN $wpdb->posts as p2p_post ON (p2p_post.ID = sort.p2p_from)";
                    }
                    $sort_sql = "ISNULL(p2p_post.post_title), p2p_post.post_title $sort_dir";
                }
            } elseif ( $post_fields[ $sort ]["type"] === "communication_channel" ) {
                $joins    = "LEFT JOIN $wpdb->postmeta as sort ON ( p.ID = sort.post_id AND sort.meta_key LIKE '{$sort}%' AND sort.meta_key NOT LIKE '%_details' AND sort.meta_id = ( SELECT meta_id FROM $wpdb->postmeta pm_sort  where pm_sort.post_id = p.ID AND pm_sort.meta_key LIKE '{$sort}%' AND sort.meta_key NOT LIKE '%_details' LIMIT 1 ))";
                $sort_sql = "sort.meta_value IS NULL, sort.meta_value = '', sort.meta_value * 1 $sort_dir, sort.meta_value $sort_dir";
            } elseif ( $post_fields[ $sort ]["type"] === "location" ) {
                $joins    = "LEFT JOIN $wpdb->postmeta sort ON ( sort.post_id = p.ID AND sort.meta_key = '$sort' AND sort.meta_id = ( SELECT meta_id FROM $wpdb->postmeta pm_sort where pm_sort.post_id = p.ID AND pm_sort.meta_key = '$sort' LIMIT 1 ) )";
                $sort_sql = "sort.meta_value IS NULL, sort.meta_value $sort_dir";
            } else {
                $joins    = "LEFT JOIN $wpdb->postmeta as sort ON ( p.ID = sort.post_id AND sort.meta_key = '$sort')";
                $sort_sql = "sort.meta_value IS NULL, sort.meta_value $sort_dir";
            }
        }
        */
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

        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $posts = $wpdb->get_results( "
            SELECT SQL_CALC_FOUND_ROWS p.ID, p.post_title, p.post_type, p.post_date
            FROM $wpdb->posts p " . $fields_sql["joins_sql"] . " " . $joins . " WHERE " . $fields_sql["where_sql"] . " " . ( empty( $fields_sql["where_sql"] ) ? "" : " AND " ) . "
            (p.post_status = 'publish') AND p.post_type = '" . esc_sql( $post_type ) . "' " . $post_query . "
            GROUP BY p.ID " . $group_by_sql . "
            ORDER BY " . $sort_sql . "
            LIMIT " . esc_sql( $offset ) . ", " . $limit . "
        ", OBJECT );

        if ( empty( $posts ) && ! empty( $wpdb->last_error ) ) {
            return new WP_Error( __FUNCTION__, "Sorry, we had a query issue.", [ 'status' => 500 ] );
        }

        // phpcs:enable
        $total_rows = $wpdb->get_var( "SELECT found_rows();" );

        //search by post_id
        if ( is_numeric( $search ) ) {
            $post = get_post( $search );
            if ( $post && self::can_view( $post_type, $post->ID ) ) {
                $posts[] = $post;
                $total_rows ++;
            }
        }
        //decode special characters in post titles
        foreach ( $posts as $post ) {
            $post->post_title = wp_specialchars_decode( $post->post_title );
        }

        return [
            "post_type" => $post_type,
            "posts" => $posts,
            "total" => $total_rows
        ];
    }
}
