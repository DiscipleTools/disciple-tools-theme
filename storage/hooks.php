<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'dt_storage_connections', function( $connections ){
    // Prefer new single-connection option
    $conn = get_option( 'dt_storage_connection', [] );
    if ( is_array( $conn ) && !empty( $conn ) && ( $conn['enabled'] ?? false ) ) {
        $connections[] = $conn;
        return $connections;
    }
    return $connections;
}, 10, 1 );

add_filter( 'dt_storage_connections_enabled', function( $response, $id ){
    // Check against new single connection
    $conn = get_option( 'dt_storage_connection', [] );
    if ( is_array( $conn ) && !empty( $conn ) && isset( $conn['id'] ) && $conn['id'] === $id ) {
        return (bool) ( $conn['enabled'] ?? false );
    }
    return false;
}, 10, 2 );
