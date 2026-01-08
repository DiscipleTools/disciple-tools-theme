class Launcher {
  constructor() {
    this.currentTheme = 'light';
    this.isTransitioning = false;
    this.systemPreferenceMediaQuery = null;

    this.init();
  }

  init() {
    this.initializeTheme();
    this.setupSystemPreferenceListener();
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
  }

  getLauncherSelector() {
    return document.querySelector('.dt-launcher-apps-selector');
  }

  toggleAppsSelector() {
    const selector = this.getLauncherSelector();
    if (selector) {
      selector.classList.toggle('open');
    }
  }

  closeAppsSelector() {
    const selector = this.getLauncherSelector();
    if (selector) {
      selector.classList.remove('open');
    }
  }

  bindEvents() {
    // Close apps selector when clicking in iframe
    const iframe = document.getElementById('launcher-app-iframe');
    if (iframe) {
      const launcher = this;
      iframe.addEventListener('load', () => {
        const iframeDocument =
          iframe.contentDocument || iframe.contentWindow.document;
        iframeDocument.addEventListener('click', (event) => {
          this.closeAppsSelector();
        });
      });
    }

    // Close apps selector when clicking outside
    document.addEventListener('click', (event) => {
      const selector = this.getLauncherSelector();
      const appsButton = document.querySelector(
        '.dt-launcher-bottom-nav #dt-apps-selector-button',
      );

      if (selector && appsButton) {
        const isClickInsideSelector = selector.contains(event.target);
        const isClickOnAppsButton = appsButton.contains(event.target);

        if (
          !isClickInsideSelector &&
          !isClickOnAppsButton &&
          selector.classList.contains('open')
        ) {
          this.closeAppsSelector();
        } else if (isClickOnAppsButton) {
          this.toggleAppsSelector();
        }
      }

      // Navigate iframe when clicking on app link
      const link = event.target.closest('.launcher-app-link');
      if (!link) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();
      this.closeAppsSelector();

      const iframeUrl =
        link.getAttribute('data-app-url') || link.getAttribute('href');

      if (iframe && iframeUrl && iframeUrl !== '#') {
        iframe.setAttribute('src', iframeUrl);
        return false;
      }

      var fallbackUrl =
        link.getAttribute('data-launcher-url') || link.getAttribute('href');
      if (fallbackUrl && fallbackUrl !== '#') {
        window.location.href = fallbackUrl;
      }

      return false;
    });
  }
}

// Initialize theme toggle when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  try {
    window.launcherInstance = new Launcher();
  } catch (error) {
    console.error('Error initializing theme:', error);
  }
});

// Make ThemeToggle available globally for manual initialization if needed
window.Launcher = Launcher;
