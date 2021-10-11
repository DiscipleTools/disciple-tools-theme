<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0040
 *
 * Adds index to activity table
 */
class Disciple_Tools_Migration_0045 extends Disciple_Tools_Migration {
    public function up() {
        wp_queue_install_tables();
    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
