<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0046 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;

        if ( ! isset( $wpdb->dt_reports ) ) {
            $wpdb->dt_reports = $wpdb->prefix . 'dt_reports';
        }
        // add parent_id
        $wpdb->query( "ALTER TABLE $wpdb->dt_reports ADD `user_id` BIGINT(22) NULL DEFAULT NULL AFTER `id`" );

        $wpdb->query( "ALTER TABLE $wpdb->dt_reports CHANGE `post_id` `post_id` BIGINT(22) DEFAULT NULL;" );
    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
