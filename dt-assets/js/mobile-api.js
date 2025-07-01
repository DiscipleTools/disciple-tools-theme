/**
 * Mobile API for Disciple Tools
 * 
 * Provides a unified API for mobile interactions and connects mobile UI to DT backend
 */

(function($) {
    'use strict';
    
    // Mobile API Object
    window.mobileAPI = {
        // Configuration
        config: {
            itemsPerPage: 20,
            loadMoreThreshold: 1000,
            searchDebounce: 300
        },
        
        // State management
        state: {
            currentPostType: '',
            currentPage: 1,
            hasMoreData: true,
            isLoading: false,
            activeFilters: {},
            searchQuery: '',
            sortField: 'post_date',
            sortOrder: 'desc'
        },
        
        // Initialize mobile API
        init: function() {
            this.bindEvents();
            this.setupInterceptors();
            this.loadPostTypeSettings();
        },
        
        // Bind mobile-specific events
        bindEvents: function() {
            const self = this;
            
            // Search functionality - handle both mobile header and other search inputs
            $(document).on('input', '#mobile-search-input', function() {
                const query = $(this).val();
                self.debounce(function() {
                    self.search(query);
                }, self.config.searchDebounce)();
                
                // Show/hide clear button
                const $clearBtn = $('.mobile-search__clear');
                if (query.length > 0) {
                    $clearBtn.show();
                } else {
                    $clearBtn.hide();
                }
            });
            
            // Clear search - handle mobile header clear button
            $(document).on('click', '.js-mobile-search-clear', function() {
                $('#mobile-search-input').val('');
                $('.mobile-search__clear').hide();
                self.clearSearch();
            });
            
            // Bottom navigation
            $(document).on('click', '.mobile-bottom-nav__item', function() {
                const tab = $(this).data('tab');
                
                // Remove active class from all items
                $('.mobile-bottom-nav__item').removeClass('active');
                // Add active class to clicked item
                $(this).addClass('active');
                
                self.handleBottomNavigation(tab);
            });
            
            // FAB click - handle menu toggle
            $(document).on('click', '.js-mobile-fab-toggle', function() {
                self.toggleFABMenu();
            });
            
            // FAB backdrop click - close menu
            $(document).on('click', '.mobile-fab-backdrop', function() {
                self.closeFABMenu();
            });
            
            // Mobile navigation menu
            $(document).on('click', '.js-mobile-menu-toggle', function() {
                self.openMobileNavMenu();
            });
            
            $(document).on('click', '.js-mobile-nav-close', function() {
                self.closeMobileNavMenu();
            });
            
            // Contact card interactions
            $(document).on('click', '.mobile-contact-card', function(e) {
                if (!$(e.target).closest('.mobile-contact-card__checkbox, .mobile-contact-card__actions').length) {
                    self.handleContactCardClick($(this));
                }
            });
            
            // Long press for bulk selection
            $(document).on('touchstart', '.mobile-contact-card', function(e) {
                self.handleLongPress($(this), e);
            });
            
            // Infinite scroll
            $(window).on('scroll', function() {
                self.handleScroll();
            });
            
            // Pull to refresh
            this.setupPullToRefresh();
            
            // Handle orientation change
            $(window).on('orientationchange', function() {
                setTimeout(function() {
                    self.handleOrientationChange();
                }, 100);
            });
            
            // FAB scroll behavior
            this.setupFABScrollBehavior();
        },
        
        // Set up interceptors for existing DT functionality
        setupInterceptors: function() {
            const self = this;
            
            // Intercept existing search API calls if they exist
            if (window.list_api && window.list_api.search) {
                const originalSearch = window.list_api.search;
                window.list_api.search = function(query) {
                    if (self.isMobileView()) {
                        self.search(query);
                    } else {
                        originalSearch.call(this, query);
                    }
                };
            }
            
            // Intercept filter API calls
            if (window.filter_api && window.filter_api.apply) {
                const originalApplyFilter = window.filter_api.apply;
                window.filter_api.apply = function(filters) {
                    if (self.isMobileView()) {
                        self.applyFilters(filters);
                    } else {
                        originalApplyFilter.call(this, filters);
                    }
                };
            }
        },
        
        // Load post type settings
        loadPostTypeSettings: function() {
            this.state.currentPostType = this.getCurrentPostType();
        },
        
        // Search functionality
        search: function(query) {
            this.state.searchQuery = query;
            this.state.currentPage = 1;
            this.loadContacts(true);
        },
        
        // Clear search
        clearSearch: function() {
            $('#mobile-search-input').val('');
            this.search('');
        },
        
        // Apply filters
        applyFilters: function(filters) {
            this.state.activeFilters = filters || {};
            this.state.currentPage = 1;
            this.loadContacts(true);
        },
        
        // Load contacts/records
        loadContacts: function(reset = false) {
            if (this.state.isLoading) return Promise.resolve();
            
            // Check if makeRequestOnPosts is available
            if (typeof window.makeRequestOnPosts !== 'function') {
                console.error('Mobile API: makeRequestOnPosts function not available. Make sure shared-functions.js is loaded.');
                this.handleError('Required functions not loaded. Please refresh the page.', null);
                return Promise.resolve();
            }
            
            const self = this;
            this.state.isLoading = true;
            
            if (reset) {
                this.state.currentPage = 1;
                this.showLoading(true);
            }
            
            const requestData = {
                offset: (this.state.currentPage - 1) * this.config.itemsPerPage,
                limit: this.config.itemsPerPage
            };
            
            // Add search if present
            if (this.state.searchQuery) {
                requestData.s = this.state.searchQuery;
            }
            
            // Add filters if present
            if (this.state.activeFilters && Object.keys(this.state.activeFilters).length > 0) {
                Object.assign(requestData, this.state.activeFilters);
            }
            
            // Add sorting
            if (this.state.sortField) {
                // DT expects sort direction to be included in the sort parameter
                // Use minus sign for descending order
                const sortValue = this.state.sortOrder === 'desc' ? 
                    `-${this.state.sortField}` : this.state.sortField;
                requestData.sort = sortValue;
            }
            
            console.log('Mobile API: Loading contacts with data:', requestData);
            
            // Try using GET request with the direct post type endpoint first
            // This matches the pattern used by other DT list implementations
            const urlParams = new URLSearchParams(requestData).toString();
            const endpoint = `${this.state.currentPostType}?${urlParams}`;
            
            console.log('Mobile API: Making request to endpoint:', endpoint);
            
            // Use DT's posts API endpoint with GET method
            const primaryRequest = window.makeRequestOnPosts('GET', endpoint, {});
            
            primaryRequest.done(function(response) {
                console.log('Mobile API: Received response:', response);
                self.handleContactsResponse(response, reset);
            }).fail(function(xhr) {
                console.error('Mobile API: Primary request failed:', xhr);
                console.error('Response Text:', xhr.responseText);
                console.error('Status:', xhr.status, xhr.statusText);
                
                // Try POST to /list endpoint as fallback
                console.log('Mobile API: Trying POST to /list endpoint as fallback');
                window.makeRequestOnPosts('POST', self.state.currentPostType + '/list', requestData)
                    .done(function(response) {
                        console.log('Mobile API: Fallback request succeeded:', response);
                        self.handleContactsResponse(response, reset);
                    }).fail(function(xhr2) {
                        console.error('Mobile API: Fallback request also failed:', xhr2);
                        self.handleError('Error loading contacts', xhr2);
                    }).always(function() {
                        self.state.isLoading = false;
                        self.showLoading(false);
                    });
            }).always(function() {
                // Only set loading false if the primary request succeeded
                if (primaryRequest.state() === 'resolved') {
                    self.state.isLoading = false;
                    self.showLoading(false);
                }
            });
            
            return primaryRequest;
        },
        
        // Handle contacts API response
        handleContactsResponse: function(response, reset) {
            const contacts = response.posts || [];
            const total = response.total || 0;
            const hasMore = total > (this.state.currentPage * this.config.itemsPerPage);
            
            this.state.hasMoreData = hasMore;
            
            if (reset) {
                this.clearContactList();
            }
            
            if (contacts.length === 0 && this.state.currentPage === 1) {
                this.showEmptyState();
            } else {
                this.hideEmptyState();
                this.renderContacts(contacts);
            }
            
            this.updateLoadMoreButton();
            this.state.currentPage++;
        },
        
        // Render contacts in mobile cards
        renderContacts: function(contacts) {
            const container = $('#mobile-contact-cards');
            
            contacts.forEach(contact => {
                const cardHtml = this.createContactCardHTML(contact);
                container.append(cardHtml);
            });
            
            // Trigger card animations
            this.animateNewCards();
        },
        
        // Create contact card HTML
        createContactCardHTML: function(contact) {
            const name = contact.post_title || contact.title || 'Unknown';
            const id = contact.ID;
            const status = contact.overall_status || {};
            const assignedTo = contact.assigned_to || {};
            const lastModified = contact.last_modified?.formatted || '';
            
            // Get contact methods - DT stores these as arrays of objects
            let email = '';
            let phone = '';
            
            if (contact.contact_email && Array.isArray(contact.contact_email)) {
                const emailObj = contact.contact_email.find(e => e.value);
                email = emailObj ? emailObj.value : '';
            } else if (contact.contact_email && contact.contact_email.values && Array.isArray(contact.contact_email.values)) {
                const emailObj = contact.contact_email.values.find(e => e.value);
                email = emailObj ? emailObj.value : '';
            }
            
            if (contact.contact_phone && Array.isArray(contact.contact_phone)) {
                const phoneObj = contact.contact_phone.find(p => p.value);
                phone = phoneObj ? phoneObj.value : '';
            } else if (contact.contact_phone && contact.contact_phone.values && Array.isArray(contact.contact_phone.values)) {
                const phoneObj = contact.contact_phone.values.find(p => p.value);
                phone = phoneObj ? phoneObj.value : '';
            }
            
            return `
                <div class="mobile-contact-card" data-contact-id="${id}">
                    <div class="mobile-contact-card__container">
                        <div class="mobile-contact-card__checkbox">
                            <input type="checkbox" id="contact-${id}" class="mobile-contact-card__checkbox-input" value="${id}">
                            <label for="contact-${id}" class="mobile-contact-card__checkbox-label"></label>
                        </div>
                        <div class="mobile-contact-card__content">
                            <div class="mobile-contact-card__header">
                                <div class="mobile-contact-card__avatar">
                                    <div class="mobile-contact-card__avatar-placeholder">
                                        ${name.charAt(0).toUpperCase()}
                                    </div>
                                </div>
                                <div class="mobile-contact-card__header-info">
                                    <h3 class="mobile-contact-card__name">
                                        <a href="/${this.state.currentPostType}/${id}" class="mobile-contact-card__name-link">
                                            ${this.escapeHtml(name)}
                                        </a>
                                    </h3>
                                    ${status.label ? `
                                        <div class="mobile-contact-card__status">
                                            <span class="mobile-contact-card__status-badge status-${status.key || ''}">
                                                ${this.escapeHtml(status.label)}
                                            </span>
                                            ${lastModified ? `<span class="mobile-contact-card__timestamp">• ${this.escapeHtml(lastModified)}</span>` : ''}
                                        </div>
                                    ` : ''}
                                </div>
                                <div class="mobile-contact-card__actions">
                                    <button class="mobile-contact-card__menu-btn js-contact-menu-toggle" data-contact-id="${id}">
                                        <svg class="mobile-contact-card__menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            ${(email || phone || assignedTo.display) ? `
                                <div class="mobile-contact-card__details">
                                    ${email ? `
                                        <div class="mobile-contact-card__detail-item">
                                            <div class="mobile-contact-card__detail-icon">
                                                <svg class="tw-h-4 tw-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                                </svg>
                                            </div>
                                            <a href="mailto:${email}" class="mobile-contact-card__detail-link">
                                                ${this.escapeHtml(email)}
                                            </a>
                                        </div>
                                    ` : ''}
                                    ${phone ? `
                                        <div class="mobile-contact-card__detail-item">
                                            <div class="mobile-contact-card__detail-icon">
                                                <svg class="tw-h-4 tw-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                            </div>
                                            <a href="tel:${phone}" class="mobile-contact-card__detail-link">
                                                ${this.escapeHtml(phone)}
                                            </a>
                                        </div>
                                    ` : ''}
                                    ${assignedTo.display ? `
                                        <div class="mobile-contact-card__detail-item">
                                            <div class="mobile-contact-card__detail-icon">
                                                <svg class="tw-h-4 tw-w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <span class="mobile-contact-card__detail-text">
                                                ${this.escapeHtml(assignedTo.display)}
                                            </span>
                                        </div>
                                    ` : ''}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        },
        
        // Handle bottom navigation
        handleBottomNavigation: function(tab) {
            switch(tab) {
                case 'all':
                    this.clearFilters();
                    break;
                case 'filter':
                    if (window.mobileFilterPanel) {
                        window.mobileFilterPanel.open();
                    }
                    break;
                case 'sort':
                    this.showSortOptions();
                    break;
                case 'bulk':
                    this.toggleBulkMode();
                    break;
            }
        },
        
        // Toggle FAB menu
        toggleFABMenu: function() {
            const $fabContainer = $('.mobile-fab-container');
            const $fab = $('.mobile-fab');
            const $menu = $('.mobile-fab-menu');
            const $backdrop = $('.mobile-fab-backdrop');
            const $plusIcon = $('.mobile-fab__icon--plus');
            const $closeIcon = $('.mobile-fab__icon--close');
            
            if ($fab.hasClass('active')) {
                this.closeFABMenu();
            } else {
                this.openFABMenu();
            }
        },
        
        // Open FAB menu
        openFABMenu: function() {
            const $fab = $('.mobile-fab');
            const $menu = $('.mobile-fab-menu');
            const $backdrop = $('.mobile-fab-backdrop');
            const $plusIcon = $('.mobile-fab__icon--plus');
            const $closeIcon = $('.mobile-fab__icon--close');
            
            $fab.addClass('active');
            $menu.show().addClass('active');
            $backdrop.show().addClass('active');
            $plusIcon.hide();
            $closeIcon.show();
            
            // Add haptic feedback if available
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        },
        
        // Close FAB menu
        closeFABMenu: function() {
            const $fab = $('.mobile-fab');
            const $menu = $('.mobile-fab-menu');
            const $backdrop = $('.mobile-fab-backdrop');
            const $plusIcon = $('.mobile-fab__icon--plus');
            const $closeIcon = $('.mobile-fab__icon--close');
            
            $fab.removeClass('active');
            $menu.removeClass('active');
            $backdrop.removeClass('active');
            $plusIcon.show();
            $closeIcon.hide();
            
            setTimeout(() => {
                $menu.hide();
                $backdrop.hide();
            }, 300);
        },
        
        // Handle contact card click
        handleContactCardClick: function($card) {
            const contactId = $card.data('contact-id');
            const isBulkMode = $('.mobile-contact-list').hasClass('bulk-mode');
            
            if (isBulkMode) {
                this.toggleContactSelection($card);
            } else {
                window.location.href = `/${this.state.currentPostType}/${contactId}`;
            }
        },
        
        // Handle long press for bulk selection
        handleLongPress: function($card, e) {
            const self = this;
            let pressTimer;
            
            const startTouch = function() {
                pressTimer = setTimeout(function() {
                    self.enterBulkMode();
                    self.toggleContactSelection($card);
                }, 500);
            };
            
            const cancelTouch = function() {
                clearTimeout(pressTimer);
            };
            
            $(document).one('touchend touchmove', cancelTouch);
            startTouch();
        },
        
        // Toggle bulk selection mode
        toggleBulkMode: function() {
            const $list = $('.mobile-contact-list');
            
            if ($list.hasClass('bulk-mode')) {
                this.exitBulkMode();
            } else {
                this.enterBulkMode();
            }
        },
        
        // Enter bulk selection mode
        enterBulkMode: function() {
            $('.mobile-contact-list').addClass('bulk-mode');
            $('.mobile-bottom-nav__item[data-tab="bulk"]').addClass('active');
            this.updateBulkSelectionUI();
        },
        
        // Exit bulk selection mode
        exitBulkMode: function() {
            $('.mobile-contact-list').removeClass('bulk-mode');
            $('.mobile-contact-card').removeClass('selected');
            $('.mobile-contact-card__checkbox-input').prop('checked', false);
            $('.mobile-bottom-nav__item[data-tab="bulk"]').removeClass('active');
            this.updateBulkSelectionUI();
        },
        
        // Toggle contact selection
        toggleContactSelection: function($card) {
            const $checkbox = $card.find('.mobile-contact-card__checkbox-input');
            
            $card.toggleClass('selected');
            $checkbox.prop('checked', $card.hasClass('selected'));
            
            this.updateBulkSelectionUI();
        },
        
        // Update bulk selection UI
        updateBulkSelectionUI: function() {
            const selectedCount = $('.mobile-contact-card.selected').length;
            const $bulkInfo = $('#mobile-bulk-info');
            const $bulkCount = $('.mobile-contact-list__bulk-count');
            
            if (selectedCount > 0) {
                $bulkInfo.show();
                $bulkCount.text(`${selectedCount} selected`);
            } else {
                $bulkInfo.hide();
            }
        },
        
        // Handle infinite scroll
        handleScroll: function() {
            if (this.state.isLoading || !this.state.hasMoreData) return;
            
            const scrollPosition = $(window).scrollTop() + $(window).height();
            const documentHeight = $(document).height();
            
            if (scrollPosition >= documentHeight - this.config.loadMoreThreshold) {
                this.loadContacts();
            }
        },
        
        // Set up pull to refresh
        setupPullToRefresh: function() {
            let startY = 0;
            let currentY = 0;
            let isRefreshing = false;
            const refreshThreshold = 100;
            
            $(document).on('touchstart', function(e) {
                // Add safety check for touch events
                if (e.originalEvent && e.originalEvent.touches && e.originalEvent.touches.length > 0) {
                    if ($(window).scrollTop() === 0) {
                        startY = e.originalEvent.touches[0].clientY;
                    }
                }
            });
            
            $(document).on('touchmove', function(e) {
                // Add safety check for touch events
                if (e.originalEvent && e.originalEvent.touches && e.originalEvent.touches.length > 0) {
                    if ($(window).scrollTop() === 0 && !isRefreshing) {
                        currentY = e.originalEvent.touches[0].clientY;
                        const pullDistance = currentY - startY;
                        
                        if (pullDistance > 0) {
                            e.preventDefault();
                            // Show pull indicator
                            if (pullDistance > refreshThreshold) {
                                // Change indicator to "release to refresh"
                            }
                        }
                    }
                }
            });
            
            $(document).on('touchend', function(e) {
                if ($(window).scrollTop() === 0 && !isRefreshing) {
                    const pullDistance = currentY - startY;
                    
                    if (pullDistance > refreshThreshold) {
                        isRefreshing = true;
                        // Show refresh indicator
                        mobileAPI.refreshContacts().always(function() {
                            isRefreshing = false;
                            // Hide refresh indicator
                        });
                    }
                }
                
                startY = 0;
                currentY = 0;
            });
        },
        
        // Refresh contacts
        refreshContacts: function() {
            return this.loadContacts(true);
        },
        
        // Sort contacts
        sort: function(field, order) {
            this.state.sortField = field;
            this.state.sortOrder = order;
            this.loadContacts(true);
        },
        
        // Show sort options
        showSortOptions: function() {
            // This could be enhanced with a proper modal
            const options = [
                { field: 'post_date', order: 'desc', label: 'Newest' },
                { field: 'post_date', order: 'asc', label: 'Oldest' },
                { field: 'last_modified', order: 'desc', label: 'Recently Modified' },
                { field: 'last_modified', order: 'asc', label: 'Least Recently Modified' }
            ];
            
            const choice = prompt('Sort by:\n' + options.map((opt, i) => `${i + 1}. ${opt.label}`).join('\n'));
            
            if (choice && !isNaN(choice) && choice >= 1 && choice <= options.length) {
                const selected = options[choice - 1];
                this.sort(selected.field, selected.order);
            }
        },
        
        // Clear filters
        clearFilters: function() {
            this.state.activeFilters = {};
            if (window.mobileFilterPanel) {
                window.mobileFilterPanel.setFilters({});
            }
            this.loadContacts(true);
        },
        
        // Utility functions
        getCurrentPostType: function() {
            const path = window.location.pathname;
            const matches = path.match(/\/(contacts|groups|locations)(?:\/|$)/);
            return matches ? matches[1] : 'contacts';
        },
        
        isMobileView: function() {
            // Check for forced mobile mode via URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('mobile') === '1') {
                return true;
            }
            
            return window.innerWidth <= 640 || /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        },
        
        showLoading: function(show) {
            const $loading = $('#mobile-contact-loading');
            const $cards = $('#mobile-contact-cards');
            
            if (show) {
                $loading.show();
            } else {
                $loading.hide();
            }
        },
        
        showEmptyState: function() {
            $('#mobile-contact-empty').show();
            $('#mobile-contact-cards').hide();
        },
        
        hideEmptyState: function() {
            $('#mobile-contact-empty').hide();
            $('#mobile-contact-cards').show();
        },
        
        clearContactList: function() {
            $('#mobile-contact-cards').empty();
        },
        
        updateLoadMoreButton: function() {
            const $loadMore = $('#mobile-load-more');
            
            if (this.state.hasMoreData && $('#mobile-contact-cards').children().length > 0) {
                $loadMore.show();
            } else {
                $loadMore.hide();
            }
        },
        
        animateNewCards: function() {
            // Add animation classes to new cards
            $('.mobile-contact-card:not(.animated)').addClass('animated fadeInUp');
        },
        
        handleOrientationChange: function() {
            // Recalculate layouts if needed
            this.updateLoadMoreButton();
        },
        
        // Setup FAB scroll behavior - REMOVED to keep FAB always visible
        setupFABScrollBehavior: function() {
            // Keep FAB always visible - removed scroll hiding behavior
            const $fab = $('.mobile-fab-container');
            if ($fab.length) {
                $fab.css({
                    'transform': 'translateY(0)',
                    'opacity': '1'
                });
            }
        },
        
        handleError: function(message, xhr) {
            console.error('Mobile API Error:', message, xhr);
            
            let errorMsg = message;
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr && xhr.statusText) {
                errorMsg = `${message}: ${xhr.statusText}`;
            }
            
            // Show user-friendly error message
            const errorHtml = `
                <div class="mobile-error-message">
                    <div class="mobile-error-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <p class="mobile-error-text">${this.escapeHtml(errorMsg)}</p>
                    <button class="mobile-error-retry" onclick="window.mobileAPI.refreshContacts()">Try Again</button>
                </div>
            `;
            
            $('#mobile-contact-cards').html(errorHtml);
            this.hideEmptyState();
            this.showLoading(false);
        },
        
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = function() {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        // Open mobile navigation menu
        openMobileNavMenu: function() {
            const $navMenu = $('#mobile-nav-menu');
            $navMenu.addClass('active');
            $('body').addClass('mobile-nav-open');
            
            // Add haptic feedback if available
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        },
        
        // Close mobile navigation menu
        closeMobileNavMenu: function() {
            const $navMenu = $('#mobile-nav-menu');
            $navMenu.removeClass('active');
            $('body').removeClass('mobile-nav-open');
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        if (mobileAPI.isMobileView()) {
            mobileAPI.init();
            mobileAPI.loadContacts(true);
        }
    });
    
    // Expose additional mobile utilities
    window.mobileUtils = {
        vibrate: function(pattern = [100]) {
            if (navigator.vibrate) {
                navigator.vibrate(pattern);
            }
        },
        
        hapticFeedback: function(type = 'impact') {
            // iOS Haptic Feedback
            if (window.navigator && window.navigator.vibrate) {
                switch(type) {
                    case 'selection':
                        window.navigator.vibrate(10);
                        break;
                    case 'impact':
                        window.navigator.vibrate(50);
                        break;
                    case 'notification':
                        window.navigator.vibrate([100, 50, 100]);
                        break;
                }
            }
        },
        
        isStandalone: function() {
            return window.matchMedia('(display-mode: standalone)').matches ||
                   window.navigator.standalone === true;
        },
        
        getDeviceInfo: function() {
            return {
                userAgent: navigator.userAgent,
                platform: navigator.platform,
                standalone: this.isStandalone(),
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            };
        }
    };
    
})(jQuery);
