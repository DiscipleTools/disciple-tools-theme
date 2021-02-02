<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Users_Mapbox_Coverage_Map extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'user-management'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'mapbox-map'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-users/mapbox-coverage-map.js'; // should be full file name plus extension
    public $permissions = [ 'list_users', 'manage_dt' ];
    public $namespace = 'user-management/v1/';

    public function __construct() {
        if ( ! DT_Mapbox_API::get_key() ) {
            return;
        }
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }

        $url_path = dt_get_url_path();
        if ( strpos( $url_path, 'user-management' ) !== false ) {
            add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 20 );
        }
        if ( "$this->base_slug/$this->slug" === $url_path ) {
            add_filter( 'dt_metrics_menu', [ $this, 'base_menu' ], 20 ); //load menu links
            add_action( 'wp_enqueue_scripts', [ $this, 'base_scripts' ], 99 );
            add_filter( 'dt_templates_for_urls', [ $this, 'dt_templates_for_urls' ] );
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function dt_templates_for_urls( $template_for_url ) {
        $template_for_url['user-management/mapbox-map'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function base_menu( $content ) {
        return $content;
    }

    public function base_add_url( $template_for_url ) {
        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '<li><a href="'. esc_url( site_url( '/user-management/mapbox-map/' ) ) .'" >' .  esc_html__( 'Coverage Map', 'disciple_tools' ) . '</a></li>';
        return $content;
    }

    public function scripts() {
        $dependencies = [
            'jquery',
            'moment',
            'lodash'
        ];

        wp_enqueue_script(
            'dt_user_map',
            get_template_directory_uri() . '/dt-metrics/common/maps_library.js',
            $dependencies,
            filemtime( get_theme_file_path() . '/dt-metrics/common/maps_library.js' ),
            true
        );
        wp_localize_script(
            'dt_user_map', 'dt_mapbox_metrics', [
                'translations'       => [
                    "user_status" => __( "User Status", 'disciple_tools' ),
                    "all" => __( "All", 'disciple_tools' ),
                    "add_user_to" => _x( "Add user to: %s", 'Add user to: France', 'disciple_tools' )
                ],
                'settings' => [
                    'title' => __( 'Responsibility Coverage', 'disciple_tools' ),
                    'menu_slug' => $this->base_slug,
                    'map_key'            => DT_Mapbox_API::get_key(),
                    'map_mirror'         => trailingslashit( dt_get_location_grid_mirror( true ) ),
                    'url_path'           => dt_get_url_path(),
                    'totals_rest_url' => 'user_grid_totals',
                    'list_by_grid_rest_url' => 'get_user_list',
                    'rest_base_url' => $this->namespace,
                    'geocoder_url' => trailingslashit( get_stylesheet_directory_uri() ),
                    'geocoder_nonce' => wp_create_nonce( 'wp_rest' ),
                    'user_status_options' => DT_User_Management::user_management_options()["user_status_options"]
                ]
            ]
        );
        wp_enqueue_script( 'dt_mapbox_caller',
            get_template_directory_uri() .  $this->js_file_name,
            [
                'jquery',
                'lodash',
                'dt_user_map'
            ],
            filemtime( get_theme_file_path() .  $this->js_file_name ),
            true
        );


        DT_Mapbox_API::load_mapbox_header_scripts();
        DT_Mapbox_API::load_mapbox_search_widget_users();
    }

    public function add_api_routes() {
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
new DT_Users_Mapbox_Coverage_Map();
