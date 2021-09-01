<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Disciple_Tools_No_Permission extends DT_Magic_Url_Base
{
    public $magic = false;
    public $parts = false;
    public $page_title = 'Home';
    public $root = "porch_app";
    public $type = 'home';

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
        if ( 'registered' === $url && ! dt_is_rest() ) {

            // register url and access
            add_action( "template_redirect", [ $this, 'theme_redirect' ] );
            add_filter( 'dt_blank_access', [ $this, 'dt_blank_access' ], 100, 1 ); // allows non-logged in visit

            // header content
            add_filter( "dt_blank_title", [ $this, "page_tab_title" ] ); // adds basic title to browser tab
            add_action( 'wp_print_scripts', [ $this, 'print_scripts' ], 1500 ); // authorizes scripts
            add_action( 'wp_print_styles', [ $this, 'print_styles' ], 1500 ); // authorizes styles

            // page content
            add_action( 'dt_blank_head', [ $this, '_header' ] );
            add_action( 'dt_blank_footer', [ $this, '_footer' ] );
            add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key

            add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
            add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
            add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ], 99 );
        }
    }

    public function dt_blank_access() {
        if ( user_can( get_current_user_id(), 'access_contacts' ) ) {
            dt_route_front_page();
        }
        else if ( ! is_user_logged_in() ) {
            dt_please_log_in();
        }
        return true;
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        return [
            'jquery',
            'jquery-ui',
            'site-js'
        ];
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        return [
            'jquery-ui-site-css',
            'foundations-css',
        ];
    }

    public function wp_enqueue_scripts() {
    }

    public function theme_redirect() {
        $path = get_theme_file_path( 'template-blank.php' );
        include( $path );
        die();
    }

    public function body(){
        ?>
        <style>
            body {
                background:white;
            }
        </style>
        <div class="grid-x">
            <div class="cell" style="text-align:center; padding-top:2em;">
                <?php esc_html_e( 'You are registered but an administrator needs to assign you access permissions.', 'disciple_tools' ) ?><br><br>
                <a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Log Off', 'disciple_tools' )?></a>
            </div>
        </div>
        <?php
    }

    public function footer_javascript(){
        ?>
        <script>
        </script>
        <?php
        return true;
    }
}
Disciple_Tools_No_Permission::instance();
