<?php
/**
 * Contains create, update and delete functions for locations, wrapping access to
 * the database
 *
 * @package  Disciple_Tools
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

/**
 * Class Disciple_Tools_Locations
 */
class Disciple_Tools_Locations
{

    /**
     * Get all locations in database
     *
     * @return array|WP_Error
     */
    public static function get_locations()
    {
        if ( ! current_user_can( 'read_location' ) ) {
            return new WP_Error( __FUNCTION__, __( "No permissions to read locations" ), [ 'status' => 403 ] );
        }

        $query_args = [
            'post_type' => 'locations',
            'orderby'   => 'ID',
            'nopaging'  => true,
        ];
        $query = new WP_Query( $query_args );

        return $query->posts;
    }

    /**
     * @param $search
     *
     * @return array|WP_Error
     */
    public static function get_locations_compact( $search )
    {
        if ( !current_user_can( 'read_location' )){
            return new WP_Error( __FUNCTION__, __( "No permissions to read locations" ), [ 'status' => 403 ] );
        }
        $query_args = [
            'post_type' => 'locations',
            'orderby'   => 'ID',
            's'         => $search,
        ];
        $query = new WP_Query( $query_args );
        $list = [];
        foreach ( $query->posts as $post ) {
            $list[] = [ "ID" => $post->ID, "name" => $post->post_title ];
        }

        return $list;
    }

    /**
     * Gets a count for the different levels of 4K locations
     *
     * @param int|string $level  Default is to 999, which is 'all' standard location records.
     *
     * @return int|null
     */
    public static function get_standard_locations_count( int $level = 999 )
    {
        switch ( $level ) {

            case 0:
                $args = [
                    'post_type'  => 'locations',
                    'meta_key'   => 'WorldID',
                    'nopaging'   => true,
                    'meta_query' => [
                        [
                            'key'     => 'WorldID',
                            'value'   => '^...$',
                            'compare' => 'REGEXP',
                        ],
                    ],

                ];
                $result = new WP_Query( $args );
                return $result->post_count;
                break;
            case 1:
                $args = [
                    'post_type'  => 'locations',
                    'meta_key'   => 'WorldID',
                    'nopaging'   => true,
                    'meta_query' => [
                        [
                            'key'     => 'WorldID',
                            'value'   => '^.......$',
                            'compare' => 'REGEXP',
                        ],
                    ],

                ];
                $result = new WP_Query( $args );
                return $result->post_count;
                break;
            case 2:
                $args = [
                    'post_type'  => 'locations',
                    'meta_key'   => 'WorldID',
                    'nopaging'   => true,
                    'meta_query' => [
                        [
                            'key'     => 'WorldID',
                            'value'   => '^...........$',
                            'compare' => 'REGEXP',
                        ],
                    ],

                ];
                $result = new WP_Query( $args );
                return $result->post_count;
                break;
            case 3:
                $args = [
                    'post_type'  => 'locations',
                    'meta_key'   => 'WorldID',
                    'nopaging'   => true,
                    'meta_query' => [
                        [
                            'key'     => 'WorldID',
                            'value'   => '^...............$',
                            'compare' => 'REGEXP',
                        ],
                    ],

                ];
                $result = new WP_Query( $args );
                return $result->post_count;
                break;
            case 4:
                $args = [
                    'post_type'  => 'locations',
                    'meta_key'   => 'WorldID',
                    'nopaging'   => true,
                    'meta_query' => [
                        [
                            'key'     => 'WorldID',
                            'value'   => '^...................$',
                            'compare' => 'REGEXP',
                        ],
                    ],

                ];
                $result = new WP_Query( $args );
                return $result->post_count;
                break;
            default:
                $args = [
                    'post_type'  => 'locations',
                    'meta_key'   => 'WorldID',
                    'nopaging'   => true,
                ];
                $result = new WP_Query( $args );

                return $result->post_count;
                break;
        }
    }

    /**
     * Returns standard countries present in the system.
     *
     * @return \WP_Query
     */
    public static function get_standard_admin0() {
        $args = [
            'post_type'  => 'locations',
            'meta_key'   => 'WorldID',
            'nopaging'   => true,
            'meta_query' => [
                [
                    'key'     => 'WorldID',
                    'value'   => '^...$',
                    'compare' => 'REGEXP',
                ],
            ],

        ];
        return new WP_Query( $args );
    }

