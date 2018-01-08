<?php

/**
 * KML File Update Class
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Locations_Import
 */
class Disciple_Tools_Locations_Import
{

    /**
     * Uploads US Census Tract KML file to Locations Post Type
     *
     * @param $state
     *
     * @return string
     */
    public static function upload_census_tract_kml_to_post_type( $state )
    {
        global $wpdb;

        // test if locations post type exists
        if ( !post_type_exists( 'locations' ) ) {
            return 'Fail: You need the locations post type installed through Disciple Tools.';
        }

        if ( !get_option( '_installed_us_county_' . $state ) ) { // check if counties are installed for the state

            $counties = dt_get_usa_meta();

            foreach ( $counties->USA_counties as $county ) {
                if ( $county->STATE == $state ) {
                    $post = [
                        "post_title"            => $county->COUNTY_NAME . ', ' . $county->STUSAB,
                        'post_type'             => 'locations',
                        "post_content"          => '',
                        "post_excerpt"          => '',
                        "post_name"             => $county->STATE . $county->COUNTY,
                        "post_content_filtered" => $county->STATE . $county->COUNTY,
                        "post_status"           => "publish",
                        "post_author"           => get_current_user_id(),
                    ];

                    $new_post_id = wp_insert_post( $post );

                    /* Metadata inserted separately to avoid activity hooks on metadata inserts. These were causing memory problems on large inserts. */
                    // state meta
                    $wpdb->insert(
                        $wpdb->postmeta,
                        [
                            'post_id'    => $new_post_id,
                            'meta_key'   => 'STATE',
                            'meta_value' => $county->STATE,
                        ]
                    );
                    // state meta
                    $wpdb->insert(
                        $wpdb->postmeta,
                        [
                            'post_id'    => $new_post_id,
                            'meta_key'   => 'COUNTY',
                            'meta_value' => $county->COUNTY,
                        ]
                    );
                    // state meta
                    $wpdb->insert(
                        $wpdb->postmeta,
                        [
                            'post_id'    => $new_post_id,
                            'meta_key'   => 'STUSAB',
                            'meta_value' => $county->STUSAB,
                        ]
                    );
                    // state meta
                    $wpdb->insert(
                        $wpdb->postmeta,
                        [
                            'post_id'    => $new_post_id,
                            'meta_key'   => 'COUNTY_NAME',
                            'meta_value' => $county->COUNTY_NAME,
                        ]
                    );
                } // end if state match
            }

            update_option( '_installed_us_county_' . $state, true, false );

            return 'Success';
        } else {
            return 'Already installed';
        }
    }

    /**
     * Import US State Tracks.
     *
     * @param $state
     *
     * @return string|\WP_Error
     */
    public static function upload_us_state_tracts( $state )
    {
        global $wpdb;

        if ( !post_type_exists( 'locations' ) ) {
            return new WP_Error( 'no_post_type','Fail: You need the locations post type installed through Disciple Tools.' );
        }

        if ( !get_option( '_installed_us_tracts_' . $state ) ) { // check if counties are installed for the state

            $directory = dt_get_usa_meta(); // get directory;
            $file = $directory->USA_states->{$state}->file;

            $kml_object = simplexml_load_file( $directory->base_url . $file ); // get xml from amazon

            foreach ( $kml_object->Document->Folder->Placemark as $place ) {

                // Parse Coordinates
                $value = '';
                if ( $place->Polygon ) {
                    $value .= $place->Polygon->outerBoundaryIs->LinearRing->coordinates;
                } elseif ( $place->MultiGeometry ) {
                    foreach ( $place->MultiGeometry->Polygon as $polygon ) {
                        $value .= $polygon->outerBoundaryIs->LinearRing->coordinates;
                    }
                }

                $value_array = substr( trim( $value ), 0, -2 ); // remove trailing ,0 so as not to create an empty array
                unset( $value );
                $value_array = explode( ',0.0 ', $value_array ); // create array from coordinates string

                $coordinates = '['; //Create JSON format coordinates. Display in Google Map
                foreach ( $value_array as $va ) {
                    if ( !empty( $va ) ) {
                        $coord = explode( ',', $va );
                        $coordinates .= '{"lat": ' . $coord[1] . ', "lng": ' . $coord[0] . '},';
                    }
                }

                unset( $value_array );
                $coordinates = substr( trim( $coordinates ), 0, -1 );
                $coordinates .= ']'; // close JSON array

                // Find County Post ID
                $geoid = $place->ExtendedData->SchemaData->SimpleData[4];
                $state_county_key = substr( $geoid, 0, 5 );
                $post_id = $wpdb->get_var( $wpdb->prepare(
                    "SELECT
                        ID
                    FROM
                        `$wpdb->posts`
                    WHERE
                        post_type = 'locations'
                        AND post_name = %s",
                    $state_county_key
                ) );

                $wpdb->insert(
                    $wpdb->postmeta,
                    [
                        'post_id'    => $post_id,
                        'meta_key'   => 'polygon_' . $geoid,
                        'meta_value' => $coordinates,
                    ]
                );
            } // end foreach tract

            unset( $kml_object );

            update_option( '_installed_us_tracts_' . $state, true, false );

            return 'Success';
        } else {
            return 'Tracts for ' . $state . ' already installed.';
        }
    }

