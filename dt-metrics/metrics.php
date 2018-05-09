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
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_google' ], 10 );

        // load basic charts
        require_once( get_template_directory() . '/dt-metrics/metrics-personal.php' );
        require_once( get_template_directory() . '/dt-metrics/metrics-project.php' );

    }

    // Enqueue maps and charts for standard metrics
    public function enqueue_google() {
        $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );
        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {
            wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', [], false );
            wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . dt_get_option( 'map_key' ), array(), null, true );
        }
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
     * Bundles the basic critical path numbers in a google chart format
     *
     * @param $check_permissions
     *
     * @return array|\WP_Error
     */
    public static function chart_critical_path_chart_data( $check_permissions )
    {

        $current_user = get_current_user();
        if ( $check_permissions && !self::can_view( 'critical_path', $current_user ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read contact" ), [ 'status' => 403 ] );
        }

        // Check for transient cache
        $current = self::chart_critical_path();
        if ( is_wp_error( $current ) ) {
            return $current;
        }

        $report = [
            [ 'Critical Path', 'Current', [ 'role' => 'annotation' ] ],
            // Prayer
//            [ 'Prayers Network', (int) $current['prayer'], (int) $current['prayer'] ], // TODO Disabled until counting strategy is defined.
            // Outreach
//            [ 'Social Engagement', (int) $current['social_engagement'], (int) $current['social_engagement'] ], // TODO Disabled until counting strategy is defined.
//            [ 'Website Visitors', (int) $current['website_visitors'], (int) $current['website_visitors'] ], // TODO Disabled until counting strategy is defined.
            // Follow-up
            [ 'New Contacts', (int) $current['new_contacts'], (int) $current['new_contacts'] ],
            [ 'Contacts Attempted', (int) $current['contacts_attempted'], (int) $current['contacts_attempted'] ],
            [ 'Contacts Established', (int) $current['contacts_established'], (int) $current['contacts_established'] ],
            [ 'First Meetings', (int) $current['first_meetings'], (int) $current['first_meetings'] ],
            // Multiplication
            [ 'Baptisms', (int) $current['baptisms'], (int) $current['baptisms'] ],
            [ 'Baptizers', (int) $current['baptizers'], (int) $current['baptizers'] ],
            [ 'Active Groups', (int) $current['active_groups'], (int) $current['active_groups'] ],
            [ 'Active Churches', (int) $current['active_churches'], (int) $current['active_churches'] ],
            [ 'Church Planters', (int) $current['church_planters'], (int) $current['church_planters'] ],
        ];

        if ( !empty( $report ) ) {
            return [
                'status' => true,
                'data'   => [
                    'chart' => $report,
                    'timestamp' => $current['timestamp'],
                    ]
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'Failed to build critical path data.',
            ];
        }
    }

    /**
     * @param $check_permissions
     *
     * @return array|\WP_Error
     */
    public static function chart_critical_path_prayer( $check_permissions )
    {

        $current_user = get_current_user();
        if ( $check_permissions && !self::can_view( 'critical_path', $current_user ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read contact" ), [ 'status' => 403 ] );
        }

        $current['prayer'] = 1000; // TODO build live report

        $report = [
            [ 'Prayer', 'Current' ],
            [ 'Prayers Network', (int) $current['prayer'] ],
        ];

        // Check for goals
        $has_goals = true; // TODO check site options to see if they have goals
        if ( $has_goals ) {

            $goal['prayer'] = 1100;

            array_push( $report[0], 'Goal' );
            array_push( $report[1], $goal['prayer'] );
        }

        if ( !empty( $report ) ) {
            return [
                'status' => true,
                'data'   => $report,
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'Failed to build critical path data.',
            ];
        }
    }

    /**
     * @param $check_permissions
     *
     * @return array|\WP_Error
     */
    public static function chart_critical_path_outreach( $check_permissions )
    {

        $current_user = get_current_user();
        if ( $check_permissions && !self::can_view( 'critical_path', $current_user ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read contact" ), [ 'status' => 403 ] );
        }

        $current['social_engagement'] = 30000; // TODO replace with calculated data
        $current['website_visitors'] = 40000; // TODO replace with calculated data

        $report = [
            [ 'Outreach', 'Current' ],
            [ 'Social Engagement', (int) $current['social_engagement'] ],
            [ 'Website Visitors', (int) $current['website_visitors'] ],
        ];

        // Check for goals
        $has_goals = true; // TODO check site options to see if they have goals
        if ( $has_goals ) {

            $goal['social_engagement'] = (int) 350000; // TODO replace with calculated data
            $goal['website_visitors'] = (int) 400000; // TODO replace with calculated data

            array_push( $report[0], 'Goal' );
            array_push( $report[1], $goal['social_engagement'] );
            array_push( $report[2], $goal['website_visitors'] );
        }

        if ( !empty( $report ) ) {
            return [
                'status' => true,
                'data'   => $report,
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'Failed to build critical path data.',
            ];
        }
    }

    /**
     * @param $check_permissions
     *
     * @return array|\WP_Error
     */
    public static function chart_critical_path_fup( $check_permissions )
    {

        $current_user = get_current_user();
        if ( $check_permissions && !self::can_view( 'critical_path', $current_user ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read contact" ), [ 'status' => 403 ] );
        }

        $current['new_contacts'] = disciple_tools()->counter->contacts_post_status( 'publish' );
        $current['contacts_attempted'] = disciple_tools()->counter->contacts_meta_counter( 'seeker_path', 'attempted' );
        $current['contacts_established'] = disciple_tools()->counter->contacts_meta_counter( 'seeker_path', 'established' );
        $current['first_meetings'] = disciple_tools()->counter->contacts_meta_counter( 'seeker_path', 'met' );

        $report = [
            [ 'Followup', 'Current' ],
            [ 'New Contacts', (int) $current['new_contacts'] ],
            [ 'Contacts Attempted', (int) $current['contacts_attempted'] ],
            [ 'Contacts Established', (int) $current['contacts_established'] ],
            [ 'First Meetings', (int) $current['first_meetings'] ],
        ];

        // Check for goals
        $has_goals = true; // TODO check site options to see if they have goals
        if ( $has_goals ) {

            $goal['new_contacts'] = (int) 400; // TODO replace with calculated data
            $goal['contacts_attempted'] = (int) 380; // TODO replace with calculated data
            $goal['contacts_established'] = (int) 200; // TODO replace with calculated data
            $goal['first_meetings'] = (int) 100; // TODO replace with calculated data

            array_push( $report[0], 'Goal' );
            array_push( $report[1], $goal['new_contacts'] );
            array_push( $report[2], $goal['contacts_attempted'] );
            array_push( $report[3], $goal['contacts_established'] );
            array_push( $report[4], $goal['first_meetings'] );
        }

        if ( !empty( $report ) ) {
            return [
                'status' => true,
                'data'   => $report,
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'Failed to build critical path data.',
            ];
        }
    }

    /**
     * @param $check_permissions
     *
     * @return array|\WP_Error
     */
    public static function chart_critical_path_multiplication( $check_permissions )
    {

        $current_user = get_current_user();
        if ( $check_permissions && !self::can_view( 'critical_path', $current_user ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read contact" ), [ 'status' => 403 ] );
        }

        $current['baptisms'] = disciple_tools()->counter->get_baptisms( 'baptisms' );
        $current['baptizers'] = disciple_tools()->counter->get_baptisms( 'baptizers' );
        $current['active_churches'] = disciple_tools()->counter->groups_meta_counter( 'is_church', '1' );
        $current['church_planters'] = disciple_tools()->counter->connection_type_counter( 'participation', 'Planting' );

        $report = [
            [ 'Multiplication', 'Current' ],
            [ 'Baptisms', (int) $current['baptisms'] ],
            [ 'Baptizers', (int) $current['baptizers'] ],
            [ 'Active Churches', (int) $current['active_churches'] ],
            [ 'Church Planters', (int) $current['church_planters'] ],
        ];

        // Check for goals
        $has_goals = true; // TODO check site options to see if they have goals
        if ( $has_goals ) {

            $goal['baptisms'] = (int) 40; // TODO replace with calculated data
            $goal['baptizers'] = (int) 35; // TODO replace with calculated data
            $goal['active_churches'] = (int) 20; // TODO replace with calculated data
            $goal['church_planters'] = (int) 5; // TODO replace with calculated data

            array_push( $report[0], 'Goal' );
            array_push( $report[1], $goal['baptisms'] );
            array_push( $report[2], $goal['baptizers'] );
            array_push( $report[3], $goal['active_churches'] );
            array_push( $report[4], $goal['church_planters'] );
        }

        if ( !empty( $report ) ) {
            return [
                'status' => true,
                'data'   => $report,
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'Failed to build critical path data.',
            ];
        }
    }

    /**
     * @param $check_permissions
     *
     * @return array|\WP_Error
     */
    public static function chart_my_contacts_progress( $check_permissions )
    {

        $current_user = get_current_user();
        if ( $check_permissions && !self::can_view( 'critical_path', $current_user ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read contact" ), [ 'status' => 403 ] );
        }

        $current['baptisms'] = disciple_tools()->counter->get_baptisms( 'baptisms' );
        $current['baptizers'] = disciple_tools()->counter->get_baptisms( 'baptizers' );
        $current['active_churches'] = disciple_tools()->counter->groups_meta_counter( 'is_church', '1' );
        $current['church_planters'] = disciple_tools()->counter->connection_type_counter( 'participation', 'Planting' );

        $report = [
            [ 'Multiplication', 'Current' ],
            [ 'Baptisms', (int) $current['baptisms'] ],
            [ 'Baptizers', (int) $current['baptizers'] ],
            [ 'Active Churches', (int) $current['active_churches'] ],
            [ 'Church Planters', (int) $current['church_planters'] ],
        ];

        // Check for goals
        $has_goals = true; // TODO check site options to see if they have goals
        if ( $has_goals ) {

            $goal['baptisms'] = (int) 40; // TODO replace with calculated data
            $goal['baptizers'] = (int) 35; // TODO replace with calculated data
            $goal['active_churches'] = (int) 20; // TODO replace with calculated data
            $goal['church_planters'] = (int) 5; // TODO replace with calculated data

            array_push( $report[0], 'Goal' );
            array_push( $report[1], $goal['baptisms'] );
            array_push( $report[2], $goal['baptizers'] );
            array_push( $report[3], $goal['active_churches'] );
            array_push( $report[4], $goal['church_planters'] );
        }

        if ( !empty( $report ) ) {
            return [
                'status' => true,
                'data'   => $report,
            ];
        } else {
            return [
                'status'  => false,
                'message' => 'Failed to build critical path data.',
            ];
        }
    }

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

    /**
     * Check permissions for if the user can view a certain report
     *
     * @param $report_name
     * @param $user_id
     *
     * @return bool
     */
    public static function can_view( $report_name, $user_id )
    {
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

    /**
     * System stats dashboard widget
     * TODO: Deprecate and remove
     *
     * @since  0.1.0
     * @access public
     */
    public function temp_delete_system_stats_widget()
    {

        // Build counters
        $system_users = count_users();
        $dispatchers = $system_users['avail_roles']['dispatcher'];
        $marketers = $system_users['avail_roles']['marketer'];
        $multipliers = $system_users['avail_roles']['multiplier'];
        $multiplier_leader = $system_users['avail_roles']['multiplier_leader'];
        $prayer_supporters = $system_users['avail_roles']['prayer_supporter'];
        $project_supporters = $system_users['avail_roles']['project_supporter'];
        $registered = $system_users['avail_roles']['registered'];

        $monitored_websites = 'x';
        $monitored_facebook_pages = 'x';

        $comments = wp_count_comments();
        $comments = $comments->total_comments;

        $comments_for_dispatcher = 'x';

        // Build variables
        $mailchimp_subscribers = disciple_tools()->logging_report_api->get_meta_key_total( '2017', 'Mailchimp', 'new_subscribers', 'max' );
        $facebook = disciple_tools()->logging_report_api->get_meta_key_total( '2017', 'Facebook', 'page_likes_count' );
        $websites = disciple_tools()->logging_report_api->get_meta_key_total( '2017', 'Analytics', 'unique_website_visitors' );

        $new_contacts = disciple_tools()->counter->contacts_post_status( 'publish' );
        $contacts_attempted = disciple_tools()->counter->contacts_meta_counter( 'seeker_path', 'attempted' );
        $contacts_established = disciple_tools()->counter->contacts_meta_counter( 'seeker_path', 'established' );
        $first_meetings = disciple_tools()->counter->contacts_meta_counter( 'seeker_path', 'met' );
        $baptisms = disciple_tools()->counter->get_baptisms( 'baptisms' );
        $baptizers = disciple_tools()->counter->get_baptisms( 'baptizers' );
        $active_churches = disciple_tools()->counter->groups_meta_counter( 'type', 'Church' );
        $church_planters = disciple_tools()->counter->connection_type_counter( 'participation', 'Planting' );


        // Build counters
        $has_at_least_1 = disciple_tools()->counter->get_generation( 'has_one_or_more' );
        $has_at_least_2 = disciple_tools()->counter->get_generation( 'has_two_or_more' );
        $has_more_than_2 = disciple_tools()->counter->get_generation( 'has_three_or_more' );

        $has_0 = disciple_tools()->counter->get_generation( 'has_0' );
        $has_1 = disciple_tools()->counter->get_generation( 'has_1' );
        $has_2 = disciple_tools()->counter->get_generation( 'has_2' );
        $has_3 = disciple_tools()->counter->get_generation( 'has_3' );

        $con_0gen = '';//disciple_tools()->counter->get_generation('at_zero');
        $con_1gen = '';//disciple_tools()->counter->get_generation('at_first');
        $con_2gen = '';//disciple_tools()->counter->get_generation('at_second');
        $con_3gen = '';//disciple_tools()->counter->get_generation('at_third');
        $con_4gen = '';//disciple_tools()->counter->get_generation('at_fourth');
        $con_5gen = '';//disciple_tools()->counter->get_generation('at_fifth');

        // Build counters
        $unassigned = disciple_tools()->counter->contacts_meta_counter( 'overall_status', 'unassigned' );

        $assigned_inquirers = disciple_tools()->counter->contacts_meta_counter( 'overall_status', 'assigned' );
        $active_inquirers = disciple_tools()->counter->contacts_meta_counter( 'overall_status', 'active' );
        $contact_attempted = disciple_tools()->counter->contacts_meta_counter( 'seeker_path', 'Contact Attempted' );
        $contact_established = disciple_tools()->counter->contacts_meta_counter( 'seeker_path', 'Contact Established' );
        $meeting_scheduled = disciple_tools()->counter->contacts_meta_counter( 'seeker_path', 'Meeting Scheduled' );
        $first_meeting_complete = disciple_tools()->counter->contacts_meta_counter( 'seeker_path', 'First Meeting Complete' );
        $ongoing_meetings = disciple_tools()->counter->contacts_meta_counter( 'seeker_path', 'Ongoing Meetings' );

        // Build counters
        $has_at_least_1 = disciple_tools()->counter->get_generation( 'has_one_or_more', 'groups' );
        $has_at_least_2 = disciple_tools()->counter->get_generation( 'has_two_or_more', 'groups' );
        $has_more_than_2 = disciple_tools()->counter->get_generation( 'has_three_or_more', 'groups' );

        $has_0 = disciple_tools()->counter->get_generation( 'has_0', 'groups' );
        $has_1 = disciple_tools()->counter->get_generation( 'has_1', 'groups' );
        $has_2 = disciple_tools()->counter->get_generation( 'has_2', 'groups' );
        $has_3 = disciple_tools()->counter->get_generation( 'has_3', 'groups' );

        $gr_0gen = '';//disciple_tools()->counter->get_generation('at_zero', 'groups');
        $gr_1gen = '';//disciple_tools()->counter->get_generation('at_first', 'groups');
        $gr_2gen = '';//disciple_tools()->counter->get_generation('at_second', 'groups');
        $gr_3gen = '';//disciple_tools()->counter->get_generation('at_third', 'groups');
        $gr_4gen = '';//disciple_tools()->counter->get_generation('at_fourth', 'groups');

        $dbs = disciple_tools()->counter->groups_meta_counter( 'type', 'DBS' );
        $active_churches = disciple_tools()->counter->groups_meta_counter( 'type', 'Church' );

        // Build counters
        $has_at_least_1 = disciple_tools()->counter->get_generation( 'has_one_or_more', 'baptisms' );
        $has_at_least_2 = disciple_tools()->counter->get_generation( 'has_two_or_more', 'baptisms' );
        $has_more_than_2 = disciple_tools()->counter->get_generation( 'has_three_or_more', 'baptisms' );

        $has_0 = disciple_tools()->counter->get_generation( 'has_0', 'baptisms' );
        $has_1 = disciple_tools()->counter->get_generation( 'has_1', 'baptisms' );
        $has_2 = disciple_tools()->counter->get_generation( 'has_2', 'baptisms' );
        $has_3 = disciple_tools()->counter->get_generation( 'has_3', 'baptisms' );

        $con_0gen = '';//disciple_tools()->counter->get_generation('at_zero', 'baptisms');
        $con_1gen = '';//disciple_tools()->counter->get_generation('at_first', 'baptisms');
        $con_2gen = '';//disciple_tools()->counter->get_generation('at_second', 'baptisms');
        $con_3gen = '';//disciple_tools()->counter->get_generation('at_third', 'baptisms');
        $con_4gen = '';//disciple_tools()->counter->get_generation('at_fourth', 'baptisms');
        $con_5gen = '';//disciple_tools()->counter->get_generation('at_fifth', 'baptisms');

        $baptisms = disciple_tools()->counter->get_baptisms( 'baptisms' );
        $baptizers = disciple_tools()->counter->get_baptisms( 'baptizers' );
    }
}

abstract class Disciple_Tools_Metrics_Hooks_Base
{
    public function __construct() {}

}