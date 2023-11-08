<?php

if ( !defined( 'ABSPATH' ) ){
    exit;
} // Exit if accessed directly

if ( !wp_next_scheduled( 'activity_log_delete_old_viewed_actions' ) ){
    wp_schedule_event( time(), 'daily', 'activity_log_delete_old_viewed_actions' );
}
add_action( 'activity_log_delete_old_viewed_actions', 'activity_log_delete_old_viewed_actions_handler' );

function activity_log_delete_old_viewed_actions_handler(){

    // Proceed with the deletion of activity log viewed events older than 3 months.
    global $wpdb;
    $wpdb->query(
        $wpdb->prepare( "
            DELETE FROM $wpdb->dt_activity_log
            WHERE action = 'viewed' AND hist_time < %d
            ", strtotime( '-3 months' )
        )
    );
}
