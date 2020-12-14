<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Sources_Chart extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'contacts'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'sources'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/contacts/sources.js'; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->title = __( 'Sources Chart', 'disciple_tools' );
        $this->base_title = __( 'Contacts', 'disciple_tools' );

        $url_path = dt_get_url_path();
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {

            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }


    /**
     * Load scripts for the plugin
     */
    public function scripts() {
        wp_register_script( 'datepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', false );
        wp_enqueue_style( 'datepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array() );

        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
        wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', [ 'amcharts-core' ], '4' );

        wp_enqueue_script( 'dt_' . $this->slug . '_script',
            get_template_directory_uri() . $this->js_file_name,
            [
                'moment',
                'jquery',
                'jquery-ui-core',
                'datepicker',
                'amcharts-core',
                'amcharts-charts',
                'lodash'
            ],
            filemtime( get_theme_file_path() . $this->js_file_name )
        );

        $contacts_custom_field_settings = DT_Posts::get_post_field_settings( "contacts" );
        $sources = [];
        foreach ( $contacts_custom_field_settings["sources"]["default"] as $key => $values ){
            $sources[ $key ] = $values["label"];
        }
        $seeker_path_settings = $contacts_custom_field_settings['seeker_path'];
        $seeker_path_settings['order'] = array_keys( $seeker_path_settings['default'] );
        $overall_status_settings = $contacts_custom_field_settings['overall_status'];
        $overall_status_settings['order'] = array_keys( $overall_status_settings['default'] );
        $milestone_settings = [];
        foreach ( $contacts_custom_field_settings["milestones"]["default"] as $key => $option ){
            $milestone_settings[$key] = $option["label"];
        }
        // Localize script with array data
        wp_localize_script(
            'dt_'.$this->slug.'_script', $this->js_object_name, [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/$this->slug",
                "data" => [
                    "sources" => $this->get_source_data_from_db(),
                ],
                'sources' => $sources,
                'source_names' => $contacts_custom_field_settings['sources']['default'],
                'seeker_path_settings' => $seeker_path_settings,
                'overall_status_settings' => $overall_status_settings,
                'milestone_settings' => $milestone_settings,
                'translations' => [
                    'filter_contacts_to_date_range' => __( "Filter contacts to date range:", 'disciple_tools' ),
                    'all_time' => __( "All Time", 'disciple_tools' ),
                    'filter_to_date_range' => __( "Filter to date range", 'disciple_tools' ),
                    'sources' => __( "Sources", 'disciple_tools' ),
                    'sources_filter_out_text' => __( "Showing contacts created during", 'disciple_tools' ),
                    'sources_all_contacts_by_source_and_status' => __( "All contacts, by source and status", 'disciple_tools' ),
                    'sources_contacts_warning' => __( "A contact can come from more than one source.", 'disciple_tools' ),
                    'sources_active_by_seeker_path' => __( "Active contacts, by source and seeker path", 'disciple_tools' ),
                    'sources_only_active' => __( "This is displaying only the contacts with an 'active' status right now.", 'disciple_tools' ),
                    'sources_active_milestone' => __( "Active contacts, by source and faith milestone", 'disciple_tools' ),
                    'sources_active_status_warning' => __( "This is displaying only the contacts with an 'active' status right now.", 'disciple_tools' ),
                    'sources_contacts_warning_milestones' => __( "A contact can come from more than one source, and it can have more than one faith milestone at the same time.", 'disciple_tools' ),
                ]
            ]
        );
    }

    public function add_api_routes() {
        $namespace = "dt-metrics/$this->base_slug/$this->slug";
        register_rest_route(
            $namespace, '/sources_chart_data', [
                'methods'  => 'GET',
                'callback' => [ $this, 'api_sources_chart_data' ],
            ]
        );
        register_rest_route(
            $namespace, '/sources_chart_data', [
                'methods'  => 'POST',
                'callback' => [ $this, 'api_sources_chart_data' ],
            ]
        );
    }

    public function api_sources_chart_data( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !( current_user_can( "dt_all_access_contacts" ) || current_user_can( "view_project_metrics" ) ) ) {
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
                return self::get_source_data_from_db();
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
            INNER JOIN $wpdb->postmeta as type ON ( p.ID = type.post_id AND type.meta_key = 'type' AND type.meta_value != 'user' )
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
            INNER JOIN $wpdb->postmeta as type ON ( p.ID = type.post_id AND type.meta_key = 'type' AND type.meta_value != 'user' )
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


}
new DT_Metrics_Sources_Chart();
