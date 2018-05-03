<?php

/**
 * Disciple_Tools_Keys_Tab
 *
 * @class      Disciple_Tools_Keys_Tab
 * @package    Disciple_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Keys_Tab
 */
class Disciple_Tools_Keys_Tab extends Disciple_Tools_Abstract_Menu_Base
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
        add_submenu_page( 'dt_options', __( 'Google Map API', 'disciple_tools' ), __( 'Google Map API', 'disciple_tools' ), 'manage_dt', 'admin.php?page=dt_options&tab=google-map', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 20, 1 );
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );

        parent::__construct();
    } // End __construct()

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=google-map" class="nav-tab ';
        if ( $tab == 'google-map' ) {
            echo 'nav-tab-active';
        }
        echo '">Google Map API</a>';
    }
    /**
     * Packages and returns tab page
     *
     * @return void
     */
    public function content( $tab )
    {
        if ( 'google-map' == $tab ) :
            $this->template( 'begin' );

                echo '<form method="post">';

                $this->google_map_api_key_metabox();

                echo '<br>';

                $this->get_your_own_google_key_metabox();

                echo '</form>';

            $this->template( 'right_column' );

            // end columns template
            $this->template( 'end' );

        endif;
    }

    public function google_map_api_key_metabox()
    {
        $this->handle_post();

        $current_key = dt_get_option( 'map_key' );
        ?>
        <form method="post">
            <?php wp_nonce_field( 'map_key_' . get_current_user_id() . '_nonce', 'map_key' . get_current_user_id() ) ?>
            <table class="widefat striped">
                <thead>
                <th colspan="2">Google Maps API Key</th>
                </thead>
                <tbody>
                <?php if ( $this->is_default_key( $current_key ) ) : ?>
                <tr>
                    <td style="max-width:150px;">
                        <label>Default Keys<br><span style="font-size:.8em; ">( You can begin with
                                    these keys, but because of popularity, these keys can hit their
                                    limits. It is recommended that you get your own private key from
                                    Google.)</span></label>
                    </td>

                    <td>
                        <select name="default_keys" style="width: 100%;" <?php echo $this->is_default_key( $current_key ) ? '' : 'disabled' ?>>
                            <?php
                            $default_keys = Disciple_Tools_Google_Geocode_API::default_google_api_keys();
                            foreach ( $default_keys as $key => $value ) {
                                echo '<option value="'.esc_attr( $key ).'" ';
                                if ( array_search( $current_key, $default_keys ) == $key ) {
                                    echo 'selected';
                                }
                                $number = $key + 1;
                                echo '>Starter Key ' . esc_attr( $number ) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php endif; ?>

                <tr>
                    <td>
                        <label>Add Your Own Key</label><br>
                        <span style="font-size:.8em;">(clear key and save to remove key)</span>
                    </td>
                    <td>
                        <input type="text" name="map_key" id="map_key" style="width: 100%;" value="<?php echo $this->is_default_key( $current_key ) ? '' : esc_attr( $current_key ) ?>"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <br><span style="float:right;"><button type="submit" class="button float-right">Save</button></span>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>

        <?php
    }

    public function handle_post()
    {
        if ( isset( $_POST[ 'map_key' . get_current_user_id() ] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ 'map_key' . get_current_user_id() ] ) ), 'map_key_' . get_current_user_id() . '_nonce' ) ) {
            if ( empty( $_POST['map_key'] ) ) {
                $default_keys = Disciple_Tools_Google_Geocode_API::default_google_api_keys();
                $count = count( $default_keys ) - 1;

                if ( ! empty( $_POST['default_keys'] ) ) {
                    $submitted_key = sanitize_text_field( wp_unslash( $_POST['default_keys'] ) );

                    if ( isset( $default_keys[ $submitted_key ] ) ) { // check if set
                        if ( $default_keys[ $submitted_key ] <= $count ) { // check if it is a valid default key number
                            update_option( 'dt_map_key', $default_keys[ $submitted_key ] );
                        }
                    }
                } else {
                    $key = $default_keys[ rand( 0, $count ) ];
                    update_option( 'dt_map_key', $key );
                }
            }
            else {
                    dt_write_log( 'not empty map_key' );
                update_option( 'dt_map_key', trim( sanitize_text_field( wp_unslash( $_POST['map_key'] ) ) ) );
                return;
            }
        }
    }

    public function is_default_key( $current_key ): bool
    {

        if ( empty( $current_key ) ) {
            dt_get_option( 'map_key' );
            return true;
        }


        $default_keys = Disciple_Tools_Google_Geocode_API::default_google_api_keys();
        foreach ( $default_keys as $default_key ) {
            if ( $default_key === $current_key ) {
                return true;
            }
        }
        return false;
    }

    public function get_your_own_google_key_metabox()
    {
        ?>
        <table class="widefat striped">
            <thead>
            <th colspan="2">Getting Your Own Google API Key</th>
            </thead>
            <tbody>
            <tr>
                <td>
                    <p>Because of Google API limits, Disciple Tools default keys will overrun daily limits. Getting your
                        own key for your site is both free and fairly simple. Follow these steps:</p>
                    <ol>
                        <li>First, you need a Gmail Account and to login to it. (<a href="https://myaccount.google.com"
                                                                                    target="_blank" rel="noopener nofollow
            noreferrer">Login</a> or <a href="https://accounts.google.com/SignUp" target="_blank" rel="noopener nofollow
            noreferrer">Create New Gmail Account</a>)
                        </li>
                        <li>Next go to <a href="https://developers.google.com/maps/documentation/javascript/"
                                          target="_blank" rel="noopener">Google Maps Javascript API key website</a>.
                            <br><img class="img-center" title="Get a key"
                                 src="<?php echo esc_url( Disciple_Tools::instance()->plugin_img_url ) ?>google-api-1-screenshot.png" alt="Get a key" />
                        </li>
                        <li>In “<strong>Activate the Google Maps JavaScript API</strong>” popup window, changed the default title (optional), select "Yes" to the Terms of Service, and then click “Next”.
                            <br><img
                                    title="Activate the Google Maps JavaScript API"
                                    src="<?php echo esc_url( Disciple_Tools::instance()->plugin_img_url ) ?>google-api-2-screenshot.png"
                                    alt="Activate the Google Maps JavaScript API" />
                        </li>
                        <li>After this, you will get your Google maps API key, copy it.<br>
                            <img title="Copy API Key"
                                    src="<?php echo esc_url( Disciple_Tools::instance()->plugin_img_url ) ?>google-api-3-screenshot.png"
                                    alt="Copy API Key" />
                        </li>
                        <li>Paste this key into the "Add Your Own Key" field above and save it. That's it!</li>
                    </ol>


                </td>
            </tr>
            <tr><td>

                    More information on the Google API limits:<br>
                    <p><a href="https://developers.google.com/maps/pricing-and-plans/standard-plan-2016-update"
                          target="_blank" rel="noopener">Here</a> you can get more information about all updates.
                        Also please check all <a
                                href="https://developers.google.com/maps/documentation/javascript/usage"
                                target="_blank" rel="noopener">Maps JavaScript API Usage Limits</a>.</p>
                </td></tr>
            </tbody>
        </table><br>
        <?php
    }
}
Disciple_Tools_Keys_Tab::instance();