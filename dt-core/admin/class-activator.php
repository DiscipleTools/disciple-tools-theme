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
    }

}
