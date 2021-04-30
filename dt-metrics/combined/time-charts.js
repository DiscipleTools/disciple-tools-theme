jQuery(function() {
    if (window.wpApiShare.url_path.startsWith( 'metrics/combined/time_charts' )) {
        projectTimeCharts()
    }
})

const CUMULATIVE_PREFIX = 'cumulative_'

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
        total_label,
        added_label,
        tooltip_label,
        date_select_label,
        all_time,
    } = escapeObject(dtMetricsProject.translations)

    const {
        chart_view: view,
    } = dtMetricsProject.state

    const now = new Date()
    const year = now.getUTCFullYear()

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
                <option value="${year}">${year}</option>
                <option value="${year - 1}">${year - 1}</option>
                <option value="${year - 2}">${year - 2}</option>
                <option value="all-time">${all_time}</option>
            </select>
        </section>
        <hr>
        <section>
            <div id="chartdiv" class="timechart"></div>
            <div id="legenddiv" ></div>
        </section>
    `

    const chartSection = document.querySelector('#chartdiv')
    chartSection.addEventListener('datachange', () => {
        createChart()
    })
    const fieldSelectElement = document.querySelector('#post-field-select')

    document.querySelector('#post-type-select').addEventListener('change', (e) => {
        const postType = e.target.value
        dtMetricsProject.state.post_type = postType
        window.API
            .getFieldSettings(postType)
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
        if (value1 = value2) return 0
        if (value1 > value2) return 1
    })
    return sortedOptions.map(([value, label]) => `
        <option value="${value}"> ${label} </option>
    `)
}

function createChart() {
    const { year, fieldType, chart_view: view } = dtMetricsProject.state
    const {
        all_time,
    } = escapeObject(dtMetricsProject.translations)

    const chartSection = document.querySelector('#chartdiv')
    am4core.useTheme(am4themes_animated);
    am4core.options.autoDispose = true

    const chart = am4core.create(chartSection, am4charts.XYChart)
    const data = dtMetricsProject.data

    const categoryAxis = chart.xAxes.push( new am4charts.CategoryAxis() )
    categoryAxis.dataFields.category = view
    categoryAxis.title.text = year === 'all-time' ? all_time : String(year)

    const valueAxis = chart.yAxes.push( new am4charts.ValueAxis() )

    chart.data = data

    if (fieldType === 'date') {
        createCumulativeChart(chart)
    } else if (fieldType === 'multi_select') {
        const keys = getDataKeys(data)
        const totalKeys = keys.filter((key) => !key.includes('cumulative_') )
        const cumulativeKeys = keys.filter((key) => key.includes('cumulative_') )
        createStackedChart(chart, cumulativeKeys)
    }

}

function createCumulativeChart(chart) {
    const {
        total_label,
        added_label,
        tooltip_label,
    } = escapeObject(dtMetricsProject.translations)
    const { chart_view: view } = dtMetricsProject.state

    const tooltipLabel = tooltip_label.replace('%1$s', '{name}').replace('%2$s', '{categoryX}')

    const columnSeries = chart.series.push( new am4charts.ColumnSeries() )
    columnSeries.name = total_label
    columnSeries.dataFields.valueY = 'cumulativeTotal'
    columnSeries.dataFields.categoryX = view
    columnSeries.columns.template.tooltipText = `[#fff font-size: 15px]${tooltipLabel}:\n[/][#fff font-size: 20px]{valueY}[/] [#fff]{additional}[/]`
    columnSeries.columns.template.propertyFields.fillOpacity = "fillOpacity";
    columnSeries.columns.template.propertyFields.stroke = "stroke";
    columnSeries.columns.template.propertyFields.strokeWidth = "strokeWidth";
    columnSeries.columns.template.propertyFields.strokeDasharray = "columnDash";
    columnSeries.tooltip.label.textAlign = "middle";

    let lineSeries = chart.series.push(new am4charts.LineSeries());
    lineSeries.name = added_label
    lineSeries.dataFields.valueY = "count";
    lineSeries.dataFields.categoryX = view

    lineSeries.stroke = am4core.color("#fdd400");
    lineSeries.strokeWidth = 3;
    lineSeries.propertyFields.strokeDasharray = "lineDash";
    lineSeries.tooltip.label.textAlign = "middle";

    let bullet = lineSeries.bullets.push(new am4charts.Bullet());
    bullet.fill = am4core.color("#fdd400"); // tooltips grab fill from parent by default
    bullet.tooltipText = `[#fff font-size: 15px]${tooltipLabel}:\n[/][#fff font-size: 20px]{valueY}[/] [#fff]{additional}[/]`
    let circle = bullet.createChild(am4core.Circle);
    circle.radius = 4;
    circle.fill = am4core.color("#fff");
    circle.strokeWidth = 3;
}

