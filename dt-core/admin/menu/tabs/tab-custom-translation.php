<?php

/**
 * Disciple Tools
 *
 * @class      Disciple_Tools_Tab_Custom_Translations
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple_Tools
 * @author     Chasm.Solutions & Kingdom.Training
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Custom_Translations
 */
class Disciple_Tools_Tab_Custom_Translations extends Disciple_Tools_Abstract_Menu_Base
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
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 10, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    } // End __construct()

    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'Translation', 'disciple_tools' ), __( 'Translation', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=translation', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        ?>
        <a href="<?php echo esc_url( admin_url() ) ?>admin.php?page=dt_options&tab=translation"
           class="nav-tab <?php echo esc_html( $tab == 'translation' ? 'nav-tab-active' : '' ) ?>">
            <?php echo esc_html__( 'Translation' ) ?>
        </a>
        <?php
    }

    private function get_post_fields( $post_type ){
        if ( $post_type === "groups" ){
            return Disciple_Tools_Groups_Post_Type::instance()->get_custom_fields_settings( null, null, true, false );
        } elseif ( $post_type === "contacts" ){
            return Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings( null, null, true, false );
        } else {
            $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type, false );
            return isset( $post_settings["fields"] ) ? $post_settings["fields"] : null;
        }
    }

    /**
     * Packages and prints tab page
     *
     * @param $tab
     */
    public function content( $tab ) {
        if ( 'translation' == $tab ) :
            $field_key = false;
            $post_type = null;
            $this->template( 'begin' );



            $this->box( 'top' );

            $this->box( 'bottom' );

            $this->template( 'right_column' );

            $this->template( 'end' );
        endif;
    }




    /**
     * Display admin notice
     * @param $notice string
     * @param $type string error|success|warning
     */
    public static function admin_notice( string $notice, string $type ) {
        ?>
        <div class="notice notice-<?php echo esc_attr( $type ) ?> is-dismissible">
            <p><?php echo esc_html( $notice ) ?></p>
        </div>
        <?php
    }
}
Disciple_Tools_Tab_Custom_Translations::instance();
