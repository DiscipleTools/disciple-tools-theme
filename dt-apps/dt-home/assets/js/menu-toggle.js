/**
 * Menu Toggle Component
 *
 * Vanilla JavaScript implementation for hamburger menu toggle
 * Switches between hamburger and close icons, shows/hides floating menu
 */

/**
 * Invite Modal Component
 *
 * Vanilla JavaScript implementation for invite modal
 * Displays share URL with copy functionality
 */
class InviteModal {
  constructor() {
    this.isOpen = false;
    this.shareUrl = '';
    this.modalElement = null;
    this.isDarkMode = false;
    this.escapeHandler = null;

    // Generate share URL
    this.shareUrl = window.location.href;

    // Bind escape handler to instance
    this.escapeHandler = this.handleEscape.bind(this);
  }

  /**
   * Handles Escape key press
   */
  handleEscape(e) {
    if (e.key === 'Escape' && this.isOpen) {
      this.close();
    }
  }

  /**
   * Detects if dark mode is active
   */
  detectDarkMode() {
    return (
      document.body.classList.contains('theme-dark') ||
      document.body.classList.contains('dark-mode') ||
      document.querySelector('.theme-dark') !== null
    );
  }

  /**
   * Applies theme-specific styles
   */
  applyThemeStyles() {
    this.isDarkMode = this.detectDarkMode();
    if (!this.modalElement) return;

    // Just add/remove theme class - styles are handled by inline CSS in PHP
    if (this.isDarkMode) {
      this.modalElement.classList.add('theme-dark');
    } else {
      this.modalElement.classList.remove('theme-dark');
    }
  }

  /**
   * Creates the modal HTML structure
   */
  createModalHTML() {
    const modal = document.createElement('div');
    modal.className = 'invite-modal-overlay';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-labelledby', 'invite-modal-title');
    modal.setAttribute('aria-modal', 'true');

    const isDark = this.detectDarkMode();
    const themeClass = isDark ? ' theme-dark' : '';

    modal.innerHTML = `
      <div class="invite-modal-content${themeClass}">
        <div class="invite-modal-header">
          <h2 id="invite-modal-title" class="invite-modal-title">Invite</h2>
          <div class="invite-modal-separator"></div>
        </div>
        <div class="invite-modal-body">
          <p class="invite-explanation-text">
            Copy this link and share it with people you are coaching. They will create their own account and have their own Home Screen.
          </p>
          <div class="invite-share-link-container">
            <input 
              type="text" 
              class="invite-url-input" 
              value="${this.shareUrl}" 
              readonly 
              aria-label="Share URL"
            />
            <button 
              type="button" 
              class="invite-copy-button"
              aria-label="Copy link to clipboard"
            >
              Copy
            </button>
          </div>
          <div class="invite-success-message">
            Link copied!
          </div>
        </div>
        <div class="invite-modal-footer">
          <button 
            type="button" 
            class="invite-close-button"
            aria-label="Close invite modal"
          >
            Close
          </button>
        </div>
      </div>
    `;

    // Prevent click propagation on modal content
    const modalContent = modal.querySelector('.invite-modal-content');
    if (modalContent) {
      modalContent.addEventListener('click', (e) => {
        e.stopPropagation();
      });
    }

    return modal;
  }

