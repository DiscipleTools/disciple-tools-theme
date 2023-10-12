<?php

if ( !defined( 'ABSPATH' ) ){
    exit;
} // Exit if accessed directly

if ( !wp_next_scheduled( 'activity_log_delete_old_viewed_actions' ) ){
    wp_schedule_event( time(), 'daily', 'activity_log_delete_old_viewed_actions' );
}
add_action( 'activity_log_delete_old_viewed_actions', 'activity_log_delete_old_viewed_actions_handler' );

function activity_log_delete_old_viewed_actions_handler(){

    // Stop if enforce delete old viewed actions is disabled.
    if ( boolval( get_option( 'dt_activity_log_enforce_delete_old_viewed_actions' ) ) ){
        return;
    }

    // Proceed with the deletion of activity log viewed events older than a month.
    global $wpdb;
    $wpdb->query(
        $wpdb->prepare(                                  "
            DELETE FROM $wpdb->dt_activity_log log
            WHERE log.action = 'viewed' AND log.hist_time < %d
            ", strtotime( '-1 months' )
        )
    );
}
