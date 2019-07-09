<?php
/**
 * Location Grid List API
 * This api is intended for direct access and is build with light WP load. This API
 * does not provide reporting, but can provide fast list and geocoding returns by
 * loading the most minimal of WP services.
 */
$response = [];

// geocodes longitude and latitude and returns json array of geoname record
if ( isset( $_GET['type'] ) && isset( $_GET['longitude'] ) && isset( $_GET['latitude'] ) ) :

    // return json grid_id result from longitude/latitude
    if ( $_GET['type'] === 'geocode' ) {

        $level = null;
        if ( isset( $_GET['level'] ) ) {
            $level = $_GET['level'];
        }
        $longitude = $_GET['longitude'];
        $latitude =  $_GET['latitude'];

        require_once ('location-grid-geocoder.php');
        $geocoder = new Location_Grid_Geocoder();

        $response =  $geocoder->get_grid_id_by_lnglat( $longitude, $latitude, $level );

    }

endif; // html


/**
 * Return
 */
header('Content-type: application/json');
echo json_encode($response);
