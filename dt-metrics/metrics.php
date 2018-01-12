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

    /**
     * Disciple_Tools_Admin_Menus The single instance of Disciple_Tools_Admin_Menus.
     *
     * @var    object
     * @access   private
     * @since    0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Admin_Menus Instance
     * Ensures only one instance of Disciple_Tools_Admin_Menus is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Metrics instance
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

    } // End __construct()

    /**
     * Get the critical path in an array
     * This function builds the caching layer to the critical path data. Often this data will not change rapidly,
     * so it a good candidate for transient caching
     *
     * @return array|WP_Error
     */
    public static function get_critical_path(): array {
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
    public static function critical_path_chart_data( $check_permissions )
    {

        $current_user = get_current_user();
        if ( $check_permissions && !self::can_view( 'critical_path', $current_user ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read contact" ), [ 'status' => 403 ] );
        }

        // Check for transient cache
        $current = self::get_critical_path();
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
    public static function critical_path_prayer( $check_permissions )
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
    public static function critical_path_outreach( $check_permissions )
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
    public static function critical_path_fup( $check_permissions )
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
    public static function critical_path_multiplication( $check_permissions )
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
     * TODO: Deprecate and Remove Functions Below
     */

    /**
     * System stats dashboard widget
     * TODO: Deprecate and remove
     *
     * @since  0.1.0
     * @access public
     */
    public function system_stats_widget()
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

        ?>
        <table class="widefat striped ">
            <thead>
            <tr>
                <th>Name</th>
                <th>Progress</th>

            </tr>
            </thead>
            <tbody>
            <tr>
                <td>System Users</td>
                <td><?php echo esc_html( $system_users['total_users'] ); ?></td>
            </tr>
            <tr>
                <td>Dispatchers</td>
                <td><?php echo esc_html( $dispatchers ); ?></td>
            </tr>
            <tr>
                <td>Marketers</td>
                <td><?php echo esc_html( $marketers ); ?></td>
            </tr>
            <tr>
                <td>Multipliers</td>
                <td><?php echo esc_html( $multipliers ); ?></td>
            </tr>
            <tr>
                <td>Multiplier Leaders</td>
                <td><?php echo esc_html( $multiplier_leader ); ?></td>
            </tr>
            <tr>
                <td>Prayer Supporters</td>
                <td><?php echo esc_html( $prayer_supporters ); ?></td>
            </tr>
            <tr>
                <td>Project Supporters</td>
                <td><?php echo esc_html( $project_supporters ); ?></td>
            </tr>
            <tr>
                <td>Registered</td>
                <td><?php echo esc_html( $registered ); ?></td>
            </tr>
            <tr>
                <td>Monitored Websites</td>
                <td><?php echo esc_html( $monitored_websites ); ?></td>
            </tr>
            <tr>
                <td>Monitored Facebook</td>
                <td><?php echo esc_html( $monitored_facebook_pages ); ?></td>
            </tr>
            <tr>
                <td>Comments</td>
                <td><?php echo esc_html( $comments ); ?></td>
            </tr>
            <tr>
                <td>Comments for @dispatcher</td>
                <td><?php echo esc_html( $comments_for_dispatcher ); ?></td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Movement funnel path dashboard widget
     *TODO: Deprecate and remove
     *
     * @since  0.1.0
     * @access public
     */
    public function critical_path_stats()
    {
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

        ?>
        <table class="widefat striped ">
            <thead>
            <tr>
                <th>Name</th>
                <th>Progress</th>

            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Prayers Network</td>
                <td><?php echo esc_html( $mailchimp_subscribers ); ?></td>

            </tr>
            <tr>
                <td>Social Engagement</td>
                <td><?php echo esc_html( $facebook ); ?></td>

            </tr>
            <tr>
                <td>Website Visitors</td>
                <td><?php echo esc_html( $websites ); ?></td>

            </tr>
            <tr>
                <td>New Contacts</td>
                <td><?php echo esc_html( $new_contacts ); ?></td>
            </tr>
            <tr>
                <td>Contact Attempted</td>
                <td><?php echo esc_html( $contacts_attempted ); ?></td>
            </tr>
            <tr>
                <td>Contact Established</td>
                <td><?php echo esc_html( $contacts_established ); ?></td>
            </tr>
            <tr>
                <td>First Meeting Complete</td>
                <td><?php echo esc_html( $first_meetings ); ?></td>
            </tr>
            <tr>
                <td>Baptisms</td>
                <td><?php echo esc_html( $baptisms ); ?></td>
            </tr>
            <tr>
                <td>Baptizers</td>
                <td><?php echo esc_html( $baptizers ); ?></td>
            </tr>
            <tr>
                <td>Active Churches</td>
                <td><?php echo esc_html( $active_churches ); ?></td>
            </tr>
            <tr>
                <td>Church Planters</td>
                <td><?php echo esc_html( $church_planters ); ?></td>
            </tr>

            </tbody>
        </table>
        <?php
    }

    /**
     * Contacts stats widget
     *TODO: Deprecate and remove
     *
     * @since  0.1.0
     * @access public
     */
    public function contacts_stats_widget()
    {

        //        print '<pre>'; print_r( disciple_tools()->counter->get_generation('generation_list') ); print '</pre>';

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

        ?>
        <table class="widefat striped ">
            <thead>
            <tr>
                <th>Name</th>
                <th>Count</th>
                <th>Name</th>
                <th>Count</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><strong>SEEKER MILESTONES</strong></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Published Contacts / New Inquirers</td>
<!--                <td>--><?php //echo esc_html( $contacts_count->publish ); ?><!--</td>-->
                <td>Contact Established</td>
                <td><?php echo esc_html( $contact_established ); ?></td>
            </tr>
            <tr>
                <td>Unassigned</td>
                <td><?php echo esc_html( $unassigned ); ?></td>
                <td>Meeting Scheduled</td>
                <td><?php echo esc_html( $meeting_scheduled ); ?></td>
            </tr>
            <tr>
                <td>Assigned Inquirers</td>
                <td><?php echo esc_html( $assigned_inquirers ); ?></td>
                <td>First Meeting Complete</td>
                <td><?php echo esc_html( $first_meeting_complete ); ?></td>
            </tr>
            <tr>
                <td>Active</td>
                <td><?php echo esc_html( $active_inquirers ); ?></td>
                <td>Ongoing Meetings</td>
                <td><?php echo esc_html( $ongoing_meetings ); ?></td>
            </tr>
            <tr>
                <td>Contact Attempted</td>
                <td><?php echo esc_html( $contact_attempted ); ?></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <th><strong>HAS AT LEAST</strong></th>
                <td></td>
                <th><strong>GENERATIONS</strong></th>
                <td></td>
            </tr>
            <tr>
                <td>Has at least 1 disciple</td>
                <td><?php echo esc_html( $has_at_least_1 ); ?></td>
                <td>Zero Gen</td>
                <td><?php echo esc_html( $con_0gen ); ?></td>
            </tr>
            <tr>
                <td>Has at least 2 disciples</td>
                <td><?php echo esc_html( $has_at_least_2 ); ?></td>
                <td>1st Gen</td>
                <td><?php echo esc_html( $con_1gen ); ?></td>
            </tr>
            <tr>
                <td>Has more than 2 disciples</td>
                <td><?php echo esc_html( $has_more_than_2 ); ?></td>
                <td>2nd Gen</td>
                <td><?php echo esc_html( $con_2gen ); ?></td>
            </tr>
            <tr>
                <td><strong>HAS</strong></td>
                <td></td>
                <td>3rd Gen</td>
                <td><?php echo esc_html( $con_3gen ); ?></td>
            </tr>
            <tr>
                <td>Has No Disciples</td>
                <td><?php echo esc_html( $has_0 ); ?></td>
                <td>4th Gen</td>
                <td><?php echo esc_html( $con_4gen ); ?></td>
            </tr>
            <tr>
                <td>Has 1 Disciple</td>
                <td><?php echo esc_html( $has_1 ); ?></td>
                <td>5th Gen</td>
                <td><?php echo esc_html( $con_5gen ); ?></td>
            </tr>
            <tr>
                <td>Has 2 Disciples</td>
                <td><?php echo esc_html( $has_2 ); ?></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Has 3 Disciples</td>
                <td><?php echo esc_html( $has_3 ); ?></td>
                <td></td>
                <td></td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Groups stats widget
     * TODO: Deprecate and remove
     *
     * @since  0.1.0
     * @access public
     */
    public function groups_stats_widget()
    {

        //        print '<pre>'; print_r( disciple_tools()->counter->get_generation('generation_list') ); print '</pre>';

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

        ?>
        <table class="widefat striped ">
            <thead>
            <tr>
                <th>Name</th>
                <th>Count</th>
                <th>Name</th>
                <th>Count</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th><strong>TOTALS</strong></th>
                <td></td>
                <td><strong>GENERATIONS</strong></td>
                <td></td>
            </tr>
            <tr>
                <td>2x2 or DBS Groups</td>
                <td><?php echo esc_html( $dbs ); ?></td>
                <td>Zero Gen (has no record of being planted by another group)</td>
                <td><?php echo esc_html( $gr_0gen ); ?></td>
            </tr>
            <tr>
                <td>Active Churches</td>
                <td><?php echo esc_html( $active_churches ); ?></td>
                <td>1st Gen</td>
                <td><?php echo esc_html( $gr_1gen ); ?></td>
            </tr>
            <tr>
                <th><strong>HAS AT LEAST</strong></th>
                <td></td>
                <td>2nd Gen</td>
                <td><?php echo esc_html( $gr_2gen ); ?></td>
            </tr>
            <tr>
                <td>Has planted at least 1 group</td>
                <td><?php echo esc_html( $has_at_least_1 ); ?></td>
                <td>3rd Gen</td>
                <td><?php echo esc_html( $gr_3gen ); ?></td>
            </tr>
            <tr>
                <td>Has planted at least 2 groups</td>
                <td><?php echo esc_html( $has_at_least_2 ); ?></td>
                <td>4th Gen</td>
                <td><?php echo esc_html( $gr_4gen ); ?></td>
            </tr>
            <tr>
                <td>Has planted at least 3 groups</td>
                <td><?php echo esc_html( $has_more_than_2 ); ?></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td><strong>HAS</strong></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Has Not Planted Another Group</td>
                <td><?php echo esc_html( $has_0 ); ?></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Has Planted 1 Group</td>
                <td><?php echo esc_html( $has_1 ); ?></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Has Planted 2 Groups</td>
                <td><?php echo esc_html( $has_2 ); ?></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Has Planted 3 Groups</td>
                <td><?php echo esc_html( $has_3 ); ?></td>
                <td></td>
                <td></td>
            </tr>

            </tbody>
        </table>
        <?php
    }

    /**
     * Baptism Generations stats dashboard widget
     * TODO: Deprecate and remove
     *
     * @since  0.1.0
     * @access public
     */
    public function baptism_stats_widget()
    {

        //        print '<pre>'; print_r( disciple_tools()->counter->get_generation('generation_list') ); print '</pre>';

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

        ?>
        <table class="widefat striped ">
            <thead>
            <tr>
                <th>Name</th>
                <th>Count</th>

            </tr>
            </thead>
            <tbody>
            <tr>
                <th><strong>TOTALS</strong></th>
                <td></td>
            </tr>
            <tr>
                <td>Baptisms</td>
                <td><?php echo esc_html( $baptisms ); ?></td>
            </tr>
            <tr>
                <td>Baptizers</td>
                <td><?php echo esc_html( $baptizers ); ?></td>
            </tr>
            <tr>
                <th><strong>HAS AT LEAST</strong></th>
                <td></td>
            </tr>
            <tr>
                <td>Has baptized at least 1 disciple</td>
                <td><?php echo esc_html( $has_at_least_1 ); ?></td>
            </tr>
            <tr>
                <td>Has baptized at least 2 disciples</td>
                <td><?php echo esc_html( $has_at_least_2 ); ?></td>
            </tr>
            <tr>
                <td>Has baptized more than 2 disciples</td>
                <td><?php echo esc_html( $has_more_than_2 ); ?></td>
            </tr>
            <tr>
                <td><strong>HAS</strong></td>
                <td></td>
            </tr>
            <tr>
                <td>Has not baptized anyone</td>
                <td><?php echo esc_html( $has_0 ); ?></td>
            </tr>
            <tr>
                <td>Has baptized 1</td>
                <td><?php echo esc_html( $has_1 ); ?></td>
            </tr>
            <tr>
                <td>Has baptized 2</td>
                <td><?php echo esc_html( $has_2 ); ?></td>
            </tr>
            <tr>
                <td>Has baptized 3</td>
                <td><?php echo esc_html( $has_3 ); ?></td>
            </tr>
            <tr>
                <th><strong>BAPTISM GENERATIONS</strong></th>
                <td></td>
            </tr>
            <tr>
                <td>Zero Gen</td>
                <td><?php echo esc_html( $con_0gen ); ?></td>
            </tr>
            <tr>
                <td>1st Gen</td>
                <td><?php echo esc_html( $con_1gen ); ?></td>
            </tr>
            <tr>
                <td>2nd Gen</td>
                <td><?php echo esc_html( $con_2gen ); ?></td>
            </tr>
            <tr>
                <td>3rd Gen</td>
                <td><?php echo esc_html( $con_3gen ); ?></td>
            </tr>
            <tr>
                <td>4th Gen</td>
                <td><?php echo esc_html( $con_4gen ); ?></td>
            </tr>
            <tr>
                <td>5th Gen</td>
                <td><?php echo esc_html( $con_5gen ); ?></td>
            </tr>

            </tbody>
        </table>
        <?php
    }

}
