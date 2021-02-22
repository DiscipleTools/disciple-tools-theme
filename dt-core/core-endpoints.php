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
    private $public_namespace = "dt-public/dt-core/v1";

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
        register_rest_route(
            $this->public_namespace, '/settings', [
                'methods'  => "GET",
                'callback' => [ $this, 'get_public_settings' ]
            ]
        );

        register_rest_route(
            $this->namespace, '/activity', [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'log_activity' ]
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

    /**
     * Expose settings publicly to world.
     * To not use unless it is for setting that must be accessed
     * before the user is logged in.
     */
    public function get_public_settings(){
        $public_settings = [
            "url" => get_home_url(),
            "login_settings" => [],
        ];
        $public_settings = apply_filters( "dt_core_public_endpoint_settings", $public_settings );
        return $public_settings;
    }


    /**
     * Log activity to the dt_activity_log
     * @param WP_REST_Request $request
     * @return array|WP_Error
     */
    public function log_activity( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( !isset( $params['action'] ) ) {
            return new WP_Error( "activity_param_error", "Please provide a valid array", [ 'status' => 400 ] );
        }

        // Validate user isn't trying to log activity for a different user
        $user = wp_get_current_user();
        if ( isset( $params['user_id'] ) && $params['user_id'] != $user->ID ) {
            return new WP_Error( "activity_param_error", "Cannot log activity for another user", [ 'status' => 400 ] );
        }

        // If logging for a post, validate user has permission
        if ( isset( $params['object_type'] ) && !empty( $params['object_type'] ) ) {
            $type = $params['object_type'];
            $post_types = apply_filters( 'dt_registered_post_types', [ 'contacts', 'groups' ] );
            if ( array_search( $type, $post_types ) !== false ) {
                $post_id = isset( $params['object_id'] ) ? $params['object_id'] : null;

                if ( !empty( $post_id ) ) {
                    $has_permission = DT_Posts::can_update( $type, $post_id );
                } else {
                    $has_permission = DT_Posts::can_access( $type );
                }

                if ( !$has_permission ) {
                    return new WP_Error( __FUNCTION__, "You do not have permission for this", [ 'status' => 403 ] );
                }
            }
        }

        dt_activity_insert( $params );
        return [
            "logged" => true
        ];
    }

}
