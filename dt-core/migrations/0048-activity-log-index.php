<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0040
 *
 * Adds index to activity table
 */
class Disciple_Tools_Migration_0048 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $object_type_index_exists = $wpdb->query( $wpdb->prepare("
                select distinct index_name
                from information_schema.statistics
                where table_schema = %s
                and table_name = '$wpdb->dt_activity_log'
                and index_name like %s
            ", DB_NAME, 'index_action' ));
        if ( $object_type_index_exists === 0 ){
            $wpdb->query( "ALTER TABLE `{$wpdb->prefix}dt_activity_log` ADD INDEX index_action (action)" );
        }
    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return array();
    }
}
