<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Milestones_Chart extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'contacts'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'milestones'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/contacts/milestones.js'; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->title = __( 'Milestones Chart', 'disciple_tools' );
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


        wp_enqueue_script( 'dt_' . $this->slug . '_script',
            get_template_directory_uri() . $this->js_file_name,
            [
                'moment',
                'jquery',
                'jquery-ui-core',
                'datepicker',
                'amcharts-core',
                'amcharts-charts',
            ],
            filemtime( get_theme_file_path() . $this->js_file_name )
        );

        // Localize script with array data
        wp_localize_script(
            'dt_'.$this->slug.'_script', $this->js_object_name, [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/$this->slug",
                "data" => [
                    'milestones' => $this->milestones()
                ],
                'translations' => [
                    'milestones' => __( "Milestones", 'disciple_tools' ),
                    'filter_contacts_to_date_range' => __( "Filter contacts to date range:", 'disciple_tools' ),
                    'all_time' => __( "All Time", 'disciple_tools' ),
                    'filter_to_date_range' => __( "Filter to date range", 'disciple_tools' ),
                ]
            ]
        );
    }

    public function add_api_routes() {
        $namespace = "dt-metrics/$this->base_slug/$this->slug";
        register_rest_route(
            $namespace, '/milestones/', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'milestones_endpoint' ],
                ],
            ]
        );
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
            INNER JOIN $wpdb->postmeta as type ON ( log.object_id = type.post_id AND type.meta_key = 'type' AND type.meta_value != 'user' )
            INNER JOIN $wpdb->posts post
            ON (
                post.ID = log.object_id
                AND post.post_type = 'contacts'
                AND post.post_status = 'publish'
            )
            INNER JOIN $wpdb->postmeta pm
            ON (
                pm.post_id = post.ID
                AND pm.meta_key = 'milestones'
                AND pm.meta_value = log.meta_value
            )
            WHERE log.meta_key = 'milestones'
            AND log.object_type = 'contacts'
            AND log.hist_time > %s
            AND log.hist_time < %s
            GROUP BY log.meta_value
        ", $start, $end ), ARRAY_A );

        $field_settings = DT_Posts::get_post_field_settings( "contacts" );
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
}
new DT_Metrics_Milestones_Chart();
