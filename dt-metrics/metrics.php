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
        $raw_connections = Disciple_Tools_Metrics_Hooks_Base::query_get_group_generations();
        $generation_tree = Disciple_Tools_Counter_Base::build_generation_tree( $raw_connections );
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

    public static function chart_my_hero_stats( $user_id = null )
    {
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
                        $chart[] = [ $v_label, intval( $value ), intval( $value ) ];
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
            $chart[] = [ $v_label, $out_of, $out_of ];
        }

        array_unshift( $chart, [ 'Step', 'Groups', [ 'role' => 'annotation' ] ] ); // add top row

        return $chart;
    }

    public static function chart_group_generations( $type = 'personal' ) {

        switch ( $type ) {
            case 'personal':
                $raw_connections = Disciple_Tools_Metrics_Hooks_Base::query_my_group_generations();
                $generation_tree = self::build_group_generation_counts( $raw_connections );
                break;
            case 'project':
                $raw_connections = Disciple_Tools_Metrics_Hooks_Base::query_get_group_generations();
                $generation_tree = self::build_group_generation_counts( $raw_connections );
                break;
            default:
                $generation_tree = [ "Generations", "Pre-Group", "Group", "Church", [ "role" => "Annotation" ] ];
                break;
        }

        array_unshift( $generation_tree, [ "Generations", "Pre-Group", "Group", "Church", [ "role" => "Annotation" ] ] );

        return $generation_tree;
    }

    public static function build_group_generation_counts( array $elements, $parent_id = 0, $generation = 0, $counts = [] ) {

        $generation++;
        if ( !isset( $counts[$generation] ) ){
            $counts[$generation] = [ (string) $generation , 0, 0, 0, 0 ];
        }
        foreach ($elements as $element) {

            if ($element['parent_id'] == $parent_id) {
                if ( $element["group_status"] === "active" ){
                    if ( $element["group_type"] === "pre-group" ){
                        $counts[ $generation ][1]++;
                    } elseif ( $element["group_type"] === "group" ){
                        $counts[ $generation ][2]++;
                    } elseif ( $element["group_type"] === "church" ){
                        $counts[ $generation ][3]++;
                    }
                    $counts[ $generation ][4]++;
                }
                $counts = self::build_group_generation_counts( $elements, $element['id'], $generation, $counts );
            }
        }

        return $counts;
    }



    public static function query_get_contact_generations() { // @todo
        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT
              a.ID as id,
              0 as parent_id
            FROM $wpdb->posts as a
            JOIN $wpdb->postmeta as c
              ON a.ID = c.post_id
              AND c.meta_key = 'baptism_date'
              AND c.meta_value >= '2018'
              AND c.meta_value < '2019'
            WHERE a.post_type = 'contacts'
              AND a.post_status = 'publish'
              AND a.ID IN (
                SELECT DISTINCT( p2p_from )
                FROM $wpdb->p2p
                WHERE p2p_type = 'baptizer_to_baptized'
                  AND p2p_from NOT IN (
                    SELECT DISTINCT( p2p_to )
                    FROM $wpdb->p2p
                    WHERE p2p_type = 'baptizer_to_baptized'
                ))
            UNION
            SELECT
              p.p2p_from as id,
              p.p2p_to as parent_id
            FROM $wpdb->p2p as p
            WHERE p.p2p_type = 'baptizer_to_baptized'
              AND p2p_to IN (
              SELECT
                t.ID
              FROM $wpdb->posts as t
                JOIN $wpdb->postmeta as e
                  ON t.ID = e.post_id
                     AND e.meta_key = 'baptism_date'
                     AND e.meta_value >= '2018'
                     AND e.meta_value < '2019'
              WHERE t.post_type = 'contacts'
                    AND t.post_status = 'publish'
            );
        ", ARRAY_A );

        return $results;
    }

    public static function query_get_baptisms_id_list( $year = null ) { // @todo
        global $wpdb;

        if ( empty( $year ) ) {
            $year = date( 'Y' ); // default to this year
        }

        $next_year = $year + 1;

        $results = $wpdb->get_col( $wpdb->prepare( "
            SELECT
              a.ID
            FROM $wpdb->posts as a
              JOIN $wpdb->postmeta as c
                ON a.ID = c.post_id
                   AND c.meta_key = 'baptism_date'
                   AND c.meta_value >= %d
                   AND c.meta_value < %d
            WHERE a.post_type = 'contacts'
                  AND a.post_status = 'publish'
        ", $year, $next_year ) );

        return $results;
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

    public static function chart_project_hero_stats()
    {
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
            'active_contacts' => $stats['active_contacts'],
            'needs_accepted' => $stats['needs_accept'],
            'updates_needed' => $stats['needs_update'],
            'total_groups' => $stats['groups'],
            'needs_training' => $needs_training,
            'generations' => 0,
        ];

        return $results;
    }



    /************************************************************************************************************
     * CRITICAL PATH
     */

    public static function chart_critical_path( $year = null )
    {
        $chart = [];

        if ( empty( $year ) ) {
            $year = date( 'Y' ); // default to this year
        }

        // Follow-up Steps
        $results = self::query_project_critical_path( $year );
        foreach ( $results as $key => $value ) {
            $new_key = ucwords( str_replace( '_', ' ', $key ) );
            $chart[] = [ $new_key, intval( $value ), intval( $value ) ];
        }

        /**
         * Filter chart array before sending to enqueue.
         */
        $chart = apply_filters( 'dt_chart_critical_path', $chart );

        array_unshift( $chart, [ 'Step', 'Contacts', [ 'role' => 'annotation' ] ] ); // add google chart headers

        return $chart;
    }

    public static function query_project_critical_path( $year = null ) {
        global $wpdb;
        $numbers = [];

        if ( is_null( $year ) ) {
            $year = date( 'Y' ); // default to this year
            $year = (int) $year;
        }

        $next_year = (int) $year + 1;

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
                (
                SELECT count(ID) as count
                FROM $wpdb->posts
                WHERE post_type = 'contacts'
                  AND post_status = 'publish'
                  AND post_date >= %s
                  AND post_date < %s
                  AND ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'corresponds_to_user'
                      AND meta_value != 0
                    GROUP BY post_id
                )
                ) as new_inquirers,
                ( SELECT
                count(a.ID)  as count
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                ON a.ID = b.post_id
                   AND b.meta_key = 'seeker_path'
                   AND ( b.meta_value = 'met' OR b.meta_value = 'ongoing' OR b.meta_value = 'coaching' )
                WHERE a.post_status = 'publish'
                  AND a.post_type = 'contacts'
                  AND post_date >= %s
                  AND post_date < %s
                  AND a.ID NOT IN (
                SELECT post_id
                FROM $wpdb->postmeta
                WHERE meta_key = 'corresponds_to_user'
                    AND meta_value != 0
                GROUP BY post_id
                ) ) as first_meetings,
                ( SELECT
                count(a.ID)  as count
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                ON a.ID = b.post_id
                   AND b.meta_key = 'seeker_path'
                   AND ( b.meta_value = 'ongoing' OR b.meta_value = 'coaching' )
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
                ) ) as ongoing_meetings,
                ( SELECT
                count(a.ID) as count
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                ON a.ID = b.post_id
                   AND b.meta_key = 'baptism_date'
                   AND ( b.meta_value >= %s
                         AND  b.meta_value < %s )
                WHERE a.post_status = 'publish'
                  AND a.post_type = 'contacts'
                ) as total_baptisms,
                ( 0 ) as 1st_gen_baptisms,
                ( SELECT count(*) as count
                    FROM $wpdb->p2p
                    WHERE p2p_from IN (
                      SELECT a.ID
                      FROM $wpdb->posts as a
                        JOIN $wpdb->postmeta as b
                          ON a.ID = b.post_id
                             AND b.meta_key = 'baptism_date'
                             AND ( b.meta_value >= %s
                                   AND b.meta_value < %s )
                      WHERE a.post_status = 'publish'
                            AND a.post_type = 'contacts'
                    )
                    AND p2p_type = 'baptizer_to_baptized' ) as baptizers,
                ( SELECT count(ID) as count
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                ON a.ID = b.post_id
                   AND b.meta_key = 'group_status'
                   AND b.meta_value = 'active'
                JOIN $wpdb->postmeta as c
                ON a.ID = c.post_id
                   AND c.meta_key = 'group_type'
                   AND ( c.meta_value = 'group' OR c.meta_value = 'church' )
                WHERE post_type = 'groups'
                  AND post_status = 'publish'
                ) as total_churches_and_groups,
                ( SELECT count(ID) as count
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                ON a.ID = b.post_id
                   AND b.meta_key = 'group_status'
                   AND b.meta_value = 'active'
                JOIN $wpdb->postmeta as c
                ON a.ID = c.post_id
                   AND c.meta_key = 'group_type'
                   AND c.meta_value = 'group'
                WHERE post_type = 'groups'
                  AND post_status = 'publish'  ) as active_groups,
                ( SELECT count(ID) as count
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as b
                ON a.ID = b.post_id
                   AND b.meta_key = 'group_status'
                   AND b.meta_value = 'active'
                JOIN $wpdb->postmeta as c
                ON a.ID = c.post_id
                   AND c.meta_key = 'group_type'
                   AND c.meta_value = 'church'
                WHERE post_type = 'groups'
                  AND post_status = 'publish'  ) as active_churches,
                ( 0 ) as 1st_gen_churches,
                ( 0 ) as church_planters,
                ( SELECT count(a.ID) as count
                FROM $wpdb->posts as a
                WHERE post_type = 'peoplegroups'
                  AND post_status = 'publish'
                  AND ID IN (
                SELECT DISTINCT( p2p_to )
                FROM $wpdb->p2p
                WHERE p2p_type = 'contacts_to_peoplegroups'
                    OR p2p_type = 'groups_to_peoplegroups'
                ) ) as people_groups;
        ", $year, $next_year, $year, $next_year, $year, $next_year, $year, $next_year ), ARRAY_A );

        if ( empty( $results ) ) {
            dt_write_log( 'failed query' );
        }

        // build baptism generations
        $raw_baptism_generation_list = self::query_get_baptism_generations( $year );
        $all_baptisms = self::build_baptism_generation_counts( $raw_baptism_generation_list );
        $baptism_generations_this_year = self::build_baptism_generations_this_year( $all_baptisms, $year );

        // build group generations
        $raw_connections = self::query_get_group_generations();
        $church_generation = self::build_group_generation_counts( $raw_connections );

        foreach ( $results[0] as $key => $value ) {
            if ( '1st_gen_baptisms' === $key ) {
                foreach ( $baptism_generations_this_year as $key_bg => $value_bg ) {
                    $baptism_key = self::add_ordinal_number_suffix( $key_bg ) . '_gen_baptisms';
                    $numbers[$baptism_key] = $value_bg;
                }
            } elseif ( '1st_gen_churches' === $key ) {
                foreach ( $church_generation as $key_gc => $value_gc ) {
                    $generation_key = self::add_ordinal_number_suffix( $key_gc ) . '_gen_churches';

                    $numbers[$generation_key] = $value_gc[3];
                }
            } else {
                $numbers[$key] = $value;
            }
        }

        /**
         * Check for manual additions to critical path
         */
        $manual_additions = $wpdb->get_results($wpdb->prepare( "
            SELECT a.report_source as source,
              h.meta_value as total,
              g.meta_value as section
            FROM $wpdb->dt_reports as a
            LEFT JOIN $wpdb->dt_reportmeta as e
              ON a.id=e.report_id
                 AND e.meta_key = 'year'
            LEFT JOIN $wpdb->dt_reportmeta as h
              ON a.id=h.report_id
                 AND h.meta_key = 'total'
              LEFT JOIN $wpdb->dt_reportmeta as g
                ON a.id=g.report_id
                   AND g.meta_key = 'section'
            WHERE category = 'manual'
              AND a.id IN ( SELECT MAX( bb.report_id )
                FROM $wpdb->dt_reportmeta as bb
                  LEFT JOIN $wpdb->dt_reportmeta as d
                    ON bb.report_id=d.report_id
                       AND d.meta_key = 'source'
                  LEFT JOIN $wpdb->dt_reportmeta as e
                    ON bb.report_id=e.report_id
                       AND e.meta_key = 'year'
                WHERE bb.meta_key = 'submit_date'
                GROUP BY d.meta_value, e.meta_value
              )
            AND e.meta_value = %s
            ", $year ), ARRAY_A );

        if ( ! empty( $manual_additions ) ) {
            // get source order
            $sources = get_option( 'dt_critical_path_sources', [] );

            foreach ( $sources as $source ) { // loop sources in order
                foreach ( $manual_additions as $mkey => $mvalue ) { // loop and match manual additions
                    if ( $source['key'] == $mvalue['source'] ) {
                        if ( 'before' == $mvalue['section'] ) {
                            $array_temp[ $mvalue['source'] ] = (int) $mvalue['total'];
                            $numbers = array_merge( $array_temp, $numbers );
                        } else {
                            $numbers[ $mvalue['source'] ] = (int) $mvalue['total'];
                        }
                    }
                }
            }
        }
//        dt_write_log( $numbers );

        return $numbers;
    }

    /**
     * Returns array with index of generation and count of baptisms as the value
     *
     * @param      $all_baptisms
     * @param null $year
     *
     * @return array
     */
    public static function build_baptism_generations_this_year( $all_baptisms, $year = null ) {

        $count = [];
        foreach ( $all_baptisms as $k => $v ) {
            $count[$k] = 0;
        }

        if ( is_null( $year ) ) {
            $year = date( 'Y' ); // default to this year
            $year = (int) $year;
        }

        // get master list of ids for baptisms this year
        $list = self::query_get_baptisms_id_list( $year );

        // redact counts according to baptisms this year
        foreach ( $list as $baptism ) {
            foreach ( $all_baptisms as $generation ) {
                if ( in_array( $baptism, $generation[2] ) ) {
                    if ( ! isset( $count[ $generation[0] ] ) ) {
                        $count[ $generation[0] ] = 0;
                    }
                    $count[ $generation[0] ]++;
                }
            }
        }
        if ( isset( $count[0] ) ) {
            unset( $count[0] );
        }

        // return counts
        return $count;
    }

    public static function query_get_baptism_generations( $year = null ) {
        global $wpdb;

        $results = $wpdb->get_results(  "
            SELECT
              a.ID as id,
              0    as parent_id
            FROM $wpdb->posts as a
            WHERE a.post_type = 'contacts'
                  AND a.post_status = 'publish'
                  AND a.ID NOT IN (
                    SELECT
                      DISTINCT( b.p2p_from ) as id
                    FROM $wpdb->p2p as b
                    WHERE b.p2p_type = 'baptizer_to_baptized'
                  )
                  AND a.ID IN (
                    SELECT
                      DISTINCT( b.p2p_to ) as id
                    FROM $wpdb->p2p as b
                    WHERE b.p2p_type = 'baptizer_to_baptized'
            )
            UNION
            SELECT
              b.p2p_from as id,
              b.p2p_to as parent_id
            FROM $wpdb->p2p as b
            WHERE b.p2p_type = 'baptizer_to_baptized'
        ", ARRAY_A);

        return $results;
    }

    public static function build_baptism_generation_counts( array $elements, $parent_id = 0, $generation = -1, $counts = [] ) { // @todo

        $generation++;
        if ( !isset( $counts[$generation] ) ){
            $counts[$generation] = [ (string) $generation , 0, [] ];
        }
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parent_id) {
                $counts[ $generation ][1]++;
                $counts[ $generation ][2][] = $element['id'];
                $counts = self::build_baptism_generation_counts( $elements, $element['id'], $generation, $counts );
            }
        }

        return $counts;
    }


    public static function add_ordinal_number_suffix( $num) {
        if ( !in_array( ( $num % 100 ), array( 11,12,13 ) )){
            switch ($num % 10) {
              // Handle 1st, 2nd, 3rd
                case 1:  return $num.'st';
                case 2:  return $num.'nd';
                case 3:  return $num.'rd';
            }
        }
        return $num.'th';
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

    public static function query_get_group_generations() {
        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT
              a.ID         as id,
              0            as parent_id,
              d.meta_value as group_type,
              c.meta_value as group_status
            FROM $wpdb->posts as a
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
        ", ARRAY_A );

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
          as groups
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

