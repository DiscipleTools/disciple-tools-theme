<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0018
 *
 * @note    Previous to this migration we were tracking user ip addresses. Since we were not using this data
 *          and have no plans for this data, it becomes a personal identity liability to have it in the
 *          database. So this migration replaces all previous records of user ip address to 0. This is
 *          in addition to removing the tracking from the activity log api.
 */
class Disciple_Tools_Migration_0018 extends Disciple_Tools_Migration {
    public function up() {
        //rename field
        global $wpdb;
        $wpdb->query("
            UPDATE $wpdb->dt_activity_log SET hist_ip = 0;
        ");
    }

    public function down() {
        return;
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return array();
    }
}
