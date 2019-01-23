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
            $this->public_namespace, '/network/trigger_transfer', [
                'methods'  => 'POST',
                'callback' => [ $this, 'trigger_transfer' ],
            ]
        );
        register_rest_route(
            $this->public_namespace, '/network/live_stats', [
                'methods'  => 'POST',
                'callback' => [ $this, 'live_stats' ],
            ]
        );
    }

    public function live_stats( WP_REST_Request $request ) {
        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return [
                'status' => 'FAIL',
                'error' => $params,
            ];
        }

        return Disciple_Tools_Snapshot_Report::snapshot_report();
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function trigger_transfer( WP_REST_Request $request ) {

        $params = $this->process_token( $request );
        if ( is_wp_error( $params ) ) {
            return $params;
        }

        if ( ! isset( $params['type'] ) || ! isset( $params['site_post_id'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameter: type or matching site_post_id.' );
        }

        switch ( $params['type'] ) {

            case 'project_totals':
                return Disciple_Tools_Network::send_project_totals( $params['site_post_id'] );
                break;

            case 'site_profile':
                return Disciple_Tools_Network::send_site_profile( $params['site_post_id'] );
                break;

            case 'site_locations':
                return Disciple_Tools_Network::send_site_locations( $params['site_post_id'] );
                break;

            default:
                return new WP_Error( __METHOD__, 'No trigger type recognized.' );
                break;
        }
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

        $result = Disciple_Tools_Network::api_set_location_attributes( $params['collection'] );
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

        $valid_token = Site_Link_System::verify_transfer_token( $params['transfer_token'] );

        // required valid token challenge
        if ( ! $valid_token ) {
            dt_write_log( $valid_token );
            return new WP_Error( __METHOD__, 'Invalid transfer token' );
        }
        // required permission challenge (that this token comes from an approved network report site link)
        if ( ! current_user_can( 'network_dashboard_transfer' ) ) {
            return new WP_Error( __METHOD__, 'Network report permission error.' );
        }

        // Add post id for site to site link
        $decrypted_key = Site_Link_System::decrypt_transfer_token( $params['transfer_token'] );
        $keys = Site_Link_System::get_site_keys();
        $params['site_post_id'] = $keys[$decrypted_key]['post_id'];

        return $params;
    }

}
Disciple_Tools_Network_Endpoints::instance();