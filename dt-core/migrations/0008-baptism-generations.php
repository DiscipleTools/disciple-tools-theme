<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0008 extends Disciple_Tools_Migration {
    public function up() {
        //skip is this is a new install
        if ( get_option( 'dt_at_install', [] )['migration_number'] ?? 0 > 8 ){
            return;
        }

        Disciple_Tools_Counter_Baptism::save_all_contact_generations();
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
