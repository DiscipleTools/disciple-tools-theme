<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'DT_Mapbox_API' ) ) {
    class DT_Mapbox_API {
        /**
         * Returns country_code from longitude and latitude
         *
         * @param $longitude
         * @param $latitude
         *
         * @return string|bool
         */
        public static function mapbox_get_country_by_coordinates( $longitude, $latitude ) {
            $country_code = false;
            if ( get_option( 'dt_mapbox_api_key' ) ) {
                $url         = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' . $longitude . ',' . $latitude . '.json?types=country&access_token=' . get_option( 'dt_mapbox_api_key' );
                $data_result = @file_get_contents( $url );
                if ( ! $data_result ) {
                    return false;
                }
                $data = json_decode( $data_result, true );

                if ( isset( $data[ 'features' ][ 0 ][ 'properties' ][ 'short_code' ] ) ) {
                    $country_code = strtoupper( $data[ 'features' ][ 0 ][ 'properties' ][ 'short_code' ] );
                }
            }

            return $country_code;
        }

        public static function mapbox_forward_lookup( $address, $country_code = null ) {
            $address = str_replace( ';', ' ', $address );
            $address = utf8_uri_encode( $address );

            if ( $country_code ) {
                $url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' . $address . '.json?types=address&access_token=' . get_option( 'dt_mapbox_api_key' );
            } else {
                $url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' . $address . '.json?country=' . $country_code . '&types=address&access_token=' . get_option( 'dt_mapbox_api_key' );
            }

            $data_result = @file_get_contents( $url );
            if ( ! $data_result ) {
                return false;
            }
            $data = json_decode( $data_result, true );

            return $data;
        }

        public static function mapbox_reverse_lookup( $longitude, $latitude ) {
            $url         = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' . $longitude . ',' . $latitude . '.json?access_token=' . get_option( 'dt_mapbox_api_key' );
            $data_result = @file_get_contents( $url );
            if ( ! $data_result ) {
                return false;
            }
            $data = json_decode( $data_result, true );

            return $data;
        }

        public static function box_geocoding_source() {

            if ( isset( $_POST['mapbox_key'] )
                 && ( isset( $_POST['geocoding_key_nonce'] )
                      && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['geocoding_key_nonce'] ) ), 'geocoding_key' . get_current_user_id() ) ) ) {

                $key = sanitize_text_field( wp_unslash( $_POST['mapbox_key'] ) );
                if ( empty( $key ) ) {
                    delete_option( 'dt_mapbox_api_key' );
                } else {
                    update_option( 'dt_mapbox_api_key', $key, true );
                }
            }
            $key = get_option( 'dt_mapbox_api_key' );
            $hidden_key = '**************' . substr( $key, -5, 5 );

            set_error_handler( [ "DT_Mapbox_API", "warning_handler" ], E_WARNING );
            $list = file_get_contents( 'https://api.mapbox.com/geocoding/v5/mapbox.places/Denver.json?access_token=' . $key );
            restore_error_handler();

            if ( $list ) {
                $status_class = 'connected';
                $message = 'Successfully connected to selected source.';
            } else {
                $status_class = 'not-connected';
                $message = 'API NOT AVAILABLE';
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
                            Mapbox API Token: <input type="text" class="regular-text" name="mapbox_key" value="<?php echo ( $key ) ? esc_attr( $hidden_key ) : ''; ?>" /> <button type="submit" class="button">Update</button>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p id="reachable_source" class="<?php echo esc_attr( $status_class ) ?>">
                                <?php echo esc_html( $message ); ?>
                            </p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <br>

            <?php if ( empty( get_option( 'dt_mapbox_api_key' ) ) ) : ?>
                <table class="widefat striped">
                    <thead>
                    <tr><th>MapBox.com Instructions</th></tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
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
                        </td>
                    </tr>
                    </tbody>
                </table>
                <br>
            <?php endif; ?>

            <?php if ( ! empty( get_option( 'dt_mapbox_api_key' ) ) ) : ?>
                <table class="widefat striped">
                    <thead>
                    <tr><th>Geocoding Test</th></tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td>
                            <!-- Geocoder Input Section -->
                            <?php // @codingStandardsIgnoreStart ?>
                            <script src='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.0/mapbox-gl-geocoder.min.js'></script>
                            <link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.4.0/mapbox-gl-geocoder.css' type='text/css' />
                            <?php // @codingStandardsIgnoreEnd ?>
                            <style>
                                .mapboxgl-ctrl-geocoder {
                                    min-width:100%;
                                }
                                #geocoder {
                                    padding-bottom: 10px;
                                }
                                #map {
                                    width:66%;
                                    height:400px;
                                    float:left;
                                }
                                #list {
                                    width:33%;
                                    float:right;
                                }
                                #selected_values {
                                    width:66%;
                                    float:left;
                                }
                                .result_box {
                                    padding: 15px 10px;
                                    border: 1px solid lightgray;
                                    margin: 5px 0 0;
                                    font-weight: bold;
                                }
                                .add-column {
                                    width:10px;
                                }
                            </style>

                            <!-- Widget -->
                            <div id='geocoder' class='geocoder'></div>
                            <div>
                                <div id='map'></div>
                                <div id="list"></div>
                            </div>
                            <div id="selected_values"></div>

                            <!-- Mapbox script -->
                            <script>
                                mapboxgl.accessToken = '<?php echo esc_html( get_option( 'dt_mapbox_api_key' ) ) ?>';
                                var map = new mapboxgl.Map({
                                    container: 'map',
                                    style: 'mapbox://styles/mapbox/streets-v11',
                                    center: [-20, 30],
                                    zoom: 1
                                });

                                map.addControl(new mapboxgl.NavigationControl());

                                var geocoder = new MapboxGeocoder({
                                    accessToken: mapboxgl.accessToken,
                                    types: 'country region district postcode locality neighborhood address place', //'country region district postcode locality neighborhood address place',
                                    marker: {color: 'orange'},
                                    mapboxgl: mapboxgl
                                });

                                document.getElementById('geocoder').appendChild(geocoder.onAdd(map));

                                // After Search Result
                                geocoder.on('result', function(e) { // respond to search
                                    geocoder._removeMarker()
                                    console.log(e)
                                })


                                map.on('click', function (e) {
                                    console.log(e)

                                    let lng = e.lngLat.lng
                                    let lat = e.lngLat.lat
                                    window.active_lnglat = [lng,lat]

                                    // add marker
                                    if ( window.active_marker ) {
                                        window.active_marker.remove()
                                    }
                                    window.active_marker = new mapboxgl.Marker()
                                        .setLngLat(e.lngLat )
                                        .addTo(map);
                                    console.log(active_marker)

                                    // add polygon
                                    jQuery.get('<?php echo esc_url( trailingslashit( get_template_directory_uri() ) ) . 'dt-mapping/' ?>location-grid-list-api.php',
                                        {
                                            type: 'possible_matches',
                                            longitude: lng,
                                            latitude:  lat,
                                            nonce: '<?php echo esc_html( wp_create_nonce( 'location_grid' ) ) ?>'
                                        }, null, 'json' ).done(function(data) {

                                        console.log(data)
                                        if ( data !== undefined ) {
                                            print_click_results( data )
                                        }

                                    })
                                });


                                // User Personal Geocode Control
                                let userGeocode = new mapboxgl.GeolocateControl({
                                    positionOptions: {
                                        enableHighAccuracy: true
                                    },
                                    marker: {
                                        color: 'orange'
                                    },
                                    trackUserLocation: false
                                })
                                map.addControl(userGeocode);
                                userGeocode.on('geolocate', function(e) { // respond to search
                                    console.log(e)
                                    let lat = e.coords.latitude
                                    let lng = e.coords.longitude
                                    window.active_lnglat = [lng,lat]

                                    // add polygon
                                    jQuery.get('<?php echo esc_url( trailingslashit( get_template_directory_uri() ) ) . 'dt-mapping/' ?>location-grid-list-api.php',
                                        {
                                            type: 'possible_matches',
                                            longitude: lng,
                                            latitude:  lat,
                                            nonce: '<?php echo esc_html( wp_create_nonce( 'location_grid' ) ) ?>'
                                        }, null, 'json' ).done(function(data) {
                                        console.log(data)

                                        if ( data !== undefined ) {

                                            print_click_results(data)
                                        }
                                    })
                                })

                                jQuery(document).ready(function() {
                                    jQuery('input.mapboxgl-ctrl-geocoder--input').attr("placeholder", "Enter Country")
                                })


                                function print_click_results( data ) {
                                    if ( data !== undefined ) {

                                        // print click results
                                        window.MBresponse = data

                                        let print = jQuery('#list')
                                        print.empty();
                                        print.append('<strong>Click Results</strong><br><hr>')
                                        let table_body = ''
                                        jQuery.each( data, function(i,v) {
                                            let string = '<tr><td class="add-column">'
                                            string += '<button onclick="add_selection(' + v.grid_id +')">Add</button></td> '
                                            string += '<td><strong style="font-size:1.2em;">'+v.name+'</strong> <br>'
                                            if ( v.admin0_name !== v.name ) {
                                                string += v.admin0_name
                                            }
                                            if ( v.admin1_name !== null ) {
                                                string += ' > ' + v.admin1_name
                                            }
                                            if ( v.admin2_name !== null ) {
                                                string += ' > ' + v.admin2_name
                                            }
                                            if ( v.admin3_name !== null ) {
                                                string += ' > ' + v.admin3_name
                                            }
                                            if ( v.admin4_name !== null ) {
                                                string += ' > ' + v.admin4_name
                                            }
                                            if ( v.admin5_name !== null ) {
                                                string += ' > ' + v.admin5_name
                                            }
                                            string += '</td></tr>'
                                            table_body += string
                                        })
                                        print.append('<table>' + table_body + '</table>')
                                    }
                                }

                                function add_selection( grid_id ) {
                                    console.log(window.MBresponse[grid_id])

                                    let div = jQuery('#selected_values')
                                    let response = window.MBresponse[grid_id]

                                    if ( window.selected_locations === undefined ) {
                                        window.selected_locations = []
                                    }
                                    window.selected_locations[grid_id] = new mapboxgl.Marker()
                                        .setLngLat( [ window.active_lnglat[0], window.active_lnglat[1] ] )
                                        .addTo(map);

                                    let name = ''
                                    name += response.name
                                    if ( response.admin1_name !== undefined && response.level > '1' ) {
                                        name += ', ' + response.admin1_name
                                    }
                                    if ( response.admin0_name && response.level > '0' ) {
                                        name += ', ' + response.admin0_name
                                    }

                                    div.append('<div class="result_box" id="'+grid_id+'">' +
                                        '<span>'+name+'</span>' +
                                        '<span style="float:right;cursor:pointer;" onclick="remove_selection(\''+grid_id+'\')">X</span>' +
                                        '<input type="hidden" name="selected_grid_id['+grid_id+']" value="' + grid_id + '" />' +
                                        '<input type="hidden" name="selected_lnglat['+grid_id+']" value="' + window.active_lnglat[0] + ',' + window.active_lnglat[1] + '" />' +
                                        '</div>')

                                }

                                function remove_selection( grid_id ) {
                                    window.selected_locations[grid_id].remove()
                                    jQuery('#' + grid_id ).remove()
                                }


                            </script>
                        </td>
                    </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php
        }

        public function warning_handler( $errno, $errstr ) {
            ?>
            <div class="notice notice-error notice-dt-mapping-source" data-notice="dt-demo">
                <p><?php echo "MIRROR SOURCE NOT AVAILABLE" ?></p>
                <p><?php echo "Error Message: " . esc_attr( $errstr ) ?></p>
            </div>
            <?php
        }
    }
}

