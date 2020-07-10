<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.

class DT_Users_Hover_Map extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'user-management'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'hover-map'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-users/hover-coverage-map.js'; // should be full file name plus extension
    public $permissions = [ 'list_users', 'manage_dt' ];
    public $namespace = null;

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }

        $this->namespace = "$this->base_slug/$this->slug";

        $url_path = dt_get_url_path();
        if ( strpos( $url_path, 'user-management' ) !== false ) {
            add_filter( 'dt_metrics_menu', [ $this, 'add_menu' ], 20 );
        }
        if ( "$this->base_slug/$this->slug" === $url_path ) {

            add_filter( 'dt_metrics_menu', [ $this, 'base_menu' ], 20 ); //load menu links
            add_action( 'wp_enqueue_scripts', [ $this, 'base_scripts' ], 99 );
            add_action( 'wp_enqueue_scripts', [ $this, 'mapping_scripts' ], 89 );
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 100 );
            add_filter( 'dt_templates_for_urls', [ $this, 'dt_templates_for_urls' ] );
        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }

    public function dt_templates_for_urls( $template_for_url ) {
        $template_for_url['user-management/hover-map'] = 'template-metrics.php';
        return $template_for_url;
    }

    public function base_menu( $content ) {
        return $content;
    }

    public function add_menu( $content ) {
        $content .= '<li><a href="'. esc_url( site_url( '/user-management/hover-map/' ) ) .'" >' .  esc_html__( 'Hover Map', 'disciple_tools' ) . '</a></li>';
        return $content;
    }

    public function base_add_url( $template_for_url ) {
        return $template_for_url;
    }

    public function scripts() {
        DT_Mapping_Module::instance()->scripts();
        global $dt_mapping;

        // Milestones Script
        wp_enqueue_script( 'dt_'.$this->slug.'_script',
            get_template_directory_uri() . $this->js_file_name,
            [
                'jquery',
                'dt_mapping_js',
                'lodash'
            ],
            filemtime( get_theme_file_path() . $this->js_file_name ),
            true
        );
        wp_localize_script(
            'dt_'.$this->slug.'_script', $this->js_object_name, [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "$this->base_slug/$this->slug",
                'base_slug' => $this->base_slug,
                'root' => esc_url_raw( rest_url() ),
                'uri' => $dt_mapping['url'],
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'translations' => [
                    'title' => __( 'Coverage Map', 'disciple_tools' )
                ]
            ]
        );
    }

    public function mapping_scripts() {
        DT_Mapping_Module::instance()->scripts();
    }

    public function data( $force_refresh = false ) {
        //get initial data
        $data = DT_Mapping_Module::instance()->data();

        $data = $this->add_all_column( $data );
        $data = $this->add_active_column( $data );
        $data = $this->add_inactive_column( $data );

        return $data;
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/data', [
                [
                    'methods'  => "GET",
                    'callback' => [ $this, 'system_map_endpoint' ],
                ],
            ]
        );
    }

    public function system_map_endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( "hover_map", "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();

        return $this->data( isset( $params["refresh"] ) && $params["refresh"] === "true" );
    }

    public function add_all_column( $data ) {
        $column_labels = $data['custom_column_labels'] ?? [];
        $column_data   = $data['custom_column_data'] ?? [];

        if ( empty( $column_labels ) ) {
            $next_column_number = 0;
        } else if ( count( $column_labels ) === 1 ) {
            $next_column_number = 1;
        } else {
            $next_column_number = count( $column_labels );
        }

        $column_labels[ $next_column_number ] = [
            'key'   => 'all',
            'label' => __( 'Locations Covered', 'disciple_tools' )
        ];

        if ( ! empty( $column_data ) ) {
            foreach ( $column_data as $key => $value ) {
                $column_data[ $key ][ $next_column_number ] = 0;
            }
        }

        $results = Disciple_Tools_Mapping_Queries::query_user_location_grid_totals();

        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( $result['count'] > 0 ) { // filter for only contact and positive counts
                    $grid_id = $result['grid_id'];

                    // test if grid_id exists, else prepare it with 0 values
                    if ( ! isset( $column_data[$grid_id] ) ) {
                        $column_data[$grid_id] = [];
                        $i                         = 0;
                        while ( $i <= $next_column_number ) {
                            $column_data[$grid_id][$i] = 0;
                            $i ++;
                        }
                    }

                    // add new record to column
                    $column_data[$grid_id][$next_column_number] = (int) $result['count'] ?? 0; // must be string
                }
            }
        }

        $data['custom_column_labels'] = $column_labels;
        $data['custom_column_data']   = $column_data;
        return $data;
    }

    public function add_active_column( $data ) {
        $column_labels = $data['custom_column_labels'] ?? [];
        $column_data   = $data['custom_column_data'] ?? [];

        if ( empty( $column_labels ) ) {
            $next_column_number = 0;
        } else if ( count( $column_labels ) === 1 ) {
            $next_column_number = 1;
        } else {
            $next_column_number = count( $column_labels );
        }

        $column_labels[ $next_column_number ] = [
            'key'   => 'active',
            'label' => __( 'Locations with Active Users', 'disciple_tools' )
        ];

        if ( ! empty( $column_data ) ) {
            foreach ( $column_data as $key => $value ) {
                $column_data[ $key ][ $next_column_number ] = 0;
            }
        }

        $results = Disciple_Tools_Mapping_Queries::query_user_location_grid_totals( 'active' );

        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( $result['count'] > 0 ) { // filter for only contact and positive counts
                    $grid_id = $result['grid_id'];

                    // test if grid_id exists, else prepare it with 0 values
                    if ( ! isset( $column_data[$grid_id] ) ) {
                        $column_data[$grid_id] = [];
                        $i                         = 0;
                        while ( $i <= $next_column_number ) {
                            $column_data[$grid_id][$i] = 0;
                            $i ++;
                        }
                    }

                    // add new record to column
                    $column_data[$grid_id][$next_column_number] = (int) $result['count'] ?? 0; // must be string
                }
            }
        }

        $data['custom_column_labels'] = $column_labels;
        $data['custom_column_data']   = $column_data;
        return $data;
    }

    public function add_inactive_column( $data ) {
        $column_labels = $data['custom_column_labels'] ?? [];
        $column_data   = $data['custom_column_data'] ?? [];

        if ( empty( $column_labels ) ) {
            $next_column_number = 0;
        } else if ( count( $column_labels ) === 1 ) {
            $next_column_number = 1;
        } else {
            $next_column_number = count( $column_labels );
        }

        $column_labels[ $next_column_number ] = [
            'key'   => 'inactive',
            'label' => __( 'Locations with Inactive Assigned', 'disciple_tools' )
        ];

        if ( ! empty( $column_data ) ) {
            foreach ( $column_data as $key => $value ) {
                $column_data[ $key ][ $next_column_number ] = 0;
            }
        }

        $results = Disciple_Tools_Mapping_Queries::query_user_location_grid_totals( 'inactive' );

        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( $result['count'] > 0 ) { // filter for only contact and positive counts
                    $grid_id = $result['grid_id'];

                    // test if grid_id exists, else prepare it with 0 values
                    if ( ! isset( $column_data[$grid_id] ) ) {
                        $column_data[$grid_id] = [];
                        $i                         = 0;
                        while ( $i <= $next_column_number ) {
                            $column_data[$grid_id][$i] = 0;
                            $i ++;
                        }
                    }

                    // add new record to column
                    $column_data[$grid_id][$next_column_number] = (int) $result['count'] ?? 0; // must be string
                }
            }
        }

        $data['custom_column_labels'] = $column_labels;
        $data['custom_column_data']   = $column_data;
        return $data;
    }

}
new DT_Users_Hover_Map();
