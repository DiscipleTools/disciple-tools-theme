<?php
declare( strict_types = 1 );
/**
 * Disciple Tools Functions.php
 *
 * @package Disciple Tools
 * @class Disciple_Tools
 */

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

require_once( get_template_directory() . '/dt-core/admin/php7-warning.php' ); // Checks for the correct php version and displays warning

/**
 * Activation, Deactivation, and Multisite
 */
register_activation_hook( __FILE__, 'dt_activate' );
register_deactivation_hook( __FILE__, 'dt_deactivate' );

/**
 * Adds the Disciple_Tools Class and runs database and roles version checks.
 */
function dt_theme_loaded()
{
    /** We want to make sure roles are up-to-date. */
    require_once( get_template_directory() . '/dt-core/admin/class-roles.php' );
    Disciple_Tools_Roles::instance()->set_roles_if_needed();


    disciple_tools();

    /**
     * We want to make sure migrations are run on updates.
     *
     * @see https://www.sitepoint.com/wordpress-plugin-updates-right-way/
     */
    try {
        require_once( get_template_directory() . '/dt-core/admin/class-migration-engine.php' );
        Disciple_Tools_Migration_Engine::migrate( disciple_tools()->migration_number );
    } catch ( Throwable $e ) {
        new WP_Error( 'migration_error', 'Migration engine failed to migrate.' );
    }

    /**
     * Load Language Files
     */
    load_theme_textdomain( 'disciple_tools', get_template_directory() .'/dt-assets/translation' );
}
add_action( 'after_setup_theme', 'dt_theme_loaded' );


/**
 * Returns the main instance of Disciple_Tools to prevent the need to use globals.
 *
 * @since  0.1.0
 * @return object Disciple_Tools
 */
function disciple_tools()
{
    return Disciple_Tools::instance();
}

/**
 * Main Disciple_Tools Class
 *
 * @class   Disciple_Tools
 * @since   0.1.0
 * @package Disciple_Tools
 */
class Disciple_Tools
{
    /**
     * Declared variables
     *
     * @var    string
     * @access public
     * @since  0.1.0
     */
    public $token;
    public $version;
    public $plugin_url;
    public $plugin_path;
    public $dt_svg;
    public $admin;
    public $settings;
    public $metrics;
    public $notifications;
    public $post_types = [];
    public $endpoints = [];
    public $core = [];
    public $hooks = [];
    public $logging = [];
    public $user_local;

    /**
     * Disciple_Tools The single instance of Disciple_Tools.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools Instance
     * Ensures only one instance of Disciple_Tools is loaded or can be loaded.
     *
     * @since  0.1.0
     * @static
     * @see    disciple_tools()
     * @return Disciple_Tools instance
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct()
    {
        global $wpdb;

        /**
         * Prepare variables
         */
        $this->token = 'disciple_tools';
        $this->version = '0.6.0';
        $this->migration_number = 3;
        $this->plugin_url = get_template_directory_uri() . '/';
        $this->plugin_path = get_template_directory() . '/';
        $this->plugin_img_url = get_template_directory_uri() . '/dt-core/admin/img/';
        $this->plugin_img_path = get_template_directory() . '/dt-core/admin/img/';
        $this->plugin_js_url = get_template_directory_uri() . '/dt-core/admin/js/';
        $this->plugin_js_path = get_template_directory() . '/dt-core/admin/js/';
        $this->plugin_css_url = get_template_directory_uri() . '/dt-core/admin/css/';
        $this->plugin_css_path = get_template_directory() . '/dt-core/admin/css/';
        $this->user_local = get_user_locale();

        $wpdb->dt_activity_log = $wpdb->prefix . 'dt_activity_log'; // Prepare database table names
        $wpdb->dt_reports = $wpdb->prefix . 'dt_reports';
        $wpdb->dt_reportmeta = $wpdb->prefix . 'dt_reportmeta';
        $wpdb->dt_share = $wpdb->prefix . 'dt_share';
        $wpdb->dt_notifications = $wpdb->prefix . 'dt_notifications';

