<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Date_Range_Activity extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'combined'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'date_range_activity'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/combined/date-range-activity.js'; // should be full file name plus extension
    public $permissions = [ 'view_project_metrics', 'dt_all_access_contacts' ];
    public $post_types = [];
    public $field_settings = [];
    public $post_type_select_options = [];
    public $post_field_select_options = [];
    public $post_field_types_filter = [
        'tags',
        'multi_select',
        'key_select'
    ];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }

        $this->title = __( 'Activity During Date Range', 'disciple_tools' );
        $this->base_title = __( 'Project', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {

            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

        }

        $post_types = DT_Posts::get_post_types();
        $post_types = array_values( array_diff( $post_types, [ 'peoplegroups' ] ) ); //skip people groups for now.
        $this->post_types = $post_types;
        $post_type_options = [];
        foreach ( $post_types as $post_type ) {
            $post_type_options[$post_type] = DT_Posts::get_label_for_post_type( $post_type );
        }

        $this->field_settings = $this->get_field_settings( $post_types[0] );
        $this->post_field_select_options = $this->create_select_options_from_field_settings( $this->field_settings );

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


        $post_type = $this->post_types[0];
        $field = array_keys( $this->post_field_select_options )[0];
        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root'               => esc_url_raw( rest_url() ),
                'site_url' => site_url(),
                'state'              => [
                    'post_type' => $post_type,
                    'field' => $field
                ],
                'translations'       => [
                    'title_date_range_activity' => $this->title,
                    'post_type_select_label' => __( 'Post Type', 'disciple_tools' ),
                    'post_field_select_label' => __( 'Field', 'disciple_tools' ),
                    'total_label' => __( 'Total', 'disciple_tools' ),
                    'date_select_label' => __( 'Date', 'disciple_tools' ),
                    'submit_button_label' => __( 'Reload', 'disciple_tools' ),
                    'results_table_head_title_label' => __( 'Title', 'disciple_tools' ),
                    'results_table_head_date_label' => __( 'Date', 'disciple_tools' )
                ],
                'select_options' => [
                    'post_type_select_options' => $this->post_type_select_options,
                    'post_field_select_options' => $this->post_field_select_options,
                ],
                'field_settings' => $this->field_settings,
                'field_conditions' => [
                    'equal' => __( 'Equal To', 'disciple_tools' ),
                    'not_equal' => __( 'Not Equal To', 'disciple_tools' )
                ]
            ]
        );
    }

    public function add_api_routes() {
        $version   = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/metrics/field_settings/(?P<post_type>\w+)', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'field_settings' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

        register_rest_route(
            $namespace, '/metrics/dummy_endpoint', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'dummy_endpoint' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

        register_rest_route(
            $namespace, '/metrics/date_range_activity', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'date_range_activity' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
    }

    public function field_settings( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            wp_send_json_error( new WP_Error( 'get_field_settings', 'Missing Permissions', [ 'status' => 400 ] ) );
        }
        $url_params = $request->get_url_params();
        return $this->get_field_settings( $url_params['post_type'] );
    }

    public function dummy_endpoint( WP_REST_Request $request ){
        return new WP_REST_Response( [] );
    }

    public function date_range_activity( WP_REST_Request $request ){
        if ( !$this->has_permission() ){
            wp_send_json_error( new WP_Error( 'date_range_activity', 'Missing Permissions', [ 'status' => 400 ] ) );
        }

        $params = $request->get_params();

        if ( !empty( $params['value'] ) ){
            $value = ( ( $params['condition'] == 'not_equal' ) ? '-' : '' ) . trim( $params['value'] );

            return DT_Posts::list_posts( $params['post_type'], [
                'assigned_to' => [ 'me' ],
                'post_date' => [
                    'start' => $params['ts_start'],
                    'end' => $params['ts_end']
                ],
                'sort' => '-post_date',
                $params['field'] => [ $value ]
            ], false );

        }

        return [];
    }

    public function get_field_settings( $post_type ) {
        $post_field_settings = DT_Posts::get_post_field_settings( $post_type );

        $field_settings = [];

        foreach ( $post_field_settings as $key => $setting ) {
            if ( array_key_exists( 'hidden', $setting ) && $setting['hidden'] === true ) {
                continue;
            }
            if ( in_array( $setting['type'], $this->post_field_types_filter ) ) {
                $field_settings[$key] = $setting;
            }
        }
        return $field_settings;
    }

    public function create_select_options_from_field_settings( $field_settings ) {
        $select_options = [];
        foreach ( $field_settings as $key => $setting ) {
            $select_options[$key] = $setting['name'];
        }
        asort( $select_options );
        return $select_options;
    }

}
new DT_Metrics_Date_Range_Activity();
