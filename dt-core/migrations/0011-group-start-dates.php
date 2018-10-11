<?php

class Disciple_Tools_Migration_0011 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $groups = $wpdb->get_results( "
            SELECT * FROM $wpdb->posts p
            WHERE p.ID NOT IN (
                SELECT  pm.post_id 
                FROM $wpdb->postmeta pm
                WHERE pm.meta_key = 'start_date'
            )            
            AND p.post_type = 'groups'
        ", ARRAY_A);

        foreach ( $groups as $group ){
            update_post_meta( $group['ID'], 'start_date', strtotime( $group["post_date"] ) );
        }
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
