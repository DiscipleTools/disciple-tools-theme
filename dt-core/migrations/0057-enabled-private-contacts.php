<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0057
 *
 * move the hide_personal_contact_type option to the contacts post type settings
 */
class Disciple_Tools_Migration_0057 extends Disciple_Tools_Migration {
    public function up() {
        //skip this migration on a new install
        if ( dt_get_initial_install_meta( 'migration_number' ) >= 57 ){
            return;
        }

        $contact_preferences = get_option( 'dt_contact_preferences', [] );
        $hide_personal_contact_type = $contact_preferences['hide_personal_contact_type'] ?? false;

        $custom_settings = get_option( 'dt_custom_post_types', [] );
        $contact_settings = $custom_settings['contacts'] ?? [];
        $contact_settings['enable_private_contacts'] = !$hide_personal_contact_type;
        $custom_settings['contacts'] = $contact_settings;
        update_option( 'dt_custom_post_types', $custom_settings );
        delete_option( 'dt_contact_preferences' );
    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
