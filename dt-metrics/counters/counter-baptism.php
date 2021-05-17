<?php
/**
 * Counts Baptism statistics in database
 *
 * @package Disciple.Tools
 *
 * @version 0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Counter_Baptism
 */
class Disciple_Tools_Counter_Baptism extends Disciple_Tools_Counter_Base  {

    public static $total;
    public static $generations;

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
     * Counts the number of contacts with no disciples in database
     *
     * @access public
     * @since  0.1.0
     *
     * @param $start
     * @param $end
     *
     * @return float|int
     */
    public static function get_number_of_baptisms( $start, $end ) {

        if ( !isset( self::$total[$start . $end] ) ){
            $baptism_generations_this_year = self::get_baptism_generations( $start, $end );
            $total_baptisms = array_sum( $baptism_generations_this_year );
            self::$total[$start . $end] = $total_baptisms;
            return $total_baptisms;
        } else {
            return self::$total[$start . $end];
        }
    }


    /**
     * Generations of baptisms which occurred in a time range
     * @param $start
     * @param $end
     *
     * @return array
     */
    public static function get_baptism_generations( $start, $end ){
        if ( !isset( self::$generations[$start . $end] ) ){
            $raw_baptism_generation_list = self::query_get_all_baptism_connections();
            if ( is_wp_error( $raw_baptism_generation_list ) ){
                return $raw_baptism_generation_list;
            }
            $all_baptisms = self::build_baptism_generation_counts( $raw_baptism_generation_list );
            $baptism_generations_this_year = self::build_baptism_generations_in_range( $all_baptisms, $start, $end );
            //hide extra generations that are only 0;
            for ( $i = count( $baptism_generations_this_year ); $i > 1; $i-- ){
                if ( $baptism_generations_this_year[$i] === 0 && $i > 1 && $baptism_generations_this_year[$i - 1] === 0 ){
                    unset( $baptism_generations_this_year[$i] );
                } else {
                    break;
                }
            }
            self::$generations[ $start . $end ] = $baptism_generations_this_year;
            if ( !isset( self::$total[ $start . $end ] )){
                $total_baptisms = array_sum( $baptism_generations_this_year );
                self::$total[$start . $end] = $total_baptisms;
            }
            return $baptism_generations_this_year;
        } else {
            return self::$generations[$start . $end];
        }
    }


    /**
     * Counts the number of baptizers who are not zero generation.
     *
     * @access public
     * @since  0.1.0
     *
     * @param int $start unix timestamp
     * @param int $end unix timestamp
     *
     * @return int
     */
    public static function get_number_of_baptizers( int $start, int $end ) {
        global $wpdb;

        $results = $wpdb->get_var( $wpdb->prepare(
            "SELECT count(DISTINCT(p2p_to)) as count
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
            AND p2p_type = 'baptizer_to_baptized'",
        $start, $end ));
        return $results;
    }

    /**
     * Baptisms with baptism date in range
     * @param int $start_date
     * @param int $end_date
     *
     * @return array
     */

    public static function query_get_baptisms_id_list( $start_date = 0, $end_date = PHP_INT_MAX ) {
        global $wpdb;

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
        ", $start_date, $end_date ) );

        return $results;
    }

    public static function build_baptism_generations_in_range( $all_baptisms, $start_date = null, $end_date = null ) {

        $count = [];
        foreach ( $all_baptisms as $k => $v ) {
            $count[$k] = 0;
        }

        // get master list of ids for baptisms this year
        $list = self::query_get_baptisms_id_list( $start_date, $end_date );

        // redact counts according to baptisms this year
        foreach ( $list as $baptism ) {
            foreach ( $all_baptisms as $generation ) {
                if ( in_array( $baptism, $generation["ids"] ) ) {
                    if ( ! isset( $count[ $generation["generation"] ] ) ) {
                        $count[ $generation["generation"] ] = 0;
                    }
                    $count[ $generation["generation"] ]++;
                }
            }
        }
        if ( isset( $count[0] ) ) {
            unset( $count[0] );
        }

        // return counts
        return $count;
    }

    public static function query_get_all_baptism_connections() {
        global $wpdb;
        //get baptizers with no parent as parent_id 0
        //get all other baptism connects with id and parent_id
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

        return dt_queries()->check_tree_health( $results );
    }

