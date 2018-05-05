jQuery(document).ready(function() {
    console.log( wpApiMetrics )

    if( ! window.location.hash || '#my_contacts' === window.location.hash  ) {
        jQuery('#metrics-sidemenu').foundation('toggle', jQuery('#critical-path-menu'));
        show_critical_path()
    }
})

function show_critical_path() {
    jQuery('#chart').empty().html('<span class="section-header">Critical Path</span><hr />')
    critical_path()
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
        url: wpApiMetrics.root + 'dt/v1/metrics/critical_path_chart_data',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiMetrics.nonce);
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
        url: wpApiMetrics.root + 'dt/v1/metrics/refresh_critical_path',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiMetrics.nonce);
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

function numberWithCommas(x) {
    x = x.toString();
    let pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(x))
        x = x.replace(pattern, "$1,$2");
    return x;
}