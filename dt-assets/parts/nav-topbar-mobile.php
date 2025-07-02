<?php
/**
 * Mobile Navigation Header
 * Enhanced mobile experience with prominent search and navigation
 */

global $pagenow;
if ( is_multisite() && 'wp-activate.php' === $pagenow ) {
    return;
}

// Get navigation menu items
$dt_nav_tabs = dt_default_menu_array();
$logo_url = $dt_nav_tabs['admin']['site']['icon'] ?? get_template_directory_uri() . '/dt-assets/images/disciple-tools-logo-white.png';
$custom_logo_url = get_option( 'custom_logo_url' );
if ( !empty( $custom_logo_url ) ) {
    $logo_url = $custom_logo_url;
}

// Get current post type for contextual search
$current_post_type = get_post_type();
if ( !$current_post_type && isset( $_GET['post_type'] ) ) {
    $current_post_type = sanitize_text_field( $_GET['post_type'] );
}

// Get search placeholder based on current context
$search_context = '';
if ( $current_post_type === 'contacts' ) {
    $search_context = __( 'Search contacts...', 'disciple_tools' );
} elseif ( $current_post_type === 'groups' ) {
    $search_context = __( 'Search groups...', 'disciple_tools' );
} else {
    $search_context = __( 'Search all records...', 'disciple_tools' );
}
?>

<!-- CSS Loading Indicator -->
<div class="dt-mobile-header-loading" id="mobile-header-loading">
    <div class="spinner"></div>
</div>

<!-- MOBILE ENHANCED HEADER -->
<div class="dt-mobile-header dt-mobile-header-enhanced">
    <!-- Main Header Bar -->
    <div class="flex items-center justify-between px-4 py-3">
        <!-- Left Section: Hamburger Menu -->
        <button id="mobile-nav-toggle" class="flex items-center justify-center w-10 h-10 rounded-md hover:bg-blue-700 transition-colors" type="button" data-open="off-canvas">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Center Section: Logo -->
        <div class="flex-shrink-0 mx-3">
            <a href="<?php echo esc_url( site_url() ); ?>" class="flex items-center">
                <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="h-8 w-auto">
            </a>
        </div>

        <!-- Right Section: User Profile -->
        <a href="<?php echo esc_url( site_url( '/settings/' ) ); ?>" class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-700 hover:bg-blue-800 transition-colors">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
        </a>
    </div>

    <!-- Global Search Bar -->
    <div class="search-container">
        <div class="search-wrapper">
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input 
                type="text" 
                id="mobile-global-search" 
                placeholder="<?php echo esc_attr( $search_context ); ?>"
                autocomplete="off"
            >
            <div id="mobile-search-spinner" class="hidden">
                <svg class="w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            
            <!-- Search Results Overlay -->
            <div id="mobile-search-results" class="hidden">
                <div id="mobile-search-content">
                    <!-- Results will be populated here -->
                </div>
            </div>
        </div>
    </div>


</div>

<!-- Mobile Navigation Overlay -->
<div id="mobile-nav-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40"></div>

<style>
/* Additional mobile-specific styles */
.dt-mobile-header {
    -webkit-backdrop-filter: blur(10px);
    backdrop-filter: blur(10px);
}

@media (max-width: 640px) {
    .dt-mobile-header .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .dt-mobile-header .overflow-x-auto::-webkit-scrollbar {
        display: none;
    }
}
</style>

 