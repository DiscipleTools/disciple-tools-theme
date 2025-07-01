'use strict';

jQuery(document).ready(function () {
  jQuery('#metrics-sidemenu').foundation(
    'down',
    jQuery(`#${window.wp_js_object.base_slug}-menu`),
  );

  let chartDiv = jQuery('#chart');
  chartDiv.empty().html(`
    <span class="section-header" title="Showing all contacts from all time with any status">${window.wp_js_object.translations.title}</span>
    <div id="mapping_chart"></div>
  `);

  window.page_mapping_view(window.wp_js_object.rest_endpoints_base);
});
