jQuery(function () {
  if (window.wpApiShare.url_path.startsWith('metrics/records/generation_tree')) {
    project_generation_tree();
  }
});

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

  jQuery('#metrics-sidemenu').foundation('down', jQuery('#records-menu'));

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

        <br>
        <button class="button" id="post-field-submit-button">${submit_button_label}</button>
        <div id="chart-loading-spinner" class="loading-spinner"></div>
    </section>
    <hr>
    <div id="generation_map" class="scrolling-wrapper" style="display: none;"></div>
    <br>
  `;

  // Add post type event listener.
  const fieldSelectElement = document.querySelector('#post-field-select')
  document.querySelector('#post-type-select').addEventListener('change', (e) => {
    dtMetricsProject.state.post_type = e.target.value;
    fieldSelectElement.innerHTML = buildFieldSelectOptions();
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
      let generation_map = $('#generation_map');

      // Refresh generation tree results.
      generation_map.fadeOut('fast', function () {
        generation_map.html(response);

        // Ensure end nodes are capped.
        let last_node = generation_map.find('li:last-child.li-gen-0');
        if (last_node) {
          last_node.addClass('last');
          last_node.find('li').addClass('last');
        }

        // Display result findings.
        generation_map.fadeIn('fast');
        loadingSpinner.classList.remove('active');
      });
    })
    .catch((error) => {
      console.log(error);
    });
  });
}

function buildFieldSelectOptions() {

  // Detect and filter out suitable connection fields by selected post type.
  dtMetricsProject.field_settings = {};
  let post_type = dtMetricsProject.state.post_type;

  if (dtMetricsProject.all_post_types[post_type] && dtMetricsProject.all_post_types[post_type]['fields']) {
    $.each(dtMetricsProject.all_post_types[post_type]['fields'], function (field_id, field_settings) {
      if ((field_settings['type'] == 'connection') && (field_settings['post_type'] == post_type)) {
        dtMetricsProject.field_settings[field_id] = field_settings;
      }
    });
  }

  // Proceed with select options html generation.
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
