<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0016
 *
 * @version_added 1.30.2
 */
class DT_Mapping_Module_Migration_0016 extends DT_Mapping_Module_Migration
{

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        $location_grid_rows = (int) $wpdb->get_var( "SELECT count(*) FROM $wpdb->dt_location_grid WHERE grid_id < 1000000000;" );
        if ( empty( $location_grid_rows ) ) {
            // delete
            delete_option( 'dt_mapping_module_migration_lock' );
            delete_option( 'dt_mapping_module_migrate_last_error' );
            delete_option( 'dt_mapping_module_migration_number' );
            delete_transient( 'dt_mapping_module_migration_lock' );
            try {
                DT_Mapping_Module_Migration_Engine::migrate( DT_Mapping_Module_Migration_Engine::$migration_number );
            } catch ( Throwable $e ) {
                $migration_error = new WP_Error( 'migration_error', 'Migration engine for mapping module failed to migrate.', [ 'error' => $e ] );
                dt_write_log( $migration_error );
            }
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
    public function get_expected_tables(): array
    {
        return [];
    }

    /**
     * Test function
     */
    public function test() {
    }

}
