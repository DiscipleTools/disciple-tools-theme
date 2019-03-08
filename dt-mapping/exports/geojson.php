<?php
// @codingStandardsIgnoreStart
require( $_SERVER[ 'DOCUMENT_ROOT' ] . '/wp-load.php' ); // loads the wp framework when called
global $wpdb;

header( 'Content-type:application/json;charset=utf-8' ); // add json header to response
header( 'Access-Control-Allow-Origin: *' );
header( 'Access-Control-Allow-Methods: GET, POST, HEAD, OPTIONS' );
header( 'Access-Control-Allow-Credentials: true' );
header( 'Access-Control-Expose-Headers: Link', false );

if ( ! isset( $_GET['map'] ) || ! isset( $_GET['value'] ) ) {
    error_log( 'Missing requirements.' );
    empty_json();
    exit;
}

$value = $_GET['value'];
$map_type = $_GET['map'];

switch ( $map_type ) {

    case 'state':
        $geojson = $wpdb->get_results( $wpdb->prepare(
            "SELECT g.geonameid as geonameid, asciiname as name, g.population, geoJSON 
                    FROM dt_geonames as g 
                    JOIN dt_geonames_polygons as gp 
                      ON g.geonameid=gp.geonamesid 
                    WHERE country_code = %s 
                      AND admin1_code = %s
                      AND feature_code = 'ADM2' 
                ",
            $value['country_code'],
            $value['admin1_code'] )
            , ARRAY_A );
        if ( empty( $geojson ) ) {
            dt_write_log( 'geojson.php: No geojson found for ' . $value );
            empty_json();
            exit;
        }

        ?>{"type": "FeatureCollection","features": [<?php
        $i = 0;
        $html = '';
        foreach ( $geojson as $geometry ) {
            if ( 0 != $i ) {
                $html .= ',';
            }
            $html .= '{"type": "Feature","geometry": ';
            $html .= $geometry['geoJSON'];

            $html .= ',"properties":{';
            $html .= '"name":' . json_encode( $geometry['name'] ) . ',';
            $html .= '"geonameid":' . $geometry['geonameid'] . ',';
            $html .= '"id":' . $geometry['geonameid'] ;
            $html .= '}';

            $html .= '}';
            $i++;
        }
        echo $html . ']}';?>
        <?php
        /* working : https://dashboard.mu-zume/wp-content/plugins/disciple-tools-network-dashboard/exports/geojson.php?map=state&value[country_code]=US&value[admin1_code]=CO */
        break;


    case 'country':
        $geojson = $wpdb->get_results( $wpdb->prepare( "
            SELECT g.geonameid as geonameid, geoJSON, population, asciiname as name
            FROM dt_geonames as g JOIN dt_geonames_polygons as gp ON g.geonameid=gp.geonameid 
            WHERE country_code = %s 
              AND feature_code = 'ADM1'", $value ), ARRAY_A );
        if ( empty( $geojson ) ) {
            dt_write_log( 'geojson.php: No geojson found for this page id' );
            empty_json();
            exit;
        }

        ?>{"type": "FeatureCollection","features": [<?php
        $i = 0;
        $html = '';
        foreach ( $geojson as $geometry ) {
            if ( 0 != $i ) {
                $html .= ',';
            }

            $html .= '{"type": "Feature","geometry": ';
            $html .= $geometry['geoJSON'];

            $html .= ',"properties":{';
            $html .= '"name":' . json_encode( $geometry['name'] ) . ',';
            $html .= '"id":' . $geometry['geonameid'] . ',';
            $html .= '"geonameid":' . $geometry['geonameid'];
            $html .= '}';

            $html .= '}';
            $i++;
        }
        echo $html . ']}';
        /* working : /wp-content/plugins/disciple-tools-network-dashboard/exports/geojson.php?map=country&value=US */
        break;

    case 'children_by_geonameid':
        $value = (int) $value;
        $geojson = $wpdb->get_results( $wpdb->prepare( "
            SELECT 
             dt_geonames_hierarchy.*,
             dt_geonames.*,
             pl.geoJSON
             FROM dt_geonames_hierarchy 
             JOIN dt_geonames ON dt_geonames_hierarchy.id=dt_geonames.geonameid
             LEFT JOIN dt_geonames_polygons_low as pl ON dt_geonames_hierarchy.id=pl.geonameid
             WHERE parent_id = %d", $value ), ARRAY_A );
        if ( empty( $geojson ) ) {
            dt_write_log( 'geojson.php: No geojson found for this page id' );
            empty_json();
            exit;
        }

        $html = '{"type":"FeatureCollection","features":[';
        $i = 0;
        foreach ( $geojson as $geometry ) {
            if ( 0 != $i ) {
                $html .= ',';
            }

            $html .= '{"type": "Feature","geometry": ';
            $html .= $geometry['geoJSON'];

            $html .= ',"properties":{';
            $html .= '"name":' . json_encode(  $geometry['name'] ) . ',';
            $html .= '"id":' . $geometry['geonameid'] . ',';
            $html .= '"geonameid":' . $geometry['geonameid'];
            $html .= '}';

            $html .= '}';
            $i++;

        }
        $html .= ']}';

        echo $html;


//        $file = $value . '.geojson';
//        $content = $html;
//        file_put_contents( $file, $content.PHP_EOL, FILE_APPEND | LOCK_EX );

        /* working : /wp-content/plugins/disciple-tools-network-dashboard/exports/geojson.php?map=children_by_geonameid&value=6252001 */
        break;

    case 'single':

        $geojson = $wpdb->get_var( $wpdb->prepare( "SELECT geoJSON FROM dt_geonames_polygons WHERE geonameid = %d", $value ) );
        if ( empty( $geojson ) ) {
            dt_write_log( 'geojson.php: No geojson found for this page id' );
            empty_json();
            exit;
        }
        ?>{"type": "FeatureCollection","features": [{"type": "Feature","geometry": <?php print $geojson; ?>}]}<?php
        /* Working single: https://dashboard.mu-zume/wp-content/plugins/disciple-tools-network-dashboard/ui/map.php?map=single&value=5411363 */
        break;

    case 'cities':

        $points = $wpdb->get_results( "SELECT * FROM dt_geonames WHERE feature_code LIKE 'PP%' AND feature_class = 'P' AND population > 100000", ARRAY_A );
        if ( empty( $points ) ) {
            dt_write_log( 'geojson.php: No geojson found for this page id' );
            empty_json();
            exit;
        }
        echo '{"type":"FeatureCollection","features":[';
        $i = 0;
        foreach( $points as $point ) {
            if ( $i > 0 ) {
                echo ',';
            }
            echo '{"type":"Feature","geometry":{"type":"Point","coordinates":['.$point['longitude'].','.$point['latitude'].']},"properties":{"name":"'.$point['name'].'","geonameid":"'.$point['geonameid'].'","population":"'.$point['population'].'"}}';
            $i++;
        }
        echo ']}';

//        echo $geojson;
        /* Working single: https://simpledashboard.disciple.tools.site/wp-content/plugins/disciple-tools-network-dashboard/exports/geojson.php?map=cities&value=nothing */
        break;



    case 'world':
        $geojson = $wpdb->get_results( "SELECT geoJSON FROM dt_geonames_polygons_low", ARRAY_A );
        dt_write_log( $geojson );
        if ( empty( $geojson ) ) {
            dt_write_log( 'geojson.php: No geojson found for this page id' );
            empty_json();
            exit;
        }

        $i = 0;
        $html = '{"type": "FeatureCollection","features": [';
        foreach ( $geojson as $geometry ) {
            if ( 0 != $i ) {
                $html .= ',';
            }
            $html .= '{"type": "Feature","geometry": ';
            $html .= $geometry['geoJSON'];
            $html .= '}';
            $i++;
        }
        $html .= ']}';
        echo $html;
        break;

    default:
        break;
}

function empty_json() {
    ?>{"type": "FeatureCollection","features": [{"type": "Feature","geometry": []}]}<?php
}