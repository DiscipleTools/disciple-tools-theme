<?php

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'DT_Mapping_Module' ) ) {

    /**
     * Set Global Database Variables
     */
    global $wpdb;
    $wpdb->dt_geonames = $wpdb->prefix .'dt_geonames';
    $wpdb->dt_geonames_counter = $wpdb->prefix . 'dt_geonames_counter';

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
            require_once( 'add-users-column.php' );
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
                $url  = ( !isset( $_SERVER["HTTPS"] ) || @( $_SERVER["HTTPS"] != 'on' ) )
                    ? 'http://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) )
                    : 'https://'. sanitize_text_field( wp_unslash( $_SERVER["SERVER_NAME"] ) );
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

            // top map list
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

            // set custom columns
            $data['custom_column_labels'] = [];
            $data['custom_column_data'] = [];

            // initialize drill down configuration
            if ( is_singular( "groups" ) || is_singular( "contacts" ) ) {
                global $wp_query;
                if ( isset( $wp_query->queried_object_id ) ) {
                    $data['default_drill_down'] = $this->default_drill_down( get_post_meta( $wp_query->queried_object_id, 'geonameid', true ), $wp_query->queried_object_id );
                }
            }
            else if ( 'settings' === dt_get_url_path() ) {
                $data['default_drill_down'] = $this->default_drill_down();
            }
            else {
                $data['default_drill_down'] = $this->default_drill_down();
            }


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
            $endpoints['modify_location_endpoint'] = [
                   'namespace' => $this->namespace,
                   'route' => '/mapping_module/modify_location',
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
        public function modify_location_endpoint( WP_REST_Request $request ) {
            if ( ! user_can(get_current_user_id(), 'manage_dt') ) {
                return new WP_Error( 'permissions', 'No permissions for the action.', [ 'status' => 401 ]  );
            }

            $params = $request->get_params();

            if ( isset( $params['key'] ) && isset( $params['geonameid'] ) ) {
                global $wpdb;

                switch( $params['key'] ) {
                    case 'name':
                        if ( isset( $params['reset'] ) && $params['reset'] === true ) {
                            // delete geonameid for the key
                            $result = $wpdb->delete(
                                $wpdb->dt_geonames_meta,
                                [
                                    'geonameid' => $params['geonameid'],
                                    'meta_key' => 'name'
                                ],
                                [
                                    '%d',
                                    '%s'
                                ]
                            );

                            // get the original name for the geonameid
                            $name = $wpdb->get_var( $wpdb->prepare( "
                                SELECT name
                                FROM $wpdb->dt_geonames
                                WHERE geonameid = %d
                            ", $params['geonameid'] ) );

                            return [
                                'status' => 'OK',
                                'value' => $name
                            ];
                        } else if ( isset( $params['value'] ) ) {
                            $insert_id = $wpdb->insert(
                                    $wpdb->dt_geonames_meta,
                                    [
                                        'geonameid' => $params['geonameid'],
                                        'meta_key' => 'name',
                                        'meta_value' => $params['value'],
                                    ],
                                    [
                                        '%d',
                                        '%s',
                                        '%s'
                                    ]
                            );
                            if ( $insert_id ) {
                                return true;
                            } else {
                                return new WP_Error('insert_fail', 'Failed to insert record' );
                            }
                        }
                        break;
                    case 'population':
                        dt_write_log($params);
                        if ( isset( $params['reset'] ) && $params['reset'] === true ) {
                            // delete geonameid for the key
                            $result = $wpdb->delete(
                                $wpdb->dt_geonames_meta,
                                [
                                    'geonameid' => $params['geonameid'],
                                    'meta_key' => 'population'
                                ],
                                [
                                    '%d',
                                    '%s'
                                ]
                            );

                            // get the original name for the geonameid
                            $population = $wpdb->get_var( $wpdb->prepare( "
                                SELECT population
                                FROM $wpdb->dt_geonames
                                WHERE geonameid = %d
                            ", $params['geonameid'] ) );

                            return [
                                'status' => 'OK',
                                'value' => $population
                            ];
                        } else if ( isset( $params['value'] ) ) {
                            $insert_id = $wpdb->insert(
                                $wpdb->dt_geonames_meta,
                                [
                                    'geonameid'  => $params[ 'geonameid' ],
                                    'meta_key'   => 'population',
                                    'meta_value' => $params[ 'value' ],
                                ],
                                [
                                    '%d',
                                    '%s',
                                    '%s'
                                ]
                            );
                            if ( $insert_id ) {
                                return true;
                            } else {
                                return new WP_Error( 'insert_fail', 'Failed to insert record' );
                            }
                        }
                        break;
                    default:
                        return new WP_Error( __METHOD__, 'Missing parameters.', [ 'status' => 400 ] );
                        break;
                }

            }
            return new WP_Error( __METHOD__, 'Missing parameters.', [ 'status' => 400 ] );
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
                $results['parent']['id'] = (int) $parent['geonameid'];
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
            $children = $this->query( 'get_countries' );

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
                              g.geonameid as id, 
                              g.geonameid, 
                              g.alt_name as name, 
                              IFNULL(g.alt_population, g.population) as population, 
                              g.latitude, 
                              g.longitude,
                              g.country_code,
                              g.level
                            FROM $wpdb->dt_geonames as g
                            WHERE g.geonameid = %s
                        ", $args[ 'geonameid' ] ), ARRAY_A );
                    }
                    break;

                case 'get_parent_by_geonameid':
                    if ( isset( $args['geonameid'] ) ) {
                        $results = $wpdb->get_row( $wpdb->prepare( "
                            SELECT 
                              p.geonameid as id, 
                              p.geonameid, 
                              p.alt_name as name, 
                              IFNULL(p.alt_population, p.population) as population, 
                              p.latitude, 
                              p.longitude,
                              p.country_code,
                              p.level
                            FROM $wpdb->dt_geonames as g
                            JOIN $wpdb->dt_geonames as p ON g.parent_id=p.geonameid
                            WHERE g.geonameid = %s
                        ", $args[ 'geonameid' ] ), ARRAY_A );
                    }
                    break;

                case 'get_children_by_geonameid':
                    if ( isset( $args['geonameid'] ) ) {
                        $results = $wpdb->get_results( $wpdb->prepare( "
                            SELECT
                              g.geonameid as id, 
                              g.geonameid, 
                              g.alt_name as name, 
                              IFNULL(g.alt_population, g.population) as population, 
                              g.latitude, 
                              g.longitude,
                              g.country_code,
                              g.level
                            FROM $wpdb->dt_geonames as g
                            WHERE g.parent_id = %d
                            ORDER BY g.alt_name ASC
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
                        // Note: $wpdb->prepare does not have a way to add a string without surrounding it with ''
                        // and this query requires a list of numbers separated by commas but without surrounding ''
                        // Any better ideas on how to still use ->prepare and not break the sql, welcome. :)
                        // @codingStandardsIgnoreStart
                        $results = $wpdb->get_results("
                            SELECT
                              g.geonameid as id, 
                              g.geonameid, 
                              g.alt_name as name, 
                              IFNULL(g.alt_population, g.population) as population, 
                              g.latitude, 
                              g.longitude,
                              g.country_code,
                              g.feature_code,
                              g.level
                            FROM $wpdb->dt_geonames as g
                            WHERE g.geonameid IN ($prepared_list)
                            ORDER BY g.alt_name ASC
                        ", ARRAY_A );
                        // @codingStandardsIgnoreEnd
                    }
                    break;

                case 'get_countries':
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
                            g.geonameid,
                            g.alt_name as name,
                            g.latitude,
                            g.longitude,
                            g.feature_class,
                            g.feature_code,
                            g.country_code,
                            g.cc2,
                            g.admin1_code,
                            g.admin2_code,
                            g.admin3_code,
                            g.admin4_code,
                            IFNULL(g.alt_population, g.population) as population,
                            g.timezone,
                            g.modification_date,
                            g.parent_id,
                            g.country_geonameid,
                            g.admin1_geonameid,
                            g.admin2_geonameid,
                            g.admin3_geonameid,
                            g.level
                        FROM $wpdb->dt_geonames as g
                     WHERE g.level = 'country'
                     ORDER BY name ASC
                    ", ARRAY_A );

                    if ( empty( $results ) ) {
                        $results = [];
                    }

                    break;

                case 'get_country_code_by_id':
                    if ( isset( $args['id'] ) ) {
                        $results = $wpdb->get_var( $wpdb->prepare( "
                            SELECT country_code 
                            FROM $wpdb->dt_geonames 
                            WHERE geonameid = %s;
                        ", $args['id'] ) );
                    }
                    if ( ! isset( $args['id'] ) ) {
                        $results = 0;
                    }

                    break;

                case 'get_hierarchy':
                    if ( isset( $args['geonameid'] ) ) {
                        $results = $wpdb->get_row( $wpdb->prepare( "
                            SELECT
                            g.parent_id,
                            g.geonameid,
                            g.country_geonameid,
                            g.admin1_geonameid,
                            g.admin2_geonameid,
                            g.admin3_geonameid,
                            g.level
                            FROM $wpdb->dt_geonames as g
                            WHERE g.geonameid = %d;
                        ", $args['geonameid'] ), ARRAY_A );
                    } else {
                        $results = $wpdb->get_results("
                          SELECT 
                            g.parent_id,
                            g.geonameid,
                            g.country_geonameid,
                            g.admin1_geonameid,
                            g.admin2_geonameid,
                            g.admin3_geonameid,
                            g.level
                          FROM $wpdb->dt_geonames as g", ARRAY_A );
                    }

                    break;

                case 'get_counter':
                    if ( isset( $args['post_id'] ) ) {
                        $results = $wpdb->get_row( $wpdb->prepare( "
                            SELECT * 
                            FROM $wpdb->dt_geonames_counter 
                            WHERE post_id = %s;
                        ", $args['post_id'] ), ARRAY_A );
                    }
                    else if ( isset( $args['geonameid'] ) ) {
                        $results = $wpdb->get_row( $wpdb->prepare( "
                            SELECT * 
                            FROM $wpdb->dt_geonames_counter 
                            WHERE geonameid = %d;
                        ", $args['geonameid'] ), ARRAY_A );
                    }
                    break;


                case 'get_regions':
                    /**
                     * Lists all countries with their region_name and region_id
                     * @note There are often two regions that claim the same country.
                     */
                    $results = $wpdb->get_results("
                            SELECT
                                g.geonameid,
                                g.alt_name as name,
                                g.latitude,
                                g.longitude,
                                g.feature_class,
                                g.feature_code,
                                g.country_code,
                                g.cc2,
                                g.admin1_code,
                                g.admin2_code,
                                g.admin3_code,
                                g.admin4_code,
                                IFNULL(g.alt_population, g.population) as population,
                                g.timezone,
                                g.modification_date,
                                g.parent_id,
                                g.country_geonameid,
                                g.admin1_geonameid,
                                g.admin2_geonameid,
                                g.admin3_geonameid,
                                g.level
                            FROM $wpdb->dt_geonames as g
                            WHERE feature_code = 'RGN' 
                            AND country_code = '';
                        ", ARRAY_A );

                    break;

                case 'get_continents':
                    $results = $wpdb->get_results("
                            SELECT
                                g.geonameid,
                                g.alt_name as name,
                                g.latitude,
                                g.longitude,
                                g.feature_class,
                                g.feature_code,
                                g.country_code,
                                g.cc2,
                                g.admin1_code,
                                g.admin2_code,
                                g.admin3_code,
                                g.admin4_code,
                                IFNULL(g.alt_population, g.population) as population,
                                g.timezone,
                                g.modification_date,
                                g.parent_id,
                                g.country_geonameid,
                                g.admin1_geonameid,
                                g.admin2_geonameid,
                                g.admin3_geonameid,
                                g.level
                            FROM $wpdb->dt_geonames as g
                            WHERE g.geonameid IN (6255146,6255147,6255148,6255149,6255151,6255150,6255152)
                            ORDER BY name ASC;
                        ", ARRAY_A );

                    break;

                case 'get_earth':
                    $results = $wpdb->get_results("
                            SELECT
                                g.geonameid,
                                g.alt_name as name,
                                g.latitude,
                                g.longitude,
                                g.feature_class,
                                g.feature_code,
                                g.country_code,
                                g.cc2,
                                g.admin1_code,
                                g.admin2_code,
                                g.admin3_code,
                                g.admin4_code,
                                IFNULL(g.alt_population, g.population) as population,
                                g.timezone,
                                g.modification_date,
                                g.parent_id,
                                g.country_geonameid,
                                g.admin1_geonameid,
                                g.admin2_geonameid,
                                g.admin3_geonameid,
                                g.level
                            FROM $wpdb->dt_geonames as g
                            WHERE g.geonameid = 6295630
                        ", ARRAY_A );

                    break;

                case 'get_geoname_totals':
                    $results = $wpdb->get_results("
                            SELECT
                              country_geonameid as geonameid,
                              level,
                              type,
                              count(country_geonameid) as count
                            FROM $wpdb->dt_geonames_counter
                            WHERE country_geonameid != ''
                            GROUP BY country_geonameid, type
                            UNION
                            SELECT
                              admin1_geonameid as geonameid,
                              level,
                              type,
                              count(admin1_geonameid) as count
                            FROM $wpdb->dt_geonames_counter
                            WHERE admin1_geonameid != ''
                            GROUP BY admin1_geonameid, type
                            UNION
                            SELECT
                              admin2_geonameid as geonameid,
                              level,
                              type,
                              count(admin2_geonameid) as count
                            FROM $wpdb->dt_geonames_counter
                            WHERE admin2_geonameid != ''
                            GROUP BY admin2_geonameid, type
                        ", ARRAY_A );

                    break;

                case 'typeahead':
                    if ( isset( $args['s'] ) ) {
                        $results = $wpdb->get_results( $wpdb->prepare( "
                            SELECT 
                            g.geonameid,
                            CASE 
                                WHEN g.level = 'country' 
                                THEN g.alt_name
                                WHEN g.level = 'admin1' 
                                THEN CONCAT( (SELECT country.alt_name FROM $wpdb->dt_geonames as country WHERE country.geonameid = g.country_geonameid LIMIT 1), ' > ', 
                                g.alt_name ) 
                                WHEN g.level = 'admin2' OR g.level = 'admin3'
                                THEN CONCAT( (SELECT name FROM $wpdb->dt_geonames as country WHERE geonameid = g.country_geonameid LIMIT 1), ' > ', 
                                (SELECT a1.alt_name FROM $wpdb->dt_geonames AS a1 WHERE a1.geonameid = g.admin1_geonameid LIMIT 1), ' > ', 
                                g.alt_name )
                                ELSE g.alt_name
                            END as name
                            FROM $wpdb->dt_geonames as g
                            WHERE g.alt_name LIKE %s
                            LIMIT 30;
                            ",
                            '%' . $wpdb->esc_like( $args['s'] ) . '%' ),
                            ARRAY_A );
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
            // build list array
            $response = [];
            $response['list'] = $wpdb->get_results( $wpdb->prepare( "
              SELECT parent_id, geonameid as id, alt_name as name 
              FROM $wpdb->dt_geonames 
              WHERE parent_id = %d 
              ORDER BY name ASC", $start_geonameid ), ARRAY_A );

            // build full results
            $query = $wpdb->get_results("SELECT parent_id, geonameid as id, alt_name as name FROM $wpdb->dt_geonames", ARRAY_A );
            if ( empty( $query ) ) {
                return $this->_no_results();
            }
            $menu_data = $this->prepare_menu_array( $query );
            $response['html'] = $this->build_locations_html_list( $start_geonameid, $menu_data, 0, 3 );

            return $response;
        }
        public function build_locations_html_list( $parent_id, $menu_data, $gen, $depth_limit ) {
            $list = '';

            if ( isset( $menu_data['parents'][$parent_id] ) && $gen < $depth_limit ) {
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

            $default_level = $this->default_map_settings();
            $list = $this->default_map_short_list();
            $default_list = [];
            $preset_array = [];

            $default_select_first_level = false;
            if ( count( $list ) < 2 ) {
                $default_select_first_level = true;
            }

            switch ( $default_level['type'] ) {
                case 'country':

                    if ( $geonameid ) {

                        foreach ( $list as $key => $value ) {
                            $default_list[] = [
                                'geonameid' => (int) $key,
                                'name' => $value,
                            ];
                        }

                        if ( $post_id ) {
                            $reference = $this->query( 'get_counter', [ 'post_id' => $post_id ] );
                        } else {
                            $reference = $this->query( 'get_hierarchy', [ 'geonameid' => $geonameid ] );
                        }

                        switch ( $reference['level'] ) {

                            case 'admin1':
                                $preset_array = [
                                    0 => [
                                        'parent' => 'drill_down_top_level',
                                        'selected' => (int) $reference['country_geonameid'] ?? 0,
                                        'list' => $default_list,
                                    ],
                                    1 => [
                                        'parent' => (int) $reference['country_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['country_geonameid'] ] ) ),
                                    ],
                                    2 => [
                                        'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['admin1_geonameid'] ] ) ),
                                    ],
                                ];
                                break;

                            case 'admin2':
                                $preset_array = [
                                    0 => [
                                        'parent' => 'drill_down_top_level',
                                        'selected' => (int) $reference['country_geonameid'] ?? 0,
                                        'list' => $default_list,
                                    ],
                                    1 => [
                                        'parent' => (int) $reference['country_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['country_geonameid'] ] ) ),
                                    ],
                                    2 => [
                                        'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['admin1_geonameid'] ] ) ),
                                    ],
                                    3 => [
                                        'parent' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'selected' => (int) $reference['adm3'] ?? 0,
                                        'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['admin2_geonameid'] ] ) ),
                                    ],
                                ];
                                break;

                            case 'country':
                            default:
                                $preset_array = [
                                    0 => [
                                        'parent' => 'drill_down_top_level',
                                        'selected' => (int) $reference['country_geonameid'] ?? 0,
                                        'list' => $default_list,
                                    ],
                                    1 => [
                                        'parent' => (int) $reference['country_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['country_geonameid'] ] ) ),
                                    ]
                                ];
                                break;
                        }

                    } else {
                        // if default list is just one country, then prepopulate second level
                        if ( $default_select_first_level ) {
                            foreach( $list as $geonameid => $name ) {
                                $preset_array[0]['list'][] = [
                                    'geonameid' => (int) $geonameid,
                                    'name' => $name
                                ];
                                $preset_array[0]['parent'] = 'drill_down_top_level';
                                $preset_array[0]['selected'] = (int) $geonameid;
                            }
                            $second_level_list = $this->format_geoname_types( $this->query( 'get_children_by_geonameid', [ 'geonameid' => $preset_array[0]['selected']  ] ) );
                            if ( ! empty( $second_level_list ) ) {
                                foreach( $second_level_list as $item ) {
                                    $preset_array[1]['list'][] = [
                                        'geonameid' => (int) $item['geonameid'],
                                        'name' => $item['name'],
                                    ];
                                }
                                $preset_array[1]['parent'] = (int) $geonameid;
                                $preset_array[1]['selected'] = 0;
                            }

                            // top level list has more than one option
                        } else {
                            foreach( $list as $geonameid => $name ) {
                                $preset_array[0]['list'][] = [
                                    'geonameid' => (int) $geonameid,
                                    'name' => $name
                                ];
                            }
                            $preset_array[0]['parent'] = 'drill_down_top_level';
                            $preset_array[0]['selected'] = 0;
                        }

                    }

                    return $preset_array;
                    break;

                case 'state':
                    if ( $geonameid ) {

                        foreach ( $list as $key => $value ) {
                            $default_list[] = [
                                'geonameid' => (int) $key,
                                'name' => $value,
                            ];
                        }

                        if ( $post_id ) {
                            $reference = $this->query( 'get_counter', [ 'post_id' => $post_id ] );
                        } else {
                            $reference = $this->query( 'get_counter', [ 'geonameid' => $geonameid ] );
                        }

                        switch ( $reference['level'] ) {

                            case 'admin2':
                                $preset_array = [
                                    0 => [
                                        'parent' => 'drill_down_top_level',
                                        'selected' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['country_geonameid'] ] ) ),
                                    ],
                                    1 => [
                                        'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['admin1_geonameid'] ] ) ),
                                    ],
                                    2 => [
                                        'parent' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'selected' => (int) $reference['adm3'] ?? 0,
                                        'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['admin2_geonameid'] ] ) ),
                                    ],
                                ];
                                break;

                            case 'country':
                            case 'admin1':
                            default:
                                $preset_array = [
                                    0 => [
                                        'parent' => 'drill_down_top_level',
                                        'selected' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['country_geonameid'] ] ) ),
                                    ],
                                    1 => [
                                        'parent' => (int) $reference['admin1_geonameid'] ?? 0,
                                        'selected' => (int) $reference['admin2_geonameid'] ?? 0,
                                        'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['admin1_geonameid'] ] ) ),
                                    ],
                                ];
                                break;
                        }

                    } else {
                        // if default list is just one country, then prepopulate second level
                        if ( $default_select_first_level ) {
                            foreach( $list as $geonameid => $name ) {
                                $preset_array[0]['list'][] = [
                                    'geonameid' => (int) $geonameid,
                                    'name' => $name
                                ];
                                $preset_array[0]['parent'] = 'drill_down_top_level';
                                $preset_array[0]['selected'] = (int) $geonameid;
                            }
                            $second_level_list = $this->format_geoname_types( $this->query( 'get_children_by_geonameid', [ 'geonameid' => $preset_array[0]['selected']  ] ) );
                            if ( ! empty( $second_level_list ) ) {
                                foreach( $second_level_list as $item ) {
                                    $preset_array[1]['list'][] = [
                                        'geonameid' => (int) $item['geonameid'],
                                        'name' => $item['name'],
                                    ];
                                }
                                $preset_array[1]['parent'] = (int) $geonameid;
                                $preset_array[1]['selected'] = 0;
                            }

                            // top level list has more than one option
                        } else {
                            foreach( $list as $geonameid => $name ) {
                                $preset_array[0]['list'][] = [
                                    'geonameid' => (int) $geonameid,
                                    'name' => $name
                                ];
                            }
                            $preset_array[0]['parent'] = 'drill_down_top_level';
                            $preset_array[0]['selected'] = 0;
                        }

                    }

                    return $preset_array;
                    break;

                case 'world':
                default:
                    $default_list = $this->query( 'get_countries' );

                    if ( $geonameid ) {
                        if ( empty( $post_id ) ) {
                            $reference = $this->query( 'get_counter', [ 'geonameid' => $geonameid ] );
                        } else {
                            $reference = $this->query( 'get_counter', [ 'post_id' => $post_id ] );
                        }
                        $preset_array = [
                            0 => [
                                'parent' => 'drill_down_top_level',
                                'selected' => (int) $reference['country_geonameid'] ?? 0,
                                'list' => $default_list,
                            ],
                            1 => [
                                'parent' => (int) $reference['country_geonameid'] ?? 0,
                                'selected' => (int) $reference['admin1_geonameid'] ?? 0,
                                'list' => $this->format_geoname_types( $this->query('get_children_by_geonameid', [ 'geonameid' => $reference['country_geonameid'] ] ) ),
                            ]
                        ];
                    } else {
                        foreach( $default_list as $country ) {
                            $preset_array[0]['list'][] = [
                                'geonameid' => (int) $country['geonameid'],
                                'name' => $country['name']
                            ];
                            $preset_array[0]['parent'] = 'drill_down_top_level';
                            $preset_array[0]['selected'] = 0;
                        }
                    }

                    return $preset_array;
                    break;
            }
        }

        public function format_geoname_types( $query ) {
            if ( ! empty( $query ) || ! is_array( $query ) ) {
                foreach ( $query as $index => $value ) {
                    if ( isset( $value['geonameid'] ) ) {
                        $query[$index]['geonameid'] = (int) $value['geonameid'];
                    }
                    if ( isset( $value['population'] ) ) {
                        $query[$index]['population'] = (int) $value['population'];
                    }
                    if ( isset( $value['latitude'] ) ) {
                        $query[$index]['latitude'] = (float) $value['latitude'];
                    }
                    if ( isset( $value['longitude'] ) ) {
                        $query[$index]['longitude'] = (float) $value['longitude'];
                    }
                }
            }
            return $query;
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

            if ( empty( $dd_array[0]['list'] ) ) {
                dt_write_log( new WP_Error('dd_list_error', 'Did not find basic list established for drill down.' ) );
            }

            echo '<ul id="drill_down">';

            foreach( $dd_array as $dd_list ) {
                if ( ! empty( $dd_list['list'] ) ) :
                ?>
                    <li>
                        <select id="<?php echo $dd_list['parent'] ?>" class="geocode-select"
                                onchange="DRILLDOWN.geoname_drill_down( this.value, '<?php echo esc_attr( $bind_function ) ?>' );jQuery(this).parent().nextAll().remove();">
                            <option value="<?php echo $dd_list['parent'] ?>"></option>
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
                endif;

            }

            echo '</u>';
        }

        public function get_post_locations( $post_id ) {
            $list = [];
            $geoname_list = get_post_meta( $post_id, 'geonameid' );
            if ( ! empty( $geoname_list  ) ) {
                $list = $this->query( 'get_by_geonameid_list', [ 'list' => $geoname_list ] );
            }
            return $list;
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
            $query = $this->query( 'get_hierarchy' );
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
                $menu_data[$menu_item['geonameid']] = $menu_item;
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

        public function get_countries_grouped_by_region( $regions = NULL ) : array {
            $regions = $this->query( 'get_regions', ['add_country_info' => true ] );
            $countries = $this->query( 'get_countries' );
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

        public function get_full_country_name( $country_code ) {

            switch ( $country_code ) {

                case "AF": $name = "Afghanistan"; break;
                case "AX": $name = "Ahvenanmaan Laeaeni"; break;
                case "AL": $name = "Albania"; break;
                case "DZ": $name = "Algeria"; break;
                case "AS": $name = "American Samoa"; break;
                case "AD": $name = "Andorra"; break;
                case "AO": $name = "Angola"; break;
                case "AI": $name = "Anguilla"; break;
                case "AG": $name = "Antigua And Barbuda"; break;
                case "AR": $name = "Argentina"; break;
                case "AM": $name = "Armenia"; break;
                case "AW": $name = "Aruba"; break;
                case "AU": $name = "Australia"; break;
                case "AT": $name = "Austria"; break;
                case "AZ": $name = "Azerbaijan"; break;
                case "BS": $name = "Bahamas, The"; break;
                case "BH": $name = "Bahrain"; break;
                case "BD": $name = "Bangladesh"; break;
                case "BB": $name = "Barbados"; break;
                case "BY": $name = "Belarus"; break;
                case "BE": $name = "Belgium"; break;
                case "BZ": $name = "Belize"; break;
                case "BJ": $name = "Benin"; break;
                case "BM": $name = "Bermuda"; break;
                case "BT": $name = "Bhutan"; break;
                case "BO": $name = "Bolivia"; break;
                case "BQ": $name = "Bonaire, Saint Eustatius and Saba"; break;
                case "BA": $name = "Bosnia And Herzegovina"; break;
                case "BW": $name = "Botswana"; break;
                case "BV": $name = "Bouvet Island"; break;
                case "BR": $name = "Brazil"; break;
                case "IO": $name = "British Indian Ocean Territory"; break;
                case "VG": $name = "British Virgin Islands"; break;
                case "BN": $name = "Brunei"; break;
                case "BG": $name = "Bulgaria"; break;
                case "BF": $name = "Burkina Faso"; break;
                case "MM": $name = "Burma"; break;
                case "BI": $name = "Burundi"; break;
                case "CV": $name = "Cabo Verde"; break;
                case "KH": $name = "Cambodia"; break;
                case "CM": $name = "Cameroon"; break;
                case "CA": $name = "Canada"; break;
                case "KY": $name = "Cayman Islands"; break;
                case "CF": $name = "Central African Republic"; break;
                case "TD": $name = "Chad"; break;
                case "CL": $name = "Chile"; break;
                case "CN": $name = "China"; break;
                case "CX": $name = "Christmas Island"; break;
                case "CO": $name = "Colombia"; break;
                case "KM": $name = "Comoros"; break;
                case "CG": $name = "Congo (Brazzaville)"; break;
                case "CD": $name = "Congo (Kinshasa)"; break;
                case "CK": $name = "Cook Islands"; break;
                case "CR": $name = "Costa Rica"; break;
                case "CI": $name = "Côte D’Ivoire"; break;
                case "HR": $name = "Croatia"; break;
                case "CU": $name = "Cuba"; break;
                case "CW": $name = "Curaçao"; break;
                case "CY": $name = "Cyprus"; break;
                case "CZ": $name = "Czechia"; break;
                case "DK": $name = "Denmark"; break;
                case "DJ": $name = "Djibouti"; break;
                case "DM": $name = "Dominica"; break;
                case "DO": $name = "Dominican Republic"; break;
                case "EC": $name = "Ecuador"; break;
                case "EG": $name = "Egypt"; break;
                case "SV": $name = "El Salvador"; break;
                case "GQ": $name = "Equatorial Guinea"; break;
                case "ER": $name = "Eritrea"; break;
                case "EE": $name = "Estonia"; break;
                case "ET": $name = "Ethiopia"; break;
                case "FK": $name = "Falkland Islands (Islas Malvinas)"; break;
                case "FO": $name = "Faroe Islands"; break;
                case "FJ": $name = "Fiji"; break;
                case "FI": $name = "Finland"; break;
                case "FR": $name = "France"; break;
                case "GF": $name = "French Guiana"; break;
                case "PF": $name = "French Polynesia"; break;
                case "GA": $name = "Gabon"; break;
                case "GM": $name = "Gambia, The"; break;
                case "GE": $name = "Georgia"; break;
                case "DE": $name = "Germany"; break;
                case "GH": $name = "Ghana"; break;
                case "GI": $name = "Gibraltar"; break;
                case "GR": $name = "Greece"; break;
                case "GL": $name = "Greenland"; break;
                case "GD": $name = "Grenada"; break;
                case "GP": $name = "Guadeloupe"; break;
                case "GU": $name = "Guam"; break;
                case "GT": $name = "Guatemala"; break;
                case "GG": $name = "Guernsey"; break;
                case "GN": $name = "Guinea"; break;
                case "GW": $name = "Guinea-Bissau"; break;
                case "GY": $name = "Guyana"; break;
                case "HT": $name = "Haiti"; break;
                case "HN": $name = "Honduras"; break;
                case "HK": $name = "Hong Kong"; break;
                case "HU": $name = "Hungary"; break;
                case "IS": $name = "Iceland"; break;
                case "IN": $name = "India"; break;
                case "ID": $name = "Indonesia"; break;
                case "IR": $name = "Iran"; break;
                case "IQ": $name = "Iraq"; break;
                case "IE": $name = "Ireland"; break;
                case "IM": $name = "Isle Of Man"; break;
                case "IL": $name = "Israel"; break;
                case "IT": $name = "Italy"; break;
                case "JM": $name = "Jamaica"; break;
                case "JP": $name = "Japan"; break;
                case "JE": $name = "Jersey"; break;
                case "JO": $name = "Jordan"; break;
                case "KZ": $name = "Kazakhstan"; break;
                case "KE": $name = "Kenya"; break;
                case "KI": $name = "Kiribati"; break;
                case "KP": $name = "Korea, North"; break;
                case "KR": $name = "Korea, South"; break;
                case "XK": $name = "Kosovo"; break;
                case "KW": $name = "Kuwait"; break;
                case "KG": $name = "Kyrgyzstan"; break;
                case "LA": $name = "Laos"; break;
                case "LV": $name = "Latvia"; break;
                case "LB": $name = "Lebanon"; break;
                case "LS": $name = "Lesotho"; break;
                case "LR": $name = "Liberia"; break;
                case "LY": $name = "Libya"; break;
                case "LI": $name = "Liechtenstein"; break;
                case "LT": $name = "Lithuania"; break;
                case "LU": $name = "Luxembourg"; break;
                case "MO": $name = "Macau"; break;
                case "MK": $name = "Macedonia"; break;
                case "MG": $name = "Madagascar"; break;
                case "MW": $name = "Malawi"; break;
                case "MY": $name = "Malaysia"; break;
                case "MV": $name = "Maldives"; break;
                case "ML": $name = "Mali"; break;
                case "MT": $name = "Malta"; break;
                case "MH": $name = "Marshall Islands"; break;
                case "MQ": $name = "Martinique"; break;
                case "MR": $name = "Mauritania"; break;
                case "MU": $name = "Mauritius"; break;
                case "YT": $name = "Mayotte"; break;
                case "MX": $name = "Mexico"; break;
                case "FM": $name = "Micronesia, Federated States Of"; break;
                case "MD": $name = "Moldova"; break;
                case "MC": $name = "Monaco"; break;
                case "MN": $name = "Mongolia"; break;
                case "ME": $name = "Montenegro"; break;
                case "MS": $name = "Montserrat"; break;
                case "MA": $name = "Morocco"; break;
                case "MZ": $name = "Mozambique"; break;
                case "NA": $name = "Namibia"; break;
                case "NR": $name = "Nauru"; break;
                case "NP": $name = "Nepal"; break;
                case "NL": $name = "Netherlands"; break;
                case "AN": $name = "Netherlands Antilles"; break;
                case "NC": $name = "New Caledonia"; break;
                case "NZ": $name = "New Zealand"; break;
                case "NI": $name = "Nicaragua"; break;
                case "NE": $name = "Niger"; break;
                case "NG": $name = "Nigeria"; break;
                case "NU": $name = "Niue"; break;
                case "NF": $name = "Norfolk Island"; break;
                case "MP": $name = "Northern Mariana Islands"; break;
                case "NO": $name = "Norway"; break;
                case "OM": $name = "Oman"; break;
                case "PK": $name = "Pakistan"; break;
                case "PW": $name = "Palau"; break;
                case "PS": $name = "Palestine"; break;
                case "PA": $name = "Panama"; break;
                case "PG": $name = "Papua New Guinea"; break;
                case "PY": $name = "Paraguay"; break;
                case "PE": $name = "Peru"; break;
                case "PH": $name = "Philippines"; break;
                case "PN": $name = "Pitcairn, Henderson, Ducie and Oeno Islands"; break;
                case "PL": $name = "Poland"; break;
                case "PT": $name = "Portugal"; break;
                case "PR": $name = "Puerto Rico"; break;
                case "QA": $name = "Qatar"; break;
                case "RE": $name = "Reunion"; break;
                case "RO": $name = "Romania"; break;
                case "RU": $name = "Russia"; break;
                case "RW": $name = "Rwanda"; break;
                case "BL": $name = "Saint Barthelemy"; break;
                case "SH": $name = "Saint Helena, Ascension, And Tristan Da Cunha"; break;
                case "KN": $name = "Saint Kitts And Nevis"; break;
                case "LC": $name = "Saint Lucia"; break;
                case "MF": $name = "Saint Martin"; break;
                case "PM": $name = "Saint Pierre And Miquelon"; break;
                case "VC": $name = "Saint Vincent And The Grenadines"; break;
                case "WS": $name = "Samoa"; break;
                case "SM": $name = "San Marino"; break;
                case "ST": $name = "Sao Tome And Principe"; break;
                case "SA": $name = "Saudi Arabia"; break;
                case "SN": $name = "Senegal"; break;
                case "RS": $name = "Serbia"; break;
                case "CS": $name = "Serbia and Montenegro"; break;
                case "SC": $name = "Seychelles"; break;
                case "SL": $name = "Sierra Leone"; break;
                case "SG": $name = "Singapore"; break;
                case "SX": $name = "Sint Maarten"; break;
                case "SK": $name = "Slovakia"; break;
                case "SI": $name = "Slovenia"; break;
                case "SB": $name = "Solomon Islands"; break;
                case "SO": $name = "Somalia"; break;
                case "ZA": $name = "South Africa"; break;
                case "GS": $name = "South Georgia And South Sandwich Islands"; break;
                case "SS": $name = "South Sudan"; break;
                case "ES": $name = "Spain"; break;
                case "LK": $name = "Sri Lanka"; break;
                case "VA": $name = "State of the Vatican City"; break;
                case "SD": $name = "Sudan"; break;
                case "SR": $name = "Suriname"; break;
                case "SJ": $name = "Svalbard and Jan Mayen"; break;
                case "SZ": $name = "Swaziland"; break;
                case "SE": $name = "Sweden"; break;
                case "CH": $name = "Switzerland"; break;
                case "SY": $name = "Syria"; break;
                case "TW": $name = "Taiwan"; break;
                case "TJ": $name = "Tajikistan"; break;
                case "TZ": $name = "Tanzania"; break;
                case "CC": $name = "Territory of Cocos (Keeling) Islands"; break;
                case "HM": $name = "Territory of Heard Island and McDonald Islands"; break;
                case "TF": $name = "Territory of the French Southern and Antarctic Lands"; break;
                case "TH": $name = "Thailand"; break;
                case "TL": $name = "Timor-Leste"; break;
                case "TG": $name = "Togo"; break;
                case "TK": $name = "Tokelau"; break;
                case "TO": $name = "Tonga"; break;
                case "TT": $name = "Trinidad And Tobago"; break;
                case "TN": $name = "Tunisia"; break;
                case "TR": $name = "Turkey"; break;
                case "TM": $name = "Turkmenistan"; break;
                case "TC": $name = "Turks And Caicos Islands"; break;
                case "TV": $name = "Tuvalu"; break;
                case "VI": $name = "U.S. Virgin Islands"; break;
                case "UG": $name = "Uganda"; break;
                case "UA": $name = "Ukraine"; break;
                case "AE": $name = "United Arab Emirates"; break;
                case "GB": $name = "United Kingdom"; break;
                case "UM": $name = "United States Minor Outlying Islands"; break;
                case "US": $name = "United States of America"; break;
                case "UY": $name = "Uruguay"; break;
                case "UZ": $name = "Uzbekistan"; break;
                case "VU": $name = "Vanuatu"; break;
                case "VE": $name = "Venezuela"; break;
                case "VN": $name = "Vietnam"; break;
                case "WF": $name = "Wallis And Futuna"; break;
                case "EH": $name = "Western Sahara"; break;
                case "YE": $name = "Yemen"; break;
                case "ZM": $name = "Zambia"; break;
                case "ZW": $name = "Zimbabwe"; break;
                default: $name = false;

            }
            return $name;

        }

    } DT_Mapping_Module::instance(); // end DT_Mapping_Module class
} // end if class check