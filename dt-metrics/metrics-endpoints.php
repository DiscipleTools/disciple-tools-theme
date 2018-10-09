<?php
/**
 * Custom endpoints file
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Class Disciple_Tools_Statistics_Endpoints
 */
class Disciple_Tools_Metrics_Endpoints {

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    /**
     * API Routes
     */
    public function add_api_routes() {
        $version = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/metrics/critical_path_by_year/(?P<id>[\w-]+)', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'critical_path_by_year' ],
                ],
            ]
        );

    }


    public function critical_path_by_year( WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            if ( $params['id'] == 'all'){
                $start = 0;
                $end = PHP_INT_MAX;
            } else {
                $year = (int) $params['id'];
                $start = DateTime::createFromFormat( "Y-m-d", $year . '-01-01' )->getTimestamp();
                $end = DateTime::createFromFormat( "Y-m-d", ( $year + 1 ) . '-01-01' )->getTimestamp();
            }
            $result = Disciple_Tools_Metrics_Hooks_Base::chart_critical_path( $start, $end );
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( "critical_path_by_year", "Missing a valid contact id", [ 'status' => 400 ] );
        }
    }


    /**
     * Get tract from submitted address
     *
     * @access public
     * @since  0.1.0
     * @return bool|WP_Error The contact on success
     */
    public function refresh_critical_path() {
        delete_transient( 'dt_critical_path' );
        $result = Disciple_Tools_Metrics::chart_critical_path();
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        else {
            return true;
        }
    }
}
