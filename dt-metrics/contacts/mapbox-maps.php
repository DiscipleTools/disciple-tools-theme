<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Mapbox_Contacts_Maps extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'contacts'; // lowercase
    public $base_title;

    public $title;
    public $slug = 'mapbox_maps'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/common/maps.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    public $namespace = 'dt-metrics/contacts/';

    public function __construct() {
        if ( ! DT_Mapbox_API::get_key() ) {
            return;
        }
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->title = __( 'Maps', 'disciple_tools' );
        $this->base_title = __( 'Contacts', 'disciple_tools' );

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
        $contact_fields = DT_Posts::get_post_field_settings( "contacts" );
        wp_localize_script(
            'dt_mapbox_script', 'dt_mapbox_metrics', [
                'translations' => [
                    'title' => __( "Mapping", "disciple_tools" ),
                ],
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
                    'totals_rest_url' => 'get_grid_totals',
                    'list_by_grid_rest_url' => 'get_list_by_grid_id',
                    'points_rest_url' => 'points_geojson',
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
        register_rest_route(
            $this->namespace, 'get_grid_totals', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_grid_totals' ],
                ],
            ]
        );
        register_rest_route(
            $this->namespace, 'get_list_by_grid_id', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_list_by_grid_id' ],
                ],
            ]
        );
        register_rest_route(
            $this->namespace, 'points_geojson', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'points_geojson' ],
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

        return self::query_contacts_geojson( $status );
    }

    public static function query_contacts_geojson( $status = null ) {
        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat
            FROM $wpdb->dt_location_grid_meta as lg
                JOIN $wpdb->posts as p ON p.ID=lg.post_id
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'overall_status'
            WHERE lg.post_type = 'contacts'
            AND pm.post_id NOT IN (SELECT u.post_id FROM $wpdb->postmeta as u WHERE u.meta_key = 'corresponds_to_user' AND u.meta_value != '' )
            AND pm.meta_value = %s ", $status), ARRAY_A );
        } else {
            $results = $wpdb->get_results("
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat
            FROM $wpdb->dt_location_grid_meta as lg
                JOIN $wpdb->posts as p ON p.ID=lg.post_id
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'overall_status'
            WHERE lg.post_type = 'contacts'
            AND pm.post_id NOT IN (SELECT u.post_id FROM $wpdb->postmeta as u WHERE ( u.meta_key = 'corresponds_to_user' AND u.meta_value != '') OR ( u.meta_key = 'overall_status' AND u.meta_value = 'closed') )", ARRAY_A);
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

        $results = self::query_contacts_location_grid_meta_totals( $status );

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

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        return self::query_contacts_under_location_grid_meta_id( $grid_id, $status );
    }

    /**
     * Area queries
     */

    public static function query_contacts_location_grid_meta_totals( $status = null ) {
        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
             SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    JOIN $wpdb->postmeta as pm ON lgm.post_id=pm.post_id AND pm.meta_key = 'overall_status' AND pm.meta_value = %s
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t0
            GROUP BY t0.admin0_grid_id
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    JOIN $wpdb->postmeta as pm ON lgm.post_id=pm.post_id AND pm.meta_key = 'overall_status' AND pm.meta_value = %s
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    JOIN $wpdb->postmeta as pm ON lgm.post_id=pm.post_id AND pm.meta_key = 'overall_status' AND pm.meta_value = %s
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    JOIN $wpdb->postmeta as pm ON lgm.post_id=pm.post_id AND pm.meta_key = 'overall_status' AND pm.meta_value = %s
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t3
            GROUP BY t3.admin3_grid_id
            UNION
            SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    JOIN $wpdb->postmeta as pm ON lgm.post_id=pm.post_id AND pm.meta_key = 'overall_status' AND pm.meta_value = %s
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t4
            GROUP BY t4.admin4_grid_id
            UNION
            SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    JOIN $wpdb->postmeta as pm ON lgm.post_id=pm.post_id AND pm.meta_key = 'overall_status' AND pm.meta_value = %s
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t5
            GROUP BY t5.admin5_grid_id;
            ", $status, $status, $status, $status, $status, $status ), ARRAY_A );

        } else {

            $results = $wpdb->get_results( "
            SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
             FROM $wpdb->dt_location_grid_meta as lgm LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id WHERE lgm.post_type = 'contacts'
             AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t0
            GROUP BY t0.admin0_grid_id
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                 FROM $wpdb->dt_location_grid_meta as lgm LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id WHERE lgm.post_type = 'contacts'
             AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
             FROM $wpdb->dt_location_grid_meta as lgm LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id WHERE lgm.post_type = 'contacts'
             AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
             FROM $wpdb->dt_location_grid_meta as lgm LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id WHERE lgm.post_type = 'contacts'
             AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t3
            GROUP BY t3.admin3_grid_id
            UNION
            SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
             FROM $wpdb->dt_location_grid_meta as lgm LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id WHERE lgm.post_type = 'contacts'
             AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t4
            GROUP BY t4.admin4_grid_id
            UNION
            SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
             FROM $wpdb->dt_location_grid_meta as lgm
                LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
             WHERE lgm.post_type = 'contacts'
             AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t5
            GROUP BY t5.admin5_grid_id;
            ", ARRAY_A );
        }

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                $list[$result['grid_id']] = $result;
            }
        }

        return $list;
    }

    public static function query_contacts_under_location_grid_meta_id( $grid_id, $status = null ) {
        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT t0.post_title, t0.post_id FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'overall_status' AND pm2.meta_value = %s )
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t0
            WHERE t0.admin0_grid_id = %d
            UNION
            SELECT DISTINCT t1.post_title, t1.post_id FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'overall_status' AND pm2.meta_value = %s )
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t1
            WHERE t1.admin1_grid_id = %d
            UNION
            SELECT DISTINCT t2.post_title, t2.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'overall_status' AND pm2.meta_value = %s )
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t2
            WHERE t2.admin2_grid_id = %d
            UNION
            SELECT DISTINCT t3.post_title, t3.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'overall_status' AND pm2.meta_value = %s )
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t3
            WHERE t3.admin3_grid_id = %d
            UNION
            SELECT DISTINCT t4.post_title, t4.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'overall_status' AND pm2.meta_value = %s )
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t4
            WHERE t4.admin4_grid_id = %d
            UNION
            SELECT DISTINCT t5.post_title, t5.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id IN ( SELECT pm2.post_id FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = 'overall_status' AND pm2.meta_value = %s )
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') )
            ) as t5
            WHERE t5.admin5_grid_id = %d;
            ", $status, $grid_id, $status, $grid_id, $status, $grid_id, $status, $grid_id, $status, $grid_id, $status, $grid_id ), ARRAY_A );

        } else {

            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT t0.post_title, t0.post_id FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t0
            WHERE t0.admin0_grid_id = %d
            UNION
            SELECT DISTINCT t1.post_title, t1.post_id FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t1
            WHERE t1.admin1_grid_id = %d
            UNION
            SELECT DISTINCT t2.post_title, t2.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t2
            WHERE t2.admin2_grid_id = %d
            UNION
            SELECT DISTINCT t3.post_title, t3.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t3
            WHERE t3.admin3_grid_id = %d
            UNION
            SELECT DISTINCT t4.post_title, t4.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t4
            WHERE t4.admin4_grid_id = %d
            UNION
            SELECT DISTINCT t5.post_title, t5.post_id  FROM (
                SELECT p.post_title, lgm.post_id, lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM $wpdb->dt_location_grid_meta as lgm
                    LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                WHERE lgm.post_type = 'contacts'
                AND lgm.post_id NOT IN (SELECT DISTINCT(p.post_id) FROM $wpdb->postmeta as p WHERE ( p.meta_key = 'corresponds_to_user' AND p.meta_value != '') OR ( p.meta_key = 'overall_status' AND p.meta_value = 'closed'))
            ) as t5
            WHERE t5.admin5_grid_id = %d;
            ", $grid_id, $grid_id, $grid_id, $grid_id, $grid_id, $grid_id ), ARRAY_A );
        }

        return $results;
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

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        return self::query_contacts_points_geojson( $status );
    }

    public static function query_contacts_points_geojson( $status = null ) {
        global $wpdb;

        /* pulling 40k from location_grid_meta table */
        if ( $status ) {
            $results = $wpdb->get_results($wpdb->prepare( "
                SELECT lgm.label as l, p.post_title as n, lgm.post_id as pid, lgm.lng, lgm.lat, lg.admin0_grid_id as a0, lg.admin1_grid_id as a1
                FROM $wpdb->dt_location_grid_meta as lgm
                     LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                     LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                    JOIN $wpdb->postmeta as pm ON pm.post_id=lgm.post_id AND meta_key = 'overall_status' AND meta_value = %s
                WHERE lgm.post_type = 'contacts'
                LIMIT 40000;
                ", $status), ARRAY_A );
        } else {
            $results = $wpdb->get_results("
                SELECT lgm.label as l, p.post_title as n, lgm.post_id as pid, lgm.lng, lgm.lat, lg.admin0_grid_id as a0, lg.admin1_grid_id as a1
                FROM $wpdb->dt_location_grid_meta as lgm
                     LEFT JOIN $wpdb->posts as p ON p.ID=lgm.post_id
                     LEFT JOIN $wpdb->dt_location_grid as lg ON lg.grid_id=lgm.grid_id
                WHERE lgm.post_type = 'contacts'
                LIMIT 40000;
                ", ARRAY_A );
        }

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
new DT_Metrics_Mapbox_Contacts_Maps();
