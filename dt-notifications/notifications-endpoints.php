<?php

/**
 * Disciple_Tools_Notifications_Endpoints
 *
 * @class      Disciple_Tools_Notifications_Endpoints
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Disciple.Tools
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Notifications_Endpoints
 */
class Disciple_Tools_Notifications_Endpoints
{
    /**
     * Disciple_Tools_Notifications_Endpoints The single instance of Disciple_Tools_Notifications_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Notifications_Endpoints Instance
     * Ensures only one instance of Disciple_Tools_Notifications_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Notifications_Endpoints instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        $version = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/notifications/mark_viewed/(?P<notification_id>\d+)', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'mark_viewed' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

        register_rest_route(
            $namespace, '/notifications/mark_all_viewed/(?P<user_id>\d+)', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'mark_all_viewed' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

        register_rest_route(
            $namespace, '/notifications/get_notifications', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_notifications' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

        register_rest_route(
            $namespace, '/notifications/get_new_notifications_count', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_new_notifications_count' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

        register_rest_route(
            $namespace, '/notifications/mark_unread/(?P<notification_id>\d+)', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'mark_unread' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

    }

    /**
     * Get tract from submitted address
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function mark_viewed( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['notification_id'] ) ) {
            $result = Disciple_Tools_Notifications::mark_viewed( $params['notification_id'] );
            if ( $result["status"] ) {
                return $result['rows_affected'];
            } else {
                return new WP_Error( "mark_viewed_processing_error", $result["message"], [ 'status' => 400 ] );
            }
        } else {
            return new WP_Error( "notification_param_error", "Please provide a valid array", [ 'status' => 400 ] );
        }
    }

    /**
     * Mark all viewed
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function mark_all_viewed( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['user_id'] ) && $params['user_id'] == get_current_user_id() ) {
            $result = Disciple_Tools_Notifications::mark_all_viewed( $params['user_id'] );
            if ( $result["status"] ) {
                return $result['rows_affected'];
            } else {
                return new WP_Error( "mark_viewed_processing_error", $result["message"], [ 'status' => 400 ] );
            }
        } else {
            return new WP_Error( "notification_param_error", "Please provide a valid array", [ 'status' => 400 ] );
        }
    }

    /**
     * Get notifications
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function get_notifications( WP_REST_Request $request ) {
        $params = $request->get_params();
        $all = isset( $params['all'] ) ? $params['all'] : false;
        if ( isset( $params['page'] ) && isset( $params['limit'] ) ) {
            $result = Disciple_Tools_Notifications::get_notifications( $all, $params['page'], $params['limit'] );
            if ( !is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_Error( "get_user_notification_results", 'Something went wrong', [ 'status' => 400 ] );
            }
        } else {
            return new WP_Error( "notification_param_error", "Please set the page and limit query parameters", [ 'status' => 400 ] );
        }
    }

    /**
     * Get tract from submitted address
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function get_new_notifications_count() {
        $result = Disciple_Tools_Notifications::get_new_notifications_count();
        if ( !is_wp_error( $result ) ){
            return $result;
        } else {
            return new WP_Error( "get_user_notification_results", "Something went wrong", [ 'status' => 400 ] );
        }
    }

    /**
     * Get tract from submitted address
     *
     * @param  WP_REST_Request $request
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function mark_unread( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['notification_id'] ) ) {
            $result = Disciple_Tools_Notifications::mark_unread( $params['notification_id'] );
            if ( $result["status"] ) {
                return $result['rows_affected'];
            } else {
                return new WP_Error( "mark_viewed_processing_error", $result["message"], [ 'status' => 400 ] );
            }
        } else {
            return new WP_Error( "notification_param_error", "Please provide a valid array", [ 'status' => 400 ] );
        }
    }

}
