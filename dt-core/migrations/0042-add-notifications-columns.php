<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0042 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;

        if ( ! isset( $wpdb->dt_notifications ) ) {
            $wpdb->dt_notifications = $wpdb->prefix . 'dt_notifications';
        }
        // add parent_id
        $wpdb->query( "ALTER TABLE $wpdb->dt_notifications ADD `notification_type` VARCHAR(20) NULL DEFAULT NULL AFTER `notification_name`" );

        // add_posttype
        $wpdb->query( "ALTER TABLE $wpdb->dt_notifications ADD `notification_sent` DATE NULL DEFAULT NULL AFTER `date_notified`" );

        // add parent_id index
        $wpdb->query( "ALTER TABLE $wpdb->dt_notifications ADD INDEX `notification_type` ( `notification_type` )" );

        // add post_type index
        $wpdb->query( "ALTER TABLE $wpdb->dt_notifications ADD INDEX `notification_sent` ( `notification_sent` )" );

        // add hash index
        $wpdb->query( "ALTER TABLE $wpdb->dt_notifications ADD INDEX `date_notified` ( `date_notified` )" );

        // rebuild indexes
        $wpdb->query( "ANALYZE TABLE $wpdb->dt_notifications" );
    }

    public function down() {
        global $wpdb;
        $wpdb->query( "ALTER TABLE $wpdb->dt_notifications DROP COLUMN `notification_type`" );
        $wpdb->query( "ALTER TABLE $wpdb->dt_notifications DROP COLUMN `notification_sent`" );
        $wpdb->query( "ALTER TABLE $wpdb->dt_notifications DROP INDEX `notification_type`" );
        $wpdb->query( "ALTER TABLE $wpdb->dt_notifications DROP INDEX `notification_sent`" );
        $wpdb->query( "ALTER TABLE $wpdb->dt_notifications DROP INDEX `date_notified`" );
        $wpdb->query( "ANALYZE TABLE $wpdb->dt_notifications" );
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