    public static function build_baptism_generation_counts( array $elements, $parent_id = 0, $generation = -1, $counts = [] ) {

        $generation++;
        if ( !isset( $counts[$generation] ) ){
            $counts[$generation] = [
                "generation" => (string) $generation,
                "total" => 0,
                "ids" => []
            ];
        }
        foreach ($elements as $element_i => $element) {
            if ($element['parent_id'] == $parent_id) {
                //find and remove if the baptisms has already been counted on a longer path
                //we keep the shorter path
                $already_counted_in_deeper_path = false;
                foreach ( $counts as $count_i => $count ){
                    if ( $count_i > $generation ){
                        if ( in_array( $element['id'], $count["ids"] ) ){
                            $counts[ $count_i ]["total"]--;
                            unset( $counts[ $count_i ]["ids"][array_search( $element['id'], $count["ids"] )] );
                        }
                    } else {
                        if (in_array( $element['id'], $count["ids"] )){
                            $already_counted_in_deeper_path = true;
                        }
                    }
                }
                if ( !$already_counted_in_deeper_path ){
                    $counts[ $generation ]["total"]++;
                    $counts[ $generation ]["ids"][] = $element['id'];
                }
                $counts = self::build_baptism_generation_counts( $elements, $element['id'], $generation, $counts );
            }
        }

        return $counts;
    }

    /*
     * Save baptism generation number on all contact who have been baptized.
     */
    public static function save_all_contact_generations(){
        $raw_baptism_generation_list = self::query_get_all_baptism_connections();
        if ( is_wp_error( $raw_baptism_generation_list ) ){
            return $raw_baptism_generation_list;
        }
        $all_baptisms = self::build_baptism_generation_counts( $raw_baptism_generation_list );
        foreach ( $all_baptisms as $baptism_generation ){
            $generation = $baptism_generation["generation"];
            $baptisms = $baptism_generation["ids"];
            foreach ( $baptisms as $contact ){
                update_post_meta( $contact, 'baptism_generation', $generation );
            }
        }
    }

    /*
     * Set baptisms generation counts on a contact's baptism tree
     * Check parent's baptism generation and cascade to children
     * $parent_ids array is used to avoid infinite loops.
     */
    public static function reset_baptism_generations_on_contact_tree( $contact_id, $parent_ids = [] ){
        global $wpdb;
        $parents = $wpdb->get_results( $wpdb->prepare("
            SELECT contact.ID as contact_id, gen.meta_value as baptism_generation
            FROM $wpdb->p2p as b
            JOIN $wpdb->posts as contact ON ( contact.ID = b.p2p_to )
            LEFT JOIN $wpdb->postmeta gen ON ( gen.post_id = contact.ID AND gen.meta_key = 'baptism_generation' )
            WHERE b.p2p_type = 'baptizer_to_baptized'
            AND b.p2p_from = %s
        ", $contact_id), ARRAY_A);

        $highest_parent_gen = 0;
        foreach ( $parents as $parent ){
            if ( empty( $parent["baptism_generation"] ) && $parent["baptism_generation"] != "0" ){
                return self::reset_baptism_generations_on_contact_tree( $parent["contact_id"] );
            } else if ( $parent["baptism_generation"] > $highest_parent_gen ){
                $highest_parent_gen = $parent["baptism_generation"];
            }
            $parent_ids[] = $parent["contact_id"];
        }
        $parent_ids[] = $contact_id;

        $current_saved_gen = get_post_meta( $contact_id, 'baptism_generation', true );
        if ( (int) $current_saved_gen != ( (int) $highest_parent_gen ) + 1 ){
            if ( sizeof( $parents ) == 0 ){
                update_post_meta( $contact_id, 'baptism_generation', "0" );
            } else {
                update_post_meta( $contact_id, 'baptism_generation', $highest_parent_gen + 1 );
            }
            $children = $wpdb->get_results( $wpdb->prepare("
                SELECT contact.ID as contact_id, gen.meta_value as baptism_generation
                FROM $wpdb->p2p as b
                JOIN $wpdb->posts as contact ON ( contact.ID = b.p2p_from )
                LEFT JOIN $wpdb->postmeta gen ON ( gen.post_id = contact.ID AND gen.meta_key = 'baptism_generation' )
                WHERE b.p2p_type = 'baptizer_to_baptized'
                AND b.p2p_to = %s
            ", $contact_id), ARRAY_A);
            foreach ( $children as $child ){
                if ( !in_array( $child["contact_id"], $parent_ids )){
                    self::reset_baptism_generations_on_contact_tree( $child["contact_id"], $parent_ids );
                }
            }
        }
    }



}
