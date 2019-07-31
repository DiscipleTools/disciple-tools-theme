<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Mapping_Queries {

    public static function get_by_grid_id( $grid_id ) {

        if ( wp_cache_get( 'get_by_grid_id', $grid_id ) ) {
            return wp_cache_get( 'get_by_grid_id', $grid_id );
        }

        global $wpdb;

        $results = $wpdb->get_row( $wpdb->prepare( "
            SELECT
              g.grid_id as id, 
              g.grid_id, 
              g.alt_name as name, 
              g.alt_population as population, 
              g.latitude, 
              g.longitude,
              g.country_code,
              g.admin0_code,
              g.parent_id,
              g.admin0_grid_id,
              g.admin1_grid_id,
              g.admin2_grid_id,
              g.admin3_grid_id,
              g.admin4_grid_id,
              g.admin5_grid_id,
              g.level,
              g.level_name,
              g.is_custom_location
            FROM $wpdb->dt_location_grid as g
            WHERE g.grid_id = %s
        ", $grid_id ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'get_by_grid_id', $results, $grid_id );

        return $results;
    }

    public static function get_parent_by_grid_id( $grid_id ) {

        if ( wp_cache_get( 'get_parent_by_grid_id', $grid_id ) ) {
            return wp_cache_get( 'get_parent_by_grid_id', $grid_id );
        }

        global $wpdb;

        $results = $wpdb->get_row( $wpdb->prepare( "
            SELECT 
              p.grid_id as id, 
              p.grid_id, 
              p.alt_name as name, 
              p.alt_population as population,
              p.latitude, 
              p.longitude,
              p.country_code,
              p.admin0_code,
              p.parent_id,
              p.admin0_grid_id,
              p.admin1_grid_id,
              p.admin2_grid_id,
              p.admin3_grid_id,
              p.admin4_grid_id,
              p.admin5_grid_id,
              p.level,
              p.level_name
            FROM $wpdb->dt_location_grid as g
            JOIN $wpdb->dt_location_grid as p ON g.parent_id=p.grid_id
            WHERE g.grid_id = %s
        ", $grid_id ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'get_parent_by_grid_id', $results, $grid_id );

        return $results;
    }

    public static function get_children_by_grid_id( $grid_id ) {

        if ( wp_cache_get( 'get_children_by_grid_id', $grid_id ) ) {
            return wp_cache_get( 'get_children_by_grid_id', $grid_id );
        }

        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
              g.grid_id as id, 
              g.grid_id, 
              g.alt_name as name, 
              g.alt_population as population, 
              g.latitude, 
              g.longitude,
              g.country_code,
              g.parent_id,
              g.admin0_grid_id,
              g.admin1_grid_id,
              g.admin2_grid_id,
              g.admin3_grid_id,
              g.admin4_grid_id,
              g.admin5_grid_id,
              g.level,
              g.is_custom_location
            FROM $wpdb->dt_location_grid as g
            WHERE g.parent_id = %d
            ORDER BY g.alt_name ASC
        ", $grid_id ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'get_children_by_grid_id', $results, $grid_id );

        return $results;
    }

    public static function get_by_grid_id_list( $list, $short = false ) {
        global $wpdb;

        if ( empty( $list ) ) {
            return [];
        }

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
                  g.grid_id, 
                  g.alt_name as name, 
                  g.alt_population as population,
                  g.latitude, 
                  g.longitude,
                  g.country_code,
                  g.level
                FROM $wpdb->dt_location_grid as g
                WHERE g.grid_id IN ($prepared_list)
                ORDER BY g.alt_name ASC
            ", ARRAY_A );
        } else {
            $results = $wpdb->get_results("
                SELECT
                  g.grid_id as id, 
                  g.grid_id, 
                  g.alt_name as name, 
                  g.alt_population as population,
                  g.latitude, 
                  g.longitude,
                  g.country_code,
                  g.parent_id,
                  g.admin0_grid_id,
                  g.admin1_grid_id,
                  g.admin2_grid_id,
                  g.admin3_grid_id,
                  g.admin4_grid_id,
                  g.admin5_grid_id,
                  g.level
                FROM $wpdb->dt_location_grid as g
                WHERE g.grid_id IN ($prepared_list)
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
                 SELECT g.grid_id
                 FROM $wpdb->dt_location_grid as g
                 WHERE g.level = 0
                 ORDER BY alt_name ASC
        " );

            wp_cache_set( 'get_countries', $results, 'ids' );

        } else {

            if ( wp_cache_get( 'get_countries', 'all' ) ) {
                return wp_cache_get( 'get_countries', 'all' );
            }

            $results = $wpdb->get_results( "
                 SELECT
                        g.grid_id,
                        g.alt_name as name,
                        g.alt_population as population,
                        g.latitude,
                        g.longitude,
                        g.country_code,
                        g.admin0_code,
                        g.modification_date,
                        g.parent_id,
                        g.admin0_grid_id,
                        g.admin1_grid_id,
                        g.admin2_grid_id,
                        g.admin3_grid_id,
                        g.admin4_grid_id,
                        g.admin5_grid_id,
                        g.level
                 FROM $wpdb->dt_location_grid as g
                 WHERE g.level = 0
                 ORDER BY name ASC
            ", ARRAY_A );

            wp_cache_set( 'get_countries', $results, 'all' );
        }

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_country_code_by_id( $grid_id ) {

        if ( wp_cache_get( 'get_country_code_by_id', $grid_id ) ) {
            return wp_cache_get( 'get_country_code_by_id', $grid_id );
        }

        global $wpdb;

        $results = $wpdb->get_var( $wpdb->prepare( "
            SELECT country_code 
            FROM $wpdb->dt_location_grid 
            WHERE grid_id = %s;
        ", $grid_id ) );

        if ( empty( $results ) ) {
            $results = 0;
        }

        wp_cache_set( 'get_country_code_by_id', $results, $grid_id );

        return $results;
    }

    public static function get_admin0_code_by_id( $grid_id ) {

        if ( wp_cache_get( 'get_admin0_code_by_id', $grid_id ) ) {
            return wp_cache_get( 'get_admin0_code_by_id', $grid_id );
        }

        global $wpdb;

        $results = $wpdb->get_var( $wpdb->prepare( "
            SELECT admin0_code 
            FROM $wpdb->dt_location_grid 
            WHERE grid_id = %s;
        ", $grid_id ) );

        if ( empty( $results ) ) {
            $results = 0;
        }

        wp_cache_set( 'get_admin0_code_by_id', $results, $grid_id );

        return $results;
    }

    public static function get_hierarchy( $grid_id = null ) {

        if ( wp_cache_get( 'get_hierarchy', $grid_id ) ) {
            return wp_cache_get( 'get_hierarchy', $grid_id );
        }

        global $wpdb;

        if ( $grid_id ) {
            $results = $wpdb->get_row( $wpdb->prepare( "
                SELECT
                g.parent_id,
                g.grid_id,
                g.admin0_grid_id,
                g.admin1_grid_id,
                g.admin2_grid_id,
                g.admin3_grid_id,
                g.admin4_grid_id,
                g.admin5_grid_id,
                g.level
                FROM $wpdb->dt_location_grid as g
                WHERE g.grid_id = %d;
            ", $grid_id ), ARRAY_A );
        } else {
            $results = $wpdb->get_results("
                SELECT 
                g.parent_id,
                g.grid_id,
                g.admin0_grid_id,
                g.admin1_grid_id,
                g.admin2_grid_id,
                g.admin3_grid_id,
                g.admin4_grid_id,
                g.admin5_grid_id,
                g.level
                FROM $wpdb->dt_location_grid as g", ARRAY_A );
        }

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'get_hierarchy', $results, $grid_id );

        return $results;
    }

    public static function get_drilldown_by_grid_id( $grid_id ) {

        if ( wp_cache_get( 'get_drilldown_by_grid_id', $grid_id ) ) {
            return wp_cache_get( 'get_drilldown_by_grid_id', $grid_id );
        }

        global $wpdb;

        $results = $wpdb->get_row( $wpdb->prepare( "
            SELECT
              g.grid_id as id, 
              g.grid_id, 
              g.alt_name as name, 
              g.alt_population as population, 
              g.latitude, 
              g.longitude,
              g.country_code,
              g.admin0_code,
              g.parent_id,
              g.admin0_grid_id,
              gc.alt_name as admin0_name,
              g.admin1_grid_id,
              ga1.alt_name as admin1_name,
              g.admin2_grid_id,
              ga2.alt_name as admin2_name,
              g.admin3_grid_id,
              ga3.alt_name as admin3_name,
              g.admin4_grid_id,
              ga4.alt_name as admin4_name,
              g.admin5_grid_id,
              ga5.alt_name as admin5_name,
              g.level,
              g.is_custom_location
            FROM $wpdb->dt_location_grid as g
            LEFT JOIN $wpdb->dt_location_grid as gc ON g.admin0_grid_id=gc.grid_id
            LEFT JOIN $wpdb->dt_location_grid as ga1 ON g.admin1_grid_id=ga1.grid_id
            LEFT JOIN $wpdb->dt_location_grid as ga2 ON g.admin2_grid_id=ga2.grid_id
            LEFT JOIN $wpdb->dt_location_grid as ga3 ON g.admin3_grid_id=ga3.grid_id
            LEFT JOIN $wpdb->dt_location_grid as ga4 ON g.admin4_grid_id=ga4.grid_id
            LEFT JOIN $wpdb->dt_location_grid as ga5 ON g.admin5_grid_id=ga5.grid_id
            WHERE g.grid_id = %s
        ", $grid_id ), ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'get_drilldown_by_grid_id', $results, $grid_id );

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
        // @todo rebuild this regions strategy for query
//        $results = $wpdb->get_results("
//            SELECT
//                g.grid_id,
//                g.alt_name as name,
//                g.alt_population as population,
//                g.latitude,
//                g.longitude,
//                g.country_code,
//                g.admin0_code,
//                g.parent_id,
//                g.admin0_grid_id,
//                g.admin1_grid_id,
//                g.admin2_grid_id,
//                g.admin3_grid_id,
//                g.admin4_grid_id,
//                g.admin5_grid_id,
//                g.level
//            FROM $wpdb->dt_location_grid as g
//            WHERE feature_code = 'RGN'
//            AND country_code = '';
//        ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'get_regions', $results );

        return $results;
    }

    public static function get_continents() {
        global $wpdb;

        // @todo rebuild continent strategy
//        $results = $wpdb->get_results("
//            SELECT
//                g.grid_id,
//                g.alt_name as name,
//                g.alt_population as population,
//                g.latitude,
//                g.longitude,
//                g.country_code,
//                g.admin0_code,
//                g.parent_id,
//                g.admin0_grid_id,
//                g.admin1_grid_id,
//                g.admin2_grid_id,
//                g.admin3_grid_id,
//                g.admin4_grid_id,
//                g.admin5_grid_id,
//                g.level
//            FROM $wpdb->dt_location_grid as g
//            WHERE g.grid_id IN (6255146,6255147,6255148,6255149,6255151,6255150,6255152)
//            ORDER BY name ASC;
//        ", ARRAY_A );

        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_earth() {
        global $wpdb;

        $results = $wpdb->get_row("
            SELECT
                g.grid_id,
                ('world') as id,
                g.alt_name as name,
                g.alt_population as population,
                g.latitude,
                g.longitude,
                g.admin0_code,
                g.parent_id,
                g.admin0_grid_id,
                g.admin1_grid_id,
                g.admin2_grid_id,
                g.admin3_grid_id,
                g.admin4_grid_id,
                g.admin5_grid_id,
                (-3) as level
            FROM $wpdb->dt_location_grid as g
            WHERE g.grid_id = 1
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
                g.admin0_grid_id,
                g.admin1_grid_id,
                g.admin2_grid_id,
                g.admin3_grid_id,
                g.admin4_grid_id,
                g.admin5_grid_id,
                g.grid_id,
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
                LEFT JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value             
                LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                LEFT JOIN $wpdb->postmeta as cs ON cs.post_id=p.post_id AND cs.meta_key = 'overall_status'
                LEFT JOIN $wpdb->postmeta as gs ON gs.post_id=p.post_id AND gs.meta_key = 'group_status'
                LEFT JOIN $wpdb->postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                LEFT JOIN $wpdb->postmeta as gd ON gd.post_id=p.post_id AND gd.meta_key = 'start_date'
                LEFT JOIN $wpdb->postmeta as ge ON ge.post_id=p.post_id AND ge.meta_key = 'end_date'
                LEFT JOIN $wpdb->postmeta as ce ON ce.post_id=p.post_id AND ce.meta_key = 'last_modified' AND cs.meta_value = 'closed'
            WHERE p.meta_key = 'location_grid'
        ");

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'counter', $results );

        set_transient( 'counter', $results, 60 * 60 * 24 );

        return $results;
    }

    public static function get_location_grid_totals() : array {

        global $wpdb;

        if ( get_transient( 'get_location_grid_totals' ) ) {
            return get_transient( 'get_location_grid_totals' );
        }

        $results = $wpdb->get_results("
            SELECT
              t1.admin0_grid_id as grid_id,
              t1.type,
              count(t1.admin0_grid_id) as count
            FROM (
                SELECT
                    g.admin0_grid_id,
                    CASE
                    	WHEN gt.meta_value = 'church' THEN 'churches'
                    	WHEN cu.meta_value IS NOT NULL THEN 'users'
                    	ELSE pp.post_type
                    END as type
                FROM $wpdb->postmeta as p
                    JOIN $wpdb->posts as pp ON p.post_id=pp.ID
                    LEFT JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value             
                    LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                    LEFT JOIN $wpdb->postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                WHERE p.meta_key = 'location_grid'
            ) as t1
            WHERE t1.admin0_grid_id != ''
            GROUP BY t1.admin0_grid_id, t1.type
            UNION
            SELECT
              t2.admin1_grid_id as grid_id,
              t2.type,
              count(t2.admin1_grid_id) as count
            FROM (
                    SELECT
                    g.admin1_grid_id,
                    CASE
                    	WHEN gt.meta_value = 'church' THEN 'churches'
                    	WHEN cu.meta_value IS NOT NULL THEN 'users'
                    	ELSE pp.post_type
                    END as type
                FROM $wpdb->postmeta as p
                    JOIN $wpdb->posts as pp ON p.post_id=pp.ID
                    LEFT JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value             
                    LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                    LEFT JOIN $wpdb->postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                WHERE p.meta_key = 'location_grid'
            ) as t2
            WHERE t2.admin1_grid_id != ''
            GROUP BY t2.admin1_grid_id, t2.type
            UNION
            SELECT
              t3.admin2_grid_id as grid_id,
              t3.type,
              count(t3.admin2_grid_id) as count
            FROM (
                    SELECT
                    g.admin2_grid_id,
                    CASE
                    	WHEN gt.meta_value = 'church' THEN 'churches'
                    	WHEN cu.meta_value IS NOT NULL THEN 'users'
                    	ELSE pp.post_type
                    END as type
                FROM $wpdb->postmeta as p
                    JOIN $wpdb->posts as pp ON p.post_id=pp.ID
                    LEFT JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value             
                    LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                    LEFT JOIN $wpdb->postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                WHERE p.meta_key = 'location_grid'
            ) as t3
            WHERE t3.admin2_grid_id != ''
            GROUP BY t3.admin2_grid_id, t3.type
            UNION
            SELECT
              t4.admin3_grid_id as grid_id,
              t4.type,
              count(t4.admin3_grid_id) as count
            FROM (
                    SELECT
                    g.admin3_grid_id,
                    CASE
                    	WHEN gt.meta_value = 'church' THEN 'churches'
                    	WHEN cu.meta_value IS NOT NULL THEN 'users'
                    	ELSE pp.post_type
                    END as type
                FROM $wpdb->postmeta as p
                    JOIN $wpdb->posts as pp ON p.post_id=pp.ID
                    LEFT JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value             
                    LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                    LEFT JOIN $wpdb->postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                WHERE p.meta_key = 'location_grid'
            ) as t4
            WHERE t4.admin3_grid_id != ''
            GROUP BY t4.admin3_grid_id, t4.type;
        ", ARRAY_A );


        set_transient( 'get_geoname_totals', $results, 60 * 60 * 24 );


        if ( empty( $results ) ) {
            $results = [];
        }

        return $results;
    }

    public static function get_location_grid_totals_for_countries() {
        global $wpdb;

        if ( wp_cache_get( 'get_location_grid_totals_for_countries' ) ) {
            return wp_cache_get( 'get_location_grid_totals_for_countries' );
        }

        $results = $wpdb->get_results("
                SELECT
                  admin0_grid_id as grid_id,
                  type,
                  count(admin0_grid_id) as count
                FROM (
                        SELECT
                        g.admin0_grid_id,
                        CASE
                            WHEN gt.meta_value = 'church' THEN 'churches'
                            WHEN cu.meta_value IS NOT NULL THEN 'users'
                            ELSE pp.post_type
                        END as type
                    FROM $wpdb->postmeta as p
                        JOIN $wpdb->posts as pp ON p.post_id=pp.ID
                        LEFT JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value             
                        LEFT JOIN $wpdb->postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'corresponds_to_user'
                        LEFT JOIN wp_postmeta as gt ON gt.post_id=p.post_id AND gt.meta_key = 'group_type'
                    WHERE p.meta_key = 'location_grid' AND g.admin0_grid_id != ''
                ) as t1
                GROUP BY admin0_grid_id, type
            ", ARRAY_A );


        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'get_location_grid_totals_for_countries', $results );

        return $results;
    }

    public static function active_admin0_grid_ids() : array {

        if ( wp_cache_get( 'active_admin0_grid_ids' ) ) {
            return wp_cache_get( 'active_admin0_grid_ids' );
        }

        global $wpdb;

        $results = $wpdb->get_col( "
            SELECT DISTINCT
                g.admin0_grid_id as grid_id
            FROM $wpdb->postmeta as p
            JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value             
            WHERE p.meta_key = 'location_grid' AND g.admin0_grid_id != 0
        ");

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'active_admin0_grid_ids', $results );

        return $results;
    }

    public static function active_admin1_grid_ids() : array {

        if ( wp_cache_get( 'active_admin1_grid_ids' ) ) {
            return wp_cache_get( 'active_admin1_grid_ids' );
        }

        global $wpdb;

        $results = $wpdb->get_col( "
            SELECT DISTINCT
                g.admin1_grid_id as grid_id
            FROM $wpdb->postmeta as p
            JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value             
            WHERE p.meta_key = 'location_grid' AND g.admin1_grid_id != 0
        ");

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'active_admin1_grid_ids', $results );

        return $results;
    }

    public static function active_admin2_grid_ids() : array {

        if ( wp_cache_get( 'active_admin2_grid_ids' ) ) {
            return wp_cache_get( 'active_admin2_grid_ids' );
        }

        global $wpdb;

        $results = $wpdb->get_col( "
            SELECT DISTINCT
                g.admin2_grid_id as grid_id
            FROM $wpdb->postmeta as p
            JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value             
            WHERE p.meta_key = 'location_grid' AND g.admin2_grid_id != 0
        ");

        if ( empty( $results ) ) {
            $results = [];
        }

        wp_cache_set( 'active_admin2_grid_ids', $results );

        return $results;
    }

    public static function get_total_record_count_in_location_grid_database() {

        if ( wp_cache_get( 'total_records_in_location_grid_database' ) ) {
            return wp_cache_get( 'total_records_in_location_grid_database' );
        }

        global $wpdb;

        $results = $wpdb->get_var("
            SELECT count(*)
            FROM $wpdb->dt_location_grid 
        ");

        if ( empty( $results ) ) {
            $results = 0;
        }

        wp_cache_set( 'total_records_in_location_grid_database', $results );

        return $results;
    }

    public static function search_location_grid_by_name( $args ) {
        global $wpdb;

        $search_query = $wpdb->esc_like( $args['search_query'] ?? "" );
        $focus_search_sql = "";
        if ( isset( $args['filter'] ) && $args["filter"] == "focus" ){
            $default_map_settings = DT_Mapping_Module::instance()->default_map_settings();
            if ( $default_map_settings["type"] === "country" && sizeof( $default_map_settings["children"] ) > 0 ){
                $joined_location_grid_ids = dt_array_to_sql( $default_map_settings["children"] );
                $focus_search_sql = "AND g.admin0_grid_id IN ( $joined_location_grid_ids ) ";
            }
        }
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $location_grid = $wpdb->get_results( $wpdb->prepare( "
            SELECT SQL_CALC_FOUND_ROWS
            DISTINCT( g.grid_id ),
            CASE 
                WHEN g.level = 0 
                  THEN g.alt_name
                WHEN g.level = 1 
                  THEN CONCAT( (SELECT country.alt_name FROM $wpdb->dt_location_grid as country WHERE country.grid_id = g.admin0_grid_id LIMIT 1), ' > ', 
                g.alt_name ) 
                WHEN g.level >= 2
                  THEN CONCAT( (SELECT country.alt_name FROM $wpdb->dt_location_grid as country WHERE country.grid_id = g.admin0_grid_id LIMIT 1), ' > ', 
                (SELECT a1.alt_name FROM $wpdb->dt_location_grid AS a1 WHERE a1.grid_id = g.admin1_grid_id LIMIT 1), ' > ', 
                g.alt_name )
                ELSE g.alt_name
            END as label
            FROM $wpdb->dt_location_grid as g
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
            'location_grid' => $location_grid,
            'total' => $total_rows
        ];
    }

    public static function search_used_location_grid_by_name( $args ) {
        global $wpdb;

        $search_query = $wpdb->esc_like( $args['search_query'] ?? "" );

        $location_grid = $wpdb->get_results( $wpdb->prepare( "
            SELECT SQL_CALC_FOUND_ROWS
            DISTINCT( g.grid_id ),
            CASE 
                WHEN g.level = 0 
                  THEN g.alt_name
                WHEN g.level = 1
                  THEN CONCAT( (SELECT country.alt_name FROM $wpdb->dt_location_grid as country WHERE country.grid_id = g.admin0_grid_id LIMIT 1), ' > ', 
                g.alt_name ) 
                WHEN g.level >= 2
                  THEN CONCAT( (SELECT country.alt_name FROM $wpdb->dt_location_grid as country WHERE country.grid_id = g.admin0_grid_id LIMIT 1), ' > ', 
                (SELECT a1.alt_name FROM $wpdb->dt_location_grid AS a1 WHERE a1.grid_id = g.admin1_grid_id LIMIT 1), ' > ', 
                g.alt_name )
                ELSE g.alt_name
            END as label
            FROM $wpdb->dt_location_grid as g
            INNER JOIN (
                SELECT
                    g.grid_id
                FROM $wpdb->postmeta as p
                JOIN $wpdb->dt_location_grid as g ON g.grid_id=p.meta_value             
                WHERE p.meta_key = 'location_grid' AND p.meta_value != ''
            ) as counter ON (g.grid_id = counter.grid_id)
            WHERE g.alt_name LIKE %s
            
            ORDER BY g.country_code, CHAR_LENGTH(label)
            LIMIT 30;
            ", '%' . $search_query . '%' ),
            ARRAY_A
        );
        $total_rows = $wpdb->get_var( "SELECT found_rows();" );
        return [
            'location_grid' => $location_grid,
            'total' => $total_rows
        ];
    }

    public static function get_names_from_ids( $location_grid_ids ) {
        global $wpdb;

        $ids = dt_array_to_sql( $location_grid_ids );
        // phpcs:disable
        // WordPress.WP.PreparedSQL.NotPrepared
        $results = $wpdb->get_results("
                            SELECT grid_id, alt_name
                            FROM $wpdb->dt_location_grid
                            WHERE grid_id IN ( $ids ) 
                        ", ARRAY_A );
        // phpcs:enable
        $prepared = [];
        foreach ( $results as $row ){
            $prepared[$row["grid_id"]] = $row["alt_name"];
        }
        return $prepared;
    }

    public static function get_location_grid_ids_and_names_for_post_ids( $post_ids ) {
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
        $location_grids = $wpdb->get_results("
                            SELECT post_id, meta_value
                            FROM $wpdb->postmeta pm
                            WHERE meta_key = 'location_grid'
                            AND post_id IN ( $joined_post_ids )
                        ", ARRAY_A );
        if ( empty( $location_grids ) ){
            return $prepared;
        }
        $location_grid_ids = array_map( function( $g ){ return $g["meta_value"]; }, $location_grids );
        $joined_location_grid_ids = dt_array_to_sql( $location_grid_ids );
        $location_grid_id_names = $wpdb->get_results("
                            SELECT grid_id, alt_name 
                            FROM $wpdb->dt_location_grid
                            WHERE grid_id IN ( $joined_location_grid_ids ) 
                        ", ARRAY_A );
        // phpcs:enable
        $mapped_location_grid_id_to_name = [];
        foreach ( $location_grid_id_names as $location_grid ){
            $mapped_location_grid_id_to_name[$location_grid["grid_id"]] = $location_grid["alt_name"];
        }
        foreach ( $location_grids as $location_grid ){
            if ( isset( $mapped_location_grid_id_to_name[$location_grid["meta_value"]] ) ){
                $prepared[$location_grid["post_id"]][] = [
                    "location_grid_id" => $location_grid["meta_value"],
                    "name" => $mapped_location_grid_id_to_name[$location_grid["meta_value"]]
                ];
            }
        }
        return $prepared;
    }
}
