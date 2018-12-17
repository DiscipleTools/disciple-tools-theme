<?php

Disciple_Tools_Metrics_Prayer::instance();
class Disciple_Tools_Metrics_Prayer extends Disciple_Tools_Metrics_Hooks_Base
{
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        if ( !$this->has_permission() ){
            return;
        }

        $url_path = dt_get_url_path();
        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 50 );

            if ( 'metrics/prayer' === $url_path ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            }
        }
    }

    public function add_url( $template_for_url ) {
        $template_for_url['metrics/prayer'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '
            <li><a href="">' .  esc_html__( 'Prayer', 'disciple_tools' ) . '</a>
                <ul class="menu vertical nested" id="prayer-menu" aria-expanded="true">
                    <li><a href="'. site_url( '/metrics/prayer/' ) .'#prayer_overview" onclick="prayer_overview()">'. esc_html__( 'Overview', 'disciple_tools' ) .'</a></li>
                </ul>
            </li>
            ';
        return $content;
    }

    public function scripts() {
//        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
//        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
        wp_enqueue_script( 'dt_metrics_prayer_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics-prayer.js', [
            'jquery',
            'jquery-ui-core',
            //            'amcharts-core',
            //            'amcharts-charts',
        ], filemtime( get_theme_file_path() . '/dt-metrics/metrics-prayer.js' ), true );

        wp_localize_script(
            'dt_metrics_prayer_script', 'dtMetricsPrayer', [
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_stylesheet_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => dt_get_option( 'map_key' ),
                'data' => $this->data(),
            ]
        );
    }

    public function data() {

        /**
         * Apply Filters before final enqueue. This provides opportunity for complete override or modification of chart.
         */
        return [
            'translations' => [
                'title_overview' => __( 'Prayer Overview', 'disciple_tools' ),
                'label_counties' => __( 'Counties', 'disciple_tools' ),
            ],
            'hero_stats' => self::chart_prayer_hero_stats(),
        ];
    }

    /**
     * API Routes
     */
    public function add_api_routes() {
        $version = '1';
        $namespace = 'dt/v' . $version;

//        register_rest_route(
//            $namespace, '/metrics/project/tree', [
//                [
//                    'methods'  => WP_REST_Server::CREATABLE,
//                    'callback' => [ $this, 'tree' ],
//                ],
//            ]
//        );
    }

    public function chart_prayer_hero_stats() {
        return [];
    }


}