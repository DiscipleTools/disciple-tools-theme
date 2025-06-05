<?php
/**
 * DT Import Feature
 * Main plugin file for CSV import functionality
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Main DT CSV Import Class (renamed to avoid conflicts)
 */
class DT_Theme_CSV_Import {
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( 'init', [ $this, 'init' ] );
        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
    }

    public function init() {
        // Only load if DT is active
        if ( !class_exists( 'Disciple_Tools' ) ) {
            return;
        }

        // Load required files
        $this->load_dependencies();

        // Register the background import action hook
        add_action( 'dt_csv_import_execute', [ DT_CSV_Import_Processor::class, 'execute_import' ] );

        // Initialize admin interface if in admin
        if ( is_admin() ) {
            $this->init_admin();
        }
    }

    private function load_dependencies() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/dt-import-utilities.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/dt-import-validators.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/dt-import-geocoding.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/dt-import-field-handlers.php';
        require_once plugin_dir_path( __FILE__ ) . 'admin/dt-import-mapping.php';
        require_once plugin_dir_path( __FILE__ ) . 'admin/dt-import-processor.php';
        require_once plugin_dir_path( __FILE__ ) . 'ajax/dt-import-ajax.php';

        if ( is_admin() ) {
            require_once plugin_dir_path( __FILE__ ) . 'admin/dt-import-admin-tab.php';
        }
    }

    private function init_admin() {
        DT_CSV_Import_Admin_Tab::instance();
    }

    public function activate() {
        // Create upload directory for temporary CSV files
        $upload_dir = wp_upload_dir();
        $dt_import_dir = $upload_dir['basedir'] . '/dt-import-temp/';

        if ( !file_exists( $dt_import_dir ) ) {
            wp_mkdir_p( $dt_import_dir );

            // Create .htaccess file to prevent direct access
            $htaccess_content = "Order deny,allow\nDeny from all\n";
            file_put_contents( $dt_import_dir . '.htaccess', $htaccess_content );
        }
    }

    public function deactivate() {
        // Clean up temporary files
        $this->cleanup_temp_files();
    }

    private function cleanup_temp_files() {
        $upload_dir = wp_upload_dir();
        $dt_import_dir = $upload_dir['basedir'] . '/dt-import-temp/';

        if ( file_exists( $dt_import_dir ) ) {
            $files = glob( $dt_import_dir . '*' );
            foreach ( $files as $file ) {
                if ( is_file( $file ) ) {
                    unlink( $file );
                }
            }
        }

        // Clean up old import sessions (older than 24 hours) from dt_reports table
        global $wpdb;

        // Get old import sessions to clean up their files
        $old_sessions = $wpdb->get_results($wpdb->prepare(
            "SELECT payload FROM $wpdb->dt_reports 
             WHERE type = 'import_session' 
             AND timestamp < %d",
            strtotime( '-24 hours' )
        ), ARRAY_A);

        // Clean up associated files
        foreach ( $old_sessions as $session ) {
            if ( !empty( $session['payload'] ) ) {
                $payload = maybe_unserialize( $session['payload'] );
                if ( isset( $payload['file_path'] ) && file_exists( $payload['file_path'] ) ) {
                    unlink( $payload['file_path'] );
                }
            }
        }

        // Delete old import session records
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $wpdb->dt_reports 
             WHERE type = 'import_session' 
             AND timestamp < %d",
            strtotime( '-24 hours' )
        ));
    }
}

// Initialize the plugin
DT_Theme_CSV_Import::instance();
