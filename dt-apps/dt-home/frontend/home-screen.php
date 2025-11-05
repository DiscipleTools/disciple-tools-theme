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
$user_name = $user->display_name ?: $user->user_login;

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
/* CSS Variables for theme-aware app cards */
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

/* Balanced iPhone-style app cards */
.app-card {
    background: var(--app-card-bg, #ffffff) !important;
    border: 1px solid var(--app-card-border, #e1e5e9) !important;
    color: var(--app-card-text, #0a0a0a) !important;
    border-radius: 16px !important;
    padding: 0.5rem !important;
    text-align: center !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    text-decoration: none !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    width: 70px !important;
    height: 70px !important;
    max-width: 70px !important;
    max-height: 70px !important;
    min-height: 70px !important;
    aspect-ratio: 1 !important;
    box-shadow: 0 1px 3px var(--app-card-shadow, rgba(0,0,0,0.1)) !important;
}

/* Dark mode styles for app cards (backup for theme-dark class) */
.theme-dark .app-card,
body.theme-dark .app-card {
    background: var(--app-card-bg, #2a2a2a) !important;
    border-color: var(--app-card-border, #404040) !important;
    color: var(--app-card-text, #f5f5f5) !important;
    box-shadow: 0 1px 3px var(--app-card-shadow, rgba(0,0,0,0.3)) !important;
}

.app-icon {
    font-size: 1.4rem !important;
    margin-bottom: 0.2rem !important;
    color: #667eea;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 32px !important;
    height: 32px !important;
    border-radius: 8px !important;
    background: rgba(102, 126, 234, 0.1) !important;
    transition: all 0.2s ease !important;
}

.app-title {
    font-size: 0.65rem !important;
    font-weight: 500 !important;
    margin-bottom: 0 !important;
    color: #2c3e50 !important;
    line-height: 1.1 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    max-width: 100% !important;
    display: block !important;
    text-align: center !important;
}

/* Dark mode styles for app titles */
.theme-dark .app-title,
body.theme-dark .app-title {
    color: #f5f5f5 !important;
}

.apps-grid {
    display: grid !important;
    grid-template-columns: repeat(6, 1fr) !important;
    gap: 0.8rem 0.1rem !important; /* Row gap 0.8rem, Column gap 0.1rem */
    margin-bottom: 2rem !important;
    max-width: none !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    justify-content: start !important;
}

@media (max-width: 1200px) {
    .apps-grid {
        grid-template-columns: repeat(5, 1fr) !important;
    }
}

@media (max-width: 768px) {
    .apps-grid {
        grid-template-columns: repeat(4, 1fr) !important;
    }
}

@media (max-width: 480px) {
    .apps-grid {
        grid-template-columns: repeat(3, 1fr) !important;
    }
}

/* Training Video Cards - Larger sizing for video content */
.training-card {
    background: white !important;
    border: 1px solid #e1e5e9 !important;
    border-radius: 16px !important;
    padding: 0.5rem !important;
    text-align: center !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    text-decoration: none !important;
    color: inherit !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    width: 200px !important;
    height: 150px !important;
    max-width: 200px !important;
    max-height: 150px !important;
    min-height: 150px !important;
    aspect-ratio: 4/3 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
    position: relative !important;
    overflow: hidden !important;
}

.training-card:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
}

.training-video-thumbnail {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    border-radius: 12px !important;
    position: relative !important;
}

.training-video-overlay {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    background: rgba(0, 0, 0, 0.3) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 12px !important;
    transition: background 0.2s ease !important;
}

.training-card:hover .training-video-overlay {
    background: rgba(0, 0, 0, 0.5) !important;
}

.training-play-button {
    width: 48px !important;
    height: 48px !important;
    background: rgba(255, 255, 255, 0.9) !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: #667eea !important;
    font-size: 1.5rem !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
}

.training-card:hover .training-play-button {
    transform: scale(1.1) !important;
    background: white !important;
}

.training-video-duration {
    position: absolute !important;
    bottom: 8px !important;
    right: 8px !important;
    background: rgba(0, 0, 0, 0.8) !important;
    color: white !important;
    padding: 2px 6px !important;
    border-radius: 4px !important;
    font-size: 0.7rem !important;
    font-weight: 500 !important;
}

.training-video-title {
    position: absolute !important;
    bottom: 0 !important;
    left: 0 !important;
    right: 0 !important;
    background: linear-gradient(transparent, rgba(0,0,0,0.8)) !important;
    color: white !important;
    padding: 1rem 0.5rem 0.5rem !important;
    font-size: 0.8rem !important;
    font-weight: 500 !important;
    text-align: center !important;
    line-height: 1.2 !important;
}

.training-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
    gap: 1.5rem !important;
    margin-bottom: 2rem !important;
    max-width: none !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
    justify-content: start !important;
}

@media (max-width: 768px) {
    .training-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) !important;
        gap: 1rem !important;
    }
    
    .training-card {
        width: 180px !important;
        height: 135px !important;
        max-width: 180px !important;
        max-height: 135px !important;
        min-height: 135px !important;
    }
}

@media (max-width: 480px) {
    .training-grid {
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)) !important;
    }
    
    .training-card {
        width: 160px !important;
        height: 120px !important;
        max-width: 160px !important;
        max-height: 120px !important;
        min-height: 120px !important;
    }
}

