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
            $modules = dt_get_option( "dt_post_type_modules" );


            // Personal
            //@todo fix query and re-enable
            //require_once( get_template_directory() . '/dt-metrics/personal/coaching-tree.php' );
            //require_once( get_template_directory() . '/dt-metrics/personal/baptism-tree.php' );
            //require_once( get_template_directory() . '/dt-metrics/personal/group-tree.php' );
            require_once( get_template_directory() . '/dt-metrics/personal/mapbox-groups-cluster-map.php' );
            require_once( get_template_directory() . '/dt-metrics/personal/mapbox-groups-point-map.php' );
            require_once( get_template_directory() . '/dt-metrics/personal/mapbox-groups-area-map.php' );
            require_once( get_template_directory() . '/dt-metrics/personal/mapbox-contacts-cluster-map.php' );
            require_once( get_template_directory() . '/dt-metrics/personal/mapbox-contacts-point-map.php' );
            require_once( get_template_directory() . '/dt-metrics/personal/mapbox-contacts-area-map.php' );
            require_once( get_template_directory() . '/dt-metrics/personal/overview.php' );

            /* Contacts */
            if ( !empty( $modules["dmm_module"]["enabled"] ) ){
                require_once( get_template_directory() . '/dt-metrics/contacts/baptism-tree.php' );
                require_once( get_template_directory() . '/dt-metrics/contacts/coaching-tree.php' );
                require_once( get_template_directory() . '/dt-metrics/contacts/milestones.php' );
                require_once( get_template_directory() . '/dt-metrics/contacts/milestones-map.php' );
            }
            require_once( get_template_directory() . '/dt-metrics/contacts/mapbox-maps.php' );
            if ( !empty( $modules["access_module"]["enabled"] ) ){
                require_once( get_template_directory() . '/dt-metrics/contacts/sources.php' );
                require_once( get_template_directory() . '/dt-metrics/contacts/overview.php' );
            }

            /* Groups */
            require_once( get_template_directory() . '/dt-metrics/groups/tree.php' );
            require_once( get_template_directory() . '/dt-metrics/groups/mapbox-cluster-map.php' );
            require_once( get_template_directory() . '/dt-metrics/groups/mapbox-point-map.php' );
            require_once( get_template_directory() . '/dt-metrics/groups/mapbox-area-map.php' );
            require_once( get_template_directory() . '/dt-metrics/groups/overview.php' );

            // Combined
            require_once( get_template_directory() . '/dt-metrics/combined/locations-list.php' );
            require_once( get_template_directory() . '/dt-metrics/combined/hover-map.php' );
            if ( !empty( $modules["access_module"]["enabled"] ) ){
                require_once( get_template_directory() . '/dt-metrics/combined/critical-path.php' );
            }

            // default menu order
            add_filter( 'dt_metrics_menu', function ( $content ){
                $modules = dt_get_option( "dt_post_type_modules" );
                if ( $content === "" ){
                    $content .= '<li><a>' . __( "Personal", "disciple_tools" ) . '</a>
                        <ul class="menu vertical nested" id="personal-menu"></ul>
                    </li>';
                    $content .= '<li><a>' . __( "Project", "disciple_tools" ) . '</a>
                        <ul class="menu vertical nested" id="combined-menu"></ul>
                    </li>';
                    if ( !empty( $modules["dmm_module"]["enabled"] ) ){
                        $content .= '<li><a>' . __( "Contacts", "disciple_tools" ) . '</a>
                            <ul class="menu vertical nested" id="contacts-menu"></ul>
                        </li>';
                    }
                    $content .= '<li><a>' . __( "Groups", "disciple_tools" ) . '</a>
                            <ul class="menu vertical nested" id="groups-menu"></ul>
                        </li>
                    ';
                }
                return $content;
            }, 10 ); //load menu links

        }

        /**
         * Add Navigation Menu
         */
        add_action( 'dt_top_nav_desktop', function(){
            ?>
            <li><a href="<?php echo esc_url( site_url( '/metrics/' ) ); ?>"><?php esc_html_e( "Metrics" ); ?></a></li>
            <?php
        }, 21 );
        add_action( 'dt_off_canvas_nav', function(){
            ?>
            <li><a href="<?php echo esc_url( site_url( '/metrics/' ) ); ?>"><?php esc_html_e( "Metrics" ); ?></a></li>
            <?php
        }, 21 );
    }

}


Disciple_Tools_Metrics::instance();

