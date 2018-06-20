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
            $image_url = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij4gICAgPGcgY2xhc3M9Im5jLWljb24td3JhcHBlciIgZmlsbD0iI2ZmZmZmZiI+ICAgICAgICA8cGF0aCBkPSJNMjAuNSAxMUgxOVY3YzAtMS4xLS45LTItMi0yaC00VjMuNUMxMyAyLjEyIDExLjg4IDEgMTAuNSAxUzggMi4xMiA4IDMuNVY1SDRjLTEuMSAwLTEuOTkuOS0xLjk5IDJ2My44SDMuNWMxLjQ5IDAgMi43IDEuMjEgMi43IDIuN3MtMS4yMSAyLjctMi43IDIuN0gyVjIwYzAgMS4xLjkgMiAyIDJoMy44di0xLjVjMC0xLjQ5IDEuMjEtMi43IDIuNy0yLjcgMS40OSAwIDIuNyAxLjIxIDIuNyAyLjdWMjJIMTdjMS4xIDAgMi0uOSAyLTJ2LTRoMS41YzEuMzggMCAyLjUtMS4xMiAyLjUtMi41UzIxLjg4IDExIDIwLjUgMTF6Ij48L3BhdGg+ICAgIDwvZz48L3N2Zz4=';
            add_menu_page( __( 'Extensions (DT)', 'disciple_tools' ), __( 'Extensions (DT)', 'disciple_tools' ), 'manage_dt', 'dt_extensions', [ $this, 'page' ], $image_url, 59 );
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
