<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Mapbox_Personal_Area_Map extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'personal'; // lowercase
    public $base_title;

    public $title;
    public $slug = 'mapbox_area_map'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/common/area-map.js'; // should be full file name plus extension
    public $permissions = [ 'access_contacts' ];
    public $namespace = 'dt-metrics/personal/';

    public function __construct() {
        if ( ! DT_Mapbox_API::get_key() ) {
            return;
        }
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }

        $this->title = __( 'Area Map', 'disciple_tools' );
        $this->base_title = __( 'Personal', 'disciple_tools' );

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
            get_template_directory_uri() . $this->js_file_name,
            [
                'jquery'
            ],
            filemtime( get_theme_file_path() .  $this->js_file_name ),
            true
        );
        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_contact_field_defaults();
        wp_localize_script(
            'dt_mapbox_script', 'dt_mapbox_metrics', [
                'translations' => [
                    'title' => __( "Mapping", "disciple_tools" ),
                    'refresh_data' => __( "Refresh Cached Data", "disciple_tools" ),
                    'population' => __( "Population", "disciple_tools" ),
                    'name' => __( "Name", "disciple_tools" ),
                ],
                'settings' => [
                    'map_key' => DT_Mapbox_API::get_key(),
                    'map_mirror' => dt_get_location_grid_mirror( true ),
                    'totals_rest_url' => 'grid_totals',
                    'totals_rest_base_url' => $this->namespace,
                    'list_rest_url' => 'get_grid_list',
                    'list_rest_base_url' => $this->namespace,
                    'geocoder_url' => trailingslashit( get_stylesheet_directory_uri() ),
                    'geocoder_nonce' => wp_create_nonce( 'wp_rest' ),
                    'menu_slug' => $this->base_slug,
                    'post_type' => 'contacts',
                    'title' => $this->title,
                    'status_list' => $contact_fields['overall_status']['default'] ?? []
                ],
            ]
        );
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, 'grid_totals', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_grid_totals' ],
                ],
            ]
        );
        register_rest_route(
            $this->namespace, 'get_grid_list', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_grid_list' ],
                ],
            ]
        );
    }

    public function get_grid_totals( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        $my_list = $this->my_list();

        $results = Disciple_Tools_Mapping_Queries::get_my_contacts_grid_totals( $my_list, $status );

        $list = [];
        foreach ( $results as $result ) {
            $list[$result['grid_id']] = $result;
        }

        return $list;
    }


    public function get_grid_list( WP_REST_Request $request ){
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }

        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        $my_list = $this->my_list();

        return $this->get_my_grid_list( $my_list, $status );

    }

    public function get_my_grid_list( array $user_post_ids, $status = null ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }

        $prepared_ids = dt_array_to_sql( $user_post_ids );

        global $wpdb;
        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT lgm.grid_id as grid_id, lgm.grid_meta_id, lgm.post_id, po.post_title as name
            FROM $wpdb->dt_location_grid_meta as lgm
            LEFT JOIN $wpdb->posts as po ON po.ID=lgm.post_id
            JOIN $wpdb->postmeta as pm ON pm.post_id=lgm.post_id AND meta_key = 'overall_status' AND meta_value = %s
            WHERE lgm.post_type ='contacts'
                AND lgm.post_id IN ($prepared_ids)
                AND po.ID NOT IN (SELECT DISTINCT(u.post_id) FROM $wpdb->postmeta as u WHERE u.meta_key = 'corresponds_to_user' AND u.meta_value != '')
                AND lgm.grid_id IS NOT NULL
            ORDER BY po.post_title
            ;", $status ), ARRAY_A );
        } else {
            $results = $wpdb->get_results( "
            SELECT DISTINCT lgm.grid_id as grid_id, lgm.grid_meta_id, lgm.post_id, po.post_title as name
            FROM $wpdb->dt_location_grid_meta as lgm
            LEFT JOIN $wpdb->posts as po ON po.ID=lgm.post_id
            WHERE lgm.post_type ='contacts'
                AND lgm.post_id IN ($prepared_ids)
                AND po.ID NOT IN (SELECT DISTINCT(u.post_id) FROM $wpdb->postmeta as u WHERE ( u.meta_key = 'corresponds_to_user' AND u.meta_value != '') OR ( u.meta_key = 'overall_status' AND u.meta_value = 'closed'))
                AND lgm.grid_id IS NOT NULL
            ORDER BY po.post_title
            ;", ARRAY_A );
        }

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
new DT_Metrics_Mapbox_Personal_Area_Map();