    /**
     * Returns standard locations from administrative level 1 (i.e. states)
     *
     * @param $world_id
     *
     * @return bool|\WP_Query|WP_Error
     */
    public static function get_standard_admin1( $world_id ) {

        if ( empty( $world_id ) ) {
            return false;
        }

        $world_id = strtoupper( substr( trim( $world_id ), 0, 3 ) );

        if ( ! preg_match( '/^[A-Z][A-Z][A-Z]/', $world_id ) ) {
            return new WP_Error( 'failed_query_pattern', 'Failed to match pattern required for world_id.' );
        }

        $args = [
            'post_type'  => 'locations',
            'nopaging'   => true,
            'meta_query' => [
                [
                    'key'     => 'WorldID',
                    'value'   => '^' . $world_id . '....$',
                    'compare' => 'REGEXP',
                ],
            ],

        ];
        return new WP_Query( $args );
    }

    /**
     * @param $world_id
     *
     * @return bool|\WP_Query|WP_Error
     */
    public static function get_standard_admin2( $world_id ) {

        if ( empty( $world_id ) ) {
            return new WP_Error( 'failed_to_provide_world_id', 'Failed to provide valid world_id.' );
        }

        $world_id = strtoupper( substr( trim( $world_id ), 0, 7 ) );

        if ( ! preg_match( '/^[A-Z][A-Z][A-Z].[A-Z][A-Z][A-Z]/', $world_id ) ) {
            return new WP_Error( 'failed_query_pattern', 'Failed to match pattern required for world_id.' );
        }

        $args = [
            'post_type'  => 'locations',
            'nopaging'   => true,
            'meta_query' => [
                [
                    'key'     => 'WorldID',
                    'value'   => '^' . $world_id . '....$',
                    'compare' => 'REGEXP',
                ],
            ],

        ];
        return new WP_Query( $args );
    }

    /**
     * Get all standard Admin level 3 records
     *
     * @param $world_id         (Required) XXX-XXX-XXX pattern world_id to find XXX-XXX-XXX-XXX next level records
     *
     * @return bool|\WP_Query|WP_Error
     */
    public static function get_standard_admin3( $world_id ) {

        if ( empty( $world_id ) ) {
            return new WP_Error( 'failed_to_provide_world_id', 'Failed to provide valid world_id.' );
        }

        $world_id = strtoupper( substr( trim( $world_id ), 0, 11 ) );

        if ( ! preg_match( '/^[A-Z][A-Z][A-Z].[A-Z][A-Z][A-Z].[A-Z][A-Z][A-Z]/', $world_id ) ) {
            return new WP_Error( 'failed_query_pattern', 'Failed to match pattern required for world_id.' );
        }

        $args = [
            'post_type'  => 'locations',
            'nopaging'   => true,
            'meta_query' => [
                [
                    'key'     => 'WorldID',
                    'value'   => '^' . $world_id . '....$',
                    'compare' => 'REGEXP',
                ],
            ],

        ];
        return new WP_Query( $args );
    }

    /**
     * Get all standard Admin level 4 records
     *
     * @param $world_id         (Required) XXX-XXX-XXX pattern world_id to find XXX-XXX-XXX-XXX next level records
     *
     * @return bool|\WP_Query|WP_Error
     */
    public static function get_standard_admin4( $world_id ) {

        if ( empty( $world_id ) ) {
            return new WP_Error( 'failed_to_provide_world_id', 'Failed to provide valid world_id.' );
        }

        $world_id = strtoupper( substr( trim( $world_id ), 0, 15 ) );

        if ( ! preg_match( '/^[A-Z][A-Z][A-Z].[A-Z][A-Z][A-Z].[A-Z][A-Z][A-Z].[A-Z][A-Z][A-Z]/', $world_id ) ) {
            return new WP_Error( 'failed_query_pattern', 'Failed to match pattern required for world_id.' );
        }

        $args = [
            'post_type'  => 'locations',
            'nopaging'   => true,
            'meta_query' => [
                [
                    'key'     => 'WorldID',
                    'value'   => '^' . $world_id . '....$',
                    'compare' => 'REGEXP',
                ],
            ],

        ];
        return new WP_Query( $args );
    }

