<?php
/**
 * Adapted from Google Analytics Plugin by Sharethis
 * Original Plugin URI: http://wordpress.org/extend/plugins/googleanalytics/
 * Original Version: 2.1.1
 * Original Author: ShareThis
 * Original Author URI: http://sharethis.com
 */


if ( !defined( 'WP_CONTENT_URL' ) ) {
	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
}
if ( !defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}
if ( !defined( 'WP_PLUGIN_URL' ) ) {
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
}
if ( !defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}
if ( !defined( 'GA_NAME' ) ) {
	define( 'GA_NAME', 'googleanalytics' );
}
if ( !defined( 'GA_PLUGIN_DIR' ) ) {
	define( 'GA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( !defined( 'GA_PLUGIN_URL' ) ) {
	define( 'GA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'GA_MAIN_FILE_PATH' ) ) {
	define( 'GA_MAIN_FILE_PATH', __FILE__ );
}
if ( !defined( 'GA_SHARETHIS_SCRIPTS_INCLUDED' ) ) {
	define( 'GA_SHARETHIS_SCRIPTS_INCLUDED', 0 );
}

define( 'GOOGLEANALYTICS_VERSION', '2.1.1' );
include_once GA_PLUGIN_DIR . 'overwrite/ga_overwrite.php';
include_once GA_PLUGIN_DIR . 'class/Ga_Autoloader.php';

Ga_Autoloader::register();
Ga_Hook::add_hooks( GA_MAIN_FILE_PATH );

add_action( 'init', 'Ga_Helper::init' );