    /**
     * Uploads US Census Tract KML file to Locations Post Type
     *
     * @param $state
     *
     * @return string
     */
    public static function census_tract_kml_to_post_type( $state )
    {
        global $wpdb;

        // test if locations post type exists
        if ( !post_type_exists( 'locations' ) ) {
            return 'Fail: You need the locations post type installed through Disciple Tools.';
        }

        if ( !get_option( '_installed_us_county_' . $state ) ) { // check if counties are installed for the state

            $counties = dt_get_usa_meta();

            foreach ( $counties->USA_counties as $county ) {
                if ( $county->STATE == $state ) {
                    $post = [
                        "post_title"            => $county->COUNTY_NAME . ', ' . $county->STUSAB,
                        'post_type'             => 'locations',
                        "post_content"          => '',
                        "post_excerpt"          => '',
                        "post_name"             => $county->STATE . $county->COUNTY,
                        "post_content_filtered" => $county->STATE . $county->COUNTY,
                        "post_status"           => "publish",
                        "post_author"           => get_current_user_id(),
                    ];

                    $new_post_id = wp_insert_post( $post );

                    /* Metadata inserted separately to avoid activity hooks on metadata inserts. These were causing memory problems on large inserts. */
                    // state meta
                    $wpdb->insert(
                        $wpdb->postmeta,
                        [
                            'post_id'    => $new_post_id,
                            'meta_key'   => 'STATE',
                            'meta_value' => $county->STATE,
                        ]
                    );
                    // state meta
                    $wpdb->insert(
                        $wpdb->postmeta,
                        [
                            'post_id'    => $new_post_id,
                            'meta_key'   => 'COUNTY',
                            'meta_value' => $county->COUNTY,
                        ]
                    );
                    // state meta
                    $wpdb->insert(
                        $wpdb->postmeta,
                        [
                            'post_id'    => $new_post_id,
                            'meta_key'   => 'STUSAB',
                            'meta_value' => $county->STUSAB,
                        ]
                    );
                    // state meta
                    $wpdb->insert(
                        $wpdb->postmeta,
                        [
                            'post_id'    => $new_post_id,
                            'meta_key'   => 'COUNTY_NAME',
                            'meta_value' => $county->COUNTY_NAME,
                        ]
                    );
                } // end if state match
            }

            update_option( '_installed_us_county_' . $state, true, false );

            return 'Success';
        } else {
            return 'Already installed';
        }
    }

