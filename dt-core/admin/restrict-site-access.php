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
