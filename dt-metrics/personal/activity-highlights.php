<?php

class Disciple_Tools_Metrics_Personal_Activity_Highlights extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'personal'; // lowercase
    public $slug = 'activity-highlights'; // lowercase
    public $base_title;

    public $title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/personal/activity-highlights.js'; // should be full file name plus extension
    public $permissions = [];
    public $namespace = null;

    public function __construct() {
        if ( !$this->has_permission() ){
            return;
        }
        parent::__construct();
        $this->title = __( 'Activity Highlights', 'disciple_tools' );
        $this->base_title = __( 'Personal', 'disciple_tools' );
        $this->namespace = "dt-metrics/$this->base_slug/$this->slug";

        $url_path = dt_get_url_path();
        if ( "metrics/$this->base_slug/$this->slug" === $url_path || "metrics" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 10 );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function scripts() {
        wp_enqueue_script( 'dt_metrics_activity_script', get_template_directory_uri() . $this->js_file_name, [
            'jquery',
            'jquery-ui-core',
            'lodash'
        ], filemtime( get_theme_file_path() .  $this->js_file_name ), true );

        wp_localize_script(
            'dt_metrics_activity_script', 'dtMetricsActivity', [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . $this->namespace,
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_template_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'data' => [
                    'highlights' => self::get_user_highlights()
                ],
                'translations' => [
                    'title' => __( 'Activity Highlights', 'disciple_tools' ),
                    'all_time' => __( "All Time", 'disciple_tools' ),
                    'filter_to_date_range' => __( "Filter to date range", 'disciple_tools' ),
                ],
            ]
        );
    }

    public function add_api_routes()
    {
        register_rest_route(
            $this->namespace, 'highlights_data', [
                'methods'  => 'GET',
                'callback' => [ $this, 'api_highlights_data' ],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function api_highlights_data( WP_REST_Request $request ) {
        $params = $request->get_params();
        try {
            if (isset( $params['from'] ) && isset( $params['to'] ) ) {
                self::check_date_string( $params['from'] );
                self::check_date_string( $params['to'] );
                return self::get_user_highlights( $params['from'], $params['to'] );
            } if (isset( $params['start'] ) && isset( $params['end'] ) ) {
                self::check_date_string( $params['start'] );
                self::check_date_string( $params['end'] );
                return self::get_user_highlights( $params['start'], $params['end'] );
            } else {
                return self::get_user_highlights();
            }
        } catch (Exception $e) {
            error_log( $e );
            return new WP_Error( __FUNCTION__, "got error ", [ 'status' => 500 ] );
        }
    }

    private static function check_date_string( string $str ) {
        if ( ! preg_match( '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $str, $matches ) ) {
            return new WP_Error( "Could not parse date, expected YYYY-MM-DD format" );
        }
    }

    private static function get_user_highlights($date_start = null, $date_end = null) {
        $user_id = get_current_user_id();

        return [
            $user_id,
            $date_start,
            $date_end,
        ];
    }

}
new Disciple_Tools_Metrics_Personal_Activity_Highlights();
