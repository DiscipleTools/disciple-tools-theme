/*
These functions make sure WordPress
and Foundation play nice together.
*/
if (Foundation.MediaQuery.current == 'small') {
  $('.title-bar').removeAttr('data-sticky').removeClass('is-anchored is-at-bottom').attr('style', '');
}

jQuery(document).foundation();


jQuery(document).ready(function () {

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
* Custom javascript for Disciple.Tools
*
* */
jQuery(document).ready($ => {
  // This adds padding to the top of the offcanvas menu, if the wp admin bar is turned on for the profile.
  $('#wpadminbar').addClass('add')
});

/* Makes sure the inner-content area is no less than the full height of the screen.
* This prevents dropdowns or other elements from being cut off on short pages */
jQuery(document).ready(function () {
  jQuery('#inner-content ').css('min-height', window.innerHeight)
})

jQuery(document).ready($ => {

  // Hide Top Menu More button if all items are showing
  top_bar_menu_more_button();
  document.addEventListener('DOMContentLoaded', top_bar_menu_more_button);
  window.addEventListener('resize', top_bar_menu_more_button);

  function top_bar_menu_more_button() {

    // Identify both main and more menu items
    let main_menu_items = $("#top-bar-menu > div.top-bar-left > ul.dropdown > li");
    let more_menu_items = $("#more-menu-button > ul.is-dropdown-submenu > li");

    // To avoid duplicates, remove main items from more sub-menu
    if (main_menu_items && more_menu_items) {

      // By default, enable all more menu items
      $(more_menu_items).find('a').show();

      // Now, hide accordingly based on what is currently shown within main menu
      $(main_menu_items).each(function (main_idx, main_item) {

        // Extract href value to be used for search from visible items
        let main_item_url = $(main_item).is(':visible') ? $(main_item).find('a').attr('href') : null;
        if (main_item_url && window.lodash.startsWith(main_item_url, 'http')) {

          // Search and remove from more menu
          let matched_more_item = $(more_menu_items).find('a[href="' + main_item_url + '"]');
          if (matched_more_item) {
            $(matched_more_item).hide();
          }
        }
      });

      // Toggle more menu visibility depending on children count
      let more_has_visible_items = $(more_menu_items).find('a[style!="display: none;"]');
      if ($(more_has_visible_items).length > 0) {
        $('#more-menu-button').show();
      } else {
        $('#more-menu-button').hide();
      }
    }
  }
});

/**
 * Ensure correct side menu highlights are maintained
 */
jQuery(document).ready(function () {

  // Determine selected menu item
  let selected_menu_item = (window.wpApiShare.url_path === 'metrics') ? 'metrics/personal/overview' : window.wpApiShare.url_path;
  let item = $('#metrics-side-section a[href*="' + selected_menu_item + '"]').last();

  // Apply class highlight for initial
  item.parent().addClass('side-menu-item-highlight');

  // Also apply additional highlights, if required parent structure detected
  let required_parent = item.parent().parent().parent();
  if (required_parent.parent() && required_parent.parent().is('ul')) {
    required_parent.find('a').first().addClass('side-menu-item-highlight');
  }

});