/* Training Video Embedded Player */
.training-video-thumbnail-container {
    position: relative !important;
    width: 100% !important;
    height: 100% !important;
}

.training-video-embed {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    border-radius: 12px !important;
    overflow: hidden !important;
}

.training-video-embed iframe {
    width: 100% !important;
    height: 100% !important;
    border: none !important;
    border-radius: 12px !important;
}

/* Enhanced training card for video playback */
.training-card.playing {
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3) !important;
    border-color: #667eea !important;
}

.training-card.playing .training-video-title {
    background: linear-gradient(transparent, rgba(102, 126, 234, 0.8)) !important;
    color: white !important;
}

/* External Link Button for Embedded Videos */
.training-video-external-link {
    position: absolute !important;
    top: 8px !important;
    right: 8px !important;
    z-index: 10 !important;
}

.external-link-button {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.25rem !important;
    background: rgba(0, 0, 0, 0.8) !important;
    color: white !important;
    padding: 0.25rem 0.5rem !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    font-size: 0.7rem !important;
    font-weight: 500 !important;
    transition: all 0.2s ease !important;
    backdrop-filter: blur(4px) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
}

.external-link-button:hover {
    background: rgba(0, 0, 0, 0.9) !important;
    color: white !important;
    text-decoration: none !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
}

.external-link-button i {
    font-size: 0.8rem !important;
}

/* Make the external link more prominent on smaller screens */
@media (max-width: 768px) {
    .training-video-external-link {
        top: 4px !important;
        right: 4px !important;
    }
    
    .external-link-button {
        padding: 0.2rem 0.4rem !important;
        font-size: 0.65rem !important;
    }
    
    .external-link-button span {
        display: none !important;
    }
    
    .external-link-button i {
        font-size: 0.9rem !important;
    }
}
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
            <div class="apps-section collapsible-section">
                <div class="section-header" onclick="toggleSection('apps')">
                    <h2 class="section-title">
                        <i class="mdi mdi-apps" style="margin-right: 0.5rem;"></i>
                        <?php esc_html_e( 'Your Apps', 'disciple_tools' ); ?>
                        <span class="section-toggle" id="apps-toggle">
                            <i class="mdi mdi-chevron-down"></i>
                        </span>
                    </h2>
                </div>
                
                <div class="section-content" id="apps-content">
                    <div class="apps-grid" id="apps-grid">
                        <!-- Apps will be loaded dynamically -->
                        <div class="app-card loading-card">
                            <div class="loading-spinner"></div>
                            <p><?php esc_html_e( 'Loading apps...', 'disciple_tools' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Training Videos Section removed: now lives on separate view (?view=training) -->

            <!-- Your Links Section -->
            <div class="links-section collapsible-section">
                <div class="section-header" onclick="toggleSection('links')">
                    <h2 class="section-title">
                        <i class="mdi mdi-link" style="margin-right: 0.5rem;"></i>
                        <?php esc_html_e( 'Your Links', 'disciple_tools' ); ?>
                        <span class="section-toggle" id="links-toggle">
                            <i class="mdi mdi-chevron-right"></i>
                        </span>
                    </h2>
                </div>
                
                <div class="section-content collapsed" id="links-content">
                    <div class="links-list" id="links-list">
                        <!-- Links will be loaded dynamically -->
                        <div class="loading-spinner" style="text-align: center; padding: 2rem;">
                            <p><?php esc_html_e( 'Loading links...', 'disciple_tools' ); ?></p>
                        </div>
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

<script>
/**
 * Training Video Management
 * Handles video embedding, thumbnails, and click-through functionality
 */

// Function to extract YouTube video ID from URL
function getYouTubeVideoId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
}

