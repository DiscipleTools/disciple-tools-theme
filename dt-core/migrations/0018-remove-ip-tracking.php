<?php

class Disciple_Tools_Migration_0018 extends Disciple_Tools_Migration {
    public function up() {
        //rename field
        global $wpdb;
        $wpdb->query("
            UPDATE wp_dt_activity_log SET hist_ip = 0;
        ");
    }

    public function down() {
        return;
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return array();
    }
}