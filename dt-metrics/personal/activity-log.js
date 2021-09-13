jQuery(document).ready(function() {
  if ('metrics' === window.wpApiShare.url_path || 'metrics/' === window.wpApiShare.url_path || window.wpApiShare.url_path.startsWith( 'metrics/personal/activity-log' ) ) {
    console.log('hi there')
    my_stats()
  }

  function my_stats() {
    "use strict";
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsPersonal.data
    let translations = dtMetricsPersonal.data.translations

    jQuery('#metrics-sidemenu').foundation('down', jQuery('#personal-menu'));

    let html = `
      <div class="cell center">
          <h3 >${ window.lodash.escape( translations.title ) }</h3>
      </div>
      `

    chartDiv.empty().html( html )

  }
})