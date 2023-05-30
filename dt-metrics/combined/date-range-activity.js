jQuery(function () {
  if (window.wpApiShare.url_path.startsWith('metrics/combined/date_range_activity')) {
    project_activity_during_date_range();
  }
});

const getFieldSettings = (postType) =>
  makeRequest('GET', `metrics/field_settings/${postType}`)

const renderFieldHtml = (data) =>
  makeRequest('GET', `metrics/render_field_html`, data)

const getDateRangeActivity = (data) =>
  makeRequest('POST', `metrics/date_range_activity`, data)

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
    let field_settings = dtMetricsProject.field_settings;
    let date_range_picker = $('.date_range_picker').data('daterangepicker');
    let post_type = $('#post-type-select').val();
    let field_id =  $('#post-field-select').val();
    let field_type = field_settings[field_id]['type'];
    let value = $('#post-field-value').val();
    let loadingSpinner = document.querySelector('#chart-loading-spinner');
    loadingSpinner.classList.add('active');

    // Capture Typeahead values if special field type is selected.
    if (window.lodash.includes(['connection', 'user_select', 'location'], field_type)) {
      let typeahead = window.Typeahead['.js-typeahead-' + field_id];
      if (typeahead) {
        switch (field_type) {
          case 'connection':
          case 'location': {
            value = typeahead.items;
            break;
          }
          case 'user_select': {
            value = typeahead.item;
            break;
          }
        }
      } else {
        value = {};
      }
    }

    // Fetch records matching activity within specified date range.
    getDateRangeActivity({
      'post_type': post_type,
      'field': field_id,
      'value': value,
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
            if (post['id'] && post['name'] && post['timestamp']) {
              let post_url = dtMetricsProject.site_url + '/' + post['post_type'] + '/' + post['id'];
              tbody.append(`
                <tr>
                    <td><a href="${post_url}" target="_blank">${window.lodash.escape(post['name'])}</a></td>
                    <td>${window.lodash.escape(moment.unix(post['timestamp']).format('dddd, MMMM Do YYYY, h:mm:ss A'))}</td>
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

function refreshFieldValueEntryElement() {

  // Empty any previous entries.
  let entry_div = jQuery('#post-field-value-entry-div');
  entry_div.empty();

  // Determine field id to be refreshed.
  let field_settings = dtMetricsProject.field_settings;
  let field_id = jQuery('#post-field-select').val();
  if (field_id && field_settings[field_id]) {

    // Based on field type & associated defaults, determine html element style to be adopted.
    if (field_settings[field_id]['default'] && Object.keys(field_settings[field_id]['default']).length > 0) {
      let options_html = `<option value="">[ ${window.lodash.escape(dtMetricsProject.translations['post_field_select_any_activity_label'])} ]</option>`;
      Object.entries(field_settings[field_id]['default']).forEach(([key, option]) => {
        options_html += `<option value="${key}">${option['label']}</option>`;
      });
      entry_div.html(`<select class="select-field" id="post-field-value">${options_html}</select>`);

    } else if (window.lodash.includes(['connection', 'user_select', 'location'], field_settings[field_id]['type'])) {

      // Fetch html rendering for selected field.
      renderFieldHtml({
        'post_type': $('#post-type-select').val(),
        'field_id': field_id
      }).promise()
      .then((response) => {
        if (response['html']) {
          entry_div.html(response['html']);
          activateSpecialFieldValueControls(field_id, field_settings[field_id]);
        }

      }).catch((error) => {
        console.log(error);
      });

    } else {
      entry_div.html('<input type="hidden" id="post-field-value" value="" />');
    }
  }
}

function activateSpecialFieldValueControls(field_id, field_settings) {
  let post_type = field_settings['post_type'];
  let field_type = field_settings['type'];

  switch (field_type) {
    case 'connection': {
      let connection_typeahead_field_input = '.js-typeahead-' + field_id;
      jQuery('#post-field-value-entry-div').find(connection_typeahead_field_input).typeahead({
          input: connection_typeahead_field_input,
          minLength: 0,
          accent: true,
          searchOnFocus: true,
          maxItem: 20,
          template: window.TYPEAHEADS.contactListRowTemplate,
          source: TYPEAHEADS.typeaheadPostsSource(post_type, field_id),
          display: ["name", "label"],
          templateValue: function () {
            if (this.items[this.items.length - 1].label) {
              return "{{label}}"
            } else {
              return "{{name}}"
            }
          },
          dynamic: true,
          multiselect: {
            matchOn: ["ID"],
            data: [],
            callback: {
              onCancel: function (node, item) {
              }
            }
          },
          callback: {
            onResult: function (node, query, result, resultCount) {
              let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result);
              $(`#${field_id}-result-container`).html(text);
            },
            onHideLayout: function () {
              $(`#${field_id}-result-container`).html("");
            },
            onClick: function (node, a, item, event) {
              // Stop list from opening again
              this.addMultiselectItemLayout(item)
              event.preventDefault()
              this.hideLayout();
              this.resetInput();
            }
          }
        });
      break;
    }
    case 'location': {
      let location_typeahead_field_input = '.js-typeahead-' + field_id;
      jQuery('#post-field-value-entry-div').find(location_typeahead_field_input).typeahead({
          input: location_typeahead_field_input,
          minLength: 0,
          accent: true,
          searchOnFocus: true,
          maxItem: 20,
          dropdownFilter: [{
            key: 'group',
            value: 'focus',
            template: window.lodash.escape(dtMetricsProject['translations']['regions_of_focus']),
            all: window.lodash.escape(dtMetricsProject['translations']['all_locations'])
          }],
          source: {
            focus: {
              display: "name",
              ajax: {
                url: dtMetricsProject['root'] + 'dt/v1/mapping_module/search_location_grid_by_name',
                data: {
                  s: "{{query}}",
                  filter: function () {
                    return window.lodash.get(window.Typeahead[location_typeahead_field_input].filters.dropdown, 'value', 'all');
                  }
                },
                beforeSend: function (xhr) {
                  xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject['nonce']);
                },
                callback: {
                  done: function (data) {
                    return data.location_grid;
                  }
                }
              }
            }
          },
          display: "name",
          templateValue: "{{name}}",
          dynamic: true,
          multiselect: {
            matchOn: ["ID"],
            data: function () {
              return [];
            }, callback: {
              onCancel: function (node, item) {
              }
            }
          },
          callback: {
            onClick: function (node, a, item, event) {
            },
            onReady() {
              this.filters.dropdown = {
                key: "group",
                value: "focus",
                template: window.lodash.escape(dtMetricsProject['translations']['regions_of_focus'])
              };
              this.container
              .removeClass("filter")
              .find("." + this.options.selector.filterButton)
              .html(window.lodash.escape(dtMetricsProject['translations']['regions_of_focus']));
            }
          }
        });
      break;
    }
    case 'user_select': {
      let user_select_typeahead_field_input = '.js-typeahead-' + field_id;
      jQuery('#post-field-value-entry-div').find(user_select_typeahead_field_input).typeahead({
        input: user_select_typeahead_field_input,
        minLength: 0,
        maxItem: 0,
        accent: true,
        searchOnFocus: true,
        source: TYPEAHEADS.typeaheadUserSource(),
        templateValue: "{{name}}",
        template: function (query, item) {
          return `<div class="assigned-to-row" dir="auto">
                  <span>
                      <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
                      ${window.lodash.escape(item.name)}
                  </span>
                  ${item.status_color ? `<span class="status-square" style="background-color: ${window.lodash.escape(item.status_color)};">&nbsp;</span>` : ''}
                  ${item.update_needed && item.update_needed > 0 ? `<span>
                    <img style="height: 12px;" src="${window.lodash.escape(window.wpApiShare.template_dir)}/dt-assets/images/broken.svg"/>
                    <span style="font-size: 14px">${window.lodash.escape(item.update_needed)}</span>
                  </span>` : ''}
                </div>`;
        },
        dynamic: true,
        hint: true,
        emptyTemplate: window.lodash.escape(window.wpApiShare.translations.no_records_found),
        callback: {
          onClick: function (node, a, item) {
          },
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $(`#${field_id}-result-container`).html(text);
          },
          onHideLayout: function () {
            $(`.${field_id}-result-container`).html("");
          }
        }
      });
      break;
    }
  }
}
