<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0011
 * Migrates contact user locations to usermeta table for new user locations system.
 *
 * @version_added 0.31.0
 * @version_removed 1.30.2
 * Unnecessary contact location migration. Potentially disruptive in newer systems
 */
class DT_Mapping_Module_Migration_0011 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {

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
