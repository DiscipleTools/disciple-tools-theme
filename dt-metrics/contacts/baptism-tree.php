<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Contacts_Baptism_Tree extends DT_Metrics_Chart_Base
{
    //slug and title of the top menu folder
    public $base_slug = 'contacts'; // lowercase
    public $slug = 'baptism-tree'; // lowercase
    public $base_title;
    public $title;
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/contacts/baptism-tree.js'; // should be full file name plus extension
    public $permissions = [ 'dt_all_access_contacts', 'view_project_metrics' ];
    public $namespace = null;

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->title = __( 'Baptism Tree', 'disciple_tools' );
        $this->base_title = __( 'Contacts', 'disciple_tools' );

        $url_path = dt_get_url_path();
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        }
        $this->namespace = "dt-metrics/$this->base_slug/$this->slug";
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
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
                'data' => $this->data(),
            ]
        );
    }

    public function data() {
        return [
            'translations' => [
                'title_baptism_tree' => __( 'Baptism Generation Tree', 'disciple_tools' ),
            ],
        ];
    }

    public function add_api_routes() {
        $version = '1';
        $namespace = 'dt/v' . $version;
        register_rest_route(
            $namespace, '/metrics/contacts/baptism_tree', [
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
        return $this->get_baptism_generations_tree();
    }

    public function get_baptism_generations_tree(){
        $query = dt_queries()->tree( 'multiplying_baptisms_only' );
        if ( is_wp_error( $query )){
            return $this->_circular_structure_error( $query );
        }
        if ( empty( $query ) ) {
            return $this->_no_results();
        }
        $menu_data = $this->prepare_menu_array( $query );
        return $this->build_menu( 0, $menu_data, -1 );
    }

    public function build_menu( $parent_id, $menu_data, $gen, $unique_check = []) {
        $html = '';

        if (isset( $menu_data['parents'][$parent_id] ))
        {
            $gen++;

            $first_section = '';
            if ( $gen === 0 ) {
                $first_section = 'first-section';
            }

            $html = '<ul class="ul-gen-'.esc_html( $gen ).'">';
            foreach ($menu_data['parents'][$parent_id] as $item_id)
            {
                $html .= '<li class="gen-node li-gen-' . esc_html( $gen ) . ' ' . esc_attr( $first_section ) . '">';
                $html .= '(' . esc_html( $gen ) . ') ';
                $html .= '<strong><a href="' . esc_url( site_url( "/contacts/" ) ) . esc_html( $item_id ) . '">' . esc_html( $menu_data['items'][ $item_id ]['name'] ) . '</a></strong><br>';

                // find child items recursively
                if ( !in_array( $item_id, $unique_check ) ){
                    $unique_check[] = $item_id;
                    $html .= $this->build_menu( $item_id, $menu_data, $gen, $unique_check );
                }

                $html .= '</li>';
            }
            $html .= '</ul>';

        }
        return $html;
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


}
new DT_Metrics_Contacts_Baptism_Tree();
