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
    public static function instance()
    {
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
    public function __construct()
    {

        // Registers the daily report cron jobs
//        @todo register on activation
        add_action( 'init', [ $this, 'register_daily_report_events' ] );

        // Adds action for Facebook report build
        add_action( 'build_disciple_tools_contacts_reports', [ $this, 'build_all_disciple_tools_contacts_reports' ] );
        add_action( 'build_disciple_tools_groups_reports', [ $this, 'build_all_disciple_tools_groups_reports' ] );
        add_action( 'build_facebook_reports', [ $this, 'build_all_facebook_reports' ] );
        add_action( 'build_twitter_reports', [ $this, 'build_all_twitter_reports' ] );
        add_action( 'build_analytics_reports', [ $this, 'build_all_analytics_reports' ] );
        add_action( 'build_adwords_reports', [ $this, 'build_all_adwords_reports' ] );
        add_action( 'build_mailchimp_reports', [ $this, 'build_all_mailchimp_reports' ] );
        add_action( 'build_youtube_reports', [ $this, 'build_all_youtube_reports' ] );
    } // End __construct()

    /**
     * Main scheduler for daily report builds
     *
     * @return void
     */
    public function register_daily_report_events()
    {

        /**
         * Get options settings for reports
         * returns an array of options:
         * creates variable $options_settings['build_report_for_contacts']
         * build_report_for_contacts => true/false
         * build_report_for_groups => true/false
         * build_report_for_facebook => true/false
         * build_report_for_twitter => true/false
         * build_report_for_analytics => true/false
         * build_report_for_adwords => true/false
         * build_report_for_mailchimp => true/false
         * build_report_for_youtube => true/false
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

        if ( !wp_next_scheduled( 'build_facebook_reports' ) && isset( $options_settings['build_report_for_facebook'] ) ) { // Facebook
            // Schedule the event
            wp_schedule_event( strtotime( 'today midnight' ), 'daily', 'build_facebook_reports' );
        }

        if ( !wp_next_scheduled( 'build_twitter_reports' ) && isset( $options_settings['build_report_for_twitter'] ) ) { // Twitter
            // Schedule the event
            wp_schedule_event( strtotime( 'today midnight' ), 'daily', 'build_twitter_reports' );
        }

        if ( !wp_next_scheduled( 'build_analytics_reports' ) && isset( $options_settings['build_report_for_analytics'] ) ) { // Analytics
            // Schedule the event
            wp_schedule_event( strtotime( 'today midnight' ), 'daily', 'build_analytics_reports' );
        }

        if ( !wp_next_scheduled( 'build_adwords_reports' ) && isset( $options_settings['build_report_for_adwords'] ) ) { // Adwords
            // Schedule the event
            wp_schedule_event( strtotime( 'today midnight' ), 'daily', 'build_adwords_reports' );
        }

        if ( !wp_next_scheduled( 'build_mailchimp_reports' ) && isset( $options_settings['build_report_for_mailchimp'] ) ) { // Mailchimp
            // Schedule the event
            wp_schedule_event( strtotime( 'today midnight' ), 'daily', 'build_mailchimp_reports' );
        }

        if ( !wp_next_scheduled( 'build_youtube_reports' ) && isset( $options_settings['build_report_for_youtube'] ) ) { // Youtube
            // Schedule the event
            wp_schedule_event( strtotime( 'today midnight' ), 'daily', 'build_youtube_reports' );
        }

        register_deactivation_hook( dirname( dirname( __DIR__ ) ) . "/disciple-tools.php", function() {
            $events = [
                "build_disciple_tools_contacts_reports",
                "build_disciple_tools_groups_reports",
                "build_facebook_reports",
            "build_twitter_reports",
                "build_analytics_reports",
            "build_adwords_reports",
                "build_mailchimp_reports",
            "build_youtube_reports",
            ];
            foreach ( $events as $event ) {
                wp_unschedule_event( wp_next_scheduled( $event ), $event );
            }
        } );
    }

    /**
     * Build reports for Contacts
     */
    public function build_all_disciple_tools_contacts_reports()
    {
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
    public function build_all_disciple_tools_groups_reports()
    {
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

    /**
     * Build all Facebook reports
     * This defines the outstanding days of reports needed to be logged (one day or multiple days), and
     * then loops those days through the Disciple_Tools_Reports_Integrations class. These loops return success or error
     * reports that are then logged to the reports database as a history of update and debugging.
     */
    public function build_all_facebook_reports()
    {
        //get the facebook pages and access tokens from the settings
        $facebook_pages = get_option( "dt_facebook_pages", [] );
        foreach ( $facebook_pages as $page_id => $facebook_page ) {
            $last_facebook_report = Disciple_Tools_Reports_API::get_last_record_of_source_and_subsource( 'Facebook', $page_id );
            if ( $last_facebook_report && isset( $last_facebook_report->report_date ) ) {
                $date_of_last_record = date( 'Y-m-d', strtotime( $last_facebook_report->report_date ) );
            } else {
                //set to yesterday to get today's report
                $date_of_last_record = date( 'Y-m-d', strtotime( '-1 day' ) );
            }
            $reports = Disciple_Tools_Reports_Integrations::facebook_prepared_data( $date_of_last_record, $facebook_page );
            // Request dates needed for reporting (loop)
            foreach ( $reports as $report ) {
                // Insert Reports
                dt_report_insert( $report );
            }
        }
    }

    public function build_all_analytics_reports()
    {
        // Calculate last day reported
        //@TODO split by subsource in case one does not update
        $last_report = Disciple_Tools_Reports_API::get_last_record_of_source( 'Analytics' );

        if ( $last_report && isset( $last_report->report_date ) ) {
            $date_of_last_record = date( 'Y-m-d', strtotime( $last_report->report_date ) );
        } else {
            //set to yesterday to get today's report
            $date_of_last_record = date( 'Y-m-d', strtotime( '-1 day' ) );
        }

        $reports = Disciple_Tools_Reports_Integrations::analytics_prepared_data( $date_of_last_record );

        // Request dates needed for reporting (loop)
        foreach ( $reports as $report ) {
            // Insert Reports
            dt_report_insert( $report );
        }
    }

    public function build_all_adwords_reports()
    {
        // Calculate the next date(s) needed reporting
        $var_date = date( 'Y-m-d', strtotime( '-1 day' ) ); //TODO: should replace this with a foreach loop that queries that last day recorded
        $dates = [ $var_date ]; // array of dates

        // Request dates needed for reporting (loop)
        foreach ( $dates as $date ) {
            // Get arrays from integrations
            $results = Disciple_Tools_Reports_Integrations::adwords_prepared_data( $date );

            // Insert Report
            $status = [];
            $i = 0; // setup variables
            foreach ( $results as $result ) {
                $status[ $i ] = dt_report_insert( $result );
            }
        }
    }

    public function build_all_mailchimp_reports()
    {
        // Calculate the next date(s) needed reporting
        $var_date = date( 'Y-m-d', strtotime( '-1 day' ) ); //TODO: should replace this with a foreach loop that queries that last day recorded
        $dates = [ $var_date ]; // array of dates

        // Request dates needed for reporting (loop)
        foreach ( $dates as $date ) {
            // Get arrays from integrations
            $results = Disciple_Tools_Reports_Integrations::mailchimp_prepared_data( $date );

            // Insert Report
            $status = [];
            $i = 0; // setup variables
            foreach ( $results as $result ) {
                $status[ $i ] = dt_report_insert( $result );
            }
        }
    }

    public function build_all_youtube_reports()
    {
        // Calculate the next date(s) needed reporting
        $var_date = date( 'Y-m-d', strtotime( '-1 day' ) ); //TODO: should replace this with a foreach loop that queries that last day recorded
        $dates = [ $var_date ]; // array of dates

        // Request dates needed for reporting (loop)
        foreach ( $dates as $date ) {
            // Get arrays from integrations
            $results = Disciple_Tools_Reports_Integrations::youtube_prepared_data( $date );

            // Insert Report
            $status = [];
            $i = 0; // setup variables
            foreach ( $results as $result ) {
                $status[ $i ] = dt_report_insert( $result );
            }
        }
    }

    public function build_all_twitter_reports()
    {
        // Calculate the next date(s) needed reporting
        $var_date = date( 'Y-m-d', strtotime( '-1 day' ) ); //TODO: should replace this with a foreach loop that queries that last day recorded
        $dates = [ $var_date ]; // array of dates

        // Request dates needed for reporting (loop)
        foreach ( $dates as $date ) {
            // Get arrays from integrations
            $results = Disciple_Tools_Reports_Integrations::youtube_prepared_data( $date );

            // Insert Report
            $status = [];
            $i = 0; // setup variables
            foreach ( $results as $result ) {
                $status[ $i ] = dt_report_insert( $result );
            }
        }
    }

}
