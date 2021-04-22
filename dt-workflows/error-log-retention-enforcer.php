<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! wp_next_scheduled( 'error_log_retention_enforcer' ) ) {
    wp_schedule_event( time(), 'daily', 'error_log_retention_enforcer' );
}
add_action( 'error_log_retention_enforcer', 'enforce_error_log_retention_policy' );

function enforce_error_log_retention_policy() {
    // Stop if enforce retention policy feature if disabled.
    if ( ! boolval( get_option( 'dt_error_log_enforce_retention_policy' ) ) ) {
        return;
    }

    global $wpdb;

    $current_retention_period_count = fetch_retention_period_count();

    // Identify count of logs greater than specified retention period.
    $stale_logs_count = $wpdb->get_results( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->dt_activity_log WHERE (action = 'error_log') AND (hist_time < %s)",
        esc_attr( strtotime( '-' . $current_retention_period_count . ' day' ) )
    ) );

    // Only delete if stale logs detected.
    if ( $stale_logs_count > 0 ) {
        $wpdb->get_results( $wpdb->prepare( "DELETE FROM $wpdb->dt_activity_log WHERE (action = 'error_log') AND (hist_time < %s)",
            esc_attr( strtotime( '-' . $current_retention_period_count . ' day' ) )
        ) );
    }

}

function fetch_retention_period_count(): int {
    $retention_period_count = get_option( 'dt_error_log_retention_period_count' );

    return ( $retention_period_count > 0 ) ? intval( $retention_period_count ) : 30;
}
