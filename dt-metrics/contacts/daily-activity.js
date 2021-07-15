jQuery(document).ready(function ($) {

  if (window.wpApiShare.url_path.startsWith('metrics/contacts/daily-activity')) {
    display_daily_activity()
  }

  function display_daily_activity() {
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#contacts-menu'));

    let chartDiv = jQuery('#chart');

    chartDiv.empty().html(`
    <div class="section-header">${window.lodash.escape(window.wp_js_object.translations.activities)}</div>
    <div class="section-subheader">${window.lodash.escape(window.wp_js_object.translations.filter_to_date_range)}:</div>
    <br>
    <select id="activity_date_range_filter">
        <option selected value="this-month">This Month</option>
        <option value="last-month">Last Month</option>
        <option value="2-months-ago">2 Months Ago</option>
        <option value="3-months-ago">3 Months Ago</option>
        <option value="4-months-ago">4 Months Ago</option>
        <option value="5-months-ago">5 Months Ago</option>
        <option value="6-months-ago">6 Months Ago</option>
    </select>
    <div style="display: inline-block" class="loading-spinner"></div>
    Click on chart timeline day summary to display a detailed list of metrics below.
    <div id="chartdiv" style="height: 600px;"></div><br>
    <h2 id="chart_day_title"></h2><hr>
    <div id="chart_day_counts_div" style="display: none;"></div><br>
    <div id="chart_day_health_counts_div" style="display: none;"></div>`);

    let start_date = window.wp_js_object.data.activities.start;
    let end_date = window.wp_js_object.data.activities.end;
    let days = window.wp_js_object.data.activities.days;

    display_daily_activity_chart(start_date, end_date, days);
  }

  function display_daily_activity_chart(start_date, end_date, days) {
    // Ensure overwritten charts are automatically disposed.
    am4core.options.autoDispose = true;

    // Ensure to hide daily sub-charts.
    $('#chart_day_title').html('');
    $('#chart_day_counts_div').fadeOut('fast', function () {});
    $('#chart_day_health_counts_div').fadeOut('fast', function () {});

    // Proceed with chart creation.
    $('#chartdiv').fadeOut('fast', function () {
      am4core.ready(function () {

        am4core.useTheme(am4themes_animated);
        am4core.ready(function () {
          let chart = am4core.create("chartdiv", am4plugins_timeline.CurveChart);

          chart.curveContainer.padding(0, 300, 0, 0);
          chart.maskBullets = false;

          let colorSet = new am4core.ColorSet();

          chart.dateFormatter.inputDateFormat = "yyyy-MM-dd";
          chart.dateFormatter.dateFormat = "yyyy-MM-dd";
          chart.fontSize = 10;
          chart.tooltipContainer.fontSize = 10;
          chart.data = build_days_data_array(days, colorSet);
          chart.clickable = true;

          let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
          categoryAxis.dataFields.category = "category";
          categoryAxis.renderer.grid.template.disabled = true;
          categoryAxis.renderer.labels.template.paddingRight = 25;
          categoryAxis.renderer.minGridDistance = 10;
          categoryAxis.renderer.innerRadius = 10;
          categoryAxis.renderer.radius = 30;

          let dateAxis = chart.xAxes.push(new am4charts.DateAxis());

          dateAxis.renderer.points = getPoints();

          dateAxis.renderer.autoScale = false;
          dateAxis.renderer.autoCenter = false;
          dateAxis.renderer.minGridDistance = 70;
          //dateAxis.baseInterval = {count: 5, timeUnit: "month"};
          dateAxis.renderer.tooltipLocation = 0;
          dateAxis.renderer.line.strokeDasharray = "1,4";
          dateAxis.renderer.line.strokeOpacity = 0.5;
          dateAxis.tooltip.background.fillOpacity = 0.2;
          dateAxis.tooltip.background.cornerRadius = 5;
          dateAxis.tooltip.label.fill = new am4core.InterfaceColorSet().getFor("alternativeBackground");
          dateAxis.tooltip.label.paddingTop = 7;
          dateAxis.endLocation = 0;
          dateAxis.startLocation = -0.5;
          dateAxis.min = Date.parse(start_date);
          dateAxis.max = Date.parse(end_date);

          let labelTemplate = dateAxis.renderer.labels.template;
          labelTemplate.verticalCenter = "middle";
          labelTemplate.fillOpacity = 0.6;
          labelTemplate.background.fill = new am4core.InterfaceColorSet().getFor("background");
          labelTemplate.background.fillOpacity = 1;
          labelTemplate.fill = new am4core.InterfaceColorSet().getFor("text");
          labelTemplate.padding(7, 7, 7, 7);

          let series = chart.series.push(new am4plugins_timeline.CurveColumnSeries());
          series.columns.template.height = am4core.percent(30);

          series.dataFields.openDateX = "start";
          series.dataFields.dateX = "end";
          series.dataFields.categoryY = "category";
          series.baseAxis = categoryAxis;
          series.columns.template.propertyFields.fill = "color"; // get color from data
          series.columns.template.propertyFields.stroke = "color";
          series.columns.template.strokeOpacity = 0;
          series.columns.template.fillOpacity = 0.6;

          let imageBullet1 = series.bullets.push(new am4plugins_bullets.PinBullet());
          imageBullet1.background.radius = 0;
          imageBullet1.locationX = 1;
          imageBullet1.propertyFields.stroke = "color";
          imageBullet1.background.propertyFields.fill = "color";
          //..imageBullet1.image = new am4core.Image();
          //..imageBullet1.image.propertyFields.href = "icon";
          //..imageBullet1.image.scale = 0.7;
          //..imageBullet1.circle.radius = am4core.percent(100);
          imageBullet1.background.fillOpacity = 0.8;
          imageBullet1.background.strokeOpacity = 0;
          imageBullet1.dy = -2;
          imageBullet1.background.pointerBaseWidth = 10;
          imageBullet1.background.pointerLength = 10
          imageBullet1.background.hide();
          imageBullet1.background.disabled = true;
          //..imageBullet1.tooltipHTML = "{tooltip}";
          imageBullet1.label = new am4core.Label();
          imageBullet1.label.html = "{tooltip}";

          // Capture bullet clicks and display count breakdowns accordingly!
          imageBullet1.cursorOverStyle = am4core.MouseCursorStyle.pointer;
          imageBullet1.cursorDownStyle = am4core.MouseCursorStyle.grabbing;
          imageBullet1.clickable = true;
          imageBullet1.events.on("hit", function (ev) {
            display_daily_counts(ev.target.dataItem.dataContext);
          });

          series.tooltip.pointerOrientation = "up";

          imageBullet1.background.adapter.add("pointerAngle", (value, target) => {
            if (target.dataItem) {
              var position = dateAxis.valueToPosition(target.dataItem.openDateX.getTime());
              return dateAxis.renderer.positionToAngle(position);
            }
            return value;
          });

          let hs = imageBullet1.states.create("hover");
          hs.properties.scale = 1.3;
          hs.properties.opacity = 1;

          let textBullet = series.bullets.push(new am4charts.LabelBullet());
          textBullet.label.propertyFields.text = "text";
          textBullet.disabled = true;
          textBullet.propertyFields.disabled = "textDisabled";
          textBullet.label.strokeOpacity = 0;
          textBullet.locationX = 1;
          textBullet.dy = -100;
          textBullet.label.textAlign = "middle";

          chart.scrollbarX = new am4core.Scrollbar();
          chart.scrollbarX.align = "center"
          chart.scrollbarX.width = am4core.percent(75);
          chart.scrollbarX.parent = chart.curveContainer;
          chart.scrollbarX.height = 300;
          chart.scrollbarX.orientation = "vertical";
          chart.scrollbarX.x = 128;
          chart.scrollbarX.y = -140;
          chart.scrollbarX.isMeasured = false;
          chart.scrollbarX.opacity = 0.5;

          let cursor = new am4plugins_timeline.CurveCursor();
          chart.cursor = cursor;
          cursor.xAxis = dateAxis;
          cursor.yAxis = categoryAxis;
          cursor.lineY.disabled = true;
          cursor.lineX.disabled = true;

          dateAxis.renderer.tooltipLocation2 = 0;
          categoryAxis.cursorTooltipEnabled = false;

          chart.zoomOutButton.disabled = true;

          let previousBullet;

          chart.events.on("inited", function () {
            setTimeout(function () {
              // DISABLE ROLLOVER FUNCTION // hoverItem(series.dataItems.getIndex(0));
            }, 2000)
          })

          function hoverItem(dataItem) {
            let bullet = dataItem.bullets.getKey(imageBullet1.uid);
            let index = dataItem.index;

            if (index >= series.dataItems.length - 1) {
              index = -1;
            }

            if (bullet) {

              if (previousBullet) {
                previousBullet.isHover = false;
              }

              bullet.isHover = true;

              previousBullet = bullet;
            }
            setTimeout(
              function () {
                hoverItem(series.dataItems.getIndex(index + 1))
              }, 1000);
          }

          // Respond to filter date range changes
          $(document).off('change').on('change', '#activity_date_range_filter', function () {
            refresh_daily_activity_chart(chart, $('#activity_date_range_filter').val());
          });

          // Display Updated Chart
          $('#chartdiv').fadeIn('slow', function () {
          });

        });

        function getPoints() {

          let points = [{x: -1300, y: 200}, {x: 0, y: 200}];

          let w = 400;
          let h = 400;
          let levelCount = 4;

          let radius = am4core.math.min(w / (levelCount - 1) / 2, h / 2);
          let startX = radius;

          for (let i = 0; i < 25; i++) {
            let angle = 0 + i / 25 * 90;
            let centerPoint = {y: 200 - radius, x: 0}
            points.push({
              y: centerPoint.y + radius * am4core.math.cos(angle),
              x: centerPoint.x + radius * am4core.math.sin(angle)
            });
          }


          for (let i = 0; i < levelCount; i++) {

            if (i % 2 != 0) {
              points.push({y: -h / 2 + radius, x: startX + w / (levelCount - 1) * i})
              points.push({y: h / 2 - radius, x: startX + w / (levelCount - 1) * i})

              let centerPoint = {y: h / 2 - radius, x: startX + w / (levelCount - 1) * (i + 0.5)}
              if (i < levelCount - 1) {
                for (let k = 0; k < 50; k++) {
                  let angle = -90 + k / 50 * 180;
                  points.push({
                    y: centerPoint.y + radius * am4core.math.cos(angle),
                    x: centerPoint.x + radius * am4core.math.sin(angle)
                  });
                }
              }

              if (i == levelCount - 1) {
                points.pop();
                points.push({y: -radius, x: startX + w / (levelCount - 1) * i})
                let centerPoint = {y: -radius, x: startX + w / (levelCount - 1) * (i + 0.5)}
                for (let k = 0; k < 25; k++) {
                  let angle = -90 + k / 25 * 90;
                  points.push({
                    y: centerPoint.y + radius * am4core.math.cos(angle),
                    x: centerPoint.x + radius * am4core.math.sin(angle)
                  });
                }
                points.push({y: 0, x: 1300});
              }

            } else {
              points.push({y: h / 2 - radius, x: startX + w / (levelCount - 1) * i})
              points.push({y: -h / 2 + radius, x: startX + w / (levelCount - 1) * i})
              let centerPoint = {y: -h / 2 + radius, x: startX + w / (levelCount - 1) * (i + 0.5)}
              if (i < levelCount - 1) {
                for (let k = 0; k < 50; k++) {
                  let angle = -90 - k / 50 * 180;
                  points.push({
                    y: centerPoint.y + radius * am4core.math.cos(angle),
                    x: centerPoint.x + radius * am4core.math.sin(angle)
                  });
                }
              }
            }
          }

          return points;
        }

      });
    });
  }

  function display_daily_counts(dataContext) {
    $('#chart_day_title').html(dataContext.text);
    display_day_counts(dataContext.counts);
  }

  function display_day_counts(day_counts) {
    //console.log(day_counts);
    let chart_day_counts_div = $('#chart_day_counts_div');
    chart_day_counts_div.fadeOut('fast', function () {

      let html = '<table>';

      html += '<thead>';
      html += '<tr>';
      html += '<th>Metric</th>';
      html += '<th>Count</th>';
      html += '</tr>';
      html += '</thead>';

      html += '<tbody>';

      // Default Metrics
      html += '<tr>';
      html += '<td>New Contacts</td>';
      html += '<td>' + day_counts['new_contacts'] + '</td>';
      html += '</tr>';

      html += '<tr>';
      html += '<td>Baptisms</td>';
      html += '<td>' + day_counts['baptisms'] + '</td>';
      html += '</tr>';

      html += '<tr>';
      html += '<td>First Meetings</td>';
      html += '<td>' + day_counts['first_meetings'] + '</td>';
      html += '</tr>';

      html += '<tr>';
      html += '<td>Ongoing Meetings</td>';
      html += '<td>' + day_counts['ongoing_meetings'] + '</td>';
      html += '</tr>';

      // Seeker Path Updates
      let seeker_path_updates = day_counts['seeker_path_updates'];

      html += '<tr>';
      html += '<td>' + seeker_path_updates['attempted']['label'] + '</td>';
      html += '<td>' + seeker_path_updates['attempted']['value'] + '</td>';
      html += '</tr>';

      html += '<tr>';
      html += '<td>' + seeker_path_updates['coaching']['label'] + '</td>';
      html += '<td>' + seeker_path_updates['coaching']['value'] + '</td>';
      html += '</tr>';

      html += '<tr>';
      html += '<td>' + seeker_path_updates['established']['label'] + '</td>';
      html += '<td>' + seeker_path_updates['established']['value'] + '</td>';
      html += '</tr>';

      html += '<tr>';
      html += '<td>' + seeker_path_updates['met']['label'] + '</td>';
      html += '<td>' + seeker_path_updates['met']['value'] + '</td>';
      html += '</tr>';

      html += '<tr>';
      html += '<td>' + seeker_path_updates['none']['label'] + '</td>';
      html += '<td>' + seeker_path_updates['none']['value'] + '</td>';
      html += '</tr>';

      html += '<tr>';
      html += '<td>' + seeker_path_updates['ongoing']['label'] + '</td>';
      html += '<td>' + seeker_path_updates['ongoing']['value'] + '</td>';
      html += '</tr>';

      html += '<tr>';
      html += '<td>' + seeker_path_updates['scheduled']['label'] + '</td>';
      html += '<td>' + seeker_path_updates['scheduled']['value'] + '</td>';
      html += '</tr>';

      // Health
      let health = day_counts['health'];

      health.forEach(function (metric) {
        html += '<tr>';
        html += '<td>' + metric['label'] + '</td>';
        html += '<td>' + metric['practicing'] + '</td>';
        html += '</tr>';
      });

      html += '</tbody>';
      html += '</table>';

      chart_day_counts_div.html(html);

      // Display Counts Chart
      chart_day_counts_div.fadeIn('slow', function () {});
    });
  }

  function refresh_daily_activity_chart(chart, date_range_filter) {

    // Start loading spinner
    $(".loading-spinner").addClass("active");

    jQuery
      .ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: `${wp_js_object.rest_endpoints_base}/daily-activity/?date_range=${date_range_filter}`,
        beforeSend: function (xhr) {
          xhr.setRequestHeader("X-WP-Nonce", window.wpApiShare.nonce);
        },
      })
      .done(function (data) {
        // Disable loading spinner
        $(".loading-spinner").removeClass("active");

        console.log(data);
        chart.dispose(); // Force chart disposal
        display_daily_activity_chart(data.start, data.end, data.days);
      })
      .fail(function (err) {
        console.log("error");
        console.log(err);
      });
  }

  function build_days_data_array(days, colorSet) {
    let data = [];

    for (let [key, day] of Object.entries(days)) {
      //console.log(day);

      data.push({
        "category": "",
        "start": key + " 00:00",
        "end": key + " 23:59",
        "color": colorSet.next(),
        "icon": fetch_icon('default'),
        "tooltip": fetch_tooltip(key, day),
        "text": key,
        "counts": day
      });
    }

    return data;
  }

  function fetch_tooltip(date, counts) {
    let html = '<h3>' + date + '</h3>';
    html += '<ul>';
    html += '<li>New Contacts: ' + counts['new_contacts'] + '</li>'
    html += '<li>Baptisms: ' + counts['baptisms'] + '</li>'
    //html += '<li>Active Groups: ' + counts['active_groups'] + '</li>'
    html += '</ul>';

    return html;
  }

  function fetch_icon(icon) {
    switch (icon) {
      case 'alarm':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjQ1Ljc3M3B4IiBoZWlnaHQ9IjQ1Ljc3M3B4IiB2aWV3Qm94PSIwIDAgNDUuNzczIDQ1Ljc3MyIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDUuNzczIDQ1Ljc3MzsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxwYXRoIGQ9Ik01LjA4MSwxMy43MzdjMi41ODItMy45NDIsNi42MDktNi44NDksMTEuMzItNy45ODhjMC4zNjMtMC4wODcsMC42NjItMC4zNDQsMC44MDItMC42ODkNCgkJCWMwLjE0MS0wLjM0NiwwLjEwNy0wLjczOC0wLjA5MS0xLjA1NUMxNS42MDQsMS42MDEsMTIuOTM2LDAsOS44ODgsMEM1LjE3NiwwLDEuMzU0LDMuODIsMS4zNTQsOC41MzJjMCwyLDAuNjkxLDMuODM3LDEuODQ1LDUuMjkNCgkJCWMwLjIzMSwwLjI5MywwLjU4OSwwLjQ1NSwwLjk2MiwwLjQzOFM0Ljg3NywxNC4wNDgsNS4wODEsMTMuNzM3eiIvPg0KCQk8cGF0aCBkPSJNMzUuODg2LDBjLTMuMDM0LDAtNS42OTMsMS41ODYtNy4yMDQsMy45NzRjLTAuMiwwLjMxNi0wLjIzNSwwLjcxMS0wLjA5NCwxLjA1OWMwLjE0MiwwLjM0OSwwLjQ0MiwwLjYwNSwwLjgwOSwwLjY5MQ0KCQkJYzQuNzI0LDEuMTEyLDguNzY1LDMuOTk5LDExLjM2OSw3LjkyOGMwLjIwNywwLjMxMiwwLjU1MiwwLjUwNSwwLjkyNywwLjUxOGMwLjM3NSwwLjAxNCwwLjczMS0wLjE1NCwwLjk2MS0wLjQ1MQ0KCQkJYzEuMTA1LTEuNDM2LDEuNzY2LTMuMjMyLDEuNzY2LTUuMTg2QzQ0LjQxNywzLjgyLDQwLjU5OCwwLDM1Ljg4NiwweiIvPg0KCQk8cGF0aCBkPSJNNDEuNzUyLDI2LjEzMmMwLTMuMjk0LTAuODU3LTYuMzktMi4zNTEtOS4wODRjLTIuNzY5LTQuOTktNy43NDItOC41NzctMTMuNTk1LTkuNDc1Yy0wLjkzMy0wLjE0My0xLjg4LTAuMjQtMi44NTMtMC4yNA0KCQkJYy0xLjAxNiwwLTIuMDA2LDAuMTA0LTIuOTc5LDAuMjZDMTQuMTQ2LDguNTI4LDkuMTk4LDEyLjEzLDYuNDU4LDE3LjEyNmMtMS40NjcsMi42NzYtMi4zMDQsNS43NDQtMi4zMDQsOS4wMDYNCgkJCWMwLDUuNTg2LDIuNDYzLDEwLjU5Nyw2LjM0MywxNC4wNDFsLTEuNTg0LDIuMjMxYy0wLjY4MiwwLjk2MS0wLjQ1NiwyLjI5MSwwLjUwNSwyLjk3NWMwLjM3NSwwLjI2NiwwLjgwNiwwLjM5NSwxLjIzMywwLjM5NQ0KCQkJYzAuNjY4LDAsMS4zMjYtMC4zMTMsMS43NDEtMC44OThsMS41ODMtMi4yM2MyLjY2OSwxLjQ1Nyw1LjcyOCwyLjI4Nyw4Ljk3OCwyLjI4N2MzLjI0OSwwLDYuMzA4LTAuODMsOC45NzctMi4yODdsMS41ODMsMi4yMw0KCQkJYzAuNDE2LDAuNTg2LDEuMDczLDAuODk4LDEuNzQxLDAuODk4YzAuNDI3LDAsMC44NTctMC4xMjksMS4yMzItMC4zOTVjMC45NjEtMC42ODQsMS4xODgtMi4wMTQsMC41MDYtMi45NzVsLTEuNTg0LTIuMjMxDQoJCQlDMzkuMjg4LDM2LjcyOSw0MS43NTIsMzEuNzE4LDQxLjc1MiwyNi4xMzJ6IE0yMi45NTQsMzkuNjc0Yy03LjQ2OCwwLTEzLjU0Mi02LjA3NC0xMy41NDItMTMuNTQyDQoJCQljMC0yLjMyOCwwLjU5MS00LjUxOSwxLjYyOS02LjQzNWMxLjk3Ni0zLjY0NCw1LjU4LTYuMjY5LDkuODI2LTYuOTNjMC42ODItMC4xMDYsMS4zNzUtMC4xNzgsMi4wODctMC4xNzgNCgkJCWMwLjY3LDAsMS4zMjUsMC4wNjUsMS45NywwLjE2YzQuMjgyLDAuNjI4LDcuOTI1LDMuMjUzLDkuOTI0LDYuOTEzYzEuMDUsMS45MjMsMS42NDcsNC4xMjYsMS42NDcsNi40NjkNCgkJCUMzNi40OTUsMzMuNiwzMC40MjEsMzkuNjc0LDIyLjk1NCwzOS42NzR6Ii8+DQoJCTxwYXRoIGQ9Ik0zMC41NCwyOS4zbC01LjE2Ni0zLjE5Yy0wLjEwNy0wLjYwNC0wLjQzNC0xLjEyNS0wLjg5My0xLjQ5NGwwLjIzNi02LjQ4MmMwLjAyOS0wLjgyOC0wLjYxNy0xLjUyMy0xLjQ0NC0xLjU1NA0KCQkJYy0wLjgyNS0wLjAzOC0xLjUyMywwLjYxNi0xLjU1NCwxLjQ0NGwtMC4yMzcsNi40ODljLTAuNjQxLDAuNDUyLTEuMDYzLDEuMTk2LTEuMDYzLDIuMDQxYzAsMS4zODEsMS4xMTksMi40OTksMi41LDIuNDk5DQoJCQljMC4zOTMsMCwwLjc2LTAuMDk5LDEuMDktMC4yNmw0Ljk1NSwzLjA2MmMwLjI0NiwwLjE1LDAuNTE5LDAuMjIzLDAuNzg3LDAuMjIzYzAuNTAzLDAsMC45OTMtMC4yNTIsMS4yNzgtMC43MTENCgkJCUMzMS40NjUsMzAuNjYsMzEuMjQ1LDI5LjczNiwzMC41NCwyOS4zeiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K";
      case 'water':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB2aWV3Qm94PSIwIDAgNTEyIDUxMiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNTEyIDUxMjsiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxwYXRoIGQ9Ik00NDMuODgyLDUuMjhDNDQwLjg0MiwxLjkyLDQzNi41NTQsMCw0MzIuMDEsMGgtMzUyYy00LjUxMiwwLTguODMyLDEuOTItMTEuODcyLDUuMjgNCgkJCWMtMy4wMDgsMy4zMjgtNC41MTIsNy44MDgtNC4wNjQsMTIuMzJsNDgsNDgwYzAuODMyLDguMTkyLDcuNzEyLDE0LjQsMTUuOTM2LDE0LjRoMjU2YzguMjI0LDAsMTUuMTA0LTYuMjA4LDE1LjkwNC0xNC40bDQ4LTQ4MA0KCQkJQzQ0OC4zOTQsMTMuMDg4LDQ0Ni45MjIsOC42MDgsNDQzLjg4Miw1LjI4eiBNNDAxLjI5LDE2Mi40OTZjLTQwLjY3MiwxMy4xNTItOTMuNiwxOS4yMzItMTM1LjEzNi0xNC44NDgNCgkJCWMtNTIuMDY0LTQyLjcyLTExNS44NzItMzUuMzYtMTU5LjEzNi0yMi40OTZMOTcuNzA2LDMyaDMxNi42MDhMNDAxLjI5LDE2Mi40OTZ6Ii8+DQoJPC9nPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPC9zdmc+DQo=";
      case 'exercise':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE4LjEuMSwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB2aWV3Qm94PSIwIDAgNjEuODU4IDYxLjg1OCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNjEuODU4IDYxLjg1ODsiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxwYXRoIHN0eWxlPSJmaWxsOiMwMTAwMDI7IiBkPSJNNTAuMDk3LDAuMDE0Yy05LjkxNywwLjg3NC0xNy4yMzUsNS44MjQtMjEuNTAxLDEyLjk4Yy0yLjg1OSwzLjU4NC04LjU3LDE0LjUyNi0xMC42NDcsMjAuMjU0DQoJCQljLTMuNzY2LDcuMTIzLTcuMDUsMTUuNTk4LTkuNjIsMjMuMjM4Yy0xLjU3MSw0LjY3Miw1LjQ4Myw3LjcyLDcuMDYzLDMuMDI3YzEuOTIyLTUuNzE2LDQuMjQ0LTExLjg5Niw2Ljg2OC0xNy42MzENCgkJCWMyLjYwNCw1LjgyOCw1LjI1LDExLjYzNyw4LjA5MSwxNy4zNTRjMi4yMDIsNC40MzgsOC44MjgsMC41NDYsNi42MzQtMy44NzdjLTIuOTI1LTUuODg1LTUuNjQyLTExLjg2NC04LjMxOS0xNy44NjMNCgkJCWMwLjAzNC0wLjExNiwwLjA3Ny0wLjIyOSwwLjExMy0wLjM0NGMwLjQ0NiwwLjEyNywwLjkzOCwwLjE2NiwxLjQ4LDAuMDYzYzQuMDk2LTAuNzY5LDguMTkyLTEuNTM2LDEyLjI5MS0yLjMwNQ0KCQkJYzEuNzUxLTAuMzI5LDIuNDIyLTIuMjQ1LDIuMTQ2LTMuNzc5Yy0wLjgyOC00LjU5Ny0zLjQ0Ny03Ljc5NS02LjcwNy0xMC44MjFjLTAuNDg0LTEuNjQ2LTIuMDk4LTMuMTAyLTMuODg5LTQuNTQ5DQoJCQljMy42MzEtNS44Nyw5LjU1OS05LjA1NiwxNy4yNzUtOS43MzZDNTUuMzEzLDUuNjgsNTQuMDAxLTAuMzI5LDUwLjA5NywwLjAxNHogTTM1LjE3MywyNi4xNDMNCgkJCWMxLjAxMywxLjA1NCwxLjg3NSwyLjE2MywyLjUyNiwzLjQ0N2MtMS45ODIsMC4zNzItMy45NjUsMC43NDMtNS45NDcsMS4xMTVDMzIuNzUyLDI5LjA5NSwzMy45MDMsMjcuNTc1LDM1LjE3MywyNi4xNDN6Ii8+DQoJCTxjaXJjbGUgc3R5bGU9ImZpbGw6IzAxMDAwMjsiIGN4PSI0My42NTMiIGN5PSIxNS42MzUiIHI9IjUuMjc1Ii8+DQoJPC9nPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPC9zdmc+DQo=";
      case 'breakfast':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjQ1LjY5MnB4IiBoZWlnaHQ9IjQ1LjY5MXB4IiB2aWV3Qm94PSIwIDAgNDUuNjkyIDQ1LjY5MSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNDUuNjkyIDQ1LjY5MTsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxwYXRoIGQ9Ik0yOS40MywyNi42NDVjLTAuMzY5LTAuNDI1LTAuOTA2LTAuNzg1LTEuNDctMC43ODVIMTcuNzg5Yy0wLjU2NCwwLTEuMTAxLDAuMzYtMS40NzEsMC43ODUNCgkJCWMtMC4zNjksMC40MjYtMC41MzUsMS4wNDktMC40NTYsMS42MDdsMS43MDEsMTEuOTE3YzAuMTM3LDAuOTU4LDAuOTU3LDEuNjkyLDEuOTI0LDEuNjkyaDYuNzczYzAuOTY3LDAsMS43ODktMC43MDUsMS45MjQtMS42NjQNCgkJCWwxLjcwMS0xMS45NDhDMjkuOTY0LDI3LjY5MSwyOS43OTcsMjcuMDcsMjkuNDMsMjYuNjQ1eiIvPg0KCQk8cGF0aCBkPSJNMTQuMDY2LDMwLjQyOGMtMC42MTgtMC4yNzEtMS4zMzMtMC4yMDUtMS44ODksMC4xNzhsLTQuNTU0LDMuMTQxYy0wLjg4NSwwLjYwOS0xLjEwNiwxLjgyLTAuNDk3LDIuNzAzbDMuOTMxLDUuNzAxDQoJCQljMC41NjgsMC44MjQsMS42NzEsMS4wODIsMi41NDcsMC41OTRsMS43MDEtMC45NDhjMC42OTgtMC4zOSwxLjA4OC0xLjE2OCwwLjk3OS0xLjk2MWwtMS4wNzgtNy44OTINCgkJCUMxNS4xMTcsMzEuMjc1LDE0LjY4NCwzMC43MDEsMTQuMDY2LDMwLjQyOHoiLz4NCgkJPHBhdGggZD0iTTcuNzg0LDM5Ljg1NWMtMC4yMTctMC4yOTEtMC41ODUtMC40MjctMC45MzktMC4zNDZjLTAuMzUzLDAuMDgxLTAuNjI3LDAuMzYxLTAuNjk4LDAuNzE3bC0wLjg3OCw0LjM2MQ0KCQkJYy0wLjA3MiwwLjM1NywwLjA3NCwwLjcyMywwLjM3LDAuOTM0YzAuMjk5LDAuMjExLDAuNjksMC4yMjcsMS4wMDQsMC4wNDFsMi44Ny0xLjcwN2MwLjIyNS0wLjEzMywwLjM4My0wLjM1NSwwLjQzNC0wLjYxMQ0KCQkJYzAuMDUyLTAuMjU4LTAuMDA5LTAuNTIzLTAuMTY2LTAuNzMyTDcuNzg0LDM5Ljg1NXoiLz4NCgkJPHBhdGggZD0iTTM4LjA2NywzMy43NDZsLTQuNTU1LTMuMTQxYy0wLjU1Ny0wLjM4My0xLjI3MS0wLjQ1MS0xLjg5LTAuMTc4Yy0wLjYxNywwLjI3MS0xLjA0OSwwLjg0OC0xLjE0MiwxLjUxNmwtMS4wNzcsNy44OTINCgkJCWMtMC4xMDgsMC43OTMsMC4yOCwxLjU3MSwwLjk3OSwxLjk2MWwxLjcsMC45NDhjMC44NzYsMC40ODgsMS45NzksMC4yMywyLjU0Ny0wLjU5NGwzLjkzMS01LjcwMQ0KCQkJQzM5LjE3MiwzNS41NjYsMzguOTUsMzQuMzU1LDM4LjA2NywzMy43NDZ6Ii8+DQoJCTxwYXRoIGQ9Ik00MC40MjIsNDQuNTg3bC0wLjg3OC00LjM2Yy0wLjA3MS0wLjM1Ny0wLjM0NS0wLjYzNy0wLjY5OC0wLjcxOHMtMC43MjMsMC4wNTYtMC45MzgsMC4zNDVsLTEuOTk2LDIuNjU1DQoJCQljLTAuMTU2LDAuMjA5LTAuMjE4LDAuNDc2LTAuMTY2LDAuNzMxYzAuMDUxLDAuMjU3LDAuMjA5LDAuNDc5LDAuNDM1LDAuNjEzbDIuODY5LDEuNzA3YzAuMzEzLDAuMTg2LDAuNzA1LDAuMTcsMS4wMDQtMC4wNDENCgkJCUM0MC4zNSw0NS4zMTEsNDAuNDk1LDQ0Ljk0Myw0MC40MjIsNDQuNTg3eiIvPg0KCQk8cGF0aCBkPSJNMjMuMDE4LDIzLjk0NWMxLjQzMywwLDEzLjk4OC0wLjEyMywxMy45ODgtNC40MWMwLTEuOTEtMi40OTUtMi45OTMtNS4zODktMy42MDZjMC4xMTItMC4xODUsMC4yMTgtMC4zNzYsMC4zMTctMC41Nw0KCQkJbDEuOTg1LTAuMTc4YzEuNTkzLTAuMDM4LDIuOTIxLTEuMjM2LDMuMDk5LTIuNzk5bDAuMzk4LTMuNDAyYzAuMTAyLTAuODgxLTAuMTU2LTEuNjA0LTAuNzktMi4zMTQNCgkJCWMtMC43MjgtMC44MTMtMS43MjYtMC43NjgtMi4zODctMC43NjhoLTAuOTA3bDAuMTk1LTIuNzk2YzAuMDAyLTAuMDE0LDAuMDAyLTAuMDU2LDAuMDAzLTAuMDY5DQoJCQljMC4wMDEtMC4wMjMsMC4wMDMtMC4wNjIsMC4wMDMtMC4wODVDMzMuNTM0LDEuMzI0LDI4LjgyNSwwLDIzLjAxNywwUzEyLjUwMiwxLjMxNCwxMi41MDIsMi45MzljMCwwLjAyNCwwLDAuMDQ2LDAuMDAzLDAuMDY5DQoJCQljMCwwLjAxNCwwLDAuMDI2LDAuMDAyLDAuMDM5bDAuNjMyLDguODQ0YzAuMTA0LDEuNDc2LDAuNTYsMi44NDgsMS4yNzgsNC4wMzljLTIuODkxLDAuNjE0LTUuMzg4LDEuNjk3LTUuMzg4LDMuNjA2DQoJCQlDOS4wMywyMy44MjIsMjEuNTg2LDIzLjk0NSwyMy4wMTgsMjMuOTQ1eiBNMzIuODk3LDEyLjAwN0wzMy4yLDcuOTA2aDEuMzMzYzAuODMyLDAsMS4wODYsMC44MTEsMS4wNTMsMS4xMDNsLTAuNDExLDMuMjg3DQoJCQljLTAuMDc4LDAuNjY3LTAuNjAyLDAuOTQ1LTEuMjk2LDAuOTczYy0wLjI5OCwwLjAxMi0xLjE5LDAuMDgyLTEuMTksMC4wODJDMzIuODExLDEyLjg0OSwzMi44NTgsMTIuNTQxLDMyLjg5NywxMi4wMDd6DQoJCQkgTTIzLjAxOCwyLjE0YzMuODA4LDAsNi44OTQsMC42NDYsNi44OTQsMS40NDRjMCwwLjgtMy4wODYsMS40NDYtNi44OTQsMS40NDZjLTMuODA2LDAtNi44OTQtMC42NDYtNi44OTQtMS40NDYNCgkJCUMxNi4xMjQsMi43ODcsMTkuMjExLDIuMTQsMjMuMDE4LDIuMTR6IE0xNi4yNDIsMTcuODg1YzEuNTk2LDEuMzg0LDMuNjc2LDIuMDA5LDUuOTM4LDIuMDA5aDEuNjc1DQoJCQljMi4yNjQsMCw0LjM0NC0wLjYyNSw1LjkzOS0yLjAwOWMyLjUxLDAuNDExLDQuMTIyLDEuMSw0LjY0NSwxLjU0N2MtMC44OTksMC43NzEtNS4wMzQsMi4wMDMtMTEuNDIsMi4wMDMNCgkJCWMtNi4zODQsMC0xMC41MjEtMS4yNTItMTEuNDE4LTIuMDI0QzEyLjExOSwxOC45NjMsMTMuNzMsMTguMjk2LDE2LjI0MiwxNy44ODV6Ii8+DQoJPC9nPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPC9zdmc+DQo=";
      case 'work':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9Ijc3OS4xMXB4IiBoZWlnaHQ9Ijc3OS4xMXB4IiB2aWV3Qm94PSIwIDAgNzc5LjExIDc3OS4xMSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNzc5LjExIDc3OS4xMTsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxwYXRoIGQ9Ik02NjIuOTE0LDYzMi4zNTFINTMwLjA3SDI1NC40NzRjLTExLjQ5LDAtMjAuODA2LDkuMzE1LTIwLjgwNiwyMC44MDZ2MTIuODA1YzAsMTEuNDksOS4zMTUsMjAuODEsMjAuODA2LDIwLjgxaDI3NS41OTgNCgkJCWgxMzIuODQ0aDY4Ljgydi01NC40MThMNjYyLjkxNCw2MzIuMzUxTDY2Mi45MTQsNjMyLjM1MXoiLz4NCgkJPGNpcmNsZSBjeD0iMjExLjE4NyIgY3k9IjE4OS42MjUiIHI9IjExNS4xOSIvPg0KCQk8cGF0aCBkPSJNNDkyLjIzNCw0NzIuMTQ3bC0yNjMuOTY5LTAuMTQ2bC02LjI1LTAuMDJ2LTMwLjYzMmwtMC4yMTctMjUuNjRjMC02MS4yNDUtNDkuNjUxLTExMC44OTgtMTEwLjg5OS0xMTAuODk4DQoJCQljLTIuMDc1LDAtNC4xMzYsMC4wNy02LjE4NCwwLjE4MmwtMC4xNTYtMC4xODJDNDYuODEzLDMwNC44MTMsMCwzNTEuNjI1LDAsNDA5LjM3MnYyOTUuMzAzaDIyMi4wMTVWNTc4Ljg3NmwtMi45MzctMC4yMzENCgkJCWMtMC4yMDktMC4wMTktMC4yNjEsMC4wMDItMC4zOTEsMC4wMDJjLTE1LjAyMSwwLTI5LjQxNy02LjMyNC0zOS41NjItMTcuMzk5bC05MC4xMTItOTguMzYzDQoJCQljLTIuODczLTMuMTM1LTIuNjYtOC4wMDMsMC40NzYtMTAuODc0YzMuMTMzLTIuODczLDguMDAzLTIuNjU5LDEwLjg3NCwwLjQ3Nmw5MC4xMTEsOTguMzYyDQoJCQljNy4zMjIsNy45OTMsMTcuNjQ4LDEyLjg4MSwyOC41MjEsMTMuMDM5YzAuNzIzLDAuMDEsMTAuNTk0LDAuMTUyLDEwLjU5NCwwLjE1MmgyNjIuNjQ1YzI1LjM3NSwwLDQ1Ljk0Ny0yMC41NzEsNDUuOTQ3LTQ1Ljk0Nw0KCQkJQzUzOC4xODIsNDkyLjcxNyw1MTcuNjA5LDQ3Mi4xNDcsNDkyLjIzNCw0NzIuMTQ3eiIvPg0KCQk8cGF0aCBkPSJNNzY1LjE5NywzMDkuMTExYy0xMC43NDQtMy42ODEtMjIuNDM5LDIuMDQ5LTI2LjEyMywxMi43OTRsLTg3LjIwOSwyNTQuNTlIMzM5LjgwM2MtMTEuMzU2LDAtMjAuNTY3LDkuMjA2LTIwLjU2NywyMC41NjQNCgkJCXM5LjIxMSwyMC41NjYsMjAuNTY3LDIwLjU2NmgzMjYuNTA3YzYuOTk0LDAsMTMuMTY4LTMuNTAzLDE2Ljg3OS04Ljg0MWMxLjI4My0xLjY5NCwyLjMzLTMuNjA3LDMuMDU5LTUuNzI5TDc3OCwzMzUuMjI1DQoJCQlDNzgxLjY3LDMyNC40ODYsNzc1Ljk0NywzMTIuNzksNzY1LjE5NywzMDkuMTExeiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K";
      case 'car':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE4LjEuMSwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB2aWV3Qm94PSIwIDAgNjEyLjAwMSA2MTIuMDAxIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA2MTIuMDAxIDYxMi4wMDE7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxwYXRoIGQ9Ik01ODkuMzMzLDI3Ni4wMzNjLTExLjIzNC0zLjc1Ni04OS4zNzgtMjAuODM0LTg5LjM3OC0yMC44MzRzLTE0NC44Ni04Mi4zNzUtMTYyLjI0NS04Mi4zNzVzLTEzNi42MzksMC4wNTMtMTM2LjYzOSwwLjA1Mw0KCQljLTI5LjEzNywwLTUzLjQ4NywyMi4yMDMtODEuNjgsNDcuOTA5Yy0xMy4yODcsMTIuMTEyLTI3Ljk1MywyNS40NDItNDQuMTMsMzcuMjk5bC02MC4yNDksOC4wMTENCgkJQzYuMzA2LDI2OC44NzIsMCwyNzcuMDE4LDAsMjg2LjY0M3Y2OS4wM2MwLDExLjkxMyw5LjY1NiwyMS41NzEsMjEuNTcsMjEuNTcxaDQxLjQwMWMzLjAwNywzNC42NSwzMi4xNTMsNjEuOTMyLDY3LjU3LDYxLjkzMg0KCQljMzUuNDE1LDAsNjQuNTYzLTI3LjI4Myw2Ny41Ny02MS45MzFoMTk3LjY4N2MzLjAwNywzNC42NSwzMi4xNTMsNjEuOTMxLDY3LjU3LDYxLjkzMXM2NC41NjMtMjcuMjgzLDY3LjU3LTYxLjkzMWgzNC4wMTMNCgkJYzI2Ljk1LDAsNDAuMTE5LTExLjY0LDQzLjQyNi0yMi41NjZDNjE2LjczOSwzMjcuMDMsNjEwLjcyNCwyODMuMTg1LDU4OS4zMzMsMjc2LjAzM3ogTTEzMC41NDEsNDA2LjQ4DQoJCWMtMTkuMzgsMC0zNS4xNDgtMTUuNzY2LTM1LjE0OC0zNS4xNDZzMTUuNzY2LTM1LjE0OCwzNS4xNDgtMzUuMTQ4YzE5LjM4LDAsMzUuMTQ2LDE1Ljc2NiwzNS4xNDYsMzUuMTQ4DQoJCUMxNjUuNjg4LDM5MC43MTQsMTQ5LjkyMSw0MDYuNDgsMTMwLjU0MSw0MDYuNDh6IE0yNjEuMDA4LDI1NS4yMDFIMTQzLjEzNGM4LjUyNi02LjczNiwxNi40MDktMTMuODg2LDIzLjY3MS0yMC41MDUNCgkJYzE5LjA4Ni0xNy40MDIsMzUuNTctMzIuNDMyLDU1LjI5NC0zMi40MzJjMCwwLDE3Ljg1LTAuMDA4LDM4LjkxLTAuMDE3VjI1NS4yMDF6IE0yODkuNzExLDIwMi4yMzYNCgkJYzE0LjU4OC0wLjAwNSwyNy41OTItMC4wMDksMzQuMTE2LTAuMDA5YzE2LjI0NSwwLDgyLjEzNSwzOC4yNjQsMTA2Ljg2NCw1Mi45NzVoLTE0MC45OEwyODkuNzExLDIwMi4yMzZMMjg5LjcxMSwyMDIuMjM2eg0KCQkgTTQ2My4zNjcsNDA2LjQ4Yy0xOS4zOCwwLTM1LjE0Ni0xNS43NjYtMzUuMTQ2LTM1LjE0NnMxNS43NjYtMzUuMTQ4LDM1LjE0Ni0zNS4xNDhjMTkuMzgsMCwzNS4xNDgsMTUuNzY2LDM1LjE0OCwzNS4xNDgNCgkJQzQ5OC41MTUsMzkwLjcxNCw0ODIuNzQ3LDQwNi40OCw0NjMuMzY3LDQwNi40OHoiLz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K";
      case 'coffee':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDUxMS45OTkgNTExLjk5OSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNTExLjk5OSA1MTEuOTk5OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8Zz4NCgk8Zz4NCgkJPHBhdGggZD0iTTE3OS4zNjEsOTkuOTAzYy0xMS40MS0xMS40MS0xNi40NTQtMTcuMDA1LTE2LjQ1Mi0zMC4wODljLTAuMDAyLTEzLjA3OSw1LjA0NC0xOC42NzQsMTYuNDU3LTMwLjA4OQ0KCQkJYzkuMDg5LTkuMDg3LDkuMDg5LTIzLjgyLDAuMDAyLTMyLjkwOWMtOS4wODctOS4wOS0yMy44MjUtOS4wODctMzIuOTE0LTAuMDAyYy0xMi42OTksMTIuNjk4LTMwLjA5NSwzMC4wOS0zMC4wOSw2Mi45OTkNCgkJCWMtMC4wMDUsMzIuOTE0LDE3LjM4OCw1MC4zMDUsMzAuMDg5LDYzLjAwMWMxMS40MTEsMTEuNDEzLDE2LjQ1NywxNy4wMTEsMTYuNDU3LDMwLjA5MmMwLDEyLjg1NCwxMC40MiwyMy4yNzMsMjMuMjczLDIzLjI3Mw0KCQkJczIzLjI3My0xMC40MTgsMjMuMjczLTIzLjI3M0MyMDkuNDU0LDEyOS45OTMsMTkyLjA2MiwxMTIuNjAxLDE3OS4zNjEsOTkuOTAzeiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNMjg3Ljk2Nyw5OS45MDNjLTExLjQxLTExLjQxLTE2LjQ1NC0xNy4wMDUtMTYuNDUyLTMwLjA4OWMtMC4wMDItMTMuMDc5LDUuMDQ0LTE4LjY3NCwxNi40NTctMzAuMDg5DQoJCQljOS4wODktOS4wODcsOS4wODktMjMuODIsMC4wMDItMzIuOTA5Yy05LjA4Ny05LjA5LTIzLjgyNS05LjA4Ny0zMi45MTQtMC4wMDJjLTEyLjY5OSwxMi42OTgtMzAuMDk1LDMwLjA5Mi0zMC4wOSw2Mi45OTkNCgkJCWMtMC4wMDUsMzIuOTE0LDE3LjM4OCw1MC4zMDUsMzAuMDg5LDYzLjAwMWMxMS40MTEsMTEuNDEzLDE2LjQ1NywxNy4wMTEsMTYuNDU3LDMwLjA5MmMwLDEyLjg1NCwxMC40MiwyMy4yNzMsMjMuMjczLDIzLjI3Mw0KCQkJczIzLjI3My0xMC40MTgsMjMuMjczLTIzLjI3M0MzMTguMDYxLDEyOS45OTMsMzAwLjY2OCwxMTIuNjAxLDI4Ny45NjcsOTkuOTAzeiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNMzgxLjQwMSw0MDMuMzkzaDIxLjk5M2MwLjAyMiwwLDAuMDM5LTAuMDAzLDAuMDYxLTAuMDAzYzQ3LjAyMy0wLjAzMSw4NS4yNzMtMzguMjk4LDg1LjI3My04NS4zMzENCgkJCWMwLTQ3LjA1My0zOC4yODEtODUuMzM0LTg1LjMzNC04NS4zMzRoLTMxLjAzSDYyLjA2Yy0xMi44NTMsMC0yMy4yNzMsMTAuNDIyLTIzLjI3MywyMy4yNzN2NzcuNTc2DQoJCQljMCw1Mi4xOTMsMjIuNTI4LDk5LjIyMSw1OC4zNywxMzEuODc5SDQ2LjU0NWMtMTIuODUzLDAtMjMuMjczLDEwLjQxOC0yMy4yNzMsMjMuMjczYzAsMTIuODUxLDEwLjQyLDIzLjI3MywyMy4yNzMsMjMuMjczDQoJCQloMTcwLjY2N2gxNzAuNjY3YzEyLjg1MywwLDIzLjI3My0xMC40MjIsMjMuMjczLTIzLjI3M2MwLTEyLjg1NC0xMC40Mi0yMy4yNzMtMjMuMjczLTIzLjI3M2gtNTAuNjEyDQoJCQlDMzU2LjEwNCw0NDguMjg5LDM3MS4yNTcsNDI3LjE1OCwzODEuNDAxLDQwMy4zOTN6IE0zOTUuNjM3LDMzMy41NzV2LTU0LjMwM2g3Ljc1OGMyMS4zODgsMCwzOC43ODgsMTcuNCwzOC43ODgsMzguNzg4DQoJCQlzLTE3LjQsMzguNzg4LTM4Ljc4OCwzOC43ODhjLTAuMDExLDAtMC4wMiwwLTAuMDMxLDBoLTkuMjQ1QzM5NS4xMTUsMzQ5LjIyOSwzOTUuNjM3LDM0MS40NjEsMzk1LjYzNywzMzMuNTc1eiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K";
      case 'dinner':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDUxMiA1MTIiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDUxMiA1MTI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNMjY0LjE4MSw3Ni45MDljLTkzLjY0NiwwLTE2OS41NjEsNzUuOTE1LTE2OS41NjEsMTY5LjU2MXM3NS45MTUsMTY5LjU2MSwxNjkuNTYxLDE2OS41NjENCgkJCXMxNjkuNTYxLTc1LjkxNSwxNjkuNTYxLTE2OS41NjFTMzU3LjgyNyw3Ni45MDksMjY0LjE4MSw3Ni45MDl6IE0yNjQuMTgsMzc1LjEyOWMtNzAuOTQyLDAtMTI4LjY1OC01Ny43MTYtMTI4LjY1OC0xMjguNjU4DQoJCQlzNTcuNzE2LTEyOC42NTgsMTI4LjY1OC0xMjguNjU4czEyOC42NTgsNTcuNzE2LDEyOC42NTgsMTI4LjY1OFMzMzUuMTIzLDM3NS4xMjksMjY0LjE4LDM3NS4xMjl6Ii8+DQoJPC9nPg0KPC9nPg0KPGc+DQoJPGc+DQoJCTxwYXRoIGQ9Ik0yNjQuMTgsMTUyLjI5OWMtNTEuOTI2LDAtOTQuMTcxLDQyLjI0NS05NC4xNzEsOTQuMTcxYzAsNTEuOTI2LDQyLjI0NSw5NC4xNzEsOTQuMTcxLDk0LjE3MQ0KCQkJYzUxLjkyNiwwLDk0LjE3MS00Mi4yNDUsOTQuMTcxLTk0LjE3MVMzMTYuMTA3LDE1Mi4yOTksMjY0LjE4LDE1Mi4yOTl6Ii8+DQoJPC9nPg0KPC9nPg0KPGc+DQoJPGc+DQoJCTxwYXRoIGQ9Ik01MDEuMzE1LDI2MC42ODdWNTQuNjRjMC0xLjk4OC0xLjI2OS0zLjc1NS0zLjE1NS00LjM5Yy0xLjg4NC0wLjYzNC0zLjk2MywwLjAwNy01LjE2NiwxLjU5MQ0KCQkJYy0yNS43MDgsMzMuOTAzLTM5LjYyMiw3NS4yODMtMzkuNjIyLDExNy44M3Y3NS4zNzhjMCw4LjY0NSw3LjAwOCwxNS42NTQsMTUuNjU0LDE1LjY1NGg2LjUyNg0KCQkJYy02LjQzMyw2Ni40NDMtMTAuNjg0LDE1OS4zNy0xMC42ODQsMTcwLjI1MWMwLDE3LjE0MiwxMC41NTEsMzEuMDM4LDIzLjU2NiwzMS4wMzhjMTMuMDE1LDAsMjMuNTY2LTEzLjg5NywyMy41NjYtMzEuMDM4DQoJCQlDNTEyLDQyMC4wNzIsNTA3Ljc0OSwzMjcuMTMsNTAxLjMxNSwyNjAuNjg3eiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KCTxnPg0KCQk8cGF0aCBkPSJNNjguNDE3LDIxOS44NDNjMTMuMDQyLTcuOSwyMS43NTktMjIuMjI0LDIxLjc1OS0zOC41ODZsLTYuNDYtMTA1LjYyMWMtMC4yNDctNC4wMjYtMy41ODQtNy4xNjUtNy42MTgtNy4xNjUNCgkJCWMtNC4zNjMsMC03LjgzOSwzLjY1NS03LjYyMiw4LjAxbDQuMjAxLDg0LjcwOWMwLDQuNzYyLTMuODYxLDguNjIxLTguNjIxLDguNjIxYy00Ljc2MSwwLTguNjIxLTMuODYxLTguNjIxLTguNjIxbC0yLjA5OS04NC42NzQNCgkJCWMtMC4xMTEtNC40NzUtMy43Ny04LjA0NC04LjI0Ny04LjA0NGMtNC40NzcsMC04LjEzNSwzLjU3LTguMjQ3LDguMDQ0bC0yLjA5OSw4NC42NzRjMCw0Ljc2Mi0zLjg2MSw4LjYyMS04LjYyMSw4LjYyMQ0KCQkJYy00Ljc2MSwwLTguNjIxLTMuODYxLTguNjIxLTguNjIxbDQuMjAxLTg0LjcwOWMwLjIxNi00LjM1Ny0zLjI2Mi04LjAxLTcuNjIyLTguMDFjLTQuMDM0LDAtNy4zNzEsMy4xMzktNy42MTcsNy4xNjVMMCwxODEuMjU4DQoJCQljMCwxNi4zNjIsOC43MTYsMzAuNjg1LDIxLjc1OSwzOC41ODZjOC40ODgsNS4xNDEsMTMuMjIsMTQuNzUzLDEyLjEyNiwyNC42MTdjLTcuMzYzLDY2LjM1OC0xMi4zNjMsMTc0LjY5My0xMi4zNjMsMTg2LjQ5NA0KCQkJYzAsMTcuMTQyLDEwLjU1MSwzMS4wMzgsMjMuNTY2LDMxLjAzOGMxMy4wMTUsMCwyMy41NjYtMTMuODk3LDIzLjU2Ni0zMS4wMzhjMC0xMS44MDEtNS4wMDEtMTIwLjEzNi0xMi4zNjMtMTg2LjQ5NA0KCQkJQzU1LjE5NiwyMzQuNjAyLDU5LjkzMywyMjQuOTgyLDY4LjQxNywyMTkuODQzeiIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K";
      case 'book':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDI5Ni45OTkgMjk2Ljk5OSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjk2Ljk5OSAyOTYuOTk5OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8Zz4NCgk8Zz4NCgkJPGc+DQoJCQk8cGF0aCBkPSJNNDUuNDMyLDM1LjA0OWMtMC4wMDgsMC0wLjAxNywwLTAuMDI1LDBjLTIuODA5LDAtNS40NTEsMS4wOTUtNy40NDYsMy4wODVjLTIuMDE3LDIuMDEyLTMuMTI4LDQuNjkxLTMuMTI4LDcuNTQzDQoJCQkJdjE1OS4zNjVjMCw1Ljg0NCw0Ljc3MywxMC42MSwxMC42NDEsMTAuNjI1YzI0LjczOCwwLjA1OSw2Ni4xODQsNS4yMTUsOTQuNzc2LDM1LjEzNlY4NC4wMjNjMC0xLjk4MS0wLjUwNi0zLjg0Mi0xLjQ2MS01LjM4Mg0KCQkJCUMxMTUuMzIyLDQwLjg0OSw3MC4yMjYsMzUuMTA3LDQ1LjQzMiwzNS4wNDl6Ii8+DQoJCQk8cGF0aCBkPSJNMjYyLjE2NywyMDUuMDQyVjQ1LjY3NmMwLTIuODUyLTEuMTExLTUuNTMxLTMuMTI4LTcuNTQzYy0xLjk5NS0xLjk5LTQuNjM5LTMuMDg1LTcuNDQ1LTMuMDg1Yy0wLjAwOSwwLTAuMDE4LDAtMC4wMjYsMA0KCQkJCWMtMjQuNzkzLDAuMDU5LTY5Ljg4OSw1LjgwMS05My4zNTcsNDMuNTkzYy0wLjk1NSwxLjU0LTEuNDYsMy40MDEtMS40Niw1LjM4MnYxNjYuNzc5DQoJCQkJYzI4LjU5Mi0yOS45MjEsNzAuMDM4LTM1LjA3Nyw5NC43NzYtMzUuMTM2QzI1Ny4zOTQsMjE1LjY1MSwyNjIuMTY3LDIxMC44ODUsMjYyLjE2NywyMDUuMDQyeiIvPg0KCQkJPHBhdGggZD0iTTI4Ni4zNzMsNzEuODAxaC03LjcwNnYxMzMuMjQxYzAsMTQuOTIxLTEyLjE1NywyNy4wODgtMjcuMTAxLDI3LjEyNWMtMjAuOTgzLDAuMDUtNTUuNTgxLDQuMTUzLTgwLjA4NCwyNy4zNDQNCgkJCQljNDIuMzc4LTEwLjM3Niw4Ny4wNTItMy42MzEsMTEyLjUxMiwyLjE3MWMzLjE3OSwwLjcyNCw2LjQ2NC0wLjAyNCw5LjAxMS0yLjA1NGMyLjUzOC0yLjAyNSwzLjk5NC01LjA1MiwzLjk5NC04LjMwMVY4Mi40MjcNCgkJCQlDMjk3LDc2LjU2OCwyOTIuMjMyLDcxLjgwMSwyODYuMzczLDcxLjgwMXoiLz4NCgkJCTxwYXRoIGQ9Ik0xOC4zMzIsMjA1LjA0MlY3MS44MDFoLTcuNzA2QzQuNzY4LDcxLjgwMSwwLDc2LjU2OCwwLDgyLjQyN3YxNjguODk3YzAsMy4yNSwxLjQ1Niw2LjI3NiwzLjk5NCw4LjMwMQ0KCQkJCWMyLjU0NSwyLjAyOSw1LjgyNywyLjc4LDkuMDExLDIuMDU0YzI1LjQ2LTUuODAzLDcwLjEzNS0xMi41NDcsMTEyLjUxMS0yLjE3MWMtMjQuNTAyLTIzLjE5LTU5LjEtMjcuMjkyLTgwLjA4My0yNy4zNDINCgkJCQlDMzAuNDksMjMyLjEzLDE4LjMzMiwyMTkuOTYzLDE4LjMzMiwyMDUuMDQyeiIvPg0KCQk8L2c+DQoJPC9nPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPC9zdmc+DQo=";
      case 'home':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE4LjEuMSwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB2aWV3Qm94PSIwIDAgMjcuMDIgMjcuMDIiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDI3LjAyIDI3LjAyOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8Zz4NCgk8cGF0aCBzdHlsZT0iZmlsbDojMDMwMTA0OyIgZD0iTTMuNjc0LDI0Ljg3NmMwLDAtMC4wMjQsMC42MDQsMC41NjYsMC42MDRjMC43MzQsMCw2LjgxMS0wLjAwOCw2LjgxMS0wLjAwOGwwLjAxLTUuNTgxDQoJCWMwLDAtMC4wOTYtMC45MiwwLjc5Ny0wLjkyaDIuODI2YzEuMDU2LDAsMC45OTEsMC45MiwwLjk5MSwwLjkybC0wLjAxMiw1LjU2M2MwLDAsNS43NjIsMCw2LjY2NywwDQoJCWMwLjc0OSwwLDAuNzE1LTAuNzUyLDAuNzE1LTAuNzUyVjE0LjQxM2wtOS4zOTYtOC4zNThsLTkuOTc1LDguMzU4QzMuNjc0LDE0LjQxMywzLjY3NCwyNC44NzYsMy42NzQsMjQuODc2eiIvPg0KCTxwYXRoIHN0eWxlPSJmaWxsOiMwMzAxMDQ7IiBkPSJNMCwxMy42MzVjMCwwLDAuODQ3LDEuNTYxLDIuNjk0LDBsMTEuMDM4LTkuMzM4bDEwLjM0OSw5LjI4YzIuMTM4LDEuNTQyLDIuOTM5LDAsMi45MzksMA0KCQlMMTMuNzMyLDEuNTRMMCwxMy42MzV6Ii8+DQoJPHBvbHlnb24gc3R5bGU9ImZpbGw6IzAzMDEwNDsiIHBvaW50cz0iMjMuODMsNC4yNzUgMjEuMTY4LDQuMjc1IDIxLjE3OSw3LjUwMyAyMy44Myw5Ljc1MiAJIi8+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg==";
      case 'beer':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjIwLjQ5NXB4IiBoZWlnaHQ9IjIwLjQ5NXB4IiB2aWV3Qm94PSIwIDAgMjAuNDk1IDIwLjQ5NSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjAuNDk1IDIwLjQ5NTsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxwYXRoIGQ9Ik0xNi4xOTcsOC41NWgtMC45MTFjLTAuMTg4LDAtMC4zNywwLjAxOS0wLjU0OCwwLjA1MlY2LjU0NWMwLTAuMTEyLTAuMDEzLTAuMjIxLTAuMDMzLTAuMzI3DQoJCQljMC41OTktMC40NDMsMC45OTEtMS4xNDgsMC45OTEtMS45NDhjMC0xLjQ5LTEuMzcyLTIuNjg1LTIuODgzLTIuMzg2Yy0wLjUtMC41MjYtMS4yMTMtMC44MjMtMS45NDYtMC43ODYNCgkJCUMxMC4zOTksMC40Miw5LjYyLDAsOC43ODksMEM4LjExNCwwLDcuNDc2LDAuMjY4LDcuMDA2LDAuNzM0QzYuODgxLDAuNjgzLDYuNzUzLDAuNjQyLDYuNjIzLDAuNjEyTDYuNDU5LDAuNTgNCgkJCUM2LjMzMywwLjU2LDYuMjA3LDAuNTQ5LDYuMDc4LDAuNTQ3Yy0wLjE4OS0wLjAxNS0wLjM3MS0wLjAxNC0wLjU1LDAuMDAxSDUuNDc5djAuMDA0QzQuNDA1LDAuNjYsMy41LDEuMjk2LDMuMTQ1LDIuMTgzDQoJCQlDMi4wOSwyLjM4MywxLjI5LDMuMzEyLDEuMjksNC40MjJjMCwwLjc3NSwwLjM5LDEuNDU4LDAuOTgyLDEuODdDMi4yNiw2LjM3NSwyLjI0Nyw2LjQ1OCwyLjI0Nyw2LjU0NXYxMi4zMDkNCgkJCWMwLDAuOTA1LDAuNzM2LDEuNjQyLDEuNjQxLDEuNjQyaDkuMjA4YzAuOTA1LDAsMS42NDItMC43MzYsMS42NDItMS42NDJWMTYuMzRjMC4xNzgsMC4wMzMsMC4zNiwwLjA1MywwLjU0OCwwLjA1M2gwLjkxMQ0KCQkJYzEuNjU5LDAsMy4wMDktMS4zNTEsMy4wMDktMy4wMXYtMS44MjJDMTkuMjA2LDkuOTAxLDE3Ljg1Niw4LjU1LDE2LjE5Nyw4LjU1eiBNMTMuNjQzLDE4Ljg1NGMwLDAuMzAyLTAuMjQ0LDAuNTQ3LTAuNTQ3LDAuNTQ3DQoJCQlIMy44ODhjLTAuMzAyLDAtMC41NDctMC4yNDUtMC41NDctMC41NDdWNi41NDVjMC0wLjMwMiwwLjI0NS0wLjU0NywwLjU0Ny0wLjU0N2g5LjIwOGMwLjMwMywwLDAuNTQ3LDAuMjQ1LDAuNTQ3LDAuNTQ3VjE4Ljg1NHoNCgkJCSBNMTQuMTMsNS4yOEwxNC4xMyw1LjI4Yy0wLjI4Mi0wLjIzMi0wLjY0LTAuMzc3LTEuMDM0LTAuMzc3SDMuODg4Yy0wLjQxNywwLTAuNzkzLDAuMTYxLTEuMDgzLDAuNDE3DQoJCQlDMi41NDksNS4xMDMsMi4zODQsNC43ODMsMi4zODQsNC40MjJjMC0wLjY1MSwwLjUyOS0xLjE4MiwxLjE4MS0xLjE4NGwwLjQ0OC0wLjAwMkw0LjEsMi43OTdDNC4yMDIsMi4yODMsNC43MzUsMS43NCw1LjU1MiwxLjY0NQ0KCQkJaDAuNjAzYzAuMjQ1LDAuMDE3LDAuNDgxLDAuMDk3LDAuNjg5LDAuMjM0bDAuNDUyLDAuMjk5bDAuMzAzLTAuNDQ5QzcuODY3LDEuMzMyLDguMzEyLDEuMDk0LDguNzksMS4wOTQNCgkJCWMwLjU1NiwwLDEuMDUsMC4zMTUsMS4yOTIsMC44MjNsMC4xODQsMC4zODdsMC40Mi0wLjA4NmMwLjU3MS0wLjExNSwxLjE1NCwwLjEyNCwxLjQ3OCwwLjU5N2wwLjIzOSwwLjM1MmwwLjQtMC4xNDcNCgkJCWMwLjE2MS0wLjA1OSwwLjMxMi0wLjA4OCwwLjQ2MS0wLjA4OGMwLjczNywwLDEuMzM4LDAuNiwxLjMzOCwxLjMzOEMxNC42MDIsNC42NzUsMTQuNDE2LDUuMDM1LDE0LjEzLDUuMjh6IE0xNy41NjUsMTMuMzgzDQoJCQljMCwwLjc1NC0wLjYxMywxLjM2OC0xLjM2OCwxLjM2OGgtMC45MTFjLTAuMTk1LDAtMC4zOC0wLjA0Mi0wLjU0OC0wLjExNnYtNC4zMjZjMC4xNjgtMC4wNzQsMC4zNTMtMC4xMTYsMC41NDgtMC4xMTZoMC45MTENCgkJCWMwLjc1NCwwLDEuMzY4LDAuNjEzLDEuMzY4LDEuMzY4VjEzLjM4M3oiLz4NCgkJPHJlY3QgeD0iMy44ODgiIHk9IjguMDAzIiB3aWR0aD0iOS4yMSIgaGVpZ2h0PSIxMC44NTEiLz4NCgk8L2c+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg==";
      case 'dance':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjI0OC45MTRweCIgaGVpZ2h0PSIyNDguOTE0cHgiIHZpZXdCb3g9IjAgMCAyNDguOTE0IDI0OC45MTQiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDI0OC45MTQgMjQ4LjkxNDsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxwYXRoIGQ9Ik0yMDEuNzExLDQ5LjU4M2MtNS40NiwwLTkuODk1LDMuNzcxLTkuODk1LDguNDE5YzAsNC42NTMsNC40MzUsOC40MTksOS44OTUsOC40MTljNS4zMTYsMCw5LjY0My0zLjU2Niw5Ljg3Ni04LjAzMg0KCQkJYzUuMTA1LTEzLjYsNC4xMDYtMjQuMDc4LDMuMDQzLTI5LjEzN2MtMC43NDctMy41MzMtNC4yLTYuMjk1LTcuODUxLTYuMjk1bC0yMy4yNy0wLjAxYy0xLjg1NywwLTMuNTk0LDAuNzI0LTQuOSwyLjAzDQoJCQljLTEuMzgyLDEuMzkxLTIuMTM4LDMuMjc2LTIuMTI5LDUuMzJjMC4wMzgsNS42OTktMS4yMjMsMTMuMzA2LTIuMzA1LDE4Ljc3NmMtMC45MDYtMC4yMzMtMS44NzctMC4zNjQtMi44ODUtMC4zNjQNCgkJCWMtNS40NjEsMC05Ljg5NSwzLjc3MS05Ljg5NSw4LjQyNGMwLDQuNjQ4LDQuNDM4LDguNDE5LDkuODk1LDguNDE5YzUuMDU1LDAsOS4yMTMtMy4yMiw5LjgxOS03LjM3OGwwLjA3NSwwLjAxOQ0KCQkJYzAuMTYzLTAuNjUzLDMuODI2LTE1LjUyMyw0LjA3OS0yNi40NTNsMjAuODk4LDAuMDA5YzAuNjY4LDMuNjE3LDEuMTExLDkuOTgzLTEuMTI0LDE4LjMyMw0KCQkJQzIwMy45ODgsNDkuNzY1LDIwMi44NzgsNDkuNTgzLDIwMS43MTEsNDkuNTgzeiIvPg0KCQk8cGF0aCBkPSJNMzUuODY0LDEzNy44MzJjMi4wMjEsNC4xOTEsNy42NDksNS42NjEsMTIuNTY4LDMuMjk1YzQuNzkzLTIuMzAxLDcuMTQxLTcuMzkzLDUuNDE0LTExLjUxOQ0KCQkJYy0xLjMtMTQuNDcyLTYuNzQ0LTIzLjQ3NS05Ljg5Ni0yNy41NzdjLTIuMjA4LTIuODUyLTYuNTE1LTMuODUxLTkuODA4LTIuMjY0bC0yMC45NjksMTAuMDg1DQoJCQljLTEuNjczLDAuODAzLTIuOTI2LDIuMjA4LTMuNTMzLDMuOTU4Yy0wLjY0NCwxLjg0OS0wLjUwMSwzLjg4MywwLjM5Miw1LjcxN2MyLjUwNCw1LjEyLDQuNjY5LDEyLjUyMiw2LjA2LDE3LjkyMQ0KCQkJYy0wLjkyMSwwLjE4My0xLjg1MSwwLjQ4NS0yLjc1MywwLjkyNWMtNC45MTcsMi4zNjYtNy4yNzMsNy42ODctNS4yNTUsMTEuODc3YzIuMDIxLDQuMTkxLDcuNjQ1LDUuNjY2LDEyLjU2OSwzLjI5NQ0KCQkJYzQuNTQ4LTIuMTg4LDYuOS02LjkwMiw1LjY0Mi0xMC45MDZsMC4wNzctMC4wMTVjLTAuMTMzLTAuNjYyLTMuMjg4LTE1LjY0NC03Ljc5OS0yNS42MDhsMTguODMtOS4wNTQNCgkJCWMyLjE3LDIuOTczLDUuMzM0LDguNTEzLDYuOTM1LDE2Ljk5OGMtMS4wNzYsMC4xNjgtMi4xNTYsMC40OS0zLjIwOCwwLjk5NEMzNi4yMDIsMTI4LjMyLDMzLjg0NiwxMzMuNjQyLDM1Ljg2NCwxMzcuODMyeiIvPg0KCQk8Y2lyY2xlIGN4PSIxMTAuNTY1IiBjeT0iMzguMTM2IiByPSIyMS4wMDQiLz4NCgkJPHBhdGggZD0iTTE0LjMzNywyMzIuODY4aDIyMC4yMzljNy45MjEsMCwxNC4zMzgtNi4yNzIsMTQuMzM4LTE0LjAyMWMwLTcuNzQ3LTYuNDE3LTE0LjAyNC0xNC4zMzgtMTQuMDI0aC02Ny4yNjINCgkJCWMwLjM5My0wLjE0NSwwLjc5NC0wLjI4LDEuMTc2LTAuNTA0YzMuMjkxLTEuOTkzLDQuMzUxLTYuMjc3LDIuMzU3LTkuNTcybC0zOS4yNzgtNjUuMDE3di0zMi4xMQ0KCQkJYzE3Ljg4LDE2LjEzOSwyNi41MjMsNDEuOTg1LDI2LjY3Nyw0Mi40NTdjMC45NTcsMi45NDksMy42OTIsNC44MjUsNi42MzcsNC44MjVjMC43MDUsMCwxLjQzNC0wLjEwNiwyLjEzOC0wLjMzNg0KCQkJYzMuNjczLTEuMTgxLDUuNjgtNS4xMTUsNC40OTQtOC43NzNjLTAuNTg4LTEuODA3LTEyLjY2MS0zOC4yNjEtMzkuOTY5LTU1LjY5N2MtMC4xMTEtOS41MjUtNy44NTItMTcuMjE3LTE3LjQwNi0xNy4yMTdoLTcuMTU5DQoJCQljLTQuNTg3LDAtOC43MzIsMS44MTEtMTEuODQzLDQuNzA5Yy0yMS40NjQtMTUuMzIyLTMxLjc5LTQ2LjE5NC0zMS45NjItNDYuNzE3Yy0xLjE5LTMuNjU0LTUuMTA4LTUuNjctOC43NzQtNC40ODUNCgkJCWMtMy42NzEsMS4xODUtNS42NzgsNS4xMTUtNC40OTIsOC43NzRjMC41ODEsMS44MDEsMTIuNTY0LDM3Ljk4LDM5LjY0Miw1NS41MDF2NTUuNzkxTDc2LjA0NSwxNjAuMTUNCgkJCWMtMS4xMDQsMS45MzctMS4yMTgsNC4yNzktMC4yOTYsNi4zMTlsMTUuNjgxLDM0Ljc1MWMwLjc5MywxLjc2LDIuMjQ1LDIuOTc4LDMuOTE4LDMuNjAzSDE0LjMzNw0KCQkJQzYuNDE5LDIwNC44MjMsMCwyMTEuMTA1LDAsMjE4Ljg0OEMwLDIyNi41OTEsNi40MTksMjMyLjg2OCwxNC4zMzcsMjMyLjg2OHogTTg5LjkxNCwxNjMuOTY4bDEzLjMwMS0yMy4zMzVoMTguNjQ3bDM3LjA0Nyw2MS4zMjUNCgkJCWMwLjg0NSwxLjM5NiwyLjExNCwyLjMzOCwzLjUyOCwyLjg3aC02Mi4xNTZjMC4xMTktMC4wNDcsMC4yNDgtMC4wNjUsMC4zNjctMC4xMTdjMy41MDctMS41ODIsNS4wNzUtNS43MTIsMy40ODktOS4yMjINCgkJCUw4OS45MTQsMTYzLjk2OHoiLz4NCgk8L2c+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg==";
      case 'drink':
        return "data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMyIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgNjQgNjQiIGhlaWdodD0iNTEyIiB2aWV3Qm94PSIwIDAgNjQgNjQiIHdpZHRoPSI1MTIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0ibTE3LjYwNiAxLjIwNS0xLjIxMiAxLjU5IDEzLjM5MyAxMC4yMDVoMy4zMDF6Ii8+PHBhdGggZD0ibTIzIDI0YzAgNC45NjIgNC4wMzcgOSA5IDlzOS00LjAzOCA5LTl2LTJjMC0uNTUyLjQ0Ny0xIDEtMSA0LjYyNSAwIDguNDQ1LTMuNTA2IDguOTQ0LThoLTE3Ljg1Nmw1LjUxOCA0LjIwNS0xLjIxMyAxLjU5MS03LjYwNi01Ljc5NmgtMTYuNzMxYy40OTkgNC40OTQgNC4zMTkgOCA4Ljk0NCA4IC41NTMgMCAxIC40NDggMSAxem02LThjMi4yMDYgMCA0IDEuNzk0IDQgNHMtMS43OTQgNC00IDQtNC0xLjc5NC00LTQgMS43OTQtNCA0LTR6Ii8+PHBhdGggZD0ibTMzIDU3di0yMi4wNTFjLS4zMy4wMy0uNjYyLjA1MS0xIC4wNTFzLS42Ny0uMDIxLTEtLjA1MXYyMi4wNTFjMCAuNDA0LS4yNDMuNzY4LS42MTUuOTIzbC03LjM4NSAzLjA3N2gxOGwtNy4zODUtMy4wNzdjLS4zNzItLjE1NS0uNjE1LS41MTktLjYxNS0uOTIzeiIvPjxjaXJjbGUgY3g9IjI5IiBjeT0iMjAiIHI9IjIiLz48Y2lyY2xlIGN4PSI0MyIgY3k9IjQ5IiByPSIyIi8+PGNpcmNsZSBjeD0iNTIiIGN5PSIzOCIgcj0iNSIvPjxjaXJjbGUgY3g9IjE3IiBjeT0iNDQiIHI9IjIiLz48Y2lyY2xlIGN4PSI4IiBjeT0iMzMiIHI9IjUiLz48L3N2Zz4=";
      case 'bed':
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB2aWV3Qm94PSIwIDAgNDkwLjcgNDkwLjciIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDQ5MC43IDQ5MC43OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8Zz4NCgk8Zz4NCgkJPHBhdGggZD0iTTQzNi4yLDE1NC42SDE4Mi40Yy0xMi40LDAtMzMuMSw0LjctMzMuMSwzNi42VjI0MGgzMjB2LTQ4LjhDNDY5LjMsMTU5LjQsNDQ4LjYsMTU0LjYsNDM2LjIsMTU0LjZ6Ii8+DQoJPC9nPg0KPC9nPg0KPGc+DQoJPGc+DQoJCTxwb2x5Z29uIHBvaW50cz0iODAuMywyNTAuNiAzMiwyNTAuNiAzMiw4MCAwLDgwIDAsNDEwLjcgMzIsNDEwLjcgMzIsMzI1LjMgNDU4LjcsMzI1LjMgNDU4LjcsNDEwLjYgNDkwLjcsNDEwLjYgNDkwLjcsMjUwLjYgCQkNCgkJCSIvPg0KCTwvZz4NCjwvZz4NCjxnPg0KCTxnPg0KCQk8Y2lyY2xlIGN4PSI4NS4zIiBjeT0iMTk3LjMiIHI9IjQ0LjciLz4NCgk8L2c+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg==";
      default:
        // book
        return "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJMYXllcl8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDI5Ni45OTkgMjk2Ljk5OSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjk2Ljk5OSAyOTYuOTk5OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8Zz4NCgk8Zz4NCgkJPGc+DQoJCQk8cGF0aCBkPSJNNDUuNDMyLDM1LjA0OWMtMC4wMDgsMC0wLjAxNywwLTAuMDI1LDBjLTIuODA5LDAtNS40NTEsMS4wOTUtNy40NDYsMy4wODVjLTIuMDE3LDIuMDEyLTMuMTI4LDQuNjkxLTMuMTI4LDcuNTQzDQoJCQkJdjE1OS4zNjVjMCw1Ljg0NCw0Ljc3MywxMC42MSwxMC42NDEsMTAuNjI1YzI0LjczOCwwLjA1OSw2Ni4xODQsNS4yMTUsOTQuNzc2LDM1LjEzNlY4NC4wMjNjMC0xLjk4MS0wLjUwNi0zLjg0Mi0xLjQ2MS01LjM4Mg0KCQkJCUMxMTUuMzIyLDQwLjg0OSw3MC4yMjYsMzUuMTA3LDQ1LjQzMiwzNS4wNDl6Ii8+DQoJCQk8cGF0aCBkPSJNMjYyLjE2NywyMDUuMDQyVjQ1LjY3NmMwLTIuODUyLTEuMTExLTUuNTMxLTMuMTI4LTcuNTQzYy0xLjk5NS0xLjk5LTQuNjM5LTMuMDg1LTcuNDQ1LTMuMDg1Yy0wLjAwOSwwLTAuMDE4LDAtMC4wMjYsMA0KCQkJCWMtMjQuNzkzLDAuMDU5LTY5Ljg4OSw1LjgwMS05My4zNTcsNDMuNTkzYy0wLjk1NSwxLjU0LTEuNDYsMy40MDEtMS40Niw1LjM4MnYxNjYuNzc5DQoJCQkJYzI4LjU5Mi0yOS45MjEsNzAuMDM4LTM1LjA3Nyw5NC43NzYtMzUuMTM2QzI1Ny4zOTQsMjE1LjY1MSwyNjIuMTY3LDIxMC44ODUsMjYyLjE2NywyMDUuMDQyeiIvPg0KCQkJPHBhdGggZD0iTTI4Ni4zNzMsNzEuODAxaC03LjcwNnYxMzMuMjQxYzAsMTQuOTIxLTEyLjE1NywyNy4wODgtMjcuMTAxLDI3LjEyNWMtMjAuOTgzLDAuMDUtNTUuNTgxLDQuMTUzLTgwLjA4NCwyNy4zNDQNCgkJCQljNDIuMzc4LTEwLjM3Niw4Ny4wNTItMy42MzEsMTEyLjUxMiwyLjE3MWMzLjE3OSwwLjcyNCw2LjQ2NC0wLjAyNCw5LjAxMS0yLjA1NGMyLjUzOC0yLjAyNSwzLjk5NC01LjA1MiwzLjk5NC04LjMwMVY4Mi40MjcNCgkJCQlDMjk3LDc2LjU2OCwyOTIuMjMyLDcxLjgwMSwyODYuMzczLDcxLjgwMXoiLz4NCgkJCTxwYXRoIGQ9Ik0xOC4zMzIsMjA1LjA0MlY3MS44MDFoLTcuNzA2QzQuNzY4LDcxLjgwMSwwLDc2LjU2OCwwLDgyLjQyN3YxNjguODk3YzAsMy4yNSwxLjQ1Niw2LjI3NiwzLjk5NCw4LjMwMQ0KCQkJCWMyLjU0NSwyLjAyOSw1LjgyNywyLjc4LDkuMDExLDIuMDU0YzI1LjQ2LTUuODAzLDcwLjEzNS0xMi41NDcsMTEyLjUxMS0yLjE3MWMtMjQuNTAyLTIzLjE5LTU5LjEtMjcuMjkyLTgwLjA4My0yNy4zNDINCgkJCQlDMzAuNDksMjMyLjEzLDE4LjMzMiwyMTkuOTYzLDE4LjMzMiwyMDUuMDQyeiIvPg0KCQk8L2c+DQoJPC9nPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPC9zdmc+DQo=";

    }
  }

});
