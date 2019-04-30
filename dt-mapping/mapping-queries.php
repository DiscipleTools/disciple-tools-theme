<?php

class Disciple_Tools_Mapping_Queries {

    public static function get_by_geonameid( int $geonameid ) {
        global $wpdb;

        $results = $wpdb->get_row( $wpdb->prepare( "
            SELECT
              g.geonameid as id, 
              g.geonameid, 
              g.alt_name as name, 
              IF(g.alt_population > 0, g.alt_population, g.population) as population, 
              g.latitude, 
              g.longitude,
              g.country_code,
              g.parent_id,
              g.country_geonameid,
              g.admin1_geonameid,
              g.admin2_geonameid,
              g.admin3_geonameid,
              g.level,
              g.is_custom_location
            FROM $wpdb->dt_geonames as g
            WHERE g.geonameid = %s
        ", $geonameid ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_parent_by_geonameid( int $geonameid ) {
        global $wpdb;

        $results = $wpdb->get_row( $wpdb->prepare( "
            SELECT 
              p.geonameid as id, 
              p.geonameid, 
              p.alt_name as name, 
              IF(g.alt_population > 0, g.alt_population, g.population) as population, 
              p.latitude, 
              p.longitude,
              p.country_code,
              p.parent_id,
              p.country_geonameid,
              p.admin1_geonameid,
              p.admin2_geonameid,
              p.admin3_geonameid,
              p.level
            FROM $wpdb->dt_geonames as g
            JOIN $wpdb->dt_geonames as p ON g.parent_id=p.geonameid
            WHERE g.geonameid = %s
        ", $geonameid ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_children_by_geonameid( int $geonameid ) {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
              g.geonameid as id, 
              g.geonameid, 
              g.alt_name as name, 
              IF(g.alt_population > 0, g.alt_population, g.population) as population, 
              g.latitude, 
              g.longitude,
              g.country_code,
              g.parent_id,
              g.country_geonameid,
              g.admin1_geonameid,
              g.admin2_geonameid,
              g.admin3_geonameid,
              g.level,
              g.is_custom_location
            FROM $wpdb->dt_geonames as g
            WHERE g.parent_id = %d
            ORDER BY g.alt_name ASC
        ", $geonameid ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_by_geonameid_list( array $list ) {
        global $wpdb;

        $prepared_list = '';
        $i = 0;
        foreach ( $list as $item ) {
            if ( $i !== 0 ) {
                $prepared_list .= ',';
            }
            $prepared_list .= (int) $item;
            $i++;
        }
        // Note: $wpdb->prepare does not have a way to add a string without surrounding it with ''
        // and this query requires a list of numbers separated by commas but without surrounding ''
        // Any better ideas on how to still use ->prepare and not break the sql, welcome. :)
        // @codingStandardsIgnoreStart
        $results = $wpdb->get_results("
            SELECT
              g.geonameid as id, 
              g.geonameid, 
              g.alt_name as name, 
              IF(g.alt_population > 0, g.alt_population, g.population) as population,
              g.latitude, 
              g.longitude,
              g.country_code,
              g.feature_code,
              g.parent_id,
              g.country_geonameid,
              g.admin1_geonameid,
              g.admin2_geonameid,
              g.admin3_geonameid,
              g.level
            FROM $wpdb->dt_geonames as g
            WHERE g.geonameid IN ($prepared_list)
            ORDER BY g.alt_name ASC
        ", ARRAY_A );
        // @codingStandardsIgnoreEnd

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_countries( $ids_only = false ) {
        global $wpdb;

        /**
         * Returns full list of countries, territories, and other political geographic entities.
         * PCLI    independent political entity
         * PCLD: dependent political entities (guam, american samoa, etc.)
         * PCLF: freely associated state (micronesia, federated states of)
         * PCLH: historical political entity, a former political entity (Netherlands Antilles)
         * PCLIX: section of independent political entity
         * PCLS: semi-independent political entity
         * TERR: territory
         */
        if ( $ids_only ) {
            $results = $wpdb->get_col( "
                 SELECT g.geonameid
                 FROM $wpdb->dt_geonames as g
                 WHERE g.level = 'country'
                 ORDER BY name ASC
        " );
        } else {
            $results = $wpdb->get_results( "
                 SELECT
                        g.geonameid,
                        g.alt_name as name,
                        g.latitude,
                        g.longitude,
                        g.feature_class,
                        g.feature_code,
                        g.country_code,
                        g.cc2,
                        g.admin1_code,
                        g.admin2_code,
                        g.admin3_code,
                        g.admin4_code,
                        IF(g.alt_population > 0, g.alt_population, g.population) as population,
                        g.timezone,
                        g.modification_date,
                        g.parent_id,
                        g.country_geonameid,
                        g.admin1_geonameid,
                        g.admin2_geonameid,
                        g.admin3_geonameid,
                        g.level
                 FROM $wpdb->dt_geonames as g
                 WHERE g.level = 'country'
                 ORDER BY name ASC
            ", ARRAY_A );
        }

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_country_code_by_id( int $geonameid ) {
        global $wpdb;

        $results = $wpdb->get_var( $wpdb->prepare( "
            SELECT country_code 
            FROM $wpdb->dt_geonames 
            WHERE geonameid = %s;
        ", $geonameid ) );

        if ( empty( $results ) ) {
            $results = 0;
        }

        return $results;
    }

    public static function get_hierarchy( $geonameid = null ) {
        global $wpdb;

        if ( $geonameid ) {
            $results = $wpdb->get_row( $wpdb->prepare( "
                SELECT
                g.parent_id,
                g.geonameid,
                g.country_geonameid,
                g.admin1_geonameid,
                g.admin2_geonameid,
                g.admin3_geonameid,
                g.level
                FROM $wpdb->dt_geonames as g
                WHERE g.geonameid = %d;
            ", $geonameid ), ARRAY_A );
        } else {
            $results = $wpdb->get_results("
                SELECT 
                g.parent_id,
                g.geonameid,
                g.country_geonameid,
                g.admin1_geonameid,
                g.admin2_geonameid,
                g.admin3_geonameid,
                g.level
                FROM $wpdb->dt_geonames as g", ARRAY_A );
        }

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_counter( $args ) {
        global $wpdb;

        if ( isset( $args['post_id'] ) ) {
            $results = $wpdb->get_row( $wpdb->prepare( "
                SELECT * 
                FROM $wpdb->dt_geonames_counter 
                WHERE post_id = %s;
            ", $args['post_id'] ), ARRAY_A );
        }
        else if ( isset( $args['geonameid'] ) ) {
            $results = $wpdb->get_row( $wpdb->prepare( "
                SELECT * 
                FROM $wpdb->dt_geonames_counter 
                WHERE geonameid = %d;
            ", $args['geonameid'] ), ARRAY_A );
        }

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_drilldown_by_geonameid( int $geonameid ) {
        global $wpdb;

        $results = $wpdb->get_row( $wpdb->prepare( "
            SELECT
              g.geonameid as id, 
              g.geonameid, 
              g.alt_name as name, 
              IF(g.alt_population > 0, g.alt_population, g.population) as population, 
              g.latitude, 
              g.longitude,
              g.country_code,
              g.parent_id,
              g.country_geonameid,
              gc.alt_name as country_name,
              g.admin1_geonameid,
              ga1.alt_name as admin1_name,
              g.admin2_geonameid,
              ga2.alt_name as admin2_name,
              g.admin3_geonameid,
              ga3.alt_name as admin3_name,
              g.level,
              g.is_custom_location
            FROM $wpdb->dt_geonames as g
            LEFT JOIN $wpdb->dt_geonames as gc ON g.country_geonameid=gc.geonameid
            LEFT JOIN $wpdb->dt_geonames as ga1 ON g.admin1_geonameid=ga1.geonameid
            LEFT JOIN $wpdb->dt_geonames as ga2 ON g.admin2_geonameid=ga2.geonameid
            LEFT JOIN $wpdb->dt_geonames as ga3 ON g.admin3_geonameid=ga3.geonameid
            WHERE g.geonameid = %s
        ", $geonameid ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_regions() {
        global $wpdb;

        /**
         * Lists all countries with their region_name and region_id
         * @note There are often two regions that claim the same country.
         */
        $results = $wpdb->get_results("
            SELECT
                g.geonameid,
                g.alt_name as name,
                g.latitude,
                g.longitude,
                g.feature_class,
                g.feature_code,
                g.country_code,
                g.cc2,
                g.admin1_code,
                g.admin2_code,
                g.admin3_code,
                g.admin4_code,
                IF(g.alt_population > 0, g.alt_population, g.population) as population,
                g.timezone,
                g.modification_date,
                g.parent_id,
                g.country_geonameid,
                g.admin1_geonameid,
                g.admin2_geonameid,
                g.admin3_geonameid,
                g.level
            FROM $wpdb->dt_geonames as g
            WHERE feature_code = 'RGN' 
            AND country_code = '';
        ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_continents() {
        global $wpdb;

        $results = $wpdb->get_results("
            SELECT
                g.geonameid,
                g.alt_name as name,
                g.latitude,
                g.longitude,
                g.feature_class,
                g.feature_code,
                g.country_code,
                g.cc2,
                g.admin1_code,
                g.admin2_code,
                g.admin3_code,
                g.admin4_code,
                IF(g.alt_population > 0, g.alt_population, g.population) as population,
                g.timezone,
                g.modification_date,
                g.parent_id,
                g.country_geonameid,
                g.admin1_geonameid,
                g.admin2_geonameid,
                g.admin3_geonameid,
                g.level
            FROM $wpdb->dt_geonames as g
            WHERE g.geonameid IN (6255146,6255147,6255148,6255149,6255151,6255150,6255152)
            ORDER BY name ASC;
        ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_earth() {
        global $wpdb;

        $results = $wpdb->get_row("
            SELECT
                g.geonameid,
                ('world') as id,
                g.alt_name as name,
                g.latitude,
                g.longitude,
                g.feature_class,
                g.feature_code,
                g.country_code,
                g.admin1_code,
                g.admin2_code,
                g.admin3_code,
                g.admin4_code,
                IF(g.alt_population > 0, g.alt_population, g.population) as population,
                g.parent_id,
                g.country_geonameid,
                g.admin1_geonameid,
                g.admin2_geonameid,
                g.admin3_geonameid,
                ('world') as level
            FROM $wpdb->dt_geonames as g
            WHERE g.geonameid = 6295630
        ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function count_geonames() {
        global $wpdb;

        $results = $wpdb->get_var("
            SELECT count(*)
            FROM $wpdb->dt_geonames 
        ");

        if ( empty( $results ) ) {
            $results = 0;
        }

        return $results;
    }

    public static function get_geoname_totals( $country_only = false ) : array {
        global $wpdb;

        if ( $country_only ) {
            $results = $wpdb->get_results("
                SELECT
                  country_geonameid as geonameid,
                  type,
                  count(country_geonameid) as count
                FROM $wpdb->dt_geonames_counter
                WHERE country_geonameid != ''
                GROUP BY country_geonameid, type
            ", ARRAY_A );
        } else {
            $results = $wpdb->get_results("
                SELECT
                  country_geonameid as geonameid,
                  type,
                  count(country_geonameid) as count
                FROM $wpdb->dt_geonames_counter
                WHERE country_geonameid != ''
                GROUP BY country_geonameid, type
                UNION
                SELECT
                  admin1_geonameid as geonameid,
                  type,
                  count(admin1_geonameid) as count
                FROM $wpdb->dt_geonames_counter
                WHERE admin1_geonameid != ''
                GROUP BY admin1_geonameid, type
                UNION
                SELECT
                  admin2_geonameid as geonameid,
                  type,
                  count(admin2_geonameid) as count
                FROM $wpdb->dt_geonames_counter
                WHERE admin2_geonameid != ''
                GROUP BY admin2_geonameid, type
                UNION
                SELECT
                  admin3_geonameid as geonameid,
                  type,
                  count(admin3_geonameid) as count
                FROM $wpdb->dt_geonames_counter
                WHERE admin3_geonameid != ''
                GROUP BY admin3_geonameid, type
            ", ARRAY_A );
        }

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function search_geonames_by_name( $args ) {
        global $wpdb;

        $search_query = $wpdb->esc_like( $args['search_query'] ?? "" );
        $focus_search_sql = "";
        if ( isset( $args['filter'] ) && $args["filter"] == "focus" ){
            $default_map_settings = DT_Mapping_Module::instance()->default_map_settings();
            if ( $default_map_settings["type"] === "country" && sizeof( $default_map_settings["children"] ) > 0 ){
                $joined_geoname_ids = dt_array_to_sql( $default_map_settings["children"] );
                $focus_search_sql = "AND g.country_geonameid IN ( $joined_geoname_ids ) ";
            }
        }
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $geonames = $wpdb->get_results( $wpdb->prepare( "
            SELECT SQL_CALC_FOUND_ROWS
            DISTINCT( g.geonameid ),
            CASE 
                WHEN g.level = 'country' 
                  THEN g.alt_name
                WHEN g.level = 'admin1' 
                  THEN CONCAT( (SELECT country.alt_name FROM $wpdb->dt_geonames as country WHERE country.geonameid = g.country_geonameid LIMIT 1), ' > ', 
                g.alt_name ) 
                WHEN g.level = 'admin2' OR g.level = 'admin3'
                  THEN CONCAT( (SELECT country.alt_name FROM $wpdb->dt_geonames as country WHERE country.geonameid = g.country_geonameid LIMIT 1), ' > ', 
                (SELECT a1.alt_name FROM $wpdb->dt_geonames AS a1 WHERE a1.geonameid = g.admin1_geonameid LIMIT 1), ' > ', 
                g.alt_name )
                ELSE g.alt_name
            END as label
            FROM $wpdb->dt_geonames as g
            WHERE g.alt_name LIKE %s
            $focus_search_sql
            ORDER BY g.country_code, CHAR_LENGTH(label)
            LIMIT 30;
            ", '%' . $search_query . '%' ),
            ARRAY_A
        );
        // phpcs:enable

        $total_rows = $wpdb->get_var( "SELECT found_rows();" );
        return [
            'geonames' => $geonames,
            'total' => $total_rows
        ];
    }

    public static function search_used_geonames_by_name( $args ) {
        global $wpdb;

        $search_query = $wpdb->esc_like( $args['search_query'] ?? "" );

        $geonames = $wpdb->get_results( $wpdb->prepare( "
            SELECT SQL_CALC_FOUND_ROWS
            DISTINCT( g.geonameid ),
            CASE 
                WHEN g.level = 'country' 
                  THEN g.alt_name
                WHEN g.level = 'admin1' 
                  THEN CONCAT( (SELECT country.alt_name FROM $wpdb->dt_geonames as country WHERE country.geonameid = g.country_geonameid LIMIT 1), ' > ', 
                g.alt_name ) 
                WHEN g.level = 'admin2' OR g.level = 'admin3'
                  THEN CONCAT( (SELECT country.alt_name FROM $wpdb->dt_geonames as country WHERE country.geonameid = g.country_geonameid LIMIT 1), ' > ', 
                (SELECT a1.alt_name FROM $wpdb->dt_geonames AS a1 WHERE a1.geonameid = g.admin1_geonameid LIMIT 1), ' > ', 
                g.alt_name )
                ELSE g.alt_name
            END as label
            FROM $wpdb->dt_geonames as g
            INNER JOIN $wpdb->dt_geonames_counter as counter ON (g.geonameid = counter.geonameid)
            WHERE g.alt_name LIKE %s
            
            ORDER BY g.country_code, CHAR_LENGTH(label)
            LIMIT 30;
            ", '%' . $search_query . '%' ),
            ARRAY_A
        );
        $total_rows = $wpdb->get_var( "SELECT found_rows();" );
        return [
            'geonames' => $geonames,
            'total' => $total_rows
        ];
    }

    public static function get_names_from_ids( $args ) {
        global $wpdb;

        $ids = dt_array_to_sql( $args['geoname_ids'] );
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $results = $wpdb->get_results("
                            SELECT geonameid, name 
                            FROM $wpdb->dt_geonames
                            WHERE geonameid IN ( $ids ) 
                        ", ARRAY_A );
        // phpcs:enable
        $prepared = [];
        foreach ( $results as $row ){
            $prepared[$row["geonameid"]] = $row["name"];
        }
        return $prepared;
    }

    public static function get_geoname_ids_and_names_for_post_ids( $args ) {
        global $wpdb;

        $prepared = [];
        foreach ( $args['post_ids'] as $post_id ) {
            $prepared[$post_id] = [];
        }
        $joined_post_ids = dt_array_to_sql( $args['post_ids'] );
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $geonames = $wpdb->get_results("
                            SELECT post_id, meta_value
                            FROM $wpdb->postmeta pm
                            WHERE meta_key = 'geonames'
                            AND post_id IN ( $joined_post_ids )
                        ", ARRAY_A );
        if ( empty( $geonames ) ){
            return $prepared;
        }
        $geoname_ids = array_map( function( $g ){ return $g["meta_value"]; }, $geonames );
        $joined_geoname_ids = dt_array_to_sql( $geoname_ids );
        $geoname_id_names = $wpdb->get_results("
                            SELECT geonameid, name 
                            FROM $wpdb->dt_geonames
                            WHERE geonameid IN ( $joined_geoname_ids ) 
                        ", ARRAY_A );
        // phpcs:enable
        $mapped_geoname_id_to_name = [];
        foreach ( $geoname_id_names as $geoname ){
            $mapped_geoname_id_to_name[$geoname["geonameid"]] = $geoname["name"];
        }
        foreach ( $geonames as $geoname ){
            if ( isset( $mapped_geoname_id_to_name[$geoname["meta_value"]] ) ){
                $prepared[$geoname["post_id"]][] = [
                    "geoname_id" => $geoname["meta_value"],
                    "name" => $mapped_geoname_id_to_name[$geoname["meta_value"]]
                ];
            }
        }
        return $prepared;
    }

}