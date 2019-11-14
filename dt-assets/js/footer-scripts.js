console.log('connect to footer-scripts.js');

/*
These functions make sure WordPress
and Foundation play nice together.
*/
jQuery(document).foundation();


function contactFilterAccordion() {
  /**
   * Managing the Contact Filters Accordion
   * Helpful Resource Guides
   * https://foundation.zurb.com/sites/docs/javascript.html
   * https://foundation.zurb.com/sites/docs/javascript.html#programmatic-use
   * https://foundation.zurb.com/sites/docs/accordion-menu.html
   * https://foundation.zurb.com/sites/docs/v/5.5.3/javascript.html
   * https://www.sitepoint.com/foundation-6-menu-component/
   */

  // (optional) Set speed and expansion options for the Contact Filter accordion
  var $accordion = new Foundation.Accordion($('#list-filter-tabs'), {
    slideSpeed: 100,
    multiExpand: true,
    allowAllClosed: true
  });

  //(optional) set Contact Filter accordion to be closed by default
  jQuery('#list-filter-tabs').find('.accordion-item.is-active').removeClass('is-active').find('.accordion-content').css({ 'display': "" });

  // (optional) set a callback when a panel open
  $('#list-filter-tabs').on('down.zf.accordion menu', function () { });

  // (optional) set a callback when a panel is down
  $('#list-filter-tabs').on('up.zf.accordion menu', function () { });

}


jQuery(document).ready(function () {

  //initialize Contact Filters accordion function
  contactFilterAccordion();

  // Remove empty P tags created by WP inside of Accordion and Orbit
  jQuery('.accordion p:empty, .orbit p:empty').remove();

  // Makes sure last grid item floats left
  jQuery('.archive-grid .columns').last().addClass('end');

  // Adds Flex Video to YouTube and Vimeo Embeds
  jQuery('iframe[src*="youtube.com"], iframe[src*="vimeo.com"]').each(function () {
    if (jQuery(this).innerWidth() / jQuery(this).innerHeight() > 1.5) {
      jQuery(this).wrap("<div class='widescreen flex-video'/>");
    } else {
      jQuery(this).wrap("<div class='flex-video'/>");
    }
  });

});

/**
* Custom javascript for Disciple Tools
*
* */
jQuery(document).ready($ => {
  // This adds padding to the top of the offcanvas menu, if the wp admin bar is turned on for the profile.
  $('#wpadminbar').addClass('add')
});
