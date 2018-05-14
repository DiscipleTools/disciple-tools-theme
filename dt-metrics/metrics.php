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
        $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );
        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_google' ], 10 );

            if ( user_can( get_current_user_id(), 'manage_dt' ) ) {

                // load basic charts
                require_once( get_template_directory() . '/dt-metrics/metrics-personal.php' );
                require_once( get_template_directory() . '/dt-metrics/metrics-project.php' );
                require_once( get_template_directory() . '/dt-metrics/metrics-users.php' );
            }
        }
    }

    // Enqueue maps and charts for standard metrics
    public function enqueue_google() {
        wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', [], false );
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

    /**
     * MY METRICS
     */

    public static function chart_my_hero_stats( $user_id = null )
    {
        if ( is_null( $user_id ) || empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $results = self::query_my_hero_stats( $user_id );

        $chart = [
            'contacts' => $results['contacts'],
            'needs_accept' => $results['needs_accept'],
            'needs_update' => $results['needs_update'],
            'groups' => $results['groups'],
        ];

        return $chart;
    }

    public static function chart_contacts_progress( $type = 'personal' )
    {
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
                $results = self::query_project_contacts_progress();

                $chart[] = [ 'Step', 'Contacts', [ 'role' => 'annotation' ] ];

                foreach ( $results as $value ) {
                    $chart[] = [ $value['label'], $value['count'], $value['count'] ];
                }
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

        switch ( $type ) {
            case 'personal':
                $results = self::query_my_group_types();
                $chart[] = [ 'Group Type', 'Number' ];
                foreach ( $results as $result ) {
                    $chart[] = [ $result['type'], intval( $result['count'] ) ];
                }
                break;
            case 'project':
                $results = self::query_project_group_types();
                $chart[] = [ 'Group Type', 'Number' ];
                foreach ( $results as $result ) {
                    $chart[] = [ $result['type'], intval( $result['count'] ) ];
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

        $default_key_list = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();

        $default_chart = [
            [ 'Step', 'Groups', [ 'role' => 'annotation' ] ],
            [ 'Fellowship', 0, 0 ],
            [ 'Giving', 0, 0 ],
            [ 'Communion', 0, 0 ],
            [ 'Baptism', 0, 0 ],
            [ 'Prayer', 0, 0 ],
            [ 'Leaders', 0, 0 ],
            [ 'Word', 0, 0 ],
            [ 'Praise', 0, 0 ],
            [ 'Evangelism', 0, 0 ],
            [ 'Covenant', 0, 0 ],
        ];

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

        if ( ! $results ) {
            $chart = $default_chart;
        }
        else {
            $chart[] = [ 'Step', 'Groups', [ 'role' => 'annotation' ] ];

            foreach ( $results as $result ) {
                if ( isset( $default_key_list[ $result['health_key'] ] ) ) {
                    $value = intval( $result['out_of'] ) - intval( $result['count'] );
                    $chart[] = [ $default_key_list[ $result['health_key'] ]['name'], intval( $value ), intval( $value ) ];
                }
            }
        }

        return $chart;
    }

    public static function chart_group_generations( $type = 'personal' ) {
        switch ( $type ) {
            case 'personal':
                $results = [];
                break;
            case 'project':
                $results = [];
                break;
            default:
                $results = [];
                break;
        }
        $default = [
            [ 'Generation', 'Pre-Group', 'Group', 'Church', [ 'role' => 'annotation' ] ],
            [ '1st Gen', 0, 0, 0, 0 ],
            [ '2st Gen', 0, 0, 0, 0 ],
            [ '3st Gen', 0, 0, 0, 0 ],
            [ '4st Gen', 0, 0, 0, 0 ],
            [ '5+ Gen', 0, 0, 0, 0 ],
        ];

        return wp_parse_args( $results, $default );
    }


    /**
     * PROJECT METRICS
     */

    public static function chart_project_hero_stats()
    {
        $default = [
            'total_contacts' => 0,
            'needs_accepted' => 0,
            'updates_needed' => 0,
            'total_groups' => 0,
            'needs_training' => 0,
            'generations' => 0,
        ];

        $results = [
            'total_contacts' => 0,
            'needs_accepted' => 0,
            'updates_needed' => 0,
            'total_groups' => 0,
            'needs_training' => 0,
            'generations' => 0,
        ];

        return wp_parse_args( $results, $default );
    }


    public static function chart_project_critical_path()
    {
        $default = [
            [ 'Step', 'Contacts', [ 'role' => 'annotation' ] ],
            [ 'New Contacts', 100, 100 ],
            [ 'Contacts Attempted', 95, 95 ],
            [ 'First Meetings', 80, 80 ],
            [ 'All Baptisms', 6, 6 ],
            [ '1st Gen', 4, 4 ],
            [ '2nd Gen', 2, 2 ],
            [ '3rd Gen', 0, 0 ],
            [ '4th Gen', 0, 0 ],
            [ 'Baptizers', 3, 3 ],
            [ 'Church Planters', 4, 4 ],
            [ 'All Groups', 4, 4 ],
            [ 'Active Pre-Groups', 4, 4 ],
            [ 'Active Groups', 4, 4 ],
            [ 'Active Churches', 5, 5 ],
            [ '1st Gen', 3, 3 ],
            [ '2nd Gen', 2, 2 ],
            [ '3rd Gen', 0, 0 ],
            [ '4th Gen', 0, 0 ],

        ];

        $results = [];

        return wp_parse_args( $results, $default );
    }

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

    public static function chart_user_contacts_per_user() {

        $default = [
            [ 'User', 'Active Contacts', 'Attempt Needed', 'Attempted', 'Established', '1st Scheduled', '1st Complete', 'Ongoing', 'Being Coached' ],
            [ 'Chris', 100, 4, 0, 4, 8, 0, 0, 9 ],
            [ 'Kara', 100, 4, 0, 4, 8, 0, 0, 9 ],
        ];

        $results = [];

        return wp_parse_args( $results, $default );
    }

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

        $defaults = [];
        $status = dt_get_option( 'seeker_path' );
        foreach ( $status as $key => $label ) {
            $defaults[$key] = [
                'label' => $label,
                'count' => 0,
            ];
        }

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
             GROUP BY b.meta_value
        ", ARRAY_A );

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
               JOIN $wpdb->postmeta as b
                 ON a.ID=b.post_id
                    AND b.meta_key = 'assigned_to'
                    AND b.meta_value = CONCAT( 'user-', %s )
             WHERE a.post_status = 'publish'
              AND a.post_type = 'contacts')
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
                     AND a.post_type = 'contacts')
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
                     AND a.post_type = 'contacts')
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
                as groups
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

}