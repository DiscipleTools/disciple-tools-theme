<?php
/**
 * Custom endpoints file
 *
 * @package  Disciple.Tools
 * @category Plugin
 * @author   Disciple.Tools
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
    private $context = 'dt';
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
        $this->namespace = $this->context . '/v' . intval( $this->version );
        add_action( 'rest_api_init', array( $this, 'add_api_routes' ) );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/people-groups/compact', array(
                'methods'  => 'GET',
                'callback' => array( $this, 'get_people_groups_compact' ),
                'permission_callback' => '__return_true',
            )
        );
        register_rest_route(
            $this->namespace, '/people-groups/search_csv', array(
                'methods'  => 'POST',
                'callback' => array( $this, 'search_csv' ),
                'permission_callback' => '__return_true',
            )
        );
        register_rest_route(
            $this->namespace, '/people-groups/search_csv_by_rop3', array(
                'methods'  => 'POST',
                'callback' => array( $this, 'search_csv_by_rop3' ),
                'permission_callback' => '__return_true',
            )
        );
        register_rest_route(
            $this->namespace, '/people-groups/add_single_people_group', array(
                'methods'  => 'POST',
                'callback' => array( $this, 'add_single_people_group' ),
                'permission_callback' => '__return_true',
            )
        );
        register_rest_route(
            $this->namespace, '/people-groups/add_bulk_people_groups', array(
                'methods'  => 'POST',
                'callback' => array( $this, 'add_bulk_people_groups' ),
                'permission_callback' => '__return_true',
            )
        );
        register_rest_route(
            $this->namespace, '/people-groups/get_bulk_people_groups_import_batches', array(
                'methods'  => 'GET',
                'callback' => array( $this, 'get_bulk_people_groups_import_batches' ),
                'permission_callback' => '__return_true',
            )
        );
        register_rest_route(
            $this->namespace, '/people-groups/link_or_update', array(
                'methods'  => 'POST',
                'callback' => array( $this, 'link_or_update' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array
     */
    public function get_people_groups_compact( WP_REST_Request $request ) {

        $params = $request->get_params();
        $search = '';
        if ( isset( $params['s'] ) ) {
            $search = $params['s'];
        }
        $people_groups = Disciple_Tools_People_Groups::get_people_groups_compact( $search );

        return $people_groups;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function search_csv( WP_REST_Request $request ) {

        $params = $request->get_params();
        if ( isset( $params['s'] ) ) {
            $people_groups = Disciple_Tools_People_Groups::search_csv( $params['s'] );
            return $people_groups;
        } else {
            return new WP_Error( __METHOD__, 'Missing required parameter `s`' );
        }
    }

    public function search_csv_by_rop3( WP_REST_Request $request ) {

        $params = $request->get_params();
        if ( isset( $params['rop3'] ) ) {
            $people_groups = Disciple_Tools_People_Groups::search_csv_by_rop3( $params['rop3'] );
            return $people_groups;
        } else {
            return new WP_Error( __METHOD__, 'Missing required parameter `rop3`' );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function add_single_people_group( WP_REST_Request $request ) {

        if ( ! current_user_can( 'manage_dt' ) ) {
            return new WP_Error( __METHOD__, 'You do not have permission to add people groups', array() );
        }

        $params = $request->get_params();
        if ( isset( $params['rop3'], $params['country'], $params['location_grid'] ) ) {
            return Disciple_Tools_People_Groups::add_single_people_group( $params['rop3'], $params['country'], $params['location_grid'] );
        } else {
            return new WP_Error( __METHOD__, 'Missing required parameters: rop3 or country or location_grid' );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function add_bulk_people_groups( WP_REST_Request $request ){
        if ( !current_user_can( 'manage_dt' ) ){
            return new WP_Error( __METHOD__, 'You do not have permission to add people groups', array() );
        }

        $params = $request->get_params();
        if ( isset( $params['groups'] ) ){
            return Disciple_Tools_People_Groups::add_bulk_people_groups( $params['groups'] );
        } else {
            return new WP_Error( __METHOD__, 'Missing required parameter: groups' );
        }
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function get_bulk_people_groups_import_batches( WP_REST_Request $request ){
        if ( !current_user_can( 'manage_dt' ) ){
            return new WP_Error( __METHOD__, 'You do not have permission to import people groups', array() );
        }

        return Disciple_Tools_People_Groups::get_bulk_people_groups_import_batches();
    }

    public function link_or_update( WP_REST_Request $request ) {

        $params = $request->get_params();
        if ( isset( $params['rop3'] ) && isset( $params['country'] ) && isset( $params['post_id'] ) ) {
            $result = Disciple_Tools_People_Groups::link_or_update( $params['rop3'], $params['country'], $params['post_id'] );
            return $result;
        } else {
            return new WP_Error( __METHOD__, 'Missing required parameter rop3 or country' );
        }
    }
}