        /**
         * Load first files
         */
        require_once( get_template_directory() . '/dt-core/libraries/posts-to-posts/posts-to-posts.php' ); // P2P library/plugin. Required before DT instance
        require_once( get_template_directory() . '/dt-core/admin/config-site-defaults.php' ); // Force required site configurations
        require_once( get_template_directory() . '/dt-core/wp-async-request.php' ); // Async Task Processing

        /**
         * Rest API Support
         */
        require_once( get_template_directory() . '/dt-core/integrations/class-api-keys.php' ); // API keys for remote access
        $this->api_keys = Disciple_Tools_Api_Keys::instance();
        require_once( get_template_directory() . '/dt-core/admin/restrict-rest-api.php' ); // sets authentication requirement for rest end points. Disables rest for pre-wp-4.7 sites.
        require_once( get_template_directory() . '/dt-core/admin/restrict-xml-rpc-pingback.php' ); // protect against DDOS attacks.

        /**
         * User Groups & Multi Roles
         */
        require_once( get_template_directory() . '/dt-core/admin/user-groups/class-user-taxonomy.php' );
        require_once( get_template_directory() . '/dt-core/admin/user-groups/user-groups-taxonomies.php' );
        require_once( get_template_directory() . '/dt-core/admin/multi-role/multi-role.php' );
        $this->multi = Disciple_Tools_Multi_Roles::instance();

        /**
         * Theme specific files
         */
        require_once( get_template_directory() . '/dt-assets/functions/theme-support.php' ); // Theme support options
        require_once( get_template_directory() . '/dt-assets/functions/cleanup.php' ); // WP Head and other cleanup functions
        require_once( get_template_directory() . '/dt-assets/functions/enqueue-scripts.php' ); // Register scripts and stylesheets
        require_once( get_template_directory() . '/dt-assets/functions/sidebar.php' ); // Register sidebars/widget areas
        require_once( get_template_directory() . '/dt-assets/functions/comments.php' ); // Makes WordPress comments suck less
        require_once( get_template_directory() . '/dt-assets/functions/page-navi.php' ); // Replace 'older/newer' post links with numbered navigation
        require_once( get_template_directory() . '/dt-assets/functions/private-site.php' ); // Sets site to private
        require_once( get_template_directory() . '/dt-assets/functions/login.php' ); // Customize the WordPress login menu
        require_once( get_template_directory() . '/dt-assets/functions/menu.php' ); // Register menus and menu walkers
        require_once( get_template_directory() . '/dt-assets/functions/breadcrumbs.php' ); // Breadcrumbs bar

        /**
         * URL loader
         */
        add_action( 'init', function() {
            $template_for_url = [
            'metrics'       => 'template-metrics.php',
            'settings'      => 'template-settings.php',
            'notifications' => 'template-notifications.php',
            'about'         => 'template-about.php',
            'team'          => 'template-team.php',
            'contacts/new'  => 'template-contacts-new.php',
            'groups/new'    => 'template-groups-new.php',
            ];
            $url_path = trim( parse_url( add_query_arg( [] ), PHP_URL_PATH ), '/' );

            if ( isset( $template_for_url[ $url_path ] ) ) {
                $template_filename = locate_template( $template_for_url[ $url_path ], true );
                if ( $template_filename ) {
                    exit(); // just exit if template was found and loaded
                } else {
                    throw new Error( "Expected to find template " . $template_for_url[ $url_path ] );
                }
            }
        } );
        /**
         * Set the locale for the user
         * must be loaded after most files
         * @return string
         */
        add_filter( 'locale', function (){
            return $this->user_local;
        } );

