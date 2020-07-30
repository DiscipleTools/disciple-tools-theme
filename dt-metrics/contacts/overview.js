jQuery(document).ready(function() {
  if ( window.wpApiShare.url_path.startsWith(  'metrics/contacts/overview' ) ) {
    project_contacts_overview()
  }

  function project_contacts_overview() {
    "use strict";
    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner active"></span> '
    chart.empty().html(spinner)
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#contacts-menu'));

    let sourceData = dtMetricsProject.data
    let translations = dtMetricsProject.data.translations

    chart.empty().html(`
        <div class="cell center">
            <h3>${translations.title_contact_overview}</h3>
        </div>
        <br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                <div class="cell center">
                </div>
                <div class="grid-x">
                    <div class="medium-3 cell center">
                        <h5>${translations.title_waiting_on_accept}<br><span id="needs_accepted">0</span></h5>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h5>${translations.title_waiting_on_update}<br><span id="updates_needed">0</span></h5>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h5>${translations.title_active_contacts}<br><span id="active_contacts">0</span></h5>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h5>${translations.title_all_contacts}<br><span id="all_contacts">0</span></h5>
                    </div>
                </div>
            </div>
            <div class="cell">
                <div id="my_contacts_progress" style="height: 350px; width=100%"></div>
            </div>
            <div class="cell">
                <div id="status_chart_div" style="height: 500px; width=100%; margin:0 auto;"></div>
            </div>
        </div>
        `)

    let hero = sourceData.hero_stats
    jQuery('#active_contacts').html(numberWithCommas(hero.active_contacts))
    jQuery('#needs_accepted').html(numberWithCommas(hero.needs_accepted))
    jQuery('#updates_needed').html(numberWithCommas(hero.updates_needed))
    jQuery('#all_contacts').html(numberWithCommas(hero.total_contacts))

    // build charts
    drawMyContactsProgress();
    draw_status_pie_chart()

    function drawMyContactsProgress() {
      let chart = am4core.create("my_contacts_progress", am4charts.XYChart)
      let title = chart.titles.create()
      title.text = `[bold]${window.dtMetricsProject.data.translations.label_follow_up_progress}[/]`
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

    function draw_status_pie_chart() {
      let contact_statuses = sourceData.contact_statuses
      am4core.useTheme(am4themes_animated);

      let container = am4core.create("status_chart_div", am4core.Container);
      container.width = am4core.percent(100);
      container.height = am4core.percent(100);
      container.layout = "horizontal";


      let chart = container.createChild(am4charts.PieChart);
      let title = chart.titles.create()
      title.text = `[bold]${window.dtMetricsProject.data.translations.title_status_chart}[/]`
      // Add data
      chart.data = contact_statuses

      // Add and configure Series
      let pieSeries = chart.series.push(new am4charts.PieSeries());
      pieSeries.dataFields.value = "count";
      pieSeries.dataFields.category = "status";
      pieSeries.slices.template.states.getKey("active").properties.shiftRadius = 0;
      pieSeries.labels.template.text = "{category}: {value.percent.formatNumber('#.#')}% ({value}) ";

      pieSeries.slices.template.events.on("hit", function (event) {
        selectSlice(event.target.dataItem);
      })

      let chart2 = container.createChild(am4charts.PieChart);
      chart2.width = am4core.percent(30);
      chart2.radius = am4core.percent(80);

      // Add and configure Series
      let pieSeries2 = chart2.series.push(new am4charts.PieSeries());
      pieSeries2.dataFields.value = "count";
      pieSeries2.dataFields.category = "reason";
      pieSeries2.slices.template.states.getKey("active").properties.shiftRadius = 0;
      pieSeries2.labels.template.disabled = true;
      pieSeries2.ticks.template.disabled = true;
      pieSeries2.alignLabels = false;
      pieSeries2.events.on("positionchanged", updateLines);

      let interfaceColors = new am4core.InterfaceColorSet();

      let line1 = container.createChild(am4core.Line);
      line1.strokeDasharray = "2,2";
      line1.strokeOpacity = 0.5;
      line1.stroke = interfaceColors.getFor("alternativeBackground");
      line1.isMeasured = false;

      let line2 = container.createChild(am4core.Line);
      line2.strokeDasharray = "2,2";
      line2.strokeOpacity = 0.5;
      line2.stroke = interfaceColors.getFor("alternativeBackground");
      line2.isMeasured = false;

      let selectedSlice;

      function selectSlice(dataItem) {
        selectedSlice = dataItem.slice;
        let fill = selectedSlice.fill;
        let count = dataItem.dataContext.reasons.length;
        pieSeries2.colors.list = [];
        for (let i = 0; i < count; i++) {
          pieSeries2.colors.list.push(fill.brighten(i * 2 / count));
        }
        chart2.data = dataItem.dataContext.reasons;
        pieSeries2.appear();

        let middleAngle = selectedSlice.middleAngle;
        let firstAngle = pieSeries.slices.getIndex(0).startAngle;
        let animation = pieSeries.animate([{
          property: "startAngle",
          to: firstAngle - middleAngle
        }, {property: "endAngle", to: firstAngle - middleAngle + 360}], 600, am4core.ease.sinOut);
        animation.events.on("animationprogress", updateLines);

        selectedSlice.events.on("transformed", updateLines);

      }

      function updateLines() {
        if (selectedSlice) {
          let p11 = {
            x: selectedSlice.radius * am4core.math.cos(selectedSlice.startAngle),
            y: selectedSlice.radius * am4core.math.sin(selectedSlice.startAngle)
          };
          let p12 = {
            x: selectedSlice.radius * am4core.math.cos(selectedSlice.startAngle + selectedSlice.arc),
            y: selectedSlice.radius * am4core.math.sin(selectedSlice.startAngle + selectedSlice.arc)
          };

          p11 = am4core.utils.spritePointToSvg(p11, selectedSlice);
          p12 = am4core.utils.spritePointToSvg(p12, selectedSlice);

          let p21 = {x: 0, y: -pieSeries2.pixelRadius};
          let p22 = {x: 0, y: pieSeries2.pixelRadius};

          p21 = am4core.utils.spritePointToSvg(p21, pieSeries2);
          p22 = am4core.utils.spritePointToSvg(p22, pieSeries2);

          line1.x1 = p11.x;
          line1.x2 = p21.x;
          line1.y1 = p11.y;
          line1.y2 = p21.y;

          line2.x1 = p12.x;
          line2.x2 = p22.x;
          line2.y1 = p12.y;
          line2.y2 = p22.y;
        }
      }

    }

    new Foundation.Reveal(jQuery('#dt-project-legend'));
  }
})

function numberWithCommas(x) {
  x = (x || 0).toString();
  let pattern = /(-?\d+)(\d{3})/;
  while (pattern.test(x))
    x = x.replace(pattern, "$1,$2");
  return x;
}
