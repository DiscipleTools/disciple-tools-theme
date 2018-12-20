<?php
/**
 * Requires site-wide login
 */
function dt_please_log_in() {
    if ( ! is_user_logged_in() ) {
        auth_redirect();
        exit;
    }
    if ( is_multisite() ) { // tests if user has access to current site in multi-site
        global $wpdb;
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
    return 1;
}
add_action( 'wp', 'dt_please_log_in', 0 );

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

# Redirects users to the login page.
add_action( 'template_redirect', 'disciple_tools_please_log_in', 0 );

# Disable content in feeds if the feed should be private.
add_filter( 'the_content_feed', 'disciple_tools_private_feed', 95 );
add_filter( 'the_excerpt_rss', 'disciple_tools_private_feed', 95 );
add_filter( 'comment_text_rss', 'disciple_tools_private_feed', 95 );

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
 * Redirects users that are not logged in to the 'wp-login.php' page.
 *
 * @since  0.1.0
 * @access public
 * @return void
 */
function disciple_tools_please_log_in() {

    // Check if the private blog feature is active and if the user is not logged in.
    if ( ! is_user_logged_in() ) {

        // Redirect to the login page.
        auth_redirect();
        exit;
    }
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



// Change homepage url
function dt_my_login_logo_url() {
    return home_url();
}