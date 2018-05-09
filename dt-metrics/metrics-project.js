jQuery(document).ready(function() {
    console.log( dtMetricsProject )

    if( ! window.location.hash || '#project_overview' === window.location.hash  ) {
        project_overview()
        jQuery('#projects-menu').foundation('down', $target);
    }
})

function project_overview() {
    "use strict";
    let chartDiv = jQuery('#chart')
    let overview = dtMetricsProject.project_overview
    chartDiv.empty().html(`
        <span class="section-header">`+ dtMetricsProject.project_overview.translations.title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                <div class="grid-x">
                    <div class="medium-3 cell center">
                        <h4>Total Contacts<br><span id="total_contacts">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Total Groups<br><span id="need_accepted">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Total Users<br><span id="updates_needed">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Total Locations<br><span id="attempts_needed">0</span></h4>
                    </div>
                </div>
            </div>
            <div class="cell">
                <div id="my_critical_path" style="height: 700px;"></div>
            </div>
            
        </div>
        `)

    let hero = overview.hero_stats
    jQuery('#total_contacts').html( numberWithCommas( hero.total_contacts ) )
    jQuery('#updates_needed').html( numberWithCommas( hero.updates_needed ) )
    jQuery('#attempts_needed').html( numberWithCommas( hero.attempts_needed ) )

    jQuery('#total_groups').html( numberWithCommas( hero.total_groups ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});

    google.charts.setOnLoadCallback(drawCriticalPath);

    function drawCriticalPath() {

        let data = google.visualization.arrayToDataTable( overview.critical_path );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            title: "Critical Path",
            legend: {position: "none"},
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_critical_path'));
        chart.draw(data, options);
    }
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