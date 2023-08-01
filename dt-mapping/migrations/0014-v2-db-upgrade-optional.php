<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Mapping_Module_Migration_0014
 *
 * @version_added 1.30.2
 */
class DT_Mapping_Module_Migration_0014 extends DT_Mapping_Module_Migration {

    /**
     * @throws \Exception  Got error when creating table $name.
     */
    public function up() {
        global $wpdb;
        // test for a specific feature that was changed in v2 to see if the v2 dataset was installed in 0006. This will be true for installs after 1.30.2, and not true installs before 1.30.2.
        $is_v2 = $wpdb->get_var( "SELECT grid_id FROM {$wpdb->prefix}dt_location_grid WHERE grid_id = 100364199 AND latitude LIKE '%39.8097%'" );
        if ( empty( $is_v2 ) ) {
            global $wpdb;
            // drop tables
            $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
            $wpdb->query("
                DROP TABLE IF EXISTS `{$wpdb->prefix}dt_location_grid_upgrade`
            ");
            $wpdb->query("
                RENAME TABLE `{$wpdb->prefix}dt_location_grid` TO `{$wpdb->prefix}dt_location_grid_upgrade`;
            ");

            // delete
            delete_option( 'dt_mapping_module_migration_lock' );
            delete_option( 'dt_mapping_module_migrate_last_error' );
            delete_option( 'dt_mapping_module_migration_number' );
            delete_transient( 'dt_mapping_module_migration_lock' );

            // delete folder and downloads
            $dir = wp_upload_dir();
            $uploads_dir = trailingslashit( $dir['basedir'] );
            if ( file_exists( $uploads_dir . 'location_grid_download/location_grid.tsv.zip' ) ) {
                unlink( $uploads_dir . 'location_grid_download/location_grid.tsv.zip' );
            }
            if ( file_exists( $uploads_dir . 'location_grid_download/location_grid.tsv' ) ) {
                unlink( $uploads_dir . 'location_grid_download/location_grid.tsv' );
            }

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
    public function get_expected_tables(): array {
        return [];
    }

    /**
     * Test function
     */
    public function test() {
    }

}
