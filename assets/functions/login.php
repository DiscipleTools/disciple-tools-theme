<?php
// Calling your own login css so you can style it
function disciple_tools_login_css() {
    wp_enqueue_style( 'disciple_tools_login_css', get_template_directory_uri() . '/build/css/login.min.css', false );
}

// changing the logo link from wordpress.org to your site
function disciple_tools_login_url() {  return home_url(); }

// changing the alt text on the logo to show your site name
function disciple_tools_login_title() { return get_option( 'blogname' ); }

// calling it only on the login page
add_action( 'login_enqueue_scripts', 'disciple_tools_login_css', 10 );
add_filter( 'login_headerurl', 'disciple_tools_login_url' );
add_filter( 'login_headertitle', 'disciple_tools_login_title' );
add_filter( 'login_redirect', function( $url, $query, $user ) {
    return home_url();
}, 10, 3 );


/**
 * Login page modifications
 *
 * @author Chasm Solutions
 * @package Disciple_Tools
 */

/*
 * Action and Filters
 */
add_filter( 'login_headerurl', 'my_login_logo_url', 10 );
add_filter( 'login_headertitle', 'my_login_logo_url_title', 10 );

/*
 * Functions
 */
// Change homepage url
function my_login_logo_url() {
    return home_url();
}

// Change title
function my_login_logo_url_title() {
    return 'Disciple_Tools';
}
