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

//$json = file_get_contents("http://dbpedia.org/data/Jakharrad.json");
//$obj = json_decode($json);
//dt_write_log("SPECIFIC");
//dt_write_log($obj->{"http://dbpedia.org/resource/Jakharrad"}->{"http://www.georss.org/georss/point"}[0]->value);
//dt_write_log($obj->{"http://dbpedia.org/resource/Jakharrad"}->{"http://www.georss.org/georss/point"}[0]->value);
//dt_write_log($obj->{"http://dbpedia.org/resource/Jakharrad"}->{"http://dbpedia.org/ontology/populationTotal"}[0]->value);
//dt_write_log("FULL");
//dt_write_log($obj->{"http://dbpedia.org/resource/Jakharrad,_Libya"});

$endpointUrl = 'https://query.wikidata.org/sparql';
$sparqlQuery = <<< 'SPARQL'
SELECT * WHERE {
  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
  ?instance_of wdt:P1566 "5414941".
  OPTIONAL { ?instance_of wdt:P1082 ?population. }
}
LIMIT 100
SPARQL;

$result = file_get_contents( $endpointUrl . '?query=' . urlencode( $sparqlQuery ) );
dt_write_log( $result );

$p = xml_parser_create();
xml_parse_into_struct( $p, $result, $vals, $index );
xml_parser_free( $p );
dt_write_log( $vals );
echo $result;