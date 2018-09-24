<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_People_Groups
 */
class Disciple_Tools_Tab_People_Groups extends Disciple_Tools_Abstract_Menu_Base
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
        add_action( 'admin_menu', [ $this, 'add_submenu' ], 115 );
        add_action( 'dt_utilities_tab_menu', [ $this, 'add_tab' ], 115, 1 );
        add_action( 'dt_utilities_tab_content', [ $this, 'content' ], 115, 1 );

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'edit.php?post_type=peoplegroups', __( 'Import', 'disciple_tools' ), __( 'Import', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=people-groups', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
        add_submenu_page( 'dt_utilities', __( 'Import People Groups', 'disciple_tools' ), __( 'Import People Groups', 'disciple_tools' ), 'manage_dt', 'dt_utilities&tab=people-groups', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_utilities&tab=people-groups" class="nav-tab ';
        if ( $tab == 'people-groups' ) {
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__( 'Import People Groups' ) . '</a>';
    }

    public function content( $tab ) {
        if ( 'people-groups' == $tab ) :

            $this->template( 'begin' );

            Disciple_Tools_People_Groups::admin_tab_table();

            $this->template( 'right_column' );

            $this->template( 'end' );

        endif;
    }



}
Disciple_Tools_Tab_People_Groups::instance();
