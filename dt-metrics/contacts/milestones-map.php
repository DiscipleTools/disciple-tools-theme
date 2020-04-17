<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly.


class DT_Metrics_Milestones_Map_Chart extends DT_Metrics_Chart_Base
{

    //slug and titile of the top menu folder
    public $base_slug = 'contacts'; // lowercase
    public $base_title = "Contacts";

    public $title = 'Milestones Map';
    public $slug = 'milestones_map'; // lowercase
    public $js_object_name = 'wp_js_object'; // This object will be loaded into the metrics.js file by the wp_localize_script.
    public $js_file_name = 'milestones_map.js'; // should be full file name plus extension
    public $permissions = [ 'view_any_contacts', 'view_project_metrics' ];
    public $namespace = null;

    public function __construct() {
        parent::__construct();
        if ( !$this->has_permission() ){
            return;
        }
        $this->namespace = "dt-metrics/$this->base_slug/$this->slug";
        $url_path = dt_get_url_path();
        // only load scripts if exact url
        if ( "metrics/$this->base_slug/$this->slug" === $url_path ) {

            add_action( 'wp_enqueue_scripts', [ $this, 'mapping_scripts' ], 89 );
            add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );

        }
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }


    public function scripts() {
        DT_Mapping_Module::instance()->scripts();
        global $dt_mapping;


        // Milestones Script
        wp_enqueue_script( 'dt_'.$this->slug.'_script',
            get_template_directory_uri() . '/dt-metrics/contacts/' . $this->js_file_name,
            [
                'jquery',
                'dt_mapping_js'
            ],
            filemtime( get_theme_file_path() . '/dt-metrics/contacts/' . $this->js_file_name ),
            true
        );
        wp_localize_script(
            'dt_'.$this->slug.'_script', $this->js_object_name, [
                'rest_endpoints_base' => esc_url_raw( rest_url() ) . "dt-metrics/$this->base_slug/$this->slug",
                'base_slug' => $this->base_slug,
                'root' => esc_url_raw( rest_url() ),
                'uri' => $dt_mapping['url'],
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'current_user_login' => wp_get_current_user()->user_login,
                'current_user_id' => get_current_user_id(),
            ]
        );
    }

    public function mapping_scripts() {
        //Drilldown and map
        DT_Mapping_Module::instance()->scripts();
    }


    public function data( $force_refresh = false ) {
        //get initial data
        $data = DT_Mapping_Module::instance()->data();

        $data = $this->add_milestones_data( $data, $force_refresh );

        return $data;
    }

    public function translations() {
        $translations = [];
        return $translations;
    }

    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/data', [
                [
                    'methods'  => "GET",
                    'callback' => [ $this, 'milestones_map_endpoint' ],
                ],
            ]
        );
    }

    public function milestones_map_endpoint( WP_REST_Request $request ){
        if ( !$this->has_permission() ) {
            return new WP_Error( "milestones_map", "Missing Permissions", [ 'status' => 400 ] );
        }
        $params = $request->get_params();

        return $this->data( isset( $params["refresh"] ) && $params["refresh"] === "true" );
    }


    public function add_milestones_data( $data, $force_refresh = false ) {


        if ( isset( $_SERVER["SERVER_NAME"] ) ) {
            $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) )
                ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) )
                : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
            if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
            }
        }
        $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );

        /**
         * Step 1
         * Extract the labels and data from the data section of the filter
         *
         * @note        No modification to this section needed.
         */
        $column_labels = $data['custom_column_labels'] ?? [];
        $column_data   = $data['custom_column_data'] ?? [];

        /**
         * Step 2
         * Get the next index/column to add
         * This will add this new column of data to the end of the list.
         *
         * @note        No modification to this section needed.
         * @note        To modify the order of the columns use the filter order
         *              found in the add_filters() function.
         * @example     Current load level 10
         *              add_filter( 'dt_mapping_module_data', 'dt_mm_add_contacts_column', 10, 1 );
         *              Change to load level 50 and thus move it down the column list. Which means it
         *              will load after 0-49, and in front of 51-1000+
         *              add_filter( 'dt_mapping_module_data', 'dt_mm_add_contacts_column', 50, 1 );
         */
        if ( empty( $column_labels ) ) {
            $next_column_number = 0;
        } else if ( count( $column_labels ) === 1 ) {
            $next_column_number = 1;
        } else {
            $next_column_number = count( $column_labels );
        }

        /**
         * Step 3
         * Add new label
         *
         * @note     Modify this! Add your column name and key.
         */
        // $column_labels[ $next_column_number ] = [
        //     'key'   => 'churches',
        //     'label' => __( 'Churches', 'disciple_tools' )
        // ];
        $field_settings = Disciple_Tools_Contact_Post_Type::instance()->get_custom_fields_settings();
        $milestones_options = $field_settings["milestones"]["default"];
        foreach ( $milestones_options as $option_key => $option_value ){
            $column_labels[] = [
                'key'   => $option_key,
                'label' => $option_value["label"]
            ];
        }
        $next_column_number = count( $column_labels );



        /**
         * Step 4
         * Add new column to existing data
         *
         * @note     No modification to this section needed.
         */
        if ( ! empty( $column_data ) ) {
            foreach ( $column_data as $key => $value ) {
                $column_data[$key][$next_column_number] = 0;
            }
        }

        /**
         * Step 5
         * Add new label and data column
         * This is the section you can loop through any content type
         * and add a new column of data for it. You want to only add grid_ids
         * that have a positive count value.
         *
         * @note    Modify this section!
         * @note    Don't add 0 values, or you might create unnecessary array and
         *          transfer weight to the mapping javascript file.
         */
        $results = Disciple_Tools_Mapping_Queries::get_location_grid_totals_on_field( "contacts", "milestones", $force_refresh );
        $keys = array_column( $column_labels, 'key' );
        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                if ( $result['type'] && $result['count'] > 0 && in_array( $result["type"], $keys ) ) { // filter for only contact and positive counts
                    $grid_id = $result['grid_id'];

                    // test if grid_id exists, else prepare it with 0 values
                    if ( ! isset( $column_data[ $grid_id ] ) ) {
                        $column_data[$grid_id] = [];
                        $i                         = 0;
                        while ( $i <= $next_column_number ) {
                            $column_data[$grid_id][$i] = 0;
                            $i ++;
                        }
                    }

                    // add new record to column
                    $column_data[$grid_id][array_search( $result["type"], $keys )] = (int) $result['count'] ?? 0; // must be string
                }
            }
        }

        /**
         * Step 6
         * Put back the modified labels and column data and return everything to the filter.
         *
         * @note    No modification to this section needed.
         */
        $data['custom_column_labels'] = $column_labels;
        $data['custom_column_data']   = $column_data;

        return $data;
    }

}
new DT_Metrics_Milestones_Map_Chart();


