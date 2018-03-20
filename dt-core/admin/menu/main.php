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
     * @access  public
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

        echo '<a href="admin.php?page=dt_options&tab=site-links" class="nav-tab ';
        if ( $tab == 'site-links' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Site Links', 'disciple_tools' ) .'</a>';

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
            case 'site-links':
                require_once( 'tab-site-links.php' );
                $object = new Disciple_Tools_Site_Links_Tab();
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
}
