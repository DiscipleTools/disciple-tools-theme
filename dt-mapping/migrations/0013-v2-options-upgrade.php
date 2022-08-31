<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0013
 *
 * @version_added 1.30.2
 */
class DT_Mapping_Module_Migration_0013 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        $current = get_option( 'dt_location_grid_mirror' );
        if ( isset( $current['key'] ) && 'amazon' === $current['key'] ) {
            $array = [
                'key'   => 'amazon',
                'label' => 'Amazon',
                'url'   => 'https://location-grid-mirror-v2.s3.amazonaws.com/',
            ];
        }
        else {
            $array = [
                'key'   => 'google',
                'label' => 'Google',
                'url'   => 'https://storage.googleapis.com/location-grid-mirror-v2/',
            ];
        }
        update_option( 'dt_location_grid_mirror', $array, true );
    }

    /**
     * @throws \Exception  Got error when dropping table $name.
     */
    public function down() {

    }

    /**
     * @return array
     */
    public function get_expected_tables(): array {
        return [];
    }

    /**
     * Test function
     */
    public function test() {
    }

}
