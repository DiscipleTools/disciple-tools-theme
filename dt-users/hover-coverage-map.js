"use strict";

jQuery(document).ready(function() {
  let chartDiv = jQuery('#chart')
  chartDiv.empty().html(`
    <span class="section-header" title="Cumulative simple map of user coverage">${window.lodash.escape( wp_js_object.translations.title )}</span>
    <div id="mapping_chart"></div>
  `)

  page_mapping_view(window.wp_js_object.rest_endpoints_base)
})
