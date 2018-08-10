<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_General_Tab
 */
class Disciple_Tools_Tab_Featured_Extensions extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        add_action( 'dt_extensions_tab_menu', [ $this, 'add_tab' ], 10, 1 ); // use the priority setting to control load order
        add_action( 'dt_extensions_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    } // End __construct()

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_extensions&tab=featured-extensions" class="nav-tab ';
        if ( $tab == 'featured-extensions' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Featured Extensions', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'featured-extensions' == $tab ) {
            // begin columns template
            $this->template( 'begin' );

            $this->box_message();

            // begin right column template
            $this->template( 'right_column' );

            // end columns template
            $this->template( 'end' );
        }
    }

    public function box_message() {

        ?>
        <table class="widefat striped">

            <tbody>
            <tr>
                <td>
                    Under construction
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }
}
Disciple_Tools_Tab_Featured_Extensions::instance();