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
                'data' => [
                    'highlights' => self::get_user_highlights('1970-01-01')
                ],
                'translations' => [
                    'title' => __( 'Activity Highlights', 'disciple_tools' ),
                    'all_time' => __( "All Time", 'disciple_tools' ),
                    'filter_to_date_range' => __( "Filter to date range", 'disciple_tools' ),
                ],
            ]
        );
    }

    public function add_api_routes()
    {
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
            if (isset( $params['from'] ) && isset( $params['to'] ) ) {
                self::check_date_string( $params['from'] );
                self::check_date_string( $params['to'] );
                return self::get_user_highlights( $params['from'], $params['to'] );
            } if (isset( $params['start'] ) && isset( $params['end'] ) ) {
                self::check_date_string( $params['start'] );
                self::check_date_string( $params['end'] );
                return self::get_user_highlights( $params['start'], $params['end'] );
            } else {
                return self::get_user_highlights();
            }
        } catch (Exception $e) {
            error_log( $e );
            return new WP_Error( __FUNCTION__, "got error ", [ 'status' => 500 ] );
        }
    }

    private static function check_date_string( string $str ) {
        if ( ! preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $str, $matches ) ) {
            return new WP_Error( "Could not parse date, expected YYYY-MM-DD format" );
        }
    }

    private static function get_user_highlights($from = null, $to = null) {

        $contact_field_settings = DT_Posts::get_post_field_settings( 'contacts' );

        $data = [];
        $data['contacts_created'] = self::get_records_created( $from, $to, 'contacts' );
        $data['quick_actions_done'] = self::get_quick_actions_done( $from, $to, $contact_field_settings );
        $data['seeker_path_changed'] = self::get_info_added( $from, $to, 'contacts', 'seeker_path', '%', $contact_field_settings);
        $data['milestones_added'] = self::get_info_added( $from, $to, 'contacts', 'milestones', 'milestone_%', $contact_field_settings );

        $data['milestones_added_by_others'] = self::get_info_added_by_others( $from, $to, 'contacts', 'milestones', 'milestone_', $contact_field_settings );
        $data['seeker_path_changed_by_others'] = self::get_info_added_by_others( $from, $to, 'contacts', 'seeker_path', '', $contact_field_settings);

        $group_field_settings = DT_Posts::get_post_field_settings( 'groups' );
        $data['groups_created'] = self::get_records_created( $from, $to, 'groups' );
        $data['health_metrics_added'] = self::get_info_added( $from, $to, 'groups', 'health_metrics', 'church_%', $group_field_settings);
        $data['group_type_changed'] = self::get_info_added( $from, $to, 'groups', 'group_type', '%', $group_field_settings);

        $data['health_metrics_added_by_others'] = self::get_info_added_by_others( $from, $to, 'groups', 'health_metrics', 'church_', $group_field_settings);
        $data['group_type_changed_by_others'] = self::get_info_added_by_others( $from, $to, 'groups', 'group_type', '', $group_field_settings);

        return $data;
    }

    private static function get_records_created( $from, $to, $post_type = 'contacts' ) {
        global $wpdb;

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

        return $rows[0]['records_created'];
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

        if ( !empty($rows) ) {
            foreach ($rows as $i => $row) {
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

        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                a.meta_value as meta_changed, COUNT(a.meta_value) as count
            FROM
                $wpdb->dt_activity_log AS a
            WHERE
                action = 'field_update'
            AND
                object_type = %s
            AND
                object_subtype = %s
            AND
                a.meta_value LIKE %s
            AND
                user_id = %d
            AND 1=1 "
                            . ( $from ? " AND hist_time >= %s " : "" )
                            . ( $to ? " AND hist_time <= %s " : "" )
                            . "
            GROUP BY
                a.meta_value;",
            ...$prepare_args
        );
        // phpcs:enable

        $rows = $wpdb->get_results( $sql, ARRAY_A );

        $rows = self::insert_labels( $rows, $subtype, $field_settings );

        return $rows;
    }

    private static function get_info_added_by_others( $from, $to, $post_type, $subtype, $meta_value_like, $field_settings ) {
        global $wpdb;

        $user_id = get_current_user_id();
        $users_contact_id = Disciple_Tools_Users::get_contact_for_user( $user_id );

        $coaching_p2p_key = $post_type === 'contacts' ? 'contacts_to_contacts' : 'groups_to_coaches';

        $prepare_args = [ "user-$user_id", $coaching_p2p_key, $users_contact_id ];

        if ( $post_type === 'contacts' ) {
            $prepare_args[] = $users_contact_id;
        }

        $prepare_args = array_merge( $prepare_args, [ $post_type, $subtype, get_current_user_id() ] );
        self::insert_dates( $from, $to, $prepare_args );


        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                meta_value as meta_changed, COUNT(meta_value) as count
            FROM (
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
                        p.p2p_from = %d " : '' )
            . "
            ) as a
            WHERE
                action = 'field_update'
            AND
                object_type = %s
            AND
                object_subtype = %s
            AND
                meta_value REGEXP '" . ( $meta_value_like === '' ? '^' : "^$meta_value_like" ) . "'
            AND
                user_id != %d
            AND 1=1 "
                            . ( $from ? " AND hist_time >= %s " : "" )
                            . ( $to ? " AND hist_time <= %s " : "" )
                            . "
            GROUP BY
                meta_value;",
            ...$prepare_args
        );
        // phpcs:enable

        $rows = $wpdb->get_results( $sql, ARRAY_A );

        $rows = self::insert_labels( $rows, $subtype, $field_settings );

        return $rows;
    }

    private static function insert_labels( $rows, $subtype, $field_settings ) {
        if ( !empty($rows) ) {
            foreach ($rows as $i => $row) {
                $label = (isset($field_settings[$subtype]['default'][$row['meta_changed']]))
                    ? $field_settings[$subtype]['default'][$row['meta_changed']]['label']
                    : null;
                $rows[$i] = array_merge([
                    'label' => $label,
                ], $row);
            }
        }

        return $rows;
    }

    private static function insert_dates( $from, $to, &$prepare_args )
    {
        if ( $from ) {
            $prepare_args[] = strtotime( $from );
        }
        if ( $to ) {
            $prepare_args[] = strtotime( $to );
        }
    }

}
new Disciple_Tools_Metrics_Personal_Activity_Highlights();
