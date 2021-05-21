<?php
/**
 * Disciple_Tools_Settings_Menu class for the admin page
 *
 * @class      Disciple_Tools_Settings_Menu
 * @version    0.1.0
 * @since      0.1.0
 * @package    Disciple.Tools
 * @author     Disciple.Tools
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-general.php' );
require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-custom-lists.php' );
require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-custom-tiles.php' );
require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-custom-fields.php' );
require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-site-links.php' );
require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-security.php' );
require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-custom-translation.php' );
require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-custom-tags.php' );
/**
 * Class Disciple_Tools_Settings_Menu
 */
class Disciple_Tools_Settings_Menu
{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action( "admin_menu", [ $this, "add_dt_options_menu" ] );
    }

    public function add_dt_options_menu() {
        $image_url = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48ZyBjbGFzcz0ibmMtaWNvbi13cmFwcGVyIiBmaWxsPSIjZmZmZmZmIj48cGF0aCBmaWxsPSIjZmZmZmZmIiBkPSJNMjEsMTBoLTEuM2MtMC4yLTAuNy0wLjUtMS40LTAuOS0yLjFsMC45LTAuOWMwLjgtMC44LDAuOC0yLDAtMi44aDBjLTAuOC0wLjgtMi0wLjgtMi44LDBsLTAuOSwwLjkgYy0wLjYtMC40LTEuMy0wLjctMi4xLTAuOVYzYzAtMS4xLTAuOS0yLTItMnMtMiwwLjktMiwydjEuM0M5LjMsNC41LDguNiw0LjcsNy45LDUuMUw3LjEsNC4yYy0wLjgtMC44LTItMC44LTIuOCwwaDAgYy0wLjgsMC44LTAuOCwyLDAsMi44bDAuOSwwLjlDNC43LDguNiw0LjUsOS4zLDQuMywxMEgzYy0xLjEsMC0yLDAuOS0yLDJjMCwxLjEsMC45LDIsMiwyaDEuM2MwLjIsMC43LDAuNSwxLjQsMC45LDIuMWwtMC45LDAuOSBjLTAuOCwwLjgtMC44LDIsMCwyLjhoMGMwLjgsMC44LDIsMC44LDIuOCwwbDAuOS0wLjljMC42LDAuNCwxLjMsMC43LDIuMSwwLjlWMjFjMCwxLjEsMC45LDIsMiwyczItMC45LDItMnYtMS4zIGMwLjctMC4yLDEuNC0wLjUsMi4xLTAuOWwwLjksMC45YzAuOCwwLjgsMiwwLjgsMi44LDBoMGMwLjgtMC44LDAuOC0yLDAtMi44bC0wLjktMC45YzAuNC0wLjYsMC43LTEuMywwLjktMi4xSDIxYzEuMSwwLDItMC45LDItMiBDMjMsMTAuOSwyMi4xLDEwLDIxLDEweiBNMTIsMTVjLTEuNywwLTMtMS4zLTMtM3MxLjMtMywzLTNzMywxLjMsMywzUzEzLjcsMTUsMTIsMTV6Ij48L3BhdGg+PC9nPjwvc3ZnPg==';
        add_menu_page( __( 'General', 'disciple_tools' ), __( 'Settings (DT)', 'disciple_tools' ), 'manage_dt', 'dt_options', [ $this, 'content' ], $image_url, 59 );
    }

    public function content() {
        if ( !current_user_can( 'manage_dt' ) ) {
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        $tab = isset( $_GET["tab"] ) ? sanitize_text_field( wp_unslash( $_GET["tab"] ) ) : 'general';

        ?>
        <div class="wrap">
            <h2><?php esc_html_e( 'DISCIPLE TOOLS : SETTINGS', 'disciple_tools' ) ?></h2>

            <h2 class="nav-tab-wrapper">
                <?php do_action( 'dt_settings_tab_menu', $tab ); ?>
            </h2>

            <?php do_action( 'dt_settings_tab_content', $tab ); ?>

        </div>
        <?php
    }
}
Disciple_Tools_Settings_Menu::instance();
