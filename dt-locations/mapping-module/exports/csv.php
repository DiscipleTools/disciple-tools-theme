<?php

if ( defined( 'ABSPATH' ) ) {
    return; // return unless accessed directly
}
if ( ! function_exists( 'dt_write_log' ) ) {
    /**
     * A function to assist development only.
     * This function allows you to post a string, array, or object to the WP_DEBUG log.
     *
     * @param $log
     */
    function dt_write_log( $log ) {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}
// @codingStandardsIgnoreLine
require( $_SERVER[ 'DOCUMENT_ROOT' ] . '/wp-load.php' ); // loads the wp framework when called

$type = $_GET['type'];
switch( $type ) {
    case 'cities':
        $places = [];
        $results = $wpdb->get_results("
            SELECT * FROM dt_geonames WHERE feature_code LIKE 'PP%' AND feature_class = 'P' AND population > 100000",
            ARRAY_A);
        foreach ($results as $index => $result ) {
            $places[] = [
                'geonamid' => $result['geonameid'],
                'name' => $result['name'],
                'population' => $result['population'],
                'latitude' => $result['latitude'],
                'longitude' => $result['longitude'],
            ];
            //    dt_write_log($value);
        }
        $columns = array( 'geonameid', 'name', 'population', 'latitude', 'longitude' );
        break;

    case 'list':
        $places = [];
        $results = $wpdb->get_results("
            SELECT meta_value 
            FROM $wpdb->postmeta 
            WHERE meta_key = 'zume_raw_record'
              AND post_id IN (SELECT ID FROM $wpdb->posts WHERE post_type = 'groups')",
            ARRAY_A);
        foreach ($results as $index => $result ) {
            $value = maybe_unserialize( $result['meta_value'] );
            $places[$index] = [
                'id' => $index,
                'name' => $value['group_name'],
                'address' => $value['address'],
                'type' => 'church',
                'lat' => $value['lat'],
                'lng' => $value['lng'],
            ];
            //    dt_write_log($value);
        }
        $columns = array( 'id', 'name', 'address', 'type', 'lat', 'lng' );
        break;
}


// output headers so that the file is downloaded rather than displayed
header( 'Content-Type: text/csv; charset=utf-8' );
header( 'Content-Disposition: attachment; filename=data.csv' );

// create a file pointer connected to the output stream
$output = fopen( 'php://output', 'w' );

// output the column headings
fputcsv( $output, $columns);

// fetch the data


// loop over the rows, outputting them
foreach ($places as $row ) {
    fputcsv( $output, $row );
}