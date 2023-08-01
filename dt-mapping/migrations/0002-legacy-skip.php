<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
/**
 * Legacy migration placeholder.
 */

class DT_Mapping_Module_Migration_0002 extends DT_Mapping_Module_Migration {
    /**
     * Install the data
     * @throws \Exception Failed to find correct records.
     */
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

