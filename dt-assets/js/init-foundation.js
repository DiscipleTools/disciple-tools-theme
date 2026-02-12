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

window.Foundation = Foundation;

// Register jQuery plugin
Foundation.addToJquery();
