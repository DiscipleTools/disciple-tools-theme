const { __, _x, _n, _nx } = wp.i18n;
jQuery(document).ready(function() {
  if( '#project_critical_path' === window.location.hash  ) {
    project_critical_path()
  }if( '#project_critical_path2' === window.location.hash  ) {
    project_critical_path2()
  }

  jQuery('#metrics-sidemenu').foundation('down', jQuery('#path-menu'));


})

function project_critical_path() {
  "use strict";
  let chartDiv = jQuery('#chart')
  let sourceData = dtMetricsProject.data
  let translations = dtMetricsProject.data.translations

  let height = $(window).height()
  let chartHeight = height - ( height * .15 ) + 'px;'

  chartDiv.empty().html(`
        <span class="section-header">${translations.title_critical_path}</span>
        <div style="width:fit-content">
        ${translations.label_select_year} 
        <select id="year_select" onchange="change_critical_path_year($(this).val())">
            ${year_list()}
        </select>
        </div>
        
        <br clear="all">
        <div class="grid-x grid-padding-x">
            <div class="cell">
                <div id="dashboard_div">
                    <div id="my_critical_path" style="min-height: 700px; height:${chartHeight}"></div>
                    <hr>
                    <div id="filter_div"></div>
                </div>
            </div>
        </div>
    `)


  // build charts
  google.charts.load('current', {'packages':['corechart', 'bar', 'controls']});
  google.charts.setOnLoadCallback(drawCriticalPath);

  new Foundation.Reveal(jQuery('.dt-project-legend'));
}

function drawCriticalPath( cp_data ) {
  let sourceData = dtMetricsProject.data

  let translations = dtMetricsProject.data.translations
  let path_data = []

  if ( cp_data ) {
    path_data = cp_data
  } else {
    path_data = sourceData.critical_path
  }
  let formattedData = [ [ 'Step', 'Contacts', {role: 'annotation'} ]]
  path_data.forEach(row=>{
    formattedData.push( [row.label, parseInt(row.value), row.value] );

  })
  let data = google.visualization.arrayToDataTable( formattedData );
  let dashboard = new google.visualization.Dashboard(
    document.getElementById('dashboard_div')
  );

  let barChart = new google.visualization.ChartWrapper({
    'chartType': 'BarChart',
    'containerId': 'my_critical_path',
    'options': {
      bars: 'horizontal',
      chartArea: {
        left: '20%',
        top: '7%',
        width: "75%",
        height: "85%" },
      hAxis: { scaleType: 'mirrorLog' },
      title: translations.title_critical_path,
      legend: { position: "none"},
      animation:{
        duration: 400,
        easing: 'out',
      },
    }
  });

  let crit_keys = []
  jQuery.each( path_data, function( index, value ) {
    crit_keys.push( value["label"] )
  })

  let categoryFilter = new google.visualization.ControlWrapper({
    'controlType': 'CategoryFilter',
    'containerId': 'filter_div',
    'options': {
      'filterColumnLabel': 'Step'
    },
    'ui': {
      'allowMultiple': true,
      'caption': "Select Path Step...",
    },
    'state': { 'selectedValues': crit_keys },

  });

  dashboard.bind(categoryFilter, barChart);

  dashboard.draw( data )
}

function year_list() {
  // create array with descending dates
  let i = 0
  let fullDate = new Date()
  let date = fullDate.getFullYear()
  let currentYear = fullDate.getFullYear()
  let options = `<option value="all">${dtMetricsProject.data.translations.label_all_time}</option>`
  while (i < 15) {
    options += `<option value="${date}" ${ date === currentYear && 'selected'}>${date}</option>`;
    i++;
    date--;
  }

  return options
}

