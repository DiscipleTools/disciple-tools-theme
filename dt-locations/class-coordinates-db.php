<?php

/**
 * Gets Coordinates from Database
 * Disciple_Tools_Coordinates_DB
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Coordinates_DB
 */
class Disciple_Tools_Coordinates_DB
{

    /**
     * @param $geoid        int tract geoid
     *
     * @return string
     */
    public static function get_db_coordinates( $geoid )
    {
        global $wpdb;

        if ( !post_type_exists( 'locations' ) ) {
            return 'post type locations is not registered';
        }

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT
                    meta_value
                FROM
                    `$wpdb->postmeta`
                WHERE
                    meta_key = %s",
                'polygon_' . $geoid
            )
        );

        return json_decode( $result );
    }

    /**
     * @param $state       int Two digit state code
     *
     * @return array|string
     */
    public static function get_db_state( $state )
    {
        if ( !post_type_exists( 'locations' ) ) {
            return 'post type locations is not registered';
        }

        global $wpdb;
        $coordinates_array = [];

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT
                meta_value, meta_key
            FROM
                `$wpdb->postmeta`
            WHERE
                meta_key LIKE %s",
            $wpdb->esc_like( "polygon_$state" ) . "%"
        ), ARRAY_A );

        foreach ( $results as $value ) {
            $coordinates_array[ $value['meta_key'] ] = json_decode( $value['meta_value'] );
        }

        return $coordinates_array;
    }

}
