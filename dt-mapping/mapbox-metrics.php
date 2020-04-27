<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Mapbox_Metrics {

    //slug and titile of the top menu folder
    public $base_slug = 'mapbox'; // lowercase
    public $base_title = "Mapping";

    public $slugs;
    public $title = 'Map';
    public $js_object_name = 'dt_mapbox_metrics'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = 'mapbox-metrics.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    public $namespace = "dt-metrics/mapbox/";

    public function __construct() {
        // parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->slugs = [ 'cluster-contacts', 'cluster-groups', 'area-contacts', 'area-groups' ];

        if ( isset( $_SERVER["SERVER_NAME"] ) ) {
            $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) )
                ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) )
                : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
            if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
            }
        }

        $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );

        // only load map scripts if exact url
        if ( substr( $url_path, 0, strlen( "metrics/$this->base_slug" ) ) === "metrics/$this->base_slug" ) {

            add_action( 'wp_enqueue_scripts', [ $this, 'map_scripts' ], 99 );
        }
        if ( strpos( $url_path, 'metrics' ) === 0 ) {
            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 99 );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function add_url( $template_for_url ) {
        foreach ( $this->slugs as $slug ) {
            $template_for_url["metrics/$this->base_slug/$slug"] = 'template-metrics.php';
        }
        return $template_for_url;
    }
    public function menu( $content ) {
        $content .= '
        <li><a href="">' . esc_html__( 'Mapping', 'disciple_tools' ) . '</a>
            <ul class="menu vertical nested" id="mapbox-menu" aria-expanded="true">
                <li><a href="'. esc_url( site_url( '/metrics/' ) ) . $this->base_slug .'/cluster-contacts/">' .  esc_html__( 'Cluster Contacts', 'disciple_tools' ) . '</a></li>
                <li><a href="'. esc_url( site_url( '/metrics/' ) ) . $this->base_slug .'/cluster-groups/">' .  esc_html__( 'Cluster Groups', 'disciple_tools' ) . '</a></li>
                <li><a href="'. esc_url( site_url( '/metrics/' ) ) . $this->base_slug .'/area-contacts/">' .  esc_html__( 'Area Contacts', 'disciple_tools' ) . '</a></li>
                <li><a href="'. esc_url( site_url( '/metrics/' ) ) . $this->base_slug .'/area-groups/">' .  esc_html__( 'Area Groups', 'disciple_tools' ) . '</a></li>
            </ul>
        </li>
        ';
        return $content;
    }


    public function map_scripts() {
        DT_Mapbox_API::load_mapbox_header_scripts();
        // Map starter Script
        wp_enqueue_script( 'dt_mapbox_script',
            get_template_directory_uri() . '/dt-mapping/' . $this->js_file_name,
            [
                'jquery'
            ],
            filemtime( get_theme_file_path() . '/dt-mapping/' . $this->js_file_name ),
            true
        );
        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_contact_field_defaults();
        $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_group_field_defaults();
        wp_localize_script(
            'dt_mapbox_script', 'dt_mapbox_metrics', [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/",
                'base_slug' => $this->base_slug,
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_id' => get_current_user_id(),
                'map_key' => DT_Mapbox_API::get_key(),
                "spinner_url" => get_stylesheet_directory_uri() . '/spinner.svg',
                "theme_uri" => trailingslashit( get_stylesheet_directory_uri() ),
                'translations' => $this->translations(),
                'contact_settings' => [
                    'post_type' => 'contacts',
                    'title' => __( 'Contacts', "disciple_tools" ),
                    'status_list' => $contact_fields['overall_status']['default'] ?? []
                ],
                'group_settings' => [
                    'post_type' => 'groups',
                    'title' => __( 'Groups', "disciple_tools" ),
                    'status_list' => $group_fields['group_status']['default'] ?? []
                ]
            ]
        );
    }


    public function translations() {
        $translations = [];
        $translations['title'] = __( "Mapping", "disciple_tools" );
        $translations['refresh_data'] = __( "Refresh Cached Data", "disciple_tools" );
        $translations['population'] = __( "Population", "disciple_tools" );
        $translations['name'] = __( "Name", "disciple_tools" );
        return $translations;
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
            $this->namespace, 'grid_totals', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'grid_totals' ],
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




    public function has_permission(){
        $permissions = $this->permissions;
        $pass = count( $permissions ) === 0;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }

    public function grid_totals( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_json_params() ?? $request->get_body_params();
        if ( ! isset( $params['post_type'] ) || empty( $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, "Missing Post Types", [ 'status' => 400 ] );
        }
        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );

        $status = null;
        if ( isset( $params['status'] ) && $params['status'] !== 'all' ) {
            $status = sanitize_text_field( wp_unslash( $params['status'] ) );
        }

        if ( $post_type === 'contacts' ) {
            $results = Disciple_Tools_Mapping_Queries::get_contacts_grid_totals( $status );
        } else if ( $post_type === 'groups' ) {
            $results = Disciple_Tools_Mapping_Queries::get_groups_grid_totals( $status );
        } else {
            return new WP_Error( __METHOD__, "Invalid post type", [ 'status' => 400 ] );
        }

        $list = [];
        foreach ( $results as $result ) {
            $list[$result['grid_id']] = $result;
        }

        return $list;

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

        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );
        if ( $post_type === 'contacts' ) {
            return $this->get_contacts_geojson( $status );
        } else if ( $post_type === 'groups' ) {
            return $this->get_groups_geojson( $status );
        } else {
            return new WP_Error( __METHOD__, "Invalid post type", [ 'status' => 400 ] );
        }
    }

    public function _empty_geojson() {
        return array(
            'type' => 'FeatureCollection',
            'features' => []
        );
    }

    public function get_groups_geojson( $status = null ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        global $wpdb;

        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat 
            FROM $wpdb->dt_location_grid_meta as lg 
                JOIN $wpdb->posts as p ON p.ID=lg.post_id 
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'group_status'
            WHERE lg.post_type = 'groups' AND pm.meta_value = %s", $status), ARRAY_A );
        } else {
            $results = $wpdb->get_results("
            SELECT lg.label as address, p.post_title as name, lg.post_id, lg.lng, lg.lat 
            FROM $wpdb->dt_location_grid_meta as lg 
                JOIN $wpdb->posts as p ON p.ID=lg.post_id 
                LEFT JOIN $wpdb->postmeta as pm ON pm.post_id=p.ID AND pm.meta_key = 'group_status'
            WHERE lg.post_type = 'groups'", ARRAY_A);
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

    public function get_contacts_geojson( $status = null ) {
        if ( ! $this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
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

        $post_type = sanitize_text_field( wp_unslash( $params['post_type'] ) );
        if ( $post_type === 'contacts' ) {
            return $this->get_contacts_grid_list( $status );
        } else if ( $post_type === 'groups' ) {
            return $this->get_groups_grid_list( $status );
        } else {
            return new WP_Error( __METHOD__, "Invalid post type", [ 'status' => 400 ] );
        }

    }

    public function get_contacts_grid_list( $status = null ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }

        global $wpdb;
        if ( $status ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT lgm.grid_id as grid_id, lgm.grid_meta_id, lgm.post_id, po.post_title as name 
            FROM $wpdb->dt_location_grid_meta as lgm 
            LEFT JOIN $wpdb->posts as po ON po.ID=lgm.post_id  
            JOIN $wpdb->postmeta as pm ON pm.post_id=lgm.post_id AND meta_key = 'overall_status' AND meta_value = 'active'
            WHERE lgm.post_type ='contacts'
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

    public function get_groups_grid_list( $status = null ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }

        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT DISTINCT lgm.grid_id as grid_id, lgm.grid_meta_id, lgm.post_id, po.post_title as name 
            FROM $wpdb->dt_location_grid_meta as lgm 
            LEFT JOIN $wpdb->posts as po ON po.ID=lgm.post_id          
            WHERE lgm.post_type ='groups' 
            	AND lgm.grid_id IS NOT NULL 
            	ORDER BY po.post_title
            ;", ARRAY_A );

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
new DT_Mapbox_Metrics();



