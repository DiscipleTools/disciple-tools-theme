<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Personal_Contacts_Points_Map extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'personal'; // lowercase
    public $base_title;

    public $title;
    public $slug = 'mapbox_points_map'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/common/points-map.js'; // should be full file name plus extension
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

        $this->title = __( 'Contacts Points Map', 'disciple_tools' );
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
            get_template_directory_uri() .  $this->js_file_name,
            [
                'jquery'
            ],
            filemtime( get_theme_file_path() . $this->js_file_name ),
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
                    'points_rest_url' => 'points_geojson',
                    'points_rest_base_url' => $this->namespace,
                    'menu_slug' => $this->base_slug,
                    'post_type' => 'contacts',
                    'title' => $this->title,
                    'status_list' => $contact_fields['overall_status']['default'] ?? []
                ]
            ]
        );
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, 'points_geojson', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'points_geojson' ],
                ],
            ]
        );
    }

    public function points_geojson( WP_REST_Request $request ) {
        if ( ! $this->has_permission() ){
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

        return self::get_my_contacts_points_geojson( $my_list, $status );
    }

    public function _empty_geojson() {
        return array(
            'type' => 'FeatureCollection',
            'features' => []
        );
    }

    public static function get_my_contacts_points_geojson( array $user_post_ids, $status = null ) {
        global $wpdb;

        $prepared_ids = dt_array_to_sql( $user_post_ids );

        // phpcs can't validate that the $prepared_ids variable is actually escaped. False positive.
        // @link https://github.com/WordPress/WordPress-Coding-Standards/issues/508
        // phpcs:disable
        if ( $status ) {
            $results = $wpdb->get_results($wpdb->prepare( "
                SELECT lgm.label as l, p.post_title as n, lgm.post_id as pid, lgm.lng, lgm.lat, lg.admin0_grid_id as a0, lg.admin1_grid_id as a1
                FROM $wpdb->dt_location_grid_meta as lgm
                     LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                     LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    JOIN $wpdb->postmeta as pm ON pm.post_id=lgm.post_id AND meta_key = 'overall_status' AND meta_value = %s
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id IN ($prepared_ids)
                LIMIT 40000;
                ", $status), ARRAY_A );
        } else {
            $results = $wpdb->get_results("
                SELECT lgm.label as l, p.post_title as n, lgm.post_id as pid, lgm.lng, lgm.lat, lg.admin0_grid_id as a0, lg.admin1_grid_id as a1
                FROM $wpdb->dt_location_grid_meta as lgm
                     LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                     LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id IN ($prepared_ids)
                LIMIT 40000;
                ", ARRAY_A );
        }
        // phpcs:enable


        $features = [];
        foreach ( $results as $result ) {
            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    "l" => $result['l'],
                    "pid" => $result['pid'],
                    "n" => $result['n'],
                    "a0" => $result['a0'],
                    "a1" => $result['a1']
                ),
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        $result['lng'],
                        $result['lat'],
                        1
                    ),
                ),
            );
        }

        $new_data = array(
            'type' => 'FeatureCollection',
            'features' => $features,
        );

        return $new_data;
    }

}
new DT_Metrics_Personal_Contacts_Points_Map();
