<?php
/**
 * Disciple_Tools_Settings_Menu class for the admin page
 *
 * @class      Disciple_Tools_Settings_Menu
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Chasm.Solutions & Kingdom.Training
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Settings_Menu
 */
class Disciple_Tools_Settings_Menu
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
        add_action( "admin_menu", [ $this, "add_dt_options_menu" ] );
    }

    public function add_dt_options_menu()
    {
        add_menu_page( __( 'Settings (DT)', 'disciple_tools' ), __( 'Settings (DT)', 'disciple_tools' ), 'manage_dt', 'dt_options', [ $this, 'content' ], dt_svg_icon(), 59 );
    }

    public function content()
    {
        if ( !current_user_can( 'manage_dt' ) ) {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        $tab = isset( $_GET["tab"] ) ? sanitize_text_field( wp_unslash( $_GET["tab"] ) ) : 'general';

        ?>
        <div class="wrap">
            <h2>DISCIPLE TOOLS : SETTINGS</h2>

            <h2 class="nav-tab-wrapper">
                <?php do_action( 'dt_settings_tab_menu', $tab ); ?>
            </h2>

            <?php do_action( 'dt_settings_tab_content', $tab ); ?>

        </div>
        <?php
    }
}
Disciple_Tools_Settings_Menu::instance();
