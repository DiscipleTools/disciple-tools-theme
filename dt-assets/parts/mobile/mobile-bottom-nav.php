<?php
/**
 * Mobile Bottom Navigation Template Part
 * 
 * Modern bottom tab navigation for mobile devices
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$post_type = dt_get_post_type();
$post_settings = DT_Posts::get_post_settings( $post_type );
?>

<nav class="mobile-bottom-nav show-for-mobile">
    <div class="mobile-bottom-nav__container">
        
        <!-- All Items Tab -->
        <button class="mobile-bottom-nav__item active" data-tab="all" aria-label="<?php esc_html_e( 'All items', 'disciple_tools' ) ?>">
            <svg class="mobile-bottom-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <span class="mobile-bottom-nav__label"><?php esc_html_e( 'All', 'disciple_tools' ) ?></span>
        </button>
        
        <!-- Filter Tab -->
        <button class="mobile-bottom-nav__item js-mobile-filter-toggle" data-tab="filter" aria-label="<?php esc_html_e( 'Filters', 'disciple_tools' ) ?>">
            <svg class="mobile-bottom-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
            </svg>
            <span class="mobile-bottom-nav__label"><?php esc_html_e( 'Filter', 'disciple_tools' ) ?></span>
        </button>
        
        <!-- Sort Tab -->
        <button class="mobile-bottom-nav__item" data-tab="sort" aria-label="<?php esc_html_e( 'Sort options', 'disciple_tools' ) ?>">
            <svg class="mobile-bottom-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"></path>
            </svg>
            <span class="mobile-bottom-nav__label"><?php esc_html_e( 'Sort', 'disciple_tools' ) ?></span>
        </button>
        
        <!-- Bulk Actions Tab -->
        <button class="mobile-bottom-nav__item" data-tab="bulk" aria-label="<?php esc_html_e( 'Bulk actions', 'disciple_tools' ) ?>">
            <svg class="mobile-bottom-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="mobile-bottom-nav__label"><?php esc_html_e( 'Select', 'disciple_tools' ) ?></span>
        </button>
        
        <!-- More Tab -->
        <button class="mobile-bottom-nav__item js-mobile-menu-toggle" data-tab="more" aria-label="<?php esc_html_e( 'More options', 'disciple_tools' ) ?>">
            <svg class="mobile-bottom-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
            </svg>
            <span class="mobile-bottom-nav__label"><?php esc_html_e( 'More', 'disciple_tools' ) ?></span>
        </button>
        
    </div>
</nav>

<!-- Mobile bottom navigation handled by mobile-api.js --> 