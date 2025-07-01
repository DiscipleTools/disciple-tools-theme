<?php
/**
 * DT Extensions Menu is a reusable class for Disciple.Tools plugins, so that they can either add or update the shared extensions menu.
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
            add_action( 'admin_menu', array( $this, 'menu' ) );
        }

        public function menu() {
            $image_url = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyBpZD0iTGF5ZXJfMiIgZGF0YS1uYW1lPSJMYXllciAyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgNDUwLjY0IDQzMS41NCI+CiAgPGRlZnM+CiAgICA8c3R5bGU+CiAgICAgIC5jbHMtMSB7CiAgICAgICAgZmlsbDogIzhiYzM0YTsKICAgICAgfQoKICAgICAgLmNscy0yIHsKICAgICAgICBmaWxsOiB1cmwoI2xpbmVhci1ncmFkaWVudCk7CiAgICAgIH0KICAgIDwvc3R5bGU+CiAgICA8bGluZWFyR3JhZGllbnQgaWQ9ImxpbmVhci1ncmFkaWVudCIgeDE9IjIyNS4zMyIgeTE9IjI0My44IiB4Mj0iNDUwLjY0IiB5Mj0iMjQzLjgiIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIj4KICAgICAgPHN0b3Agb2Zmc2V0PSIwIiBzdG9wLWNvbG9yPSIjMWQxZDFiIi8+CiAgICAgIDxzdG9wIG9mZnNldD0iLjQ3IiBzdG9wLWNvbG9yPSIjOGJjMzRhIi8+CiAgICAgIDxzdG9wIG9mZnNldD0iMSIgc3RvcC1jb2xvcj0iIzhiYzM0YSIvPgogICAgPC9saW5lYXJHcmFkaWVudD4KICA8L2RlZnM+CiAgPGcgaWQ9IkxheWVyXzEtMiIgZGF0YS1uYW1lPSJMYXllciAxIj4KICAgIDxnPgogICAgICA8cG9seWdvbiBjbGFzcz0iY2xzLTIiIHBvaW50cz0iNDUwLjY0IDQzMS41NCAzNzUuNTQgNDMxLjU0IDIyNS4zMyAxMTcuMjcgMjU0LjU5IDU2LjA1IDQ1MC42NCA0MzEuNTQiLz4KICAgICAgPHBvbHlnb24gY2xhc3M9ImNscy0xIiBwb2ludHM9IjI1NC41OSA1Ni4wNSAyMjUuMzMgMTE3LjI3IDIyNS4zMiAxMTcuMjcgNzUuMTEgNDMxLjU0IDAgNDMxLjU0IDI5LjM1IDM3NS4zMyAyMDUuMyAzOC4zNSAyMjUuMzIgMCAyNDQuMzQgMzYuNDMgMjU0LjU5IDU2LjA1Ii8+CiAgICA8L2c+CiAgPC9nPgo8L3N2Zz4=';
            add_menu_page( __( 'Extensions (D.T)', 'disciple_tools' ), __( 'Extensions (D.T)', 'disciple_tools' ), 'manage_dt', 'dt_extensions', [ $this, 'page' ], $image_url, 50 );
        }

        /**
         * @return void
         */
        public function page() {
            if ( !current_user_can( 'manage_dt' ) ) {
                wp_die( 'You do not have sufficient permissions to access this page.' );
            }

            $tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'featured-extensions';

            ?>
            <div class="wrap">
                <h2><?php esc_html_e( 'DISCIPLE TOOLS : EXTENSIONS', 'disciple_tools' ) ?></h2>

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
