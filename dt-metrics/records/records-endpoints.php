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
 * Class Disciple_Tools_Metrics_Records_Endpoints
 */
class Disciple_Tools_Metrics_Records_Endpoints
{

    private $version = 1;
    private $context = 'dt';
    private $namespace;
    public $permissions = [ 'view_project_metrics', 'dt_all_access_contacts' ];

    /**
     * Disciple_Tools_Metrics_Records_Endpoints The single instance of Disciple_Tools_Metrics_Records_Endpoints.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Metrics_Records_Endpoints Instance
     * Ensures only one instance of Disciple_Tools_Metrics_Records_Endpoints is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Metrics_Endpoints instance
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
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    } // End __construct()

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/metrics/cumulative-posts', [
                'methods'  => 'POST',
                'callback' => [ $this, 'get_posts_by_field_in_date_range' ],
                'permission_callback' => [ $this, 'has_permission' ],
            ]
        );
        register_rest_route(
            $this->namespace, '/metrics/changed-posts', [
                'methods'  => 'POST',
                'callback' => [ $this, 'get_posts_by_field_in_date_range_changes' ],
                'permission_callback' => [ $this, 'has_permission' ],
            ]
        );
    }

    public function has_permission(){
        $permissions = $this->permissions;
        $pass = count( $permissions ) === 0;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function get_posts_by_field_in_date_range( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params['post_type'], $params['field'] ) ){
            return DT_Counter_Post_Stats::get_posts_by_field_in_date_range( $params['post_type'], $params['field'], [
                'key' => $params['key'] ?? null,
                'start' => $params['ts_start'] ?? 0,
                'end' => $params['ts_end'] ?? time(),
                'limit' => $params['limit'] ?? 100,
            ] );
        }

        return [];
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return array|WP_Error
     */
    public function get_posts_by_field_in_date_range_changes( WP_REST_Request $request ){
        $params = $request->get_params();
        if ( isset( $params['post_type'], $params['field'] ) ){
            return DT_Counter_Post_Stats::get_posts_by_field_in_date_range_changes( $params['post_type'], $params['field'], [
                'key' => $params['key'] ?? null,
                'start' => $params['ts_start'] ?? 0,
                'end' => $params['ts_end'] ?? time(),
                'limit' => $params['limit'] ?? 100,
            ] );
        }

        return [];
    }
}

Disciple_Tools_Metrics_Records_Endpoints::instance();
