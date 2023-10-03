<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Metrics_Generation_Tree extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'records'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'generation_tree'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/records/generation-tree.js'; // should be full file name plus extension
    public $permissions = [ 'view_project_metrics', 'dt_all_access_contacts' ];
    public $post_types = [];
    public $field_settings = [];
    public $post_type_select_options = [];
    public $post_field_select_options = [];
    public $post_field_types_filter = [
        'connection'
    ];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }

        $this->title = __( 'Generation Tree', 'disciple_tools' );
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

        $all_settings = Disciple_Tools_Core_Endpoints::get_settings();

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
                    'post_type_select_label' => __( 'Record Type', 'disciple_tools' ),
                    'post_field_select_label' => __( 'Field', 'disciple_tools' ),
                    'submit_button_label' => __( 'Generate', 'disciple_tools' ),
                    'show_all_button_label' => __( 'Show All', 'disciple_tools' ),
                    'modal' => [
                        'add_child_title' => __( 'Add Child To', 'disciple_tools' ),
                        'add_child_name_title' => __( 'Name', 'disciple_tools' ),
                        'add_child_but' => __( 'Add Child', 'disciple_tools' ),
                        'focus_title' => __( 'Focus On Node', 'disciple_tools' ),
                        'focus_are_you_sure_question' => __( 'Are you sure you wish to focus on node?', 'disciple_tools' ),
                        'focus_yes' => __( 'Yes', 'disciple_tools' )
                    ]
                ],
                'select_options' => [
                    'post_type_select_options' => $this->post_type_select_options,
                    'post_field_select_options' => $this->post_field_select_options,
                ],
                'field_settings' => $this->field_settings,
                'all_post_types' => $all_settings['post_types']
            ]
        );
    }

    public function add_api_routes() {
        $version   = '1';
        $namespace = 'dt/v' . $version;

        register_rest_route(
            $namespace, '/metrics/generation_tree', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'generation_tree' ],
                    'permission_callback' => function(){
                        return $this->has_permission();
                    },
                ],
            ]
        );
    }

    public function generation_tree( WP_REST_Request $request ): string{

        $params = $request->get_params();
        if ( isset( $params['post_type'], $params['field'] ) ){
            $post_type = $params['post_type'];
            $field = $params['field'];
            $field_settings = $this->get_field_settings( $post_type );

            // Ensure post types match.
            if ( $field_settings[$field]['post_type'] === $post_type ){

                // Determine query name to adopt; groups or other?
                $query = dt_queries()->tree( 'generation_tree_multiplying_only', [
                    'post_type' => $post_type,
                    'p2p_key' => $field_settings[$field]['p2p_key'] ?? ''
                ] );

                // Capture and encode circular errors + no results.
                if ( is_wp_error( $query ) ){
                    return $this->_circular_structure_error( $query );
                }
                if ( empty( $query ) ){
                    return $this->_no_results();
                }

                // Build generation tree html.
                $menu_data = $this->prepare_menu_array( $query );

                // Reshape if focus on id has been requested.
                $parent_id = 0;
                if ( !empty( $params['focus_id'] ) ) {
                    $focus_id = $params['focus_id'];
                    $parent_id = $menu_data['items'][$focus_id]['parent_id'] ?? 0;
                    $menu_data['parents'][$parent_id] = [ $focus_id ];

                    return $this->build_menu( $post_type, $parent_id, $menu_data, -1 );
                } else {
                    return $this->build_menu( $post_type, $parent_id, $menu_data, -1 );
                }
            }
        }

        return '';
    }

    public function get_field_settings( $post_type ): array{
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

    public function create_select_options_from_field_settings( $field_settings ): array{
        $select_options = [];
        foreach ( $field_settings as $key => $setting ) {
            $select_options[$key] = $setting['name'];
        }
        asort( $select_options );
        return $select_options;
    }

    private function prepare_menu_array( $query ): array{
        // prepare special array with parent-child relations
        $menu_data = array(
            'items' => array(),
            'parents' => array()
        );

        foreach ( $query as $menu_item )
        {
            $menu_data['items'][$menu_item['id']] = $menu_item;
            $menu_data['parents'][$menu_item['parent_id']][] = $menu_item['id'];
        }
        return $menu_data;
    }

    private function build_menu( $post_type, $parent_id, $menu_data, $gen, $unique_check = [] ): string{
        $html = '';

        if ( isset( $menu_data['parents'][$parent_id] ) )
        {
            $gen++;

            $first_section = '';
            if ( $gen === 0 ) {
                $first_section = 'first-section';
            }

            $html = '<ul class="ul-gen-'.esc_html( $gen ).'">';
            foreach ( $menu_data['parents'][$parent_id] as $item_id )
            {
                $html .= '<input type="hidden" class="li-gen-' . esc_html( $gen ) . '-id" value="' . esc_html( $item_id ) . '" />';
                $html .= '<li class="gen-node li-gen-' . esc_html( $gen ) . ' ' . esc_attr( $first_section ) . '">';
                $html .= '(' . esc_html( $gen ) . ') ';
                $html .= '<strong><a href="' . esc_url( site_url( '/' . $post_type . '/' ) ) . esc_html( $item_id ) . '" target="_blank">' . esc_html( $menu_data['items'][ $item_id ]['name'] ) . '</a>';
                $html .= '<span class="gen-node-controls" style="display: none; margin-left: 10px;">';
                $html .= '<i class="mdi mdi-account-multiple-plus-outline gen-node-control-add-child" style="font-size: 15px; cursor: pointer;" data-post_type="'. esc_html( $post_type ) .'" data-post_id="'. esc_html( $item_id ) .'" data-post_name="'. esc_html( $menu_data['items'][ $item_id ]['name'] ) .'"></i>';
                $html .= '<i class="mdi mdi-bullseye-arrow gen-node-control-focus" style="font-size: 15px; cursor: pointer; margin-left: 5px;" data-post_type="'. esc_html( $post_type ) .'" data-post_id="'. esc_html( $item_id ) .'" data-post_name="'. esc_html( $menu_data['items'][ $item_id ]['name'] ) .'"></i>';
                $html .= '</span>';
                $html .= '</strong><br>';

                // find child items recursively
                if ( !in_array( $item_id, $unique_check ) ){
                    $unique_check[] = $item_id;
                    $html .= $this->build_menu( $post_type, $item_id, $menu_data, $gen, $unique_check );
                }

                $html .= '</li>';
            }
            $html .= '</ul>';

        }
        return $html;
    }

}
new DT_Metrics_Generation_Tree();
