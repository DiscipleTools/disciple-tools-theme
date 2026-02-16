<?php
/**
 * Disciple_Tools PWA to add Progressive Web App features to D.T
 *
 * Current Features Implemented:
 * - Add to Home Screen
 *
 * Possible Future Features:
 * - Push notifications
 * - Offline access
 *
 * @class   Disciple_Tools_PWA
 * @version 1.0.0
 * @since   1.67.0
 * @package Disciple.Tools
 *
 */


/**
 * Class Disciple_Tools_PWA
 */
class Disciple_Tools_PWA
{
    /**
     * The single instance of Disciple_Tools_PWA
     *
     * @var    object
     * @access private
     * @since  1.0.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_PWA Instance
     * Ensures only one instance of Disciple_Tools_PWA is loaded or can be loaded.
     *
     * @since  1.0.0
     * @static
     * @return Disciple_Tools_PWA instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    public function __construct()
    {
        add_action( 'parse_request', [ $this, 'parse_request' ], 999 );

        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 999 );
    }

    public function parse_request()
    {
        if ( !isset( $_SERVER['REQUEST_URI'] ) ) {
            return;
        }
        $uri = dt_get_url_path( true );

        if ( 'manifest.json' === $uri ) {
            $this->print_manifest_json();
            return;
        }
    }

    /**
     * Handle "/manifest.json" request.
     *
     * @since 1.0.0
     */
    private function print_manifest_json() {

        $instance_name = get_bloginfo( 'name' );
        $instance_desc = get_bloginfo( 'description' );

        // Determine start_url based on referrer
        // If manifest is requested from dt-home page, use that URL as start_url
        $start_url = $this->get_start_url_from_referrer();

        $data = array(
            'id'               => home_url(),
            'name'             => $instance_name,
            'short_name'       => $instance_name,
            'description'      => $instance_desc,
            'theme_color'      => '#3f729b',
            'background_color' => '#3f729b',
            'orientation'      => 'portrait',
            'display'          => 'minimal-ui',
            'scope'            => '/',
            'start_url'        => $start_url,
            'icons'            => [
                [
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'src' => esc_url( get_template_directory_uri() ) . '/dt-assets/favicons/android-chrome-192x192.png',
                ],
                [
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'src' => esc_url( get_template_directory_uri() ) . '/dt-assets/favicons/android-chrome-512x512.png',
                ],
            ],
            'shortcuts'        => [],
            'categories'       => [ 'productivity', 'utilities' ],
        );

        // add shortcuts for all main menu items
        $dt_nav_tabs = dt_default_menu_array();
        foreach ( $dt_nav_tabs['main'] as $dt_main_tabs ) {
            if ( ! ( isset( $dt_main_tabs['hidden'] ) && $dt_main_tabs['hidden'] ) ) {
                $data['shortcuts'][] = [
                    'name' => $dt_main_tabs['label'],
                    'url' => esc_url( $dt_main_tabs['link'] ),
                    'icons' => [
                        [
                            'sizes' => '96x96',
                            'src' => esc_url( get_template_directory_uri() ) . '/dt-assets/favicons/android-chrome-96x96.png',
                        ],
                    ],
                ];
            }
        }

        header( 'Content-Type: application/json' );

        // remove empty values
        foreach ( $data as $key => $value ) {
            if ( empty( $value ) ) {
                unset( $data[ $key ] );
            }
        }

        /**
         * filter manifest data
         */
        $data = apply_filters(
            'disciple_tools_manifest_data',
            $data
        );
        echo json_encode( $data );
        exit;
    }

    /**
     * Get start_url based on HTTP referrer.
     * If referrer is a dt-home page (apps/launcher/{key}), return that URL.
     * Otherwise, return default '/'.
     *
     * @since 1.0.0
     * @return string The start_url to use in manifest
     */
    private function get_start_url_from_referrer() {
        // Check if HTTP_REFERER is set
        if ( ! isset( $_SERVER['HTTP_REFERER'] ) || empty( $_SERVER['HTTP_REFERER'] ) ) {
            return '/';
        }

        $referer = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );

        // Parse the referrer URL
        $referer_parts = parse_url( $referer );
        if ( ! isset( $referer_parts['path'] ) ) {
            return '/';
        }

        $referer_path = trim( $referer_parts['path'], '/' );
        $path_segments = explode( '/', $referer_path );

        // Check if referrer is a dt-home page: apps/launcher/{magic_key}
        // Path segments should be: ['apps', 'launcher', '{magic_key}']
        if ( count( $path_segments ) >= 3 
            && $path_segments[0] === 'apps' 
            && $path_segments[1] === 'launcher' 
            && ! empty( $path_segments[2] ) ) {
            
            // Extract the magic key (third segment)
            $magic_key = sanitize_text_field( $path_segments[2] );
            
            // Validate magic key format (should be alphanumeric, typically 32+ chars)
            if ( ! empty( $magic_key ) && strlen( $magic_key ) >= 16 ) {
                // Build the dt-home URL path (base URL only, no additional path segments)
                // This ensures the app always starts at the home screen, not a sub-page
                $dt_home_path = '/apps/launcher/' . $magic_key;
                
                // Note: We intentionally don't preserve query strings or additional path segments
                // to ensure consistent behavior when launching from home screen
                
                return $dt_home_path;
            }
        }

        // Default: return home page
        return '/';
    }

    public function scripts() {
        // to add a custom install prompt, include this js.
        // dt_theme_enqueue_script( 'pwa', 'dt-assets/js/pwa.js' );
    }
}
