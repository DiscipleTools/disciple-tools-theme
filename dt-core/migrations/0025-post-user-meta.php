<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0025 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $test = $wpdb->query( "
            CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dt_post_user_meta` (
                `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                `post_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                `meta_key` varchar(255),
                `meta_value` LONGTEXT,
                `date` datetime,
                PRIMARY KEY (`id`)
            ) $charset_collate;" //@phpcs:ignore
        );
        if ( !$test ){
             throw new Exception( 'Could not create table dt_post_user_meta' );
        }
    }

    public function down() {
        global $wpdb;

    }

    public function test() {
        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        return array(
            "{$wpdb->prefix}dt_post_user_meta" =>
                "CREATE TABLE `{$wpdb->prefix}dt_post_user_meta` (
                    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                    `post_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                    `meta_key` varchar(255) default null,
                    `meta_value` LONGTEXT,
                    `date` datetime default null,
                    PRIMARY KEY (`id`)
            ) $charset_collate;",
        );
    }
}
