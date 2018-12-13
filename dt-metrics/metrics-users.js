jQuery(document).ready(function() {
    console.log( dtMetricsUsers )

    if( ! window.location.hash || '#users_activity' === window.location.hash  ) {
        users_activity()
    }

})

function users_activity() {
    "use strict";
    let chartDiv = jQuery('#chart')
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#users-menu'));
    let sourceData = dtMetricsUsers.data
    chartDiv.empty().html(`
        <span class="section-header">${sourceData.translations.title_activity}</span>
        
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                <div class="grid-x">
                    <div class="medium-4 cell center">
                        <h4>${sourceData.translations.label_total_users}<br><span id="total_users">0</span></h4>
                    </div>
                    <div class="medium-4 cell center left-border-grey">
                        <h4>${sourceData.translations.label_total_multipliers}<br><span id="total_multipliers">0</span></h4>
                    </div>
                    <div class="medium-4 cell center left-border-grey">
                        <h4>${sourceData.translations.label_total_dispatchers}<br><span id="total_dispatchers">0</span></h4>
                    </div>
                </div>
            </div>
            <div class="cell">
                <span class="section-subheader">${sourceData.translations.title_recent_activity}</span>
                <div id="chart_line_logins" style="height:300px"></div>
            </div>
            <div class="cell">
            <hr>
                <p><span class="section-subheader">${sourceData.translations.label_contacts_per_user}</span></p>
                <div id="contacts_per_user" ></div>
            </div>
            <div class="cell">
                <hr>
                <div class="grid-x grid-padding-x">
                    <div class="cell medium-6">
                        <span class="section-subheader">${sourceData.translations.label_least_active}</span>
                        <div id="least_active"></div>
                    </div>
                    <div class="cell medium-6">
                        <span class="section-subheader">${sourceData.translations.label_most_active}</span>
                        <div id="most_active"></div>
                    </div>
                </div>
            </div>
        </div>
        `)

    let hero = sourceData.hero_stats
    jQuery('#total_users').html( numberWithCommas( hero.total_users ) )
    jQuery('#total_multipliers').html( numberWithCommas( hero.total_multipliers ) )
    jQuery('#total_dispatchers').html( numberWithCommas( hero.total_dispatchers ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'line', 'table']});

    google.charts.setOnLoadCallback(drawLineChartLogins);
    google.charts.setOnLoadCallback(drawContactsPerUser);
    google.charts.setOnLoadCallback(drawLeastActive);
    google.charts.setOnLoadCallback(drawMostActive);

    function drawLineChartLogins() {
        let chartData = google.visualization.arrayToDataTable( sourceData.recent_activity );
        let options = {
            vAxis: {title: 'logins'},
            // chartArea: {
            //     width: "100%",
            //     height: "85%" },
            legend: { position: 'bottom' }
        };
        let chart = new google.visualization.LineChart( document.getElementById('chart_line_logins') );

        chart.draw(chartData, options);
    }

    function drawContactsPerUser() {
        let chartData = google.visualization.arrayToDataTable( [
            ['Name', 'Total', 'Attempt Needed', 'Attempted', 'Established', 'Meeting Scheduled', 'Meeting Complete', 'Ongoing', 'Being Coached'],
            ['Chris', 20, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
            ['Kara', 10, 4, 3, 0, 10, 5, 6, 7 ],
        ] );
        let options = {
            chartArea: {
                left: '5%',
                top: '7%',
                width: "100%",
                height: "85%" },
            legend: { position: 'bottom' },
            alternatingRowStyle: true,
            sort: 'enable',
            showRowNumber: true,
            width: '100%',

        };
        let chart = new google.visualization.Table( document.getElementById('contacts_per_user') );

        chart.draw(chartData, options);
    }

    function drawLeastActive() {
        let chartData = google.visualization.arrayToDataTable( [
            ['Name', 'Logins Last 30', 'Updates Last 30'],
            ['Jimmy', 3, 0 ],
            ['Lazardo', 0, 0 ]
        ] );
        let options = {
            chartArea: {
                left: '5%',
                top: '7%',
                width: "100%",
                height: "85%" },
            legend: { position: 'bottom' },
            alternatingRowStyle: true,
            sort: 'enable',
            showRowNumber: true,
            width: '100%',

        };
        let chart = new google.visualization.Table( document.getElementById('least_active') );

        chart.draw(chartData, options);
    }

    function drawMostActive() {
        let chartData = google.visualization.arrayToDataTable( [
            ['Name', 'Logins Last 30', 'Updates Last 30'],
            ['Over Achiever', 34, 23 ],
            ['Lazardo', 24, 34 ]
        ] );
        let options = {
            chartArea: {
                left: '5%',
                top: '7%',
                width: "100%",
                height: "85%" },
            legend: { position: 'bottom' },
            alternatingRowStyle: true,
            sort: 'enable',
            showRowNumber: true,
            width: '100%',

        };
        let chart = new google.visualization.Table( document.getElementById('most_active') );

        chart.draw(chartData, options);
    }

}



function numberWithCommas(x) {
    x = x.toString();
    let pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(x))
        x = x.replace(pattern, "$1,$2");
    return x;
}

