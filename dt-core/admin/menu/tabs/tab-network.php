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
        add_submenu_page( 'dt_options', __( 'Network', 'disciple_tools' ), __( 'Network', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=network', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=network" class="nav-tab ';
        if ( $tab == 'network' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Network', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'network' == $tab ) {

            self::template( 'begin' );

            $this->network_enable();
            $this->admin_site_link_box();

            self::template( 'right_column' );

            self::template( 'end' );
        }
    }

    public function network_enable() {
        $this->box( 'top', 'Enable and Configure Network Connection' );

        Disciple_Tools_Network::admin_network_enable_box();

        $this->box( 'bottom' );
    }

    public function admin_site_link_box() {
        if ( get_option( 'dt_network_enabled' ) ) {

            $this->box( 'top', 'Site Links for Network Dashboards' );

            Disciple_Tools_Network::admin_site_link_box();

            $this->box( 'bottom' );
        }
    }
}
Disciple_Tools_Network_Tab::instance();
