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
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );

        if ( !$this->has_permission() ) {
            return;
        }

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
                    <li><a href="' . site_url( '/metrics/critical-path/' ) . '#project_seeker_path" onclick="project_seeker_path()">' . esc_html__( 'Seeker Path', 'disciple_tools' ) . '</a></li>
                </ul>
            </li>
            ';

        return $content;
    }

    public function scripts() {
        wp_register_script( 'datepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', false );
        wp_enqueue_style( 'datepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array() );

        wp_enqueue_script( 'dt_metrics_project_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics-critical-path.js', [
            'moment',
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts',
            'datepicker',
        ], filemtime( get_theme_file_path() . '/dt-metrics/metrics-project.js' ) );

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
            'seeker_path' => $this->seeker_path()
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
        register_rest_route(
            $namespace, '/metrics/seeker_path/', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'seeker_path_endpoint' ],
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

    public function seeker_path_endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( "critical_path_by_year", "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();
        if ( isset( $params["start"], $params["end"] ) ){
            $start = strtotime( $params["start"] );
            $end = strtotime( $params["end"] );
            $result = $this->seeker_path( $start, $end );
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                return new WP_REST_Response( $result );
            }
        } else {
            return new WP_Error( "seeker_path", "Missing a valid values", [ 'status' => 400 ] );
        }


    }

    public function _no_results() {
        return '<p>' . esc_attr( 'No Results', 'disciple_tools' ) . '</p>';
    }



    public function seeker_path( $start = null, $end = null ){
        global $wpdb;
        if ( empty( $start ) ){
            $start = 0;
        }
        if ( empty( $end ) ){
            $end = time();
        }

        $res = $wpdb->get_results( $wpdb->prepare( "
            SELECT COUNT( DISTINCT(log.object_id) ) as `value`, log.meta_value as seeker_path
            FROM $wpdb->dt_activity_log log
            INNER JOIN $wpdb->posts post
            WHERE log.object_type = 'contacts'
            AND log.meta_key = 'seeker_path'
            AND log.hist_time > %s
            AND log.hist_time < %s
            AND post.post_type = 'contacts'
            AND post.post_status = 'publish'
            GROUP BY log.meta_value
        ", $start, $end ), ARRAY_A );

        $field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $seeker_path_options = $field_settings["seeker_path"]["default"];
        $seeker_path_data = [];

        foreach ( $seeker_path_options as $option_key => $option_value ){
            $seeker_path_data[$option_value["label"]] = 0;
            foreach ( $res as $r ){
                if ( $r["seeker_path"] === $option_key ){
                    $seeker_path_data[$option_value["label"]] = $r["value"];
                }
            }
        }
        $return = [];
        foreach ( $seeker_path_data as $k => $v ){
            $return[] = [
                "seeker_path" => $k,
                "value" => (int) $v
            ];
        }

        return $return;
    }
}
