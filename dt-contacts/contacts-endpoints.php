<?php
/**
 * Rest API example class
 */


class DT_Contacts_Endpoints {
    public $permissions = [ 'view_any_seekers', 'view_project_metrics' ];

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }


    //See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
    public function add_api_routes() {
        $namespace = 'dt-sample/v1';

        register_rest_route(
            $namespace, '/endpoint', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'private_endpoint' ],
                ],
            ]
        );
    }


    public function endpoint( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( "private_endpoint", "Missing Permissions", [ 'status' => 400 ] );
        }

        // run your function here

        return true;
    }
}
