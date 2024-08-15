/*
This javascript file is enqueued on all admin pages.
shared scripts applicable to all sections.
 */
'use strict';

window.dt_admin_shared = {
  escape(str) {
    if (typeof str !== 'string') return str;
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&apos;');
  },
};

jQuery(function ($) {

function handle_docs_request(title_div, content_div) {
  $('#dt_right_docs_section').fadeOut('fast', function () {
    $('#dt_right_docs_title').html($('#' + title_div).html());
    $('#dt_right_docs_content').html($('#' + content_div).html());

    $('#dt_right_docs_section').fadeIn('fast');
  });
}

//attach to any <a> tags with class dt-docs, how does this attach to <a> tags?
$(document).on('click', '.dt-docs', function (evt) {
  handle_docs_request($(evt.currentTarget).data('title'), $(evt.currentTarget).data('content'));
});

});

//create placeholder help section
//us id attributes starting with dt_ instead of ml_links_