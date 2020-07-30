const chart_label_width = 230
const chart_row_height = 25
const chart_min_height = 84

jQuery(document).ready(function() {
  if ( window.wpApiShare.url_path.startsWith( 'metrics/combined/critical_path' ) ) {
    project_critical_path()
  }
})


function numberWithCommas(x) {
  x = (x || 0).toString();
  let pattern = /(-?\d+)(\d{3})/;
  while (pattern.test(x))
    x = x.replace(pattern, "$1,$2");
  return x;
}

function project_critical_path() {
  let chartDiv = jQuery('#chart')
  let translations = dtMetricsProject.translations

  jQuery('#metrics-sidemenu').foundation('down', jQuery('#combined-menu'));

  chartDiv.empty().html(`
    <div class="section-header">${ translations.title_critical_path }</div>
    <div class="date_range_picker">
        <i class="fi-calendar"></i>&nbsp;
        <span>${moment().format("YYYY")}</span>
        <i class="dt_caret down"></i>
    </div>
    <div style="display: inline-block" class="loading-spinner"></div>
    <hr>
    <div id="mediachart" style="width:90%;"></div>
    <div id="activityChart" style=" width:90%;"></div>
    <div id="ongoingChart" style="width:90%;"></div>
    <!--<div id="chartdiv" style="height: 800px; width:100%;"></div>-->
    <br>
    <h4>${ translations.filter_critical_path }</h4>
    <div id="field_selector" style="display: flex; flex-wrap: wrap"> </div>
  `)

  fieldSelector( dtMetricsProject.data.cp )

  window.METRICS.setupDatePicker(
    `${dtMetricsProject.root}dt/v1/metrics/critical_path_activity`,
    function (data, label) {
      if (data) {
        $('.date_range_picker span').html(label);
        dtMetricsProject.data.cp = data
        fieldSelector( dtMetricsProject.data.cp )
        mediaChart(data)
        activityChart(data)
        ongoingChart(data)
        // main_chart(data)
      }
    },
    moment().startOf('year')
  )
  buildCharts( dtMetricsProject.data.cp )
}

let buildCharts = function( data ){
  mediaChart(data)
  activityChart(data)
  ongoingChart(data)
  // main_chart(data)
}

let main_chart = function(data){


  data = data.filter(a=>a.outreach === undefined).reverse()

  // Create chart instance
  $('#chartdiv').empty().height(50 + chart_row_height * data.length)
  let chart = am4core.create("chartdiv", am4charts.XYChart);

  chart.data = data

  chart.legend = new am4charts.Legend();
  chart.legend.useDefaultMarker = true;

  // Create axes
  let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
  categoryAxis.dataFields.category = "label";
  categoryAxis.renderer.grid.template.location = 0;
  categoryAxis.renderer.minGridDistance = 30;
  categoryAxis.renderer.maxGridDistance = 30;
  let valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
  // valueAxis.title.text = "Critical Path";
  // valueAxis.title.fontWeight = 800;
  // valueAxis.renderer.opposite = true;
  // valueAxis.min = 1
  // console.log(max);
  // valueAxis.max = max.value * 1.1
  // console.log(valueAxis.max);


  // valueAxis.logarithmic = true;

  // Create series
  let series = chart.series.push(new am4charts.ColumnSeries());
  series.name = "Current System counts"
  series.dataFields.valueX = "total";
  series.dataFields.categoryY = "label";
  series.clustered = false;
  series.tooltipText = "Total: [bold]{valueX}[/]";

  // var valueLabel = series.bullets.push(new am4charts.LabelBullet());
  // valueLabel.label.text = "{valueX}";
  // valueLabel.label.horizontalCenter = "left";
  // valueLabel.label.dx = 10;
  // valueLabel.label.hideOversized = false;
  // valueLabel.label.truncate = false;

  let series2 = chart.series.push(new am4charts.ColumnSeries());
  series2.name = "Activity"
  series2.dataFields.valueX = "value";
  series2.dataFields.test = "value";
  series2.dataFields.categoryY = "label";
  series2.clustered = false;
  series2.columns.template.height = am4core.percent(50);
  series2.tooltipText = "[bold]{test}[/]";

  let valueLabel = series2.bullets.push(new am4charts.LabelBullet());
  valueLabel.label.text = "{valueX}";
  valueLabel.label.horizontalCenter = "left";
  valueLabel.label.dx = 10;
  valueLabel.label.hideOversized = false;
  valueLabel.label.truncate = false;


  chart.cursor = new am4charts.XYCursor();
  chart.cursor.lineX.disabled = true;
  chart.cursor.lineY.disabled = true;


  let label = categoryAxis.renderer.labels.template;
  // label.wrap = true;
  label.truncate = true
  label.maxWidth = chart_label_width;
  label.minWidth = chart_label_width;
  label.tooltipText = "{category}";
}


