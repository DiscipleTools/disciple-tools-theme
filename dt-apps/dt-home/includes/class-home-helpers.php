<?php
/**
 * Home Screen Helper Functions
 *
 * Provides utility functions for the Home Screen magic link app.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Generate a magic URL for the home screen app
 *
 * This function generates magic URLs for navigation within the home screen app.
 * It only applies to apps with creation_type === 'coded' (magic link apps).
 *
 * @param string $action Optional. The action to append to the URL (e.g., 'app/{slug}', 'logout').
 * @param string $key Optional. The magic link key. If not provided, uses current user's key.
 * @return string The generated magic URL.
 */
function dt_home_magic_url( $action = '', $key = '' ) {
    // Get the home screen app instance
    $home_app = DT_Home_Magic_Link_App::instance();
    
    if ( ! $key ) {
        // Get current user's magic link key
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return '';
        }
        
        $meta_key = $home_app->root . '_' . $home_app->type . '_magic_key';
        $key = get_user_option( $meta_key, $user_id );
        
        if ( empty( $key ) ) {
            return '';
        }
    }
    
    // Build the base URL
    $url = DT_Magic_URL::get_link_url( $home_app->root, $home_app->type, $key );
    
    // Append action if provided
    if ( $action ) {
        $url = trailingslashit( $url ) . ltrim( $action, '/' );
    }
    
    return $url;
}

/**
 * Get magic URL for a specific app (only for coded apps)
 *
 * @param array $app The app data array
 * @param string $action Optional. The action to append to the URL.
 * @param bool $with_launcher Optional. Whether to add launcher=1 parameter. Default false.
 * @return string The generated magic URL, or empty string if not applicable.
 */
function dt_home_get_app_magic_url( $app, $action = '', $with_launcher = false ) {
    // Only apply to coded apps
    if ( empty( $app['creation_type'] ) || $app['creation_type'] !== 'coded' ) {
        return '';
    }
    
    // Check if app has magic_link_meta
    if ( empty( $app['magic_link_meta'] ) ) {
        return '';
    }
    
    $app_meta = $app['magic_link_meta'];
    
    // Only handle user post_type for now
    if ( empty( $app_meta['post_type'] ) || $app_meta['post_type'] !== 'user' ) {
        return '';
    }
    
    $app_ml_root = $app_meta['root'] ?? '';
    $app_ml_type = $app_meta['type'] ?? '';
    
    if ( empty( $app_ml_root ) || empty( $app_ml_type ) ) {
        return '';
    }
    
    // Get user's magic URL key
    $meta_key = DT_Magic_URL::get_public_key_meta_key( $app_ml_root, $app_ml_type );
    $magic_url_key = get_user_option( $meta_key, get_current_user_id() );
    
    if ( empty( $magic_url_key ) ) {
        return '';
    }
    
    // Build the URL
    $url = DT_Magic_URL::get_link_url( $app_ml_root, $app_ml_type, $magic_url_key );
    
    // Append action if provided
    if ( $action ) {
        $url = trailingslashit( $url ) . ltrim( $action, '/' );
    }
    
    // Add launcher parameter if requested
    if ( $with_launcher ) {
        $separator = strpos( $url, '?' ) !== false ? '&' : '?';
        $url .= $separator . 'launcher=1';
    }
    
    return $url;
}

