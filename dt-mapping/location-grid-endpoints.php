<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class Location_Grid_Endpoints
 */
class Location_Grid_Endpoints // @todo placeholder
{

    private $version = 1;
    private $context = "dt";
    private $namespace;

    /**
     * Location_Grid_Endpoints The single instance of Location_Grid_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main Location_Grid_Endpoints Instance
     * Ensures only one instance of Location_Grid_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Location_Grid_Endpoints instance
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
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/location-grid/geocode', [
                'methods'  => 'GET',
                'callback' => [ $this, 'geocode' ],
            ]
        );
    }

    public function geocode( WP_REST_Request $request ) {

        $params = $request->get_params();
//        if ( isset( $params['rop3'] ) && isset( $params['country'] ) && isset( $params['post_id'] ) ) {
//            $result = Disciple_Tools_People_Groups::link_or_update( $params['rop3'], $params['country'], $params['post_id'] );
//            return $result;
//        } else {
//            return new WP_Error( __METHOD__, 'Missing required parameter rop3 or country' );
//        }
    }
}