  /**
   * Binds event listeners to the modal
   */
  bindEvents() {
    if (!this.modalElement) return;

    // Close button
    const closeButton = this.modalElement.querySelector('.invite-close-button');
    if (closeButton) {
      closeButton.addEventListener('click', () => this.close());
    }

    // Copy button
    const copyButton = this.modalElement.querySelector('.invite-copy-button');
    if (copyButton) {
      copyButton.addEventListener('click', () => this.copyToClipboard());
    }

    // Close on overlay click
    this.modalElement.addEventListener('click', (e) => {
      if (e.target === this.modalElement) {
        this.close();
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', this.escapeHandler);
  }

  /**
   * Opens the modal
   */
  open() {
    if (this.isOpen) return;

    this.isOpen = true;
    this.modalElement = this.createModalHTML();
    document.body.appendChild(this.modalElement);

    // Apply theme styles
    this.applyThemeStyles();

    // Bind events
    this.bindEvents();

    // Prevent body scroll
    document.body.style.overflow = 'hidden';

    // Focus management - focus the close button for accessibility
    setTimeout(() => {
      const closeButton = this.modalElement.querySelector(
        '.invite-close-button',
      );
      if (closeButton) {
        closeButton.focus();
      }
    }, 100);
  }

  /**
   * Closes the modal
   */
  close() {
    if (!this.isOpen || !this.modalElement) return;

    this.isOpen = false;

    // Remove escape key listener
    if (this.escapeHandler) {
      document.removeEventListener('keydown', this.escapeHandler);
    }

    // Remove modal from DOM
    if (this.modalElement.parentNode) {
      this.modalElement.parentNode.removeChild(this.modalElement);
    }

    this.modalElement = null;

    // Restore body scroll
    document.body.style.overflow = '';
  }

  /**
   * Copies the share URL to clipboard
   */
  async copyToClipboard() {
    try {
      await navigator.clipboard.writeText(this.shareUrl);
      this.showCopySuccess();
    } catch (err) {
      // Fallback for older browsers
      this.fallbackCopy();
    }
  }

  /**
   * Fallback copy method for older browsers
   */
  fallbackCopy() {
    const textArea = document.createElement('textarea');
    textArea.value = this.shareUrl;
    textArea.style.position = 'fixed';
    textArea.style.left = '-9999px';
    textArea.style.top = '-9999px';
    document.body.appendChild(textArea);
    textArea.select();
    try {
      document.execCommand('copy');
      this.showCopySuccess();
    } catch (err) {
      console.error('Failed to copy:', err);
    } finally {
      document.body.removeChild(textArea);
    }
  }

  /**
   * Shows success message after copying
   */
  showCopySuccess() {
    const successMessage = this.modalElement.querySelector(
      '.invite-success-message',
    );
    if (successMessage) {
      successMessage.classList.add('show');
      setTimeout(() => {
        successMessage.classList.remove('show');
      }, 2000);
    }
  }
}

class MenuToggle {
  constructor() {
    this.isMenuOpen = false;
    this.menuButton = null;
    this.menuIcon = null;
    this.floatingMenu = null;
    this.inviteModal = new InviteModal();

    this.init();
  }

  init() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setupElements());
    } else {
      this.setupElements();
    }
  }

  setupElements() {
    this.menuButton = document.getElementById('menu-toggle-button');
    this.menuIcon = document.getElementById('menu-icon');
    this.floatingMenu = document.getElementById('floating-menu');
    this.backdrop = null;

    if (this.menuButton && this.menuIcon && this.floatingMenu) {
      // Create menu items immediately but menu starts hidden
      this.createMenuItems();
      this.floatingMenu.setAttribute('role', 'menu');
      this.floatingMenu.setAttribute('aria-hidden', 'true');
      // Ensure menu is completely hidden on init
      this.floatingMenu.classList.remove('active');
      this.floatingMenu.style.display = 'none';
      this.floatingMenu.style.opacity = '0';
      this.floatingMenu.style.visibility = 'hidden';
      this.floatingMenu.style.pointerEvents = 'none';
      this.menuButton.setAttribute('aria-haspopup', 'menu');
      this.menuButton.setAttribute('aria-expanded', 'false');
      this.bindEvents();
    } else {
      console.warn('Menu toggle elements not found');
    }
  }

