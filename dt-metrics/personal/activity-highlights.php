<?php

class Disciple_Tools_Metrics_Personal_Activity_Highlights extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'personal'; // lowercase
    public $slug = 'activity-highlights'; // lowercase
    public $base_title;

    public $title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/personal/activity-highlights.js'; // should be full file name plus extension
    public $permissions = [];
    public $namespace = null;

    public function __construct() {
        if ( !$this->has_permission() ){
            return;
        }
        parent::__construct();
        $this->title = __( 'Activity Highlights', 'disciple_tools' );
        $this->base_title = __( 'Personal', 'disciple_tools' );
        $this->namespace = "dt-metrics/$this->base_slug/$this->slug";

        $url_path = dt_get_url_path();
        if ( "metrics/$this->base_slug/$this->slug" === $url_path || "metrics" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 10 );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function scripts() {
        wp_enqueue_script( 'dt_metrics_activity_script', get_template_directory_uri() . $this->js_file_name, [
            'jquery',
            'jquery-ui-core',
            'lodash'
        ], filemtime( get_theme_file_path() .  $this->js_file_name ), true );

        wp_localize_script(
            'dt_metrics_activity_script', 'dtMetricsActivity', [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . $this->namespace,
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_template_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'reaction_options' => apply_filters( 'dt_comments_reaction_options', dt_get_site_custom_lists( 'comment_reaction_options' ) ),
                'data' => [
                    'highlights' => self::get_user_highlights( '1970-01-01' )
                ],
                'translations' => [
                    'title' => __( 'Activity Highlights', 'disciple_tools' ),
                    'all_time' => __( "All Time", 'disciple_tools' ),
                    'filter_to_date_range' => __( "Filter to date range", 'disciple_tools' ),
                    'all' => __( "All", 'disciple_tools' ),
                    'contact' => __( "Contact", 'disciple_tools' ),
                    'group' => __( "Group", 'disciple_tools' ),
                ],
            ]
        );
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, 'highlights_data', [
                'methods'  => 'GET',
                'callback' => [ $this, 'api_highlights_data' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function api_highlights_data( WP_REST_Request $request ) {
        $params = $request->get_params();
        try {
            if ( isset( $params['from'] ) && isset( $params['to'] ) ) {
                self::check_date_string( $params['from'] );
                self::check_date_string( $params['to'] );
                return self::get_user_highlights( $params['from'], $params['to'] );
            } if ( isset( $params['start'] ) && isset( $params['end'] ) ) {
                self::check_date_string( $params['start'] );
                self::check_date_string( $params['end'] );
                return self::get_user_highlights( $params['start'], $params['end'] );
            } else {
                return self::get_user_highlights();
            }
        } catch ( Exception $e ) {
            error_log( $e );
            return new WP_Error( __FUNCTION__, "got error ", [ 'status' => 500 ] );
        }
    }

    private static function check_date_string( string $str ) {
        if ( ! preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $str, $matches ) ) {
            return new WP_Error( "Could not parse date, expected YYYY-MM-DD format" );
        }
    }

    private static function get_user_highlights( $from = null, $to = null ) {

        $contact_field_settings = DT_Posts::get_post_field_settings( 'contacts' );

        $data = [];
        $data['contacts_created'] = self::get_records_created( $from, $to, 'contacts' );
        $data['quick_actions_done'] = self::get_quick_actions_done( $from, $to, $contact_field_settings );
        $data['seeker_path_changed'] = self::get_info_added( $from, $to, 'contacts', 'seeker_path', '%', $contact_field_settings );
        $data['milestones_added'] = self::get_info_added( $from, $to, 'contacts', 'milestones', 'milestone_%', $contact_field_settings );

        $data['milestones_added_by_others'] = self::get_info_added_by_others( $from, $to, 'contacts', 'milestones', 'milestone_', $contact_field_settings );
        $data['seeker_path_changed_by_others'] = self::get_info_added_by_others( $from, $to, 'contacts', 'seeker_path', '', $contact_field_settings );

        $data['baptisms'] = self::get_baptisms( $from, $to );
        $data['baptisms_by_others'] = self::get_baptisms_by_others( $from, $to );

        $data['comments_posted'] = self::get_comments_posted( $from, $to );
        $data['comments_liked'] = self::get_comments_liked( $from, $to );

        $group_field_settings = DT_Posts::get_post_field_settings( 'groups' );
        $data['groups_created'] = self::get_records_created( $from, $to, 'groups' );
        $data['health_metrics_added'] = self::get_info_added( $from, $to, 'groups', 'health_metrics', 'church_%', $group_field_settings );
        $data['group_type_changed'] = self::get_info_added( $from, $to, 'groups', 'group_type', '%', $group_field_settings );

        $data['health_metrics_added_by_others'] = self::get_info_added_by_others( $from, $to, 'groups', 'health_metrics', 'church_', $group_field_settings );
        $data['group_type_changed_by_others'] = self::get_info_added_by_others( $from, $to, 'groups', 'group_type', '', $group_field_settings );

        return $data;
    }

    private static function get_records_created( $from, $to, $post_type = 'contacts' ) {
        global $wpdb;

        $post_settings = apply_filters( 'dt_get_post_type_settings', [], $post_type );

        $prepare_args = [ $post_type, get_current_user_id() ];
        self::insert_dates( $from, $to, $prepare_args );

        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                COUNT(action) as records_created
            FROM
                $wpdb->dt_activity_log
            WHERE
                object_type = %s
                AND
                    action = 'created'
                AND
                    user_id = %d
                AND 1=1 "
                               . ( $from ? " AND hist_time >= %s " : "" )
                               . ( $to ? " AND hist_time <= %s " : "" )
                               . "
            GROUP BY
                action;",
            ...$prepare_args
        );
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        // phpcs:enable

        $records_created = $rows[0]['records_created'];

        return [
            'count' => $records_created,
            'label' => sprintf( esc_html__( '%1$d %2$s created', 'disciple_tools' ), $records_created, $records_created === 1 ? $post_settings['label_singular'] : $post_settings['label_plural'] ),
        ];
    }

    private static function get_quick_actions_done( $from, $to, $contact_field_settings ) {
        global $wpdb;

        $prepare_args = [ get_current_user_id() ];
        self::insert_dates( $from, $to, $prepare_args );

        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                object_subtype as quick_button, COUNT(object_subtype) as count
            FROM
                $wpdb->dt_activity_log
            WHERE
                action = 'field_update'
            AND
                object_subtype LIKE 'quick_button_%'
            AND
                user_id = %d
            AND 1=1 "
                            . ( $from ? " AND hist_time >= %s " : "" )
                            . ( $to ? " AND hist_time <= %s " : "" )
                            . "
            GROUP BY
                object_subtype;",
            ...$prepare_args
        );
        // phpcs:enable

        $rows = $wpdb->get_results( $sql, ARRAY_A );

        if ( !empty( $rows ) ) {
            foreach ( $rows as $i => $row ) {
                $rows[$i] = array_merge([
                    'label' => $contact_field_settings[$row['quick_button']]['name'],
                ], $row);
            }
        }
        return $rows;
    }

    private static function get_info_added( $from, $to, $post_type, $subtype, $meta_value_like, $field_settings ) {
        global $wpdb;

        $prepare_args = [ $post_type, $subtype, $meta_value_like, get_current_user_id() ];
        self::insert_dates( $from, $to, $prepare_args );

        $postmeta_join_sql = self::get_postmeta_join_sql();

        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                a1.meta_value as meta_changed, COUNT(a1.meta_value) as count
            FROM
                $wpdb->dt_activity_log AS a1
            $postmeta_join_sql
            WHERE
                action = 'field_update'
            AND
                object_type = %s
            AND
                object_subtype = %s
            AND
                a1.meta_value LIKE %s
            AND
                user_id = %d
            AND 1=1 "
                            . ( $from ? " AND hist_time >= %s " : "" )
                            . ( $to ? " AND hist_time <= %s " : "" )
                            . "
            GROUP BY
                a1.meta_value;",
            ...$prepare_args
        );
        // phpcs:enable

        $rows = $wpdb->get_results( $sql, ARRAY_A );

        $rows = self::insert_labels( $rows, $subtype, $field_settings );

        return $rows;
    }

    private static function get_info_added_by_others( $from, $to, $post_type, $subtype, $meta_value_like, $field_settings ) {
        global $wpdb;

        $records_connected_to_sql = self::get_activity_logs_by_others_sql( $post_type );

        $postmeta_join_sql = self::get_postmeta_join_sql();

        $prepare_args = [ $post_type, $subtype, get_current_user_id() ];

        self::insert_dates( $from, $to, $prepare_args );

        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                a1.meta_value as meta_changed, COUNT(a1.meta_value) as count
            FROM (
                $records_connected_to_sql
            ) as a1
            $postmeta_join_sql
            WHERE
                action = 'field_update'
            AND
                object_type = %s
            AND
                object_subtype = %s
            AND
                a1.meta_value REGEXP '" . ( $meta_value_like === '' ? '^' : "^$meta_value_like" ) . "'
            AND
                user_id != %d
            AND 1=1 "
                            . ( $from ? " AND hist_time >= %s " : "" )
                            . ( $to ? " AND hist_time <= %s " : "" )
                            . "
            GROUP BY
                a1.meta_value;",
            ...$prepare_args
        );
        // phpcs:enable

        $rows = $wpdb->get_results( $sql, ARRAY_A );

        $rows = self::insert_labels( $rows, $subtype, $field_settings );

        return $rows;
    }

    private static function get_baptisms( $from, $to ) {
        global $wpdb;

        $user_id = get_current_user_id();
        $users_contact_id = Disciple_Tools_Users::get_contact_for_user( $user_id );

        $prepare_args = [ $users_contact_id ];
        self::insert_dates( $from, $to, $prepare_args );

        $postmeta_join_sql = self::get_postmeta_join_sql();

        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                a1.meta_value as baptism_date
            FROM (
                SELECT
                    *
                FROM
                    $wpdb->dt_activity_log
                WHERE
                    meta_key = 'baptism_date'
            ) as a1
            JOIN (
                SELECT
                    *
                FROM
                    $wpdb->dt_activity_log
                WHERE
                    meta_key = 'baptizer_to_baptized'
            ) as a2
            $postmeta_join_sql
            WHERE
                a1.action = 'field_update'
            AND
                a1.object_id = a2.meta_value
            AND
                a2.object_id = %d
            AND 1=1 "
                            . ( $from ? " AND a1.meta_value >= %s " : "" )
                            . ( $to ? " AND a1.meta_value <= %s " : "" )
                            . "
            ",
            ...$prepare_args
        );

        $rows = $wpdb->get_results( $sql, ARRAY_A );
        // phpcs:enable

        return count( $rows );
    }

    private static function get_baptisms_by_others( $from, $to ) {
        global $wpdb;

        $user_id = get_current_user_id();
        $users_contact_id = Disciple_Tools_Users::get_contact_for_user( $user_id );

        $prepare_args = [ $users_contact_id ];
        self::insert_dates( $from, $to, $prepare_args );

        $activity_by_others = self::get_activity_logs_by_others_sql( 'contacts' );
        $postmeta_join_sql = self::get_postmeta_join_sql();

        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                a1.meta_value as baptism_date, a2.object_name as baptizer_name
            FROM (
                SELECT
                    *
                FROM (
                    $activity_by_others
                ) as a
                WHERE
                    a.meta_key = 'baptism_date'
            ) as a1
            JOIN (
                SELECT
                    *
                FROM
                    $wpdb->dt_activity_log
                WHERE
                    meta_key = 'baptizer_to_baptized'
            ) as a2
            $postmeta_join_sql
            WHERE
                a1.action = 'field_update'
            AND
                a1.object_id = a2.meta_value
            AND
                a2.object_id != %d
            AND 1=1 "
                            . ( $from ? " AND a1.meta_value >= %s " : "" )
                            . ( $to ? " AND a1.meta_value <= %s " : "" )
                            . "
            ",
            ...$prepare_args
        );

        $rows = $wpdb->get_results( $sql, ARRAY_A );
        // phpcs:enable

        return $rows;
    }

    private static function get_comments_posted( $from, $to ) {
        global $wpdb;

        $user_id = get_current_user_id();

        $prepare_args = [ $user_id ];
        self::insert_dates( $from, $to, $prepare_args, false );

        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                c.comment_date, c.comment_content, p.post_title, p.post_type, p.ID
            FROM
                $wpdb->comments c
            JOIN
                $wpdb->posts p
            ON
                c.comment_post_ID = p.ID
            WHERE
                user_id = %d
            AND 1=1 "
                            . ( $from ? " AND comment_date >= %s " : "" )
                            . ( $to ? " AND comment_date <= %s " : "" )
                            . "
            ORDER BY c.comment_date desc
            ", $prepare_args);

        $rows = $wpdb->get_results( $sql, ARRAY_A );
        // phpcs:enable

        return $rows;
    }

    private static function get_comments_liked( $from, $to ) {
        global $wpdb;

        $user_id = get_current_user_id();

        $prepare_args = [ $user_id ];
        self::insert_dates( $from, $to, $prepare_args, false );

        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                c.comment_id, c.comment_date, c.comment_content, p.post_title, p.post_type, p.ID, cm.meta_key as reaction_type
            FROM
                $wpdb->comments c
            JOIN
                $wpdb->posts p
            ON
                c.comment_post_ID = p.ID
            JOIN
                $wpdb->commentmeta cm
            ON
                cm.comment_id = c.comment_id
            WHERE
                cm.meta_value = %d
            AND
                cm.meta_key REGEXP '^reaction_'
            AND 1=1 "
                            . ( $from ? " AND comment_date >= %s " : "" )
                            . ( $to ? " AND comment_date <= %s " : "" )
                            . "
            ORDER BY c.comment_date desc
            ", $prepare_args);

        $rows = $wpdb->get_results( $sql, ARRAY_A );
        // phpcs:enable

        $reaction_options = apply_filters( 'dt_comments_reaction_options', dt_get_site_custom_lists( 'comment_reaction_options' ) );

        $comments = [];

        if ( !empty( $rows ) ) {
            foreach ( $rows as $row ) {
                $reaction_key = str_replace( 'reaction_', '', $row['reaction_type'] );
                $reaction = isset( $reaction_options[$reaction_key] ) ? $reaction_options[$reaction_key] : null;

                if ( !$reaction ) { continue; }

                $reaction['key'] = $reaction_key;

                $comment_id = $row['comment_id'];
                $comments[$comment_id] = array_merge([
                    'reactions' => isset( $comments[$comment_id] )
                        ? array_merge( $comments[$comment_id]['reactions'], [ $reaction ] )
                        : [ $reaction ],
                ], $row);
            }
        }

        $merged_comments = [];

        foreach ( $comments as $comment ) {
            $merged_comments[] = $comment;
        }

        return $merged_comments;
    }

    private static function get_activity_logs_by_others_sql( $post_type ) {
        global $wpdb;

        $user_id = get_current_user_id();
        $users_contact_id = Disciple_Tools_Users::get_contact_for_user( $user_id );

        $coaching_p2p_key = $post_type === 'contacts' ? 'contacts_to_contacts' : 'groups_to_coaches';

        $prepare_args = [ "user-$user_id", $coaching_p2p_key, $users_contact_id ];

        if ( $post_type === 'contacts' ) {
            $prepare_args[] = $users_contact_id;
        }

        $records_connected_to_sql = $wpdb->prepare( "
            SELECT
                a.*
            FROM
                $wpdb->postmeta pm
            JOIN
                $wpdb->dt_activity_log a
            ON
                pm.post_id = a.object_id
            WHERE
                pm.meta_key = 'assigned_to'
            AND
                pm.meta_value = %s
            UNION SELECT
                    a.*
                FROM
                    $wpdb->p2p p
                JOIN
                    $wpdb->dt_activity_log a
                ON
                    p.p2p_from = a.object_id
                WHERE
                    p.p2p_type = %s
                AND
                    p.p2p_to = %d " . (
            $post_type === 'contacts' ? "
            UNION SELECT
                    a.*
                FROM
                    $wpdb->p2p p
                JOIN
                    $wpdb->dt_activity_log a
                ON
                    p.p2p_to = a.object_id
                WHERE
                    p.p2p_type = 'contacts_to_subassigned'
                AND
                    p.p2p_from = %d " : '' ) . "
            ", $prepare_args );

        return $records_connected_to_sql;
    }

    private static function get_postmeta_join_sql() {
        global $wpdb;

        return "
        INNER JOIN
            $wpdb->postmeta pm
        ON
            a1.meta_key = pm.meta_key
        AND
            a1.meta_value = pm.meta_value
        AND
            a1.object_id = pm.post_id
        ";
    }

    private static function insert_labels( $rows, $subtype, $field_settings ) {
        if ( !empty( $rows ) ) {
            foreach ( $rows as $i => $row ) {
                $label = ( isset( $field_settings[$subtype]['default'][$row['meta_changed']] ) )
                    ? $field_settings[$subtype]['default'][$row['meta_changed']]['label']
                    : null;
                $rows[$i] = array_merge([
                    'label' => $label,
                ], $row);
            }
        }

        return $rows;
    }

    private static function insert_dates( $from, $to, &$prepare_args, $epoch_timestamp = true ) {
        if ( $from ) {
            $prepare_args[] = $epoch_timestamp ? strtotime( $from ) : $from;
        }
        if ( $to ) {
            $prepare_args[] = $epoch_timestamp ? strtotime( $to ) : $to;
        }
    }

}
new Disciple_Tools_Metrics_Personal_Activity_Highlights();
