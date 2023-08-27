<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Locations_List extends DT_Metrics_Chart_Base
{

    //slug and title of the top menu folder
    public $base_slug = 'combined'; // lowercase
    public $base_title;
    public $title;
    public $slug = 'locations_list'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = '/dt-metrics/combined/locations-list.js'; // should be full file name plus extension
    public $permissions = array( 'dt_all_access_contacts', 'view_project_metrics' );
    public $namespace = null;

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->title = __( 'Locations List', 'disciple_tools' );
        $this->base_title = __( 'Project', 'disciple_tools' );

        $this->namespace = "dt-metrics/$this->base_slug/$this->slug";

        $url_path = dt_get_url_path( true );
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'list_scripts' ), 99 );

        }
        add_action( 'rest_api_init', array( $this, 'add_api_routes' ) );
    }

    public function list_scripts() {
        DT_Mapping_Module::instance()->drilldown_script();

        // Datatable
        wp_register_style( 'datatable-css', '//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css', array(), '1.10.19' );
        wp_enqueue_style( 'datatable-css' );
        wp_register_script( 'datatable', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', array(), '1.10.19' );

        // Map starter Script
        wp_enqueue_script( 'dt_'.$this->slug.'_script',
            get_template_directory_uri() . $this->js_file_name,
            array(
                'jquery',
                'datatable',
                'lodash',
            ),
            filemtime( get_theme_file_path() .  $this->js_file_name ),
            true
        );
        wp_localize_script(
            'dt_'.$this->slug.'_script', $this->js_object_name, array(
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/$this->slug",
                'rest_endpoint' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/$this->slug/data",
                'base_slug' => $this->base_slug,
                'load_url' => "metrics/$this->base_slug/$this->slug",
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
                'translations' => $this->translations(),
                'mapping_module' => DT_Mapping_Module::instance()->localize_script(),
            )
        );
    }

    public function data( $force_refresh = false ) {
        //get initial data
        $data = DT_Mapping_Module::instance()->data();

        $data = $this->add_contacts_column( $data );
        $data = $this->add_groups_column( $data );
        $data = $this->add_churches_column( $data );
        $data = $this->add_users_column( $data );

        return $data;
    }

    public function translations() {
        $translations = array();
        return $translations;
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/data', array(
                array(
                    'methods'  => 'GET',
                    'callback' => array( $this, 'system_map_endpoint' ),
                    'permission_callback' => '__return_true',
                ),
            )
        );
    }

    public function system_map_endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( 'location_list', 'Missing Permissions', array( 'status' => 400 ) );
        }
        $params = $request->get_params();

        return $this->data( isset( $params['refresh'] ) && $params['refresh'] === 'true' );
    }

    public function add_contacts_column( $data ) {
        $column_labels = $data['custom_column_labels'] ?? array();
        $column_data   = $data['custom_column_data'] ?? array();
        if ( empty( $column_labels ) ) {
            $next_column_number = 0;
        } else if ( count( $column_labels ) === 1 ) {
            $next_column_number = 1;
        } else {
            $next_column_number = count( $column_labels );
        }
        $column_labels[ $next_column_number ] = array(
            'key'   => 'contacts',
            'label' => __( 'Contacts', 'disciple_tools' ),
        );
        if ( ! empty( $column_data ) ) {
            foreach ( $column_data as $key => $value ) {
                $column_data[$key][$next_column_number] = 0;
            }
        }
        $results = Disciple_Tools_Mapping_Queries::query_location_grid_meta_totals( 'contacts', array( 'overall_status' => array( '-closed' ), 'type' => array( 'access' ) ) );
        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( $result['count'] > 0 ) { // filter for only contact and positive counts
                    $grid_id = $result['grid_id'];

                    // test if grid_id exists, else prepare it with 0 values
                    if ( ! isset( $column_data[ $grid_id ] ) ) {
                        $column_data[ $grid_id ] = array();
                        $i                         = 0;
                        while ( $i <= $next_column_number ) {
                            $column_data[$grid_id][$i] = 0;
                            $i++;
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

    public function add_groups_column( $data ) {
        $column_labels = $data['custom_column_labels'] ?? array();
        $column_data   = $data['custom_column_data'] ?? array();
        if ( empty( $column_labels ) ) {
            $next_column_number = 0;
        } else if ( count( $column_labels ) === 1 ) {
            $next_column_number = 1;
        } else {
            $next_column_number = count( $column_labels );
        }
        $column_labels[ $next_column_number ] = array(
            'key'   => 'groups',
            'label' => __( 'Groups', 'disciple_tools' ),
        );
        if ( ! empty( $column_data ) ) {
            foreach ( $column_data as $key => $value ) {
                $column_data[$key][$next_column_number] = 0;
            }
        }
        $results = Disciple_Tools_Mapping_Queries::query_location_grid_meta_totals( 'groups', array( 'group_type' => array( 'group' ) ) );
        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( $result['count'] > 0 ) { // filter for only contact and positive counts
                    $grid_id = $result['grid_id'];

                    // test if grid_id exists, else prepare it with 0 values
                    if ( ! isset( $column_data[ $grid_id ] ) ) {
                        $column_data[$grid_id] = array();
                        $i                         = 0;
                        while ( $i <= $next_column_number ) {
                            $column_data[$grid_id][$i] = 0;
                            $i++;
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

    public function add_churches_column( $data ) {
        $column_labels = $data['custom_column_labels'] ?? array();
        $column_data   = $data['custom_column_data'] ?? array();
        if ( empty( $column_labels ) ) {
            $next_column_number = 0;
        } else if ( count( $column_labels ) === 1 ) {
            $next_column_number = 1;
        } else {
            $next_column_number = count( $column_labels );
        }
        $column_labels[ $next_column_number ] = array(
            'key'   => 'churches',
            'label' => __( 'Churches', 'disciple_tools' ),
        );
        if ( ! empty( $column_data ) ) {
            foreach ( $column_data as $key => $value ) {
                $column_data[$key][$next_column_number] = 0;
            }
        }
        $results = Disciple_Tools_Mapping_Queries::query_location_grid_meta_totals( 'groups', array( 'group_type' => array( 'church' ) ) );
        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( $result['count'] > 0 ) { // filter for only contact and positive counts
                    $grid_id = $result['grid_id'];

                    // test if grid_id exists, else prepare it with 0 values
                    if ( ! isset( $column_data[ $grid_id ] ) ) {
                        $column_data[$grid_id] = array();
                        $i                         = 0;
                        while ( $i <= $next_column_number ) {
                            $column_data[$grid_id][$i] = 0;
                            $i++;
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

    public function add_users_column( $data ) {
        $column_labels = $data['custom_column_labels'] ?? array();
        $column_data   = $data['custom_column_data'] ?? array();

        if ( empty( $column_labels ) ) {
            $next_column_number = 0;
        } else if ( count( $column_labels ) === 1 ) {
            $next_column_number = 1;
        } else {
            $next_column_number = count( $column_labels );
        }

        $column_labels[ $next_column_number ] = array(
            'key'   => 'users',
            'label' => __( 'Users', 'disciple_tools' ),
        );

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
                        $column_data[$grid_id] = array();
                        $i                         = 0;
                        while ( $i <= $next_column_number ) {
                            $column_data[$grid_id][$i] = 0;
                            $i++;
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
new DT_Metrics_Locations_List();
