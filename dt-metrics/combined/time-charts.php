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

        // $post_types = DT_Posts::get_post_types(); // This is only giving [ 'peoplegroups' ] at the moment
        $post_types = [ 'contacts', 'groups', 'peoplegroups' ];
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

    public function scripts() {
        wp_register_script( 'datepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array(), false, true );
        wp_enqueue_style( 'datepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array() );

        wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, false, true );
        wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, false, true );

        wp_enqueue_script( 'dt_metrics_project_script', get_template_directory_uri() . $this->js_file_name, [
            'moment',
            'jquery',
            'jquery-ui-core',
            'amcharts-core',
            'amcharts-charts',
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
                'data'               => $this->get_stats_by_month( $this->post_types[0], array_key_first( $this->field_settings ), gmdate( "Y" ) ),
                'translations'       => [
                    "title_time_charts" => __( 'Time Charts', 'disciple_tools' ),
                    "post_type_select_label" => __( 'Post Type', 'disciple_tools' ),
                    "post_field_select_label" => __( 'Post Field', 'disciple_tools' ),
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

    public function get_stats_by_month( $post_type, $field, $year ) {
        return Disciple_Tools_Counter_Post_Stats::get_date_field_by_month( $post_type, $field, $year );
    }
}
new DT_Metrics_Time_Charts();