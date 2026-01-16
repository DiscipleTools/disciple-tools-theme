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

    // Also set on document root for broader compatibility
    document.documentElement.style.setProperty(
      '--theme-mode',
      this.currentTheme,
    );

    // Save preference
    localStorage.setItem('dt-home-theme', this.currentTheme);

    // Update toggle button icon
    this.updateToggleButton();

    // Dispatch custom event for other components
    this.dispatchThemeChangeEvent();
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
