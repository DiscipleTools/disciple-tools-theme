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

            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 10 );

            //load base chart setup
            require_once( get_template_directory() . '/dt-metrics/charts-base.php' );
            // load basic charts
            require_once( get_template_directory() . '/dt-metrics/metrics-personal.php' );
            require_once( get_template_directory() . '/dt-metrics/metrics-critical-path.php' );
            require_once( get_template_directory() . '/dt-metrics/metrics-project.php' );
            require_once( get_template_directory() . '/dt-metrics/metrics-workers.php' );
//            require_once( get_template_directory() . '/dt-metrics/metrics-prayer.php' );
            require_once( get_template_directory() . '/dt-metrics/contacts/sources.php' );
            require_once( get_template_directory() . '/dt-metrics/contacts/milestones.php' );
            require_once( get_template_directory() . '/dt-metrics/contacts/milestones-map.php' );
//            require_once( get_template_directory() . '/dt-metrics/contacts/seeker-path.php' );
        }
    }

    // Enqueue maps and charts for standard metrics
    public function enqueue_scripts() {
        wp_register_script( 'datepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', false );
        wp_enqueue_style( 'datepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array() );

        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, false, true );
        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, false, true );
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

    public static function convert_seconds( $seconds, $include_seconds = false ) {
        $dt1 = new DateTime( "@0" );
        $dt2 = new DateTime( "@$seconds" );
        if ( $include_seconds ) {
            return $dt1->diff( $dt2 )->format( '%a days, %h hours, %i minutes and %s seconds' );
        } else {
            return $dt1->diff( $dt2 )->format( '%a days, %h hours, %i minutes' );
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
Disciple_Tools_Metrics::instance();


function dt_get_time_until_midnight() {

    /**
     * If looking for the timestamp for tomorrow midnight, use strtotime('tomorrow')
     */
    $midnight = mktime( 0, 0, 0, gmdate( 'n' ), gmdate( 'j' ) +1, gmdate( 'Y' ) );
    return intval( $midnight - current_time( 'timestamp' ) );
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
            'teams' => (int) $results['teams'],
        ];

        return $chart;
    }

    public static function chart_contacts_progress( $type = 'personal' ) {
        $chart = [];

        switch ( $type ) {
            case 'personal':
                $results = self::query_my_contacts_progress( get_current_user_id() );
                foreach ( $results as $value ) {
                    $chart[] = [
                        'label' => $value['label'],
                        'value' => $value['count']
                    ];
                }
                break;
            case 'project':
                $results = self::query_project_contacts_progress();
                foreach ( $results as $value ) {
                    $chart[] = [
                        'label' => $value['label'],
                        'value' => $value['value']
                    ];
                }
                break;
            default:
                $chart = [];
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
                foreach ( $results as $result ) {
                    $result["label"] = $types[$result['type']]["label"] ?? $result['type'];
                    $chart[] = $result;
                }
                break;
            case 'project':
                $results = self::query_project_group_types();
                foreach ( $results as $result ) {
                    $result["label"] = $types[$result['type']]["label"] ?? $result['type'];
                    $chart[] = $result;
                }
                break;
            default:
                $chart = [];
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
            $out_of = 0;
            if ( isset( $results[0]['out_of'] ) ) {
                $out_of = $results[0]['out_of'];
            }
            foreach ( $labels as $label_key => $label_value ) {
                $row = [
                    "label"      => $label_value,
                    "practicing" => 0,
                    "remaining"  => (int) $out_of
                ];
                foreach ( $results as $result ) {
                    if ( $result['health_key'] === $label_key ) {
                        $row["practicing"] = (int) $result["count"];
                        $row["remaining"]  = intval( $result['out_of'] ) - intval( $result['count'] );
                    }
                }
                $chart[] = $row;
            }
        }
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
//        $first_row = [ "Generations" ];
//        foreach ( $generation_tree[0] as $key => $label ){
//            if ( $key != "generation" && $key != "total" ){
//                if ( isset( $groups_fields["group_type"]["default"][$key]["label"] ) ){
//                    $first_row[] = $groups_fields["group_type"]["default"][$key]["label"];
//                } else {
//                    $first_row[] = $key;
//                }
//            }
//        }
        return $generation_tree;
    }

    public static function chart_project_hero_stats() {

        $stats = self::query_project_hero_stats();
        $group_health = self::query_project_group_health();
        $needs_training = 0; // @todo

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
            'fully_practicing' => (int) $stats['groups'] - (int) $needs_training, // @todo
            'teams' => $stats['teams'],
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
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
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

    /**
     * @note active use
     *
     * @return array
     */
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
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
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
                    AND c.meta_value != 'team'
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
                AND c.meta_value != 'team'
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
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
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
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
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
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
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
                as `groups`,
                
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
                  JOIN $wpdb->postmeta as e
                    ON a.ID=e.post_id
                      AND e.meta_key = 'group_type'
                      AND e.meta_value = 'team'
                WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups')
                as `teams`
            ",
            $user_id,
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
                JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                     AND e.meta_key = 'group_type'
                     AND ( e.meta_value = 'group' OR e.meta_value = 'church' )
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
                JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                     AND e.meta_key = 'group_type'
                     AND ( e.meta_value = 'group' OR e.meta_value = 'church' )
                LEFT JOIN $wpdb->postmeta as d
                  ON ( a.ID=d.post_id
                  AND d.meta_key = 'health_metrics' )
              WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups'
              GROUP BY d.meta_value
        ",
            'user-' . $user_id,
            'user-' . $user_id
        ), ARRAY_A );

        return $results;
    }

    public static function query_project_group_health() {
        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT d.meta_value as health_key,
              count(distinct(a.ID)) as count,
              ( SELECT count(*)
              FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as c
                  ON a.ID=c.post_id
                     AND c.meta_key = 'group_status'
                     AND c.meta_value = 'active'
                JOIN $wpdb->postmeta as d
                  ON a.ID=d.post_id
                     AND d.meta_key = 'group_type'
                     AND ( d.meta_value = 'group' OR d.meta_value = 'church' )
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
                    AND d.meta_key = 'health_metrics' )
                JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                     AND e.meta_key = 'group_type'
                     AND ( e.meta_value = 'group' OR e.meta_value = 'church' )
              WHERE a.post_status = 'publish'
                    AND a.post_type = 'groups'
              GROUP BY d.meta_value
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
              JOIN $wpdb->postmeta as d
                ON a.ID = d.post_id
                   AND d.meta_key = 'group_type'
                   AND d.meta_value != 'team'
            WHERE a.post_status = 'publish'
                  AND a.post_type = 'groups'
                  AND a.ID NOT IN (
                      SELECT DISTINCT (p2p_from)
                      FROM $wpdb->p2p
                      WHERE p2p_type = 'groups_to_groups'
                      GROUP BY p2p_from)
            UNION
            SELECT
              p.p2p_from  as id,
              p.p2p_to    as parent_id,
              (SELECT meta_value
               FROM $wpdb->postmeta
               WHERE post_id = p.p2p_from
                     AND meta_key = 'group_type')
                     as group_type,
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
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
                    GROUP BY post_id
                )
            )
            as active_contacts,
            (SELECT count(a.ID)
                FROM $wpdb->posts as a
                    JOIN $wpdb->postmeta as b
                    ON a.ID=b.post_id
                       AND b.meta_key = 'accepted'
                       AND (b.meta_value = '' OR b.meta_value = 'no')
                JOIN $wpdb->postmeta as d
                ON a.ID=d.post_id
                   AND d.meta_key = 'overall_status'
                   AND d.meta_value = 'assigned'
                WHERE a.post_status = 'publish'
                AND a.post_type = 'contacts'
                AND a.ID NOT IN (
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
                    GROUP BY post_id
                )
            )
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
                    SELECT post_id FROM $wpdb->postmeta
                    WHERE meta_key = 'type' AND meta_value = 'user'
                    GROUP BY post_id
                )
            )
            as needs_update,
            (SELECT count(a.ID)
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as d
                ON a.ID=d.post_id
                    AND d.meta_key = 'group_status'
                AND d.meta_value = 'active'
                JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                     AND e.meta_key = 'group_type'
                     AND e.meta_value != 'team'
                WHERE a.post_status = 'publish'
                AND a.post_type = 'groups')
            as `groups`,
            (SELECT count(a.ID)
                FROM $wpdb->posts as a
                JOIN $wpdb->postmeta as d
                ON a.ID=d.post_id
                    AND d.meta_key = 'group_status'
                AND d.meta_value = 'active'
                JOIN $wpdb->postmeta as e
                  ON a.ID=e.post_id
                     AND e.meta_key = 'group_type'
                     AND e.meta_value = 'team'
                WHERE a.post_status = 'publish'
                AND a.post_type = 'groups')
            as `teams`
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

// Tests if timestamp is valid.
if ( ! function_exists( 'is_valid_timestamp' ) ) {
    function is_valid_timestamp( $timestamp ) {
        return ( (string) (int) $timestamp === $timestamp )
            && ( $timestamp <= PHP_INT_MAX )
            && ( $timestamp >= ~PHP_INT_MAX );
    }
}

