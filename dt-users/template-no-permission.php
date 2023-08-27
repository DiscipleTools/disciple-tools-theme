<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Disciple_Tools_No_Permission extends DT_Magic_Url_Base
{
    public $magic = false;
    public $parts = false;
    public $page_title = 'Home';
    public $root = 'system_app';
    public $type = 'no_permission';
    public $type_name = 'no_permission';

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
            add_action( 'template_redirect', array( $this, 'theme_redirect' ) );
            add_filter( 'dt_blank_access', array( $this, 'dt_blank_access' ), 100, 1 ); // allows non-logged in visit

            // header content
            add_filter( 'dt_blank_title', array( $this, 'page_tab_title' ) ); // adds basic title to browser tab
            add_action( 'wp_print_scripts', array( $this, 'print_scripts' ), 1500 ); // authorizes scripts
            add_action( 'wp_print_styles', array( $this, 'print_styles' ), 1500 ); // authorizes styles

            // page content
            add_action( 'dt_blank_head', array( $this, '_header' ) );
            add_action( 'dt_blank_footer', array( $this, '_footer' ) );
            add_action( 'dt_blank_body', array( $this, 'body' ) ); // body for no post key

            add_filter( 'dt_magic_url_base_allowed_css', array( $this, 'dt_magic_url_base_allowed_css' ), 10, 1 );
            add_filter( 'dt_magic_url_base_allowed_js', array( $this, 'dt_magic_url_base_allowed_js' ), 10, 1 );
            add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 99 );
        }
    }

    public function dt_blank_access() {
        require_once get_template_directory() . '/dt-core/setup-functions.php';
        dt_setup_roles_and_permissions(); //make sure roles are set up
        if ( current_user_can( 'access_disciple_tools' ) ) {
            dt_route_front_page();
        }
        else if ( ! is_user_logged_in() ) {
            dt_please_log_in();
        }
        return true;
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        return array(
            'jquery',
            'jquery-ui',
            'site-js',
        );
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        return array(
            'jquery-ui-site-css',
            'foundations-css',
        );
    }

    public function wp_enqueue_scripts() {
    }

    public function theme_redirect() {
        $path = get_theme_file_path( 'template-blank.php' );
        include $path;
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
                <?php esc_html_e( 'You are registered but an administrator needs to assign you access permissions.', 'disciple_tools' ) ?>
                <br><br>
￼               <a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Log Out', 'disciple_tools' )?></a>
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
