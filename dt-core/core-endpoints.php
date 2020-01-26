<?php
/**
 * Custom endpoints file
 */

/**
 * Class Disciple_Tools_Users_Endpoints
 */
class Disciple_Tools_Core_Endpoints {

    private $version = 1;
    private $context = "dt-core";
    private $namespace;

    /**
     * Disciple_Tools_Users_Endpoints constructor.
     */
    public function __construct() {
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * Setup for API routes
     */
    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/settings', [
                'methods'  => "GET",
                'callback' => [ $this, 'get_settings' ]
            ]
        );
    }


    /**
     * These are settings available to any logged in user.
     */
    public function get_settings() {
        $user = wp_get_current_user();
        if ( $user ){
            $available_translations = dt_get_available_languages();
            return [
                "available_translations" => $available_translations
            ];
        } else {
            return new WP_Error( "get_settings", "Something went wrong. Are you a user?", [ 'status' => 400 ] );
        }
    }

}
