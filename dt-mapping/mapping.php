<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'DT_Mapping_Module' ) ) {

    /**
     * Set Global Database Variables
     */
    global $wpdb;
    $wpdb->dt_location_grid = $wpdb->prefix .'dt_location_grid';
    $wpdb->dt_location_grid = $wpdb->prefix .'dt_location_grid';

    /*******************************************************************************************************************
     * MIGRATION ENGINE
     ******************************************************************************************************************/
    require_once( 'class-migration-engine.php' );
    try {
        DT_Mapping_Module_Migration_Engine::migrate( DT_Mapping_Module_Migration_Engine::$migration_number );
    } catch ( Throwable $e ) {
        $migration_error = new WP_Error( 'migration_error', 'Migration engine for mapping module failed to migrate.', [ 'error' => $e ] );
        dt_write_log( $migration_error );
    }
    /*******************************************************************************************************************/

    if ( ! function_exists( 'spinner' ) ) {
        function spinner() {
            $dir = __DIR__;
            if ( strpos( $dir, 'wp-content/themes' ) ) {
                $nest = explode( get_stylesheet(), plugin_dir_path( __FILE__ ) );
                return get_theme_file_uri() . $nest[1] . 'spinner.svg';
            } else if ( strpos( $dir, 'wp-content/plugins' ) ) {
                return plugin_dir_url( __FILE__ ) . 'spinner.svg';
            } else {
                return plugin_dir_url( __FILE__ ) . 'spinner.svg';
            }
        }
    }



    /**
     * Class DT_Mapping_Module
     */
    class DT_Mapping_Module {
        public $permissions;
        private $namespace;
        private $public_namespace;
        public $module_path;
        public $module_url;
        public $endpoints;

        // Singleton
        private static $_instance = null;
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        public function __construct() {

            require_once( 'mapping-queries.php' );
            require_once( 'mapping-admin.php' ); // can't filter for is_admin because of REST dependencies


            /**
             * LOAD REST ENDPOINTS
             *
             * Endpoints can be modified when included into other metrics locations. Just add a filter for
             * dt_mapping_module_endpoints and modify the route
             */
            $this->namespace = "dt/v1";
            $this->public_namespace = "dt-public/v1";
            $this->endpoints = apply_filters( 'dt_mapping_module_endpoints', $this->default_endpoints() );
            add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
            /** End LOAD REST ENDPOINTS */

            /**
             * PERMISSION CHECK
             *
             * Themes or plugins implementing the module need to add a simple filter to check
             * permissions and control access to the mapping module resource. By default the module is disabled.
             * Example:
             *      add_filter( 'dt_mapping_module_has_permissions', function() {
             *          if ( current_user_can( 'view_any_contacts' ) ) {
             *              return true;
             *          }
             *          return false;
             *      });
             */
            $this->permissions = apply_filters( 'dt_mapping_module_has_permissions', false );
            if ( ! $this->permissions ) {
                return;
            }
            /** END PERMISSION CHECK */

            /**
             * SET FILE LOCATIONS
             */
            $dir = __DIR__;
            if ( strpos( $dir, 'wp-content/themes' ) ) {
                $this->module_path = plugin_dir_path( __FILE__ );
                $nest              = explode( get_stylesheet(), plugin_dir_path( __FILE__ ) );
                $this->module_url  = get_theme_file_uri() . $nest[1];
            } else if ( strpos( $dir, 'wp-content/plugins' ) ) {
                $this->module_path = plugin_dir_path( __FILE__ );
                $this->module_url  = plugin_dir_url( __FILE__ );
            } else {
                $this->module_path = plugin_dir_path( __FILE__ );
                $this->module_url  = plugin_dir_url( __FILE__ );
            }
            /** END SET FILE LOCATIONS */


            /**
             * DEFAULT MAPPING NAVIGATION
             *
             * This default navigation can be disabled and a custom navigation can replace it.
             * 1. Default. The url /mapping/, the top nav are created, and scripts are loaded for that url.
             * 2. Custom URL supplied. The new base name is used to filter when scripts are loaded.
             * 3. Disabling all. You can disable even the script loading by supplying the filter a false return.
             * This would allow you to load the scripts yourself in the plugin or theme.
             *
             * Example for adding custom url:
             *      add_filter( 'dt_mapping_module_url_base', function( $base_url ) {
             *          $base_url = 'new_base_name';
             *          return $base_url;
             *      });
             *
             * Example for disabling all:
             *      add_filter( 'dt_mapping_module_url_base', function( $base_url ) {
             *          return false;
             *      });
             *
             */
            $url_base = apply_filters( 'dt_mapping_module_url_base', 'mapping' );
            $url_base_length = (int) strlen( $url_base );
            if ( isset( $_SERVER["SERVER_NAME"] ) ) {
                $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) )
                    ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) )
                    : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
                if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                    $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
                }
            }

            $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );

            if ( 'metrics' === substr( $url_path, '0', 7 ) ) {
                add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
                add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 99 );

                if ( 'metrics/mapping' === $url_path ){
                    add_action( 'wp_enqueue_scripts', [ $this, 'drilldown_script' ], 89 );
                    add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
                }
            }
            if ( 'mapping' === $url_base ) {
                if ( 'mapping' === substr( $url_path, '0', $url_base_length ) ) {

                    add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
                    add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 99 );
                    add_action( 'wp_enqueue_scripts', [ $this, 'drilldown_script' ], 89 );
                    add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
                }
            }
            else if ( $url_base === substr( $url_path, '0', $url_base_length ) ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'drilldown_script' ], 89 );
            }
            /* End DEFAULT MAPPING DEFINITION */
        }

        /**
         * ENABLED DEFAULT NAVIGATION FUNCTIONS
         */

        public function add_url( $template_for_url ) {
            $template_for_url['metrics/mapping'] = 'template-metrics.php';
            return $template_for_url;
        }
        public function menu( $content ) {
            $content .= '
            <li><a href="">' . esc_html__( 'Mapping', 'disciple_tools' ) . '</a>
                <ul class="menu vertical nested" id="mapping-menu" aria-expanded="true">
                    <li><a href="'. esc_url( site_url( '/metrics/mapping/' ) ) .'#mapping_view" onclick="page_mapping_view()">' .  esc_html__( 'Map', 'disciple_tools' ) . '</a></li>
                    <li><a href="'. esc_url( site_url( '/metrics/mapping/' ) ) .'#mapping_list" onclick="page_mapping_list()">' .  esc_html__( 'List', 'disciple_tools' ) . '</a></li>
                </ul>
            </li>
            ';
            return $content;
        }
        public function scripts() {
            // Amcharts
            wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, false, true );
            wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, false, true );
            wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', false, false, true );
            wp_register_script( 'amcharts-maps', 'https://www.amcharts.com/lib/4/maps.js', false, false, true );


            // Datatable
            wp_register_style( 'datatable-css', '//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css' );
            wp_enqueue_style( 'datatable-css' );
            wp_register_script( 'datatable', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', false, '1.10' );

            // Mapping Script
            wp_enqueue_script( 'dt_mapping_module_script', $this->module_url . 'mapping.js', [
                'jquery',
                'jquery-ui-core',
                'amcharts-core',
                'amcharts-charts',
                'amcharts-animated',
                'amcharts-maps',
                'datatable',
                'mapping-drill-down',
                'lodash'
            ], filemtime( $this->module_path . 'mapping.js' ), true );
            wp_localize_script(
                'dt_mapping_module_script', 'mappingModule', [
                    'root' => esc_url_raw( rest_url() ),
                    'uri' => $this->module_url,
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'current_user_login' => wp_get_current_user()->user_login,
                    'current_user_id' => get_current_user_id(),
                    'mapping_module' => $this->localize_script(),
                ]
            );
        }

        public function drilldown_script() {
            // Drill Down Tool
            wp_enqueue_script( 'mapping-drill-down', get_template_directory_uri() . '/dt-mapping/drill-down.js', [ 'jquery', 'lodash' ], '1.1' );
            wp_localize_script(
                'mapping-drill-down', 'mappingModule', array(
                    'mapping_module' => self::localize_script(),
                )
            );
        }

        /**
         * Script Module
         *
         * @note    This is added to scripts, but can also be added to other localization scripts in the system via
         *          'mapping_module' => DT_Mapping_Module::instance()->localize_script,
         * @note    data, settings, and translations can be modified by the filters.
         * @return array
         */
        public function localize_script() {
            $mapping_module = [
                'data' => apply_filters( 'dt_mapping_module_data', $this->data() ),
                'settings' => apply_filters( 'dt_mapping_module_settings', $this->settings() ),
                'translations' => apply_filters( 'dt_mapping_module_translations', $this->translations() ),
            ];

            /**
             * Full permissions
             */
            if ( $this->permissions ) {
                return $mapping_module;
            }
            /**
             * Approved member of the site can get data for drill down and geocoding
             */
            else if ( user_can( get_current_user_id(), 'read' ) ) {
                unset( $mapping_module['data']['custom_column_data'] );
                unset( $mapping_module['data']['custom_column_labels'] );
                return $mapping_module;
            }
            /**
             * No permissions, no data
             */
            else {
                return [];
            }
        }
        public function data() {
            $data = [];

            // top map list
            $data['top_map_list'] = $this->default_map_short_list();
            if ( isset( $data['top_map_list']['world'] ) ) {
                $data['world'] = $this->get_world_map_data();
            } else {
                foreach ( $data['top_map_list'] as $grid_id => $name ) {
                    $data[$grid_id] = $this->map_level_by_grid_id( $grid_id );
                }
                $default_map_settings = $this->default_map_settings();
                $data[$default_map_settings['parent']] = $this->map_level_by_grid_id( $default_map_settings['parent'] );
            }

            // set custom columns
            $data['custom_column_labels'] = [];
            $data['custom_column_data'] = [];

            // initialize drill down configuration
//            $data['default_drill_down'] = $this->drill_down_array();


            return $data;
        }
        public function settings() {
            $settings = [];

            $settings['root'] = esc_url_raw( rest_url() );
            $settings['endpoints'] = $this->endpoints;
            $settings['mapping_source_url'] = dt_get_location_grid_mirror( true );
            $settings['population_division'] = $this->get_population_division();
            $settings['default_map_settings'] = $this->default_map_settings();
            $settings['spinner'] = ' <img src="'. spinner() . '" width="12px" />';
            $settings['spinner_large'] = ' <img src="'. spinner() . '" width="24px" />';
            $settings['heatmap_focus'] = 0;
            $settings['current_map'] = 'top_map_list';

            return $settings;
        }

        public function translations() {
            $translations = [];

            $translations['title'] = __( "Mapping", "disciple_tools" );
            $translations['refresh_data'] = __( "Refresh Cached Data", "disciple_tools" );
            $translations['population'] = __( "Population", "disciple_tools" );
            $translations['name'] = __( "Name", "disciple_tools" );

            return $translations;
        }

        /**
         * REST API
         */
        public function add_api_routes() {
            if ( ! empty( $this->endpoints ) ) {
                foreach ( $this->endpoints as $key => $endpoint ) {
                    register_rest_route(
                        $endpoint['namespace'], $endpoint['route'], [
                            'methods'  => $endpoint['method'],
                            'callback' => [ $this, $key ],
                        ]
                    );
                }
            }

        }

        public function default_endpoints( $endpoints = [] ) {
            $endpoints['get_default_map_data_endpoint'] = [
                'namespace' => $this->namespace,
                'route' => '/mapping_module/get_default_map_data',
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'method' => 'POST',
            ];
            $endpoints['get_map_by_grid_id_endpoint'] = [
                'namespace' => $this->namespace,
                'route' => '/mapping_module/get_map_by_grid_id',
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'method' => 'POST',
            ];
            $endpoints['modify_location_endpoint'] = [
                   'namespace' => $this->namespace,
                   'route' => '/mapping_module/modify_location',
                   'nonce' => wp_create_nonce( 'wp_rest' ),
                   'method' => 'POST',
            ];
            $endpoints['search_location_grid_by_name'] = [
               'namespace' => $this->namespace,
               'route' => '/mapping_module/search_location_grid_by_name',
               'nonce' => wp_create_nonce( 'wp_rest' ),
               'method' => 'GET',
            ];
            $endpoints['get_drilldown_endpoint'] = [
                'namespace' => $this->namespace,
                'route' => '/mapping_module/get_drilldown',
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'method' => 'POST',
            ];
            $endpoints['delete_transient_endpoint'] = [
                'namespace' => $this->namespace,
                'route' => '/mapping_module/delete_transient',
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'method' => 'POST',
            ];
            // add another endpoint here
            return $endpoints;
        }

        public function get_default_map_data_endpoint( WP_REST_Request $request ) {
            if ( ! $this->permissions ) {
                return new WP_Error( __METHOD__, 'No permission', [ 'status' => 101 ] );
            }

            return $this->localize_script();
        }

        public function get_map_by_grid_id_endpoint( WP_REST_Request $request ) {
            if ( ! $this->permissions ) {
                return new WP_Error( __METHOD__, 'No permission', [ 'status' => 101 ] );
            }

            $params = $request->get_params();
            if ( isset( $params['grid_id'] ) ) {
                return $this->map_level_by_grid_id( $params['grid_id'] );
            } else {
                return new WP_Error( __METHOD__, 'Missing parameters.', [ 'status' => 400 ] );
            }
        }

        public function modify_location_endpoint( WP_REST_Request $request ) {
            if ( !user_can( get_current_user_id(), 'manage_dt' ) ) {
                return new WP_Error( 'permissions', 'No permissions for the action.', [ 'status' => 401 ] );
            }

            $params = $request->get_params();

            return DT_Mapping_Module_Admin::instance()->process_rest_edits( $params );
        }

        public function search_location_grid_by_name( WP_REST_Request $request ){
            if ( !current_user_can( 'read_location' )){
                return new WP_Error( __FUNCTION__, "No permissions to read locations", [ 'status' => 403 ] );
            }
            $params = $request->get_params();
            $search = "";
            if ( isset( $params['s'] ) ) {
                $search = $params['s'];
            }
            $filter = "all";
            if ( isset( $params['filter'] ) ){
                $filter = $params['filter'];
            }
            //search for only the locations that are currently in use
            if ( $filter === "used" ){
                $locations = Disciple_Tools_Mapping_Queries::search_used_location_grid_by_name( [
                    "search_query" => $search,
                ] );
            } else {
                $locations = Disciple_Tools_Mapping_Queries::search_location_grid_by_name( [
                    "search_query" => $search,
                    "filter" => $filter
                ] );
            }

            $prepared = [];
            foreach ( $locations["location_grid"] as $location ){
                $prepared[] = [
                    "name" => $location["label"],
                    "ID" => $location["grid_id"]
                ];
            }

            return [
                'location_grid' => $prepared,
                'total' => $locations["total"]
            ];
        }

        public function get_drilldown_endpoint( WP_REST_Request $request ) {
            if ( ! $this->permissions ) {
                return new WP_Error( __METHOD__, 'No permission', [ 'status' => 101 ] );
            }
            $params = $request->get_params();

            if ( isset( $params['grid_id'] ) ) {
                $grid_id = sanitize_key( wp_unslash( $params['grid_id'] ) );

                return $this->drill_down_array( $grid_id );
            } else {
                return new WP_Error( __METHOD__, 'Missing parameters.', [ 'status' => 400 ] );
            }
        }
        public function delete_transient_endpoint( WP_REST_Request $request ) {
            if ( ! $this->permissions ) {
                return new WP_Error( __METHOD__, 'No permission', [ 'status' => 101 ] );
            }
            $params = $request->get_params();

            if ( isset( $params['key'] ) && $params['key'] === 'counter' ) {
                delete_transient( 'counter' );
                Disciple_Tools_Mapping_Queries::counter();
                return true;
            }
            if ( isset( $params['key'] ) && $params['key'] === 'get_location_grid_totals' ) {
                delete_transient( 'get_location_grid_totals' );
                Disciple_Tools_Mapping_Queries::get_location_grid_totals();
                return true;
            }

            return new WP_Error( __METHOD__, 'Missing parameters.', [ 'status' => 400 ] );
        }


        /**
         * MAP BUILDING
         */

        /**
         * Drill down widget.
         * This can be used for initial load of the drill down for performance. It is a replica of the javascript found in drill_down.js
         *
         * @param      $bind_function
         * @param null $grid_id
         */
        public function drill_down_widget( $bind_function, $grid_id = null) {
            $dd_array = $this->drill_down_array( $grid_id );

            if ( empty( $dd_array[0]['list'] ) ) {
                dt_write_log( new WP_Error( 'dd_list_error', 'Did not find basic list established for drill down.' ) );
            }

            ?>
            <ul class="drill_down">
            <?php

            foreach ( $dd_array as $section ) {
                if ( $section['link'] ) {
                    $hollow_class = 'hollow';
                    if ( $section['active'] ) {
                        $hollow_class = '';
                    }
                    ?>
                    <li>
                        <button id="<?php echo esc_html( $section['parent'] ) ?>" type="button"
                                onclick="DRILLDOWN.get_drill_down( '<?php echo esc_attr( $bind_function ) ?>', '<?php echo esc_attr( $section['selected'] ) ?>' )"
                                class="button <?php echo esc_attr( $hollow_class ) ?> geocode-link">
                            <?php echo esc_html( $section['selected_name'] ) ?>
                        </button>
                    </li>
                    <?php
                } else {
                    if ( ! empty( $section['list'] ) ) : ?>
                        <li>
                            <select id="<?php echo esc_html( $section['parent'] ) ?>" class="geocode-select"
                                    onchange="DRILLDOWN.get_drill_down( '<?php echo esc_attr( $bind_function ) ?>', this.value )">
                                <option value="<?php echo esc_html( $section['parent'] ) ?>"></option>
                                <?php
                                foreach ( $section['list'] as $item ) {
                                    echo '<option value="' . esc_html( $item['grid_id'] ) . '" ';
                                    if ( $item['grid_id'] == $section['selected'] ) {
                                        echo 'selected';
                                    }
                                    echo '>' . esc_html( $item['name'] ) . '</option>';
                                }
                                ?>
                            </select>
                        </li>
                    <?php endif;
                }
            }

            ?></ul><?php
        }




        private function drill_down_add_custom_locations( $know_parent, $reference, $list = [] ){
            if ( (int) $reference["parent_id"] === (int) $know_parent ){
                return array_reverse( $list );
            }
            $next = Disciple_Tools_Mapping_Queries::get_by_grid_id( $reference["parent_id"] );
            $list[] = $next;
            return $this->drill_down_add_custom_locations( $know_parent, $next, $list );
        }

        private function drill_down_for_location( $reference, $default_select_first_level ){
            $default_level = $this->default_map_settings();
            $highest_admin_level = 0;
            foreach ( $reference as $r_key => $r_value ){
                if ( strpos( $r_key, "_grid_id" ) !== false && !empty( $r_value )){
                    $key = str_replace( "admin", "", $r_key );
                    $highest_admin_level = (int) str_replace( "_grid_id", "", $key );
                }
            }

            $preset_array = [];
            if ( !$default_select_first_level ){
                $preset_array[] = [
                    'parent' => 'top_map_level',
                    'selected' => 'top_map_level',
                    'selected_name' => __( 'World', 'disciple_tools' ),
                    'link' => true,
                    'active' => false,
                ];
            }
            if ( $default_level["type"] !== 'state' ){
                $preset_array[] = [
                    'parent' => 'top_map_level',
                    'selected' => (int) $reference['admin0_grid_id'],
                    'selected_name' => $reference['admin0_name'],
                    'link' => true,
                    'active' => false,
                ];
            }
            foreach ( $reference as $r_key => $r_value ){
                if ( strpos( $r_key, "_grid_id" ) !== false && $r_key !== "admin0_grid_id" ) {
                    $admin_level    = str_replace( "_grid_id", "", str_replace( "admin", "", $r_key ) );
                    if ( !empty( $r_value ) ) {
                        $preset_array[] = [
                            'parent'        => (int) $reference[ "admin" . ( (int) $admin_level - 1 ) . "_grid_id" ] ?? 0,
                            'selected'      => (int) $reference[ $r_key ] ?? 0,
                            'selected_name' => $reference[ 'admin' . $admin_level . '_name' ],
                            'link'          => true,
                            'active'        => false,
                        ];
                    }
                }
            }

            if ( (int) $reference["level"] > 5 ){
                $other_levels = $this->drill_down_add_custom_locations( $preset_array[ sizeof( $preset_array ) - 1 ]['selected'], $reference, [ $reference ] );
                foreach ( $other_levels as $level ) {
                    $preset_array[] = [
                        'parent'        => (int) $level["parent_id"] ?? 0,
                        'selected'      => (int) $level["grid_id"] ?? 0,
                        'selected_name' => $level["name"],
                        'link'          => true,
                        'active'        => false
                    ];
                }
            }
            $preset_array[ sizeof( $preset_array ) - 1 ]['active'] = true;
            $child_list = $this->format_location_grid_types( Disciple_Tools_Mapping_Queries::get_children_by_grid_id( $reference["grid_id"] ) );
            $deeper_levels = $this->get_deeper_levels( $child_list );
            $preset_array[] = [
                'parent' => $preset_array[ sizeof( $preset_array ) - 1 ]["selected"],
                'selected'      => 0,
                'selected_name' => null,
                'link'          => false,
                'active'        => false,
                'deeper_levels' => $deeper_levels,
                'list' => $child_list

            ];
            return $preset_array;
        }

        /**
         * Drill Down Array
         * This is the core logic and array builder for the drilldown
         *
         * @param null $grid_id
         *
         * @return array|bool|mixed
         */
        public function drill_down_array( $grid_id = null ) {

            $default_level = $this->default_map_settings();
            $list = $this->default_map_short_list();

            $default_select_first_level = false;
            if ( $default_level['type'] !== 'world' && count( $list ) < 2 ) {
                $default_select_first_level = true;
            }

            if ( empty( $grid_id ) || $grid_id === 'top_map_level' || $grid_id === 'world' ) {

                if ( wp_cache_get( 'drill_down_array_default' ) ) {
                    return wp_cache_get( 'drill_down_array_default' );
                }

                $grid_id = null;

                $id_list = array_keys( $list );
                if ( empty( $id_list ) || array_search( 'World', $list ) ) {
                    $child_list = $this->format_location_grid_types( Disciple_Tools_Mapping_Queries::get_children_by_grid_id( 1 ) );
                }
                else if ( count( $id_list ) === 1 ) {
                    $child_list = $this->format_location_grid_types( Disciple_Tools_Mapping_Queries::get_children_by_grid_id( $id_list[0] ) );
                }
                else {
                    $child_list = $this->format_location_grid_types( Disciple_Tools_Mapping_Queries::get_by_grid_id_list( $id_list ) );
                }

                $deeper_levels = $this->get_deeper_levels( $child_list );

                $selected_name = __( 'World', 'disciple_tools' );
                if ( $default_level['type'] === 'country' && $default_select_first_level ){
                    $selected_name = $list[ array_keys( $list )[0] ];
                }
                if ( $default_level['type'] === 'state' ){
                    if ( $default_select_first_level ){
                        $selected_name = $list[ array_keys( $list )[0] ];
                    } else {
                        $parent = $this->format_location_grid_types( Disciple_Tools_Mapping_Queries::get_by_grid_id( $child_list[0]["parent_id"] ) );
                        $selected_name = $parent["name"] ?? $selected_name;
                    }
                }

                $preset_array = [
                    [
                        'parent' => 'top_map_level',
                        'selected' => 'top_map_level',
                        'selected_name' => $selected_name,
                        'link' => true,
                        'active' => false,
                    ],
                    [
                        'parent' => 'top_map_level',
                        'selected' => 0,
                        'selected_name' => '',
                        'list' => $child_list,
                        'link' => false,
                        'active' => true,
                        'deeper_levels' => $deeper_levels,
                    ]
                ];

                wp_cache_set( 'drill_down_array_default', $preset_array );
                return $preset_array;

            } else {
                // build from grid_id

                $reference = Disciple_Tools_Mapping_Queries::get_drilldown_by_grid_id( $grid_id );
                if ( empty( $reference ) ) {
                    dt_write_log( __METHOD__ . ": Error with grid_id (" . $grid_id . " Disciple_Tools_Mapping_Queries::get_drilldown_by_grid_id Failure" );
                    return new WP_Error( 'no_location_grid', 'Location Grid not found.' );
                }
                return $this->drill_down_for_location( $reference, $default_select_first_level );

            }
        } // END drill_down_array()


        public function get_default_map_data() {

            $results = [ // set array defaults
                 'self' => [],
                 'children' => [],
                 'deeper_levels' => [],
            ];

            // get default setting for start level
            $starting_map_level = $this->default_map_settings();
            $type = $starting_map_level['type'];

            switch ( $type ) {

                case 'world':
                    return $this->get_world_map_data();
                    break;

                case 'country':
                    if ( empty( $starting_map_level['children'] ) ) {
                        return $this->get_world_map_data();
                    }

                    if ( count( $starting_map_level['children'] ) < 2 ) {
                        $grid_id = $starting_map_level['children'][0];

                        // self
                        $self = Disciple_Tools_Mapping_Queries::get_by_grid_id( $grid_id );
                        if ( ! $self ) {
                            return $this->get_world_map_data();
                        }
                        $results['self'] = [
                            'name' => $self['name'],
                            'id' => (int) $self['grid_id'],
                            'grid_id' => (int) $self['grid_id'],
                            'population' => (int) $self['population'],
                            'population_formatted' => number_format( (int) $self['population'] ),
                            'latitude' => (float) $self['latitude'],
                            'longitude' => (float) $self['longitude'],
                        ];

                        // children
                        $children = Disciple_Tools_Mapping_Queries::get_children_by_grid_id( $grid_id );

                        if ( ! empty( $children ) ) {
                            // loop and modify types and population
                            foreach ( $children as $child ) {
                                $index = $child['grid_id'];
                                $results['children'][$index] = $child;

                                // set types
                                $results['children'][$index]['id'] = (int) $child['id'];
                                $results['children'][$index]['grid_id'] = (int) $child['grid_id'];
                                $results['children'][$index]['population'] = (int) $child['population'];
                                $results['children'][$index]['population_formatted'] = number_format( $child['population'] );
                                $results['children'][$index]['latitude'] = (float) $child['latitude'];
                                $results['children'][$index]['longitude'] = (float) $child['longitude'];
                            }
                        }

                        // deeper levels
                        $results['deeper_levels'] = $this->get_deeper_levels( $results['children'] );
                    }
                    else {

                        $self = Disciple_Tools_Mapping_Queries::get_by_grid_id_list( array_keys( $starting_map_level['children'] ) );
                        if ( empty( $self ) ) {
                            return $this->get_world_map_data();
                        }

                        foreach ( $starting_map_level['children'] as $k => $v ) {
                            $grid_id = $k;

                            // self
                            $self = Disciple_Tools_Mapping_Queries::get_by_grid_id( $grid_id );
                            if ( ! $self ) {
                                return $this->get_world_map_data();
                            }
                            $results['self'] = [
                                'name' => $self['name'],
                                'id' => (int) $self['grid_id'],
                                'grid_id' => (int) $self['grid_id'],
                                'population' => (int) $self['population'],
                                'population_formatted' => number_format( (int) $self['population'] ),
                                'latitude' => (float) $self['latitude'],
                                'longitude' => (float) $self['longitude'],
                            ];

                            // children
                            $children = Disciple_Tools_Mapping_Queries::get_children_by_grid_id( $grid_id );

                            if ( ! empty( $children ) ) {
                                // loop and modify types and population
                                foreach ( $children as $child ) {
                                    $index = $child['grid_id'];
                                    $results['children'][$index] = $child;

                                    // set types
                                    $results['children'][$index]['id'] = (int) $child['id'];
                                    $results['children'][$index]['grid_id'] = (int) $child['grid_id'];
                                    $results['children'][$index]['population'] = (int) $child['population'];
                                    $results['children'][$index]['population_formatted'] = number_format( $child['population'] );
                                    $results['children'][$index]['latitude'] = (float) $child['latitude'];
                                    $results['children'][$index]['longitude'] = (float) $child['longitude'];
                                }
                            }

                            // deeper levels
                            $results['deeper_levels'] = $this->get_deeper_levels( $results['children'] );
                        }
                    }

                    return $results;
                    break;

                case 'state':
                    if ( empty( $starting_map_level['children'] ) ) {
                        return $this->get_world_map_data();
                    }

                    return $results;
                    break;

                default:
                    return $this->get_world_map_data();
                    break;
            }
        }

        public function default_map_settings() : array {
            $level = get_option( 'dt_mapping_module_starting_map_level' );

            if ( ! $level || ! is_array( $level ) ) {
                $level = [
                    'type' => 'world',
                    'parent' => 'world',
                    'children' => [],
                ];
                update_option( 'dt_mapping_module_starting_map_level', $level, false );
            }

            return $level;
        }

        /**
         * Returns key/value pairs of default locations
         *
         * @return array
         */
        public function default_map_short_list() : array {
            $list = [];
            $default_map_settings = $this->default_map_settings();

            if ( $default_map_settings['type'] === 'world' ) {
                $list = [ 'world' => 'World' ];
            }
            else if ( $default_map_settings['type'] !== 'world' && empty( $default_map_settings['children'] ) ) {
                $list = [ 'world' => 'World' ];
            }
            else {
                $children = Disciple_Tools_Mapping_Queries::get_by_grid_id_list( $default_map_settings['children'] );
                if ( ! empty( $children ) ) {
                    foreach ( $children as $child ) {
                        $list[$child['grid_id']] = $child['name'];
                    }
                }
            }
            return $list;
        }

        public function map_level_by_grid_id( $grid_id ) {
            $results = [
                'parent' => [],
                'self' => [],
                'children' => [],
                'deeper_levels' => [],
            ];

            // else if not world, build data from grid_id
            $parent = Disciple_Tools_Mapping_Queries::get_parent_by_grid_id( $grid_id );
            if ( ! empty( $parent ) ) {
                $results['parent'] = $parent;

                // set types
                $results['parent']['id'] = (int) $parent['grid_id'];
                $results['parent']['grid_id'] = (int) $parent['grid_id'];
                $results['parent']['population'] = (int) $parent['population'];
                $results['parent']['population_formatted'] = number_format( $parent['population'] );
                $results['parent']['latitude'] = (float) $parent['latitude'];
                $results['parent']['longitude'] = (float) $parent['longitude'];
                $results['parent']['parent_id'] = empty( $parent['parent_id'] ) ? null : (int) $parent['parent_id'];
                $results['parent']['admin0_grid_id'] = empty( $parent['admin0_grid_id'] ) ? null : (int) $parent['admin0_grid_id'];
                $results['parent']['admin1_grid_id'] = empty( $parent['admin1_grid_id'] ) ? null : (int) $parent['admin1_grid_id'];
                $results['parent']['admin2_grid_id'] = empty( $parent['admin2_grid_id'] ) ? null : (int) $parent['admin2_grid_id'];
                $results['parent']['admin3_grid_id'] = empty( $parent['admin3_grid_id'] ) ? null : (int) $parent['admin3_grid_id'];
                $results['parent']['admin4_grid_id'] = empty( $parent['admin4_grid_id'] ) ? null : (int) $parent['admin4_grid_id'];
                $results['parent']['admin5_grid_id'] = empty( $parent['admin5_grid_id'] ) ? null : (int) $parent['admin5_grid_id'];
            }

            $self = Disciple_Tools_Mapping_Queries::get_by_grid_id( $grid_id );
            if ( ! empty( $self ) ) {
                $results['self'] = $self;

                // set types
                $results['self']['id'] = (int) $self['id'];
                $results['self']['grid_id'] = (int) $self['grid_id'];
                $results['self']['population'] = (int) $self['population'];
                $results['self']['population_formatted'] = number_format( $self['population'] );
                $results['self']['latitude'] = (float) $self['latitude'];
                $results['self']['longitude'] = (float) $self['longitude'];
                $results['self']['parent_id'] = empty( $self['parent_id'] ) ? null : (int) $self['parent_id'];
                $results['self']['admin0_grid_id'] = empty( $self['admin0_grid_id'] ) ? null : (int) $self['admin0_grid_id'];
                $results['self']['admin1_grid_id'] = empty( $self['admin1_grid_id'] ) ? null : (int) $self['admin1_grid_id'];
                $results['self']['admin2_grid_id'] = empty( $self['admin2_grid_id'] ) ? null : (int) $self['admin2_grid_id'];
                $results['self']['admin3_grid_id'] = empty( $self['admin3_grid_id'] ) ? null : (int) $self['admin3_grid_id'];
                $results['self']['admin4_grid_id'] = empty( $self['admin4_grid_id'] ) ? null : (int) $self['admin4_grid_id'];
                $results['self']['admin5_grid_id'] = empty( $self['admin5_grid_id'] ) ? null : (int) $self['admin5_grid_id'];
            }

            // get children
            $children = Disciple_Tools_Mapping_Queries::get_children_by_grid_id( $grid_id );
            if ( ! empty( $children ) ) {
                // loop and modify types and population
                foreach ( $children as $child ) {
                    $index = $child['grid_id'];
                    $results['children'][$index] = $child;

                    // set types
                    $results['children'][$index]['id'] = (int) $child['id'];
                    $results['children'][$index]['grid_id'] = (int) $child['grid_id'];
                    $results['children'][$index]['population'] = (int) $child['population'];
                    $results['children'][$index]['population_formatted'] = number_format( $child['population'] );
                    $results['children'][$index]['latitude'] = (float) $child['latitude'];
                    $results['children'][$index]['longitude'] = (float) $child['longitude'];
                    $results['children'][$index]['parent_id'] = empty( $child['parent_id'] ) ? null : (int) $child['parent_id'];
                    $results['children'][$index]['admin0_grid_id'] = empty( $child['admin0_grid_id'] ) ? null : (int) $child['admin0_grid_id'];
                    $results['children'][$index]['admin1_grid_id'] = empty( $child['admin1_grid_id'] ) ? null : (int) $child['admin1_grid_id'];
                    $results['children'][$index]['admin2_grid_id'] = empty( $child['admin2_grid_id'] ) ? null : (int) $child['admin2_grid_id'];
                    $results['children'][$index]['admin3_grid_id'] = empty( $child['admin3_grid_id'] ) ? null : (int) $child['admin3_grid_id'];
                    $results['children'][$index]['admin4_grid_id'] = empty( $child['admin4_grid_id'] ) ? null : (int) $child['admin4_grid_id'];
                    $results['children'][$index]['admin5_grid_id'] = empty( $child['admin5_grid_id'] ) ? null : (int) $child['admin5_grid_id'];
                }
            }

//            $available_geojson = $this->get_available_geojson();
            if ( ! empty( $results['children'] ) ) {
                foreach ( $results['children'] as $index => $child ) {
                    $results['deeper_levels'][$index] = true;
                }
            }

            return apply_filters( 'dt_mapping_module_map_level_by_grid_id', $results );
        }



        /**
         * Gets default world view
         * @return array
         */
        public function get_world_map_data() : array {
            $results = [ // set array defaults
                 'self' => [],
                 'children' => [],
                 'deeper_levels' => [],
            ];

            $results['self'] = $this->format_location_grid_types( Disciple_Tools_Mapping_Queries::get_earth() );
            $results['self']['population_formatted'] = number_format( $results['self']['population'] ?? 0 );

            $results['children'] = $this->get_countries_map_data();
            $results['deeper_levels'] = $this->get_deeper_levels( $results['children'] );

            return $results;
        }

        public function get_countries_map_data() {
            $children = Disciple_Tools_Mapping_Queries::get_countries();

            $results = [];

            if ( ! empty( $children ) ) {
                // loop and modify types and population
                foreach ( $children as $child ) {
                    $index = $child['grid_id'];
                    $results[$index] = $child;

                    // set types
                    $results[$index]['id'] = (int) $child['grid_id'];
                    $results[$index]['grid_id'] = (int) $child['grid_id'];
                    $results[$index]['population'] = (int) $child['population'];
                    $results[$index]['population_formatted'] = number_format( $child['population'] );
                    $results[$index]['latitude'] = (float) $child['latitude'];
                    $results[$index]['longitude'] = (float) $child['longitude'];
                    $results[$index]['parent_id'] = empty( $child['parent_id'] ) ? null : (int) $child['parent_id'];
                    $results[$index]['admin0_grid_id'] = empty( $child['admin0_grid_id'] ) ? null : (int) $child['admin0_grid_id'];
                    $results[$index]['admin1_grid_id'] = empty( $child['admin1_grid_id'] ) ? null : (int) $child['admin1_grid_id'];
                    $results[$index]['admin2_grid_id'] = empty( $child['admin2_grid_id'] ) ? null : (int) $child['admin2_grid_id'];
                    $results[$index]['admin3_grid_id'] = empty( $child['admin3_grid_id'] ) ? null : (int) $child['admin3_grid_id'];
                    $results[$index]['admin4_grid_id'] = empty( $child['admin4_grid_id'] ) ? null : (int) $child['admin4_grid_id'];
                    $results[$index]['admin5_grid_id'] = empty( $child['admin5_grid_id'] ) ? null : (int) $child['admin5_grid_id'];
                }
            }
            return $results;
        }

        public function get_deeper_levels( array $children ) {

            $results = [];
            if ( ! empty( $children ) ) {
                foreach ( $children as $index => $child ) {
                    $results[$child['grid_id']] = true;
                }
            }
            return $results;
        }

        public function get_grid_id_title( int $grid_id ) : string {
            $result = Disciple_Tools_Mapping_Queries::get_by_grid_id( $grid_id );
            return $result['name'] ?? '';
        }

        public function get_population_division() {
            $data = [];

            $data['base'] = (int) get_option( 'dt_mapping_module_population', true );

            /**********************************************************************************************************
             * Filter to supply custom divisions geographic unit.
             *
             * @example     [
             *                  6252001 => 5000
             *              ]
             *
             *              This would make the "United States" ( i.e. 6252001) use divisions of 5000
             */
            $data['custom'] = apply_filters( 'dt_mapping_module_custom_population_divisions', [] );

            return $data;
        }
        public function format_location_grid_types( $query ) {
            if ( ! empty( $query ) || ! is_array( $query ) ) {
                foreach ( $query as $index => $value ) {
                    if ( isset( $value['grid_id'] ) ) {
                        $query[$index]['grid_id'] = (int) $value['grid_id'];
                    }
                    if ( isset( $value['population'] ) ) {
                        $query[$index]['population'] = (int) $value['population'];
                        $query[$index]['population_formatted'] = number_format( (int) $query[$index]['population'] );
                    }
                    if ( isset( $value['latitude'] ) ) {
                        $query[$index]['latitude'] = (float) $value['latitude'];
                    }
                    if ( isset( $value['longitude'] ) ) {
                        $query[$index]['longitude'] = (float) $value['longitude'];
                    }
                    if ( isset( $value['parent_id'] ) ) {
                        $query[$index]['parent_id'] = (int) $value['parent_id'];
                    }
                    if ( isset( $value['admin0_grid_id'] ) ) {
                        $query[$index]['admin0_grid_id'] = empty( $value['admin0_grid_id'] ) ? null : (int) $value['admin0_grid_id'];
                    }
                    if ( isset( $value['admin1_grid_id'] ) ) {
                        $query[$index]['admin1_grid_id'] = empty( $value['admin1_grid_id'] ) ? null : (int) $value['admin1_grid_id'];
                    }
                    if ( isset( $value['admin2_grid_id'] ) ) {
                        $query[$index]['admin2_grid_id'] = empty( $value['admin2_grid_id'] ) ? null : (int) $value['admin2_grid_id'];
                    }
                    if ( isset( $value['admin3_grid_id'] ) ) {
                        $query[$index]['admin3_grid_id'] = empty( $value['admin3_grid_id'] ) ? null : (int) $value['admin3_grid_id'];
                    }
                    if ( isset( $value['admin4_grid_id'] ) ) {
                        $query[$index]['admin4_grid_id'] = empty( $value['admin4_grid_id'] ) ? null : (int) $value['admin4_grid_id'];
                    }
                    if ( isset( $value['admin5_grid_id'] ) ) {
                        $query[$index]['admin5_grid_id'] = empty( $value['admin5_grid_id'] ) ? null : (int) $value['admin5_grid_id'];
                    }
                }
            }
            return $query;
        }
        public function get_post_locations( $post_id ) {
            $list = [];
            $location_grid_list = get_post_meta( $post_id, 'location_grid' );
            if ( !empty( $location_grid_list ) ) {
                $list = Disciple_Tools_Mapping_Queries::get_by_grid_id_list( $location_grid_list );
            }
            return $list;
        }

        public function get_countries_grouped_by_region( $regions = null ): array {
            $regions = Disciple_Tools_Mapping_Queries::get_regions();
            $countries = Disciple_Tools_Mapping_Queries::get_countries();
            $list = [];

            foreach ( $regions as $item ) {
                $cc2 = explode( ',', $item['cc2'] );

                $list[$item['grid_id']]['name'] = $item['name'];
                $list[$item['grid_id']]['country_codes'] = $cc2;

                foreach ( $countries as $country ) {
                    if ( array_search( $country['country_code'], $cc2 ) !== false ) {
                        $list[$item['grid_id']]['countries'][] = $country;
                    }
                }
            }

            return $list;
        }
    } DT_Mapping_Module::instance(); // end DT_Mapping_Module class
} // end if class check

if ( ! function_exists( 'dt_get_location_grid_mirror' ) ) {
    /**
     * Best way to call for the mapping polygon
     * @return array|string
     */
    function dt_get_location_grid_mirror( $url_only = false ) {
        $mirror = get_option( 'dt_location_grid_mirror' );
        if ( empty( $mirror ) ) {
            $array = [
                'key'   => 'google',
                'label' => 'Google',
                'url'   => 'https://storage.googleapis.com/location-grid-mirror/',
            ];
            update_option( 'dt_location_grid_mirror', $array, true );
            $mirror = $array;
        }

        if ( $url_only ) {
            return $mirror['url'];
        }

        return $mirror;
    }
}

if ( ! function_exists( 'dt_get_mapbox_endpoint' ) ) {
    function dt_get_mapbox_endpoint( $type = 'places' ) : string {
        switch ( $type ) {
            case 'permanent':
                return 'https://api.mapbox.com/geocoding/v5/mapbox.places-permanent/';
                break;
            case 'places':
            default:
                return 'https://api.mapbox.com/geocoding/v5/mapbox.places/';
                break;
        }
    }
}
