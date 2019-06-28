<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Mapping_Queries {

    public static function get_by_geonameid( $geonameid ) {

        if ( wp_cache_get( 'get_by_geonameid', $geonameid ) ) {
            return wp_cache_get( 'get_by_geonameid', $geonameid );
        }

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

        wp_cache_set( 'get_by_geonameid', $results, $geonameid );

        return $results;
    }

    public static function get_parent_by_geonameid( $geonameid ) {

        if ( wp_cache_get( 'get_parent_by_geonameid', $geonameid ) ) {
            return wp_cache_get( 'get_parent_by_geonameid', $geonameid );
        }

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

        wp_cache_set( 'get_parent_by_geonameid', $results, $geonameid );

        return $results;
    }

    public static function get_children_by_geonameid( $geonameid ) {

        if ( wp_cache_get( 'get_children_by_geonameid', $geonameid ) ) {
            return wp_cache_get( 'get_children_by_geonameid', $geonameid );
        }

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

        wp_cache_set( 'get_children_by_geonameid', $results, $geonameid );

        return $results;
    }

    public static function get_by_geonameid_list( $list, $short = false ) {
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
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        if ( $short ) {
            $results = $wpdb->get_results("
                SELECT
                  g.geonameid, 
                  g.alt_name as name, 
                  IF(g.alt_population > 0, g.alt_population, g.population) as population,
                  g.latitude, 
                  g.longitude,
                  g.country_code,
                  g.level
                FROM $wpdb->dt_geonames as g
                WHERE g.geonameid IN ($prepared_list)
                ORDER BY g.alt_name ASC
            ", ARRAY_A );
        } else {
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
        }
        // phpcs:enable

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

            if ( wp_cache_get( 'get_countries', 'ids' ) ) {
                return wp_cache_get( 'get_countries', 'ids' );
            }

            $results = $wpdb->get_col( "
                 SELECT g.geonameid
                 FROM $wpdb->dt_geonames as g
                 WHERE g.level = 'country'
                 ORDER BY name ASC
        " );

            wp_cache_set( 'get_countries', $results, 'ids' );

        } else {

            if ( wp_cache_get( 'get_countries', 'all' ) ) {
                return wp_cache_get( 'get_countries', 'all' );
            }

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

            wp_cache_set( 'get_countries', $results, 'all' );
        }

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_country_code_by_id( $geonameid ) {

        if ( wp_cache_get( 'get_country_code_by_id', $geonameid ) ) {
            return wp_cache_get( 'get_country_code_by_id', $geonameid );
        }

        global $wpdb;

        $results = $wpdb->get_var( $wpdb->prepare( "
            SELECT country_code 
            FROM $wpdb->dt_geonames 
            WHERE geonameid = %s;
        ", $geonameid ) );

        if ( empty( $results ) ) {
            $results = 0;
        }

        wp_cache_set( 'get_country_code_by_id', $results, $geonameid );

        return $results;
    }

    public static function get_hierarchy( $geonameid = null ) {

        if ( wp_cache_get( 'get_hierarchy', $geonameid ) ) {
            return wp_cache_get( 'get_hierarchy', $geonameid );
        }

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

        wp_cache_set( 'get_hierarchy', $results, $geonameid );

        return $results;
    }

    public static function get_drilldown_by_geonameid( $geonameid ) {

        if ( wp_cache_get( 'get_drilldown_by_geonameid', $geonameid ) ) {
            return wp_cache_get( 'get_drilldown_by_geonameid', $geonameid );
        }

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

        wp_cache_set( 'get_drilldown_by_geonameid', $results, $geonameid );

        return $results;
    }

    public static function get_regions() {

        if ( wp_cache_get( 'get_regions' ) ) {
            return wp_cache_get( 'get_regions' );
        }

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

        wp_cache_set( 'get_regions', $results );

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

    public static function counter() {

        if ( get_transient( 'counter' ) ) {
            return get_transient( 'counter' );
        }

        if ( wp_cache_get( 'counter' ) ) {
            return wp_cache_get( 'counter' );
        }

        global $wpdb;

        $results = $wpdb->get_results( "
            SELECT
                g.country_geonameid,
                g.admin1_geonameid,
                g.admin2_geonameid,
                g.admin3_geonameid,
                g.geonameid,
   				g.level,
                p.post_id,
                CASE
                    WHEN gt.meta_value = 'church' THEN 'churches'
                    WHEN cu.meta_value IS NOT NULL THEN 'users'
                    ELSE pp.post_type
                END as type, 
                IF (pp.post_type = 'contacts', cs.meta_value, gs.meta_value) as status,
                IF (pp.post_type = 'contacts', UNIX_TIMESTAMP(pp.post_date), gd.meta_value) as created_date,
                IF (pp.post_type = 'contacts', ce.meta_value, ge.meta_value) as end_date
            FROM $wpdb->postmeta as p
                JOIN $wpdb->posts as pp ON p.post_id=pp.ID
                LEFT JOIN $wpdb->dt_geonames as g ON g.geonameid=p.meta_value             
                LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                LEFT JOIN $wpdb->postmeta as cs ON cs.post_id=p.post_id AND cs.meta_key = 'overall_status'
                LEFT JOIN $wpdb->postmeta as gs ON gs.post_id=p.post_id AND gs.meta_key = 'group_status'
                LEFT JOIN $wpdb->postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                LEFT JOIN $wpdb->postmeta as gd ON gd.post_id=p.post_id AND gd.meta_key = 'start_date'
                LEFT JOIN $wpdb->postmeta as ge ON ge.post_id=p.post_id AND ge.meta_key = 'end_date'
                LEFT JOIN $wpdb->postmeta as ce ON ce.post_id=p.post_id AND ce.meta_key = 'last_modified' AND cs.meta_value = 'closed'
            WHERE p.meta_key = 'geonames'
        ");

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'counter', $results );

        set_transient( 'counter', $results, strtotime( 'Tomorrow 3am' ) );

        return $results;
    }

    public static function get_geoname_totals() : array {

        global $wpdb;

        if ( get_transient( 'get_geoname_totals' ) ) {
            return get_transient( 'get_geoname_totals' );
        }

        $results = $wpdb->get_results("
            SELECT
              t1.country_geonameid as geonameid,
              t1.type,
              count(t1.country_geonameid) as count
            FROM (
                SELECT
                    g.country_geonameid,
                    CASE
                    	WHEN gt.meta_value = 'church' THEN 'churches'
                    	WHEN cu.meta_value IS NOT NULL THEN 'users'
                    	ELSE pp.post_type
                    END as type
                FROM $wpdb->postmeta as p
                    JOIN $wpdb->posts as pp ON p.post_id=pp.ID
                    LEFT JOIN $wpdb->dt_geonames as g ON g.geonameid=p.meta_value             
                    LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                    LEFT JOIN $wpdb->postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                WHERE p.meta_key = 'geonames'
            ) as t1
            WHERE t1.country_geonameid != ''
            GROUP BY t1.country_geonameid, t1.type
            UNION
            SELECT
              t2.admin1_geonameid as geonameid,
              t2.type,
              count(t2.admin1_geonameid) as count
            FROM (
                    SELECT
                    g.admin1_geonameid,
                    CASE
                    	WHEN gt.meta_value = 'church' THEN 'churches'
                    	WHEN cu.meta_value IS NOT NULL THEN 'users'
                    	ELSE pp.post_type
                    END as type
                FROM $wpdb->postmeta as p
                    JOIN $wpdb->posts as pp ON p.post_id=pp.ID
                    LEFT JOIN $wpdb->dt_geonames as g ON g.geonameid=p.meta_value             
                    LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                    LEFT JOIN $wpdb->postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                WHERE p.meta_key = 'geonames'
            ) as t2
            WHERE t2.admin1_geonameid != ''
            GROUP BY t2.admin1_geonameid, t2.type
            UNION
            SELECT
              t3.admin2_geonameid as geonameid,
              t3.type,
              count(t3.admin2_geonameid) as count
            FROM (
                    SELECT
                    g.admin2_geonameid,
                    CASE
                    	WHEN gt.meta_value = 'church' THEN 'churches'
                    	WHEN cu.meta_value IS NOT NULL THEN 'users'
                    	ELSE pp.post_type
                    END as type
                FROM $wpdb->postmeta as p
                    JOIN $wpdb->posts as pp ON p.post_id=pp.ID
                    LEFT JOIN $wpdb->dt_geonames as g ON g.geonameid=p.meta_value             
                    LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                    LEFT JOIN $wpdb->postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                WHERE p.meta_key = 'geonames'
            ) as t3
            WHERE t3.admin2_geonameid != ''
            GROUP BY t3.admin2_geonameid, t3.type
            UNION
            SELECT
              t4.admin3_geonameid as geonameid,
              t4.type,
              count(t4.admin3_geonameid) as count
            FROM (
                    SELECT
                    g.admin3_geonameid,
                    CASE
                    	WHEN gt.meta_value = 'church' THEN 'churches'
                    	WHEN cu.meta_value IS NOT NULL THEN 'users'
                    	ELSE pp.post_type
                    END as type
                FROM $wpdb->postmeta as p
                    JOIN $wpdb->posts as pp ON p.post_id=pp.ID
                    LEFT JOIN $wpdb->dt_geonames as g ON g.geonameid=p.meta_value             
                    LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                    LEFT JOIN $wpdb->postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                WHERE p.meta_key = 'geonames'
            ) as t4
            WHERE t4.admin3_geonameid != ''
            GROUP BY t4.admin3_geonameid, t4.type;
        ", ARRAY_A );

        set_transient( 'get_geoname_totals', $results, strtotime( 'Tomorrow 1am' ) );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_geoname_totals_for_countries() {
        global $wpdb;

        if ( wp_cache_get( 'get_geoname_totals_for_countries' ) ) {
            return wp_cache_get( 'get_geoname_totals_for_countries' );
        }

        $results = $wpdb->get_results("
                SELECT
                  country_geonameid as geonameid,
                  type,
                  count(country_geonameid) as count
                FROM (
                        SELECT
                        g.country_geonameid,
                        CASE
                            WHEN gt.meta_value = 'church' THEN 'churches'
                            WHEN cu.meta_value IS NOT NULL THEN 'users'
                            ELSE pp.post_type
                        END as type
                    FROM $wpdb->postmeta as p
                        JOIN $wpdb->posts as pp ON p.post_id=pp.ID
                        LEFT JOIN $wpdb->dt_geonames as g ON g.geonameid=p.meta_value             
                        LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                        LEFT JOIN wp_postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                    WHERE p.meta_key = 'geonames' AND g.country_geonameid != ''
                ) as t1
                GROUP BY country_geonameid, type
            ", ARRAY_A );


        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'get_geoname_totals_for_countries', $results );

        return $results;
    }

    public static function active_countries_geonames() : array {

        if ( wp_cache_get( 'active_countries_geonames' ) ) {
            return wp_cache_get( 'active_countries_geonames' );
        }

        global $wpdb;

        $results = $wpdb->get_col( "
            SELECT DISTINCT
                g.country_geonameid as geonameid
            FROM $wpdb->postmeta as p
            JOIN $wpdb->dt_geonames as g ON g.geonameid=p.meta_value             
            WHERE p.meta_key = 'geonames' AND g.country_geonameid != 0
        ");

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'active_countries_geonames', $results );

        return $results;
    }

    public static function active_admin1_geonames() : array {

        if ( wp_cache_get( 'active_admin1_geonames' ) ) {
            return wp_cache_get( 'active_admin1_geonames' );
        }

        global $wpdb;

        $results = $wpdb->get_col( "
            SELECT DISTINCT
                g.admin1_geonameid as geonameid
            FROM $wpdb->postmeta as p
            JOIN $wpdb->dt_geonames as g ON g.geonameid=p.meta_value             
            WHERE p.meta_key = 'geonames' AND g.admin1_geonameid != 0
        ");

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'active_admin1_geonames', $results );

        return $results;
    }

    public static function active_admin2_geonames() : array {

        if ( wp_cache_get( 'active_admin2_geonames' ) ) {
            return wp_cache_get( 'active_admin2_geonames' );
        }

        global $wpdb;

        $results = $wpdb->get_col( "
            SELECT DISTINCT
                g.admin2_geonameid as geonameid
            FROM $wpdb->postmeta as p
            JOIN $wpdb->dt_geonames as g ON g.geonameid=p.meta_value             
            WHERE p.meta_key = 'geonames' AND g.admin2_geonameid != 0
        ");

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'active_admin2_geonames', $results );

        return $results;
    }

    public static function get_total_record_count_in_geonames_database() {

        if ( wp_cache_get( 'total_records_in_geonames_database' ) ) {
            return wp_cache_get( 'total_records_in_geonames_database' );
        }

        global $wpdb;

        $results = $wpdb->get_var("
            SELECT count(*)
            FROM $wpdb->dt_geonames 
        ");

        if ( empty( $results ) ) {
            $results = 0;
        }

        wp_cache_set( 'total_records_in_geonames_database', $results );

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
            ORDER BY CASE
                WHEN g.alt_name LIKE %s then 1
                ELSE 2
            END, g.country_code, CHAR_LENGTH(label)
            LIMIT 30;
            ", '%' . $search_query . '%', $search_query ),
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
            INNER JOIN (
                SELECT
                    g.geonameid
                FROM $wpdb->postmeta as p
                JOIN $wpdb->dt_geonames as g ON g.geonameid=p.meta_value             
                WHERE p.meta_key = 'geonames' AND p.meta_value != ''
            ) as counter ON (g.geonameid = counter.geonameid)
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

    public static function get_names_from_ids( $geoname_ids ) {
        global $wpdb;

        $ids = dt_array_to_sql( $geoname_ids );
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $results = $wpdb->get_results("
                            SELECT geonameid, alt_name
                            FROM $wpdb->dt_geonames
                            WHERE geonameid IN ( $ids ) 
                        ", ARRAY_A );
        // phpcs:enable
        $prepared = [];
        foreach ( $results as $row ){
            $prepared[$row["geonameid"]] = $row["alt_name"];
        }
        return $prepared;
    }

    public static function get_geoname_ids_and_names_for_post_ids( $post_ids ) {
        global $wpdb;

        $prepared = [];

        foreach ( $post_ids as $post_id ) {
            $prepared[$post_id] = [];
        }
        if ( empty( $post_ids ) ){
            return [];
        }
        $joined_post_ids = dt_array_to_sql( $post_ids );
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
                            SELECT geonameid, alt_name 
                            FROM $wpdb->dt_geonames
                            WHERE geonameid IN ( $joined_geoname_ids ) 
                        ", ARRAY_A );
        // phpcs:enable
        $mapped_geoname_id_to_name = [];
        foreach ( $geoname_id_names as $geoname ){
            $mapped_geoname_id_to_name[$geoname["geonameid"]] = $geoname["alt_name"];
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
