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

    public static function insert_parent_location( $address, $parent_name, $child_id ) {

        $google_result = Disciple_Tools_Google_Geocode_API::query_google_api( $address );
        if ( ! $google_result ) {
            return new WP_Error( __METHOD__, 'Failed to geocode address.' );
        }

        // lookup $child id
        // parse child id raw

        // create new parent

        // connect new parent to next level existing parent

        // connect child to new parent

        // return new parent id
        return [
            'status' => 'OK',
            'data' => true,
        ];
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
