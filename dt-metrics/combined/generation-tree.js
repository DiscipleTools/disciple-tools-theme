jQuery(function () {
  if (window.wpApiShare.url_path.startsWith('metrics/combined/generation_tree')) {
    project_generation_tree();
  }
});

const getFieldSettings = (postType) =>
  makeRequest('GET', `metrics/field_settings/${postType}`)

const getGenerationTree = (data) =>
  makeRequest('POST', `metrics/generation_tree`, data)


const escapeObject = window.SHAREDFUNCTIONS.escapeObject

function project_generation_tree() {
  const chartDiv = document.querySelector('#chart');

  const {
    title_date_range_activity,
    post_type_select_label,
    post_field_select_label,
    submit_button_label
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
        <div id="post-field-value-entry-div"></div>

        <br>
        <button class="button" id="post-field-submit-button">${submit_button_label}</button>
        <div id="chart-loading-spinner" class="loading-spinner"></div>
    </section>
    <hr>
    <div id="generation_tree_results_div" style="display: none;"></div>
  `;

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

  // Add submit button event listener.
  document.querySelector('#post-field-submit-button').addEventListener('click', (e) => {
    let post_type = $('#post-type-select').val();
    let field_id = $('#post-field-select').val();
    let loadingSpinner = document.querySelector('#chart-loading-spinner');
    loadingSpinner.classList.add('active');

    // Request new generational tree.
    getGenerationTree({
      'post_type': post_type,
      'field': field_id
    })
    .promise()
    .then((response) => {
      let generation_tree_results_div = $('#generation_tree_results_div');

      // Refresh generation tree results.
      generation_tree_results_div.fadeOut('fast', function () {
        generation_tree_results_div.html(response);

        // Display result findings.
        generation_tree_results_div.fadeIn('fast');
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
  .reduce((options, [key, setting]) => {
    options[key] = setting.name
    return options
  }, {})
  const postFieldOptions = escapeObject(unescapedOptions)
  const sortedOptions = Object.entries(postFieldOptions).sort(([key1, value1], [key2, value2]) => {
    if (value1 < value2) return -1
    if (value1===value2) return 0
    if (value1 > value2) return 1
  })
  return sortedOptions.map(([value, label]) => `
        <option value="${value}"> ${label} </option>
    `)
}