function createStackedChart(chart, keys) {
    const { field, chart_view } = dtMetricsProject.state

    const createSeries = (field, name) => {
        const series = chart.series.push( new am4charts.ColumnSeries())
        series.dataFields.valueY = field
        series.dataFields.categoryX = chart_view
        series.name = name
        series.columns.template.tooltipText = '{name}: [bold]{valueY}[/]'
        series.stacked = true
    }

    // create the series for each key name in the data arrays
    // then get the labels from field_settings, if they exist
    const fieldLabels = keys.map((key) => {
        const fieldSettings = dtMetricsProject.field_settings[field]
        const defaultSettings = fieldSettings ? fieldSettings.default : []

        const newKey = isCumulativeKey(key) ? key.replace(CUMULATIVE_PREFIX, '') : key
        const label = defaultSettings[newKey] ? defaultSettings[newKey].label : newKey
        return {
            field: key,
            label: label,
        }
    })
    // then loop over the keys and labels and create the stacked series
    fieldLabels.forEach(({ field, label }) => {
        createSeries(field, label)
    })

    const legendContainer = am4core.create('legenddiv', am4core.Container)
    legendContainer.width = am4core.percent(100)
    legendContainer.height = am4core.percent(100)
    chart.legend = new am4charts.Legend()
    chart.legend.parent = legendContainer

    const resizeLegend = (e) => {
        document.getElementById('legenddiv').style.height = `${chart.legend.contentHeight}px`
    }

    chart.events.on('datavalidated', resizeLegend)
    chart.events.on('maxsizechanged', resizeLegend)

    chart.legend.events.on('datavalidated', resizeLegend)
    chart.legend.events.on('maxsizechanged', resizeLegend)

}

function getData() {
    const { post_type: postType, field, year } = dtMetricsProject.state

    const isAllTime = year === 'all-time'
    const data = isAllTime
        ? window.API.getTimeMetricsByYear(postType, field)
        : window.API.getTimeMetricsByMonth(postType, field, year)

    data.promise()
        .then((data) => {
            window.dtMetricsProject.data = isAllTime 
                ? formatYearData(data)
                : formatMonthData(data)
            const chartElement = document.querySelector('#chartdiv')
            chartElement.dispatchEvent( new Event('datachange') )
        })
        .catch((error) => {
            console.log(error)
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

    if (fieldType === 'date') {
        return formatSimpleYearData(yearlyData)
    } else if (fieldType === 'multi_select') {
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
            cumulativeTotal,
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

    if (fieldType === 'date') {
        return formatSimpleMonthData(monthlyData)
    } else if (fieldType === 'multi_select') {
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

    let cumulativeTotal = 0
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
            'cumulativeTotal': cumulativeTotal
        }
    })

    return formattedMonthlyData
}

function formatCompoundMonthData(monthlyData) {
    const monthLabels = window.wpApiShare.translations.month_labels
    const keys = getDataKeys(monthlyData)

    let cumulativeTotals = {}
    const cumulativeKeys = makeCumulativeKeys(keys)

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
