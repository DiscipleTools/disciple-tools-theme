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

// Enqueue stylesheets properly
wp_enqueue_style( 'material-font-icons-css', 'https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css', [], '7.4.47' );
wp_enqueue_style( 'dt-home-style', get_template_directory_uri() . '/dt-apps/dt-home/assets/css/home-screen.css', [], '1.0.3' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e( 'App Launcher', 'disciple_tools' ); ?></title>
    <?php
    wp_head();
    // Explicitly print stylesheets for standalone template
    // Use wp_styles()->do_items() to ensure stylesheets are output
    global $wp_styles;
    if ( isset( $wp_styles ) ) {
        $wp_styles->do_items( 'material-font-icons-css' );
        $wp_styles->do_items( 'dt-home-style' );
    }
    ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        
        .launcher-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100%;
            position: relative;
        }
        
        .launcher-iframe-container {
            flex: 1;
            width: 100%;
            overflow: hidden;
            position: relative;
            padding-bottom: var(--launcher-bottom-nav-height, 70px);
        }
        
        .launcher-iframe-container iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }
    </style>
</head>
<body>
    <div class="launcher-wrapper">
        <div class="launcher-iframe-container">
            <iframe 
                id="launcher-app-iframe" 
                src="<?php echo esc_url( $target_app_url ); ?>" 
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

