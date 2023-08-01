<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class DT_Login_Page_Base
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
    }

    public function theme_redirect() {
        $path = get_theme_file_path( 'template-blank.php' );
        include( $path );
        die();
    }

    public function _header(){
        do_action( 'dt_login_head_top' );
        wp_head();
        $this->header_style();
        $this->header_javascript();
        do_action( 'dt_login_head_bottom' );

    }
    public function header_style(){
        ?>
        <style>
            body {
                background: white;
            }
        </style>
        <?php
    }
    public function _browser_tab_title( $title ){
        return get_bloginfo( 'name' );
    }
    public function header_javascript(){
        ?>
        <script>
            let jsObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'translations' => [],
            ]) ?>][0]

            jQuery(document).ready(function(){
                clearInterval(window.fiveMinuteTimer)
            })
        </script>
        <?php
        return true;
    }
    public function _footer(){
        wp_footer();
    }
    public function scripts() {

    }
    public function _print_scripts(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php
        $allowed_js = [
            'jquery',
            'jquery-ui',
            'site-js',
            'lodash',
            'moment',
            'mapbox-gl',
            'mapbox-cookie',
            'mapbox-search-widget',
            'google-search-widget',
        ];

        $allowed_js = apply_filters( 'dt_login_allowed_js', $allowed_js );

        global $wp_scripts;

        if ( isset( $wp_scripts ) ){
            foreach ( $wp_scripts->queue as $key => $item ){
                if ( ! in_array( $item, $allowed_js ) ){
                    unset( $wp_scripts->queue[$key] );
                }
            }
        }
        unset( $wp_scripts->registered['mapbox-search-widget']->extra['group'] );
    }
    public function _print_styles(){
        // @link /disciple-tools-theme/dt-assets/functions/enqueue-scripts.php
        $allowed_css = [
            'foundation-css',
            'jquery-ui-site-css',
            'site-css',
            'mapbox-gl-css',
        ];

        $allowed_css = apply_filters( 'dt_login_allowed_css', $allowed_css );

        global $wp_styles;
        if ( isset( $wp_styles ) ) {
            foreach ( $wp_styles->queue as $key => $item ) {
                if ( !in_array( $item, $allowed_css ) ) {
                    unset( $wp_styles->queue[$key] );
                }
            }
        }
    }

    public function body(){

    }
}
