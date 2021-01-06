<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class Disciple_Tools_Migration_0027 extends Disciple_Tools_Migration {
    public function up() {
        $site_options = get_option( 'dt_site_options' );
        if ( isset( $site_options["notifications"] ) && !isset( $site_options["notifications"]["types"] ) ) {
            $site_options["notifications"] = [
                    "types" => $site_options["notifications"],
            ];
            update_option( "dt_site_options", $site_options );
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
