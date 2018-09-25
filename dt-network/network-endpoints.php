<?php
/**
 * Rest Endpoints for the network feature of Disciple Tools
 *
 * @class      Disciple_Tools_Notifications
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


/**
 * Class Disciple_Tools_Network_Endpoints
 */
class Disciple_Tools_Network_Endpoints
{

    private $version = 1;
    private $namespace;

    /**
     * Disciple_Tools_Network_Endpoints The single instance of Disciple_Tools_Network_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Network_Endpoints Instance
     * Ensures only one instance of Disciple_Tools_Network_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Network_Endpoints instance
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
        $this->namespace = "dt/v" . intval( $this->version );
        $this->public_namespace = "dt-public/v" . intval( $this->version );

        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->public_namespace, '/network/report_by_date', [
                'methods'  => 'POST',
                'callback' => [ $this, 'report_by_date' ],
            ]
        );
        register_rest_route(
            $this->public_namespace, '/network/report_project_total', [
                'methods'  => 'POST',
                'callback' => [ $this, 'report_project_total' ],
            ]
        );
        register_rest_route(
            $this->public_namespace, '/network/get_locations', [
                'methods'  => 'POST',
                'callback' => [ $this, 'get_locations' ],
            ]
        );
        register_rest_route(
            $this->public_namespace, '/network/set_location_attributes', [
                'methods'  => 'POST',
                'callback' => [ $this, 'set_location_attributes' ],
            ]
        );
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function report_by_date( WP_REST_Request $request ) {

        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return $params;
        }

        if ( isset( $params['date'] ) ) {
            $result = Disciple_Tools_Network::report_by_date( $params['data'] );
            if ( is_wp_error( $result ) ) {
                return new WP_Error( __METHOD__, $result->get_error_message() );
            }
            return $result;
        } else {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function report_project_total( WP_REST_Request $request ) {

        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return $params;
        }

        $result = Disciple_Tools_Network::report_project_total();
        if ( is_wp_error( $result ) ) {
            return new WP_Error( __METHOD__, $result->get_error_message() );
        }
        return $result;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function get_locations( WP_REST_Request $request ) {

        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return $params;
        }

        if ( ! isset( $params['check_sum'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        $result = Disciple_Tools_Network::get_locations( $params['check_sum'] );
        if ( is_wp_error( $result ) ) {
            return new WP_Error( __METHOD__, $result->get_error_message() );
        }

        return $result;

    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function set_location_attributes( WP_REST_Request $request ) {

        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return $params;
        }

        if ( ! isset( $params['collection'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        $result = Disciple_Tools_Network::set_location_attributes( $params['collection'] );
        if ( is_wp_error( $result ) ) {
            return new WP_Error( __METHOD__, $result->get_error_message() );
        }

        return $result;
    }

    /**
     * Process the standard security checks on an api request to network endpoints.
     * @param \WP_REST_Request $request
     *
     * @return array|\WP_Error
     */
    public function process_token( WP_REST_Request $request ) {

        $params = $request->get_params();

        // required token parameter challenge
        if ( ! isset( $params['transfer_token'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters.' );
        }

        $site_key = Site_Link_System::verify_transfer_token( $params['transfer_token'] );

        // required valid token challenge
        if ( ! $site_key ) {
            dt_write_log( $site_key );
            return new WP_Error( __METHOD__, 'Invalid transfer token' );
        }

        // required permission challenge (that this token comes from an approved network report site link)
        if ( ! user_can( get_current_user_id(), 'network_reports' ) ) {
            return new WP_Error( __METHOD__, 'Network report permission error.' );
        }

        return $params;
    }

}
Disciple_Tools_Network_Endpoints::instance();