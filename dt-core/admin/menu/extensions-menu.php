<?php
/**
 * DT Extensions Menu is a reusable class for Disciple Tools plugins, so that they can either add or update the shared extensions menu.
 * @class DT_Extensions_Menu
 * @version 0.1.0
 * @requires disciple-tools-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}

if( ! class_exists( 'DT_Extensions_Menu' ) ) {
    /**
     * Class DT_Extensions_Menu
     */
    class DT_Extensions_Menu
    {
        private static $_instance = null;

        /**
         * @return \DT_Extensions_Menu|object
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        } // End instance()


        public function __construct() {
            add_action( "admin_menu", array( $this, "menu" ) );
        }

        public function menu() {
            add_menu_page( __( 'Extensions (DT)', 'disciple_tools' ), __( 'Extensions (DT)', 'disciple_tools' ), 'manage_dt', 'dt_extensions', [$this, 'content'], 'dashicons-admin-generic', 59 );
        }

        /**
         * @return void
         */
        public function content() {
            echo '<div class="wrap"><h1>DISCIPLE TOOLS - EXTENSIONS</h1>';
            echo 'Under construction';
            echo '</div>';
        }
    }
    DT_Extensions_Menu::instance();
}