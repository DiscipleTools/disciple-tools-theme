<?php

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_SSO_Login
 */
class Disciple_Tools_SSO_Login extends Disciple_Tools_Abstract_Menu_Base
{
    private $token = 'sso-login';
    private $tab_title = 'SSO Login';

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
        add_submenu_page( 'dt_options', __( 'SSO Login', 'disciple_tools' ), __( 'SSO Login', 'disciple_tools' ), 'manage_dt', 'dt_options&tab=security', [ 'Disciple_Tools_Settings_Menu', 'content' ] );
    }

    public function add_tab( $tab ) {
        echo '<a href="'. esc_url( admin_url() ).'admin.php?page=dt_options&tab=sso-login" class="nav-tab ';
        if ( $tab == 'sso-login' ) {
            echo 'nav-tab-active';
        }
        echo '">'. esc_attr__( 'SSO Login', 'disciple_tools' ) .'</a>';
    }

    public function content( $settings_tab ) {
        if ( 'sso-login' !== $settings_tab ) {
            return;
        }

        if ( !current_user_can( 'manage_options' ) ) {
            var_dump( user_can( get_current_user_id(), 'manage_options' ) );
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        $link = 'admin.php?page=dt_options&tab='.$this->token.'&sub_tab=';

        if ( isset( $_GET["sub_tab"] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET["sub_tab"] ) );
        } else {
            $tab = 'general';
        }

        $vars = $this->process_postback();
        $is_dt = class_exists( 'Disciple_Tools' );
        $tabs = [];
        foreach ( $vars as $val ) {
            $tabs[$val['tab']] = ucwords( str_replace( '_', ' ', $val['tab'] ) );
        }
        ?>
        <div class="wrap">
            <h2><?php echo esc_html( $this->tab_title ) ?></h2>
            <h2 class="nav-tab-wrapper">
                <?php
                foreach ( $tabs as $key => $value ) {
                    ?>
                    <a href="<?php echo esc_attr( $link . $key ) ?>"
                       class="nav-tab <?php echo esc_html( ( $tab == $key ) ? 'nav-tab-active' : '' ); ?>"><?php echo esc_html( $value ) ?></a>
                    <?php
                }
                ?>
            </h2>
            <div class="wrap">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder">
                        <div id="post-body-content">
                            <!-- Box -->
                            <form method="post">
                                <?php wp_nonce_field( $this->token.get_current_user_id(), $this->token . '_nonce' ) ?>
                                <table class="widefat striped">
                                    <tbody>
                                    <?php
                                    if ( ! empty( $vars ) ) {
                                        foreach ( $vars as $key => $value ) {
                                            if ( $tab === $value['tab'] ) {
                                                $this->tab( $value );
                                            }
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="2">
                                            <button class="button" type="submit">Save</button> <button class="button" type="submit" style="float:right;" name="delete" value="1">Reset</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </form>
                            <br>
                            <!-- End Main Column -->
                        </div><!-- end post-body-content -->
                    </div><!-- post-body meta box container -->
                </div><!--poststuff end -->
            </div><!-- wrap end -->
        </div><!-- End wrap -->
        <?php
    }

    public function tab( $args ) {
        switch ( $args['type'] ) {
            case 'text':
                ?>
                <tr>
                    <td style="width:10%; white-space:nowrap;">
                       <strong><?php echo esc_html( $args['label'] ) ?></strong>
                    </td>
                    <td>
                        <input type="text" name="<?php echo esc_attr( $args['key'] ) ?>" value="<?php echo esc_attr( $args['value'] ) ?>" /> <?php echo esc_attr( $args['description'] ) ?>
                    </td>
                </tr>
                <?php
                break;
            case 'select':
                ?>
                <tr>
                    <td style="width:10%; white-space:nowrap;">
                       <strong><?php echo esc_html( $args['label'] ) ?></strong>
                    </td>
                    <td>
                        <select name="<?php echo esc_attr( $args['key'] ) ?>">
                            <option></option>
                            <?php
                            foreach ( $args['default'] as $item_key => $item_value ) {
                                ?>
                                <option value="<?php echo esc_attr( $item_key ) ?>" <?php echo ( $item_key === $args['value'] ) ? 'selected' : '' ?>><?php echo esc_html( $item_value ) ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <?php echo esc_html( $args['description'] ) ?>
                    </td>
                </tr>
                <?php
                break;
            case 'label':
                ?>
                <tr>
                    <td style="width:10%; white-space:nowrap;">
                       <strong><?php echo esc_html( $args['label'] ) ?></strong>
                    </td>
                    <td>
                        <?php echo esc_html( $args['description'] ) ?>
                        <?php echo ( isset( $args['description_2'] ) && ! empty( $args['description_2'] ) ) ? '<p>' . esc_html( $args['description_2'] ) . '</p>' : '' ?>
                    </td>
                </tr>
                <?php
                break;
            default:
                break;
        }
    }

    public function process_postback(){
        $vars = self::dt_sso_login_fields();

        // process POST
        if ( isset( $_POST[$this->token.'_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[$this->token.'_nonce'] ) ), $this->token . get_current_user_id() ) ) {
            $params = $_POST;

            foreach ( $params as $key => $param ) {
                if ( isset( $vars[$key]['value'] ) ) {
                    $vars[$key]['value'] = $param;
                }
            }

            if ( isset( $params['delete'] ) ) {
                delete_site_option( 'dt_sso_login_fields' );
            } else {
                update_site_option( 'dt_sso_login_fields', $vars );
            }
        }

        return $vars;
    }

    public static function dt_sso_login_fields() {
        $defaults = [

            // general
            'general_label' => [
                'tab' => 'general',
                'key' => 'general_label',
                'label' => 'GENERAL',
                'description' => '',
                'value' => '',
                'type' => 'label',
            ],
            'login_method' => [
                'tab' => 'general',
                'key' => 'login_method',
                'label' => 'Login Method',
                'description' => 'Login like Wordpress normally does or like a mobile app.',
                'default' => [
                    'wordpress' => DT_Login_Methods::WORDPRESS,
                    'mobile' => DT_Login_Methods::MOBILE,
                ],
                'value' => 'wordpress',
                'type' => 'select',
            ],
            'login_redirect_to' => [
                'tab' => 'general',
                'key' => 'login_redirect_to',
                'label' => 'Login Redirect',
                'description' => 'Url to redirect the user to after successful login',
                'value' => '/',
                'type' => 'text',

            ],

            'shortcode_firebase_logon_buttons' => [
                'tab' => 'shortcodes',
                'key' => 'shortcode_firebase_logon_buttons',
                'label' => 'Firebase Logon Buttons',
                'description' => '[dt_firebase_login_ui]',
                'description_2' => '',
                'value' => '',
                'type' => 'label',
            ],

            'shortcode_firebase_logout_script' => [
                'tab' => 'shortcodes',
                'key' => 'shortcode_firebase_logout_script',
                'label' => 'shortcode to add on your logout screen to log the user out if using the mobile login',
                'description' => '[dt_firebase_logout_script]',
                'description_2' => '',
                'value' => '',
                'type' => 'label',
            ],


            // firebase
            'firebase_config_label' => [
                'tab' => 'firebase',
                'key' => 'firebase_config_label',
                'label' => 'Where to find the config details',
                'description' => 'Go to your firebase console and in the project settings get the config details from your webapp https://console.firebase.google.com/',
                'description_2' => '',
                'value' => '',
                'type' => 'label',
            ],
            'firebase_api_key' => [
                'tab' => 'firebase',
                'key' => 'firebase_api_key',
                'label' => 'Firebase API Key',
                'description' => '',
                'value' => '',
                'type' => 'text',
            ],
            'firebase_project_id' => [
                'tab' => 'firebase',
                'key' => 'firebase_project_id',
                'label' => 'Firebase Project ID',
                'description' => '',
                'value' => '',
                'type' => 'text',
            ],
            'firebase_app_id' => [
                'tab' => 'firebase',
                'key' => 'firebase_app_id',
                'label' => 'Firebase App ID',
                'description' => '',
                'value' => '',
                'type' => 'text',
            ],

            'identity_providers' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_label',
                'label' => 'SSO Identity Providers',
                'description' => 'Choose which identity providers you are using. These also need to be set up in the Firebase project.',
                'value' => '',
                'type' => 'label',
            ],
            'identity_providers_email' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_email',
                'label' => 'Email and Password',
                'description' => '',
                'value' => 'on',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
            ],
            'identity_providers_google' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_google',
                'label' => 'Google',
                'description' => '',
                'value' => 'off',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
            ],
            'identity_providers_facebook' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_facebook',
                'label' => 'Facebook',
                'description' => '',
                'value' => 'off',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
            ],
            'identity_providers_microsoft' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_microsoft',
                'label' => 'Microsoft',
                'description' => '',
                'value' => 'off',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
            ],
            'identity_providers_apple' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_apple',
                'label' => 'Apple',
                'description' => '',
                'value' => 'off',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
            ],
            'identity_providers_twitter' => [
                'tab' => 'identity_providers',
                'key' => 'identity_providers_twitter',
                'label' => 'Twitter',
                'description' => '',
                'value' => 'off',
                'type' => 'select',
                'default' => [
                    'on' => 'on',
                    'off' => 'off',
                ],
            ],

        ];

        $defaults_count = count( $defaults );

        $saved_fields = get_option( 'dt_sso_login_fields', [] );
        $saved_count = count( $saved_fields );

        $fields = wp_parse_args( $saved_fields, $defaults );

        if ( $defaults_count !== $saved_count ) {
            update_option( 'dt_sso_login_fields', $fields, true );
        }

        return $fields;
    }

    /**
     * Get the value from the fields array
     * @param string $field_name
     * @return mixed
     */
    public static function dt_sso_login_field( string $field_name ) {
        $fields = self::dt_sso_login_fields();

        if ( !isset( $fields[$field_name] ) ) {
            return false;
        }

        $value = $fields[$field_name]['value'];

        return $value;
    }

}
Disciple_Tools_SSO_Login::instance();
