<?php
/**
 * Plugin Name: Disciple Tools - Google Earth
 */
if ( defined( 'ABSPATH' ) ) {
    return; // return unless accessed directly
}
// @codingStandardsIgnoreStart
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

$places = [];
$results = $wpdb->get_results("
    SELECT meta_value 
    FROM $wpdb->postmeta 
    WHERE meta_key = 'zume_raw_record'
      AND post_id IN (SELECT ID FROM $wpdb->posts WHERE post_type = 'groups')", ARRAY_A);
foreach ($results as $index => $result ) {
    $value = maybe_unserialize( $result['meta_value'] );
    $places[$index] = [
        'id' => $index,
        'name' => urlencode( $value['group_name'] ),
        'address' => '',
        'type' => 'church',
        'lat' => $value['lat'],
        'lng' => $value['lng'],
    ];
//    dt_write_log($value);
}


/**
 * Get data
 */
//$places = [
//    [
//        'id' => '1',
//        'name' => 'Pan Africa Market',
//        'address' => '1521 1st Ave, Seattle, WA',
//        'type' => 'restaurant',
//        'lng' => '-122.340145',
//        'lat' => '47.608941',
//    ],
//    [
//        'id' => '2',
//        'name' => 'Buddha Thai & Bar',
//        'address' => '2222 2nd Ave, Seattle, WA',
//        'type' => 'bar',
//        'lng' => '-122.344394',
//        'lat' => '47.613591',
//    ],
//];

/**
 * Build KML File
 */
// Creates the Document.
$dom = new DOMDocument( '1.0', 'UTF-8' );

// Creates the root KML element and appends it to the root document.
$node = $dom->createElementNS( 'http://earth.google.com/kml/2.1', 'kml' );
$parNode = $dom->appendChild( $node );

// Creates a KML Document element and append it to the KML element.
$dnode = $dom->createElement( 'Document' );
$docNode = $parNode->appendChild( $dnode );

// Creates the two Style elements, one for restaurant and one for bar, and append the elements to the Document element.
$restStyleNode = $dom->createElement( 'Style' );
$restStyleNode->setAttribute( 'id', 'restaurantStyle' );
$restIconstyleNode = $dom->createElement( 'IconStyle' );
$restIconstyleNode->setAttribute( 'id', 'restaurantIcon' );
$restIconNode = $dom->createElement( 'Icon' );
$restHref = $dom->createElement( 'href', 'http://maps.google.com/mapfiles/kml/pal2/icon63.png' );
$restIconNode->appendChild( $restHref );
$restIconstyleNode->appendChild( $restIconNode );
$restStyleNode->appendChild( $restIconstyleNode );
$docNode->appendChild( $restStyleNode );

$barStyleNode = $dom->createElement( 'Style' );
$barStyleNode->setAttribute( 'id', 'barStyle' );
$barIconstyleNode = $dom->createElement( 'IconStyle' );
$barIconstyleNode->setAttribute( 'id', 'barIcon' );
$barIconNode = $dom->createElement( 'Icon' );
$barHref = $dom->createElement( 'href', 'http://maps.google.com/mapfiles/kml/pal2/icon27.png' );
$barIconNode->appendChild( $barHref );
$barIconstyleNode->appendChild( $barIconNode );
$barStyleNode->appendChild( $barIconstyleNode );
$docNode->appendChild( $barStyleNode );


// Iterates through the MySQL results, creating one Placemark for each row.
foreach ($places as $row)
{
  // Creates a Placemark and append it to the Document.

    $node = $dom->createElement( 'Placemark' );
    $placeNode = $docNode->appendChild( $node );

  // Creates an id attribute and assign it the value of id column.
    $placeNode->setAttribute( 'id', 'placemark' . $row['id'] );

  // Create name, and description elements and assigns them the values of the name and address columns from the results.
    $nameNode = $dom->createElement( 'name', htmlentities( $row['name'] ) );
    $placeNode->appendChild( $nameNode );
    $descNode = $dom->createElement( 'description', $row['address'] );
    $placeNode->appendChild( $descNode );
    $styleUrl = $dom->createElement( 'styleUrl', '#' . $row['type'] . 'Style' );
    $placeNode->appendChild( $styleUrl );

  // Creates a Point element.
    $pointNode = $dom->createElement( 'Point' );
    $placeNode->appendChild( $pointNode );

  // Creates a coordinates element and gives it the value of the lng and lat columns from the results.
    $coorStr = $row['lng'] . ','  . $row['lat'];
    $coorNode = $dom->createElement( 'coordinates', $coorStr );
    $pointNode->appendChild( $coorNode );
}

/**
 * Output KML
 */
$kmlOutput = $dom->saveXML();
header( 'Content-type: application/vnd.google-earth.kml+xml' );
echo $kmlOutput;
//dt_write_log($kmlOutput);



