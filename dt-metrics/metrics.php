<?php

/**
 * Disciple_Tools_Metrics
 *
 * @class      Disciple_Tools_Metrics
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 * @author     Chasm.Solutions & Kingdom.Training
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Admin_Menus
 */
class Disciple_Tools_Metrics
{

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $url_path = dt_get_url_path();
        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_google' ], 10 );

            if ( user_can( get_current_user_id(), 'manage_dt' ) || current_user_can( "view_project_metrics" ) ) {

                // load basic charts
                require_once( get_template_directory() . '/dt-metrics/metrics-personal.php' );
                require_once( get_template_directory() . '/dt-metrics/metrics-project.php' );
//                require_once( get_template_directory() . '/dt-metrics/metrics-users.php' );
            }
        }
    }

    // Enqueue maps and charts for standard metrics
    public function enqueue_google() {
        /* phpcs:ignore WordPress.WP.EnqueuedResourceParameters */
        wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', [], false );
        /* phpcs:ignore WordPress.WP.EnqueuedResourceParameters */
        wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . dt_get_option( 'map_key' ), array(), null, true );
    }

    /**
     * Get the critical path in an array
     * This function builds the caching layer to the critical path data. Often this data will not change rapidly,
     * so it a good candidate for transient caching
     *
     * @return array|WP_Error
     */
    public static function chart_critical_path(): array {
        // Check for transient cache first for speed
        $current = get_transient( 'dt_critical_path' );
        if ( empty( $current ) ) {
            $current = Disciple_Tools_Counter::critical_path();
            if ( is_wp_error( $current ) ) {
                return $current;
            }
            $current['timestamp'] = current_time( 'mysql' ); // add timestamp so that we can publish age of the data
            set_transient( 'dt_critical_path', $current, 6 * HOUR_IN_SECONDS ); // transient is set to update every 6 hours. Average work day.
        }
        return $current;
    }



    /**
     * Check permissions for if the user can view a certain report
     *
     * @param $report_name
     * @param $user_id
     *
     * @return bool
     */
    public static function can_view( $report_name, $user_id ) {
        // TODO decide on permission strategy for reporting
        // Do we hardwire permissions to reports to the roles of a person?
        // Do we set up a permission assignment tool in the config area, so that a group could assign reports to a role
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }
        if ( ! $user_id ) {
            return false;
        }

        switch ( $report_name ) {
            case 'critical_path':
                return true;
                break;
            default:
                return true; // TODO temporary true response returned until better permissions check is created
                break;
        }
    }


}

/**
 * This builds and gets the generation tree, and for speed caches today's snapshot
 *
 * @param bool $reset (This allows the ability to reset the cache)
 *
 * @return array|mixed
 */
function dt_get_generation_tree( $reset = false ) {

    $generation_tree = get_transient( 'dt_generation_tree' );

    if ( ! $generation_tree || $reset ) {
        $generation_tree  =Disciple_Tools_Counter::critical_path( 'all_group_generations', 0, PHP_INT_MAX );
        set_transient( 'dt_generation_tree', $generation_tree, dt_get_time_until_midnight() );
    }
    return $generation_tree;
}

function dt_get_time_until_midnight() {
    $midnight = mktime( 0, 0, 0, date( 'n' ), date( 'j' ) +1, date( 'Y' ) );
    return $midnight - current_time( 'timestamp' );
}

abstract class Disciple_Tools_Metrics_Hooks_Base
{
    public function __construct() {}

