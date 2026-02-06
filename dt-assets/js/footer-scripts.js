/*
These functions make sure WordPress
and Foundation play nice together.
*/

// Initialize Foundation-dependent code using DTFoundation utility
window.DTFoundation.ready((foundation) => {
  if (foundation.MediaQuery && foundation.MediaQuery.current == 'small') {
    jQuery('.title-bar')
      .removeAttr('data-sticky')
      .removeClass('is-anchored is-at-bottom')
      .attr('style', '');
  }

  if (foundation.Reveal && foundation.Reveal.defaults) {
    foundation.Reveal.defaults.closeOnClick = false;
  }

  // Initialize Foundation jQuery plugin
  window.DTFoundation.plugin(() => {
    jQuery(document).foundation();
  });
});

jQuery(document).ready(function () {
  // Remove empty P tags created by WP inside of Accordion and Orbit
  jQuery('.accordion p:empty, .orbit p:empty').remove();

  // Makes sure last grid item floats left
  jQuery('.archive-grid .columns').last().addClass('end');

  // Adds Flex Video to YouTube and Vimeo Embeds
  jQuery('iframe[src*="youtube.com"], iframe[src*="vimeo.com"]').each(
    function () {
      if (jQuery(this).innerWidth() / jQuery(this).innerHeight() > 1.5) {
        jQuery(this).wrap("<div class='widescreen flex-video'/>");
      } else {
        jQuery(this).wrap("<div class='flex-video'/>");
      }
    },
  );
});

/**
 * Custom javascript for Disciple.Tools
 *
 * */
jQuery(document).ready(($) => {
  // This adds padding to the top of the offcanvas menu, if the wp admin bar is turned on for the profile.
  $('#wpadminbar').addClass('add');
});

//Hide Top Menu More button if all items are showing
document.addEventListener('DOMContentLoaded', top_bar_menu_more_button);
window.addEventListener('resize', top_bar_menu_more_button);

function top_bar_menu_more_button() {
  if (
    jQuery('#top-bar-menu > div.top-bar-left > ul > li:nth-last-child(2)').is(
      ':visible',
    )
  ) {
    jQuery('#more-menu-button').hide();
  } else {
    jQuery('#more-menu-button').show();
  }
}

/**
 * Ensure correct side menu highlights are maintained
 */
jQuery(document).ready(function ($) {
  // Determine selected menu item
  let selected_menu_item =
    window.wpApiShare.url_path === 'metrics'
      ? 'metrics/personal/overview'
      : window.wpApiShare.url_path;

  // Ignore url search parameters
  selected_menu_item = window.lodash.split(selected_menu_item, '?', 1)[0];

  let item = $(
    '#metrics-side-section a[href*="' + selected_menu_item + '"]',
  ).last();

  // Apply class highlight for initial
  item.parent().addClass('side-menu-item-highlight');

  // Also apply additional highlights, if required parent structure detected
  let required_parent = item.parent().parent().parent();
  if (required_parent.parent() && required_parent.parent().is('ul')) {
    required_parent.find('a').first().addClass('side-menu-item-highlight');
  }
});

/**
 * Mobile navigation dropdown functionality
 */
jQuery(document).ready(function ($) {
  // Close mobile add new dropdown when clicking outside
  $(document).on('click', function (e) {
    if (
      !$(e.target).closest(
        '#mobile-add-new-dropdown, [data-toggle="mobile-add-new-dropdown"]',
      ).length
    ) {
      window.DTFoundation.plugin(() => {
        window.DTFoundation.callMethod('#mobile-add-new-dropdown', 'close');
      });
    }
  });
});
