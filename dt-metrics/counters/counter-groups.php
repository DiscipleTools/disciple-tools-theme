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
                    $church_generations[$gen_key] = $gen_val["church"];
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
//                @todo implement church planter field on group/church
                return 0;
                break;

            default: // countable contacts
                return 0;
        }
    }


    public static function get_group_generations( $start, $end, $args = [] ){
        if ( !isset( self::$generations[$start.$end] ) ){
            $raw_connections = self::query_get_all_group_connections();
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

        return $results;
    }

    public static function query_get_groups_id_list( $start_date = 0, $end_date = PHP_INT_MAX, $args = [] ) {
        global $wpdb;

        $results = $wpdb->get_col( $wpdb->prepare( "
            SELECT
              a.ID
            FROM $wpdb->posts as a
              JOIN $wpdb->postmeta as status
                ON a.ID = status.post_id
                   AND status.meta_key = 'group_status'
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
            WHERE a.post_type = 'groups'
              AND a.post_status = 'publish'
              AND ( status.meta_value = 'active' AND c.meta_value < %d )
              AND ( status.meta_value = 'active' OR d.meta_value > %d ) 
        ", isset( $args['assigned_to'] ) ? 'user-' . $args['assigned_to'] : '%%', $end_date, $start_date ) );

        return $results;
    }

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
                    if ( $element["group_status"] === "active" ) {
                        if ( $element["group_type"] === "pre-group" ) {
                            $counts[ $generation ]["pre-group"] ++;
                        } elseif ( $element["group_type"] === "group" ) {
                            $counts[ $generation ]["group"] ++;
                        } elseif ( $element["group_type"] === "church" ) {
                            $counts[ $generation ]["church"] ++;
                        }
                    }
                }
                $counts = self::build_group_generation_counts( $elements, $element['id'], $generation, $counts, $ids_to_include );
            }
        }

        return $counts;
    }


}
