<?php
/**
 * Brute-force throttle for the JWT authentication token endpoint.
 *
 * The /jwt-auth/v1/token endpoint verifies credentials with no built-in rate
 * limiting, so it can be used for unthrottled online password guessing. This
 * caps repeated failures two ways within a time window: per client IP, and per
 * targeted account. The per-account cap backstops distributed guessing that
 * rotates source IPs to stay under the per-IP cap; it uses a higher threshold
 * and longer window so ordinary mistyped logins do not trip it. It is scoped to
 * the JWT token endpoint only — wp-login and XML-RPC are untouched — so it does
 * not overlap with login-form limiters. Sites running a dedicated security
 * plugin (e.g. Wordfence) that already covers REST authentication can disable
 * it with: add_filter( 'dt_enable_jwt_login_throttle', '__return_false' ).
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
 * Transient key holding the recent-failure count for an existing account.
 *
 * @param int $user_id
 * @return string
 */
function dt_jwt_throttle_account_key( $user_id ) {
    return 'dt_jwt_throttle_uid_' . (int) $user_id;
}

/**
 * Transient key holding the recent-failure count for a targeted account, or an
 * empty string when no usable identifier was supplied. The identifier is
 * normalized with sanitize_user(), as wp_authenticate() does before firing
 * wp_login_failed, then resolved to the account it names (login first, then
 * email, matching core's authenticate order) so a username and an email for
 * the same user share one bucket. An identifier matching no account is
 * bucketed by its lower-cased form so guesses against unknown names still count.
 *
 * @param mixed $username Username or email as submitted.
 * @return string
 */
function dt_jwt_throttle_user_key( $username ) {
    if ( !is_string( $username ) && !is_numeric( $username ) ) {
        return '';
    }
    $username = sanitize_user( (string) $username );
    if ( $username === '' ) {
        return '';
    }
    $user = get_user_by( 'login', $username );
    if ( !$user && is_email( $username ) ) {
        $user = get_user_by( 'email', $username );
    }
    if ( $user instanceof WP_User ) {
        return dt_jwt_throttle_account_key( $user->ID );
    }
    return 'dt_jwt_throttle_user_' . md5( strtolower( $username ) );
}

/**
 * The error returned to a client that has exceeded a failure threshold.
 *
 * @return WP_Error
 */
function dt_jwt_throttle_too_many_error() {
    return new WP_Error(
        'jwt_auth_too_many_attempts',
        __( 'Too many failed login attempts. Please try again later.', 'disciple_tools' ),
        [ 'status' => 429 ]
    );
}

/**
 * Whether the current request targets the JWT token-mint endpoint (not /validate).
 *
 * @param WP_REST_Request|null $request
 * @return bool
 */
function dt_is_jwt_token_request( $request = null ) {
    // WordPress dispatches REST routes case-insensitively, so match the route
    // the same way; a case-varied path (e.g. /JWT-Auth/v1/Token) still reaches
    // the real token handler and must not slip past the throttle.
    if ( $request instanceof WP_REST_Request ) {
        return strtolower( $request->get_route() ) === '/jwt-auth/v1/token';
    }
    $path = isset( $_SERVER['REQUEST_URI'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : '';
    return strpos( $path, 'jwt-auth/v1/token' ) !== false && strpos( $path, 'token/validate' ) === false;
}

/**
 * Reject token requests once the client IP or the targeted account has exceeded
 * its failure threshold.
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
        return dt_jwt_throttle_too_many_error();
    }
    $account_max = (int) apply_filters( 'dt_jwt_login_throttle_max_account_attempts', 20 );
    $user_key = dt_jwt_throttle_user_key( $request->get_param( 'username' ) );
    if ( $user_key !== '' && (int) get_transient( $user_key ) >= $account_max ) {
        return dt_jwt_throttle_too_many_error();
    }
    return $result;
}
add_filter( 'rest_pre_dispatch', 'dt_jwt_throttle_block', 10, 3 );

/**
 * Count a failed credential attempt against both the client IP and the targeted
 * account, for token requests only.
 *
 * @param string $username Username or email the failed attempt targeted.
 */
function dt_jwt_throttle_record_failure( $username = '' ) {
    if ( !apply_filters( 'dt_enable_jwt_login_throttle', true ) || !dt_is_jwt_token_request() ) {
        return;
    }
    $window = (int) apply_filters( 'dt_jwt_login_throttle_window', 15 * MINUTE_IN_SECONDS );
    $key = dt_jwt_throttle_key();
    set_transient( $key, (int) get_transient( $key ) + 1, $window );

    $user_key = dt_jwt_throttle_user_key( $username );
    if ( $user_key !== '' ) {
        $account_window = (int) apply_filters( 'dt_jwt_login_throttle_account_window', HOUR_IN_SECONDS );
        set_transient( $user_key, (int) get_transient( $user_key ) + 1, $account_window );
    }
}
add_action( 'wp_login_failed', 'dt_jwt_throttle_record_failure', 10, 1 );

/**
 * Clear the targeted account's failure counter once a token is successfully issued.
 */
function dt_jwt_throttle_clear_on_success( $data, $user ) {
    // Only the authenticated account's counter is cleared, never the per-IP
    // counter. A success can be an attacker authenticating to an account they
    // control, so clearing the shared IP bucket on success would let them reset
    // it at will and spray guesses across other accounts from one IP unthrottled.
    if ( $user instanceof WP_User ) {
        delete_transient( dt_jwt_throttle_account_key( $user->ID ) );
    }
    return $data;
}
add_filter( 'jwt_auth_token_before_dispatch', 'dt_jwt_throttle_clear_on_success', 10, 2 );
