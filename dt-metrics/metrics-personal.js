jQuery(document).ready(function() {
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));

    if( ! window.location.hash || '#my_stats' === window.location.hash  ) {
        my_stats()
    }
})

function my_stats() {
    "use strict";
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsPersonal.data
    let translations = dtMetricsPersonal.data.translations

    chartDiv.empty().html(`
        <div class="cell center">
            <h3 >${ translations.title }</h3>
        </div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
        <h3 class="section-header">${ translations.title_contacts }</h3>
            <div class="cell center callout">
                <div class="grid-x">
                    <div class="medium-4 cell center ">
                        <h5>${ translations.title_waiting_on_accept }<br><span id="needs_accepted">0</span></h5>
                    </div>
                    <div class="medium-4 cell center left-border-grey">
                        <h5>${ translations.title_waiting_on_update }<br><span id="updates_needed">0</span></h5>
                    </div>
                    <div class="medium-4 cell center left-border-grey">
                        <h5>${ translations.label_active_contacts }<br><span id="contacts">0</span></h5>
                    </div>
                </div>
            </div>
            <div class="cell">
                <div id="my_contacts_progress" style="height: 350px; width=100%"></div>
            </div>
            <h3 class="section-header" style="margin-top:40px;">${ translations.title_groups }</h3>
            <div class="cell">
                <div class="cell center callout">
                    <div class="grid-x">
                        <div class="medium-4 cell center">
                            <h5>${ translations.title_total_groups }<br><span id="total_groups">0</span></h5>
                        </div>
                        <div class="medium-4 cell center left-border-grey">
                            <h5>${ translations.title_needs_training }<br><span id="needs_training">0</span></h5>
                        </div>
                        <div class="medium-4 cell center left-border-grey">
                            <h5>${ translations.title_fully_practicing }<br><span id="fully_practicing">0</span></h5>
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
                        <span class="section-subheader">${ translations.title_group_types }</span>
                        <div id="group_types" style="height: 400px;"></div>
                    </div>
                    <div class="cell medium-6">
                        <div id="group_generations" style="height: 400px;"></div>
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
    jQuery('#fully_practicing').html( numberWithCommas( hero.fully_practicing ) )

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
                title: translations.label_number_of_contacts,
            },
            title: translations.label_my_follow_up_progress,
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
                format: 'decimal',
            },
            hAxis: {
                format: 'decimal',
            },
            title: translations.label_group_needing_training,
          legend: {position: "bottom"},
          isStacked: true
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
            pieSliceText: 'label',
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
        };

        let chart = new google.visualization.PieChart(document.getElementById('group_types'));
        chart.draw(data, options);
    }

    function drawGroupGenerations() {

      let formattedData = [sourceData.group_generations[0]]
      sourceData.group_generations.forEach( (row, index)=>{
        if ( index !== 0 ){
          formattedData.push( [row["generation"], row["pre-group"], row["group"], row["church"], ''] )
        }
      })
      let data = google.visualization.arrayToDataTable( formattedData );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%", },
            title: translations.title_generations,
            vAxis: {
                format: '0'
            },
            hAxis: {
                title: translations.label_groups_by_type,
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

}

function numberWithCommas(x) {
    x = x.toString();
    let pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(x))
        x = x.replace(pattern, "$1,$2");
    return x;
}
