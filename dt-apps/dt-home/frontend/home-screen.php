<?php
/**
 * Home Screen Dashboard Template
 *
 * Main frontend template for the Home Screen magic link app.
 * Displays a personalized dashboard with apps and training videos.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$user = wp_get_current_user();

// Get admin settings
$settings = get_option( 'dt_home_screen_settings', [
    'title' => __( 'Welcome to your Home Screen', 'disciple_tools' ),
    'description' => __( 'Your personalized dashboard for apps and training.', 'disciple_tools' ),
] );

// Ensure we have all settings with defaults
$settings = wp_parse_args( $settings, [
    'title' => __( 'Welcome to your Home Screen', 'disciple_tools' ),
    'description' => __( 'Your personalized dashboard for apps and training.', 'disciple_tools' ),
] );
?>

<div id="custom-style"></div>
<style>
/* CSS Variables for theme-aware app cards - Keep inline for early initialization */
:root {
    --app-card-bg: #ffffff;
    --app-card-border: #e1e5e9;
    --app-card-text: #0a0a0a;
    --app-card-shadow: rgba(0,0,0,0.1);
    --app-card-hover-border: #667eea;
}

/* Support theme class on html element for early initialization */
html.theme-dark,
.theme-dark {
    --app-card-bg: #2a2a2a;
    --app-card-border: #404040;
    --app-card-text: #f5f5f5;
    --app-card-shadow: rgba(0,0,0,0.3);
    --app-card-hover-border: #4a9eff;
}

body.theme-dark,
html.theme-dark body {
    --app-card-bg: #2a2a2a;
    --app-card-border: #404040;
    --app-card-text: #f5f5f5;
    --app-card-shadow: rgba(0,0,0,0.3);
    --app-card-hover-border: #4a9eff;
}

/* Note: Component styles are now in home-screen.css for better maintainability */

/* Note: Training card, grid, and container styles are now in home-screen.css for better maintainability */
</style>
<div id="wrapper">
    <div class="home-screen-container">
        <!-- Header Section -->
        <div class="home-screen-header">
            <table class="header-table">
                <tr>
                    <td class="header-content-cell">
                        <h1><?php echo esc_html( $settings['title'] ); ?></h1>
                        <p><?php echo esc_html( $settings['description'] ); ?></p>
                    </td>
                    <td class="header-controls-cell">
                        <div class="header-controls">
                            <!-- Theme toggle will be added here by JavaScript -->
                            <button type="button" class="menu-toggle-button" id="menu-toggle-button" aria-label="<?php esc_attr_e( 'Toggle menu', 'disciple_tools' ); ?>" title="<?php esc_attr_e( 'Toggle menu', 'disciple_tools' ); ?>">
                                <i class="mdi mdi-menu dt-menu-icon" id="menu-icon"></i>
                            </button>
                            <div class="floating-menu" id="floating-menu">
                                <!-- Menu items will be added here later -->
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Main Content -->
        <div class="home-screen-content">
            <!-- Apps Section -->
            <div class="apps-section">
                <h2 class="section-title">
                    <i class="mdi mdi-apps" style="margin-right: 0.5rem;"></i>
                    <?php esc_html_e( 'Your Apps', 'disciple_tools' ); ?>
                </h2>
                
                <div class="apps-grid" id="apps-grid">
                    <!-- Apps will be loaded dynamically -->
                    <div class="loading-card">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
            </div>

            <!-- Training Videos Section removed: now lives on separate view (?view=training) -->

            <!-- Your Links Section -->
            <div class="links-section">
                <h2 class="section-title">
                    <i class="mdi mdi-link" style="margin-right: 0.5rem;"></i>
                    <?php esc_html_e( 'Your Links', 'disciple_tools' ); ?>
                </h2>
                
                <div class="links-list" id="links-list">
                    <!-- Links will be loaded dynamically -->
                    <div class="loading-card">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Error Messages -->
<div id="error" style="display: none; color: #dc3545; background: #f8d7da; padding: 1rem; margin: 1rem 0; border-radius: 4px; border: 1px solid #f5c6cb;"></div>

<!-- Success Messages -->
<div id="success" style="display: none; color: #155724; background: #d4edda; padding: 1rem; margin: 1rem 0; border-radius: 4px; border: 1px solid #c3e6cb;"></div>
