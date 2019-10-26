<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Security_Tab
 */
class Disciple_Tools_Security_Tab extends Disciple_Tools_Abstract_Menu_Base
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
        add_submenu_page( 'dt_options', __( 'Security', 'disciple_tools' ), __( 'Security', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=security', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=security" class="nav-tab ';
        if ( $tab == 'security' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'Security', 'disciple_tools' ) .'</a>';
    }

    public function content( $tab ) {
        if ( 'security' == $tab ) {


            self::template( 'begin' );

            $this->save_settings();
            $this->security_enable_box();

            self::template( 'right_column' );


            self::template( 'end' );
        }
    }

    public function security_enable_box() {
        $this->box( 'top', 'Enable and Configure Security Headers' );

        $xss_disabled = get_option( "dt_disable_header_xss" );
        $referer_disabled = get_option( "dt_disable_header_referer" );
        $content_type_disabled = get_option( "dt_disable_header_content_type" );
        $strict_transport_disabled = get_option( "dt_disable_header_strict_transport" );
        ?>
        <p>Here we set some security headers for the Theme. These are enabled by default. We recommend leaving them enabled unless you run into any issues.</p>

        <form method="POST" action="">
            <?php wp_nonce_field( 'security_headers', 'security_headers_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th>X-XSS-Protection</th>
                    <td><input name="xss" type="checkbox" value="1" <?php echo $xss_disabled ? '' : 'checked' ?>></td>
                    <td>Enable cross-site scripting filters.</td>
                </tr>
                <tr>
                    <th>Referrer-Policy</th>
                    <td><input name="referer" type="checkbox" value="1" <?php echo $referer_disabled ? '' : 'checked' ?>></td>
                    <td>Set Referrer Policy to "same-origin" to avoid leaking D.T activity</td>
                </tr>
                <tr>
                    <th>X-Content-Type-Options</th>
                    <td><input name="content_type" type="checkbox" value="1" <?php echo $content_type_disabled ? '' : 'checked' ?>></td>
                    <td>Stops a browser from trying to MIME-sniff the content type</td>
                </tr>
                <tr>
                    <th>Strict-Transport-Security</th>
                    <td><input name="strict_transport" type="checkbox" value="1" <?php echo $strict_transport_disabled ? '' : 'checked' ?>></td>
                    <td>Enforce the use of HTTPS.</td>
                </tr>


            </table>

            <button type="submit" class="button button-primary">Save Changes</button>
        </form>

        <?php
        $this->box( 'bottom' );
    }

    public function save_settings(){
        if ( !empty( $_POST ) ){
            if ( isset( $_POST['security_headers_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['security_headers_nonce'] ), 'security_headers' ) ) {
                update_option( "dt_disable_header_xss", isset( $_POST["xss"] ) ? "0" : "1" );
                update_option( "dt_disable_header_referer", isset( $_POST["referer"] ) ? "0" : "1" );
                update_option( "dt_disable_header_content_type", isset( $_POST["content_type"] ) ? "0" : "1" );
                update_option( "dt_disable_header_strict_transport", isset( $_POST["strict_transport"] ) ? "0" : "1" );

            }
        }
    }


}
Disciple_Tools_Security_Tab::instance();
