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
        <span class="section-header">`+ sourceData.translations.title_activity +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                <div class="grid-x">
                    <div class="medium-3 cell center">
                        <h4>Total Users<br><span id="total_users">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Multipliers<br><span id="total_multipliers">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Dispatchers<br><span id="total_dispatchers">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Other<br><span id="total_other">0</span></h4>
                    </div>
                    
                </div>
            </div>
            <div class="cell">
                <span class="section-subheader">Recent Logins</span>
                <div id="chart_line_logins" style="height:300px"></div>
            </div>
            <div class="cell">
            <hr>
                <p><span class="section-subheader">Contacts Per User</span></p>
                <div id="contacts_per_user" ></div>
            </div>
            <div class="cell">
                <hr>
                <div class="grid-x grid-padding-x">
                    <div class="cell medium-6">
                        <span class="section-subheader">Least Active</span>
                        <div id="least_active"></div>
                    </div>
                    <div class="cell medium-6">
                        <span class="section-subheader">Most Active</span>
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
    jQuery('#total_other').html( numberWithCommas( hero.total_other ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'line', 'table']});

    google.charts.setOnLoadCallback(drawLineChartLogins);
    google.charts.setOnLoadCallback(drawContactsPerUser);
    google.charts.setOnLoadCallback(drawLeastActive);
    google.charts.setOnLoadCallback(drawMostActive);

    function drawLineChartLogins() {
        let chartData = google.visualization.arrayToDataTable( [
            ['Year', 'Logins', 'Updates', 'Status Changes'],
            ['June',  1000, 400, 100],
            ['July',  1000, 400, 100],
            ['Aug',  1000, 400, 100],
            ['Sept',  1000, 400, 100],
            ['Oct',  1170, 460, 150],
            ['Nov',  660, 1120, 12],
        ] );
        let options = {
            vAxis: {title: 'logins'},
            // chartArea: {
            //     left: '5%',
            //     top: '7%',
            //     width: "90%",
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


    new Foundation.Reveal(jQuery('.dt-project-legend'));

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

function numberWithCommas(x) {
    x = x.toString();
    let pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(x))
        x = x.replace(pattern, "$1,$2");
    return x;
}

