jQuery(document).ready(function() {

  // console.log(dt_mapbox_metrics)

  if ('/metrics/groups/tree' === window.location.pathname || '/metrics/groups/tree/' === window.location.pathname  ) {
    project_group_tree()
  }


  function project_group_tree() {
    "use strict";
    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner active"></span> '

    chart.empty().html(spinner)


    //   jQuery('#metrics-sidemenu').foundation('down', jQuery('#groups-menu'));
    //
    //   let chartDiv = jQuery('#chart')
    //   let sourceData = dtMetricsProject.data
    //   let translations = dtMetricsProject.data.translations
    //
    //   let height = $(window).height()
    //   let chartHeight = height - (height * .15)
    //
    //   chartDiv.empty().html(`
    //       <span class="section-header">${_.escape(translations.title_group_tree)}</span><hr>
    //
    //       <br clear="all">
    //       <div class="grid-x grid-padding-x">
    //       <div class="cell">
    //            <span>
    //               <button class="button hollow toggle-singles" id="highlight-active" onclick="highlight_active();">Highlight Active</button>
    //           </span>
    //           <span>
    //               <button class="button hollow toggle-singles" id="highlight-churches" onclick="highlight_churches();">Highlight Churches</button>
    //           </span>
    //       </div>
    //           <div class="cell">
    //               <div class="scrolling-wrapper" id="generation_map"><img src="${dtMetricsProject.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" /></div>
    //           </div>
    //       </div>
    //       <div id="modal" class="reveal" data-reveal></div>
    //   `)
    //
    //   jQuery.ajax({
    //     type: "POST",
    //     contentType: "application/json; charset=utf-8",
    //     data: JSON.stringify({"type": "groups"}),
    //     dataType: "json",
    //     url: dtMetricsProject.root + 'dt/v1/metrics/project/tree/',
    //     beforeSend: function (xhr) {
    //       xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
    //     },
    //   })
    //     .done(function (data) {
    //       if (data) {
    //         jQuery('#generation_map').empty().html(data)
    //         jQuery('#generation_map li:last-child').addClass('last');
    //       }
    //     })
    //     .fail(function (err) {
    //       console.log("error")
    //       console.log(err)
    //       jQuery("#errors").append(err.responseText)
    //     })
    //
    //   new Foundation.Reveal(jQuery('#modal'))
    // }
  }
})
