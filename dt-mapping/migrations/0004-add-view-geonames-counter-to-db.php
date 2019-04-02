<?php

/**
 * Class DT_Mapping_Module_Migration_0004
 *
 * @note    This migration adds a view table that combines the geoname tagged contacts and groups along with their
 *          higher level name divisions. (Country geonameid, Admin1 geonameid, Admin2 geonameid)
 *
 */
class DT_Mapping_Module_Migration_0004 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception
     */
    public function up() {
        global $wpdb;

        $wpdb->query("
            CREATE OR REPLACE ALGORITHM = MERGE VIEW {$wpdb->prefix}dt_geonames_counter AS    
            SELECT
                g.country_geonameid,
                g.admin1_geonameid,
                g.admin2_geonameid,
                g.admin3_geonameid,
                g.geonameid,
   				g.level,
                p.post_id,
                IF (cu.meta_value = 'user', 'users', pp.post_type) as type, 
                IF (pp.post_type = 'contacts', cs.meta_value, gs.meta_value) as status,
                IF (pp.post_type = 'contacts', UNIX_TIMESTAMP(pp.post_date), gd.meta_value) as created_date,
                IF (pp.post_type = 'contacts', ce.meta_value, ge.meta_value) as end_date
            FROM {$wpdb->prefix}postmeta as p
                JOIN {$wpdb->prefix}posts as pp ON p.post_id=pp.ID
                LEFT JOIN {$wpdb->prefix}dt_geonames as g ON g.geonameid=p.meta_value             
                LEFT JOIN {$wpdb->prefix}postmeta as cu ON cu.post_id=p.post_id AND cu.meta_key = 'type'
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
            DROP VIEW {$wpdb->prefix}dt_geonames_counter 
        ");

        return;
    }

    /**
     * @throws \Exception
     */
    public function test() {
        global $wpdb;

        $result = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}dt_geonames_counter" );
        if ( $result < 0 ) {
            throw new Exception( "Got error finding table '{$wpdb->prefix}dt_geonames_counter': $wpdb->last_error" );
        }
    }

    public function get_expected_tables(): array {
        return array();
    }
}
