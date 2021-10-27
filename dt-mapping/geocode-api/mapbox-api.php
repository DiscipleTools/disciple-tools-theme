<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * DT_Mapbox_API
 *
 * @version 1.0 Initialize
 *          1.1 Major revisions and function additions
 */

if ( ! class_exists( 'DT_Mapbox_API' ) ) {
    /**
     * Class DT_Mapbox_API
     */
    class DT_Mapbox_API {

        /**
         * Mapbox Endpoint
         */
        public static $mapbox_endpoint = 'https://api.mapbox.com/geocoding/v5/mapbox.places/';

        /**
         * Mapbox GL for loading in the header
         */
        public static $mapbox_gl_js = 'https://api.mapbox.com/mapbox-gl-js/v1.1.0/mapbox-gl.js';
        public static $mapbox_gl_css = 'https://api.mapbox.com/mapbox-gl-js/v1.1.0/mapbox-gl.css';
        public static $mapbox_gl_version = '1.1.0';

        /**
         * Mapbox Geocoder loaded in the body
         */
        public static $mb_geocoder_js = 'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.0/mapbox-gl-geocoder.min.js';
        public static $mb_geocoder_css = 'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.0/mapbox-gl-geocoder.css';
        public static $mb_geocoder_version = '4.4.0';

        /**
         * Mapbox Key options storage
         */
        public static function get_key() {
            return get_option( 'dt_mapbox_api_key' );
        }
        public static function delete_key() {
            return delete_option( 'dt_mapbox_api_key' );
        }
        public static function update_key( $key ) {
            return update_option( 'dt_mapbox_api_key', $key, true );
        }

        /**
         * Geocoder Scripts for Echo
         */
        public static function geocoder_scripts() {
            // Mapbox requires the goecoder placed in the body at the top of the map.
            // @codingStandardsIgnoreStart
            ?>
            <script src="<?php echo esc_url_raw( self::$mb_geocoder_js ) ?>"></script>
            <link rel='stylesheet' href="<?php echo esc_url_raw( self::$mb_geocoder_css ) ?>" type='text/css' />
            <?php
            // @codingStandardsIgnoreEnd
        }

        /**
         * Forward Address Lookup
         * @param $address
         * @param null $country_code
         * @return array|bool|mixed|object
         */
        public static function forward_lookup( $address, $country_code = null ) {
            $address = str_replace( ';', ' ', $address );
            $address = utf8_uri_encode( $address );

            if ( $country_code ) {
                $url = self::$mapbox_endpoint . $address . '.json?types=address&access_token=' . self::get_key();
            } else {
                $url = self::$mapbox_endpoint  . $address . '.json?country=' . $country_code . '&types=address&access_token=' . self::get_key();
            }

            /** @link https://codex.wordpress.org/Function_Reference/wp_remote_get */
            $response = wp_remote_get( esc_url_raw( $url ) );
            $data_result = wp_remote_retrieve_body( $response );

            if ( ! $data_result ) {
                return false;
            }
            return json_decode( $data_result, true );
        }


        public static function lookup( $search_string, $type = 'full', $country_code = null ) {
            $search_string = str_replace( ';', ' ', $search_string );
            $search_string = utf8_uri_encode( $search_string );

            // country, region, place, district, postcode, locality, neighborhood, address, poi, poi.landmark
            switch ( $type ) {
                case 'country':
                    if ( $country_code ) {
                        $url = self::$mapbox_endpoint  . $search_string . '.json?country=' . $country_code . '&types=country&access_token=' . self::get_key();
                    } else {
                        $url = self::$mapbox_endpoint . $search_string . '.json?types=country&access_token=' . self::get_key();
                    }
                    break;
                case 'region':
                    if ( $country_code ) {
                        $url = self::$mapbox_endpoint  . $search_string . '.json?country=' . $country_code . '&types=country,region&access_token=' . self::get_key();
                    } else {
                        $url = self::$mapbox_endpoint . $search_string . '.json?types=country,region&access_token=' . self::get_key();
                    }
                    break;
                case 'address':
                    if ( $country_code ) {
                        $url = self::$mapbox_endpoint  . $search_string . '.json?country=' . $country_code . '&types=address&access_token=' . self::get_key();
                    } else {
                        $url = self::$mapbox_endpoint . $search_string . '.json?types=address&access_token=' . self::get_key();
                    }
                    break;
                case 'poi':
                    if ( $country_code ) {
                        $url = self::$mapbox_endpoint  . $search_string . '.json?country=' . $country_code . '&types=poi&access_token=' . self::get_key();
                    } else {
                        $url = self::$mapbox_endpoint . $search_string . '.json?types=poi&access_token=' . self::get_key();
                    }
                    break;
                case 'place':
                    if ( $country_code ) {
                        $url = self::$mapbox_endpoint  . $search_string . '.json?country=' . $country_code . '&types=place&access_token=' . self::get_key();
                    } else {
                        $url = self::$mapbox_endpoint . $search_string . '.json?types=place&access_token=' . self::get_key();
                    }
                    break;
                case 'full':
                default:
                    if ( $country_code ) {
                        $url = self::$mapbox_endpoint  . $search_string . '.json?country=' . $country_code . 'types=country,region,place,address&&access_token=' . self::get_key();
                    } else {
                        $url = self::$mapbox_endpoint . $search_string . '.json?types=country,region,place,address&access_token=' . self::get_key();
                    }
                    break;
            }


            /** @link https://codex.wordpress.org/Function_Reference/wp_remote_get */
            $response = wp_remote_get( esc_url_raw( $url ) );
            $data_result = wp_remote_retrieve_body( $response );

            if ( ! $data_result ) {
                return false;
            }
            return json_decode( $data_result, true );
        }

        public static function reverse_lookup( $longitude, $latitude ) {
            $url         = self::$mapbox_endpoint  . $longitude . ',' . $latitude . '.json?access_token=' . self::get_key();
            $response = wp_remote_get( esc_url_raw( $url ) );
            $data_result = wp_remote_retrieve_body( $response );

            if ( ! $data_result ) {
                return false;
            }
            return json_decode( $data_result, true );
        }

        /**
         * Returns country_code from longitude and latitude
         *
         * @param $longitude
         * @param $latitude
         *
         * @return string|bool
         */
        public static function get_country_by_coordinates( $longitude, $latitude ) {
            $country_code = false;
            if ( self::get_key() ) {
                $url         = self::$mapbox_endpoint  . $longitude . ',' . $latitude . '.json?types=country&access_token=' . self::get_key();
                $data_result = @file_get_contents( $url );
                if ( ! $data_result ) {
                    return false;
                }
                $data = json_decode( $data_result, true );

                if ( isset( $data['features'][0]['properties']['short_code'] ) ) {
                    $country_code = strtoupper( $data['features'][0]['properties']['short_code'] );
                }
            }

            return $country_code;
        }

        public static function static_map( $longitude, $latitude, $zoom = 7, $width = 600, $height = 250, $type = 'streets-v11' ) {
            return 'https://api.mapbox.com/styles/v1/mapbox/'.$type.'/static/'. $longitude.',' . $latitude .','. $zoom .',0,0/'.$width.'x'.$height.'?access_token=' . self::get_key();
        }

        public static function get_zoom( string $code ) {
            /**
            @link https://docs.mapbox.com/api/search/#data-types
            The data types available in the geocoder, listed from the largest to the most granular, are:

            country         - Generally recognized countries or, in some cases like Hong Kong, an area of quasi-national administrative status that has been given a designated country code under ISO 3166-1.
            region          - Top-level sub-national administrative features, such as states in the United States or provinces in Canada or China.
            postcode        - Postal codes used in country-specific national addressing systems.
            district        - Features that are smaller than top-level administrative features but typically larger than cities, in countries that use such an additional layer in postal addressing (for example, prefectures in China).
            place           - Typically these are cities, villages, municipalities, etc. They’re usually features used in postal addressing, and are suitable for display in ambient end-user applications where current-location context is needed (for example, in weather displays).
            locality        - Official sub-city features present in countries where such an additional administrative layer is used in postal addressing, or where such features are commonly referred to in local parlance. Examples include city districts in Brazil and Chile and arrondissements in France.
            neighborhood    - Colloquial sub-city features often referred to in local parlance. Unlike locality features, these typically lack official status and may lack universally agreed-upon boundaries.
            address         - Individual residential or business addresses.
            poi             - Points of interest. These include restaurants, stores, concert venues, parks, museums, etc.
            admin0-admin5   - Used by Location Grid for administrative levels
             */
            switch ( $code ) {
                case 'world':
                case 'continent':
                    $level = 1;
                    break;
                case 'admin0':
                case 'country':
                    $level = 2;
                    break;
                case 'admin1':
                case 'region':
                case 'postcode':
                    $level = 5;
                    break;
                case 'admin2':
                case 'district':
                    $level = 8;
                    break;
                case 'admin3':
                case 'admin4':
                case 'admin5':
                case 'neighborhood':
                    $level = 10;
                    break;
                case 'place':
                case 'poi':
                case 'address':
                case 'lnglat':
                default:
                    $level = 13;
                    break;
            }
            return $level;
        }

        /**
         * Build Components
         */
        public static function location_list_url() {
            global $dt_mapping;
            if ( file_exists( $dt_mapping['path'] . 'location-grid-list-api.php' ) ) {
                return $dt_mapping['path'] . 'location-grid-list-api.php';
            }
            return '';
        }

        public static function load_mapbox_header_scripts() {
            // Mabox Mapping API
            wp_enqueue_script( 'jquery-cookie', 'https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js', [ 'jquery' ], '3.0.0' );
            wp_enqueue_script( 'mapbox-cookie', trailingslashit( get_stylesheet_directory_uri() ) . 'dt-mapping/geocode-api/mapbox-cookie.js', [ 'jquery', 'jquery-cookie' ], '3.0.0' );
            wp_enqueue_script( 'mapbox-gl', self::$mapbox_gl_js, [ 'jquery' ], self::$mapbox_gl_version, false );
            wp_enqueue_style( 'mapbox-gl-css', self::$mapbox_gl_css, [], self::$mapbox_gl_version );
        }

        public static function load_mapbox_search_widget() {
            if ( file_exists( get_template_directory() . '/dt-mapping/geocode-api/mapbox-search-widget.js' ) ) {
                global $post;
                if ( is_single() ) {
                    $post_record = DT_Posts::get_post( get_post_type(), $post->ID );
                } else {
                    $post_record = false;
                }

                if ( ! function_exists( 'dt_get_location_grid_mirror' ) ) {
                    require_once( get_template_directory() . '/dt-mapping/globals.php' );
                }

                wp_enqueue_script( 'mapbox-search-widget', trailingslashit( get_stylesheet_directory_uri() ) . 'dt-mapping/geocode-api/mapbox-search-widget.js', [ 'jquery', 'mapbox-gl' ], filemtime( get_template_directory() . '/dt-mapping/geocode-api/mapbox-search-widget.js' ), true );
                wp_localize_script(
                    "mapbox-search-widget", "dtMapbox", array(
                        'post_type' => get_post_type(),
                        "post_id" => $post->ID ?? 0,
                        "post" => $post_record ?? false,
                        "map_key" => self::get_key(),
                        "mirror_source" => dt_get_location_grid_mirror( true ),
                        "google_map_key" => ( Disciple_Tools_Google_Geocode_API::get_key() ) ? Disciple_Tools_Google_Geocode_API::get_key() : false,
                        "spinner_url" => get_stylesheet_directory_uri() . '/spinner.svg',
                        "theme_uri" => get_stylesheet_directory_uri(),
                        "translations" => array(
                            'add' => __( 'add', 'disciple_tools' ),
                            'use' => __( 'Use', 'disciple_tools' ),
                            'search_location' => __( 'Search Location', 'disciple_tools' ),
                            'delete_location' => __( 'Delete Location', 'disciple_tools' ),
                            'open_mapping' => __( 'Open Mapping', 'disciple_tools' ),
                            'clear' => __( 'Clear', 'disciple_tools' )
                        )
                    )
                );
                add_action( 'wp_head', [ 'DT_Mapbox_API', 'mapbox_search_widget_css' ] );

                // load Google Geocoder if key is present.
                if ( Disciple_Tools_Google_Geocode_API::get_key() ){
                    Disciple_Tools_Google_Geocode_API::load_google_geocoding_scripts();
                }
            }
        }

        public static function load_mapbox_search_widget_users() {
            if ( ! class_exists( 'Disciple_Tools_Users' ) ) {
                return;
            }
            if ( file_exists( get_template_directory() . '/dt-mapping/geocode-api/mapbox-users-search-widget.js' ) ) {

                wp_enqueue_script( 'mapbox-search-widget', trailingslashit( get_stylesheet_directory_uri() ) . 'dt-mapping/geocode-api/mapbox-users-search-widget.js', [ 'jquery', 'mapbox-gl', 'shared-functions' ], filemtime( get_template_directory() . '/dt-mapping/geocode-api/mapbox-users-search-widget.js' ), true );
                wp_localize_script(
                    "mapbox-search-widget", "dtMapbox", array(
                        'post_type' => 'user',
                        "user_id" => get_current_user_id(),
                        "user_location" => Disciple_Tools_Users::get_user_location( get_current_user_id() ),
                        "map_key" => self::get_key(),
                        "google_map_key" => ( Disciple_Tools_Google_Geocode_API::get_key() ) ? Disciple_Tools_Google_Geocode_API::get_key() : false,
                        "spinner_url" => get_stylesheet_directory_uri() . '/spinner.svg',
                        "theme_uri" => get_stylesheet_directory_uri(),
                        "translations" => array(
                            'add' => __( 'add', 'disciple_tools' )
                        )
                    )
                );
                add_action( 'wp_head', [ 'DT_Mapbox_API', 'mapbox_search_widget_css' ] );

                // load Google Geocoder if key is present.
                if ( Disciple_Tools_Google_Geocode_API::get_key() ){
                    Disciple_Tools_Google_Geocode_API::load_google_geocoding_scripts();
                }
            }
        }

        public static function mapbox_search_widget_css() {
            /* Added these few style classes inline vers css file. */
            ?>
            <style>
                /* mapbox autocomplete elements*/
                #mapbox-search {
                    margin:0;
                }
                #mapbox-search-wrapper {
                    margin: 0 0 1rem;
                }
                .mapbox-autocomplete {
                    /*the container must be positioned relative:*/
                    position: relative;
                }
                .mapbox-autocomplete-items {
                    position: absolute;
                    border: 1px solid #e6e6e6;
                    border-bottom: none;
                    border-top: none;
                    z-index: 99;
                    /*position the autocomplete items to be the same width as the container:*/
                    top: 100%;
                    left: 0;
                    right: 0;
                }
                .mapbox-autocomplete-items div {
                    padding: 10px;
                    cursor: pointer;
                    background-color: #fff;
                    border-bottom: 1px solid #e6e6e6;
                }
                .mapbox-autocomplete-items div:hover {
                    /*when hovering an item:*/
                    background-color: #00aeff;
                }
                .mapbox-autocomplete-active {
                    /*when navigating through the items using the arrow keys:*/
                    background-color: #00aeff !important;
                    color: #ffffff;
                }
                #mapbox-spinner-button {
                    border-radius:0;
                    display:none;
                }
                /* end mapbox elements*/
            </style>
            <?php
        }

        public static function load_header() {
            add_action( "enqueue_scripts", [ 'DT_Mapbox_API', 'load_mapbox_header_scripts' ] );
        }

        public static function load_admin_header() {
            add_action( "admin_enqueue_scripts", [ 'DT_Mapbox_API', 'load_mapbox_header_scripts' ] );
        }

        public static function is_active_mapbox_key() : array {
            $key = self::get_key();
            $url = self::$mapbox_endpoint . 'Denver.json?access_token=' . $key;
            $response = wp_remote_get( esc_url_raw( $url ) );
            $data_result = json_decode( wp_remote_retrieve_body( $response ), true );

            if ( isset( $data_result['features'] ) && ! empty( $data_result['features'] ) ) {
                return [
                    'success' => true,
                    'message' => ''
                ];
            } else {
                return [
                    'success' => false,
                    'message' => ( isset( $data_result['message'] ) && ! empty( $data_result['message'] ) ) ? $data_result['message'] : ''
                ];
            }
        }

        /**
         * Administrative Page Metabox
         */
        public static function metabox_for_admin() {
            global $dt_mapping;

            if ( isset( $_POST['mapbox_key'] ) && isset( $_POST['action'] ) && ( isset( $_POST['geocoding_key_nonce'] )
                      && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['geocoding_key_nonce'] ) ), 'geocoding_key' . get_current_user_id() ) ) ) {

                $key = sanitize_text_field( wp_unslash( $_POST['mapbox_key'] ) );
                $action = sanitize_text_field( wp_unslash( $_POST['action'] ) );
                if ( empty( $key ) || $action === 'delete' ) {
                    self::delete_key();
                } else if ( $action === 'add' ) {
                    self::update_key( $key );
                }
            }
            $key = self::get_key();

            $mapbox_key_active_state = self::is_active_mapbox_key();
            if ( $mapbox_key_active_state['success'] ) {
                $status_class = 'connected';
                $message      = 'Successfully connected to selected source.';
            } else {
                $status_class = 'not-connected';
                if ( empty( $key ) ) {
                    $message = 'Please add a Mapbox API Token';
                } else {
                    $message = 'Could not connect to the Mapbox API or could not verify the token';
                    $message .= ! empty( $mapbox_key_active_state['message'] ) ? ' - ' . $mapbox_key_active_state['message'] : '';
                }
            }
            ?>
            <form method="post">
                <table class="widefat striped">
                    <thead>
                    <tr><th>MapBox.com</th></tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?php wp_nonce_field( 'geocoding_key' . get_current_user_id(), 'geocoding_key_nonce' ); ?>
                            Mapbox API Token: <input type="text" class="regular-text" name="mapbox_key" value="<?php echo ( $key ) ? esc_attr( $key ) : ''; ?>" />
                            <?php if ( self::get_key() ) : ?>
                                <button type="submit" name="action" value="delete" class="button">Delete</button>
                            <?php else : ?>
                                <button type="submit" name="action" value="add" class="button">Add</button>
                            <?php endif; ?>
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
                                <h2>MapBox.com Instructions</h2>
                                <ol>
                                    <li>
                                        Go to <a href="https://www.mapbox.com/">MapBox.com</a>.
                                    </li>
                                    <li>
                                        Register for a new account (<a href="https://account.mapbox.com/auth/signup/">MapBox.com</a>)<br>
                                        <em>(email required, no credit card required)</em>
                                    </li>
                                    <li>
                                        Once registered, go to your account home page. (<a href="https://account.mapbox.com/">Account Page</a>)<br>
                                    </li>
                                    <li>
                                        Inside the section labeled "Access Tokens", either create a new token or use the default token provided. Copy this token.
                                    </li>
                                    <li>
                                        Paste the token into the "Mapbox API Token" field in the box above.
                                    </li>
                                </ol>
                            <?php elseif ( self::is_dt() ) :
                                global $wpdb;
                                $records_upgraded = self::are_records_and_users_upgraded_with_mapbox();
                                if ( !$records_upgraded ) : ?>
                                    <p class="not-connected">
                                        <strong>Next:</strong> Please upgrade Users, Contacts and Groups below for the Locations to show up on maps and charts.
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <style>
                #reachable_source.connected{ padding: 0 10px 0 0; color: darkgreen; background-color: transparent}
                .not-connected { padding: 10px; background-color: lightcoral; }
            </style>
            <br>

            <?php
        }

        public static function is_dt(): bool
        {
            $wp_theme = wp_get_theme();

            // child theme check
            if ( get_template_directory() !== get_stylesheet_directory() ) {
                if ( 'disciple-tools-theme' == $wp_theme->get( 'Template' ) ) {
                    return true;
                }
            }

            // main theme check
            $is_theme_dt = class_exists( "Disciple_Tools" );
            if ( $is_theme_dt ) {
                return true;
            }

            return false;
        }

        public static function are_records_and_users_upgraded_with_mapbox(){
            $location_wo_meta = DT_Mapping_Module_Admin::instance()->get_record_count_with_no_location_meta();
            $user_location_wo_meta = DT_Mapping_Module_Admin::instance()->get_user_count_with_no_location_meta();
            if ( !empty( $location_wo_meta ) || !empty( $user_location_wo_meta ) ){
                return false;
            }
            return true;
        }

        public static function parse_raw_result( array $raw_response, $item, $first_result_only = false ) {

            if ( ! isset( $raw_response['features'] ) || empty( $raw_response['features'] ) ) {
                return false;
            }

            $data = [];

            switch ( $item ) {
                case 'features':
                    return $raw_response['features'] ?? false;
                    break;

                /**
                 * Standard elements
                 */
                case 'id':
                    if ( $first_result_only ) {
                        return $raw_response['features'][0]['id'] ?? false;
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $data[] = $feature['id'];
                        }
                        return $data;
                    }
                    break;
                case 'type':
                    if ( $first_result_only ) {
                        return $raw_response['features'][0]['type'] ?? false;
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $data[] = $feature['type'];
                        }
                        return $data;
                    }
                    break;
                case 'place_type':
                    if ( $first_result_only ) {
                        return $raw_response['features'][0]['place_type'][0] ?? false;
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $data[] = $feature['place_type'];
                        }
                        return $data;
                    }
                    break;
                case 'relevance':
                    if ( $first_result_only ) {
                        return $raw_response['features'][0]['relevance'] ?? false;
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $data[] = $feature['relevance'];
                        }
                        return $data;
                    }
                    break;
                case 'properties':
                    if ( $first_result_only ) {
                        return $raw_response['features'][0]['properties'] ?? false;
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $data[] = $feature['properties'];
                        }
                        return $data;
                    }
                    break;
                case 'text':
                    if ( $first_result_only ) {
                        return $raw_response['features'][0]['text'] ?? false;
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $data[] = $feature['text'];
                        }
                        return $data;
                    }
                    break;
                case 'place_name':
                    if ( $first_result_only ) {
                        return $raw_response['features'][0]['place_name'] ?? false;
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $data[] = $feature['place_name'];
                        }
                        return $data;
                    }
                    break;
                case 'center':
                    if ( $first_result_only ) {
                        return $raw_response['features'][0]['center'] ?? false;
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $data[] = $feature['center'];
                        }
                        return $data;
                    }
                    break;
                case 'geometry':
                    if ( $first_result_only ) {
                        return $raw_response['features'][0]['geometry'] ?? false;
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $data[] = $feature['geometry'];
                        }
                        return $data;
                    }
                    break;
                case 'context':
                    if ( $first_result_only ) {
                        return $raw_response['features'][0]['context'] ?? false;
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $data[] = $feature['context'];
                        }
                        return $data;
                    }
                    break;
                case 'attribution':
                    return $raw_response['attribution'] ?? false;
                    break;

                /**
                 * Parsed Elements
                 */
                case 'neighborhood':
                    if ( $first_result_only ) {
                        return $data[] = [ self::context_filter( $raw_response['features'][0]['context'], 'neighborhood' ) ];
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $location = self::context_filter( $feature['context'], 'neighborhood' );
                            if ( ! empty( $location ) ) {
                                $data[$location['id']] = $location;
                            }
                        }
                        sort( $data );
                        return $data;
                    }
                    break;
                case 'postcode':
                    if ( $first_result_only ) {
                        return $data[] = [ self::context_filter( $raw_response['features'][0]['context'], 'postcode' ) ];
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $location = self::context_filter( $feature['context'], 'postcode' );
                            if ( ! empty( $location ) ) {
                                $data[$location['id']] = $location;
                            }
                        }
                        sort( $data );
                        return $data;
                    }
                    break;
                case 'place':
                    if ( $first_result_only ) {
                        return $data[] = [ self::context_filter( $raw_response['features'][0]['context'], 'place' ) ];
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $location = self::context_filter( $feature['context'], 'place' );
                            if ( ! empty( $location ) ) {
                                $data[$location['id']] = $location;
                            }
                        }
                        sort( $data );
                        return $data;
                    }
                    break;
                case 'region':
                    if ( $first_result_only ) {
                        return $data[] = [ self::context_filter( $raw_response['features'][0]['context'], 'region' ) ];
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $location = self::context_filter( $feature['context'], 'region' );
                            if ( ! empty( $location ) ) {
                                $data[$location['id']] = $location;
                            }
                        }
                        sort( $data );
                        return $data;
                    }
                    break;
                case 'country':
                    if ( $first_result_only ) {
                        return $data[] = [ self::context_filter( $raw_response['features'][0]['context'], 'country' ) ];
                    }
                    else {
                        foreach ( $raw_response['features'] as $feature ) {
                            $location = self::context_filter( $feature['context'], 'country' );
                            if ( ! empty( $location ) ) {
                                $data[$location['id']] = $location;
                            }
                        }
                        sort( $data );
                        return $data;
                    }
                    break;
                case 'full_location_name':
                    $country = self::context_filter( $raw_response['features'][0]['context'], 'country' );
                    $state = self::context_filter( $raw_response['features'][0]['context'], 'region' );
                    $city = self::context_filter( $raw_response['features'][0]['context'], 'place' );

                    if ( ! empty( $city['text'] ) ) {
                        $full_location_name = $city['text'] . ', ' . $state['text'] . ', ' . $country['text'];
                    }
                    else if ( ! empty( $state['text'] ) ) {
                        $full_location_name = $state['text'] . ', ' . $country['text'];
                    }
                    else if ( ! empty( $country['text'] ) ) {
                        $full_location_name = $country['text'];
                    }
                    return $full_location_name;

                    break;

                case 'lng':
                case 'longitude': // returns single value
                    return (float) $raw_response['features'][0]['center'][0] ?? false;
                    break;

                case 'lat':
                case 'latitude': // returns single value
                    return (float) $raw_response['features'][0]['center'][1] ?? false;
                    break;

                case 'coordinates':
                case 'lnglat':
                    foreach ( $raw_response['features'] as $feature ) {
                        $location = $feature['center'][1] ?? false;
                        if ( ! empty( $location ) ) {
                            $data[$location['id']] = $location;
                        }
                    }
                    sort( $data );
                    return $data;
                    break;

                case 'full': // useful for running a raw result though the array check at the beginning of the function
                    return $raw_response;
                    break;

                default:
                    return false;
                    break;
            }

        }

        private static function context_filter( $context, $feature ) {
            $data = [];
            foreach ( $context as $item ) {
                $split = explode( '.', $item['id'] );
                $data[$split[0]] = $item;
            }
            return $data[$feature] ?? false;
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

    }
}

