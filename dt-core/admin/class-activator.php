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
    public static function activate( $network_wide )
    {
        global $wpdb;
        $disciple_tools = disciple_tools();
        $disciple_tools->_log_version_number();

        /** Create roles and capabilities */
        require_once( 'class-roles.php' );
        $roles = Disciple_Tools_Roles::instance();
        $roles->set_roles();

        /** Setup key for JWT authentication */
        if ( !defined( 'JWT_AUTH_SECRET_KEY' ) ) {
            if ( get_option( "my_jwt_key" ) ) {
                // @codingStandardsIgnoreLine
                define( 'JWT_AUTH_SECRET_KEY', get_option( "my_jwt_key" ) );
            } else {
                $iv = password_hash( random_bytes( 16 ), PASSWORD_DEFAULT );
                // @codingStandardsIgnoreLine
                update_option( 'my_jwt_key', $iv );
                // @codingStandardsIgnoreLine
                define( 'JWT_AUTH_SECRET_KEY', $iv );
            }
        }

        /** Initialize default dt site options */
        dt_get_option( 'dt_site_options' );
        dt_get_option( 'dt_site_custom_lists' );
        dt_get_option( 'base_user' );
        dt_get_option( 'map_key' );

        /** Activate database creation for Disciple Tools Activity logs */
        if ( is_multisite() && $network_wide ) {
            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach ( $blog_ids as $blog_id ) {
                switch_to_blog( $blog_id );
                Disciple_Tools_Migration_Engine::migrate( disciple_tools()->migration_number );
                restore_current_blog();
            }
        } else {
            Disciple_Tools_Migration_Engine::migrate( disciple_tools()->migration_number );
        }
        // Disciple_Tools_Migration_Engine::migrate is also run on updates, see
        // the code in disciple-tools.php
    }

    /**
     * Creating tables whenever a new blog is created
     *
     * @param $blog_id
     * @param $user_id
     * @param $domain
     * @param $path
     * @param $site_id
     * @param $meta
     */
    public static function on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta )
    {
        if ( is_plugin_active_for_network( 'disciple-tools/disciple-tools.php' ) ) {
            switch_to_blog( $blog_id );
            Disciple_Tools_Migration_Engine::migrate( disciple_tools()->migration_number );
            restore_current_blog();
        }
    }

    /**
     * @param $tables
     *
     * @return array
     */
    public static function on_delete_blog( $tables )
    {
        global $wpdb;
        $tables[] = $wpdb->prefix . 'dt_activity_log';
        $tables[] = $wpdb->prefix . 'dt_reports';
        $tables[] = $wpdb->prefix . 'dt_reportmeta';

        return $tables;
    }

}
