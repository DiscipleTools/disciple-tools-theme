<?php

/**
 * Disciple_Tools_Locations_Endpoints
 *
 * @class   Disciple_Tools_Locations_Endpoints
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Locations_Endpoints
 */
class Disciple_Tools_Locations_Endpoints
{

    private $version = 1;
    private $context = "dt";
    private $namespace;

    /**
     * Disciple_Tools_Locations_Endpoints The single instance of Disciple_Tools_Locations_Endpoints.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Locations_Endpoints Instance
     * Ensures only one instance of Disciple_Tools_Locations_Endpoints is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Locations_Endpoints instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct() {
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    /**
     * Registers all of the routes associated with locations
     */
    public function add_api_routes() {
        $base = '/locations';

        // Holds all routes for locations
        $routes = [
            $base => [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_locations' ],
            ],
            '/locations-compact' => [ // @todo remove, redundant and out of pattern
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_locations_compact' ],
            ],
            $base.'/compact' => [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_locations_compact' ],
            ],
            $base.'/grouped' => [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'get_all_locations_grouped' ],
            ],
            $base.'/import_check' => [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'import_check' ],
            ],
            $base.'/import_check' => [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [ $this, 'import_check' ],
            ],
            $base.'/validate_address' => [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'validate_address' ],
            ],
            $base.'/auto_build_location' => [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'auto_build_location' ],
            ],
            $base.'/auto_build_simple_location' => [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'auto_build_simple_location' ],
            ],
            $base.'/auto_build_levels_from_post' => [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'auto_build_levels_from_post' ],
            ],
        ];

        // Register each route
        foreach ($routes as $route => $args) {
            register_rest_route( $this->namespace, $route, $args );
        }
    }

    /**
     * This import check is run at the end of the location import utility
     * @see /dt-core/utilities/tab-import-csv.php
     * @return array|\WP_Error
     */
    public function import_check() {
        $count = get_transient( 'dt_import_finished_count' );
        $errors = get_transient( 'dt_import_finished_with_errors' );

        if ( empty( $count ) ) {
            $count = 0;
        }
        if ( empty( $errors ) ) {
            $errors = [];
        }

        return [
            'count' => $count,
            'errors' => $errors,
        ];
    }

    /**
     * @return array|\WP_Error
     */
    public function get_locations() {
        //        $params = $request->get_params();
        //        @TODO check permissions
        $locations = Disciple_Tools_Locations::get_locations();

        return $locations;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array
     */
    public function get_locations_compact( WP_REST_Request $request ) {
        $params = $request->get_params();
        $search = "";
        if ( isset( $params['s'] ) ) {
            $search = $params['s'];
        }
        $locations = Disciple_Tools_Locations::get_locations_compact( $search );

        return $locations;
    }

    /**
     *
     * @return array
     */
    public function get_all_locations_grouped() {
        return Disciple_Tools_Locations::get_all_locations_grouped();
    }

    /**
     * Get tract from submitted address
     *
     * @param WP_REST_Request $request
     * @access public
     * @since 0.1
     * @return string|WP_Error The contact on success
     */
    public function validate_address( WP_REST_Request $request ){
        $params = $request->get_json_params();
        if ( isset( $params['address'] ) ){

            $result = Disciple_Tools_Google_Geocode_API::query_google_api( $params['address'] );

            if ( $result['status'] == 'OK' ){
                return $result;
            } else {
                return new WP_Error( "status_error", 'Zero Results', array( 'status' => 400 ) );
            }
        } else {
            return new WP_Error( "param_error", "Please provide a valid address", array( 'status' => 400 ) );
        }
    }

    public function auto_build_location( WP_REST_Request $request ){
        $params = $request->get_json_params();

        if ( isset( $params['data'] ) && isset( $params['type'] ) ){

            if ( !current_user_can( 'publish_locations' ) ) {
                return new WP_Error( __FUNCTION__, __( "You may not publish a location" ), [ 'status' => 403 ] );
            }

            $components = $params['components'] ?? [];

            $result = Disciple_Tools_Locations::auto_build_location( $params['data'], $params['type'], $components );

            if ( is_wp_error( $result ) ) {
                return $result;
            }

            if ( $result['status'] == 'OK' ){
                return $result;
            } else {
                return new WP_Error( "status_error", 'Zero Results', array( 'status' => 400 ) );
            }
        } else {
            return new WP_Error( "param_error", "Please provide a valid address", array( 'status' => 400 ) );
        }
    }

    public function auto_build_simple_location( WP_REST_Request $request ){
        $params = $request->get_json_params();

        if ( isset( $params['title'] ) ){

            if ( !current_user_can( 'publish_locations' ) ) {
                return new WP_Error( __FUNCTION__, __( "You may not publish a location" ), [ 'status' => 403 ] );
            }

            $args = [
                'post_title' => sanitize_text_field( wp_unslash( $params['title'] ) ),
                'post_type' => 'locations',
                'post_status' => 'publish',
            ];
            return wp_insert_post( $args, true );

        } else {
            return new WP_Error( "param_error", "Please provide a valid address", array( 'status' => 400 ) );
        }
    }

    public function auto_build_levels_from_post( WP_REST_Request $request ){
        $params = $request->get_json_params();

        if ( isset( $params['post_id'] ) ){

            if ( !current_user_can( 'publish_locations' ) ) {
                return new WP_Error( __FUNCTION__, __( "You may not publish a location" ), [ 'status' => 403 ] );
            }

            $result = Disciple_Tools_Locations::auto_build_location( $params['post_id'], 'post_id' );

            if ( 'OK' == $result['status'] ?? '' ) {
                $posts_created = $result['posts_created'] ?? [];
                $formatted_array = [];

                foreach ( $posts_created as $single_post ) {
                    $item = get_post_meta( $single_post, 'base_name', true );
                    $formatted_array[] = [
                        'id' => md5( $item ),
                        'link' => '<a href="'. esc_url( admin_url() ).'post.php?post='. esc_attr( $single_post ).'&action=edit">'. esc_html( $item ) .'</a>',
                    ];
                }

                return $formatted_array;
            } else {
                return new WP_Error( "processing_error", "Please provide a valid address", array( 'status' => 400 ) );
            }
        } else {
            return new WP_Error( "param_error", "Please provide a valid address", array( 'status' => 400 ) );
        }
    }
}
