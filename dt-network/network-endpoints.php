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

        return [
            'partner_id' => dt_get_partner_profile_id(),
            'contacts' => [
                'current_state' => [
                    'active_contacts' => rand(300, 1000),
                    'paused_contacts' => rand(300, 1000),
                    'closed_contacts' => rand(300, 1000),
                    'all_contacts' => rand(8000, 10000),
                    'critical_path' => [
                        'attempt_needed' => rand(8000, 10000),
                        'attempted' => rand(8000, 10000),
                        'established' => rand(8000, 10000),
                        'scheduled' => rand(8000, 10000),
                        'met' => rand(8000, 10000),
                        'ongoing_meetings' => rand(8000, 10000),
                        'coaching' => rand(8000, 10000),
                    ],
                ],
                'added' => [
                    'sixty_days' => [
                        '2018-12-15' => rand(300, 1000),
                        '2018-12-14' => rand(300, 1000),
                        '2018-12-13' => rand(300, 1000),
                        '2018-12-12' => rand(300, 1000),
                        '2018-12-11' => rand(300, 1000),
                        '2018-12-10' => rand(300, 1000),
                        '2018-12-09' => rand(300, 1000),
                        '2018-12-08' => rand(300, 1000),
                        '2018-12-07' => rand(300, 1000),
                        '2018-12-06' => rand(300, 1000),
                        '2018-12-05' => rand(300, 1000),
                        '2018-12-04' => rand(300, 1000),
                        '2018-12-03' => rand(300, 1000),
                        '2018-12-02' => rand(300, 1000),
                        '2018-12-01' => rand(300, 1000),
                        '2018-11-30' => rand(300, 1000),
                        '2018-11-29' => rand(300, 1000),
                        '2018-11-28' => rand(300, 1000),
                        '2018-11-27' => rand(300, 1000),
                        '2018-11-26' => rand(300, 1000),
                        '2018-11-25' => rand(300, 1000),
                        '2018-11-24' => rand(300, 1000),
                        '2018-11-23' => rand(300, 1000),
                        '2018-11-22' => rand(300, 1000),
                        '2018-11-21' => rand(300, 1000),
                        '2018-11-20' => rand(300, 1000),
                        '2018-11-19' => rand(300, 1000),
                        '2018-11-18' => rand(300, 1000),
                        '2018-11-17' => rand(300, 1000),
                        '2018-11-16' => rand(300, 1000),
                        '2018-11-15' => rand(300, 1000),
                        '2018-11-14' => rand(300, 1000),
                        '2018-11-13' => rand(300, 1000),
                        '2018-11-12' => rand(300, 1000),
                        '2018-11-11' => rand(300, 1000),
                        '2018-11-10' => rand(300, 1000),
                        '2018-11-09' => rand(300, 1000),
                        '2018-11-08' => rand(300, 1000),
                        '2018-11-07' => rand(300, 1000),
                        '2018-11-06' => rand(300, 1000),
                        '2018-11-05' => rand(300, 1000),
                        '2018-11-04' => rand(300, 1000),
                        '2018-11-03' => rand(300, 1000),
                        '2018-11-02' => rand(300, 1000),
                        '2018-11-01' => rand(300, 1000),
                        '2018-10-30' => rand(300, 1000),
                        '2018-10-29' => rand(300, 1000),
                        '2018-10-28' => rand(300, 1000),
                        '2018-10-27' => rand(300, 1000),
                        '2018-10-26' => rand(300, 1000),
                        '2018-10-25' => rand(300, 1000),
                        '2018-10-24' => rand(300, 1000),
                        '2018-10-23' => rand(300, 1000),
                        '2018-10-22' => rand(300, 1000),
                        '2018-10-21' => rand(300, 1000),
                        '2018-10-20' => rand(300, 1000),
                        '2018-10-19' => rand(300, 1000),
                        '2018-10-18' => rand(300, 1000),
                        '2018-10-17' => rand(300, 1000),
                        '2018-10-16' => rand(300, 1000),
                        '2018-10-15' => rand(300, 1000),
                    ],
                    'twenty_four_months' => [
                        '2018-12' => rand(300, 1000),
                        '2018-11' => rand(300, 1000),
                        '2018-10' => rand(300, 1000),
                        '2018-09' => rand(300, 1000),
                        '2018-08' => rand(300, 1000),
                        '2018-07' => rand(300, 1000),
                        '2018-06' => rand(300, 1000),
                        '2018-05' => rand(300, 1000),
                        '2018-04' => rand(300, 1000),
                        '2018-03' => rand(300, 1000),
                        '2018-02' => rand(300, 1000),
                        '2018-01' => rand(300, 1000),
                        '2017-12' => rand(300, 1000),
                        '2017-11' => rand(300, 1000),
                        '2017-10' => rand(300, 1000),
                        '2017-09' => rand(300, 1000),
                        '2017-08' => rand(300, 1000),
                        '2017-07' => rand(300, 1000),
                        '2017-06' => rand(300, 1000),
                        '2017-05' => rand(300, 1000),
                        '2017-04' => rand(300, 1000),
                        '2017-03' => rand(300, 1000),
                        '2017-02' => rand(300, 1000),
                        '2017-01' => rand(300, 1000),
                    ]
                ],
            ],
            'baptisms' => [
                'current_state' => [
                    'active_baptisms' => rand(300, 1000),
                    'all_baptisms' => rand(300, 1000),
                    'multiplying' => rand(300, 1000),
                ],
                'added' => [
                    'sixty_days' => [
                        '2018-12-15' => rand(300, 1000),
                        '2018-12-14' => rand(300, 1000),
                        '2018-12-13' => rand(300, 1000),
                        '2018-12-12' => rand(300, 1000),
                        '2018-12-11' => rand(300, 1000),
                        '2018-12-10' => rand(300, 1000),
                        '2018-12-09' => rand(300, 1000),
                        '2018-12-08' => rand(300, 1000),
                        '2018-12-07' => rand(300, 1000),
                        '2018-12-06' => rand(300, 1000),
                        '2018-12-05' => rand(300, 1000),
                        '2018-12-04' => rand(300, 1000),
                        '2018-12-03' => rand(300, 1000),
                        '2018-12-02' => rand(300, 1000),
                        '2018-12-01' => rand(300, 1000),
                        '2018-11-30' => rand(300, 1000),
                        '2018-11-29' => rand(300, 1000),
                        '2018-11-28' => rand(300, 1000),
                        '2018-11-27' => rand(300, 1000),
                        '2018-11-26' => rand(300, 1000),
                        '2018-11-25' => rand(300, 1000),
                        '2018-11-24' => rand(300, 1000),
                        '2018-11-23' => rand(300, 1000),
                        '2018-11-22' => rand(300, 1000),
                        '2018-11-21' => rand(300, 1000),
                        '2018-11-20' => rand(300, 1000),
                        '2018-11-19' => rand(300, 1000),
                        '2018-11-18' => rand(300, 1000),
                        '2018-11-17' => rand(300, 1000),
                        '2018-11-16' => rand(300, 1000),
                        '2018-11-15' => rand(300, 1000),
                        '2018-11-14' => rand(300, 1000),
                        '2018-11-13' => rand(300, 1000),
                        '2018-11-12' => rand(300, 1000),
                        '2018-11-11' => rand(300, 1000),
                        '2018-11-10' => rand(300, 1000),
                        '2018-11-09' => rand(300, 1000),
                        '2018-11-08' => rand(300, 1000),
                        '2018-11-07' => rand(300, 1000),
                        '2018-11-06' => rand(300, 1000),
                        '2018-11-05' => rand(300, 1000),
                        '2018-11-04' => rand(300, 1000),
                        '2018-11-03' => rand(300, 1000),
                        '2018-11-02' => rand(300, 1000),
                        '2018-11-01' => rand(300, 1000),
                        '2018-10-30' => rand(300, 1000),
                        '2018-10-29' => rand(300, 1000),
                        '2018-10-28' => rand(300, 1000),
                        '2018-10-27' => rand(300, 1000),
                        '2018-10-26' => rand(300, 1000),
                        '2018-10-25' => rand(300, 1000),
                        '2018-10-24' => rand(300, 1000),
                        '2018-10-23' => rand(300, 1000),
                        '2018-10-22' => rand(300, 1000),
                        '2018-10-21' => rand(300, 1000),
                        '2018-10-20' => rand(300, 1000),
                        '2018-10-19' => rand(300, 1000),
                        '2018-10-18' => rand(300, 1000),
                        '2018-10-17' => rand(300, 1000),
                        '2018-10-16' => rand(300, 1000),
                        '2018-10-15' => rand(300, 1000),
                    ],
                    'twenty_four_months' => [
                        '2018-12' => rand(300, 1000),
                        '2018-11' => rand(300, 1000),
                        '2018-10' => rand(300, 1000),
                        '2018-09' => rand(300, 1000),
                        '2018-08' => rand(300, 1000),
                        '2018-07' => rand(300, 1000),
                        '2018-06' => rand(300, 1000),
                        '2018-05' => rand(300, 1000),
                        '2018-04' => rand(300, 1000),
                        '2018-03' => rand(300, 1000),
                        '2018-02' => rand(300, 1000),
                        '2018-01' => rand(300, 1000),
                        '2017-12' => rand(300, 1000),
                        '2017-11' => rand(300, 1000),
                        '2017-10' => rand(300, 1000),
                        '2017-09' => rand(300, 1000),
                        '2017-08' => rand(300, 1000),
                        '2017-07' => rand(300, 1000),
                        '2017-06' => rand(300, 1000),
                        '2017-05' => rand(300, 1000),
                        '2017-04' => rand(300, 1000),
                        '2017-03' => rand(300, 1000),
                        '2017-02' => rand(300, 1000),
                        '2017-01' => rand(300, 1000),
                    ]
                ],
                'highest_generation' => 6,
                'generations' => [
                    0 => rand(300, 1000),
                    1 => rand(300, 1000),
                    2 => rand(300, 1000),
                    3 => rand(300, 1000),
                    4 => rand(300, 1000),
                    5 => rand(300, 1000),
                    6 => rand(300, 1000),
                ],
            ],
            'groups' => [
                'current_state' => [ // measure the current state of the system today
                    'active' => [
                        'pre_group' => rand(300, 1000),
                        'group' => rand(300, 1000),
                        'church' => rand(300, 1000),
                        'leadership_cell' => rand(300, 1000),
                    ],
                    'total_active' => rand(300, 1000), // all non-duplicate groups in the system active or inactive.
                    'inactive' => [
                        'pre_group' => rand(300, 1000),
                        'group' => rand(300, 1000),
                        'church' => rand(300, 1000),
                        'leadership_cell' => rand(300, 1000),
                    ],
                    'all' => rand(300, 1000),
                ],
                'added' => [ // measure the addition of groups over time
                    'sixty_days' => [
                        '2018-12-15' => rand(300, 1000),
                        '2018-12-14' => rand(300, 1000),
                        '2018-12-13' => rand(300, 1000),
                        '2018-12-12' => rand(300, 1000),
                        '2018-12-11' => rand(300, 1000),
                        '2018-12-10' => rand(300, 1000),
                        '2018-12-09' => rand(300, 1000),
                        '2018-12-08' => rand(300, 1000),
                        '2018-12-07' => rand(300, 1000),
                        '2018-12-06' => rand(300, 1000),
                        '2018-12-05' => rand(300, 1000),
                        '2018-12-04' => rand(300, 1000),
                        '2018-12-03' => rand(300, 1000),
                        '2018-12-02' => rand(300, 1000),
                        '2018-12-01' => rand(300, 1000),
                        '2018-11-30' => rand(300, 1000),
                        '2018-11-29' => rand(300, 1000),
                        '2018-11-28' => rand(300, 1000),
                        '2018-11-27' => rand(300, 1000),
                        '2018-11-26' => rand(300, 1000),
                        '2018-11-25' => rand(300, 1000),
                        '2018-11-24' => rand(300, 1000),
                        '2018-11-23' => rand(300, 1000),
                        '2018-11-22' => rand(300, 1000),
                        '2018-11-21' => rand(300, 1000),
                        '2018-11-20' => rand(300, 1000),
                        '2018-11-19' => rand(300, 1000),
                        '2018-11-18' => rand(300, 1000),
                        '2018-11-17' => rand(300, 1000),
                        '2018-11-16' => rand(300, 1000),
                        '2018-11-15' => rand(300, 1000),
                        '2018-11-14' => rand(300, 1000),
                        '2018-11-13' => rand(300, 1000),
                        '2018-11-12' => rand(300, 1000),
                        '2018-11-11' => rand(300, 1000),
                        '2018-11-10' => rand(300, 1000),
                        '2018-11-09' => rand(300, 1000),
                        '2018-11-08' => rand(300, 1000),
                        '2018-11-07' => rand(300, 1000),
                        '2018-11-06' => rand(300, 1000),
                        '2018-11-05' => rand(300, 1000),
                        '2018-11-04' => rand(300, 1000),
                        '2018-11-03' => rand(300, 1000),
                        '2018-11-02' => rand(300, 1000),
                        '2018-11-01' => rand(300, 1000),
                        '2018-10-30' => rand(300, 1000),
                        '2018-10-29' => rand(300, 1000),
                        '2018-10-28' => rand(300, 1000),
                        '2018-10-27' => rand(300, 1000),
                        '2018-10-26' => rand(300, 1000),
                        '2018-10-25' => rand(300, 1000),
                        '2018-10-24' => rand(300, 1000),
                        '2018-10-23' => rand(300, 1000),
                        '2018-10-22' => rand(300, 1000),
                        '2018-10-21' => rand(300, 1000),
                        '2018-10-20' => rand(300, 1000),
                        '2018-10-19' => rand(300, 1000),
                        '2018-10-18' => rand(300, 1000),
                        '2018-10-17' => rand(300, 1000),
                        '2018-10-16' => rand(300, 1000),
                        '2018-10-15' => rand(300, 1000),
                    ],
                    'twenty_four_months' => [
                    '2018-12' => rand(300, 1000),
                    '2018-11' => rand(300, 1000),
                    '2018-10' => rand(300, 1000),
                    '2018-09' => rand(300, 1000),
                    '2018-08' => rand(300, 1000),
                    '2018-07' => rand(300, 1000),
                    '2018-06' => rand(300, 1000),
                    '2018-05' => rand(300, 1000),
                    '2018-04' => rand(300, 1000),
                    '2018-03' => rand(300, 1000),
                    '2018-02' => rand(300, 1000),
                    '2018-01' => rand(300, 1000),
                    '2017-12' => rand(300, 1000),
                    '2017-11' => rand(300, 1000),
                    '2017-10' => rand(300, 1000),
                    '2017-09' => rand(300, 1000),
                    '2017-08' => rand(300, 1000),
                    '2017-07' => rand(300, 1000),
                    '2017-06' => rand(300, 1000),
                    '2017-05' => rand(300, 1000),
                    '2017-04' => rand(300, 1000),
                    '2017-03' => rand(300, 1000),
                    '2017-02' => rand(300, 1000),
                    '2017-01' => rand(300, 1000),
                    ]
                ],
                'church_generations' => [
                    'highest_generation' => 6,
                    'generations' => [
                        0 => rand(300, 1000),
                        1 => rand(300, 1000),
                        2 => rand(300, 1000),
                        3 => rand(300, 1000),
                        4 => rand(300, 1000),
                        5 => rand(300, 1000),
                        6 => rand(300, 1000),
                    ],
                ],
                'all_generations' => [
                    'highest_generation' => 6,
                    'generations' => [
                        0 => rand(300, 1000),
                        1 => rand(300, 1000),
                        2 => rand(300, 1000),
                        3 => rand(300, 1000),
                        4 => rand(300, 1000),
                        5 => rand(300, 1000),
                        6 => rand(300, 1000),
                    ],
                ]
            ],
            'users' => [
                'current_state' => [
                    'total_users' => rand(300, 1000),
                    'roles' => [
                        'multipliers' => rand(3, 100),
                        'dispatchers' => rand(3, 100),
                        'responders' => rand(3, 100),
                        'strategists' => rand(3, 100),
                    ],
                    'updates' => rand(300, 1000),
                ],
                'login_activity' => [
                    'sixty_days' => [
                        '2018-12-15' => rand(300, 1000),
                        '2018-12-14' => rand(300, 1000),
                        '2018-12-13' => rand(300, 1000),
                        '2018-12-12' => rand(300, 1000),
                        '2018-12-11' => rand(300, 1000),
                        '2018-12-10' => rand(300, 1000),
                        '2018-12-09' => rand(300, 1000),
                        '2018-12-08' => rand(300, 1000),
                        '2018-12-07' => rand(300, 1000),
                        '2018-12-06' => rand(300, 1000),
                        '2018-12-05' => rand(300, 1000),
                        '2018-12-04' => rand(300, 1000),
                        '2018-12-03' => rand(300, 1000),
                        '2018-12-02' => rand(300, 1000),
                        '2018-12-01' => rand(300, 1000),
                        '2018-11-30' => rand(300, 1000),
                        '2018-11-29' => rand(300, 1000),
                        '2018-11-28' => rand(300, 1000),
                        '2018-11-27' => rand(300, 1000),
                        '2018-11-26' => rand(300, 1000),
                        '2018-11-25' => rand(300, 1000),
                        '2018-11-24' => rand(300, 1000),
                        '2018-11-23' => rand(300, 1000),
                        '2018-11-22' => rand(300, 1000),
                        '2018-11-21' => rand(300, 1000),
                        '2018-11-20' => rand(300, 1000),
                        '2018-11-19' => rand(300, 1000),
                        '2018-11-18' => rand(300, 1000),
                        '2018-11-17' => rand(300, 1000),
                        '2018-11-16' => rand(300, 1000),
                        '2018-11-15' => rand(300, 1000),
                        '2018-11-14' => rand(300, 1000),
                        '2018-11-13' => rand(300, 1000),
                        '2018-11-12' => rand(300, 1000),
                        '2018-11-11' => rand(300, 1000),
                        '2018-11-10' => rand(300, 1000),
                        '2018-11-09' => rand(300, 1000),
                        '2018-11-08' => rand(300, 1000),
                        '2018-11-07' => rand(300, 1000),
                        '2018-11-06' => rand(300, 1000),
                        '2018-11-05' => rand(300, 1000),
                        '2018-11-04' => rand(300, 1000),
                        '2018-11-03' => rand(300, 1000),
                        '2018-11-02' => rand(300, 1000),
                        '2018-11-01' => rand(300, 1000),
                        '2018-10-30' => rand(300, 1000),
                        '2018-10-29' => rand(300, 1000),
                        '2018-10-28' => rand(300, 1000),
                        '2018-10-27' => rand(300, 1000),
                        '2018-10-26' => rand(300, 1000),
                        '2018-10-25' => rand(300, 1000),
                        '2018-10-24' => rand(300, 1000),
                        '2018-10-23' => rand(300, 1000),
                        '2018-10-22' => rand(300, 1000),
                        '2018-10-21' => rand(300, 1000),
                        '2018-10-20' => rand(300, 1000),
                        '2018-10-19' => rand(300, 1000),
                        '2018-10-18' => rand(300, 1000),
                        '2018-10-17' => rand(300, 1000),
                        '2018-10-16' => rand(300, 1000),
                        '2018-10-15' => rand(300, 1000),
                    ],
                    'twenty_four_months' => [
                        '2018-12' => rand(300, 1000),
                        '2018-11' => rand(300, 1000),
                        '2018-10' => rand(300, 1000),
                        '2018-09' => rand(300, 1000),
                        '2018-08' => rand(300, 1000),
                        '2018-07' => rand(300, 1000),
                        '2018-06' => rand(300, 1000),
                        '2018-05' => rand(300, 1000),
                        '2018-04' => rand(300, 1000),
                        '2018-03' => rand(300, 1000),
                        '2018-02' => rand(300, 1000),
                        '2018-01' => rand(300, 1000),
                        '2017-12' => rand(300, 1000),
                        '2017-11' => rand(300, 1000),
                        '2017-10' => rand(300, 1000),
                        '2017-09' => rand(300, 1000),
                        '2017-08' => rand(300, 1000),
                        '2017-07' => rand(300, 1000),
                        '2017-06' => rand(300, 1000),
                        '2017-05' => rand(300, 1000),
                        '2017-04' => rand(300, 1000),
                        '2017-03' => rand(300, 1000),
                        '2017-02' => rand(300, 1000),
                        '2017-01' => rand(300, 1000),
                    ]
                ]
            ],
            'locations' => [
                'current_state' => [
                    'active_locations' => rand(300, 1000),
                    'inactive_locations' => rand(300, 1000),
                    'all_locations' => rand(300, 1000),
                ],
                'list' => [
                    [
                        'location_name' => '',
                        'location_id' => '',
                        'parent_id' => '',
                        'geonameid' => '',
                        'longitude' => '',
                        'latitude' => '',
                        'total_contacts' => 0,
                        'total_groups' => 0,
                        'total_users' => 0,
                        'new_contacts' => 0,
                        'new_groups' => 0,
                        'new_users' => 0,
                    ],
                    [
                        'location_name' => '',
                        'location_id' => '',
                        'parent_id' => '',
                        'geonameid' => '',
                        'longitude' => '',
                        'latitude' => '',
                        'total_contacts' => 0,
                        'total_groups' => 0,
                        'total_users' => 0,
                        'new_contacts' => 0,
                        'new_groups' => 0,
                        'new_users' => 0,
                    ],
                    [
                        'location_name' => '',
                        'location_id' => '',
                        'parent_id' => '',
                        'geonameid' => '',
                        'longitude' => '',
                        'latitude' => '',
                        'total_contacts' => 0,
                        'total_groups' => 0,
                        'total_users' => 0,
                        'new_contacts' => 0,
                        'new_groups' => 0,
                        'new_users' => 0,
                    ],
                ],
            ],
            'date' => current_time( 'timestamp' ),
            'status' => 'OK',
        ];
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