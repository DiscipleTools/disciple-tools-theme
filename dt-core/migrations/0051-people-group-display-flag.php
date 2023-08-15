<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0051
 * Migrate people groups display tab flag.
 */
class Disciple_Tools_Migration_0051 extends Disciple_Tools_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up(){

        $dt_people_groups_display_tab = get_option( 'dt_people_groups_display_tab', false );
        $custom_post_types = get_option( 'dt_custom_post_types', [] );
        if ( !isset( $custom_post_types['peoplegroups'] ) ){
            $custom_post_types['peoplegroups'] = [
                'label_singular' => 'People Group',
                'label_plural' => 'People Groups',
                'hidden' => !$dt_people_groups_display_tab,
                'is_custom' => true
            ];
            update_option( 'dt_custom_post_types', $custom_post_types );
        }
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
