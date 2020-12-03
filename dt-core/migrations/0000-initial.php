<?php
declare(strict_types=1);

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


require_once( 'abstract.php' );

/**
 * Class Disciple_Tools_Migration_0000
 */
class Disciple_Tools_Migration_0000 extends Disciple_Tools_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $table) {
            $rv = $wpdb->query( $table ); // WPCS: unprepared SQL OK
            if ( $rv == false ) {
                throw new Exception( "Got error when creating table $name: $wpdb->last_error" );
            }
        }
    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {
        global $wpdb;
        $expected_tables = $this->get_expected_tables();
        foreach ( $expected_tables as $name => $table) {
            $rv = $wpdb->query( "DROP TABLE `{$name}`" ); // WPCS: unprepared SQL OK
            if ( $rv == false ) {
                throw new Exception( "Got error when dropping table $name: $wpdb->last_error" );
            }
        }
    }

    /**
     * @return array
     */
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
                    PRIMARY KEY (`histid`)
                ) $charset_collate;",
            "{$wpdb->prefix}dt_reports" =>
                "CREATE TABLE `{$wpdb->prefix}dt_reports` (
                    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `report_date` DATE NOT NULL,
                    `report_source` VARCHAR(55) NOT NULL,
                    `report_subsource` VARCHAR(100) NOT NULL,
                    `focus` VARCHAR(25) DEFAULT NULL,
                    `category` VARCHAR(25) DEFAULT NULL,
                    PRIMARY KEY (`id`)
            ) $charset_collate;",
            "{$wpdb->prefix}dt_reportmeta" =>
                "CREATE TABLE `{$wpdb->prefix}dt_reportmeta` (
                    `meta_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `report_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                    `meta_key` VARCHAR(255) NOT NULL,
                    `meta_value` LONGTEXT,
                    PRIMARY KEY (`meta_id`)
            ) $charset_collate;",
            "{$wpdb->prefix}dt_share" =>
                "CREATE TABLE `{$wpdb->prefix}dt_share` (
                    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                    `post_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                    `meta` LONGTEXT,
                    PRIMARY KEY (`id`)
            ) $charset_collate;",
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
                    PRIMARY KEY (`id`)
            ) $charset_collate;",
        );
    }

    /**
     * Test function
     */
    public function test() {
        $this->test_expected_tables();
    }

}
