<?php

/**
 * Disciple_Tools_Config class for the admin page
 *
 * @class      Disciple_Tools_Config
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Chasm.Solutions & Kingdom.Training
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Config
 */
final class Disciple_Tools_Config
{

    /**
     * Disciple_Tools_Config The single instance of Disciple_Tools_Config.
     *
     * @var     object
     * @access    private
     * @since     0.1.0
     */
    private static $_instance = null;

    /**
     * Disciple_Tools_Options_Menu Instance
     * Ensures only one instance of Disciple_Tools_Config_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Config instance
     */
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
     * @access  portal
     * @since   0.1.0
     */
    public function __construct()
    {
        add_action( "admin_menu", [ $this, "add_dt_options_menu" ] );

        // check for default options
        if ( !get_option( 'dt_site_options' ) ) {
            $site_options = dt_get_site_options_defaults();
            add_option( 'dt_site_options', $site_options, '', true );
        }
    } // End __construct()

    /**
     * Loads the subnav page
     *
     * @since 0.1.0
     */
    public function add_dt_options_menu()
    {

        add_menu_page( __( 'Settings (DT)', 'disciple_tools' ), __( 'Settings (DT)', 'disciple_tools' ), 'manage_dt', 'dt_options', [ $this, 'build_default_page' ], dt_svg_icon(), 59 );

        add_submenu_page( 'dt_options', __( 'Import', 'disciple_tools' ), __( 'Import', 'disciple_tools' ), 'manage_dt', 'import_export', [ $this, 'build_import_export_page' ] );
//        add_submenu_page( 'dt_options', __( 'De-Duplication', 'disciple_tools' ), __( 'De-Duplication', 'disciple_tools' ), 'manage_dt', 'de_duplication', [ $this, 'build_extensions_page' ] );

        //        @todo potentially remove these pages
//        add_submenu_page( 'dt_options', 'API Keys', 'API Keys', 'manage_dt', 'dt_api_keys', [ $this, 'build_api_key_page' ] );
//        add_submenu_page( 'dt_options', 'Reports Log', 'Reports Log', 'manage_dt', 'dt_reports_log', [ $this, 'build_reports_log_page' ] );
//        add_submenu_page( 'dt_options', 'Activity', 'Activity', 'manage_dt', 'dt_activity', [ $this, 'build_activity_page' ] );
//        add_submenu_page( 'dt_options', 'Notifications', 'Notifications', 'manage_dt', 'dt_notifications', [ $this, 'build_notifications_page' ] );

        do_action( 'dt_admin_menu' );
    }

    /**
     * Builds default options page with the tab bar and tab page content
     *
     * @since 0.1.0
     */
    public function build_default_page()
    {

        if ( !current_user_can( 'manage_dt' ) ) {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        /**
         * Begin Header & Tab Bar
         */
        $tab = isset( $_GET["tab"] ) ? sanitize_text_field( wp_unslash( $_GET["tab"] ) ) : 'general';

        echo '<div class="wrap">
            <h2>DISCIPLE TOOLS : CONFIGURATION</h2>
            <h2 class="nav-tab-wrapper">';

        echo '<a href="admin.php?page=dt_options&tab=general" class="nav-tab ';
        if ( $tab == 'general' || !isset( $tab ) ) {
            echo 'nav-tab-active';
        }
        echo '">General</a>';

        echo '<a href="admin.php?page=dt_options&tab=custom-lists" class="nav-tab ';
        if ( $tab == 'custom-lists' ) {
            echo 'nav-tab-active';
        }
        echo '">Custom Lists</a>';

        echo '<a href="admin.php?page=dt_options&tab=setup-checklist" class="nav-tab ';
        if ( $tab == 'setup-checklist' ) {
            echo 'nav-tab-active';
        }
        echo '">Setup Checklist</a>';

        echo '</h2>';

        // End Tab Bar

        /**
         * Begin Page Content
         */
        switch ( $tab ) {

            case 'general':
                require_once( 'tab-general.php' );
                $object = new Disciple_Tools_General_Tab();
                $object->content(); // prints
                break;
            case 'custom-lists':
                require_once( 'tab-custom-lists.php' );
                $object = new Disciple_Tools_Custom_Lists_Tab();
                $object->content(); // prints
                break;
            case 'setup-checklist':
                require_once( 'tab-setup-checklist.php' );
                $object = new Disciple_Tools_Setup_Steps_Tab();
                $object->content(); // prints
                break;

            default:
                break;
        }

        echo '</div>'; // end div class wrap

    }

    /**
     * Builds default options page with the tab bar and tab page content
     *
     * @since 0.1.0
     */
    public function build_import_export_page()
    {

        if ( !current_user_can( 'manage_dt' ) ) {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        /**
         * Begin Header & Tab Bar
         */
        $tab = isset( $_GET["tab"] ) ? sanitize_text_field( wp_unslash( $_GET["tab"] ) ) : 'import';

        echo '<div class="wrap">
            <h2>DISCIPLE TOOLS : IMPORT/EXPORT</h2>
            <h2 class="nav-tab-wrapper">';

        // tab labels
        echo '<a href="admin.php?page=import_export&tab=import" class="nav-tab ';
        if ( $tab == 'import' || !isset( $tab ) ) {
            echo 'nav-tab-active';
        }
        echo '">Import</a>';

        echo '</h2>';
        // End Tab Bar

        /**
         * Begin Page Content
         */
        switch ( $tab ) {

            case 'import':
                require_once( get_template_directory() . '/dt-core/admin/utilities/locations-import-csv.php' );
                $object = new Disciple_Tools_Import_CSV();
                $object->wizard(); // prints
                break;


            default:
                break;
        }

        echo '</div>'; // end div class wrap

    }

    /**
     * Builds default options page with the tab bar and tab page content
     *
     * @since 0.1.0
     */
    public function build_extensions_page()
    {

        if ( !current_user_can( 'manage_dt' ) ) {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        /**
         * Begin Header & Tab Bar
         */
        $tab = isset( $_GET["tab"] ) ? sanitize_text_field( wp_unslash( $_GET["tab"] ) ) : 'general';

        echo '<div class="wrap">
            <h2>DISCIPLE TOOLS : EXTENSIONS</h2>
            <h2 class="nav-tab-wrapper">';

        // tab labels
        echo '<a href="admin.php?page=extensions&tab=general" class="nav-tab ';
        if ( $tab == 'general' || !isset( $tab ) ) {
            echo 'nav-tab-active';
        }
        echo '">General</a>';

        echo '</h2>';
        // End Tab Bar

        /**
         * Begin Page Content
         */
        switch ( $tab ) {

            case 'general':
                require_once( 'tab-extensions.php' );
                Disciple_Tools_Extensions_Tab::content();
                break;
            default:
                break;
        }

        echo '</div>'; // end div class wrap

    }

    /**
     * Builds menu page notifications page
     * @todo possibly remove this function
     */
    public function build_notifications_page()
    {
        dt_notifications_table();
    }

    /**
     * Display the list table page
     *
     * @todo possibly remove this function
     * @return void
     */
    public function build_reports_log_page()
    {
        $list_table = new Disciple_Tools_Reports_List_Table();
        $list_table->prepare_items();
        ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Disciple Tools Reports Log</h2>
            <p>This table displays the ongoing reports being recorded nightly from the different integration
                sources.</p>
            <?php $list_table->display(); ?>
        </div>
        <?php
    }



}
