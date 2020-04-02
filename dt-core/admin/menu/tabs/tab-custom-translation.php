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

    /**
     * Google Translate API Key options storage
     */
    public static function get_key() {
        return get_option( 'dt_googletranslate_api_key' );
    }
    public static function delete_key() {
        return delete_option( 'dt_googletranslate_api_key' );
    }
    public static function update_key( $key ) {
        return update_option( 'dt_googletranslate_api_key', $key, true );
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



            $this->box( 'top', __( 'Google Translate API Key', 'disciple_tools' ) );

            $this->googleTranslateAPIkey();

            $this->box( 'bottom' );
            $this->template( 'right_column' );

            $this->template( 'end' );
        endif;
    }


    private function googleTranslateAPIkey() {
        if ( isset( $_POST['googleTranslate_key'] )
                 && ( isset( $_POST['googletranslate_key_nonce'] )
                      && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['googletranslate_key_nonce'] ) ), 'googletranslate_key_nonce' . get_current_user_id() ) ) ) {

            $key = sanitize_text_field( wp_unslash( $_POST['googleTranslate_key'] ) );
            if ( empty( $key ) ) {
                self::delete_key();
            } else {
                self::update_key( $key );
            }
        }
        $key = self::get_key();
        ?> <form method="post">



                            <?php wp_nonce_field( 'googletranslate_key_nonce' . get_current_user_id(), 'googletranslate_key_nonce' ); ?>
                            Google Translate API Token: <input type="text" class="regular-text" name="googleTranslate_key" value="<?php echo ( $key ) ? esc_attr( $key ) : ''; ?>" /> <button type="submit" class="button">Update</button>




                            <?php if ( empty( self::get_key() ) ) : ?>
                                <h2>Google Translate API Instructions</h2>
                                <ol>
                                    <li>
                                        Go to <a href="">Link to GT</a>.
                                    </li>
                                </ol>
                            <?php endif;
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
