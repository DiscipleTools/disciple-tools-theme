/**
 * Theme Toggle Component
 *
 * Vanilla JavaScript implementation for dark/light theme switching
 * Integrates with the existing CSS variable system
 */

class ThemeToggle {
  constructor() {
    this.currentTheme = 'light';
    this.isTransitioning = false;
    this.systemPreferenceMediaQuery = null;

    this.init();
  }

  init() {
    this.initializeTheme();
    this.setupSystemPreferenceListener();
    this.createToggleButton();
    this.bindEvents();
  }

  initializeTheme() {
    // Check for saved preference first
    const savedTheme = localStorage.getItem('dt-home-theme');
    if (savedTheme) {
      this.currentTheme = savedTheme;
    } else {
      // Check system preference
      this.currentTheme = this.getSystemPreference();
    }
    this.applyTheme();
  }

  getSystemPreference() {
    if (
      window.matchMedia &&
      window.matchMedia('(prefers-color-scheme: dark)').matches
    ) {
      return 'dark';
    }
    return 'light';
  }

  setupSystemPreferenceListener() {
    if (window.matchMedia) {
      this.systemPreferenceMediaQuery = window.matchMedia(
        '(prefers-color-scheme: dark)',
      );
      this.systemPreferenceMediaQuery.addEventListener('change', () => {
        // Only update if no saved preference exists
        if (!localStorage.getItem('dt-home-theme')) {
          const newTheme = this.getSystemPreference();
          if (newTheme !== this.currentTheme) {
            this.currentTheme = newTheme;
            this.applyTheme();
          }
        }
      });
    }
  }

  createToggleButton() {
    // Create the toggle button element
    const toggleButton = document.createElement('button');
    toggleButton.type = 'button'; // Ensure it's a button, not a submit button
    toggleButton.className = 'theme-toggle-button';
    toggleButton.setAttribute(
      'aria-label',
      this.currentTheme === 'light'
        ? 'Switch to dark mode'
        : 'Switch to light mode',
    );
    toggleButton.setAttribute(
      'title',
      this.currentTheme === 'light'
        ? 'Switch to dark mode'
        : 'Switch to light mode',
    );

    // Add the icon
    const icon = document.createElement('i');
    icon.className =
      this.currentTheme === 'light'
        ? 'mdi mdi-weather-night theme-icon'
        : 'mdi mdi-white-balance-sunny theme-icon';
    toggleButton.appendChild(icon);

    // Add to the header controls (insert at the beginning so menu button stays on the right)
    const headerControls = document.querySelector('.header-controls');
    if (headerControls) {
      // Insert before the first child (menu button) to maintain order: theme toggle -> menu button
      const firstChild = headerControls.firstElementChild;
      if (firstChild) {
        headerControls.insertBefore(toggleButton, firstChild);
      } else {
        headerControls.appendChild(toggleButton);
      }
    }
  }

  bindEvents() {
    const toggleButton = document.querySelector('.theme-toggle-button');
    if (toggleButton) {
      toggleButton.addEventListener('click', () => this.toggleTheme());
    }
  }

  applyTheme() {
    // Update the html element class for early theme detection
    const html = document.documentElement;
    html.classList.remove('theme-light', 'theme-dark');
    html.classList.add(`theme-${this.currentTheme}`);

    // Update the document body class for theme detection
    if (document.body) {
      document.body.classList.remove('theme-light', 'theme-dark');
      document.body.classList.add(`theme-${this.currentTheme}`);
    }

    // Update CSS custom properties on the root element
    const root = document.documentElement;
    if (this.currentTheme === 'dark') {
      root.style.setProperty('--theme-mode', 'dark');
      root.style.setProperty('--body-background-color', '#1a1a1a');
      root.style.setProperty('--text-color', '#f5f5f5');
      root.style.setProperty('--surface-0', '#2a2a2a');
      root.style.setProperty('--surface-1', '#1a1a1a');
      root.style.setProperty('--surface-2', '#333333');
      root.style.setProperty('--primary-color', '#4a9eff');
      root.style.setProperty('--border-color', '#404040');
      root.style.setProperty('--shadow-color', 'rgba(0,0,0,0.3)');
      // App card CSS variables for dark mode
      root.style.setProperty('--app-card-bg', '#2a2a2a');
      root.style.setProperty('--app-card-border', '#404040');
      root.style.setProperty('--app-card-text', '#f5f5f5');
      root.style.setProperty('--app-card-shadow', 'rgba(0,0,0,0.3)');
      root.style.setProperty('--app-card-hover-border', '#4a9eff');
    } else {
      root.style.setProperty('--theme-mode', 'light');
      root.style.setProperty('--body-background-color', '#e2e2e2');
      root.style.setProperty('--text-color', '#0a0a0a');
      root.style.setProperty('--surface-0', '#e2e2e2');
      root.style.setProperty('--surface-1', 'hsla(0, 0%, 90%, 1)');
      root.style.setProperty('--surface-2', '#c2bfbf');
      root.style.setProperty('--primary-color', '#667eea');
      root.style.setProperty('--border-color', '#e1e5e9');
      root.style.setProperty('--shadow-color', 'rgba(0,0,0,0.1)');
      // App card CSS variables for light mode
      root.style.setProperty('--app-card-bg', '#ffffff');
      root.style.setProperty('--app-card-border', '#e1e5e9');
      root.style.setProperty('--app-card-text', '#0a0a0a');
      root.style.setProperty('--app-card-shadow', 'rgba(0,0,0,0.1)');
      root.style.setProperty('--app-card-hover-border', '#667eea');
    }

    // Also set on document root for broader compatibility
    document.documentElement.style.setProperty(
      '--theme-mode',
      this.currentTheme,
    );

    // Force body background color change
    document.body.style.backgroundColor =
      this.currentTheme === 'dark' ? '#1a1a1a' : '#e2e2e2';

    // Force direct style changes on key elements
    this.applyDirectStyles();

    // Save preference
    localStorage.setItem('dt-home-theme', this.currentTheme);

    // Update toggle button icon
    this.updateToggleButton();

    // Dispatch custom event for other components
    this.dispatchThemeChangeEvent();
  }

