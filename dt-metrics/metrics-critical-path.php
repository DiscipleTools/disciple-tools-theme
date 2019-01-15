<?php

Disciple_Tools_Metrics_Critical_Path::instance();
class Disciple_Tools_Metrics_Critical_Path extends Disciple_Tools_Metrics_Hooks_Base {
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    public function __construct() {
        if ( !$this->has_permission() ) {
            return;
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );


        $url_path = dt_get_url_path();
        if ( 'metrics' === substr( $url_path, '0', 7 ) ) {

            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 50 );

            if ( 'metrics/critical-path' === $url_path ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            }
        }
    }

    public function add_url( $template_for_url ) {
        $template_for_url['metrics/critical-path'] = 'template-metrics.php';

        return $template_for_url;
    }

    public function add_menu( $content ) {
        $content .= '
            <li><a href="">' . esc_html__( 'Critical Path', 'disciple_tools' ) . '</a>
                <ul class="menu vertical nested" id="path-menu" aria-expanded="true">
                    <li><a href="' . site_url( '/metrics/critical-path/' ) . '#project_critical_path" onclick="project_critical_path()">' . esc_html__( 'Critical Path', 'disciple_tools' ) . '</a></li>
                    <li><a href="' . site_url( '/metrics/critical-path/' ) . '#project_critical_path2" onclick="project_critical_path2()">' . esc_html__( 'Critical Path2', 'disciple_tools' ) . '</a></li>
                </ul>
            </li>
            ';

        return $content;
    }

    public function scripts() {
        wp_enqueue_script( 'dt_metrics_project_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics-critical-path.js', [
            'moment',
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts',
            'datepicker',
            'wp-i18n'
        ], filemtime( get_theme_file_path() . '/dt-metrics/metrics-critical-path.js' ) );

        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root'               => esc_url_raw( rest_url() ),
                'theme_uri'          => get_stylesheet_directory_uri(),
                'nonce'              => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id'    => get_current_user_id(),
                'map_key'            => dt_get_option( 'map_key' ),
                'data'               => $this->data(),
            ]
        );
    }

    public function data() {

        /**
         * Apply Filters before final enqueue. This provides opportunity for complete override or modification of chart.
         */

        return [
            'translations'  => [
                'title_critical_path' => __( 'Critical Path', 'disciple_tools' ),
                'label_select_year' => __( 'Select All time or a specific year to display', 'disciple_tools' ),
                'label_all_time' => __( 'All time', 'disciple_tools' ),
            ],
            'critical_path' => self::chart_critical_path( dt_date_start_of_year(), dt_date_end_of_year() ),
        ];
    }

    /**
     * API Routes
     */
    public function add_api_routes() {
        $version   = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/metrics/critical_path_by_year/(?P<id>[\w-]+)', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'critical_path_by_year' ],
                ],
            ]
        );
    }

    public function critical_path_by_year( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            return new WP_Error( "critical_path_by_year", "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();
        if ( isset( $params['id'] ) ) {
            if ( $params['id'] == 'all' ) {
                $start = 0;
                $end   = PHP_INT_MAX;
            } else {
                $year  = (int) $params['id'];
                $start = DateTime::createFromFormat( "Y-m-d", $year . '-01-01' )->getTimestamp();
                $end   = DateTime::createFromFormat( "Y-m-d", ( $year + 1 ) . '-01-01' )->getTimestamp();
            }
            $result = Disciple_Tools_Metrics_Hooks_Base::chart_critical_path( $start, $end );
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( "critical_path_by_year", "Missing a valid contact id", [ 'status' => 400 ] );
        }
    }

    public function _no_results() {
        return '<p>' . esc_attr( 'No Results', 'disciple_tools' ) . '</p>';
    }

}
