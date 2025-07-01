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

<!-- MOBILE ENHANCED HEADER -->
<div class="dt-mobile-header bg-blue-600 shadow-lg sticky top-0 z-50">
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
    <div class="px-4 pb-3">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input type="text" 
                   id="mobile-global-search" 
                   class="block w-full pl-10 pr-12 py-3 border border-transparent rounded-lg bg-white bg-opacity-20 placeholder-white placeholder-opacity-75 text-white focus:outline-none focus:bg-white focus:text-gray-900 focus:placeholder-gray-500 focus:border-white focus:ring-2 focus:ring-white focus:ring-opacity-50 transition-all" 
                   placeholder="<?php echo esc_attr( $search_context ); ?>"
                   autocomplete="off">
            
            <!-- Clear Search Button -->
            <button id="mobile-search-clear" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-white hover:text-gray-300 transition-colors hidden">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Search Results Dropdown -->
        <div id="mobile-search-results" class="hidden absolute left-4 right-4 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 max-h-80 overflow-y-auto z-50">
            <div id="mobile-search-loading" class="hidden p-4 text-center text-gray-500">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500 inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <?php esc_html_e( 'Searching...', 'disciple_tools' ); ?>
            </div>
            <div id="mobile-search-content"></div>
        </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="bg-blue-700 bg-opacity-50 px-4 py-2">
        <div class="flex items-center justify-between">
            <!-- Post Type Quick Links -->
            <div class="flex space-x-4 overflow-x-auto">
                <?php 
                $main_post_types = array_slice( $dt_nav_tabs['main'], 0, 4 ); // Show first 4 main post types
                foreach ( $main_post_types as $key => $nav_item ) : 
                    if ( !( $nav_item['hidden'] ?? false ) ) :
                ?>
                    <a href="<?php echo esc_url( $nav_item['link'] ); ?>" 
                       class="flex-shrink-0 px-3 py-1 text-sm text-white bg-blue-800 rounded-full hover:bg-blue-900 transition-colors whitespace-nowrap">
                        <?php echo esc_html( $nav_item['label'] ); ?>
                    </a>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>

            <!-- Add New Button -->
            <?php if ( isset( $dt_nav_tabs['admin']['add_new'] ) && !( $dt_nav_tabs['admin']['add_new']['hidden'] ?? false ) ) : ?>
                <button id="mobile-add-new-toggle" 
                        class="flex items-center justify-center w-8 h-8 bg-green-500 rounded-full hover:bg-green-600 transition-colors ml-4">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </button>

                <!-- Add New Dropdown -->
                <div id="mobile-add-new-dropdown" class="hidden absolute right-4 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                    <?php if ( isset( $dt_nav_tabs['admin']['add_new']['submenu'] ) && ! empty( $dt_nav_tabs['admin']['add_new']['submenu'] ) ) : ?>
                        <?php foreach ( $dt_nav_tabs['admin']['add_new']['submenu'] as $dt_nav_submenu ) : ?>
                            <?php if ( ! isset( $dt_nav_submenu['hidden'] ) || ! $dt_nav_submenu['hidden'] ) : ?>
                                <a href="<?php echo esc_url( $dt_nav_submenu['link'] ); ?>" 
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                    <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <?php echo esc_html( $dt_nav_submenu['label'] ); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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

<script>
// Initialize mobile header functionality
document.addEventListener('DOMContentLoaded', function() {
    // Focus management for search
    const searchInput = document.getElementById('mobile-global-search');
    const clearButton = document.getElementById('mobile-search-clear');
    
    if (searchInput && clearButton) {
        searchInput.addEventListener('input', function() {
            clearButton.classList.toggle('hidden', !this.value);
        });
        
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            clearButton.classList.add('hidden');
            document.getElementById('mobile-search-results').classList.add('hidden');
        });
    }
});
</script> 