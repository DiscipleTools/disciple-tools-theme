<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_General_Tab
 */
class Disciple_Tools_Site_Links_Tab extends Disciple_Tools_Abstract_Menu_Base
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
        add_submenu_page( 'dt_options', __( 'Site Link System', 'disciple_tools' ), __( 'Site Link System', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=site-links', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=site-links" class="nav-tab ';
        if ( $tab == 'site-links' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Site Link System', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'site-links' == $tab ) {

            self::template( 'begin' );

                $this->box_message();

            self::template( 'right_column' );

            self::template( 'end' );
        }
    }

    public function box_message() {
        $this->box( 'top', 'Site links are configured through the Site Links System admin menu' );
        ?>
            <a href="<?php echo esc_url( admin_url() ) ?>edit.php?post_type=site_link_system" class="button">Go To Site Link System</a>
        <?php
        $this->box( 'bottom' );
    }
}
Disciple_Tools_Site_Links_Tab::instance();
