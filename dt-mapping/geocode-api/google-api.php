<?php
/**
 * Disciple_Tools_Google_Geocode_API
 *
 * @class   Disciple_Tools_Google_Geocode_API
 */

/**
 * @version 1.5
 *
 * @since 1.0 raw query, ip lookup
 *        1.1 add map_key and rewrite for array in query_google_api
 *        1.2 add query with components, add refers lookup, add parse_raw_results
 *        1.3 moved keys and options within class
 *        1.4 added keys in parse function
 *        1.5 added error check for ip address lookup
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Google_Geocode_API
 */
if ( ! class_exists( 'Disciple_Tools_Google_Geocode_API' ) ) {
    class Disciple_Tools_Google_Geocode_API {
        public function __construct() {
        }

        public static function get_key() {
            return get_option( 'dt_google_map_key' );
        }

        public static function update_key( string $key ) {
            return update_option( 'dt_google_map_key', $key, true );
        }

        public static function delete_key(){
            return delete_option( 'dt_google_map_key' );
        }

        /**
         * Google geocode-api service
         * Supply a `physical address` or for reverse lookup supply `latitude,longitude`
         *
         * @param $address          string   Can be an address or a geolocation lat, lng
         * @param $type             string      Default is 'full_object', which returns full google response, 'coordinates only' returns array with coordinates_only
         *                          and 'core' returns an array of the core information elements of the google response.
         *
         * @return array|mixed|object|bool
         */
        public static function query_google_api( $address, $type = 'raw' ) {
            $address     = str_replace( '   ', ' ', $address );
            $address     = str_replace( '  ', ' ', $address );
            $address     = urlencode( trim( $address ) );
            $url_address = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . self::get_key();
            $details     = json_decode( self::url_get_contents( $url_address ), true );

            if ( ! isset( $details['status'] ) || $details['status'] === 'ZERO_RESULTS' ) {
                return false;
            } else {
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
                        $g_lat               = $details['results'][0]['geometry']['location']['lat'];
                        $g_lng               = $details['results'][0]['geometry']['location']['lng'];
                        $g_formatted_address = $details['results'][0]['formatted_address'];

                        return [
                            'lng'               => $g_lng,
                            'lat'               => $g_lat,
                            'formatted_address' => $g_formatted_address,
                            'raw'               => $details,
                        ];
                        break;
                    case 'all_points':
                        return [
                            'center'            => $details['results'][0]['geometry']['location'],
                            'northeast'         => $details['results'][0]['geometry']['bounds']['northeast'],
                            'southwest'         => $details['results'][0]['geometry']['bounds']['southwest'],
                            'formatted_address' => $details['results'][0]['formatted_address'],
                            'raw'               => $details,
                        ];
                        break;
                    default:
                        return $details; // raw response
                        break;
                }
            }
        }

        public static function query_google_api_with_components( $address, $components = [] ) {
            $address = str_replace( '   ', ' ', $address );
            $address = str_replace( '  ', ' ', $address );
            $address = urlencode( trim( $address ) );

            $components = wp_parse_args( $components, [
                'country' => '',
            ] );

            $component_string = '';
            $i                = 0;
            foreach ( $components as $key => $item ) {
                if ( ! empty( $components ) ) {
                    if ( ! ( 0 == $i ) ) {
                        $component_string .= '|';
                    }
                    $component_string .= $key . ':' . $item;
                }
            }

            $url_address = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&components=' . $component_string . '&key=' . self::get_key();
            $details     = json_decode( self::url_get_contents( $url_address ), true );

            if ( ! isset( $details['status'] ) || $details['status'] === 'ZERO_RESULTS' ) {
                return false;
            } else {
                return $details;
            }
        }

        /**
         * Use Latitude Longitude to find political structures
         *
         * @param        $latlng
         * @param string $result_type
         *
         * @return array|bool|mixed|object
         */
        public static function query_google_api_reverse( $latlng, $result_type = 'locality' ) {
            $latlng = trim( $latlng );
            $latlng = str_replace( ' ', '', $latlng );

            $url_address = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . $latlng . '&result_type=' . $result_type . '&key=' . self::get_key();
            $details     = json_decode( self::url_get_contents( $url_address ), true );

            if ( ! isset( $details['status'] ) || $details['status'] === 'ZERO_RESULTS' ) {
                return false;
            } else {
                return $details; // raw response
            }
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


        /**************************************************************************************************************
         * ADMIN
         **************************************************************************************************************/
        public static function metabox_for_admin() {

            if ( isset( $_POST['google_key'] )
                && ( isset( $_POST['google_geocoding_key_nonce'] )
                    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['google_geocoding_key_nonce'] ) ), 'google_geocoding_key' . get_current_user_id() ) ) ) {

                $key = sanitize_text_field( wp_unslash( $_POST['google_key'] ) );
                if ( empty( $key ) ) {
                    self::delete_key();
                } else {
                    self::update_key( $key );
                }
            }
            $key = self::get_key();

            $google_key_active_state = self::is_active_google_key();
            if ( $google_key_active_state['success'] ) {
                $status_class = 'connected';
                $message      = 'Successfully connected to Google Maps API.';
            } else {

                // As a no-google-key is an ok state; no need to flag as an error
                if ( ! empty( $key ) ) {
                    $status_class = 'not-connected';
                    $message      = ! empty( $google_key_active_state['message'] ) ? $google_key_active_state['message'] : 'API NOT CONFIGURED.';
                } else {
                    $status_class = 'connected';
                    $message      = '';
                }
            }
            ?>
            <form method="post">
                <table class="widefat striped">
                    <thead>
                    <tr><th>Google Geocoding Service (optional)</th></tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            For some countries, the address lookup service provided by the Open Street Maps (Mapbox) service may not sufficiently support
                            addresses or addresses in certain languages. If this is the case for your country, you can add the Google Maps Geocoding API
                            on top of the MapBox service. The system will still run Mapbox maps for metrics, but the search for location provide on records,
                            will use the Google APi to lookup addresses.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php wp_nonce_field( 'google_geocoding_key' . get_current_user_id(), 'google_geocoding_key_nonce' ); ?>
                            Google API Key: <input type="text" class="regular-text" name="google_key" value="<?php echo ( $key ) ? esc_attr( $key ) : ''; ?>" /> <button type="submit" class="button">Update</button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p id="reachable_source" class="<?php echo esc_attr( $status_class ) ?>">
                                <?php echo esc_html( $message ); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <a onclick="jQuery('#google-geocode-instructions').toggle();" class="button">Show Instructions</a>
                            <ol id="google-geocode-instructions" style="display:none;">
                                <li>
                                    Go to <a href="https://console.cloud.google.com">https://console.cloud.google.com</a>.
                                </li>
                                <li>
                                    Register with your Google Account or Gmail Account
                                </li>
                                <li>
                                    Once registered, create a new project.<br>
                                </li>
                                <li>
                                    Then go to APIs & Services > Credentials and "Create Credentials" API Key. Copy this key.
                                </li>
                                <li>
                                    Paste the key into the "Google API Key" field in the box above here in the Disciple.Tools Mapping Admin.
                                </li>
                                <li>
                                    Again, in Google Cloud Console, in APIs & Services go to Library. Find and enable: (1) Maps Javascript API,
                                    (2) Places API, and (3) GeoCoding API.
                                </li>
                                <li>
                                    Lastly, in  in Credentials for the API key it is recommended in the settings of the API key to be set "None" for Application Restrictions
                                    and "Don't restrict key" in API Restrictions.
                                </li>
                            </ol>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <br>

            <?php
        }

        /**
         * Parse the raw Google API response to get specific information
         *
         * @param $raw_response (array)  full raw response from Google GeoCoding Lookup.
         * @param $item         (string)
         *                      country - (string) long country name
         *                      country_short_name - (string) two letter country code
         *                      admin1 - (string) long name of administrative level 1 (i.e. state level)
         *                      admin2 - (string) long name of administrative level 2 (i.e. counties or provinces)
         *                      admin3 - (string) long name of administrative level 3 (varies greatly between countries)
         *                      locality - (string) long name of the locality (often, locality is city name or similar political unit)
         *                      neighborhood - (string) long name of the neighborhood (not often present, except in first world countries)
         *                      postal_code - (string)
         *                      address_components - (array) all address components returned in google result
         *                      formatted_address - (string)
         *                      latlng - (string) location center coordinates formatted into a single string as `latitude,longitude`
         *                      geometry - (array) full geometry section of google response
         *                      bounds - (array) bounds include the northeast and southwest lat/lng
         *                      viewport - (array) similar to bounds, but is sensitive to the best display for the location. ex. Might exclude distant islands for a country
         *                      location - (array) contains lat/lng in array form
         *                      lat - (string) latitude coordinates
         *                      lng - (string) longitude coordinates
         *                      northeast - (array) contains the northeast corner of the suggested viewport boundary of the location
         *                      northeast_lat - (string)
         *                      northeast_lng - (string)
         *                      southwest - (array) contains the southwest corner of the suggested viewport boundary of the location
         *                      southwest_lat - (string)
         *                      southwest_lng - (string)
         *                      location_type - (string) i.e. ROOFTOP or APPROXIMATE
         *                      political - (array) returns all address components marked as political entities
         *                      full - (array) returns the entire first result of the results array. Basically, $raw_response['results'][0]
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

                case 'administrative_area_level_1':
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

                case 'administrative_area_level_2':
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

                case 'administrative_area_level_3':
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

                    return $political ? : false;
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

                case 'base_name':
                    /**
                     * "Self" returns the isolated searched element of any google result.
                     * If the queried item was a street address like 123 Street Name Blvd., Denver, CO 80126, "self" will return "123"
                     * If the queried item was "Phoenix, AZ, US", "self" will return "Phoenix".
                     * Using "self" is more reliable than using post title, because the post title can be changed.
                     */
                    return $raw['address_components'][0]['long_name'];
                    break;

                case 'base_name_full':
                    /**
                     * Returns the full array address component.
                     */
                    return $raw['address_components'][0];
                    break;

                case 'full': // useful for running a raw result though the array check at the beginning of the function
                    return $raw;
                    break;

                default:
                    return false;
                    break;
            }
        }

        /**
         * @param array $raw_response
         * @param       $item
         *
         * @return bool
         */
        public static function test_raw_result( array $raw_response, $item ): bool {

            if ( empty( $raw_response ) || ! isset( $raw_response['status'] ) || ! isset( $raw_response['results'][0] ) ) {
                return false;
            }
            if ( ! ( 'OK' == $raw_response['status'] ) ) {
                return false;
            }

            $raw = $raw_response['results'][0];

            switch ( $item ) {
                case 'is_country':
                    return $raw['types'][0] == 'country';

                case 'is_admin1':
                    return $raw['types'][0] == 'administrative_area_level_1';

                case 'is_admin2':
                    return $raw['types'][0] == 'administrative_area_level_2';

                case 'is_admin3':
                    return $raw['types'][0] == 'administrative_area_level_3';

                case 'is_admin4':
                    return $raw['types'][0] == 'administrative_area_level_4';

                case 'locality':
                case 'neighborhood':
                case 'route':
                case 'street_address':
                    return $raw['types'][0] == 'locality';

                default:
                    return false;
            }
        }

        public static function check_valid_request_result( $raw_result ): bool {
            if ( empty( $raw_result ) || ! isset( $raw_result['status'] ) || ! isset( $raw_result['results'][0] ) ) {
                return false;
            }

            if ( 'OK' == $raw_result['status'] && isset( $raw_result['results'][0] ) ) {
                return true;
            }

            return false;
        }

        public static function is_active_google_key(): array {
            $key = self::get_key();

            // Query all available api call types for - Denver, CO, USA
            $response_api                 = self::query_google_api( 'Denver, CO, USA' );
            $response_api_reverse         = self::query_google_api_reverse( '39.742043,-104.991531' ); // Denver, CO, USA - Lat,Long
            $response_api_with_components = self::query_google_api_with_components( 'Denver, CO, USA' );

            // Return response accordingly, based on returning status
            $default_error_msg = 'API NOT CONFIGURED.';
            if ( ( $response_api === false ) && ( $response_api_reverse === false ) && ( $response_api_with_components === false ) ) {
                return [
                    'success' => false,
                    'message' => $default_error_msg
                ];
            }

            // Individually check response statuses
            if ( ! isset( $response_api['status'] ) || $response_api['status'] !== 'OK' ) {
                return [
                    'success' => false,
                    'message' => ( isset( $response_api['error_message'] ) && ! empty( $response_api['error_message'] ) ) ? $response_api['error_message'] : $default_error_msg
                ];
            }
            if ( ! isset( $response_api_reverse['status'] ) || $response_api_reverse['status'] !== 'OK' ) {
                return [
                    'success' => false,
                    'message' => ( isset( $response_api_reverse['error_message'] ) && ! empty( $response_api_reverse['error_message'] ) ) ? $response_api_reverse['error_message'] : $default_error_msg
                ];
            }
            if ( ! isset( $response_api_with_components['status'] ) || $response_api_with_components['status'] !== 'OK' ) {
                return [
                    'success' => false,
                    'message' => ( isset( $response_api_with_components['error_message'] ) && ! empty( $response_api_with_components['error_message'] ) ) ? $response_api_with_components['error_message'] : $default_error_msg
                ];
            }

            // If this point is reached, then simply return true
            return [
                'success' => true,
                'message' => ''
            ];
        }

        public static function load_google_geocoding_scripts() {
            $api_key = self::get_key();
            wp_enqueue_script( 'google-search-widget', 'https://maps.googleapis.com/maps/api/js?libraries=places&key='.$api_key, [ 'jquery', 'mapbox-gl', 'shared-functions' ], '1', false );
        }
    }
}
/**
 * NOTES on the result designations
 * @link https://developers.google.com/maps/documentation/geocoding/intro#geocoding
 * @link https://developers.google.com/maps/documentation/javascript/geocoding
 * street_address indicates a precise street address.
 * route indicates a named route (such as "US 101").
 * intersection indicates a major intersection, usually of two major roads.
 * political indicates a political entity. Usually, this type indicates a polygon of some civil administration.
 * country indicates the national political entity, and is typically the highest order type returned by the Geocoder.
 * administrative_area_level_1 indicates a first-order civil entity below the country level. Within the United States, these administrative levels are states. Not all nations exhibit these administrative levels. In most cases, administrative_area_level_1 short names will closely match ISO 3166-2 subdivisions and other widely circulated lists; however this is not guaranteed as our geocode-api results are based on a variety of signals and location data.
 * administrative_area_level_2 indicates a second-order civil entity below the country level. Within the United States, these administrative levels are counties. Not all nations exhibit these administrative levels.
 * administrative_area_level_3 indicates a third-order civil entity below the country level. This type indicates a minor civil division. Not all nations exhibit these administrative levels.
 * administrative_area_level_4 indicates a fourth-order civil entity below the country level. This type indicates a minor civil division. Not all nations exhibit these administrative levels.
 * administrative_area_level_5 indicates a fifth-order civil entity below the country level. This type indicates a minor civil division. Not all nations exhibit these administrative levels.
 * colloquial_area indicates a commonly-used alternative name for the entity.
 * locality indicates an incorporated city or town political entity.
 * ward indicates a specific type of Japanese locality, to facilitate distinction between multiple locality components within a Japanese address.
 * sublocality indicates a first-order civil entity below a locality. For some locations may receive one of the additional types: sublocality_level_1 to sublocality_level_5. Each sublocality level is a civil entity. Larger numbers indicate a smaller geographic area.
 * neighborhood indicates a named neighborhood
 * premise indicates a named location, usually a building or collection of buildings with a common name
 * subpremise indicates a first-order entity below a named location, usually a singular building within a collection of buildings with a common name
 * postal_code indicates a postal code as used to address postal mail within the country.
 * natural_feature indicates a prominent natural feature.
 * airport indicates an airport.
 * park indicates a named park.
 * point_of_interest indicates a named point of interest. Typically, these "POI"s are prominent local entities that don't easily fit in another category, such as "Empire State Building" or "Statue of Liberty."
 */
