<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class DT_Zume_Hooks
 */
class Disciple_Tools_Metrics_Hooks
{

    private static $_instance = null;
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Build hook classes
     */
    public function __construct()
    {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_google' ], 10 );

        require_once( get_template_directory() . '/dt-metrics/metrics-personal.php' );
        Disciple_Tools_Metrics_Personal::instance();
    }

    // Enqueue maps and charts for standard metrics
    public function enqueue_google() {
        $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );
        if ( 'metrics' === $url_path ) {
            wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', [], false );
            wp_enqueue_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . dt_get_option( 'map_key' ), array(), null, true );
        }
    }
}

abstract class Disciple_Tools_Metrics_Hooks_Base
{
    public function __construct() {}

}