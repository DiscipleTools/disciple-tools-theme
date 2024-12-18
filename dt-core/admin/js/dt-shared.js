/*
This javascript file is enqueued on all admin pages.
shared scripts applicable to all sections.
 */
'use strict';

function makeRequest(type, url, data, base = 'dt/v1/') {
  // Add trailing slash if missing
  if (!base.endsWith('/') && !url.startsWith('/')) {
    base += '/';
  }
  const options = {
    type: type,
    contentType: 'application/json; charset=utf-8',
    dataType: 'json',
    url: url.startsWith('http')
      ? url
      : `${window.wpApiSettings.root}${base}${url}`,
    beforeSend: (xhr) => {
      xhr.setRequestHeader('X-WP-Nonce', window.wpApiSettings.nonce);
    },
  };
  if (data) {
    options.data = type === 'GET' ? data : JSON.stringify(data);
  }
  return jQuery.ajax(options);
}

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

  /**
   * Update options. Provide an object with the options to update.
   * @param options
   */
  update_dt_options: (options) =>
    makeRequest('POST', 'update-dt-options', options, 'dt-admin-settings/'),

  plugin_install: (download_url) =>
    makeRequest(
      'POST',
      `plugin-install`,
      {
        download_url: download_url,
      },
      `dt-admin-settings/`,
    ),

  plugin_delete: (plugin_slug) =>
    makeRequest(
      'POST',
      `plugin-delete`,
      {
        plugin_slug: plugin_slug,
      },
      `dt-admin-settings/`,
    ),

  plugin_activate: (plugin_slug) =>
    makeRequest(
      'POST',
      `plugin-activate`,
      {
        plugin_slug: plugin_slug,
      },
      `dt-admin-settings/`,
    ),

  plugin_deactivate: (plugin_slug) =>
    makeRequest(
      'POST',
      `plugin-deactivate`,
      {
        plugin_slug: plugin_slug,
      },
      `dt-admin-settings/`,
    ),
};

jQuery(function ($) {
  function handle_docs_request(title_div, content_div) {
    $('#dt_right_docs_section').fadeOut('fast', function () {
      $('#dt_right_docs_title').html($('#' + title_div).html());
      $('#dt_right_docs_content').html($('#' + content_div).html());

      $('#dt_right_docs_section').fadeIn('fast');
    });
  }

  //attach to any <a> tags with class dt-docs
  $(document).on('click', '.dt-docs', function (evt) {
    if (evt) {
      evt.preventDefault();
    }
    handle_docs_request(
      $(evt.currentTarget).data('title'),
      $(evt.currentTarget).data('content'),
    );
  });
});
