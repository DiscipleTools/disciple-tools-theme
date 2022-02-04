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

//Hide Top Menu More button if all items are showing
document.addEventListener('DOMContentLoaded', top_bar_menu_more_button);
window.addEventListener('resize', top_bar_menu_more_button);

function top_bar_menu_more_button () {
  if ( $("#top-bar-menu > div.top-bar-left > ul > li:nth-last-child(2)").is(':visible')) {
    $("#more-menu-button").hide();
  } else {
    $("#more-menu-button").show();
  }
}

/**
 * Ensure correct side menu highlights are maintained
 */
jQuery(document).ready(function () {

  let url_path = window.wpApiShare.url_path;
  if (url_path.includes('metrics')) {
    highlight_current_menu_item_metrics();

  } else if (url_path.includes('user-management/')) {
    highlight_current_menu_item_users();
  }

  function highlight_current_menu_item_metrics() {
    // Determine actual selected metric menu item
    let selected_metric_name = (window.wpApiShare.url_path === 'metrics') ? 'metrics/personal/overview' : window.wpApiShare.url_path;
    let metric = $('#metrics-side-section a[href$="' + selected_metric_name + '"]').last();

    // Apply class highlight
    metric.parent().addClass('side-menu-item-highlight');
    metric.parent().parent().parent().find('a').first().addClass('side-menu-item-highlight');
  }

  function highlight_current_menu_item_users() {
    // Determine actual selected user menu item
    let item = $('#metrics-side-section a[href*="' + window.wpApiShare.url_path + '"]').last();

    // Apply class highlight
    item.parent().addClass('side-menu-item-highlight');
  }

});
