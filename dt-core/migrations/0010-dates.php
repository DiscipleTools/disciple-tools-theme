<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0010 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $dates_meta = $wpdb->get_results( "
            SELECT * FROM $wpdb->postmeta pm
            WHERE
            ( pm.meta_key = 'start_date' OR pm.meta_key = 'end_date' OR pm.meta_key = 'baptism_date' )
            AND meta_value != ''
        ", ARRAY_A);

        foreach ( $dates_meta as $meta ){
            if ( !is_numeric( $meta["meta_value"] ) ){
                $date = strtotime( $meta["meta_value"] );
                update_post_meta( $meta["post_id"], $meta["meta_key"], $date );
            }
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
