<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Migrate legacy storage settings to the new single-connection option shape.
 *
 * Moves from:
 * - option 'dt_storage_connection_id'
 * - option 'dt_storage_connection_objects' (array or JSON; possibly associative)
 * into:
 * - option 'dt_storage_connection' (flat array for the selected connection)
 */
class Disciple_Tools_Migration_0061 extends Disciple_Tools_Migration {
    public function up() {
        $connection_id = get_option( 'dt_storage_connection_id' );
        $connection    = get_option( 'dt_storage_connection', [] );

        // Only migrate if we have a selected id and nothing already migrated
        if ( empty( $connection_id ) || !empty( $connection ) ) {
            return;
        }

        $objects = get_option( 'dt_storage_connection_objects', [] );

        // Normalize objects to an array of connections
        $decoded = [];
        if ( is_string( $objects ) && !empty( $objects ) ) {
            $maybe = json_decode( $objects, true );
            if ( is_array( $maybe ) ) { $decoded = $maybe; }
        } elseif ( is_array( $objects ) ) {
            $decoded = $objects;
        }

        // If decoded is an associative map (old format), convert to list
        if ( !empty( $decoded ) && array_values( $decoded ) !== $decoded ) {
            $decoded = array_values( $decoded );
        }

        // Find the selected connection and persist only that one
        if ( is_array( $decoded ) ) {
            $selected = [];
            foreach ( $decoded as $item ) {
                if ( isset( $item['id'] ) && $item['id'] === $connection_id ) {
                    $selected = $item;
                    break;
                }
            }
            if ( !empty( $selected ) ) {
                // Flatten provider-specific details into the root of the connection array
                $flat     = $selected;
                $provider = isset( $selected['type'] ) ? $selected['type'] : '';
                if ( !empty( $provider ) && isset( $selected[ $provider ] ) && is_array( $selected[ $provider ] ) ) {
                    $details = $selected[ $provider ];
                    unset( $flat['aws'], $flat['backblaze'], $flat['minio'] );
                    $flat = array_merge( $flat, $details );
                }
                update_option( 'dt_storage_connection', $flat );
            }
        }
    }

    public function down() {
        // No-op. We do not restore legacy options once migrated.
    }

    public function test() {
    }

    public function get_expected_tables(): array {
        return [];
    }
}
