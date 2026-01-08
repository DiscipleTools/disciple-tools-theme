<?php
/**
 * Launcher Wrapper Page
 *
 * This template is used when launcher=1 parameter is present in the URL.
 * It displays the launcher bottom navigation and loads the target app in an iframe.
 *
 * @var string $target_app_url The URL of the target app (without launcher parameter)
 * @var array $apps Array of all apps for the apps selector
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Include helper functions
require_once get_template_directory() . '/dt-apps/dt-home/includes/class-home-helpers.php';

// Get all apps for the apps selector
$apps_manager = DT_Home_Apps::instance();
$apps = $apps_manager->get_apps_for_user( get_current_user_id() );

// Mark iframe requests so nested pages can detect they are inside the launcher
$iframe_target_app_url = add_query_arg( 'launcher_iframe', '1', $target_app_url );

// Enqueue stylesheets properly
wp_enqueue_style( 'material-font-icons-css', 'https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css', [], '7.4.47' );
wp_enqueue_style( 'dt-home-style', get_template_directory_uri() . '/dt-apps/dt-home/assets/css/home-screen.css', [], '1.1' );

wp_enqueue_script( 'dt-home-launcher', get_template_directory_uri() . '/dt-apps/dt-home/assets/js/launcher.js', [], '1.0.0' );

add_filter( 'dt_magic_url_base_allowed_js', function ( $allowed_js ) {
        $allowed_js = [];
        $allowed_js[] = 'dt-home-launcher';

        return $allowed_js;
}, 10, 1 );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e( 'App Launcher', 'disciple_tools' ); ?></title>
    <?php
    wp_head();
    // Explicitly print stylesheets and scripts for standalone template
    wp_print_styles( 'material-font-icons-css' );
    wp_print_styles( 'dt-home-style' );
    ?>

</head>
<body class="launcher">
    <div class="launcher-wrapper">
        <div class="launcher-iframe-container">
            <iframe
                id="launcher-app-iframe"
                src="<?php echo esc_url( $iframe_target_app_url ); ?>"
                title="<?php esc_attr_e( 'App Content', 'disciple_tools' ); ?>"
                allowfullscreen>
            </iframe>
        </div>
        <?php
        // Include launcher bottom nav with wrapper context
        // Set a flag to indicate we're in wrapper context
        $is_wrapper_context = true;
        $partial_path = get_template_directory() . '/dt-apps/dt-home/frontend/partials/launcher-bottom-nav.php';
        if ( file_exists( $partial_path ) ) {
            include $partial_path;
        }
        ?>
    </div>
</body>
</html>

