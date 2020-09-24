<?php

/**
 * Class Disciple_Tools_Migration_0035
 * Add indexed to the postmeta table to customize for D.T list queries
 */
class Disciple_Tools_Migration_0035 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $meta_key_index_exists = $wpdb->query( $wpdb->prepare("
            select distinct index_name
            from information_schema.statistics
            where table_schema = %s
            and table_name = '{$wpdb->postmeta}'
            and index_name like %s
        ", DB_NAME, 'meta_key_meta_value_post_id_dt' ));
        if ( $meta_key_index_exists === 0 ){
            $alter = $wpdb->query( "
                ALTER TABLE `{$wpdb->postmeta}`
                ADD INDEX meta_key_meta_value_post_id_dt (meta_key(100),meta_value(100),post_id),
                ADD INDEX meta_key_post_id_dt (meta_key(100),post_id);
            ");
            if ( !$alter ){
                throw new Exception( 'Could not alter table postmeta' );
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
            "{$wpdb->prefix}postmeta" =>
                "CREATE TABLE `{$wpdb->postmeta}` (
                  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                  `meta_key` varchar(255) NOT NULL,
                  `meta_value` longtext NOT NULL,
                  PRIMARY KEY (`meta_id`),
                  KEY `post_id` (`post_id`),
                  KEY `meta_key` (`meta_key`),
                  KEY `meta_key_post_id` (`meta_key`,`post_id`),
                  KEY `meta_key_meta_value_post_id` (`meta_key`(100),`meta_value`(100),`post_id`)
            ) $charset_collate;",
        );
    }
}