    public static function chart_my_hero_stats( $user_id = null ) {
        if ( is_null( $user_id ) || empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $results = self::query_my_hero_stats( $user_id );

        $group_health = self::query_my_group_health();
        $needs_training = 0;

        if ( ! empty( $group_health ) ) {
            foreach ( $group_health as $value ) {
                $count = intval( $value['out_of'] ) - intval( $value['count'] );
                if ( $count > $needs_training ) {

                    $needs_training = $count;
                }
            }
        }

        $chart = [
            'contacts' => $results['contacts'],
            'needs_accept' => $results['needs_accept'],
            'needs_update' => $results['needs_update'],
            'groups' => $results['groups'],
            'needs_training' => $needs_training,
            'fully_practicing' => (int) $results['groups'] - (int) $needs_training,
        ];

        return $chart;
    }

    public static function chart_contacts_progress( $type = 'personal' ) {
        $chart = [];

        switch ( $type ) {
            case 'personal':
                $results = self::query_my_contacts_progress( get_current_user_id() );

                $chart[] = [ 'Step', 'Contacts', [ 'role' => 'annotation' ] ];

                foreach ( $results as $value ) {
                    $chart[] = [ $value['label'], $value['count'], $value['count'] ];
                }
                break;
            case 'project':
                $chart = self::query_project_contacts_progress();

                break;
            default:
                $chart = [
                    [ 'Step', 'Contacts', [ 'role' => 'annotation' ] ],
                    [ 'Contact Attempt Needed', 0, 0 ],
                    [ 'Contact Attempted', 0, 0 ],
                    [ 'Contact Established', 0, 0 ],
                    [ 'First Meeting Scheduled', 0, 0 ],
                    [ 'First Meeting Complete', 0, 0 ],
                    [ 'Ongoing Meetings', 0, 0 ],
                    [ 'Being Coached', 0, 0 ],
                ];
                break;
        }

        return $chart;
    }

    public static function chart_group_types( $type = 'personal' ) {

        $chart = [];

        $types = $status = dt_get_option( 'group_type' );

        switch ( $type ) {
            case 'personal':
                $results = self::query_my_group_types();
                $chart[] = [ 'Group Type', 'Number' ];
                foreach ( $results as $result ) {
                    $label = $types[$result['type']] ?? $result['type'];
                    $chart[] = [ $label, intval( $result['count'] ) ];
                }
                break;
            case 'project':
                $results = self::query_project_group_types();
                $chart[] = [ 'Group Type', 'Number' ];
                foreach ( $results as $result ) {
                    $label = $types[$result['type']] ?? $result['type'];
                    $chart[] = [ $label, intval( $result['count'] ) ];
                }
                break;
            default:
                $chart = [
                    [ 'Group Type', 'Number' ],
                    [ 'Pre-Group', 0 ],
                    [ 'Group', 0 ],
                    [ 'Church', 0 ],
                ];
                break;
        }

        return $chart;
    }

    public static function chart_group_health( $type = 'personal' ) {

        // Make key list
        $default_key_list = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
        $labels = [];
        foreach ( $default_key_list as $key => $list ) {
            if ( 'church' == substr( $key, 0, 6 ) ) {
                $labels[$key] = $list['name'];
            }
        }

        $chart = [];

        switch ( $type ) {
            case 'personal':
                $results = self::query_my_group_health();
                break;
            case 'project':
                $results = self::query_project_group_health();
                break;
            default:
                $results = false;
                break;
        }

        if ( $results ) {

            if ( isset( $results[0]['out_of'] ) ) {
                $out_of = $results[0]['out_of'];
            }

            // Create rows from results
            foreach ( $results as $result ) {
                foreach ( $labels as $k_label => $v_label ) {
                    if ( $k_label === $result['health_key'] ) {
                        $value = intval( $result['out_of'] ) - intval( $result['count'] );
                        $chart[] = [ $v_label, intval( $result['count'] ), intval( $value ), '' ];
                        unset( $labels[ $k_label ] ); // remove established value from list
                        break;
                    }
                    $out_of = $result['out_of'];
                }
            }
        } else {
            $out_of = 0;
        }

        // Create remaining rows at full value
        foreach ( $labels as $k_label => $v_label ) {
            $chart[] = [ $v_label, 0, $out_of, '' ];
        }

        array_unshift( $chart, [ 'Step', 'Practicing', 'Not Practicing', [ 'role' => 'annotation' ] ] ); // add top row

        return $chart;
    }

    public static function chart_group_generations( $type = 'personal' ) {

        switch ( $type ) {
            case 'personal':
                $user_id = get_current_user_id();
                $generation_tree  = Disciple_Tools_Counter::critical_path( 'all_group_generations', 0, PHP_INT_MAX, [ 'assigned_to' => $user_id ] );
                break;
            case 'project':
                $generation_tree  = Disciple_Tools_Counter::critical_path( 'all_group_generations', 0, PHP_INT_MAX );
                break;
            default:
                $generation_tree = [ "Generations", "Pre-Group", "Group", "Church", [ "role" => "Annotation" ] ];
                break;
        }

        return $generation_tree;
    }


    public static function chart_streams() {
        $tree = dt_get_generation_tree();

        $streams = Disciple_Tools_Counter_Base::get_stream_count( $tree );

        ksort( $streams );

        $chart = [
            [ 'Generations', 'Streams' ],
        ];

        foreach ( $streams as $row_key => $row_value ) {
            $chart[] = [ (string) $row_key . ' gen' , intval( $row_value ) ];
        }

        return $chart;
    }

    public static function chart_project_hero_stats() {
        $stats = self::query_project_hero_stats();

        $group_health = self::query_project_group_health();
        $needs_training = 0;

        if ( ! empty( $group_health ) ) {
            foreach ( $group_health as $value ) {
                $count = intval( $value['out_of'] ) - intval( $value['count'] );
                if ( $count > $needs_training ) {

                    $needs_training = $count;
                }
            }
        }

        $results = [
            'total_contacts' => $stats["total_contacts"],
            'active_contacts' => $stats['active_contacts'],
            'needs_accepted' => $stats['needs_accept'],
            'updates_needed' => $stats['needs_update'],
            'total_groups' => $stats['groups'],
            'needs_training' => $needs_training,
            'fully_practicing' => (int) $stats['groups'] - (int) $needs_training,
            'generations' => 0,
        ];

        return $results;
    }


    /************************************************************************************************************
     * CRITICAL PATH
     *
     * @param null $start
     * @param null $end
     *
     * @return array|
     */

    public static function chart_critical_path( $start = null, $end = null ) {
        $chart = Disciple_Tools_Counter::critical_path( 'all', $start, $end );

        /**
         * Filter chart array before sending to enqueue.
         */
        $chart = apply_filters( 'dt_chart_critical_path', $chart, $start, $end );

        return $chart;
    }



    // @todo
    public static function chart_timeline() {
        $default = [
            'May' => [
                [
                    'day' => '20',
                    'content' => [
                        [
                            'count' => 0,
                            'tag' => 'new contacts',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'new groups',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'seeker steps increased',
                        ],

                    ],
                ],
                [
                    'day' => '19',
                    'content' => [
                        [
                            'count' => 0,
                            'tag' => 'new contacts',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'new groups',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'seeker steps increased',
                        ],

                    ],
                ],
                [
                    'day' => '17',
                    'content' => [
                        [
                            'count' => 0,
                            'tag' => 'new contacts',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'new groups',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'seeker steps increased',
                        ],

                    ],
                ],
                [
                    'day' => '16',
                    'content' => [
                        [
                            'count' => 0,
                            'tag' => 'new contacts',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'new groups',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'seeker steps increased',
                        ],

                    ],
                ],
                [
                    'day' => '01',
                    'content' => [
                        [
                            'count' => 0,
                            'tag' => 'new contacts',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'new groups',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'seeker steps increased',
                        ],

                    ],
                ],
            ],
            'April' => [
                [
                    'day' => '30',
                    'content' => [
                        [
                            'count' => 0,
                            'tag' => 'new contacts',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'new groups',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'seeker steps increased',
                        ],

                    ],
                ],
                [
                    'day' => '29',
                    'content' => [
                        [
                            'count' => 0,
                            'tag' => 'new contacts',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'new groups',
                        ],
                        [
                            'count' => 0,
                            'tag' => 'seeker steps increased',
                        ],

                    ],
                ],
            ],
        ];

        $results = [];

        return wp_parse_args( $results, $default );
    }

    /**
     * USERS METRICS
     */
    public static function chart_user_hero_stats() {
        $default = [
            'multipliers' => 0,
            'dispatchers' => 0,
            'other' => 0,
            'total' => 0,
        ];

        $system_users = count_users();
        $dispatchers = $system_users['avail_roles']['dispatcher'];
        $multiplier = $system_users['avail_roles']['multiplier'];
        $total_users = $system_users['total_users'];

        $other = $total_users - ( $dispatchers + $multiplier );

        $results = [
            'multipliers' => $multiplier,
            'dispatchers' => $dispatchers,
            'other' => $other,
            'total' => $system_users['total_users'],
        ];

        return wp_parse_args( $results, $default );
    }

    public static function chart_user_logins_by_day() {

        $results = self::query_logins_by_day();

        $logins = [
            [ 'Day', 'Logins' ]
        ];

        foreach ( $results as $result ) {
            $logins[] = [ $result['day'] . ' ' . $result['month'], intval( $result['logins'] ) ];
        }

        return $logins;
    }

    // @todo
    public static function chart_user_contacts_per_user() {

        $default = [
            [ 'User', 'Active Contacts', 'Attempt Needed', 'Attempted', 'Established', '1st Scheduled', '1st Complete', 'Ongoing', 'Being Coached' ],
            [ 'Chris', 100, 4, 0, 4, 8, 0, 0, 9 ],
            [ 'Kara', 100, 4, 0, 4, 8, 0, 0, 9 ],
        ];

        $results = [];

        return wp_parse_args( $results, $default );
    }

    // @todo
    public static function chart_user_least_active() {

        $default = [
            [ 'User', 'Login (Days Ago)' ],
            [ 'Chris', 34 ],
            [ 'Kara', 14 ],
            [ 'Mason', 9 ],
        ];

        $results = [];

        return wp_parse_args( $results, $default );
    }

    // @todo
    public static function chart_user_most_active() {

        $default = [
            [ 'User', 'Logins' ],
            [ 'Chris', 34 ],
            [ 'Kara', 14 ],
            [ 'Mason', 9 ],
        ];

        $results = [];

        return wp_parse_args( $results, $default );
    }


    /**
     * QUERIES
     */

    public static function query_my_contacts_progress( $user_id = null ) {
        global $wpdb;
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $defaults = [];
        $status = dt_get_option( 'seeker_path' );
        foreach ( $status as $key => $label ) {
            $defaults[$key] = [
                'label' => $label,
                'count' => 0,
            ];
        }

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT b.meta_value as status, count( a.ID ) as count
             FROM $wpdb->posts as a
               JOIN $wpdb->postmeta as b
                 ON a.ID=b.post_id
                    AND b.meta_key = 'seeker_path'
               JOIN $wpdb->postmeta as c
                 ON a.ID=c.post_id
                    AND c.meta_key = 'assigned_to'
                    AND c.meta_value = %s
               JOIN $wpdb->postmeta as d
                 ON a.ID=d.post_id
                    AND d.meta_key = 'overall_status'
                    AND d.meta_value = 'active'
             WHERE a.post_status = 'publish'
              AND a.post_type = 'contacts'
              AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'corresponds_to_user'
                      AND meta_value != 0
                    GROUP BY post_id
                )
             GROUP BY b.meta_value
        ",
        'user-'. $user_id ), ARRAY_A );

        $query_results = [];

        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( isset( $defaults[$result['status']] ) ) {
                    $query_results[$result['status']] = [
                        'label' => $defaults[$result['status']]['label'],
                        'count' => intval( $result['count'] ),
                    ];
                }
            }
        }

        return wp_parse_args( $query_results, $defaults );
    }

    public static function query_project_contacts_progress() {
        global $wpdb;


        $results = $wpdb->get_results( "
            SELECT b.meta_value as status, count( a.ID ) as count
             FROM $wpdb->posts as a
               JOIN $wpdb->postmeta as b
                 ON a.ID=b.post_id
                    AND b.meta_key = 'seeker_path'
               JOIN $wpdb->postmeta as d
                 ON a.ID=d.post_id
                    AND d.meta_key = 'overall_status'
                    AND d.meta_value = 'active'
             WHERE a.post_status = 'publish'
                AND a.post_type = 'contacts'
                AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'corresponds_to_user'
                      AND meta_value != 0
                    GROUP BY post_id
                )
             GROUP BY b.meta_value
        ", ARRAY_A );

        $query_results = [];

        $status = dt_get_option( 'seeker_path' );
        foreach ( $status as $status_key => $status_label ){
            $added = false;
            foreach ( $results as $result ) {
                if ( $result["status"] == $status_key ){
                    $query_results[] = [
                        'key' => $status_key,
                        'label' => $status_label,
                        'value' => intval( $result['count'] )
                    ];
                    $added = true;
                }
            }
            if ( !$added ){
                $query_results[] = [
                    'key' => $status_key,
                    'label' => $status_label,
                    'value' => 0
                ];
            }
        }

        return $query_results;
    }

    public static function query_logins_by_day() {
        global $wpdb;

        $results = $wpdb->get_results("
            SELECT
              DATE_FORMAT(FROM_UNIXTIME(`hist_time`), '%e') AS day,
              DATE_FORMAT(FROM_UNIXTIME(`hist_time`), '%b') AS month,
              DATE_FORMAT(FROM_UNIXTIME(`hist_time`), '%Y') AS year,
              count(histid) as logins
            FROM $wpdb->dt_activity_log
            WHERE action = 'logged_in'
            GROUP BY day
            ORDER BY DAY(hist_time)
            LIMIT 60
        ", ARRAY_A );

        return $results;
    }

    public static function query_project_group_types() {
        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT c.meta_value as type, count( a.ID ) as count
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                ON a.ID=b.post_id
                AND b.meta_key = 'group_status'
                   AND b.meta_value = 'active'
                JOIN $wpdb->postmeta as c
                ON a.ID=c.post_id
                AND c.meta_key = 'group_type'
                WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups'
                GROUP BY type
                ORDER BY type DESC
        ", ARRAY_A );

        return $results;
    }

    public static function query_my_group_types( $user_id = null ) {
        global $wpdb;

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT c.meta_value as type, count( a.ID ) as count
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                ON a.ID=b.post_id
                AND b.meta_key = 'group_status'
                   AND b.meta_value = 'active'
                JOIN $wpdb->postmeta as c
                ON a.ID=c.post_id
                AND c.meta_key = 'group_type'
                JOIN $wpdb->postmeta as d
                 ON a.ID=d.post_id
                    AND d.meta_key = 'assigned_to'
                    AND d.meta_value = %s
                WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups'
                GROUP BY type
                ORDER BY type DESC
        ",
        'user-' . $user_id ), ARRAY_A );

        return $results;
    }

    public static function query_my_hero_stats( $user_id = null ) {
        global $wpdb;
        $numbers = [];

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }
        $personal_counts = $wpdb->get_results( $wpdb->prepare( "
            SELECT (
             SELECT count(a.ID)
             FROM $wpdb->posts as a
              JOIN $wpdb->postmeta as d
                   ON a.ID=d.post_id
                      AND d.meta_key = 'overall_status'
                      AND d.meta_value = 'active'
              JOIN $wpdb->postmeta as b
                 ON a.ID=b.post_id
                    AND b.meta_key = 'assigned_to'
                    AND b.meta_value = CONCAT( 'user-', %s )
             WHERE a.post_status = 'publish'
              AND a.post_type = 'contacts'
              AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'corresponds_to_user'
                      AND meta_value != 0
                    GROUP BY post_id
                ))
              as contacts,
              (SELECT count(a.ID)
               FROM $wpdb->posts as a
                 JOIN $wpdb->postmeta as b
                   ON a.ID=b.post_id
                      AND b.meta_key = 'accepted'
                      AND b.meta_value = 'no'
                 JOIN $wpdb->postmeta as c
                   ON a.ID=c.post_id
                      AND c.meta_key = 'assigned_to'
                      AND c.meta_value = CONCAT( 'user-', %s )
                 JOIN $wpdb->postmeta as d
                   ON a.ID=d.post_id
                      AND d.meta_key = 'overall_status'
                      AND d.meta_value = 'active'
               WHERE a.post_status = 'publish'
                     AND a.post_type = 'contacts'
                     AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'corresponds_to_user'
                      AND meta_value != 0
                    GROUP BY post_id
                ))
                as needs_accept,
              (SELECT count(a.ID)
               FROM $wpdb->posts as a
                 JOIN $wpdb->postmeta as b
                   ON a.ID=b.post_id
                      AND b.meta_key = 'requires_update'
                      AND b.meta_value = 'yes'
                 JOIN $wpdb->postmeta as c
                   ON a.ID=c.post_id
                      AND c.meta_key = 'assigned_to'
                      AND c.meta_value = CONCAT( 'user-', %s )
                 JOIN $wpdb->postmeta as d
                   ON a.ID=d.post_id
                      AND d.meta_key = 'overall_status'
                      AND d.meta_value = 'active'
               WHERE a.post_status = 'publish'
                     AND a.post_type = 'contacts'
                     AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'corresponds_to_user'
                      AND meta_value != 0
                    GROUP BY post_id
                ))
                as needs_update,
              (SELECT count(a.ID)
               FROM $wpdb->posts as a
                 JOIN $wpdb->postmeta as c
                   ON a.ID=c.post_id
                      AND c.meta_key = 'assigned_to'
                      AND c.meta_value = CONCAT( 'user-', %s )
                 JOIN $wpdb->postmeta as d
                   ON a.ID=d.post_id
                      AND d.meta_key = 'group_status'
                      AND d.meta_value = 'active'
               WHERE a.post_status = 'publish'
                     AND a.post_type = 'groups')
                as `groups`
            ",
            $user_id,
            $user_id,
            $user_id,
            $user_id
        ), ARRAY_A );

        if ( empty( $personal_counts ) ) {
            return new WP_Error( __METHOD__, 'No results from the personal count query' );
        }

        foreach ( $personal_counts[0] as $key => $value ) {
            $numbers[$key] = $value;
        }

        return $numbers;
    }

    public static function query_my_group_health( $user_id = null ) {
        global $wpdb;

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT d.meta_key as health_key,
              count(*) as count,
              ( SELECT count(*)
              FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                     AND b.meta_key = 'assigned_to'
                     AND b.meta_value = %s
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                     AND c.meta_key = 'group_status'
                     AND c.meta_value = 'active'
              WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
              ) as out_of
              FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                     AND b.meta_key = 'assigned_to'
                     AND b.meta_value = %s
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                     AND c.meta_key = 'group_status'
                     AND c.meta_value = 'active'
                JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
              WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
                    AND d.meta_key LIKE %s
              GROUP BY d.meta_key
        ",
            'user-' . $user_id,
            'user-' . $user_id,
        $wpdb->esc_like( 'church_' ) . '%' ), ARRAY_A );

        return $results;
    }

    public static function query_project_group_health() {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare( "
            SELECT d.meta_key as health_key,
              count(*) as count,
              ( SELECT count(*)
              FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                     AND c.meta_key = 'group_status'
                     AND c.meta_value = 'active'
              WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
              ) as out_of
              FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                     AND c.meta_key = 'group_status'
                     AND c.meta_value = 'active'
                JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
              WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
                    AND d.meta_key LIKE %s
              GROUP BY d.meta_key
        ", $wpdb->esc_like( 'church_' ) . '%' ), ARRAY_A );

        return $results;
    }


    public static function query_my_group_generations( $user_id = null ) {
        global $wpdb;

        if ( is_null( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
              a.ID         as id,
              0            as parent_id,
              d.meta_value as group_type,
              c.meta_value as group_status
            FROM $wpdb->posts as a
              JOIN $wpdb->postmeta as b
                  ON a.ID=b.post_id
                     AND b.meta_key = 'assigned_to'
                     AND b.meta_value = %s
              JOIN $wpdb->postmeta as c
                ON a.ID = c.post_id
                   AND c.meta_key = 'group_status'
              LEFT JOIN $wpdb->postmeta as d
                ON a.ID = d.post_id
                   AND d.meta_key = 'group_type'
            WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups'
                  AND a.ID NOT IN (
              SELECT DISTINCT (p2p_from)
              FROM $wpdb->p2p
              WHERE p2p_type = 'groups_to_groups'
              GROUP BY p2p_from)
            UNION
            SELECT
              p.p2p_from                          as id,
              p.p2p_to                            as parent_id,
              (SELECT meta_value
               FROM $wpdb->postmeta
               WHERE post_id = p.p2p_from
                     AND meta_key = 'group_type') as group_type,
               (SELECT meta_value
               FROM $wpdb->postmeta
               WHERE post_id = p.p2p_from
                     AND meta_key = 'group_status') as group_status
            FROM $wpdb->p2p as p
            WHERE p.p2p_type = 'groups_to_groups'
        ", 'user-' . $user_id ), ARRAY_A );

        return $results;
    }

    public static function query_project_hero_stats() {
        global $wpdb;

        $numbers = [];
        $numbers["total_contacts"] = Disciple_Tools_Counter::critical_path( 'new_contacts' );


        $results = $wpdb->get_results( "
            SELECT (
                SELECT count(a.ID)
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                ON a.ID = b.post_id
                AND b.meta_key = 'overall_status'
                AND b.meta_value = 'active'
                WHERE a.post_status = 'publish'
                AND a.post_type = 'contacts'
                AND a.ID NOT IN (
                SELECT bb.post_id
                FROM $wpdb->postmeta as bb
                WHERE meta_key = 'corresponds_to_user'
                AND meta_value != 0
                GROUP BY bb.post_id )
                )
          as active_contacts,
               (SELECT count(a.ID)
                FROM $wpdb->posts as a
                            JOIN $wpdb->postmeta as b
                            ON a.ID=b.post_id
                               AND b.meta_key = 'accepted'
                                     AND b.meta_value = 'no'
               JOIN $wpdb->postmeta as d
               ON a.ID=d.post_id
                      AND d.meta_key = 'overall_status'
               AND d.meta_value = 'active'
               WHERE a.post_status = 'publish'
                AND a.post_type = 'contacts'
                AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'corresponds_to_user'
                      AND meta_value != 0
                    GROUP BY post_id
                ))
          as needs_accept,
               (SELECT count(a.ID)
                FROM $wpdb->posts as a
                            JOIN $wpdb->postmeta as b
                            ON a.ID=b.post_id
                               AND b.meta_key = 'requires_update'
                                     AND b.meta_value = 'yes'
               JOIN $wpdb->postmeta as d
               ON a.ID=d.post_id
                      AND d.meta_key = 'overall_status'
               AND d.meta_value = 'active'
               WHERE a.post_status = 'publish'
                AND a.post_type = 'contacts'
                AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'corresponds_to_user'
                      AND meta_value != 0
                    GROUP BY post_id
                ))
          as needs_update,
               (SELECT count(a.ID)
                FROM $wpdb->posts as a
               JOIN $wpdb->postmeta as d
               ON a.ID=d.post_id
                      AND d.meta_key = 'group_status'
               AND d.meta_value = 'active'
               WHERE a.post_status = 'publish'
                AND a.post_type = 'groups')
          as `groups`
        ",
        ARRAY_A );

        if ( empty( $results ) ) {
            return new WP_Error( __METHOD__, 'No results from the personal count query' );
        }

        foreach ( $results[0] as $key => $value ) {
            $numbers[$key] = $value;
        }

        return $numbers;
    }
}

