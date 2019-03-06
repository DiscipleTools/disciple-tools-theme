<?php
/**
 * Geocoder for coding Disciple Tools coded location to Geonamed locations
 */

function dt_geocode() {
    return new DT_Geocoder();
}

class DT_Geocoder
{
    public function __construct() {
    }

    public function add( $type ) {

        switch ( $type ) {
            case 'country':

                break;
            default:
                break;
        }
    }

    public function parse_location( $foreign_key ) {
        dt_write_log( __METHOD__ );
        global $wpdb;
        $location = dt_network_dashboard_queries( 'location_by_foreign_key', [ "foreign_key" => $foreign_key ] );
        dt_write_log( $location );

        // check if lat/lng exists
        if ( empty( $location['latitude'] ) || empty( $location['longitude'] ) ) {

        }

        if ( ! empty( $location['country_short_name'] ) ) {

        }

        // check for state match
        if ( ! empty( $location['admin1_short_name'] ) ) {


        }


        // build by type
        if ( ! empty( $location['types'] ) ) {
            switch ( $location['types'] ) {
                case 'country':

                    break;
                case 'administrative_area_level_1':

                    break;
                case 'administrative_area_level_2':

                    break;
                case 'locality':

                    break;
                default:
                    break;
            }
        }

        // check for country match







        // check for county match

        // geocode lat/lng for additional google data

        // check geonames api for geocoding.

        return true;
    }

    public function queries( $type, $args ) {
        global $wpdb;
        $results = [];
        switch ( $type ) {
            case 'all_gn_countries':
                //SELECT g.geonameid, g.name, g.country_code, g.admin1_code, g.admin2_code, g.admin3_code, g.population, p.geoJSON from dt_geonames as g LEFT JOIN dt_geonames_polygons_low as p ON g.geonameid=p.geonameid WHERE g.feature_class = 'A' AND g.feature_code = 'PCLI'
                $results = $wpdb->get_results(
                    "SELECT 
                                g.geonameid, 
                                g.name, 
                                g.country_code, 
                                g.population, 
                                p.geoJSON 
                            FROM dt_geonames as g 
                            LEFT JOIN dt_geonames_polygons_low as p 
                              ON g.geonameid=p.geonameid 
                            WHERE g.feature_class = 'A' 
                              AND g.feature_code = 'PCLI'",
                ARRAY_A );
                break;
            default:
                break;
        }
        return $results;
    }
}

/*
SELECT g.geonameid, g.name, g.country_code, g.admin1_code, g.admin2_code, g.admin3_code, g.population, p.geoJSON from dt_geonames as g LEFT JOIN dt_geonames_polygons_low as p ON g.geonameid=p.geonameid WHERE g.feature_class = 'A' AND g.feature_code = 'PCLI'

SELECT
( SELECT count(*) from dt_geonames_hierarchy ) as hierarchy,
( SELECT count(*) from dt_geonames WHERE feature_class = 'A' AND ( feature_code = 'PCLI' OR feature_code LIKE 'ADM%' ) ) as gn_admin_codes,
( SELECT count(*) FROM dt_geonames WHERE feature_class = 'A' AND feature_code = 'PCLI' ) as countries,
( SELECT count(*) FROM dt_geonames WHERE feature_class = 'A' AND feature_code = 'ADM1' ) as admin1,
( SELECT count(*) FROM dt_geonames WHERE feature_class = 'A' AND feature_code = 'ADM2' ) as admin2,
( SELECT count(*) FROM dt_geonames WHERE feature_class = 'A' AND feature_code = 'ADM3' ) as admin3,
( SELECT count(*) FROM dt_geonames WHERE feature_class = 'A' AND feature_code = 'ADM4' ) as admin4,
( SELECT count(*) FROM dt_geonames WHERE feature_class = 'A' AND feature_code = 'ADM5' ) as admin5






































 */