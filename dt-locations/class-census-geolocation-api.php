<?php

/**
 * Disciple_Tools_Census_Geolocation
 *
 * @class   Disciple_Tools_Census_Geolocation
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools_Tabs
 * @author  Chasm.Solutions
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Census_Geolocation
 */
class Disciple_Tools_Census_Geolocation
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
     * Gets the census data query object using longitude and latitude
     *
     * @since  0.1.0
     *
     * @param  $lng
     * @param  $lat
     * @param  $type
     *
     * @return array|mixed|object
     */
    public static function query_census_api( $lng, $lat, $type = 'full_object' )
    {

        $tract_address = 'https://geocoding.geo.census.gov/geocoder/geographies/coordinates?x=' . $lng . '&y=' . $lat . '&benchmark=4&vintage=4&format=json';
        $census_result = json_decode( self::url_get_contents( $tract_address ) );

        if ( $census_result == '' || !isset( $census_result->result->geographies->{'Census Tracts'}[0]->STATE ) ) { /* Census API gives false errors. This is attempting to try a couple times before returning error. */

            $census_result = json_decode( file_get_contents( $tract_address ) );

            if ( $census_result == '' || !isset( $census_result->result->geographies->{'Census Tracts'}[0]->STATE ) ) {

                sleep( 1 ); // wait 1 second, then try again

                $census_result = json_decode( file_get_contents( $tract_address ) );

                if ( $census_result == '' || !isset( $census_result->result->geographies->{'Census Tracts'}[0]->STATE ) ) {
                    return 'ZERO_RESULTS';
                }
            }
        }

        if ( $type == 'core' ) {

            $state_code = $census_result->result->geographies->{'Census Tracts'}[0]->STATE;
            $tract_county = $census_result->result->geographies->{'Census Tracts'}[0]->COUNTY;
            $tract_geoid = $census_result->result->geographies->{'Census Tracts'}[0]->GEOID;
            $tract_lng = $census_result->result->geographies->{'Census Tracts'}[0]->CENTLON;
            $tract_lat = $census_result->result->geographies->{'Census Tracts'}[0]->CENTLAT;
            $tract_size = $census_result->result->geographies->{'Census Tracts'}[0]->AREALAND;

            if ( $tract_size > 1000000000 ) {
                $zoom = 8;
            } elseif ( $tract_size > 100000000 ) {
                $zoom = 10;
            } elseif ( $tract_size > 50000000 ) {
                $zoom = 12;
            } elseif ( $tract_size > 10000000 ) {
                $zoom = 13;
            } else {
                $zoom = 14;
            }

            return [
                'state'  => $state_code,
                'county' => $tract_county,
                'geoid'  => $tract_geoid,
                'lat'    => $tract_lat,
                'lng'    => $tract_lng,
                'size'   => $tract_size,
                'zoom'   => $zoom,
            ];
        } elseif ( $type == 'geoid' ) {
            if ( $census_result->result->geographies->{'Census Tracts'}[0] ) {
                return $tract_geoid = $census_result->result->geographies->{'Census Tracts'}[0]->GEOID;
            } else {
                return false;
            }
        } else {
            return $census_result; // full_object returned
        }
    }

    /**
     * @since  0.1.0
     *
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
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $output = curl_exec( $ch );
        curl_close( $ch );

        return $output;
    }

}
