<?php
/**
 * Custom endpoints file
 */

/**
 * Class Disciple_Tools_Users_Endpoints
 */
class Disciple_Tools_Users_Endpoints
{

    private $version = 1;
    private $context = "dt";
    private $namespace;

    /**
     * Disciple_Tools_Users_Endpoints constructor.
     */
    public function __construct() {
        $this->namespace = $this->context . "/v" . $this->version;
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * Setup for API routes
     */
    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/users/get_users', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_users' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            $this->namespace, '/users/switch_preference', [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'switch_preference' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            $this->namespace, '/users/app_switch', [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'app_switch' ],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            $this->namespace, '/users/get_filters', [
                'methods' => "GET",
                'callback' => [ $this, 'get_user_filters' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->namespace, '/users/save_filters', [
                'methods' => "POST",
                'callback' => [ $this, 'save_user_filter' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->namespace, '/users/save_filters', [
                'methods' => "DELETE",
                'callback' => [ $this, 'delete_user_filter' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->namespace, '/users/change_password', [
                'methods' => "POST",
                'callback' => [ $this, 'change_password' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->namespace, '/users/create', [
                'methods' => "POST",
                'callback' => [ $this, 'create_user' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->namespace, '/users/contact-id', [
                'methods' => "GET",
                'callback' => [ $this, 'get_user_contact_id' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->namespace, '/users/current_locations', [
                'methods' => "GET",
                'callback' => [ $this, 'get_current_locations' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->namespace, '/users/user_location', [
                'methods' => "POST",
                'callback' => [ $this, 'add_user_location' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->namespace, '/users/user_location', [
                'methods' => "DELETE",
                'callback' => [ $this, 'delete_user_location' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->namespace, '/user/update', [
                'methods' => "POST",
                'callback' => [ $this, 'update_user' ],
                'permission_callback' => '__return_true',
            ]
        );
        register_rest_route(
            $this->namespace, '/user/my', [
                'methods' => "GET",
                'callback' => [ $this, 'get_my_info' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function get_users( WP_REST_Request $request ) {
        $params = $request->get_params();
        $search = "";
        if ( isset( $params['s'] ) ) {
            $search = $params['s'];
        }
        $get_all = 0;
        if ( isset( $params["get_all"] ) ){
            $get_all = $params["get_all"] === "1";
        }
        return Disciple_Tools_Users::get_assignable_users_compact( $search, $get_all );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function switch_preference( WP_REST_Request $request ) {
        $params = $request->get_params();
        $user_id = get_current_user_id();
        if ( isset( $params['preference_key'] ) && $user_id ) {
            $result = Disciple_Tools_Users::switch_preference( $user_id, $params['preference_key'], $params['type'] ?? null );
            if ( $result["status"] ) {
                return $result["response"];
            } else {
                return new WP_Error( "changed_notification_error", $result["message"], [ 'status' => 400 ] );
            }
        } else {
            return new WP_Error( "preference_error", "Please provide a valid preference to change for user", [ 'status' => 400 ] );
        }
    }

    public function app_switch( WP_REST_Request $request ) {
        $params = $request->get_params();
        $user_id = get_current_user_id();
        if ( isset( $params['app_key'] ) && ! empty( $params['app_key'] ) && $user_id ) {
            $result = Disciple_Tools_Users::app_switch( $user_id, $params['app_key'] );
            if ( $result["status"] ) {
                return $result["response"];
            } else {
                return new WP_Error( __METHOD__, $result["message"], [ 'status' => 400 ] );
            }
        } else {
            return new WP_Error( "preference_error", "Please provide a valid preference to change for user", [ 'status' => 400 ] );
        }
    }

    public function get_user_filters( WP_REST_Request $request ){
        $params = $request->get_params();
        $force_refresh = false;
        if ( isset( $params["force_refresh"] ) && !empty( $params["force_refresh"] ) ) {
            $force_refresh = true;
        }
        if ( isset( $params["post_type"] ) ) {
            return Disciple_Tools_Users::get_user_filters( $params["post_type"], $force_refresh );
        }
        return [];
    }

    public function save_user_filter( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params["filter"], $params["post_type"] ) ){
            return Disciple_Tools_Users::save_user_filter( $params["filter"], $params["post_type"] );
        } else {
            return new WP_Error( "missing_error", "Missing filters", [ 'status' => 400 ] );
        }
    }
    public function delete_user_filter( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params["id"], $params["post_type"] ) ) {
            return Disciple_Tools_Users::delete_user_filter( $params["id"], $params["post_type"] );
        } else {
            return new WP_Error( "missing_error", "Missing filters", [ 'status' => 400 ] );
        }
    }

    public function change_password( WP_REST_Request $request ){
        $params = $request->get_params();

        $user_id = get_current_user_id();
        if ( isset( $params["password"] ) && $user_id ){
            dt_write_log( $params["password"] );

            wp_set_password( $params["password"], $user_id );
            wp_logout();
            wp_redirect( '/' );
            return true;
        } else {
            return new WP_Error( "missing_error", "Missing filters", [ 'status' => 400 ] );
        }
    }

    public function create_user( WP_REST_Request $request ){
        $params = $request->get_params();

        if ( isset( $params["user-email"], $params["user-display"] ) ){
            $user_roles = [ "multiplier" ];
            if ( isset( $params["user-user_role"] ) ){
                $user_roles = [ $params["user-user_role"] ];
            }
            if ( isset( $params["user-roles"] ) && !empty( $params["user-roles"] ) ){
                $user_roles =$params["user-roles"];
            }
            $user_login = $params["user-user_login"] ?? $params["user-email"];
            $user_login = $params["user-username"] ?? $user_login;
            if ( isset( $params["user-password"] ) ) {
                $password = $params["user-password"];
            }
            if ( isset( $params["user-optional-fields"] ) ) {
                $optional_fields = $params["user-optional-fields"];
            }
            if ( isset( $params["locale"] ) ) {
                $locale = $params["locale"];
            }
            if ( isset( $params["return_contact"] ) ) {
                $return_contact = true;
            }
            return Disciple_Tools_Users::create_user( $user_login, $params["user-email"], $params["user-display"], $user_roles, $params["corresponds_to_contact"] ?? null, $locale ?? null, $return_contact ?? false, $password, $optional_fields );
        } else {
            return new WP_Error( "missing_error", "Missing fields", [ 'status' => 400 ] );
        }
    }

    public function get_user_contact_id( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params["user_id"] ) ){
            return Disciple_Tools_Users::get_contact_for_user( $params["user_id"] );
        } else {
            return new WP_Error( "missing_error", "Missing fields", [ 'status' => 400 ] );
        }
    }

    public function get_current_locations(){
        return Disciple_Tools_Users::get_user_location( get_current_user_id() );
    }

    /**
     * POST user_location endpoint
     *
     * If no user_id is supplied, then the add request applies to logged in user. If request is made for non-logged in
     * user, then the current user must have be a disciple tools admin.
     *
     * {
            user_id: {user_id},
            user_location: {
                location_grid_meta: [
                {
                    grid_meta_id: {grid_meta_id},
                }
            ]
            }
        }
     *
     * {
        user_id: {user_id},
        user_location: {
            location_grid_meta: [
                {
                    lng: {lng},
                    lat: {lat},
                    level: {level},
                    label: {label},
                    source: 'user'
                }
            ]}
        }
     *
     *
     * @param WP_REST_Request $request
     * @return array|bool|WP_Error
     */
    public function add_user_location( WP_REST_Request $request ) {
        $params = $request->get_params();

        // mapbox add
        if ( isset( $params['user_location']['location_grid_meta'] ) ) {

            // only dt admin caps can add locations for other users
            $user_id = get_current_user_id();
            if ( isset( $params['user_id'] ) && ! empty( $params['user_id'] ) && (int) $params['user_id'] !== $user_id ) {
                if ( user_can( $user_id, 'manage_dt' ) ) { // if user_id param is set, you must be able to edit users.
                    $user_id = sanitize_text_field( wp_unslash( $params['user_id'] ) );
                } else {
                    return new WP_Error( __METHOD__, "No permission to edit this user", [ 'status' => 400 ] );
                }
            }

            $new_location_grid_meta = [];
            foreach ( $params['user_location']['location_grid_meta'] as $grid_meta ) {
                $new_location_grid_meta[] = Disciple_Tools_Users::add_user_location_meta( $grid_meta, $user_id );
            }

            if ( ! empty( $new_location_grid_meta ) ) {
                return [
                    'user_id' => $user_id,
                    'user_title' => dt_get_user_display_name( $user_id ),
                    'user_location' => Disciple_Tools_Users::get_user_location( $user_id )
                ];
            }
            return new WP_Error( __METHOD__, 'Failed to create user location' );
        }

        // typeahead add
        else if ( isset( $params["grid_id"] ) ){
            return Disciple_Tools_Users::add_user_location( $params["grid_id"] );
        }

        // parameter fail
        else {
            return new WP_Error( "missing_error", "Missing fields", [ 'status' => 400 ] );
        }
    }

    public function delete_user_location( WP_REST_Request $request ) {
        $params = $request->get_params();

        // mapbox add
        if ( isset( $params['user_location']['location_grid_meta'] ) ) {

            // only dt admin caps can add locations for other users
            $user_id = get_current_user_id();
            if ( isset( $params['user_id'] ) && ! empty( $params['user_id'] ) && (int) $params['user_id'] !== $user_id ) {
                // if user_id param is set, you must be able to edit users.
                if ( user_can( $user_id, 'manage_dt' ) ) {
                    $user_id = sanitize_text_field( wp_unslash( $params['user_id'] ) );
                } else {
                    return new WP_Error( __METHOD__, "No permission to edit this user", [ 'status' => 400 ] );
                }
            }

            $new_location_grid_meta = [];
            foreach ( $params['user_location']['location_grid_meta'] as $grid_meta ) {
                if ( isset( $grid_meta['grid_meta_id'] ) ) {
                    $new_location_grid_meta[] = Disciple_Tools_Users::delete_user_location_meta( $params['user_location']['location_grid_meta'][0]['grid_meta_id'], $user_id );
                }
            }

            if ( ! empty( $new_location_grid_meta ) ) {
                return [
                    'user_id' => $user_id,
                    'user_location' => Disciple_Tools_Users::get_user_location( $user_id )
                ];
            }
            return new WP_Error( __METHOD__, 'Failed to delete user location' );
        }
        // typeahead add
        else if ( isset( $params["grid_id"] ) ){
            return Disciple_Tools_Users::delete_user_location( $params["grid_id"] );
        }
        else {
            return new WP_Error( "missing_error", "Missing fields", [ 'status' => 400 ] );
        }
    }

    public function update_user( WP_REST_Request $request ){
        $body = $request->get_json_params() ?? $request->get_body_params();
        $user = wp_get_current_user();
        if ( !$user ) {
            return new WP_Error( "update_user", "Something went wrong. Are you a user?", [ 'status' => 400 ] );
        }
        return Disciple_Tools_Users::update_settings_on_user( $user->ID, $body );
    }


    public function get_my_info( WP_REST_Request $request ){
        $user = wp_get_current_user();
        if ( $user ){
            return [
                "ID" => $user->ID,
                "user_email" => $user->user_email,
                "display_name" => $user->display_name,
                "locale" => get_user_locale( $user->ID ),
                "locations" => self::get_current_locations(),
            ];
        } else {
            return new WP_Error( "get_my_info", "Something went wrong. Are you a user?", [ 'status' => 400 ] );
        }
    }
}
