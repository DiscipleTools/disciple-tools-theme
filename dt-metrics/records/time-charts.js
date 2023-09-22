jQuery(function() {
    if (window.wpApiShare.url_path.startsWith( 'metrics/records/time_charts' )) {
        projectTimeCharts()
    }
})

const CUMULATIVE_PREFIX = 'cumulative_'
const graphTypes = ['stacked', 'line']

const getTimeMetricsByYear = (postType, field) =>
window.makeRequest('GET', `metrics/time_metrics_by_year/${postType}/${field}`)

const getTimeMetricsByMonth = (postType, field, year) =>
window.makeRequest('GET', `metrics/time_metrics_by_month/${postType}/${field}/${year}`)

const getMetricsCumulativePosts = (data) =>
  window.makeRequest('POST', `metrics/cumulative-posts`, data)

const getMetricsChangedPosts = (data) =>
  window.makeRequest('POST', `metrics/changed-posts`, data)

const getFieldSettings = (postType) =>
window.makeRequest('GET', `metrics/field_settings/${postType}`)

const escapeObject = window.SHAREDFUNCTIONS.escapeObject

function projectTimeCharts() {

    const chartDiv = document.querySelector('#chart')
    const {
        title_time_charts,
        post_type_select_label,
        post_field_select_label,
        date_select_label,
        all_time,
        stacked_chart_title,
        cumulative_chart_title,
        additions_chart_title,
    } = escapeObject(window.dtMetricsProject.translations)

    const postTypeOptions = escapeObject(window.dtMetricsProject.select_options.post_type_select_options)

    jQuery('#metrics-sidemenu').foundation('down', jQuery('#records-menu'));

    chartDiv.innerHTML = `
        <div class="section-header"> ${title_time_charts} </div>
        <section class="chart-controls">
            <label class="section-subheader" for="post-type-select"> ${post_type_select_label} </label>
            <select class="select-field" id="post-type-select">
                ${ Object.entries(postTypeOptions).map(([value, label]) => `
                    <option value="${value}"> ${label} </option>
                `) }
            </select>
            <label class="section-subheader" for="post-field-select">${post_field_select_label}</label>
            <select class="select-field" id="post-field-select">
                ${ buildFieldSelectOptions() }
            </select>
            <label class="section-subheader" for="date-select">${date_select_label}</label>
            <select class="select-field" id="date-select">
                ${ buildDateSelectOptions(all_time) }
            </select>
            <div id="chart-loading-spinner" class="loading-spinner active"></div>
        </section>
        <hr>
        <section id="chart-area">
            <section id="stacked-chart" style="display: none">
                <h2>${stacked_chart_title}</h2>
                <div class="timechart"></div>
                <div class="legend"></div>
            </section>
            <section id="cumulative-chart" style="display: none">
                <h2>${cumulative_chart_title}</h2>
                <div class="timechart"></div>
                <div class="legend"></div>
            </section>
            <section id="additions-chart" style="display: none">
                <h2>${additions_chart_title}</h2>
                <div class="timechart"></div>
                <div class="legend"></div>
            </section>
        </section>
    `

    /*jQuery('#metrics-content-modal').empty().html(`
        <div class="large reveal" id="post_details_modal" data-reveal data-reset-on-close>
            <button class="button loader" data-close aria-label="Close reveal" type="button">
                Close
            </button>

            <button class="close-button" data-close aria-label="Close" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `);*/

    const chartSection = document.querySelector('#chart-area')
    const loadingSpinner = document.querySelector('#chart-loading-spinner')
    chartSection.addEventListener('datachange', () => {
        createCharts()
        loadingSpinner.classList.remove('active')
    })
    const fieldSelectElement = document.querySelector('#post-field-select')

    document.querySelector('#post-type-select').addEventListener('change', (e) => {
        const postType = e.target.value
        window.dtMetricsProject.state.post_type = postType
        getFieldSettings(postType)
            .promise()
            .then((data) => {
                window.dtMetricsProject.field_settings = data
                fieldSelectElement.innerHTML = buildFieldSelectOptions()
                fieldSelectElement.dispatchEvent( new Event('change') )
            })
            .catch((error) => {
                console.log(error)
            })
    })

    fieldSelectElement.addEventListener('change', (e) => {
        window.dtMetricsProject.state.field = e.target.value
        if (!window.dtMetricsProject.field_settings[e.target.value]) {
            console.error(e.target.value, 'not found in', window.dtMetricsProject.field_settings)
            return
        }
        window.dtMetricsProject.state.fieldType = window.dtMetricsProject.field_settings[e.target.value].type
        getData()
    })

    document.querySelector('#date-select').addEventListener('change', (e) => {
        const year = e.target.value
        window.dtMetricsProject.state.year = year
        window.dtMetricsProject.state.chart_view = year === 'all-time' ? 'year' : 'month'
        getData()
    })

    // trigger the first get of data on page load
    fieldSelectElement.dispatchEvent( new Event('change') )
}

