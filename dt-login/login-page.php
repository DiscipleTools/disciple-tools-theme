<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


function dt_login_defaults() {
    $defaults = get_option( 'dt_login_defaults' );
    if ( empty( $defaults ) ) {
        $defaults = [
            'users_can_register' => get_option( 'users_can_register' ),
            'default_role' => 'registered',
            'login_url' => 'login',
            'redirect_url' => 'contacts',
        ];
        update_option( 'dt_login_defaults', $defaults, true );
    }
    return $defaults;
}


class Disciple_Tools_Login_Base extends DT_Login_Page_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        parent::__construct();

        add_filter( 'register_dt_login_vars', [ $this, 'register_dt_login_vars' ], 10, 1 );
        if ( is_admin() ) {
            add_action( 'dt_login_admin_fields', [ $this, 'dt_login_admin_fields' ], 5, 1 );
            add_filter( 'dt_login_admin_update_fields', [ $this, 'dt_login_admin_update_fields' ], 10, 1 );
        }

        $url = dt_get_url_path();
        if ( ( 'login' === substr( $url, 0, 5 ) ) ) {
            add_action( 'template_redirect', [ $this, 'theme_redirect' ] );

            add_filter( 'dt_blank_access', function(){ return true;
            } );
            add_filter( 'dt_allow_non_login_access', function(){ return true;
            }, 100, 1 );

            add_filter( 'dt_blank_title', [ $this, '_browser_tab_title' ] );
            add_action( 'dt_blank_head', [ $this, '_header' ] );
            add_action( 'dt_blank_footer', [ $this, '_footer' ] );
            add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key

            // load page elements
            add_action( 'wp_print_scripts', [ $this, '_print_scripts' ], 1500 );
            add_action( 'wp_print_styles', [ $this, '_print_styles' ], 1500 );

            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }

    }

    public function register_dt_login_vars( $vars ) {
        $defaults = dt_login_defaults();
        foreach ( $defaults as $k => $v ) {
            $vars[$k] = $v;
        }
        return $vars;
    }

    public function dt_login_admin_update_fields( $post_vars ) {
        $defaults = dt_login_defaults();

        // user register
        if ( isset( $post_vars['users_can_register'] ) ) {
            if ( $post_vars['users_can_register'] !== $defaults['users_can_register'] ) {
                $defaults['users_can_register'] = $post_vars['users_can_register'];
                update_option( 'dt_login_defaults', $defaults, true );
                update_option( 'users_can_register', 1, true );
            }
        } else {
            if ( ! empty( $defaults['users_can_register'] ) ) {
                $defaults['users_can_register'] = 0;
                update_option( 'dt_login_defaults', $defaults, true );
                update_option( 'users_can_register', 0, true );
            }
        }

        // roles
        if ( isset( $post_vars['default_role'] ) ) {
            if ( $post_vars['default_role'] !== $defaults['default_role'] ) {
                $defaults['default_role'] = $post_vars['default_role'];
                update_option( 'dt_login_defaults', $defaults, true );
            }
        }

        // login
        if ( isset( $post_vars['login_url'] ) ) {
            if ( $post_vars['login_url'] !== $defaults['login_url'] ) {
                $defaults['login_url'] = $post_vars['login_url'];
                update_option( 'dt_login_defaults', $defaults, true );
            }
        }

        // redirect
        if ( isset( $post_vars['redirect_url'] ) ) {
            if ( $post_vars['redirect_url'] !== $defaults['redirect_url'] ) {
                $defaults['redirect_url'] = $post_vars['redirect_url'];
                update_option( 'dt_login_defaults', $defaults, true );
            }
        }

        return $post_vars;
    }

    public function dt_login_admin_fields( $dt_login ) {
        ?>
        <tr>
            <td colspan="2">
                <strong>Global Settings</strong>
            </td>
        </tr>
        <tr>
            <th scope="row"></th>
            <td> <fieldset><legend class="screen-reader-text"><span>Membership</span></legend><label for="users_can_register">
                        <input name="users_can_register" type="checkbox" id="users_can_register" value="1" <?php checked( '1', get_option( 'users_can_register' ) ); ?> />
                        Anyone can register</label>
                </fieldset>
            </td>
        </tr>
        <tr>
            <th scope="row"></th>
            <td>
                Default role for new registrations. (Recommended: registered, multiplier, partner)<br>
                <select name="default_role">
                    <?php wp_dropdown_roles( $dt_login['default_role'] ?? 'registered' ); ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Navigation</strong>
            </td>
        </tr>
        <tr>
            <td style="font-size:1.2em; text-align: center;">
                <?php
                if ( empty( $dt_login['login_url'] ) ) {
                    echo '&#10060;';
                } else {
                    echo '&#9989;';
                }
                ?>
            </td>
            <td>
                <strong>Login URL</strong><br>
                <strong><?php echo esc_url( site_url( '/' ) ) ?></strong><input class="regular-text" name="login_url" placeholder="Login Page" value="<?php echo esc_attr( $dt_login['login_url'] ) ?>"/> <br>
            </td>
        </tr>
        <tr>
            <td style="font-size:1.2em; text-align: center;">
                <?php
                if ( empty( $dt_login['redirect_url'] ) ) {
                    echo '&#10060;';
                } else {
                    echo '&#9989;';
                }
                ?>
            </td>
            <td>
                <strong>Success URL</strong> <br>(when someone successfully logs in, where do they get redirected)<br>
                <strong><?php echo esc_url( site_url( '/' ) ) ?></strong><input class="regular-text" name="redirect_url" placeholder="Redirect Page" value="<?php echo esc_attr( $dt_login['redirect_url'] ) ?>"/> <br>
            </td>
        </tr>
        <?php
    }

    public function body(){
        require_once( get_template_directory() . '/dt-login/login-template.php' );
    }
}
Disciple_Tools_Login_Base::instance();
