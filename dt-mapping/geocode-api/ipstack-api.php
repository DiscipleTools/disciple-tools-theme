<?php
/**
 * class DT_Ipstack_API
 * Collection of functions to handle geocode-api for IP Addresses
 *
 * @version 1.0 Initialize
 *          1.1 Added class_exists check and removed class initialization
 */

if ( ! class_exists( 'DT_Ipstack_API' ) ) {
    class DT_Ipstack_API {
        /**
         * Holds the values to be used in the fields callbacks
         */
        public static $base_url = 'https://ipapi.co/';

        /**
         * @link https://ipapi.co/#pricing
         *
         * @param $ip_address
         *
         * @return bool|array False on fail, or result array on success
         */
        public static function geocode_ip_address( $ip_address, $type = 'lnglat' ) {
            $data = [];

            if ( is_null( $ip_address ) || empty( $ip_address ) ) {
                $ip_address = self::get_real_ip_address();
            } else {
                $ip_address = trim( $ip_address );
            }

            switch ( $type ) {
                case 'lnglat':
                    $response = explode( ',', json_decode( self::url_get_contents( self::$base_url . $ip_address . '/latlng' ), true ) );

                    $data['latitude'] = $response[0];
                    $data['longitude'] = $response[1];
                    $data['lnglat'] = $data['longitude'] . ',' . $data['latitude'];

                    break;

                case 'full':
                default:
                    $response = json_decode( self::url_get_contents( self::$base_url . $ip_address . '/json' ), true );

                    $data['latitude'] = $response['latitude'];
                    $data['longitude'] = $response['longitude'];
                    $data['lnglat'] = $data['longitude'] . ',' . $data['latitude'];
                    $data['full'] = $response;

                    break;
            }

            if ( ! $data ) {
                return false;
            }

            return $data;

            /* Sample Full Response
             {
                "ip" : "8.8.8.8"
                "city" : "Mountain View"
                "region" : "California"
                "region_code" : "CA"
                "country" : "US"
                "country_name" : "United States"
                "continent_code" : "NA"
                "in_eu" : false
                "postal" : "94035"
                "latitude" : 37.386
                "longitude" : -122.0838
                "timezone" : "America/Los_Angeles"
                "utc_offset" : "-0700"
                "country_calling_code" : "+1"
                "currency" : "USD"
                "languages" : "en-US,es-US,haw"
                "asn" : AS15169
                "org" : "Google LLC"
             }
             */

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


    }
}
