<?php
/**
 * DT Home Screen Loader
 *
 * Main loader file for the Home Screen magic link app.
 * This file should be included in the theme to load the Home Screen functionality.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define the plugin path
if ( ! defined( 'DT_HOME_PATH' ) ) {
    define( 'DT_HOME_PATH', get_template_directory() . '/dt-apps/dt-home/' );
}

if ( ! defined( 'DT_HOME_URL' ) ) {
    define( 'DT_HOME_URL', get_template_directory_uri() . '/dt-apps/dt-home/' );
}

// Load the main magic link app
require_once DT_HOME_PATH . 'magic-link-home-app.php';

// Load admin functionality (only in admin)
if ( is_admin() ) {
    require_once DT_HOME_PATH . 'admin/home-admin.php';
}

// Load any additional includes
$includes_dir = DT_HOME_PATH . 'includes/';
if ( is_dir( $includes_dir ) ) {
    $includes = glob( $includes_dir . '*.php' );
    foreach ( $includes as $include ) {
        require_once $include;
    }
}
