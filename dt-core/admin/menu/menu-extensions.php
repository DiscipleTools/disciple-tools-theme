<?php
/**
 * DT Extensions Menu is a reusable class for Disciple Tools plugins, so that they can either add or update the shared extensions menu.
 * @class DT_Extensions_Menu
 * @version 0.1.0
 * @requires disciple-tools-theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}

if ( ! class_exists( 'DT_Extensions_Menu' ) ) {
    /**
     * Class DT_Extensions_Menu
     */
    class DT_Extensions_Menu
    {
        private static $_instance = null;
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
            add_menu_page( __( 'Extensions (DT)', 'disciple_tools' ), __( 'Extensions (DT)', 'disciple_tools' ), 'manage_dt', 'dt_extensions', [ $this, 'page' ], 'dashicons-admin-generic', 59 );
        }

        /**
         * @return void
         */
        public function page()
        {
            if ( !current_user_can( 'manage_dt' ) ) {
                wp_die( 'You do not have sufficient permissions to access this page.' );
            }

            $tab = isset( $_GET["tab"] ) ? sanitize_text_field( wp_unslash( $_GET["tab"] ) ) : 'featured-extensions';

            ?>
            <div class="wrap">
                <h2>DISCIPLE TOOLS : EXTENSIONS</h2>

                <h2 class="nav-tab-wrapper">
                    <?php do_action( 'dt_extensions_tab_menu', $tab ); ?>
                </h2>

                <?php do_action( 'dt_extensions_tab_content', $tab ); ?>

            </div>
            <?php
        }
    }
    DT_Extensions_Menu::instance();
}