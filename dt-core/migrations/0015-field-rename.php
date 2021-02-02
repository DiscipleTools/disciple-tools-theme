<?php

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0015 extends Disciple_Tools_Migration {
    public function up() {
        //rename field
        global $wpdb;
        $wpdb->get_results("
            UPDATE $wpdb->postmeta
            SET meta_value = 'not_responding'
            WHERE meta_key  = 'reason_paused'
            AND meta_value = 'not-responding'
        ", ARRAY_A);
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
