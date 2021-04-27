<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Time_Charts extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'combined'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'time_charts'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/combined/time-charts.js'; // should be full file name plus extension
    public $permissions = [ 'access_contacts', 'view_project_metrics' ];
    public $post_types = [];
    public $field_settings = [];
    public $post_type_select_options = [];
    public $post_field_select_options = [];
    public $post_field_types_filter = [ 'date', /* 'key_select', 'multi_select', */ /* 'connection', 'number' */ ]; // connection and number would be interesting for additions to groups, and quick button usage

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }

        $this->title = __( 'Time Charts', 'disciple_tools' );
        $this->base_title = __( 'Project', 'disciple_tools' );

        $url_path = dt_get_url_path();
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {

            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

        }

        $post_types = DT_Posts::get_post_types();
        $post_types = array_values( array_diff( $post_types, [ "peoplegroups" ] ) ); //skip people groups for now.
        $this->post_types = $post_types;
        $post_type_options = [];
        foreach ($post_types as $post_type) {
            $post_type_options[$post_type] = DT_Posts::get_label_for_post_type( $post_type );
        }

        $this->field_settings = $this->get_field_settings( $post_types[0] );
        $this->post_field_select_options = $this->field_settings;

        $this->post_type_select_options = apply_filters( 'dt_time_chart_select_options', $post_type_options );

        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function scripts() {
        wp_register_script( 'datepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array(), false, true );
        wp_enqueue_style( 'datepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array() );

        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, false, true );
        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, false, true );
        wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', [ 'amcharts-core' ], '4', true );

        wp_enqueue_script( 'dt_metrics_project_script', get_template_directory_uri() . $this->js_file_name, [
            'moment',
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts',
            'amcharts-animated',
            'datepicker',
            'wp-i18n'
        ], filemtime( get_theme_file_path() . $this->js_file_name ), true );

        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root'               => esc_url_raw( rest_url() ),
                'theme_uri'          => get_template_directory_uri(),
                'nonce'              => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id'    => get_current_user_id(),
                'state'              => [
                    'chart_view' => 'month',
                    'post_type' => $this->post_types[0],
                    'field' => array_key_first( $this->field_settings ),
                    'year' => gmdate( "Y" ),
                ],
                'data'               => [],
                'translations'       => [
                    "title_time_charts" => __( 'Time Charts', 'disciple_tools' ),
                    "post_type_select_label" => __( 'Post Type', 'disciple_tools' ),
                    "post_field_select_label" => __( 'Post Field', 'disciple_tools' ),
                    "total_label" => __( 'Total', 'disciple_tools' ),
                    "added_label" => __( 'Added', 'disciple_tools' ),
                    "tooltip_label" => _x( '%1$s in %2$s', 'Total in January', 'disciple_tools' ),
                ],
                'select_options' => [
                    'post_type_select_options' => $this->post_type_select_options,
                    'post_field_select_options' => $this->post_field_select_options,
                ],
                'fields_type_filter' => $this->post_field_types_filter,
                'field_settings' => $this->field_settings,
            ]
        );
    }

    public function add_api_routes() {
        $version   = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/metrics/time_metrics_by_month/(?P<post_type>\w+)/(?P<field>\w+)/(?P<year>\d+)', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'time_metrics_by_month' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

        register_rest_route(
            $namespace, '/metrics/field_settings/(?P<post_type>\w+)', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'field_settings' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
    }

    public function time_metrics_by_month( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            return new WP_Error( "time_metrics_by_month", "Missing Permissions", [ 'status' => 400 ] );
        }
        $url_params = $request->get_url_params();
        return $this->get_stats_by_month( $url_params['post_type'], $url_params['field'], $url_params['year'] );
    }

    public function field_settings( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            return new WP_Error( "get_field_settings", "Missing Permissions", [ 'status' => 400 ] );
        }
        $url_params = $request->get_url_params();
        return $this->get_field_settings( $url_params['post_type'] );
    }

    public function get_stats_by_month( $post_type, $field, $year ) {
        return Disciple_Tools_Counter_Post_Stats::get_date_field_by_month( $post_type, $field, $year );
    }

    public function get_field_settings( $post_type ) {
        $post_field_settings = DT_Posts::get_post_field_settings( $post_type );

        $field_settings = [];

        foreach ($post_field_settings as $key => $setting) {
            if ( in_array( $setting['type'], $this->post_field_types_filter ) ) {
                $field_settings[$key] = $setting['name'];
            }
        }
        asort( $field_settings );
        return $field_settings;
    }
}
new DT_Metrics_Time_Charts();