<?php
/**
 * Home Screen Migration Utilities
 *
 * Handles migration of existing apps to include role-based access control.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class DT_Home_Migration
 *
 * Handles migration tasks for the Home Screen app.
 */
class DT_Home_Migration {

    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'init', [ $this, 'maybe_run_migrations' ], 5 );
    }

    /**
     * Check if migrations need to be run
     */
    public function maybe_run_migrations() {
        $migration_version = get_option( 'dt_home_migration_version', '0.0.0' );
        $current_version = '1.0.0';

        if ( version_compare( $migration_version, $current_version, '<' ) ) {
            $this->run_migrations( $migration_version, $current_version );
            update_option( 'dt_home_migration_version', $current_version );
        }
    }

    /**
     * Run migrations from old version to new version
     */
    private function run_migrations( $from_version, $to_version ) {
        error_log( "DT Home: Running migrations from {$from_version} to {$to_version}" );

        // Migration 1.0.0: Add role fields to existing apps
        if ( version_compare( $from_version, '1.0.0', '<' ) ) {
            $this->migrate_apps_add_role_fields();
        }
    }

    /**
     * Migrate existing apps to include role fields
     */
    private function migrate_apps_add_role_fields() {
        $apps_manager = DT_Home_Apps::instance();
        $apps = $apps_manager->get_all_apps();
        $updated = false;

        foreach ( $apps as $index => $app ) {
            $needs_update = false;

            // Add slug if missing
            if ( !isset( $app['slug'] ) || empty( $app['slug'] ) ) {
                $app['slug'] = sanitize_title( $app['title'] ?? 'app-' . $app['id'] );
                $needs_update = true;
            }

            // Add user_roles_type if missing
            if ( !isset( $app['user_roles_type'] ) ) {
                $app['user_roles_type'] = 'support_all_roles';
                $needs_update = true;
            }

            // Add roles array if missing
            if ( !isset( $app['roles'] ) || !is_array( $app['roles'] ) ) {
                $app['roles'] = [];
                $needs_update = true;
            }

            if ( $needs_update ) {
                $apps[$index] = $app;
                $updated = true;
            }
        }

        if ( $updated ) {
            update_option( 'dt_home_screen_apps', $apps );
            error_log( 'DT Home: Migrated ' . count( $apps ) . ' apps with role fields' );
        }
    }

    /**
     * Get migration status
     */
    public function get_migration_status() {
        $migration_version = get_option( 'dt_home_migration_version', '0.0.0' );
        $apps_manager = DT_Home_Apps::instance();
        $apps = $apps_manager->get_all_apps();
        
        $migrated_apps = 0;
        foreach ( $apps as $app ) {
            if ( isset( $app['slug'] ) && isset( $app['user_roles_type'] ) && isset( $app['roles'] ) ) {
                $migrated_apps++;
            }
        }

        return [
            'migration_version' => $migration_version,
            'total_apps' => count( $apps ),
            'migrated_apps' => $migrated_apps,
            'needs_migration' => $migrated_apps < count( $apps )
        ];
    }

    /**
     * Force run migrations (for admin use)
     */
    public function force_migration() {
        $this->migrate_apps_add_role_fields();
        update_option( 'dt_home_migration_version', '1.0.0' );
        return $this->get_migration_status();
    }
}

// Initialize the migration class
DT_Home_Migration::instance();
