/**
 * Mobile Footer Toolbar JavaScript
 * Handles modals, filters, exports, and add new functionality
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize on mobile and when footer is present
        if (($(window).width() <= 768 && $('.dt-mobile-footer').length) || $('.dt-mobile-footer').length) {
            initializeMobileFooter();
        }
    });

    function initializeMobileFooter() {
        // Add class to body to trigger CSS rules for hiding redundant sections
        $('body').addClass('has-mobile-footer');
        
        // Modal management
        const modals = {
            filters: '#mobile-filters-modal',
            splitBy: '#mobile-split-by-modal',
            exports: '#mobile-exports-modal',
            addNew: '#mobile-add-new-modal'
        };

        // Show/hide modal functions
        function showModal(modalId) {
            hideAllModals();
            
            // Create a completely new modal element to bypass CSS conflicts
            if (modalId === '#mobile-add-new-modal') {
                showAddNewModal();
                return;
            }
            
            // For other modals, use the existing approach
            $(modalId).removeClass('hidden').addClass('flex');
            $(modalId).css({
                'display': 'flex !important',
                'z-index': '9999999',
                'position': 'fixed',
                'top': '0',
                'left': '0',
                'width': '100%',
                'height': '100%',
                'background-color': 'rgba(0, 0, 0, 0.5)'
            });
            
            $('body').addClass('overflow-hidden');
        }
        
        function showAddNewModal() {
            // Remove any existing custom modal
            $('#custom-add-new-modal').remove();
            
            // Create a brand new modal element with inline styles
            const modalHtml = `
                <div id="custom-add-new-modal" style="
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    width: 100% !important;
                    height: 100% !important;
                    background: rgba(0, 0, 0, 0.5) !important;
                    z-index: 99999999 !important;
                    display: flex !important;
                    align-items: flex-end !important;
                    justify-content: center !important;
                ">
                    <div style="
                        background: white !important;
                        width: 100% !important;
                        max-height: 80vh !important;
                        border-radius: 16px 16px 0 0 !important;
                        box-shadow: 0 -10px 25px rgba(0,0,0,0.2) !important;
                        overflow-y: auto !important;
                    ">
                        <div style="
                            display: flex !important;
                            justify-content: space-between !important;
                            align-items: center !important;
                            padding: 16px 20px !important;
                            border-bottom: 1px solid #e5e7eb !important;
                        ">
                            <h3 style="
                                margin: 0 !important;
                                font-size: 18px !important;
                                font-weight: 600 !important;
                                color: #111827 !important;
                            ">Create New Record</h3>
                            <button id="close-custom-modal" style="
                                background: none !important;
                                border: none !important;
                                color: #9ca3af !important;
                                cursor: pointer !important;
                                padding: 4px !important;
                                font-size: 24px !important;
                            ">&times;</button>
                        </div>
                        <div id="custom-modal-content" style="padding: 20px !important;">
                            Loading...
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to body
            $('body').append(modalHtml);
            
            // Load content via AJAX or use static content
            loadAddNewContent();
            
            // Close handler
            $('#close-custom-modal').on('click', function() {
                $('#custom-add-new-modal').remove();
                $('body').removeClass('overflow-hidden');
            });
            
            // Background click to close
            $('#custom-add-new-modal').on('click', function(e) {
                if (e.target === this) {
                    $('#custom-add-new-modal').remove();
                    $('body').removeClass('overflow-hidden');
                }
            });
            
            // Escape key to close
            $(document).on('keydown.customModal', function(e) {
                if (e.key === 'Escape') {
                    $('#custom-add-new-modal').remove();
                    $('body').removeClass('overflow-hidden');
                    $(document).off('keydown.customModal');
                }
            });
            
            $('body').addClass('overflow-hidden');
        }
        
        function loadAddNewContent() {
            // For now, create static content - later we can make this dynamic
            const content = `
                <div style="margin-bottom: 12px;">
                    <a href="/contacts/new" style="
                        display: flex !important;
                        align-items: center !important;
                        width: 100% !important;
                        padding: 16px !important;
                        background: #f0fdf4 !important;
                        border: 1px solid #bbf7d0 !important;
                        border-radius: 8px !important;
                        text-decoration: none !important;
                        color: inherit !important;
                        transition: background-color 0.2s !important;
                    " onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                        <svg style="width: 20px; height: 20px; margin-right: 12px; color: #059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <div>
                            <div style="font-weight: 500; color: #111827; margin-bottom: 4px;">New Contact</div>
                            <div style="font-size: 14px; color: #6b7280;">Create a new contact record</div>
                        </div>
                    </a>
                </div>
                <div style="margin-bottom: 12px;">
                    <a href="/groups/new" style="
                        display: flex !important;
                        align-items: center !important;
                        width: 100% !important;
                        padding: 16px !important;
                        background: #f9fafb !important;
                        border: 1px solid #e5e7eb !important;
                        border-radius: 8px !important;
                        text-decoration: none !important;
                        color: inherit !important;
                        transition: background-color 0.2s !important;
                    " onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#f9fafb'">
                        <svg style="width: 20px; height: 20px; margin-right: 12px; color: #2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <div>
                            <div style="font-weight: 500; color: #111827; margin-bottom: 4px;">New Group</div>
                            <div style="font-size: 14px; color: #6b7280;">Create a new group record</div>
                        </div>
                    </a>
                </div>
            `;
            
            $('#custom-modal-content').html(content);
        }

        function hideModal(modalId) {
            $(modalId).addClass('hidden').removeClass('flex');
            $('body').removeClass('overflow-hidden');
        }

        function hideAllModals() {
            Object.values(modals).forEach(modalId => {
                $(modalId).addClass('hidden').removeClass('flex');
            });
            $('body').removeClass('overflow-hidden');
        }

        // Button click handlers
        $('#mobile-filters-btn').on('click', function() {
            showModal(modals.filters);
            loadFiltersContent();
        });

        $('#mobile-split-by-btn').on('click', function() {
            showModal(modals.splitBy);
        });

        $('#mobile-exports-btn').on('click', function() {
            showModal(modals.exports);
        });

        $('#mobile-add-new-footer-btn').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showModal(modals.addNew);
            return false;
        });



        // Close modal handlers
        $('#close-filters-modal, #close-split-by-modal, #close-exports-modal, #close-add-new-modal').on('click', function() {
            hideAllModals();
        });

        // Close modals when clicking overlay
        $('.fixed.inset-0.bg-black.bg-opacity-50').on('click', function(e) {
            if (e.target === this) {
                hideAllModals();
            }
        });

        // Escape key to close modals
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                hideAllModals();
            }
        });

        // Filters functionality
        function loadFiltersContent() {
            const $content = $('#mobile-filters-content');
            
            // Check if filters are already loaded
            if ($content.children().length > 0) {
                return;
            }

            $content.html('<div class="flex justify-center items-center py-4"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div></div>');

            // Clone filters from desktop version
            const $desktopFilters = $('#list-filters');
            if ($desktopFilters.length) {
                const $filterTabs = $desktopFilters.find('#list-filter-tabs').clone();
                const $customFilters = $desktopFilters.find('.custom-filters').clone();
                
                $content.empty();
                
                // Add create custom filter link
                $content.append(`
                    <div class="mb-4">
                        <button class="flex items-center w-full px-4 py-2 text-left bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors" data-open="filter-modal">
                            <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="font-medium text-gray-900">${window.SHAREDFUNCTIONS.escapeHTML(dt_mobile_footer.translations.create_custom_filter)}</span>
                        </button>
                    </div>
                `);
                
                if ($filterTabs.length) {
                    $filterTabs.addClass('mobile-filter-tabs');
                    $content.append($filterTabs);
                }
                
                if ($customFilters.length) {
                    $customFilters.addClass('mobile-custom-filters');
                    $content.append($customFilters);
                }

                // Re-initialize Foundation accordions for mobile
                $content.find('.accordion').foundation();
                
                // Handle filter selection
                $content.find('.js-list-view').on('change', function() {
                    // Trigger the existing filter change handler
                    hideAllModals();
                    // The existing modular-list.js will handle the filter change
                });
            } else {
                $content.html('<p class="text-gray-500 text-center py-4">' + dt_mobile_footer.translations.no_filters_available + '</p>');
            }
        }

        // Split By functionality
        $('#mobile-split-by-go').on('click', function() {
            const selectedField = $('#mobile-split-by-select').val();
            if (!selectedField) {
                alert(dt_mobile_footer.translations.please_select_field);
                return;
            }

            // Set the desktop split by field and trigger
            $('#split_by_current_filter_select').val(selectedField);
            $('#split_by_current_filter_button').click();
            
            hideAllModals();
        });

        // Export functionality
        $('#mobile-export-csv').on('click', function() {
            hideAllModals();
            $('#export_csv_list').click();
        });

        $('#mobile-export-email').on('click', function() {
            hideAllModals();
            $('#export_bcc_email_list').click();
        });

        $('#mobile-export-phone').on('click', function() {
            hideAllModals();
            $('#export_phone_list').click();
        });

        $('#mobile-export-map').on('click', function() {
            hideAllModals();
            $('.export_map_list').click();
        });

        // Handle window resize to hide modals on orientation change
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                hideAllModals();
                // Remove mobile footer class on desktop to restore sidebar
                $('body').removeClass('has-mobile-footer');
            } else {
                // Re-add mobile footer class on mobile
                $('body').addClass('has-mobile-footer');
            }
        });

        // Add padding to bottom of content to prevent overlap with footer
        function adjustContentPadding() {
            const footerHeight = $('.dt-mobile-footer').outerHeight();
            if (footerHeight) {
                $('main, #main, .main-content').css('padding-bottom', footerHeight + 20 + 'px');
            }
        }

        // Initial padding adjustment
        adjustContentPadding();

        // Adjust on window resize
        $(window).on('resize', adjustContentPadding);

        // Show footer after content is loaded
        $('.dt-mobile-footer').removeClass('hidden').addClass('flex');

        // Add pulse animation to buttons on first load
        setTimeout(function() {
            $('.dt-mobile-footer button').addClass('animate-pulse');
            setTimeout(function() {
                $('.dt-mobile-footer button').removeClass('animate-pulse');
            }, 2000);
        }, 1000);
    }

    // Helper function to check if device is mobile
    function isMobileDevice() {
        return $(window).width() <= 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    // Initialize on window load as well to ensure all dependencies are ready
    $(window).on('load', function() {
        if (isMobileDevice() && $('.dt-mobile-footer').length) {
            // Ensure filters are synchronized
            setTimeout(function() {
                const currentFilter = $('.js-list-view:checked').val();
                if (currentFilter) {
                    $('#mobile-filters-content').attr('data-current-filter', currentFilter);
                }
            }, 500);
        }
    });

})(jQuery); 