<?php

class Disciple_Tools_Migration_0007 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'p2p';
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            P2P_Storage::install();
        }
    }

    public function down() {
        global $wpdb;
        $wpdb->query( "DROP TABLE IF EXISTS $wpdb->p2p" );
        $wpdb->query( "DROP TABLE IF EXISTS $wpdb->p2pmeta" );
    }

    public function test() {
        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        return array();
    }
}
