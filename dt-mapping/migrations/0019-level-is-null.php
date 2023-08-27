<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0017
 * checks east and west are installed correctly
 */
class DT_Mapping_Module_Migration_0019 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        if ( ! isset( $wpdb->dt_location_grid ) ) {
            $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
        }

        $wpdb->query( "UPDATE $wpdb->dt_location_grid SET level = '0' WHERE level is NULL AND level_name = 'admin0'" );
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
        return array();
    }

    /**
     * Test function
     */
    public function test() {
    }
}
