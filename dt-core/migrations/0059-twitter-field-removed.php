<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0059
 *
 * Add the twitter field as a custom field instead of a default field for all old instances
 */
class Disciple_Tools_Migration_0059 extends Disciple_Tools_Migration {
    public function up() {
        //skip this migration on a new install
        if ( dt_get_initial_install_meta( 'migration_number' ) >= 59 ){
            return;
        }

        $custom_field_options = dt_get_option( 'dt_field_customizations' );
        $existing = isset( $custom_field_options['contacts']['twitter'] ) ? $custom_field_options['contacts']['twitter'] : [];

        //if already disabled then skip
        if ( isset( $existing['enabled'] ) && $existing['enabled'] === false ) {
            return;
        }

        //if not used then skip
        global $wpdb;
        $count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->postmeta WHERE meta_key LIKE 'contact_twitter%'" );
        if ( empty( $count ) ){
            return;
        }

        //create a record in the field customizations.
        $existing['name'] = $existing['name'] ?? 'Twitter';
        $existing['icon'] = $existing['icon'] ?? get_template_directory_uri() . '/dt-assets/images/twitter.svg?v=2';
        $existing['enabled'] = $existing['enabled'] ?? true;
        $existing['type'] = 'communication_channel';
        $existing['tile'] = $existing['tile'] ?? 'details';

        $custom_field_options['contacts']['contact_twitter'] = $existing;
        update_option( 'dt_field_customizations', $custom_field_options );
    }

    public function down() {
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