        /**
         * Versioning System
         */
        require_once( get_template_directory() . '/dt-core/config-required-plugins.php' );
        require_once( get_template_directory() . '/dt-core/libraries/class-tgm-plugin-activation.php' );
        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
            require( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
        }
        Puc_v4_Factory::buildUpdateChecker(
            'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-version-control/master/disciple-tools-theme-version-control.json',
            __FILE__,
            'disciple-tools-theme'
        );

        /**
         * Data model
         *
         * @posttype   Contacts       Post type for contact storage
         * @posttype   Groups         Post type for groups storage
         * @posttype   Locations      Post type for location information.
         * @posttype   People Groups  (optional) Post type for people groups
         * @posttype   Prayer         Post type for prayer movement updates.
         * @posttype   Project        Post type for movement project updates. (These updates are intended to be for extended owners of the movement project, and different than the prayer guide published in the prayer post type.)
         * @taxonomies
         * @service    Post to Post connections
         * @service    User groups via taxonomies
         */
        require_once( get_template_directory() . '/dt-core/class-taxonomy.php' );

        /**
         * dt-posts
         */
        require_once( get_template_directory() . '/dt-core/posts.php' );

        /**
         * dt-contacts
         */
        require_once( get_template_directory() . '/dt-contacts/contacts-post-type.php' );
        $this->post_types['contacts'] = Disciple_Tools_Contact_Post_Type::instance();
        require_once( get_template_directory() . '/dt-contacts/contacts-endpoints.php' );
        $this->endpoints['contacts'] = Disciple_Tools_Contacts_Endpoints::instance();
        require_once( get_template_directory() . '/dt-contacts/contacts-template.php' ); // Functions to support theme

        /**
         * dt-groups
         */
        require_once( get_template_directory() . '/dt-groups/groups-post-type.php' );
        $this->post_types['groups'] = Disciple_Tools_Groups_Post_Type::instance();
        require_once( get_template_directory() . '/dt-groups/groups-template.php' ); // Functions to support theme
        require_once( get_template_directory() . '/dt-groups/groups.php' );
        require_once( get_template_directory() . '/dt-groups/groups-endpoints.php' ); // builds rest endpoints
        $this->endpoints['groups'] = Disciple_Tools_Groups_Endpoints::instance();

        /**
         * dt-locations
         */
        require_once( get_template_directory() . '/dt-locations/locations-post-type.php' );
        $this->post_types['locations'] = Disciple_Tools_Location_Post_Type::instance();
        require_once( get_template_directory() . '/dt-locations/locations-template.php' );
        require_once( get_template_directory() . '/dt-locations/locations.php' ); // serves the locations rest endpoints
        require_once( get_template_directory() . '/dt-locations/locations-endpoints.php' ); // builds rest endpoints
        $this->endpoints['locations'] = Disciple_Tools_Locations_Endpoints::instance();

        /**
         * dt-people-groups
         */
        require_once( get_template_directory() . '/dt-people-groups/people-groups-post-type.php' );
        $this->post_types['peoplegroups'] = Disciple_Tools_People_Groups_Post_Type::instance();
        require_once( get_template_directory() . '/dt-people-groups/people-groups-template.php' );
        require_once( get_template_directory() . '/dt-people-groups/people-groups.php' );
        require_once( get_template_directory() . '/dt-people-groups/people-groups-endpoints.php' ); // builds rest endpoints
        $this->endpoints['peoplegroups'] = Disciple_Tools_People_Groups_Endpoints::instance();

        /**
         * dt-metrics
         */
        require_once( get_template_directory() . '/dt-metrics/class-counter.php' );
        $this->counter = Disciple_Tools_Counter::instance();
        require_once( get_template_directory() . '/dt-metrics/class-goals.php' );
        require_once( get_template_directory() . '/dt-metrics/metrics-template.php' );
        require_once( get_template_directory() . '/dt-metrics/metrics.php' );
        $this->metrics = Disciple_Tools_Metrics::instance();
        require_once( get_template_directory() . '/dt-metrics/metrics-endpoints.php' );
        $this->endpoints['metrics'] = new Disciple_Tools_Metrics_Endpoints();

