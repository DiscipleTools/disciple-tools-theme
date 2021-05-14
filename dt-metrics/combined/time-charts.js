jQuery(function() {
    if (window.wpApiShare.url_path.startsWith( 'metrics/combined/time_charts' )) {
        projectTimeCharts()
    }
})

const CUMULATIVE_PREFIX = 'cumulative_'
const graphTypes = ['stacked', 'line']

const getTimeMetricsByYear = (postType, field) =>
makeRequest('GET', `metrics/time_metrics_by_year/${postType}/${field}`)

const getTimeMetricsByMonth = (postType, field, year) =>
makeRequest('GET', `metrics/time_metrics_by_month/${postType}/${field}/${year}`)

const getFieldSettings = (postType) =>
makeRequest('GET', `metrics/field_settings/${postType}`)

function escapeObject(obj) {
    return Object.fromEntries(Object.entries(obj).map(([key, value]) => {
        return [ key, window.lodash.escape(value)]
    }))
}

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
    } = escapeObject(dtMetricsProject.translations)

    const postTypeOptions = escapeObject(dtMetricsProject.select_options.post_type_select_options)

    jQuery('#metrics-sidemenu').foundation('down', jQuery('#combined-menu'));

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

    const chartSection = document.querySelector('#chart-area')
    const loadingSpinner = document.querySelector('#chart-loading-spinner')
    chartSection.addEventListener('datachange', () => {
        createCharts()
        loadingSpinner.classList.remove('active')
    })
    const fieldSelectElement = document.querySelector('#post-field-select')

    document.querySelector('#post-type-select').addEventListener('change', (e) => {
        const postType = e.target.value
        dtMetricsProject.state.post_type = postType
        getFieldSettings(postType)
            .promise()
            .then((data) => {
                dtMetricsProject.field_settings = data
                fieldSelectElement.innerHTML = buildFieldSelectOptions()
                fieldSelectElement.dispatchEvent( new Event('change') )
            })
            .catch((error) => {
                console.log(error)
            })
    })

    fieldSelectElement.addEventListener('change', (e) => {
        dtMetricsProject.state.field = e.target.value
        if (!dtMetricsProject.field_settings[e.target.value]) {
            console.error(e.target.value, 'not found in', dtMetricsProject.field_settings)
            return
        }
        dtMetricsProject.state.fieldType = dtMetricsProject.field_settings[e.target.value].type
        getData()
    })

    document.querySelector('#date-select').addEventListener('change', (e) => {
        const year = e.target.value
        dtMetricsProject.state.year = year
        dtMetricsProject.state.chart_view = year === 'all-time' ? 'year' : 'month'
        getData()
    })

    // trigger the first get of data on page load
    fieldSelectElement.dispatchEvent( new Event('change') )
}

