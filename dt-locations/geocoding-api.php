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

    public function __construct() {}

    /**
     * Google geocoding service
     *
     * Supply a `physical address` or for reverse lookup supply `latitude,longitude`
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

        $latlng = $details['latitude'] . ',' . $details['longitude'];
        $raw = self::query_google_api( $latlng );

        return [
            'lng' => $details['longitude'],
            'lat' => $details['latitude'],
            'formatted_address' => $formatted_address,
            'raw' => $raw
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

    /**
     * Parse the raw Google API response to get specific information
     *
     * @param $raw_response   (array)  full raw response from Google GeoCoding Lookup.
     * @param $item (string)
     *              country - (string) long country name
     *              country_short_name - (string) two letter country code
     *              admin1 - (string) long name of administrative level 1 (i.e. state level)
     *              admin2 - (string) long name of administrative level 2 (i.e. counties or provinces)
     *              admin3 - (string) long name of administrative level 3 (varies greatly between countries)
     *              locality - (string) long name of the locality (often, locality is city name or similar political unit)
     *              neighborhood - (string) long name of the neighborhood (not often present, except in first world countries)
     *              postal_code - (string)
     *              address_components - (array) all address components returned in google result
     *              formatted_address - (string)
     *              latlng - (string) location center coordinates formatted into a single string as `latitude,longitude`
     *              geometry - (array) full geometry section of google response
     *              bounds - (array) bounds include the northeast and southwest lat/lng
     *              viewport - (array) similar to bounds, but is sensitive to the best display for the location. ex. Might exclude distant islands for a country
     *              location - (array) contains lat/lng in array form
     *              lat - (string) latitude coordinates
     *              lng - (string) longitude coordinates
     *              northeast - (array) contains the northeast corner of the suggested viewport boundary of the location
     *              northeast_lat - (string)
     *              northeast_lng - (string)
     *              southwest - (array) contains the southwest corner of the suggested viewport boundary of the location
     *              southwest_lat - (string)
     *              southwest_lng - (string)
     *              location_type - (string) i.e. ROOFTOP or APPROXIMATE
     *              political - (array) returns all address components marked as political entities
     *              full - (array) returns the entire first result of the results array. Basically, $raw_response['results'][0]
     *
     * @return bool|array|string  returns array or string on success; returns false on fail
     */
    public static function parse_raw_result( array $raw_response, $item ) {

        if ( empty( $raw_response ) || ! isset( $raw_response['status'] ) || ! isset( $raw_response['results'][0] ) ) {
            return false;
        }
        if ( ! ( 'OK' == $raw_response['status'] ) ) {
            return false;
        }

        $raw = $raw_response['results'][0];

        switch ( $item ) {

            case 'country':
                foreach ( $raw['address_components'] as $component ) {
                    if ( 'country' == $component['types'][0] ) {
                        return $component['long_name'];
                    }
                }
                return false;
                break;

            case 'country_short_name':
                foreach ( $raw['address_components'] as $component ) {
                    if ( 'country' == $component['types'][0] ) {
                        return $component['short_name'];
                    }
                }
                return false;
                break;

            case 'admin1':
                foreach ( $raw['address_components'] as $component ) {
                    if ( 'administrative_area_level_1' == $component['types'][0] ) {
                        return $component['long_name'];
                    }
                }
                return false;
                break;

            case 'admin2':
                foreach ( $raw['address_components'] as $component ) {
                    if ( 'administrative_area_level_2' == $component['types'][0] ) {
                        return $component['long_name'];
                    }
                }
                return false;
                break;

            case 'admin3':
                foreach ( $raw['address_components'] as $component ) {
                    if ( 'administrative_area_level_3' == $component['types'][0] ) {
                        return $component['long_name'];
                    }
                }
                return false;
                break;

            case 'locality':
                foreach ( $raw['address_components'] as $component ) {
                    if ( 'locality' == $component['types'][0] ) {
                        return $component['long_name'];
                    }
                }
                return false;
                break;

            case 'neighborhood':
                foreach ( $raw['address_components'] as $component ) {
                    if ( 'neighborhood' == $component['types'][0] ) {
                        return $component['long_name'];
                    }
                }
                return false;
                break;

            case 'postal_code':
                foreach ( $raw['address_components'] as $component ) {
                    if ( 'postal_code' == $component['types'][0] ) {
                        return $component['long_name'];
                    }
                }
                return false;
                break;

            case 'address_components':
                return $raw['address_components'] ?? false;
                break;

            case 'formatted_address':
                return $raw['formatted_address'] ?? false;
                break;

            case 'latlng':
                $location = $raw['geometry']['location'] ?? false;
                if ( ! $location ) {
                    return false;
                }
                return $location['lat'] . ',' . $location['lng'];
                break;

            case 'geometry':
                return $raw['geometry'] ?? false;
                break;

            case 'bounds':
                return $raw['geometry']['bounds'] ?? false;
                break;

            case 'viewport':
                return $raw['geometry']['viewport'] ?? false;
                break;

            case 'location':
                return $raw['geometry']['location'] ?? false;
                break;

            case 'lat':
                return $raw['geometry']['location']['lat'] ?? false;
                break;

            case 'lng':
                return $raw['geometry']['location']['lng'] ?? false;
                break;

            case 'northeast':
                return $raw['geometry']['viewport']['northeast'] ?? false;
                break;

            case 'northeast_lat':
                return $raw['geometry']['viewport']['northeast']['lat'] ?? false;
                break;

            case 'northeast_lng':
                return $raw['geometry']['viewport']['northeast']['lng'] ?? false;
                break;

            case 'southwest':
                return $raw['geometry']['viewport']['southwest'] ?? false;
                break;

            case 'southwest_lat':
                return $raw['geometry']['viewport']['southwest']['lat'] ?? false;
                break;

            case 'southwest_lng':
                return $raw['geometry']['viewport']['southwest']['lng'] ?? false;
                break;

            case 'location_type':
                return $raw['geometry']['location_type'] ?? false;
                break;

            case 'place_id':
                return $raw['place_id'] ?? false;
                break;

            case 'political':
                $political = [];
                foreach ( $raw['address_components'] as $component ) {
                    $designation = $component['types'][1] ?? '';
                    if ( 'political' == $designation ) {
                        $political[] = $component;
                    }
                }
                return $political ?: false;
                break;

            case 'types':
                /**
                 * Will return the location level or type
                 * - country
                 * - administrative_area_level_1
                 * - administrative_area_level_2
                 * - administrative_area_level_3
                 * - administrative_area_level_4
                 * - locality
                 * - neighborhood
                 * - route
                 * - street_address
                 */
                return $raw['types'][0] ?? false;
                break;

            case 'self':
                /**
                 * "Self" returns the isolated searched element of any google result.
                 * If the queried item was a street address like 123 Street Name Blvd., Denver, CO 80126, "self" will return "123"
                 * If the queried item was "Phoenix, AZ, US", "self" will return "Phoenix".
                 *
                 * Using "self" is more reliable than using post title, because the post title can be changed.
                 */
                return $raw['address_components'][0]['long_name'];
                break;

            case 'self_full':
                /**
                 * Returns the full array address component.
                 */
                return $raw['address_components'][0];
                break;

            case 'full':
                return $raw;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     *
     *
     * @param array $raw_response
     * @param       $item
     *
     * @return bool
     */
    public static function test_raw_result( array $raw_response, $item ) : bool {

        if ( empty( $raw_response ) || ! isset( $raw_response['status'] ) || ! isset( $raw_response['results'][0] ) ) {
            return false;
        }
        if ( ! ( 'OK' == $raw_response['status'] ) ) {
            return false;
        }

        $raw = $raw_response['results'][0];

        switch ( $item ) {
            case 'is_country':
                return $raw['types'][0] == 'country' ;
                break;

            case 'is_admin1':
                return $raw['types'][0] == 'administrative_area_level_1' ;
                break;

            case 'is_admin2':
                return $raw['types'][0] == 'administrative_area_level_2' ;
                break;

            case 'is_admin3':
                return $raw['types'][0] == 'administrative_area_level_3' ;
                break;

            case 'is_admin4':
                return $raw['types'][0] == 'administrative_area_level_4' ;
                break;

            case 'locality':
                return $raw['types'][0] == 'locality' ;
                break;

            case 'neighborhood':
                return $raw['types'][0] == 'locality' ;
                break;

            case 'route':
                return $raw['types'][0] == 'locality' ;
                break;

            case 'street_address':
                return $raw['types'][0] == 'locality' ;
                break;


            default:
                return false;
                break;
        }
    }

    public static function check_valid_request_result( $raw_result ) : bool {
        if ( empty( $raw_result ) || ! isset( $raw_result['status'] ) || ! isset( $raw_result['results'][0] ) ) {
            return false;
        }

        if ( 'OK' == $raw_result['status'] && isset( $raw_result['results'][0] ) ) {
            return true;
        }
        return false;
    }


}
