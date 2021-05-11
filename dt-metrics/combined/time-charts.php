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
    public $post_field_types_filter = [
        'date',
        'tags',
        'multi_select',
        'key_select',
        'connection',
        'boolean',
    ]; // connection and number would be interesting for additions to groups, and quick button usage
    public $multi_fields = [
        'tags',
        'multi_select',
        'key_select',
        'boolean',
    ];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }

        $this->title = __( 'Fields over Time Charts', 'disciple_tools' );
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
        $field = array_key_first( $this->post_field_select_options );
        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root'               => esc_url_raw( rest_url() ),
                'theme_uri'          => get_template_directory_uri(),
                'nonce'              => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id'    => get_current_user_id(),
                'state'              => [
                    'chart_view' => 'month',
                    'post_type' => $post_type,
                    'field' => $field,
                    'year' => gmdate( "Y" ),
                    'earliest_year' => $this->get_earliest_year(),
                ],
                'data'               => [],
                'translations'       => [
                    "title_time_charts" => __( 'Fields over Time Charts', 'disciple_tools' ),
                    "post_type_select_label" => __( 'Post Type', 'disciple_tools' ),
                    "post_field_select_label" => __( 'Field', 'disciple_tools' ),
                    "total_label" => __( 'Total', 'disciple_tools' ),
                    "added_label" => __( 'Added', 'disciple_tools' ),
                    "tooltip_label" => _x( '%1$s in %2$s', 'Total in January', 'disciple_tools' ),
                    "date_select_label" => __( 'Date', 'disciple_tools' ),
                    "all_time" => __( 'All time', 'disciple_tools' ),
                    "stacked_chart_title" => __( 'All cumulative totals', 'disciple_tools' ),
                    "cumulative_chart_title" => __( 'Single cumulative totals', 'disciple_tools' ),
                    "additions_chart_title" => __( 'Number added', 'disciple_tools' ),
                    "true_label" => __( 'Yes', 'disciple_tools' ),
                    "false_label" => __( 'No', 'disciple_tools' ),
                ],
                'select_options' => [
                    'post_type_select_options' => $this->post_type_select_options,
                    'post_field_select_options' => $this->post_field_select_options,
                ],
                'multi_fields' => $this->multi_fields,
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
            $namespace, '/metrics/time_metrics_by_year/(?P<post_type>\w+)/(?P<field>\w+)', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'time_metrics_by_year' ],
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
            wp_send_json_error( new WP_Error( "time_metrics_by_month", "Missing Permissions", [ 'status' => 400 ] ) );
        }


        $url_params = $request->get_url_params();
        $post_type = $url_params['post_type'];
        $field = $url_params['field'];
        $year = $url_params['year'];

        $error = $this->checkInput( $post_type, $field, $year );
        if ( $error ) {
            wp_send_json_error( $error );
        }

        return $this->get_stats_by_month( $url_params['post_type'], $url_params['field'], $url_params['year'] );
    }

    public function time_metrics_by_year( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            wp_send_json_error( new WP_Error( "time_metrics_by_year", "Missing Permissions", [ 'status' => 400 ] ) );
        }
        $url_params = $request->get_url_params();
        $post_type = $url_params['post_type'];
        $field = $url_params['field'];

        $error = $this->checkInput( $post_type, $field );
        if ( $error ) {
            wp_send_json_error( $error );
        }

        return $this->get_stats_by_year( $url_params['post_type'], $url_params['field'] );
    }

    public function field_settings( WP_REST_Request $request ) {
        if ( !$this->has_permission() ) {
            wp_send_json_error( new WP_Error( "get_field_settings", "Missing Permissions", [ 'status' => 400 ] ) );
        }
        $url_params = $request->get_url_params();
        return $this->get_field_settings( $url_params['post_type'] );
    }

    public function get_stats_by_month( $post_type, $field, $year ) {
        $field_settings = $this->get_field_settings( $post_type );
        if ( $field_settings[$field]['type'] === 'date' ) {
            return DT_Counter_Post_Stats::get_date_field_by_month( $post_type, $field, $year );
        } elseif ( in_array( $field_settings[$field]['type'], $this->multi_fields ) ) {
            return DT_Counter_Post_Stats::get_multi_field_by_month( $post_type, $field, $year );
        } elseif ( $field_settings[$field]['type'] === 'connection' ) {
            $connection_type = $field_settings[$field]['p2p_key'];
            return DT_Counter_Post_Stats::get_connection_field_by_month( $connection_type, $year );
        } else {
            return [];
        }
    }

    public function get_stats_by_year( $post_type, $field ) {
        $field_settings = $this->get_field_settings( $post_type );
        if ( $field_settings[$field]['type'] === 'date' ) {
            return DT_Counter_Post_Stats::get_date_field_by_year( $post_type, $field );
        } elseif ( in_array( $field_settings[$field]['type'], $this->multi_fields ) ) {
            return DT_Counter_Post_Stats::get_multi_field_by_year( $post_type, $field );
        } elseif ( $field_settings[$field]['type'] === 'connection' ) {
            $connection_type = $field_settings[$field]['p2p_key'];
            return DT_Counter_Post_Stats::get_connection_field_by_year( $connection_type );
        } else {
            return [];
        }
    }

    public function get_field_settings( $post_type ) {
        $post_field_settings = DT_Posts::get_post_field_settings( $post_type );

        $field_settings = [];

        foreach ($post_field_settings as $key => $setting) {
            if ( array_key_exists( 'hidden', $setting ) && $setting['hidden'] === true ) {
                continue;
            }
            if ( in_array( $setting['type'], $this->post_field_types_filter ) ) {
                $field_settings[$key] = $setting;
            }
        }
        return $field_settings;
    }

    public function get_earliest_year() {
        return DT_Counter_Post_Stats::get_earliest_year();
    }

    public function create_select_options_from_field_settings( $field_settings ) {
        $select_options = [];
        foreach ($field_settings as $key => $setting) {
            $select_options[$key] = $setting['name'];
        }
        asort( $select_options );
        return $select_options;
    }

    private function checkInput( $post_type, $field, $year = null ) {
        $current_year = gmdate( "Y" );
        if ( !in_array( $post_type, $this->post_types, true ) ) {
            return new WP_Error( 'time_metrics_by_month', 'not a suitable post type', [ 'status' => 400 ] );
        }
        if ( !array_key_exists( $field, $this->get_field_settings( $post_type ) ) ) {
            return new WP_Error( 'time_metrics_by_month', 'not a suitable post type field', [ 'status' => 400 ] );
        }
        if ( $year !== null && $year > $current_year ) {
            return new WP_Error( 'time_metrics_by_month', 'year is in the future', [ 'status' => 400 ] );
        }
    }
}
new DT_Metrics_Time_Charts();