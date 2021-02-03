<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Mapbox_Combined_Maps extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'combined'; // lowercase
    public $base_title;

    public $title;
    public $slug = 'mapbox_combined_maps'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/common/maps_library.js'; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];
    public $namespace = 'dt-metrics/combined/';

    public function __construct() {
        if ( ! DT_Mapbox_API::get_key() ) {
            return;
        }
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->title = __( 'Combined Maps', 'disciple_tools' );
        $this->base_title = __( 'Project', 'disciple_tools' );

        $url_path = dt_get_url_path();
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function scripts() {
        DT_Mapbox_API::load_mapbox_header_scripts();
        // Map starter Script
        wp_enqueue_script( 'dt_mapbox_script',
            get_template_directory_uri() .  $this->js_file_name,
            [
                'jquery',
                'lodash'
            ],
            filemtime( get_theme_file_path() .  $this->js_file_name ),
            true
        );
        wp_enqueue_script( 'dt_mapbox_caller',
            get_template_directory_uri() .  '/dt-metrics/combined/combined.js',
            [
                'jquery',
                'lodash',
                'dt_mapbox_script'
            ],
            filemtime( get_theme_file_path() .  '/dt-metrics/combined/combined.js' ),
            true
        );
        wp_localize_script(
            'dt_mapbox_script', 'dt_mapbox_metrics', [
                'translations' => [],
                'settings' => [
                    'map_key' => DT_Mapbox_API::get_key(),
                    'map_mirror' => dt_get_location_grid_mirror( true ),
                    'menu_slug' => $this->base_slug,
                    'post_type' => 'contacts',
                    'title' => $this->title,
                    'geocoder_url' => trailingslashit( get_stylesheet_directory_uri() ),
                    'geocoder_nonce' => wp_create_nonce( 'wp_rest' ),
                    'rest_base_url' => $this->namespace,
                    'rest_url' => 'cluster_geojson',
                    'totals_rest_url' => 'user_grid_totals',
                    'list_by_grid_rest_url' => 'get_user_list',
                    'points_rest_url' => 'points_geojson',
                ],
            ]
        );
        wp_localize_script(
            'dt_mapbox_caller', 'dt_metrics_mapbox_caller_js', [
                'translations' => [
                    'contacts' => __( "Active Contacts", "disciple_tools" ),
                    'groups' => __( "Active Groups", "disciple_tools" ),
                    'active_users' => __( "Active Users", 'disciple_tools' )
                ],
            ]
        );
    }

    public function add_api_routes() {
//        register_rest_route(
//            $this->namespace, 'cluster_geojson', [
//                [
//                    'methods'  => WP_REST_Server::CREATABLE,
//                    'callback' => [ $this, 'cluster_geojson' ],
//                ],
//            ]
//        );
//        register_rest_route(
//            $this->namespace, 'get_grid_totals', [
//                [
//                    'methods'  => WP_REST_Server::CREATABLE,
//                    'callback' => [ $this, 'get_grid_totals' ],
//                ],
//            ]
//        );
//        register_rest_route(
//            $this->namespace, 'get_list_by_grid_id', [
//                [
//                    'methods'  => WP_REST_Server::CREATABLE,
//                    'callback' => [ $this, 'get_list_by_grid_id' ],
//                ],
//            ]
//        );
        register_rest_route(
            $this->namespace, 'points_geojson', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'points_geojson' ],
                ],
            ]
        );
        register_rest_route(
            $this->namespace, '/user_grid_totals', [
                [
                    'methods'  => "POST",
                    'callback' => [ $this, 'grid_totals' ],
                ],
            ]
        );
        register_rest_route(
            $this->namespace, '/get_user_list', [
                [
                    'methods'  => "POST",
                    'callback' => [ $this, 'get_user_list' ],
                ],
            ]
        );
    }

    public function cluster_geojson( WP_REST_Request $request ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }

        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }
        $post_type = $params['post_type'];

        return Disciple_Tools_Mapping_Queries::cluster_geojson( $post_type );
    }



    public function get_grid_totals( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }
        $post_type = $params['post_type'];

        $results = Disciple_Tools_Mapping_Queries::query_location_grid_meta_totals( $post_type, [] );

        $list = [];
        foreach ( $results as $result ) {
            $list[$result['grid_id']] = $result;
        }

        return $list;

    }

    public function get_list_by_grid_id( WP_REST_Request $request ) {
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['grid_id'] ) || empty( $params['grid_id'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }
        $grid_id = sanitize_text_field( wp_unslash( $params['grid_id'] ) );

        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }
        $post_type = $params['post_type'];

        return Disciple_Tools_Mapping_Queries::query_under_location_grid_meta_id( $post_type, $grid_id, [] );
    }


    /**
     * Points
     */
    public function points_geojson( WP_REST_Request $request ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }

        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }
        $post_type = $params['post_type'];
        $query = [];
        $modules = dt_get_option( "dt_post_type_modules" );
        if ( $post_type === "contacts" ){
            if ( !empty( $modules["access_module"]["enabled"] ) ){
                $query = [ "type" => [ "access" ],  "overall_status" => [ '-closed' ] ];
            }
        }
        if ( $post_type === "groups" ){
            $query = [ "group_status" => [ "-inactive" ] ];
        }

        return Disciple_Tools_Mapping_Queries::points_geojson( $post_type, $query );
    }

    public function grid_totals( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_json_params() ?? $request->get_body_params();
        $query = ( isset( $params["query"] ) && !empty( $params["query"] ) ) ? $params["query"] : [];
        $status = null;
        if ( isset( $query['status'] ) && $query['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $query['status'] ) );
        }
        $status = "active";

        $results = Disciple_Tools_Mapping_Queries::query_user_location_grid_totals( $status );

        return $results;

    }

    public function get_user_list( WP_REST_Request $request ){
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }

        global $wpdb;
        $results = $wpdb->get_results( "
                SELECT u.display_name as name, lgm.grid_meta_id, lgm.grid_id, lgm.post_id as user_id
                FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->users as u ON u.ID=lgm.post_id
                WHERE lgm.post_type = 'users'
                ", ARRAY_A );

        $list = [];
        foreach ( $results as $result ) {
            if ( ! isset( $list[$result['grid_id']] ) ) {
                $list[$result['grid_id']] = [];
            }
            $list[$result['grid_id']][] = $result;
        }

        return $list;
    }


}
new DT_Metrics_Mapbox_Combined_Maps();
