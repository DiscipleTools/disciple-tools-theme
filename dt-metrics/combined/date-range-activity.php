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
        'multi_select',
        'key_select',
        'tags',
        'communication_channel',
        'connection',
        'user_select',
        'text',
        'textarea',
        'link',
        'date',
        'number',
        'boolean',
        'location'
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
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'site_url' => site_url(),
                'state'              => [
                    'post_type' => $post_type,
                    'field' => $field
                ],
                'translations'       => [
                    'title_date_range_activity' => $this->title,
                    'post_type_select_label' => __( 'Post Type', 'disciple_tools' ),
                    'post_field_select_label' => __( 'Field', 'disciple_tools' ),
                    'post_field_select_any_activity_label' => __( 'Any Activity', 'disciple_tools' ),
                    'total_label' => __( 'Total', 'disciple_tools' ),
                    'date_select_label' => __( 'Date Range', 'disciple_tools' ),
                    'submit_button_label' => __( 'Reload', 'disciple_tools' ),
                    'results_table_head_title_label' => __( 'Title', 'disciple_tools' ),
                    'results_table_head_date_label' => __( 'Date', 'disciple_tools' ),
                    'results_table_head_new_value_label' => __( 'New Value', 'disciple_tools' ),
                    'regions_of_focus' => __( 'Regions of Focus', 'disciple_tools' ),
                    'all_locations' => __( 'All Locations', 'disciple_tools' )
                ],
                'select_options' => [
                    'post_type_select_options' => $this->post_type_select_options,
                    'post_field_select_options' => $this->post_field_select_options,
                ],
                'field_settings' => $this->field_settings
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
            $namespace, '/metrics/render_field_html', [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback' => [ $this, 'render_field_html' ],
                    'permission_callback' => '__return_true'
                ]
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
                    'methods'  => WP_REST_Server::CREATABLE,
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

    public function render_field_html( WP_REST_Request $request ){
        if ( !$this->has_permission() ){
            wp_send_json_error( new WP_Error( 'render_field_html', 'Missing Permissions', [ 'status' => 400 ] ) );
        }
        $params = $request->get_params();
        if ( isset( $params['post_type'], $params['field_id'] ) ){

            $post_type = $params['post_type'];
            $field_id = $params['field_id'];

            // Capture rendered field html.
            ob_start();
            $field_settings = DT_Posts::get_post_field_settings( $post_type );
            $field_settings[$field_id]['custom_display'] = false;
            render_field_for_display( $field_id, $field_settings, null );
            $rendered_field_html = ob_get_contents();
            ob_end_clean();

            return [
                'html' => $rendered_field_html
            ];
        }

        return [];
    }

    public function dummy_endpoint( WP_REST_Request $request ){
        return new WP_REST_Response( [] );
    }

    public function date_range_activity( WP_REST_Request $request ){
        if ( !$this->has_permission() ){
            wp_send_json_error( new WP_Error( 'date_range_activity', 'Missing Permissions', [ 'status' => 400 ] ) );
        }

        $params = $request->get_params();
        if ( isset( $params['post_type'], $params['field'], $params['ts_start'], $params['ts_end'] ) ){

            // Fetch associated field settings.
            $settings = $this->get_field_settings( $params['post_type'] )[$params['field']];
            $field_type = $settings['type'];

            // Proceed with generating corresponding select SQL parts.
            $field_type_sql = "AND field_type = '" . esc_sql( $field_type ) . "'";
            $meta_key_sql = "AND meta_key LIKE '" . esc_sql( $params['field'] ) . "'";

            // Accommodate special cases.
            if ( $field_type == 'communication_channel' ){
                $field_type_sql = "AND (field_type = '' OR field_type = '" . esc_sql( $field_type ) . "')";
                $meta_key_sql = "AND meta_key LIKE '" . esc_sql( $params['field'] ) . "%'";
                $meta_value_sql = "AND meta_value LIKE '" . ( empty( $params['value'] ) ? '%' : esc_sql( $params['value'] ) ) . "'";

            } elseif ( ( $field_type == 'connection' ) || ( $field_type == 'location' ) ){
                $meta_key_sql = ( $field_type == 'connection' ) ? "AND meta_key LIKE '%'" : "AND meta_key LIKE '" . esc_sql( $params['field'] ) . "'";

                $values = [];
                foreach ( $params['value'] ?? [] as $value ){
                    if ( isset( $value['ID'] ) ){
                        $values[] = $value['ID'];
                    }
                }
                $meta_value_sql = ( !empty( $values ) ? 'AND meta_value IN (' . dt_array_to_sql( $values ) . ')' : "AND meta_value LIKE '%'" );

            } elseif ( $field_type == 'user_select' ){
                $value = $params['value'];
                $meta_value_sql = ( !empty( $value ) ? "AND meta_value LIKE 'user-" . $value['ID'] . "'" : "AND meta_value LIKE '%'" );

            } else {
                $meta_value_sql = "AND meta_value LIKE '" . ( empty( $params['value'] ) ? '%' : esc_sql( $params['value'] ) ) . "'";
            }

            // Execute sql query.
            $supported_actions_sql = dt_array_to_sql( [
                'field_update',
                'connected to',
                'disconnected from'
            ] );

            global $wpdb;
            // phpcs:disable
            $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT *
            FROM $wpdb->dt_activity_log
            WHERE action IN ( $supported_actions_sql )
            AND object_type = %s
            AND hist_time BETWEEN %d AND %d
            $field_type_sql
            $meta_key_sql
            $meta_value_sql
            ORDER BY hist_time DESC;
            ", $params['post_type'], $params['ts_start'], $params['ts_end'] ), ARRAY_A );
            // phpcs:enable

            // Package result findings and return.
            $posts = [];
            foreach ( $results ?? [] as $activity ){

                // Determine new value label to be returned.
                $new_value = $activity['meta_value'] ?? '';
                if ( $field_type == 'connection' ){
                    if ( isset( $settings['post_type'], $new_value ) ){
                        $post = DT_Posts::get_post( $settings['post_type'], $new_value, true, false );
                        if ( !is_wp_error( $post ) ){
                            $new_value = $post['name'] ?? $new_value;
                        }
                    }
                } elseif ( $field_type == 'location' ){
                    $geocoder = new Location_Grid_Geocoder();
                    $new_value = $geocoder->_format_full_name( [ 'grid_id' => $new_value ] );

                } elseif ( $field_type == 'user_select' ){
                    if ( strpos( $new_value, 'user-' ) == 0 ){
                        $post = DT_Posts::get_post( 'contacts', Disciple_Tools_Users::get_contact_for_user( substr( $new_value, 5 ) ), true, false );
                        if ( !is_wp_error( $post ) ){
                            $new_value = $post['name'] ?? $new_value;
                        }
                    }
                } elseif ( $field_type == 'date' ){
                    $new_value = ( $new_value != 'value_deleted' ) ? gmdate( 'F j, Y, g:i A', $new_value ) : '';

                } elseif ( $field_type == 'boolean' ){
                    $new_value = ( $new_value == 1 ) ? __( 'True', 'disciple_tools' ) : __( 'False', 'disciple_tools' );

                } elseif ( isset( $settings['default'], $settings['default'][$new_value], $settings['default'][$new_value]['label'] ) ){
                    $new_value = $settings['default'][$new_value]['label'];

                } elseif ( $new_value == 'value_deleted' ){
                    $new_value = '';
                }

                $posts[] = [
                    'id' => $activity['object_id'],
                    'post_type' => $activity['object_type'],
                    'name' => $activity['object_name'],
                    'timestamp' => $activity['hist_time'],
                    'new_value' => $new_value
                ];
            }

            return [
                'total' => count( $posts ),
                'posts' => $posts
            ];
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
            if ( array_key_exists( 'private', $setting ) && $setting['private'] === true ) {
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
