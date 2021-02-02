<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0019
 *
 */
class Disciple_Tools_Migration_0019 extends Disciple_Tools_Migration
{
    public function up() {
        /**
         * Correction to this migration made in 0020, and for backward compatibility with already installed systems,
         * this migration file is included as a placeholder so that the migration engine doesn't stall. The migration
         * engine does not allow you to skip numbers.
         */
    }

    public function down() {
        return;
    }

    public function test() {
    }

    public function get_expected_tables(): array
    {
        return [];
    }
}
