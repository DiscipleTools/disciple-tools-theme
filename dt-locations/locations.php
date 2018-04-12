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
class Disciple_Tools_Locations extends Disciple_Tools_Posts
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function create_location( $fields = [], $check_permissions = true )
    {

        if ( $check_permissions && !current_user_can( 'publish_locations' ) ) {
            return new WP_Error( __FUNCTION__, __( "You may not publish a contact" ), [ 'status' => 403 ] );
        }

        $args = wp_parse_args(
            $fields,
            [
                'post_title'        => __('No Title'),
                'post_parent'       => '',
                'post_type'         => "locations",
                "post_status"       => 'publish',
                'meta_input'        => [
                    'location'      => [],
                    'lat'           => '',
                    'lng'           => '',
                    'northeast_lat' => '',
                    'northeast_lng' => '',
                    'southwest_lat' => '',
                    'southwest_lng' => '',
                ],
            ]
        );

        $post_id = wp_insert_post( $args );

        // Fire action after insert.
        do_action( 'dt_create_location', $args );

        return $post_id;
    }

    public static function update_location( $fields = [], $check_permissions = true ) {
        // @todo
    }

    public static function delete_location( $post_id, $check_permissions = true ) {
        // @todo
    }

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

    public static function convert_google_result_to_location_array( $raw_google_result ) {
        /**
        [raw] => Array
        (
        [results] => Array
        (
        [0] => Array
        (
        [address_components] => Array
        (
        [0] => Array
        (
        [long_name] => 9134
        [short_name] => 9134
        [types] => Array
        (
        [0] => street_number
        )

        )

        [1] => Array
        (
        [long_name] => Woodland Drive
        [short_name] => Woodland Dr
        [types] => Array
        (
        [0] => route
        )

        )

        [2] => Array
        (
        [long_name] => Northridge
        [short_name] => Northridge
        [types] => Array
        (
        [0] => neighborhood
        [1] => political
        )

        )

        [3] => Array
        (
        [long_name] => Littleton
        [short_name] => Littleton
        [types] => Array
        (
        [0] => locality
        [1] => political
        )

        )

        [4] => Array
        (
        [long_name] => Douglas County
        [short_name] => Douglas County
        [types] => Array
        (
        [0] => administrative_area_level_2
        [1] => political
        )

        )

        [5] => Array
        (
        [long_name] => Colorado
        [short_name] => CO
        [types] => Array
        (
        [0] => administrative_area_level_1
        [1] => political
        )

        )

        [6] => Array
        (
        [long_name] => United States
        [short_name] => US
        [types] => Array
        (
        [0] => country
        [1] => political
        )

        )

        [7] => Array
        (
        [long_name] => 80126
        [short_name] => 80126
        [types] => Array
        (
        [0] => postal_code
        )

        )

        [8] => Array
        (
        [long_name] => 2276
        [short_name] => 2276
        [types] => Array
        (
        [0] => postal_code_suffix
        )

        )

        )

        [formatted_address] => 9134 Woodland Dr, Littleton, CO 80126, USA
        [geometry] => Array
        (
        [location] => Array
        (
        [lat] => 39.550007
        [lng] => -104.988124
        )

        [location_type] => ROOFTOP
        [viewport] => Array
        (
        [northeast] => Array
        (
        [lat] => 39.551355980291
        [lng] => -104.98677501971
        )

        [southwest] => Array
        (
        [lat] => 39.548658019708
        [lng] => -104.98947298029
        )

        )

        )

        [place_id] => ChIJb7OyjEyCbIcRdsbUSyhASfU
        [types] => Array
        (
        [0] => street_address
        )

        )

        )

        [status] => OK
        )

        )

         */

        $locations_array = [
            'post_title'        => '',
            'post_parent'       => '',
            'post_type'         => "locations",
            "post_status"       => 'publish',
            'meta_input'        => [
                'location'      => [],
                'lat'           => '',
                'lng'           => '',
                'northeast_lat' => '',
                'northeast_lng' => '',
                'southwest_lat' => '',
                'southwest_lng' => '',
            ],
        ];



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
            'posts_per_page' => 30,
        ];
        $query = new WP_Query( $query_args );
        $list = [];
        foreach ( $query->posts as $post ) {
            $list[] = [
            "ID" => $post->ID,
            "name" => $post->post_title
            ];
        }
        return [
        "total" => $query->found_posts,
        "posts" => $list
        ];
    }

    public static function get_all_locations_grouped(){
        if ( !current_user_can( 'read_location' )){
            return new WP_Error( __FUNCTION__, __( "No permissions to read locations" ), [ 'status' => 403 ] );
        }
        $query_args = [
            'post_type' => 'locations',
            'orderby'   => 'ID',
            'nopaging'  => true,
        ];
        $query = new WP_Query( $query_args );
        $list = [];

        foreach ( $query->posts as $post ){
            $list[ $post->ID ] = [
                "ID" => $post->ID,
                "name" => $post->post_title,
                "parent" => $post->post_parent,
                "region" => "No Region"
            ];
        }
        function get_top_parent( $list, $current_id ){
            if ( $list[ $current_id ]["parent"] == 0 ){
                return $current_id;
            } else {
                return get_top_parent( $list, $list[$current_id]["parent"] );
            }
        }

        foreach ( $list as $post_id => $post_value ) {
            if ( $post_value["parent"] &&
                 isset( $list[$post_value["parent"]] ) ){
                $top_parent = get_top_parent( $list, $post_id );
                $list[$post_id]["region"] = $list[$top_parent]["name"];
                $list[$post_id]["filter"] = $list[$top_parent]["name"];
                $list[$top_parent]["region"] = $list[$top_parent]["name"];
                $list[$top_parent]["filter"] = $list[$top_parent]["name"];
            }
        }
        $return_list = [];
        foreach ( $list as $post_id => $post_value ) {
            $return_list[] = $post_value;
        }
        return [
            "total" => $query->found_posts,
            "posts" => $return_list
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
    public static function geocode_address( $address, $type = 'full_object' )
    {

        $google_result = Disciple_Tools_Google_Geocode_API::query_google_api( $address, $type ); // get google api info
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

        $google_result = Disciple_Tools_Google_Geocode_API::query_google_api( $address, $type = 'core' ); // get google api info
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
        $google_result = Disciple_Tools_Google_Geocode_API::query_google_api( $address, $type = 'core' ); // get google api info
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
