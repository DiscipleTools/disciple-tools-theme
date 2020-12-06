<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0038 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;

        if ( ! isset( $wpdb->dt_reports ) ) {
            $wpdb->dt_reports = $wpdb->prefix . 'dt_reports';
        }
        // add parent_id
        $wpdb->query( "ALTER TABLE $wpdb->dt_reports ADD `parent_id` BIGINT(22) NULL DEFAULT NULL AFTER `id`" );

        // add_posttype
        $wpdb->query( "ALTER TABLE $wpdb->dt_reports ADD `post_type` VARCHAR(20) NULL DEFAULT NULL AFTER `post_id`" );

        // move hash
        $wpdb->query( "ALTER TABLE $wpdb->dt_reports MODIFY COLUMN `hash` VARCHAR(65) NULL DEFAULT NULL AFTER `timestamp`" );

        // add parent_id index
        $wpdb->query( "ALTER TABLE $wpdb->dt_reports ADD INDEX `parent_id` ( `parent_id` )" );

        // add post_type index
        $wpdb->query( "ALTER TABLE $wpdb->dt_reports ADD INDEX `post_type` ( `post_type` )" );

        // add hash index
        $wpdb->query( "ALTER TABLE $wpdb->dt_reports ADD INDEX `hash` ( `hash` )" );

        // rebuild indexes
        $wpdb->query( "ANALYZE TABLE $wpdb->dt_reports" );
    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
