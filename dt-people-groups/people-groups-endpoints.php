<?php
/**
 * Custom endpoints file
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class Disciple_Tools_People_Groups_Endpoints
 */
class Disciple_Tools_People_Groups_Endpoints
{

    private $version = 1;
    private $context = "dt";
    private $namespace;

    /**
     * Disciple_Tools_People_Groups_Endpoints The single instance of Disciple_Tools_People_Groups_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_People_Groups_Endpoints Instance
     * Ensures only one instance of Disciple_Tools_People_Groups_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_People_Groups_Endpoints instance
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
        $this->namespace = $this->context . "/v" . intval( $this->version );
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes()
    {
        register_rest_route(
            $this->namespace, '/people-groups-compact', [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_people_groups_compact' ],
            ]
        );
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array
     */
    public function get_people_groups_compact( WP_REST_Request $request )
    {

        $params = $request->get_params();
        $search = "";
        if ( isset( $params['s'] ) ) {
            $search = $params['s'];
        }
        $people_groups = Disciple_Tools_people_groups::get_people_groups_compact( $search );

        return $people_groups;
    }
}
