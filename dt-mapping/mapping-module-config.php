<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'DT_Mapping_Module_Config' ) ) {
    class DT_Mapping_Module_Config
    {
        private static $_instance = null;
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        } // End instance()

        public function __construct() {

            /**
             * Load custom columns or remove these and replace with your own.
             * Use these as examples and make your own columns.
             */

            /**
             * dt_mapping_module_has_permissions
             *
             * @see    mapping.php:56
             */
            add_filter( 'dt_mapping_module_has_permissions', [ $this, 'custom_permission_check' ] );

            /**
             * dt_mapping_module_translations
             *
             * @see     mapping.php:119 125
             */
            add_filter( 'dt_mapping_module_translations', [ $this, 'custom_translations_filter' ] );

            /**
             * dt_mapping_module_settings
             *
             * @see     mapping.php:241
             */
            add_filter( 'dt_mapping_module_settings', [ $this, 'custom_settings_filter' ] );

            /**
             * Use this filter to add data to sub levels by location_grid
             * dt_mapping_module_map_level_by_grid_id
             *
             * @see     mapping.php:389
             */
            add_filter( 'dt_mapping_module_map_level_by_grid_id', [ $this, 'map_level_by_grid_id_filter' ], 10, 1 );

            /**
             * dt_mapping_module_url_base
             *
             * @see     mapping.php:102
             */
            add_filter( 'dt_mapping_module_url_base', [ $this, 'custom_url_base' ] );

            /**
             * dt_mapping_module_endpoints
             *
             * @see     mapping.php:77
             */
            add_filter( 'dt_mapping_module_endpoints', [ $this, 'add_custom_endpoints' ], 10, 1 );

        }

        /**
         * Set a link in the top bar of the site
         */
        public function top_nav_desktop() {
            if ( $this->custom_permission_check() ){
                ?>
                <li><a
                    href="<?php echo esc_url( site_url( '/mapping/' ) ) . '#mapping_view'; ?>"><?php esc_html_e( "Mapping" ); ?></a>
                </li><?php
            }
        }

        /**
         * custom_permission_check
         *
         * @return bool
         */
        public function custom_permission_check(): bool {
            /**
             * Add logic to evaluate current user and return a bool decision on permission to the mapping module
             * Example below gives permission to dispatchers and admins.
             */
            if ( current_user_can( 'view_any_contacts' )
                || current_user_can( 'view_project_metrics' )
                || current_user_can( 'view_mapping' ) ) {
                return true;
            }
            return false;
        }

        public function custom_settings_filter( $data ) {
            /**
             * Add or modify current settings
             */
            return $data;
        }

        /**
         * custom_translations
         *
         * @param $translations
         *
         * @return mixed
         */
        public function custom_translations_filter( $translations ) {
            /**
             * Add translation strings
             */
            return $translations;
        }

        /**
         * Pre-processes map_level data before delivery
         *
         * @param $data
         *
         * @return mixed
         */
        public function map_level_by_grid_id_filter( $data ) {
            /**
             * Add filter here
             */
            return $data;
        }

        /**
         * add_custom_endpoints
         *
         * @param $endpoints
         *
         * @return mixed
         */
        public function add_custom_endpoints( $endpoints ) {
            /**
             * Add new endpoint here
             */
            return $endpoints;
        }

        /**
         * Set the base url for the mapping links to respond to.
         *
         * @param $base_url (default is '
         *
         * @return string
         */
        public function custom_url_base( $base_url ) {
            /**
             * Add new url base for listener
             */
            return $base_url;
        }

    }
    DT_Mapping_Module_Config::instance();
}

