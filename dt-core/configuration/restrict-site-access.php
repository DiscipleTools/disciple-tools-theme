<?php

/**
 * Login workflow
 * security headers
 */


/**
 * Requires site-wide login
 */
function dt_please_log_in() {

    global $wpdb, $pagenow;
    if ( 'wp-login.php' === $pagenow ){
        return 1;
    }
    if ( dt_is_rest() ) {
        // rest requests are secured by restrict-rest-api.php
        return 1;
    }
    if ( apply_filters( 'dt_allow_non_login_access', false ) ){
        return 1;
    }
    if ( is_multisite() ) { // tests if user has access to current site in multi-site
        if ( 'wp-activate.php' === $pagenow ) {
            return 1;
        }
        if ( is_super_admin( get_current_user_id() ) ) {
            return 1;
        }
        else if ( empty( get_user_meta( get_current_user_id(), $wpdb->prefix . 'capabilities', true ) ) ) {
            wp_destroy_current_session();
            wp_clear_auth_cookie();
            auth_redirect();
            exit;
        }
    }
    if ( ! is_user_logged_in() ) {
        auth_redirect();
        exit;
    }
    return 1;
}

/**
 * Removes feeds via filters
 */
add_filter( 'the_content_feed', 'dt_remove_filtered_feed', 999 );
add_filter( 'the_excerpt_rss', 'dt_remove_filtered_feed', 999 );
add_filter( 'comment_text_rss', 'dt_remove_filtered_feed', 999 );
function dt_remove_filtered_feed() {
    return 'Feed restricted.';
}

/**
 * Removes feeds via actions
 */
add_action( 'do_feed', 'dt_remove_action_feed', 1 );
add_action( 'do_feed_rdf', 'dt_remove_action_feed', 1 );
add_action( 'do_feed_rss', 'dt_remove_action_feed', 1 );
add_action( 'do_feed_rss2', 'dt_remove_action_feed', 1 );
add_action( 'do_feed_atom', 'dt_remove_action_feed', 1 );
add_action( 'do_feed_rss2_comments', 'dt_remove_action_feed', 1 );
add_action( 'do_feed_atom_comments', 'dt_remove_action_feed', 1 );
function dt_remove_action_feed() {
    wp_die( 'Feed restricted.' );
}

/**
 * Removes pingback and a couple common DOS attacks strategies from header
 */
$xmlrpc_enabled = getenv( 'XMLRPC_ENABLED' );
function dt_remove_x_pingback_header( $headers ) {
    unset( $headers['X-Pingback'] );
    return $headers;
}
add_filter( 'wp_headers', 'dt_remove_x_pingback_header' );
function dt_block_xmlrpc_attacks( $methods ) {
    unset( $methods['pingback.ping'] );
    unset( $methods['pingback.extensions.getPingbacks'] );
    return $methods;
}
add_filter( 'xmlrpc_methods', 'dt_block_xmlrpc_attacks' );
if ( $xmlrpc_enabled === true || strtolower( $xmlrpc_enabled ) == 'true' ) {
    add_filter( 'xmlrpc_enabled', '__return_true' );
} else {
    add_filter( 'xmlrpc_enabled', '__return_false' );
}

/**
 * Removes header clutter
 */
function dt_head_cleanup() {
    // Remove category feeds
    remove_action( 'wp_head', 'feed_links_extra', 3 );
    // Remove post and comment feeds
    remove_action( 'wp_head', 'feed_links', 2 );
    // Remove EditURI link
    remove_action( 'wp_head', 'rsd_link' );
    // Remove Windows live writer
    remove_action( 'wp_head', 'wlwmanifest_link' );
    // Remove index link
    remove_action( 'wp_head', 'index_rel_link' );
    // Remove previous link
    remove_action( 'wp_head', 'parent_post_rel_link', 10 );
    // Remove start link
    remove_action( 'wp_head', 'start_post_rel_link', 10 );
    // Remove links for adjacent posts
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
    // Remove WP version
    remove_action( 'wp_head', 'wp_generator' );
} /* end Joints head cleanup */
add_action( 'init', 'dt_head_cleanup' );