function buildFieldSelectOptions() {
    const unescapedOptions = Object.entries(window.dtMetricsProject.field_settings)
        .reduce((options, [ key, setting ]) => {
            options[key] = setting.name
            return options
        }, {})
    const postFieldOptions = escapeObject(unescapedOptions)
    const sortedOptions = Object.entries(postFieldOptions).sort(([key1, value1], [key2, value2]) => {
        if (value1 < value2) return -1
        if (value1 === value2) return 0
        if (value1 > value2) return 1
    })
    return sortedOptions.map(([value, label]) => `
        <option value="${value}"> ${label} </option>
    `)
}

function buildDateSelectOptions(allTimeLabel) {
    const { earliest_year } = window.dtMetricsProject.state

    const now = new Date()
    const currentYear = now.getUTCFullYear()

    let options = ''
    for (let year = currentYear; year > earliest_year - 1; year--) {
        options += `<option value="${year}">${year}</option>`
    }
    options += `<option value="all-time">${allTimeLabel}</option>`
    return options
}

function createCharts() {
    const { fieldType } = window.dtMetricsProject.state
    const {
        added_label,
        total_label,
    } = escapeObject(window.dtMetricsProject.translations)
    const data = window.dtMetricsProject.data

    // if date field create cumulative and addition charts
    if ( !window.dtMetricsProject.multi_fields.includes(fieldType)) {
        hideChart('stacked-chart')
        createChart('cumulative-chart', ['cumulative_count'], {
            customLabel: total_label,
        })
        createChart('additions-chart', ['count'], {
            customLabel: added_label,
            graphType: 'line'
        })
    } else {
        const keys = getDataKeys(data)
        const totalKeys = keys.filter((key) => !key.includes('cumulative_') )
        const cumulativeKeys = keys.filter((key) => key.includes('cumulative_') )
        createChart('stacked-chart', cumulativeKeys)
        createChart('cumulative-chart', cumulativeKeys, {
            single: true,
        })
        createChart('additions-chart', totalKeys, {
            single: true,
            graphType: 'line',
        })
    }
    // if multi field create stacked cumulative chart, cumulative and addition chart
}

function hideChart(id) {
    const chartSection = document.getElementById(id)
    chartSection.style.display = 'none'
}

function showChart(id) {
    const chartSection = document.getElementById(id)
    chartSection.style.display = 'block'
}

