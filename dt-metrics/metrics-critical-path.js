jQuery(document).ready(function() {
  if( '#project_critical_path' === window.location.hash  ) {
    project_critical_path()
  }
  if( '#project_seeker_path' === window.location.hash  ) {
    project_seeker_path()
  }
  if( '#project_milestones' === window.location.hash  ) {
    project_milestones()
  }
  jQuery('#metrics-sidemenu').foundation('down', jQuery('#path-menu'));


})

let setupDatePicker = function (endpoint_url, callback) {
  $('#date_range').daterangepicker({
    "showDropdowns": true,
    ranges: {
      'All time': [moment("1980-01-01"),  moment().endOf('year')],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
      'Last Month': [moment().subtract(1, 'month').startOf('month'),
        moment().subtract(1, 'month').endOf('month')],
      'This Year': [moment().startOf('year'), moment().endOf('year')],
      'Last Year': [moment().subtract(1, 'year').startOf('year'),
        moment().subtract(1, 'year').endOf('year')]

    },
    "linkedCalendars": false,
    locale: {
      format: 'YYYY-MM-DD'
    },
    "startDate": moment().startOf('year').format('YYYY-MM-DD'),
    "endDate": moment().endOf('year').format('YYYY-MM-DD'),
  }, function(start, end, label) {
    jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: `${endpoint_url}?start=${start.format('YYYY-MM-DD')}&end=${end.format('YYYY-MM-DD')}`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
      },
    })
      .done(callback)
      .fail(function (err) {
        console.log("error")
        console.log(err)
        jQuery("#errors").append(err.responseText)
      })
    // console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
  });
}


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

function project_seeker_path() {
  let chartDiv = jQuery('#chart')
  let sourceData = dtMetricsProject.data
  let translations = dtMetricsProject.data.translations

  chartDiv.empty().html(`
    <div class="section-header">Seeker path</div>
    <div class="section-subheader">Date Range:</div>
    <input id="date_range" type="text" name="daterange" style="max-width: 250px"/>
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


  setupDatePicker(
    `${dtMetricsProject.root}dt/v1/metrics/seeker_path/`,
    function (data) {
      if ( data ){
        chart.data = data
      }
    }
  )
}

function project_milestones() {
  let chartDiv = jQuery('#chart')
  let sourceData = dtMetricsProject.data
  let translations = dtMetricsProject.data.translations

  chartDiv.empty().html(`
    <div class="section-header">Milestones</div>
    <div class="section-subheader">Date Range:</div>
    <input id="date_range" type="text" name="daterange" style="max-width: 250px"/>
    <div id="chartdiv" style="height: 400px"></div>
  `)

  let chart = am4core.create("chartdiv", am4charts.XYChart);

  chart.data = sourceData.milestones
  let categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
  categoryAxis.dataFields.category = "milestones";
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
  series.dataFields.categoryX = "milestones";
  series.name = "Visits";
  series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
  series.columns.template.fillOpacity = .8;

  let columnTemplate = series.columns.template;
  columnTemplate.strokeWidth = 2;
  columnTemplate.strokeOpacity = 1;

  setupDatePicker(
    `${dtMetricsProject.root}dt/v1/metrics/milestones/`,
    function (data) {
      if ( data ){
        chart.data = data
      }
    }
  )

}
