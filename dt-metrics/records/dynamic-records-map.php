<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Dynamic_Records_Map extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'records'; // lowercase
    public $base_title;
    public $post_type = 'contacts';
    public $post_types = [];
    public $post_type_options = [];
    public $post_type_system_options = [];

    public $title;
    public $slug = 'dynamic_records_map'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/records/dynamic-records-map.js'; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];
    public $namespace = 'dt-metrics/records';
    public $base_filter = [];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->title = __( 'Records Map', 'disciple_tools' );
        $this->base_title = __( 'Contacts', 'disciple_tools' );

        // Build post types array, ignoring some for now.
        // TODO: Only select post types with valid location field types!
        $post_types = DT_Posts::get_post_types();
        $post_types = array_values( array_diff( $post_types, [ 'peoplegroups' ] ) );
        $this->post_types = $post_types;
        $this->post_type_options = [];
        foreach ( $post_types as $post_type ){
            $field_settings = [];
            foreach ( DT_Posts::get_post_field_settings( $post_type ) ?? [] as $field_key => $field_setting ){
                if ( isset( $field_setting['type'] ) && in_array( $field_setting['type'], [ 'key_select', 'multi_select' ] ) ){
                    if ( ( isset( $field_setting['hidden'] ) && $field_setting['hidden'] ) || ( isset( $field_setting['private'] ) && $field_setting['private'] ) ){
                        continue;
                    } else {
                        $field_settings[$field_key] = [
                            'key' => $field_key,
                            'type' => $field_setting['type'],
                            'name' => $field_setting['name'],
                            'default' => $field_setting['default'] ?? []
                        ];
                    }
                }
            }

            $this->post_type_options[$post_type] = [
                'post_type' => $post_type,
                'label' => DT_Posts::get_label_for_post_type( $post_type ),
                'fields' => $field_settings
            ];
        }

        $this->post_type_system_options = [];
        $this->post_type_system_options['users'] = [
            'label' => __( 'Users', 'disciple_tools' )
        ];

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
        wp_localize_script(
            'dt_mapbox_script', 'dt_mapbox_metrics', [
                'translations' => [
                    'add_records' => [
                        'title' => __( 'Add Records', 'disciple_tools' ),
                        'post_types_title' => __( 'Post Types', 'disciple_tools' ),
                        'layer_tab_button_title' => __( 'Layer', 'disciple_tools' ),
                        'confirm_delete_layer' => __( 'Are you sure you wish to delete layer?', 'disciple_tools' ),
                        'post_type_select_opt_group_record_types' => __( 'Record Types', 'disciple_tools' ),
                        'post_type_select_opt_group_record_types_query_all' => __( 'All', 'disciple_tools' ),
                        'post_type_select_opt_group_system' => __( 'System', 'disciple_tools' )
                    ]
                ],
                'settings' => [
                    'map_key' => DT_Mapbox_API::get_key(),
                    'no_map_key_msg' => _x( 'To view this map, a mapbox key is needed; click here to add.', 'install mapbox key to view map', 'disciple_tools' ),
                    'map_mirror' => dt_get_location_grid_mirror( true ),
                    'menu_slug' => $this->base_slug,
                    'post_type' => $this->post_type,
                    'post_types' => $this->post_type_options,
                    'post_types_system_options' => $this->post_type_system_options,
                    'title' => $this->title,
                    'geocoder_url' => trailingslashit( get_stylesheet_directory_uri() ),
                    'geocoder_nonce' => wp_create_nonce( 'wp_rest' ),
                    'rest_base_url' => $this->namespace,
                    'rest_url' => 'cluster_geojson',
                    'post_type_rest_url' => 'post_type_geojson',
                    'totals_rest_url' => 'get_grid_totals',
                    'list_by_grid_rest_url' => 'get_list_by_grid_id',
                    'points_rest_url' => 'points_geojson'
                ],
            ]
        );
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/post_type_geojson', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'post_type_geojson' ],
                    'permission_callback' => [ $this, 'has_permission' ]
                ]
            ]
        );
        register_rest_route(
            $this->namespace, '/cluster_geojson', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'cluster_geojson' ],
                    'permission_callback' => [ $this, 'has_permission' ]
                ]
            ]
        );
        register_rest_route(
            $this->namespace, '/get_grid_totals', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_grid_totals' ],
                    'permission_callback' => [ $this, 'has_permission' ]
                ]
            ]
        );
        register_rest_route(
            $this->namespace, '/get_list_by_grid_id', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_list_by_grid_id' ],
                    'permission_callback' => [ $this, 'has_permission' ]
                ]
            ]
        );
        register_rest_route(
            $this->namespace, '/points_geojson', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'points_geojson' ],
                    'permission_callback' => [ $this, 'has_permission' ]
                ]
            ]
        );
    }

    public function post_type_geojson( WP_REST_Request $request ){
        $response = [];
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( !empty( $params['post_type'] ) ){

            // Ensure params shape is altered accordingly, for system based post types.
            switch ( $params['post_type'] ){
                case 'system-users':
                    $params['field_type'] = 'user_select';
                    break;
                default:
                    break;
            }

            // Execute request query.
            $response = Disciple_Tools_Mapping_Queries::post_type_geojson( $params['post_type'], $params );
        }

        return [
            'request' => $params,
            'response' => $response
        ];
    }

    public function cluster_geojson( WP_REST_Request $request ) {
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, 'Missing Post Types', [ 'status' => 400 ] );
        }
        $post_type = $params['post_type'];
        $query = ( isset( $params['query'] ) && !empty( $params['query'] ) ) ? $params['query'] : [];
        $query = dt_array_merge_recursive_distinct( $query, $this->base_filter );

        return Disciple_Tools_Mapping_Queries::cluster_geojson( $post_type, $query );
    }



    public function get_grid_totals( WP_REST_Request $request ) {
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
        $post_type = $params['post_type'];
        $query = ( isset( $params['query'] ) && !empty( $params['query'] ) ) ? $params['query'] : [];
        $query = dt_array_merge_recursive_distinct( $query, $this->base_filter );

        return Disciple_Tools_Mapping_Queries::query_under_location_grid_meta_id( $post_type, $grid_id, $query );
    }


    /**
     * Points
     */
    public function points_geojson( WP_REST_Request $request ) {
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, 'Missing Post Types', [ 'status' => 400 ] );
        }
        $post_type = $params['post_type'];
        $query = ( isset( $params['query'] ) && !empty( $params['query'] ) ) ? $params['query'] : [];
        $query = dt_array_merge_recursive_distinct( $query, $this->base_filter );

        return Disciple_Tools_Mapping_Queries::points_geojson( $post_type, $query );
    }


}
new DT_Metrics_Dynamic_Records_Map();
