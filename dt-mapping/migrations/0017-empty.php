<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0017
 * checks east and west are installed correctly
 */
class DT_Mapping_Module_Migration_0017 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {}

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
