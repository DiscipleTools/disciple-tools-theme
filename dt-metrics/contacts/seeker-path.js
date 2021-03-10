jQuery(document).ready(function() {

  jQuery('#metrics-sidemenu').foundation('down', jQuery('#contacts-menu'));

  let chartDiv = jQuery('#chart')
  let sourceData = wp_js_object.data

  chartDiv.empty().html(`
    <div class="section-header">${window.lodash.escape(window.wp_js_object.translations.seeker_path) }</div>
    <div class="section-subheader">${ window.lodash.escape(window.wp_js_object.translations.filter_contacts_to_date_range) }</div>
    <div class="date_range_picker">
        <i class="fi-calendar"></i>&nbsp;
        <span>${ window.lodash.escape(window.wp_js_object.translations.all_time) }</span> 
        <i class="dt_caret down"></i>
    </div>
    <div style="display: inline-block" class="loading-spinner"></div>
    <hr>
    <div id="chartdiv" style="height: 400px"></div>
  `)

  let chart = am4core.create("chartdiv", am4charts.XYChart);

  chart.data = sourceData.seeker_path
  let categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
  categoryAxis.dataFields.category = "seeker_path";
  categoryAxis.renderer.grid.template.location = 0;
  categoryAxis.renderer.minGridDistance = 30;
  categoryAxis.renderer.labels.template.adapter.add("dy", function(dy, target) {
    if (target.dataItem && target.dataItem.index & 2 == 2) {
      return dy + 25;
    }
    return dy;
  });

  let valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

  // Create series
  let series = chart.series.push(new am4charts.ColumnSeries());
  series.dataFields.valueY = "value";
  series.dataFields.categoryX = "seeker_path";
  series.name = "Visits";
  series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
  series.columns.template.fillOpacity = .8;

  let columnTemplate = series.columns.template;
  columnTemplate.strokeWidth = 2;
  columnTemplate.strokeOpacity = 1;


  window.METRICS.setupDatePicker(
    `${wp_js_object.rest_endpoints_base}/seeker_path/`,
    function (data, label, start, end) {
      if ( data ){
        $('.date_range_picker span').html( label );
        chart.data = data
      }
    }
  )
})
