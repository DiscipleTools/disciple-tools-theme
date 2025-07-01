<?php
/**
 * Mobile Header Template Part
 * 
 * Modern mobile header with search functionality and navigation menu
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Simplified version to test loading
echo '<div style="background: green; color: white; padding: 10px; margin: 10px; position: relative; z-index: 10000;">DEBUG: Mobile header template loaded successfully!</div>';

try {
    $post_type = 'contacts'; // Default fallback
    if ( function_exists( 'dt_get_post_type' ) ) {
        $post_type = dt_get_post_type();
    }
    
    $post_settings = [];
    if ( class_exists( 'DT_Posts' ) && method_exists( 'DT_Posts', 'get_post_settings' ) ) {
        $post_settings = DT_Posts::get_post_settings( $post_type );
    }
    
    $label_plural = $post_settings['label_plural'] ?? ucfirst( $post_type );
    
    echo '<div style="background: lightgreen; color: black; padding: 5px; margin: 5px;">DEBUG: Post type: ' . esc_html($post_type) . ', Label: ' . esc_html($label_plural) . '</div>';
    
} catch ( Exception $e ) {
    echo '<div style="background: red; color: white; padding: 10px; margin: 10px;">DEBUG: Error in mobile header: ' . esc_html($e->getMessage()) . '</div>';
}
?>

<header class="mobile-header show-for-mobile" style="background: white !important; padding: 16px !important; border-bottom: 1px solid #ccc !important; position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; z-index: 10000 !important; display: block !important; visibility: visible !important; opacity: 1 !important; width: 100% !important; box-sizing: border-box !important;">
    <div class="mobile-header__content">
        <div class="mobile-header__top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h1 class="mobile-header__title" style="font-size: 20px; font-weight: 600; color: #1a202c; margin: 0;">
                <?php echo esc_html( $label_plural ?? 'Contacts' ); ?>
            </h1>
            <button class="mobile-header__menu-btn js-mobile-menu-toggle" style="background: none; border: none; padding: 8px; cursor: pointer;" aria-label="Open menu">
                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        
        <div class="mobile-search" style="position: relative; display: flex; align-items: center;">
            <div class="mobile-search__icon" style="position: absolute; left: 12px; z-index: 1; color: #a0aec0;">
                <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input 
                type="search" 
                class="mobile-search__input" 
                id="mobile-search-input"
                style="width: 100%; padding: 12px 12px 12px 44px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8f9fa; font-size: 16px;"
                placeholder="Search <?php echo esc_attr( $label_plural ?? 'contacts' ); ?>"
                autocomplete="off"
            >
        </div>
    </div>
</header>

<div style="background: lightblue; color: black; padding: 10px; margin: 10px; position: fixed !important; top: 300px !important; left: 0 !important; z-index: 20000 !important; width: 300px !important;">
    DEBUG: Mobile header HTML rendered successfully! Header should be at top of screen.
</div>

<!-- IMPOSSIBLE TO MISS TEST ELEMENT -->
<div style="background: red !important; color: white !important; font-size: 24px !important; font-weight: bold !important; padding: 20px !important; margin: 20px !important; position: fixed !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; z-index: 99999 !important; border: 5px solid black !important; box-shadow: 0 0 20px rgba(0,0,0,0.8) !important; text-align: center !important;">
    🔴 MOBILE HEADER TEST 🔴<br>
    IF YOU SEE THIS, THE HEADER TEMPLATE IS WORKING!
</div>

<!-- Mobile Navigation Menu -->
<div class="mobile-nav-menu" id="mobile-nav-menu">
    <div class="mobile-nav-menu__backdrop js-mobile-nav-close"></div>
    <div class="mobile-nav-menu__content">
        <div class="mobile-nav-menu__header">
            <h2 class="mobile-nav-menu__title"><?php esc_html_e( 'Navigation', 'disciple_tools' ) ?></h2>
            <button class="mobile-nav-menu__close js-mobile-nav-close" aria-label="<?php esc_html_e( 'Close menu', 'disciple_tools' ) ?>">
                <svg class="tw-h-6 tw-w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <nav class="mobile-nav-menu__nav">
            <ul class="mobile-nav-menu__list">
                <?php 
                // Define navigation items safely
                $navigation_items = [
                    [
                        'label' => 'Contacts',
                        'url' => home_url('/contacts'),
                        'is_current' => ($post_type === 'contacts')
                    ],
                    [
                        'label' => 'Groups',
                        'url' => home_url('/groups'),
                        'is_current' => ($post_type === 'groups')
                    ],
                    [
                        'label' => 'Dashboard',
                        'url' => home_url('/'),
                        'is_current' => false
                    ]
                ];
                
                // Safety check before foreach
                if (is_array($navigation_items)) :
                    foreach ( $navigation_items as $item ) : ?>
                        <li class="mobile-nav-menu__item">
                            <a href="<?php echo esc_url( $item['url'] ) ?>" 
                               class="mobile-nav-menu__link <?php echo $item['is_current'] ? 'mobile-nav-menu__link--active' : '' ?>">
                                <div class="mobile-nav-menu__link-content">
                                    <span class="mobile-nav-menu__link-text"><?php echo esc_html( $item['label'] ) ?></span>
                                    <?php if ( $item['is_current'] ) : ?>
                                        <svg class="mobile-nav-menu__check-icon tw-h-5 tw-w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </li>
                    <?php endforeach;
                endif; ?>
            </ul>
        </nav>
        
        <div class="mobile-nav-menu__footer">
            <div class="mobile-nav-menu__divider"></div>
            <a href="<?php echo esc_url( home_url( '/settings' ) ) ?>" class="mobile-nav-menu__link">
                <div class="mobile-nav-menu__link-content">
                    <span class="mobile-nav-menu__link-text"><?php esc_html_e( 'Settings', 'disciple_tools' ) ?></span>
                </div>
            </a>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ) ?>" class="mobile-nav-menu__link">
                <div class="mobile-nav-menu__link-content">
                    <span class="mobile-nav-menu__link-text"><?php esc_html_e( 'Sign Out', 'disciple_tools' ) ?></span>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Mobile header search handled by mobile-api.js --> 