    /**
     * Import US State Tracks.
     *
     * @since 0.1.0
     *
     * @param $state
     *
     * @return string
     */
    public static function upload_us_state_tracts_coordinates( $state )
    {
        global $wpdb;

        if ( !post_type_exists( 'locations' ) ) {
            return 'Fail: You need the locations post type installed through Disciple Tools.';
        }

        if ( !get_option( '_installed_us_tracts_' . $state ) ) { // check if counties are installed for the state

            $directory = dt_get_usa_meta(); // get directory;
            $file = $directory->USA_states->{$state}->file;

            $kml_object = simplexml_load_file( $directory->base_url . $file ); // get xml from amazon

            foreach ( $kml_object->Document->Folder->Placemark as $place ) {

                // Parse Coordinates
                $value = '';
                if ( $place->Polygon ) {
                    $value .= $place->Polygon->outerBoundaryIs->LinearRing->coordinates;
                } elseif ( $place->MultiGeometry ) {
                    foreach ( $place->MultiGeometry->Polygon as $polygon ) {
                        $value .= $polygon->outerBoundaryIs->LinearRing->coordinates;
                    }
                }

                $value_array = substr( trim( $value ), 0, -2 ); // remove trailing ,0 so as not to create an empty array
                unset( $value );
                $value_array = explode( ',0.0 ', $value_array ); // create array from coordinates string

                $coordinates = '['; //Create JSON format coordinates. Display in Google Map
                foreach ( $value_array as $va ) {
                    if ( !empty( $va ) ) {
                        $coord = explode( ',', $va );
                        $coordinates .= '{"lat": ' . $coord[1] . ', "lng": ' . $coord[0] . '},';
                    }
                }

                unset( $value_array );
                $coordinates = substr( trim( $coordinates ), 0, -1 );
                $coordinates .= ']'; // close JSON array

                // Find County Post ID
                $geoid = $place->ExtendedData->SchemaData->SimpleData[4];
                $state_county_key = substr( $geoid, 0, 5 );
                $post_id = $wpdb->get_var( $wpdb->prepare(
                    "SELECT
                        ID
                    FROM
                        `$wpdb->posts`
                    WHERE
                        post_type = 'locations'
                        AND post_name = %s",
                    $state_county_key
                ) );

                $wpdb->insert(
                    $wpdb->postmeta,
                    [
                        'post_id'    => $post_id,
                        'meta_key'   => 'polygon_' . $geoid,
                        'meta_value' => $coordinates,
                    ]
                );
            } // end foreach tract

            unset( $kml_object );

            update_option( '_installed_us_tracts_' . $state, true, false );

            return 'Success';
        } else {
            return 'Tracts for ' . $state . ' already installed.';
        }
    }

    /**
     * The box for deleting locations
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function delete_locations_box()
    {
        // check if $_POST to change option
        $status = '';

        if ( !empty( $_POST['delete_location'] ) && isset( $_POST['delete_location'] ) && wp_verify_nonce( sanitize_key( $_POST['delete_location'] ), 'delete_location_validate' ) ) {
            $status = $this->delete_locations();
        }

        // return form and dropdown
        $html = '';
        $html .= '<table class="widefat striped">
                    <thead><th>Delete All Locations</th></thead>
                    <tbody>
                        <tr>
                            <td><form action="" method="POST">' . wp_nonce_field( 'delete_location_validate', 'delete_location', true, false ) . '<button type="submit" class="button" value="submit">Delete All Locations Immediately</button>' . $status . '</form></td>
                        </tr>
                    </tbody>
                </table>
        ';

        return $html;
    }

    /**
     * Delete all locations in database
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function delete_locations()
    {
        global $wpdb;

        $args = [
            'numberposts' => -1,
            'post_type'   => 'locations',
        ];

        $locations = get_posts( $args );
        foreach ( $locations as $location ) {
            $id = $location->ID;
            wp_delete_post( $id, true );
        }

        $wpdb->get_results( "DELETE FROM $wpdb->postmeta WHERE NOT EXISTS (SELECT NULL FROM $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id)" );

        return 'Locations deleted';
    }

    /**
     * Import Omega Zone locations
     *
     * @since 0.1.0
     *
     * @param $cnty_id
     * @param $admin
     */
    public function insert_location_oz( $cnty_id, $admin )
    {

        $list = dt_get_oz_country_list( $admin );
        $parent_post_id = '';

        // Install single top level country record for Admin1 only
        if ( $admin == 'admin1' ) {

            $country_list = dt_get_oz_country_list( 'cnty' );
            $country_name = '';
            $country_id = '';

            foreach ( $country_list as $value ) {
                if ( $value->CntyID == $cnty_id ) {
                    $country_name = $value->Zone_Name;
                    $country_id = $value->WorldID;
                    break;
                }
            }

            if ( !empty( $country_name ) || !empty( $country_id ) ) {
                $post = [
                    "post_title"   => $country_name . ' ( ' . $country_id . ' )',
                    'post_type'    => 'locations',
                    "post_content" => '',
                    "post_excerpt" => '',
                    "post_name"    => $country_id,
                    "post_status"  => "publish",
                    "post_author"  => get_current_user_id(),
                ];

                $parent_post_id = wp_insert_post( $post );
            }
        }

        // Loop the admin level list
        foreach ( $list as $item ) {

            if ( $item->CntyID == $cnty_id ) {

                $content = '';

                foreach ( $item as $key => $value ) {
                    $content .= $key . ': ' . $value . '<br>';
                }

                $post = [
                    "post_title"   => $item->Zone_Name . ' ( ' . $item->WorldID . ' )',
                    'post_type'    => 'locations',
                    "post_content" => $content,
                    "post_excerpt" => '',
                    "post_parent"  => $parent_post_id,
                    "post_name"    => $item->WorldID,
                    "post_status"  => "publish",
                    "post_author"  => get_current_user_id(),
                ];

                wp_insert_post( $post );
            }
        }
    }

