jQuery(function () {
  if (
    'metrics' === window.wpApiShare.url_path ||
    'metrics/' === window.wpApiShare.url_path ||
    window.wpApiShare.url_path.startsWith('metrics/personal/overview')
  ) {
    my_stats();
  }

  async function my_stats() {
    'use strict';
    let chartDiv = jQuery('#chart');

    let sourceData = await fetch(window.dtMetricsPersonal.rest_url + '/data', {
      headers: { 'X-WP-Nonce': window.dtMetricsPersonal.nonce },
    }).then((response) => response.json());

    let translations = window.dtMetricsPersonal.translations;

    jQuery('#metrics-sidemenu').foundation('down', jQuery('#personal-menu'));

    let html = `
      <div class="cell center">
          <h3 >${window.SHAREDFUNCTIONS.escapeHTML(translations.title)}</h3>
      </div>
      <br><br>
      <div class="grid-x grid-padding-x grid-padding-y">
        <h3 class="section-header">${window.SHAREDFUNCTIONS.escapeHTML(translations.title_contacts)}</h3>
        <div class="cell center callout">
            <div class="grid-x">
                <div class="medium-4 cell center ">
                    <h5>${window.SHAREDFUNCTIONS.escapeHTML(translations.title_waiting_on_accept)}<br><span id="needs_accepted">0</span></h5>
                </div>
                <div class="medium-4 cell center left-border-grey">
                    <h5>${window.SHAREDFUNCTIONS.escapeHTML(translations.title_waiting_on_update)}<br><span id="updates_needed">0</span></h5>
                </div>
                <div class="medium-4 cell center left-border-grey">
                    <h5>${window.SHAREDFUNCTIONS.escapeHTML(translations.label_active_contacts)}<br><span id="contacts">0</span></h5>
                </div>
            </div>
        </div>`;
    if (sourceData.contacts_progress) {
      html += `<div class="cell">
            <div id="my_contacts_progress" style="height: 350px; width=100%"></div>
        </div>`;
    }

    if (sourceData.hero_stats)
      html += `<h3 class="section-header" style="margin-top:40px;">${window.SHAREDFUNCTIONS.escapeHTML(translations.title_groups)}</h3>
        <div class="cell">
            <div class="cell center callout">
                <div class="grid-x">
                    <div class="medium-4 cell center">
                        <h5>${window.SHAREDFUNCTIONS.escapeHTML(translations.title_total_groups)}<br><span id="total_groups">0</span></h5>
                    </div>
                    <div class="medium-4 cell center left-border-grey">
                        <h5>${window.SHAREDFUNCTIONS.escapeHTML(translations.title_teams)}<br><span id="teams">0</span></h5>
                    </div>
               </div>
            </div>
        </div>`;
    if (sourceData.group_types) {
      html += `
        <div class="cell"  id="my_groups_health_container">
            <div id="my_groups_health" style="height: 500px;"></div>
            <hr>
        </div>`;
    }
    if (sourceData.group_health && sourceData.group_generations) {
      html += `
        <div class="cell">
            <div class="grid-x">
                <div class="cell medium-6 center">
                    <div id="group_types" style="height: 400px;"></div>
                </div>
                <div class="cell medium-6">
                    <div id="group_generations" style="height: 400px;"></div>
                </div>
            </div>
        </div>`;
    }
    html += `</div>`;

    chartDiv.empty().html(html);

    let hero = sourceData.hero_stats;
    jQuery('#contacts').html(numberWithCommas(hero.contacts));
    jQuery('#needs_accepted').html(numberWithCommas(hero.needs_accept));
    jQuery('#updates_needed').html(numberWithCommas(hero.needs_update));

    jQuery('#total_groups').html(numberWithCommas(hero.groups));
    jQuery('#teams').html(numberWithCommas(hero.teams));

    if (sourceData.contacts_progress) {
      // build charts
      drawMyContactsProgress();
    }
    if (
      sourceData.preferences.groups.church_metrics &&
      sourceData.group_health
    ) {
      drawMyGroupHealth();
    }
    if (sourceData.group_types) {
      drawGroupTypes();
    }
    if (sourceData.group_generations) {
      drawGroupGenerations();
    }

    function drawMyContactsProgress() {
      let chart = window.am4core.create(
        'my_contacts_progress',
        window.am4charts.XYChart,
      );
      let title = chart.titles.create();
      title.text = `[bold]${translations.label_my_follow_up_progress}[/]`;
      chart.data = sourceData.contacts_progress.reverse();

      let categoryAxis = chart.yAxes.push(new window.am4charts.CategoryAxis());
      categoryAxis.dataFields.category = 'label';
      categoryAxis.renderer.grid.template.location = 0;
      categoryAxis.renderer.minGridDistance = 30;

      let valueAxis = chart.xAxes.push(new window.am4charts.ValueAxis());
      valueAxis.title.text = 'Number of contacts';

      let series = chart.series.push(new window.am4charts.ColumnSeries());
      series.dataFields.valueX = 'value';
      series.dataFields.categoryY = 'label';
      series.columns.template.tooltipText = 'Total: [bold]{valueX}[/]';

      // field value label
      let valueLabel = series.bullets.push(new window.am4charts.LabelBullet());
      valueLabel.label.text = '{valueX}';
      valueLabel.label.horizontalCenter = 'left';
      valueLabel.label.dx = 10;
      valueLabel.label.hideOversized = false;
      valueLabel.label.truncate = false;
    }

    function drawMyGroupHealth() {
      let chart = window.am4core.create(
        'my_groups_health',
        window.am4charts.XYChart,
      );
      chart.data = sourceData.group_health;
      let title = chart.titles.create();
      title.text = `[bold]${translations.label_group_needing_training}[/]`;
      let categoryAxis = chart.xAxes.push(new window.am4charts.CategoryAxis());
      categoryAxis.dataFields.category = 'label';
      categoryAxis.renderer.grid.template.location = 0;
      categoryAxis.renderer.minGridDistance = 20;
      categoryAxis.renderer.labels.template.wrap = true;
      categoryAxis.events.on('sizechanged', function (ev) {
        var axis = ev.target;
        var cellWidth = axis.pixelWidth / (axis.endIndex - axis.startIndex);
        axis.renderer.labels.template.maxWidth =
          cellWidth > 70 ? cellWidth : 70;
        axis.renderer.labels.template.disabled = cellWidth < 70;
      });

      let valueAxis = chart.yAxes.push(new window.am4charts.ValueAxis());
      valueAxis.min = 0;
      valueAxis.max = 100;
      valueAxis.strictMinMax = true;
      valueAxis.calculateTotals = true;
      valueAxis.renderer.minWidth = 50;
      valueAxis.renderer.labels.template.adapter.add('text', function (text) {
        return text + '%';
      });

      let series1 = chart.series.push(new window.am4charts.ColumnSeries());
      series1.columns.template.width = window.am4core.percent(80);
      series1.columns.template.tooltipText = '{name}: {valueY}';
      series1.name = 'Practicing';
      series1.dataFields.categoryX = 'label';
      series1.dataFields.valueY = 'practicing';
      series1.dataFields.valueYShow = 'totalPercent';
      series1.dataItems.template.locations.categoryX = 0.5;
      series1.stacked = true;
      series1.tooltip.pointerOrientation = 'vertical';

      let series2 = chart.series.push(new window.am4charts.ColumnSeries());
      series2.stroke = window.am4core.color('#da7070'); // red
      series2.fill = window.am4core.color('#da7070'); // red
      series2.columns.template.width = window.am4core.percent(80);
      series2.columns.template.tooltipText = '{name}: {valueY}';
      series2.name = 'Not Practicing';
      series2.dataFields.categoryX = 'label';
      series2.dataFields.valueY = 'remaining';
      series2.dataFields.valueYShow = 'totalPercent';
      series2.dataItems.template.locations.categoryX = 0.5;
      series2.stacked = true;
      series2.tooltip.pointerOrientation = 'vertical';
      chart.legend = new window.am4charts.Legend();
    }

    function drawGroupTypes() {
      let chart = window.am4core.create(
        'group_types',
        window.am4charts.PieChart,
      );
      let title = chart.titles.create();
      title.text = `[bold]${translations.title_group_types}[/]`;
      chart.data = sourceData.group_types;
      let pieSeries = chart.series.push(new window.am4charts.PieSeries());
      pieSeries.dataFields.value = 'count';
      pieSeries.dataFields.category = 'label';
      pieSeries.labels.template.disabled = true;
      chart.innerRadius = window.am4core.percent(30);
      chart.legend = new window.am4charts.Legend();
    }

    function drawGroupGenerations() {
      let chart = window.am4core.create(
        'group_generations',
        window.am4charts.XYChart,
      );
      let title = chart.titles.create();
      title.text = `[bold]${translations.title_generations}[/]`;

      chart.data = sourceData.group_generations.reverse();
      let categoryAxis = chart.yAxes.push(new window.am4charts.CategoryAxis());
      categoryAxis.dataFields.category = 'generation';
      categoryAxis.renderer.grid.template.location = 0;
      categoryAxis.renderer.labels.template.adapter.add(
        'text',
        function (text) {
          return translations.label_generation + ' ' + text;
        },
      );

      let valueAxis = chart.xAxes.push(new window.am4charts.ValueAxis());
      valueAxis.renderer.inside = true;
      valueAxis.renderer.labels.template.disabled = true;
      valueAxis.min = 0;

      function fetchGroupType(id) {
        let found_type = null;
        jQuery.each(sourceData.group_types, function (idx, type) {
          if (type['type'] == id) {
            found_type = type;
          }
        });

        return found_type;
      }

      function createSeries(field, name) {
        let series = chart.series.push(new window.am4charts.ColumnSeries());
        series.name = name;
        series.dataFields.valueX = field;
        series.dataFields.categoryY = 'generation';
        series.stacked = true;
        series.columns.template.width = window.am4core.percent(60);
        series.columns.template.tooltipText = '[bold]{name}[/]\n {valueX}';
        let labelBullet = series.bullets.push(
          new window.am4charts.LabelBullet(),
        );
        labelBullet.label.text = '{valueX}';
        labelBullet.locationX = 0.5;
        return series;
      }

      // Iterate over and capture returned group types into set
      let group_type_set = {};
      jQuery.each(chart.data, function (idx, generation) {
        jQuery.each(generation, function (key, value) {
          let group_type = fetchGroupType(key);
          if (
            group_type &&
            !Object.prototype.hasOwnProperty.call(
              group_type_set,
              group_type['type'],
            )
          ) {
            group_type_set[group_type['type']] = group_type;
          }
        });
      });

      // Iterate over group type set, creating series accordingly
      jQuery.each(group_type_set, function (key, type) {
        createSeries(type['type'], type['label']);
      });

      chart.legend = new window.am4charts.Legend();
    }

    new window.Foundation.Reveal(jQuery('.dt-project-legend'));
  }
});

function numberWithCommas(x) {
  x = (x || 0).toString();
  let pattern = /(-?\d+)(\d{3})/;
  while (pattern.test(x)) x = x.replace(pattern, '$1,$2');
  return x;
}
