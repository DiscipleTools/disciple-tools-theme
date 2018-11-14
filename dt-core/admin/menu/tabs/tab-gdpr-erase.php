<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_GDPR_Erase_Tab
 */
class Disciple_Tools_GDPR_Erase_Tab extends Disciple_Tools_Abstract_Menu_Base
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
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 120 );
        add_action( 'dt_utilities_tab_menu', [ $this, 'add_tab' ], 120, 1 ); // use the priority setting to control load order
        add_action( 'dt_utilities_tab_content', [ $this, 'content' ], 120, 1 );


        parent::__construct();
    } // End __construct()


    public function add_submenu() {
        add_submenu_page( 'dt_utilities', __( 'GDPR Erase', 'disciple_tools' ), __( 'GDPR Erase', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=remove_contact_data', [ 'Disciple_Tools_Utilities_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_utilities&tab=remove_contact_data" class="nav-tab ';
        if ( $tab == 'remove_contact_data' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'GDPR Erase', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'remove_contact_data' == $tab ) {

            self::template( 'begin', 1 );

            $gdpr = new Disciple_Tools_GDPR();
            $gdpr->contact_data_removal_page();

            self::template( 'end', 1 );
        }
    }
}
Disciple_Tools_GDPR_Erase_Tab::instance();
