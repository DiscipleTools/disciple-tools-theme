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
    const fieldLabels = keys.map((key) => {
        const fieldSettings = window.dtMetricsProject.field_settings[field]
        const defaultSettings = fieldSettings && fieldSettings.default ? fieldSettings.default : []

        const newKey = isCumulativeKey(key) ? key.replace(CUMULATIVE_PREFIX, '') : key

        let label = ''
        if (defaultSettings[newKey]) {
            label = window.lodash.escape( defaultSettings[newKey].label )
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
    const series = fieldLabels.map(({ field, label }) => {
        if (graphType === 'stacked') {
            if (single && !firstSerie) {
                return createColumnSeries(chart, field, label, true)
            }
            firstSerie = false
            return createColumnSeries(chart, field, label)
        } else if (graphType === 'line') {
            if (single && !firstSerie) {
                return createLineSeries(chart, field, label, true)
            }
            firstSerie = false
            return createLineSeries(chart, field, label)
        }
    })

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

function initialiseChart(id) {
    const { year, chart_view: view } = window.dtMetricsProject.state
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

    chart.data = data

    return [ chart, valueAxis ]
}

function createColumnSeries(chart, field, name, hidden = false) {
    const { chart_view } = window.dtMetricsProject.state
    const { tooltip_label } = escapeObject(window.dtMetricsProject.translations)

    const tooltipLabel = tooltip_label.replace('%1$s', '{name}').replace('%2$s', '{categoryX}')

    const series = chart.series.push( new window.am4charts.ColumnSeries())
    series.dataFields.valueY = field
    series.dataFields.categoryX = chart_view
    series.name = name
    series.columns.template.tooltipText = `[#fff font-size: 12px]${tooltipLabel}:\n[/][#fff font-size: 15px]{valueY}[/] [#fff]{additional}[/]`
    series.stacked = true
    if (hidden) {
        series.hide()
    }

    // Capture event clicks.
    series.columns.template.events.on("hit", function (e) {
      let target = e.target;
      let date_key = target.dataItem.component.dataFields.categoryX;
      let metric_key = target.dataItem.component.dataFields.valueY;
      let data = target.dataItem.dataContext;

      displayPostListModal(data[date_key], date_key, metric_key);
    });

    return series
}

function createLineSeries(chart, field, name, hidden = false) {
    const { chart_view } = window.dtMetricsProject.state
    const { tooltip_label } = escapeObject(window.dtMetricsProject.translations)
    const tooltipLabel = tooltip_label.replace('%1$s', '{name}').replace('%2$s', '{categoryX}')

    let lineSeries = chart.series.push(new window.am4charts.LineSeries());
    lineSeries.name = name
    lineSeries.dataFields.valueY = field;
    lineSeries.dataFields.categoryX = chart_view

//    lineSeries.stroke = window.am4core.color("#fdd400");
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
    let selected_posts = [];
    let posts = window.dtMetricsProject.state['posts'];

    if (posts && posts.length > 0) {

      // Filter out required posts.
      let limit = 100;
      jQuery.each(posts, function (idx, post) {
        if (post['id'] && (post[date_key] && post[date_key] === date) && (post['value'] && window.lodash.includes(metric_key, post['value']))) {
          if (--limit >= 0) {
            selected_posts.push(post);
          }
        }
      });

      // Proceed with displaying post list.
      if (selected_posts.length > 0) {
        let sorted_posts = window.lodash.orderBy(selected_posts, ['name'], ['asc']);
        let list_html = `
        <br>
        ${(function (posts_to_filter) {
            let post_list_html = ``;
            jQuery.each(posts_to_filter, function (idx, post) {
              let url = window.dtMetricsProject.site + window.dtMetricsProject.state.post_type + '/' + post['id'];

              post_list_html += `
              <div>
                <a href="${url}" target="_blank">${post['name'] ? post['name'] : post['id']}</a>
              </div>
              `;
            });
            return post_list_html;
        })(sorted_posts)}
        <br>
        `;

        // Render post html list.
        let content = jQuery('#template_metrics_modal_content');
        jQuery('#template_metrics_modal_title').empty().html(window.lodash.escape(window.dtMetricsProject.translations.modal_title));
        jQuery(content).css('max-height', '300px');
        jQuery(content).css('overflow', 'auto');
        jQuery(content).empty().html(list_html);
        jQuery('#template_metrics_modal').foundation('open');
      }
    }
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
    const { post_type: postType, field, year } = window.dtMetricsProject.state

    const isAllTime = year === 'all-time'
    const data = isAllTime
        ? getTimeMetricsByYear(postType, field)
        : getTimeMetricsByMonth(postType, field, year)

    const loadingSpinner = document.querySelector('.loading-spinner')
    const chartElement = document.querySelector('#chart-area')
    loadingSpinner.classList.add('active')
    data.promise()
        .then(({ data, posts, cumulative_offset }) => {
          window.dtMetricsProject.state['posts'] = posts;

            if ( !data ) {
                throw new Error('no data object returned')
            }
            window.dtMetricsProject.cumulative_offset = cumulative_offset
            window.dtMetricsProject.data = isAllTime
                ? formatYearData(data)
                : formatMonthData(data)
            chartElement.dispatchEvent( new Event('datachange') )
            loadingSpinner.classList.remove('active')
        })
        .catch((error) => {
            console.log(error)
            chartElement.dispatchEvent( new Event('datachange') )
            loadingSpinner.classList.remove('active')
        })
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
