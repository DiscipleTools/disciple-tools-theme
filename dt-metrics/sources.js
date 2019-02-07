(function($) {
  "use strict";

  // This is based from the example in one-page-chart-template.js

  jQuery(document).ready(function() {
    if('#sources' === window.location.hash) {
      window.show_sources_overview()
    }
  })

  const { __, _x, _n, _nx } = wp.i18n;

  window.show_sources_overview = function show_sources_overview(dateRange) {

    dateRange = dateRange || {};

    let chartDiv = jQuery('#chart')

    chartDiv.empty().html(`
      <span class="section-header">${_.escape(window.wpApiSources.translations.title)}</span>


      <div class="section-subheader">${ __( 'Filter contacts to date range:', 'disciple_tools' ) }</div>
      <input id="date_range" type="text" name="daterange" style="max-width: 250px; display: inline-block"/>
      <div style="display: inline-block" class="loading-spinner"></div>

      <div id="charts"></div>
    `)

    window.METRICS.setupDatePicker(
      `${window.wpApiSources.root}dt/v1/metrics/sources_chart_data/`,
      function (data, label) {
        if ( data ){
          draw_data(data, label)
        }
      },
      moment(0),
      moment()
    )


    function draw_data(data, label = "This year") {
      if (!data) {
        data = window.wpApiSources.data
      }
      let chartsDiv = $("#charts").empty()

      data = Object.values(data);

      let height = Math.min(60*data.length, 1000) +"px"

      chartDiv.find(".js-loading").remove()

      let filteringOutText = `${__( "Showing contacts from", 'disciple_tools' )} ${label}.`;

      chartsDiv.append($("<div>").html(`
  
        <hr>
  
        <h1>${__("All contacts, by source and status", "disciple_tools")}</h1>
  
        <p>${filteringOutText} ${__( "A contact can come from more than one source.", 'disciple_tools' )}</p>
  
        <div id="chartdiv1" style="min-height: ${height}"></div>
  
        <hr>
  
        <h1>${__( "Active contacts, by source and seeker path", 'disciple_tools' )}</h1>
  
        <p>${ __( 'This is displaying only the contacts with an active status right now.', 'disciple_tools' ) } 
        ${filteringOutText} 
        ${__( "A contact can come from more than one source.", 'disciple_tools' )}
        </p>
  
        <div id="chartdiv2" style="min-height: ${height}"></div>
  
        <hr>
  
        <h1>${__( "Active contacts, by source and faith milestone", 'disciple_tools' )}</h1>
  
        <p>${__( "This is displaying only the contacts with an \"active\" status right now.", 'disciple_tools' )}
        ${filteringOutText} 
        ${__( "A contact can come from more than one source, and it can have more than one faith milestone at the same time.", 'disciple_tools' )}</p>
         
        <p><b>${ __( 'Faith milestone', 'disciple_tools' ) }:</b> <select class="js-milestone"></select></p>
  
        <div id="chartdiv3" style="min-height: ${height}"></div>
      `))

      let localizedObject = window.wpApiSources

      // Prepare data
      for (let item of data) {
        if (item.name_of_source == 'null') {
          item.translated_source = 'null (none set)';
        } else {
          item.translated_source =
            _.get(localizedObject, `sources.${item.name_of_source}`) || item.name_of_source;
        }
      }
      // We need to collect all status names, because not all of them are in localizedObject
      const status_names = []; /* eg: ['assigned', 'closed'] */
      status_names.push(...localizedObject.overall_status_settings.order)

      const seeker_path_names = [] /* eg: ['attempted', 'established'] */
      seeker_path_names.push(...localizedObject.seeker_path_settings.order)

      const milestone_names = [] /* eg: ['milestone_belief', 'milestone_has_bible'] */
      milestone_names.push(...Object.keys(localizedObject.milestone_settings))

      for (let item of data) {
        for (let key of Object.keys(item)) {
          if (key.startsWith('status_')) {
            let status_name = key.replace('status_', '', 1);
            if (!status_names.includes(status_name)) {
              status_names.push(status_name)
            }
          } else if (key.startsWith('active_seeker_path_')) {
            let seeker_path_name = key.replace('active_seeker_path_', '', 1)
            if (!seeker_path_names.includes(seeker_path_name)) {
              seeker_path_names.push(seeker_path_name)
            }
          } else if (key.startsWith('active_milestone_')) {
            let milestone_name = key.replace('active_', '', 1)
            if (!milestone_names.includes(milestone_name)) {
              milestone_names.push(milestone_name);
            }
          }
        }
      }

      {
        // Create chart instance
        let chart = am4core.create("chartdiv1", am4charts.XYChart);

        chart.data = _.orderBy(data, (a=>{return a["total"] || 0}), ['asc']);

        // Create axes
        let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
        categoryAxis.dataFields.category = "translated_source";
        categoryAxis.title.text = "Source";
        categoryAxis.renderer.grid.template.location = 0;
        categoryAxis.renderer.minGridDistance = 20;

        let valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
        valueAxis.title.text = "Contacts";

        // Create series
        for (let status of status_names) {
          let series = chart.series.push(new am4charts.ColumnSeries());
          if (localizedObject.overall_status_settings.default[status].color) {
            series.columns.template.fill = am4core.color(localizedObject.overall_status_settings.default[status].color);
          }
          series.stroke = am4core.color("#000000");
          series.dataFields.valueX = "status_" + status;
          series.dataFields.categoryY = "translated_source";
          series.name = localizedObject.overall_status_settings.default[status].label || status;
          series.tooltipText = "{name}: [bold]{valueX}[/]";
          series.stacked = true;
        }

        // Add cursor and legend
        chart.cursor = new am4charts.XYCursor();
        chart.legend = new am4charts.Legend();
        chart.legend.position = 'top';
      }

      {

        // Create chart instance
        let chart2 = am4core.create("chartdiv2", am4charts.XYChart);
        chart2.data = _.orderBy(data, a=>a.total_active_seeker_path || 0, ['asc']);

        // Create axes
        let categoryAxis = chart2.yAxes.push(new am4charts.CategoryAxis());
        categoryAxis.dataFields.category = "translated_source";
        categoryAxis.title.text = "Source";
        categoryAxis.renderer.grid.template.location = 0;
        categoryAxis.renderer.minGridDistance = 20;

        let valueAxis = chart2.xAxes.push(new am4charts.ValueAxis());
        valueAxis.title.text = "Contacts";

        // Create series
        for (let seeker_path of seeker_path_names) {
          let series = chart2.series.push(new am4charts.ColumnSeries());
          series.dataFields.valueX = "active_seeker_path_" + seeker_path;
          series.dataFields.categoryY = "translated_source";
          series.stroke = am4core.color("#000");
          series.name = localizedObject.seeker_path_settings.default[seeker_path].label || seeker_path;
          series.tooltipText = "{name}: [bold]{valueX}[/]";
          series.stacked = true;
        }

        // Add cursor and legend
        chart2.cursor = new am4charts.XYCursor();
        chart2.legend = new am4charts.Legend();
        chart2.legend.position = 'top';
      }

      {
        for (let milestone of milestone_names) {
          let name = (localizedObject.milestone_settings[milestone] || {}) || milestone;
          $(".js-milestone").append($("<option>").val(milestone).text(name));
        }

        // Create chart instance
        let allSeries = [];
        let chart3 = am4core.create("chartdiv3", am4charts.XYChart);
        chart3.data = _.orderBy(data, ['total'], ['asc']);

        // Create axes
        let categoryAxis = chart3.yAxes.push(new am4charts.CategoryAxis());
        categoryAxis.dataFields.category = "translated_source";
        categoryAxis.title.text = "Source";
        categoryAxis.renderer.grid.template.location = 0;
        categoryAxis.renderer.minGridDistance = 20;

        let valueAxis = chart3.xAxes.push(new am4charts.ValueAxis());
        valueAxis.title.text = "Contacts";

        // Create series
        for (let milestone of milestone_names) {
          let series = chart3.series.push(new am4charts.ColumnSeries());
          series.dataFields.valueX = "active_" + milestone;
          series.dataFields.categoryY = "translated_source";
          series.stroke = am4core.color("#000");
          series.name = (localizedObject.milestone_settings[milestone] || {}) || milestone;
          series.tooltipText = "{name}: [bold]{valueX}[/]";
          // series.stacked = true;
          series.clustered = false
          series.hide()
          allSeries.push(series);
        }
        // Add cursor
        chart3.cursor = new am4charts.XYCursor();
        chart3.events.on("inited", function (ev) {
          $(".js-milestone").trigger("change");
        })

        $(".js-milestone").on("change", function () {
          let milestone = $(this).val();
          chart3.data = _.orderBy(data, (a=>{return a["active_" + milestone] || 0}), ['asc']);
          for (let series of allSeries) {
            if (series.dataFields.valueX == "active_" + milestone) {
              series.show();
            } else {
              series.hide();
            }
          }
        })
      }
    }

    draw_data()
  }

  function show_error(error) {
    console.error(error)
    $("body").empty()
      .append($("<h1>").text("Error:"))
      .append($("<pre>").text(error))
      .append($("<p>").text("See developer console for more details."))
  }

})(jQuery)