function createChart(id, keys, options) {
    const defaultOptions = {
        single: false,
        graphType: 'stacked',
        customLabel: ''
    }
    const { single, graphType, customLabel } = { ...defaultOptions, ...options }
    const { field, fieldType } = window.dtMetricsProject.state
    const { true_label, false_label } = escapeObject(window.dtMetricsProject.translations)

    if (!graphTypes.includes(graphType)) {
        throw new Error(`graphType ${graphType} not found in ${graphTypes}`)
    }

    showChart(id)
    const [ chart, valueAxis ] = initialiseChart(id)

    // create the series for each key name in the data arrays
    // then get the labels from field_settings, if they exist
    let fieldLabels = keys.map((key) => {
        const fieldSettings = window.dtMetricsProject.field_settings[field]
        const defaultSettings = fieldSettings && fieldSettings.default ? fieldSettings.default : []

        const newKey = isCumulativeKey(key) ? key.replace(CUMULATIVE_PREFIX, '') : key

        let label = ''
        if (defaultSettings[newKey]) {
            label = window.SHAREDFUNCTIONS.escapeHTML( defaultSettings[newKey].label )
        } else if ( fieldType === 'boolean' ) {
            if (newKey === '1') {
                label = true_label
            } else {
                label = false_label
            }
        } else {
            label = newKey
        }
        return {
            field: key,
            label: customLabel === '' ? label : customLabel,
        }
    })

    if (single) {
        chart.events.on("ready", () => {
            const [ min, max ] = getMinMaxValuesOfDataForKey(keys[0])
            if (0 === max) return
            valueAxis.zoomToValues(0, max)
        })
    }
    // then loop over the keys and labels and create the stacked series
    let firstSerie = true

    // Adjust field labels shape accordingly based on field type.
    let series = [];
    if (fieldType === 'connection') {
      if (graphType === 'stacked') {
        series = [
          {
            field: 'cumulative_count',
            label: window.SHAREDFUNCTIONS.escapeHTML( window.dtMetricsProject.translations.total_label )
          }
        ].map(({field, label}) => {
          let generated_serie = createChartSeries(graphType, single, firstSerie, chart, field, label);
          firstSerie = generated_serie.first_serie;
          return generated_serie.series;
        });
      } else if (graphType === 'line') {
        series = [
          {
            field: 'connected',
            label: window.SHAREDFUNCTIONS.escapeHTML( window.dtMetricsProject.translations.connected_label )
          },
          {
            field: 'disconnected',
            label: window.SHAREDFUNCTIONS.escapeHTML( window.dtMetricsProject.translations.disconnected_label )
          }
        ].map(({field, label}) => {
          let generated_serie = createChartSeries(graphType, single, firstSerie, chart, field, label);
          firstSerie = generated_serie.first_serie;
          return generated_serie.series;
        });
      }
    } else {
      series = fieldLabels.map(({field, label}) => {
        let generated_serie = createChartSeries(graphType, single, firstSerie, chart, field, label);
        firstSerie = generated_serie.first_serie;
        return generated_serie.series;
      });
    }

    if (single) {
        addHideOtherSeriesEventHandlers(series)
    }

    const chartSection = document.getElementById(id)
    const legendDiv = chartSection.querySelector('.legend')

    const legendContainer = window.am4core.create(legendDiv, window.am4core.Container)
    legendContainer.width = window.am4core.percent(100)
    legendContainer.height = window.am4core.percent(100)
    chart.legend = new window.am4charts.Legend()
    chart.legend.minHeight = 36
    chart.legend.scrollable = true
    chart.legend.parent = legendContainer

    const resizeLegend = (e) => {
        const legendStyle = legendDiv.computedStyle || window.getComputedStyle(legendDiv)
        const paddingTop = parseInt(legendStyle.paddingTop)
        const paddingBottom = parseInt(legendStyle.paddingBottom)
        const newHeight = chart.legend.contentHeight + paddingTop + paddingBottom
        legendDiv.style.height = `${newHeight}px`
        legendDiv.style.paddingBottom = '10px'
    }

    chart.events.on('datavalidated', resizeLegend)
    chart.events.on('maxsizechanged', resizeLegend)

    chart.legend.events.on('datavalidated', resizeLegend)
    chart.legend.events.on('maxsizechanged', resizeLegend)

    return series
}

function createChartSeries(graphType, single, firstSerie, chart, field, label){
  if (graphType === 'stacked') {
    if (single && !firstSerie) {
      return {
        'first_serie': firstSerie,
        'series': createColumnSeries(chart, field, label, true)
      };
    }
    return {
      'first_serie': false,
      'series': createColumnSeries(chart, field, label)
    };
  } else if (graphType === 'line') {
    if (single && !firstSerie) {
      return {
        'first_serie': firstSerie,
        'series': createLineSeries(chart, field, label, true)
      };
    }
    return {
      'first_serie': false,
      'series': createLineSeries(chart, field, label)
    };
  }
}

function initialiseChart(id) {
    const { year, chart_view: view, fieldType: field_type } = window.dtMetricsProject.state
    const {
        all_time,
    } = escapeObject(window.dtMetricsProject.translations)

    window.am4core.options.autoDispose = true

    const chartSection = document.getElementById(id)
    const timechartDiv = chartSection.querySelector('.timechart')

    const chart = window.am4core.create(timechartDiv, window.am4charts.XYChart)
    const data = window.dtMetricsProject.data

    const categoryAxis = chart.xAxes.push( new window.am4charts.CategoryAxis() )
    categoryAxis.dataFields.category = view
    categoryAxis.title.text = year === 'all-time' ? all_time : String(year)

    const valueAxis = chart.yAxes.push( new window.am4charts.ValueAxis() )
    valueAxis.maxPrecision = 0

    // Adjust data shape accordingly based on field type.
    switch (field_type) {
      case 'connection': {
        chart.data = window.dtMetricsProject.data_connection;
        break;

      } default: {
        chart.data = data;
        break;
      }
    }

    return [ chart, valueAxis ]
}

