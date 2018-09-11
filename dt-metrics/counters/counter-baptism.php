<?php
/**
 * Counts Baptism statistics in database
 *
 * @package Disciple_Tools
 *
 * @version 0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Counter_Baptism
 */
class Disciple_Tools_Counter_Baptism extends Disciple_Tools_Counter_Base  {

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
     */
    public static function get_number_of_baptisms() {
        global $wpdb;

        $results = $wpdb->get_var(
            "SELECT
                count(`p2p_id`)
            FROM
                `$wpdb->p2p`
            WHERE
                `p2p_type` = 'baptizer_to_baptized'
            "
        );

        return $results;
    }

    /**
     * Counts the number of baptizers who are not zero generation.
     *
     * @access public
     * @since  0.1.0
     */
    public static function get_number_of_baptizers() {
        global $wpdb;

        $results = $wpdb->get_var(
            "SELECT
                COUNT(DISTINCT `p2p_to`)
            FROM
                `$wpdb->p2p`
            WHERE
                `p2p_type` = 'baptizer_to_baptized'"
        );

        return $results;
    }

    /*
     * Save baptism generation number on all contact who have been baptized.
     */
    public static function save_all_contact_generations(){
        $raw_baptism_generation_list = Disciple_Tools_Metrics_Hooks_Base::query_get_baptism_generations();
        $all_baptisms = Disciple_Tools_Metrics_Hooks_Base::build_baptism_generation_counts( $raw_baptism_generation_list );
        foreach ( $all_baptisms as $baptism_generation ){
            $generation = $baptism_generation[0];
            $baptisms = $baptism_generation[2];
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
