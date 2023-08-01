<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0047
 * Set the meta data for when a instances was installed
 */
class Disciple_Tools_Migration_0047 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;

        $at_install = get_option( 'dt_initial_install_meta' );


        if ( empty( $at_install ) ){
            $first_activity_time = $wpdb->get_var( "SELECT MIN(hist_time) FROM $wpdb->dt_activity_log" );
            update_option( 'dt_initial_install_meta', [
                'time' => $first_activity_time,
                'migration_number' => 0,
                'theme_version' => 0,
            ] );
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
