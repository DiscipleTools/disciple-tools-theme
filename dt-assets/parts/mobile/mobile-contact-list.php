<?php
/**
 * Mobile Contact List Template Part
 * 
 * Container for mobile contact cards with infinite scroll
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Get data from global variables or use defaults
global $mobile_post_type, $mobile_post_settings;
$post_type = $mobile_post_type ?? ($args['post_type'] ?? 'contacts');
$post_settings = $mobile_post_settings ?? ($args['post_settings'] ?? []);

// Fallback if no settings available
if ( empty( $post_settings ) && class_exists( 'DT_Posts' ) ) {
    try {
        $post_settings = DT_Posts::get_post_settings( $post_type );
    } catch ( Exception $e ) {
        $post_settings = [ 'label_plural' => 'Contacts', 'label_singular' => 'Contact' ];
    }
}

?>

<div style="background: blue; color: white; padding: 10px; margin: 10px;">
    DEBUG: Mobile contact list template loaded successfully!
</div>

<div class="mobile-contact-list" id="mobile-contact-list">
    <div class="mobile-contact-list__container">
        
        <!-- Loading State -->
        <div class="mobile-contact-list__loading" id="mobile-contact-loading">
            <div class="mobile-contact-list__loading-spinner">
                <svg class="animate-spin tw-h-8 tw-w-8 tw-text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span><?php esc_html_e( 'Loading contacts...', 'disciple_tools' ); ?></span>
            </div>
        </div>
        
        <!-- Empty State -->
        <div class="mobile-contact-list__empty" id="mobile-contact-empty" style="display: none;">
            <div class="mobile-contact-list__empty-content">
                <svg class="mobile-contact-list__empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                <h3 class="mobile-contact-list__empty-title">
                    <?php echo esc_html( sprintf( _x( 'No %s found', 'No contacts found', 'disciple_tools' ), $post_settings['label_plural'] ?? $post_type ) ); ?>
                </h3>
                <p class="mobile-contact-list__empty-text">
                    <?php esc_html_e( 'Try adjusting your filters or search terms', 'disciple_tools' ); ?>
                </p>
                <button class="mobile-contact-list__empty-action" onclick="window.location.href='<?php echo esc_url( home_url( '/' ) . $post_type . '/new' ); ?>'">
                    <?php echo esc_html( sprintf( _x( 'Create New %s', 'Create New contact', 'disciple_tools' ), $post_settings['label_singular'] ?? $post_type ) ); ?>
                </button>
            </div>
        </div>
        
        <!-- Contact Cards Container -->
        <div class="mobile-contact-list__cards" id="mobile-contact-cards">
            <!-- Contact cards will be loaded here dynamically -->
        </div>
        
        <!-- Load More Button -->
        <div class="mobile-contact-list__load-more" id="mobile-load-more" style="display: none;">
            <button class="mobile-contact-list__load-more-btn" id="mobile-load-more-btn">
                <span class="mobile-contact-list__load-more-text"><?php esc_html_e( 'Load More', 'disciple_tools' ); ?></span>
                <svg class="mobile-contact-list__load-more-spinner" style="display: none;" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </div>
        
        <!-- Bulk Selection Info -->
        <div class="mobile-contact-list__bulk-info" id="mobile-bulk-info" style="display: none;">
            <div class="mobile-contact-list__bulk-info-content">
                <span class="mobile-contact-list__bulk-count">0 selected</span>
                <div class="mobile-contact-list__bulk-actions">
                    <button class="mobile-contact-list__bulk-action" data-action="select-all">
                        <?php esc_html_e( 'Select All', 'disciple_tools' ); ?>
                    </button>
                    <button class="mobile-contact-list__bulk-action" data-action="deselect-all">
                        <?php esc_html_e( 'Deselect All', 'disciple_tools' ); ?>
                    </button>
                    <button class="mobile-contact-list__bulk-action mobile-contact-list__bulk-action--exit" data-action="exit-bulk">
                        <?php esc_html_e( 'Exit', 'disciple_tools' ); ?>
                    </button>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- Template JavaScript removed - contact loading handled by mobile-api.js -->
<script>
// Template JavaScript disabled to prevent conflicts with mobile-api.js
// Mobile contact functionality is handled by the main mobile API
/*
document.addEventListener('DOMContentLoaded', function() {
    const contactList = document.getElementById('mobile-contact-list');
    const contactCards = document.getElementById('mobile-contact-cards');
    const loadingSpinner = document.getElementById('mobile-contact-loading');
    const emptyState = document.getElementById('mobile-contact-empty');
    const loadMoreBtn = document.getElementById('mobile-load-more-btn');
    const bulkInfo = document.getElementById('mobile-bulk-info');
    const bulkCount = contactList.querySelector('.mobile-contact-list__bulk-count');
    
    let currentPage = 1;
    let hasMoreData = true;
    let isLoading = false;
    
    // Initialize mobile contact list
    initializeMobileContactList();
    
    function initializeMobileContactList() {
        // Load initial data
        loadContacts(1);
        
        // Set up infinite scroll (optional)
        setupInfiniteScroll();
        
        // Set up bulk actions
        setupBulkActions();
        
        // Set up load more button
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', loadMoreContacts);
        }
    }
    
    function loadContacts(page = 1, reset = false) {
        if (isLoading) return;
        
        isLoading = true;
        showLoading(true);
        
        // This would connect to the existing DT list API
        const searchQuery = document.getElementById('mobile-search-input')?.value || '';
        const currentFilters = getCurrentFilters();
        
        // Mock data loading - replace with actual API call
        setTimeout(() => {
            const mockContacts = generateMockContacts(page);
            
            if (reset) {
                contactCards.innerHTML = '';
                currentPage = 1;
            }
            
            if (mockContacts.length > 0) {
                renderContacts(mockContacts);
                currentPage = page;
                hasMoreData = mockContacts.length >= 20; // Assuming 20 per page
            } else {
                if (page === 1) {
                    showEmptyState();
                } else {
                    hasMoreData = false;
                }
            }
            
            showLoading(false);
            updateLoadMoreButton();
            isLoading = false;
        }, 1000);
    }
    
    function renderContacts(contacts) {
        contacts.forEach(contact => {
            const cardHtml = createContactCardHTML(contact);
            contactCards.insertAdjacentHTML('beforeend', cardHtml);
        });
    }
    
    function createContactCardHTML(contact) {
        // This would use the mobile-contact-card.php template
        // For now, return a simplified version
        return `
            <div class="mobile-contact-card" data-contact-id="${contact.id}">
                <div class="mobile-contact-card__container">
                    <div class="mobile-contact-card__checkbox">
                        <input type="checkbox" id="contact-${contact.id}" class="mobile-contact-card__checkbox-input" value="${contact.id}">
                        <label for="contact-${contact.id}" class="mobile-contact-card__checkbox-label"></label>
                    </div>
                    <div class="mobile-contact-card__content">
                        <div class="mobile-contact-card__header">
                            <div class="mobile-contact-card__avatar">
                                <div class="mobile-contact-card__avatar-placeholder">
                                    ${contact.name.charAt(0).toUpperCase()}
                                </div>
                            </div>
                            <div class="mobile-contact-card__header-info">
                                <h3 class="mobile-contact-card__name">
                                    <a href="/contacts/${contact.id}" class="mobile-contact-card__name-link">
                                        ${contact.name}
                                    </a>
                                </h3>
                                <div class="mobile-contact-card__status">
                                    <span class="mobile-contact-card__status-badge status-${contact.status.key}">
                                        ${contact.status.label}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function generateMockContacts(page) {
        // Mock data generation - replace with actual API call
        const contacts = [];
        const startId = (page - 1) * 20 + 1;
        
        for (let i = 0; i < 20; i++) {
            contacts.push({
                id: startId + i,
                name: `Contact ${startId + i}`,
                status: {
                    key: 'active',
                    label: 'Active'
                },
                email: `contact${startId + i}@example.com`,
                phone: `+1-555-${String(startId + i).padStart(4, '0')}`
            });
        }
        
        return contacts;
    }
    
    function showLoading(show) {
        loadingSpinner.style.display = show ? 'flex' : 'none';
    }
    
    function showEmptyState() {
        emptyState.style.display = 'flex';
        contactCards.style.display = 'none';
    }
    
    function updateLoadMoreButton() {
        const loadMoreContainer = document.getElementById('mobile-load-more');
        if (hasMoreData && contactCards.children.length > 0) {
            loadMoreContainer.style.display = 'block';
        } else {
            loadMoreContainer.style.display = 'none';
        }
    }
    
    function loadMoreContacts() {
        loadContacts(currentPage + 1);
    }
    
    function setupInfiniteScroll() {
        // Optional: Set up infinite scroll
        window.addEventListener('scroll', () => {
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 1000) {
                if (hasMoreData && !isLoading) {
                    loadMoreContacts();
                }
            }
        });
    }
    
    function setupBulkActions() {
        // Set up bulk selection handlers
        contactList.addEventListener('change', function(e) {
            if (e.target.classList.contains('mobile-contact-card__checkbox-input')) {
                updateBulkSelection();
            }
        });
        
        // Set up bulk action buttons
        contactList.addEventListener('click', function(e) {
            if (e.target.hasAttribute('data-action')) {
                const action = e.target.getAttribute('data-action');
                handleBulkAction(action);
            }
        });
    }
    
    function updateBulkSelection() {
        const selectedCheckboxes = contactList.querySelectorAll('.mobile-contact-card__checkbox-input:checked');
        const selectedCount = selectedCheckboxes.length;
        
        if (selectedCount > 0) {
            bulkInfo.style.display = 'block';
            bulkCount.textContent = `${selectedCount} selected`;
            contactList.classList.add('bulk-mode');
        } else {
            bulkInfo.style.display = 'none';
            contactList.classList.remove('bulk-mode');
        }
        
        // Update contact card visual states
        contactList.querySelectorAll('.mobile-contact-card').forEach(card => {
            const checkbox = card.querySelector('.mobile-contact-card__checkbox-input');
            if (checkbox && checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    }
    
    function handleBulkAction(action) {
        const selectedCheckboxes = contactList.querySelectorAll('.mobile-contact-card__checkbox-input:checked');
        
        switch (action) {
            case 'select-all':
                contactList.querySelectorAll('.mobile-contact-card__checkbox-input').forEach(cb => {
                    cb.checked = true;
                });
                updateBulkSelection();
                break;
                
            case 'deselect-all':
                selectedCheckboxes.forEach(cb => {
                    cb.checked = false;
                });
                updateBulkSelection();
                break;
                
            case 'exit-bulk':
                selectedCheckboxes.forEach(cb => {
                    cb.checked = false;
                });
                contactList.classList.remove('bulk-mode');
                bulkInfo.style.display = 'none';
                break;
        }
    }
    
    function getCurrentFilters() {
        // This would get the current filter state
        return {};
    }
    
    // Expose API for external use
    window.mobileContactList = {
        refresh: () => loadContacts(1, true),
        loadMore: loadMoreContacts,
        search: (query) => {
            // Update search and reload
            loadContacts(1, true);
        }
    };
});
*/
</script>