/**

The data types available in the geocoder, listed from the largest to the most granular, are:

country         - Generally recognized countries or, in some cases like Hong Kong, an area of quasi-national administrative status that has been given a designated country code under ISO 3166-1.
region          - Top-level sub-national administrative features, such as states in the United States or provinces in Canada or China.
postcode        - Postal codes used in country-specific national addressing systems.
district        - Features that are smaller than top-level administrative features but typically larger than cities, in countries that use such an additional layer in postal addressing (for example, prefectures in China).
place           - Typically these are cities, villages, municipalities, etc. They’re usually features used in postal addressing, and are suitable for display in ambient end-user applications where current-location context is needed (for example, in weather displays).
locality        - Official sub-city features present in countries where such an additional administrative layer is used in postal addressing, or where such features are commonly referred to in local parlance. Examples include city districts in Brazil and Chile and arrondissements in France.
neighborhood    - Colloquial sub-city features often referred to in local parlance. Unlike locality features, these typically lack official status and may lack universally agreed-upon boundaries.
address         - Individual residential or business addresses.
poi             - Points of interest. These include restaurants, stores, concert venues, parks, museums, etc.


*/
/**
EXAMPLE RESPONSE

(
    [type] => FeatureCollection
    [query] => Array
        (
            [0] => highlands
            [1] => ranch
            [2] => co
        )

    [features] => Array
        (
            [0] => Array
                (
                    [id] => address.1434026830431070
                    [type] => Feature
                    [place_type] => Array
                        (
                            [0] => address
                        )

                    [relevance] => 0.99
                    [properties] => Array
                        (
                            [accuracy] => street
                        )

                    [text] => County Line Road
                    [place_name] => County Line Road, Highlands Ranch, Colorado 80124, United States
                    [center] => Array
                        (
                            [0] => -104.8745144
                            [1] => 39.5659061
                        )

                    [geometry] => Array
                        (
                            [type] => Point
                            [coordinates] => Array
                                (
                                    [0] => -104.8745144
                                    [1] => 39.5659061
                                )

                        )

                    [context] => Array
                        (
                            [0] => Array
                                (
                                    [id] => neighborhood.280159
                                    [text] => Castlewood
                                )

                            [1] => Array
                                (
                                    [id] => postcode.9509949786967160
                                    [text] => 80124
                                )

                            [2] => Array
                                (
                                    [id] => place.8851940959283391
                                    [wikidata] =>
                                    [text] => Highlands Ranch
                                )

                            [3] => Array
                                (
                                    [id] => region.10094095868017490
                                    [short_code] => US-CO
                                    [wikidata] => Q1261
                                    [text] => Colorado
                                )

                            [4] => Array
                                (
                                    [id] => country.9053006287256050
                                    [short_code] => us
                                    [wikidata] => Q30
                                    [text] => United States
                                )

                        )

                )

            [1] => Array
                (
                    [id] => address.2460830930065704
                    [type] => Feature
                    [place_type] => Array
                        (
                            [0] => address
                        )

                    [relevance] => 0.99
                    [properties] => Array
                        (
                            [accuracy] => street
                        )

                    [text] => Cottoncreek Dr
                    [place_name] => Cottoncreek Dr, Highlands Ranch, Colorado 80130, United States
                    [center] => Array
                        (
                            [0] => -104.9070019
                            [1] => 39.5334287
                        )

                    [geometry] => Array
                        (
                            [type] => Point
                            [coordinates] => Array
                                (
                                    [0] => -104.9070019
                                    [1] => 39.5334287
                                )

                        )

                    [context] => Array
                        (
                            [0] => Array
                                (
                                    [id] => neighborhood.32817
                                    [text] => Carriage Club
                                )

                            [1] => Array
                                (
                                    [id] => postcode.16510139692750790
                                    [text] => 80130
                                )

                            [2] => Array
                                (
                                    [id] => place.8851940959283391
                                    [wikidata] =>
                                    [text] => Highlands Ranch
                                )

                            [3] => Array
                                (
                                    [id] => region.10094095868017490
                                    [short_code] => US-CO
                                    [wikidata] => Q1261
                                    [text] => Colorado
                                )

                            [4] => Array
                                (
                                    [id] => country.9053006287256050
                                    [short_code] => us
                                    [wikidata] => Q30
                                    [text] => United States
                                )

                        )

                )

            [2] => Array
                (
                    [id] => address.942586569251724
                    [type] => Feature
                    [place_type] => Array
                        (
                            [0] => address
                        )

                    [relevance] => 0.99
                    [properties] => Array
                        (
                            [accuracy] => street
                        )

                    [text] => Colorado Highway 470
                    [place_name] => Colorado Highway 470, Highlands Ranch, Colorado 80129, United States
                    [center] => Array
                        (
                            [0] => -105.0263374
                            [1] => 39.5640733
                        )

                    [geometry] => Array
                        (
                            [type] => Point
                            [coordinates] => Array
                                (
                                    [0] => -105.0263374
                                    [1] => 39.5640733
                                )

                        )

                    [context] => Array
                        (
                            [0] => Array
                                (
                                    [id] => neighborhood.279927
                                    [text] => Wolhurst
                                )

                            [1] => Array
                                (
                                    [id] => postcode.18484341735575200
                                    [text] => 80129
                                )

                            [2] => Array
                                (
                                    [id] => place.8851940959283391
                                    [wikidata] =>
                                    [text] => Highlands Ranch
                                )

                            [3] => Array
                                (
                                    [id] => region.10094095868017490
                                    [short_code] => US-CO
                                    [wikidata] => Q1261
                                    [text] => Colorado
                                )

                            [4] => Array
                                (
                                    [id] => country.9053006287256050
                                    [short_code] => us
                                    [wikidata] => Q30
                                    [text] => United States
                                )

                        )

                )

            [3] => Array
                (
                    [id] => address.93500362632578
                    [type] => Feature
                    [place_type] => Array
                        (
                            [0] => address
                        )

                    [relevance] => 0.5
                    [properties] => Array
                        (
                            [accuracy] => street
                        )

                    [text] => South University Boulevard
                    [place_name] => South University Boulevard, Littleton, Colorado 80210, United States
                    [matching_text] => Colorado Highway 177
                    [matching_place_name] => Colorado Highway 177, Littleton, Colorado 80210, United States
                    [center] => Array
                        (
                            [0] => -104.9553051
                            [1] => 39.6217977
                        )

                    [geometry] => Array
                        (
                            [type] => Point
                            [coordinates] => Array
                                (
                                    [0] => -104.9553051
                                    [1] => 39.6217977
                                )

                        )

                    [context] => Array
                        (
                            [0] => Array
                                (
                                    [id] => postcode.93500362632578
                                    [text] => 80210
                                )

                            [1] => Array
                                (
                                    [id] => place.18080090047604780
                                    [wikidata] => Q953583
                                    [text] => Littleton
                                )

                            [2] => Array
                                (
                                    [id] => region.10094095868017490
                                    [short_code] => US-CO
                                    [wikidata] => Q1261
                                    [text] => Colorado
                                )

                            [3] => Array
                                (
                                    [id] => country.9053006287256050
                                    [short_code] => us
                                    [wikidata] => Q30
                                    [text] => United States
                                )

                        )

                )

            [4] => Array
                (
                    [id] => address.5465220035127262
                    [type] => Feature
                    [place_type] => Array
                        (
                            [0] => address
                        )

                    [relevance] => 0.5
                    [properties] => Array
                        (
                            [accuracy] => street
                        )

                    [text] => C-470
                    [place_name] => C-470, Littleton, Colorado 80128, United States
                    [matching_text] => Colorado Highway 470
                    [matching_place_name] => Colorado Highway 470, Littleton, Colorado 80128, United States
                    [center] => Array
                        (
                            [0] => -105.0412336
                            [1] => 39.5664011
                        )

                    [geometry] => Array
                        (
                            [type] => Point
                            [coordinates] => Array
                                (
                                    [0] => -105.0412336
                                    [1] => 39.5664011
                                )

                        )

                    [context] => Array
                        (
                            [0] => Array
                                (
                                    [id] => neighborhood.279927
                                    [text] => Wolhurst
                                )

                            [1] => Array
                                (
                                    [id] => postcode.5460871545452160
                                    [text] => 80128
                                )

                            [2] => Array
                                (
                                    [id] => place.18080090047604780
                                    [wikidata] => Q953583
                                    [text] => Littleton
                                )

                            [3] => Array
                                (
                                    [id] => region.10094095868017490
                                    [short_code] => US-CO
                                    [wikidata] => Q1261
                                    [text] => Colorado
                                )

                            [4] => Array
                                (
                                    [id] => country.9053006287256050
                                    [short_code] => us
                                    [wikidata] => Q30
                                    [text] => United States
                                )

                        )

                )

        )

    [attribution] => NOTICE: © 2019 Mapbox and its suppliers. All rights reserved. Use of this data is subject to the Mapbox Terms of Service (https://www.mapbox.com/about/maps/). This response and the information it contains may not be retained. POI(s) provided by Foursquare.
)

*/
