<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0015
 *
 * @version_added 1.30.2
 */
class DT_Mapping_Module_Migration_0015 extends DT_Mapping_Module_Migration
{

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $has_upgrade_table = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}dt_location_grid_upgrade';" );
        $location_grid_rows = (int) $wpdb->get_var( "SELECT count(*) FROM $wpdb->dt_location_grid" );
        if ( !empty( $has_upgrade_table ) && !empty( $location_grid_rows ) ) {
            $wpdb->query( "INSERT INTO `{$wpdb->prefix}dt_location_grid` SELECT * FROM `{$wpdb->prefix}dt_location_grid_upgrade` WHERE grid_id > 1000000000;" );
            $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}dt_location_grid_upgrade`;" );
        }
    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {

    }

    /**
     * @return array
     */
    public function get_expected_tables(): array
    {
        return [];
    }

    /**
     * Test function
     */
    public function test() {
    }

}
