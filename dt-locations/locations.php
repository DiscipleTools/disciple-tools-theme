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
    public function __construct() {
        parent::__construct();
    }

    public static function auto_build_location( $data, $type, $components = [] ) {
        dt_write_log( __METHOD__ );

        // verify google geocode data
        $post_raw = [];
        $geocode = new Disciple_Tools_Google_Geocode_API(); // api class
        $errors = new WP_Error();

        switch ( $type ) {
            case 'post_id':
                $post_raw = get_post_meta( intval( $data ), 'raw', true );
                $post_id = intval( $data );
                break;
            case 'raw':
                if ( $geocode::check_valid_request_result( $data ) ) {
                    $post_raw = $data;
                }
                break;
            case 'address':
                $raw = $geocode::query_google_api_with_components( $data, $components );
                if ( $geocode::check_valid_request_result( $raw ) ) {
                    $post_raw = $raw;
                }
                break;
            default:
                $errors->add( __METHOD__, 'Type required' );
                return $errors;
                break;
        }

        if ( ! $geocode::check_valid_request_result( $post_raw ) ) {
            $errors->add( __METHOD__, 'No google geocode installed.' );
            return $errors;
        }

        // build locations
        $posts_created = [];
        $parent_id = 0; // cascade parent id down through the levels
        $auto_build_settings = dt_get_option( 'location_levels' ); // auto build settings
        $locations_result = self::query_all_geocoded_locations(); // array of all locations
        $components = array_reverse( $geocode::parse_raw_result( $post_raw, 'address_components' ), true );
        $latlng = $geocode::parse_raw_result( $post_raw, 'latlng' );

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
                            $update_level = wp_update_post( $update_this_level_post, true );
                            if ( is_wp_error( $update_level ) ) {
                                $errors->add( __METHOD__, 'Failed update parent on ' . $this_level_id . ' post: ' . $update_level->get_error_message() );
                            }
                        }
                        $parent_id = $this_level_id;
                    } else {
                        $this_level_raw = $geocode::query_google_api_reverse( $latlng, $component['types'][0] );
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
        if ( ! empty( $post_id ) ) {
            if ( $post_id != $parent_id ) {
                $update_post = [
                    'ID'           => $post_id,
                    'post_parent' => $parent_id,
                ];
                $update = wp_update_post( $update_post, true );
                if ( is_wp_error( $update ) ) {
                    $errors->add( __METHOD__, 'Failed update current post: ' . $update->get_error_message() );
                }
                $posts_created[] = $update;
            }
        }

        return [
            'status' => 'OK',
            'posts_created' => $posts_created,
            'errors' => $errors,
        ];

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
     * @param array         $locations_result (collection of all locations in the system)
     * @param string      $base_name (long name of the address you are looking for)
     * @param string      $types_name (string for the specific google geo code type. ie. country, locality)
     *
     * @return bool|int Returns post_id on success, false on failure.
     */
    public static function does_location_exist( array $locations_result, $base_name, $types_name ) {
        if ( empty( $locations_result ) || ! is_array( $locations_result ) ) {
            $locations_result = self::query_all_geocoded_locations();
        }
        foreach ( $locations_result as $result ) {
            if ( ! isset( $result['raw'] ) ) {
                continue;
            }
            if ( $base_name == Disciple_Tools_Google_Geocode_API::parse_raw_result( $result['raw'], 'base_name' )
                && $types_name == Disciple_Tools_Google_Geocode_API::parse_raw_result( $result['raw'], 'types' ) ) {
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
    public static function get_locations() {
        if ( ! current_user_can( 'read_location' ) ) {
            return new WP_Error( __FUNCTION__, "No permissions to read locations", [ 'status' => 403 ] );
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
    public static function get_locations_compact( $search ) {
        if ( !current_user_can( 'read_location' )){
            return new WP_Error( __FUNCTION__, "No permissions to read locations", [ 'status' => 403 ] );
        }
        $query_args = [
            'post_type' => 'locations',
            'orderby'   => 'title',
            'order' => 'ASC',
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

    public static function get_location_with_connections( $id ) {
        global $wpdb;
        $location_with_connections = [];

        $location = get_post( $id, ARRAY_A );
        if ( empty( $location ) ) {
            return new WP_Error( __METHOD__, 'No location found' );
        }
        $location_meta = get_post_meta( $id );
        if ( empty( $location_meta ) ) {
            return new WP_Error( __METHOD__, 'No location found' );
        }

        $location_with_connections['post_title'] = $location['post_title'];
        $location_with_connections['id'] = $id;

        if ( isset( $location_meta['raw'][0] ) && ! empty( $location_meta['raw'][0] ) ) {
            $raw = maybe_unserialize( $location_meta['raw'][0] );
            $google = new Disciple_Tools_Google_Geocode_API();
            $location_with_connections['api_key'] = $google::key();
            $location_with_connections['latitude'] = $google::parse_raw_result( $raw, 'lat' );
            $location_with_connections['longitude'] = $google::parse_raw_result( $raw, 'lng' );
            $location_with_connections['raw'] = $google::parse_raw_result( $raw, 'full' );
            $location_with_connections['zoom'] = 7; // @todo find a way to define this on the fly
        }

        $connections = $wpdb->get_results( $wpdb->prepare( "
            SELECT
              ID as id,
              post_title as name,
              p2p_type,
              b.meta_value as type,
              IF( c.meta_value, true, false) as is_user
            FROM $wpdb->p2p
              INNER JOIN $wpdb->posts
                ON $wpdb->p2p.p2p_from=$wpdb->posts.ID
              JOIN $wpdb->postmeta as a
                ON a.post_id=$wpdb->p2p.p2p_from
                   AND ( meta_key = 'overall_status' OR meta_key = 'group_status' )
                   AND meta_value = 'active'
              LEFT JOIN $wpdb->postmeta as b
                ON b.post_id=$wpdb->p2p.p2p_from
                   AND b.meta_key = 'group_type'
              LEFT JOIN wp_postmeta as c
                ON c.post_id=wp_p2p.p2p_from
                   AND c.meta_key = 'corresponds_to_user'
            WHERE p2p_to = %s
            ORDER BY post_title ASC
        ", $id ), ARRAY_A );

        $location_with_connections['total_contacts'] = 0;
        $location_with_connections['total_groups'] = 0;
        $location_with_connections['total_workers'] = 0;
        if ( ! empty( $connections ) ) {
            foreach ( $connections as $connection ) {
                if ( $connection['is_user'] ) {
                    $location_with_connections['workers'][] = $connection;
                    $location_with_connections['total_workers']++;
                }
                else if ( $connection['p2p_type'] === 'contacts_to_locations' ) {
                    $location_with_connections['contacts'][] = $connection;
                    $location_with_connections['total_contacts']++;
                } else {
                    $location_with_connections['groups'][] = $connection;
                    $location_with_connections['total_groups']++;
                }
            }
        }

        $location_with_connections['status'] = 'OK';

        return $location_with_connections;
    }

    public static function get_all_locations_grouped(){
        if ( !current_user_can( 'read_location' )){
            return new WP_Error( __FUNCTION__, "No permissions to read locations", [ 'status' => 403 ] );
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
