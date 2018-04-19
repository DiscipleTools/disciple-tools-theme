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

    public static function auto_build_location( $post_id ) {
        dt_write_log( __METHOD__ );

        $geocode = new Disciple_Tools_Google_Geocode_API(); // api class
        $errors = new WP_Error();

        $post_raw = get_post_meta( $post_id, 'raw', true );
        if ( ! $post_raw ) {
            $errors->add( __METHOD__, 'No google geocode installed.' );
        }

        $posts_created = [];
        $parent_id = 0; // cascade parent id down through the levels
        $auto_build_settings = dt_get_option( 'location_levels' ); // auto build settings
        $locations_result = self::query_all_geocoded_locations(); // array of all locations
        $base_name = $geocode::parse_raw_result( $post_raw, 'base_name' ); // base location name of post
        $components = array_reverse( $geocode::parse_raw_result( $post_raw, 'address_components' ), true );

        foreach ( $components as $key => $component ) {

            if ( ! ( 0 == $key ) ) { // check if
                if ( $auto_build_settings[$component['types'][0]] ?? false ) { // if level is not set, then skip
                    // get existing level post id, or make new post
                    $this_level_name = $component['long_name'];
                    $this_level_id = self::does_location_exist( $locations_result, $this_level_name, $component['types'][0] );
                    if ( $this_level_id ) {
                        if ( ! ( $parent_id == wp_get_post_parent_id( $this_level_id ) ) ) {
                            $update_this_level_post = [
                                'ID'           => $this_level_id,
                                'post_parent' => $parent_id,
                                'post_status' => 'publish',
                                'post_type' => 'locations',
                            ];
                            dt_write_log( 'About to insert' );
                            dt_write_log( $update_this_level_post );
                            $update_level = wp_update_post( $update_this_level_post, true );
                            if ( is_wp_error( $update_level ) ) {
                                $errors->add( __METHOD__, 'Failed update parent on ' . $this_level_id . ' post: ' . $update_level->get_error_message() );
                            }
                        }
                        $parent_id = $this_level_id;
                    } else {
                        $this_level_raw = $geocode::query_google_api( $this_level_name );
                        if ( ! $this_level_raw ) {
                            $errors->add( __METHOD__, 'Geocode of '.$this_level_name.' failed' );
                        }
                        else {
                            $args = [
                                'post_title' => $geocode::parse_raw_result( $this_level_raw, 'base_name' ),
                                'post_parent' => $parent_id,
                                'post_status' => 'publish',
                                'post_type' => 'locations',
                                'meta_input' => [
                                    'location_address' => $geocode::parse_raw_result( $this_level_raw, 'formatted_address' ),
                                    'raw' => $this_level_raw,
                                    'types' => $geocode::parse_raw_result( $this_level_raw, 'types' ),
                                    'base_name' => $geocode::parse_raw_result( $this_level_raw, 'base_name' ),
                                ]
                            ];
                            dt_write_log( 'About to insert' );
                            dt_write_log( $args );
                            $this_level_id = wp_insert_post( $args, true );
                            if ( is_wp_error( $this_level_id ) ) {
                                $errors->add( __METHOD__, 'Failed to create post record in DT.: ' . $this_level_id->get_error_message() );
                            }
                            else {
                                $parent_id = $this_level_id;
                                $posts_created[] = $this_level_id;
                            } // end if insert successful
                        } // end if geocoding successful
                    } // end if level already exists
                } // end if level required by settings
            } // make sure not to create self

        } // end foreach loop through address_component levels


       // add parent_id to $post_id
        if ( $post_id != $parent_id ) {
            $update_post = [
                'ID'           => $post_id,
                'post_parent' => $parent_id,
            ];
            $update = wp_update_post( $update_post, true );
            if ( is_wp_error( $update ) ) {
                $errors->add( __METHOD__, 'Failed update current post: ' . $update->get_error_message() );
            }
        }

        // return new parent id
        if ( is_wp_error( $update ) ) {
            return [
                'status' => 'Fail',
                'posts_created' => $posts_created,
                'errors' => $errors,
            ];
        } else {
            return [
                'status' => 'OK',
                'posts_created' => $posts_created,
                'errors' => $errors,
            ];
        }
    }

    public static function get_next_level_parent_id( $raw ) {

        $existing_locations = self::query_geocoded_names();
        $political_list = Disciple_Tools_Google_Geocode_API::parse_raw_result( $raw, 'address_components' );
        foreach ( $political_list as $key => $item ) {
            if ( ! ( 0 == $key ) ) { // exclude self
                foreach ( $existing_locations as $existing_location ) {
                    if ( $item['long_name'] == $existing_location['base_name'] ) { // look for match
                        return $existing_location['ID'];
                    }
                }
            }
        }

        return false;
    }

    /**
     * Filters the self::query_all_geocoded_locations() to find a matching location.
     *
     * @param array $locations_result
     * @param       $address_component
     *
     * @return bool|int Returns post_id on success, false on failure.
     */
    public static function does_location_exist( array $locations_result, $address_component, $type ) {
        if ( empty( $locations_result ) || ! is_array( $locations_result ) ) {
            $locations_result = self::query_all_geocoded_locations();
        }
        foreach ( $locations_result as $result ) {
            if ( ! isset( $result['raw'] ) ) {
                continue;
            }
            if ( $address_component == Disciple_Tools_Google_Geocode_API::parse_raw_result( $result['raw'], 'base_name' )
                && $type == Disciple_Tools_Google_Geocode_API::parse_raw_result( $result['raw'], 'types' )) {
                return $result['ID'];
            }
        }
        return false;
    }

    /**
     * Returns all geocoded location post_types with 5 columns
     * - ID (int)
     * - post_title (string)
     * - post_parent (int)
     * types      (string) (administrative levels like `country`, `administrative_area_level_1`, `locality`, etc.
     *              country
     *              administrative_area_level_1
     *              administrative_area_level_2
     *              administrative_area_level_3
     *              administrative_area_level_4
     *
     * - raw        (array) (raw google response
     *
     * @return array|null
     */
    public static function query_all_geocoded_locations() {
        global $wpdb;

        $results = $wpdb->get_results( "
        SELECT a.ID, a.post_title, a.post_parent, b.meta_value as types, c.meta_value as raw
        FROM $wpdb->posts as a
          LEFT JOIN $wpdb->postmeta as b
          ON a.ID=b.post_id
             AND b.meta_key = 'types'
          LEFT JOIN $wpdb->postmeta as c
          ON a.ID=c.post_id
            AND c.meta_key = 'raw'
        WHERE post_type = 'locations'
              AND post_status = 'publish'
              AND c.meta_value IS NOT NULL
        ",
        ARRAY_A );

        if ( empty( $results ) ) {
            return $results;
        } else {
            foreach ( $results as $key => $result ) {
                $results[$key]['raw'] = maybe_unserialize( $result['raw'] );
            }
            return $results;
        }
    }

    public static function query_geocoded_names() {
        global $wpdb;

        $results = $wpdb->get_results( "
        SELECT a.ID, a.post_parent, b.meta_value as types, c.meta_value as base_name
        FROM $wpdb->posts as a
          LEFT JOIN $wpdb->postmeta as b
          ON a.ID=b.post_id
             AND b.meta_key = 'types' AND meta_value != 'route' AND meta_value != 'street_address'
          LEFT JOIN $wpdb->postmeta as c
          ON a.ID=c.post_id
             AND c.meta_key = 'base_name'
        WHERE post_type = 'locations'
              AND post_status = 'publish'
              AND c.meta_value IS NOT NULL
        ",
        ARRAY_A );

        if ( empty( $results ) ) {
            return $results;
        } else {
            return $results;
        }
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
}