  createMenuItems() {
    // Get current view from URL
    const params = new URLSearchParams(window.location.search);
    const currentView = (params.get('view') || 'apps').toLowerCase();

    // Build base URL without view parameter
    const url = new URL(window.location.href);
    url.searchParams.delete('view');
    const baseUrl = url.pathname + url.search;
    const separator = url.search ? '&' : '?';

    const logoutUrl =
      (window.dtHomeMenuToggleSettings &&
        window.dtHomeMenuToggleSettings.logoutUrl) ||
      '/wp-login.php?action=logout';

    // Create menu items
    const menuItems = [
      {
        id: 'menu-apps',
        label: 'Apps',
        icon: 'mdi-apps',
        action: () => {
          window.location.href = baseUrl + separator + 'view=apps';
        },
        active: currentView === 'apps',
      },
      {
        id: 'menu-training',
        label: 'Training',
        icon: 'mdi-play-circle',
        action: () => {
          window.location.href = baseUrl + separator + 'view=training';
        },
        active: currentView === 'training',
      },
      {
        id: 'menu-invite',
        label: 'Invite',
        icon: 'mdi-account-plus',
        action: () => {
          this.openInviteModal();
        },
        active: false,
      },
      {
        id: 'menu-logout',
        label: 'Logout',
        icon: 'mdi-logout',
        action: () => {
          if (!logoutUrl) {
            console.warn('Logout URL not set');
            return;
          }
          const targetWindow = window.top || window;
          targetWindow.location.href = logoutUrl;
        },
        active: false,
      },
    ];

    // Clear existing menu items
    this.floatingMenu.innerHTML = '';

    // Create and append menu items
    menuItems.forEach((item) => {
      const menuItem = document.createElement('button');
      menuItem.className = 'menu-item';
      menuItem.id = item.id;
      menuItem.setAttribute('role', 'menuitem');
      menuItem.setAttribute('tabindex', '-1');
      menuItem.innerHTML = `
        <i class="mdi ${item.icon}"></i>
        <span>${item.label}</span>
      `;

      if (item.active) {
        menuItem.classList.add('active');
      }

      menuItem.addEventListener('click', (e) => {
        e.stopPropagation();
        item.action();
        this.closeMenu();
      });

      this.floatingMenu.appendChild(menuItem);
    });
  }

