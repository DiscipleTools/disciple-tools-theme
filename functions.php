<?php
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

/**
 * Test for minimum required PHP version
 */
if ( version_compare( phpversion(), '7.0', '<' ) ) {

    /* We only support PHP >= 7.0, however, we want to support allowing users
     * to install this theme even on old versions of PHP, without showing a
     * horrible message, but instead a friendly notice.
     *
     * For this to work, this file must be compatible with old PHP versions.
     * Feel free to use PHP 7 features in other files, but not in this one.
     */

    new WP_Error( 'php_version_fail', 'Disciple Tools theme requires PHP version 7.0 or greater. Your current version is: '.phpversion().' Please upgrade PHP or uninstall this theme' );
    add_action( 'admin_notices', 'dt_theme_admin_notice_required_php_version' );
}
else {

    /**
     * Adds the Disciple_Tools Class and runs database and roles version checks.
     */
    function dt_theme_loaded() {
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
            Disciple_Tools_Migration_Engine::migrate( Disciple_Tools::instance()->migration_number );
        } catch ( Throwable $e ) {
            new WP_Error( 'migration_error', 'Migration engine failed to migrate.' );
        }



        /**
         * Load Language Files
         */
        load_theme_textdomain( 'disciple_tools', get_template_directory() . '/dt-assets/translation' );
    }
    add_action( 'after_setup_theme', 'dt_theme_loaded' );

    /**
     * Returns the main instance of Disciple_Tools to prevent the need to use globals.
     *
     * @since  0.1.0
     * @return object Disciple_Tools
     */
    function disciple_tools() {
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
        public $theme_url;
        public $theme_path;
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
        public $user_locale;
        public $site_locale;

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
        public static function instance() {
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
        public function __construct() {
            global $wpdb;

            /**
             * Prepare variables
             */
            $this->token = 'disciple_tools';
            $this->version = '0.28.0';
            $this->migration_number = 29;




            $this->theme_url = get_template_directory_uri() . '/';
            $this->theme_path = get_template_directory() . '/';

            $this->admin_img_url = get_template_directory_uri() . '/dt-core/admin/img/';
            $this->admin_js_url = get_template_directory_uri() . '/dt-core/admin/js/';
            $this->admin_js_path = get_template_directory() . '/dt-core/admin/js/';
            $this->admin_css_url = get_template_directory_uri() . '/dt-core/admin/css/';
            $this->admin_css_path = get_template_directory() . '/dt-core/admin/css/';

            $this->user_locale = get_user_locale();
            $this->site_locale = get_locale();

            set_up_wpdb_tables();

            /**
             * Load first files
             */
            require_once( get_template_directory() . '/dt-core/global-functions.php' );
            $is_rest = dt_is_rest();
            $url_path = dt_get_url_path();
            require_once( get_template_directory() . '/dt-core/libraries/posts-to-posts/posts-to-posts.php' ); // P2P library/plugin. Required before DT instance
            require_once( get_template_directory() . '/dt-core/admin/config-site-defaults.php' ); // Force required site configurations
            require_once( get_template_directory() . '/dt-core/wp-async-request.php' ); // Async Task Processing


            require_once( get_template_directory() . '/dt-core/admin/restrict-rest-api.php' ); // sets authentication requirement for rest end points. Disables rest for pre-wp-4.7 sites.
            require_once( get_template_directory() . '/dt-core/admin/restrict-site-access.php' ); // protect against DDOS attacks.

            /**
             * User Groups & Multi Roles
             */
            require_once( get_template_directory() . '/dt-core/admin/multi-role/multi-role.php' );
            $this->multi = Disciple_Tools_Multi_Roles::instance();

            /**
             * Theme specific files
             */
            if ( !$is_rest ){
                require_once( get_template_directory() . '/dt-assets/functions/cleanup.php' ); // WP Head and other cleanup functions
                require_once( get_template_directory() . '/dt-assets/functions/enqueue-scripts.php' ); // Register scripts and stylesheets
                require_once( get_template_directory() . '/dt-assets/functions/menu.php' ); // Register menus and menu walkers
                require_once( get_template_directory() . '/dt-assets/functions/details-bar.php' ); // Breadcrumbs bar
            }


            /**
             * URL loader
             */
            add_action( 'init', function() {
                $template_for_url = [
                    'metrics'               => 'template-metrics.php',
                    'settings'              => 'template-settings.php',
                    'notifications'         => 'template-notifications.php',
                    'contacts/new'          => 'template-contacts-new.php',
                    'groups/new'            => 'template-groups-new.php',
                    'contacts/mergedetails' => 'template-merge-details.php',
                    'view-duplicates'       => 'template-view-duplicates.php',
                ];

                $template_for_url = apply_filters( 'dt_templates_for_urls', $template_for_url );

                $url_path = dt_get_url_path();

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
             *
             * @return string
             */
            add_filter( 'locale', function() {
                if ( is_admin() ) {
                    return $this->site_locale;
                } else {
                    return $this->user_locale;
                }
            } );

            /**
             * Versioning System
             */
            require_once( get_template_directory() . '/dt-core/config-required-plugins.php' );
            require_once( get_template_directory() . '/dt-core/libraries/class-tgm-plugin-activation.php' );

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

            require_once( get_template_directory() . '/dt-core/admin/site-link-post-type.php' );
            Site_Link_System::instance( 100, 'dashicons-admin-links' );

            /**
             * dt-posts
             */
            require_once( get_template_directory() . '/dt-posts/posts.php' );
            require_once( get_template_directory() . '/dt-posts/custom-post-type.php' );
            require_once( get_template_directory() . '/dt-posts/dt-posts.php' );
            require_once( get_template_directory() . '/dt-posts/dt-posts-endpoints.php' );
            Disciple_Tools_Posts_Endpoints::instance();

            /**
             * dt-contacts
             */
            require_once( get_template_directory() . '/dt-contacts/contacts-post-type.php' );
            $this->post_types['contacts'] = Disciple_Tools_Contact_Post_Type::instance();
            require_once( get_template_directory() . '/dt-contacts/contacts.php' );
            new Disciple_Tools_Contacts();
            require_once( get_template_directory() . '/dt-contacts/contacts-template.php' ); // Functions to support theme
            require_once( get_template_directory() . '/dt-contacts/contacts-transfer.php' ); // Functions to support theme
            if ( strpos( $url_path, 'contact' ) !== false ){
                require_once( get_template_directory() . '/dt-contacts/contacts-endpoints.php' );
                $this->endpoints['contacts'] = Disciple_Tools_Contacts_Endpoints::instance();
            }

            /**
             * dt-groups
             */
            require_once( get_template_directory() . '/dt-groups/groups-post-type.php' );
            $this->post_types['groups'] = Disciple_Tools_Groups_Post_Type::instance();
            require_once( get_template_directory() . '/dt-groups/groups-template.php' ); // Functions to support theme
            require_once( get_template_directory() . '/dt-groups/groups.php' );
            new Disciple_Tools_Groups();
            if ( strpos( $url_path, 'group' ) !== false ){
                require_once( get_template_directory() . '/dt-groups/groups-endpoints.php' ); // builds rest endpoints
                $this->endpoints['groups'] = Disciple_Tools_Groups_Endpoints::instance();
            }
            /**
             * dt-mapping
             */
            require_once( get_template_directory() . '/dt-mapping/loader.php' );
            new DT_Mapping_Module_Loader();


            /**
             * dt-people-groups
             */
            require_once( get_template_directory() . '/dt-people-groups/people-groups-post-type.php' );
            $this->post_types['peoplegroups'] = Disciple_Tools_People_Groups_Post_Type::instance();
            require_once( get_template_directory() . '/dt-people-groups/people-groups.php' );
            if ( strpos( $url_path, 'people-groups' ) !== false ){
                require_once( get_template_directory() . '/dt-people-groups/people-groups-template.php' );
                require_once( get_template_directory() . '/dt-people-groups/people-groups-endpoints.php' ); // builds rest endpoints
                $this->endpoints['peoplegroups'] = Disciple_Tools_People_Groups_Endpoints::instance();
            }
            /**
             * dt-metrics
             */
            require_once( get_template_directory() . '/dt-metrics/counter.php' );
            if ( strpos( $url_path, 'metrics' ) !== false ) {
                require_once( get_template_directory() . '/dt-metrics/metrics.php' );
            }

            /**
             * dt-users
             */
            require_once( get_template_directory() . '/dt-users/users.php' );
            $this->core['users'] = new Disciple_Tools_Users();
            require_once( get_template_directory() . '/dt-users/users-template.php' );
            require_once( get_template_directory() . '/dt-users/users-endpoints.php' );
            $this->endpoints['users'] = new Disciple_Tools_Users_Endpoints();
            if ( !$is_rest ){
                require_once( get_template_directory() . '/dt-users/users-product-tour.php' );
            }
            require_once( get_template_directory() . '/dt-users/user-management.php' );
            new DT_User_Management();

            /**
             * dt-notifications
             */
            require_once( get_template_directory() . '/dt-notifications/notifications-template.php' );
            require_once( get_template_directory() . '/dt-notifications/notifications.php' );
            $this->core['notifications'] = Disciple_Tools_Notifications::instance();
            require_once( get_template_directory() . '/dt-notifications/notifications-endpoints.php' );
            $this->endpoints['notifications'] = Disciple_Tools_Notifications_Endpoints::instance();
            require_once( get_template_directory() . '/dt-notifications/notifications-email.php' ); // sends notification emails through the async task process
            require_once( get_template_directory() . '/dt-core/logging/usage.php' );

            /**
             * Post-to-Post configuration
             */
            require_once( get_template_directory() . '/dt-core/config-p2p.php' ); // Creates the post to post relationship between the post type tables.

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
            require_once( get_template_directory() . '/dt-workflows/workflows.php' );
            $this->workflows = Disciple_Tools_Workflows::instance();

            /**
             * Network
             */
            if ( get_option( 'dt_network_enabled' ) ) {
                require_once( get_template_directory() . '/dt-network/network-endpoints.php' );
            }
            require_once( get_template_directory() . '/dt-network/network.php' );
            require_once( get_template_directory() . '/dt-network/network-queries.php' );

            require_once( get_template_directory() . '/dt-core/admin/gdpr.php' );
            require_once( get_template_directory() . '/dt-core/multisite.php' );

            /**
             * core
             */
            require_once( get_template_directory() . '/dt-core/core-endpoints.php' );
            new Disciple_Tools_Core_Endpoints();

            /**
             * Admin panel
             * Contains all those features that only run if in the Admin panel
             * or those things directly supporting Admin panel features.
             */
            if ( is_admin() ) {

                if ( ! class_exists( 'Puc_v4_Factory' ) ) {
                    require( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
                }
                Puc_v4_Factory::buildUpdateChecker(
                    'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-version-control/master/disciple-tools-theme-version-control.json',
                    __FILE__,
                    'disciple-tools-theme'
                );

                // Administration
                require_once( get_template_directory() . '/dt-core/admin/admin-enqueue-scripts.php' ); // Load admin scripts
                require_once( get_template_directory() . '/dt-core/admin/admin-theme-design.php' ); // Configures elements of the admin enviornment
                require_once( get_template_directory() . '/dt-core/admin/config-dashboard.php' );
                $this->config_dashboard = Disciple_Tools_Dashboard::instance();

                // Admin Menus
                /* Note: The load order matters for the menus and submenus. Submenu must load after menu. */
                require_once( get_template_directory() . '/dt-core/admin/menu/tabs/abstract-tabs-base.php' ); // registers all the menu pages and tabs
                require_once( get_template_directory() . '/dt-core/admin/menu/menu-settings.php' );

                require_once( get_template_directory() . '/dt-core/admin/menu/menu-extensions.php' );
                require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-featured-extensions.php' );

                require_once( get_template_directory() . '/dt-core/admin/menu/menu-utilities.php' );
                require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-people-groups.php' );
                require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-utilities-overview.php' );
                require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-fields.php' );
                require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-contact-import.php' );
                require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-gdpr-erase.php' );
                require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-gdpr-export.php' );

                require_once( get_template_directory() . '/dt-core/admin/menu/menu-metrics.php' );
                require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-metrics-reports.php' );
                require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-metrics-sources.php' );
                require_once( get_template_directory() . '/dt-core/admin/menu/tabs/tab-metrics-edit.php' );
                /* End menu tab section */


                // Contacts
                require_once( get_template_directory() . '/dt-contacts/contacts-config.php' );
                $this->config_contacts = Disciple_Tools_Config_Contacts::instance();
                require_once( get_template_directory() . '/dt-groups/groups-config.php' );
                $this->config_groups = Disciple_Tools_Groups_Config::instance();

            }
            /* End Admin configuration section */

            add_action( 'switch_blog', 'set_up_wpdb_tables', 99, 2 );

        } // End __construct()

        /**
         * Cloning is forbidden.
         *
         * @access public
         * @since  0.1.0
         */
        public function __clone() {
            wp_die( esc_html( "Cheatin' huh?" ), __FUNCTION__ );
        } // End __clone()

        /**
         * Unserializing instances of this class is forbidden.
         *
         * @access public
         * @since  0.1.0
         */
        public function __wakeup() {
            wp_die( esc_html( "Cheatin' huh?" ), __FUNCTION__ );
        } // End __wakeup()

    } // End Class


    /**
     * Route Front Page depending on login role
     */
    function dt_route_front_page() {
        if ( user_can( get_current_user_id(), 'access_contacts' ) ) {
            wp_safe_redirect( apply_filters( 'dt_front_page', home_url( '/contacts' ) ) );
        }
        else if ( ! is_user_logged_in() ) {
            dt_please_log_in();
        }
        else {
            wp_safe_redirect( home_url( '/settings' ) );
        }
    }
    function set_up_wpdb_tables(){
        global $wpdb;
        $wpdb->dt_activity_log = $wpdb->prefix . 'dt_activity_log'; // Prepare database table names
        $wpdb->dt_reports = $wpdb->prefix . 'dt_reports';
        $wpdb->dt_reportmeta = $wpdb->prefix . 'dt_reportmeta';
        $wpdb->dt_share = $wpdb->prefix . 'dt_share';
        $wpdb->dt_notifications = $wpdb->prefix . 'dt_notifications';
        $wpdb->dt_post_user_meta = $wpdb->prefix . 'dt_post_user_meta';
        $wpdb->dt_location_grid = $wpdb->prefix . 'dt_location_grid';
        $wpdb->dt_location_grid_meta = $wpdb->prefix . 'dt_location_grid_meta';

        $more_tables = apply_filters( 'dt_custom_tables', [] );
        foreach ( $more_tables as $table ){
            $wpdb->$table = $wpdb->prefix . $table;
        }
    }
}

/**
 * Php Version Alert
 */
function dt_theme_admin_notice_required_php_version() {
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e( 'Disciple Tools theme requires PHP version 7.0 or greater. Your current version is:', 'disciple_tools' );
            echo esc_html( phpversion() );
            esc_html_e( 'Please upgrade PHP or uninstall this theme', 'disciple_tools' ); ?></p>
    </div>
    <?php
}
