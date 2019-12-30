<?php

class Disciple_Tools_Migration_0028 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $meta_key_index_exists = $wpdb->query( $wpdb->prepare("
            select distinct index_name
            from information_schema.statistics
            where table_schema = %s
            and table_name = '{$wpdb->prefix}dt_post_user_meta'
            and index_name like %s 
        ", DB_NAME, 'meta_key_index' ));
        if ( $meta_key_index_exists === 0 ){
            $alter = $wpdb->query( "
                ALTER TABLE `{$wpdb->prefix}dt_post_user_meta`
                ADD `category` varchar(255) NULL,
                ADD INDEX meta_key_index (meta_key),
                ADD INDEX category_index (category)
            ");
            if ( !$alter ){
                 throw new Exception( 'Could not alter table dt_post_user_meta' );
            }
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
                    `category` varchar(255) default null,
                    PRIMARY KEY (`id`),
                    key `meta_key_index` (`meta_key`),
                    key `category_index` (`category`)
            ) $charset_collate;",
        );
    }
}
