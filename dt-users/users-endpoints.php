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
    public function __construct()
    {
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * Setup for API routes
     */
    public function add_api_routes()
    {
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
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|\WP_Error
     */
    public function get_users( WP_REST_Request $request )
    {
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
    public function switch_preference( WP_REST_Request $request )
    {
        $params = $request->get_params();
        $user_id = get_current_user_id();
        if ( isset( $params['preference_key'] ) ) {
            $result = Disciple_Tools_Users::switch_preference( $user_id, $params['preference_key'] );
            if ( $result["status"] ) {
                return $result["response"];
            } else {
                return new WP_Error( "changed_notification_error", $result["message"], [ 'status', 400 ] );
            }
        } else {
            return new WP_Error( "preference_error", "Please provide a valid preference to change for user", [ 'status', 400 ] );
        }
    }

}
