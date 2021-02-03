<?php
/**
 * Location Grid List API
 * This api is intended for direct access and is build with light WP load. This API
 * does not provide reporting, but can provide fast list and geocoding returns by
 * loading the most minimal of WP services.
 */

if ( defined( 'ABSPATH' ) ) { exit; }
/**
 * @link https://stackoverflow.com/questions/45421976/wordpress-rest-api-slow-response-time
 *       https://deliciousbrains.com/wordpress-rest-api-vs-custom-request-handlers/
 *
 * @version 1.0 Initialization
 */

define( 'DOING_AJAX', true );

//Tell WordPress to only load the basics
define( 'SHORTINIT', 1 );

// Setup
if ( ! isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
    exit( 'missing server info' );
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'; //@phpcs:ignore

if ( ! defined( 'WP_CONTENT_URL' ) ) {
    define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
}
$dir = __DIR__;
if ( strpos( $dir, 'wp-content/themes' ) ) {
    $mapping_url = ABSPATH . 'wp-content/themes/' . get_option( 'template' ) . '/dt-mapping/';
}  else {
    $mapping_url = basename( plugin_dir_path( dirname( __FILE__, 2 ) ) ); // @todo
}

if ( file_exists( $mapping_url . 'geocode-api/location-grid-geocoder.php' ) ) {
    require_once( 'geocode-api/location-grid-geocoder.php' ); // Location grid geocoder
} else {
    echo json_encode( [ 'error' => 'did not find geocoder file' ] );
    return;
}

// register global database
global $wpdb;
$wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
$wpdb->dt_location_grid_meta = $wpdb->prefix . 'dt_location_grid_meta';


$geocoder = new Location_Grid_Geocoder();

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

        $response = $geocoder->get_grid_id_by_lnglat( $longitude, $latitude, $country_code, $level );

        header( 'Content-type: application/json' );
        echo json_encode( $response );
    }

    // possible list
    if ( $_GET['type'] === 'possible_matches' ) {

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

        $response = $geocoder->get_possible_matches_by_lnglat( $longitude, $latitude, $country_code );

        header( 'Content-type: application/json' );
        echo json_encode( $response );
    }



endif; // html

// geocodes bounding box
if ( isset( $_GET['type'] ) && isset( $_GET['north_latitude'] ) && isset( $_GET['south_latitude'] ) && isset( $_GET['west_longitude'] ) && isset( $_GET['east_longitude'] ) && isset( $_GET['nonce'] ) ) :

    // ids within bounding box
    if ( $_GET['type'] === 'match_within_bbox' ) {

        $level = null;
        if ( isset( $_GET['level'] ) ) {
            $level = sanitize_text_field( wp_unslash( $_GET['level'] ) );
        }

        $north_latitude  = sanitize_text_field( wp_unslash( $_GET['north_latitude'] ) );
        $south_latitude  = sanitize_text_field( wp_unslash( $_GET['south_latitude'] ) );
        $west_longitude  = sanitize_text_field( wp_unslash( $_GET['west_longitude'] ) );
        $east_longitude  = sanitize_text_field( wp_unslash( $_GET['east_longitude'] ) );

        $response = $geocoder->get_matches_within_bbox( $north_latitude, $south_latitude, $west_longitude, $east_longitude, $level );

        header( 'Content-type: application/json' );
        echo json_encode( $response );
    }

endif;

exit();
