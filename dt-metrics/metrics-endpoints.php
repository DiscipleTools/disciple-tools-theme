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
    /**
     * Disciple_Tools_Metrics_Endpoints The single instance of Disciple_Tools_Metrics_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Metrics_Endpoints Instance
     * Ensures only one instance of Disciple_Tools_Metrics_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Metrics_Endpoints instance
     */
    public static function instance()
    {
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
    public function __construct()
    {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    /**
     * API Routes
     */
    public function add_api_routes()
    {
        $version = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/metrics/critical_path_prayer', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'critical_path_prayer' ],
                ],
            ]
        );

        register_rest_route(
            $namespace, '/metrics/critical_path_outreach', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'critical_path_outreach' ],
                ],
            ]
        );

        register_rest_route(
            $namespace, '/metrics/critical_path_fup', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'critical_path_fup' ],
                ],
            ]
        );

        register_rest_route(
            $namespace, '/metrics/critical_path_multiplication', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'critical_path_multiplication' ],
                ],
            ]
        );

        register_rest_route(
            $namespace, '/metrics/critical_path_chart_data', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'critical_path_chart_data' ],
                ],
            ]
        );

        register_rest_route(
            $namespace, '/metrics/refresh_critical_path', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'refresh_critical_path' ],
                ],
            ]
        );

    }

    /**
     * Get tract from submitted address
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function critical_path_chart_data()
    {
        $result = Disciple_Tools_Metrics::critical_path_chart_data( true );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        elseif ( $result["status"] ) {
            return $result['data'];
        }
        else {
            return new WP_Error( "critical_path_processing_error", $result["message"], [ 'status' => 400 ] );
        }
    }

    /**
     * Get tract from submitted address
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function critical_path_prayer()
    {
        $result = Disciple_Tools_Metrics::critical_path_prayer( true );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        elseif ( $result["status"] ) {
            return $result['data'];
        }
        else {
            return new WP_Error( "critical_path_processing_error", $result["message"], [ 'status' => 400 ] );
        }
    }

    /**
     * Get tract from submitted address
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function critical_path_outreach()
    {
        $result = Disciple_Tools_Metrics::critical_path_outreach( true );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        elseif ( $result["status"] ) {
            return $result['data'];
        }
        else {
            return new WP_Error( "critical_path_processing_error", $result["message"], [ 'status' => 400 ] );
        }
    }

    /**
     * Get tract from submitted address
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function critical_path_fup()
    {
        $result = Disciple_Tools_Metrics::critical_path_fup( true );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        elseif ( $result["status"] ) {
            return $result['data'];
        }
        else {
            return new WP_Error( "critical_path_processing_error", $result["message"], [ 'status' => 400 ] );
        }
    }

    /**
     * Get tract from submitted address
     *
     * @access public
     * @since  0.1.0
     * @return string|WP_Error|array The contact on success
     */
    public function critical_path_multiplication()
    {
        $result = Disciple_Tools_Metrics::critical_path_multiplication( true );
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        elseif ( $result["status"] ) {
            return $result['data'];
        }
        else {
            return new WP_Error( "critical_path_processing_error", $result["message"], [ 'status' => 400 ] );
        }
    }

    /**
     * Get tract from submitted address
     *
     * @access public
     * @since  0.1.0
     * @return bool|WP_Error The contact on success
     */
    public function refresh_critical_path()
    {
        delete_transient( 'dt_critical_path' );
        $result = Disciple_Tools_Metrics::get_critical_path();
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        else {
            return true;
        }
    }


}
