jQuery(function() {
    if (window.wpApiShare.url_path.startsWith( 'metrics/combined/time_charts' )) {
        projectTimeCharts()
    }
})

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
    } = escapeObject(dtMetricsProject.translations)

    const postTypeOptions = escapeObject(dtMetricsProject.select_options.post_type_select_options)
    const postFieldOptions = escapeObject(dtMetricsProject.select_options.post_field_select_options)

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
                ${ Object.entries(postFieldOptions).map(([value, label]) => `
                    <option value="${value}"> ${label} </option>
                `) }
            </select>
        </section>
        <hr>
    `

}