function createColumnSeries(chart, field, name, hidden = false) {
    const { chart_view, fieldType: field_type } = window.dtMetricsProject.state
    const { tooltip_label } = escapeObject(window.dtMetricsProject.translations)

    const tooltipLabel = tooltip_label.replace('%1$s', '{name}').replace('%2$s', '{categoryX}')

    const series = chart.series.push( new window.am4charts.ColumnSeries())
    series.dataFields.valueY = field
    series.dataFields.categoryX = chart_view
    series.name = name
    series.columns.template.tooltipText = `[#fff font-size: 12px]${tooltipLabel}:\n[/][#fff font-size: 15px]{valueY}[/] [#fff]{additional}[/]`
    if (hidden) {
        series.hide()
    }

    // Adopt the correct chart series.
    switch (field_type) {
      case 'connection': {
        series.stacked = false;
        break;
      }
      default: {
        series.stacked = true;
        break;
      }
    }

    // Capture event clicks.
    series.columns.template.events.on("hit", function (e) {
      let target = e.target;
      let date_key = target.dataItem.component.dataFields.categoryX;
      let metric_key = target.dataItem.component.dataFields.valueY;
      let data = target.dataItem.dataContext;

      displayPostListModal(data[date_key], date_key, metric_key, data['cumulative_count']);
    });

    return series
}

function createLineSeries(chart, field, name, hidden = false) {
    const { chart_view, fieldType: field_type } = window.dtMetricsProject.state
    const { tooltip_label } = escapeObject(window.dtMetricsProject.translations)
    const tooltipLabel = tooltip_label.replace('%1$s', '{name}').replace('%2$s', '{categoryX}')

    let lineSeries = chart.series.push(new window.am4charts.LineSeries());
    lineSeries.name = name
    lineSeries.dataFields.valueY = field;
    lineSeries.dataFields.categoryX = chart_view

    if (field_type === 'connection' && field === 'disconnected') {
      lineSeries.stroke = window.am4core.color("#d70101");
    }
    lineSeries.strokeWidth = 3;
    lineSeries.propertyFields.strokeDasharray = "lineDash";
    lineSeries.tooltip.label.textAlign = "middle";
    if (hidden) {
        lineSeries.hide()
    }

    let bullet = lineSeries.bullets.push(new window.am4charts.Bullet());
//    bullet.fill = window.am4core.color("#fdd400"); // tooltips grab fill from parent by default
    bullet.tooltipText = `[#fff font-size: 12px]${tooltipLabel}:\n[/][#fff font-size: 15px]{valueY}[/] [#fff]{additional}[/]`

    // Capture event clicks.
    bullet.events.on("hit", function (e) {
      let target = e.target;
      let date_key = target.dataItem.component.dataFields.categoryX;
      let metric_key = target.dataItem.component.dataFields.valueY;
      let data = target.dataItem.dataContext;

      displayPostListModal(data[date_key], date_key, metric_key);
    });

    let circle = bullet.createChild(window.am4core.Circle);
    circle.radius = 4;
    circle.fill = window.am4core.color("#fff");
    circle.strokeWidth = 3;

    return lineSeries
}