/**
 * Conditional tag to see if we have a private blog.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function disciple_tools_is_private_blog() {
    return true;
}

/**
 * Conditional tag to see if we have a private feed.
 *
 * @since  1.0.0
 * @access public
 * @return bool
 */
function disciple_tools_is_private_feed() {
    return true;
}



/**
 * Blocks feed items if the user has selected the private feed feature.
 *
 * @since  0.2.0
 * @access public
 * @param  string  $content
 * @return string
 */
function disciple_tools_private_feed( $content ) {

    return disciple_tools_is_private_feed() ? disciple_tools_get_private_feed_message() : $content;
}

/**
 * Returns the private feed error message.
 *
 * @since  1.0.0
 * @access public
 * @return string
 */
function disciple_tools_get_private_feed_message() {

    return apply_filters( 'disciple_tools_feed_error_message', 'Restricted Feed' );
}


// Calling your own login css so you can style it
function disciple_tools_login_css() {
    dt_theme_enqueue_style( 'disciple_tools_login_css', 'dt-assets/build/css/login.min.css' );
}

// changing the logo link from wordpress.org to your site
function disciple_tools_login_url() {  return home_url(); }

// changing the alt text on the logo to show your site name
function disciple_tools_login_title() { return get_option( 'blogname' ); }

// calling it only on the login page
add_action( 'login_enqueue_scripts', 'disciple_tools_login_css', 10 );
add_filter( 'login_redirect',
    function( $url, $query, $user ) {
        if ( $url != admin_url() ){
            return $url;
        } else {
            return home_url();
        }
    },
    10,
3 );


//set security headers
add_action( 'send_headers', 'dt_security_headers_insert' );
// admin section doesn't have a send_headers action so we abuse init
add_action( 'admin_init', 'dt_security_headers_insert' );
// wp-login.php doesn't have a send_headers action so we abuse init
add_action( 'login_init', 'dt_security_headers_insert' );
/*
 * Add security headers
 */
function dt_security_headers_insert() {
    $xss_disabled = get_option( "dt_disable_header_xss" );
    $referer_disabled = get_option( "dt_disable_header_referer" );
    $content_type_disabled = get_option( "dt_disable_header_content_type" );
    $strict_transport_disabled = get_option( "dt_disable_header_strict_transport" );
    if ( !$xss_disabled ){
        header( "X-XSS-Protection: 1; mode=block" );
    }
    if ( !$referer_disabled ){
        header( "Referrer-Policy: same-origin" );
    }
    if ( !$content_type_disabled ){
        header( "X-Content-Type-Options: nosniff" );
    }
    if ( !$strict_transport_disabled && is_ssl() ){
        header( "Strict-Transport-Security: max-age=2592000" );
    }
//    header( "Content-Security-Policy: default-src 'self' https:; img-src 'self' https: data:; script-src https: 'self' 'unsafe-inline' 'unsafe-eval'; style-src  https: 'self' 'unsafe-inline'" );
}


add_filter( 'login_errors', 'login_error_messages' );
/**
 * change the error message if it is invalid_username or incorrect password
 *
 * @param $message string Error string provided by WordPress
 * @return $message string Modified error string
 */
function login_error_messages( $message ){
    global $errors;
    if ( isset( $errors->errors['invalid_username'] ) || isset( $errors->errors['incorrect_password'] ) || isset( $errors->errors['invalid_email'] ) ) {
        $message = __( 'ERROR: Invalid username/password combination.', 'disciple_tools' ) . ' ' .
            sprintf(
                ( '<a href="%1$s" title="%2$s">%3$s</a>?' ),
                site_url( 'wp-login.php?action=lostpassword', 'login' ),
                __( 'Reset password', 'disciple_tools' ),
                __( 'Lost your password', 'disciple_tools' )
            );
    }
    return $message;
}

