import { Foundation } from 'foundation-sites/js/foundation.core';
import { Accordion } from 'foundation-sites/js/foundation.accordion';
import { AccordionMenu } from 'foundation-sites/js/foundation.accordionMenu';
import { Drilldown } from 'foundation-sites/js/foundation.drilldown';
import { Dropdown } from 'foundation-sites/js/foundation.dropdown';
import { DropdownMenu } from 'foundation-sites/js/foundation.dropdownMenu';
import { Equalizer } from 'foundation-sites/js/foundation.equalizer';
import { Magellan } from 'foundation-sites/js/foundation.magellan';
import { OffCanvas } from 'foundation-sites/js/foundation.offcanvas';
import { Tabs } from 'foundation-sites/js/foundation.tabs';
import { ResponsiveAccordionTabs } from 'foundation-sites/js/foundation.responsiveAccordionTabs';
import { ResponsiveMenu } from 'foundation-sites/js/foundation.responsiveMenu';
import { Reveal } from 'foundation-sites/js/foundation.reveal';
import { SmoothScroll } from 'foundation-sites/js/foundation.smoothScroll';
import { Sticky } from 'foundation-sites/js/foundation.sticky';
import { Toggler } from 'foundation-sites/js/foundation.toggler';

import { Box } from 'foundation-sites/js/foundation.util.box';
import { Keyboard } from 'foundation-sites/js/foundation.util.keyboard';
import { MediaQuery } from 'foundation-sites/js/foundation.util.mediaQuery';
import { Nest } from 'foundation-sites/js/foundation.util.nest';
import { Triggers } from 'foundation-sites/js/foundation.util.triggers';
import { Touch } from 'foundation-sites/js/foundation.util.touch';

// Register plugins
Foundation.plugin(Accordion, 'Accordion');
Foundation.plugin(AccordionMenu, 'AccordionMenu');
Foundation.plugin(Drilldown, 'Drilldown');
Foundation.plugin(Dropdown, 'Dropdown');
Foundation.plugin(DropdownMenu, 'DropdownMenu');
Foundation.plugin(Equalizer, 'Equalizer');
Foundation.plugin(Magellan, 'Magellan');
Foundation.plugin(OffCanvas, 'OffCanvas');
Foundation.plugin(Tabs, 'Tabs');
Foundation.plugin(ResponsiveAccordionTabs, 'ResponsiveAccordionTabs');
Foundation.plugin(ResponsiveMenu, 'ResponsiveMenu');
Foundation.plugin(Reveal, 'Reveal');
Foundation.plugin(SmoothScroll, 'SmoothScroll');
Foundation.plugin(Sticky, 'Sticky');
Foundation.plugin(Toggler, 'Toggler');

// Manual utility registration if needed (usually handled by plugins)
Foundation.Box = Box;
Foundation.Keyboard = Keyboard;
Foundation.MediaQuery = MediaQuery;
Foundation.Nest = Nest;
Foundation.Triggers = Triggers;
Foundation.Touch = Touch;

// Set window.Foundation immediately and synchronously
// This ensures it's available before any other scripts try to use it
// Note: DTFoundation utility (foundation-utils.js) provides helper functions
// for other scripts to wait for Foundation to be ready
window.Foundation = Foundation;

// Register jQuery plugin - ensure jQuery is available first
// CRITICAL: Foundation.addToJquery() uses $ from its closure (imported in foundation.core.js)
// When the legacy build wraps code in System.register, that closure might be broken
// So we manually register the jQuery plugin using window.jQuery instead
function initializeFoundationJQuery() {
  // Check if jQuery is available
  const jQuery = window.jQuery || window.$;
  if (jQuery && typeof jQuery === 'function') {
    try {
      // Try Foundation's addToJquery first (in case it works)
      if (typeof Foundation.addToJquery === 'function') {
        Foundation.addToJquery();
      }

      // Manually register jQuery plugin if Foundation.addToJquery didn't work
      // This replicates what addToJquery() does, but uses window.jQuery
      if (!jQuery.fn.foundation || typeof jQuery.fn.foundation !== 'function') {
        const foundation = function (method) {
          const type = typeof method;
          const $noJS = jQuery('.no-js');

          if ($noJS.length) {
            $noJS.removeClass('no-js');
          }

          if (type === 'undefined') {
            // Initialize Foundation
            if (Foundation.MediaQuery && Foundation.MediaQuery._init) {
              Foundation.MediaQuery._init();
            }
            if (Foundation.reflow) {
              Foundation.reflow(this);
            }
          } else if (type === 'string') {
            // Invoke method on plugin
            const args = Array.prototype.slice.call(arguments, 1);
            const plugClass = this.data('zfPlugin');

            if (
              typeof plugClass !== 'undefined' &&
              typeof plugClass[method] !== 'undefined'
            ) {
              if (this.length === 1) {
                plugClass[method].apply(plugClass, args);
              } else {
                this.each(function (i, el) {
                  plugClass[method].apply(jQuery(el).data('zfPlugin'), args);
                });
              }
            } else {
              throw new ReferenceError(
                "We're sorry, '" +
                  method +
                  "' is not an available method for " +
                  (plugClass ? plugClass.constructor.name : 'this element') +
                  '.',
              );
            }
          } else {
            throw new TypeError(
              `We're sorry, ${type} is not a valid parameter. You must use a string representing the method you wish to invoke.`,
            );
          }
          return this;
        };

        jQuery.fn.foundation = foundation;
      }

      // Verify it was registered
      if (jQuery.fn.foundation && typeof jQuery.fn.foundation === 'function') {
        // Dispatch custom event to signal Foundation is ready
        const foundationReadyEvent = new CustomEvent('foundation:ready', {
          detail: { Foundation },
        });
        window.dispatchEvent(foundationReadyEvent);
      } else {
        throw new Error('Foundation jQuery plugin was not registered');
      }
    } catch (error) {
      console.warn('Error initializing Foundation jQuery plugin:', error);
      // Retry after a short delay
      setTimeout(initializeFoundationJQuery, 100);
    }
  } else {
    // Wait for jQuery to be available - check multiple times
    let attempts = 0;
    const checkJQuery = () => {
      attempts++;
      const jQuery = window.jQuery || window.$;
      if (jQuery && typeof jQuery === 'function') {
        initializeFoundationJQuery();
      } else if (attempts < 20) {
        setTimeout(checkJQuery, 50);
      } else {
        console.warn('jQuery not available for Foundation initialization');
      }
    };
    checkJQuery();
  }
}

// Try to initialize immediately
if (typeof window !== 'undefined') {
  // Use requestAnimationFrame to ensure DOM is ready
  if (window.requestAnimationFrame) {
    window.requestAnimationFrame(() => {
      initializeFoundationJQuery();
    });
  } else {
    setTimeout(initializeFoundationJQuery, 0);
  }

  // Also try when DOM is ready as a fallback
  if (window.document) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initializeFoundationJQuery);
    } else {
      // DOM already loaded, try initialization with a small delay
      setTimeout(initializeFoundationJQuery, 10);
    }
  }
}
