<?php
declare(strict_types=1);

/**
 * Disciple Tools Themes Function.php
 * @package Disciple Tools
 */

/**
 * Php Version Alert
 */
function dt_theme_admin_notice_required_php_version() {
    ?>
    <div class="notice notice-error">
        <p><?php esc_html_e( "The Disciple Tools theme requires PHP 7.0 or greater before it will have any effect. Please upgrade your PHP version or uninstall this theme.", "disciple_tools" ); ?></p>
    </div>
    <?php
}

/**
 * Error handler for PHP version fail
 * @return bool
 */
function dt_theme_after_switch_theme_switch_back() {
    switch_theme( get_option( 'theme_switched' ) );
    return false;
}

/**
 * Test for minimum required PHP version
 */
if (version_compare( phpversion(), '7.0', '<' )) {

    /* We only support PHP >= 7.0, however, we want to support allowing users
     * to install this theme even on old versions of PHP, without showing a
     * horrible message, but instead a friendly notice.
     *
     * For this to work, this file must be compatible with old PHP versions.
     * Feel free to use PHP 7 features in other files, but not in this one.
     */

    add_action( 'admin_notices', 'dt_theme_admin_notice_required_php_version' );
    error_log( 'Disciple Tools theme requires PHP version 7.0 or greater, please upgrade PHP or uninstall this theme' );
    add_action( 'after_switch_theme', 'dt_theme_after_switch_theme_switch_back' );
    return;
}

/**
 * Globals
 */

/* TODO: I don't think this is necessary, and it may break some things. We
 * should investigate and possibly remove it. In the meantime, ignore the PHPCS
 * errors.
 */

// @codingStandardsIgnoreStart

if ( !defined( 'WP_CONTENT_URL' ) ) {
    define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
}

if ( !defined( 'WP_CONTENT_DIR' ) ) {
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}

if ( !defined( 'WP_PLUGIN_URL' ) ) {
    define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
}

if ( !defined( 'WP_PLUGIN_DIR' ) ) {
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}

if ( !defined( 'WP_LANG_DIR' ) ) {
    define( 'WP_LANG_DIR', WP_CONTENT_DIR . '/languages' );
}

if ( !defined( 'DISCIPLE_TOOLS_DIR' ) ) {
    define( 'DISCIPLE_TOOLS_DIR', WP_PLUGIN_DIR . '/disciple-tools' );
}

// @codingStandardsIgnoreEnd

// Removes the admin bar
add_filter( 'show_admin_bar', '__return_false' );


/**
 * Disciple_Tools_Theme Classes
 *
 * @class Disciple_Tools_Theme
 * @version    0.1
 * @since 0.1
 * @package    Disciple_Tools_Theme
 * @author Chasm.Solutions & Kingdom.Training
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Theme
 */
class Disciple_Tools_Theme {

    /**
     * Disciple_Tools_Theme The single instance of Disciple_Tools_Theme.
     * @var     object
     * @access  private
     * @since     0.1
     */
    private static $_instance = null;

    /**
     * Main Disciple_Tools_Theme Instance
     *
     * Ensures only one instance of Disciple_Tools_Admin_Menus is loaded or can be loaded.
     *
     * @since 0.1
     * @static
     * @return Disciple_Tools_Theme instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     * @access  public
     * @since   0.1
     */
    public function __construct() {

        // Foundations theme configurations
        require_once( get_template_directory().'/assets/functions/theme-support.php' ); // Theme support options
        require_once( get_template_directory().'/assets/functions/cleanup.php' ); // WP Head and other cleanup functions
        require_once( get_template_directory().'/assets/functions/enqueue-scripts.php' ); // Register scripts and stylesheets
        require_once( get_template_directory().'/assets/functions/sidebar.php' ); // Register sidebars/widget areas
        require_once( get_template_directory().'/assets/functions/comments.php' ); // Makes WordPress comments suck less
        require_once( get_template_directory().'/assets/functions/page-navi.php' ); // Replace 'older/newer' post links with numbered navigation
        require_once( get_template_directory().'/assets/translation/translation.php' ); // Adds support for multiple languages

        // Adds Disciple Tools Theme General Functions
        require_once( get_template_directory().'/assets/functions/private-site.php' ); // Sets site to private
        require_once( get_template_directory().'/assets/functions/login.php' ); // Customize the WordPress login menu
        require_once( get_template_directory().'/assets/functions/menu.php' ); // Register menus and menu walkers
        require_once( get_template_directory().'/assets/functions/breadcrumbs.php' ); // Breadcrumbs bar

        // Adds Page Specific Scripts
        require_once( get_template_directory().'/assets/functions/page-front-page.php' );

        // Load plugin library that "requires plugins" at activation
        require_once( get_template_directory().'/update/config-required-plugins.php' );
        require_once( get_template_directory().'/update/class-tgm-plugin-activation.php' );


        // Catch `metrics` URL and load metrics template.
        add_action('init', function() {
            $template_for_url = array(
                'metrics' => 'template-metrics.php',
                'settings' => 'template-settings.php',
                'notifications' => 'template-notifications.php',
                'about' => 'template-about.php',
                'team' => 'template-team.php',
                'contacts/new' => 'template-contacts-new.php',
                'groups/new' => 'template-groups-new.php',
            );
            $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );

            if ( isset( $template_for_url[ $url_path ] ) ) {
                $template_filename = locate_template( $template_for_url[ $url_path ], true );
                if ( $template_filename ) {
                    exit(); // just exit if template was found and loaded
                } else {
                    throw new Error( "Expected to find template " . $template_for_url[ $url_path ] );
                }
            }

        });

        // Checker for new version and updater service.
        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
            require 'update/plugin-update-checker/plugin-update-checker.php';
        }
        $my_update_checker = Puc_v4_Factory::buildUpdateChecker(
            'https://raw.githubusercontent.com/DiscipleTools/disciple-tools-version-control/master/disciple-tools-theme-version-control.json',
            __FILE__,
            'disciple-tools-theme'
        );


    } // End __construct()

}

/**
 * Gets the instance of the `dt_sample_data` class.  This function is useful for quickly grabbing data
 * used throughout the plugin.
 *
 * @since  0.1
 * @access public
 * @return object
    */
function dt_theme() {
    return Disciple_Tools_Theme::instance();
}

// Let's roll!
//add_action( 'after_setup_theme', 'dt_theme' );
dt_theme();
