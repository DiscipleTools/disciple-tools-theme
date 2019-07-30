<?php
/**
 * Location Grid List API
 * This api is intended for direct access and is build with light WP load. This API
 * does not provide reporting, but can provide fast list and geocoding returns by
 * loading the most minimal of WP services.
 */

/**
 * @link https://stackoverflow.com/questions/45421976/wordpress-rest-api-slow-response-time
 *       https://deliciousbrains.com/wordpress-rest-api-vs-custom-request-handlers/
 */

define( 'DOING_AJAX', true );

//Tell WordPress to only load the basics
define( 'SHORTINIT', 1 );

//get path of wp-load.php and load it
if ( ! isset( $_SERVER['CONTEXT_DOCUMENT_ROOT'] ) ) {
    exit();
}

require_once filter_var( $_SERVER['CONTEXT_DOCUMENT_ROOT'], FILTER_SANITIZE_URL ) . '/wp-load.php'; //@phpcs:ignore

if ( ! defined( 'WP_CONTENT_URL' ) ) {
    define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
}

// register global database
global $wpdb;
$wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';

// geocodes longitude and latitude and returns json array of location_grid record
if ( isset( $_GET['type'] ) && isset( $_GET['longitude'] ) && isset( $_GET['latitude'] ) && isset( $_GET['nonce'] ) ) :

    // return json grid_id result from longitude/latitude
    if ( $_GET['type'] === 'geocode' ) {

        $level = null;
        if ( isset( $_GET['level'] ) ) {
            $level = sanitize_text_field( wp_unslash( $_GET['level'] ) );
        }
        $country_code = null;
        if ( isset( $_GET['country_code'] ) ) {
            $country_code = sanitize_text_field( wp_unslash( $_GET['country_code'] ) );
        }
        $longitude = sanitize_text_field( wp_unslash( $_GET['longitude'] ) );
        $latitude  = sanitize_text_field( wp_unslash( $_GET['latitude'] ) );

        require_once( '../dt-core/global-functions.php' );
        require_once( 'location-grid-geocoder.php' ); // Location grid geocoder
        $geocoder = new Location_Grid_Geocoder();

        $response = $geocoder->get_grid_id_by_lnglat( $longitude, $latitude, $country_code );

        header( 'Content-type: application/json' );
        echo json_encode( $response );
    }

endif; // html

exit();
