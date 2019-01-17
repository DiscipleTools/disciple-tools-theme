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

        $location_list = [
            ['id' => 'AF', 'name' => 'Afganistan'],
            ['id' => 'US', 'name' => 'United States'],
            ['id' => 'TN', 'name' => 'Tunisia'],
        ];
        $location_id = rand(0,2);

        $profile = dt_get_partner_profile();

        return [
            'partner_id' => $profile['partner_id'],
            'profile' => $profile,
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
                        [
                            'date' => '2018-12-15',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-14',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-13',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-12',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-11',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-10',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-09',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-08',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-07',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-06',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-05',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-04',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-03',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-02',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-30',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-29',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-28',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-27',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-26',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-25',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-24',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-23',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-22',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-21',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-20',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-19',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-18',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-17',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-16',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-15',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-14',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-13',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-12',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-11',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-10',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-09',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-08',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-07',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-06',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-05',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-04',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-03',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-02',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-31',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-30',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-29',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-28',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-27',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-26',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-25',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-24',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-23',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-22',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-21',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-20',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-19',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-18',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-17',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-16',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-15',
                            'value' => rand(300, 1000),
                        ],
                    ],
                    'twenty_four_months' => [
                        [
                            'date' => '2018-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-09-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-08-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-07-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-06-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-05-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-04-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-03-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-02-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-01-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-10-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-09-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-08-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-07-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-06-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-05-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-04-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-03-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-02-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-01-01',
                            'value' => rand(300, 1000),
                        ],
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
                            [
                                'date' => '2018-12-15',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-14',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-13',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-12',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-11',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-10',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-09',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-08',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-07',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-06',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-05',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-04',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-03',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-02',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-12-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-30',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-29',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-28',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-27',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-26',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-25',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-24',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-23',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-22',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-21',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-20',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-19',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-18',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-17',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-16',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-15',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-14',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-13',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-12',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-11',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-10',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-09',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-08',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-07',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-06',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-05',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-04',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-03',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-02',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-31',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-30',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-29',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-28',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-27',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-26',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-25',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-24',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-23',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-22',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-21',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-20',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-19',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-18',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-17',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-16',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-15',
                                'value' => rand(300, 1000),
                            ],
                        ],
                        'twenty_four_months' => [
                            [
                                'date' => '2018-12-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-11-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-10-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-09-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-08-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-07-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-06-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-05-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-04-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-03-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-02-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2018-01-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-12-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-11-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-10-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-09-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-08-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-07-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-06-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-05-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-04-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-03-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-02-01',
                                'value' => rand(300, 1000),
                            ],
                            [
                                'date' => '2017-01-01',
                                'value' => rand(300, 1000),
                            ],
                        ],
                    ],
                    'highest_generation' => 6,
                    'generations' => [
                        [
                            'label' => 'Gen 1',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 2',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 3',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 4',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 5',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 6',
                            'value' => rand(300, 1000)
                        ]
                    ],
                ],
                'coaching' => [
                    'highest_generation' => 3,
                    'generations' => [
                        [
                            'label' => 'Gen 1',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 2',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 3',
                            'value' => rand(300, 1000)
                        ]
                    ],
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
                'by_types' => [
                    [
                        'label' => 'Pre-Group',
                        'value' => rand(300, 1000),
                    ],
                    [
                        'label' => 'Group',
                        'value' => rand(300, 1000),
                    ],
                    [
                        'label' => 'Church',
                        'value' => rand(300, 1000),
                    ],
                    [
                        'label' => 'Leadership Cell',
                        'value' => rand(300, 1000),
                    ]
                ],
                'added' => [ // measure the addition of groups over time
                    'sixty_days' => [
                                 [
                                     'date' => '2018-12-15',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-14',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-13',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-12',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-11',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-10',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-09',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-08',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-07',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-06',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-05',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-04',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-03',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-02',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-12-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-30',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-29',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-28',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-27',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-26',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-25',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-24',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-23',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-22',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-21',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-20',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-19',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-18',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-17',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-16',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-15',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-14',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-13',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-12',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-11',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-10',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-09',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-08',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-07',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-06',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-05',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-04',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-03',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-02',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-31',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-30',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-29',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-28',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-27',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-26',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-25',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-24',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-23',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-22',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-21',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-20',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-19',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-18',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-17',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-16',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-15',
                                     'value' => rand(300, 1000),
                                 ],
                             ],
                    'twenty_four_months' => [
                                 [
                                     'date' => '2018-12-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-11-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-10-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-09-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-08-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-07-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-06-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-05-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-04-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-03-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-02-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2018-01-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-12-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-11-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-10-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-09-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-08-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-07-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-06-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-05-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-04-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-03-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-02-01',
                                     'value' => rand(300, 1000),
                                 ],
                                 [
                                     'date' => '2017-01-01',
                                     'value' => rand(300, 1000),
                                 ],
                             ],
                ],
                'health' => [
                    [
                        'category' => 'Baptism',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Bible Study',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Communion',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Fellowship',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Giving',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Prayer',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Praise',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Sharing',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Leaders',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ],
                    [
                        'category' => 'Commitment',
                        'practicing' => rand(300, 1000),
                        'not_practicing' => rand(300, 1000),
                    ]
                ],
                'church_generations' => [
                    'highest_generation' => 4,
                    'generations' => [
                        [
                            'label' => 'Gen 1',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 2',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 3',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 4',
                            'value' => rand(300, 1000)
                        ]
                    ],
                ],
                'all_generations' => [
                    'highest_generation' => 7,
                    'generations' => [
                        [
                            'label' => 'Gen 1',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 2',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 3',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 4',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 5',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 6',
                            'value' => rand(300, 1000)
                        ],
                        [
                            'label' => 'Gen 7',
                            'value' => rand(300, 1000)
                        ],
                    ],
                ]
            ],
            'users' => [
                'current_state' => [
                    'total_users' => rand(300, 1000),
                    'roles' => [
                        'responders' => rand(3, 100),
                        'dispatchers' => rand(3, 100),
                        'multipliers' => rand(3, 100),
                        'admins' => rand(3, 100),
                    ],
                    'updates' => rand(300, 1000),
                ],
                'login_activity' => [
                    'sixty_days' => [
                        [
                            'date' => '2018-12-15',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-14',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-13',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-12',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-11',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-10',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-09',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-08',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-07',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-06',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-05',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-04',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-03',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-02',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-30',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-29',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-28',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-27',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-26',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-25',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-24',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-23',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-22',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-21',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-20',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-19',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-18',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-17',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-16',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-15',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-14',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-13',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-12',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-11',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-10',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-09',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-08',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-07',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-06',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-05',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-04',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-03',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-02',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-31',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-30',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-29',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-28',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-27',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-26',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-25',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-24',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-23',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-22',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-21',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-20',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-19',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-18',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-17',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-16',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-15',
                            'value' => rand(300, 1000),
                        ],
                    ],
                    'twenty_four_months' => [
                        [
                            'date' => '2018-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-10-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-09-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-08-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-07-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-06-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-05-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-04-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-03-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-02-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2018-01-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-12-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-11-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-10-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-09-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-08-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-07-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-06-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-05-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-04-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-03-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-02-01',
                            'value' => rand(300, 1000),
                        ],
                        [
                            'date' => '2017-01-01',
                            'value' => rand(300, 1000),
                        ],
                    ],
                ],
                'last_thirty_day_engagement' => [
                    [
                        'label' => 'Active',
                        'value' => rand(300, 1000),
                    ],
                    [
                        'label' => 'Inactive',
                        'value' => rand(300, 1000),
                    ]
                ]
            ],
            'locations' => [
                'countries' => [
                    [
                        'id' => $location_list[$location_id]['id'],
                        'name' => $location_list[$location_id]['name'],
                        'site_name' => $profile['partner_name'],
                        'contacts' => rand(300, 1000),
                        'groups' => rand(300, 1000),
                        'value' => 100,
                        'color' => 'red'
                    ]
                ],
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