    /**
     * Returns the tract geoid from an address
     * Zume Project USA
     *
     * @param  $address
     *
     * @return array
     */
    public static function geocode_address( $address, $type = 'full_object' )
    {

        $google_result = Disciple_Tools_Google_Geolocation::query_google_api( $address, $type ); // get google api info
        if ( $google_result == 'ZERO_RESULTS' ) {
            return [
                'status' => false,
                'message'  => 'Zero Results for Location',
            ];
        }

        return [
            'status' => true,
            'results'  => $google_result,
        ];
    }

    /**
     * Returns the tract geoid from an address
     * Zume Project USA
     *
     * @param  $address
     *
     * @return array
     */
    public static function get_tract_by_address( $address )
    {

        $google_result = Disciple_Tools_Google_Geolocation::query_google_api( $address, $type = 'core' ); // get google api info
        if ( $google_result == 'ZERO_RESULTS' ) {
            return [
                'status' => 'ZERO_RESULTS',
                'tract'  => '',
            ];
        }

        $census_result = Disciple_Tools_Census_Geolocation::query_census_api( $google_result['lng'], $google_result['lat'], $type = 'core' ); // get census api data
        if ( $census_result == 'ZERO_RESULTS' ) {
            return [
                'status' => 'ZERO_RESULTS',
                'tract'  => '',
            ];
        }

        return [
            'status' => 'OK',
            'tract'  => $census_result['geoid'],
        ];
    }

    /**
     * Returns the all the array elements needed for an address to tract map search
     * Zume Project USA
     *
     * @param  $address
     *
     * @return array
     */
    public static function get_tract_map( $address )
    {

        // Google API
        $google_result = Disciple_Tools_Google_Geolocation::query_google_api( $address, $type = 'core' ); // get google api info
        if ( $google_result == 'ZERO_RESULTS' ) {
            return [
                'status'  => 'ZERO_RESULTS',
                'message' => 'Failed google geolocation lookup.',
            ];
        }
        $lng = $google_result['lng'];
        $lat = $google_result['lat'];
        $formatted_address = $google_result['formatted_address'];

        // Census API
        $census_result = Disciple_Tools_Census_Geolocation::query_census_api( $lng, $lat, $type = 'core' ); // get census api data
        if ( $census_result == 'ZERO_RESULTS' ) {
            return [
                'status'  => 'ZERO_RESULTS',
                'message' => 'Failed getting census data',
            ];
        }
        $geoid = $census_result['geoid'];
        $zoom = $census_result['zoom'];
        $state = $census_result['state'];
        $county = $census_result['county'];

        // Boundary data
        $coordinates = Disciple_Tools_Coordinates_DB::get_db_coordinates( $geoid ); // return coordinates from database

        return [
            'status'            => 'OK',
            'zoom'              => $zoom,
            'lng'               => $lng,
            'lat'               => $lat,
            'formatted_address' => $formatted_address,
            'geoid'             => $geoid,
            'coordinates'       => $coordinates,
            'state'             => $state,
            'county'            => $county,
        ];
    }

    /**
     * Returns the all the array elements needed for an address to tract map search
     * Zume Project
     *
     * @param  $params
     *
     * @return array
     */
    public static function get_map_by_geoid( $params )
    {

        $geoid = $params['geoid'];

        // Boundary data
        $coordinates = Disciple_Tools_Coordinates_DB::get_db_coordinates( $geoid ); // return coordinates from database
        $meta = dt_get_coordinates_meta( $geoid ); // returns an array of meta

        return [
            'status'      => 'OK',
            'zoom'        => $meta['zoom'],
            'lng'         => (float) $meta['center_lng'],
            'lat'         => (float) $meta['center_lat'],
            'geoid'       => $geoid,
            'coordinates' => $coordinates,
            'state'       => substr( $geoid, 0, 1 ),
        ];
    }

}
