<?php

/**
 * Disciple_Tools_Google_Geocode_API
 *
 * @class   Disciple_Tools_Google_Geocode_API
 * @version 0.1.0
 * @package Disciple_Tools_Google_Geocode_API
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Google_Geocode_API

 */
class Disciple_Tools_Google_Geocode_API
{
    public static function key() {
        return dt_get_option( 'map_key' );
    }

    /**
     * Google geocoding service
     *
     * @param $address          string   Can be an address or a geolocation lat, lng
     * @param $type             string      Default is 'full_object', which returns full google response, 'coordinates only' returns array with coordinates_only
     *                          and 'core' returns an array of the core information elements of the google response.
     *
     * @return array|mixed|object|bool
     */
    public static function query_google_api( $address, $type = 'raw' )
    {
        $address = str_replace( '   ', ' ', $address );
        $address = str_replace( '  ', ' ', $address );
        $address = urlencode( trim( $address ) );
        $url_address = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . self::key();
        $details = json_decode( self::url_get_contents( $url_address ), true );

        if ( $details['status'] == 'ZERO_RESULTS' ) {
            return false;
        }
        else {
            switch ( $type ) {
                case 'validate':
                    return true;
                    break;
                case 'coordinates_only':
                    $g_lat = $details['results'][0]['geometry']['location']['lat'];
                    $g_lng = $details['results'][0]['geometry']['location']['lng'];

                    return [
                        'lng' => $g_lng,
                        'lat' => $g_lat,
                        'raw' => $details,
                    ];
                    break;
                case 'core':
                    $g_lat = $details['results'][0]['geometry']['location']['lat'];
                    $g_lng = $details['results'][0]['geometry']['location']['lng'];
                    $g_formatted_address = $details['results'][0]['formatted_address'];


                    return [
                        'lng' => $g_lng,
                        'lat' => $g_lat,
                        'formatted_address' => $g_formatted_address,
                        'raw' => $details,
                    ];
                    break;
                case 'all_points':
                    return [
                        'center' => $details['results'][0]['geometry']['location'],
                        'northeast' => $details['results'][0]['geometry']['bounds']['northeast'],
                        'southwest' => $details['results'][0]['geometry']['bounds']['southwest'],
                        'formatted_address' => $details['results'][0]['formatted_address'],
                        'raw' => $details,
                    ];
                    break;
                default:
                    return $details; // raw response
                    break;
            }
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

    /**
     * @param $ip_address
     *
     * @return array
     */
    public static function geocode_ip_address( $ip_address ) {
        if ( is_null( $ip_address ) || empty( $ip_address ) ) {
            $ip_address = self::get_real_ip_address();
        }

        $url_address = 'http://freegeoip.net/json/' . $ip_address;
        $details = json_decode( self::url_get_contents( $url_address ), true );

        $formatted_address = '';
        $formatted_address .= empty( $details['city'] ) ? '' : $details['city'];
        $formatted_address .= empty( $details['region_name'] ) ? '' : ', ' . $details['region_name'];
        $formatted_address .= empty( $details['zip_code'] ) ? '' : ' ' . $details['zip_code'];
        $formatted_address .= empty( $details['country_name'] ) ? '' : ' ' . $details['country_name'];

        return [
            'lng' => $details['longitude'],
            'lat' => $details['latitude'],
            'formatted_address' => $formatted_address,
        ];
    }

    /**
     * Check Google for address validation
     * @param $address
     * @return mixed
     */
    public static function check_for_valid_address( $address ) {
        $address = str_replace( '   ', ' ', $address );
        $address = str_replace( '  ', ' ', $address );
        $address = urlencode( trim( $address ) );
        $url_address = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . self::key();
        $details = json_decode( self::url_get_contents( $url_address ) );

        if ($details->status == 'ZERO_RESULTS' ) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * @return string
     */
    public static function get_real_ip_address()
    {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ))   //check ip from share internet
        {
            // @codingStandardsIgnoreLine
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ))   //to check ip is pass from proxy
        {
            // @codingStandardsIgnoreLine
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) )
        {
            // @codingStandardsIgnoreLine
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }


    public static function parse_raw_result( $item ) {
        switch( $item ) {
            case 'country':
                break;
            case 'country_short_name':
                break;
            case 'admin1':
                break;
            case 'admin2':
                break;
            case 'admin3':
                break;
            case 'locality':
                break;
            case 'neighborhood':
                break;
            case 'postal_code':
                break;
            case 'lnglat':
                break;
            case 'lat':
                break;
            case 'lng':
                break;
            case 'northeast':
                break;
            case 'northeast_lng':
                break;
            case 'northeast_lat':
                break;
            case 'southwest':
                break;
            case 'southwest_lng':
                break;
            case 'southwest_lat':
                break;
            case 'location_type':
                break;
            case 'address_components':
                break;
            case 'formatted_address':
                break;
            case 'geometry':
                break;
            case 'bounds':
                break;
            case 'viewport':
                break;
            case 'place_id':
                break;
            case 'political':
                break;
        }
    }

}