let setupChart = function ( chart, valueX, titleText){

  let title = chart.titles.create();
  title.text = `[bold]${titleText}[/]`;
  title.textAlign = "middle";
  title.dy = -5

  let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
  categoryAxis.dataFields.category = "label";
  categoryAxis.renderer.grid.template.location = 0;
  categoryAxis.renderer.minGridDistance = 10;

  let label = categoryAxis.renderer.labels.template;
  label.truncate = true
  label.maxWidth = chart_label_width;
  label.minWidth = chart_label_width;
  label.tooltipText = "{description}";
  label.textAlign = "end"
  label.dx = -5

  let valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
  valueAxis.title.fontWeight = 800;
  valueAxis.renderer.grid.template.disabled = true
  valueAxis.extraMax = 0.1;
  valueAxis.min = 0
  valueAxis.paddingRight = 20;

  let series = chart.series.push(new am4charts.ColumnSeries());
  series.name = "Activity"
  series.dataFields.valueX = valueX;
  series.dataFields.categoryY = "label";
  series.clustered = false;
  series.tooltipText = "[bold]{valueX}[/]";
  series.columns.template.height = 20

  //field value label
  let valueLabel = series.bullets.push(new am4charts.LabelBullet());
  valueLabel.label.text = "{valueX}";
  valueLabel.label.horizontalCenter = "left";
  valueLabel.label.dx = 10;
  valueLabel.label.hideOversized = false;
  valueLabel.label.truncate = false;

  chart.cursor = new am4charts.XYCursor();
  chart.cursor.lineX.disabled = true;
  chart.cursor.lineY.disabled = true;
}

let mediaChart = function ( data ) {
  data = data.filter(a=>a.outreach).reverse()
  $('#mediachart').empty().height(chart_min_height + chart_row_height * data.length)
  if ( data.length ) {
    let chart = am4core.create("mediachart", am4charts.XYChart);
    chart.data = data
    setupChart( chart, "outreach", dtMetricsProject.translations.title_outreach )
  }
}

let activityChart = function ( data ) {
  data = data.filter(a=>a.type==="activity").reverse()
  $('#activityChart').empty().height(chart_min_height+chart_row_height * data.length)
  if ( data.length ) {
    let chart = am4core.create("activityChart", am4charts.XYChart);
    chart.data = data
    setupChart( chart, "value", dtMetricsProject.translations.title_follow_up )
  }
}
let ongoingChart = function ( data ) {
  data = data.filter(a=>a.type==="ongoing").reverse()
  $('#ongoingChart').empty().height(chart_min_height+chart_row_height * data.length)
  if ( data.length ) {
    let chart = am4core.create("ongoingChart", am4charts.XYChart);
    chart.data = data
    setupChart( chart, "total", dtMetricsProject.translations.movement_training )
  }
}

let filtered = []
let fieldSelector = function( data ){
  let html = ``
  data.forEach(field=>{
    let checked = !filtered.includes(field.key) ? "checked" : ""
    html += `<label style="flex-grow: 0;flex-basis:20%">
      <input type="checkbox" class="field-button" data-key="${field.key}" ${checked}>
      ${field.label}
    </label>
    `
  })
  $('#field_selector').html(html)

  $('.field-button').on("click", function () {
    let key = $(this).data("key")
    if ( $(this).is(":checked") ){
      filtered = filtered.filter(a=>a!==key)
    } else {
      filtered.push(key)
    }
    buildCharts( dtMetricsProject.data.cp.filter(a=>!filtered.includes(a.key)) )
  })

}