  bindEvents() {
    // Toggle menu on button click
    this.menuButton.addEventListener('click', (e) => {
      e.stopPropagation();
      this.toggleMenu();
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (
        this.isMenuOpen &&
        !this.floatingMenu.contains(e.target) &&
        !this.menuButton.contains(e.target)
      ) {
        this.closeMenu();
      }
    });

    // Close menu on escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.isMenuOpen) {
        this.closeMenu();
      }
      if (this.isMenuOpen && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
        e.preventDefault();
        const items = Array.from(
          this.floatingMenu.querySelectorAll('.menu-item'),
        );
        if (!items.length) return;
        const current = document.activeElement;
        let idx = items.indexOf(current);
        if (idx === -1) idx = 0;
        if (e.key === 'ArrowDown') idx = (idx + 1) % items.length;
        if (e.key === 'ArrowUp') idx = (idx - 1 + items.length) % items.length;
        items[idx].focus();
      }
    });
  }

  toggleMenu() {
    if (this.isMenuOpen) {
      this.closeMenu();
    } else {
      this.openMenu();
    }
  }

  openMenu() {
    console.log('openMenu called, current state:', {
      isMenuOpen: this.isMenuOpen,
      hasActiveClass: this.floatingMenu.classList.contains('active'),
      ariaHidden: this.floatingMenu.getAttribute('aria-hidden'),
    });

    this.isMenuOpen = true;

    // Change icon from hamburger to close - explicitly set className to ensure clean state
    // Include dt-menu-icon class for theme color support, but set explicitly to avoid overlay
    this.menuIcon.className = 'mdi mdi-close dt-menu-icon';

    // Ensure menu items are created if not already
    if (!this.floatingMenu.children.length) {
      console.log('Creating menu items...');
      this.createMenuItems();
    }

    // Position floating menu relative to the button (align right, below)
    this.positionMenu();

    // Show floating menu - use both classList and direct style to ensure it shows
    this.floatingMenu.classList.add('active');
    this.floatingMenu.setAttribute('aria-hidden', 'false');
    this.menuButton.setAttribute('aria-expanded', 'true');

    // Force visibility with inline styles as backup
    this.floatingMenu.style.opacity = '1';
    this.floatingMenu.style.visibility = 'visible';
    this.floatingMenu.style.transform = 'translateY(0)';
    this.floatingMenu.style.pointerEvents = 'auto';
    this.floatingMenu.style.display = 'flex';
    this.floatingMenu.style.flexDirection = 'column';

    console.log('Menu opened, styles applied:', {
      className: this.floatingMenu.className,
      computedOpacity: window.getComputedStyle(this.floatingMenu).opacity,
      computedVisibility: window.getComputedStyle(this.floatingMenu).visibility,
      computedDisplay: window.getComputedStyle(this.floatingMenu).display,
    });

    // Create backdrop to close on outside click (covers the page)
    this.createBackdrop();

    // Focus first item for keyboard users
    const firstItem = this.floatingMenu.querySelector('.menu-item');
    if (firstItem) {
      firstItem.focus();
    }

    // Update aria-label
    this.menuButton.setAttribute('aria-label', 'Close menu');
    this.menuButton.setAttribute('title', 'Close menu');
  }

  closeMenu() {
    this.isMenuOpen = false;

    // Change icon from close to hamburger - explicitly set className to ensure clean state
    // Include dt-menu-icon class for theme color support, but set explicitly to avoid overlay
    this.menuIcon.className = 'mdi mdi-menu dt-menu-icon';

    // Hide floating menu - remove class and reset inline styles
    this.floatingMenu.classList.remove('active');
    this.floatingMenu.setAttribute('aria-hidden', 'true');
    this.menuButton.setAttribute('aria-expanded', 'false');

    // Force hide with inline styles
    this.floatingMenu.style.opacity = '0';
    this.floatingMenu.style.visibility = 'hidden';
    this.floatingMenu.style.transform = 'translateY(-10px)';
    this.floatingMenu.style.pointerEvents = 'none';

    // Remove backdrop
    if (this.backdrop) {
      this.backdrop.remove();
      this.backdrop = null;
    }

    // Update aria-label
    this.menuButton.setAttribute('aria-label', 'Toggle menu');
    this.menuButton.setAttribute('title', 'Toggle menu');

    // Return focus to the toggle button
    this.menuButton.focus();
  }

  positionMenu() {
    // Position the floating menu directly under the menu toggle button
    const btnRect = this.menuButton.getBoundingClientRect();
    const menu = this.floatingMenu;

    // First, ensure menu has some content to measure
    if (!menu.children.length) {
      console.warn('Menu has no items to display');
      return;
    }

    // Temporarily show and measure (without transition for accurate measurement)
    const prevPosition = menu.style.position;
    const prevVisibility = menu.style.visibility;
    const prevDisplay = menu.style.display;
    const prevOpacity = menu.style.opacity;

    menu.style.position = 'fixed';
    menu.style.visibility = 'visible';
    menu.style.display = 'flex';
    menu.style.flexDirection = 'column';
    menu.style.opacity = '1';
    menu.style.top = '-9999px';
    menu.style.left = '-9999px';

    // Force a reflow to get accurate measurements
    menu.offsetHeight;

    const menuWidth = menu.offsetWidth || 220;
    const menuHeight = menu.offsetHeight || 10;

    // Calculate position - center directly under the button
    // Horizontal: align center of menu with center of button
    let left = btnRect.left + btnRect.width / 2 - menuWidth / 2;
    const padding = 8;

    // Ensure menu stays within viewport
    if (left < padding) {
      left = padding;
    }
    if (left + menuWidth > window.innerWidth - padding) {
      left = window.innerWidth - menuWidth - padding;
    }

    // Vertical: directly below the button with small gap
    let top = btnRect.bottom + 8;

    // If menu would overflow bottom, position above button instead
    if (top + menuHeight + padding > window.innerHeight) {
      top = btnRect.top - menuHeight - 8;
    }

    // Apply positioning
    menu.style.left = `${left}px`;
    menu.style.top = `${top}px`;
    menu.style.position = 'fixed';

    // Restore visibility to be controlled by CSS 'active' class
    menu.style.visibility = '';
    menu.style.display = '';
    menu.style.opacity = '';

    console.log('Menu positioned at:', {
      left,
      top,
      width: menuWidth,
      height: menuHeight,
    });
  }

  createBackdrop() {
    if (this.backdrop) return;
    const backdrop = document.createElement('div');
    backdrop.className = 'menu-backdrop';
    backdrop.addEventListener('click', () => this.closeMenu());
    document.body.appendChild(backdrop);
    this.backdrop = backdrop;
  }

  /**
   * Opens the invite modal
   */
  openInviteModal() {
    if (this.inviteModal) {
      this.inviteModal.open();
    }
  }
}

// Make InviteModal available globally
window.InviteModal = InviteModal;

// Initialize menu toggle when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  // Only initialize if we're on the home screen or training screen
  if (
    document.querySelector('.home-screen-container') ||
    document.querySelector('.training-screen-container')
  ) {
    try {
      console.log('Initializing menu toggle...');
      window.menuToggleInstance = new MenuToggle();
      console.log('Menu toggle initialized successfully');
    } catch (error) {
      console.error('Error initializing menu toggle:', error);
    }
  }
});

// Make MenuToggle available globally for manual initialization if needed
window.MenuToggle = MenuToggle;
