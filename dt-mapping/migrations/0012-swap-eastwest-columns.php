<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0012
 */
class DT_Mapping_Module_Migration_0012 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        if ( ! isset( $wpdb->dt_location_grid ) ) {
            $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
        }


        // SELECT COUNT(*) FROM wp_3_dt_location_grid WHERE east_longitude < west_longitude
        $count = $wpdb->query( "SELECT COUNT(*) FROM $wpdb->dt_location_grid WHERE east_longitude < west_longitude" );
        if ( $count > 0 ){
            // alter columns
            $wpdb->query( "ALTER TABLE $wpdb->dt_location_grid CHANGE `east_longitude` `west_longitude_new` FLOAT NULL DEFAULT NULL" );
            $wpdb->query( "ALTER TABLE $wpdb->dt_location_grid CHANGE `west_longitude` `east_longitude` FLOAT NULL DEFAULT NULL" );
            $wpdb->query( "ALTER TABLE $wpdb->dt_location_grid CHANGE `west_longitude_new` `west_longitude` FLOAT NULL DEFAULT NULL" );

            $wpdb->query( "ANALYZE TABLE $wpdb->dt_location_grid" );
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
    public function get_expected_tables(): array {
        return [];
    }

    /**
     * Test function
     */
    public function test() {
    }

}