function displayPostListModal(date, date_key, metric_key) {
  if (date && date_key && metric_key) {

    // Determine click display parameters.
    let { post_type, field, fieldType, year, earliest_year } = window.dtMetricsProject.state;
    let is_cumulative = metric_key.startsWith('cumulative_');
    let is_all_time = year === 'all-time';
    let clicked_year = is_all_time ? date : year;
    let limit = 100;

    // Build request payload.
    let payload = {
      'post_type': post_type,
      'field': field,
      'key': is_cumulative ? metric_key.substring('cumulative_'.length) : metric_key,
      'limit': limit,
    };

    // Determine request query date range.
    if (is_all_time) {
      payload['ts_start'] = window.moment().year(is_cumulative ? earliest_year : clicked_year).month(0).date(1).hour(0).minute(0).second(0).unix();
      payload['ts_end'] = window.moment().year(clicked_year).month(11).date(31).hour(23).minute(59).second(59).unix();
    } else {
      payload['ts_start'] = window.moment().year(is_cumulative ? earliest_year : clicked_year).month(parseInt(window.moment().month(date).format('M')) - 1).date(1).hour(0).minute(0).second(0).unix();
      payload['ts_end'] = window.moment().year(clicked_year).month(parseInt(window.moment().month(date).format('M')) - 1).date(window.moment().month(date).endOf('month').format('D')).hour(23).minute(59).second(59).unix();
    }

    // Final adjustments for specific field types.
    if (fieldType === 'connection' && metric_key.includes('cumulative_')) {
      payload['key'] = 'cumulative';
      payload['ts_start'] = is_all_time ? window.moment().year(earliest_year).month(0).date(1).hour(0).minute(0).second(0).unix():window.moment().year(earliest_year).month(parseInt(window.moment().month(date).format('M')) - 1).date(1).hour(0).minute(0).second(0).unix();
    }
    // Dispatch request and process response accordingly.
    getMetricsCumulativePosts(payload)
    .promise()
    .then(response => {
      if (response && response.data) {
        let selected_posts = [];
        let posts = response.data;

        // Limit to first X elements.
        selected_posts = posts.slice(0, limit);

        // Proceed with displaying post list.
        let sorted_posts = window.lodash.orderBy(selected_posts, ['name'], ['asc']);
        let list_html = `
          <br>
          ${(function (posts_to_filter) {
          let post_list_html = `
          <table>
            <thead>
                <tr>
                    <th>${window.lodash.escape(window.dtMetricsProject.translations.modal_table_head_no)}</th>
                    <th>${window.lodash.escape(window.dtMetricsProject.translations.modal_table_head_title)}</th>
                </tr>
            </thead>
            <tbody>
          `;
          let counter = 0;
          jQuery.each(posts_to_filter, function (idx, post) {
            let url = window.dtMetricsProject.site + window.dtMetricsProject.state.post_type + '/' + post['id'];

            post_list_html += `
                <tr>
                    <td>${++counter}</td>
                    <td><a href="${url}" target="_blank">${post['name'] ? post['name'] : post['id']}</a></td>
                </tr>
                `;
          });

          post_list_html += `
            </tbody>
          </table>
          `;

          return (posts_to_filter.length > 0) ? post_list_html : window.dtMetricsProject.translations.modal_no_records;
        })(sorted_posts)}
          <br>
          `;

        // Determine overall total value.
        let total = sorted_posts.length;
        if (response.total) {
          total = parseInt(response.total);
        }

        // Render post html list.
        let title = window.dtMetricsProject.translations.modal_title + ((total > 0) ? ` [ ${total} ]` : '' );
        let content = jQuery('#template_metrics_modal_content');
        jQuery('#template_metrics_modal_title').empty().html(window.lodash.escape(title));
        jQuery(content).css('max-height', '300px');
        jQuery(content).css('overflow', 'auto');
        jQuery(content).empty().html(list_html);
        jQuery('#template_metrics_modal').foundation('open');
      }
    })
    .catch(error => {
      console.log(error);
    });
  }
}

function getMinMaxValuesOfDataForKey(key) {
    const data = window.dtMetricsProject.data
    let min = 0
    let max = 0

    data.forEach(timePeriodData => {
        const dataStr = timePeriodData[key]
        if (!dataStr || dataStr.length === 0) return
        let dataVal = parseInt(dataStr)

        if (dataVal < min) {
            min = dataVal
            return
        } else if (dataVal > max) {
            max = dataVal
            return
        } else {
            return
        }
    });
    return [ min, max ]
}

function addHideOtherSeriesEventHandlers(series) {
    if (series.length <= 1) return
    series.forEach((serie) => {
        serie.events.on('shown', () => {
            const otherSeries = series.filter((otherSerie) => serie !== otherSerie )
            otherSeries.forEach((otherSerie) => {
                otherSerie.hide()
            })
        })
    })
}

