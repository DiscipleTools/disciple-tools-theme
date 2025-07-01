<?php
/**
 * Mobile Filter Panel Template Part
 * 
 * Bottom sheet style filter panel for mobile devices
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$post_type = $args['post_type'] ?? 'contacts';
$post_settings = $args['post_settings'] ?? DT_Posts::get_post_settings( $post_type );
$field_options = $post_settings['fields'] ?? [];

// Get common filter fields
$common_filters = [];
$allowed_types = [ 'user_select', 'multi_select', 'key_select', 'boolean', 'date', 'datetime', 'location', 'connection', 'tags' ];

foreach ( $field_options as $field_key => $field ) {
    if ( $field_key && in_array( $field['type'] ?? '', $allowed_types ) && !( isset( $field['hidden'] ) && $field['hidden'] ) ) {
        $common_filters[$field_key] = $field;
    }
}

// Sort filters alphabetically
uasort( $common_filters, function ( $a, $b ) {
    return strnatcmp( $a['name'] ?? 'z', $b['name'] ?? 'z' );
});

?>

<!-- Mobile Filter Panel Overlay -->
<div class="mobile-filter-panel-overlay" id="mobile-filter-overlay">
    <div class="mobile-filter-panel" id="mobile-filter-panel">
        
        <!-- Panel Handle -->
        <div class="mobile-filter-panel__handle">
            <div class="mobile-filter-panel__handle-bar"></div>
        </div>
        
        <!-- Panel Header -->
        <div class="mobile-filter-panel__header">
            <h2 class="mobile-filter-panel__title">
                <?php esc_html_e( 'Filters', 'disciple_tools' ); ?>
            </h2>
            <button class="mobile-filter-panel__close" id="mobile-filter-close" aria-label="<?php esc_html_e( 'Close filters', 'disciple_tools' ); ?>">
                <svg class="tw-h-6 tw-w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Panel Content -->
        <div class="mobile-filter-panel__content">
            
            <!-- Active Filters -->
            <div class="mobile-filter-panel__active-filters" id="mobile-active-filters" style="display: none;">
                <div class="mobile-filter-panel__section-header">
                    <h3 class="mobile-filter-panel__section-title"><?php esc_html_e( 'Active Filters', 'disciple_tools' ); ?></h3>
                    <button class="mobile-filter-panel__clear-all" id="mobile-clear-all-filters">
                        <?php esc_html_e( 'Clear All', 'disciple_tools' ); ?>
                    </button>
                </div>
                <div class="mobile-filter-panel__active-filter-list" id="mobile-active-filter-list">
                    <!-- Active filters will be shown here -->
                </div>
            </div>
            
            <!-- Quick Filters -->
            <div class="mobile-filter-panel__section">
                <h3 class="mobile-filter-panel__section-title"><?php esc_html_e( 'Quick Filters', 'disciple_tools' ); ?></h3>
                <div class="mobile-filter-panel__quick-filters">
                    <button class="mobile-filter-panel__quick-filter" data-filter="my-contacts">
                        <svg class="mobile-filter-panel__quick-filter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span><?php echo esc_html( sprintf( _x( 'My %s', 'My contacts', 'disciple_tools' ), $post_settings['label_plural'] ?? $post_type ) ); ?></span>
                    </button>
                    
                    <button class="mobile-filter-panel__quick-filter" data-filter="recent">
                        <svg class="mobile-filter-panel__quick-filter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span><?php esc_html_e( 'Recently Updated', 'disciple_tools' ); ?></span>
                    </button>
                    
                    <button class="mobile-filter-panel__quick-filter" data-filter="favorites">
                        <svg class="mobile-filter-panel__quick-filter-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span><?php esc_html_e( 'Favorites', 'disciple_tools' ); ?></span>
                    </button>
                </div>
            </div>
            
            <!-- Detailed Filters -->
            <div class="mobile-filter-panel__section">
                <h3 class="mobile-filter-panel__section-title"><?php esc_html_e( 'Filter by Field', 'disciple_tools' ); ?></h3>
                
                <div class="mobile-filter-panel__filter-list">
                    <?php foreach ( array_slice( $common_filters, 0, 8 ) as $field_key => $field ) : ?>
                        <div class="mobile-filter-panel__filter-item" data-field="<?php echo esc_attr( $field_key ); ?>">
                            <div class="mobile-filter-panel__filter-header">
                                <div class="mobile-filter-panel__filter-info">
                                    <?php dt_render_field_icon( $field, 'mobile-filter-panel__filter-icon' ); ?>
                                    <span class="mobile-filter-panel__filter-name"><?php echo esc_html( $field['name'] ); ?></span>
                                </div>
                                <button class="mobile-filter-panel__filter-toggle" data-field="<?php echo esc_attr( $field_key ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Toggle %s filter', 'disciple_tools' ), $field['name'] ) ); ?>">
                                    <svg class="mobile-filter-panel__chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="mobile-filter-panel__filter-content" data-field="<?php echo esc_attr( $field_key ); ?>">
                                <?php if ( $field['type'] === 'key_select' ) : ?>
                                    <div class="mobile-filter-panel__filter-options">
                                        <?php foreach ( $field['default'] as $option_key => $option_value ) : ?>
                                            <label class="mobile-filter-panel__filter-option">
                                                <input type="checkbox" 
                                                       class="mobile-filter-panel__filter-checkbox" 
                                                       data-field="<?php echo esc_attr( $field_key ); ?>" 
                                                       value="<?php echo esc_attr( $option_key ); ?>">
                                                <span class="mobile-filter-panel__filter-option-text">
                                                    <?php echo esc_html( $option_value['label'] ?? $option_key ); ?>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                <?php elseif ( $field['type'] === 'boolean' ) : ?>
                                    <div class="mobile-filter-panel__filter-options">
                                        <label class="mobile-filter-panel__filter-option">
                                            <input type="checkbox" 
                                                   class="mobile-filter-panel__filter-checkbox" 
                                                   data-field="<?php echo esc_attr( $field_key ); ?>" 
                                                   value="1">
                                            <span class="mobile-filter-panel__filter-option-text">
                                                <?php esc_html_e( 'Yes', 'disciple_tools' ); ?>
                                            </span>
                                        </label>
                                        <label class="mobile-filter-panel__filter-option">
                                            <input type="checkbox" 
                                                   class="mobile-filter-panel__filter-checkbox" 
                                                   data-field="<?php echo esc_attr( $field_key ); ?>" 
                                                   value="0">
                                            <span class="mobile-filter-panel__filter-option-text">
                                                <?php esc_html_e( 'No', 'disciple_tools' ); ?>
                                            </span>
                                        </label>
                                    </div>
                                    
                                <?php elseif ( in_array( $field['type'], [ 'date', 'datetime' ] ) ) : ?>
                                    <div class="mobile-filter-panel__date-range">
                                        <div class="mobile-filter-panel__date-input-group">
                                            <label class="mobile-filter-panel__date-label">
                                                <?php esc_html_e( 'From:', 'disciple_tools' ); ?>
                                            </label>
                                            <input type="date" 
                                                   class="mobile-filter-panel__date-input" 
                                                   data-field="<?php echo esc_attr( $field_key ); ?>" 
                                                   data-range="start">
                                        </div>
                                        <div class="mobile-filter-panel__date-input-group">
                                            <label class="mobile-filter-panel__date-label">
                                                <?php esc_html_e( 'To:', 'disciple_tools' ); ?>
                                            </label>
                                            <input type="date" 
                                                   class="mobile-filter-panel__date-input" 
                                                   data-field="<?php echo esc_attr( $field_key ); ?>" 
                                                   data-range="end">
                                        </div>
                                    </div>
                                    
                                <?php else : ?>
                                    <div class="mobile-filter-panel__search-input">
                                        <input type="text" 
                                               class="mobile-filter-panel__text-input" 
                                               data-field="<?php echo esc_attr( $field_key ); ?>" 
                                               placeholder="<?php echo esc_attr( sprintf( __( 'Search %s...', 'disciple_tools' ), $field['name'] ) ); ?>">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        </div>
        
        <!-- Panel Footer -->
        <div class="mobile-filter-panel__footer">
            <button class="mobile-filter-panel__btn mobile-filter-panel__btn--secondary" id="mobile-filter-reset">
                <?php esc_html_e( 'Reset', 'disciple_tools' ); ?>
            </button>
            <button class="mobile-filter-panel__btn mobile-filter-panel__btn--primary" id="mobile-filter-apply">
                <?php esc_html_e( 'Apply Filters', 'disciple_tools' ); ?>
            </button>
        </div>
        
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterOverlay = document.getElementById('mobile-filter-overlay');
    const filterPanel = document.getElementById('mobile-filter-panel');
    const filterClose = document.getElementById('mobile-filter-close');
    const filterApply = document.getElementById('mobile-filter-apply');
    const filterReset = document.getElementById('mobile-filter-reset');
    const clearAllFilters = document.getElementById('mobile-clear-all-filters');
    
    let activeFilters = {};
    let panelOpen = false;
    
    // Initialize filter panel
    initializeFilterPanel();
    
    function initializeFilterPanel() {
        // Set up panel toggle handlers
        setupPanelToggles();
        
        // Set up filter interactions
        setupFilterInteractions();
        
        // Set up quick filters
        setupQuickFilters();
        
        // Set up gesture handling
        setupGestureHandling();
    }
    
    function setupPanelToggles() {
        // Open panel
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('js-mobile-filter-toggle')) {
                openFilterPanel();
            }
        });
        
        // Close panel
        filterClose.addEventListener('click', closeFilterPanel);
        filterOverlay.addEventListener('click', function(e) {
            if (e.target === filterOverlay) {
                closeFilterPanel();
            }
        });
        
        // Handle escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && panelOpen) {
                closeFilterPanel();
            }
        });
    }
    
    function setupFilterInteractions() {
        // Toggle filter sections
        filterPanel.addEventListener('click', function(e) {
            if (e.target.classList.contains('mobile-filter-panel__filter-toggle')) {
                const field = e.target.getAttribute('data-field');
                toggleFilterSection(field);
            }
        });
        
        // Handle filter changes
        filterPanel.addEventListener('change', function(e) {
            if (e.target.classList.contains('mobile-filter-panel__filter-checkbox') ||
                e.target.classList.contains('mobile-filter-panel__date-input') ||
                e.target.classList.contains('mobile-filter-panel__text-input')) {
                updateActiveFilters();
            }
        });
        
        // Apply filters
        filterApply.addEventListener('click', applyFilters);
        
        // Reset filters
        filterReset.addEventListener('click', resetFilters);
        
        // Clear all filters
        clearAllFilters.addEventListener('click', clearAllActiveFilters);
    }
    
    function setupQuickFilters() {
        filterPanel.addEventListener('click', function(e) {
            if (e.target.closest('.mobile-filter-panel__quick-filter')) {
                const quickFilter = e.target.closest('.mobile-filter-panel__quick-filter');
                const filterType = quickFilter.getAttribute('data-filter');
                applyQuickFilter(filterType);
            }
        });
    }
    
    function setupGestureHandling() {
        let startY = 0;
        let currentY = 0;
        let isDragging = false;
        
        filterPanel.addEventListener('touchstart', function(e) {
            if (e.target.closest('.mobile-filter-panel__handle')) {
                startY = e.touches[0].clientY;
                isDragging = true;
            }
        });
        
        filterPanel.addEventListener('touchmove', function(e) {
            if (!isDragging) return;
            
            currentY = e.touches[0].clientY;
            const deltaY = currentY - startY;
            
            if (deltaY > 0) {
                // Dragging down
                const translateY = Math.min(deltaY, 100);
                filterPanel.style.transform = `translateY(${translateY}px)`;
            }
        });
        
        filterPanel.addEventListener('touchend', function(e) {
            if (!isDragging) return;
            
            const deltaY = currentY - startY;
            
            if (deltaY > 100) {
                // Close panel if dragged down enough
                closeFilterPanel();
            } else {
                // Snap back
                filterPanel.style.transform = '';
            }
            
            isDragging = false;
        });
    }
    
    function openFilterPanel() {
        filterOverlay.classList.add('active');
        filterPanel.classList.add('active');
        panelOpen = true;
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Update active filters display
        updateActiveFiltersDisplay();
    }
    
    function closeFilterPanel() {
        filterOverlay.classList.remove('active');
        filterPanel.classList.remove('active');
        panelOpen = false;
        
        // Restore body scroll
        document.body.style.overflow = '';
        
        // Reset panel transform
        filterPanel.style.transform = '';
    }
    
    function toggleFilterSection(field) {
        const filterItem = filterPanel.querySelector(`[data-field="${field}"]`);
        const content = filterItem.querySelector('.mobile-filter-panel__filter-content');
        const chevron = filterItem.querySelector('.mobile-filter-panel__chevron');
        
        if (content.classList.contains('active')) {
            content.classList.remove('active');
            chevron.style.transform = '';
        } else {
            // Close other sections
            filterPanel.querySelectorAll('.mobile-filter-panel__filter-content.active').forEach(c => {
                c.classList.remove('active');
            });
            filterPanel.querySelectorAll('.mobile-filter-panel__chevron').forEach(ch => {
                ch.style.transform = '';
            });
            
            // Open this section
            content.classList.add('active');
            chevron.style.transform = 'rotate(180deg)';
        }
    }
    
    function updateActiveFilters() {
        // Collect all active filter values
        activeFilters = {};
        
        // Collect checkbox filters
        filterPanel.querySelectorAll('.mobile-filter-panel__filter-checkbox:checked').forEach(checkbox => {
            const field = checkbox.getAttribute('data-field');
            const value = checkbox.value;
            
            if (!activeFilters[field]) {
                activeFilters[field] = [];
            }
            activeFilters[field].push(value);
        });
        
        // Collect date filters
        filterPanel.querySelectorAll('.mobile-filter-panel__date-input').forEach(input => {
            const field = input.getAttribute('data-field');
            const range = input.getAttribute('data-range');
            const value = input.value;
            
            if (value) {
                if (!activeFilters[field]) {
                    activeFilters[field] = {};
                }
                activeFilters[field][range] = value;
            }
        });
        
        // Collect text filters
        filterPanel.querySelectorAll('.mobile-filter-panel__text-input').forEach(input => {
            const field = input.getAttribute('data-field');
            const value = input.value.trim();
            
            if (value) {
                activeFilters[field] = value;
            }
        });
        
        updateActiveFiltersDisplay();
    }
    
    function updateActiveFiltersDisplay() {
        const activeFiltersSection = document.getElementById('mobile-active-filters');
        const activeFiltersList = document.getElementById('mobile-active-filter-list');
        
        if (Object.keys(activeFilters).length > 0) {
            activeFiltersSection.style.display = 'block';
            
            // Generate active filter tags
            let filterTags = '';
            Object.keys(activeFilters).forEach(field => {
                const values = activeFilters[field];
                if (Array.isArray(values)) {
                    values.forEach(value => {
                        filterTags += `<span class="mobile-filter-panel__active-tag" data-field="${field}" data-value="${value}">
                            ${field}: ${value}
                            <button class="mobile-filter-panel__active-tag-remove">×</button>
                        </span>`;
                    });
                } else if (typeof values === 'object') {
                    filterTags += `<span class="mobile-filter-panel__active-tag" data-field="${field}">
                        ${field}: ${values.start || ''} - ${values.end || ''}
                        <button class="mobile-filter-panel__active-tag-remove">×</button>
                    </span>`;
                } else {
                    filterTags += `<span class="mobile-filter-panel__active-tag" data-field="${field}" data-value="${values}">
                        ${field}: ${values}
                        <button class="mobile-filter-panel__active-tag-remove">×</button>
                    </span>`;
                }
            });
            
            activeFiltersList.innerHTML = filterTags;
        } else {
            activeFiltersSection.style.display = 'none';
        }
    }
    
    function applyQuickFilter(filterType) {
        // Clear existing filters first
        resetFilters();
        
        switch (filterType) {
            case 'my-contacts':
                // This would set the assigned_to filter to current user
                break;
            case 'recent':
                // This would set a date filter for recently updated
                break;
            case 'favorites':
                // This would filter for favorited items
                break;
        }
        
        applyFilters();
    }
    
    function applyFilters() {
        // This would connect to the existing DT filter API
        if (typeof window.mobileContactList !== 'undefined') {
            window.mobileContactList.refresh();
        }
        
        closeFilterPanel();
        
        // Update bottom nav filter indicator
        const filterTab = document.querySelector('.mobile-bottom-nav__item[data-tab="filter"]');
        if (filterTab && Object.keys(activeFilters).length > 0) {
            filterTab.classList.add('has-active-filters');
        } else if (filterTab) {
            filterTab.classList.remove('has-active-filters');
        }
    }
    
    function resetFilters() {
        // Clear all filter inputs
        filterPanel.querySelectorAll('.mobile-filter-panel__filter-checkbox').forEach(cb => {
            cb.checked = false;
        });
        
        filterPanel.querySelectorAll('.mobile-filter-panel__date-input, .mobile-filter-panel__text-input').forEach(input => {
            input.value = '';
        });
        
        activeFilters = {};
        updateActiveFiltersDisplay();
    }
    
    function clearAllActiveFilters() {
        resetFilters();
        applyFilters();
    }
    
    // Handle removing individual filter tags
    filterPanel.addEventListener('click', function(e) {
        if (e.target.classList.contains('mobile-filter-panel__active-tag-remove')) {
            const tag = e.target.closest('.mobile-filter-panel__active-tag');
            const field = tag.getAttribute('data-field');
            const value = tag.getAttribute('data-value');
            
            // Remove from activeFilters
            if (value) {
                if (Array.isArray(activeFilters[field])) {
                    activeFilters[field] = activeFilters[field].filter(v => v !== value);
                    if (activeFilters[field].length === 0) {
                        delete activeFilters[field];
                    }
                } else {
                    delete activeFilters[field];
                }
            } else {
                delete activeFilters[field];
            }
            
            // Update UI
            const checkbox = filterPanel.querySelector(`[data-field="${field}"][value="${value}"]`);
            if (checkbox) {
                checkbox.checked = false;
            }
            
            updateActiveFiltersDisplay();
        }
    });
    
    // Expose API for external use
    window.mobileFilterPanel = {
        open: openFilterPanel,
        close: closeFilterPanel,
        getActiveFilters: () => activeFilters,
        setFilters: (filters) => {
            activeFilters = filters;
            updateActiveFiltersDisplay();
        }
    };
});
</script>
