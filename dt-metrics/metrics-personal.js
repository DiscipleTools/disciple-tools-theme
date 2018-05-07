jQuery(document).ready(function() {
    console.log( dtMetricsPersonal )

    if( ! window.location.hash || '#overview' === window.location.hash  ) {
        overview()
    }
    if( '#my_contacts_progress' === window.location.hash  ) {
        my_contacts_progress()
    }
    if( '#my_groups_progress' === window.location.hash  ) {
        my_groups_progress()
    }
})

function overview() {
    "use strict";
    let chartDiv = jQuery('#chart')
    let overview = dtMetricsPersonal.overview
    chartDiv.empty().html(`
        <span class="section-header">`+ dtMetricsPersonal.overview.translations.title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell">
                <div class="grid-x callout">
                    <div class="medium-3 cell center">
                        <h4>Total Contacts<br><span id="total_contacts"></span></h4>
                    </div>
                    <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                        <h4>Total Groups<br><span id="total_groups"></span></h4>
                    </div>
                    <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                        <h4>Updates Needed<br><span id="updates_needed"></span></h4>
                    </div>
                    <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                        <h4>Attempts Needed<br><span id="attempts_needed"></span></h4>
                    </div>
                </div>
            </div>
            <div class="cell center">
                <span class="section-subheader">My Contacts Progress</span>
            </div>
            <div class="cell">
                <div id="my_contacts_progress" style="height: 500px; margin: 0 1em 1.2em; "></div>
            </div>
            <div class="cell center">
                <hr>
                <span class="section-subheader">My Groups Progress</span>
            </div>
            <div class="cell">
                <div id="my_groups_progress" style="height: 500px; margin: 0 1em 1.2em; "></div>
            </div>
            <div class="cell center">
                <hr>
                <span class="section-subheader">My Critical Path</span>
            </div>
            <div class="cell">
                <div id="my_critical_path" style="height: 500px; margin: 0 1em 1.2em; "></div>
            </div>
        </div>
        `)

    let hero = overview.hero_stats
    jQuery('#total_contacts').append( numberWithCommas( hero.total_contacts ) )
    jQuery('#total_groups').append( numberWithCommas( hero.total_groups ) )
    jQuery('#updates_needed').append( numberWithCommas( hero.updates_needed ) )
    jQuery('#attempts_needed').append( numberWithCommas( hero.attempts_needed ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});

    google.charts.setOnLoadCallback(drawMyContactsProgress);
    google.charts.setOnLoadCallback(drawMyGroupsProgress);
    google.charts.setOnLoadCallback(drawCriticalPath);

    function drawMyContactsProgress() {

        let data = google.visualization.arrayToDataTable( overview.contacts_progress );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '25%',
                top: '0%',
                width: "80%",
                height: "90%" },
            hAxis: {
                title: 'number of contacts',
            },
            title: "Number of groups according to their member count",
            legend: {position: "none"},
            colors: ['blue'],
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_contacts_progress'));
        chart.draw(data, options);
    }

    function drawMyGroupsProgress() {

        let data = google.visualization.arrayToDataTable( overview.groups_progress );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '25%',
                top: '0%',
                width: "80%",
                height: "90%" },
            vAxis: {
                title: 'steps',
            },
            hAxis: {
                title: 'number of groups',
            },
            title: "Number of groups according to their member count",
            legend: {position: "none"},
            colors: ['green'],
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_groups_progress'));
        chart.draw(data, options);
    }

    function drawCriticalPath() {

        let data = google.visualization.arrayToDataTable( overview.critical_path );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '25%',
                top: '0%',
                width: "80%",
                height: "90%" },
            vAxis: {
                title: 'steps',
            },
            hAxis: {
                title: 'number of contacts',
            },
            title: "Number of groups according to their member count",
            legend: {position: "none"},
            colors: ['green'],
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_critical_path'));
        chart.draw(data, options);
    }
}

function my_contacts_progress() {
    "use strict";
    let chartDiv = jQuery('#chart')
    let myContacts = dtMetricsPersonal.my_contacts
    chartDiv.empty().html(`
        <span class="section-header">`+ myContacts.translations.title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell">
                <div class="grid-x callout">
                    <div class="medium-3 cell center">
                    <h4>Total Contacts<br><span id="total_contacts"></span></h4>
                    </div>
                    <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>This Month<br><span id="this_month"></span></h4>
                    </div>
                </div>
            </div>
            <div class="cell">
            <div id="contacts_progress" style="height: 500px; margin: 0 1em 1.2em; "></div>
            </div>
        </div>
        `)

    let hero = myContacts.hero_stats
    jQuery('#total_contacts').append( numberWithCommas( hero.total_contacts ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});

    google.charts.setOnLoadCallback(drawMyContactsProgress);

    function drawMyContactsProgress() {

        let data = google.visualization.arrayToDataTable( myContacts.progress );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '25%',
                top: '0%',
                width: "80%",
                height: "90%" },
            vAxis: {
                title: 'steps',
            },
            hAxis: {
                title: 'number of contacts',
            },
            title: "Number of groups according to their member count",
            legend: {position: "none"},
            colors: ['blue'],
        };

        let chart = new google.visualization.BarChart(document.getElementById('contacts_progress'));
        chart.draw(data, options);
    }
}

function my_groups_progress() {
    "use strict";
    let chartDiv = jQuery('#chart')
    let myGroups = dtMetricsPersonal.my_groups
    chartDiv.empty().html(`
        <span class="section-header">`+ myGroups.translations.title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell">
                <div class="grid-x callout">
                    <div class="medium-3 cell center">
                    <h4>Total Groups<br><span id="total_groups"></span></h4>
                    </div>
                    <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>This Month<br><span id="this_month"></span></h4>
                    </div>
                </div>
            </div>
            <div class="cell">
            <div id="my_contacts_progress" style="height: 500px; margin: 0 1em 1.2em; "></div>
            </div>
        </div>
        `)

    let hero = myGroups.hero_stats
    jQuery('#total_groups').append( numberWithCommas( hero.total_groups ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});

    google.charts.setOnLoadCallback(drawMyContactsProgress);

    function drawMyContactsProgress() {

        let data = google.visualization.arrayToDataTable( myGroups.progress );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '25%',
                top: '0%',
                width: "80%",
                height: "90%" },
            vAxis: {
                title: 'steps',
            },
            hAxis: {
                title: 'number of contacts',
            },
            title: "Number of groups according to their member count",
            legend: {position: "none"},
            colors: ['blue'],
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_contacts_progress'));
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