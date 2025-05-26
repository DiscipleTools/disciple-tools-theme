<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0053
 *
 * Delete stale array field type activity logs.
 */
class Disciple_Tools_Migration_0054 extends Disciple_Tools_Migration {
    public function up() {
        //skip this migration on a new install
        if ( dt_get_initial_install_meta( 'migration_number' ) > 53 ){
            return;
        }

        global $wpdb;

        // Delete stale array field type activity logs.
        $wpdb->query(
            "
                DELETE FROM $wpdb->dt_activity_log
                WHERE action = 'field_update' AND field_type = 'array'
            "
        );
    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
