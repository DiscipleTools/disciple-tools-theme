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

    // Add to the header controls
    const headerControls = document.querySelector('.header-controls');
    if (headerControls) {
      headerControls.appendChild(toggleButton);
    }
  }

  bindEvents() {
    const toggleButton = document.querySelector('.theme-toggle-button');
    if (toggleButton) {
      toggleButton.addEventListener('click', () => this.toggleTheme());
    }
  }

  applyTheme() {
    // Update the document body class for theme detection
    document.body.classList.remove('theme-light', 'theme-dark');
    document.body.classList.add(`theme-${this.currentTheme}`);

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

    console.log('Theme applied:', this.currentTheme);

    // Debug: Log current CSS variable values
    const computedStyle = getComputedStyle(document.documentElement);
    console.log('CSS Variables:', {
      '--surface-1': computedStyle.getPropertyValue('--surface-1'),
      '--text-color': computedStyle.getPropertyValue('--text-color'),
      '--border-color': computedStyle.getPropertyValue('--border-color'),
      '--primary-color': computedStyle.getPropertyValue('--primary-color'),
    });

    // Debug: Check if body class is applied
    console.log('Body classes:', document.body.className);

    // Debug: Check computed styles of the container
    const container = document.querySelector('.home-screen-container');
    if (container) {
      const containerStyle = getComputedStyle(container);
      console.log('Container computed styles:', {
        backgroundColor: containerStyle.backgroundColor,
        color: containerStyle.color,
      });
    }
  }

  applyDirectStyles() {
    // Apply styles directly to elements to ensure they take effect
    const container = document.querySelector('.home-screen-container');
    const content = document.querySelector('.home-screen-content');
    const appCards = document.querySelectorAll('.app-card');
    const appTitles = document.querySelectorAll('.app-title');
    const sectionTitles = document.querySelectorAll('.section-title');
    const sectionToggles = document.querySelectorAll('.section-toggle');

    console.log('Found elements:', {
      container: !!container,
      content: !!content,
      appCards: appCards.length,
      appTitles: appTitles.length,
      sectionTitles: sectionTitles.length,
      sectionToggles: sectionToggles.length,
    });

    if (this.currentTheme === 'dark') {
      // Dark mode styles
      if (container) {
        container.style.backgroundColor = '#1a1a1a';
        container.style.color = '#f5f5f5';
      }
      if (content) {
        content.style.backgroundColor = '#1a1a1a';
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

        // Debug: Log the first card's computed styles
        if (index === 0) {
          const computedStyle = getComputedStyle(card);
          console.log('First app card computed styles:', {
            backgroundColor: computedStyle.backgroundColor,
            borderColor: computedStyle.borderColor,
            color: computedStyle.color,
            boxShadow: computedStyle.boxShadow,
          });
        }
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
        content.style.backgroundColor = 'hsla(0, 0%, 90%, 1)';
        content.style.color = '#0a0a0a';
      }
      appCards.forEach((card) => {
        card.style.setProperty(
          'background-color',
          'hsla(0, 0%, 90%, 1)',
          'important',
        );
        card.style.setProperty('border-color', '#e1e5e9', 'important');
        card.style.setProperty('color', '#0a0a0a', 'important');
        card.style.setProperty(
          'box-shadow',
          '0 1px 3px rgba(0,0,0,0.1)',
          'important',
        );
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

    console.log(
      'Toggling theme from',
      this.currentTheme,
      'to',
      this.currentTheme === 'light' ? 'dark' : 'light',
    );

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
    console.log('Reapplying theme styles to all elements');
    this.applyDirectStyles();
  }
}

// Initialize theme toggle when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  // Only initialize if we're on the home screen
  if (document.querySelector('.home-screen-container')) {
    try {
      console.log('Initializing theme toggle...');
      window.themeToggleInstance = new ThemeToggle();
      console.log('Theme toggle initialized successfully');
    } catch (error) {
      console.error('Error initializing theme toggle:', error);
    }
  }
});

// Make ThemeToggle available globally for manual initialization if needed
window.ThemeToggle = ThemeToggle;
