<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0012 extends Disciple_Tools_Migration {
    public function up() {
        $site_options = dt_get_option( 'dt_site_options' );
        if ( isset( $site_options["notifications"]["milestones"]["label"] ) ){
            $site_options["notifications"]["milestones"]["label"] = __( 'Contact Milestones and Group Health metrics', 'disciple_tools' );
            update_option( "dt_site_options", $site_options );

        }
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
