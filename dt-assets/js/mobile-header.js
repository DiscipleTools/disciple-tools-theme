jQuery(document).ready(function($) {
    'use strict';

    // Wait for CSS to load before initializing to prevent FOUC
    waitForCSSAndInitialize();

    /**
     * Initialize mobile header immediately since we're using inline critical CSS
     */
    function waitForCSSAndInitialize() {
        // Check if this is a mobile device or small screen
        if (!isMobileDevice() && window.innerWidth > 768) {
            console.log('Not a mobile device, skipping mobile header initialization');
            return;
        }

        // Mark CSS as loaded since we're using inline critical CSS
        $('body').addClass('css-loaded');
        $('.dt-mobile-header').addClass('css-loaded');
        
        // Initialize mobile header functionality immediately
        initializeMobileHeader();
        
        // Update localized variable
        if (window.dt_mobile_header) {
            window.dt_mobile_header.css_loaded = true;
        }
    }

    /**
     * Simple mobile device detection
     */
    function isMobileDevice() {
        // Check for mobile user agents
        const userAgent = navigator.userAgent || navigator.vendor || window.opera;
        const mobileRegex = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i;
        
        // Also check screen width
        const isSmallScreen = window.innerWidth <= 768;
        
        return mobileRegex.test(userAgent) || isSmallScreen;
    }

    function initializeMobileHeader() {
        // Add mobile view classes immediately for CSS targeting
        $('body').addClass('dt-mobile-view');
        $('html').addClass('dt-mobile-view');

        // Force show critical elements immediately
        $('.dt-mobile-header').show();
        $('.dt-mobile-header .search-container').show();
        $('.dt-mobile-header #mobile-global-search').show();

        // Ensure Foundation is initialized
        initializeFoundation();
        
        // Search functionality
        initializeGlobalSearch();
        
        // Mobile navigation overlay
        initializeMobileNavigation();
        
        // Touch and focus improvements
        initializeTouchHandling();
        
        // Show the mobile header now that everything is loaded
        showMobileHeader();
    }

         /**
      * Show mobile header with smooth transition
      */
     function showMobileHeader() {
         const mobileHeader = $('.dt-mobile-header');
         const loadingIndicator = $('#mobile-header-loading');
         
         if (mobileHeader.length) {
             // Add class to body for proper content spacing
             $('body').addClass('has-mobile-header');
             
             // Hide loading indicator first
             loadingIndicator.fadeOut(200);
             
             // Show mobile header
             mobileHeader.addClass('css-loaded');
             
             // Calculate padding after header is fully visible
             setTimeout(() => {
                 adjustContentPadding();
             }, 100);
             
             // Remove critical CSS after full CSS is loaded to prevent conflicts
             setTimeout(() => {
                 $('#dt-mobile-critical-css').remove();
                 loadingIndicator.remove(); // Clean up loading indicator
                 
                 // Readjust padding after everything is loaded
                 adjustContentPadding();
             }, 500);
         }
     }

    /**
     * Initialize Foundation components and disable problematic global handlers
     */
    function initializeFoundation() {
        // Disable Foundation's automatic initialization in mobile mode
        disableFoundationAutoInit();
        
        // Disable Foundation's global data-close handlers in mobile mode
        disableFoundationGlobalHandlers();
        
        // Only initialize off-canvas with the original Foundation if available
        if (typeof window.OriginalFoundation !== 'undefined') {
            try {
                // Initialize off-canvas specifically with original Foundation
                const offCanvas = $('#off-canvas');
                if (offCanvas.length && !offCanvas.hasClass('is-reveal-open')) {
                    // Use original Foundation for off-canvas only
                    const tempFoundation = window.Foundation;
                    window.Foundation = window.OriginalFoundation;
                    offCanvas.foundation();
                    window.Foundation = tempFoundation;
                }
            } catch (error) {
                console.warn('Foundation off-canvas initialization warning:', error.message);
            }
        }
    }

    /**
     * Disable Foundation's automatic initialization in mobile mode
     */
    function disableFoundationAutoInit() {
        // Prevent Foundation from auto-initializing on document ready
        $(document).off('ready.zf.reveal ready.zf.dropdown ready.zf.tooltip ready.zf.accordion');
        
        // Remove data attributes that trigger automatic Foundation initialization
        $('[data-dropdown], [data-tooltip], [data-accordion]').each(function() {
            const $this = $(this);
            // Store original data attributes for potential restoration
            if (!$this.data('dt-original-foundation')) {
                $this.data('dt-original-foundation', {
                    dropdown: $this.attr('data-dropdown'),
                    tooltip: $this.attr('data-tooltip'),
                    accordion: $this.attr('data-accordion')
                });
                // Remove the attributes to prevent auto-init
                $this.removeAttr('data-dropdown data-tooltip data-accordion');
            }
        });
    }

    /**
     * Disable Foundation's global event handlers that cause conflicts in mobile mode
     */
    function disableFoundationGlobalHandlers() {
        // Add global Foundation method safety wrapper
        addFoundationSafetyWrapper();
        // Remove Foundation's global data-close event handlers
        $(document).off('click.zf.trigger', '[data-close]');
        $(document).off('click.fndtn.reveal', '[data-close]');
        $(document).off('click.fndtn.dropdown', '[data-close]');
        $(document).off('click.fndtn.modal', '[data-close]');
        
        // Override Foundation's global click handler with our own safe version
        $(document).on('click.dt-mobile', '[data-close]', function(e) {
            const $target = $(this);
            const $closestModal = $target.closest('[data-reveal], .reveal');
            const $closestDropdown = $target.closest('[data-dropdown], .dropdown-pane');
            const $closestOffCanvas = $target.closest('#off-canvas');
            
            // Only handle Foundation close for properly initialized elements
            if ($closestOffCanvas.length && typeof Foundation !== 'undefined') {
                try {
                    if ($closestOffCanvas.foundation && typeof $closestOffCanvas.foundation === 'function') {
                        $closestOffCanvas.foundation('close');
                    }
                } catch (error) {
                    console.warn('Foundation close failed:', error.message);
                    // Fallback: just hide the element
                    $closestOffCanvas.hide();
                }
            } else if ($closestModal.length) {
                // For modals, try Foundation first, then fallback
                try {
                    if ($closestModal.foundation && typeof $closestModal.foundation === 'function') {
                        $closestModal.foundation('close');
                    } else {
                        $closestModal.hide();
                    }
                } catch (error) {
                    console.warn('Modal close failed:', error.message);
                    $closestModal.hide();
                }
            } else if ($closestDropdown.length) {
                // For dropdowns, just hide them
                $closestDropdown.hide();
            }
            
            e.preventDefault();
            e.stopPropagation();
        });
    }

    /**
     * Add safety wrapper for Foundation methods to prevent errors
     */
    function addFoundationSafetyWrapper() {
        // Override jQuery's foundation method with a safe version for mobile
        const originalFoundation = $.fn.foundation;
        
        $.fn.foundation = function(method, ...args) {
            // In mobile mode, always provide safe fallbacks without warnings
            if (method === 'open') {
                this.show().addClass('is-reveal-open is-active');
                $('body').addClass('is-reveal-open');
                // Focus for accessibility
                this.focus();
                return this;
            } else if (method === 'close') {
                this.hide().removeClass('is-reveal-open is-active');
                $('body').removeClass('is-reveal-open');
                return this;
            } else if (method === 'toggle') {
                this.toggle();
                this.toggleClass('is-active');
                return this;
            } else if (method === 'destroy') {
                // Clean up any Foundation data and classes
                this.removeData('zfPlugin foundation')
                    .removeClass('is-initialized is-reveal-open is-active');
                return this;
            } else if (!method) {
                // Initialization call - pretend it worked
                this.addClass('is-initialized');
                return this;
            }
            
            // For any other method calls, just return this to prevent errors
            return this;
        };

        // Completely override Foundation for mobile mode to prevent conflicts
        if (typeof window.Foundation !== 'undefined') {
            // Store original Foundation for restoration if needed
            window.OriginalFoundation = window.Foundation;
            
            // Clean up any existing Foundation instances that might cause errors
            cleanupFoundationInstances();
            
            // Create a minimal Foundation replacement for mobile
            window.Foundation = {
                // Essential methods that might be called
                reInit: function() {
                    console.log('Foundation reInit called - mobile mode active');
                    return this;
                },
                reflow: function() {
                    console.log('Foundation reflow called - mobile mode active');
                    return this;
                },
                GetYoDigits: function() {
                    return Math.round(Math.random() * 1000000);
                },
                // Plugin registration (prevents errors)
                plugin: function() {
                    console.log('Foundation plugin registration - mobile mode active');
                    return this;
                },
                // Safe utilities
                Keyboard: window.OriginalFoundation?.Keyboard || {},
                MediaQuery: window.OriginalFoundation?.MediaQuery || {},
                utils: window.OriginalFoundation?.utils || {},
                // Add common plugin stubs
                DropdownMenu: function() { return this; },
                Reveal: function() { return this; },
                Tooltip: function() { return this; },
                Accordion: function() { return this; },
                OffCanvas: function() { return this; }
            };
        }
        
        // Handle Foundation data attributes to prevent errors
        initializeFoundationDataAttributes();
    }

    /**
     * Clean up existing Foundation instances to prevent conflicts
     */
    function cleanupFoundationInstances() {
        // Remove Foundation data from elements that might cause conflicts
        $('[data-zf-plugin]').each(function() {
            const $this = $(this);
            // Store the plugin name for reference
            const pluginName = $this.attr('data-zf-plugin');
            console.log('Cleaning up Foundation plugin:', pluginName);
            
            // Remove Foundation-specific data and classes
            $this.removeData('zfPlugin')
                 .removeData('foundation')
                 .removeClass('is-initialized')
                 .removeAttr('data-zf-plugin');
                 
            // Remove any Foundation event handlers
            $this.off('.zf');
        });
        
                 // Clean up global Foundation event handlers that might be causing issues
         $(document).off('.zf');
         $(window).off('.zf');
     }

    /**
     * Handle Foundation data attributes to prevent errors
     */
    function initializeFoundationDataAttributes() {
        // Handle data-close attributes
        $(document).on('click', '[data-close]', function(e) {
            e.preventDefault();
            const $this = $(this);
            const target = $this.attr('data-close');
            
            if (target) {
                // Close specific target
                const $target = $('#' + target);
                if ($target.length) {
                    $target.hide().removeClass('is-reveal-open is-active');
                    $('body').removeClass('is-reveal-open');
                }
            } else {
                // Close closest modal/reveal
                const $modal = $this.closest('.reveal, .dropdown-pane, .off-canvas');
                if ($modal.length) {
                    $modal.hide().removeClass('is-reveal-open is-active');
                    $('body').removeClass('is-reveal-open');
                }
            }
        });
        
        // Handle data-open attributes
        $(document).on('click', '[data-open]', function(e) {
            e.preventDefault();
            const target = $(this).attr('data-open');
            
            if (target) {
                const $target = $('#' + target);
                if ($target.length) {
                    $target.show().addClass('is-reveal-open is-active');
                    $('body').addClass('is-reveal-open');
                    
                    // Focus on the modal for accessibility
                    $target.focus();
                }
            }
        });
        
        // Handle data-toggle attributes
        $(document).on('click', '[data-toggle]', function(e) {
            e.preventDefault();
            const target = $(this).attr('data-toggle');
            
            if (target) {
                const $target = $('#' + target);
                if ($target.length) {
                    $target.toggle();
                    $target.toggleClass('is-active');
                } else {
                    // Handle class-based toggles
                    const $classTarget = $('.' + target);
                    if ($classTarget.length) {
                        $classTarget.toggle();
                        $classTarget.toggleClass('is-active');
                    }
                }
            }
        });
        
        // Handle ESC key to close modals
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27) { // ESC key
                const $openModal = $('.reveal.is-reveal-open:visible');
                if ($openModal.length) {
                    $openModal.hide().removeClass('is-reveal-open is-active');
                    $('body').removeClass('is-reveal-open');
                }
            }
        });
        
        // Handle clicking outside modals to close them
        $(document).on('click', '.reveal.is-reveal-open', function(e) {
            if (e.target === this) {
                $(this).hide().removeClass('is-reveal-open is-active');
                $('body').removeClass('is-reveal-open');
            }
        });
    }

    /**
     * Global Search Functionality
     */
    function initializeGlobalSearch() {
        const searchInput = $('#mobile-global-search');
        const clearButton = $('#mobile-search-clear');
        const searchResults = $('#mobile-search-results');
        const searchLoading = $('#mobile-search-loading');
        const searchContent = $('#mobile-search-content');
        
        let searchTimeout;
        let currentRequest;

        if (!searchInput.length) return;

        // Search input events
        searchInput.on('input', function() {
            const query = $(this).val().trim();
            
            // Show/hide clear button
            clearButton.toggleClass('hidden', !query);
            
            // Clear timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // Abort previous request
            if (currentRequest) {
                currentRequest.abort();
            }
            
            if (query.length < 2) {
                searchResults.addClass('hidden');
                return;
            }
            
            // Debounce search
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        // Clear search
        clearButton.on('click', function() {
            searchInput.val('').focus();
            clearButton.addClass('hidden');
            searchResults.addClass('hidden');
        });

        // Close search results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#mobile-global-search, #mobile-search-results').length) {
                searchResults.addClass('hidden');
            }
        });

        // Search focus events
        searchInput.on('focus', function() {
            if ($(this).val().trim().length >= 2) {
                searchResults.removeClass('hidden');
            }
        });

        function performSearch(query) {
            searchResults.removeClass('hidden');
            searchLoading.removeClass('hidden');
            searchContent.empty();

            // Prepare search data
            const searchData = {
                action: 'dt_mobile_global_search',
                query: query,
                nonce: window.dt_mobile_header?.nonce || ''
            };

            currentRequest = $.ajax({
                url: window.dt_mobile_header?.ajax_url || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: searchData,
                timeout: 10000
            })
            .done(function(response) {
                searchLoading.addClass('hidden');
                
                if (response.success && response.data) {
                    displaySearchResults(response.data);
                } else {
                    displayNoResults();
                }
            })
            .fail(function(xhr) {
                searchLoading.addClass('hidden');
                
                if (xhr.statusText !== 'abort') {
                    displaySearchError();
                }
            });
        }

        function displaySearchResults(results) {
            if (!results || results.length === 0) {
                displayNoResults();
                return;
            }

            let resultsHtml = '';
            
            results.forEach(function(result) {
                resultsHtml += `
                    <div class="search-result-item" data-id="${result.id}" data-type="${result.post_type}">
                        <div class="mobile-search-result-title">${escapeHtml(result.title)}</div>
                        <div class="mobile-search-result-meta">
                            <span class="mobile-search-result-type">${escapeHtml(result.post_type_label)}</span>
                            ${result.status ? `<span class="text-sm text-gray-500">${escapeHtml(result.status)}</span>` : ''}
                        </div>
                    </div>
                `;
            });

            searchContent.html(resultsHtml);

            // Add click handlers for results
            $('.search-result-item').on('click', function() {
                const id = $(this).data('id');
                const type = $(this).data('type');
                
                if (id && type) {
                    window.location.href = `/${type}/${id}`;
                }
            });
        }

        function displayNoResults() {
            searchContent.html(`
                <div class="mobile-search-no-results">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <p class="text-gray-500">No results found</p>
                </div>
            `);
        }

        function displaySearchError() {
            searchContent.html(`
                <div class="mobile-search-no-results">
                    <svg class="w-12 h-12 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500">Search error occurred</p>
                </div>
            `);
        }
    }



    /**
     * Mobile Navigation Functionality
     */
    function initializeMobileNavigation() {
        const navToggle = $('#mobile-nav-toggle');
        const navOverlay = $('#mobile-nav-overlay');
        const offCanvas = $('#off-canvas');

        // Handle navigation toggle
        navToggle.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Direct off-canvas control for mobile
            if (offCanvas.length) {
                const isOpen = offCanvas.hasClass('is-open');
                
                if (isOpen) {
                    // Close off-canvas
                    offCanvas.removeClass('is-open is-reveal-open');
                    $('body').removeClass('is-reveal-open');
                    navOverlay.addClass('hidden');
                } else {
                    // Open off-canvas
                    offCanvas.addClass('is-open is-reveal-open');
                    $('body').addClass('is-reveal-open');
                    navOverlay.removeClass('hidden');
                    
                    // Focus on off-canvas for accessibility
                    offCanvas.focus();
                }
            }
        });

        // Handle overlay click to close
        navOverlay.on('click', function() {
            offCanvas.removeClass('is-open is-reveal-open');
            $('body').removeClass('is-reveal-open');
            $(this).addClass('hidden');
        });

        // Handle close button click
        offCanvas.on('click', '[data-close]', function(e) {
            e.preventDefault();
            offCanvas.removeClass('is-open is-reveal-open');
            $('body').removeClass('is-reveal-open');
            navOverlay.addClass('hidden');
        });

        // Handle escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                // Close off-canvas
                if (offCanvas.hasClass('is-open')) {
                    offCanvas.removeClass('is-open is-reveal-open');
                    $('body').removeClass('is-reveal-open');
                    navOverlay.addClass('hidden');
                }
                // Close other overlays
                $('#mobile-search-results').addClass('hidden');
            }
        });

        // Handle swipe gestures to close (for touch devices)
        let startX = 0;
        let startY = 0;
        
        offCanvas.on('touchstart', function(e) {
            const touch = e.originalEvent.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
        });
        
        offCanvas.on('touchmove', function(e) {
            if (!startX || !startY) return;
            
            const touch = e.originalEvent.touches[0];
            const diffX = startX - touch.clientX;
            const diffY = startY - touch.clientY;
            
            // If swiping left and horizontal swipe is greater than vertical
            if (diffX > 50 && Math.abs(diffX) > Math.abs(diffY)) {
                offCanvas.removeClass('is-open is-reveal-open');
                $('body').removeClass('is-reveal-open');
                navOverlay.addClass('hidden');
                startX = 0;
                startY = 0;
            }
        });
    }

    /**
     * Touch and Focus Handling
     */
    function initializeTouchHandling() {
        // Add active states for touch devices
        $('.dt-mobile-header button, .dt-mobile-header a').on('touchstart', function() {
            $(this).addClass('touch-active');
        }).on('touchend touchcancel', function() {
            $(this).removeClass('touch-active');
        });

        // Improve focus visibility
        $('.dt-mobile-header input, .dt-mobile-header button, .dt-mobile-header a').on('focus', function() {
            $(this).addClass('focused');
        }).on('blur', function() {
            $(this).removeClass('focused');
        });

        // Handle viewport changes
        let viewportWidth = window.innerWidth;
        $(window).on('resize orientationchange', function() {
            const newWidth = window.innerWidth;
            
            if (Math.abs(newWidth - viewportWidth) > 50) {
                // Close all mobile dropdowns on significant viewport changes
                $('#mobile-search-results').addClass('hidden');
                $('#mobile-nav-overlay').addClass('hidden');
                
                // Readjust content padding for header height changes
                adjustContentPadding();
                
                viewportWidth = newWidth;
            }
        });
    }

         /**
      * Adjust content padding to account for fixed header
      */
     function adjustContentPadding() {
         const mobileHeader = $('.dt-mobile-header');
         if (mobileHeader.length && mobileHeader.is(':visible')) {
             // Force a reflow to ensure accurate height measurement
             mobileHeader[0].offsetHeight;
             
             const headerHeight = mobileHeader.outerHeight();
             console.log('Mobile header height:', headerHeight);
             
             if (headerHeight && headerHeight > 0) {
                 // Set CSS custom property for use in other calculations
                 document.documentElement.style.setProperty('--mobile-header-height', headerHeight + 'px');
                 
                 $('body').css({
                     'padding-top': headerHeight + 'px',
                     'margin-top': '0'
                 });
                 
                 // Also adjust main content areas specifically
                 $('main, #main, .main-content, #content').css({
                     'margin-top': '0',
                     'padding-top': '20px'
                 });
                 
                 console.log('Applied padding-top:', headerHeight + 'px');
             }
         }
     }

    /**
     * Utility Functions
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Add CSS for touch states
    const touchStyles = `
        <style>
        .dt-mobile-header .touch-active {
            transform: scale(0.95);
            transition: transform 0.1s ease;
        }
        
        .dt-mobile-header .focused {
            outline: 2px solid rgba(59, 130, 246, 0.5);
            outline-offset: 2px;
        }
        
        .search-result-item:hover,
        .search-result-item:focus {
            background-color: #f3f4f6;
        }
        
        .search-result-item:active {
            background-color: #e5e7eb;
        }
        
        @media (prefers-reduced-motion: reduce) {
            .dt-mobile-header * {
                animation: none !important;
                transition: none !important;
            }
        }
        </style>
    `;
    
    $('head').append(touchStyles);
}); 