<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0056
 *
 * Update activity logs object subtype column for link & comm_channel fields.
 */
class Disciple_Tools_Migration_0056 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;

        // First, identify all unique post types with a field_update action.
        $activity_log_post_types = $wpdb->get_results( "SELECT DISTINCT object_type AS post_type FROM $wpdb->dt_activity_log WHERE action = 'field_update' ORDER BY object_type ASC", ARRAY_A );
        foreach ( $activity_log_post_types ?? [] as $activity_log_post_type ) {

            // Attempt to obtain a handle to corresponding field settings.
            $post_type = $activity_log_post_type['post_type'];
            $post_type_field_settings = DT_Posts::get_post_field_settings( $post_type, false, true );
            $link_settings = DT_Posts::get_field_settings_by_type( $post_type, 'link' ) ?? [];
            $comm_channel_settings = DT_Posts::get_field_settings_by_type( $post_type, 'communication_channel' ) ?? [];

            // If valid, attempt to identify all associated field types.
            if ( isset( $post_type_field_settings ) ) {

                $processed_subtypes = [];

                $activity_log_obj_subtypes = $wpdb->get_results( $wpdb->prepare( "SELECT object_subtype, field_type FROM $wpdb->dt_activity_log WHERE action = 'field_update' AND object_type = %s AND ( field_type != NULL OR field_type != '') AND field_type IN ('link', 'communication_channel')", $post_type ), ARRAY_A );
                foreach ( $activity_log_obj_subtypes ?? [] as $activity_log_obj_subtype ) {
                    $obj_subtype = $activity_log_obj_subtype['object_subtype'];
                    $field_type = $activity_log_obj_subtype['field_type'];

                    // Only process new hits.
                    if ( !isset( $processed_subtypes[ $obj_subtype ] ) ) {
                        switch ( $field_type ) {
                            case 'link':
                                foreach ( $link_settings as $link_field ) {

                                    // Find potential match.
                                    if ( strpos( $obj_subtype, $link_field ) !== false ) {
                                        foreach ( array_keys( $post_type_field_settings[$link_field]['default'] ?? [] ) as $default_key ){

                                            // As a final confirmation, also match on defaults.
                                            if ( strpos( $obj_subtype, $default_key ) !== false ){
                                                $processed_subtypes[ $obj_subtype ] = [
                                                    'subtype' => $obj_subtype,
                                                    'field_key' => $link_field,
                                                    'field_type' => $field_type
                                                ];
                                            }
                                        }
                                    }
                                }
                                break;

                            case 'communication_channel':
                                foreach ( $comm_channel_settings as $comm_field ) {

                                    // Find potential match.
                                    if ( strpos( $obj_subtype, $comm_field ) !== false ) {
                                        $processed_subtypes[ $obj_subtype ] = [
                                            'subtype' => $obj_subtype,
                                            'field_key' => $comm_field,
                                            'field_type' => $field_type
                                        ];
                                    }
                                }
                                break;
                        }
                    }
                }

                // Update all identified subtype field keys.
                foreach ( $processed_subtypes as $subtype_key => $identified ) {
                    $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->dt_activity_log SET object_subtype = %s WHERE action = 'field_update' AND object_type = %s AND field_type = %s AND object_subtype = %s", $identified['field_key'], $post_type, $identified['field_type'], $identified['subtype'] ) );
                }
            }
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
