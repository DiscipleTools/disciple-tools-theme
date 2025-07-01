<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Mapbox_Groups_Maps extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'groups'; // lowercase
    public $base_title;
    public $post_type = 'groups';

    public $title;
    public $slug = 'mapbox_groups_maps'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/common/maps_library.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_groups', 'view_project_metrics' ];
    public $namespace = 'dt-metrics/groups';
    public $base_filter = [];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->title = __( 'Group Maps', 'disciple_tools' );
        $this->base_title = __( 'Groups', 'disciple_tools' );


        $url_path = dt_get_url_path( true );
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
        $field_settings = DT_Posts::get_post_field_settings( $this->post_type );
        wp_localize_script(
            'dt_mapbox_script', 'dt_mapbox_metrics', [
                'settings' => [
                    'map_key' => DT_Mapbox_API::get_key(),
                    'no_map_key_msg' => _x( 'To view this map, a mapbox key is needed; click here to add.', 'install mapbox key to view map', 'disciple_tools' ),
                    'map_mirror' => dt_get_location_grid_mirror( true ),
                    'menu_slug' => $this->base_slug,
                    'post_type' => 'groups',
                    'title' => $this->title,
                    'geocoder_url' => trailingslashit( get_stylesheet_directory_uri() ),
                    'geocoder_nonce' => wp_create_nonce( 'wp_rest' ),
                    'rest_base_url' => $this->namespace,
                    'rest_url' => 'cluster_geojson',
                    'totals_rest_url' => 'get_grid_totals',
                    'list_by_grid_rest_url' => 'get_list_by_grid_id',
                    'points_rest_url' => 'points_geojson',
                    'split_by' => [ 'group_status' => $field_settings['group_status'] ],
                ],
            ]
        );
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/cluster_geojson', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'cluster_geojson' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
        register_rest_route(
            $this->namespace, '/get_grid_totals', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_grid_totals' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
        register_rest_route(
            $this->namespace, '/get_list_by_grid_id', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_list_by_grid_id' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
        register_rest_route(
            $this->namespace, '/points_geojson', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'points_geojson' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
    }

    public function cluster_geojson( WP_REST_Request $request ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, 'Missing Permissions', [ 'status' => 400 ] );
        }

        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, 'Missing Post Types', [ 'status' => 400 ] );
        }
        $post_type = $params['post_type'];
        $query = ( isset( $params['query'] ) && !empty( $params['query'] ) ) ? $params['query'] : [];
        $query = dt_array_merge_recursive_distinct( $query, $this->base_filter );
        $offset = !empty( $params['offset'] ) ? $params['offset'] : 0;
        $limit = !empty( $params['limit'] ) ? $params['limit'] : 50000;

        return Disciple_Tools_Mapping_Queries::cluster_geojson( $post_type, $query, $offset, $limit );
    }



    public function get_grid_totals( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, 'Missing Permissions', [ 'status' => 400 ] );
        }
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, 'Missing Post Types', [ 'status' => 400 ] );
        }
        $post_type = $params['post_type'];
        $query = ( isset( $params['query'] ) && !empty( $params['query'] ) ) ? $params['query'] : [];
        $query = dt_array_merge_recursive_distinct( $query, $this->base_filter );
        $results = Disciple_Tools_Mapping_Queries::query_location_grid_meta_totals( $post_type, $query );

        $list = [];
        foreach ( $results as $result ) {
            $list[$result['grid_id']] = $result;
        }

        return $list;
    }

    public function get_list_by_grid_id( WP_REST_Request $request ) {
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['grid_id'] ) || empty( $params['grid_id'] ) ) {
            return new WP_Error( __METHOD__, 'Missing Post Types', [ 'status' => 400 ] );
        }
        $grid_id = sanitize_text_field( wp_unslash( $params['grid_id'] ) );

        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, 'Missing Post Types', [ 'status' => 400 ] );
        }
        $post_type = $params['post_type'];
        $query = ( isset( $params['query'] ) && !empty( $params['query'] ) ) ? $params['query'] : [];
        $query = dt_array_merge_recursive_distinct( $query, $this->base_filter );

        return Disciple_Tools_Mapping_Queries::query_under_location_grid_meta_id( $post_type, $grid_id, $query );
    }


    /**
     * Points
     */
    public function points_geojson( WP_REST_Request $request ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, 'Missing Permissions', [ 'status' => 400 ] );
        }

        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, 'Missing Post Types', [ 'status' => 400 ] );
        }
        $post_type = $params['post_type'];
        $query = ( isset( $params['query'] ) && !empty( $params['query'] ) ) ? $params['query'] : [];
        $query = dt_array_merge_recursive_distinct( $query, $this->base_filter );
        $offset = !empty( $params['offset'] ) ? $params['offset'] : 0;
        $limit = !empty( $params['limit'] ) ? $params['limit'] : 50000;

        return Disciple_Tools_Mapping_Queries::points_geojson( $post_type, $query, $offset, $limit );
    }
}
new DT_Metrics_Mapbox_Groups_Maps();
