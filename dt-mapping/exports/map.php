<?php
session_start();
// @codingStandardsIgnoreStart
require( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' ); // loads the wp framework when called
?>

<!DOCTYPE html>
<html>
<head>
    <title>Map</title>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <style>
        /* Always set the map height explicitly to define the size of the div
         * element that contains the map. */
        #map {
            height: 100%;
        }
        /* Optional: Makes the sample page fill the window. */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0 0 15px;
        }
        #info-box {
            background-color: white;
            border: 1px solid black;
            bottom: 30px;
            height: 20px;
            padding: 10px;
            position: absolute;
            left: 30px;
            font-size:1.5em;
        }
    </style>
</head>
<body>

<?php
$countries = json_decode( file_get_contents('../countries.json'), true );
asort( $countries );
$current_value = 0;
if ( isset( $_GET['value'] ) ) {
    $current_value = sanitize_text_field( $_GET['value'] );
}
?>

<!--<form method="get">
    <input name="map" type="hidden" value="single">
    <input name="value" value="" placeholder="geonameid (5417618)">
    <button type="submit">Single</button> <a style='font-size:.8em; float:right;' href="<?php /*$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2); echo 'https://' . $_SERVER['HTTP_HOST'] . $uri_parts[0]; */?>">refresh</a>

</form>-->
<form method="get">
    <input name="map" type="hidden" value="state">
    <select name="value[country_code]">
        <option>Select Country</option>
        <?php
        foreach($countries as $key => $value  ) {
            if ( $key === $current_value ) {
                echo '<option value="'.esc_attr( $key ).'" selected>'. esc_html( $value ).'</option>';
            } else {
                echo '<option value="'.esc_attr( $key ).'">'. esc_html( $value ).'</option>';
            }
        }
        ?>
    </select>
    <input name="value[admin1_code]" type="text" value="" placeholder="admin1 code">
    <button type="submit">Counties of State</button>
</form>
<form method="get">
    <input name="map" type="hidden" value="country">
    <select name="value">
        <option>Select Country</option>
        <?php
        foreach($countries as $key => $value  ) {
            if ( $key === $current_value ) {
                echo '<option value="'.esc_attr( $key ).'" selected>'. esc_html( $value ).'</option>';
            } else {
                echo '<option value="'.esc_attr( $key ).'">'. esc_html( $value ).'</option>';
            }
        }
        ?>
    </select>
    <button type="submit">States of Country</button>
</form>
<form method="get">
    <input name="map" type="hidden" value="country_low">
    <select name="value">
        <option>Select Country</option>
        <?php
        foreach($countries as $key => $value  ) {
            if ( $key === $current_value ) {
                echo '<option value="'.esc_attr( $key ).'" selected>'. esc_html( $value ).'</option>';
            } else {
                echo '<option value="'.esc_attr( $key ).'">'. esc_html( $value ).'</option>';
            }
        }
        ?>
    </select>
    <button type="submit">States of Country (low)</button>
</form>
<?php
if ( ! ( isset( $_GET['map'] ) && ! empty( $_GET['map'] ) ) || ! ( isset( $_GET['value'] ) && ! empty( $_GET['value'] ) ) ) {
    ?></body></html><?php
    return;
}

$value = $_GET['value'];
$map_type = $_GET['map'];
$_SESSION['map'] = $map_type;
$_SESSION['value'] = $value;

switch ( $map_type ) {
    case 'single':
        $center = $wpdb->get_row($wpdb->prepare( "SELECT latitude as lat, longitude as lng FROM dt_geonames WHERE geonameid = %s", $value ), ARRAY_A );
        $zoom = 7;
        break;
    case 'state':
        $center = $wpdb->get_row($wpdb->prepare( "SELECT latitude as lat, longitude as lng FROM dt_geonames WHERE country_code = %s and admin1_code = %s LIMIT 1", $value['country_code'], $value['admin1_code']), ARRAY_A );
        $zoom = 7;
        break;
    case 'country':
        $center = $wpdb->get_row($wpdb->prepare( "SELECT latitude as lat, longitude as lng FROM dt_geonames WHERE country_code = %s and feature_code = 'PCLI' LIMIT 1", $value ), ARRAY_A );
        $zoom = 4;
        break;
    case 'country_low':
        $center = $wpdb->get_row($wpdb->prepare( "SELECT latitude as lat, longitude as lng FROM dt_geonames WHERE country_code = %s and feature_code = 'PCLI' LIMIT 1", $value ), ARRAY_A );
        $zoom = 4;
        break;
    default:
        break;
}



?>

<div id="map"></div>
<div id="info-box"></div>


<script>
    var map;

    if ( ! <?php echo (float) $center['lat'] ?> ) {
        console.log('No query for center found.' )
    }

    var CENTER = {lat: <?php echo (float) $center['lat'] ?>, lng: <?php echo (float) $center['lng'] ?>};
    var ZOOM = <?php echo (int) $zoom ?>;


    function initMap() {
        // Create a map in the usual way.
        // var myStyle = [
        //     {
        //         featureType: "poi",
        //         elementType: "labels",
        //         stylers: [
        //             { visibility: "off" }
        //         ]
        //     },{
        //         featureType: "water",
        //         elementType: "labels",
        //         stylers: [
        //             { visibility: "off" }
        //         ]
        //     },{
        //         featureType: "road",
        //         elementType: "labels",
        //         stylers: [
        //             { visibility: "off" }
        //         ]
        //     }
        // ];
        let mapOptions = {
            center: CENTER,
            zoom: ZOOM
            // mapTypeControlOptions: {
            //     mapTypeIds: ['mystyle', google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.TERRAIN]
            // },
            // mapTypeId: 'mystyle'
        };
        map = new google.maps.Map(
            document.getElementById('map'), mapOptions);

        map.data.loadGeoJson( '<?php echo site_url() ?>/wp-content/plugins/disciple-tools-network-dashboard/exports/geojson.php?map=<?php echo $map_type; ?><?php
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $item ) {
                echo '&value['.$key.']=' . $item;
            }
        } else {
            echo '&value=' . $value;
        }
        ?>');

        // map.mapTypes.set('mystyle', new google.maps.StyledMapType(myStyle, { name: 'Simple' }));

        map.data.setStyle(function(feature) {
            var population = feature.getProperty('churches');
            var color = population > 50 ? 'red' : 'blue';
            return {
                fillColor: color,
                strokeWeight: 1
            };
        });

        map.data.addListener('mouseover', function(event) {
            let needed = event.feature.getProperty('population') / 5000
            if ( needed === 0 ) {
                needed = 1
            }

            document.getElementById('info-box').textContent =
                event.feature.getProperty('name') + " | " +
                "population: " + event.feature.getProperty('population').toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + " | " +
                "churches needed: " + parseInt(needed).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' | ' +
                "churches: " + event.feature.getProperty('churches');
        });


    }
</script>
<!-- Working single: https://dashboard.mu-zume/wp-content/plugins/disciple-tools-network-dashboard/ui/map.php?map=single&value=5411363 -->
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo dt_get_option( 'map_key' ) ?>&callback=initMap">
</script>
</body>
</html>

