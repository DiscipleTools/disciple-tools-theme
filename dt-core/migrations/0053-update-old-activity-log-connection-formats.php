<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0053
 *
 * Updates old activity table connection log entry formats.
 */
class Disciple_Tools_Migration_0053 extends Disciple_Tools_Migration {
    public function up() {
        //skip this migration on a new install
        if ( dt_get_initial_install_meta( 'migration_number' ) > 52 ){
            return;
        }

        global $wpdb;

        // Identify old connection log entry formats.
        $old_connection_logs = $wpdb->get_results(
            // phpcs:disable
            $wpdb->prepare( "
                SELECT DISTINCT log.object_type AS post_type, log.meta_key AS p2p_key, log.field_type AS direction
                FROM $wpdb->dt_activity_log AS log
                WHERE log.object_subtype = 'p2p' AND log.field_type LIKE 'connection%'
            " ), ARRAY_A
            // phpcs:enable
        );

        // Iterate over identified connections.....
        foreach ( $old_connection_logs ?? [] as $log ){
            if ( isset( $log['post_type'], $log['p2p_key'], $log['direction'] ) ){
                $post_type = $log['post_type'];
                $p2p_key = $log['p2p_key'];
                $log_direction = $log['direction'];
                $directions = [ 'any' ];

                // Determine actual direction to be adopted.
                if ( strpos( $log_direction, 'to' ) !== false ){
                    $directions[] = 'to';

                } elseif ( strpos( $log_direction, 'from' ) !== false ){
                    $directions[] = 'from';
                }

                // Attempt to locate corresponding p2p connection field.
                $post_field_settings = DT_Posts::get_post_field_settings( $post_type, false );
                $connection_field = DT_Posts::get_post_field_settings_by_p2p( $post_field_settings, $p2p_key, $directions );
                if ( !empty( $connection_field ) && isset( $connection_field['key'] ) ){
                    $field_key = $connection_field['key'];

                    // Migrate common log records to new format.
                    $wpdb->query(
                        // phpcs:disable
                        $wpdb->prepare( "
                             UPDATE $wpdb->dt_activity_log
                             SET
                                 object_subtype = %s,
                                 object_note = %s,
                                 field_type = %s
                             WHERE object_type = %s
                                AND object_subtype = 'p2p'
                                AND field_type = %s
                                AND meta_key = %s
                         ", $field_key, $log_direction, 'connection', $post_type, $log_direction, $p2p_key )
                        // phpcs:enable
                    );
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