function buildFieldSelectOptions() {
    const unescapedOptions = Object.entries(dtMetricsProject.field_settings)
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
    const { earliest_year } = dtMetricsProject.state

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
    const { fieldType } = dtMetricsProject.state
    const {
        added_label,
        total_label,
    } = escapeObject(dtMetricsProject.translations)
    const data = dtMetricsProject.data

    // if date field create cumulative and addition charts
    if (fieldType === 'date' || fieldType === 'connection') {
        hideChart('stacked-chart')
        createChart('cumulative-chart', ['cumulative_count'], {
            customLabel: total_label,
        })
        createChart('additions-chart', ['count'], {
            customLabel: added_label,
            graphType: 'line'
        })
    } else if ( dtMetricsProject.multi_fields.includes(fieldType)) {
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
    const { field, fieldType } = dtMetricsProject.state
    const { true_label, false_label } = escapeObject(dtMetricsProject.translations)

    if (!graphTypes.includes(graphType)) {
        throw new Error(`graphType ${graphType} not found in ${graphTypes}`)
    }

    showChart(id)
    const [ chart, valueAxis ] = initialiseChart(id)

    // create the series for each key name in the data arrays
    // then get the labels from field_settings, if they exist
    const fieldLabels = keys.map((key) => {
        const fieldSettings = dtMetricsProject.field_settings[field]
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

    const legendContainer = am4core.create(legendDiv, am4core.Container)
    legendContainer.width = am4core.percent(100)
    legendContainer.height = am4core.percent(100)
    chart.legend = new am4charts.Legend()
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
    const { year, chart_view: view } = dtMetricsProject.state
    const {
        all_time,
    } = escapeObject(dtMetricsProject.translations)

    am4core.options.autoDispose = true

    const chartSection = document.getElementById(id)
    const timechartDiv = chartSection.querySelector('.timechart')

    const chart = am4core.create(timechartDiv, am4charts.XYChart)
    const data = dtMetricsProject.data

    const categoryAxis = chart.xAxes.push( new am4charts.CategoryAxis() )
    categoryAxis.dataFields.category = view
    categoryAxis.title.text = year === 'all-time' ? all_time : String(year)

    const valueAxis = chart.yAxes.push( new am4charts.ValueAxis() )
    valueAxis.maxPrecision = 0

    chart.data = data

    return [ chart, valueAxis ]
}

function createColumnSeries(chart, field, name, hidden = false) {
    const { chart_view } = dtMetricsProject.state
    const { tooltip_label } = escapeObject(dtMetricsProject.translations)

    const tooltipLabel = tooltip_label.replace('%1$s', '{name}').replace('%2$s', '{categoryX}')

    const series = chart.series.push( new am4charts.ColumnSeries())
    series.dataFields.valueY = field
    series.dataFields.categoryX = chart_view
    series.name = name
    series.columns.template.tooltipText = `[#fff font-size: 12px]${tooltipLabel}:\n[/][#fff font-size: 15px]{valueY}[/] [#fff]{additional}[/]`
    series.stacked = true
    if (hidden) {
        series.hide()
    }

    return series
}

function createLineSeries(chart, field, name, hidden = false) {
    const { chart_view } = dtMetricsProject.state
    const { tooltip_label } = escapeObject(dtMetricsProject.translations)
    const tooltipLabel = tooltip_label.replace('%1$s', '{name}').replace('%2$s', '{categoryX}')
    
    let lineSeries = chart.series.push(new am4charts.LineSeries());
    lineSeries.name = name
    lineSeries.dataFields.valueY = field;
    lineSeries.dataFields.categoryX = chart_view

//    lineSeries.stroke = am4core.color("#fdd400");
    lineSeries.strokeWidth = 3;
    lineSeries.propertyFields.strokeDasharray = "lineDash";
    lineSeries.tooltip.label.textAlign = "middle";
    if (hidden) {
        lineSeries.hide()
    }

    let bullet = lineSeries.bullets.push(new am4charts.Bullet());
//    bullet.fill = am4core.color("#fdd400"); // tooltips grab fill from parent by default
    bullet.tooltipText = `[#fff font-size: 12px]${tooltipLabel}:\n[/][#fff font-size: 15px]{valueY}[/] [#fff]{additional}[/]`
    let circle = bullet.createChild(am4core.Circle);
    circle.radius = 4;
    circle.fill = am4core.color("#fff");
    circle.strokeWidth = 3;

    return lineSeries
}

function getMinMaxValuesOfDataForKey(key) {
    const data = dtMetricsProject.data
    let min = 0
    let max = 0

    data.forEach(timePeriodData => {
        const dataStr = timePeriodData[key]
        if (!dataStr || dataStr.length === 0) return
        dataVal = parseInt(dataStr)

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
    const { post_type: postType, field, year } = dtMetricsProject.state

    const isAllTime = year === 'all-time'
    const data = isAllTime
        ? getTimeMetricsByYear(postType, field)
        : getTimeMetricsByMonth(postType, field, year)

    const loadingSpinner = document.querySelector('.loading-spinner')
    const chartElement = document.querySelector('#chart-area')
    loadingSpinner.classList.add('active')
    data.promise()
        .then(({ data, cumulative_offset }) => {
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
    const { fieldType } = dtMetricsProject.state

    if (fieldType === 'date' || fieldType === 'connection') {
        return formatSimpleYearData(yearlyData)
    } else if ( dtMetricsProject.multi_fields.includes(fieldType)) {
        return formatCompoundYearData(yearlyData)
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
    const { fieldType } = dtMetricsProject.state

    if (fieldType === 'date' || fieldType === 'connection') {
        return formatSimpleMonthData(monthlyData)
    } else if ( dtMetricsProject.multi_fields.includes(fieldType)) {
        return formatCompoundMonthData(monthlyData)
    }
}

function isInFuture(monthNumber) {
    const { year } = dtMetricsProject.state
    const now = new Date()
    return now.getUTCFullYear() === parseInt(year) && monthNumber > now.getMonth() + 1
}

function formatSimpleMonthData(monthlyData) {
    const monthLabels = window.wpApiShare.translations.month_labels

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
    const monthLabels = window.wpApiShare.translations.month_labels
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
        const count = data[key] ? parseInt(data[key]) : 0
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
