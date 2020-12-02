<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0002 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_notifications` ADD field_key VARCHAR(255) NOT NULL DEFAULT ''" );
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_notifications` ADD field_value VARCHAR(255) NOT NULL DEFAULT ''" );
    }

    public function down() {
        global $wpdb;
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_notifications` DROP COLUMN field_key" );
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_notifications` DROP COLUMN field_value" );
    }

    public function test() {
        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        return array(
            "{$wpdb->prefix}dt_notifications" =>
                "CREATE TABLE `{$wpdb->prefix}dt_notifications` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
                    `source_user_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
                    `post_id` bigint(20) UNSIGNED NOT NULL DEFAULT '0',
                    `secondary_item_id` bigint(20) DEFAULT NULL,
                    `notification_name` varchar(75) NOT NULL DEFAULT '0',
                    `notification_action` varchar(75) NOT NULL DEFAULT '0',
                    `notification_note` varchar(255) DEFAULT NULL,
                    `date_notified` DATETIME NOT NULL,
                    `is_new` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
                    `field_key` VARCHAR(255) NOT NULL DEFAULT '',
                    `field_value` VARCHAR(255) NOT NULL DEFAULT '',
                    PRIMARY KEY (`id`)
            ) $charset_collate;"
        );
    }
}
