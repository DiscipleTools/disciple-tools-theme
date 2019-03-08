<?php

/**
 * Class DT_Mapping_Module_Migration_0005
 *
 * @note    This migration adds a view table that combines the geoname tagged contacts and groups along with their
 *          higher level name divisions. (Country geonameid, Admin1 geonameid, Admin2 geonameid)
 *
 */
class DT_Mapping_Module_Migration_0005 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception
     */
    public function up() {
        global $wpdb;

        $wpdb->query("
            CREATE OR REPLACE VIEW {$wpdb->prefix}dt_geonames_reference AS    
            SELECT 
            CASE
                WHEN g.feature_code = 'PCLI' THEN g.geonameid
                WHEN g.feature_code = 'ADM1' THEN (SELECT parent_id FROM dt_geonames_hierarchy WHERE id = g.geonameid LIMIT 1)
                WHEN g.feature_code = 'ADM2' THEN (SELECT parent_id FROM dt_geonames_hierarchy WHERE id = (SELECT parent_id FROM dt_geonames_hierarchy WHERE id = g.geonameid LIMIT 1) LIMIT 1)
                WHEN g.feature_code = 'ADM3' THEN (SELECT parent_id FROM dt_geonames_hierarchy WHERE id = (SELECT parent_id FROM dt_geonames_hierarchy WHERE id = (SELECT parent_id FROM dt_geonames_hierarchy WHERE id = g.geonameid LIMIT 1) LIMIT 1) LIMIT 1)
                ELSE 'Unknown'
            END as PCLI,
            CASE
                WHEN g.feature_code = 'PCLI' THEN ''
                WHEN g.feature_code = 'ADM1' THEN g.geonameid
                WHEN g.feature_code = 'ADM2' THEN (SELECT parent_id FROM dt_geonames_hierarchy WHERE id = g.geonameid LIMIT 1)
                WHEN g.feature_code = 'ADM3' THEN (SELECT parent_id FROM dt_geonames_hierarchy WHERE id = (SELECT parent_id FROM dt_geonames_hierarchy WHERE id = g.geonameid LIMIT 1) LIMIT 1)
                ELSE 'Unknown'
            END as ADM1,
            CASE
                WHEN g.feature_code = 'PCLI' THEN ''
                WHEN g.feature_code = 'ADM1' THEN ''
                WHEN g.feature_code = 'ADM2' THEN g.geonameid
                WHEN g.feature_code = 'ADM3' THEN (SELECT parent_id FROM dt_geonames_hierarchy WHERE id = g.geonameid LIMIT 1)
                ELSE 'Unknown'
            END as ADM2,
            CASE
                WHEN g.feature_code = 'PCLI' THEN ''
                WHEN g.feature_code = 'ADM1' THEN ''
                WHEN g.feature_code = 'ADM2' THEN ''
                WHEN g.feature_code = 'ADM3' THEN g.geonameid
                ELSE 'Unknown'
            END as ADM3,
            p.meta_value as geonameid, 
            g.name,
            g.feature_code,
            p.post_id,
            pp.post_type, 
            IF (pp.post_type = 'contacts', cs.meta_value, gs.meta_value) as status,
            IF (pp.post_type = 'contacts', UNIX_TIMESTAMP(pp.post_date), gd.meta_value) as created_date,
            IF (pp.post_type = 'contacts', ce.meta_value, ge.meta_value) as end_date
            FROM {$wpdb->prefix}postmeta as p
            JOIN dt_geonames as g ON g.geonameid=p.meta_value
            JOIN {$wpdb->prefix}posts as pp ON p.post_id=pp.ID
            LEFT JOIN {$wpdb->prefix}postmeta as cs ON cs.post_id=p.post_id AND cs.meta_key = 'overall_status'
            LEFT JOIN {$wpdb->prefix}postmeta as gs ON gs.post_id=p.post_id AND gs.meta_key = 'group_status'
            LEFT JOIN {$wpdb->prefix}postmeta as gd ON gd.post_id=p.post_id AND gd.meta_key = 'start_date'
            LEFT JOIN {$wpdb->prefix}postmeta as ge ON ge.post_id=p.post_id AND ge.meta_key = 'end_date'
            LEFT JOIN {$wpdb->prefix}postmeta as ce ON ce.post_id=p.post_id AND ce.meta_key = 'last_modified' AND cs.meta_value = 'closed'
            WHERE p.meta_key = 'geonameid'  
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

        $result = $wpdb->query("SHOW TABLES LIKE '{$wpdb->prefix}dt_geonames_reference'");
        if ( $result !== 1 ) {
            throw new Exception( "Got error finding table '{$wpdb->prefix}dt_geonames_reference': $wpdb->last_error" );
        }
    }

    public function get_expected_tables(): array {
        return array();
    }
}
