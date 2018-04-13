<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_General_Tab
 */
class Disciple_Tools_Submenu_Import extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;
    public static function instance()
    {
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
    public function __construct()
    {
        add_action( "admin_menu", [ $this, "register_menu" ] );
        parent::__construct();
    } // End __construct()

    public function register_menu() {
        add_submenu_page( 'dt_options', __( 'Import', 'disciple_tools' ), __( 'Import', 'disciple_tools' ), 'manage_dt', 'dt_import_export', [ $this, 'build_import_export_page' ] );
    }

    public function build_import_export_page()
    {
        if ( !current_user_can( 'manage_dt' ) ) {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        $tab = isset( $_GET["tab"] ) ? sanitize_text_field( wp_unslash( $_GET["tab"] ) ) : 'import';

        ?>
        <div class="wrap">
            <h2>DISCIPLE TOOLS : IMPORT</h2>

            <h2 class="nav-tab-wrapper">
                <?php do_action( 'dt_submenu_import_tab_menu', $tab ); ?>
            </h2>

            <?php do_action( 'dt_submenu_import_tab_content', $tab ); ?>

        </div>
        <?php
    }
}
Disciple_Tools_Submenu_Import::instance();