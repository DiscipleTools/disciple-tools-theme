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
* Custom javascript for Disciple Tools
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
