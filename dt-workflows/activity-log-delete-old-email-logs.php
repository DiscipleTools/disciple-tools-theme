<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !wp_next_scheduled( 'activity_log_delete_old_email_logs' ) ) {
    wp_schedule_event( time(), 'daily', 'activity_log_delete_old_email_logs' );
}
add_action( 'activity_log_delete_old_email_logs', 'activity_log_delete_old_email_logs_handler' );

function activity_log_delete_old_email_logs_handler() {

    // Ensure email logs processing flag is enabled.
    if ( boolval( get_option( 'dt_email_logs_enabled' ) ) ) {

        // Proceed with the deletion of mail_sent activity log events older than a month.
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare( "
             DELETE FROM $wpdb->dt_activity_log log
             WHERE log.action = 'mail_sent' AND log.hist_time < %d
             ", strtotime( '-1 months' )
            )
        );
    }
}
