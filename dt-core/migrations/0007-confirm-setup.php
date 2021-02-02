<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0007 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'p2p';
        if ($wpdb->get_var( "SHOW TABLES LIKE '$wpdb->p2p'" ) != $table_name ) {
            P2P_Storage::install();
        }

        require_once( get_template_directory() . '/dt-core/configuration/class-roles.php' );
        Disciple_Tools_Roles::instance()->set_roles_if_needed();

        /** Initialize default dt site options */
        dt_get_option( 'dt_site_options' );
        dt_get_option( 'dt_site_custom_lists' );
        dt_get_option( 'base_user' );
        dt_get_option( 'location_levels' );

    }

    public function down() {
        return;
    }

    public function test() {
        $this->test_expected_tables();
    }


    public function get_expected_tables(): array {
        return array();
    }
}
