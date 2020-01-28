<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Seeker_Path_Chart extends DT_Metrics_Chart_Base
{

    //slug and titile of the top menu folder
    public $base_slug = 'contacts'; // lowercase
    public $base_title = "Contacts";

    public $title = 'Seeker path';
    public $slug = 'seeker_path'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = 'seeker-path.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
//    public $namespace = "dt-metrics/$this->base_slug/$this->slug";

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $url_path = dt_get_url_path();

        // only load scripts if exact url
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }


    /**
     * Load scripts for the plugin
     */
    public function scripts() {

        wp_enqueue_script( 'dt_' . $this->slug . '_script',
            get_template_directory_uri() . '/dt-metrics/contacts/' . $this->js_file_name,
            [
                'moment',
                'jquery',
                'jquery-ui-core',
                'datepicker',
                'amcharts-core',
                'amcharts-charts',
            ],
            filemtime( get_theme_file_path() . '/dt-metrics/contacts/' . $this->js_file_name )
        );

        // Localize script with array data
        wp_localize_script(
            'dt_'.$this->slug.'_script', $this->js_object_name, [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/$this->slug",
                "data" => [
                    'seeker_path' => $this->seeker_path()
                ],
                'translations' => [
                    'seeker_path' => __( "Seeker Path", 'disciple_tools' ),
                    'filter_contacts_to_date_range' => __( "Filter contacts to date range:", 'disciple_tools' ),
                    'all_time' => __( "All time", 'disciple_tools' ),
                    'filter_to_date_range' => __( "Filter to date range", 'disciple_tools' ),
                ]
            ]
        );
    }

    public function add_api_routes() {
        $namespace = "dt-metrics/$this->base_slug/$this->slug";
        register_rest_route(
            $namespace, '/seeker_path/', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'seeker_path_endpoint' ],
                ],
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

    public function seeker_path( $start = null, $end = null ){
        if ( empty( $start ) ){
            $start = 0;
        }
        if ( empty( $end ) ){
            $end = time();
        }

        $seeker_path_activity = Disciple_Tools_Counter_Contacts::seeker_path_activity( $start, $end );
        $return = [];
        foreach ( $seeker_path_activity as $key => $value ){
            if ( $key != "none" ){
                $return[] = [
                    "seeker_path" => $value["label"],
                    "value" => (int) $value["value"]
                ];
            }
        }

        return $return;
    }




}
new DT_Metrics_Seeker_Path_Chart();
