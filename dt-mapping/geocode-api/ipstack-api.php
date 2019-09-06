<?php
/**
 * class DT_Ipstack_API
 * Collection of functions to handle geocode-api for IP Addresses
 *
 * @version 1.0 Initialize
 *          1.1 Added class_exists check and removed class initialization
 *          1.2 Added parser, user lookup, new endpoint, class rename
 *          1.3 Added admin_box
 */

if ( ! class_exists( 'DT_Ipstack_API' ) ) {
    class DT_Ipstack_API {
        /**
         * Ipstack Developer
         * @link https://ipstack.com/quickstart
         * @link https://ipstack.com/product
         */

        /*************************************************************************************************************
         * SETUP
         *************************************************************************************************************/

        public static $ipstack_endpoint = 'http://api.ipstack.com/';
        public static function get_key() {
            return get_option( 'dt_ipstack_api_key' );
        }
        public static function delete_key() {
            return delete_option( 'dt_ipstack_api_key' );
        }
        public static function update_key( $key ) {
            return update_option( 'dt_ipstack_api_key', $key, true );
        }

        /**************************************************************************************************************
         * GEOCODING
         *************************************************************************************************************/
        public static function geocode_ip_address( $ip_address, $type = null ) {
            $data = [];

            if ( ! self::check_valid_ip_address( $ip_address ) ) {
                return [ 'error' => 'Invalid IP Address' ];
            }

            if ( is_null( $ip_address ) || empty( $ip_address ) ) {
                $ip_address = self::get_real_ip_address();
            } else {
                $ip_address = trim( $ip_address );
            }

            $response = json_decode( self::url_get_contents( self::make_url( $ip_address ) ), true );
            if ( isset( $response['success'] ) && ! $response['success'] ) {
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
            $response = json_decode( self::url_get_contents( self::make_url( 'check' ) ), true );
            if ( isset( $response['success'] ) && ! $response['success'] ) {
                return [];
            }
            return $response;
        }

        /**************************************************************************************************************
         * ADMIN
         **************************************************************************************************************/
        public static function metabox_for_admin() {

            if ( isset( $_POST['ipstack_key'] )
                && ( isset( $_POST['ip_geocoding_key_nonce'] )
                    && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ip_geocoding_key_nonce'] ) ), 'ip_geocoding_key' . get_current_user_id() ) ) ) {

                $key = sanitize_text_field( wp_unslash( $_POST['ipstack_key'] ) );
                if ( empty( $key ) ) {
                    self::delete_key();
                } else {
                    self::update_key( $key );
                }
            }
            $key = self::get_key();
            $hidden_key = '**************' . substr( $key, -5, 5 );

            if ( self::is_active_ipstack_key() ) {
                $status_class = 'connected';
                $message = 'Successfully connected to IP Stack API.';
            } else {
                $status_class = 'not-connected';
                $message = 'API NOT AVAILABLE. CHECK YOR API KEY';
            }
            ?>
            <form method="post">
                <table class="widefat striped">
                    <thead>
                    <tr><th>IP Stack - IP Address to Geolocation Translation Service</th></tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?php wp_nonce_field( 'ip_geocoding_key' . get_current_user_id(), 'ip_geocoding_key_nonce' ); ?>
                            IP Stack API Token: <input type="text" class="regular-text" name="ipstack_key" value="<?php echo ( $key ) ? esc_attr( $hidden_key ) : ''; ?>" /> <button type="submit" class="button">Update</button>
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
                            <?php if ( empty( self::get_key() ) ) : ?>
                                <ol>
                                    <li>
                                        Go to <a href="https://ipstack.com">IpStack.com</a>.
                                    </li>
                                    <li>
                                        Register for a new free account (<a href="https://ipstack.com/product">IpStack.com</a>)<br>
                                        <em>(email required, no credit card required)</em>
                                    </li>
                                    <li>
                                        Once registered, go to your account home page. (<a href="https://ipstack.com/quickstart/">Account Page</a>)<br>
                                    </li>
                                    <li>
                                        Inside the section labeled "Your API Access Key". Copy this token.
                                    </li>
                                    <li>
                                        Paste the token into the "IP Stack API Token" field in the box above.
                                    </li>
                                </ol>
                            <?php endif; ?>

                            <?php if ( ! empty( self::get_key() ) ) : ?>
                                <a onclick="jQuery('#ip-geocode-report').toggle();" class="button">Show Report</a>
                                <div id="ip-geocode-report" style="display:none;">
                                    <?php print '<br>Your IP Response<br><pre>';
                                    print_r( self::geocode_current_visitor() );
                                    print '</pre>'; ?>
                                </div>

                            <?php endif; ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <br>

            <?php
        }

        /**************************************************************************************************************
         * UTILITIES
         **************************************************************************************************************/
        public static function check_valid_ip_address( $ip_address ) {
            return filter_var( $ip_address, FILTER_VALIDATE_IP );
        }

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

        public static function make_url( $ip_address ) {
            $key = self::get_key();
            return self::$ipstack_endpoint . $ip_address . '?access_key=' . $key;
        }

        public static function is_active_ipstack_key() : bool {
            $response = self::geocode_current_visitor();
            return ! empty( $response ); // false if empty response, true if successful geocode
        }

        public static function parse_raw_result( array $raw_response, $item ) {

            if ( empty( $raw_response ) || isset( $raw_response['error'] ) ) {
                return false;
            }

            switch ( $item ) {

                case 'ip':
                    return $raw_response['ip'] ?? false;
                    break;

                case 'type':
                    return $raw_response['type'] ?? false;
                    break;

                case 'continent_code':
                    return $raw_response['continent_code'] ?? false;
                    break;

                case 'continent_name':
                    return $raw_response['continent_name'] ?? false;
                    break;

                case 'country_code':
                    return $raw_response['country_code'] ?? false;
                    break;

                case 'country_name':
                    return $raw_response['country_name'] ?? false;
                    break;

                case 'region_code':
                    return $raw_response['region_code'] ?? false;
                    break;

                case 'region_name':
                    return $raw_response['region_name'] ?? false;
                    break;

                case 'city':
                    return $raw_response['city'] ?? false;
                    break;

                case 'zip':
                    return $raw_response['zip'] ?? false;
                    break;

                case 'lat':
                case 'latitude':
                    return $raw_response['latitude'] ?? false;
                    break;

                case 'lng':
                case 'longitude':
                    return (float) $raw_response['longitude'] ?? false;
                    break;

                case 'is_eu':
                    return $raw_response['location']['is_eu'] ?? false;
                    break;

                case 'languages':
                    return $raw_response['location']['languages'] ?? [];
                    break;

                case 'geoname_id':
                    return $raw_response['location']['geoname_id'] ?? [];
                    break;

                case 'capital':
                    return $raw_response['location']['capital'] ?? [];
                    break;

                case 'country_flag':
                    return $raw_response['location']['country_flag'] ?? [];
                    break;

                case 'country_flag_emoji':
                    return $raw_response['location']['country_flag_emoji'] ?? [];
                    break;

                case 'country_flag_emoji_unicode':
                    return $raw_response['location']['country_flag_emoji_unicode'] ?? [];
                    break;

                case 'calling_code':
                    return $raw_response['location']['calling_code'] ?? [];
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
    }
}

/***************************************************************************************************************
 * Expected Full Response

Array
(
    [ip] => 168.103.64.137
    [type] => ipv4
    [continent_code] => NA
    [continent_name] => North America
    [country_code] => US
    [country_name] => United States
    [region_code] => CO
    [region_name] => Colorado
    [city] => Highlands Ranch
    [zip] => 80129
    [latitude] => 39.548759460449
    [longitude] => -105.00215148926
    [location] => Array
        (
            [geoname_id] => 5425043
            [capital] => Washington D.C.
            [languages] => Array
                (
                    [0] => Array
                        (
                            [code] => en
                            [name] => English
                            [native] => English
                        )
                )

            [country_flag] => http://assets.ipstack.com/flags/us.svg
            [country_flag_emoji] => 🇺🇸
            [country_flag_emoji_unicode] => U+1F1FA U+1F1F8
            [calling_code] => 1
            [is_eu] =>
        )
)
 ****************************************************************************************************************/