        /**
         * dt-users
         */
        require_once( get_template_directory() . '/dt-users/users.php' );
        $this->core['users'] = new Disciple_Tools_Users();
        require_once( get_template_directory() . '/dt-users/users-template.php' );
        require_once( get_template_directory() . '/dt-users/users-endpoints.php' );
        $this->endpoints['users'] = new Disciple_Tools_Users_Endpoints();

        /**
         * dt-notifications
         */
        require_once( get_template_directory() . '/dt-notifications/notifications-hooks.php' );
        $this->hooks['notifications'] = Disciple_Tools_Notification_Hooks::instance();
        require_once( get_template_directory() . '/dt-notifications/notifications-template.php' );
        require_once( get_template_directory() . '/dt-notifications/notifications.php' );
        $this->core['notifications'] = Disciple_Tools_Notifications::instance();
        require_once( get_template_directory() . '/dt-notifications/notifications-endpoints.php' );
        $this->endpoints['notifications'] = Disciple_Tools_Notifications_Endpoints::instance();
        require_once( get_template_directory() . '/dt-notifications/notifications-email.php' ); // sends notification emails through the async task process

        /**
         * Post-to-Post configuration
         */
        require_once( get_template_directory() . '/dt-core/config-p2p.php' ); // Creates the post to post relationship between the post type tables.

        // Custom Metaboxes
        require_once( get_template_directory() . '/dt-core/admin/metaboxes/box-address.php' ); // todo remove theme dependency on this box. used by both theme and wp-admin

        /**
         * Logging
         */
        require_once( get_template_directory() . '/dt-core/logging/class-activity-api.php' );
        $this->logging_activity_api = new Disciple_Tools_Activity_Log_API();
        require_once( get_template_directory() . '/dt-core/logging/class-activity-hooks.php' ); // contacts and groups report building
        $this->logging_activity_hooks = Disciple_Tools_Activity_Hooks::instance();
        require_once( get_template_directory() . '/dt-core/logging/class-reports-api.php' );
        $this->logging_reports_api = new Disciple_Tools_Reports_API();
        require_once( get_template_directory() . '/dt-core/logging/class-reports-cron.php' ); // Cron scheduling for nightly builds of reports
        $this->logging_reports_cron = Disciple_Tools_Reports_Cron::instance();
        require_once( get_template_directory() . '/dt-core/logging/class-reports-dt.php' ); // contacts and groups report building

        /**
         * Workflows
         */
        require_once( get_template_directory() . '/dt-workflows/index.php' );
        $this->workflows = Disciple_Tools_Workflows::instance();