  applyDirectStyles() {
    // Apply styles directly to elements to ensure they take effect
    const container = document.querySelector('.home-screen-container');
    const content = document.querySelector('.home-screen-content');
    const appCards = document.querySelectorAll('.app-card');
    const appTitles = document.querySelectorAll('.app-title');
    const sectionTitles = document.querySelectorAll('.section-title');
    const sectionToggles = document.querySelectorAll('.section-toggle');

    if (this.currentTheme === 'dark') {
      // Dark mode styles
      if (container) {
        container.style.backgroundColor = '#1a1a1a';
        container.style.color = '#f5f5f5';
      }
      if (content) {
        //content.style.backgroundColor = '#1a1a1a';
        content.style.color = '#f5f5f5';
      }
      appCards.forEach((card, index) => {
        card.style.setProperty('background-color', '#1a1a1a', 'important');
        card.style.setProperty('border-color', '#404040', 'important');
        card.style.setProperty('color', '#f5f5f5', 'important');
        card.style.setProperty(
          'box-shadow',
          '0 1px 3px rgba(0,0,0,0.3)',
          'important',
        );
      });
      appTitles.forEach((title) => {
        title.style.color = '#f5f5f5';
      });
      sectionTitles.forEach((title) => {
        title.style.color = '#f5f5f5';
      });
      sectionToggles.forEach((toggle) => {
        toggle.style.color = '#4a9eff';
      });
    } else {
      // Light mode styles
      if (container) {
        container.style.backgroundColor = 'hsla(0, 0%, 90%, 1)';
        container.style.color = '#0a0a0a';
      }
      if (content) {
        //content.style.backgroundColor = 'hsla(0, 0%, 90%, 1)';
        content.style.color = '#0a0a0a';
      }
      appCards.forEach((card) => {
        card.style.removeProperty('background-color');
        card.style.removeProperty('border-color');
        card.style.removeProperty('color');
        card.style.removeProperty('box-shadow');
      });
      appTitles.forEach((title) => {
        title.style.color = '#0a0a0a';
      });
      sectionTitles.forEach((title) => {
        title.style.color = '#0a0a0a';
      });
      sectionToggles.forEach((toggle) => {
        toggle.style.color = '#667eea';
      });
    }
  }

  updateToggleButton() {
    const toggleButton = document.querySelector('.theme-toggle-button');
    const icon = toggleButton?.querySelector('.theme-icon');

    if (toggleButton && icon) {
      // Update aria labels
      toggleButton.setAttribute(
        'aria-label',
        this.currentTheme === 'light'
          ? 'Switch to dark mode'
          : 'Switch to light mode',
      );
      toggleButton.setAttribute(
        'title',
        this.currentTheme === 'light'
          ? 'Switch to dark mode'
          : 'Switch to light mode',
      );

      // Update icon
      icon.className =
        this.currentTheme === 'light'
          ? 'mdi mdi-weather-night theme-icon'
          : 'mdi mdi-white-balance-sunny theme-icon';
    }
  }

  async toggleTheme() {
    if (this.isTransitioning) return;

    this.isTransitioning = true;

    // Add transition class for smooth animation
    const toggleButton = document.querySelector('.theme-toggle-button');
    if (toggleButton) {
      toggleButton.classList.add('transitioning');
    }

    // Small delay for smooth transition
    await new Promise((resolve) => setTimeout(resolve, 150));

    this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
    this.applyTheme();

    // Reset transition state
    await new Promise((resolve) => setTimeout(resolve, 150));
    this.isTransitioning = false;

    if (toggleButton) {
      toggleButton.classList.remove('transitioning');
    }
  }

  dispatchThemeChangeEvent() {
    // Dispatch custom event for other components to listen to
    const event = new CustomEvent('theme-changed', {
      detail: { theme: this.currentTheme },
      bubbles: true,
      composed: true,
    });
    document.dispatchEvent(event);
  }

  // Public method to reapply styles (useful when new elements are added)
  reapplyStyles() {
    this.applyDirectStyles();
  }
}

// Initialize theme toggle when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  // Initialize on apps or training views
  if (
    document.querySelector('.home-screen-container') ||
    document.querySelector('.training-screen-container')
  ) {
    try {
      window.themeToggleInstance = new ThemeToggle();
    } catch (error) {
      console.error('Error initializing theme toggle:', error);
    }
  }
});

// Make ThemeToggle available globally for manual initialization if needed
window.ThemeToggle = ThemeToggle;
