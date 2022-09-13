<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Class DT_Mapping_Module_Migration_0007
 *
 * @note    Find any geonames records and convert them automatically
 * @version_added 0.22.1
 * @version_removed 1.30.2
 * Migration of geonames no longer necessary
 */


class DT_Mapping_Module_Migration_0007 extends DT_Mapping_Module_Migration {
    public function up() {

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

