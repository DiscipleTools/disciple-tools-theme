<?php
/**
 * Archive Locations
 */

/**
 * Process $_POST content
 */
if ( ( isset( $_POST['location_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['location_nonce'] ) ), 'location_default_nonce' ) ) && isset( $_POST['set_locations_default'] ) ) {
    update_user_meta( get_current_user_id(), 'dt_location_default', sanitize_text_field( wp_unslash( $_POST['set_locations_default'] ) ) );
}

/**
 * Build Queries
 */
if ( ! class_exists( 'Disciple_Tools_Locations' ) ) {
    wp_die( 'The Disciple Tools Plugin is require. Please install this.' );
}
$dt_level_0_count = Disciple_Tools_Locations::get_standard_locations_count( 0 );

$dt_country_default_id = get_user_meta( get_current_user_id(), 'dt_location_default', true );
$dt_country_default = get_post( $dt_country_default_id );
$dt_country_default_meta = get_post_meta( $dt_country_default_id );

$dt_admin_0_results = Disciple_Tools_Locations::get_standard_admin0();
if ( is_wp_error( $dt_admin_0_results ) ) {
    wp_die( 'Query error getting standard locations: Admin 0' );
}

?>

<?php get_header(); ?>

<?php dt_print_breadcrumbs( null, __( "Locations" ) ); ?>

<div id="content">

    <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

        <div class="large-3 small-12 cell">

            <section class="bordered-box">

                <span class="section-header">Locations</span>
                <hr>


                <h5>Browse Locations</h5>

                <ul id="browse-sidemenu" class="vertical menu accordion-menu" style="display:none;" data-accordion-menu>

                    <?php
                    /************************
                     * Admin Level 0
                     */
                    if ( $dt_admin_0_results->have_posts() ) {

                        foreach ( $dt_admin_0_results->posts as $dt_admin_0_record ) {

                            echo '<li class="top-border"><a href="#">' . esc_attr( $dt_admin_0_record->post_title ); // level 0 list

                            /************************
                             * Admin Level 1
                             */
                            $dt_world_id_0 = get_post_meta( $dt_admin_0_record->ID, 'WorldID', true );
                            $dt_admin_1_results = Disciple_Tools_Locations::get_standard_admin1( $dt_world_id_0 );
                            if ( is_wp_error( $dt_admin_1_results ) ) {
                                wp_die( 'Query error getting standard locations: Admin 1' );
                            }

                            if ( $dt_admin_1_results->post_count > 0 ) {

                                echo ' (' . esc_attr( $dt_admin_1_results->post_count ) . ' )</a>';
                                echo '<ul class="menu vertical nested">'; // level 1 ul

                                foreach ( $dt_admin_1_results->posts as $dt_admin_1_record ) {

                                    echo '<li class="top-border"><a href="#">' . esc_attr( $dt_admin_1_record->post_title ); // level 1 li

                                    /***************************
                                     * Admin Level 2
                                     */
                                    $dt_world_id_1 = get_post_meta( $dt_admin_1_record->ID, 'WorldID', true );
                                    $dt_admin_2_results = Disciple_Tools_Locations::get_standard_admin2( $dt_world_id_1 );
                                    if ( is_wp_error( $dt_admin_2_results ) ) {
                                        wp_die( 'Query error getting standard locations: Admin 2' );
                                    }

                                    if ( $dt_admin_2_results->post_count > 0 ) {

                                        echo ' ( ' . esc_attr( $dt_admin_2_results->post_count ) . ' )</a>';
                                        echo '<ul class="menu vertical nested">'; // level 2 ul

                                        foreach ( $dt_admin_2_results->posts as $dt_admin_2_record ) {
                                            echo '<li class="bottom-border"><a href="#">' . esc_attr( $dt_admin_2_record->post_title ) . '</a>';

                                            /****************************
                                             * Admin Level 3
                                             */
                                            $dt_world_id_2 = get_post_meta( $dt_admin_2_record->ID, 'WorldID', true );
                                            $dt_admin_3_results = Disciple_Tools_Locations::get_standard_admin3( $dt_world_id_2 );
                                            if ( is_wp_error( $dt_admin_3_results ) ) {
                                                wp_die( 'Query error getting standard locations: Admin 3' );
                                            }

                                            if ( $dt_admin_3_results->post_count > 0 ) {

                                                echo ' ( ' . esc_attr( $dt_admin_3_results->post_count ) . ' )</a>';
                                                echo '<ul class="menu vertical nested">'; // level 3 ul

                                                foreach ( $dt_admin_3_results->posts as $dt_admin_3_record ) {
                                                    echo '<li class="bottom-border"><a href="#">' . esc_attr( $dt_admin_3_record->post_title ) . '</a>';

                                                    /****************************
                                                     * Admin Level 4
                                                     */
                                                    $dt_world_id_3 = get_post_meta( $dt_admin_3_record->ID, 'WorldID', true );
                                                    $dt_admin_4_results = Disciple_Tools_Locations::get_standard_admin4( $dt_world_id_3 );
                                                    if ( is_wp_error( $dt_admin_3_results ) ) {
                                                        wp_die( 'Query error getting standard locations: Admin 3' );
                                                    }

                                                    if ( $dt_admin_4_results->post_count > 0 ) {

                                                        echo ' ( ' . esc_attr( $dt_admin_4_results->post_count ) . ' )</a>';
                                                        echo '<ul class="menu vertical nested">'; // level 3 ul

                                                        foreach ( $dt_admin_4_results->posts as $dt_admin_4_record ) {
                                                            echo '<li class="bottom-border"><a href="#">' . esc_attr( $dt_admin_4_record->post_title ) . '</a>';
                                                            // All countries targeted for use do not have useful admin divisions below 4. This is the extent of the nesting supported.

                                                        }

                                                        echo '</ul>'; // end level 3 ul

                                                    } else {
                                                        echo '</a>';
                                                    }
                                                }

                                                echo '</ul>'; // end level 3 ul

                                            } else {
                                                echo '</a>';
                                            }
                                        }

                                        echo '</ul>'; // end level 2 ul

                                    } else {
                                        echo '</a>';
                                    }

                                    echo '</li>'; // end level 1 li

                                }
                                echo '</ul>'; // end level 1 list

                            } else {
                                echo '</a>';
                            }
                            echo '</li>'; // end level 0 list
                        }
                    }
                    ?>

                </ul> <!-- End list-->

                <script>
                    jQuery(document).ready(function() {
                        jQuery('#browse-sidemenu').show(); // Delayed display. Allows the menu to load before displaying, if there are a number of locations.
                    });
                </script>

                <hr>

                <?php
                if ( $dt_level_0_count > 1 ) : // Discover if there are more than on countries installed. ?>

                    <h5>Default View:</h5>

                    <form method="post" id="dt_locations_default_form">
                        <?php wp_nonce_field( 'location_default_nonce', 'location_nonce', false ); ?>
                        <select name="set_locations_default">
                            <?php
                            foreach ( $dt_admin_0_results->posts as $post ) {
                                echo '<option value="' . esc_attr( $post->ID ) . '"';
                                if ( $dt_country_default_id == $post->ID ) {
                                    echo 'selected';
                                }
                                echo '>' . esc_html( $post->post_title ) . '</option>';
                            }
                            ?>
                        </select>
                        <button type="submit" class="button small">Change</button>
                    </form>

                <?php endif; // if more than one country installed. ?>


            </section>

        </div>

        <div id="main" class="large-9 small-12 cell" role="main">

            <section class="bordered-box">

                <div id="map" style="width:100%;"></div>


                <script type="text/javascript">

                    jQuery(document).ready(function () {
                        /**
                         * Set boundary of country map
                         */
                        let $mapDiv = jQuery('#map');
                        $mapDiv.height(jQuery(window).height() - jQuery('header').height() - 150) // set height for map according to screen

                        let centerLat = <?php echo (float) esc_attr( $dt_country_default_meta['lat'][0] ); ?>;
                        let centerLng = <?php echo (float) esc_attr( $dt_country_default_meta['lng'][0] ); ?>;
                        let center = new google.maps.LatLng(centerLat, centerLng);

                        let sw = new google.maps.LatLng(<?php echo (float) esc_attr( $dt_country_default_meta['southwest_lat'][0] ); ?>, <?php echo (float) esc_attr( $dt_country_default_meta['southwest_lng'][0] ); ?>);
                        let ne = new google.maps.LatLng(<?php echo (float) esc_attr( $dt_country_default_meta['northeast_lat'][0] ); ?>, <?php echo (float) esc_attr( $dt_country_default_meta['northeast_lng'][0] ); ?>);
                        let bounds = new google.maps.LatLngBounds(sw, ne);

                        let mapDim = { height: $mapDiv.height(), width: $mapDiv.width() };

                        let zoom = getBoundsZoomLevel( bounds, mapDim );

                        let map = new google.maps.Map(document.getElementById('map'), {
                            zoom: zoom,
                            center: center,
                            mapTypeId: 'terrain'
                        });


                        // first
                        let marker = new google.maps.Marker({
                            position: center,
                            map: map,
                        });
                        let infowindow = new google.maps.InfoWindow({
                            content: '<div id="content">' +
                            '<div id="siteNotice">' +
                            '</div>' +
                            '<h1 id="firstHeading" class="firstHeading">Uluru</h1>' +
                            '<div id="bodyContent">' +
                            '<p><b>Uluru</b>, also referred to as <b>Ayers Rock</b>, is a large ' +
                            'sandstone rock formation in the southern part of the ' +
                            'Northern Territory, central Australia. It lies 335&#160;km (208&#160;mi) ' +
                            'south west of the nearest large town, Alice Springs; 450&#160;km ' +
                            '(280&#160;mi) by road. Kata Tjuta and Uluru are the two major ' +
                            'features of the Uluru - Kata Tjuta National Park. Uluru is ' +
                            'sacred to the Pitjantjatjara and Yankunytjatjara, the ' +
                            'Aboriginal people of the area. It has many springs, waterholes, ' +
                            'rock caves and ancient paintings. Uluru is listed as a World ' +
                            'Heritage Site.</p>' +
                            '<p>Attribution: Uluru, <a href="https://en.wikipedia.org/w/index.php?title=Uluru&oldid=297882194">' +
                            'https://en.wikipedia.org/w/index.php?title=Uluru</a> ' +
                            '(last visited June 22, 2009).</p>' +
                            '</div>' +
                            '</div>'
                        });
                        marker.addListener('click', function () {
                            infowindow.open(map, marker);
                        });




                    });
                </script>


            </section>

        </div> <!-- end #main -->

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
