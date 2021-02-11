<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Personal_Groups_Tree extends DT_Metrics_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'personal'; // lowercase
    public $slug = 'group-tree'; // lowercase
    public $base_title;
    public $title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/personal/group-tree.js'; // should be full file name plus extension
    public $permissions = [ 'access_contacts' ];
    public $namespace = null;
    public $my_list = [];

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->base_title = __( 'Personal', 'disciple_tools' );
        $this->title = __( 'My Groups Tree', 'disciple_tools' );

        $url_path = dt_get_url_path();
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
            $namespace, '/metrics/my/group_tree', [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'tree' ],
                ],
            ]
        );

    }

    public function tree( WP_REST_Request $request ) {
        if ( !$this->has_permission() ){
            return new WP_Error( __METHOD__, "Missing Permissions", [ 'status' => 400 ] );
        }
        return $this->get_group_generations_tree();
    }

    public function scripts() {
        wp_enqueue_script( 'dt_metrics_project_script', get_template_directory_uri() . $this->js_file_name, [
            'jquery',
            'lodash'
        ], filemtime( get_theme_file_path() . $this->js_file_name ), true );

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
                'view_record' => __( "View Record", "disciple_tools" ),
                'assigned_to' => __( "Assigned To", "disciple_tools" ),
                'status' => __( "Status", "disciple_tools" ),
                'total_members' => __( "Total Members", "disciple_tools" ),
                'view_group' => __( "View Group", "disciple_tools" ),
            ],
        ];
    }

    public function get_group_generations_tree(){
        global $wpdb;
        $user = wp_get_current_user();
        $generational_groups_shared_with_me = $wpdb->get_results( $wpdb->prepare( "
            SELECT s.post_id FROM $wpdb->p2p p2p
            INNER JOIN $wpdb->dt_share s ON ( ( s.post_id = p2p.p2p_from OR s.post_id = p2p.p2p_to ) && s.user_id = %s )
            WHERE p2p.p2p_type = 'groups_to_groups'
            ", $user->ID ), ARRAY_A
        );

        foreach ( $generational_groups_shared_with_me as $l ){
            $this->my_list[] = (int) $l["post_id"];
        }

        $query = dt_queries()->tree( 'multiplying_groups_only' );
        if ( is_wp_error( $query )){
            return $this->_circular_structure_error( $query );
        }
        if ( empty( $query ) ) {
            return $this->_no_results();
        }
        $menu_data = $this->prepare_menu_array( $query );

        foreach ( $menu_data['parents'] as $parent_id => $parent ) {
            if ( !in_array( $parent_id, $this->my_list ) && 0 !== $parent_id ) {
                unset( $menu_data['parents'][$parent_id] );
                unset( $menu_data['parents'][0][array_search( $parent_id, $menu_data['parents'][0] )] );
            }
        }
        if ( count( $menu_data['parents'] ) <= 1 ) {
            return $this->_no_results();
        }

        return $this->build_group_tree( 0, $menu_data, 0 );
    }

    public function prepare_menu_array( $query) {
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

    public function build_group_tree( $parent_id, $menu_data, $gen) {
        $html = '';

        if (isset( $menu_data['parents'][$parent_id] ))
        {
            $first_section = '';
            if ( $gen === 0 ) {
                $first_section = 'first-section';
            }

            $html = '<ul class="ul-gen-'.esc_html( $gen ).'">';
            $gen++;
            foreach ($menu_data['parents'][$parent_id] as $item_id)
            {
                $html .= '<li class="gen-node li-gen-' . esc_html( $gen ) . ' ' . esc_html( $first_section ) . '">';
                $html .= '<span class="' . esc_html( $menu_data['items'][ $item_id ]['group_status'] ) . ' ' . esc_html( $menu_data['items'][ $item_id ]['group_type'] ) . '">(' . esc_html( $gen ) . ') ';
                if ( in_array( $item_id, $this->my_list ) ) {
                    $html .= '<a onclick="open_modal_details(' . esc_html( $item_id ) . ')">' . esc_html( $menu_data['items'][ $item_id ]['name'] ) . '</a></span>';
                } else {
                    $html .= __( 'Group', 'disciple_tools' ) . '</span>';
                }

                $html .= $this->build_group_tree( $item_id, $menu_data, $gen );

                $html .= '</li>';
            }
            $html .= '</ul>';

        }
        return $html;
    }


}
new DT_Metrics_Personal_Groups_Tree();


