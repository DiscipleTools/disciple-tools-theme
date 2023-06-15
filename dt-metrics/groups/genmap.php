<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Groups_Genmap extends DT_Metrics_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'groups'; // lowercase
    public $slug = 'genmap'; // lowercase
    public $base_title;
    public $title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/groups/genmap.js'; // should be full file name plus extension
    public $css_file_name = '/dt-metrics/common/jquery.orgchart.css'; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];
    public $namespace = null;

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->base_title = __( 'Genmap', 'disciple_tools' );
        $this->title = __( 'Groups Genmap', 'disciple_tools' );

        $url_path = dt_get_url_path( true );
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }

        $this->namespace = "dt-metrics/$this->base_slug/$this->slug";
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function add_api_routes() {

        $version = '1';
        $namespace = 'dt/v' . $version;
        register_rest_route(
            $namespace, '/metrics/group/genmap', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'get_genmap' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );

    }

    public function tree( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, 'Missing Permissions', [ 'status' => 400 ] );
        }
        return $this->get_group_generations_tree();
    }

    public function scripts() {
        wp_enqueue_script( 'dt_metrics_project_script', get_template_directory_uri() . $this->js_file_name, [
            'jquery',
            'lodash'
        ], filemtime( get_theme_file_path() . $this->js_file_name ), true );

        wp_enqueue_script( 'orgchart_js', 'https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.7.0/js/jquery.orgchart.min.js', [
            'jquery',
        ], '3.7.0', true );

        wp_enqueue_style( 'orgchart_css', get_template_directory_uri() . $this->css_file_name, [], filemtime( get_theme_file_path() . $this->css_file_name ) );

        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root' => esc_url_raw( rest_url() ),
                'theme_uri' => get_template_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => empty( DT_Mapbox_API::get_key() ) ? '' : DT_Mapbox_API::get_key(),
                'data' => $this->data(),
            ]
        );
    }

    public function data() {
        return [
            'translations' => [
                'title_group_tree' => __( 'Group Generation Tree', 'disciple_tools' ),
                'highlight_active' => __( 'Highlight Active', 'disciple_tools' ),
                'highlight_churches' => __( 'Highlight Churches', 'disciple_tools' ),
                'members' => __( 'Members', 'disciple_tools' ),
                'view_record' => __( 'View Record', 'disciple_tools' ),
                'assigned_to' => __( 'Assigned To', 'disciple_tools' ),
                'status' => __( 'Status', 'disciple_tools' ),
                'total_members' => __( 'Total Members', 'disciple_tools' ),
                'view_group' => __( 'View Group', 'disciple_tools' ),

            ],
        ];
    }

    public function get_genmap() {
        $query = dt_queries()->tree( 'multiplying_groups_only' );
        if ( is_wp_error( $query ) ){
            return $this->_circular_structure_error( $query );
        }
        if ( empty( $query ) ) {
            return $this->_no_results();
        }
        $menu_data = $this->prepare_menu_array( $query );
        return $this->build_group_array( 0, $menu_data, 0 );
    }
    public function prepare_menu_array( $query ) {
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
    public function build_group_array( $parent_id, $menu_data, $gen ) {
        $html = [];

        if ( isset( $menu_data['parents'][$parent_id] ) )
        {
            $children = [];
            $next_gen = $gen + 1;

            foreach ( $menu_data['parents'][$parent_id] as $item_id )
            {
                if ( isset( $menu_data['parents'][$item_id] ) )
                {
                    $children[] = $this->build_group_array( $item_id, $menu_data, $next_gen );
                }
            }
            $html = [
                'name' => $menu_data['items'][ $parent_id ]['name'] ?? 'All Groups' ,
                'title' => '(' . $gen . ') ' . ( $menu_data['items'][ $parent_id ]['name'] ?? 'All Groups' ),
                'children' => $children,
            ];

        }
        return $html;
    }

}
new DT_Metrics_Groups_Genmap();


