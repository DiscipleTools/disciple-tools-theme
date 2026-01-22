/**
 * Main entry point for Vite
 */

// Import styles
import '../scss/style.scss';

// Import dependencies (matching Gulp's SOURCE.scripts sequence)
import 'what-input';

// Foundation core
import 'foundation-sites/dist/js/plugins/foundation.core.js';
import 'foundation-sites/dist/js/plugins/foundation.util.keyboard.js';
import 'foundation-sites/dist/js/plugins/foundation.util.nest.js';
import 'foundation-sites/dist/js/plugins/foundation.util.mediaQuery.js';
import 'foundation-sites/dist/js/plugins/foundation.util.triggers.js';
import 'foundation-sites/dist/js/plugins/foundation.util.box.js';
import 'foundation-sites/dist/js/plugins/foundation.util.touch.js';

// Foundation plugins
import 'foundation-sites/dist/js/plugins/foundation.accordion.js';
import 'foundation-sites/dist/js/plugins/foundation.accordionMenu.js';
import 'foundation-sites/dist/js/plugins/foundation.drilldown.js';
import 'foundation-sites/dist/js/plugins/foundation.dropdown.js';
import 'foundation-sites/dist/js/plugins/foundation.dropdownMenu.js';
import 'foundation-sites/dist/js/plugins/foundation.equalizer.js';
import 'foundation-sites/dist/js/plugins/foundation.magellan.js';
import 'foundation-sites/dist/js/plugins/foundation.offcanvas.js';
import 'foundation-sites/dist/js/plugins/foundation.tabs.js';
import 'foundation-sites/dist/js/plugins/foundation.responsiveAccordionTabs.js';
import 'foundation-sites/dist/js/plugins/foundation.responsiveMenu.js';
import 'foundation-sites/dist/js/plugins/foundation.reveal.js';
import 'foundation-sites/dist/js/plugins/foundation.smoothScroll.js';
import 'foundation-sites/dist/js/plugins/foundation.sticky.js';
import 'foundation-sites/dist/js/plugins/foundation.toggler.js';

// Masonry
import 'masonry-layout';

// Custom theme logic
import './footer-scripts.js';
