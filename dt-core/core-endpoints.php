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
            $this->namespace, '/settings', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_settings' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->public_namespace, '/settings', [
                'methods'  => 'GET',
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
            $this->namespace, '/plugin-install', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_install' ],
                'permission_callback' => [ $this, 'plugin_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/plugin-delete', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_delete' ],
                'permission_callback' => [ $this, 'plugin_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/plugin-activate', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_activate' ],
                'permission_callback' => [ $this, 'plugin_permission_check' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/plugin-deactivate', [
                'methods'  => 'POST',
                'callback' => [ $this, 'plugin_deactivate' ],
                'permission_callback' => [ $this, 'plugin_permission_check' ],
            ]
        );
    }


    /**
     * These are settings available to any logged in user.
     */
    public static function get_settings() {
        $user = wp_get_current_user();
        if ( !$user ){
            return new WP_Error( 'get_settings', 'Something went wrong. Are you a user?', [ 'status' => 400 ] );
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

        // Validate user isn't trying to log activity for a different user
        $user = wp_get_current_user();
        if ( isset( $params['user_id'] ) && $params['user_id'] != $user->ID ) {
            return new WP_Error( 'activity_param_error', 'Cannot log activity for another user', [ 'status' => 400 ] );
        }

        // If logging for a post, validate user has permission
        if ( isset( $params['object_type'] ) && !empty( $params['object_type'] ) ) {
            $type = $params['object_type'];
            $post_types = DT_Posts::get_post_types();
            if ( array_search( $type, $post_types ) !== false ) {
                $post_id = isset( $params['object_id'] ) ? $params['object_id'] : null;

                if ( !empty( $post_id ) ) {
                    $has_permission = DT_Posts::can_update( $type, $post_id );
                } else {
                    $has_permission = DT_Posts::can_access( $type );
                }

                if ( !$has_permission ) {
                    return new WP_Error( __FUNCTION__, 'You do not have permission for this', [ 'status' => 403 ] );
                }
            }
        }

        dt_activity_insert( $params );
        return [
            'logged' => true
        ];
    }

    public function plugin_install( WP_REST_Request $request ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        $params = $request->get_params();
        $download_url = sanitize_text_field( wp_unslash( $params['download_url'] ) );
        set_time_limit( 0 );
        $folder_name = explode( '/', $download_url );
        $folder_name = get_home_path() . 'wp-content/plugins/' . $folder_name[4] . '.zip';
        if ( $folder_name != '' ) {
            //download the zip file to plugins
            file_put_contents( $folder_name, file_get_contents( $download_url ) );
            // get the absolute path to $file
            $folder_name = realpath( $folder_name );
            //unzip
            WP_Filesystem();
            $unzip = unzip_file( $folder_name, realpath( get_home_path() . 'wp-content/plugins/' ) );
            //remove the file
            unlink( $folder_name );
        }
        return true;
    }

    public function plugin_permission_check() {
        if ( ! current_user_can( 'manage_dt' ) ) {
            return new WP_Error( 'forbidden', 'You are not allowed to do that.', array( 'status' => 403 ) );
        }
        return true;
    }

    public function plugin_delete( WP_REST_Request $request ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $params = $request->get_params();
        $plugin_slug = sanitize_text_field( wp_unslash( $params['plugin_slug'] ) );
        $installed_plugins = get_plugins();
        foreach ( $installed_plugins as $index => $plugin ) {
            if ( $plugin['TextDomain'] === $plugin_slug ) {
                delete_plugins( [ $index ] );
                return true;
            }
        }
        return false;
    }

    public function plugin_activate( WP_REST_Request $request ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $params = $request->get_params();
        $plugin_slug = sanitize_text_field( wp_unslash( $params['plugin_slug'] ) );
        $installed_plugins = get_plugins();
        foreach ( $installed_plugins as $index => $plugin ) {
            if ( $plugin['TextDomain'] === $plugin_slug ) {
                activate_plugin( $index );
                return true;
            }
        }
        return false;
    }

    public function plugin_deactivate( WP_REST_Request $request ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $params = $request->get_params();
        $plugin_slug = sanitize_text_field( wp_unslash( $params['plugin_slug'] ) );
        $installed_plugins = get_plugins();
        foreach ( $installed_plugins as $index => $plugin ) {
            if ( $plugin['TextDomain'] === $plugin_slug ) {
                deactivate_plugins( $index );
                return true;
            }
        }
        return false;
    }
}
