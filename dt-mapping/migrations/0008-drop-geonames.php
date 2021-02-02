<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0007
 *
 * @note    Drop geonames table
 */


class DT_Mapping_Module_Migration_0008 extends DT_Mapping_Module_Migration {
    public function up() {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}dt_geonames" );
    }

    public function down() {
        return;
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}

