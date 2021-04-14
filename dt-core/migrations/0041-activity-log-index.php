<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0040
 *
 * Adds index to activity table
 */
class Disciple_Tools_Migration_0041 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $object_type_index_exists = $wpdb->query( $wpdb->prepare("
                select distinct index_name
                from information_schema.statistics
                where table_schema = %s
                and table_name = '$wpdb->dt_activity_log'
                and index_name like %s
            ", DB_NAME, 'object_id_meta_key_meta_value' ));
        if ( $object_type_index_exists === 0 ){
            $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_activity_log` ADD INDEX object_id_meta_key_meta_value (object_id, meta_key, meta_value)" );
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
