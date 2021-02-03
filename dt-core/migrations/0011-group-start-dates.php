<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0011 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        //make sure all groups have a start date and that it is correctly formatted.
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
        //make sure all churches have a church date.
        $churches_no_start_date = $wpdb->get_results( "
            SELECT * FROM $wpdb->posts p
            JOIN $wpdb->postmeta status ON ( p.ID = status.post_id AND status.meta_key = 'group_status' AND status.meta_value = 'active' )
            JOIN $wpdb->postmeta type ON ( p.ID = type.post_id AND type.meta_key = 'group_type' AND type.meta_value = 'church' )
            WHERE p.ID NOT IN (
                SELECT pm.post_id
                FROM $wpdb->postmeta pm
                WHERE pm.meta_key = 'church_start_date'
            )
            AND p.post_type = 'groups'

        ", ARRAY_A);

        foreach ( $churches_no_start_date as $church ){
            update_post_meta( $church['ID'], 'church_start_date', get_post_meta( $church["ID"], "start_date", true ) );
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
