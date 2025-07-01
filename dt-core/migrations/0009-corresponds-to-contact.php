<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0009 extends Disciple_Tools_Migration {
    public function up() {
        DT_User_Hooks_And_Configuration::create_contacts_for_existing_users();
    }

    public function down() {
        return;
    }

    public function test() {
        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        return array();
    }
}
