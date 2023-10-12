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
            $wpdb->prepare( "
                DELETE FROM $wpdb->dt_activity_log AS log
                WHERE log.action = 'field_update' AND log.field_type = 'array'
            " ), ARRAY_A
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
