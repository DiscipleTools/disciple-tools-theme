<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Mapping_Map_Chart {

    //slug and titile of the top menu folder
    public $base_slug = 'mapping'; // lowercase
    public $base_title = "Mapping";

    public $title = 'Map';
    public $slug = 'map'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = 'mapping-metrics.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    public $namespace = null;

    public function __construct() {
        // parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->namespace = "dt-metrics/$this->base_slug/$this->slug";
        if ( isset( $_SERVER["SERVER_NAME"] ) ) {
            $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) )
                ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) )
                : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
            if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
            }
        }

        $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );

        // only load map scripts if exact url
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'mapping_scripts' ], 89 );
            add_action( 'wp_enqueue_scripts', [ $this, 'map_scripts' ], 99 );
        }
        if ( "metrics/$this->base_slug/list" === $url_path ) {
            // add_action( 'wp_enqueue_scripts', [ $this, 'mapping_scripts' ], 89 );
            add_action( 'wp_enqueue_scripts', [ $this, 'list_scripts' ], 99 );
        }
        if ( strpos( $url_path, 'metrics' ) === 0 ) {
            add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
            add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 99 );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function add_url( $template_for_url ) {
        $template_for_url["metrics/$this->base_slug/$this->slug"] = 'template-metrics.php';
        $template_for_url["metrics/$this->base_slug/list"] = 'template-metrics.php';
        return $template_for_url;
    }
    public function menu( $content ) {
        $content .= '
        <li><a href="">' . esc_html__( 'Mapping', 'disciple_tools' ) . '</a>
            <ul class="menu vertical nested" id="mapping-menu" aria-expanded="true">
                <li><a href="'. esc_url( site_url( '/metrics/mapping/' ) ) .'map">' .  esc_html__( 'Map', 'disciple_tools' ) . '</a></li>
                <li><a href="'. esc_url( site_url( '/metrics/mapping/' ) ) .'list">' .  esc_html__( 'List', 'disciple_tools' ) . '</a></li>
            </ul>
        </li>
        ';
        return $content;
    }


    public function map_scripts() {
        // Map starter Script
        wp_enqueue_script( 'dt_'.$this->slug.'_script',
            get_template_directory_uri() . '/dt-mapping/' . $this->js_file_name,
            [
                'jquery',
                'dt_mapping_js'
            ],
            filemtime( get_theme_file_path() . '/dt-mapping/' . $this->js_file_name ),
            true
        );
        wp_localize_script(
            'dt_'.$this->slug.'_script', $this->js_object_name, [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/$this->slug",
                'base_slug' => $this->base_slug,
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'translations' => $this->translations()
            ]
        );
    }



    public function mapping_scripts() {
        DT_Mapping_Module::instance()->scripts();
    }


    public function data( $force_refresh = false ) {
        //get initial data
        $data = DT_Mapping_Module::instance()->data();

        if ( $force_refresh ){
            delete_transient( 'get_location_grid_totals' );
        }

        $data = apply_filters( 'dt_mapping_module_data', $data );

        return $data;
    }

    public function translations() {
        $translations = [];
        $translations['title'] = __( "Mapping", "disciple_tools" );
        $translations['refresh_data'] = __( "Refresh Cached Data", "disciple_tools" );
        $translations['population'] = __( "Population", "disciple_tools" );
        $translations['name'] = __( "Name", "disciple_tools" );
        return $translations;
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/data', [
                [
                    'methods'  => "GET",
                    'callback' => [ $this, 'mapping_endpoint' ],
                ],
            ]
        );
    }

    public function mapping_endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( "mapping", "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();

        return $this->data( isset( $params["refresh"] ) && $params["refresh"] === "true" );
    }


    public function has_permission(){
        $permissions = $this->permissions;
        $pass = count( $permissions ) === 0;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }

    /**
     * List view
     */

    public function list_scripts() {
        DT_Mapping_Module::instance()->drilldown_script();

        // Datatable
        wp_register_style( 'datatable-css', '//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css', [], '1.10.19' );
        wp_enqueue_style( 'datatable-css' );
        wp_register_script( 'datatable', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', [], '1.10.19' );

        // Map starter Script
        wp_enqueue_script( 'dt_'.$this->slug.'_script',
            get_template_directory_uri() . '/dt-mapping/' . $this->js_file_name,
            [
                'jquery',
                'datatable'
            ],
            filemtime( get_theme_file_path() . '/dt-mapping/' . $this->js_file_name ),
            true
        );
        wp_localize_script(
            'dt_'.$this->slug.'_script', $this->js_object_name, [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/$this->slug",
                'base_slug' => $this->base_slug,
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'translations' => $this->translations(),
                'mapping_module' => DT_Mapping_Module::instance()->localize_script(),
            ]
        );
    }
}
new DT_Metrics_Mapping_Map_Chart();



