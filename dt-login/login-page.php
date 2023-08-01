<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


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

        $url = dt_get_url_path();
        $login_page_enabled = DT_Login_Fields::get( 'login_enabled' ) === 'on';

        if ( $login_page_enabled && ( 'login' === substr( $url, 0, 5 ) ) ) {
            add_action( 'template_redirect', [ $this, 'theme_redirect' ] );
            add_filter( 'dt_blank_access', function(){ return true;
            }, 100, 1 );
            add_filter( 'dt_allow_non_login_access', function(){ return true;
            }, 100, 1 );
            add_filter( 'dt_override_header_meta', function (){ return true;
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

    public function body(){

        do_action( 'dt_login_login_page_header' );

        require_once( get_template_directory() . '/dt-login/login-template.php' );
    }
}
Disciple_Tools_Login_Base::instance();
