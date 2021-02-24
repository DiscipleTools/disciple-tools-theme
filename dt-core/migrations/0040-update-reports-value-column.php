<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0040
 *
 * Updates the value column of reports to bigint 22 from int 11.
 */
class Disciple_Tools_Migration_0040 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;
        $wpdb->query( "ALTER TABLE $wpdb->dt_reports CHANGE `value` `value` BIGINT(22)  NOT NULL  DEFAULT '0';" );
        $wpdb->query( "ALTER TABLE $wpdb->dt_reports ADD INDEX (`value`);" );
    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