add_action( 'login_init', 'dt_redirect_logged_in' );
//redirect already logged in users from the login page.
function dt_redirect_logged_in() {
    global $action;
    if ( 'logout' === $action || !is_user_logged_in()) {
        return;
    }
    if ( !empty( $_GET["redirect_to"] ) ) {
        wp_safe_redirect( esc_url_raw( wp_unslash( $_GET["redirect_to"] ) ) );
    } else {
        dt_route_front_page();
    }
    exit;
}

/**
 * Force password reset to remain on current site for multi-site installations.
 */
add_filter("lostpassword_url", function ( $url, $redirect) {

    $args = array( 'action' => 'lostpassword' );

    if ( !empty( $redirect ) ) {
        $args['redirect_to'] = $redirect;
    }

    return add_query_arg( $args, site_url( 'wp-login.php' ) );
}, 10, 2);

// fixes other password reset related urls
add_filter( 'network_site_url', function( $url, $path, $scheme) {

    if (stripos( $url, "action=lostpassword" ) !== false) {
        return site_url( 'wp-login.php?action=lostpassword', $scheme );
    }

    if (stripos( $url, "action=resetpass" ) !== false) {
        return site_url( 'wp-login.php?action=resetpass', $scheme );
    }

    return $url;
}, 10, 3 );

// fixes URLs in email that goes out.
function dt_multisite_retrieve_password_message( $message, $key, $user_login, $user_data) {
    $message = __( 'Someone has requested a password reset for the following account:', 'disciple_tools' ) . "\r\n\r\n";
    /* translators: %s: Site name. */
    $message .= sprintf( __( 'DT Site Name: %s', 'disciple_tools' ), wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) ) . "\r\n\r\n";
    /* translators: %s: User login. */
    $message .= sprintf( __( 'Username: %s', 'disciple_tools' ), $user_login ) . "\r\n\r\n";
    $message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', 'disciple_tools' ) . "\r\n\r\n";
    $message .= __( 'To reset your password, visit the following address:', 'disciple_tools' ) . "\r\n\r\n";
    $message .= '<' . site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'https' ) . ">\r\n";
    return $message;
}
add_filter( "retrieve_password_message", 'dt_multisite_retrieve_password_message', 99, 4 );

// fixes email title
add_filter("retrieve_password_title", function( $title) {
    return "[" . wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) . "] Password Reset";
});
//add_filter( 'wp_handle_upload_prefilter', 'dt_disable_file_upload' ); //this breaks uploading plugins and themes


/**
 * Filter permissions for super admin users
 * return $caps if allowed
 * add 'do_not_allow' to $caps if not allowed.
 */
add_filter( 'map_meta_cap', 'restrict_super_admin', 10, 4 );
function restrict_super_admin( $caps, $cap, $user_id, $args ){
    if ( is_multisite() && is_super_admin( $user_id ) ){
        $user = get_user_by( "ID", $user_id );
        $expected_roles = apply_filters( 'dt_set_roles_and_permissions', [] );
        $dt_roles = array_map( function ( $a ){
            return array_keys( $a["permissions"] );
        }, $expected_roles );
        $dt_permissions = array_merge( ...array_values( $dt_roles ) );
        $dt_permission_prefixes = [ "view_any_", "update_any", "delete_any", "access_" ];
        foreach ( $dt_permission_prefixes as $prefix ){
            if ( strpos( $cap, $prefix ) !== false ){
                $dt_permissions[] = $cap;
            }
        }
        //if it is not a D.T permission, continue as normal.
        if ( !in_array( $cap, $dt_permissions, true )){
            return $caps;
        }
        //limit the super admin to the actions the administrator or dt_admin can take on a site.
        $roles = empty( $user->roles ) ? [ "administrator", "dt_admin" ] : $user->roles;
        //limit the super admin to the the roles they have on that site.
        $has_cap = false;
        foreach ( $roles as $role_key ){
            $role = dt_multi_role_get_role( $role_key );
            if ( isset( $role->caps[$cap] ) && $role->caps[$cap] ){
                $has_cap = true;
            }
        }
        if ( !$has_cap ){
            $caps[] = 'do_not_allow';
        }
    }

    return $caps;
}
