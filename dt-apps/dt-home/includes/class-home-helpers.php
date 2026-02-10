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
 * @param int|null $user_id Optional. The user ID. Defaults to current user.
 * @return string The generated magic URL.
 */
function dt_home_magic_url( $action = '', $key = '', $user_id = null ) {
    // Get the home screen app instance
    $home_app = DT_Home_Magic_Link_App::instance();

    if ( ! $key ) {
        // Get current user's magic link key
        $user_id = $user_id ?? get_current_user_id();
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
 * Get the canonical logout URL that redirects users to the WP login page.
 *
 * @return string
 */
function dt_home_get_logout_url() {
    $redirect = wp_login_url();
    return wp_logout_url( $redirect );
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

    $app_ml_root = $app_meta['root'] ?? '';
    $app_ml_type = $app_meta['type'] ?? '';

    if ( empty( $app_ml_root ) || empty( $app_ml_type ) ) {
        return '';
    }

    $magic_url_key = '';

    // Handle user post_type
    if ( isset( $app_meta['post_type'] ) && $app_meta['post_type'] === 'user' ) {
        // Get user's magic URL key
        $meta_key = DT_Magic_URL::get_public_key_meta_key( $app_ml_root, $app_ml_type );
        $magic_url_key = get_user_option( $meta_key, get_current_user_id() );

        if ( empty( $magic_url_key ) ) {
            return '';
        }
    } elseif (
        isset( $app_meta['root'], $app_meta['post_type'] )
        && 'templates' === strtolower( (string) $app_meta['root'] )
        && in_array( $app_meta['post_type'], [ 'contacts' ], true )
    ) {
        // Handle template post_type (contacts)
        $post_id = Disciple_Tools_Users::get_contact_for_user( get_current_user_id() );

        if ( ! empty( $post_id ) ) {
            $post = DT_Posts::get_post( $app_meta['post_type'], $post_id );
            $meta_key = $app_meta['meta_key'] ?? '';

            if ( ! is_wp_error( $post ) && ! empty( $post ) && ! empty( $meta_key ) ) {
                if ( isset( $post[ $meta_key ] ) && ! empty( $post[ $meta_key ] ) ) {
                    $magic_url_key = $post[ $meta_key ];
                } else {
                    $magic_url_key = dt_create_unique_key();
                    update_post_meta( $post_id, $meta_key, $magic_url_key );
                }
            }
        }

        if ( empty( $magic_url_key ) ) {
            return '';
        }
    } else {
        // Unsupported post_type
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

/**
 * Check if users can invite others to the home screen
 *
 * This function checks both:
 * 1. Whether user registration is enabled (via dt_can_users_register())
 * 2. Whether the "Allow users to invite others" setting is enabled
 *
 * @return bool True if users can invite others, false otherwise.
 */
function homescreen_invite_users_enabled() {
    // Check if user registration is enabled
    if ( ! function_exists( 'dt_can_users_register' ) ) {
        return false;
    }

    if ( ! dt_can_users_register() ) {
        return false;
    }

    // Check if invite others setting is enabled (defaults to true)
    // The setting is stored in dt_home_screen_settings array
    $settings = get_option( 'dt_home_screen_settings', [] );
    $invite_others = isset( $settings['invite_others'] ) ? $settings['invite_others'] : true;
    return (bool) $invite_others;
}

/**
 * Check if login is required to access the home screen
 *
 * @return bool True if login is required, false otherwise.
 */
function homescreen_require_login() {
    // The setting is stored in dt_home_screen_settings array
    $settings = get_option( 'dt_home_screen_settings', [] );
    $require_login = isset( $settings['require_login'] ) ? $settings['require_login'] : true;
    return (bool) $require_login;
}
