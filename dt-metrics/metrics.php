<?php

/**
 * Disciple_Tools_Metrics
 *
 * @class      Disciple_Tools_Metrics
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Disciple.Tools
 */

if ( !defined( 'ABSPATH' ) ){
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Admin_Menus
 */
class Disciple_Tools_Metrics{


    private static $_instance = null;

    public static function instance(){
        if ( is_null( self::$_instance ) ){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct(){
        $url_path = dt_get_url_path();
        if ( strpos( $url_path, 'metrics' ) !== false ){
            // wait for D.T post type classes to be set up before building metrics for them
            add_action( 'after_setup_theme', function (){
                $modules = dt_get_option( 'dt_post_type_modules' );

                // Personal
                require_once( get_template_directory() . '/dt-metrics/records/genmap.php' );
                new DT_Metrics_Groups_Genmap( 'personal', __( 'Personal', 'disciple_tools' ) );
                require_once( get_template_directory() . '/dt-metrics/records/dynamic-records-map.php' );
                new DT_Metrics_Dynamic_Records_Map( 'personal', __( 'Personal', 'disciple_tools' ) );
                //                require_once( get_template_directory() . '/dt-metrics/personal/coaching-tree.php' );
                //                require_once( get_template_directory() . '/dt-metrics/personal/baptism-tree.php' );
                //                require_once( get_template_directory() . '/dt-metrics/personal/group-tree.php' );

                //                require_once( get_template_directory() . '/dt-metrics/personal/mapbox-contacts-maps.php' );
                //                require_once( get_template_directory() . '/dt-metrics/personal/mapbox-groups-maps.php' );
                require_once( get_template_directory() . '/dt-metrics/personal/activity-highlights.php' );
                require_once( get_template_directory() . '/dt-metrics/personal/activity-log.php' );
                require_once( get_template_directory() . '/dt-metrics/personal/overview.php' );

                //...require_once( get_template_directory() . '/dt-metrics/combined/mapbox-maps.php' );

                require_once( get_template_directory() . '/dt-metrics/records/records-endpoints.php' );

                if ( dt_has_permissions( [ 'dt_all_access_contacts', 'view_project_metrics' ] ) ){ // tests if project level permissions
                    if ( !empty( $modules['access_module']['enabled'] ) ){
                        //...require_once( get_template_directory() . '/dt-metrics/contacts/mapbox-maps.php' );
                        require_once( get_template_directory() . '/dt-metrics/contacts/sources.php' );
                        //...require_once( get_template_directory() . '/dt-metrics/contacts/overview.php' );
                    }

                    /* Groups */
                    /*require_once( get_template_directory() . '/dt-metrics/groups/tree.php' );
                    require_once( get_template_directory() . '/dt-metrics/groups/mapbox-maps.php' );
                    require_once( get_template_directory() . '/dt-metrics/groups/overview.php' );*/

                    // Combined
                    require_once( get_template_directory() . '/dt-metrics/combined/site-links.php' );
                    //..require_once( get_template_directory() . '/dt-metrics/combined/daily-activity.php' );
                    require_once( get_template_directory() . '/dt-metrics/combined/locations-list.php' );

                    /* Record Types */
                    require_once( get_template_directory() . '/dt-metrics/records/date-range-activity.php' );
                    require_once( get_template_directory() . '/dt-metrics/records/time-charts.php' );
                    require_once( get_template_directory() . '/dt-metrics/records/select-tags-charts.php' );
                    new DT_Metrics_Groups_Genmap( 'records', __( 'Genmap', 'disciple_tools' ) );
                    new DT_Metrics_Dynamic_Records_Map( 'records', __( 'Maps', 'disciple_tools' ) );
                    require_once( get_template_directory() . '/dt-metrics/combined/hover-map.php' );
                }
                if ( !empty( $modules['access_module']['enabled'] ) ){
                    require_once( get_template_directory() . '/dt-metrics/combined/critical-path.php' );
                }
            }, 1000);

            // default menu order
            add_filter( 'dt_metrics_menu', function ( $content ){
                $modules = dt_get_option( 'dt_post_type_modules' );
                if ( $content === '' ){

                    $content .= '<li><a>' . __( 'Personal', 'disciple_tools' ) . '</a>
                                <ul class="menu vertical nested" id="personal-menu"></ul>
                            </li>';

                    if ( dt_has_permissions( [ 'dt_all_access_contacts', 'view_project_metrics' ] ) ){
                        $content .= '<li><a>' . __( 'Project', 'disciple_tools' ) . '</a>
                                <ul class="menu vertical nested" id="records-menu"></ul>
                            </li>';
                    } // permission check
                }
                return $content;
            }, 10 ); //load menu links

        }

        /**
         * Add Navigation Menu
         */
        if ( current_user_can( 'access_disciple_tools' ) ) {
            add_filter( 'desktop_navbar_menu_options', function ( $tabs ){
                $tabs['metrics'] = [
                    'link' => site_url( '/metrics/' ),
                    'label' => __( 'Metrics', 'disciple_tools' )
                ];
                return $tabs;
            }, 25 );
        }
    }
}


Disciple_Tools_Metrics::instance();
