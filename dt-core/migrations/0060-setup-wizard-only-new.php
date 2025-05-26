<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0059
 *
 * Only show the setup wizard on new installs
 */
class Disciple_Tools_Migration_0060 extends Disciple_Tools_Migration {
    public function up() {
        //skip this migration on a new install
        if ( dt_get_initial_install_meta( 'migration_number' ) >= 60 ){
            return;
        }

        update_option( 'dt_setup_wizard_completed', true );
    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
