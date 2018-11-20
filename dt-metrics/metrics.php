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
        if ( strpos( $url_path, "metrics" ) !== false ) {

            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_google' ], 10 );

            // load basic charts
            require_once( get_template_directory() . '/dt-metrics/metrics-personal.php' );
            require_once( get_template_directory() . '/dt-metrics/metrics-project.php' );
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


function dt_get_time_until_midnight() {
    $midnight = mktime( 0, 0, 0, date( 'n' ), date( 'j' ) +1, date( 'Y' ) );
    return $midnight - current_time( 'timestamp' );
}

abstract class Disciple_Tools_Metrics_Hooks_Base
{
    public $permissions = [];

    public function __construct() {}

    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }

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

        $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
        $types = $group_fields["group_type"]["default"];

        switch ( $type ) {
            case 'personal':
                $results = self::query_my_group_types();
                $chart[] = [ 'Group Type', 'Number' ];
                foreach ( $results as $result ) {
                    $label = $types[$result['type']]["label"] ?? $result['type'];
                    $chart[] = [ $label, intval( $result['count'] ) ];
                }
                break;
            case 'project':
                $results = self::query_project_group_types();
                $chart[] = [ 'Group Type', 'Number' ];
                foreach ( $results as $result ) {
                    $label = $types[$result['type']]["label"] ?? $result['type'];
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
        $group_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();
        $labels = [];

        foreach ( $group_fields["health_metrics"]["default"] as $key => $option ) {
            $labels[$key] = $option["label"];
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
            $chart[] = [ $v_label, 0, (int) $out_of, '' ];
        }

        array_unshift( $chart, [ 'Step', __( 'Practicing', 'disciple_tools' ), __( 'Not Practicing', 'disciple_tools' ), [ 'role' => 'annotation' ] ] ); // add top row

        return $chart;
    }

    public static function chart_group_generations( $type = 'personal' ) {

        $groups_fields = Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings();

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
        $first_row = [ "Generations" ];
        foreach ( $generation_tree[0] as $key => $label ){
            if ( $key != "generation" && $key != "total" ){
                if ( isset( $groups_fields["group_type"]["default"][$key]["label"] ) ){
                    $first_row[] = $groups_fields["group_type"]["default"][$key]["label"];
                } else {
                    $first_row[] = $key;
                }
            }
        }
        $first_row[] = [ "role" => "Annotation" ];
        return array_merge( [ $first_row ], $generation_tree );
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


    /**
     * QUERIES
     */

    public static function query_my_contacts_progress( $user_id = null ) {
        global $wpdb;
        if ( empty( $user_id ) ) {
            $user_id = get_current_user_id();
        }

        $defaults = [];
        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $seeker_path_options = $contact_fields["seeker_path"]["default"];
        foreach ( $seeker_path_options as $key => $option ) {
            $defaults[$key] = [
                'label' => $option["label"],
                'count' => 0,
            ];
        }

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT b.meta_value as seeker_path, count( a.ID ) as count
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
                if ( isset( $defaults[$result['seeker_path']] ) ) {
                    $query_results[$result['seeker_path']] = [
                        'label' => $defaults[$result['seeker_path']]['label'],
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
            SELECT b.meta_value as seeker_path, count( a.ID ) as count
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

        $contact_fields = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $seeker_path_options = $contact_fields["seeker_path"]["default"];

        foreach ( $seeker_path_options as $seeker_path_key => $seeker_path_option ){
            $added = false;
            foreach ( $results as $result ) {
                if ( $result["seeker_path"] == $seeker_path_key ){
                    $query_results[] = [
                        'key' => $seeker_path_key,
                        'label' => $seeker_path_option['label'],
                        'value' => intval( $result['count'] )
                    ];
                    $added = true;
                }
            }
            if ( !$added ){
                $query_results[] = [
                    'key' => $seeker_path_key,
                    'label' => $seeker_path_option['label'],
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
                      AND b.meta_value = ''
                  JOIN $wpdb->postmeta as c
                    ON a.ID=c.post_id
                      AND c.meta_key = 'assigned_to'
                      AND c.meta_value = CONCAT( 'user-', %s )
                  JOIN $wpdb->postmeta as d
                    ON a.ID=d.post_id
                      AND d.meta_key = 'overall_status'
                      AND d.meta_value = 'assigned'
                WHERE a.post_status = 'publish'
                  AND a.post_type = 'contacts'
                  AND a.ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'corresponds_to_user'
                      AND meta_value != 0
                    GROUP BY post_id
                  )
              ) as needs_accept,
              (SELECT count(a.ID)
                FROM $wpdb->posts as a
                  JOIN $wpdb->postmeta as b
                    ON a.ID=b.post_id
                      AND b.meta_key = 'requires_update'
                      AND b.meta_value = '1'
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
                )
              ) as needs_update,
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
            SELECT d.meta_value as health_key,
              count(distinct(a.ID)) as count,
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
                LEFT JOIN $wpdb->postmeta as d
                  ON ( a.ID=d.post_id
                  AND d.meta_key = 'health_metrics' )
              WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups'
              GROUP BY d.meta_key
        ",
            'user-' . $user_id,
            'user-' . $user_id
        ), ARRAY_A );

        return $results;
    }

    public static function query_project_group_health() {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare( "
            SELECT d.meta_value as health_key,
              count(distinct(a.ID)) as count,
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
                  ON ( a.ID=d.post_id
                    AND d.meta_key = %s )
              WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
              GROUP BY d.meta_value
        ", 'health_metrics' ), ARRAY_A );

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
                       AND b.meta_value = ''
               JOIN $wpdb->postmeta as d
               ON a.ID=d.post_id
                   AND d.meta_key = 'overall_status'
                   AND d.meta_value = 'assigned'
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
                               AND b.meta_value = '1'
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

