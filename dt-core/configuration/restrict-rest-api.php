<?php
/**
 * Original Plugin Name: Disable REST API
 * Original Plugin URI: http://www.binarytemplar.com/disciple_tools
 * Original Description: Disable the use of the JSON REST API on your website to anonymous users
 * Original Version: 1.3
 * Original Author: Dave McHale
 * Original Author URI: http://www.binarytemplar.com
 * Original License: GPL2+
 */

/**
 * Integrated into Disciple Tools core to require authentication for all Rest API interactions.
 *
 * @since 0.1.0
 */

$dt_dra_current_wp_version = get_bloginfo( 'version' );

if ( version_compare( $dt_dra_current_wp_version, '4.7', '>=' ) ) {
    dt_dra_force_auth_error();
} else {
    dt_dra_disable_via_filters();
}

//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/
//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/
//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/

/**
 * This function is called if the current version of WordPress is 4.7 or above
 * Forcibly raise an authentication error to the REST API if the user is not logged in
 */
function dt_dra_force_auth_error() {
    add_filter( 'rest_authentication_errors', 'dt_dra_only_allow_logged_in_rest_access' );
}

/**
 * This function gets called if the current version of WordPress is less than 4.7
 * We are able to make use of filters to actually disable the functionality entirely
 */
function dt_dra_disable_via_filters() {

    // Filters for WP-API version 1.x
    add_filter( 'json_enabled', '__return_false' );
    add_filter( 'json_jsonp_enabled', '__return_false' );

    // Filters for WP-API version 2.x
    add_filter( 'rest_enabled', '__return_false' );
    add_filter( 'rest_jsonp_enabled', '__return_false' );

    // Remove REST API info from head and headers
    remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
    remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
    remove_action( 'template_redirect', 'rest_output_link_header', 11 );
}

/**
 * Returning an authentication error if a user who is not logged in tries to query tries to query a REST API endpoint that is not public
 *
 * @param  $access
 *
 * @return WP_Error
 */
function dt_dra_only_allow_logged_in_rest_access( $access ) {
    /*
     * Disable the built in Wordpress API because it opens all users and contacts to anyone who is logged in.
     */
    if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/wp-json/wp/' ) !== false ) {
        return new WP_Error( 'wp_api_disabled', 'The Wordpress built in API is disabled.', [ 'status' => rest_authorization_required_code() ] );
    }

    $authorized = false;
    /**
     * External integrations to a Disciple Tools site can be done through the /dt-public/ route, which is left open to non-logged in external access
     */
    if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/dt-public/' ) !== false ) {
        $authorized = true;
    }
    /**
     * JWT token authentication is also open on the Disciple Tools use of WP REST API
     */
    $path = dt_get_url_path();
    if ( $path == "wp-json/jwt-auth/v1/token" || $path == "wp-json/jwt-auth/v1/token/validate" ) {
        $authorized = true;
    }

    $authorized = apply_filters( 'dt_allow_rest_access', $authorized );

    // validate site to site transfer token
    $auth_token = null;
    if ( !$authorized && !is_user_logged_in() ){
        if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ){
            $auth_token = sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) );
        } elseif ( function_exists( "apache_request_headers" ) && isset( apache_request_headers()['Authorization'] ) ){
            $auth_token = sanitize_text_field( wp_unslash( apache_request_headers()['Authorization'] ) );
        }  elseif ( function_exists( "apache_request_headers" ) && isset( apache_request_headers()['authorization'] ) ){
            $auth_token = sanitize_text_field( wp_unslash( apache_request_headers()['authorization'] ) );
        }
        if ( $auth_token ) {
            $site_link_token = str_replace( 'Bearer ', '', $auth_token );
            $authorized = Site_Link_System::verify_transfer_token( $site_link_token );
            if ( !$authorized ){
                return new WP_Error( 'rest_cannot_access', 'Invalid token', [ 'status' => rest_authorization_required_code() ] );
            }
        }
    }

    /**
     * All other requests to the REST API require a person to be logged in to make a REST Request.
     */
    if ( !is_user_logged_in() && !$authorized ) {
        return new WP_Error( 'rest_cannot_access', 'Only authenticated users can access the REST API.', [ 'status' => rest_authorization_required_code() ] );
    }

    return $access;
}
