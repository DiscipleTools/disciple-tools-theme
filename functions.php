<?php
/**
 * Disciple.Tools Functions.php
 *
 * @package Disciple.Tools
 * @class Disciple_Tools
 */

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Test for minimum required PHP version
 */
if ( version_compare( phpversion(), '7.4', '<' ) ) {

    /* We only support PHP >= 7.0, however, we want to support allowing users
     * to install this theme even on old versions of PHP, without showing a
     * horrible message, but instead a friendly notice.
     *
     * For this to work, this file must be compatible with old PHP versions.
     * Feel free to use PHP 7 features in other files, but not in this one.
     */
    add_action( 'admin_notices', function (){
        ?>
        <div class="notice notice-error">
            <p><?php echo esc_html( 'Disciple.Tools theme requires PHP version 7.4 or greater. Your current version is: ' . phpversion() . ' Please upgrade PHP.' );?></p>
        </div>
        <?php
    } );
    return;
}

/**
 * Main Disciple_Tools Class
 *
 * @class   Disciple_Tools
 * @since   0.1.0
 * @package Disciple.Tools
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
    public $admin;
    public $logging_activity_api;
    public $user_locale;
    public $site_locale;
    public $multi;
    /**
     * Disciple_Tools The single instance of Disciple_Tools.
     *
     * @var    object
     * @access private
     * @since  0.1.0
     */
    private static $_instance = null;
    public string $admin_img_url = '';
    public string $admin_js_url = '';
    public string $admin_js_path = '';
    public string $admin_css_url = '';
    public string $admin_css_path = '';


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
        /**
         * Prepare variables
         */
        $this->token = 'disciple_tools';
        $this->version = '1.71.0';
        // $this->migration_number = 38; // moved to Disciple_Tools_Migration_Engine::$migration_number

        $this->theme_url = get_template_directory_uri() . '/';
        $this->theme_path = get_template_directory() . '/';

        $this->admin_img_url = get_template_directory_uri() . '/dt-core/admin/img/';
        $this->admin_js_url = get_template_directory_uri() . '/dt-core/admin/js/';
        $this->admin_js_path = get_template_directory() . '/dt-core/admin/js/';
        $this->admin_css_url = get_template_directory_uri() . '/dt-core/admin/css/';
        $this->admin_css_path = get_template_directory() . '/dt-core/admin/css/';

        $this->user_locale = get_user_locale();
        $this->site_locale = get_locale();

        /** We want to make sure roles are up-to-date. */
        require_once( 'dt-core/configuration/class-roles.php' );
        Disciple_Tools_Roles::instance()->set_roles_if_needed();

        set_up_wpdb_tables();

        /**
         * Load first files
         */
        require_once( 'dt-core/multisite.php' );
        require_once( 'dt-core/utilities/dt-components.php' );
        require_once( 'dt-core/global-functions.php' );
        require_once( 'dt-core/utilities/loader.php' );

        $is_rest = dt_is_rest();
        $url_path = dt_get_url_path();

        require_once( 'dt-core/libraries/posts-to-posts/posts-to-posts.php' ); // P2P library/plugin. Required before DT instance
        require_once( 'dt-core/libraries/wp-queue/wp-queue.php' ); //w
        if ( !class_exists( 'Jwt_Auth' ) ) {
            require_once( 'dt-core/libraries/wp-api-jwt-auth/jwt-auth.php' );
        }
        require_once( 'dt-core/configuration/config-site-defaults.php' ); // Force required site configurations
        require_once( 'dt-core/wp-async-request.php' ); // Async Task Processing
        require_once( 'dt-core/configuration/restrict-rest-api.php' ); // sets authentication requirement for rest end points. Disables rest for pre-wp-4.7 sites.
        require_once( 'dt-core/configuration/restrict-site-access.php' ); // protect against DDOS attacks.
        require_once( 'dt-core/configuration/dt-configuration.php' ); //settings and configuration to alter default WP
        require_once( 'dt-core/dt-route.php' ); // utility class wrapping registering rest routes
        require_once( 'dt-reports/magic-url-class.php' );
        require_once( 'dt-reports/magic-url-base.php' );
        require_once( 'dt-reports/magic-url-endpoints.php' );
        require_once( 'dt-reports/magic-url-setup.php' );
        require_once( 'dt-reports/magic-url-bulk-send.php' );


        /**
         * User Groups & Multi Roles
         */
        require_once( 'dt-core/admin/multi-role/multi-role.php' );
        $this->multi = Disciple_Tools_Multi_Roles::instance();

        /**
         * Theme specific files
         */
        if ( !$is_rest ){
            require_once( 'dt-assets/functions/cleanup.php' ); // WP Head and other cleanup functions
            require_once( 'dt-assets/functions/enqueue-scripts.php' ); // Register scripts and stylesheets
            require_once( 'dt-assets/functions/menu.php' ); // Register menus and menu walkers
            require_once( 'dt-assets/functions/details-bar.php' ); // Breadcrumbs bar
        }

        /**
         * Versioning System
         */
        require_once( 'dt-core/config-required-plugins.php' );
        require_once( 'dt-core/libraries/class-tgm-plugin-activation.php' );
        require_once( 'dt-core/release-notifications.php' );

        /**
         * Data model
         *
         * @posttype   Contacts       Post type for contact storage
         * @posttype   Groups         Post type for groups storage
         * @taxonomies
         * @service    Post to Post connections
         * @service    User groups via taxonomies
         */

        require_once( 'dt-core/admin/site-link-post-type.php' );
        Site_Link_System::instance( 100, 'dashicons-admin-links' );


        /**
         * dt-posts
         */
        require_once( 'dt-posts/posts.php' );
        new Disciple_Tools_Posts();
        require_once( 'dt-posts/custom-post-type.php' );
        require_once( 'dt-posts/dt-posts.php' );
        require_once( 'dt-posts/dt-posts-endpoints.php' );
        require_once( 'dt-posts/dt-posts-hooks.php' );
        require_once( 'dt-posts/dt-posts-metrics.php' );
        Disciple_Tools_Posts_Endpoints::instance();
        new DT_Posts_Hooks();
        require_once( 'dt-posts/module-base.php' );

        /**
         * dt-contacts
         */
        require_once( 'dt-contacts/contacts.php' );

        require_once( 'dt-contacts/contacts-transfer.php' ); // Functions to support theme

        /**
         * dt-groups
         */
        require_once( 'dt-groups/groups.php' );
        /**
         * dt-mapping
         */
        require_once( 'dt-mapping/loader.php' );
        new DT_Mapping_Module_Loader();


        /**
         * dt-people-groups
         */
        require_once( 'dt-people-groups/people-groups.php' );
        require_once( 'dt-people-groups/people-groups-base.php' );
        new Disciple_Tools_People_Groups_Base();

        require_once( 'dt-people-groups/people-groups-post-type.php' );
        Disciple_Tools_People_Groups_Post_Type::instance();

        if ( strpos( $url_path, 'people-groups' ) !== false ){
            require_once( 'dt-people-groups/people-groups-endpoints.php' ); // builds rest endpoints
            Disciple_Tools_People_Groups_Endpoints::instance();
        }

        /**
         * dt-metrics
         */
        require_once( 'dt-metrics/counter.php' );
        require_once( 'dt-metrics/charts-base.php' );
        require_once( 'dt-metrics/metrics.php' );


        /**
         * dt-users
         */
        require_once( 'dt-users/users.php' );
        new Disciple_Tools_Users();
        require_once( 'dt-users/user-hooks-and-config.php' );
        new DT_User_Hooks_And_Configuration();
        require_once( 'dt-users/user-metrics.php' );
        new DT_User_Metrics();
        require_once( 'dt-users/users-template.php' );
        require_once( 'dt-users/users-endpoints.php' );
        new Disciple_Tools_Users_Endpoints();
        require_once( 'dt-users/user-management.php' );
        require_once( 'dt-users/user-initial-setup.php' );
        require_once( 'dt-users/hover-coverage-map.php' );
        require_once( 'dt-users/mapbox-coverage-map.php' );
        require_once( 'dt-users/template-no-permission.php' );



        /**
         * dt-notifications
         */
        require_once( 'dt-notifications/notifications-template.php' );
        require_once( 'dt-notifications/notifications.php' );
        Disciple_Tools_Notifications::instance();
        require_once( 'dt-notifications/notifications-endpoints.php' );
        Disciple_Tools_Notifications_Endpoints::instance();
        require_once( 'dt-notifications/notifications-email.php' ); // sends notification emails through the async task process
        require_once( 'dt-core/logging/usage.php' );

        /**
         * dt-notifications queue
         */
        require_once( 'dt-notifications/notifications-queue.php' );
        require_once( 'dt-notifications/notifications-scheduler.php' );
        new Disciple_Tools_Notifications_Scheduler( Disciple_Tools_Notifications::instance() );

        /**
         * dt-login
         */
        require_once( 'dt-login/login-methods.php' );
        require_once( 'dt-login/login-firebase-token.php' );
        require_once( 'dt-login/login-user-manager.php' );
        require_once( 'dt-login/login-fields.php' );
        require_once( 'dt-login/login-shortcodes.php' );
        require_once( 'dt-login/login-endpoints.php' );

        require_once( 'dt-login/pages/base.php' );
        require_once( 'dt-login/login-functions.php' );
        require_once( 'dt-login/login-email.php' );

        // pages
        require_once( 'dt-login/login-page.php' );
        require_once( 'dt-login/pages/privacy-policy.php' ); // {site}/privacy-policy
        require_once( 'dt-login/pages/terms-of-service.php' ); // {site}/terms-of-service
        //require_once( 'dt-login/pages/registration-holding.php' ); // {site}/reghold


        /**
         * Logging
         */
        require_once( 'dt-core/logging/class-activity-api.php' );
        $this->logging_activity_api = new Disciple_Tools_Activity_Log_API();
        require_once( 'dt-core/logging/class-activity-hooks.php' ); // contacts and groups report building
        Disciple_Tools_Activity_Hooks::instance();

        /**
         * Reports
         */
        require_once( 'dt-reports/reports.php' );

        /**
         * Workflows
         */
        require_once( 'dt-workflows/workflows.php' );
        Disciple_Tools_Workflows::instance();

        /**
         * dt-import
         */
        require_once( 'dt-import/dt-import.php' );
        DT_Theme_CSV_Import::instance();

        /**
         * core
         */
        require_once( 'dt-core/core-endpoints.php' );
        new Disciple_Tools_Core_Endpoints();
        require_once( 'dt-core/admin/admin-settings-endpoints.php' );
        new Disciple_Tools_Admin_Settings_Endpoints();
        require_once( 'dt-core/configuration/class-pwa.php' );
        new Disciple_Tools_PWA();

        /**
         * Admin panel
         * Contains all those features that only run if in the Admin panel
         * or those things directly supporting Admin panel features.
         */
        $disable = isset( $_POST['wppusher'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( !( is_multisite() && class_exists( 'DT_Multisite' ) ) && ( is_admin() || wp_doing_cron() ) && !$disable ){
            require( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
            $theme_folder_name = basename( dirname( __FILE__ ) );
            PucFactory::buildUpdateChecker(
                'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-version-control/master/disciple-tools-theme-version-control.json',
                __FILE__,
                $theme_folder_name
            );
        }
        if ( is_admin() ){

            // Administration
            require_once( 'dt-core/admin/admin-enqueue-scripts.php' ); // Load admin scripts
            require_once( 'dt-core/admin/admin-functions.php' ); // Load admin functions
            require_once( 'dt-core/admin/admin-theme-design.php' ); // Configures elements of the admin enviornment
            require_once( 'dt-core/admin/config-dashboard.php' );
            Disciple_Tools_Dashboard::instance();

            // Admin Menus
            /* Note: The load order matters for the menus and submenus. Submenu must load after menu. */
            require_once( 'dt-core/admin/menu/tabs/abstract-tabs-base.php' ); // registers all the menu pages and tabs
            require_once( 'dt-core/admin/menu/menu-settings.php' );
            require_once( 'dt-core/admin/menu/menu-setup-wizard.php' );

            require_once( 'dt-core/admin/menu/menu-extensions.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-featured-extensions.php' );

            require_once( 'dt-core/admin/menu/menu-utilities.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-people-groups.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-utilities-overview.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-fields.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-scripts.php' );

            require_once( 'dt-core/admin/menu/tabs/tab-gdpr.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-background-jobs.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-email-logs.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-error-logs.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-workflows.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-exports.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-imports.php' );

            require_once( 'dt-core/admin/menu/menu-metrics.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-metrics-reports.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-metrics-sources.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-metrics-edit.php' );

            require_once( 'dt-core/admin/menu/menu-customizations.php' );
            require_once( 'dt-core/admin/menu/tabs/tab-customizations.php' );
            /* End menu tab section */

            require_once( 'dt-core/setup-functions.php' );

        }
        require_once( 'dt-core/admin/menu/tabs/admin-endpoints.php' );

        //create scheduler for job queue
        wp_queue()->cron();

        /* End Admin configuration section */

        require_once( 'dt-core/dependencies/deprecated-dt-functions.php' );

        add_action( 'switch_blog', 'set_up_wpdb_tables', 99, 2 );
        add_action( 'wp_loaded', [ $this, 'dt_url_loader' ], 10000 );
        add_filter( 'locale', [ $this, 'dt_locale' ] );
        add_action( 'init', [ $this, 'migrations' ] );
        add_action( 'init', [ $this, 'setup_wizard' ] );
    } // End __construct()


    public function dt_url_loader(){
        $template_for_url = [
            'metrics'               => 'template-metrics.php',
            'settings'              => 'template-settings.php',
            'notifications'         => 'template-notifications.php',
            'view-duplicates'       => 'template-view-duplicates.php'
        ];

        $template_for_url = apply_filters( 'dt_templates_for_urls', $template_for_url );

        $url_path = untrailingslashit( dt_get_url_path( true ) ); //allow get parameters

        if ( isset( $template_for_url[ $url_path ] ) && dt_please_log_in() ) {
            $template_filename = locate_template( $template_for_url[ $url_path ], true );
            if ( $template_filename ) {
                exit(); // just exit if template was found and loaded
            } else {
                throw new Error( 'Expected to find template ' . $template_for_url[ $url_path ] );
            }
        }
    }

    /**
     * Set the locale for the user
     * must be loaded after most files
     *
     * @return string
     */
    public function dt_locale() {
        if ( is_admin() ) {
            return $this->site_locale;
        } else {
            return $this->user_locale;
        }
    }

    public function migrations(){
        /**
         * We want to make sure migrations are run on updates.
         *
         * @see https://www.sitepoint.com/wordpress-plugin-updates-right-way/
         */
        try {
            require_once( 'dt-core/configuration/class-migration-engine.php' );
            Disciple_Tools_Migration_Engine::migrate( Disciple_Tools_Migration_Engine::$migration_number );
        } catch ( Throwable $e ) {
            new WP_Error( 'migration_error', 'Migration engine failed to migrate.', [ 'message' => $e->getMessage() ] );
        }
    }

    public function setup_wizard(){
        $is_rest = dt_is_rest();

        /**
         * Redirect to setup wizard if not seen
         */
        $setup_wizard_completed = get_option( 'dt_setup_wizard_completed' );
        $setup_wizard_completed = apply_filters( 'dt_setup_wizard_completed', $setup_wizard_completed );
        $current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        $is_administrator = current_user_can( 'manage_options' );
        if ( !$is_rest && !is_network_admin() && !wp_doing_cron() && !$setup_wizard_completed && $is_administrator && $current_page !== 'dt_setup_wizard' ) {
            wp_redirect( admin_url( 'admin.php?page=dt_setup_wizard' ) );
        }
    }

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
    if ( current_user_can( 'access_disciple_tools' ) || current_user_can( 'access_contacts' ) ) {
        /**
         * Use this filter to add a new landing page for logged in users with 'access_contacts' capabilities
         */
        if ( current_user_can( 'access_contacts' ) ){
            wp_safe_redirect( apply_filters( 'dt_front_page', home_url( '/contacts' ) ) );
        } else {
            wp_safe_redirect( apply_filters( 'dt_front_page', home_url( '/settings' ) ) );
        }
    }
    else if ( ! is_user_logged_in() ) {
        dt_please_log_in();
    }
    else {
        /**
         * Use this filter to give a front page for logged in users who do not have basic 'access_contacts' capabilities
         * This is used for specific custom roles that are not intended to see the basic framework of DT.
         * Use this to create a dedicated landing page for partners, donors, or subscribers.
         */
        wp_safe_redirect( apply_filters( 'dt_non_standard_front_page', home_url( '/registered' ) ) );
    }
}
function set_up_wpdb_tables(){
    global $wpdb;
    $wpdb->dt_activity_log = $wpdb->prefix . 'dt_activity_log'; // Prepare database table names
    $wpdb->dt_reports = $wpdb->prefix . 'dt_reports';
    $wpdb->dt_reportmeta = $wpdb->prefix . 'dt_reportmeta';
    $wpdb->dt_share = $wpdb->prefix . 'dt_share';
    $wpdb->dt_notifications = $wpdb->prefix . 'dt_notifications';
    $wpdb->dt_notifications_queue = $wpdb->prefix . 'dt_notifications_queue';
    $wpdb->dt_post_user_meta = $wpdb->prefix . 'dt_post_user_meta';
    $wpdb->dt_location_grid = apply_filters( 'dt_location_grid_table', $wpdb->prefix . 'dt_location_grid' );
    $wpdb->dt_location_grid_meta = $wpdb->prefix . 'dt_location_grid_meta';

    $more_tables = apply_filters( 'dt_custom_tables', [] );
    foreach ( $more_tables as $table ){
        $wpdb->$table = $wpdb->prefix . $table;
    }
}

/**
 * Intended to avoid restrictions with passing null-containing strings to bcrypt functions like password_hash.
 *
 * In particular, prevents the error "Bcrypt password must not contain null character" when passing output of
 * random_bytes directly to password_hash.
 *
 * @param int $length
 * @return string
 *
 * @since 1.67.0
 */
function random_bytes_no_null( int $length ): string {
    $str = '';
    while ( true ) {
        $str .= str_replace( "\0", '', random_bytes( $length ) );
        if ( strlen( $str ) >= $length ) {
            break;
        }
    }

    return substr( $str, 0, $length );
}

/** Setup key for JWT authentication */
if ( !defined( 'JWT_AUTH_SECRET_KEY' ) ) {
    if ( get_option( 'my_jwt_key' ) ) {
        define( 'JWT_AUTH_SECRET_KEY', get_option( 'my_jwt_key' ) );
    } else {
        try {
            $iv = password_hash( random_bytes_no_null( 16 ), PASSWORD_DEFAULT );
            update_option( 'my_jwt_key', $iv );
            define( 'JWT_AUTH_SECRET_KEY', $iv );
        } catch ( Exception $e ) {
            dt_write_log( $e->getMessage() );
        }
    }
}

/**
 * Mobile Detection and Tailwind Integration
 * Added for enhanced mobile UI experience
 */

/**
 * Detect if request is from mobile device or responsive design testing mode
 *
 * @return bool
 */
function dt_is_mobile_request() {
    // Check for responsive design testing mode
    if ( isset( $_GET['mobile'] ) || isset( $_GET['responsive'] ) ) {
        return true;
    }
    
    // User agent detection
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $mobile_agents = [
        'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 
        'BlackBerry', 'Windows Phone', 'Opera Mini'
    ];
    
    foreach ( $mobile_agents as $agent ) {
        if ( stripos( $user_agent, $agent ) !== false ) {
            return true;
        }
    }
    
    // WordPress built-in mobile detection
    if ( function_exists( 'wp_is_mobile' ) && wp_is_mobile() ) {
        return true;
    }
    
    return false;
}

/**
 * Enqueue Tailwind CSS and mobile-specific assets
 */
function dt_enqueue_mobile_assets() {
    if ( dt_is_mobile_request() ) {
        // Add critical mobile CSS inline to prevent FOUC
        add_action( 'wp_head', 'dt_mobile_critical_css', 1 );
        
        // Enqueue mobile-specific styles (critical CSS is inline)
        wp_enqueue_style(
            'dt-mobile-header',
            get_template_directory_uri() . '/dt-assets/css/mobile-header.css',
            array(),
            filemtime( get_template_directory() . '/dt-assets/css/mobile-header.css' )
        );
        
        // Enqueue mobile-specific JavaScript with CSS loading detection
        wp_enqueue_script(
            'dt-mobile-header-js',
            get_template_directory_uri() . '/dt-assets/js/mobile-header.js',
            array( 'jquery' ),
            filemtime( get_template_directory() . '/dt-assets/js/mobile-header.js' ),
            true
        );
        
        // Enqueue mobile footer JavaScript
        wp_enqueue_script(
            'dt-mobile-footer-js',
            get_template_directory_uri() . '/dt-assets/js/mobile-footer.js',
            array( 'jquery' ),
            filemtime( get_template_directory() . '/dt-assets/js/mobile-footer.js' ),
            true
        );
        
        // Localize script with search settings and CSS loading detection
        wp_localize_script( 'dt-mobile-header-js', 'dt_mobile_header', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'rest_base' => esc_url_raw( rest_url() . 'dt/v1/' ),
            'search_placeholder' => __( 'Search contacts, groups...', 'disciple_tools' ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'css_loaded' => false, // Will be set to true once CSS is confirmed loaded
        ) );
        
        // Localize mobile footer script with translations and settings
        wp_localize_script( 'dt-mobile-footer-js', 'dt_mobile_footer', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'rest_base' => esc_url_raw( rest_url() . 'dt/v1/' ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'current_post_type' => get_post_type(),
            'translations' => array(
                'filters' => __( 'Filters', 'disciple_tools' ),
                'split_by' => __( 'Split By', 'disciple_tools' ),
                'exports' => __( 'Exports', 'disciple_tools' ),
                'add_new' => __( 'Add New', 'disciple_tools' ),
                'close' => __( 'Close', 'disciple_tools' ),
                'go' => __( 'Go', 'disciple_tools' ),
                'please_select_field' => __( 'Please select a field to split by', 'disciple_tools' ),
                'no_filters_available' => __( 'No filters available for this view', 'disciple_tools' ),
            )
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'dt_enqueue_mobile_assets' );

/**
 * Add mobile detection body class
 */
function dt_mobile_body_class( $classes ) {
    if ( dt_is_mobile_request() ) {
        $classes[] = 'dt-mobile-view';
        
        // Add class to indicate mobile footer is present for CSS adjustments
        $current_page = get_query_var( 'post_type', 'contacts' );
        if ( is_archive() || is_post_type_archive() ) {
            $classes[] = 'has-mobile-footer';
        }
    }
    return $classes;
}
add_filter( 'body_class', 'dt_mobile_body_class' );

/**
 * Add critical mobile CSS inline to prevent FOUC
 */
function dt_mobile_critical_css() {
    ?>
    <style id="dt-mobile-critical-css">
    /* Critical CSS for mobile header to prevent FOUC */
    .dt-mobile-header {
        background-color: #2563eb; /* bg-blue-600 */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        position: sticky;
        top: 0;
        z-index: 50;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    }
    
    .dt-mobile-header.css-loaded {
        opacity: 1;
    }
    
    .dt-mobile-header .flex {
        display: flex;
    }
    
    .dt-mobile-header .items-center {
        align-items: center;
    }
    
    .dt-mobile-header .justify-between {
        justify-content: space-between;
    }
    
    .dt-mobile-header .px-4 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .dt-mobile-header .py-3 {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
    
    .dt-mobile-header .w-10 {
        width: 2.5rem;
    }
    
    .dt-mobile-header .h-10 {
        height: 2.5rem;
    }
    
    .dt-mobile-header .w-8 {
        width: 2rem;
    }
    
    .dt-mobile-header .h-8 {
        height: 2rem;
    }
    
    .dt-mobile-header .rounded-md {
        border-radius: 0.375rem;
    }
    
    .dt-mobile-header .rounded-full {
        border-radius: 9999px;
    }
    
    .dt-mobile-header .text-white {
        color: #ffffff;
    }
    
    .dt-mobile-header .bg-blue-700 {
        background-color: #1d4ed8;
    }
    
    .dt-mobile-header .bg-blue-800 {
        background-color: #1e40af;
    }
    
    .dt-mobile-header .hover\:bg-blue-700:hover {
        background-color: #1d4ed8;
    }
    
    .dt-mobile-header .hover\:bg-blue-800:hover {
        background-color: #1e40af;
    }
    
    .dt-mobile-header .transition-colors {
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, color 0.15s ease-in-out;
    }
    
    .dt-mobile-header .hidden {
        display: none;
    }
    
    /* Loading spinner for graceful loading */
    .dt-mobile-header-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #2563eb;
        height: 4rem;
        position: sticky;
        top: 0;
        z-index: 50;
    }
    
    .dt-mobile-header-loading .spinner {
        width: 1.5rem;
        height: 1.5rem;
        border: 2px solid #ffffff;
        border-top: 2px solid transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    /* Prevent flash of unstyled content */
    body.dt-mobile-view:not(.css-loaded) .dt-mobile-header {
        opacity: 0;
    }
    
    body.dt-mobile-view.css-loaded .dt-mobile-header {
        opacity: 1;
    }
    </style>
    <?php
}

/**
 * AJAX handler for mobile global search
 */
function dt_mobile_global_search() {
    // Verify nonce
    if ( ! wp_verify_nonce( $_POST['nonce'], 'wp_rest' ) ) {
        wp_die( 'Security check failed' );
    }
    
    // Check if user is logged in
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'User not authenticated' );
        return;
    }
    
    $query = sanitize_text_field( $_POST['query'] ?? '' );
    
    if ( empty( $query ) || strlen( $query ) < 2 ) {
        wp_send_json_error( 'Query too short' );
        return;
    }
    
    $results = [];
    
    // Search contacts
    if ( current_user_can( 'access_contacts' ) ) {
        $contacts = dt_search_posts( 'contacts', $query, 5 );
        if ( ! is_wp_error( $contacts ) && isset( $contacts['posts'] ) ) {
            foreach ( $contacts['posts'] as $contact ) {
                $results[] = [
                    'id' => $contact['ID'],
                    'title' => $contact['name'] ?? $contact['post_title'],
                    'post_type' => 'contacts',
                    'post_type_label' => __( 'Contact', 'disciple_tools' ),
                    'status' => $contact['overall_status']['label'] ?? '',
                ];
            }
        }
    }
    
    // Search groups
    if ( current_user_can( 'access_groups' ) ) {
        $groups = dt_search_posts( 'groups', $query, 5 );
        if ( ! is_wp_error( $groups ) && isset( $groups['posts'] ) ) {
            foreach ( $groups['posts'] as $group ) {
                $results[] = [
                    'id' => $group['ID'],
                    'title' => $group['name'] ?? $group['post_title'],
                    'post_type' => 'groups',
                    'post_type_label' => __( 'Group', 'disciple_tools' ),
                    'status' => $group['group_status']['label'] ?? '',
                ];
            }
        }
    }
    
    // Limit total results
    $results = array_slice( $results, 0, 10 );
    
    wp_send_json_success( $results );
}
add_action( 'wp_ajax_dt_mobile_global_search', 'dt_mobile_global_search' );

/**
 * Fallback search function if dt_search_posts doesn't exist
 */
function dt_search_posts( $post_type, $query, $limit = 10 ) {
    if ( function_exists( 'DT_Posts::search' ) ) {
        return DT_Posts::search( $post_type, [ 'text' => $query ], false );
    }
    
    // Fallback to WordPress search
    $search_args = [
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        's' => $query,
        'meta_query' => [
            [
                'key' => '_wp_trash_meta_status',
                'compare' => 'NOT EXISTS'
            ]
        ]
    ];
    
    $posts = get_posts( $search_args );
    
    $formatted_posts = [];
    foreach ( $posts as $post ) {
        $formatted_posts[] = [
            'ID' => $post->ID,
            'post_title' => $post->post_title,
            'name' => get_the_title( $post->ID ),
        ];
    }
    
    return [ 'posts' => $formatted_posts ];
}

/**
 * Returns the main instance of Disciple_Tools to prevent the need to use globals.
 *
 * @since  0.1.0
 * @return object Disciple_Tools
 */
function disciple_tools() {
    return Disciple_Tools::instance();
}


add_action( 'after_setup_theme', function(){

    /**
     * Load the Disciple Tools Theme
     */
    disciple_tools();

    /**
     * Load Language Files
     */
    load_theme_textdomain( 'disciple_tools', get_template_directory() . '/dt-assets/translation' );
}, 5 );

/**
 * The disciple_tools_load_plugins hook
 * Set up hook for loading plugins
 */
add_action( 'after_setup_theme', function (){
    do_action( 'disciple_tools_load_plugins' );
}, 30 );

/**
 * The disciple_tools_loaded hook
 * Disciple.Tools theme and plugins are loaded.
 * It is now safe to us the Disciple.Tools API, run actions and views
 */
add_action( 'init', function (){
    do_action( 'disciple_tools_loaded' );
}, 100 );
