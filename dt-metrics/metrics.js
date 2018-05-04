jQuery(document).ready(function() {
    console.log( dtMetrics )

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
        url: dtMetrics.root + 'dt/v1/metrics/critical_path_chart_data',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtMetrics.nonce);
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
        url: dtMetrics.root + 'dt/v1/metrics/refresh_critical_path',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtMetrics.nonce);
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

function show_zume_project(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <span class="section-header">`+ wpApiZumeMetrics.translations.zume_project +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal>`+ legend() +` <button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        
        <div id="zume-locations" style="height: 500px; margin: 0 1em 1.2em; "></div>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell">
                <div class="grid-x callout">
                    <div class="medium-3 cell center">
                    <h4>Trained Groups<br><span class="trained_groups"></span></h4>
                    </div>
                    <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>Trained People<br><span class="trained_people"></span></h4>
                    </div>
                    <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>Countries<br><span id="total_countries"></span></h4>
                    </div>
                    <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>Translations<br><span id="total_languages"></span></h4>
                    </div>
                    
                </div>
            </div>
            <div class="cell center">
                <p class="section-subheader" >Groups Trends</p>
                <div id="combo_trend_groups" style="width: 100%; height: 500px;"></div>
            </div>
            <div class="cell">
                <div class="grid-x callout">
                    <div class="medium-3 cell center">
                    <h4>Registered Groups<br><span id="registered_groups"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Engaged Groups<br><span id="engaged_groups"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Trained Groups<br><span class="trained_groups"></span></h4>
                    </div>
                    <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>Total Training<br><span id="hours_trained_group"></span> hours</h4>
                    </div>
                </div>
            </div>
            <div class="cell center">
                <span class="section-subheader" >People Trends</span>
                <div id="combo_trend_people" style="width: 100%; height: 500px;"></div>
            </div>
            <div class="cell center">
                <div class="grid-x callout">
                    <div class="medium-3 cell center">
                    <h4>Registered People<br><span id="registered_people"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Engaged People<br><span id="engaged_people"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Trained People<br><span class="trained_people"></span></h4>
                    </div>
                    <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>Total Training<br><span id="hours_trained_per_person"></span> hours</h4>
                    </div>
                </div>
            </div>
            <div class="cell center">
                <span class="section-subheader" >Activity in the Last 30 Days</span>
                <div id="combo_active" style="width: 100%; height: 500px;"></div>
            </div>
            <div class="cell center"><hr>
                <div class="grid-x">
                    <div class="cell center medium-6">
                        <span class="section-subheader" >Languages by User</span>
                        <div id="people_languages" style="width: 100%; height: 300px"></div>
                    </div>
                    <div class="cell center medium-6">
                        <span class="section-subheader" >Logins by Month</span>
                        <div id="chart_line_logins" style="width: 100%; height: 300px"></div>
                    </div>
                </div>
            </div>
        </div>
        `)

    // Add hero stats
    let hero = wpApiZumeMetrics.zume_stats.hero_stats
    jQuery('#hours_trained_group').append( numberWithCommas( hero.hours_trained_as_group ) )
    jQuery('#hours_trained_per_person').append( numberWithCommas( hero.hours_trained_per_person ) )
    jQuery('#total_countries').append( numberWithCommas( hero.total_countries ) )
    jQuery('#total_languages').append( numberWithCommas( hero.total_languages ) )

    jQuery('#registered_groups').append( numberWithCommas( hero.registered_groups ) )
    jQuery('#engaged_groups').append( numberWithCommas( hero.engaged_groups ) )
    jQuery('.trained_groups').append( numberWithCommas( hero.trained_groups ) )
    jQuery('#active_groups').append( numberWithCommas( hero.active_groups ) )

    jQuery('#registered_people').append( numberWithCommas( hero.registered_people ) )
    jQuery('#engaged_people').append( numberWithCommas( hero.engaged_people ) )
    jQuery('.trained_people').append( numberWithCommas( hero.trained_people ) )
    jQuery('#active_people').append( numberWithCommas( hero.active_people ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'controls', 'table']});

    google.charts.setOnLoadCallback(drawWorld);
    google.charts.setOnLoadCallback(drawComboTrendsGroups);
    google.charts.setOnLoadCallback(drawComboTrendsPeople);
    google.charts.setOnLoadCallback(drawComboActive);
    google.charts.setOnLoadCallback(drawLanguagesChart);
    google.charts.setOnLoadCallback(drawLineChartLogins);

    function drawWorld() {

        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.group_coordinates)
        let options = {
            tooltip: {trigger: 'none'}
        };
        let chart = new google.visualization.GeoChart(document.getElementById('zume-locations'));
        chart.draw(data, options);
    }


    function drawComboTrendsGroups() {
        // Some raw data (not necessarily accurate)
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.groups_progress_by_month);

        let options = {
            hAxis: {title: 'Months' },
            // vAxis: { scaleType: 'mirrorLog' },
            seriesType: 'bars',
            chartArea:{left: '10%',top:'5px',width:'75%',height:'75%'},
            series: {3: {type: 'line'}},
            colors:['lightgreen', 'limegreen', 'green', 'darkgreen'],
        };

        let chart = new google.visualization.ComboChart(document.getElementById('combo_trend_groups'));
        chart.draw(data, options);
    }

    function drawComboTrendsPeople() {
        // Some raw data (not necessarily accurate)
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.people_progress_by_month);

        let options = {
            hAxis: {title: 'Months'},
            seriesType: 'bars',
            chartArea:{left: '10%',top:'5px',width:'75%',height:'75%'},
            series: {3: {type: 'line'}},
            colors:['lightblue', 'skyblue', 'blue', 'darkblue'],
        };

        let chart = new google.visualization.ComboChart(document.getElementById('combo_trend_people'));
        chart.draw(data, options);
    }

    function drawComboActive() {
        // Some raw data (not necessarily accurate)
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.active_by_month);

        let options = {
            hAxis: {title: 'Months'},
            seriesType: 'bars',
            chartArea:{left: '10%',top:'5px',width:'75%',height:'75%'},
            series: {2: {type: 'line'}},
            colors:['limegreen', 'skyblue', 'darkblue'],
        };

        let chart = new google.visualization.ComboChart(document.getElementById('combo_active'));
        chart.draw(data, options);
    }

    function drawLanguagesChart() {
        let chartData = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.people_languages );
        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '10%',
                top: '10px',
                width: "80%",
                height: "90%" },
            pieHole: 0.4,
        }

        let chart = new google.visualization.PieChart(document.getElementById('people_languages'));
        chart.draw(chartData, options);
    }

    function drawLineChartLogins() {
        let chartData = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.logins_by_month );
        let options = { legend: { position: 'bottom' } };
        let chart = new google.visualization.LineChart( document.getElementById('chart_line_logins') );

        chart.draw(chartData, options);
    }

    new Foundation.Reveal(jQuery('#zume-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_project' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_zume_groups(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.8
    let chartDiv = jQuery('#chart')

    chartDiv.empty().html(`
        <span class="section-header">`+ wpApiZumeMetrics.translations.zume_groups +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal>`+ legend() +` <button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
            
            <br><br>
            <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell">
                <div class="grid-x callout">
                    <div class="medium-3 cell center">
                    <h4>Registered Groups<br><span id="registered_groups"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Engaged Groups<br><span id="engaged_groups"></span></h4>
                    </div>
                    <div class="medium-3 cell center">
                    <h4>Trained Groups<br><span id="trained_groups"></span></h4>
                    </div>
                    <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>Recently Active<br><span id="active_groups"></span></h4>
                    </div>
                </div>
            </div>
            
            <div class="cell center">
                <span class="section-subheader">Members in Groups</span>
                <div id="zume-groups" style="height: 500px; margin: 0 1em; "></div>
            </div>
            <div class="cell center">
            <hr>
                <span class="section-subheader">Next Session for Groups</span>
                <div id="groups-in-session" style="height: 400px;"></div>
            </div>
            <div class="cell center">
            <hr>
                <p class="section-subheader" >Sessions Completed by Groups</p>
                <div id="sessions_completed_by_groups" style="height: 400px; "></div>
            </div>
            <div class="cell center">
            <hr>
                <p class="section-subheader" >Groups Trends</p>
                <div id="combo_trend_groups" style="width: 100%; height: 500px;"></div>
            </div>
        </div>
        `)

    // Add hero stats
    let hero = wpApiZumeMetrics.zume_stats.hero_stats
    jQuery('#registered_groups').append( numberWithCommas( hero.registered_groups ) )
    jQuery('#engaged_groups').append( numberWithCommas( hero.engaged_groups ) )
    jQuery('#trained_groups').append( numberWithCommas( hero.trained_groups ) )
    jQuery('#active_groups').append( numberWithCommas( hero.active_groups ) )

    google.charts.load('current', {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawMembersPerGroup)
    google.charts.setOnLoadCallback(drawCurrentSessionChart)
    google.charts.setOnLoadCallback(drawSessionsCompleted)
    google.charts.setOnLoadCallback(drawComboTrendsGroups)

    function drawMembersPerGroup() {

        let data = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.members_per_group );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '15%',
                top: '0%',
                width: "80%",
                height: "90%" },
            vAxis: {
                title: 'group size',
            },
            hAxis: {
                title: 'number of groups',
            },
            title: "Number of groups according to their member count",
            legend: {position: "none"},
            colors: ['green'],
        };

        let chart = new google.visualization.BarChart(document.getElementById('zume-groups'));
        chart.draw(data, options);
    }

    function drawCurrentSessionChart() {
        // Members in Groups
        let data = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.current_session_of_group );

        let options = {
            bars: 'vertical',
            chartArea: {
                left: '15%',
                top: '0%',
                width: "80%",
                height: "90%" },
            vAxis: {
                title: 'session'
            },
            hAxis: {
                title: 'number of groups at different stages',
                scaleType: 'mirrorLog'
            },
            legend: {
                position: 'none'
            },
            colors: ['green'],
        }

        let chart = new google.visualization.BarChart(document.getElementById('groups-in-session'));
        chart.draw(data, options);
    }

    function drawSessionsCompleted() {
        let data = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.sessions_completed_by_groups );
        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '15%',
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
            colors: ['lightgreen'],
        }

        let chart = new google.visualization.BarChart(document.getElementById('sessions_completed_by_groups'));
        chart.draw(data, options);
    }

    function drawComboTrendsGroups() {
        // Some raw data (not necessarily accurate)
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.groups_progress_by_month);

        let options = {
            hAxis: {title: 'Months' },
            // vAxis: { scaleType: 'mirrorLog' },
            seriesType: 'bars',
            chartArea:{left: '10%',top:'5px',width:'75%',height:'75%'},
            series: {3: {type: 'line'}},
            colors:['lightgreen', 'limegreen', 'green', 'darkgreen'],
        };

        let chart = new google.visualization.ComboChart(document.getElementById('combo_trend_groups'));
        chart.draw(data, options);
    }

    new Foundation.Reveal(jQuery('#zume-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_groups' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_zume_people(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.3
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`<span class="section-header">`+ wpApiZumeMetrics.translations.zume_people +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal>`+ legend() +` <button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
            
            <br><br>
                <div class="grid-x grid-padding-x grid-padding-y">
                    <div class="cell center">
                        <div class="grid-x callout">
                            <div class="medium-3 cell center">
                            <h4>Registered People<br><span id="registered_people"></span></h4>
                            </div>
                            <div class="medium-3 cell center">
                            <h4>Engaged People<br><span id="engaged_people"></span></h4>
                            </div>
                            <div class="medium-3 cell center">
                            <h4>Trained People<br><span id="trained_people"></span></h4>
                            </div>
                            <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                            <h4>Recently Active<br><span id="active_people" ></span></h4>
                            </div>
                        </div>
                    </div>
                    
                    <div class="cell center">
                        <span class="section-subheader">People Trends</span>
                        <div id="combo_trend_people" style="width: 100%; height: 400px;"></div>
                        <hr>
                    </div>
                    <div class="cell center">
                        <span class="section-subheader">Language Users in Zúme</span>
                        <div id="people_languages" style="height: 500px; margin: 0 1em; "></div>
                    </div>
                    <div class="cell center">
                    <hr>
                        <span class="section-subheader" >Logins by Month</span>
                        <div id="chart_line_logins" style="width: 100%; height: 500px"></div>
                    </div>
            </div>
        `)

    let hero = wpApiZumeMetrics.zume_stats.hero_stats
    jQuery('#registered_people').append( numberWithCommas( hero.registered_people ) )
    jQuery('#engaged_people').append( numberWithCommas( hero.engaged_people ) )
    jQuery('#trained_people').append( numberWithCommas( hero.trained_people ) )
    jQuery('#active_people').append( numberWithCommas( hero.active_people ) )

    google.charts.load('current', {'packages':['corechart', 'treemap']});

    google.charts.setOnLoadCallback(drawComboTrendsPeople);
    google.charts.setOnLoadCallback(drawLanguagesChart)
    google.charts.setOnLoadCallback(drawLineChartLogins);


    function drawComboTrendsPeople() {
        // Some raw data (not necessarily accurate)
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.people_progress_by_month);

        let options = {
            hAxis: {title: 'Months'},
            seriesType: 'bars',
            chartArea:{left: '10%',top:'5px',width:'75%',height:'75%'},
            series: {3: {type: 'line'}},
            colors:['lightblue', 'skyblue', 'blue', 'darkblue'],
        };

        let chart = new google.visualization.ComboChart(document.getElementById('combo_trend_people'));
        chart.draw(data, options);
    }

    function drawLanguagesChart() {
        let chartData = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.people_languages );
        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '10%',
                top: '10px',
                width: "80%",
                height: "90%" },
            pieHole: 0.4,
        }

        let chart = new google.visualization.PieChart(document.getElementById('people_languages'));
        chart.draw(chartData, options);
    }

    function drawLineChartLogins() {
        let chartData = google.visualization.arrayToDataTable( wpApiZumeMetrics.zume_stats.logins_by_month );
        let options = { legend: { position: 'bottom' } };
        let chart = new google.visualization.LineChart( document.getElementById('chart_line_logins') );

        chart.draw(chartData, options);
    }

    new Foundation.Reveal(jQuery('#zume-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_project' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function show_zume_locations(){
    "use strict";
    let screenHeight = jQuery(window).height()
    let chartHeight = screenHeight / 1.8
    let chartDiv = jQuery('#chart')

    chartDiv.empty().html(`<span class="section-header">`+ wpApiZumeMetrics.translations.zume_locations +`</span>
        
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="zume-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="zume-project-legend" data-reveal>`+ legend() +` <button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <div class="grid-x">
            <div class="cell center">
                <span class="section-subheader">U.S.A</span>
                <div id="zume-region-usa" style="height: 500px; margin: 1.2em 1em; "></div>
            </div>
            <div class="cell center">
            <hr>
                <span class="section-subheader">Africa</span>
                <div id="zume-region-africa" style="height: 500px; margin: 1.2em 1em; "></div>
            </div>
            <div class="cell center">
            <hr>
                <span class="section-subheader">Europe</span>
                <div id="zume-region-europe" style="height: 500px; margin: 1.2em 1em; "></div>
            </div>
            <div class="cell center">
            <hr>
                <span class="section-subheader">Asia</span>
                <div id="zume-region-asia" style="height: 500px; margin: 1.2em 1em; "></div>
            </div>
            <div class="cell center">
                <hr>
                <span class="section-subheader">South America</span>
                <div id="zume-region-americas" style="height: 500px; margin: 1.2em 1em; "></div>
            </div>
            <div class="cell center">
                <hr>
                <p><span class="section-subheader">Groups and Individuals by Country</span></p>
            </div>
            <div class="cell">
                <div class="grid-x grid-padding-x">
                    <div class="cell medium-6">
                        <div id="country_list_by_group"></div>
                    </div>
                    <div class="cell medium-6">
                        <div id="country_list_by_people"></div>
                    </div>
                </div>
            </div>
        </div>
        
    `)

    google.charts.load('current', {'packages':['geochart', 'table'], 'mapsApiKey': wpApiZumeMetrics.map_key });

    google.charts.setOnLoadCallback(drawRegions);
    google.charts.setOnLoadCallback(drawGroupCountryTable);
    google.charts.setOnLoadCallback(drawPeopleCountryTable);

    function drawRegions() {

        /* Codes for regions found at the bottom of https://developers.google.com/chart/interactive/docs/gallery/geochart */

        // USA
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.group_coordinates)

        let chart1 = new google.visualization.GeoChart(document.getElementById('zume-region-usa'));
        chart1.draw(data, {
            region: 'US',
            resolution: 'provinces',
            tooltip: {trigger: 'none'}
        });

        // AFRICA
        let chart2 = new google.visualization.GeoChart(document.getElementById('zume-region-africa'));
        chart2.draw(data, {
            region: '002',
            tooltip: {trigger: 'none'}
        });

        // EUROPE
        let chart3 = new google.visualization.GeoChart(document.getElementById('zume-region-europe'));
        chart3.draw(data, {
            region: '150',
            tooltip: {trigger: 'none'}
        });

        // ASIA
        let chart4 = new google.visualization.GeoChart(document.getElementById('zume-region-asia'));
        chart4.draw(data, {
            region: '142',
            tooltip: {trigger: 'none'}
        });

        // AMERICAS
        let chart6 = new google.visualization.GeoChart(document.getElementById('zume-region-americas'));
        chart6.draw(data, {
            region: '005',
            tooltip: {trigger: 'none'}
        });
    }

    function drawGroupCountryTable() {
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.country_list_by_group);
        let table = new google.visualization.Table( document.getElementById('country_list_by_group') );
        table.draw(data, {
            width: '100%',
            height: '100%',
            sort: 'enable',
            sortColumn: 1,
            sortAscending: false,
            page: 'enable',
            pageSize: 20,
            pagingButtons: 'auto',
        });
    }

    function drawPeopleCountryTable() {
        let data = google.visualization.arrayToDataTable(wpApiZumeMetrics.zume_stats.country_list_by_people);
        let table = new google.visualization.Table( document.getElementById('country_list_by_people') );
        table.draw(data, {
            width: '100%',
            height: '100%',
            sort: 'enable',
            sortColumn: 1,
            sortAscending: false,
            page: 'enable',
            pageSize: 20,
            pagingButtons: 'auto',
        });
    }

    new Foundation.Reveal(jQuery('#zume-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( stats as of `+ wpApiZumeMetrics.zume_stats.timestamp +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_locations' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+wpApiZumeMetrics.plugin_uri+`includes/ajax-loader.gif" /></span> 
            </div>`)
}

function legend() {
    return `<h2>Chart Legend</h2><hr>
            <dl>
            <dt>Registered</dt><dd>Groups or people who have registered on Zumeproject.com</dd>
            <dt>Engaged</dt><dd>Groups or people who have registered on Zumeproject.com</dd>
            <dt>Trained</dt><dd>Trained groups and people have been through the entire Zúme training.</dd>
            <dt>Active</dt><dd>Active groups and people have finished a session in the last 30 days. Active in month charts measure according to the month listed. It is the same 'active' behavior, but broken up into different time units.</dd>
            <dt>Hours of Training</dt><dd>Hours of completed sessions for groups or people.</dd>
            <dt>Countries</dt><dd>In the overview page, "Countries" counts number of countries with trained groups.</dd>
            <dt>Translations</dt><dd>Translations counts the number of translations installed in ZúmeProject.com.</dd>
            </dl>`
}

function refresh_stats_data( page ){
    jQuery.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: wpApiMetricsPage.root + 'dt/v1/zume/reset_zume_stats',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiMetricsPage.nonce);
        },
    })
        .done(function (data) {
            wpApiZumeMetrics.zume_stats = data
            switch( page ) {
                case 'show_zume_languages':
                    show_zume_languages()
                    break;
                case 'show_zume_locations':
                    show_zume_locations()
                    break;
                case 'show_zume_groups':
                    show_zume_groups()
                    break;
                case 'show_zume_pipeline':
                    show_zume_pipeline()
                    break;
                case 'show_zume_project':
                    show_zume_project()
                    break;

                default:
                    break;
            }
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