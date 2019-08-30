<?php
/**
 * class DT_Ipapi_API
 * Collection of functions to handle geocode-api for IP Addresses
 *
 * @version 1.0 Initialize
 *          1.1 Added class_exists check and removed class initialization
 *          1.2 Added parser, user lookup, new endpoint, class rename
 */

if ( ! class_exists( 'DT_Ipapi_API' ) ) {
    class DT_Ipapi_API {

        public static $base_url = 'https://ipapi.co/';
        /**
         * IpAPI Developer
         * @link https://ipapi.co/api/#introduction
         */

        /**
         * @link https://ipapi.co/#pricing
         *
         * @param $ip_address
         * @param $type
         *
         * @return array False on fail, or result array on success
         */
        public static function geocode_ip_address( $ip_address, $type = null ) {
            $data = [];

            if ( ! self::check_valid_ip_address( $ip_address ) ) {
                return ['error' => 'Invalid IP Address'];
            }

            if ( is_null( $ip_address ) || empty( $ip_address ) ) {
                $ip_address = self::get_real_ip_address();
            } else {
                $ip_address = trim( $ip_address );
            }

            $response = json_decode( self::url_get_contents( self::$base_url . $ip_address . '/json' ), true );
            if ( $response['error'] ?? false ) {
                return $response;
            }

            switch ( $type ) {
                case 'lnglat':
                    $data['latitude'] = self::parse_raw_result( $response, 'latitude' );
                    $data['longitude'] = self::parse_raw_result( $response, 'longitude' );
                    $data['lnglat'] = self::parse_raw_result( $response, 'lnglat' );
                    return $data;
                    break;

                default:
                    return $response;
                    break;
            }
        }

        public static function geocode_current_visitor() : array {
            $results = json_decode( self::url_get_contents( self::$base_url . '/json' ), true );
            if ( $response['error'] ?? false ) {
                return [];
            }
            return $results;

            /**
             * Successful Response
                {
                    "ip": "71.218.39.51",
                    "city": "Denver",
                    "region": "Colorado",
                    "region_code": "CO",
                    "country": "US",
                    "country_name": "United States",
                    "continent_code": "NA",
                    "in_eu": false,
                    "postal": "80211",
                    "latitude": 39.7628,
                    "longitude": -105.0263,
                    "timezone": "America/Denver",
                    "utc_offset": "-0600",
                    "country_calling_code": "+1",
                    "currency": "USD",
                    "languages": "en-US,es-US,haw,fr",
                    "asn": "AS209",
                    "org": "CenturyLink Communications, LLC"
                }
             */
        }

        public static function check_valid_ip_address( $ip_address ) {
            return filter_var( $ip_address, FILTER_VALIDATE_IP );
        }

        /**
         * @return string
         */
        public static function get_real_ip_address() {
            $ip = '';
            if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )   //check ip from share internet
            {
                $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
            } else if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )   //to check ip is pass from proxy
            {
                $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
            } else if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
                $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
            }

            return $ip;
        }

        /**
         * @param $url
         *
         * @return mixed
         */
        public static function url_get_contents( $url ) {
            if ( ! function_exists( 'curl_init' ) ) {
                die( 'CURL is not installed!' );
            }
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            $output = curl_exec( $ch );
            curl_close( $ch );

            return $output;
        }

        public static function parse_raw_result( array $raw_response, $item ) {

            if ( empty( $raw_response ) || $raw_response['error'] ?? false ) {
                return false;
            }

            /**
             * Expected Full Response
                {
                "ip": "162.144.179.239",
                "city": "Provo",
                "region": "Utah",
                "region_code": "UT",
                "country": "US",
                "country_name": "United States",
                "continent_code": "NA",
                "in_eu": false,
                "postal": "84606",
                "latitude": 40.2342,
                "longitude": -111.6442,
                "timezone": "America/Denver",
                "utc_offset": "-0600",
                "country_calling_code": "+1",
                "currency": "USD",
                "languages": "en-US,es-US,haw,fr",
                "asn": "AS46606",
                "org": "Unified Layer"
                }
             */

            switch ( $item ) {

                case 'city':
                    return $raw_response['city'] ?? false;
                    break;

                case 'region':
                    return $raw_response['region'] ?? false;
                    break;

                case 'region_code':
                    return $raw_response['region_code'] ?? false;
                    break;

                case 'country':
                    return $raw_response['country'] ?? false;
                    break;

                case 'country_name':
                    return $raw_response['country_name'] ?? false;
                    break;

                case 'continent_code':
                    return $raw_response['continent_code'] ?? false;
                    break;

                case 'in_eu':
                    return $raw_response['in_eu'] ?? false;
                    break;

                case 'postal':
                    return $raw_response['postal'] ?? false;
                    break;

                case 'lat':
                case 'latitude':
                    return $raw_response['latitude'] ?? false;
                    break;

                case 'lng':
                case 'longitude':
                    return (float) $raw_response['longitude'] ?? false;
                    break;

                case 'timezone':
                    return $raw_response['timezone'] ?? false;
                    break;

                case 'utc_offset':
                    return $raw_response['utc_offset'] ?? false;
                    break;

                case 'country_calling_code':
                    return $raw_response['country_calling_code'] ?? false;
                    break;

                case 'currency':
                    return $raw_response['currency'] ?? false;
                    break;

                case 'languages':
                    return $raw_response['languages'] ?? false;
                    break;

                case 'asn':
                    return $raw_response['asn'] ?? false;
                    break;

                case 'org':
                    return $raw_response['org'] ?? false;
                    break;

                case 'lnglat':
                    $longitude = self::parse_raw_result( $raw_response, 'longitude' );
                    $latitude = self::parse_raw_result( $raw_response, 'latitude' );
                    if ( ! $longitude || ! $latitude ) {
                        return false;
                    }
                    return $longitude . ',' . $latitude;
                    break;

                case 'latlng':
                    $longitude = self::parse_raw_result( $raw_response, 'longitude' );
                    $latitude = self::parse_raw_result( $raw_response, 'latitude' );
                    if ( ! $longitude || ! $latitude ) {
                        return false;
                    }
                    return $latitude . ',' . $longitude;
                    break;

                case 'full': // useful for running a raw result though the array check at the beginning of the function
                    return $raw_response;
                    break;

                default:
                    return false;
                    break;
            }
        }

        /**
         * Expected Full Response
        {
        "ip": "162.144.179.239",
        "city": "Provo",
        "region": "Utah",
        "region_code": "UT",
        "country": "US",
        "country_name": "United States",
        "continent_code": "NA",
        "in_eu": false,
        "postal": "84606",
        "latitude": 40.2342,
        "longitude": -111.6442,
        "timezone": "America/Denver",
        "utc_offset": "-0600",
        "country_calling_code": "+1",
        "currency": "USD",
        "languages": "en-US,es-US,haw,fr",
        "asn": "AS46606",
        "org": "Unified Layer"
        }
         */
        /**
         * LatLong Response
         * 40.234200,-111.644200
         */
        /**
         * Error Response
        {
        "ip": "162.1449999.179.239",
        "error": true,
        "reason": "Invalid IP Address"
        }
         */

    }
}
