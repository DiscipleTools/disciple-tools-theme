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
                'callback' => [ $this, 'get_settings' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->public_namespace, '/settings', [
                'methods'  => "GET",
                'callback' => [ $this, 'get_public_settings' ],
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

        register_rest_route(
            $this->public_namespace, '/get-post-fields', [
                'methods' => 'POST',
                'callback' => [ $this, 'get_post_fields' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            $this->namespace, '/create-new-tile', [
                'methods' => 'POST',
                'callback' => [ $this, 'create_new_tile' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * These are settings available to any logged in user.
     */
    public static function get_settings() {
        $user = wp_get_current_user();
        if ( !$user ){
            return new WP_Error( "get_settings", "Something went wrong. Are you a user?", [ 'status' => 400 ] );
        }
        $available_translations = dt_get_available_languages();
        $post_types = DT_Posts::get_post_types();
        $post_types_settings = [];
        foreach ( $post_types as $post_type ){
            $post_types_settings[$post_type] = DT_Posts::get_post_settings( $post_type );
        }
        return [
            "available_translations" => $available_translations,
            "post_types" => $post_types_settings,
            'plugins' => apply_filters( 'dt_plugins', [] ),
        ];
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

    public static function get_post_fields() {
        $output = [];
        $post_types = DT_Posts::get_post_types();

        foreach ( $post_types as $post_type ) {
            $post_label = DT_Posts::get_label_for_post_type( $post_type );
            $output[] = [
                'label' => $post_label,
                'post_type' => $post_type,
                'post_tile' => null,
                'post_setting' => null,
            ];

            $post_tiles = DT_Posts::get_post_tiles( $post_type );
            foreach ( $post_tiles as $tile_key => $tile_value ) {
                $output[] = [
                    'label' => $post_label . ' > ' . $tile_value['label'],
                    'post_type' => $post_type,
                    'post_tile' => $tile_key,
                    'post_setting' => null,
                ];

                $post_settings = DT_Posts::get_post_settings( $post_type, false );
                foreach ( $post_settings['fields'] as $setting_key => $setting_value ) {
                    if ( $setting_value['tile'] === $tile_key ) {
                        $output[] = [
                            'label' => $post_label . ' > ' . $tile_value['label'] . ' > ' . $setting_value['name'],
                            'post_type' => $post_type,
                            'post_tile' => $tile_key,
                            'post_setting' => $setting_key,
                        ];
                    }
                }
            }
        }
        header( 'Content-Type" => application/json' );
        echo json_encode( $output );
    }

    public static function create_new_tile( WP_REST_Request $request ) {
        $post_submission = $request->get_params();

        if ( isset( $post_submission["new_tile_name"], $post_submission["post_type"] ) ) {
            $post_type = sanitize_text_field( wp_unslash( $post_submission["post_type"] ) );
            $new_tile_name = sanitize_text_field( wp_unslash( $post_submission["new_tile_name"] ) );
            $tile_options = dt_get_option( "dt_custom_tiles" );
            $post_tiles = DT_Posts::get_post_tiles( $post_type );
            $tile_key = dt_create_field_key( $new_tile_name );
            if ( in_array( $tile_key, array_keys( $post_tiles ) ) ){
                Disciple_Tools_Customizations_Tab::admin_notice( __( "tile already exists", 'disciple_tools' ), "error" );
                return false;
            }
            if ( !isset( $tile_options[$post_type] ) ){
                $tile_options[$post_type] = [];
            }
            $tile_options[$post_type][$tile_key] = [ "label" => $new_tile_name ];

            update_option( "dt_custom_tiles", $tile_options );
            $created_tile = [
                'post_type' => $post_type,
                'key' => $tile_key,
                'label' => $new_tile_name,
            ];
            return $created_tile;
        }
        return false;
    }
}
