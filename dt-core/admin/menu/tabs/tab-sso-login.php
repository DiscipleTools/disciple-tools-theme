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
            if ( $is_dt === $val['requires_dt'] ) {
                $tabs[$val['tab']] = ucwords( $val['tab'] );
            }
            elseif ( ! $val['requires_dt'] ) {
                $tabs[$val['tab']] = ucwords( $val['tab'] );
            }
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
                                                if ( $is_dt === $value['requires_dt'] ) {
                                                    $this->tab( $value );
                                                }
                                                elseif ( ! $value['requires_dt'] ) {
                                                    $this->tab( $value );
                                                }
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
        $vars = dt_sso_login_fields();

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
                delete_option( 'dt_sso_login_fields' );
            } else {
                update_option( 'dt_sso_login_fields', $vars, true );
            }
        }

        return $vars;
    }

}
Disciple_Tools_SSO_Login::instance();
