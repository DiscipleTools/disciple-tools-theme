<?php

/**
 * Class DT_Mapping_Module_Migration_0006
 *
 * @note    This migration adds a view table that combines the geoname tagged contacts and groups along with their
 *          higher level name divisions. (Country geonameid, Admin1 geonameid, Admin2 geonameid)
 *
 */
class DT_Mapping_Module_Migration_0006 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception
     */
    public function up() {
        global $wpdb;

        // @note currently this is a 25ms query. Improvements welcome.
        $wpdb->query("
            CREATE OR REPLACE VIEW {$wpdb->prefix}dt_geonames_reference AS    
            SELECT
                g.geonameid,
                IFNULL(gn.meta_value, g.name) as name,
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
                IFNULL(gp.meta_value, g.population) as population,
                g.timezone,
                g.modification_date,
                gh.parent_id,
                gh.country_geonameid,
                gh.admin1_geonameid,
                gh.admin2_geonameid,
                gh.admin3_geonameid,
                IFNULL(gcna.meta_value, gcn.name) as country_name,
                IFNULL(ga1na.meta_value, ga1n.name) as admin1_name,
                IFNULL(ga2na.meta_value, ga2n.name) as admin2_name
            FROM dt_geonames_hierarchy as gh
            LEFT JOIN dt_geonames as g ON gh.geonameid=g.geonameid
            LEFT JOIN {$wpdb->prefix}dt_geonames_meta as gn ON gh.geonameid=gn.geonameid AND gn.meta_key = 'name'
            LEFT JOIN {$wpdb->prefix}dt_geonames_meta as gp ON gh.geonameid=gp.geonameid AND gp.meta_key = 'population'
            LEFT JOIN dt_geonames as gcn ON gcn.geonameid=gh.country_geonameid
            LEFT JOIN {$wpdb->prefix}dt_geonames_meta as gcna ON gcna.geonameid=gh.country_geonameid AND gcna.meta_key = 'name'
            LEFT JOIN dt_geonames as ga1n ON ga1n.geonameid=gh.admin1_geonameid
            LEFT JOIN {$wpdb->prefix}dt_geonames_meta as ga1na ON ga1na.geonameid=gh.admin1_geonameid AND ga1na.meta_key = 'name'
            LEFT JOIN dt_geonames as ga2n ON ga2n.geonameid=gh.admin2_geonameid
            LEFT JOIN {$wpdb->prefix}dt_geonames_meta as ga2na ON ga2na.geonameid=gh.admin2_geonameid AND ga2na.meta_key = 'name'
            UNION ALL
            SELECT
                sub.geonameid,
                sub.name,
                sub.latitude,
                sub.longitude,
                ('L') as feature_class,
                ('LCTY') as feature_code,
                g.country_code as country_code,
                g.cc2 as cc2,
                g.admin1_code as admin1_code,
                g.admin2_code as admin2_code,
                g.admin3_code as admin3_code,
                g.admin4_code as admin4_code,
                sub.population,
                g.timezone as timezone,
                sub.modification_date,
                sub.parent_id,
                gh.country_geonameid,
                gh.admin1_geonameid,
                gh.admin2_geonameid,
                gh.admin3_geonameid,
                IFNULL(gcna.meta_value, gcn.name) as country_name,
                IFNULL(ga1na.meta_value, ga1n.name) as admin1_name,
                IFNULL(ga2na.meta_value, ga2n.name) as admin2_name
            FROM {$wpdb->prefix}dt_geonames_sublocations as sub
            LEFT JOIN dt_geonames_hierarchy as gh ON sub.parent_id=gh.geonameid
            LEFT JOIN dt_geonames as g ON sub.parent_id=g.geonameid
            LEFT JOIN dt_geonames as gcn ON gcn.geonameid=gh.country_geonameid
            LEFT JOIN {$wpdb->prefix}dt_geonames_meta as gcna ON gcna.geonameid=gh.country_geonameid AND gcna.meta_key = 'name'
            LEFT JOIN dt_geonames as ga1n ON ga1n.geonameid=gh.admin1_geonameid
            LEFT JOIN {$wpdb->prefix}dt_geonames_meta as ga1na ON ga1na.geonameid=gh.admin1_geonameid AND ga1na.meta_key = 'name'
            LEFT JOIN dt_geonames as ga2n ON ga2n.geonameid=gh.admin2_geonameid
            LEFT JOIN {$wpdb->prefix}dt_geonames_meta as ga2na ON ga2na.geonameid=gh.admin2_geonameid AND ga2na.meta_key = 'name'  
        ");

        $this->test();
    }

    public function down() {
        global $wpdb;

        $wpdb->query("
            DROP VIEW {$wpdb->prefix}dt_geonames_reference 
        ");

        return;
    }

    /**
     * @throws \Exception
     */
    public function test() {
        global $wpdb;

//        $result = $wpdb->query( "SELECT COUNT(*) FROM {$wpdb->prefix}dt_geonames_reference" );
//        if ( $result < 1 ) {
//            throw new Exception( "Got error finding view '{$wpdb->prefix}dt_geonames_reference': $wpdb->last_error" );
//        }
    }

    public function get_expected_tables(): array {
        return array();
    }
}
