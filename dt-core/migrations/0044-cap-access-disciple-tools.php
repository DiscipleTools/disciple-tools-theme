<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0044
 * Reset roles after creating access_disciple_tools capability
 */
class Disciple_Tools_Migration_0044 extends Disciple_Tools_Migration {
    public function up(){
        require_once( get_template_directory() . '/dt-core/setup-functions.php' );
        dt_setup_roles_and_permissions();
    }

    public function down() {
        global $wpdb;

    }

    public function test() {
//        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        return [];
    }
}
