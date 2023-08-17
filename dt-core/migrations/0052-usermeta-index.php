<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0040
 *
 * Adds index to activity table
 */
class Disciple_Tools_Migration_0051 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $index_exists = $wpdb->query( $wpdb->prepare("
                select distinct index_name
                from information_schema.statistics
                where table_schema = %s
                and table_name = '$wpdb->usermeta'
                and index_name like %s
            ", DB_NAME, 'index_user_id_meta_key' ));
        if ( $index_exists === 0 ){
            $wpdb->query( "ALTER TABLE `{$wpdb->prefix}usermeta` ADD INDEX index_user_id_meta_key (user_id, meta_key)" );
        }
    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
