<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Disciple_Tools_Api_Keys
 * Generate api keys for DT. The api key can be used by external sites or
 * applications where there is no authenticated user.
 */
class Disciple_Tools_Api_Keys
{
    /**
     * @var object instance. The class instance
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Api_Keys Instance
     * Ensures only one instance of Disciple_Tools_Api_Keys is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @return Disciple_Tools_Api_Keys instance
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
     * @access public
     * @since  0.1.0
     */
    public function __construct()
    {
    }

    /**
     * Display an admin notice on the page
     *
     * @param $notice , the message to display
     * @param $type   , the type of message to display
     *
     * @access private
     * @since  0.1.0
     */
    private function admin_notice( $notice, $type )
    {
        echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>';
        echo esc_html( $notice );
        echo '</p></div>';
    }

    /**
     * The API keys page html
     *
     * @access public
     * @since  0.1.0
     */
    public function api_keys_page()
    {

        if ( !current_user_can( "manage_dt" ) ) {
            // I'm not sure this check is necessary, but it can't hurt.
            // Only admins are expected to have the "export" capability.
            throw new Exception( 'Current user does not have "export" capability' );
        }

        $keys = get_option( "dt_api_keys", [] );

        if ( isset( $_POST['api-key-view-field'] ) && wp_verify_nonce( sanitize_key( $_POST['api-key-view-field'] ), 'api-keys-view' ) ) {

            if ( isset( $_POST["application"] ) && !empty( $_POST["application"] ) ) {
                $client_id = wordwrap( strtolower( sanitize_text_field( wp_unslash( $_POST["application"] ) ) ), 1, '-', 0 );
                $token = bin2hex( random_bytes( 32 ) );
                if ( !isset( $keys[ $client_id ] ) ) {
                    $keys[ $client_id ] = [ "client_id" => $client_id, "client_token" => $token ];
                    update_option( "dt_api_keys", $keys );
                } else {
                    $this->admin_notice( "Application already exists", "error" );
                }
            } elseif ( isset( $_POST["delete"] ) ) {
                if ( $keys[ sanitize_text_field( wp_unslash( $_POST["delete"] ) ) ] ) {
                    unset( $keys[ sanitize_text_field( wp_unslash( $_POST["delete"] ) ) ] );
                    update_option( "dt_api_keys", $keys );
                }
            }
        }
        include 'views/api-keys-view.php';
    }

    /**
     * Check to see if an api key and token exist
     *
     * @param $client_id
     * @param $client_token
     *
     * @return bool
     */
    public function check_api_key( $client_id, $client_token )
    {
        $keys = get_option( "dt_api_keys", [] );

        return isset( $keys[ $client_id ] ) && $keys[ $client_id ]["client_token"] == $client_token;
    }

}
