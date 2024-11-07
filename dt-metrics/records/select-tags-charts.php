<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Select_Tags_Charts extends DT_Metrics_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'records'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'select_tags_charts'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/records/select-tags-charts.js'; // should be full file name plus extension
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
        if ( !$this->has_permission() ) {
            return;
        }

        $this->title = __( 'Simple Chart', 'disciple_tools' );
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

        $this->post_type_select_options = $post_type_options;
        $this->field_settings = $this->get_field_settings( $post_types[0] );
        $this->post_field_select_options = $this->create_select_options_from_field_settings( $this->field_settings );

        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function scripts() {
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
            'wp-i18n'
        ], filemtime( get_theme_file_path() . $this->js_file_name ), true );

        $post_type = $this->post_types[0];
        $field = array_keys( $this->post_field_select_options )[0];
        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'site' => esc_url_raw( site_url( '/' ) ),
                'state' => [
                    'chart_view' => 'year',
                    'post_type' => $post_type,
                    'field' => $field,
                    'year' => gmdate( 'Y' ),
                    'earliest_year' => DT_Counter_Post_Stats::get_earliest_year()
                ],
                'data' => [],
                'translations' => [
                    'title_select_tags_charts' => $this->title,
                    'description' => __( 'This chart shows the total number for each field value at the end of the selected date.', 'disciple_tools' ),
                    'post_type_select_label' => __( 'Record Type', 'disciple_tools' ),
                    'post_field_select_label' => __( 'Field', 'disciple_tools' ),
                    'date_select_label' => __( 'Date', 'disciple_tools' ),
                    'all_time' => __( 'All Time', 'disciple_tools' ),
                    'modal_title' => __( 'Records', 'disciple_tools' ),
                    'modal_table_head_title' => __( 'Title', 'disciple_tools' ),
                    'modal_no_records' => __( 'No Records Available', 'disciple_tools' )
                ],
                'select_options' => [
                    'post_type_select_options' => $this->post_type_select_options
                ],
                'multi_fields' => $this->post_field_types_filter,
                'field_settings' => $this->field_settings
            ]
        );
    }

    public function add_api_routes() {
        $version   = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/metrics/time_metrics_by_year/(?P<post_type>\w+)/(?P<field>\w+)/(?P<year>\d+)', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'time_metrics_by_year' ],
                    'permission_callback' => [ $this, 'has_permission' ],
                ],
            ]
        );

        register_rest_route(
            $namespace, '/metrics/field_settings/(?P<post_type>\w+)', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'field_settings' ],
                    'permission_callback' => [ $this, 'has_permission' ],
                ],
            ]
        );
    }

    public function time_metrics_by_year( WP_REST_Request $request ) {
        $url_params = $request->get_url_params();
        $post_type = $url_params['post_type'];
        $field = $url_params['field'];
        $year = $url_params['year'];

        $error = $this->check_input( $post_type, $field, $year );
        if ( $error ) {
            wp_send_json_error( $error );
        }

        return $this->get_stats_by_year( $post_type, $field, $year );
    }

    public function field_settings( WP_REST_Request $request ): array {
        $url_params = $request->get_url_params();
        return $this->get_field_settings( $url_params['post_type'] );
    }

    public function get_stats_by_year( $post_type, $field, $year ): array {
        $field_settings = $this->get_field_settings( $post_type );
        if ( in_array( $field_settings[$field]['type'], $this->post_field_types_filter ) ) {
            return DT_Counter_Post_Stats::get_multi_field_by_year( $post_type, $field, $year );
        } else {
            return [];
        }
    }

    public function get_field_settings( $post_type ): array {
        $post_field_settings = DT_Posts::get_post_field_settings( $post_type );

        $field_settings = [];

        foreach ( $post_field_settings as $key => $setting ) {
            if ( array_key_exists( 'hidden', $setting ) && $setting['hidden'] === true ) {
                continue;
            }
            if ( in_array( $setting['type'], $this->post_field_types_filter ) ){
                $field_settings[$key] = $setting;
            }
        }
        return $field_settings;
    }

    public function create_select_options_from_field_settings( $field_settings ): array {
        $select_options = [];
        foreach ( $field_settings as $key => $setting ) {
            $select_options[$key] = $setting['name'];
        }
        asort( $select_options );
        return $select_options;
    }

    private function check_input( $post_type, $field, $year = null ) {
        $current_year = gmdate( 'Y' );
        if ( !in_array( $post_type, $this->post_types, true ) ) {
            return new WP_Error( 'input_checker', 'not a suitable post type', [ 'status' => 400 ] );
        }
        if ( !array_key_exists( $field, $this->get_field_settings( $post_type ) ) ) {
            return new WP_Error( 'input_checker', 'not a suitable post type field', [ 'status' => 400 ] );
        }
        if ( $year !== null && $year > $current_year ) {
            return new WP_Error( 'input_checker                           ', 'year is in the future', [ 'status' => 400 ] );
        }

        return null;
    }
}
new DT_Metrics_Select_Tags_Charts();
