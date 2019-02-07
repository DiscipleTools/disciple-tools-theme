<?php
declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/** This class was built following the example of one-page-chart-template.php */


class Disciple_Cools_Metrics_Chart_Sources extends Disciple_Tools_Metrics_Hooks_Base {

    public $title = 'Sources';
    public $slug = 'sources'; // lowercase
    public $js_object_name = 'wpApiSources'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = 'sources.js'; // should be full file name plus extension
    public $deep_link_hash = '#sources'; // should be the full hash name. #example_of_hash
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];


    public function __construct() {
        if ( !$this->has_permission() ){
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
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /** Load scripts */
    public function scripts() {
        wp_enqueue_script( 'dt_' . $this->slug . '_script', trailingslashit( plugin_dir_url( __FILE__ ) ) . $this->js_file_name, [
            'moment',
            'lodash',
            'datepicker',
            'wp-i18n'
        ], filemtime( plugin_dir_path( __FILE__ ) . $this->js_file_name ), true );

        $contacts_custom_field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( false );

        $overall_status_settings = $contacts_custom_field_settings['overall_status'];
        $overall_status_settings['order'] = array_keys( $overall_status_settings['default'] );

        $seeker_path_settings = $contacts_custom_field_settings['seeker_path'];
        $seeker_path_settings['order'] = array_keys( $seeker_path_settings['default'] );

        $milestone_settings = [];
        foreach ( $contacts_custom_field_settings["milestones"]["default"] as $key => $option ){
            $milestone_settings[$key] = $option["label"];
        }

        // Localize script with array data
        wp_localize_script(
            'dt_' . $this->slug . '_script', $this->js_object_name, [
                'name_key' => $this->slug,
                'root' => esc_url_raw( rest_url() ),
                'plugin_uri' => plugin_dir_url( __DIR__ ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'spinner' => '<img src="' .trailingslashit( plugin_dir_url( __DIR__ ) ) . 'ajax-loader.gif" style="height:1em;" />',
                'translations' => [
                    "title" => $this->title,
                ],
                'sources' => Disciple_Tools_Contacts::list_sources(),
                'source_names' => $contacts_custom_field_settings['sources']['default'],
                'overall_status_settings' => $overall_status_settings,
                'seeker_path_settings' => $seeker_path_settings,
                'milestone_settings' => $milestone_settings,
                'source_data' => self::get_data_from_db()
            ]
        );
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
    public static function get_data_from_db( string $from = null, string $to = null ) {
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

        $milestones = self::get_sources_milestones();

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

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, 'sources_chart_data', [
                'methods'  => 'GET',
                'callback' => [ $this, 'api_sources_chart_data' ],
            ]
        );
        register_rest_route(
            $this->namespace, 'sources_chart_data', [
                'methods'  => 'POST',
                'callback' => [ $this, 'api_sources_chart_data' ],
            ]
        );
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
                return self::get_data_from_db( $params['from'], $params['to'] );
            } if (isset( $params['start'] ) && isset( $params['end'] ) ) {
                self::check_date_string( $params['start'] );
                self::check_date_string( $params['end'] );
                return self::get_data_from_db( $params['start'], $params['end'] );
            } else {
                return self::get_data_from_db();
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
