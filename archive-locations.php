<?php declare(strict_types=1); ?>

<?php get_header(); ?>

<?php dt_print_breadcrumbs( null, __( "Locations" ) ); ?>

<?php
(function() {
//    global $post;

//    $locations = Disciple_Tools_Locations::get_locations();
//    if ( is_wp_error( $locations ) ) {
//        wp_die('You do not have permission to see locations.' );
//    }

//    $marker = Disciple_Tools_Locations::geocode_address('Grand Casablanca, Morocco', 'all_points');
//    if ( ! $marker ) {
//        wp_die('no location' );
//    }
//    dt_write_log($marker);

//    dt_write_log(Disciple_Tools_Locations::get_4k_location_count( 'all' ));

    // get how many countries


    // get how many levels per country

    ?>

<div id="content">

    <div id="inner-content" class="grid-x grid-margin-x">

        <div class="large-3 small-12 cell">

            <section class="bordered-box">

                <h3>Locations</h3>

                <ul id="metrics-sidemenu" class="vertical menu accordion-menu" data-accordion-menu>

                <?php
                $args = array(
                    'post_type' => 'locations',
                    'meta_key' => 'WorldID',
                    'nopaging' => true,
                    'meta_query' => [
                        [
                            'key' => 'WorldID',
                            'value' => '^...$',
                            'compare' => 'REGEXP',
                        ]
                    ]

                );
                $query = new WP_Query( $args );

                if ( $query->have_posts() ) {
                    foreach ( $query->posts as $admin0 ) {
                        echo '<li class="top-border"><a href="#">' . esc_attr( $admin0->post_title ); // level 0 list

                            $world_id_0 = get_post_meta( $admin0->ID, 'WorldID', true );

                            $args1 = array(
                                'post_type' => 'locations',
                                'nopaging' => true,
                                'meta_query' => [
                                    [
                                        'key' => 'WorldID',
                                        'value' => '^'.$world_id_0.'....$',
                                        'compare' => 'REGEXP',
                                    ]
                                ]

                            );
                            $query1 = new WP_Query( $args1 );

                        if ( $query1->post_count > 0 ) {
                            echo ' (' . esc_attr( $query1->post_count ) . ' )';
                        }

                            echo '</a>';

                            echo '<ul class="menu vertical nested">'; // level 1 ul

                        foreach ( $query1->posts as $admin1 ) {

                            echo '<li class="top-border"><a href="#">' . esc_attr( $admin1->post_title ); // level 1 li

                                $world_id_1 = get_post_meta( $admin1->ID, 'WorldID', true );

                                $args2 = array(
                                    'post_type' => 'locations',
                                    'nopaging' => true,
                                    'meta_query' => [
                                        [
                                            'key' => 'WorldID',
                                            'value' => '^'.$world_id_1.'....$',
                                            'compare' => 'REGEXP',
                                        ]
                                    ]

                                );
                                $query2 = new WP_Query( $args2 );

                            if ( $query2->post_count > 0 ) {
                                echo ' ( '. esc_attr( $query2->post_count ) . ' )';
                            }

                                echo '</a>';

                                echo '<ul class="menu vertical nested">'; // level 2 ul

                            foreach ( $query2->posts as $admin2 ) {
                                echo '<li class="bottom-border"><a href="#">' . esc_attr( $admin2->post_title ) . '</a>';
                            }

                                echo '</ul>'; // end level 2 ul

                                    echo '</li>'; // end level 1 li

                        }
                            echo '</ul>'; // end level 1 list

                        echo '</li>'; // end level 0 list

                    }
                }
                ?>

                </ul> <!-- End list-->

            </section>

        </div>

        <div id="main" class="large-9 small-12 cell" role="main">

            <section class="bordered-box">

                <div id="map" style="width:100%;"></div>

                <script type="text/javascript">

                    jQuery(document).ready(function () {

                        jQuery('#map').height( jQuery(window).height() - jQuery('header').height() - 150 ) // set height for map

                        let zoom = 10
                        let centerLat = 32.5997754
                        let centerLng = -8.6600586
                        let center = new google.maps.LatLng(centerLat, centerLng);
                        let map = new google.maps.Map(document.getElementById('map'), {
                            zoom: zoom,
                            center: center,
                            mapTypeId: 'terrain'
                        })
                        var contentString = '<div id="content">'+
                            '<div id="siteNotice">'+
                            '</div>'+
                            '<h1 id="firstHeading" class="firstHeading">Uluru</h1>'+
                            '<div id="bodyContent">'+
                            '<p><b>Uluru</b>, also referred to as <b>Ayers Rock</b>, is a large ' +
                            'sandstone rock formation in the southern part of the '+
                            'Northern Territory, central Australia. It lies 335&#160;km (208&#160;mi) '+
                            'south west of the nearest large town, Alice Springs; 450&#160;km '+
                            '(280&#160;mi) by road. Kata Tjuta and Uluru are the two major '+
                            'features of the Uluru - Kata Tjuta National Park. Uluru is '+
                            'sacred to the Pitjantjatjara and Yankunytjatjara, the '+
                            'Aboriginal people of the area. It has many springs, waterholes, '+
                            'rock caves and ancient paintings. Uluru is listed as a World '+
                            'Heritage Site.</p>'+
                            '<p>Attribution: Uluru, <a href="https://en.wikipedia.org/w/index.php?title=Uluru&oldid=297882194">'+
                            'https://en.wikipedia.org/w/index.php?title=Uluru</a> '+
                            '(last visited June 22, 2009).</p>'+
                            '</div>'+
                            '</div>';

                        var infowindow = new google.maps.InfoWindow({
                            content: contentString
                        });

                        var marker = new google.maps.Marker({
                            position: center,
                            map: map,
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: 50,
                                strokeColor: 'red',
                                strokeWeight: 5,
                                fillColor: 'red',
                                fillOpacity: .8
                            },
                            label: 'Marker',


                        });
                        marker.addListener('click', function() {
                            infowindow.open(map, marker);
                        });

//                        var infowindow2 = new google.maps.InfoWindow({
//                            content: contentString
//                        });
//                        var marker2 = new google.maps.Marker({
//                            position: {lat: centerLat + 1, lng: centerLng },
//                            map: map,
//                            icon: {
//                                path: google.maps.SymbolPath.CIRCLE,
//                                scale: 50,
//                                strokeColor: 'black',
//                                strokeWeight: 5,
//                                fillColor: 'black',
//                                fillOpacity: .8,
//                            },
//                            label: {text: 'Tunis Higher', color: "white"},
//
//                        });
//                        marker2.addListener('click', function() {
//                            infowindow2.open(map, marker2);
//                        });



//                        var locations = [
//                            {lat: -31.563910, lng: 147.154312},
//                            {lat: -33.718234, lng: 150.363181},
//                            {lat: -33.727111, lng: 150.371124},
//                            {lat: -33.848588, lng: 151.209834},
//                            {lat: -33.851702, lng: 151.216968},
//                            {lat: -34.671264, lng: 150.863657},
//                            {lat: -35.304724, lng: 148.662905},
//                            {lat: -36.817685, lng: 175.699196},
//                            {lat: -36.828611, lng: 175.790222},
//                            {lat: -37.750000, lng: 145.116667},
//                            {lat: -37.759859, lng: 145.128708},
//                            {lat: -37.765015, lng: 145.133858},
//                            {lat: -37.770104, lng: 145.143299},
//                            {lat: -37.773700, lng: 145.145187},
//                            {lat: -37.774785, lng: 145.137978},
//                            {lat: -37.819616, lng: 144.968119},
//                            {lat: -38.330766, lng: 144.695692},
//                            {lat: -39.927193, lng: 175.053218},
//                            {lat: -41.330162, lng: 174.865694},
//                            {lat: -42.734358, lng: 147.439506},
//                            {lat: -42.734358, lng: 147.501315},
//                            {lat: -42.735258, lng: 147.438000},
//                            {lat: -43.999792, lng: 170.463352}
//                        ]
//
//                        var map = new google.maps.Map(document.getElementById('map'), {
//                            zoom: 3,
//                            center: {lat: -28.024, lng: 140.887}
//                        });
//
//                        // Create an array of alphabetical characters used to label the markers.
//                        var labels = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
//
//                        // Add some markers to the map.
//                        // Note: The code uses the JavaScript Array.prototype.map() method to
//                        // create an array of markers based on a given "locations" array.
//                        // The map() method here has nothing to do with the Google Maps API.
//                        var markers = locations.map(function(location, i) {
//                            return new google.maps.Marker({
//                                position: location,
//                                label: labels[i % labels.length]
//                            });
//                        });
//
//                        // Add a marker clusterer to manage the markers.
//                        var markerCluster = new MarkerClusterer(map, markers,
//                            {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});



                    });
                </script>



            </section>

        </div> <!-- end #main -->

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php

})();

get_footer();

