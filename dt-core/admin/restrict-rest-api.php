<?php
/**
 * Original Plugin Name: Disable REST API
 * Original Plugin URI: http://www.binarytemplar.com/disable-json-api
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
    add_action( 'rest_api_init', "dt_add_api_routes" );
    add_action( 'init', 'dt_setup_jwt' );
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
function dt_dra_force_auth_error()
{
    add_filter( 'rest_authentication_errors', 'dt_dra_only_allow_logged_in_rest_access' );
}

/**
 * This function gets called if the current version of WordPress is less than 4.7
 * We are able to make use of filters to actually disable the functionality entirely
 */
function dt_dra_disable_via_filters()
{

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
function dt_dra_only_allow_logged_in_rest_access( $access )
{
    $is_public = false;
    $is_jwt = false;
    if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/dt-public/' ) !== false ) {
        $is_public = true;
    }
    if ( $_SERVER['REQUEST_URI'] == "/wp-json/jwt-auth/v1/token" || $_SERVER['REQUEST_URI'] == "/wp-json/jwt-auth/v1/token/validate" ) {
        $is_jwt = true;
    }
    if ( !is_user_logged_in() && !$is_public && !$is_jwt ) {
        return new WP_Error( 'rest_cannot_access', __( 'Only authenticated users can access the REST API.', 'disable-json-api' ), [ 'status' => rest_authorization_required_code() ] );
    }

    return $access;
}

/**
 * Setup the rest api routes for the plugin
 */
function dt_add_api_routes()
{
    // setup the facebook endpoints
    Disciple_Tools::instance()->facebook_integration->add_api_routes();
}

/**
 * Define key for JWT authentication
 */
function dt_setup_jwt()
{
    if ( !defined( 'JWT_AUTH_SECRET_KEY' ) ) {
        $iv = get_option( "my_jwt_key" );
        // @codingStandardsIgnoreLine
        define( 'JWT_AUTH_SECRET_KEY', $iv );
    }
}

