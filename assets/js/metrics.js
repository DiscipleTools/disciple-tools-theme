/* this is for dedicated scripting for metrics */

jQuery(document).ready(function() {
  jQuery('#metrics-sidemenu').foundation('toggle', jQuery('#critical-path-menu'));
  show_critical_path()
})

function show_critical_path() {
  jQuery('#chart').empty().html('<span class="section-header">Critical Path</span><hr />')

  critical_path()
}

function show_critical_path_prayer(){
  "use strict";
  jQuery('#chart').empty().html('<span class="section-header">Prayer</span><hr />')

  critical_path_prayer()
}

function show_critical_path_outreach(){
  "use strict";
  jQuery('#chart').empty().html('<span class="section-header">Outreach</span><hr />')

  critical_path_outreach()
}

function show_critical_path_fup(){
  "use strict";
  jQuery('#chart').empty().html('<span class="section-header">Follow-up</span><hr />')

  critical_path_fup()
}

function show_critical_path_multiplication(){
  "use strict";
  jQuery('#chart').empty().html('<span class="section-header">Multiplication</span><hr />')

  critical_path_multiplication()
}

function show_contacts(){
  "use strict";
  jQuery('#chart').empty().html('<span class="section-header">Contacts</span><hr />')

}

function show_groups(){
  "use strict";
  jQuery('#chart').empty().html('<span class="section-header">Groups</span><hr />')

}

function show_workers(){
  "use strict";
  jQuery('#chart').empty().html('<span class="section-header">Workers</span><hr />')

}

function show_locations(){
  "use strict";
  jQuery('#chart').empty().html('<span class="section-header">Locations</span><hr />')

}

function show_pace(){
  "use strict";
  jQuery('#chart').empty().html('<span class="section-header">Pace</span><hr />')

}

function critical_path(){
  "use strict";
  let screen_height = jQuery(window).height()
  let chartDiv = jQuery('#chart')

  chartDiv.append(`<div id="critical-path" style="height: ` + screen_height / 1.6 + `px; margin: 2.5em 1em; "></div>`)

  jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiMetricsPage.root + 'dt/v1/metrics/critical_path_chart_data',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiMetricsPage.nonce);
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

      chartDiv.append(`<div><span class="small grey">( stats as of `+ data.timestamp +` )</span> <a onclick="refresh_critical_path_data()">Refresh</a></div>`)

    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })

}

function refresh_critical_path_data(){
  jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiMetricsPage.root + 'dt/v1/metrics/refresh_critical_path',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiMetricsPage.nonce);
    },
  })
    .done(function (data) {
      show_critical_path()
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })

}

function critical_path_prayer(){
  "use strict";
  jQuery('#chart').append('<div id="critical-path-prayer" style="height: 140px; margin: 2.5em 1em; "></div><hr />')

  jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiMetricsPage.root + 'dt/v1/metrics/critical_path_prayer',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiMetricsPage.nonce);
    },
  })
    .done(function (data) {

      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(function() {
        "use strict";
        let chartData = google.visualization.arrayToDataTable(data);

        let options = {
          bars: 'horizontal',

        };

        let chart = new google.charts.Bar(document.getElementById('critical-path-prayer'));
        chart.draw(chartData, options);
      });

    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}

function critical_path_outreach(){
  "use strict";
  jQuery('#chart').append('<div id="critical-path-outreach" style="height: 300px;margin: 2.5em 1em;"></div><hr />')

  jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiMetricsPage.root + 'dt/v1/metrics/critical_path_outreach',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiMetricsPage.nonce);
    },
  })
    .done(function (data) {

      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(function() {
        "use strict";
        let chartData = google.visualization.arrayToDataTable(data);

        let options = {
          bars: 'horizontal',

        };

        let chart = new google.charts.Bar(document.getElementById('critical-path-outreach'));
        chart.draw(chartData, options);
      });

    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}

function critical_path_fup() {
  "use strict";
  jQuery('#chart').append('<div id="critical-path-fup" style="height: 500px;margin: 2.5em 1em;"></div><hr />')

  jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiMetricsPage.root + 'dt/v1/metrics/critical_path_fup',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiMetricsPage.nonce);
    },
  })
    .done(function (data) {

      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(function() {
        "use strict";
        let chartData = google.visualization.arrayToDataTable(data);

        let options = {
          bars: 'horizontal',

        };

        let chart = new google.charts.Bar(document.getElementById('critical-path-fup'));
        chart.draw(chartData, options);
      });

    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}

function critical_path_multiplication() {
  "use strict";
  jQuery('#chart').append('<div id="critical-path-multiplication" style="height: 500px;margin: 2.5em 1em;"></div>')

  jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiMetricsPage.root + 'dt/v1/metrics/critical_path_multiplication',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiMetricsPage.nonce);
    },
  })
    .done(function (data) {

      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(function() {
        "use strict";
        let chartData = google.visualization.arrayToDataTable(data);

        let options = {
          bars: 'horizontal',

        };

        let chart = new google.charts.Bar(document.getElementById('critical-path-multiplication'));
        chart.draw(chartData, options);
      });

    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}

function show_fake_chart( text ) { // TODO: Remove this placeholder function
  "use strict";
  jQuery('#chart').html(`<img src="http://via.placeholder.com/1000x600?text=` + text + `" width="1000px" height="600px"/>`)
}
