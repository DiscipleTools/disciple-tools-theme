<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0056
 *
 * Add activity logs field key column.
 */
class Disciple_Tools_Migration_0056 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_activity_log` ADD field_key VARCHAR(255) NOT NULL DEFAULT ''" );
    }

    public function down() {
        global $wpdb;
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_activity_log` DROP COLUMN field_key" );
    }

    public function test() {
        $this->test_expected_tables();
    }

    public function get_expected_tables(): array {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        return array(
            "{$wpdb->prefix}dt_activity_log" =>
                "CREATE TABLE `{$wpdb->prefix}dt_activity_log` (
                    `histid` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_caps` varchar(70) NOT NULL DEFAULT 'guest',
                    `action` varchar(255) NOT NULL,
                    `object_type` varchar(255) NOT NULL,
                    `object_subtype` varchar(255) NOT NULL DEFAULT '',
                    `object_name` varchar(255) NOT NULL,
                    `object_id` int(11) NOT NULL DEFAULT '0',
                    `user_id` int(11) NOT NULL DEFAULT '0',
                    `hist_ip` varchar(55) NOT NULL DEFAULT '127.0.0.1',
                    `hist_time` int(11) NOT NULL DEFAULT '0',
                    `object_note` VARCHAR(255) NOT NULL DEFAULT '0',
                    `meta_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `meta_key` VARCHAR(100) NOT NULL DEFAULT '0',
                    `meta_value` VARCHAR(255) NOT NULL DEFAULT '0',
                    `meta_parent` BIGINT(20) NOT NULL DEFAULT '0',
                    `old_value` VARCHAR(255) NOT NULL DEFAULT '',
                    `field_type` VARCHAR(255) NOT NULL DEFAULT '',
                    `field_key` VARCHAR(255) NOT NULL DEFAULT '',
                    PRIMARY KEY (`histid`),
                    key `object_id_index` (`object_id`),
                    key `object_type_index` (`object_type`),
                    key `dt_user_id_action_object_type` (`user_id`,`action`(100),`object_type`(100)),
                    key `dt_meta_key_object_type` (`meta_key`,`object_type`(100)),
                    key `object_id_meta_key_meta_value` (`object_id`,`meta_key`,`meta_value`),
                    key `index_action` (`action`),
                    key `object_type_subtype_index` (`object_type`,`object_subtype`)
                ) $charset_collate;"
        );
    }
}
