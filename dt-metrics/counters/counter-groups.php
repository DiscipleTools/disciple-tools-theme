<?php
/**
 * Counts Misc Groups and Church numbers
 *
 * @package Disciple_Tools
 * @version 0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Counter_Groups
 */
class Disciple_Tools_Counter_Groups extends Disciple_Tools_Counter_Base  {

    private static $generations;

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct() {
        parent::__construct();
    } // End __construct()


    /**
     * Returns count of contacts for different statuses
     * Primary 'countable'
     *
     * @param string $status
     * @param int $start
     * @param int $end
     * @param array $args
     *
     * @return int|array
     */
    public static function get_groups_count( string $status, int $start, int $end, $args = [] ) {

        $status = strtolower( $status );

        switch ( $status ) {

            case 'generations':
                return self::get_group_generations( $start, $end, $args );
                break;
            case 'church_generations':
                $generations = self::get_group_generations( $start, $end );
                $church_generations = [];
                foreach ( $generations as $gen_key => $gen_val ){
                    $church_generations[$gen_val["generation"]] = $gen_val["church"];
                }
                return $church_generations;
                break;
            case 'churches_and_groups':
                $generations = self::get_group_generations( $start, $end );
                $total = 0;
                foreach ( $generations as $gen ){
                    $total += $gen["group"] + $gen["church"];
                }
                return $total;
                break;

            case 'active_churches':
                $generations = self::get_group_generations( $start, $end );
                $total = 0;
                foreach ( $generations as $gen ){
                    $total += $gen["church"];
                }
                return $total;
                break;

            case 'active_groups':
                $generations = self::get_group_generations( $start, $end );
                $total = 0;
                foreach ( $generations as $gen ){
                    $total += $gen["group"];
                }
                return $total;
                break;

            case 'church_planters':
                return self::query_church_planters( $start, $end );
                break;

            default: // countable contacts
                return 0;
        }
    }


    /**
     * Get group generation of groups that were active in the time range
     * @param $start
     * @param $end
     * @param array $args
     *
     * @return array
     */
    public static function get_group_generations( $start, $end, $args = [] ){
        if ( !isset( self::$generations[$start.$end] ) ){
            $raw_connections = self::query_get_all_group_connections();
            if ( is_wp_error( $raw_connections ) ){
                return $raw_connections;
            }
            $groups_in_time_range = self::query_get_groups_id_list( $start, $end, $args );
            $church_generation = self::build_group_generation_counts( $raw_connections, 0, 0, [], $groups_in_time_range );
            $generations = [];
            foreach ( $church_generation as $k => $v ){
                $generations[] = $v;
            }
            $church_generation = $generations;
            self::$generations[$start.$end] = $church_generation;
            return $church_generation;
        } else {
            return self::$generations[$start.$end];
        }
    }


    public static function query_get_all_group_connections() {
        global $wpdb;
        //get all group connections with parent_id, group_id, group_type, group_status
        //first get groups with no parent as parent_id 0
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
                      GROUP BY p2p_from
                  )
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

        return dt_queries()->check_tree_health( $results );
    }


    /**
     * Groups that were active in a date range
     * @param int $start_date
     * @param int $end_date
     * @param array $args
     *
     * @return array
     */
    public static function query_get_groups_id_list( $start_date = 0, $end_date = PHP_INT_MAX, $args = [] ) {
        global $wpdb;

        $results = $wpdb->get_col( $wpdb->prepare( "
            SELECT
              a.ID
            FROM $wpdb->posts as a
              JOIN $wpdb->postmeta as status
                ON a.ID = status.post_id
                AND status.meta_key = 'group_status'
              JOIN $wpdb->postmeta as type
                ON a.ID = type.post_id
                AND type.meta_key = 'group_type'
              JOIN $wpdb->postmeta as assigned_to
                ON a.ID = assigned_to.post_id
                AND assigned_to.meta_key = 'assigned_to'
                AND assigned_to.meta_value LIKE %s
              LEFT JOIN $wpdb->postmeta as c
                ON a.ID = c.post_id
                   AND c.meta_key = 'start_date'
              LEFT JOIN $wpdb->postmeta as d
                ON a.ID = d.post_id
                AND d.meta_key = 'end_date'
              LEFT JOIN $wpdb->postmeta as e
                ON a.ID = e.post_id
                AND e.meta_key = 'church_start_date'
            WHERE a.post_type = 'groups'
              AND a.post_status = 'publish'
              AND (
                type.meta_value = 'pre-group'
                OR ( type.meta_value = 'group'
                  AND c.meta_value < %d
                  AND ( status.meta_value = 'active' OR d.meta_value > %d ) )
                OR ( type.meta_value = 'church'
                  AND e.meta_value < %d
                  AND ( status.meta_value = 'active' OR d.meta_value > %d ) )
              )
        ", isset( $args['assigned_to'] ) ? 'user-' . $args['assigned_to'] : '%%', $end_date, $start_date, $end_date, $start_date ) );

        return $results;
    }


    /**
     * Count group generations by group type
     *
     * @param array $elements
     * @param int $parent_id
     * @param int $generation
     * @param array $counts
     * @param array $ids_to_include
     *
     * @return array
     */
    public static function build_group_generation_counts( array $elements, $parent_id = 0, $generation = 0, $counts = [], $ids_to_include = [] ) {

        $generation++;
        if ( !isset( $counts[$generation] ) ){
            $counts[$generation] = [
                "generation" => (string) $generation,
                "pre-group" => 0,
                "group" => 0,
                "church" => 0,
                "total" => 0
            ];
        }
        foreach ($elements as $element) {

            if ($element['parent_id'] == $parent_id) {
                if ( in_array( $element['id'], $ids_to_include ) ) {
                    if ( $element["group_type"] === "pre-group" ) {
                        $counts[ $generation ]["pre-group"] ++;
                    } elseif ( $element["group_type"] === "group" ) {
                        $counts[ $generation ]["group"] ++;
                    } elseif ( $element["group_type"] === "church" ) {
                        $counts[ $generation ]["church"] ++;
                    }
                    $counts[ $generation ]["total"] ++;
                }
                $counts = self::build_group_generation_counts( $elements, $element['id'], $generation, $counts, $ids_to_include );
            }
        }

        return $counts;
    }

    public static function query_church_planters( $start, $end ){
        global $wpdb;
        $count = $wpdb->get_var( $wpdb->prepare("
            SELECT COUNT(DISTINCT(p2p.p2p_to))
            FROM $wpdb->posts as p
            JOIN $wpdb->postmeta pm ON (
                p.ID = pm.post_id
                AND pm.meta_key = 'church_start_date'
                AND pm.meta_value > %s
                AND pm.meta_value < %s
            )
            JOIN $wpdb->p2p p2p ON (
                p2p.p2p_from = p.ID
                AND p2p.p2p_type = 'groups_to_coaches'
            )
            WHERE p.post_type = 'groups'
        ", $start, $end ));
        return $count;
    }


}
