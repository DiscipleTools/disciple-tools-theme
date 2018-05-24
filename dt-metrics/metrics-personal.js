jQuery(document).ready(function() {
    console.log( dtMetricsPersonal )

    if( ! window.location.hash || '#my_stats' === window.location.hash  ) {
        my_stats()
    }
})

function my_stats() {
    "use strict";
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsPersonal.data
    let label = dtMetricsPersonal.data.translations

    chartDiv.empty().html(`
        <span class="section-header">`+ label.title +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" class="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                <p><span class="section-subheader">`+ label.title_contacts +`</span></p>
                <div class="grid-x">
                    <div class="medium-4 cell center">
                        <h4>`+ label.label_total_contacts +`<br><span id="contacts">0</span></h4>
                    </div>
                    <div class="medium-4 cell center left-border-grey">
                        <h4>`+ label.title_waiting_on_accept +`<br><span id="needs_accepted">0</span></h4>
                    </div>
                    <div class="medium-4 cell center left-border-grey">
                        <h4>`+ label.title_waiting_on_update +`<br><span id="updates_needed">0</span></h4>
                    </div>
                </div>
            </div>
            <div class="cell">
                <div id="my_contacts_progress" style="height: 350px;"></div>
            </div>
            <div class="cell">
            <br>
                <div class="cell center callout">
                    <p><span class="section-subheader">`+ label.title_groups +`</span></p>
                    <div class="grid-x">
                        <div class="medium-4 cell center">
                            <h4>`+ label.title_total_groups +`<br><span id="total_groups">0</span></h4>
                        </div>
                        <div class="medium-4 cell center left-border-grey">
                            <h4>`+ label.title_needs_training +`<br><span id="needs_training">0</span></h4>
                        </div>
                   </div> 
                </div>
            </div>
            <div class="cell">
                <div id="my_groups_health" style="height: 500px;"></div>
            </div>
            <div class="cell">
            <hr>
                <div class="grid-x">
                    <div class="cell medium-6 center">
                        <span class="section-subheader">`+ label.title_group_types +`</span>
                        <div id="group_types" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
            
        </div>
        `)

    let hero = sourceData.hero_stats
    jQuery('#contacts').html( numberWithCommas( hero.contacts ) )
    jQuery('#needs_accepted').html( numberWithCommas( hero.needs_accept ) )
    jQuery('#updates_needed').html( numberWithCommas( hero.needs_update ) )

    jQuery('#total_groups').html( numberWithCommas( hero.groups ) )
    jQuery('#needs_training').html( numberWithCommas( hero.needs_training ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});

    google.charts.setOnLoadCallback(drawMyContactsProgress);
    google.charts.setOnLoadCallback(drawMyGroupHealth);
    google.charts.setOnLoadCallback(drawGroupTypes);
    google.charts.setOnLoadCallback(drawGroupGenerations);

    function drawMyContactsProgress() {

        let data = google.visualization.arrayToDataTable( sourceData.contacts_progress );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            hAxis: {
                title: label.label_number_of_contacts,
            },
            title: "My Follow-Up Progress",
            legend: {position: "none"},
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_contacts_progress'));
        chart.draw(data, options);
    }

    function drawMyGroupHealth() {

        let data = google.visualization.arrayToDataTable( sourceData.group_health );

        let options = {
            chartArea: {
                left: '10%',
                top: '10%',
                width: "85%",
                height: "75%" },
            vAxis: {
                title: 'groups',
                format: 'decimal',
            },
            hAxis: {
                format: 'decimal',
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
        let data = google.visualization.arrayToDataTable( sourceData.group_types );

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

        let data = google.visualization.arrayToDataTable( sourceData.group_generations );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            title: "Generations",
            vAxis: {
                title: 'generations',
                format: '0'
            },
            hAxis: {
                format: '0'
            },
            legend: { position: 'bottom', maxLines: 3 },
            isStacked: true,
            colors: [ 'lightgreen', 'limegreen', 'darkgreen' ],
        };

        let chart = new google.visualization.BarChart(document.getElementById('group_generations'));
        chart.draw(data, options);
    }

    new Foundation.Reveal(jQuery('.dt-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( stats as of  )</span> 
            <a onclick="refresh_stats_data( 'show_zume_groups' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+dtMetricsPersonal.theme_uri+`/dt-assets/images/ajax-loader.gif" /></span> 
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

function numberWithCommas(x) {
    x = x.toString();
    let pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(x))
        x = x.replace(pattern, "$1,$2");
    return x;
}