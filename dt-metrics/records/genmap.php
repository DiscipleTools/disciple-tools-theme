<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Groups_Genmap extends DT_Metrics_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'records'; // lowercase
    public $slug = 'genmap'; // lowercase
    public $base_title;
    public $title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];
    public $namespace = null;

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->base_title = __( 'Genmap', 'disciple_tools' );
        $this->title = __( 'Generation Map', 'disciple_tools' );

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
            $namespace, '/metrics/records/genmap', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'tree' ],
                    'permission_callback' => function() {
                        return $this->has_permission();
                    }
                ],
            ]
        );
    }

    public function tree( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, 'Missing Permissions', [ 'status' => 400 ] );
        }
        $params = dt_recursive_sanitize_array( $request->get_params() );
        if ( ! isset( $params['p2p_type'], $params['p2p_direction'], $params['post_type'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters! [Required: p2p_type, p2p_direction, post_type ]', [ 'status' => 400 ] );
        }

        $query = $this->get_query( $params['post_type'], $params['p2p_type'], $params['p2p_direction'] );

        return $this->get_genmap( $query, $params['gen_depth_limit'] ?? 10, $params['focus_id'] ?? 0 );
    }

    public function scripts() {

        $js_file_name = 'dt-metrics/records/genmap.js';
        $js_uri = get_template_directory_uri() . "/$js_file_name";
        $js_dir = get_template_directory() . "/$js_file_name";
        wp_enqueue_script( 'dt_metrics_project_script', $js_uri, [
            'jquery',
            'lodash'
        ], filemtime( $js_dir ), true );

        wp_localize_script(
            'dt_metrics_project_script', 'dtMetricsProject', [
                'root' => esc_url_raw( rest_url() ),
                'site_url' => esc_url_raw( site_url() ),
                'theme_uri' => get_template_directory_uri(),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'map_key' => empty( DT_Mapbox_API::get_key() ) ? '' : DT_Mapbox_API::get_key(),
                'data' => [],
                'translations' => [
                    'title' => __( 'Generation Map', 'disciple_tools' ),
                    'highlight_active' => __( 'Highlight Active', 'disciple_tools' ),
                    'highlight_churches' => __( 'Highlight Churches', 'disciple_tools' ),
                    'members' => __( 'Members', 'disciple_tools' ),
                    'view_record' => __( 'View Record', 'disciple_tools' ),
                    'assigned_to' => __( 'Assigned To', 'disciple_tools' ),
                    'status' => __( 'Status', 'disciple_tools' ),
                    'total_members' => __( 'Total Members', 'disciple_tools' ),
                    'view_group' => __( 'View Group', 'disciple_tools' ),
                    'details' => [
                        'status' => __( 'Status', 'disciple_tools' ),
                        'groups' => __( 'Groups', 'disciple_tools' ),
                        'assigned_to' => __( 'Assigned To', 'disciple_tools' ),
                        'coaches' => __( 'Coaches', 'disciple_tools' ),
                        'type' => __( 'Type', 'disciple_tools' ),
                        'member_count' => __( 'Member Count', 'disciple_tools' ),
                        'members' => __( 'Members', 'disciple_tools' )
                    ],
                    'modal' => [
                        'add_child_title' => __( 'Add Child To', 'disciple_tools' ),
                        'add_child_name_title' => __( 'Name', 'disciple_tools' ),
                        'add_child_but' => __( 'Add Child', 'disciple_tools' ),
                        'focus_title' => __( 'Focus On Node', 'disciple_tools' ),
                        'focus_are_you_sure_question' => __( 'Are you sure you wish to focus on node?', 'disciple_tools' ),
                        'focus_yes' => __( 'Yes', 'disciple_tools' )
                    ],
                    'infinite_loops' => [
                        'title' => __( 'Infinite Loops', 'disciple_tools' )
                    ]
                ],
                'post_types' => Disciple_Tools_Core_Endpoints::get_settings()['post_types'] ?? []
            ]
        );

        wp_enqueue_script( 'orgchart_js', 'https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.7.0/js/jquery.orgchart.min.js', [
            'jquery',
        ], '3.7.0', true );

        $css_file_name = 'dt-metrics/common/jquery.orgchart.custom.css';
        $css_uri = get_template_directory_uri() . "/$css_file_name";
        $css_dir = get_template_directory() . "/$css_file_name";
        wp_enqueue_style( 'orgchart_css', $css_uri, [], filemtime( $css_dir ) );
    }

    public function get_query( $post_type, $p2p_type, $p2p_direction ) {
        global $wpdb;

        // p2p direction will govern overall query sql shape.
        if ( in_array( $p2p_direction, [ 'any', 'to' ] ) ) {
            $not_from = 'NOT';
            $not_to = '';
            $select_id = 'p2p_from';
            $select_parent_id = 'p2p_to';
        } else {
            $not_from = '';
            $not_to = 'NOT';
            $select_id = 'p2p_to';
            $select_parent_id = 'p2p_from';
        }

        $query = $wpdb->get_results( $wpdb->prepare( "
                    SELECT
                      a.ID         as id,
                      0            as parent_id,
                      a.post_title as name
                    FROM $wpdb->posts as a
                    WHERE a.post_type = %s
                    AND a.ID %1s IN (
                      SELECT DISTINCT (p2p_from)
                      FROM $wpdb->p2p
                      WHERE p2p_type = %s
                      GROUP BY p2p_from
                    )
                      AND a.ID %1s IN (
                      SELECT DISTINCT (p2p_to)
                      FROM $wpdb->p2p
                      WHERE p2p_type = %s
                      GROUP BY p2p_to
                    )
                    UNION
                    SELECT
                      p.%1s  as id,
                      p.%1s    as parent_id,
                      (SELECT sub.post_title FROM $wpdb->posts as sub WHERE sub.ID = p.%1s ) as name
                    FROM $wpdb->p2p as p
                    WHERE p.p2p_type = %s;
                ", $post_type, $not_from, $p2p_type, $not_to, $p2p_type, $select_id, $select_parent_id, $select_id, $p2p_type ), ARRAY_A );

        return $query;
    }

    public function get_genmap( $query, $depth_limit, $focus_id ) {

        if ( is_wp_error( $query ) ){
            return $this->_circular_structure_error( $query );
        }
        if ( empty( $query ) ) {
            return $this->_no_results();
        }
        $menu_data = $this->prepare_menu_array( $query );

        return $this->build_array( $focus_id ?? 0, $menu_data, 0, $depth_limit );
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

    public function build_array( $parent_id, $menu_data, $gen, $depth_limit ) {
        $children = [];
        if ( isset( $menu_data['parents'][$parent_id] ) && ( $gen < $depth_limit ) )
        {
            $next_gen = $gen + 1;

            foreach ( $menu_data['parents'][$parent_id] as $item_id )
            {
                $children[] = $this->build_array( $item_id, $menu_data, $next_gen, $depth_limit );
            }
        }
        $array = [
            'id' => $parent_id,
            'name' => $menu_data['items'][ $parent_id ]['name'] ?? 'SYSTEM',
            'content' => 'Gen ' . $gen,
            'children' => $children,
            'has_infinite_loop' => $this->has_infinite_loop( $parent_id, $children )
        ];

        return $array;
    }

    public function has_infinite_loop( $parent_id, $children ): bool {
        foreach ( $children ?? [] as $child ) {
            if ( $parent_id === $child['id'] ) {
                return true;
            }
            if ( !empty( $child['children'] ) ) {
                if ( $this->has_infinite_loop( $parent_id, $child['children'] ) ) {
                    return true;
                }
            }
        }

        return false;
    }
}
new DT_Metrics_Groups_Genmap();

