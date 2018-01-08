/**
 * This is a duplicate script to the critical_path() and refresh_critical_path_data() found in Disciple Tools Theme metrics.js
 * This is only loaded in the wp-admin and we didn't want to make it dependent on the theme being installed.
 * TODO: Find a less WET way of hosting these two functions.
 * @see /wp-content/themes/disciple-tools-theme/assets/js/metrics.js
 */
function critical_path(){
  "use strict";
  let screen_height = jQuery(window).height()
  let chartDiv = jQuery('#chart')

  chartDiv.append(`<div id="critical-path" style="height: ` + screen_height / 2 + `px; margin: 2.5em 1em; "></div>`)

  jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiDashboard.root + 'dt/v1/metrics/critical_path_chart_data',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiDashboard.nonce);
    },
  })
    .done(function (data) {

      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(function() {

        let chartData = google.visualization.arrayToDataTable(data.chart);

        let options = {
          bars: 'horizontal',
          chartArea: {
            left: '20%',
            top: '0%',
            width: "80%",
            height: "90%" },
          hAxis: {
            scaleType: 'mirrorLog',
            title: 'logarithmic scale'
          },
          legend: {
            position: 'none'
          },
        }

        let chart = new google.visualization.BarChart(document.getElementById('critical-path'));
        chart.draw(chartData, options);

      });

      chartDiv.append(`<div><span class="small grey">( stats as of `+ data.timestamp +` )</span> <a href="javascript:void(0);" onclick="refresh_critical_path_data()">Refresh</a></div>`)

    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })

}
jQuery(document).ready(function() {
  "use strict";
  critical_path()
})

function refresh_critical_path_data(){
  jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiDashboard.root + 'dt/v1/metrics/refresh_critical_path',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiDashboard.nonce);
    },
  })
    .done(function (data) {
      location.reload();
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })

}

