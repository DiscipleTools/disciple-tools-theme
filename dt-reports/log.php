<?php

if ( defined( 'ABSPATH' ) ) {
    exit;
}

function _dt_network_doing_it_wrong( string $message ) {
    header( 'Content-type: application/json' );
    echo json_encode( array( 'error' => $message ) );
    exit();
}
if ( !function_exists( 'dt_write_log' ) ) {
    function dt_write_log( $log ) {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}
if ( ! function_exists( 'dt_recursive_sanitize_array' ) ) {
    function dt_recursive_sanitize_array( array $array ) : array {
        foreach ( $array as $key => &$value ) {
            if ( is_array( $value ) ) {
                $value = dt_recursive_sanitize_array( $value );
            }
            else {
                $value = sanitize_text_field( wp_unslash( $value ) );
            }
        }
        return $array;
    }
}


/**
 * @link https://stackoverflow.com/questions/45421976/wordpress-rest-api-slow-response-time
 *       https://deliciousbrains.com/wordpress-rest-api-vs-custom-request-handlers/
 *
 * @version 1.0 Initialization
 */

define( 'DOING_AJAX', true );

//Tell WordPress to only load the basics
define( 'SHORTINIT', 1 );

/**** LOAD NEEDED FILES *****/
if ( !isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
    _dt_network_doing_it_wrong( 'missing server info' );
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'; //@phpcs:ignore
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/post.php'; //@phpcs:ignore
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/meta.php'; //@phpcs:ignore

if ( !defined( 'WP_CONTENT_URL' ) ) {
    define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
}

require_once( 'reports.php' );

$mapping_path = ABSPATH . 'wp-content/themes/disciple-tools-theme/dt-mapping/';
if ( file_exists( $mapping_path . 'geocode-api/location-grid-geocoder.php' ) ) {
    require_once( $mapping_path. 'geocode-api/location-grid-geocoder.php' ); // Location grid geocoder
    require_once( $mapping_path. 'location-grid-meta.php' ); // Location grid geocoder
    require_once( $mapping_path. 'mapping-queries.php' ); // Location grid geocoder
} else {
    _dt_network_doing_it_wrong( 'did not find geocoder file' );
}
if ( file_exists( $mapping_path . 'geocode-api/ipstack-api.php' ) ) {
    require_once( $mapping_path. 'geocode-api/ipstack-api.php' ); // Location grid geocoder
} else {
    _dt_network_doing_it_wrong( 'did not find ipstack file' );
}
$theme_path = ABSPATH . 'wp-content/themes/disciple-tools-theme/';
if ( file_exists( $theme_path . 'dt-core/admin/site-link-post-type.php' ) ) {
    require_once( $theme_path. 'dt-core/admin/site-link-post-type.php' ); // Location grid geocoder
} else {
    _dt_network_doing_it_wrong( 'did not find site linking file' );
}
if ( file_exists( $theme_path . 'dt-core/logging/class-activity-api.php' ) ) {
    require_once( $theme_path. 'dt-core/logging/class-activity-api.php' ); // Location grid geocoder
} else {
    _dt_network_doing_it_wrong( 'did not find site linking file' );
}

$params = $_POST; // @phpcs:ignore

// Validate Transfer Token
if ( ! Site_Link_System::verify_transfer_token( $params['transfer_token'] ) ) {
    _dt_network_doing_it_wrong( 'transfer token failed' );
}

// Qualify data payload
if ( ! ( isset( $params['data'] ) && ! empty( $params['data'] ) && is_array( $params['data'] ) ) ) {
    _dt_network_doing_it_wrong( 'no data id found or data is not an array' );
}

//    [
//        'type' => 'type',
//        'subtype' => 'subtype',
//        'location_type' => 'ip', // ip, grid, lnglat
//        'location_value' => '184.96.211.187',
//        'payload' => [
//            'initials' => 'CC',
//            'group_size' => '3',
//            'country' => 'United States',
//            'language' => 'en',
//            'note' => 'This is the full note'.time()
//        ],
//        'timestamp' => ''
//    ],

// LOOP THROUGH ACTIVITY ELEMENTS
$process_status = Disciple_Tools_Reports::insert_public_log( $params['data'] );

header( 'Content-type: application/json' );
echo json_encode( $process_status );
exit();



/************* EXAMPLE PAYLOAD **********************************
$params = [
'transfer_token' => Site_Link_System::create_transfer_token_for_site( Site_Link_System::instance()->get_site_key_by_id(8739) ),
'data' => [
[
'site_id' => hash('sha256', 'site_id1'.rand ( 0 , 19999 )),
'action' => 'action',
'category' => 'ip',
'location_type' => 'ip', // ip, grid, lnglat
'location_value' => '184.96.211.187',
'payload' => [
    'initials' => 'CC',
    'group_size' => '3',
    'country' => 'United States',
    'language' => 'en',
    'note' => 'This is the full note'.time()
],
'timestamp' => ''
],
[
'site_id' => hash('sha256', 'site_id5'.rand ( 0 , 19999 )),
'action' => 'action',
'category' => 'grid',
'location_type' => 'grid', // ip, grid, lnglat
'location_value' => '100364508',
'payload' => [
'initials' => 'CC',
'group_size' => '3',
'country' => 'United States',
'language' => 'en',
'note' => 'This is the full note'.time()
],
'timestamp' => ''
],
[
'site_id' => hash('sha256', 'site_id2'.rand ( 0 , 19999 )),
'action' => 'action',
'category' => 'lnglat',
'location_type' => 'lnglat', // ip, grid, lnglat
'location_value' => [
'lng' => '-104.968',
'lat' => '39.7075',
'level' => 'admin2',
],
'payload' => [
'initials' => 'CC',
'group_size' => '3',
'country' => 'Slovenia',
'language' => 'en',
'note' => 'This is the full note'.time()
],
'timestamp' => ''
],
[
'site_id' => hash('sha256', 'site_id3'.rand ( 0 , 19999 )),
'action' => 'action',
'category' => 'complete',
'location_type' => 'complete', // ip, grid, lnglat
'location_value' => [
'lng' => '-104.968',
'lat' => '39.7075',
'level' => 'admin2',
'label' => 'Denver, Colorado, US',
'grid_id' => '100364508'
], // ip, grid, lnglat
'payload' => [
'initials' => 'CC',
'group_size' => '3',
'country' => 'United States',
'language' => 'en',
'note' => 'This is the full note'.time()
],
'timestamp' => ''
],
]
];
 ************* END EXAMPLE PAYLOAD *******************************/
