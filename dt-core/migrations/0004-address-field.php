<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0004 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE
                    `$wpdb->postmeta`
                    SET meta_key=CONCAT('contact_', meta_key)
                WHERE
                    meta_key LIKE %s",
                $wpdb->esc_like( 'address_' ) . '%'
            )
        );

    }

    public function down() {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE
                    `$wpdb->postmeta`
                SET meta_key=REPLACE(meta_key, 'contact_', '')
                WHERE
                    meta_key LIKE %s",
                $wpdb->esc_like( 'contact_address_' ) . '%'
            )
        );
    }

    public function test() {
        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        //no db alteration
        return array();
    }
}