        /**
         * Admin panel
         * Contains all those features that only run if in the Admin panel
         * or those things directly supporting Admin panel features.
         */
        if ( is_admin() ) {

            // Administration
            require_once( get_template_directory() . '/dt-core/admin/admin-enqueue-scripts.php' ); // Load admin scripts
            require_once( get_template_directory() . '/dt-core/admin/admin-theme-design.php' ); // Configures elements of the admin enviornment
            require_once( get_template_directory() . '/dt-core/admin/config-dashboard.php' );
            $this->config_dashboard = Disciple_Tools_Dashboard::instance();

            // Settings Menu
            require_once( get_template_directory() . '/dt-core/admin/menu/main.php' ); // main registers all the menu pages and tabs
            $this->config_menu = Disciple_Tools_Config::instance();
            require_once( get_template_directory() . '/dt-core/admin/menu/extensions-menu.php' ); // main registers all the menu pages and tabs
            require_once( get_template_directory() . '/dt-core/admin/utilities/locations-async-insert.php' ); // required to load for async listening


            // Contacts
            require_once( get_template_directory() . '/dt-contacts/contacts-config.php' );
            $this->config_contacts = Disciple_Tools_Config_Contacts::instance();
            require_once( get_template_directory() . '/dt-groups/groups-config.php' );
            $this->config_groups = Disciple_Tools_Groups_Config::instance();

            // Locations
            require_once( get_template_directory() . '/dt-locations/geocoding-api.php' );

            // People Groups
            require_once( get_template_directory() . '/dt-people-groups/admin-menu.php' );
            $this->people_groups_admin = Disciple_Tools_People_Groups_Admin_Menu::instance();

            // Tables
            require_once( get_template_directory() . '/dt-core/admin/menu/tables/notifications-table.php' );
            require_once( get_template_directory() . '/dt-core/admin/menu/tables/activity-list-table.php' ); // contacts and groups report building
            require_once( get_template_directory() . '/dt-core/admin/menu/tables/reports-list-table.php' ); // contacts and groups report building

            // Metaboxes
            require_once( get_template_directory() . '/dt-core/admin/metaboxes/box-activity.php' );
            require_once( get_template_directory() . '/dt-core/admin/metaboxes/box-share-contact.php' );
        }
        /* End Admin configuration section */
    } // End __construct()

    /**
     * Log the plugin version number.
     *
     * @access private
     * @since  0.1.0
     */
    public function _log_version_number()
    {
        // Log the version number.
        update_option( $this->token . '-version', $this->version );
    } // End _log_version_number()

    /**
     * Cloning is forbidden.
     *
     * @access public
     * @since  0.1.0
     */
    public function __clone()
    {
        wp_die( esc_html__( "Cheatin' huh?" ), __FUNCTION__ );
    } // End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @access public
     * @since  0.1.0
     */
    public function __wakeup()
    {
        wp_die( esc_html__( "Cheatin' huh?" ), __FUNCTION__ );
    } // End __wakeup()

} // End Class


/**
 * Deactivation Hook
 */
function dt_deactivate()
{
    require_once get_template_directory() . '/dt-core/admin/class-deactivator.php';
    Disciple_Tools_Deactivator::deactivate();
}

/**
 * Activation Hook
 */
function dt_activate()
{
    require_once get_template_directory() . '/dt-core/admin/class-activator.php';
    Disciple_Tools_Activator::activate();
}

/**
 * Route Front Page depending on login role
 */
function dt_route_front_page()
{
    if ( user_can( get_current_user_id(), 'access_contacts' ) ) {
        wp_safe_redirect( home_url( '/contacts' ) );
    } else {
        wp_safe_redirect( home_url( '/settings' ) );
    }
}

/**
 * A simple function to assist with development and non-disruptive debugging.
 * -----------
 * -----------
 * REQUIREMENT:
 * WP Debug logging must be set to true in the wp-config.php file.
 * Add these definitions above the "That's all, stop editing! Happy blogging." line in wp-config.php
 * -----------
 * define( 'WP_DEBUG', true ); // Enable WP_DEBUG mode
 * define( 'WP_DEBUG_LOG', true ); // Enable Debug logging to the /wp-content/debug.log file
 * define( 'WP_DEBUG_DISPLAY', false ); // Disable display of errors and warnings
 * @ini_set( 'display_errors', 0 );
 * -----------
 * -----------
 * EXAMPLE USAGE:
 * (string)
 * write_log('THIS IS THE START OF MY CUSTOM DEBUG');
 * -----------
 * (array)
 * $an_array_of_things = ['an', 'array', 'of', 'things'];
 * write_log($an_array_of_things);
 * -----------
 * (object)
 * $an_object = new An_Object
 * write_log($an_object);
 */
if ( !function_exists( 'dt_write_log' ) ) {
    /**
     * A function to assist development only.
     * This function allows you to post a string, array, or object to the WP_DEBUG log.
     *
     * @param $log
     */
    function dt_write_log( $log )
    {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}
