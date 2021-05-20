<?php

/**
 * Disciple Tools
 *
 * @class      Disciple_Tools_Tab_Custom_Translations
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Disciple.Tools
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
        ?>
        <form method="post">
            <?php wp_nonce_field( 'googletranslate_key_nonce' . get_current_user_id(), 'googletranslate_key_nonce' ); ?>
             Google Translate API Key: <input type="text" class="regular-text" name="googleTranslate_key" value="<?php echo ( $key ) ? esc_attr( $key ) : ''; ?>" /> <button type="submit" class="button">Update</button>
        </form>

        <?php if ( empty( self::get_key() ) ) : ?>

            <h2>Setting up API keys for Google Translate</h2>

            <p>In order to create a Google Translate API Key you need a paid account. Through their terms and services, Google doesn’t allow for non-paid usage of their Translation API.</p>
            <p>More information is available at: <a href="https://cloud.google.com/translate/" target="blank" rel="noopener noreferrer">https://cloud.google.com/translate/</a></p>
            <p>To create your application’s API key simply follow the steps below:</p>
            <p>Go to the <a class="more-link" href="https://console.cloud.google.com" target="_blank" rel="noopener noreferrer">Cloud Platform Console.</a></p>

            <p>The first thing you need is an account in Google Cloud Console and a payment method in it. To do this, follow these steps:</p>

            <ol>
                <li>Access the <a rel="noreferrer noopener" aria-label="Google Cloud Console (opens in a new tab)" href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a> and log in or, if you do not have an account yet, sign up.</li>
                <li>Open the left side menu of the console and select <em>Billing</em>.</li>
                <li>Click on the button <em>New billing account</em>. Keep in mind that if this is not your first billing account, you must first open the list of billing accounts. To do this, click on the name of your existing billing account near the top of the page and then click on <em>Manage billing accounts</em>.</li>
                <li>Enter the name of the billing account and your billing information. The options you’ll see depend on the country of your billing address.</li>
                <li>Click <em>Submit and enable billing</em>.</li></ol>
            </ol>

            <p>By default, the person creating the billing account is a billing administrator for the account. Once you have the account created and the billing information ready, you can continue with the following steps to obtain the API Key.</p>

            <h2>How to Create a New Project in Google Cloud</h2>
            <ol>
            <li>Go to the bar in the top of the Google Cloud Console and in the drop-down you can see your created projects as well as the option to create a new one by clicking on the <em>New Project</em> button:</li>
            <li>Now give the new project a name and create it by clicking on the corresponding button, as you can see in the following screenshot:</li>
            <li>Before you can use a Google API in your project, you have to activate it. Go to the side menu and select the <em>APIs &amp; Services</em> option:</li>
            <li>Click on the upper button <em>Enable APIs and services</em> to continue with the activation process of the API. This takes us to a search box where we have to look for the API we’re interested in. In this case, we want to use the Google Translate API. Type <em>translate</em> in the search box and click on the result <em>Cloud Translate API</em>:</li>
            <li>Click on the Enable button to activate the API in our project</li>
            <li>go to the side menu again and select the Credentials option: On this screen we see a button with a drop-down and the text Create credentials. Don’t click on the button! Instead, open the drop-down by clicking on the arrow to the right of the button and select the API Key option.</li>
            <li>This creates the new Key API. Copy the API key</li>
            <li>Click "Restrict Key"</li>
            <li>There you can select to restrict the API Key by HTTP referrers, which means that you can only make calls to the Google Cloud Translate API using the API Key from certain domain names.</li>
            <li>You must add the valid domain names in the text box that appears when selecting the HTTP referrers option. i.e. https://test.com/.</li>

            <li>Copy the API key from the Google Cloud Console, into the "Google Translation API Key:" box above and click update</li>
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