// Function to extract Vimeo video ID from URL
function getVimeoVideoId(url) {
    const regExp = /vimeo\.com\/(\d+)/;
    const match = url.match(regExp);
    return match ? match[1] : null;
}

// Function to get video thumbnail URL
function getVideoThumbnail(videoUrl, type = 'youtube') {
    if (type === 'youtube') {
        const videoId = getYouTubeVideoId(videoUrl);
        if (videoId) {
            return `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
        }
    } else if (type === 'vimeo') {
        const videoId = getVimeoVideoId(videoUrl);
        if (videoId) {
            // Note: Vimeo requires API call for thumbnails, using placeholder for now
            return `https://vumbnail.com/${videoId}.jpg`;
        }
    }
    return null;
}

// Function to create training video card HTML
function createTrainingVideoCard(video) {
    const thumbnailUrl = getVideoThumbnail(video.url, video.type || 'youtube');
    const duration = video.duration || '0:00';
    const title = video.title || 'Training Video';
    
    return `
        <div class="training-card" onclick="openVideoInNewTab('${video.url}')">
            <img src="${thumbnailUrl}" alt="${title}" class="training-video-thumbnail" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDIwMCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMTUwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik04MCA2MEwxMjAgOTBMMTgwIDYwVjkwSDEyMFY2MEg4MFoiIGZpbGw9IiM2Njc5RUEiLz4KPC9zdmc+'" />
            <div class="training-video-overlay">
                <div class="training-play-button">
                    <i class="mdi mdi-play"></i>
                </div>
            </div>
            <div class="training-video-duration">${duration}</div>
            <div class="training-video-title">${title}</div>
        </div>
    `;
}

// Function to open video in new tab
function openVideoInNewTab(videoUrl) {
    window.open(videoUrl, '_blank', 'noopener,noreferrer');
}

// Function to load training videos
function loadTrainingVideos() {
    // This would typically make an AJAX call to get video data
    // For now, we'll use placeholder data
    const trainingVideos = [
        {
            title: 'Getting Started with Disciple Tools',
            url: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            duration: '5:30',
            type: 'youtube'
        },
        {
            title: 'Advanced Features Tutorial',
            url: 'https://vimeo.com/123456789',
            duration: '8:15',
            type: 'vimeo'
        }
    ];
    
    const trainingGrid = document.getElementById('training-grid');
    if (trainingGrid) {
        trainingGrid.innerHTML = trainingVideos.map(createTrainingVideoCard).join('');
    }
}

// Function to toggle section visibility
function toggleSection(sectionName) {
    const content = document.getElementById(sectionName + '-content');
    const toggle = document.getElementById(sectionName + '-toggle');
    
    if (content && toggle) {
        content.classList.toggle('collapsed');
        const isCollapsed = content.classList.contains('collapsed');
        toggle.innerHTML = isCollapsed ? '<i class="mdi mdi-chevron-right"></i>' : '<i class="mdi mdi-chevron-down"></i>';
        
        // Load training videos when section is expanded
        if (sectionName === 'training' && !isCollapsed) {
            loadTrainingVideos();
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Load training videos if section is already expanded
    const trainingContent = document.getElementById('training-content');
    if (trainingContent && !trainingContent.classList.contains('collapsed')) {
        loadTrainingVideos();
    }
});
</script>
