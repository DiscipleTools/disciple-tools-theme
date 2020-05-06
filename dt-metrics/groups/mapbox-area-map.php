<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Mapbox_Groups_Area_Map extends DT_Metrics_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'groups'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'mapbox_area_map'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/common/area-map.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    public $namespace = 'dt-metrics/groups/';

    public function __construct() {
        if ( ! DT_Mapbox_API::get_key() ) {
            return;
        }
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->title = __( 'Area Map', 'disciple_tools' );
        $this->base_title = __( 'Groups', 'disciple_tools' );

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
                'jquery'
            ],
            filemtime( get_theme_file_path() .  $this->js_file_name ),
            true
        );
        $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_group_field_defaults();
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
                    'totals_rest_base_url' => 'dt-metrics/mapbox/',
                    'list_rest_url' => 'get_grid_list',
                    'list_rest_base_url' => 'dt-metrics/mapbox/',
                    'list_by_grid_rest_url' => 'list_by_grid_id',
                    'list_by_grid_rest_base_url' => $this->namespace,
                    'geocoder_url' => trailingslashit( get_stylesheet_directory_uri() ),
                    'geocoder_nonce' => wp_create_nonce( 'wp_rest' ),
                    'menu_slug' => $this->base_slug,
                    'post_type' => 'groups',
                    'title' => $this->title,
                    'status_list' => $group_fields['group_status']['default'] ?? []
                ],
            ]
        );
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, 'list_by_grid_id', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'list_by_grid_id' ],
                ],
            ]
        );
    }

    public function list_by_grid_id( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['grid_id'] ) || empty( $params['grid_id'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }
        $grid_id = sanitize_text_field( wp_unslash( $params['grid_id'] ) );

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        return $this->query_groups_under_grid_id( $grid_id, $status );
    }

    public function query_groups_under_grid_id( $grid_id, $status ) {
        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT t0.post_title, t0.post_id FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'group_status' AND pm2.meta_value = %s )
            ) as t0
            WHERE t0.admin0_grid_id = %d
            UNION
            SELECT DISTINCT t1.post_title, t1.post_id FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'group_status' AND pm2.meta_value = %s )
            ) as t1
            WHERE t1.admin1_grid_id = %d
            UNION
            SELECT DISTINCT t2.post_title, t2.post_id  FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'group_status' AND pm2.meta_value = %s )
            ) as t2
            WHERE t2.admin2_grid_id = %d
            UNION
            SELECT DISTINCT t3.post_title, t3.post_id  FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'group_status' AND pm2.meta_value = %s )
            ) as t3
            WHERE t3.admin3_grid_id = %d
            UNION
            SELECT DISTINCT t4.post_title, t4.post_id  FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'group_status' AND pm2.meta_value = %s )
            ) as t4
            WHERE t4.admin4_grid_id = %d
            UNION
            SELECT DISTINCT t5.post_title, t5.post_id  FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
                AND pm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'group_status' AND pm2.meta_value = %s )
            ) as t5
            WHERE t5.admin5_grid_id = %d;
            ", $status,$grid_id,$status,$grid_id,$status,$grid_id,$status,$grid_id,$status,$grid_id,$status,$grid_id ), ARRAY_A );
        } else {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT t0.post_title, t0.post_id FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t0
            WHERE t0.admin0_grid_id = %d
            UNION
            SELECT DISTINCT t1.post_title, t1.post_id FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t1
            WHERE t1.admin1_grid_id = %d
            UNION
            SELECT DISTINCT t2.post_title, t2.post_id  FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t2
            WHERE t2.admin2_grid_id = %d
            UNION
            SELECT DISTINCT t3.post_title, t3.post_id  FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t3
            WHERE t3.admin3_grid_id = %d
            UNION
            SELECT DISTINCT t4.post_title, t4.post_id  FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t4
            WHERE t4.admin4_grid_id = %d
            UNION
            SELECT DISTINCT t5.post_title, t5.post_id  FROM (
                SELECT p.post_title, pm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->postmeta as pm
                JOIN $wpdb->posts as p ON p.ID=pm.post_id AND p.post_type = 'groups'
                LEFT JOIN $wpdb->dt_location_grid as lg ON pm.meta_value=lg.grid_id
                WHERE pm.meta_key = 'location_grid'
            ) as t5
            WHERE t5.admin5_grid_id = %d;
            ", $grid_id,$grid_id,$grid_id,$grid_id,$grid_id,$grid_id ), ARRAY_A );
        }

        return $results;
    }

}
new DT_Metrics_Mapbox_Groups_Area_Map();


