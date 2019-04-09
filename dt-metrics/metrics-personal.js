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

    chartDiv.empty().html(`
        <div class="cell center">
            <h3>${ __( 'My Overview', 'disciple_tools' ) }</h3>
        </div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
        <h3 class="section-header">${ __( 'Contacts', 'disciple_tools' ) }</h3>
            <div class="cell center callout">
                <div class="grid-x">
                    <div class="medium-4 cell center ">
                        <h5>${ __( 'Waiting on Accept', 'disciple_tools' ) }<br><span id="needs_accepted">0</span></h5>
                    </div>
                    <div class="medium-4 cell center left-border-grey">
                        <h5>${ __( 'Waiting on Update', 'disciple_tools' ) }<br><span id="updates_needed">0</span></h5>
                    </div>
                    <div class="medium-4 cell center left-border-grey">
                        <h5>${ __( 'Active Contacts', 'disciple_tools' ) }<br><span id="contacts">0</span></h5>
                    </div>
                </div>
            </div>
            <div class="cell">
                <div id="my_contacts_progress" style="height: 350px; width=100%"></div>
            </div>
            <h3 class="section-header" style="margin-top:40px;">${ __( 'Groups', 'disciple_tools' ) }</h3>
            <div class="cell">
                <div class="cell center callout">
                    <div class="grid-x">
                        <div class="medium-4 cell center">
                            <h5>${ __( 'Total Groups', 'disciple_tools' ) }<br><span id="total_groups">0</span></h5>
                        </div>
                        <div class="medium-4 cell center left-border-grey">
                            <h5>${ __( 'Lead Teams', 'disciple_tools' ) }<br><span id="teams">0</span></h5>
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
                        <span class="section-subheader">${ __( 'Group Types', 'disciple_tools' ) }</span>
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
    // jQuery('#needs_training').html( numberWithCommas( hero.needs_training ) )
    // jQuery('#fully_practicing').html( numberWithCommas( hero.fully_practicing ) )
    jQuery('#teams').html( numberWithCommas( hero.teams ) )



    // build charts

    drawMyContactsProgress()
    drawMyGroupHealth();
    drawGroupTypes();
    drawGroupGenerations();

    function drawMyContactsProgress() {
      let chart = am4core.create("my_contacts_progress", am4charts.XYChart)
      let title = chart.titles.create()
      title.text = `[bold]${ __( 'Follow-up of my active contacts', 'disciple_tools' ) }[/]`
      chart.data = sourceData.contacts_progress.reverse()

      let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "label";
      categoryAxis.renderer.grid.template.location = 0;
      categoryAxis.renderer.minGridDistance = 30;

      let valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
      valueAxis.title.text = "Number of contacts"

      let series = chart.series.push(new am4charts.ColumnSeries());
      series.dataFields.valueX = "value";
      series.dataFields.categoryY = "label";
      series.columns.template.tooltipText = "Total: [bold]{valueX}[/]";

      // field value label
      let valueLabel = series.bullets.push(new am4charts.LabelBullet());
      valueLabel.label.text = "{valueX}";
      valueLabel.label.horizontalCenter = "left";
      valueLabel.label.dx = 10;
      valueLabel.label.hideOversized = false;
      valueLabel.label.truncate = false;

    }

    function drawMyGroupHealth() {
      let chart = am4core.create("my_groups_health", am4charts.XYChart);
      chart.data = sourceData.group_health
      let title = chart.titles.create()
      title.text = `[bold]${__( 'Active Group Health Metrics', 'disciple_tools' )}[/]`
      let categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "label";
      categoryAxis.renderer.grid.template.location = 0;

      let valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
      valueAxis.min = 0;
      valueAxis.max = 100;
      valueAxis.strictMinMax = true;
      valueAxis.calculateTotals = true;
      valueAxis.renderer.minWidth = 50;
      valueAxis.renderer.labels.template.adapter.add("text", function(text) {
        return text + "%";
      });

      let series1 = chart.series.push(new am4charts.ColumnSeries());
      series1.columns.template.width = am4core.percent(80);
      series1.columns.template.tooltipText = "{name}: {valueY}";
      series1.name = "Practicing";
      series1.dataFields.categoryX = "label";
      series1.dataFields.valueY = "practicing";
      series1.dataFields.valueYShow = "totalPercent";
      series1.dataItems.template.locations.categoryX = 0.5;
      series1.stacked = true;
      series1.tooltip.pointerOrientation = "vertical";
      
      let series2 = chart.series.push(new am4charts.ColumnSeries());
      series2.stroke = am4core.color("#da7070"); // red
      series2.fill = am4core.color("#da7070"); // red
      series2.columns.template.width = am4core.percent(80);
      series2.columns.template.tooltipText =
        "{name}: {valueY}";
      series2.name = "Not Practicing";
      series2.dataFields.categoryX = "label";
      series2.dataFields.valueY = "remaining";
      series2.dataFields.valueYShow = "totalPercent";
      series2.dataItems.template.locations.categoryX = 0.5;
      series2.stacked = true;
      series2.tooltip.pointerOrientation = "vertical";
      chart.legend = new am4charts.Legend();

    }

    function drawGroupTypes() {
      let chart = am4core.create("group_types", am4charts.PieChart);
      let title = chart.titles.create()
      title.text = `[bold]${__( 'Group Types', 'disciple_tools' )}[/]`
      chart.data = sourceData.group_types
      let pieSeries = chart.series.push(new am4charts.PieSeries());
      pieSeries.dataFields.value = "count";
      pieSeries.dataFields.category = "label";

      chart.innerRadius = am4core.percent(30);
    }

    function drawGroupGenerations() {
      let chart = am4core.create("group_generations", am4charts.XYChart);
      let title = chart.titles.create()
      title.text = `[bold]${ __( 'Group and Church Generations', 'disciple_tools' ) }[/]`

      chart.data = sourceData.group_generations.reverse()

      let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "generation";
      categoryAxis.renderer.grid.template.location = 0;
      categoryAxis.renderer.labels.template.adapter.add("text", function(text) {
        return __( "Generation", "disciple_tools" ) + ' ' + text;
      });

      let valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
      valueAxis.renderer.inside = true;
      valueAxis.renderer.labels.template.disabled = true;
      valueAxis.min = 0;

      function createSeries(field, name) {
        let series = chart.series.push(new am4charts.ColumnSeries());
        series.name = name;
        series.dataFields.valueX = field;
        series.dataFields.categoryY = "generation";
        series.stacked = true;
        series.columns.template.width = am4core.percent(60);
        series.columns.template.tooltipText = "[bold]{name}[/]\n {valueX}";
        let labelBullet = series.bullets.push(new am4charts.LabelBullet());
        labelBullet.label.text = "{valueX}";
        labelBullet.locationX = 0.5;
        return series;
      }

      createSeries("pre-group", __( 'Pre-Group', 'disciple_tools' ) );
      createSeries("group", __( 'Group', 'disciple_tools' ) );
      createSeries("church", __( 'Church', 'disciple_tools' ) );
      chart.legend = new am4charts.Legend();
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