function getData() {
    const { post_type: postType, fieldType: field_type, field, year } = window.dtMetricsProject.state

    const isAllTime = year === 'all-time'
    const data = isAllTime
        ? getTimeMetricsByYear(postType, field)
        : getTimeMetricsByMonth(postType, field, year)

    const loadingSpinner = document.querySelector('.loading-spinner')
    const chartElement = document.querySelector('#chart-area')
    loadingSpinner.classList.add('active')
    data.promise()
        .then(( response ) => {
          if ( !response && !response.data ) {
            throw new Error('no data object returned')
          }

          let data = response.data;

          // Capture additional metadata.
          switch (field_type) {
            case 'connection': {
              processConnectionData(data);
              break;
            }
            default: {
              window.dtMetricsProject.cumulative_offset = (response.cumulative_offset !== undefined) ? response.cumulative_offset : 0;
              window.dtMetricsProject.data = isAllTime ? formatYearData(data) : formatMonthData(data);
              break;
            }
          }

          // Refresh chart display.
          chartElement.dispatchEvent( new Event('datachange') )
          loadingSpinner.classList.remove('active')
        })
        .catch((error) => {
            console.log(error)
            chartElement.dispatchEvent( new Event('datachange') )
            loadingSpinner.classList.remove('active')
        })
}

function processConnectionData(data) {
  if (data && data.records) {
    const {post_type, fieldType: field_type, field, year} = window.dtMetricsProject.state;
    const is_all_time = (year === 'all-time');

    if (is_all_time) {
      window.dtMetricsProject.data_connection = [];
      jQuery.each(data.records, function (year, metrics) {
        window.dtMetricsProject.data_connection.push(
          {
            'year': String(year),
            'connected': metrics.connected,
            'disconnected': metrics.disconnected,
            'cumulative_count': metrics.cumulative_count
          }
        );
      });
    } else {
      let cumulative_offset = (data.cumulative_totals.cumulative_count !== undefined) ? data.cumulative_totals.cumulative_count : 0;

      const month_labels = window.SHAREDFUNCTIONS.get_months_labels();
      window.dtMetricsProject.data_connection = month_labels.map((month_label, i) => {
        const month_number = i + 1;
        if (isInFuture(month_number)) {
          return {
            'month': month_label,
          }
        }

        const month_data = window.lodash.find(data.records, function (record, month) {
          return (month === String(month_number));
        });

        if (month_data) {
          cumulative_offset = month_data.cumulative_count;

          return {
            'month': month_label,
            'connected': month_data.connected,
            'disconnected': month_data.disconnected,
            'cumulative_count': month_data.cumulative_count
          }
        } else {
          return {
            'month': month_label,
            'connected': 0,
            'disconnected': 0,
            'cumulative_count' : cumulative_offset
          }
        }
      });
    }
  }
}

/**
 * Formats the metric data by filling in any blank years and calculating
 * cumulative counts for the charts
 *
 * Deals with data coming back from different types of fields (e.g. multi_select, date etc.)
 */
function formatYearData(yearlyData) {
    const { fieldType } = window.dtMetricsProject.state

    if ( window.dtMetricsProject.multi_fields.includes(fieldType)) {
        return formatCompoundYearData(yearlyData)
    } else {
        return formatSimpleYearData(yearlyData)
    }
}

function formatSimpleYearData(yearlyData) {
    if (yearlyData.length === 0) return yearlyData

    let cumulativeTotal = 0
    const minYear = parseInt(yearlyData[0].year)
    const maxYear = parseInt(yearlyData[yearlyData.length - 1].year)

    const formattedYearlyData = []
    let i = 0
    for (let year = minYear; year < maxYear + 1; year++, i++) {
        const yearData = yearlyData.find((data) => data.year === String(year) )
        const count = yearData ? parseInt(yearData.count) : 0
        cumulativeTotal += count

        formattedYearlyData[i] = {
            year: String(year),
            count: count,
            cumulative_count: cumulativeTotal,
        }
    }

    return formattedYearlyData
}

function formatCompoundYearData(yearlyData) {
    if (yearlyData.length === 0) return yearlyData

    const keys = getDataKeys(yearlyData)

    let cumulativeTotals = {}
    const cumulativeKeys = makeCumulativeKeys(keys)

    const minYear = parseInt(yearlyData[0].year)
    const maxYear = parseInt(yearlyData[yearlyData.length - 1].year)

    const formattedYearlyData = []
    let i = 0
    for (let year = minYear; year < maxYear + 1; year++, i++) {
        const yearData = yearlyData.find((data) => data.year === String(year) )

        cumulativeTotals = calculateCumulativeTotals(keys, yearData, cumulativeTotals, cumulativeKeys)

        formattedYearlyData[i] = {
            ...yearData,
            ...cumulativeTotals,
            year: String(year),
        }
    }

    return formattedYearlyData
}

