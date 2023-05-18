jQuery(function () {
  if (window.wpApiShare.url_path.startsWith('metrics/combined/date_range_activity')) {
    project_activity_during_date_range();
  }
});

const getFieldSettings = (postType) =>
  makeRequest('GET', `metrics/field_settings/${postType}`)

const getDateRangeActivity = (data) =>
  makeRequest('GET', `metrics/date_range_activity`, data)

const escapeObject = window.SHAREDFUNCTIONS.escapeObject

function project_activity_during_date_range() {
  const chartDiv = document.querySelector('#chart');

  const {
    title_date_range_activity,
    post_type_select_label,
    post_field_select_label,
    date_select_label,
    submit_button_label,
    total_label,
    results_table_head_title_label,
    results_table_head_date_label
  } = escapeObject(dtMetricsProject.translations);

  const postTypeOptions = escapeObject(dtMetricsProject.select_options.post_type_select_options);

  jQuery('#metrics-sidemenu').foundation('down', jQuery('#combined-menu'));

  // Display initial controls.
  chartDiv.innerHTML = `
    <div class="section-header">${title_date_range_activity}</div>
    <section class="chart-controls">
        <label class="section-subheader" for="post-type-select">${post_type_select_label}</label>
        <select class="select-field" id="post-type-select">
            ${Object.entries(postTypeOptions).map(([value, label]) => `
                <option value="${value}"> ${label} </option>
            `)}
        </select>

        <label class="section-subheader" for="post-field-select">${post_field_select_label}</label>
        <select class="select-field" id="post-field-select">
            ${buildFieldSelectOptions()}
        </select>
        <select class="select-field" id="post-field-condition-select">
            ${buildFieldConditionSelectOptions()}
        </select>
        <div id="post-field-value-entry-div"></div>

        <label class="section-subheader" for="date-select">${date_select_label}</label>
        <div class="date_range_picker">
            <i class="fi-calendar"></i>&nbsp;
            <span>${moment().format("YYYY")}</span>
            <i class="dt_caret down"></i>
        </div>

        <br><br>
        <button class="button" id="post-field-submit-button">${submit_button_label}</button>
        <div id="chart-loading-spinner" class="loading-spinner"></div>
    </section>
    <hr>
    <div id="activity_results_div" style="display: none;">
        <h2>${total_label}: <span id="activity_results_total">0</span></h2>
        <br>
        <table>
            <thead>
                <tr>
                    <th>${results_table_head_title_label}</th>
                    <th>${results_table_head_date_label}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
  `;

  // Activate date range picker element.
  window.METRICS.setupDatePicker(
    `${dtMetricsProject.root}dt/v1/metrics/dummy_endpoint`,
    function (data, label) {
      $('.date_range_picker span').html(label);
      $('#post-field-submit-button').click();
    },
    moment().startOf('year')
  );

  // Display field value entry accordingly based on selected field.
  refreshFieldValueEntryElement();

  // Add post type event listener.
  const fieldSelectElement = document.querySelector('#post-field-select')
  document.querySelector('#post-type-select').addEventListener('change', (e) => {
    const postType = e.target.value
    dtMetricsProject.state.post_type = postType;
    getFieldSettings(postType)
    .promise()
    .then((data) => {
      console.log(data);
      dtMetricsProject.field_settings = data;
      fieldSelectElement.innerHTML = buildFieldSelectOptions();
      fieldSelectElement.dispatchEvent(new Event('change'));
    })
    .catch((error) => {
      console.log(error)
    });
  });

  // Add field event listener.
  fieldSelectElement.addEventListener('change', (e) => {
    refreshFieldValueEntryElement();
  });

  // Add submit button event listener.
  document.querySelector('#post-field-submit-button').addEventListener('click', (e) => {
    let date_range_picker = $('.date_range_picker').data('daterangepicker');
    let loadingSpinner = document.querySelector('#chart-loading-spinner');
    loadingSpinner.classList.add('active');

    // Fetch records matching activity within specified date range.
    getDateRangeActivity({
      'post_type': $('#post-type-select').val(),
      'field': $('#post-field-select').val(),
      'condition': $('#post-field-condition-select').val(),
      'value': $('#post-field-value').val(),
      'ts_start': (date_range_picker.startDate.unix() > 0) ? date_range_picker.startDate.unix() : 0,
      'ts_end': date_range_picker.endDate.unix()
    })
    .promise()
    .then((response) => {

      let total = response['total'];
      let posts = response['posts'];

      let activity_results_div = $('#activity_results_div');

      // Refresh activity during date range results display.
      activity_results_div.fadeOut('fast', function () {
        $('#activity_results_total').html(total);

        // Refresh and re-populate results table.
        let table = activity_results_div.find('table');
        table.fadeOut('fast');
        if(total && (total > 0)) {

          let tbody = table.find('tbody');
          tbody.empty();

          posts.forEach(function (post) {
            if (post['ID'] && post['post_title'] && post['post_date']) {
              let post_url = dtMetricsProject.site_url + '/' + post['post_type'] + '/' + post['ID'];
              tbody.append(`
                <tr>
                    <td><a href="${post_url}" target="_blank">${window.lodash.escape(post['post_title'])}</a></td>
                    <td>${window.lodash.escape(post['post_date']['formatted'])}</td>
                </tr>
              `);
            }
          });

          table.fadeIn('fast');
        }

        // Display result findings.
        activity_results_div.fadeIn('fast');
        loadingSpinner.classList.remove('active');

      });

    })
    .catch((error) => {
      console.log(error);
    });

  });
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

function buildFieldConditionSelectOptions() {
  let conditions = Object.entries(dtMetricsProject.field_conditions)
  .reduce((options, [key, label]) => {
    options[key] = label
    return options
  }, {});

  let html = ``;
  Object.entries(conditions).forEach(([key, label]) => {
    html += `<option value="${key}">${label}</option>`;
  });

  return html;
}

function refreshFieldValueEntryElement() {

  // Empty any previous entries.
  let entry_div = jQuery('#post-field-value-entry-div');
  entry_div.empty();

  // Determine field id to be refreshed.
  let field_settings = dtMetricsProject.field_settings;
  let field_id = jQuery('#post-field-select').val();
  if (field_id && field_settings[field_id]) {

    // Based on field type, determine html element style to be adopted.
    if (window.lodash.includes(['key_select', 'multi_select', 'link'], field_settings[field_id]['type'])) {
      let options_html = ``;
      Object.entries(field_settings[field_id]['default']).forEach(([key, option]) => {
        options_html += `<option value="${key}">${option['label']}</option>`;
      });
      entry_div.html(`<select class="select-field" id="post-field-value">${options_html}</select>`);
    } else {
      entry_div.html('<input type="text" id="post-field-value" />');
    }
  }
}
