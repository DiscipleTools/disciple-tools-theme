<?php

/**
 * Disciple Tools
 *
 * @class   Disciple_Tools_
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Reports_Cron
 */
class Disciple_Tools_Reports_Cron
{

    /**
     * Disciple_Tools_Reports_Cron The single instance of Disciple_Tools_Reports_Cron.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Reports_Cron Instance
     * Ensures only one instance of Disciple_Tools_Admin_Menus is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Reports_Cron instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct() {
        // Adds actions for building reports
        add_action( 'build_disciple_tools_contacts_reports', [ $this, 'build_all_disciple_tools_contacts_reports' ] );
        add_action( 'build_disciple_tools_groups_reports', [ $this, 'build_all_disciple_tools_groups_reports' ] );
        /**
         * This action fires after build reports
         */
        do_action( 'dt_build_report' );
    } // End __construct()

    /**
     * Main scheduler for daily report builds
     *
     * @return void
     */
    public static function register_daily_report_events() {

        /**
         * Get options settings for reports
         * returns an array of options:
         * creates variable $options_settings['build_report_for_contacts']
         * build_report_for_contacts => true/false
         * build_report_for_groups => true/false
         */
        $site_options = dt_get_option( 'dt_site_options' );
        $options_settings = $site_options['daily_reports'];

        /**
         * Schedule different sources if there is not a previous report scheduled or the option has been turned off.
         */
        if ( !wp_next_scheduled( 'build_disciple_tools_contacts_reports' ) && isset( $options_settings['build_report_for_contacts'] ) ) { // Contacts
            // Schedule the event
            wp_schedule_event( strtotime( 'today midnight' ), 'daily', 'build_disciple_tools_contacts_reports' );
        }

        if ( !wp_next_scheduled( 'build_disciple_tools_groups_reports' ) && isset( $options_settings['build_report_for_groups'] ) ) { // Groups
            // Schedule the event
            wp_schedule_event( strtotime( 'today midnight' ), 'daily', 'build_disciple_tools_groups_reports' );
        }

        if ( !wp_next_scheduled( 'build_disciple_tools_reports' ) ) { // All other reports
            // Schedule the event
            wp_schedule_event( strtotime( 'today midnight' ), 'daily', 'build_disciple_tools_reports' );
        }

    }

    public static function unschedule_report_events() {
        $events = [
            "build_disciple_tools_contacts_reports",
            "build_disciple_tools_groups_reports",
            "build_disciple_tools_reports",
        ];
        foreach ( $events as $event ) {
            wp_unschedule_event( wp_next_scheduled( $event ), $event );
        }
    }

    /**
     * Build reports for Contacts
     */
    public function build_all_disciple_tools_contacts_reports() {
        // Calculate the next date(s) needed reporting
        $var_date = date( 'Y-m-d', strtotime( '-1 day' ) ); //TODO: should replace this with a foreach loop that queries that last day recorded
        $dates = [ $var_date ]; // array of dates

        // Request dates needed for reporting (loop)
        foreach ( $dates as $date ) {
            // Get arrays from integrations
            $results = Disciple_Tools_Reports_Contacts_Groups::contacts_prepared_data( $date );

            // Insert Report
            $status = [];
            $i = 0; // setup variables
            foreach ( $results as $result ) {
                $status[ $i ] = dt_report_insert( $result );
            }
        }
    }

    /**
     * Build reports for Groups
     */
    public function build_all_disciple_tools_groups_reports() {
        // Calculate the next date(s) needed reporting
        $var_date = date( 'Y-m-d', strtotime( '-1 day' ) ); //TODO: should replace this with a foreach loop that queries that last day recorded
        $dates = [ $var_date ]; // array of dates

        // Request dates needed for reporting (loop)
        foreach ( $dates as $date ) {
            // Get arrays from integrations
            $results = Disciple_Tools_Reports_Contacts_Groups::groups_prepared_data( $date );

            // Insert Report
            $status = [];
            $i = 0; // setup variables
            foreach ( $results as $result ) {
                $status[ $i ] = dt_report_insert( $result );
            }
        }
    }

}
