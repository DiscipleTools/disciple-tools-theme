/* this is for dedicated scripting for metrics */

function show_fake_chart( text ) { // TODO: Remove this placeholder function
  "use strict";
  jQuery('#chart').html(`<img src="http://via.placeholder.com/1000x600?text=` + text + `" width="1000px" height="600px"/>`)
}

function show_critical_path() {
  jQuery('#chart').empty().html('<span class="section-header">Critical Path</span><hr />')

  critical_path_prayer()
  critical_path_media()
  critical_path_fup()
  critical_path_multiplication()
}

function critical_path_prayer(){
  "use strict";
  jQuery('#chart').append('<div id="critical-path-prayer" style="height: 140px; margin: 2.5em 1em; "></div><hr />')

  jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiNotifications.root + 'dt/v1/metrics/critical_path_prayer',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiNotifications.nonce);
    },
  })
    .done(function (data) {

      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(function() {
        "use strict";
        var chartData = google.visualization.arrayToDataTable(data);

        var options = {
          bars: 'horizontal',

        };

        var chart = new google.charts.Bar(document.getElementById('critical-path-prayer'));
        chart.draw(chartData, options);
      });

    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}

function critical_path_media(){
  "use strict";
  jQuery('#chart').append('<div id="critical-path-media" style="height: 300px;margin: 2.5em 1em;"></div><hr />')

  jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiNotifications.root + 'dt/v1/metrics/critical_path_media',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiNotifications.nonce);
    },
  })
    .done(function (data) {

      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(function() {
        "use strict";
        var chartData = google.visualization.arrayToDataTable(data);

        var options = {
          bars: 'horizontal',

        };

        var chart = new google.charts.Bar(document.getElementById('critical-path-media'));
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
    url: wpApiNotifications.root + 'dt/v1/metrics/critical_path_fup',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiNotifications.nonce);
    },
  })
    .done(function (data) {

      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(function() {
        "use strict";
        var chartData = google.visualization.arrayToDataTable(data);

        var options = {
          bars: 'horizontal',

        };

        var chart = new google.charts.Bar(document.getElementById('critical-path-fup'));
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
    url: wpApiNotifications.root + 'dt/v1/metrics/critical_path_multiplication',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiNotifications.nonce);
    },
  })
    .done(function (data) {

      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(function() {
        "use strict";
        var chartData = google.visualization.arrayToDataTable(data);

        var options = {
          bars: 'horizontal',

        };

        var chart = new google.charts.Bar(document.getElementById('critical-path-multiplication'));
        chart.draw(chartData, options);
      });

    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}
