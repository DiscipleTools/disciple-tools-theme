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
            * {
                box-sizing: border-box;
            }
            body, p {
                margin: 0
            }
            body {
                background: #f0f0f1;
                font-family: Helvetica,Arial,sans-serif;
                font-weight: 300;
                line-height: 1.5;
            }
            h1 {
                line-height: 1.4;
                margin-block-end: 0.5rem;
                margin-block-start: 0;
                font-weight: 300;
            }
            a {
                color: #3f729b;
                cursor: pointer;
                line-height: inherit;
                text-decoration: none;
            }
            #content {
                margin-block: 2rem;
                max-width: 23rem;
                width: calc( 90vw + (10 / 100 * 320px) - ( 2 * 0.5rem ) );
                margin-inline: auto;
            }
            #login {
                background-color: #fff;
                border: 1px solid hsla(0,0%,4%,.25);
                padding: 1rem;
                margin-block-end: 1rem;
            }
            .center {
                text-align: center;
            }
            .flow > * + * {
                margin-top: var(--flow-space, 1rem);
            }
            label {
                color: #0a0a0a;
                display: block;
                font-size: .9333333333rem;
                font-weight: 300;
                line-height: 1.8;
                margin: 0;
            }
            [type=text], [type=password] {
                background-color: #fefefe;
                border: 1px solid #cacaca;
                border-radius: 0;
                -webkit-box-shadow: inset 0 1px 2px hsla(0,0%,4%,.1);
                box-shadow: inset 0 1px 2px hsla(0,0%,4%,.1);
                color: #0a0a0a;
                display: block;
                font-family: inherit;
                font-size: 1rem;
                font-weight: 300;
                height: 2.5rem;
                line-height: 1.5;
                margin: 0 0 1.0666666667rem;
                padding: 0.5333333333rem;
                width: 100%;
            }
            .button {
                border: 1px solid transparent;
                border-radius: 5px;
                cursor: pointer;
                display: inline-block;
                font-family: inherit;
                font-size: .9rem;
                line-height: 1;
                padding: 0.85em 1em;
                text-align: center;
                -webkit-transition: background-color .25s ease-out,color .25s ease-out;
                transition: background-color .25s ease-out,color .25s ease-out;
                vertical-align: middle;
            }
            .button, .button.disabled, .button.disabled:focus, .button.disabled:hover, .button[disabled], .button[disabled]:focus, .button[disabled]:hover {
                background-color: #3f729b;
                color: #fefefe;
            }
            #loginform {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
            }
            .login-username, .login-password {
                width: 100%;
            }
            .form-error {
                display: none;
                font-size: .8rem;
                font-weight: 700;
                margin-bottom: 1.0666666667rem;
                margin-top: -0.5333333333rem;
            }

            .form-error, .is-invalid-label {
                color: #cc4b37;
            }
            .callout {
                background-color: white;
                border: ;
            }
            .calout.warning,
            .callout.alert {
                background-color: #f7e4e1;
                color: #0a0a0a;
                padding: 0.5rem 1rem;
                border: 1px solid hsla(0,0%,4%,.25);
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
        $allowed_css = [];

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
