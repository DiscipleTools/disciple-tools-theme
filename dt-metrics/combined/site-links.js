jQuery(document).ready(function ($) {

  if (window.wpApiShare.url_path.startsWith('metrics/combined/site-links')) {
    display_site_link_metrics()
  }

  function display_site_link_metrics() {
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#combined-menu'));

    let chartDiv = jQuery('#chart');

    // Display chart controls
    chartDiv.empty().html(`
    <div class="section-header">${window.lodash.escape(window.wp_js_object.translations.headings.header)}</div>
    <div class="section-subheader">${window.lodash.escape(window.wp_js_object.translations.headings.sub_header)}:</div>
    <br>

    <table>
        <thead>
          <tr>
            <th>${window.lodash.escape(window.wp_js_object.translations.headings.date_range_header)}</th>
            <th>${window.lodash.escape(window.wp_js_object.translations.headings.site_links_header)}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
            <tr>
                <td>` + date_ranges_select_html() + `</td>
                <td>` + site_links_select_html() + `</td>
                <td>
                    <button id="chart_refresh_but" class="button">${window.lodash.escape(window.wp_js_object.translations.headings.refresh_but_header)}</button>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="display: inline-block" class="loading-spinner"></div>

    <div id="status_div" style="display: none;">
        <h2>${window.lodash.escape(window.wp_js_object.translations.headings.status_header)}</h2><hr>
        <div id="status_chart" style="height: 400px;"></div><br>
    </div>

    <div id="seeker_div" style="display: none;">
        <h2>${window.lodash.escape(window.wp_js_object.translations.headings.seeker_path_header)}</h2><hr>
        <div id="seeker_chart" style="height: 400px;"></div><br>
    </div>

    <div id="milestones_div" style="display: none;">
        <h2>${window.lodash.escape(window.wp_js_object.translations.headings.milestones_header)}</h2><hr>
        <div id="milestones_chart" style="height: 400px;"></div>
    </div>`);

    // Activate date range picker
    window.METRICS.setupDatePickerWithoutEndpoint(
      function (start, end, label) {
        $('.date_range_picker span').html(label);
      },
      moment().startOf('year'),
      moment().endOf('year')
    );

    // Listen out for refresh requests
    $('#chart_refresh_but').on('click', function () {
      $(".loading-spinner").addClass("active");
      refresh_charts();
    });

    // Force an initial refreshed display
    refresh_charts();
  }

  function date_ranges_select_html() {
    return `<div class="date_range_picker" style="min-width: 150px;">
                <i class="fi-calendar"></i>
                <span>${moment().format("YYYY")}</span>
                <i class="dt_caret down"></i>
            </div>`;
  }

  function site_links_select_html() {
    let sites = window.wp_js_object.data.sites;

    if (sites && sites.length > 0) {

      let html = '<select id="site_links_filter" style="min-width: 150px;">';
      $.each(sites, function (idx, val) {
        html += '<option value="' + window.lodash.escape(val['id']) + '">' + window.lodash.escape(val['name']) + '</option>';
      });

      html += '</select>';
      return html;

    } else {
      return window.lodash.escape(window.wp_js_object.translations.headings.site_links_none_header);
    }
  }

  function refresh_charts() {
    // Hide various charts
    $('#status_div').fadeOut('fast');
    $('#seeker_div').fadeOut('fast');
    $('#milestones_div').fadeOut('fast');

    // Fetch current parameters
    let drp = $('.date_range_picker').data('daterangepicker');
    let start_date = drp.startDate.unix();
    let end_date = drp.endDate.unix();
    let site_id = $('#site_links_filter').val();

    // Fetch metrics from specified endpoint
    jQuery
      .ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: `${wp_js_object.rest_endpoints_base}/site-links/?site_id=${site_id}&start=${start_date}&end=${end_date}`,
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-WP-Nonce", window.wpApiShare.nonce);
        },
      })
      .done(function (data) {
        // Disable loading spinner
        $(".loading-spinner").removeClass("active");

        if (data) {
          display_site_link_charts(data['statuses'], data['seeker_paths'], data['milestones']);
        }
      })
      .fail(function (err) {
        console.log("error");
        console.log(err);
      });
  }

  function display_site_link_charts(statuses, seeker_paths, milestones) {
    // Ensure overwritten charts are automatically disposed.
    am4core.options.autoDispose = true;
    am4core.useTheme(am4themes_animated);

    // Proceed with current statuses chart creation.
    $('#status_div').fadeOut('fast', function () {
      display_site_link_charts_status(statuses, function () {
        $('#status_div').fadeIn('fast');
      });
    });

    // Proceed with current statuses chart creation.
    $('#seeker_div').fadeOut('fast', function () {
      display_site_link_charts_seeker(seeker_paths, function () {
        $('#seeker_div').fadeIn('fast');
      });
    });

    // Proceed with current statuses chart creation.
    $('#milestones_div').fadeOut('fast', function () {
      display_site_link_charts_milestones(milestones, function () {
        $('#milestones_div').fadeIn('fast');
      });
    });
  }

  function display_site_link_charts_status(statuses, callback) {
    am4core.ready(function () {

      // Create chart instance
      let chart = am4core.create("status_chart", am4charts.PieChart);

      // Add data
      chart.data = [];
      if (statuses && statuses.length > 0) {
        $.each(statuses, function (idx, metric) {
          if (metric['status'] && metric['count']) {
            chart.data.push({
              'status': metric['status'],
              'count': metric['count']
            });
          }
        });

        // Add and configure Series
        let pieSeries = chart.series.push(new am4charts.PieSeries());
        pieSeries.dataFields.value = "count";
        pieSeries.dataFields.category = "status";
        pieSeries.slices.template.stroke = am4core.color("#fff");
        pieSeries.slices.template.strokeWidth = 2;
        pieSeries.slices.template.strokeOpacity = 1;

        // This creates initial animation
        pieSeries.hiddenState.properties.opacity = 1;
        pieSeries.hiddenState.properties.endAngle = -90;
        pieSeries.hiddenState.properties.startAngle = -90;

        // Execute callback() function
        callback();
      }
    }); // end am4core.ready()
  }

  function display_site_link_charts_seeker(seeker_paths, callback) {
    am4core.ready(function () {

      // Create chart instance
      let chart = am4core.create("seeker_chart", am4charts.XYChart);

      // Add data
      chart.data = [];
      if (seeker_paths && seeker_paths.length > 0) {
        $.each(seeker_paths, function (idx, metric) {
          if (metric['seeker_path'] && metric['count']) {
            chart.data.push({
              'seeker_path': metric['seeker_path'],
              'count': metric['count']
            });
          }
        });

        // Create axes
        let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
        categoryAxis.dataFields.category = "seeker_path";
        categoryAxis.numberFormatter.numberFormat = "#";
        categoryAxis.renderer.inversed = true;
        categoryAxis.renderer.grid.template.location = 0;
        categoryAxis.renderer.cellStartLocation = 0.1;
        categoryAxis.renderer.cellEndLocation = 0.9;

        let valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
        valueAxis.renderer.opposite = true;

        createSeries("count", "");

        // Execute callback() function
        callback();
      }

      // Create series
      function createSeries(field, name) {
        let series = chart.series.push(new am4charts.ColumnSeries());
        series.dataFields.valueX = field;
        series.dataFields.categoryY = "seeker_path";
        series.name = name;
        series.columns.template.tooltipText = "{categoryY}: [bold]{valueX}[/]";
        series.columns.template.height = am4core.percent(100);
        series.sequencedInterpolation = true;

        let valueLabel = series.bullets.push(new am4charts.LabelBullet());
        valueLabel.label.text = "{valueX}";
        valueLabel.label.horizontalCenter = "left";
        valueLabel.label.dx = 10;
        valueLabel.label.hideOversized = false;
        valueLabel.label.truncate = false;

        let categoryLabel = series.bullets.push(new am4charts.LabelBullet());
        categoryLabel.label.text = "{name}";
        categoryLabel.label.horizontalCenter = "right";
        categoryLabel.label.dx = -10;
        categoryLabel.label.fill = am4core.color("#fff");
        categoryLabel.label.hideOversized = false;
        categoryLabel.label.truncate = false;
      }
    }); // end am4core.ready()
  }

  function display_site_link_charts_milestones(milestones, callback) {
    am4core.ready(function () {

      // Create chart instance
      let chart = am4core.create("milestones_chart", am4charts.XYChart);

      // Add data
      chart.data = [];
      if (milestones && milestones.length > 0) {
        $.each(milestones, function (idx, metric) {
          if (metric['milestone'] && metric['count']) {
            chart.data.push({
              'milestone': metric['milestone'],
              'count': metric['count']
            });
          }
        });

        // Create axes
        let categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
        categoryAxis.dataFields.category = "milestone";
        categoryAxis.renderer.grid.template.location = 0;
        categoryAxis.renderer.minGridDistance = 30;

        categoryAxis.renderer.labels.template.adapter.add("dy", function (dy, target) {
          if (target.dataItem && target.dataItem.index & 2 == 2) {
            return dy + 25;
          }
          return dy;
        });

        let valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

        // Create series
        let series = chart.series.push(new am4charts.ColumnSeries());
        series.dataFields.valueY = "count";
        series.dataFields.categoryX = "milestone";
        series.name = "Milestones";
        series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
        series.columns.template.fillOpacity = .8;

        let columnTemplate = series.columns.template;
        columnTemplate.strokeWidth = 2;
        columnTemplate.strokeOpacity = 1;

        // Execute callback() function
        callback();
      }
    }); // end am4core.ready()
  }
});
