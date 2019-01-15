<?php

Disciple_Tools_Metrics_Contacts::instance();
class Disciple_Tools_Metrics_Contacts extends Disciple_Tools_Metrics_Hooks_Base {
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        if ( !$this->has_permission() ) {
            return;
        }

        $url_path = dt_get_url_path();
        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 50 );

            if ( 'metrics/contacts' === $url_path ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            }
        }
    }

    public function add_url( $template_for_url ) {
        $template_for_url['metrics/contacts'] = 'template-metrics.php';

        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '
            <li><a href="">' . esc_html__( 'Contacts', 'disciple_tools' ) . '</a>
                <ul class="menu vertical nested" id="contacts-menu" aria-expanded="true">
                    <li><a href="' . site_url( '/metrics/contacts/' ) . '#project_seeker_path" onclick="project_seeker_path()">' . esc_html__( 'Seeker Path', 'disciple_tools' ) . '</a></li>
                    <li><a href="' . site_url( '/metrics/contacts/' ) . '#project_milestones" onclick="project_milestones()">' . esc_html__( 'Milestones', 'disciple_tools' ) . '</a></li>
                    <li><a href="' . site_url( '/metrics/contacts/' ) . '#contact_sources" onclick="show_sources_overview()">' . esc_html__( 'Sources', 'disciple_tools' ) . '</a></li>
                </ul>
            </li>
            ';

        return $content;
    }

    public function scripts() {
        wp_enqueue_script( 'dt_metrics_project_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics-contacts.js', [
            'moment',
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts',
            'datepicker',
            'wp-i18n'
        ], filemtime( get_theme_file_path() . '/dt-metrics/metrics-contacts.js' ) );

        $contacts_custom_field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false );
        $overall_status_settings = $contacts_custom_field_settings['overall_status'];
        $overall_status_settings['order'] = array_keys( $overall_status_settings['default'] );
        $seeker_path_settings = $contacts_custom_field_settings['seeker_path'];
        $seeker_path_settings['order'] = array_keys( $seeker_path_settings['default'] );
        $milestone_settings = [];
        foreach ( $contacts_custom_field_settings["milestones"]["default"] as $key => $option ){
            $milestone_settings[$key] = $option["label"];
        }
        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root'               => esc_url_raw( rest_url() ),
                'theme_uri'          => get_stylesheet_directory_uri(),
                'nonce'              => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id'    => get_current_user_id(),
                'data'               => $this->data(),
                'spinner' => '<img src="' .trailingslashit( plugin_dir_url( __DIR__ ) ) . 'ajax-loader.gif" style="height:1em;" />',
                'translations' => [
                    "title" => "test",
                ],
                'sources' => Disciple_Tools_Contacts::list_sources(),
                'source_names' => $contacts_custom_field_settings['sources']['default'],
                'overall_status_settings' => $overall_status_settings,
                'seeker_path_settings' => $seeker_path_settings,
                'milestone_settings' => $milestone_settings,
            ]
        );
    }

    public function data() {

        /**
         * Apply Filters before final enqueue. This provides opportunity for complete override or modification of chart.
         */

        return [
            'seeker_path' => $this->seeker_path(),
            'milestones' => $this->milestones(),
            'sources' => $this->get_source_data_from_db(),
        ];
    }

    /**
     * API Routes
     */
    public function add_api_routes() {
        $version   = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/metrics/seeker_path/', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'seeker_path_endpoint' ],
                ],
            ]
        );
        register_rest_route(
            $namespace, '/metrics/milestones/', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'milestones_endpoint' ],
                ],
            ]
        );
        register_rest_route(
            $namespace, '/metrics/sources_chart_data', [
                'methods'  => 'GET',
                'callback' => [ $this, 'api_sources_chart_data' ],
            ]
        );
        register_rest_route(
            $namespace, '/metrics/sources_chart_data', [
                'methods'  => 'POST',
                'callback' => [ $this, 'api_sources_chart_data' ],
            ]
        );

    }


    public function seeker_path_endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( "seeker_path_endpoint", "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();
        if ( isset( $params["start"], $params["end"] ) ){
            $start = strtotime( $params["start"] );
            $end = strtotime( $params["end"] );
            $result = $this->seeker_path( $start, $end );
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( "seeker_path_endpoint", "Missing a valid values", [ 'status' => 400 ] );
        }
    }

    public function milestones_endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( "milestones", "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();
        if ( isset( $params["start"], $params["end"] ) ){
            $start = strtotime( $params["start"] );
            $end = strtotime( $params["end"] );
            $result = $this->milestones( $start, $end );
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( "milestones", "Missing a valid values", [ 'status' => 400 ] );
        }
    }

    public function _no_results() {
        return '<p>' . esc_attr( 'No Results', 'disciple_tools' ) . '</p>';
    }



    public function seeker_path( $start = null, $end = null ){
        global $wpdb;
        if ( empty( $start ) ){
            $start = 0;
        }
        if ( empty( $end ) ){
            $end = time();
        }

        $res = $wpdb->get_results( $wpdb->prepare( "
            SELECT COUNT( DISTINCT(log.object_id) ) as `value`, log.meta_value as seeker_path
            FROM $wpdb->dt_activity_log log
            INNER JOIN $wpdb->posts post
            ON ( 
                post.ID = log.object_id
                AND log.meta_key = 'seeker_path'
                AND log.hist_time > %s
                AND log.hist_time < %s
                AND log.object_type = 'contacts' 
            )
            INNER JOIN $wpdb->postmeta pm
            ON (
                pm.post_id = post.ID
                AND pm.meta_key = 'seeker_path'
                AND pm.meta_value = log.meta_value
            )
            WHERE post.post_type = 'contacts'
            AND post.post_status = 'publish'
            GROUP BY log.meta_value
        ", $start, $end ), ARRAY_A );

        $field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $seeker_path_options = $field_settings["seeker_path"]["default"];
        $seeker_path_data = [];

        foreach ( $seeker_path_options as $option_key => $option_value ){
            $seeker_path_data[$option_value["label"]] = 0;
            foreach ( $res as $r ){
                if ( $r["seeker_path"] === $option_key ){
                    $seeker_path_data[$option_value["label"]] = $r["value"];
                }
            }
        }
        $return = [];
        foreach ( $seeker_path_data as $k => $v ){
            $return[] = [
                "seeker_path" => $k,
                "value" => (int) $v
            ];
        }

        return $return;
    }
    public function milestones( $start = null, $end = null ){
        global $wpdb;
        if ( empty( $start ) ){
            $start = 0;
        }
        if ( empty( $end ) ){
            $end = time();
        }

        $res = $wpdb->get_results( $wpdb->prepare( "
            SELECT COUNT( DISTINCT(log.object_id) ) as `value`, log.meta_value as milestones
            FROM $wpdb->dt_activity_log log
            INNER JOIN $wpdb->posts post 
            ON (
                post.ID = log.object_id
                AND log.meta_key = 'milestones'
                AND log.hist_time > %s
                AND log.hist_time < %s
                AND log.object_type = 'contacts'
            )
            INNER JOIN $wpdb->postmeta pm
            ON (
                pm.post_id = post.ID
                AND pm.meta_key = 'milestones'
                AND pm.meta_value = log.meta_value
            )
            WHERE post.post_type = 'contacts'
            AND post.post_status = 'publish'
            GROUP BY log.meta_value
        ", $start, $end ), ARRAY_A );

        $field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $milestones_options = $field_settings["milestones"]["default"];
        $milestones_data = [];

        foreach ( $milestones_options as $option_key => $option_value ){
            $milestones_data[$option_value["label"]] = 0;
            foreach ( $res as $r ){
                if ( $r["milestones"] === $option_key ){
                    $milestones_data[$option_value["label"]] = $r["value"];
                }
            }
        }
        $return = [];
        foreach ( $milestones_data as $k => $v ){
            $return[] = [
                "milestones" => $k,
                "value" => (int) $v
            ];
        }

        return $return;
    }

    /**
     * Get source data. Returns an array that looks like this:
     *
     * $rv = [
     *  'website foobar' => [
     *      'name_of_source' => 'website foobar',
     *      'status_paused' => 1,
     *      'status_closed' => 4,
     *      'status_active' => 3,
     *      'total' => 8,
     *      'active_seeker_path_attempted' => 2,
     *      'active_seeker_path_ongoing' => 1,
     *  ],
     *  // etc
     * ];
     *
     */
    public static function get_source_data_from_db( string $from = null, string $to = null ) {
        global $wpdb;

        $prepare_args = [ '1' ];
        if ( $from ) {
            $prepare_args[] = $from;
        }
        if ( $to ) {
            $prepare_args[] = $to;
        }

        // We're being careful to avoid SQL injection and sprintf injection.
        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                a.meta_value sources, b.meta_value overall_status, c.meta_value seeker_path, COUNT(p.ID) count
            FROM
                $wpdb->posts p
            LEFT JOIN
                $wpdb->postmeta a ON p.ID = a.post_id AND a.meta_key = 'sources'
            LEFT JOIN
                $wpdb->postmeta b ON p.ID = b.post_id AND b.meta_key = 'overall_status'
            LEFT JOIN
                $wpdb->postmeta c ON p.ID = c.post_id AND c.meta_key = 'seeker_path'
            WHERE
                post_type = 'contacts'
                AND 1=%s "
                               . ( $from ? " AND post_date >= %s " : "" )
                               . ( $to ? " AND post_date <= %s " : "" )
                               . "
            GROUP BY sources, overall_status, seeker_path;",
            ...$prepare_args
        );
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        // phpcs:enable

        $rv = [];

        foreach ($rows as $row) {
            $source = $row['sources'] ?? 'null';
            $status = 'status_' . ( $row['overall_status'] ?? 'null' );
            $rv[$source]['name_of_source'] = $source;
            $rv[$source][$status] = ( $rv[$source][$status] ?? 0 ) + (int) $row['count'];
            $rv[$source]['total'] = ( $rv[$source]['total'] ?? 0 ) + (int) $row['count'];
            if ( !isset( $rv[$source]['total_active_seeker_path'] )) {
                $rv[$source]['total_active_seeker_path'] = 0;
            }
            if ($row['overall_status'] == 'active') {
                $rv[$source]['total_active_seeker_path'] += (int) $row['count'];
                $rv[$source]['active_seeker_path_' . ( $row['seeker_path'] ?? 'null' )] = (int) $row['count'];
            }
        }
        uasort( $rv, function( $a, $b ) {
            if ( $a['total'] != $b['total'] ) {
                return $a['total'] - $b['total'];
            } else {
                return strcmp( $a['name_of_source'], $b['name_of_source'] );
            }
        } );

        $milestones = self::get_sources_milestones( $from, $to );

        foreach ($milestones as $source => $milestone_data) {
            foreach ($milestone_data as $key => $value) {
                $rv[$source][$key] = $value;
            }
        }

        return $rv;
    }


    public static function get_sources_milestones( string $from = null, string $to = null ) {
        global $wpdb;

        $prepare_args = [ 'milestones' ];
        if ( $from ) {
            $prepare_args[] = $from;
        }
        if ( $to ) {
            $prepare_args[] = $to;
        }

        // We're being careful to avoid SQL injection and sprintf injection.
        // phpcs:disable WordPress.DB.PreparedSQL
        $sql = $wpdb->prepare( "
            SELECT
                a.meta_value sources, b.meta_value milestone, COUNT(p.ID) count
            FROM
                $wpdb->posts p
            LEFT JOIN
                $wpdb->postmeta a ON p.ID = a.post_id AND a.meta_key = 'sources'
            JOIN
                $wpdb->postmeta b ON p.ID = b.post_id AND b.meta_key = %s
            JOIN
                $wpdb->postmeta c ON p.ID = c.post_id AND c.meta_key = 'overall_status' AND c.meta_value = 'active'
            WHERE
                post_type = 'contacts'
                AND 1=1 "
                               . ( $from ? " AND post_date >= %s " : "" )
                               . ( $to ? " AND post_date <= %s " : "" )
                               . "
            GROUP BY
                sources, milestone;",
            ...$prepare_args
        );
        $rows = $wpdb->get_results( $sql, ARRAY_A );
        // phpcs:enable

        $rv = [];
        foreach ($rows as $row) {
            $source = $row['sources'] ?? 'null';
            $rv[$source]['name_of_source'] = $source;
            $rv[$source]['active_' . $row['milestone']] = (int) $row['count'];
        }
        return $rv;
    }

    public function api_sources_chart_data( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !( current_user_can( "view_any_contacts" ) || current_user_can( "view_project_metrics" ) ) ) {
            return new WP_Error( __FUNCTION__, "Permission required: view all contacts", [ 'status' => 403 ] );
        }
        try {
            if (isset( $params['from'] ) && isset( $params['to'] ) ) {
                self::check_date_string( $params['from'] );
                self::check_date_string( $params['to'] );
                return self::get_source_data_from_db( $params['from'], $params['to'] );
            } if (isset( $params['start'] ) && isset( $params['end'] ) ) {
                self::check_date_string( $params['start'] );
                self::check_date_string( $params['end'] );
                return self::get_source_data_from_db( $params['start'], $params['end'] );
            } else {
                return self::get_data_source_from_db();
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
}
