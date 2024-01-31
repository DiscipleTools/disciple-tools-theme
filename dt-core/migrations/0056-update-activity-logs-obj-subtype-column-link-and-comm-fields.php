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

        // First, update all communication_channel field types.
        $wpdb->query( "UPDATE $wpdb->dt_activity_log
            SET object_subtype = CASE
              WHEN LOCATE('_', object_subtype, (CHAR_LENGTH(object_subtype) - 4)) > 0 THEN LEFT(object_subtype, (CHAR_LENGTH(object_subtype) - 4))
              ELSE object_subtype
            END
            WHERE action = 'field_update'
              AND field_type = 'communication_channel'
              AND object_subtype LIKE 'contact%_%'
              AND object_subtype NOT LIKE '%details'" );

        // Next, process all link field types -> identify all unique post types with a field_update action.
        $link_categories = [];
        $activity_log_post_types = $wpdb->get_results( "SELECT DISTINCT object_type AS post_type FROM $wpdb->dt_activity_log WHERE action = 'field_update' ORDER BY object_type ASC", ARRAY_A );
        foreach ( $activity_log_post_types ?? [] as $activity_log_post_type ) {
            $post_type = $activity_log_post_type['post_type'];
            $post_type_field_settings = DT_Posts::get_post_field_settings( $post_type, false, true );
            $link_settings = DT_Posts::get_field_settings_by_type( $post_type, 'link' ) ?? [];

            // If valid, attempt to identify all associated categories.
            if ( isset( $post_type_field_settings, $link_settings ) ){
                foreach ( $link_settings as $link_field ){
                    foreach ( array_keys( $post_type_field_settings[$link_field]['default'] ?? [] ) as $default_key ) {
                        if ( !in_array( $default_key, $link_categories ) ) {
                            $link_categories[] = $default_key;
                        }
                    }
                }
            }
        }

        // Build update sql accordingly, based on identified link categories.
        if ( !empty( $link_categories ) ) {
            $case_conditions = '';
            foreach ( $link_categories as $link_category ) {
                $case_conditions .= " WHEN LOCATE('link_field_', object_subtype) > 0 AND LOCATE('_$link_category', object_subtype) > 0 THEN TRIM(TRAILING '_$link_category' FROM SUBSTRING(object_subtype, CHAR_LENGTH('link_field_') + 1)) ";
            }

            // phpcs:disable
            // WordPress.WP.PreparedSQL.NotPrepared
            $wpdb->query( "UPDATE $wpdb->dt_activity_log
                SET object_subtype = CASE
                  $case_conditions
                  ELSE object_subtype
                END
                WHERE action = 'field_update'
                  AND field_type = 'link'" );
            // phpcs:enable
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
