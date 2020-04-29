<?php

/**
 * Disciple_Tools_Metrics
 *
 * @class      Disciple_Tools_Metrics
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 * @author     Chasm.Solutions & Kingdom.Training
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Admin_Menus
 */
class Disciple_Tools_Metrics
{

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $url_path = dt_get_url_path();
        if ( strpos( $url_path, "metrics" ) !== false ) {

            // load base chart setup
            require_once( get_template_directory() . '/dt-metrics/charts-base.php' );

            // load basic charts
            require_once( get_template_directory() . '/dt-metrics/personal/mapbox-area-map.php' );
            require_once( get_template_directory() . '/dt-metrics/personal/overview.php' );

            // Critical Path
            require_once( get_template_directory() . '/dt-metrics/project/critical-path.php' );

            /* Contacts */
            require_once( get_template_directory() . '/dt-metrics/contacts/baptism-tree.php' );
            require_once( get_template_directory() . '/dt-metrics/contacts/coaching-tree.php' );
            require_once( get_template_directory() . '/dt-metrics/contacts/sources.php' );
            require_once( get_template_directory() . '/dt-metrics/contacts/milestones.php' );
            require_once( get_template_directory() . '/dt-metrics/contacts/milestones-map.php' );
            if ( DT_Mapbox_API::get_key() ) {
                require_once( get_template_directory() . '/dt-metrics/contacts/mapbox-cluster-map.php' );
                require_once( get_template_directory() . '/dt-metrics/contacts/mapbox-point-map.php' );
                require_once( get_template_directory() . '/dt-metrics/contacts/mapbox-area-map.php' );
            }
            require_once( get_template_directory() . '/dt-metrics/contacts/overview.php' );

            /* Groups */
            require_once( get_template_directory() . '/dt-metrics/groups/tree.php' );
            if ( DT_Mapbox_API::get_key() ) {
                require_once( get_template_directory() . '/dt-metrics/groups/mapbox-cluster-map.php' );
                require_once( get_template_directory() . '/dt-metrics/groups/mapbox-point-map.php' );
                require_once( get_template_directory() . '/dt-metrics/groups/mapbox-area-map.php' );
            }
            require_once( get_template_directory() . '/dt-metrics/groups/overview.php' );
        }
    }

}
Disciple_Tools_Metrics::instance();


function dt_get_time_until_midnight() {
    $midnight = mktime( 0, 0, 0, gmdate( 'n' ), gmdate( 'j' ) +1, gmdate( 'Y' ) );
    return intval( $midnight - current_time( 'timestamp' ) );
}

// Tests if timestamp is valid.
if ( ! function_exists( 'is_valid_timestamp' ) ) {
    function is_valid_timestamp( $timestamp ) {
        return ( (string) (int) $timestamp === $timestamp )
            && ( $timestamp <= PHP_INT_MAX )
            && ( $timestamp >= ~PHP_INT_MAX );
    }
}

