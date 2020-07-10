<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'DT_Mapping_Module' ) ) {
    if ( ! function_exists( 'wp_create_nonce' ) ) {
        require_once( ABSPATH . '/wp-includes/pluggable.php' );
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
        public $cache_length;

        // Singleton
        private static $_instance = null;
        public static function instance() {
            global $dt_mapping;
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self( $dt_mapping );
            }
            return self::$_instance;
        }

        public function __construct( $dt_mapping ) {

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
             *
             * For targeted use. Give only the 'view_mapping' permission
             *
             * Governing filter living inside
             * @link mapping-module-config.php
             *
             *
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
                if ( current_user_can( 'view_any_contacts' )
                    || current_user_can( 'view_project_metrics' )
                    || current_user_can( 'view_mapping' ) ) {
                    $this->permissions = true;
                }
                return;
            }
            /** END PERMISSION CHECK */



            /**
             * SET FILE LOCATIONS
             */
            $this->module_path = $dt_mapping['path'];
            $this->module_url  = $dt_mapping['url'];
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

            if ( 'mapping' === $url_base && ! DT_Mapbox_API::get_key() ) {
                if ( 'mapping' === substr( $url_path, '0', $url_base_length ) ) {
                    add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
                    add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 99 );
                    add_action( 'wp_enqueue_scripts', [ $this, 'drilldown_script' ], 89 );
                    add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
                }
            }
            else if ( $url_base === substr( $url_path, '0', $url_base_length ) && ! DT_Mapbox_API::get_key() ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'drilldown_script' ], 89 );
            }
            /* End DEFAULT MAPPING DEFINITION */

            add_action( 'delete_post', [ $this, 'delete_grid_meta_on_post_delete' ] );

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
                    <li><a href="' . esc_url( site_url( '/metrics/mapping/' ) ) . '#mapping_view" onclick="page_mapping_view()">' . esc_html__( 'Map', 'disciple_tools' ) . '</a></li>
                    <li><a href="' . esc_url( site_url( '/metrics/mapping/' ) ) . '#mapping_list" onclick="page_mapping_list()">' . esc_html__( 'List', 'disciple_tools' ) . '</a></li>
                </ul>
            </li>';
            return $content;
        }

        public function scripts() {
            global $dt_mapping;

            // Amcharts
            wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, 4, true );
            wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, 4, true );
            wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', [], 4, true );
            wp_register_script( 'amcharts-maps', 'https://www.amcharts.com/lib/4/maps.js', false, 4, true );

            $this->drilldown_script();

            // mapping css
            wp_register_style( 'mapping-css', $dt_mapping["mapping_css_url"], [], $dt_mapping["mapping_css_version"] );
            wp_enqueue_style( 'mapping-css' );

            // Mapping Script
            wp_enqueue_script( 'dt_mapping_js',
                $dt_mapping['mapping_js_url'],
                [
                    'jquery',
                    'jquery-ui-core',
                    'amcharts-core',
                    'amcharts-animated',
                    'amcharts-maps',
                    'mapping-drill-down',
                    'lodash'
                ], $dt_mapping['mapping_js_version'], true
            );
            wp_localize_script(
                'dt_mapping_js', 'mappingModule', [
                    'root' => esc_url_raw( rest_url() ),
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'current_user_login' => wp_get_current_user()->user_login,
                    'current_user_id' => get_current_user_id(),
                    'mapping_module' => $this->localize_script(),
                ]
            );

        }


        public function drilldown_script() {
            global $dt_mapping;
            // Drill Down Tool
            $settings = apply_filters( 'dt_mapping_module_settings', $this->settings() );
            wp_enqueue_script( 'mapping-drill-down', $dt_mapping['drill_down_js_url'], [ 'jquery', 'lodash' ], $dt_mapping['drill_down_js_version'] );
            wp_localize_script(
                'mapping-drill-down',
                'drilldownModule', [
                    'drilldown' => [
                        $settings['current_map'] => $this->drill_down_array( $settings['current_map'] )
                    ],
                    "settings" => $this->drillown_settings(),
                    "current_map" => $settings["current_map"]
                ]
            );
        }

        public function drillown_settings() {
            global $dt_mapping;
            return [
                'root' => esc_url_raw( rest_url() ),
                'endpoints' => apply_filters( 'dt_mapping_module_endpoints', $this->default_endpoints() ),
                'spinner' => '<img src="'. $dt_mapping['spinner'] . '" width="12px" />',
                'spinner_large' => '<img src="'. $dt_mapping['spinner'] . '" width="24px" />',
            ];
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
                // 'data' => apply_filters( 'dt_mapping_module_data', $this->data() ),
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

            return $data;
        }
        public function settings() {
            global $dt_mapping;
            $settings = [];

            $settings['root'] = esc_url_raw( rest_url() );
            $settings['endpoints'] = $this->endpoints;
            $settings['mapping_source_url'] = dt_get_location_grid_mirror( true );
            $settings['population_division'] = $this->get_population_division();
            $settings['default_map_settings'] = $this->default_map_settings();
            $settings['spinner'] = ' <img src="'. $dt_mapping['spinner'] . '" width="12px" />';
            $settings['spinner_large'] = ' <img src="'. $dt_mapping['spinner'] . '" width="24px" />';
            $settings['heatmap_focus'] = 0;
            $settings['current_map'] = ( isset( $settings['default_map_settings']["children"] ) && count( $settings['default_map_settings']["children"] ) === 1 ) ? $settings['default_map_settings']["children"][0] : $settings['default_map_settings']["parent"];
            $settings['cached'] = 0; // this controls the endpoint transient caching

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

        /************************************************************************************************************
         * ENDPOINTS
         ************************************************************************************************************/
        public function default_endpoints( $endpoints = [] ) {
            /** Defines a default length of cache. @var cache_length */
            $this->cache_length = apply_filters( 'dt_mapping_cache_length', 60 *60 );

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

            if ( isset( $params['cached'] ) && ! empty( $params['cached'] ) ) {
                $trans_key = 'dt_default_map_';
                if ( ! empty( get_transient( $trans_key ) ) ) {
                    return get_transient( $trans_key );
                }
            }

            $response = $this->localize_script();

            // set transient for cached
            if ( isset( $params['cached'] ) && ! empty( $params['cached'] ) ) {
                set_transient( $trans_key, $response, $this->cache_length );
            }

            return $response;
        }

        public function get_map_by_grid_id_endpoint( WP_REST_Request $request ) {
            if ( ! current_user_can( 'view_mapping' ) && ! $this->permissions ) {
                return new WP_Error( __METHOD__, 'No permission', [ 'status' => 101 ] );
            }
            $params = $request->get_params();

            if ( isset( $params['grid_id'] ) ) {
                // check for cached
                if ( isset( $params['cached'] ) && ! empty( $params['cached'] ) ) {
                    dt_write_log( 'cache triggered' );
                    dt_write_log( $this->cache_length );
                    $trans_key = 'dt_grid_' . hash( 'sha256', $params['grid_id'] );
                    if ( ! empty( get_transient( $trans_key ) ) ) {
                        return get_transient( $trans_key );
                    }
                }

                $grid_id = sanitize_key( wp_unslash( $params['grid_id'] ) );

                $response = $this->map_level_by_grid_id( $grid_id );

                // set transient for cache
                if ( isset( $params['cached'] ) && ! empty( $params['cached'] ) ) {
                    set_transient( $trans_key, $response, $this->cache_length );
                }

                return $response;
            } else {
                return new WP_Error( __METHOD__, 'Missing parameters.', [ 'status' => 400 ] );
            }
        }

        public function modify_location_endpoint( WP_REST_Request $request ) {
            if ( ! $this->permissions ) {
                return new WP_Error( 'permissions', 'No permissions for the action.', [ 'status' => 401 ] );
            }

            $params = $request->get_params();

            return DT_Mapping_Module_Admin::instance()->process_rest_edits( $params );
        }

        public function search_location_grid_by_name( WP_REST_Request $request ){
            if ( ! current_user_can( 'read_location' ) && ! $this->permissions ) {
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
            $params = $request->get_params();

            if ( isset( $params['grid_id'] ) ) {
                // check for cached
                if ( isset( $params['cached'] ) && ! empty( $params['cached'] ) ) {
                    $trans_key = 'dt_drill_' . hash( 'sha256', $params['grid_id'] );
                    if ( ! empty( get_transient( $trans_key ) ) ) {
                        return get_transient( $trans_key );
                    }
                }

                $grid_id = sanitize_key( wp_unslash( $params['grid_id'] ) );

                $response = $this->drill_down_array( $grid_id );

                // set transient for cached
                if ( isset( $params['cached'] ) && ! empty( $params['cached'] ) ) {
                    set_transient( $trans_key, $response, $this->cache_length );
                }

                return $response;
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
        /************************************************************************************************************
         * END ENDPOINTS
         ************************************************************************************************************/


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
            $preset_array = [];
            $preset_array[] = [
                'parent' => 'world',
                'selected' => 'world',
                'selected_name' => __( 'World', 'disciple_tools' ),
                'link' => true,
                'active' => false,
            ];
            $preset_array[] = [
                'parent' => 'world',
                'selected' => (int) $reference['admin0_grid_id'],
                'selected_name' => $reference['admin0_name'],
                'link' => true,
                'active' => false,
            ];
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

            if ( (int) $reference["grid_id"] != (int) $preset_array[ sizeof( $preset_array ) - 1 ]['selected'] ){
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
            $preset_array[ sizeof( $preset_array ) - 1 ]["self"] = $reference;
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

            if ( empty( $grid_id ) || $grid_id === 'world' || $grid_id === 'world' || $grid_id === '1' || $grid_id === 1 ) {

                if ( wp_cache_get( 'drill_down_array_default' ) ) {
                    return wp_cache_get( 'drill_down_array_default' );
                }

                $grid_id = null;

                $child_list = $this->format_location_grid_types( Disciple_Tools_Mapping_Queries::get_children_by_grid_id( 1 ) );

                $deeper_levels = $this->get_deeper_levels( $child_list );

                $selected_name = __( 'World', 'disciple_tools' );
                $selected_grid_id = 1;
                if ( $default_level['type'] === 'country' && $default_select_first_level ){
                    $selected_name = $list[ array_keys( $list )[0] ];
                    $selected_grid_id = array_keys( $list )[0];
                }
                if ( $default_level['type'] === 'state' ){
                    if ( $default_select_first_level ){
                        $selected_name = $list[ array_keys( $list )[0] ];
                        $selected_grid_id = array_keys( $list )[0];
                    } else {
                        $parent = $this->format_location_grid_types( Disciple_Tools_Mapping_Queries::get_by_grid_id( $child_list[0]["parent_id"] ) );
                        $selected_grid_id = $child_list[0]["parent_id"];
                        $selected_name = $parent["name"] ?? $selected_name;
                    }
                }

                $preset_array = [
                    [
                        'parent' => 'world',
                        'selected' => $selected_grid_id,
                        'selected_name' => $selected_name,
                        'link' => true,
                        'active' => false,
                    ],
                    [
                        'parent' => 'world',
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
            $level = apply_filters( 'dt_starting_map_level', get_option( 'dt_mapping_module_starting_map_level' ) );

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
         * Builds default settings for specific grid_id.
         * Note: Useful for functions supplying a different starting point to the display map.
         *
         * @param $grid_id
         * @return array
         */
        public function get_map_level_settings_by_grid_id( $grid_id ) {
            $default_array = [];

            $row = Disciple_Tools_Mapping_Queries::get_by_grid_id( $grid_id );
            //            (
//                [id] => 100364199
//                [grid_id] => 100364199
//                [name] => United States
//                [population] => 310232863
//                [latitude] => 45.7987
//                [longitude] => 0.311424
//                [country_code] => US
//                [admin0_code] => USA
//                [parent_id] => 1
//                [admin0_grid_id] => 100364199
//                [admin1_grid_id] =>
//                [admin2_grid_id] =>
//                [admin3_grid_id] =>
//                [admin4_grid_id] =>
//                [admin5_grid_id] =>
//                [level] => 0
//                [level_name] => admin0
//                [is_custom_location] => 0
//            )

            if ( ! empty( $row ) ) {
                if ( $row['level_name'] === 'world' ) {
                    $default_array = [
                        'type' => 'world',
                        'parent' => 'world',
                        'children' => []
                    ];
                }
                else if ( $row['level_name'] === 'admin0' ) {
                    $default_array = [
                        'type' => 'country',
                        'parent' => 'world',
                        'children' => [ $grid_id ]
                    ];
                }
                else if ( $row['level_name'] === 'admin1' ) {
                    $default_array = [
                        'type' => 'state',
                        'parent' => $row['parent_id'],
                        'children' => [ $grid_id ]
                    ];
                }
                else {
                    $default_array = [
                        'type' => $row['level_name'],
                        'parent' => $row['parent_id'],
                        'children' => [ $grid_id ]
                    ];
                }
            }

            return $default_array;
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

            if ( $grid_id === 'world' ){
                return $this->get_world_map_data();
            }

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

        public function delete_grid_meta_on_post_delete( $post_id ) {
            if ( wp_is_post_revision( $post_id ) ) {
                return;
            }

            $post = get_post( $post_id );

            if ( in_array( $post->post_status, [ 'auto-draft', 'inherit' ] ) ) {
                return;
            }

            // Skip for menu items.
            $post_type = get_post_type( $post->ID );
            if ( ! in_array( $post_type, [ 'contacts', 'groups', 'trainings' ] ) ) {
                return;
            }

            if ( ! class_exists( 'Location_Grid_Geocoder' ) ) {
                require_once( 'geocode-api/location-grid-geocoder.php' );
            }
            Location_Grid_Meta::delete_location_grid_meta( $post_id, 'all', 0 );
        }
    }
    DT_Mapping_Module::instance(); // end DT_Mapping_Module class
} // end if class check

