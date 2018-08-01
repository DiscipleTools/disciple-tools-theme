<?php
/**
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1.0
 * @package    Disciple_Tools
 * @subpackage Disciple_Tools/includes/admin
 * @author
 */

require_once( dirname( __FILE__ ) . '/class-migration-engine.php' );


//add_action('after_switch_theme', 'dt_theme_activation');

function dt_theme_activation() {
    global $wpdb;
    $disciple_tools = disciple_tools();
    $disciple_tools->_log_version_number();

    /** Create roles and capabilities */
    require_once( 'class-roles.php' );
    $roles = Disciple_Tools_Roles::instance();
    $roles->set_roles();
    /** Initialize default dt site options */
    dt_get_option( 'dt_site_options' );
    dt_get_option( 'dt_site_custom_lists' );
    dt_get_option( 'base_user' );
    dt_get_option( 'map_key' );
    dt_get_option( 'location_levels' );

    /** Register Cron Jobs for Daily Reports */
    Disciple_Tools_Reports_Cron::register_daily_report_events();

    /** Activate database creation */
    try {
        Disciple_Tools_Migration_Engine::migrate( disciple_tools()->migration_number );
    } catch ( Throwable $exception ) {
        new WP_Error( 'migration_error', 'Migration tool threw an error in the theme activation' );
    }
    // Disciple_Tools_Migration_Engine::migrate is also run on updates, see
    // the code in functions.php

    // Confirm install of Post 2 Post tables
    require_once( get_stylesheet_directory() . 'dt-core/libraries/posts-to-posts/vendor/scribu/lib-posts-to-posts/storage.php' );
    require_once( get_stylesheet_directory() . 'dt-core/libraries/posts-to-posts/vendor/scribu/scb-framework/Util.php' );
    P2P_Storage::install();

    dt_write_log( __METHOD__ );
    dt_write_log( 'test' );
}


/**
 * Class Disciple_Tools_Activator
 */
class Disciple_Tools_Activator
{

    /**
     * Activities to run during installation.
     *
     * @since 0.1.0
     */
    public static function activate()
    {
        /** @todo Since moving to a theme, I do not believe this activation section is called. I believe it is only called when it is a plugin not a theme. */
        global $wpdb;
        $disciple_tools = disciple_tools();
        $disciple_tools->_log_version_number();

        /** Create roles and capabilities */
        require_once( 'class-roles.php' );
        $roles = Disciple_Tools_Roles::instance();
        $roles->set_roles();


        /** Initialize default dt site options */
        dt_get_option( 'dt_site_options' );
        dt_get_option( 'dt_site_custom_lists' );
        dt_get_option( 'base_user' );
        dt_get_option( 'map_key' );
        dt_get_option( 'location_levels' );

        /** Register Cron Jobs for Daily Reports */
        Disciple_Tools_Reports_Cron::register_daily_report_events();

        /** Activate database creation */
        try {
            Disciple_Tools_Migration_Engine::migrate( disciple_tools()->migration_number );
        } catch ( Throwable $exception ) {
            new WP_Error( 'migration_error', 'Migration tool threw an error in the theme activation' );
        }
        // Disciple_Tools_Migration_Engine::migrate is also run on updates, see
        // the code in functions.php

        dt_write_log( __METHOD__ );
        dt_write_log( 'test' );
    }

    public static function install_p2p() {
        // Confirm install of Post 2 Post tables
        require_once( get_stylesheet_directory() . 'dt-core/libraries/posts-to-posts/vendor/scribu/lib-posts-to-posts/storage.php' );
        require_once( get_stylesheet_directory() . 'dt-core/libraries/posts-to-posts/vendor/scribu/scb-framework/Util.php' );
        P2P_Storage::install();

        dt_write_log( __METHOD__ );
        dt_write_log( 'p2p tables installed' );
        return true;
    }

}
