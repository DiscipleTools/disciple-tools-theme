<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$user = wp_get_current_user();
$settings = get_option( 'dt_home_screen_settings', [
    'title' => __( 'Training', 'disciple_tools' ),
    'description' => __( 'Watch training videos and tutorials.', 'disciple_tools' ),
] );
$settings = wp_parse_args( $settings, [
    'title' => __( 'Training', 'disciple_tools' ),
    'description' => __( 'Watch training videos and tutorials.', 'disciple_tools' ),
] );
?>

<div id="wrapper">
    <div class="training-screen-container home-screen-container">
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
                            <div class="floating-menu" id="floating-menu"></div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Main Content -->
        <div class="home-screen-content">
            <div class="training-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="mdi mdi-play-circle" style="margin-right: 0.5rem;"></i>
                        <?php esc_html_e( 'Training Videos', 'disciple_tools' ); ?>
                    </h2>
                </div>
                <div class="section-content expanded" id="training-content">
                    <div class="training-grid" id="training-grid">
                        <div class="loading-card">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

