<?php
/**
 * Count first generations in database using the Post-to-Post
 *
 * @package Disciple.Tools
 * @version 0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Counter_Connected
 */
class Disciple_Tools_Counter_Connected extends Disciple_Tools_Counter_Base  {

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
     * @param $type
     *
     * @return int
     */
    public function has_zero( $type ) {
        global $wpdb;

        $post_type = 'contacts';
        if ($type == 'groups_to_groups') { $post_type = 'groups'; }

        // Get values
        $total_contacts = wp_count_posts( $post_type )->publish;
        $wpdb->get_var( $wpdb->prepare(
            "SELECT DISTINCT
                p2p_to
            FROM
                `$wpdb->p2p`
            WHERE
                p2p_type = %s",
            $type
        ), ARRAY_A );
        $has_disciples = $wpdb->num_rows;

        // Subtract total contacts from contacts with disciples
        $gen_count = $total_contacts - $has_disciples;

        return $gen_count;
    }

    /**
     * Counts the number of contacts with at least two disciples
     *
     * @param $min_number
     * @param $type
     *
     * @return int
     */
    public function has_at_least( $min_number, $type ) {
        global $wpdb;
        $i = 0;

        $p2p_array_to = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                p2p_to
            FROM
                `$wpdb->p2p`
            WHERE
                p2p_type = %s",
            $type
        ), ARRAY_A );
        $p2p_distinct = array_unique( $p2p_array_to, SORT_REGULAR );

        foreach ($p2p_distinct as $item) {
            if ($this->get_number_disciples( $item, $p2p_array_to ) >= $min_number) {
                $i++;
            };
        }
        return $i;
    }

    /**
     * Counts the number of disciples or groups connected to a single record.
     * Example: How many contacts have one disciple? How many have two disciples?
     * This helps identify general fruitfulness.
     *
     * @param $exact_number
     * @param $type
     *
     * @return int
     */
    public function has_exactly( $exact_number, $type ) {
        global $wpdb;
        $i = 0;

        $p2p_array_to = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                p2p_to
            FROM
                `$wpdb->p2p`
            WHERE
                p2p_type = %s",
            $type
        ), ARRAY_A );
        $p2p_distinct = array_unique( $p2p_array_to, SORT_REGULAR );

        foreach ($p2p_distinct as $item) {
            if ($this->get_number_disciples( $item, $p2p_array_to ) == $exact_number) {
                $i++;
            };
        }
        return $i;
    }

    /**
     * Query: number of disciples of a given record
     *
     * @param $contact
     * @param $column
     *
     * @return int
     */
    protected function get_number_disciples( $contact, $column ) {
        $i = 0;

        foreach ($column as $item) {
            if ($item == $contact) {
                $i++;
            }
        }
        return $i;
    }

    /**
     * Has Meta Key
     *
     * @param $type
     * @param $meta_value
     *
     * @return null|string
     */
    public function has_meta_value( $type, $meta_value ) {
        global $wpdb;

        //Select count(DISTINCT p2p_to) as planters from wp_p2p INNER JOIN wp_p2pmeta ON wp_p2p.p2p_id=wp_p2pmeta.p2p_id  where meta_value = 'Planting'
        $results = $wpdb->get_var( $wpdb->prepare(
            "SELECT
                count(DISTINCT `p2p_to`)
            FROM
                `$wpdb->p2p`
            INNER JOIN
                `$wpdb->p2pmeta`
            ON
                `$wpdb->p2p`.`p2p_id` = `$wpdb->p2pmeta`.`p2p_id`
            WHERE
                `meta_value` = %s
                AND `p2p_type` = %s",
            $meta_value,
            $type
        ) );

        return $results;

    }
}