    /**
     * @param $geojson
     *
     * @since 0.1.0
     *
     * @return bool
     */
    public static function insert_geojson( $geojson )
    {
        global $wpdb;

        if ( empty( $geojson ) ) {
            return false;
        }

        $record_count = count( $geojson['features'] );
        $i = 0;

        foreach ( $geojson['features'] as $place ) {
            $properties = $place['properties'];
            $properties['coordinates'] = json_encode( $place['geometry']['coordinates'] ); // combine coordinates into a the single properties array
            $properties['coordinates_type'] = $place['geometry']['type'];

            $world_id = $properties['WorldID'];

            // duplicate check
            $duplicate_post_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT
                    ID
                FROM
                    `$wpdb->posts`
                WHERE
                    post_name = %s",
                $world_id
            ) );

            // insert post record
            $wpdb->replace(
                $wpdb->posts,
                [
                    'ID'                => $duplicate_post_id,
                    'post_name'         => strtolower( $properties['WorldID'] ),
                    'post_author'       => get_current_user_id(),
                    'post_date'         => current_time( 'mysql' ),
                    'post_date_gmt'     => current_time( 'mysql' ),
                    'post_content'      => $properties['Zone_Name'] . ' (' . $properties['WorldID'] . ')',
                    'post_title'        => $properties['Zone_Name'],
                    'post_status'       => 'publish',
                    'ping_status'       => 'closed',
                    'post_modified'     => current_time( 'mysql' ),
                    'post_modified_gmt' => current_time( 'mysql' ),
                    'post_parent'       => 0,
                    'guid'              => home_url() . '/locations/' . strtolower( $properties['WorldID'] ),
                    'post_type'         => 'locations',
                ],
                [
                    '%d',
                    '%s',
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                    '%s',
                ]
            );
            $new_post_id = $wpdb->insert_id;

            // insert metadata
            if ( !empty( $new_post_id ) ) {

                // delete previous meta records
                if ( !empty( $duplicate_post_id ) ) {
                    $wpdb->delete( $wpdb->postmeta, [ 'post_id' => $duplicate_post_id ], $where_format = null );
                }

                foreach ( $properties as $key => $value ) {
                    $wpdb->insert(
                        $wpdb->postmeta,
                        [
                            'post_id'    => $new_post_id,
                            'meta_key'   => $key,
                            'meta_value' => $value,
                        ],
                        [
                            '%d',
                            '%s',
                            '%s',
                        ]
                    );
                }
            }
            $i++;
        }

        return ( $record_count == $i ) ? true : false;
    }

    /**
     * @since 0.1.0
     *
     * @param $cnty_id
     *
     * @return bool
     */
    public static function delete_location_data( $cnty_id )
    {
        global $wpdb;

        $results1 = $wpdb->query( $wpdb->prepare(
            "DELETE from
                `$wpdb->posts`
            WHERE
                post_type = 'locations'
                AND post_name LIKE %s",
            $wpdb->esc_like( $cnty_id ) . "%"
        ) );
        $results2 = $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE NOT EXISTS (SELECT NULL FROM $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id);" );

        return ( $results1 || $results2 ) ? true : false;
    }

}
