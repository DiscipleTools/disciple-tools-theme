<?php
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


/**
 * Login page modifications
 *
 * @author Chasm Solutions
 * @package Disciple_Tools
 */

/*
 * Action and Filters
 */

/*
 * Functions
 */
// Change homepage url
function dt_my_login_logo_url() {
    return home_url();
}