function change_critical_path_year( year ) {
  jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: dtMetricsProject.root + 'dt/v1/metrics/critical_path_by_year/'+year,
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
    },
  })
    .done(function (data) {
      if ( data ) {
        drawCriticalPath( data )
      }
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}

function numberWithCommas(x) {
  x = (x || 0).toString();
  let pattern = /(-?\d+)(\d{3})/;
  while (pattern.test(x))
    x = x.replace(pattern, "$1,$2");
  return x;
}

function project_critical_path2() {
  let chartDiv = jQuery('#chart')
  let sourceData = dtMetricsProject.data
  let translations = dtMetricsProject.data.translations


  chartDiv.empty().html(`
    <div class="section-header">${ __( 'Critical Path', 'disciple_tools' ) }</div>
    <div class="date_range_picker">
        <i class="fi-calendar"></i>&nbsp;
        <span>${ __( 'All time', 'disciple_tools' ) }</span> 
        <i class="dt_caret down"></i>
    </div>
    <div style="display: inline-block" class="loading-spinner"></div>
    <hr>
    <div id="chartdiv" style="height: 800px; width:100%"></div>
    <br>
    <!--<div id="chartdiv2" style="height: 600px; width:100%"></div>-->
  `)

  // @todo implement endpoint
  window.METRICS.setupDatePicker(
    `${dtMetricsProject.root}dt/v1/metrics/critical_path/`,
    function (data, label) {
      if (data) {
        $('.date_range_picker span').html( label );
        // chart.data = data
      }
    }
  )

  // Create chart instance
  let chart = am4core.create("chartdiv", am4charts.XYChart);

  //@todo move to endpoint
  chart.data = [
    // {
    //   "country": "Contacts",
    //   "ensemble": 3000,
    //   "activity": 3027
    // },
  {
      "country": "Assigned",
      "ensemble": 10,
      "activity": 155
  }, {
    "country": "Active Contacts",
    "ensemble": 652,
    "activity": 149
  }, {
      "country": "Contact Attempted Needed",
      "ensemble": 111,
      "activity": 149
  }, {
      "country": "Contact Attempted",
      "ensemble": 9,
      "activity": 107,
      "needed": 111
  }, {
      "country": "Contact establish",
      "ensemble": 63,
      "activity": 142,
      "needed": 9
  }, {
      "country": "Meeting scheduled",
      "ensemble": 24,
      "activity": 124,
      "needed": 63
  }, {
      "country": "1st meeting Complete",
      "ensemble": 251,
      "activity": 159,
      "needed": 24
  }, {
      "country": "Ongoing",
      "ensemble": 157,
      "activity": 50,
      "needed": 251
  }, {
      "country": "Coaching",
      "ensemble": 37,
      "activity": 13,
      "needed": 157
  }, {
      "country": "Baptized",
      "ensemble": 213,
      "activity": 23,
  }, {
      "country": "Baptism Gen 1",
      "ensemble": 163,
      "activity": 22,
  }, {
      "country": "Baptism Gen 2",
      "ensemble": 88,
      "activity": 1,
  }, {
      "country": "Baptism Gen 3",
      "ensemble": 10,
      // "activity": ,
  }, {
      "country": "Baptizers",
      "ensemble": 92,
      "activity": 14,
  }
  ].reverse();
  chart.legend = new am4charts.Legend();
  chart.legend.useDefaultMarker = true;

  // Create axes
  let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
  categoryAxis.dataFields.category = "country";
  categoryAxis.renderer.grid.template.location = 0;
  categoryAxis.renderer.minGridDistance = 30;
  // categoryAxis.renderer.inversed = true;
  categoryAxis.renderer.grid.template.location = 0;
  // categoryAxis.renderer.cellStartLocation = 0.1;
  // categoryAxis.renderer.cellEndLocation = 0.9;

  let valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
  valueAxis.title.text = "Critical Path";
  valueAxis.title.fontWeight = 800;
  // valueAxis.renderer.opposite = true;

  // valueAxis.logarithmic = true;

  // Create series
  let series = chart.series.push(new am4charts.ColumnSeries());
  series.name = "Current System counts"
  series.dataFields.valueX = "ensemble";
  series.dataFields.categoryY = "country";
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
  series2.dataFields.valueX = "activity";
  series2.dataFields.categoryY = "country";
  series2.clustered = false;
  series2.columns.template.height = am4core.percent(50);
  series2.tooltipText = "New: [bold]{valueX}[/]";
  //
  // let series3 = chart.series.push(new am4charts.ColumnSeries());
  // series3.name = "Needed"
  // series3.dataFields.valueX = "needed";
  // series3.dataFields.categoryY = "country";
  // series3.clustered = false;
  // series3.columns.template.height = am4core.percent(50);
  // series3.tooltipText = "New: [bold]{valueX}[/]";

  // function createSeries(field, name) {
  //   var series = chart.series.push(new am4charts.ColumnSeries());
  //   series.dataFields.valueX = field;
  //   series.dataFields.categoryY = "country";
  //   series.name = name;
  //   series.columns.template.tooltipText = "{name}: [bold]{valueX}[/]";
  //   series.columns.template.height = am4core.percent(100);
  //   series.sequencedInterpolation = true;
  //
  //   var valueLabel = series.bullets.push(new am4charts.LabelBullet());
  //   valueLabel.label.text = "{valueX}";
  //   valueLabel.label.horizontalCenter = "left";
  //   valueLabel.label.dx = 10;
  //   valueLabel.label.hideOversized = false;
  //   valueLabel.label.truncate = false;
  //
  //   // var categoryLabel = series.bullets.push(new am4charts.LabelBullet());
  //   // categoryLabel.label.text = "{name}";
  //   // categoryLabel.label.horizontalCenter = "right";
  //   // categoryLabel.label.dx = -10;
  //   // categoryLabel.label.fill = am4core.color("#fff");
  //   // categoryLabel.label.hideOversized = false;
  //   // categoryLabel.label.truncate = false;
  // }
  // createSeries("ensemble", "Current System Counts");
  // createSeries("activity", "Activity");


  chart.cursor = new am4charts.XYCursor();
  chart.cursor.lineX.disabled = true;
  chart.cursor.lineY.disabled = true;

}
