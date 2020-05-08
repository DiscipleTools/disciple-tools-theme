<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Personal_Groups_Cluster_Map extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'personal'; // lowercase
    public $base_title;

    public $title;
    public $slug = 'mapbox_groups_cluster_map'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/common/cluster-map.js'; // should be full file name plus extension
    public $permissions = [ 'access_contacts' ];
    public $namespace = 'dt-metrics/personal/groups/';

    public function __construct() {
        if ( ! DT_Mapbox_API::get_key() ) {
            return;
        }
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }

        $this->title = __( 'Groups Cluster Map', 'disciple_tools' );
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
                    'rest_url' => 'cluster_geojson',
                    'rest_base_url' => $this->namespace,
                    'menu_slug' => $this->base_slug,
                    'post_type' => 'groups',
                    'title' => $this->title,
                    'status_list' => $contact_fields['overall_status']['default'] ?? []
                ],
            ]
        );
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, 'cluster_geojson', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'cluster_geojson' ],
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

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        $my_list = $this->my_groups_list();

        return self::get_my_groups_cluster_geojson( $my_list, $status );
    }

    public static function get_my_groups_cluster_geojson( $my_list, $status = null ) {

        global $wpdb;

        $prepared_ids = dt_array_to_sql( $my_list );

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat
            FROM $wpdb->dt_location_grid_meta as lg
                JOIN $wpdb->posts as p ON p.ID=lg.post_id
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'group_status'
            WHERE lg.post_type = 'groups'
              AND pm.post_id IN ($prepared_ids)
              AND pm.meta_value = %s
              ", $status), ARRAY_A );
        } else {
            $results = $wpdb->get_results("
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat
            FROM $wpdb->dt_location_grid_meta as lg
                JOIN $wpdb->posts as p ON p.ID=lg.post_id
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'group_status'
            WHERE lg.post_type = 'groups'
            AND pm.post_id IN ($prepared_ids)", ARRAY_A);
        }

        $features = [];
        foreach ( $results as $result ) {
            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    "address" => $result['address'],
                    "post_id" => $result['post_id'],
                    "name" => $result['name']
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
new DT_Metrics_Personal_Groups_Cluster_Map();
