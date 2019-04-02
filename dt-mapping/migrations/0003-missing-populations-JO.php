<?php
/**
 * Class DT_Mapping_Module_Migration_0003
 *
 * @note  Add missing populations for Jordan
 */

class DT_Mapping_Module_Migration_0003 extends DT_Mapping_Module_Migration {
    public function up() {
        $ms_migration_number = false;
        if ( is_multisite() ) {
            $ms_migration_number = (int) get_site_option( 'dt_mapping_module_multisite_migration_number', true );
        }

        if ( ! is_multisite() || $ms_migration_number < 3 ) { // note: match the migration number to the class number
            global $wpdb;

            // sql for updating country names in {$wpdb->prefix}dt_geonames
            $sql = [];
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 176726 WHERE geonameid = 8621687";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 52714 WHERE geonameid = 8621685";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 73477 WHERE geonameid = 8621686";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 36670 WHERE geonameid = 8621688";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 152122 WHERE geonameid = 7910931";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 8152 WHERE geonameid = 8621696";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 54867 WHERE geonameid = 8621721";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 95124 WHERE geonameid = 8621720";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 29407 WHERE geonameid = 8621695";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 10896 WHERE geonameid = 8621719";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 16806 WHERE geonameid = 8621694";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 101377 WHERE geonameid = 8621697";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 10243 WHERE geonameid = 8621730";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 25245 WHERE geonameid = 8621741";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 60803 WHERE geonameid = 8621739";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 99231 WHERE geonameid = 8621635";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 247031 WHERE geonameid = 8621683";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 7490 WHERE geonameid = 8621684";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 196196 WHERE geonameid = 8621634";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 743980 WHERE geonameid = 8621761";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 118004 WHERE geonameid = 8621765";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 84370 WHERE geonameid = 8621763";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 582659 WHERE geonameid = 9915282";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 956104 WHERE geonameid = 8621743";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 129650 WHERE geonameid = 8621764";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 855955 WHERE geonameid = 8621742";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 169434 WHERE geonameid = 8621740";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 367370 WHERE geonameid = 8621762";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 80713 WHERE geonameid = 8621691";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 481900 WHERE geonameid = 8621690";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 802265 WHERE geonameid = 8621689";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 802265 WHERE geonameid = 7910933";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 122330 WHERE geonameid = 8621604";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 161505 WHERE geonameid = 8621603";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 78427 WHERE geonameid = 8621606";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 42571 WHERE geonameid = 8621630";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 238502 WHERE geonameid = 8621601";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 51501 WHERE geonameid = 8621607";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 204313 WHERE geonameid = 8621605";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 131797 WHERE geonameid = 8621599";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 739212 WHERE geonameid = 8621602";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 19828 WHERE geonameid = 8621728";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 17323 WHERE geonameid = 8621737";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 19279 WHERE geonameid = 8621727";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 87652 WHERE geonameid = 8621722";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 19279 WHERE geonameid = 11189149";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 38260 WHERE geonameid = 8621632";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 137820 WHERE geonameid = 8621631";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 29142 WHERE geonameid = 8621729";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 159018 WHERE geonameid = 8621738";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 237059 WHERE geonameid = 8621633";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 36422 WHERE geonameid = 8621693";
            $sql[] = "UPDATE {$wpdb->prefix}dt_geonames SET population = 152770 WHERE geonameid = 8621692";

            $results = [];
            $e = 0;
            $s = 0;
            foreach ( $sql as $query ) {
                $result = $wpdb->query( $query );
                if ( $result === false ) {
                    $results['error'] = $e++;
                    $results['error_statements'][] = $query;
                } else {
                    $results['success'] = $s++;
                }
            }

            dt_write_log( $results );

            if ( is_multisite() ) {
                update_site_option( 'dt_mapping_module_multisite_migration_number', 3 ); // set the migration number for multisite
            }
        }

    }

    public function down() {
        return;
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return array();
    }
}