/**
 * Formats the metric data by filling in any blank months and calculating
 * cumulative counts for the charts
 *
 * Deals with data coming back from different types of fields (e.g. multi_select, date etc.)
 */
function formatMonthData(monthlyData) {
    const { fieldType } = window.dtMetricsProject.state

    if ( window.dtMetricsProject.multi_fields.includes(fieldType)) {
        return formatCompoundMonthData(monthlyData)
    } else {
        return formatSimpleMonthData(monthlyData)
    }
}

function isInFuture(monthNumber) {
    const { year } = window.dtMetricsProject.state
    const now = new Date()
    return now.getUTCFullYear() === parseInt(year) && monthNumber > now.getMonth() + 1
}

function formatSimpleMonthData(monthlyData) {
    const monthLabels = window.SHAREDFUNCTIONS.get_months_labels()

    let cumulativeTotal = window.dtMetricsProject.cumulative_offset
    const formattedMonthlyData = monthLabels.map((monthLabel, i) => {
        const monthNumber = i + 1
        if (isInFuture(monthNumber)) {
            return {
                month: monthLabel,
            }
        }

        const monthData = monthlyData.find((mData) => mData.month === String(monthNumber) )
        const count = monthData ? parseInt(monthData.count) : 0
        cumulativeTotal = cumulativeTotal + count

        return {
            'month': monthLabel,
            'count': count,
            'cumulative_count': cumulativeTotal
        }
    })

    return formattedMonthlyData
}

function formatCompoundMonthData(monthlyData) {
    const monthLabels = window.SHAREDFUNCTIONS.get_months_labels()
    const keys = getDataKeys(monthlyData)

    const cumulative_offsets = window.dtMetricsProject.cumulative_offset
    let cumulativeTotals = {}
    const cumulativeKeys = makeCumulativeKeys(keys)

    // initialise the totals with the offset data
    cumulativeTotals = calculateCumulativeTotals(keys, cumulative_offsets, cumulativeTotals, cumulativeKeys )

    const formattedMonthlyData = monthLabels.map((monthLabel, i) => {
        const monthNumber = i + 1
        if ( isInFuture(monthNumber) ) {
            return {
                month: monthLabel,
            }
        }

        const monthData = monthlyData.find((mData) => mData.month === String(monthNumber)) || {}
        cumulativeTotals = calculateCumulativeTotals(keys, monthData, cumulativeTotals, cumulativeKeys)

        return {
            ...monthData,
            ...cumulativeTotals,
            month: monthLabel,
        }
    })

    return formattedMonthlyData
}

function makeCumulativeKeys(keys) {
    const cumulativeKeys = {}
    keys.forEach((key) => {
        const cumulativeKey = `${CUMULATIVE_PREFIX}${key}`
        cumulativeKeys[key] = cumulativeKey
    })
    return cumulativeKeys
}

function calculateCumulativeTotals(keys, data, cumulativeTotals, cumulativeKeys) {
    // add onto previous data to get cumulative totals
    // each key always has a value >= 0
  keys.forEach((key) => {
        const count = ((typeof data !== 'undefined') && data[key]) ? parseInt(data[key]) : 0
        const cumulativeKey = cumulativeKeys[key]
        if (!cumulativeTotals[cumulativeKey] && count > 0) {
            cumulativeTotals[cumulativeKey] = count
            return
        } else if (cumulativeTotals[cumulativeKey] && count > 0) {
            cumulativeTotals[cumulativeKey] = cumulativeTotals[cumulativeKey] + count
        }
    })

    return cumulativeTotals
}

function getDataKeys(data) {
    if (data.length === 0) return []

    let dataWithKeys = {}
    // loop over all data and merge them all to make sure we get an object with the keys in
    // (as some months/years might be empty)
    data.forEach((d) => {
        dataWithKeys = {
            ...dataWithKeys,
            ...d,
        }
    })
    const keys = Object.keys(dataWithKeys).filter((key) => key !== 'month' && key !== 'year' )
    if (Object.keys(keys).length === 0) {
        return []
    }
    return keys
}

function isCumulativeKey(key) {
    return key.includes(CUMULATIVE_PREFIX)
}
