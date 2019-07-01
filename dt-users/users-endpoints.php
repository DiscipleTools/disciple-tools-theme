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
        $this->namespace = $this->context . "/v" . intval( $this->version );
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
            ]
        );

        register_rest_route(
            $this->namespace, '/users/switch_preference', [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'switch_preference' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/users/get_filters', [
                'methods' => "GET",
                'callback' => [ $this, 'get_user_filters' ]
            ]
        );
        register_rest_route(
            $this->namespace, '/users/save_filters', [
                'methods' => "POST",
                'callback' => [ $this, 'save_user_filters' ]
            ]
        );
        register_rest_route(
            $this->namespace, '/users/change_password', [
                'methods' => "POST",
                'callback' => [ $this, 'change_password' ]
            ]
        );
        register_rest_route(
            $this->namespace, '/users/disable_product_tour', [
                'methods' => "GET",
                'callback' => [ $this, 'disable_product_tour' ]
            ]
        );
        register_rest_route(
            $this->namespace, '/users/create', [
                'methods' => "POST",
                'callback' => [ $this, 'create_user' ]
            ]
        );
        register_rest_route(
            $this->namespace, '/users/contact-id', [
                'methods' => "GET",
                'callback' => [ $this, 'get_user_contact_id' ]
            ]
        );
        register_rest_route(
            $this->namespace, '/users/current_locations', [
                'methods' => "GET",
                'callback' => [ $this, 'get_current_locations' ]
            ]
        );
        register_rest_route(
            $this->namespace, '/users/user_location', [
                'methods' => "POST",
                'callback' => [ $this, 'add_user_location' ]
            ]
        );
        register_rest_route(
            $this->namespace, '/users/user_location', [
                'methods' => "DELETE",
                'callback' => [ $this, 'delete_user_location' ]
            ]
        );
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|\WP_Error
     */
    public function get_users( WP_REST_Request $request ) {
        $params = $request->get_params();
        $search = "";
        if ( isset( $params['s'] ) ) {
            $search = $params['s'];
        }
        $users = Disciple_Tools_Users::get_assignable_users_compact( $search );

        return $users;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|\WP_Error
     */
    public function switch_preference( WP_REST_Request $request ) {
        $params = $request->get_params();
        $user_id = get_current_user_id();
        if ( isset( $params['preference_key'] ) && $user_id ) {
            $result = Disciple_Tools_Users::switch_preference( $user_id, $params['preference_key'], $params['type'] ?? null );
            if ( $result["status"] ) {
                return $result["response"];
            } else {
                return new WP_Error( "changed_notification_error", $result["message"], [ 'status', 400 ] );
            }
        } else {
            return new WP_Error( "preference_error", "Please provide a valid preference to change for user", [ 'status', 400 ] );
        }
    }


    public function get_user_filters( WP_REST_Request $request ){
        return Disciple_Tools_Users::get_user_filters();
    }

    public function save_user_filters( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params["filters"] )){
            return Disciple_Tools_Users::save_user_filters( $params["filters"] );
        } else {
            return new WP_Error( "missing_error", "Missing filters", [ 'status', 400 ] );
        }
    }

    public function change_password( WP_REST_Request $request ){
        $params = $request->get_params();

        $user_id = get_current_user_id();
        if ( isset( $params["password"] ) && $user_id){
            dt_write_log( $params["password"] );

            wp_set_password( $params["password"], $user_id );
            wp_logout();
            wp_redirect( '/' );
            return true;
        } else {
            return new WP_Error( "missing_error", "Missing filters", [ 'status', 400 ] );
        }
    }


    public function disable_product_tour(){
        return update_user_meta( get_current_user_id(), 'dt_product_tour', true );
    }


    public function create_user( WP_REST_Request $request ){
        $params = $request->get_params();

        if ( isset( $params["user-email"], $params["user-display"], $params["corresponds_to_contact"] ) ){
            return Disciple_Tools_Users::create_user( $params["user-email"], $params["user-email"], $params["user-display"], $params["corresponds_to_contact"] );
        } else {
            return new WP_Error( "missing_error", "Missing fields", [ 'status', 400 ] );
        }
    }

    public function get_user_contact_id( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params["user_id"] ) ){
            return Disciple_Tools_Users::get_contact_for_user( $params["user_id"] );
        } else {
            return new WP_Error( "missing_error", "Missing fields", [ 'status', 400 ] );
        }
    }

    public function get_current_locations(){
        return DT_Mapping_Module::instance()->get_post_locations( dt_get_associated_user_id( get_current_user_id(), 'user' ) );
    }

    public function add_user_location( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params["grid_id"] ) ){
            return Disciple_Tools_Users::add_user_location( $params["grid_id"] );
        } else {
            return new WP_Error( "missing_error", "Missing fields", [ 'status', 400 ] );
        }
    }

    public function delete_user_location( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params["grid_id"] ) ){
            return Disciple_Tools_Users::delete_user_location( $params["grid_id"] );
        } else {
            return new WP_Error( "missing_error", "Missing fields", [ 'status', 400 ] );
        }
    }

}
