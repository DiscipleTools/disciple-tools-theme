<?php

/**
 * Disciple_Tools_Google_Geolocation
 *
 * @class   Disciple_Tools_Google_Geolocation
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools_Tabs
 * @author  Chasm.Solutions
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Google_Geolocation
 */
class Disciple_Tools_Google_Geolocation
{

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct()
    {

    } // End __construct()

    /**
     * @param $address          string   Can be an address or a geolocation lat, lng
     * @param $type             string      Default is 'full_object', which returns full google response, 'coordinates only' returns array with coordinates_only
     *                          and 'core' returns an array of the core information elements of the google response.
     *
     * @return array|mixed|object|bool
     */
    public static function query_google_api( $address, $type = 'full_object' )
    {

        $address = str_replace( '   ', ' ', $address );
        $address = str_replace( '  ', ' ', $address );
        $address = urlencode( trim( $address ) );
        $url_address = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . dt_get_option( 'map_key' );
        $details = json_decode( self::url_get_contents( $url_address ) );

        if ( $details->status == 'ZERO_RESULTS' ) {
            return 'ZERO_RESULTS';
        }

        if ( 'coordinates_only' == $type ) {

            $g_lat = $details->results[0]->geometry->location->lat;
            $g_lng = $details->results[0]->geometry->location->lng;

            return [ 'lng' => $g_lng, 'lat' => $g_lat ];
        }
        elseif ( 'core' == $type ) {
            $g_lat = $details->results[0]->geometry->location->lat;
            $g_lng = $details->results[0]->geometry->location->lng;
            $g_formatted_address = $details->results[0]->formatted_address;

            return [ 'lng' => $g_lng, 'lat' => $g_lat, 'formatted_address' => $g_formatted_address ];
        }
        elseif ( 'all_points' == $type ) {

            return [
                'center' => $details->results[0]->geometry->location,
                'northeast' => $details->results[0]->geometry->bounds->northeast,
                'southwest' => $details->results[0]->geometry->bounds->southwest,
                'formatted_address' => $details->results[0]->formatted_address,
            ];
        }
        else {
            return $details; // full_object returned
        }
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    public static function url_get_contents( $url )
    {
        if ( !function_exists( 'curl_init' ) ) {
            die( 'CURL is not installed!' );
        }
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $output = curl_exec( $ch );
        curl_close( $ch );

        return $output;
    }

}
