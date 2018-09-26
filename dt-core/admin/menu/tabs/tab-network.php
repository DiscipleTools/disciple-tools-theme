<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Network_Tab
 */
class Disciple_Tools_Network_Tab extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 50, 1 ); // use the priority setting to control load order
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );


        parent::__construct();
    } // End __construct()


    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Network Dashboards', 'disciple_tools' ), __( 'Network Dashboards', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=network', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=network" class="nav-tab ';
        if ( $tab == 'network' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Network Dashboards', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'network' == $tab ) {

            require_once( get_template_directory() . '/dt-network/network.php' );
            require_once( get_template_directory() . '/dt-network/network-endpoints.php' );

            self::template( 'begin' );

            $this->network_enable_box();

            if ( get_option( 'dt_network_enabled' ) ) {
                $this->partner_profile_metabox();
                $this->admin_site_link_box();
                $this->admin_locations_gname_installed_box();
            }


            self::template( 'right_column' );

            self::template( 'end' );
        }
    }

    public function network_enable_box() {
        $this->box( 'top', 'Enable and Configure Network Connection' );

        Disciple_Tools_Network::admin_network_enable_box();

        $this->box( 'bottom' );
    }

    public function admin_site_link_box() {
        $this->box( 'top', 'Network Dashboards' );

        Disciple_Tools_Network::admin_site_link_box();

        $this->box( 'bottom' );
    }

    /**
     * This box displays location list and the gname coded elements
     */
    public function admin_locations_gname_installed_box() {
        $this->box( 'top', 'Locations Status' );

        Disciple_Tools_Network::admin_locations_gname_installed_box();

        $this->box( 'bottom' );
    }

    public function partner_profile_metabox() {
        Disciple_Tools_Network::admin_partner_profile_box();
    }


}
Disciple_Tools_Network_Tab::instance();
