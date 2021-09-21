<?php

/**
 * Section getting metrics data
 * For users to see their own stats
 * For admin to view user stats
 */
class DT_User_Metrics {

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }


    public function add_api_routes() {
        $can_view_user_stats = function ( WP_REST_Request $request ){
            $params = $request->get_params();
            $user_id = get_current_user_id();
            if ( isset( $params["user_id"] ) && !empty( $params["user_id"] ) ){
                $user_id = (int) $params["user_id"];
            }
            return Disciple_Tools_Users::can_view( $user_id );
        };

        $namespace = 'dt-users/v1';
        register_rest_route(
            $namespace, 'activity', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_activity' ],
                'permission_callback' => $can_view_user_stats
            ]
        );
    }

    public function get_activity( WP_REST_Request $request ){
        return [];
    }

}
