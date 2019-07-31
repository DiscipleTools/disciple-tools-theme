<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'DT_Mapping_Module' ) ) {

    /**
     * Set Global Database Variables
     */
    global $wpdb;
    $wpdb->dt_geonames = $wpdb->prefix .'dt_geonames';

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
                foreach ( $data['top_map_list'] as $geonameid => $name ) {
                    $data[$geonameid] = $this->map_level_by_geoname( $geonameid );
                }
                $default_map_settings = $this->default_map_settings();
                $data[$default_map_settings['parent']] = $this->map_level_by_geoname( $default_map_settings['parent'] );
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
            $settings['mapping_source_url'] = dt_get_saturation_mapping_mirror( true );
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
            $endpoints['get_map_by_geonameid_endpoint'] = [
                'namespace' => $this->namespace,
                'route' => '/mapping_module/get_map_by_geonameid',
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'method' => 'POST',
            ];
            $endpoints['modify_location_endpoint'] = [
                   'namespace' => $this->namespace,
                   'route' => '/mapping_module/modify_location',
                   'nonce' => wp_create_nonce( 'wp_rest' ),
                   'method' => 'POST',
            ];
            $endpoints['search_geonames_by_name'] = [
               'namespace' => $this->namespace,
               'route' => '/mapping_module/search_geonames_by_name',
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

        public function get_map_by_geonameid_endpoint( WP_REST_Request $request ) {
            if ( ! $this->permissions ) {
                return new WP_Error( __METHOD__, 'No permission', [ 'status' => 101 ] );
            }

            $params = $request->get_params();
            if ( isset( $params['geonameid'] ) ) {
                return $this->map_level_by_geoname( $params['geonameid'] );
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

        public function search_geonames_by_name( WP_REST_Request $request ){
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
                $locations = Disciple_Tools_Mapping_Queries::search_used_geonames_by_name( [
                    "search_query" => $search,
                ] );
            } else {
                $locations = Disciple_Tools_Mapping_Queries::search_geonames_by_name( [
                    "search_query" => $search,
                    "filter" => $filter
                ] );
            }

            $prepared = [];
            foreach ( $locations["geonames"] as $location ){
                $prepared[] = [
                    "name" => $location["label"],
                    "ID" => $location["geonameid"]
                ];
            }

            return [
                'geonames' => $prepared,
                'total' => $locations["total"]
            ];
        }

        public function get_drilldown_endpoint( WP_REST_Request $request ) {
            if ( ! $this->permissions ) {
                return new WP_Error( __METHOD__, 'No permission', [ 'status' => 101 ] );
            }
            $params = $request->get_params();

            if ( isset( $params['geonameid'] ) ) {
                $geonameid = sanitize_key( wp_unslash( $params['geonameid'] ) );

                return $this->drill_down_array( $geonameid );
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
            if ( isset( $params['key'] ) && $params['key'] === 'get_geoname_totals' ) {
                delete_transient( 'get_geoname_totals' );
                Disciple_Tools_Mapping_Queries::get_geoname_totals();
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
         * @param null $geonameid
         */
        public function drill_down_widget( $bind_function, $geonameid = null) {
            $dd_array = $this->drill_down_array( $geonameid );

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
                                    echo '<option value="' . esc_html( $item['geonameid'] ) . '" ';
                                    if ( $item['geonameid'] == $section['selected'] ) {
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

        /**
         * Drill Down Array
         * This is the core logic and array builder for the drilldown
         *
         * @param null $geonameid
         *
         * @return array|bool|mixed
         */
        public function drill_down_array( $geonameid = null ) {

            $default_level = $this->default_map_settings();
            $list = $this->default_map_short_list();

            $default_select_first_level = false;
            if ( count( $list ) < 2 ) {
                $default_select_first_level = true;
            }

            if ( empty( $geonameid ) || $geonameid === 'top_map_level' ) {

                if ( wp_cache_get( 'drill_down_array_default' ) ) {
                    return wp_cache_get( 'drill_down_array_default' );
                }

                $geonameid = null;

                switch ( $default_level['type'] ) {

                    case 'country':

                        if ( $default_select_first_level ) {
                            // if there is only one top level selected

                            foreach ( $list as $index => $item ) {
                                $selected = $index;
                                $selected_name = $item;
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $index ) );
                            }

                            $deeper_levels = $this->get_deeper_levels( $child_list );
                            $preset_array = [
                                0 => [
                                    'parent' => 'top_map_level',
                                    'selected' => $selected,
                                    'selected_name' => $selected_name,
                                    'link' => true,
                                    'active' => true,
                                ],
                                1 => [
                                    'parent' => $selected,
                                    'selected' => 0,
                                    'list' => $child_list,
                                    'link' => false,
                                    'active' => false,
                                    'deeper_levels' => $deeper_levels,
                                ],
                            ];

                        } else {
                            // if there are multiple top levels selected
                            $items = [];
                            foreach ( $list as $index => $item ) {
                                $items[] = $index;
                            }

                            $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_by_geonameid_list( $items ) );
                            $deeper_levels = $this->get_deeper_levels( $child_list );

                            $preset_array = [
                                0 => [
                                    'parent' => 'top_map_level',
                                    'selected' => 'top_map_level',
                                    'selected_name' => __( 'World', 'disciple_tools' ),
                                    'link' => true,
                                    'active' => false,
                                ],
                                1 => [
                                    'parent' => 'top_map_level',
                                    'selected' => 0,
                                    'selected_name' => '',
                                    'list' => $child_list,
                                    'link' => false,
                                    'active' => true,
                                    'deeper_levels' => $deeper_levels,
                                ],
                            ];

                        }


                        break;

                    case 'state':
                        if ( $default_select_first_level ) {
                            // if there is only one top level selected

                            foreach ( $list as $index => $item ) {
                                $selected = $index;
                                $selected_name = $item;
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $index ) );
                            }

                            $deeper_levels = $this->get_deeper_levels( $child_list );
                            $preset_array = [
                                0 => [
                                    'parent' => 'top_map_level',
                                    'selected' => $selected,
                                    'selected_name' => $selected_name,
                                    'link' => true,
                                    'active' => true,
                                ],
                                1 => [
                                    'parent' => $selected,
                                    'selected' => 0,
                                    'list' => $child_list,
                                    'link' => false,
                                    'active' => false,
                                    'deeper_levels' => $deeper_levels,
                                ],
                            ];

                        } else {
                            // if there are multiple top levels selected
                            $items = [];
                            foreach ( $list as $index => $item ) {
                                $items[] = $index;
                            }

                            $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_by_geonameid_list( $items ) );
                            $parent = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_by_geonameid( $child_list[0]['country_geonameid'] ) );
                            $deeper_levels = $this->get_deeper_levels( $child_list );

                            $preset_array = [
                                0 => [
                                    'parent' => 'top_map_level',
                                    'selected' => 'top_map_level',
                                    'selected_name' => $parent['name'],
                                    'link' => true,
                                    'active' => true,
                                ],
                                1 => [
                                    'parent' => 'top_map_level',
                                    'selected' => 0,
                                    'selected_name' => '',
                                    'list' => $child_list,
                                    'link' => false,
                                    'active' => true,
                                    'deeper_levels' => $deeper_levels,
                                ],
                            ];

                        }


                        break;

                    case 'world':
                    default:
                        $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_countries() );
                        $deeper_levels = $this->get_deeper_levels( $child_list );
                        $preset_array = [
                            0 => [
                                'parent' => 'top_map_level',
                                'selected' => 'top_map_level',
                                'selected_name' => __( 'World', 'disciple_tools' ),
                                'link' => true,
                                'active' => true,
                            ],
                            1 => [
                                'parent' => 'top_map_level',
                                'selected' => 0,
                                'list' => $child_list,
                                'link' => false,
                                'active' => false,
                                'deeper_levels' => $deeper_levels,
                            ],
                        ];


                        break;
                }

                wp_cache_set( 'drill_down_array_default', $preset_array );
                return $preset_array;

            } else {
                // build from geonameid

                $reference = Disciple_Tools_Mapping_Queries::get_drilldown_by_geonameid( $geonameid );
                if ( empty( $reference ) ) {
                    return new WP_Error( 'no_geoname', 'Geoname not found.' );
                }

                switch ( $default_level['type'] ) {

                    case 'country':

                        // build array according to level
                        switch ( $reference['level'] ) {

                            case 'admin3':
                            case 'admin3c': // custom
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $reference['admin3_geonameid'] ) );
                                $deeper_levels = $this->get_deeper_levels( $child_list );

                                if ( $default_select_first_level ) {
                                    $preset_array = [
                                    0 => [
                                        'parent' => 'top_map_level',
                                        'selected' => (int) $reference['country_geonameid'],
                                        'selected_name' => $reference['country_name'],
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    1 => [
                                        'parent' => (int) $reference['country_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin1_geonameid'],
                                        'selected_name' => $reference['admin1_name'],
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    2 => [
                                        'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin2_geonameid'],
                                        'selected_name' => $reference['admin2_name'],
                                        'link' => true,
                                        'active' => true,
                                    ],
                                    3 => [
                                        'parent' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin3_geonameid'] ?? 0,
                                        'selected_name' => $reference['admin3_name'],
                                        'list' => $child_list,
                                        'link' => false,
                                        'active' => false,
                                        'deeper_levels' => $deeper_levels,
                                    ],

                                    ];
                                } else {
                                    $preset_array = [
                                    0 => [
                                        'parent' => 'top_map_level',
                                        'selected' => 'top_map_level',
                                        'selected_name' => __( 'World', 'disciple_tools' ),
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    1 => [
                                        'parent' => 'top_map_level',
                                        'selected' => (int) $reference['country_geonameid'],
                                        'selected_name' => $reference['country_name'],
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    2 => [
                                        'parent' => (int) $reference['country_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin1_geonameid'],
                                        'selected_name' => $reference['admin1_name'],
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    3 => [
                                        'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin2_geonameid'],
                                        'selected_name' => $reference['admin2_name'],
                                        'link' => true,
                                        'active' => true,
                                    ],
                                    4 => [
                                        'parent' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin3_geonameid'] ?? 0,
                                        'selected_name' => $reference['admin3_name'],
                                        'list' => $child_list,
                                        'link' => false,
                                        'active' => false,
                                        'deeper_levels' => $deeper_levels,
                                    ],

                                    ];
                                }
                            break;

                            case 'admin2':
                            case 'admin2c': // custom
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $reference['admin2_geonameid'] ) );
                                $deeper_levels = $this->get_deeper_levels( $child_list );

                                if ( $default_select_first_level ) {
                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => (int) $reference['country_geonameid'],
                                            'selected_name' => $reference['country_name'],
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        1 => [
                                            'parent' => (int) $reference['country_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin1_geonameid'],
                                            'selected_name' => $reference['admin1_name'],
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        2 => [
                                            'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin2_geonameid'],
                                            'selected_name' => $reference['admin2_name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        3 => [
                                            'parent' => (int) $reference['admin2_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin3_geonameid'] ?? 0,
                                            'selected_name' => $reference['admin3_name'],
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => false,
                                            'deeper_levels' => $deeper_levels,
                                        ],

                                    ];
                                } else {
                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => 'top_map_level',
                                            'selected_name' => __( 'World', 'disciple_tools' ),
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        1 => [
                                            'parent' => 'top_map_level',
                                            'selected' => (int) $reference['country_geonameid'],
                                            'selected_name' => $reference['country_name'],
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        2 => [
                                            'parent' => (int) $reference['country_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin1_geonameid'],
                                            'selected_name' => $reference['admin1_name'],
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        3 => [
                                            'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin2_geonameid'],
                                            'selected_name' => $reference['admin2_name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        4 => [
                                            'parent' => (int) $reference['admin2_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin3_geonameid'] ?? 0,
                                            'selected_name' => $reference['admin3_name'],
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => false,
                                            'deeper_levels' => $deeper_levels,
                                        ],

                                    ];
                                }
                                break;

                            case 'admin1':
                            case 'admin1c':
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $reference['admin1_geonameid'] ) );
                                $deeper_levels = $this->get_deeper_levels( $child_list );

                                if ( $default_select_first_level ) {
                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => (int) $reference['country_geonameid'],
                                            'selected_name' => $reference['country_name'],
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        1 => [
                                            'parent' => (int) $reference['country_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin1_geonameid'],
                                            'selected_name' => $reference['admin1_name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        2 => [
                                            'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin2_geonameid'] ?? 0,
                                            'selected_name' => $reference['admin2_name'],
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => false,
                                            'deeper_levels' => $deeper_levels,
                                        ],

                                    ];
                                } else {
                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => 'top_map_level',
                                            'selected_name' => __( 'World', 'disciple_tools' ),
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        1 => [
                                            'parent' => 'top_map_level',
                                            'selected' => (int) $reference['country_geonameid'],
                                            'selected_name' => $reference['country_name'],
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        2 => [
                                            'parent' => (int) $reference['country_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin1_geonameid'],
                                            'selected_name' => $reference['admin1_name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        3 => [
                                            'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin2_geonameid'] ?? 0,
                                            'selected_name' => $reference['admin2_name'],
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => false,
                                            'deeper_levels' => $deeper_levels,
                                        ],

                                    ];
                                }
                                break;

                            case 'country':
                            default:
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $reference['country_geonameid'] ) );
                                $deeper_levels = $this->get_deeper_levels( $child_list );

                                if ( $default_select_first_level ) {
                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => (int) $reference['country_geonameid'],
                                            'selected_name' => $reference['country_name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        1 => [
                                            'parent' => (int) $reference['country_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin1_geonameid'] ?? 0,
                                            'selected_name' => $reference['admin1_name'],
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => false,
                                            'deeper_levels' => $deeper_levels,
                                        ],
                                    ];
                                } else {
                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => 'top_map_level',
                                            'selected_name' => __( 'World', 'disciple_tools' ),
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        1 => [
                                            'parent' => 'top_map_level',
                                            'selected' => (int) $reference['country_geonameid'],
                                            'selected_name' => $reference['country_name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        2 => [
                                            'parent' => (int) $reference['country_geonameid'] ?? 0,
                                            'selected' => (int) $reference['admin1_geonameid'] ?? 0,
                                            'selected_name' => $reference['admin1_name'],
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => false,
                                            'deeper_levels' => $deeper_levels,
                                        ],
                                    ];
                                }

                                break;
                        }


                        return $preset_array;
                        break;

                    case 'state':

                        // build array according to level
                        switch ( $reference['level'] ) {

                            case 'admin3':
                            case 'admin3c':
                            case 'admin2':
                            case 'admin2c':
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $reference['geonameid'] ) );
                                $deeper_levels = $this->get_deeper_levels( $child_list );

                                $country = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_by_geonameid( $reference['country_geonameid'] ) );
                                $state = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_by_geonameid( $reference['admin1_geonameid'] ) );
                                $county = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_by_geonameid( $reference['admin2_geonameid'] ) );

                                if ( $default_select_first_level ) {

                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => 'top_map_level',
                                            'selected_name' => $reference['admin1_name'],
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        1 => [
                                            'parent' => 'top_map_level',
                                            'selected' => (int) $reference['admin2_geonameid'],
                                            'selected_name' => $reference['admin2_name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        2 => [
                                            'parent' => $reference['admin2_geonameid'],
                                            'selected' => 0,
                                            'selected_name' => '',
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => true,
                                            'deeper_levels' => $deeper_levels,
                                        ],
                                    ];

                                } else {

                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => 'top_map_level',
                                            'selected_name' => $country['name'],
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        1 => [
                                            'parent' => 'top_map_level',
                                            'selected' => $state['geonameid'],
                                            'selected_name' => $state['name'],
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        3 => [
                                            'parent' => $state['geonameid'],
                                            'selected' => $county['geonameid'],
                                            'selected_name' => $county['name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        4 => [
                                            'parent' => $county['geonameid'],
                                            'selected' => 0,
                                            'selected_name' => '',
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => true,
                                            'deeper_levels' => $deeper_levels,
                                        ],
                                    ];
                                }

                                break;

                            case 'admin1':
                            case 'admin1c':
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $reference['geonameid'] ) );
                                $deeper_levels = $this->get_deeper_levels( $child_list );

                                $country = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_by_geonameid( $reference['country_geonameid'] ) );
                                $state = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_by_geonameid( $reference['admin1_geonameid'] ) );

                                if ( $default_select_first_level ) {

                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => (int) $reference['admin1_geonameid'],
                                            'selected_name' => $reference['admin1_name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        1 => [
                                            'parent' => 'top_map_level',
                                            'selected' => $list['parent'],
                                            'selected_name' => '',
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => true,
                                            'deeper_levels' => $deeper_levels,
                                        ],
                                        2 => [
                                            'parent' => 'top_map_level',
                                            'selected' => $list['parent'],
                                            'selected_name' => '',
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => true,
                                            'deeper_levels' => $deeper_levels,
                                        ],
                                    ];

                                } else {

                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => 'top_map_level',
                                            'selected_name' => $country['name'],
                                            'link' => true,
                                            'active' => false,
                                        ],
                                        1 => [
                                            'parent' => 'top_map_level',
                                            'selected' => $state['geonameid'],
                                            'selected_name' => $state['name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        3 => [
                                            'parent' => $state['geonameid'],
                                            'selected' => 0,
                                            'selected_name' => '',
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => false,
                                            'deeper_levels' => $deeper_levels,
                                        ],
                                    ];
                                }
                                break;

                            case 'country':
                            default:
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $reference['geonameid'] ) );
                                $deeper_levels = $this->get_deeper_levels( $child_list );

                                if ( $default_select_first_level ) {

                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => (int) $reference['admin1_geonameid'],
                                            'selected_name' => $reference['admin1_name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        1 => [
                                            'parent' => 'top_map_level',
                                            'selected' => $list['parent'],
                                            'selected_name' => '',
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => true,
                                            'deeper_levels' => $deeper_levels,
                                        ],
                                    ];

                                } else {
                                    $parent = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_by_geonameid( $reference['country_geonameid'] ) );
                                    $preset_array = [
                                        0 => [
                                            'parent' => 'top_map_level',
                                            'selected' => 'top_map_level',
                                            'selected_name' => $parent['name'],
                                            'link' => true,
                                            'active' => true,
                                        ],
                                        1 => [
                                            'parent' => 'top_map_level',
                                            'selected' => 0,
                                            'selected_name' => '',
                                            'list' => $child_list,
                                            'link' => false,
                                            'active' => true,
                                            'deeper_levels' => $deeper_levels,
                                        ],
                                    ];
                                }

                                break;

                        }


                        return $preset_array;

                        break;

                    case 'world':
                    default:
                        // build array according to level
                        switch ( $reference['level'] ) {

                            case 'admin3':
                            case 'admin3c':
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $reference['admin3_geonameid'] ) );
                                $deeper_levels = $this->get_deeper_levels( $child_list );
                                $preset_array = [
                                    0 => [
                                        'parent' => 'top_map_level',
                                        'selected' => 'top_map_level',
                                        'selected_name' => __( 'World', 'disciple_tools' ),
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    1 => [
                                        'parent' => 'top_map_level',
                                        'selected' => (int) $reference['country_geonameid'],
                                        'selected_name' => $reference['country_name'],
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    2 => [
                                        'parent' => (int) $reference['country_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin1_geonameid'],
                                        'selected_name' => $reference['admin1_name'],
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    3 => [
                                        'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin2_geonameid'],
                                        'selected_name' => $reference['admin2_name'],
                                        'link' => true,
                                        'active' => true,
                                    ],
                                    4 => [
                                        'parent' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin3_geonameid'],
                                        'selected_name' => $reference['admin3_name'],
                                        'link' => true,
                                        'active' => true,
                                    ],
                                    5 => [
                                        'parent' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin3_geonameid'] ?? 0,
                                        'selected_name' => $reference['admin3_name'],
                                        'list' => $child_list,
                                        'link' => false,
                                        'active' => false,
                                        'deeper_levels' => $deeper_levels,
                                    ],

                                ];
                                break;

                            case 'admin2':
                            case 'admin2c':
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $reference['admin2_geonameid'] ) );
                                $deeper_levels = $this->get_deeper_levels( $child_list );
                                $preset_array = [
                                    0 => [
                                        'parent' => 'top_map_level',
                                        'selected' => 'top_map_level',
                                        'selected_name' => __( 'World', 'disciple_tools' ),
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    1 => [
                                        'parent' => 'top_map_level',
                                        'selected' => (int) $reference['country_geonameid'],
                                        'selected_name' => $reference['country_name'],
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    2 => [
                                        'parent' => (int) $reference['country_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin1_geonameid'],
                                        'selected_name' => $reference['admin1_name'],
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    3 => [
                                        'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin2_geonameid'],
                                        'selected_name' => $reference['admin2_name'],
                                        'link' => true,
                                        'active' => true,
                                    ],
                                    4 => [
                                        'parent' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin3_geonameid'] ?? 0,
                                        'selected_name' => $reference['admin3_name'],
                                        'list' => $child_list,
                                        'link' => false,
                                        'active' => false,
                                        'deeper_levels' => $deeper_levels,
                                    ],

                                ];
                                break;

                            case 'admin1':
                            case 'admin1c':
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $reference['admin1_geonameid'] ) );
                                $deeper_levels = $this->get_deeper_levels( $child_list );
                                $preset_array = [
                                    0 => [
                                        'parent' => 'top_map_level',
                                        'selected' => 'top_map_level',
                                        'selected_name' => __( 'World', 'disciple_tools' ),
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    1 => [
                                        'parent' => 'top_map_level',
                                        'selected' => (int) $reference['country_geonameid'],
                                        'selected_name' => $reference['country_name'],
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    2 => [
                                        'parent' => (int) $reference['country_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin1_geonameid'],
                                        'selected_name' => $reference['admin1_name'],
                                        'link' => true,
                                        'active' => true,
                                    ],
                                    3 => [
                                        'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'selected_name' => $reference['admin2_name'],
                                        'list' => $child_list,
                                        'link' => false,
                                        'active' => false,
                                        'deeper_levels' => $deeper_levels,
                                    ],

                                ];
                                break;

                            case 'country':
                            default:
                                $child_list = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $reference['country_geonameid'] ) );
                                $deeper_levels = $this->get_deeper_levels( $child_list );
                                $preset_array = [
                                    0 => [
                                        'parent' => 'top_map_level',
                                        'selected' => 'top_map_level',
                                        'selected_name' => __( 'World', 'disciple_tools' ),
                                        'link' => true,
                                        'active' => false,
                                    ],
                                    1 => [
                                        'parent' => 'top_map_level',
                                        'selected' => (int) $reference['country_geonameid'],
                                        'selected_name' => $reference['country_name'],
                                        'link' => true,
                                        'active' => true,
                                    ],
                                    2 => [
                                        'parent' => (int) $reference['country_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'selected_name' => $reference['admin1_name'],
                                        'list' => $child_list,
                                        'link' => false,
                                        'active' => false,
                                        'deeper_levels' => $deeper_levels,
                                    ],
                                ];
                                break;
                        }

                        return $preset_array;
                        break;
                }
            }
        }

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
                        $geonameid = $starting_map_level['children'][0];

                        // self
                        $self = Disciple_Tools_Mapping_Queries::get_by_geonameid( $geonameid );
                        if ( ! $self ) {
                            return $this->get_world_map_data();
                        }
                        $results['self'] = [
                            'name' => $self['name'],
                            'id' => (int) $self['geonameid'],
                            'geonameid' => (int) $self['geonameid'],
                            'population' => (int) $self['population'],
                            'population_formatted' => number_format( (int) $self['population'] ),
                            'latitude' => (float) $self['latitude'],
                            'longitude' => (float) $self['longitude'],
                        ];

                        // children
                        $children = Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $geonameid );

                        if ( ! empty( $children ) ) {
                            // loop and modify types and population
                            foreach ( $children as $child ) {
                                $index = $child['geonameid'];
                                $results['children'][$index] = $child;

                                // set types
                                $results['children'][$index]['id'] = (int) $child['id'];
                                $results['children'][$index]['geonameid'] = (int) $child['geonameid'];
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

                        $self = Disciple_Tools_Mapping_Queries::get_by_geonameid_list( array_keys( $starting_map_level['children'] ) );
                        if ( empty( $self ) ) {
                            return $this->get_world_map_data();
                        }

                        foreach ( $starting_map_level['children'] as $k => $v ) {
                            $geonameid = $k;

                            // self
                            $self = Disciple_Tools_Mapping_Queries::get_by_geonameid( $geonameid );
                            if ( ! $self ) {
                                return $this->get_world_map_data();
                            }
                            $results['self'] = [
                                'name' => $self['name'],
                                'id' => (int) $self['geonameid'],
                                'geonameid' => (int) $self['geonameid'],
                                'population' => (int) $self['population'],
                                'population_formatted' => number_format( (int) $self['population'] ),
                                'latitude' => (float) $self['latitude'],
                                'longitude' => (float) $self['longitude'],
                            ];

                            // children
                            $children = Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $geonameid );

                            if ( ! empty( $children ) ) {
                                // loop and modify types and population
                                foreach ( $children as $child ) {
                                    $index = $child['geonameid'];
                                    $results['children'][$index] = $child;

                                    // set types
                                    $results['children'][$index]['id'] = (int) $child['id'];
                                    $results['children'][$index]['geonameid'] = (int) $child['geonameid'];
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
                $children = Disciple_Tools_Mapping_Queries::get_by_geonameid_list( $default_map_settings['children'] );
                if ( ! empty( $children ) ) {
                    foreach ( $children as $child ) {
                        $list[$child['geonameid']] = $child['name'];
                    }
                }
            }
            return $list;
        }

        public function map_level_by_geoname( $geonameid ) {
            $results = [
                'parent' => [],
                'self' => [],
                'children' => [],
                'deeper_levels' => [],
            ];

            // else if not world, build data from geonameid
            $parent = Disciple_Tools_Mapping_Queries::get_parent_by_geonameid( $geonameid );
            if ( ! empty( $parent ) ) {
                $results['parent'] = $parent;

                // set types
                $results['parent']['id'] = (int) $parent['geonameid'];
                $results['parent']['geonameid'] = (int) $parent['geonameid'];
                $results['parent']['population'] = (int) $parent['population'];
                $results['parent']['population_formatted'] = number_format( $parent['population'] );
                $results['parent']['latitude'] = (float) $parent['latitude'];
                $results['parent']['longitude'] = (float) $parent['longitude'];
                $results['parent']['parent_id'] = (int) $parent['parent_id'];
                $results['parent']['country_geonameid'] = (int) $parent['country_geonameid'];
                $results['parent']['admin1_geonameid'] = (int) $parent['admin1_geonameid'];
                $results['parent']['admin2_geonameid'] = (int) $parent['admin2_geonameid'];
                $results['parent']['admin3_geonameid'] = (int) $parent['admin3_geonameid'];
            }

            $self = Disciple_Tools_Mapping_Queries::get_by_geonameid( $geonameid );
            if ( ! empty( $self ) ) {
                $results['self'] = $self;

                // set types
                $results['self']['id'] = (int) $self['id'];
                $results['self']['geonameid'] = (int) $self['geonameid'];
                $results['self']['population'] = (int) $self['population'];
                $results['self']['population_formatted'] = number_format( $self['population'] );
                $results['self']['latitude'] = (float) $self['latitude'];
                $results['self']['longitude'] = (float) $self['longitude'];
                $results['self']['parent_id'] = (int) $self['parent_id'];
                $results['self']['country_geonameid'] = (int) $self['country_geonameid'];
                $results['self']['admin1_geonameid'] = (int) $self['admin1_geonameid'];
                $results['self']['admin2_geonameid'] = (int) $self['admin2_geonameid'];
                $results['self']['admin3_geonameid'] = (int) $self['admin3_geonameid'];
            }

            // get children
            $children = Disciple_Tools_Mapping_Queries::get_children_by_geonameid( $geonameid );
            if ( ! empty( $children ) ) {
                // loop and modify types and population
                foreach ( $children as $child ) {
                    $index = $child['geonameid'];
                    $results['children'][$index] = $child;

                    // set types
                    $results['children'][$index]['id'] = (int) $child['id'];
                    $results['children'][$index]['geonameid'] = (int) $child['geonameid'];
                    $results['children'][$index]['population'] = (int) $child['population'];
                    $results['children'][$index]['population_formatted'] = number_format( $child['population'] );
                    $results['children'][$index]['latitude'] = (float) $child['latitude'];
                    $results['children'][$index]['longitude'] = (float) $child['longitude'];
                    $results['children'][$index]['parent_id'] = (int) $child['parent_id'];
                    $results['children'][$index]['country_geonameid'] = (int) $child['country_geonameid'];
                    $results['children'][$index]['admin1_geonameid'] = (int) $child['admin1_geonameid'];
                    $results['children'][$index]['admin2_geonameid'] = (int) $child['admin2_geonameid'];
                    $results['children'][$index]['admin3_geonameid'] = (int) $child['admin3_geonameid'];
                }
            }

            $available_geojson = $this->get_available_geojson();
            if ( ! empty( $results['children'] ) || ! empty( $available_geojson ) ) {
                foreach ( $results['children'] as $index => $child ) {
                    if ( isset( $available_geojson[$index] ) ) {
                        $results['deeper_levels'][$index] = true;
                    }
                }
            }

            return apply_filters( 'dt_mapping_module_map_level_by_geoname', $results );
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

            $results['self'] = $this->format_geoname_types( Disciple_Tools_Mapping_Queries::get_earth() );
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
                    $index = $child['country_code'];
                    $results[$index] = $child;

                    // set types
                    $results[$index]['id'] = (int) $child['geonameid'];
                    $results[$index]['geonameid'] = (int) $child['geonameid'];
                    $results[$index]['population'] = (int) $child['population'];
                    $results[$index]['population_formatted'] = number_format( $child['population'] );
                    $results[$index]['latitude'] = (float) $child['latitude'];
                    $results[$index]['longitude'] = (float) $child['longitude'];
                    $results[$index]['parent_id'] = (int) $child['parent_id'];
                    $results[$index]['country_geonameid'] = (int) $child['country_geonameid'];
                    $results[$index]['admin1_geonameid'] = (int) $child['admin1_geonameid'];
                    $results[$index]['admin2_geonameid'] = (int) $child['admin2_geonameid'];
                    $results[$index]['admin3_geonameid'] = (int) $child['admin3_geonameid'];
                }
            }
            return $results;
        }

        public function get_deeper_levels( array $children ) {

            $results = [];
            if ( ! empty( $children ) ) {
                foreach ( $children as $index => $child ) {
                    $results[$child['geonameid']] = true;
                }
            }
            return $results;
        }

        public function get_geonameid_title( int $geonameid ) : string {
            $result = Disciple_Tools_Mapping_Queries::get_by_geonameid( $geonameid );
            return $result['name'] ?? '';
        }

        public function get_available_geojson() { // @todo needs upgrade. Now polygon, polygon_collection are both folders to check

            if ( get_transient( 'dt_mapping_module_available_geojson' ) ) {
                return get_transient( 'dt_mapping_module_available_geojson' );
            }

            // get mirror source
            $mirror_source = dt_get_saturation_mapping_mirror( true );
            // get new array
            $list = file_get_contents( $mirror_source . 'polygon/available_polygons.json' );
            if ( ! $list ) {
                dt_write_log( 'Failed to retrieve available locations list. Check Mapping admin configuration.' );
                dt_write_log( $list );

                return [];
            }
            $list = json_decode( $list, true );

            // cache new response
            set_transient( 'dt_mapping_module_available_geojson', $list, 60 * 60 * 24 );

            return $list;
        }

        public static function reset_available_geojson() {
            return delete_option( 'dt_mapping_module_available_geojson' );
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
        public function format_geoname_types( $query ) {
            if ( ! empty( $query ) || ! is_array( $query ) ) {
                foreach ( $query as $index => $value ) {
                    if ( isset( $value['geonameid'] ) ) {
                        $query[$index]['geonameid'] = (int) $value['geonameid'];
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
                        $query[$index]['parent_id'] = (float) $value['parent_id'];
                    }
                    if ( isset( $value['country_geonameid'] ) ) {
                        $query[$index]['country_geonameid'] = (float) $value['country_geonameid'];
                    }
                    if ( isset( $value['admin1_geonameid'] ) ) {
                        $query[$index]['admin1_geonameid'] = (float) $value['admin1_geonameid'];
                    }
                    if ( isset( $value['admin2_geonameid'] ) ) {
                        $query[$index]['admin2_geonameid'] = (float) $value['admin2_geonameid'];
                    }
                    if ( isset( $value['admin3_geonameid'] ) ) {
                        $query[$index]['admin3_geonameid'] = (float) $value['admin3_geonameid'];
                    }
                }
            }
            return $query;
        }
        public function get_post_locations( $post_id ) {
            $list = [];
            $geoname_list = get_post_meta( $post_id, 'geonames' );
            if ( !empty( $geoname_list ) ) {
                $list = Disciple_Tools_Mapping_Queries::get_by_geonameid_list( $geoname_list );
            }
            return $list;
        }

        public function get_countries_grouped_by_region( $regions = null ): array {
            $regions = Disciple_Tools_Mapping_Queries::get_regions();
            $countries = Disciple_Tools_Mapping_Queries::get_countries();
            $list = [];

            foreach ( $regions as $item ) {
                $cc2 = explode( ',', $item['cc2'] );

                $list[$item['geonameid']]['name'] = $item['name'];
                $list[$item['geonameid']]['country_codes'] = $cc2;

                foreach ( $countries as $country ) {
                    if ( array_search( $country['country_code'], $cc2 ) !== false ) {
                        $list[$item['geonameid']]['countries'][] = $country;
                    }
                }
            }

            return $list;
        }
    } DT_Mapping_Module::instance(); // end DT_Mapping_Module class


    /**
     * Best way to call for the mapping polygon
     * @return array|string
     */
    function dt_get_saturation_mapping_mirror( $url_only = false ) {
        $mirror = get_option( 'dt_saturation_mapping_mirror' );
        if ( empty( $mirror ) ) {
            $array = [
                'key' => 'github',
                'label' => 'GitHub',
                'url' => 'https://raw.githubusercontent.com/DiscipleTools/saturation-grid-project/master/'
            ];
            update_option( 'dt_saturation_mapping_mirror', $array, true );
            $mirror = $array;
        }

        if ( $url_only ) {
            return $mirror['url'];
        }

        return $mirror;
    }
} // end if class check
