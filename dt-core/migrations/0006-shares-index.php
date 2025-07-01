<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0006 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_share` ADD INDEX post_id_index (post_id)" );
    }

    public function down() {
        global $wpdb;
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_share` DROP INDEX post_id_index" );
    }

    public function test() {
        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        return array(
            "{$wpdb->prefix}dt_share" =>
                "CREATE TABLE `{$wpdb->prefix}dt_share` (
                    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                    `post_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
                    `meta` LONGTEXT,
                    PRIMARY KEY (`id`),
                    KEY `post_id_index` (`post_id`)
            ) $charset_collate;",
        );
    }
}
