<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Migration_0053
 *
 * Updates old activity table connection log entry formats.
 */
class Disciple_Tools_Migration_0053 extends Disciple_Tools_Migration {
    public function up() {
        global $wpdb;

        // Identify old connection log entry formats.
        $old_connection_logs = $wpdb->get_results(
            // phpcs:disable
            $wpdb->prepare( "
                SELECT
                    log.histid AS id, log.object_type AS post_type, log.meta_key AS p2p_key, log.field_type AS direction
                FROM $wpdb->dt_activity_log AS log
                WHERE log.object_subtype = 'p2p'
                    AND log.field_type LIKE 'connection%'
            " ), ARRAY_A
            // phpcs:enable
        );

        // Iterate over identified connections.....
        $sql_queries = [];
        foreach ( $old_connection_logs ?? [] as $log ){
            if ( isset( $log['id'], $log['post_type'], $log['p2p_key'], $log['direction'] ) ){
                $log_id = $log['id'];
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
                    //...$field_key = $connection_field['key'];

                    $sql_queries[] = [
                        'log_id' => $log_id,
                        'field_key' => $connection_field['key'],
                        'log_direction' => $log_direction
                    ];
                }
            }
        }

        // Batch and dispatch update queries.
        $batch_limit = 1000;
        $batch_size = ( count( $sql_queries ) < $batch_limit ) ? count( $sql_queries ) : $batch_limit;
        $batch_counter = 0;
        $obj_subtype_sql = [];
        $obj_note_sql = [];
        $hist_ids = [];
        foreach ( $sql_queries as $log ) {
            $hist_ids[] = $log['log_id'];
            $obj_subtype_sql[] = 'WHEN ' . $log['log_id'] . " THEN '" . $log['field_key'] . "'";
            $obj_note_sql[] = 'WHEN ' . $log['log_id'] . " THEN '" . $log['log_direction'] . "'";

            if ( ++$batch_counter >= $batch_size ) {
                // phpcs:disable
                $sql = "
                    UPDATE $wpdb->dt_activity_log
                    SET
                        object_subtype = ( CASE histid ". implode(' ', $obj_subtype_sql ) ." END ),
                        object_note = ( CASE histid ". implode(' ', $obj_note_sql ) ." END ),
                        field_type = 'connection'
                    WHERE histid IN ( ". implode( ',', $hist_ids ) ." );
                ";

                // Dispatch batch....
                $wpdb->query( $sql );

                // Reset counters and containers.
                $batch_counter = 0;
                $obj_subtype_sql = [];
                $obj_note_sql = [];
                $hist_ids = [];
                // phpcs:enable
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
