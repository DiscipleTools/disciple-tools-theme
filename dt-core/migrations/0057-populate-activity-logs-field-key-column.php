<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0057
 *
 * Populate activity logs field key column.
 */
class Disciple_Tools_Migration_0057 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;

        // First, identify all unique post types with a field_update action.
        $activity_log_post_types = $wpdb->get_results( "SELECT DISTINCT object_type AS post_type FROM $wpdb->dt_activity_log WHERE action = 'field_update' ORDER BY object_type ASC", ARRAY_A );
        foreach ( $activity_log_post_types ?? [] as $activity_log_post_type ) {

            // Attempt to obtain a handle to corresponding field settings.
            $post_type = $activity_log_post_type['post_type'];
            $post_type_field_settings = DT_Posts::get_post_field_settings( $post_type, false, true );

            // If valid, attempt to identify all associated field types.
            if ( isset( $post_type_field_settings ) ) {
                $activity_log_field_types = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT field_type FROM $wpdb->dt_activity_log WHERE action = 'field_update' AND object_type = %s AND ( field_type != NULL OR field_type != '') ORDER BY field_type ASC", $post_type ), ARRAY_A );
                foreach ( $activity_log_field_types ?? [] as $activity_log_field_type ) {
                    $field_type = $activity_log_field_type['field_type'];

                    // Identify and update new activity log field_key column.
                    switch ( $field_type ) {
                        case 'text':
                        case 'textarea':
                        case 'date':
                        case 'datetime':
                        case 'boolean':
                        case 'key_select':
                        case 'multi_select':
                        case 'array':
                        case 'number':
                        case 'tags':
                        case 'user_select':
                        case 'location':
                        case 'location_meta':
                        case 'hash':
                            $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->dt_activity_log SET field_key = meta_key WHERE action = 'field_update' AND object_type = %s AND field_type = %s", $post_type, $field_type ) );
                            break;
                        case 'link':
                        case 'communication_channel':
                            foreach ( $post_type_field_settings ?? [] as $field_key => $field_setting ) {
                                $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->dt_activity_log SET field_key = %s WHERE action = 'field_update' AND object_type = %s AND field_type = %s AND meta_key LIKE %s", $field_key, $post_type, $field_type, '%' . $field_key . '%' ) );
                            }
                            break;
                    }
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
