jQuery(document).ready(function() {
    console.log( dtMetricsProject )

    if( ! window.location.hash || '#project' === window.location.hash  ) {
        project()
    }
})

function project() {
    "use strict";
    let chartDiv = jQuery('#chart')
    let overview = dtMetricsProject.project
    chartDiv.empty().html(`
        <span class="section-header">`+ dtMetricsProject.project.translations.title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                <p><span class="section-subheader">Contacts</span></p>
                <div class="grid-x">
                    <div class="medium-3 cell center">
                        <h4>Total Contacts<br><span id="total_contacts">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Need Accepted<br><span id="need_accepted">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Updates Needed<br><span id="updates_needed">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Attempts Needed<br><span id="attempts_needed">0</span></h4>
                    </div>
                </div>
            </div>
            <div class="cell">
                <div id="my_contacts_progress" style="height: 350px;"></div>
            </div>
            <div class="cell">
            <hr>
                <div id="my_critical_path" style="height: 650px;"></div>
            </div>
            <div class="cell">
            <br>
                <div class="cell center callout">
                    <p><span class="section-subheader">Groups</span></p>
                    <div class="grid-x">
                        <div class="medium-3 cell center">
                            <h4>Total Groups<br><span id="total_groups">0</span></h4>
                        </div>
                   </div> 
                </div>
            </div>
            <div class="cell">
                <div class="grid-x">
                    <div class="cell medium-6 center">
                        <span class="section-subheader">Group Types</span>
                        <div id="group_types" style="height: 400px;"></div>
                    </div>
                    <div class="cell medium-6">
                        <div id="group_generations" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
            <div class="cell">
            <hr>
                <div id="my_groups_health" style="height: 500px;"></div>
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

    google.charts.setOnLoadCallback(drawMyContactsProgress);
    google.charts.setOnLoadCallback(drawMyGroupHealth);
    google.charts.setOnLoadCallback(drawCriticalPath);
    google.charts.setOnLoadCallback(drawGroupTypes);
    google.charts.setOnLoadCallback(drawGroupGenerations);

    function drawMyContactsProgress() {

        let data = google.visualization.arrayToDataTable( overview.contacts_progress );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            hAxis: {
                title: 'number of contacts',
            },
            title: "My Follow-Up Progress",
            legend: {position: "none"},
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_contacts_progress'));
        chart.draw(data, options);
    }

    function drawCriticalPath() {

        let data = google.visualization.arrayToDataTable( overview.critical_path );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            title: "My Critical Path",
            legend: {position: "none"},
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_critical_path'));
        chart.draw(data, options);
    }

    function drawMyGroupHealth() {

        let data = google.visualization.arrayToDataTable( overview.group_health );

        let options = {
            chartArea: {
                left: '10%',
                top: '10%',
                width: "85%",
                height: "75%" },
            vAxis: {
                title: 'groups',
            },
            title: "Groups Needing Training Attention",
            legend: {position: "none"},
            colors: ['green' ],
        };

        let chart = new google.visualization.ColumnChart(document.getElementById('my_groups_health'));

        function selectHandler() {
            let selectedItem = chart.getSelection()[0];
            if (selectedItem) {
                let topping = data.getValue(selectedItem.row, 0);
                alert('You selected ' + topping);
            }
        }

        google.visualization.events.addListener(chart, 'select', selectHandler);

        chart.draw(data, options);
    }

    function drawGroupTypes() {
        let data = google.visualization.arrayToDataTable( overview.group_types );

        let options = {
            legend: 'bottom',
            pieSliceText: 'groups',
            pieStartAngle: 135,
            slices: {
                0: { color: 'lightgreen' },
                1: { color: 'limegreen' },
                2: { color: 'darkgreen' },
            },
            pieHole: 0.4,
            chartArea: {
                left: '0%',
                top: '7%',
                width: "100%",
                height: "80%" },
            fontSize: '20',
        };

        let chart = new google.visualization.PieChart(document.getElementById('group_types'));
        chart.draw(data, options);
    }

    function drawGroupGenerations() {

        let data = google.visualization.arrayToDataTable( overview.group_generations );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            title: "Generations",
            legend: { position: 'bottom', maxLines: 3 },
            isStacked: true,
            colors: [ 'lightgreen', 'limegreen', 'darkgreen' ],
        };

        let chart = new google.visualization.BarChart(document.getElementById('group_generations'));
        chart.draw(data, options);
    }
}

function my_contacts_progress() {
    "use strict";
    let chartDiv = jQuery('#chart')
    let myContacts = dtMetricsProject.my_contacts
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
    let myGroups = dtMetricsProject.my_groups
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