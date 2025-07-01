<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0016 extends Disciple_Tools_Migration {
    public function up() {
        //skip this migration on a new install
        if ( dt_get_initial_install_meta( 'migration_number' ) > 16 ){
            return;
        }

        //rename field
        global $wpdb;
        $wpdb->get_results("
            UPDATE $wpdb->postmeta
            SET meta_value = '1'
            WHERE meta_key IN ( 'requires_update', 'accepted' )
            AND meta_value = 'yes'
        ", ARRAY_A);
        $wpdb->get_results("
            UPDATE $wpdb->postmeta
            SET meta_value = ''
            WHERE meta_key IN ( 'requires_update', 'accepted' )
            AND meta_value = 'no'
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
