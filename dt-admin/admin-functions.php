<?php
/**
 * Disciple Tools Admin Functions
 *
 * @package  Disciple.Tools
 * @since    1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class DT_Admin
 */
class DT_Admin {
    
    private static $_instance = null;
    
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
        add_action( 'init', [ $this, 'init' ] );
    }
    
    public function init() {
        // Initialize admin functionality
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts() {
        // Only load on admin pages
        if ( ! $this->is_admin_page() ) {
            return;
        }
        
        // Enqueue admin CSS
        wp_enqueue_style( 
            'dt-admin-css', 
            get_template_directory_uri() . '/dt-admin/admin-assets/css/admin.css', 
            [], 
            filemtime( get_template_directory() . '/dt-admin/admin-assets/css/admin.css' )
        );
        
        // Enqueue Foundation accordion menu if not already loaded
        wp_enqueue_script( 'foundation-accordion-menu' );
        
        // Add admin page body class
        add_filter( 'body_class', [ $this, 'add_admin_body_class' ] );
    }
    
    /**
     * Check if we're on an admin page
     */
    private function is_admin_page() {
        $url_path = dt_get_url_path();
        return strpos( $url_path, 'dt-admin' ) === 0;
    }
    
    /**
     * Add admin body class
     */
    public function add_admin_body_class( $classes ) {
        $classes[] = 'dt-admin-page';
        return $classes;
    }
    
    /**
     * Get current admin section and subsection
     */
    public static function get_current_section() {
        $url_path = dt_get_url_path();
        $path_parts = explode( '/', trim( $url_path, '/' ) );
        
        return [
            'section' => isset( $path_parts[1] ) ? sanitize_text_field( $path_parts[1] ) : 'mapping',
            'subsection' => isset( $path_parts[2] ) ? sanitize_text_field( $path_parts[2] ) : 'overview'
        ];
    }
    
    /**
     * Get admin navigation menu data
     */
    public static function get_admin_menu() {
        return [
            'mapping' => [
                'label' => __( 'Mapping', 'disciple_tools' ),
                'icon' => 'map-marker.svg',
                'submenu' => [
                    'overview' => __( 'Overview', 'disciple_tools' ),
                    'location-grid' => __( 'Location Grid', 'disciple_tools' ),
                    'geocoding' => __( 'Geocoding', 'disciple_tools' ),
                    'layers' => __( 'Map Layers', 'disciple_tools' )
                ]
            ],
            'settings' => [
                'label' => __( 'Settings & Configuration', 'disciple_tools' ),
                'icon' => 'settings.svg',
                'submenu' => [
                    'general' => __( 'General', 'disciple_tools' ),
                    'custom-fields' => __( 'Custom Fields', 'disciple_tools' ),
                    'custom-lists' => __( 'Custom Lists', 'disciple_tools' ),
                    'custom-tiles' => __( 'Custom Tiles', 'disciple_tools' ),
                    'roles' => __( 'Roles & Permissions', 'disciple_tools' ),
                    'security' => __( 'Security', 'disciple_tools' ),
                    'workflows' => __( 'Workflows', 'disciple_tools' )
                ]
            ],
            'plugins' => [
                'label' => __( 'Plugins', 'disciple_tools' ),
                'icon' => 'puzzle-piece.svg',
                'submenu' => [
                    'installed' => __( 'Installed Plugins', 'disciple_tools' ),
                    'available' => __( 'Available Extensions', 'disciple_tools' ),
                    'settings' => __( 'Plugin Settings', 'disciple_tools' ),
                    'updates' => __( 'Updates', 'disciple_tools' )
                ]
            ],
            'tools' => [
                'label' => __( 'System Tools', 'disciple_tools' ),
                'icon' => 'tools.svg',
                'submenu' => [
                    'data' => __( 'Data Management', 'disciple_tools' ),
                    'logs' => __( 'System Logs', 'disciple_tools' ),
                    'jobs' => __( 'Background Jobs', 'disciple_tools' ),
                    'database' => __( 'Database Utilities', 'disciple_tools' ),
                    'scripts' => __( 'Scripts', 'disciple_tools' )
                ]
            ]
        ];
    }
}

// Initialize DT Admin
DT_Admin::instance(); 