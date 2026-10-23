<?php
/**
 * Custom endpoints file
 */

/**
 * Class Disciple_Tools_Users_Endpoints
 */
class Disciple_Tools_Core_Endpoints {

    private $version = 1;
    private $context = 'dt-core';
    private $namespace;
    private $public_namespace = 'dt-public/dt-core/v1';

    /**
     * Disciple_Tools_Users_Endpoints constructor.
     */
    public function __construct() {
        $this->namespace = $this->context . '/v' . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * Setup for API routes
     */
    public function add_api_routes() {
        register_rest_route(
            $this->public_namespace, '/settings', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_public_settings' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            $this->namespace, '/settings', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_settings' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            $this->namespace, '/activity', [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'log_activity' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * These are settings available to any logged in user.
     */
    public static function get_settings() {
        $user = wp_get_current_user();
        if ( !$user->exists() ){
            return new WP_Error( 'get_settings', 'Something went wrong. Are you a user?', [ 'status' => 401 ] );
        }
        $available_translations = dt_get_available_languages();
        $post_types = DT_Posts::get_post_types();
        $post_types_settings = [];
        foreach ( $post_types as $post_type ){
            $post_types_settings[$post_type] = DT_Posts::get_post_settings( $post_type, false, true );
        }
        return [
            'available_translations' => $available_translations,
            'post_types' => $post_types_settings,
            'plugins' => apply_filters( 'dt_plugins', [] ),
            'mapping' => [
                'mapbox_api_key' => DT_Mapbox_API::get_key(),
                'google_api_key' => Disciple_Tools_Google_Geocode_API::get_key(),
            ]
        ];
    }

    /**
     * Expose settings publicly to world.
     * To not use unless it is for setting that must be accessed
     * before the user is logged in.
     */
    public function get_public_settings(){
        $public_settings = [
            'url' => get_home_url(),
            'login_settings' => [],
        ];
        $public_settings = apply_filters( 'dt_core_public_endpoint_settings', $public_settings );
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
            return new WP_Error( 'activity_param_error', 'Please provide a valid array', [ 'status' => 400 ] );
        }

        if ( !is_user_logged_in() ) {
            return new WP_Error( __FUNCTION__, 'You must be logged in', [ 'status' => 401 ] );
        }

        // Validate user isn't trying to log activity for a different user
        $user = wp_get_current_user();
        if ( isset( $params['user_id'] ) && $params['user_id'] != $user->ID ) {
            return new WP_Error( 'activity_param_error', 'Cannot log activity for another user', [ 'status' => 400 ] );
        }

        $allowed_fields = [ 'action', 'object_type', 'object_subtype', 'object_name', 'object_id', 'object_note', 'meta_key', 'meta_value', 'old_value', 'field_type' ];
        $activity = array_intersect_key( $params, array_flip( $allowed_fields ) );
        $activity['user_id'] = $user->ID;

        // If logging for a post, validate user has permission
        if ( !empty( $activity['object_type'] ) ) {
            $type = is_string( $activity['object_type'] ) ? strtolower( trim( $activity['object_type'] ) ) : '';
            if ( !in_array( $type, DT_Posts::get_post_types(), true ) ) {
                return new WP_Error( 'activity_param_error', 'Invalid object_type', [ 'status' => 400 ] );
            }
            $activity['object_type'] = $type;
            $post_id = isset( $activity['object_id'] ) ? (int) $activity['object_id'] : 0;

            if ( !empty( $post_id ) ) {
                $has_permission = DT_Posts::can_update( $type, $post_id );
            } else {
                $has_permission = DT_Posts::can_access( $type );
            }

            if ( !$has_permission ) {
                return new WP_Error( __FUNCTION__, 'You do not have permission for this', [ 'status' => 403 ] );
            }
            $activity['object_id'] = $post_id;
        } else {
            unset( $activity['object_id'] );
        }

        dt_activity_insert( $activity );
        return [
            'logged' => true
        ];
    }
}
