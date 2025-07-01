<?php
/**
 * Mobile Floating Action Button (FAB) Template Part
 * 
 * Primary action button for creating new records
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$post_type = dt_get_post_type();

// Get available post types that user can create
$available_post_types = [];
$all_post_types = DT_Posts::get_post_types();

foreach ( $all_post_types as $type ) {
    if ( current_user_can( "create_" . $type ) ) {
        $post_settings = DT_Posts::get_post_settings( $type );
        $available_post_types[] = [
            'key' => $type,
            'label' => $post_settings['label_singular'] ?? ucfirst( $type ),
            'icon' => $post_settings['icon'] ?? 'mdi mdi-account',
            'url' => home_url( '/' ) . $type . '/new'
        ];
    }
}
?>

<div class="mobile-fab-container show-for-mobile">
    <!-- FAB Button -->
    <button class="mobile-fab js-mobile-fab-toggle" aria-label="<?php esc_html_e( 'Create new record', 'disciple_tools' ) ?>">
        <svg class="mobile-fab__icon mobile-fab__icon--plus" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <svg class="mobile-fab__icon mobile-fab__icon--close" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
    
    <!-- FAB Menu -->
    <div class="mobile-fab-menu" style="display: none;">
        <?php foreach ( $available_post_types as $index => $type ) : ?>
            <a href="<?php echo esc_url( $type['url'] ) ?>" class="mobile-fab-menu__item" style="animation-delay: <?php echo $index * 50; ?>ms;">
                <div class="mobile-fab-menu__icon">
                    <?php if ( $type['key'] === 'contacts' ) : ?>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    <?php elseif ( $type['key'] === 'groups' ) : ?>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    <?php else : ?>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <span class="mobile-fab-menu__label"><?php echo esc_html( $type['label'] ) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Backdrop -->
    <div class="mobile-fab-backdrop" style="display: none;"></div>
</div>

<!-- FAB interactions handled by mobile-api.js --> 