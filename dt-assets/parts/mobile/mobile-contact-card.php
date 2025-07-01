<?php
/**
 * Mobile Contact Card Template Part
 * 
 * Modern card-based layout for displaying contact information on mobile
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Get contact data - this would be passed from the calling template
$contact = $args['contact'] ?? [];
$post_type = $args['post_type'] ?? 'contacts';

// Prepare contact data
$contact_id = $contact['ID'] ?? 0;
$contact_name = $contact['post_title'] ?? __( 'Unknown', 'disciple_tools' );
$contact_status = $contact['overall_status']['label'] ?? '';
$contact_status_key = $contact['overall_status']['key'] ?? '';
$assigned_to = $contact['assigned_to']['display'] ?? '';
$last_modified = $contact['last_modified']['formatted'] ?? '';

?>

<div class="mobile-contact-card" data-contact-id="<?php echo esc_attr( $contact_id ); ?>">
    <div class="mobile-contact-card__container">
        
        <!-- Selection Checkbox (hidden by default, shown in bulk mode) -->
        <div class="mobile-contact-card__checkbox">
            <input type="checkbox" 
                   id="contact-<?php echo esc_attr( $contact_id ); ?>" 
                   name="bulk_send_app_id" 
                   value="<?php echo esc_attr( $contact_id ); ?>"
                   class="mobile-contact-card__checkbox-input">
            <label for="contact-<?php echo esc_attr( $contact_id ); ?>" class="mobile-contact-card__checkbox-label"></label>
        </div>
        
        <!-- Main Card Content -->
        <div class="mobile-contact-card__content">
            
            <!-- Avatar and Primary Info -->
            <div class="mobile-contact-card__header">
                <div class="mobile-contact-card__avatar">
                    <div class="mobile-contact-card__avatar-placeholder">
                        <?php echo esc_html( strtoupper( substr( $contact_name, 0, 1 ) ) ); ?>
                    </div>
                </div>
                
                <div class="mobile-contact-card__header-info">
                    <h3 class="mobile-contact-card__name">
                        <a href="<?php echo esc_url( home_url( '/' ) . $post_type . '/' . $contact_id ); ?>" 
                           class="mobile-contact-card__name-link">
                            <?php echo esc_html( $contact_name ); ?>
                        </a>
                    </h3>
                    
                    <?php if ( $contact_status ) : ?>
                        <div class="mobile-contact-card__status">
                            <span class="mobile-contact-card__status-badge status-<?php echo esc_attr( $contact_status_key ); ?>">
                                <?php echo esc_html( $contact_status ); ?>
                            </span>
                            <?php if ( $last_modified ) : ?>
                                <span class="mobile-contact-card__timestamp">
                                    • <?php echo esc_html( $last_modified ); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Card Actions Menu -->
                <div class="mobile-contact-card__actions">
                    <button class="mobile-contact-card__menu-btn js-contact-menu-toggle" 
                            data-contact-id="<?php echo esc_attr( $contact_id ); ?>"
                            aria-label="<?php esc_html_e( 'Contact options', 'disciple_tools' ); ?>">
                        <svg class="mobile-contact-card__menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
        </div>
        
    </div>
</div>
