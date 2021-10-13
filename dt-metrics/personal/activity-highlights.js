jQuery(document).ready(function() {
  if ( window.wpApiShare.url_path.startsWith( 'metrics/personal/activity-highlights' ) ) {
    my_stats()
  }

  function my_stats() {
    "use strict";
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsActivity.data
    let translations = dtMetricsActivity.translations

    jQuery('#metrics-sidemenu').foundation('down', jQuery('#personal-menu'));

    const title = makeTitle(window.lodash.escape( translations.title ))

    /* highlights */
    chartDiv.empty().html(`
      ${title}
      <div class="section-subheader">${window.lodash.escape(translations.filter_contacts_to_date_range)}</div>
      <div class="date_range_picker">
          <i class="fi-calendar"></i>&nbsp;
          <span>${window.lodash.escape(translations.all_time)}</span>
          <i class="dt_caret down"></i>
      </div>
      <div style="display: inline-block" class="loading-spinner"></div>
      <hr>

      <div id="charts"></div>
    `)

    window.METRICS.setupDatePicker(
      `${dtMetricsActivity.rest_endpoints_base}/highlights_data/`,
      function (data, label) {
        if (data) {
          $('.date_range_picker span').html(label);
          buildHighlights(data, label)
        }
      }
    )

    buildHighlights(sourceData.highlights)
  }
})

function buildHighlights(data, label = "all time") {
  console.log(data, label)
}

function makeTitle(title) {
  return `
    <div class="cell center">
      <h3>${ title }</h3>
    </div>
  `
}
