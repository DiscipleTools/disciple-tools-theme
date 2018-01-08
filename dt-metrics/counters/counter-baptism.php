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

}
