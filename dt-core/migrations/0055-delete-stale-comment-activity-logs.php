<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0055
 *
 * Delete stale comment activity logs.
 */
class Disciple_Tools_Migration_0055 extends Disciple_Tools_Migration {
    public function up() {
        //skip this migration on a new install
        if ( dt_get_initial_install_meta( 'migration_number' ) > 54 ){
            return;
        }

        global $wpdb;

        // Delete stale comment activity logs.
        $wpdb->query(
            $wpdb->prepare( "
                DELETE FROM $wpdb->dt_activity_log AS log
                WHERE log.action = 'comment'
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
