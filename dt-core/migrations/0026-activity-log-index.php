<?php

class Disciple_Tools_Migration_0026 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $meta_key_index_exists = $wpdb->query( $wpdb->prepare("
                select distinct index_name
                from information_schema.statistics
                where table_schema = %s
                and table_name = '$wpdb->dt_activity_log'
                and index_name like %s 
            ", DB_NAME, 'meta_key_index' ));
        if ( $meta_key_index_exists === 0 ){
            $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_activity_log` ADD INDEX meta_key_index (meta_key)" );
        }
    }

    public function down() {
        global $wpdb;
        $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_activity_log` DROP INDEX meta_key_index (meta_key)" );
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
                    PRIMARY KEY (`histid`),
                    key `object_id_index` (`object_id`),
                    key `meta_key_index` (`meta_key`)
                ) $charset_collate;"
        );
    }
}
