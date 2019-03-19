<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'DT_Mapping_Module' )  ) {

    /**
     * Set Global Database Variables
     */
    global $wpdb;
    $wpdb->dt_geonames = 'dt_geonames';
    $wpdb->dt_geonames_hierarchy = 'dt_geonames_hierarchy';
    $wpdb->dt_geonames_reference = $wpdb->prefix . 'dt_geonames_reference';

    /*******************************************************************************************************************
     * MIGRATION ENGINE
     ******************************************************************************************************************/
    require_once('class-migration-engine.php');
    try{
        DT_Mapping_Module_Migration_Engine::migrate( DT_Mapping_Module_Migration_Engine::$migration_number );
    } catch ( Throwable $e ) {
        $error = new WP_Error( 'migration_error', 'Migration engine for mapping module failed to migrate.', ['error' => $e ] );
        dt_write_log($error);
    }
    /*******************************************************************************************************************/

    if ( ! function_exists( 'spinner' ) ) {
        function spinner() {
            $dir = __DIR__;
            if ( strpos( $dir,'wp-content/themes' ) ) {
                $nest = explode( get_stylesheet(), plugin_dir_path(__FILE__ ) );
                return get_theme_file_uri() . $nest[1] . 'spinner.svg';
            } else if ( strpos( $dir,'wp-content/plugins' ) ) {
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

            require_once( 'add-contacts-column.php' );
            require_once( 'add-groups-column.php' );
            require_once( 'mapping-admin.php' );

            /**
             * SET FILE LOCATIONS
             */
            $dir = __DIR__;
            if ( strpos( $dir,'wp-content/themes' ) ) {
                $this->module_path = plugin_dir_path(__FILE__ );
                $nest = explode( get_stylesheet(), plugin_dir_path(__FILE__ ) );
                $this->module_url = get_theme_file_uri() . $nest[1];
            } else if ( strpos( $dir,'wp-content/plugins' ) ) {
                $this->module_path = plugin_dir_path(__FILE__ );
                $this->module_url = plugin_dir_url(__FILE__ );
            } else {
                $this->module_path = plugin_dir_path(__FILE__ );
                $this->module_url = plugin_dir_url(__FILE__ );
            }
            /** END SET FILE LOCATIONS */

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
                $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) ) ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) ) : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
                if ( isset( $_SERVER["REQUEST_URI"] ) ) {
                    $url .= sanitize_text_field( wp_unslash( $_SERVER["REQUEST_URI"] ) );
                }
            }
            $url_path = trim( str_replace( get_site_url(), "", $url ), '/' );
            if ( 'mapping' ===  $url_base ) {

                add_action( 'dt_top_nav_desktop', [ $this, 'top_nav_desktop' ] ); // add menu bar before checking for page

                if ( 'mapping' === substr( $url_path, '0', $url_base_length ) ) {

                    add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
                    add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 99 );
                    add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
                }
            }
            else if ( $url_base === substr( $url_path, '0', $url_base_length ) ) {
                add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
            }
            /* End DEFAULT MAPPING DEFINITION */

        }

        /**
         * ENABLED DEFAULT NAVIGATION FUNCTIONS
         */
        public function top_nav_desktop() {
            ?><li><a href="<?php echo esc_url( site_url( '/mapping/' ) ) . '#mapping_view' ; ?>"><?php esc_html_e( "Mapping" ); ?></a></li><?php
        }
        public function add_url( $template_for_url ) {
            $template_for_url['mapping'] = 'template-metrics.php';
            return $template_for_url;
        }
        public function menu( $content ) {
            $content .= '<li><a href="'. esc_url( site_url( '/mapping/' ) ) .'#mapping_view" onclick="page_mapping_view()">' .  esc_html__( 'Map' ) . '</a></li>';
            $content .= '<li><a href="'. esc_url( site_url( '/mapping/' ) ) .'#mapping_list" onclick="page_mapping_list()">' .  esc_html__( 'List' ) . '</a></li>';
            return $content;
        }
        public function scripts() {

            // self hosted or publically hosted amcharts scripts
            if ( get_option( 'dt_mapping_module_local_amcharts' ) === true ) { // local hosted @todo add checkbox to admin area

                wp_enqueue_script( 'amcharts-core', $this->module_url . 'amcharts/dist/script/core.js',
                    [], filemtime( $this->module_path . 'amcharts4/dist/script/core.js' ), true );
                wp_enqueue_script( 'amcharts-charts', $this->module_url . 'amcharts/dist/script/charts.js',
                    [
                        'amcharts-core'
                    ], filemtime( $this->module_path . 'amcharts4/dist/script/charts.js' ), true );
                wp_enqueue_script( 'amcharts-maps', $this->module_url . 'amcharts/dist/script/maps.js',
                    [
                        'amcharts-core'
                    ], filemtime( $this->module_path . 'amcharts4/dist/script/maps.js' ), true );
                wp_enqueue_script( 'amcharts-animated', $this->module_url . 'amcharts/dist/script/themes/animated.js',
                    [
                        'amcharts-core'
                    ], filemtime( $this->module_path . 'amcharts4/dist/script/themes/animated.js' ), true );

            } else { // cdn hosted files

                wp_register_script( 'amcharts-core', 'https://www.amcharts.com/lib/4/core.js', false, '4' );
                wp_register_script( 'amcharts-charts', 'https://www.amcharts.com/lib/4/charts.js', false, '4' );
                wp_register_script( 'amcharts-animated', 'https://www.amcharts.com/lib/4/themes/animated.js', false, '4' );
                wp_register_script( 'amcharts-maps', 'https://www.amcharts.com/lib/4/maps.js', false, '4' );
                wp_register_script( 'amcharts-world', 'https://www.amcharts.com/lib/4/geodata/worldLow.js', false, '4' );

            }

            wp_register_style( 'datatable-css', '//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css' );
            wp_enqueue_style( 'datatable-css' );
            wp_register_script( 'datatable', '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js', false, '1.10' );

            // drill down tool
            wp_enqueue_script( 'mapping-drill-down', get_template_directory_uri() . '/dt-mapping/drill-down.js', [ 'jquery', 'lodash' ], '1.1' );
            wp_localize_script(
                'mapping-drill-down', 'mappingModule', array(
                    'mapping_module' => DT_Mapping_Module::instance()->localize_script(),
                )
            );

            wp_enqueue_script( 'dt_mapping_module_script', $this->module_url . 'mapping.js', [
                'jquery',
                'jquery-ui-core',
                'amcharts-core',
                'amcharts-charts',
                'amcharts-animated',
                'amcharts-maps',
                'amcharts-world',
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

            $data['top_map_list'] = $this->default_map_short_list();
            if ( isset( $data['top_map_list']['world'] ) ) {
                $data['world'] = $this->get_world_map_data();
            } else {
                foreach( $data['top_map_list'] as $geonameid => $name ) {
                    $data[$geonameid] = $this->map_level_by_geoname( $geonameid );
                }
                $default_map_settings = $this->default_map_settings();
                $data[$default_map_settings['parent']] = $this->map_level_by_geoname( $default_map_settings['parent'] );
            }

            $data['custom_column_labels'] = [];
            $data['custom_column_data'] = [];


            return $data;
        }
        public function settings() {
            $settings = [];

            $settings['root'] = esc_url_raw( rest_url() );
            $settings['endpoints'] = DT_Mapping_Module::instance()->endpoints;
            $settings['mapping_source_url'] = dt_get_mapping_polygon_mirror( true );
            $settings['population_division'] = $this->get_population_division();
            $settings['default_map_settings'] = $this->default_map_settings();
            $settings['spinner'] = ' <img src="'. spinner() . '" width="12px" />';
            $settings['spinner_large'] = ' <img src="'. spinner() . '" width="24px" />';

            return $settings;
        }
        public function translations( ) {
            $translations = [];

            $translations['title'] = __( "Mapping", "dt_mapping_module" );

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
            $endpoints['get_children'] = [ // @todo remove with the explore section of the admin
                'namespace' => $this->namespace,
                'route' => '/mapping_module/get_children',
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

        public function get_children( WP_REST_Request $request ) { // @todo remove with the explore section of the admin
            /**
             * Services the explore section of the admin area
             */
            if ( ! $this->permissions ) {
                return new WP_Error( __METHOD__, 'No permission', [ 'status' => 101 ] );
            }

            $params = $request->get_params();
            if ( isset( $params['geonameid'] ) ) {
                return $this->get_locations_list( $params['geonameid'] );
            } else {
                return new WP_Error( __METHOD__, 'Missing parameters.', [ 'status' => 400 ] );
            }
        }

        /**
         * MAP BUILDING
         */

        /**
         * These default map data sections can have data added to them through the filter.
         *
         * @return array
         */

        public function get_default_map_data( ) {

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
                        $self = $this->query( 'get_by_geonameid', ['geonameid' => $geonameid ] );
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
                        $children = $this->query( 'get_children_by_geonameid', ['geonameid' => $geonameid ] );

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

                        $self = $this->query( 'get_by_geonameid_list', ['list' => array_keys( $starting_map_level['children'] ) ] );
                        if ( empty( $self ) ) {
                            return $this->get_world_map_data();
                        }

                        foreach ( $starting_map_level['children'] as $k => $v ) {
                            $geonameid = $k;

                            // self
                            $self = $this->query( 'get_by_geonameid', ['geonameid' => $geonameid ] );
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
                            $children = $this->query( 'get_children_by_geonameid', ['geonameid' => $geonameid ] );

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
                dt_write_log(__METHOD__ . ': Set the initial map level' );
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
                $list = ['world' => 'World'];
            }
            else if ( $default_map_settings['type'] !== 'world' && empty( $default_map_settings['children'] ) ) {
                $list = ['world' => 'World'];
            }
            else {
                $children = $this->query( 'get_by_geonameid_list', [ 'list' => $default_map_settings['children'] ] );
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
            $parent = $this->query( 'get_parent_by_geonameid', ['geonameid' => $geonameid ] );
            if ( ! empty( $parent ) ) {
                $results['parent'] = $parent;

                // set types
                $results['parent']['id'] = (int) $parent['id'];
                $results['parent']['geonameid'] = (int) $parent['geonameid'];
                $results['parent']['population'] = (int) $parent['population'];
                $results['parent']['population_formatted'] = number_format( $parent['population'] );
                $results['parent']['latitude'] = (float) $parent['latitude'];
                $results['parent']['longitude'] = (float) $parent['longitude'];
            }

            $self = $this->query( 'get_by_geonameid', ['geonameid' => $geonameid ] );
            if ( ! empty( $self ) ) {
                $results['self'] = $self;

                // set types
                $results['self']['id'] = (int) $self['id'];
                $results['self']['geonameid'] = (int) $self['geonameid'];
                $results['self']['population'] = (int) $self['population'];
                $results['self']['population_formatted'] = number_format( $self['population'] );
                $results['self']['latitude'] = (float) $self['latitude'];
                $results['self']['longitude'] = (float) $self['longitude'];
            }

            // get children
            $children = $this->query( 'get_children_by_geonameid', ['geonameid' => $geonameid ] );
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

            $available_geojson = $this->get_available_geojson();
            if ( ! empty( $results['children'] ) || ! empty( $available_geojson ) ) {
                foreach( $results['children'] as $index => $child ) {
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


            $results['self'] =  [
                'name' => 'World',
                'id' => 'world',
                'geonameid' => 6295630,
                'population' => 7700000000,
                'population_formatted' => number_format(7700000000 ),
                'latitude' => 0,
                'longitude' => 0,
                'countries' => [],
                'unique_source_url' => false,
                'url' => '',
            ];
            $results['children'] = $this->get_countries_map_data();
            $results['deeper_levels'] = $this->get_deeper_levels( $results['children'] );

            return $results;
        }

        public function get_countries_map_data() {
            $children = $this->query( 'list_countries' );

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
                }
            }
            return $results;
        }

        public function get_deeper_levels( array $children ) {
            $available_geojson = $this->get_available_geojson();
            $results = [];
            if ( ! empty( $children ) || ! empty( $available_geojson ) ) {
                foreach( $children as $index => $child ) {
                    if ( isset( $available_geojson[$child['geonameid']] ) ) {
                        $results[$child['geonameid']] = true;
                    }
                }
            }
            return $results;
        }

        public function get_geonameid_title( int $geonameid ) : string {
            $result = $this->query( 'get_by_geonameid', [ 'geonameid' => $geonameid ] );
            return $result['name'] ?? '';
        }


        public function get_available_geojson() {

            //caching response
//            self::reset_available_geojson(); // @todo remove (only used for dev)
            if ( get_option( 'dt_mapping_module_available_geojson') ) {
                return get_option( 'dt_mapping_module_available_geojson');
            }

            // get mirror source
            $mirror_source = dt_get_mapping_polygon_mirror( true );
            // get new array
            $list = file_get_contents( $mirror_source . 'available_locations.json' );
            if ( ! $list ) {
                dt_write_log('Failed to retrieve available locations list. Check Mapping admin configuration.');
                dt_write_log($list);

                return [];
            }
            $list = json_decode($list, true );

            // cache new response
            add_option('dt_mapping_module_available_geojson', $list, null, false );

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

        /**
         * UTILITIES SECTION
         */

        /**
         * All Queries
         *
         * @param       $type
         * @param array $args
         *
         * @return array|int|null|object|string|\WP_Error
         */
        public function query( $type, $args = [] ) {
            global $wpdb; $results = [];

            if ( empty( $type ) ) {
                return new WP_Error( __METHOD__, 'Required type is missing.' );
            }

            switch ( $type ) {

                case 'get_by_geonameid':
                    if ( isset( $args['geonameid'] ) ) {
                        $results = $wpdb->get_row( $wpdb->prepare( "
                            SELECT 
                              geonameid as id, 
                              geonameid as geonameid, 
                              name, 
                              population,
                              latitude,
                              longitude,
                              country_code,
                              feature_code
                            FROM dt_geonames
                            WHERE geonameid = %s
                             ORDER BY name ASC
                        ", $args[ 'geonameid' ] ), ARRAY_A );
                    }
                    break;

                case 'get_parent_by_geonameid':
                    if ( isset( $args['geonameid'] ) ) {
                        $results = $wpdb->get_row( $wpdb->prepare( "
                            SELECT 
                              gh.parent_id as id, 
                              gh.parent_id as geonameid, 
                              gp.name, 
                              gp.population,
                              gp.latitude,
                              gp.longitude,
                              gc.country_code
                            FROM dt_geonames_hierarchy as gh
                              JOIN dt_geonames as gp
                                ON gp.geonameid=gh.parent_id
                              JOIN dt_geonames as gc
                                ON gc.geonameid=gh.id
                            WHERE id = %s
                        ", $args[ 'geonameid' ] ), ARRAY_A );
                    }
                    break;

                case 'get_children_by_geonameid':
                    if ( isset( $args['geonameid'] ) ) {
                        $results = $wpdb->get_results( $wpdb->prepare( "
                            SELECT DISTINCTROW
                              g.geonameid as id, 
                              g.geonameid, 
                              g.name, 
                              g.population, 
                              g.latitude, 
                              g.longitude,
                              g.country_code
                            FROM dt_geonames_hierarchy as gh
                              JOIN dt_geonames as g
                              ON g.geonameid=gh.id
                            WHERE parent_id = %d
                            ORDER BY g.name ASC
                        ", $args['geonameid'] ), ARRAY_A );
                    }
                    break;

                case 'get_by_geonameid_list':
                    /**
                     * Gets a specific list of geonameids
                     * This requires an array of geonames.
                     */
                    if ( isset( $args['list'] ) && is_array( $args['list'] ) ) {
                        $prepared_list = '';
                        $i = 0;
                        foreach ( $args['list'] as $list ) {
                            if ( $i !== 0 ) {
                                $prepared_list .= ',';
                            }
                            $prepared_list .= (int) $list;
                            $i++;
                        }
                        $results = $wpdb->get_results("
                            SELECT DISTINCTROW
                              g.geonameid as id, 
                              g.geonameid, 
                              g.name, 
                              g.population, 
                              g.latitude, 
                              g.longitude,
                              g.country_code,
                              g.feature_code
                            FROM dt_geonames as g
                            WHERE geonameid IN ($prepared_list)
                            ORDER BY g.name ASC
                        ", ARRAY_A );
                    }
                    break;

                case 'list_countries':
                    if ( isset( $args['only_countries'] ) ) {
                        /**
                         * Returns list of countries and territories, excluding:
                         * PCLD: dependent political entities (guam, american samoa, etc.)
                         * PCLF: freely associated state (micronesia, federated states of)
                         * PCLH: historical political entity, a former political entity (Netherlands Antilles)
                         * PCLIX: section of independent political entity
                         * PCLS: semi-independent political entity
                         */
                        $results = $wpdb->get_results( "
                         SELECT 
                          country_code as id, 
                          geonameid, 
                          name, 
                          population, 
                          latitude, 
                          longitude,
                          country_code
                         FROM dt_geonames 
                         WHERE feature_code = 'PCLI' OR feature_code = 'TERR' AND geonameid != 6697173
                         ORDER BY name ASC
                        ", ARRAY_A );
                    }
                    else {
                        /**
                         * Returns full list of countries, territories, and other political geographic entities.
                         * PCLI	independent political entity
                         * PCLD: dependent political entities (guam, american samoa, etc.)
                         * PCLF: freely associated state (micronesia, federated states of)
                         * PCLH: historical political entity, a former political entity (Netherlands Antilles)
                         * PCLIX: section of independent political entity
                         * PCLS: semi-independent political entity
                         * TERR: territory
                         */
                        $results = $wpdb->get_results( "
                         SELECT 
                          country_code as id, 
                          geonameid, 
                          name, 
                          population, 
                          latitude, 
                          longitude,
                          country_code
                         FROM dt_geonames 
                         WHERE feature_code LIKE 'PCL%' OR feature_code = 'TERR' AND geonameid != 6697173
                         ORDER BY name ASC
                        ", ARRAY_A );
                    }

                    if ( empty( $results ) ) {
                        $results = [];
                    }

                    break;


                case 'get_country_code_by_id':
                    if ( isset( $args['id'] ) ) {
                        $results = $wpdb->get_var( $wpdb->prepare( "
                            SELECT country_code FROM dt_geonames WHERE geonameid = %s;
                        ", $args['id'] ) );
                    }
                    if ( ! isset( $args['id'] ) ) {
                        $results = 0;
                    }

                    break;

                case 'get_full_hierarchy':
                    $results = $wpdb->get_results("
                          SELECT DISTINCTROW parent_id, id, name 
                          FROM dt_geonames_hierarchy", ARRAY_A );

                    break;

                case 'get_reference':
                    if ( isset( $args['post_id'] ) ) {
                        $results = $wpdb->get_row( $wpdb->prepare( "
                            SELECT * 
                            FROM {$wpdb->prefix}dt_geonames_reference 
                            WHERE post_id = %s;
                        ", $args['post_id'] ), ARRAY_A );
                    }
                    else if ( isset( $args['geonameid'] ) ) {
                        $results = $wpdb->get_row( $wpdb->prepare( "
                            SELECT * 
                            FROM {$wpdb->prefix}dt_geonames_reference 
                            WHERE geonameid = %d;
                        ", $args['geonameid'] ), ARRAY_A );
                    }
                    break;

                case 'get_continents_countries_and_states':
                    $results = $wpdb->get_results("
                            SELECT DISTINCT parent_id, id, name
                            FROM dt_geonames_hierarchy
                            JOIN dt_geonames as g 
                            ON dt_geonames_hierarchy.id=g.geonameid
                            WHERE parent_id IN (
                              SELECT geonameid
                              FROM dt_geonames
                              WHERE feature_code = 'PCLI'
                              )
                            OR id IN (
                              SELECT geonameid
                              FROM dt_geonames
                              WHERE feature_code = 'PCLI'
                            )
                            OR parent_id IN (6255146,6255147,6255148,6255149,6255151,6255150,6255152,6295630)
                            OR id IN (6255146,6255147,6255148,6255149,6255151,6255150,6255152,6295630);
                        ", ARRAY_A );

                    break;

                case 'get_continents_and_countries':
                    $results = $wpdb->get_results("
                            SELECT DISTINCT parent_id, id, g.name
                            FROM dt_geonames_hierarchy
                            JOIN dt_geonames as g 
                            ON dt_geonames_hierarchy.id=g.geonameid
                            WHERE id IN (
                              SELECT geonameid
                              FROM dt_geonames
                              WHERE feature_code = 'PCLI'
                            )
                            OR parent_id IN (6255146,6255147,6255148,6255149,6255151,6255150,6255152,6295630)
                            OR id IN (6255146,6255147,6255148,6255149,6255151,6255150,6255152,6295630);
                        ", ARRAY_A );

                    break;

                case 'get_regions':
                    if ( isset( $args['add_country_info'] ) ) {
                        /**
                         * Lists all countries with their region_name and region_id
                         * @note There are often two regions that claim the same country.
                         */
                        $results = $wpdb->get_results("
                            SELECT
                                gh.parent_id as region_id,
                                rg.name as region_name,
                                g.*
                            FROM dt_geonames_hierarchy as gh
                            JOIN dt_geonames as g ON gh.id=g.geonameid
                            LEFT JOIN dt_geonames as rg ON gh.parent_id=rg.geonameid
                            WHERE 
                            parent_id IN (
                              SELECT rgn.geonameid 
                              FROM dt_geonames as rgn 
                              WHERE rgn.feature_code = 'RGN'  
                                AND rgn.country_code = '')
                            ORDER BY region_name ASC;
                        ", ARRAY_A );
                    } else {
                        $results = $wpdb->get_results("
                            SELECT *
                            FROM dt_geonames
                            WHERE feature_code = 'RGN' AND country_code = ''
                            ORDER BY name ASC;
                        ", ARRAY_A );
                    }

                    break;

                case 'get_continents':
                    $results = $wpdb->get_results("
                            SELECT *
                            FROM dt_geonames
                            WHERE geonameid IN (6255146,6255147,6255148,6255149,6255151,6255150,6255152)
                            ORDER BY name ASC;
                        ", ARRAY_A );

                    break;

                case 'get_earth':
                    $results = $wpdb->get_results("
                            SELECT *
                            FROM dt_geonames
                            WHERE geonameid = 6295630
                        ", ARRAY_A );

                    break;

                case 'get_geoname_totals':
                    $results = $wpdb->get_results("
                            SELECT
                              PCLI as geonameid,
                              'PCLI'      as level,
                              post_type,
                              count(PCLI) as count
                            FROM {$wpdb->prefix}dt_geonames_reference
                            WHERE PCLI != ''
                            GROUP BY PCLI, post_type
                            UNION
                            SELECT
                              ADM1 as geonameid,
                              'ADM1'      as level,
                              post_type,
                              count(ADM1) as count
                            FROM {$wpdb->prefix}dt_geonames_reference
                            WHERE ADM1 != ''
                            GROUP BY ADM1, post_type
                            UNION
                            SELECT
                              ADM2 as geonameid,
                              'ADM2'      as level,
                              post_type,
                              count(ADM2) as count
                            FROM {$wpdb->prefix}dt_geonames_reference
                            WHERE ADM2 != ''
                            GROUP BY ADM2, post_type
                        ", ARRAY_A );

                    break;

                case 'get_hierarchy_for_geoname':
                    /**
                     * Gets a single row from geonameid with country, state, county columns for given geonameid
                     *
                     * @example     [
                     *                  geonameid   (supplied geonameid)
                     *                  country_id  (geonameid for the country related to supplied geonameid)
                     *                  adm1_id    (geonameid for the state/administration level 1 related to supplied geonameid)
                     *                  adm2_id    (geonameid for the county/administration level 2 related to supplied geonameid)
                     *                  country_name
                     *                  adm1_name
                     *                  adm2_name
                     *              ]
                     */
                    if ( isset( $args['geonameid'] ) ) {
                        $results = $wpdb->get_row( $wpdb->prepare( "
                            SELECT
                                  g.geonameid,
                                  (CASE WHEN (`g`.`feature_code` = 'PCLI')
                                    THEN `g`.`geonameid`
                                   WHEN (`g`.`feature_code` = 'ADM1')
                                     THEN (SELECT `dt_geonames_hierarchy`.`parent_id`
                                           FROM `dt_geonames_hierarchy`
                                           WHERE (`dt_geonames_hierarchy`.`id` = `g`.`geonameid`)
                                           LIMIT 1)
                                   WHEN (`g`.`feature_code` = 'ADM2')
                                     THEN (SELECT `dt_geonames_hierarchy`.`parent_id`
                                           FROM `dt_geonames_hierarchy`
                                           WHERE (`dt_geonames_hierarchy`.`id` = (SELECT `dt_geonames_hierarchy`.`parent_id`
                                                                                  FROM `dt_geonames_hierarchy`
                                                                                  WHERE (`dt_geonames_hierarchy`.`id` = `g`.`geonameid`)
                                                                                  LIMIT 1))
                                           LIMIT 1)
                                   WHEN (`g`.`feature_code` = 'ADM3')
                                     THEN (SELECT `dt_geonames_hierarchy`.`parent_id`
                                           FROM `dt_geonames_hierarchy`
                                           WHERE (`dt_geonames_hierarchy`.`id` = (SELECT `dt_geonames_hierarchy`.`parent_id`
                                                                                  FROM `dt_geonames_hierarchy`
                                                                                  WHERE (`dt_geonames_hierarchy`.`id` =
                                                                                         (SELECT `dt_geonames_hierarchy`.`parent_id`
                                                                                          FROM `dt_geonames_hierarchy`
                                                                                          WHERE (`dt_geonames_hierarchy`.`id` = `g`.`geonameid`)
                                                                                          LIMIT 1))
                                                                                  LIMIT 1))
                                           LIMIT 1)
                                   ELSE 'Unknown' END)                         AS `country_id`,
                                  
                                  (CASE WHEN (`g`.`feature_code` = 'PCLI')
                                    THEN NULL
                                   WHEN (`g`.`feature_code` = 'ADM1')
                                     THEN `g`.`geonameid`
                                   WHEN (`g`.`feature_code` = 'ADM2')
                                     THEN (SELECT `dt_geonames_hierarchy`.`parent_id`
                                           FROM `dt_geonames_hierarchy`
                                           WHERE (`dt_geonames_hierarchy`.`id` = `g`.`geonameid`)
                                           LIMIT 1)
                                   WHEN (`g`.`feature_code` = 'ADM3')
                                     THEN (SELECT `dt_geonames_hierarchy`.`parent_id`
                                           FROM `dt_geonames_hierarchy`
                                           WHERE (`dt_geonames_hierarchy`.`id` = (SELECT `dt_geonames_hierarchy`.`parent_id`
                                                                                  FROM `dt_geonames_hierarchy`
                                                                                  WHERE (`dt_geonames_hierarchy`.`id` = `g`.`geonameid`)
                                                                                  LIMIT 1))
                                           LIMIT 1)
                                   ELSE 'Unknown' END) AS `adm1_id`,
                                
                                  
                                  (CASE WHEN (`g`.`feature_code` = 'PCLI')
                                    THEN NULL
                                   WHEN (`g`.`feature_code` = 'ADM1')
                                     THEN NULL
                                   WHEN (`g`.`feature_code` = 'ADM2')
                                     THEN `g`.`geonameid`
                                   WHEN (`g`.`feature_code` = 'ADM3')
                                     THEN (SELECT `dt_geonames_hierarchy`.`parent_id`
                                           FROM `dt_geonames_hierarchy`
                                           WHERE (`dt_geonames_hierarchy`.`id` = `g`.`geonameid`)
                                           LIMIT 1)
                                   ELSE 'Unknown' END)                         AS `adm2_id`,
                                   (SELECT `dt_geonames`.`name`
                                   FROM `dt_geonames`
                                   WHERE (`dt_geonames`.`geonameid` = `country_id`)) AS `country_name`,
                                  (SELECT `dt_geonames`.`name`
                                   FROM `dt_geonames`
                                   WHERE (`dt_geonames`.`geonameid` = `adm1_id`)) AS `adm1_name`,
                                  (SELECT `dt_geonames`.`name`
                                   FROM `dt_geonames`
                                   WHERE (`dt_geonames`.`geonameid` = `adm2_id`)) AS `adm2_name` 
                            FROM dt_geonames as g
                            WHERE geonameid = %d
                            ",
                            $args[ 'geonameid' ]
                        ), ARRAY_A );
                    }
                    break;


                default:$results = []; break;

            }

            return $results;
        }


        /**
         * Explore section of the admin area
         */
        //
        public function get_locations_list( $start_geonameid = 6295630 ) {
            global $wpdb;
            $response = [];

            // build list array
            $response['list'] = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCTROW parent_id, id, name FROM dt_geonames_hierarchy WHERE parent_id = %d ORDER BY name ASC", $start_geonameid ), ARRAY_A );

            // build full results
            $query = $wpdb->get_results("SELECT DISTINCTROW parent_id, id, name FROM dt_geonames_hierarchy", ARRAY_A );
            if ( empty( $query ) ) {
                return $this->_no_results();
            }
            $menu_data = $this->prepare_menu_array( $query );
            $response['html'] = $this->build_locations_html_list( $start_geonameid, $menu_data, 0, 3 );

            return $response;
        }
        public function build_locations_html_list( $parent_id, $menu_data, $gen, $depth_limit ) {
            $list = '';

            if (isset( $menu_data['parents'][$parent_id] ) && $gen < $depth_limit )
            {
                $gen++;
                foreach ($menu_data['parents'][$parent_id] as $item_id)
                {
                    $list .= '<div style="padding-left: '.$gen.'0px;">' . $menu_data['items'][$item_id]['name'] . ' ('. $item_id . ')' . '</div>';
                    $sub = $this->build_locations_html_list( $item_id, $menu_data, $gen, $depth_limit );
                    if ( ! empty( $sub ) ) {
                        $list .= $sub;
                    }
                }
            }
            return $list;
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
        public function _no_results() {
            return '<p>'. esc_attr( 'No Results', 'disciple_tools' ) .'</p>';
        }
        /**
         * End explore section of the admin area
         */

        /**
         * Creates the initial drill down array
         *
         * @param null $geonameid
         * @param null $post_id
         *
         * @return array
         */
        public function default_drill_down( $geonameid = null, $post_id = null ) : array {
            $preset_array = [];

            $list = $this->default_map_short_list();

            $default_select_first_level = false;
            if ( count( $list ) < 2 ) {
                $default_select_first_level = true;
            }

            // no starting geonameid
            if ( empty( $geonameid ) ) {

                // if default list is just one country, then prepopulate second level
                if ( $default_select_first_level ) {
                    foreach( $list as $geonameid => $name ) {
                        $preset_array['drill_down_top_level']['list'][] = [
                            'geonameid' => $geonameid,
                            'name' => $name
                        ];
                        $preset_array['drill_down_top_level']['selected'] = $geonameid;
                    }
                    $second_level_list = $this->query( 'get_children_by_geonameid', [ 'geonameid' => $preset_array['drill_down_top_level']['selected']  ] );
                    if ( ! empty( $second_level_list ) ) {
                        foreach( $second_level_list as $item ) {
                            $preset_array[$item['geonameid']]['list'][] = [
                                  'geonameid' => $item['geonameid'],
                                  'name' => $item['name'],
                            ];
                            $preset_array[$item['geonameid']]['selected'] = 0;
                        }
                    }

                // top level list has more than one option
                } else {
                    foreach( $list as $geonameid => $name ) {
                        $preset_array['drill_down_top_level']['list'][] = [
                            'geonameid' => $geonameid,
                            'name' => $name
                        ];
                    }
                    $preset_array['drill_down_top_level']['selected'] = 0;
                }
            }


            // has a starting geonameid and a post_id
            else {

                $list = $this->default_map_short_list();
                $default_list = [];
                foreach ( $list as $key => $value ) {
                    $default_list[$key] = [
                        'geonameid' => $key,
                        'name' => $value,
                    ];
                }

                if ( empty( $post_id ) ) {
                    $reference = $this->query( 'get_reference', [ 'geonameid' => $geonameid ] );
                } else {
                    $reference = $this->query( 'get_reference', [ 'post_id' => $post_id ] );
                }

                switch ( $reference['feature_code'] ) {

                    case 'ADM1':
                        $preset_array = [
                            'drill_down_top_level' => [
                                'selected' => $reference['pcli'] ?? 0,
                                'list' => $default_list,
                            ],
                            $reference['pcli'] => [
                                'selected' => $reference['adm1'] ?? 0,
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['pcli'] ] ),
                            ],
                            $reference['adm1'] => [
                                'selected' => $reference['adm2'] ?? 0,
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['adm1'] ] ),
                            ],
                        ];
                        break;

                    case 'ADM2':
                        $preset_array = [
                            'drill_down_top_level' => [
                                'selected' => $reference['pcli'] ?? 0,
                                'list' => $default_list,
                            ],
                            $reference['pcli'] => [
                                'selected' => $reference['pcli'] ?? 0,
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['pcli'] ] ),
                            ],
                            $reference['adm1'] => [
                                'selected' => $reference['adm2'] ?? 0,
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['adm1'] ] ),
                            ],
                            $reference['adm2'] => [
                                'selected' => $reference['adm3'] ?? 0,
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['adm2'] ] ),
                            ],
                        ];
                        break;

                    case 'PCLI':
                    default:
                    $preset_array = [
                            'drill_down_top_level' => [
                                'selected' => $reference['pcli'] ?? 0,
                                'list' => $default_list,
                            ],
                            $reference['pcli'] => [
                                'selected' => $reference['adm1'] ?? 0,
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['pcli'] ] ),
                            ]
                        ];
                        break;
                }

            }
            return $preset_array;
        }

        /**
         * Create the initial drop down list for geonames widget
         *
         * @param      $bind_function
         * @param null $geonameid
         * @param null $post_id
         */
        public function drill_down_input( $bind_function, $geonameid = null, $post_id = null  ) {
            $dd_array = $this->default_drill_down( $geonameid, $post_id );

            if ( empty( $dd_array['drill_down_top_level']['list'] ) ) {
                dt_write_log( new WP_Error('dd_list_error', 'Did not find basic list established for drill down.' ) );
            }

            echo '<ul id="drill_down">';

            foreach( $dd_array as $key => $dd_list ) {
                ?>
                    <li>
                        <select id="<?php echo $key ?>" class="geocode-select" onchange="GEOCODING.geoname_drill_down( this.value, '<?php echo esc_attr( $bind_function ) ?>' );jQuery(this).parent().nextAll().remove();">
                            <option value="<?php echo $key ?>"></option>
                            <?php
                                foreach( $dd_list['list'] as $item ) {
                                    echo '<option value="'.$item['geonameid'].'" ';
                                    if ( $item['geonameid'] == $dd_list['selected'] ) {
                                        echo 'selected';
                                    }
                                    echo '>'.$item['name'].'</option>';
                                }
                            ?>
                        </select>
                    </li>
                <?php
            }

            echo '</u>';
        }

        /**
         * Provides a drill down widget
         *
         * @note    Works in connection with drill-down.js
         */
        public function initial_drill_down_input( $div = 'geocode-selected-value', $bind_function = '', $geonameid = null, $post_id = null ) {
            $list = $this->default_map_short_list();
            $default_select_first_level = false;
            if ( count( $list ) < 2 ) {
                $default_select_first_level = true;
            }

            /**
             * Blank widget
             */
            if ( empty( $geonameid ) ) {
                $first_level_geonameid = null;
                ?>

                <ul id="drill_down">
                    <li>
                        <select id="drill_down_top_level" class="geocode-select" onchange="GEOCODING.geoname_drill_down( '<?php echo esc_attr( $div ) ?>', '<?php echo esc_attr( $bind_function ) ?>', this.value, <?php echo esc_attr( $geonameid ) ?>  );jQuery(this).parent().nextAll().remove();">
                            <option value=""></option>
                            <?php
                                if ( $default_select_first_level ) {
                                    foreach( $list as $geonameid => $name ) {
                                        echo '<option value="'.esc_attr( $geonameid ).'" selected>'. esc_html( $name ) .'</option>';
                                        $first_level_geonameid = $geonameid;
                                    }
                                } else {
                                    echo '<option></option>';
                                    foreach( $list as $geonameid => $name ) {
                                        echo '<option value="'.esc_attr( $geonameid ).'">'. esc_html( $name ) .'</option>';
                                    }
                                }
                            ?>
                        </select>
                    </li>
                    <?php
                    if ( $default_select_first_level ) {
                        // auto generate second level
                        $second_level_list = $this->query( 'get_children_by_geonameid', [ 'geonameid' => $first_level_geonameid ] );
                        if ( ! empty( $second_level_list ) ) {
                            ?>
                            <li>
                                <select id="<?php echo esc_attr( $first_level_geonameid );  ?>" class="geocode-select" onchange="GEOCODING.geoname_drill_down( '<?php echo esc_attr( $div ) ?>', '<?php echo esc_attr( $bind_function ) ?>', this.value, <?php echo esc_attr( $geonameid ) ?> );jQuery(this).parent().nextAll().remove();">
                                    <option value="<?php echo esc_attr( $first_level_geonameid );  ?>"></option>
                                    <?php
                                        foreach( $second_level_list as $item ) {
                                            echo '<option value="'.esc_attr( $item['geonameid'] ).'">'. esc_html( $item['name'] ) .'</option>';
                                        }
                                    ?>
                                </select>
                            </li>
                            <?php
                        }
                    }

                    ?>
                </ul>
                <input type="hidden" id="geocode-selected-value" value="" />
                <?php
            }

            // Preloaded widget with geonameid
            else if ( ! empty( $geonameid ) && ! empty( $post_id ) ) {

                $default_top_level = $this->default_map_short_list();
                foreach ( $default_top_level as $key => $value ) {
                    $default_settings['list'][] = [
                        'geonameid' => $key,
                        'name' => $value,
                    ];
                }
                $reference = $this->query( 'get_reference', [ 'post_id' => $post_id ] );


                switch ( $reference['feature_code'] ) {

                    case 'ADM1':
                        $dd = [
                            'drill_down_top_level' => [
                                'selected' => $reference['pcli'],
                                'list' => $default_settings['list'],
                            ],
                            $reference['pcli'] => [
                                'selected' => $reference['adm1'],
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['pcli'] ] ),
                            ],
                            $reference['adm1'] => [
                                'selected' => $reference['adm2'],
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['adm1'] ] ),
                            ],
                        ];
                        break;

                    case 'ADM2':
                        $dd = [
                            'drill_down_top_level' => [
                                'selected' => $reference['pcli'],
                                'list' => $default_settings['list'],
                            ],
                            $reference['pcli'] => [
                                'selected' => $reference['pcli'],
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['pcli'] ] ),
                            ],
                            $reference['adm1'] => [
                                'selected' => $reference['adm2'],
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['adm1'] ] ),
                            ],
                            $reference['adm2'] => [
                                'selected' => $reference['adm3'],
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['adm2'] ] ),
                            ],
                        ];
                        break;

                    case 'PCLI':
                    default:
                        $dd = [
                            'drill_down_top_level' => [
                                'selected' => $reference['pcli'],
                                'list' => $default_settings['list'],
                            ],
                            $reference['pcli'] => [
                                'selected' => $reference['adm1'],
                                'list' => $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['pcli'] ] ),
                            ]
                        ];
                        break;
                }

                ?>

                <ul id="drill_down">
                    <?php
                        if ( $dd ) :
                        foreach ( $dd as $key => $value ) :
                        ?>
                        <li>
                            <select id="<?php echo $key ?>" class="geocode-select" onchange="GEOCODING.geoname_drill_down( '<?php echo esc_attr( $div ) ?>', '<?php echo esc_attr( $bind_function ) ?>', this.value, <?php echo esc_attr( $geonameid ) ?> );jQuery(this).parent().nextAll().remove();">
                                <option value="<?php echo $key ?>"></option>
                                <?php
                                foreach( $value['list'] as $item ) {
                                    echo '<option value="'.esc_attr( $item['geonameid'] ).'"';
                                    if ( $value['selected'] == $item['geonameid'] ) {
                                        echo ' selected';
                                    }
                                    echo '>'. esc_html( $item['name'] ) .'</option>';
                                }
                                ?>
                            </select>
                        </li>
                    <?php endforeach; endif; ?>
                </ul>
                <input type="hidden" id="geocode-selected-value" value="" />
                <?php
            }
        }

        /**
         * Get a list of parent geonameids from a supplied geonameid
         * Currently this is a heavy query because it pulls the entire hierarchy table and loops through it.
         * If possible it is better to use $this->query( 'get_hierarchy_for_geoname') which returns a single row
         * that has related country, state, and county for any given geoname. The result is narrow, but the
         * query is much lighter and faster.
         *
         * @param $geonameid
         *
         * @return array
         */
        public function get_parents( $geonameid ) : array {
            $query = $this->query( 'get_full_hierarchy' );
            $hierarchy_data = $this->_prepare_list( $query );
            $parents = $this->_build_parent_list( $geonameid, $hierarchy_data );
            array_unshift($parents,$geonameid);
            dt_write_log($parents);
            return $parents;
        }
        public function _prepare_list( $query ) : array {

            $menu_data = [];

            foreach ( $query as $menu_item )
            {
                $menu_data[$menu_item['id']] = $menu_item;
            }

            return $menu_data;
        }
        public function _build_parent_list( $geonameid, $hierarchy_data ) : array {
            $list = [];

            if (isset( $hierarchy_data[$geonameid] ) )
            {
                $list[] = $hierarchy_data[$geonameid]['parent_id'];
                $parents = $this->_build_parent_list( $hierarchy_data[$geonameid]['parent_id'], $hierarchy_data );
                if ( ! empty( $parents ) ) {
                    $list = array_merge( $list, $parents );
                }

            }
            return $list;
        }

        public function get_countries_grouped_by_region() : array {
            $regions = $this->query( 'get_regions', ['add_country_info' => true ] );
            $list = [];
            foreach ( $regions as $item ) {
                $list[$item['region_id']]['region_name'] = $item['region_name'];
                $list[$item['region_id']]['countries'][] = $item;
            }
            return $list;
        }

    } DT_Mapping_Module::instance(); // end DT_Mapping_Module class
} // end if class check