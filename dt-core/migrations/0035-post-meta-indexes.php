<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

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
                ADD INDEX meta_key_meta_value_post_id_dt (meta_key(100),meta_value(100),post_id)
            ");
            if ( !$alter ){
                throw new Exception( 'Could not alter table postmeta' );
            }
        }
        $meta_key_index_exists = $wpdb->query( $wpdb->prepare("
            select distinct index_name
            from information_schema.statistics
            where table_schema = %s
            and table_name = '{$wpdb->postmeta}'
            and index_name like %s
        ", DB_NAME, 'meta_key_post_id_dt' ));
        if ( $meta_key_index_exists === 0 ){
            $alter = $wpdb->query( "
                ALTER TABLE `{$wpdb->postmeta}`
                ADD INDEX meta_key_post_id_dt (meta_key(100),post_id);
            ");
            if ( !$alter ){
                throw new Exception( 'Could not alter table postmeta' );
            }
        }
        $meta_key_index_exists = $wpdb->query( $wpdb->prepare("
            select distinct index_name
            from information_schema.statistics
            where table_schema = %s
            and table_name = '{$wpdb->postmeta}'
            and index_name like %s
        ", DB_NAME, 'meta_key_index' ));
        if ( $meta_key_index_exists === 1 ){
            $alter = $wpdb->query( "
                ALTER TABLE `{$wpdb->postmeta}`
                DROP INDEX `meta_key_index`
            ");
            if ( !$alter ){
                throw new Exception( 'Could not alter table postmeta' );
            }
        }

        // activity_log table

        $index_exists = $wpdb->query( $wpdb->prepare("
            select distinct index_name
            from information_schema.statistics
            where table_schema = %s
            and table_name = '{$wpdb->dt_activity_log}'
            and index_name like %s
        ", DB_NAME, 'dt_user_id_action_object_type' ));
        if ( $index_exists === 0 ){
            $alter = $wpdb->query( "
                ALTER TABLE `{$wpdb->dt_activity_log}`
                ADD INDEX `dt_user_id_action_object_type` (user_id,action(100),object_type(100))
            ");
            if ( !$alter ){
                throw new Exception( 'Could not alter table dt_activity_log' );
            }
        }
        $index_exists = $wpdb->query( $wpdb->prepare("
            select distinct index_name
            from information_schema.statistics
            where table_schema = %s
            and table_name = '{$wpdb->dt_activity_log}'
            and index_name like %s
        ", DB_NAME, 'dt_meta_key_object_type' ));
        if ( $index_exists === 0 ){
            $alter = $wpdb->query( "
                ALTER TABLE `{$wpdb->dt_activity_log}`
                ADD INDEX dt_meta_key_object_type (meta_key(100),object_type(100));
            ");
            if ( !$alter ){
                throw new Exception( 'Could not alter table dt_activity_log' );
            }
        }
        $index_exists = $wpdb->query( $wpdb->prepare("
            select distinct index_name
            from information_schema.statistics
            where table_schema = %s
            and table_name = '{$wpdb->dt_activity_log}'
            and index_name like %s
        ", DB_NAME, 'user_id_index' ));
        if ( $index_exists === 1 ){
            $alter = $wpdb->query( "
                ALTER TABLE `{$wpdb->dt_activity_log}`
                DROP INDEX `user_id_index`
            ");
            if ( !$alter ){
                throw new Exception( 'Could not alter table dt_activity_log' );
            }
        }
        $index_exists = $wpdb->query( $wpdb->prepare("
            select distinct index_name
            from information_schema.statistics
            where table_schema = %s
            and table_name = '{$wpdb->dt_activity_log}'
            and index_name like %s
        ", DB_NAME, 'meta_key_index' ));
        if ( $index_exists === 1 ){
            $alter = $wpdb->query( "
                ALTER TABLE `{$wpdb->dt_activity_log}`
                DROP INDEX `meta_key_index`
            ");
            if ( !$alter ){
                throw new Exception( 'Could not alter table dt_activity_log' );
            }
        }
    }

    public function down() {
    }

    public function test() {
    }


    public function get_expected_tables(): array {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        return array(
            "{$wpdb->prefix}postmeta" =>
                "CREATE TABLE `{$wpdb->postmeta}` (
                  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                  `meta_key` varchar(255) DEFAULT NULL,
                  `meta_value` longtext,
                  PRIMARY KEY (`meta_id`),
                  KEY `post_id` (`post_id`),
                  KEY `meta_key` (`meta_key`(191)),
                  KEY `meta_key_meta_value_post_id_dt` (`meta_key`(100),`meta_value`(100),`post_id`),
                  KEY `meta_key_post_id_dt` (`meta_key`(100),`post_id`)
            ) $charset_collate;",
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
                    key `meta_key_index` (`meta_key`),
                    key `object_type_index` (`object_type`),
                    key `user_id_index` (`user_id`),
                    key `dt_user_id_action_object_type` (`user_id`,`action`(100),`object_type`(100)),
                    key `dt_meta_key_object_type` (`meta_key`(100),`object_type`(100))
                ) $charset_collate;"
        );
    }
}
