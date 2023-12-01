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
    private $is_site_admin;

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
        if ( dt_is_rest() ) {
            return;
        }

        if ( !current_user_can( 'manage_dt' ) ){
            return;
        }

        add_action( 'admin_menu', [ $this, 'add_submenu' ], 99 );
        add_action( 'dt_settings_tab_menu', [ $this, 'add_tab' ], 50, 1 ); // use the priority setting to control load order
        add_action( 'dt_settings_tab_content', [ $this, 'content' ], 99, 1 );

        $user_id = get_current_user_id();

        $this->is_site_admin = is_super_admin( $user_id );

        parent::__construct();
    } // End __construct()


    public function add_submenu() {
        add_submenu_page( 'dt_options', __( 'SSO Login', 'disciple_tools' ), __( 'SSO Login', 'disciple_tools' ), 'manage_dt', 'dt_options&tab='.$this->token, [ 'Disciple_Tools_Settings_Menu', 'content' ] );
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

        if ( !current_user_can( 'manage_dt' ) ) {
            var_dump( user_can( get_current_user_id(), 'manage_dt' ) );
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        $link = 'admin.php?page=dt_options&tab='.$this->token.'&sub_tab=';

        if ( isset( $_GET['sub_tab'] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET['sub_tab'] ) );
        } else {
            $tab = 'general';
        }

        $vars = $this->process_postback();
        $tabs = [];
        foreach ( $vars as $val ) {
            $tabs[$val['tab']] = ucwords( str_replace( '_', ' ', $val['tab'] ) );
        }
        ?>
        <div class="wrap">
            <h2><?php echo esc_html( $this->tab_title ) ?></h2>
            <?php if ( is_multisite() ) : ?>
            <p>
                Please configure the SSO Login settings from the
                <?php if ( class_exists( 'DT_Multisite' ) ) : ?>
                    <a href="<?php echo esc_url( network_admin_url( 'admin.php?page=disciple-tools-multisite&tab=sso-login' ) ) ?>">
                        Disciple.Tools Network Admin Dashboard </a>
                <?php else : ?>
                    Disciple.Tools Network Admin Dashboard
                <?php endif; ?>
                using the <a href="https://disciple.tools/plugins/multisite/" target="_blank">D.T Multisite plugin.</a>
            </p>
            <?php endif; ?>
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
                                    $any_multisite_level_args = false;
                                    if ( ! empty( $vars ) ) {
                                        foreach ( $vars as $key => $value ) {
                                            if ( $tab === $value['tab'] ) {
                                                $any_multisite_level_args = is_multisite() && !empty( $value['multisite_level'] );
                                                $this->tab( $value );
                                            }
                                        }
                                    }


                                    ?>
                                    <tr>
                                        <td colspan="2">
                                            <button class="button" type="submit">Save</button> <button class="button" <?php echo esc_attr( $any_multisite_level_args ? 'disabled' : '' ) ?> type="submit" style="float:right;" name="delete" value="1">Reset</button>
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
        $must_have_super_admin_rights = is_multisite() && !empty( $args['multisite_level'] );
        switch ( $args['type'] ) {
            case 'text':
                ?>
                <tr>
                    <td style="width:10%; white-space:nowrap;">
                       <strong><?php echo esc_html( $args['label'] ) ?></strong>
                    </td>
                    <td>
                        <input
                            type="<?php echo esc_attr( $must_have_super_admin_rights ? 'password' : 'text' ) ?>"
                            name="<?php echo esc_attr( $args['key'] ) ?>"
                            value="<?php echo esc_attr( $must_have_super_admin_rights ? 'hidden secret value' : $args['value'] ) ?>"
                            <?php echo $must_have_super_admin_rights ? 'disabled' : '' ?>
                        />
                        <?php echo esc_attr( $args['description'] ) ?>
                    </td>
                </tr>
                <?php
                break;
            case 'role':
            case 'select':

                $options = [];
                if ( $args['type'] === 'role' ) {
                    $roles = function_exists( 'dt_list_roles' ) ? dt_list_roles() : [ 'multiplier' => [ 'label' => 'Multiplier', 'disabled' => false ] ];

                    foreach ( $roles as $role_key => $role ) {
                        if ( $role['disabled'] === false ) {
                            $options[$role_key] = $role['label'];
                        }
                    }
                } else {
                    $options = $args['default'];
                }
                ?>
                <tr>
                    <td style="width:10%; white-space:nowrap;">
                       <strong><?php echo esc_html( $args['label'] ) ?></strong>
                    </td>
                    <td>
                        <select
                            name="<?php echo esc_attr( $args['key'] ) ?>"
                            <?php echo $must_have_super_admin_rights ? 'disabled' : '' ?>
                        >
                            <option></option>
                            <?php
                            foreach ( $options as $item_key => $item_value ) {
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
        // process POST
        $has_permission = current_user_can( 'manage_dt' );
        if ( $has_permission && isset( $_POST[$this->token.'_nonce'] )
            && wp_verify_nonce( sanitize_key( wp_unslash( $_POST[$this->token.'_nonce'] ) ), $this->token . get_current_user_id() ) ) {


            $params = dt_recursive_sanitize_array( $_POST );

            if ( isset( $params['delete'] ) ) {
                DT_Login_Fields::delete();
            } else {
                DT_Login_Fields::update( $params );
            }
        }

        $vars = DT_Login_Fields::all();

        return $vars;
    }
}
Disciple_Tools_SSO_Login::instance();
