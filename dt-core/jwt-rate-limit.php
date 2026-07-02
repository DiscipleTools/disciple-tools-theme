<?php
/**
 * Brute-force throttle for the JWT authentication token endpoint.
 *
 * The /jwt-auth/v1/token endpoint verifies credentials with no built-in rate
 * limiting, so it can be used for unthrottled online password guessing. This
 * caps repeated failures from a single client IP within a time window. It is
 * scoped to the JWT token endpoint only — wp-login and XML-RPC are untouched —
 * so it does not overlap with login-form limiters. Sites running a dedicated
 * security plugin (e.g. Wordfence) that already covers REST authentication can
 * disable it with: add_filter( 'dt_enable_jwt_login_throttle', '__return_false' ).
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The client IP used to bucket throttle counters. Defaults to the TCP peer
 * (REMOTE_ADDR), which a request cannot spoof via headers. Sites behind a
 * trusted reverse proxy can supply the forwarded client IP via the filter.
 *
 * @return string
 */
function dt_jwt_throttle_client_ip() {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    return (string) apply_filters( 'dt_jwt_throttle_client_ip', $ip );
}

/**
 * Transient key holding the recent-failure count for the current client IP.
 *
 * @return string
 */
function dt_jwt_throttle_key() {
    return 'dt_jwt_throttle_' . md5( dt_jwt_throttle_client_ip() );
}

/**
 * Whether the current request targets the JWT token-mint endpoint (not /validate).
 *
 * @param WP_REST_Request|null $request
 * @return bool
 */
function dt_is_jwt_token_request( $request = null ) {
    if ( $request instanceof WP_REST_Request ) {
        return $request->get_route() === '/jwt-auth/v1/token';
    }
    $path = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    return strpos( $path, 'jwt-auth/v1/token' ) !== false && strpos( $path, 'token/validate' ) === false;
}

/**
 * Reject token requests from a client IP that has exceeded the failure threshold.
 */
function dt_jwt_throttle_block( $result, $server, $request ) {
    if ( $result !== null ) {
        return $result;
    }
    if ( !apply_filters( 'dt_enable_jwt_login_throttle', true ) || !dt_is_jwt_token_request( $request ) ) {
        return $result;
    }
    $max = (int) apply_filters( 'dt_jwt_login_throttle_max_attempts', 10 );
    if ( (int) get_transient( dt_jwt_throttle_key() ) >= $max ) {
        return new WP_Error(
            'jwt_auth_too_many_attempts',
            __( 'Too many failed login attempts. Please try again later.', 'disciple_tools' ),
            [ 'status' => 429 ]
        );
    }
    return $result;
}
add_filter( 'rest_pre_dispatch', 'dt_jwt_throttle_block', 10, 3 );

/**
 * Count a failed credential attempt against the client IP, for token requests only.
 */
function dt_jwt_throttle_record_failure() {
    if ( !apply_filters( 'dt_enable_jwt_login_throttle', true ) || !dt_is_jwt_token_request() ) {
        return;
    }
    $window = (int) apply_filters( 'dt_jwt_login_throttle_window', 15 * MINUTE_IN_SECONDS );
    $key = dt_jwt_throttle_key();
    set_transient( $key, (int) get_transient( $key ) + 1, $window );
}
add_action( 'wp_login_failed', 'dt_jwt_throttle_record_failure' );

/**
 * Clear the failure counter once a token is successfully issued.
 */
function dt_jwt_throttle_clear_on_success( $data, $user ) {
    delete_transient( dt_jwt_throttle_key() );
    return $data;
}
add_filter( 'jwt_auth_token_before_dispatch', 'dt_jwt_throttle_clear_on_success', 10, 2 );
