/**
 * Foundation Utilities
 *
 * Provides reusable utilities for checking Foundation availability and waiting
 * for Foundation to be ready. This file loads synchronously (not bundled) to
 * ensure it's available before other scripts that depend on Foundation.
 *
 * Usage:
 *   // Wait for Foundation object
 *   window.DTFoundation.ready((Foundation) => {
 *     new Foundation.Accordion(element);
 *   });
 *
 *   // Wait for jQuery plugin
 *   window.DTFoundation.plugin(() => {
 *     jQuery('#element').foundation('open');
 *   });
 */
(function (window) {
  'use strict';

  const DTFoundation = {
    /**
     * Check if Foundation object is available
     * @returns {boolean}
     */
    isAvailable: function () {
      return !!(window.Foundation && window.Foundation.Accordion);
    },

    /**
     * Check if Foundation jQuery plugin is available
     * @returns {boolean}
     */
    isPluginAvailable: function () {
      const $ = window.jQuery || window.$;
      return !!(
        $ &&
        $.fn &&
        $.fn.foundation &&
        typeof $.fn.foundation === 'function'
      );
    },

    /**
     * Wait for Foundation object to be available
     * @param {Function} callback - Function to call when Foundation is ready
     * @param {number} maxAttempts - Maximum number of polling attempts (default: 50)
     * @param {number} attempt - Current attempt number (internal use)
     */
    ready: function (callback, maxAttempts = 50, attempt = 0) {
      if (this.isAvailable()) {
        callback(window.Foundation);
        return;
      }

      // Set up event listener on first attempt
      if (attempt === 0) {
        const handleFoundationReady = () => {
          if (this.isAvailable()) {
            window.removeEventListener(
              'foundation:ready',
              handleFoundationReady,
            );
            callback(window.Foundation);
          }
        };
        window.addEventListener('foundation:ready', handleFoundationReady);
      }

      if (attempt >= maxAttempts) {
        console.warn(
          'DTFoundation: Foundation object not available after waiting',
        );
        return;
      }

      setTimeout(() => {
        this.ready(callback, maxAttempts, attempt + 1);
      }, 50);
    },

    /**
     * Ensure a specific element has a Foundation plugin instance initialized
     * @param {jQuery} $element - jQuery element to check/initialize
     * @returns {boolean} - True if element has plugin instance
     */
    ensureElementInitialized: function ($element) {
      if (!$element || !$element.length) {
        return false;
      }

      // Check if element already has a plugin instance
      const plugClass = $element.data('zfPlugin');
      if (plugClass !== undefined && plugClass !== null) {
        return true;
      }

      // Try to initialize the element if it has Foundation data attributes
      const $ = window.jQuery || window.$;
      if ($ && $.fn && $.fn.foundation) {
        try {
          // For Reveal modals, try initializing with Foundation.Reveal if available
          if (
            $element.attr('data-reveal') !== undefined &&
            window.Foundation &&
            window.Foundation.Reveal
          ) {
            try {
              // Check if it's already initialized
              if (!$element.data('zfPlugin')) {
                new window.Foundation.Reveal($element);
              }
              // Check again if it was initialized
              if ($element.data('zfPlugin') !== undefined) {
                return true;
              }
            } catch (e) {
              // Fall through to try foundation() method
            }
          }

          // Call foundation() without arguments to initialize the element
          // This works for elements with Foundation data attributes
          $element.foundation();
          // Check again if it was initialized
          return $element.data('zfPlugin') !== undefined;
        } catch (e) {
          // Element might not have Foundation attributes or already initialized
          // Return false to allow retry
          return false;
        }
      }

      return false;
    },

    /**
     * Safely call a Foundation method on an element, ensuring it's initialized first
     * @param {jQuery|string} element - jQuery element or selector
     * @param {string} method - Foundation method name
     * @param {...*} args - Arguments to pass to the method
     */
    callMethod: function (element, method) {
      const $ = window.jQuery || window.$;
      if (!$ || !$.fn || !$.fn.foundation) {
        console.warn('DTFoundation: jQuery or Foundation plugin not available');
        return;
      }

      const $element = typeof element === 'string' ? $(element) : element;
      if (!$element || !$element.length) {
        console.warn('DTFoundation: Element not found:', element);
        return;
      }

      // Capture all arguments (skip element and method)
      const args = Array.prototype.slice.call(arguments, 2);

      // Ensure document is initialized first
      try {
        $(document).foundation();
      } catch (e) {
        // Ignore errors
      }

      // Ensure this specific element is initialized
      if (!this.ensureElementInitialized($element)) {
        // If still not initialized, wait a bit and try again
        setTimeout(() => {
          // Try to initialize again
          try {
            $(document).foundation();
          } catch (e) {
            // Ignore errors
          }

          // Try to initialize the element again
          if (!this.ensureElementInitialized($element)) {
            // Last resort: for Reveal modals, try direct initialization
            if (
              $element.attr('data-reveal') !== undefined &&
              window.Foundation &&
              window.Foundation.Reveal
            ) {
              try {
                new window.Foundation.Reveal($element);
              } catch (e) {
                // Ignore initialization errors
              }
            }

            // Check one more time if it's initialized now
            if (!this.ensureElementInitialized($element)) {
              // Even if not initialized, try calling the method anyway
              // Foundation might handle it gracefully or the element might work without explicit initialization
              try {
                $element.foundation(method, ...args);
                return;
              } catch (e) {
                console.warn(
                  'DTFoundation: Could not initialize element or call method:',
                  element,
                  e,
                );
                return;
              }
            }
          }
          // Call the method with captured arguments
          $element.foundation(method, ...args);
        }, 100);
        return;
      }

      // Element is initialized, call the method
      $element.foundation(method, ...args);
    },

    /**
     * Wait for Foundation jQuery plugin to be available and ensure document is initialized
     * @param {Function} callback - Function to call when plugin is ready
     * @param {number} maxAttempts - Maximum number of polling attempts (default: 50)
     * @param {number} attempt - Current attempt number (internal use)
     */
    plugin: function (callback, maxAttempts = 50, attempt = 0) {
      const $ = window.jQuery || window.$;

      if (this.isPluginAvailable()) {
        // Ensure document has been initialized (jQuery(document).foundation() called)
        // This initializes all Foundation components on the page
        if ($ && $.fn && $.fn.foundation) {
          try {
            // Check if document foundation has been called by trying to initialize
            // If it's already initialized, this is a no-op
            $(document).foundation();
            // Add a small delay to allow initialization to complete
            setTimeout(() => {
              callback();
            }, 50);
            return;
          } catch (e) {
            // Ignore errors - might already be initialized or element might not exist yet
            callback();
            return;
          }
        }
        callback();
        return;
      }

      // Set up event listener on first attempt
      if (attempt === 0) {
        const handleFoundationReady = () => {
          // Small delay to ensure jQuery plugin is registered after event
          setTimeout(() => {
            if (this.isPluginAvailable()) {
              // Ensure document is initialized so all Foundation components are ready
              if ($ && $.fn && $.fn.foundation) {
                try {
                  $(document).foundation();
                  // Add delay to allow initialization to complete
                  setTimeout(() => {
                    window.removeEventListener(
                      'foundation:ready',
                      handleFoundationReady,
                    );
                    callback();
                  }, 50);
                  return;
                } catch (e) {
                  // Ignore errors
                }
              }
              window.removeEventListener(
                'foundation:ready',
                handleFoundationReady,
              );
              callback();
            }
          }, 10);
        };
        window.addEventListener('foundation:ready', handleFoundationReady);
      }

      if (attempt >= maxAttempts) {
        console.warn(
          'DTFoundation: Foundation jQuery plugin not available after waiting',
        );
        // Last attempt - try manual initialization if Foundation exists
        if (
          window.Foundation &&
          typeof window.Foundation.addToJquery === 'function'
        ) {
          try {
            window.Foundation.addToJquery();
            if (this.isPluginAvailable()) {
              // Ensure document is initialized
              if ($ && $.fn && $.fn.foundation) {
                try {
                  $(document).foundation();
                  setTimeout(() => {
                    callback();
                  }, 50);
                  return;
                } catch (e) {
                  // Ignore errors
                }
              }
              callback();
              return;
            }
          } catch (e) {
            console.warn(
              'DTFoundation: Failed to manually initialize Foundation jQuery plugin:',
              e,
            );
          }
        }
        return;
      }

      setTimeout(() => {
        this.plugin(callback, maxAttempts, attempt + 1);
      }, 50);
    },
  };

  // Expose globally
  window.DTFoundation = DTFoundation;
})(window);
