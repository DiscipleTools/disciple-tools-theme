jQuery(function () {
  if (window.wpApiShare.url_path.startsWith('metrics/records/date_range_activity')) {
    project_activity_during_date_range();
  }
});

const getFieldSettings = (postType) =>
  window.makeRequest('GET', `metrics/date_range_field_settings/${postType}`)

const renderFieldHtml = (data) =>
  window.makeRequest('GET', `metrics/render_field_html`, data)

const getDateRangeActivity = (data) =>
  window.makeRequest('POST', `metrics/date_range_activity`, data)

const escapeObject = window.SHAREDFUNCTIONS.escapeObject

function project_activity_during_date_range() {
  const chartDiv = document.querySelector('#chart');

  const {
    title_date_range_activity,
    post_type_select_label,
    post_field_select_label,
    date_select_label,
    date_select_custom_label,
    submit_button_label,
    total_label,
    results_table_head_title_label,
    results_table_head_date_label,
    results_table_head_new_value_label
  } = escapeObject(window.dtMetricsProject.translations);

  const postTypeOptions = escapeObject(window.dtMetricsProject.select_options.post_type_select_options);

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
        <div id="post-field-value-entry-div"></div>

        <label class="section-subheader" for="date-select">${date_select_label}</label>
        <div class="date_range_picker">
            <i class="fi-calendar"></i>&nbsp;
            <span>${window.moment().format("YYYY")}</span>
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
                    <th>${results_table_head_new_value_label}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
  `;

  // Activate date range picker element.
  window.METRICS.setupDatePickerWithoutEndpoint(
    function (start, end, label) {
      jQuery('.date_range_picker span').html(label);
      jQuery('#post-field-submit-button').click();
    },
    window.moment().startOf('year')
  );

  // Add post type event listener.
  const fieldSelectElement = document.querySelector('#post-field-select')
  document.querySelector('#post-type-select').addEventListener('change', (e) => {
    const postType = e.target.value
    window.dtMetricsProject.state.post_type = postType;
    getFieldSettings(postType)
    .promise()
    .then((data) => {
      window.dtMetricsProject.field_settings = data;
      fieldSelectElement.innerHTML = buildFieldSelectOptions();

      // Update selection based on detected defaults.
      if ( e.detail && e.detail.field ) {
        jQuery('#post-field-select').val(e.detail.field);
        fieldSelectElement.dispatchEvent(new CustomEvent('change', {'detail': e.detail}));

      } else {
        fieldSelectElement.dispatchEvent(new Event('change'));
      }

    })
    .catch((error) => {
      console.log(error)
    });
  });

  // Add field event listener.
  fieldSelectElement.addEventListener('change', (e) => {
    refreshFieldValueEntryElement( e.detail, function () {

      // Default to any specified date ranges.
      if (e.detail ) {
        if (e.detail.ts_start && e.detail.ts_end) {
          let date_range_picker = jQuery('.date_range_picker').data('daterangepicker');
          date_range_picker.setStartDate(window.moment.unix(parseInt(e.detail.ts_start)));
          date_range_picker.setEndDate(window.moment.unix(parseInt(e.detail.ts_end)).format("YYYY-MM-DD"));

          // Default to custom range label.
          jQuery('.date_range_picker span').html(window.lodash.escape(date_select_custom_label));
        }
        jQuery('#post-field-submit-button').click();
      }
    } );
  });

  // Add submit button event listener.
  document.querySelector('#post-field-submit-button').addEventListener('click', (e) => {
    let field_settings = window.dtMetricsProject.field_settings;
    let date_range_picker = jQuery('.date_range_picker').data('daterangepicker');
    let post_type = jQuery('#post-type-select').val();
    let field_id =  jQuery('#post-field-select').val();
    let field_type = field_settings[field_id]['type'];
    let value = jQuery('#post-field-value').val();
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

    // Build payload
    let payload = {
      'post_type': post_type,
      'field': field_id,
      'value': value,
      'ts_start': (date_range_picker.startDate.unix() > 0) ? date_range_picker.startDate.unix() : 0,
      'ts_end': date_range_picker.endDate.unix()
    };

    // Determine search param value shape to be adopted.
    let search_param_value = value;
    if ( Array.isArray( value ) && window.lodash.includes(['connection', 'location'], field_type) ) {
      search_param_value = value.filter((x) => x.ID !== null)
      .map((x) => x.ID)
      .join(',');

    } else if ( (value && value.ID) && window.lodash.includes(['user_select'], field_type) ) {
      search_param_value = value.ID;
    }

    // Dynamically update URL parameters.
    const url = new URL(window.location);
    url.searchParams.set('record_type', post_type);
    url.searchParams.set('field', field_id);
    url.searchParams.set('value', search_param_value );
    url.searchParams.set('ts_start', payload.ts_start);
    url.searchParams.set('ts_end', payload.ts_end);
    window.history.pushState(null, document.title, url.search);

    // Fetch records matching activity within specified date range.
    getDateRangeActivity(payload)
    .promise()
    .then((response) => {

      let total = response['total'];
      let posts = response['posts'];

      let activity_results_div = jQuery('#activity_results_div');

      // Refresh activity during date range results display.
      activity_results_div.fadeOut('fast', function () {
        jQuery('#activity_results_total').html(total);

        // Refresh and re-populate results table.
        let table = activity_results_div.find('table');
        table.fadeOut('fast');
        if(total && (total > 0)) {

          let tbody = table.find('tbody');
          tbody.empty();

          posts.forEach(function (post) {
            if (post['id'] && post['name'] && post['timestamp']) {
              let post_url = window.dtMetricsProject.site_url + '/' + post['post_type'] + '/' + post['id'];
              let new_value = window.SHAREDFUNCTIONS.escapeHTML(post['new_value']);

              // Apply custom styling.
              if ((post['field_type'] && post['field_type'] === 'connection') && (post['deleted'] && post['deleted'] === true)) {
                new_value = `<s>${window.SHAREDFUNCTIONS.escapeHTML(post['new_value'])}</s>`;
              }

              tbody.append(`
                <tr>
                    <td><a href="${post_url}" target="_blank">${window.SHAREDFUNCTIONS.escapeHTML(post['name'])}</a></td>
                    <td>${window.SHAREDFUNCTIONS.escapeHTML(window.moment.unix(post['timestamp']).format('dddd, MMMM Do YYYY, h:mm:ss A'))}</td>
                    <td>${new_value}</td>
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

  // Handle any available request defaults.
  handleRequestDefaults();
}

function handleRequestDefaults() {
  const request_params = window.dtMetricsProject.request.params;

  // Ensure required parts are present, in order to proceed.
  if ( request_params && request_params.record_type && request_params.field ) {

    // Update selected post type and forward request params to change event.
    jQuery('#post-type-select').val(request_params.record_type);
    document.querySelector('#post-type-select').dispatchEvent(new CustomEvent('change', {'detail': request_params}));

  } else {

    // Display field value entry accordingly based on selected field.
    refreshFieldValueEntryElement();
  }
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

function refreshFieldValueEntryElement( details = null, callback = null ) {

  // Empty any previous entries.
  let entry_div = jQuery('#post-field-value-entry-div');
  entry_div.empty();

  // Determine field id to be refreshed.
  let field_settings = window.dtMetricsProject.field_settings;
  let field_id = jQuery('#post-field-select').val();
  if (field_id && field_settings[field_id]) {

    // Based on field type & associated defaults, determine html element style to be adopted.
    if (field_settings[field_id]['default'] && Object.keys(field_settings[field_id]['default']).length > 0) {
      let options_html = `<option value="">[ ${window.SHAREDFUNCTIONS.escapeHTML(window.dtMetricsProject.translations['post_field_select_any_activity_label'])} ]</option>`;
      Object.entries(field_settings[field_id]['default']).forEach(([key, option]) => {
        options_html += `<option value="${key}">${option['label']}</option>`;
      });
      entry_div.html(`<select class="select-field" id="post-field-value">${options_html}</select>`);

      // Select any requested default field values.
      if ( details && details.value !== null ) {
        jQuery('#post-field-value').val(details.value);
      }

      if ( callback ) {
        callback();
      }

    } else if (window.lodash.includes(['connection', 'user_select', 'location'], field_settings[field_id]['type'])) {

      // Fetch html rendering for selected field.
      renderFieldHtml({
        'post_type': jQuery('#post-type-select').val(),
        'field_id': field_id
      }).promise()
      .then((response) => {
        let execute_callback = true;
        if (response['html']) {
          entry_div.html(response['html']);
          activateSpecialFieldValueControls(field_id, field_settings[field_id]);

          // Select any requested default field values.
          if ( details && details.value !== null ) {
            switch (field_settings[field_id]['type']) {
              case 'connection': {

                // Short-circuit main parent callback flow.
                execute_callback = false;

                // First attempt to obtain a handle onto recently instantiated typeahead object.
                let connection_typeahead_field_input = '.js-typeahead-' + field_id;
                let connection_typeahead = window.Typeahead[connection_typeahead_field_input];
                if ( connection_typeahead && field_settings[field_id]['post_type'] ) {

                  // Support multiple post ids; by first concatenating corresponding promises.
                  let promises = [];
                  jQuery.each(String(details.value).split(','), function (idx, id) {
                    promises.push(
                      window.API.get_post(field_settings[field_id]['post_type'], id)
                      .then(post => {

                        // On a successful hit, add as item to multi select typeahead field.
                        if (post && post['ID'] && post['title']) {
                          connection_typeahead.addMultiselectItemLayout({
                            'ID': post['ID'],
                            'label': post['title'],
                            'name': post['title']
                          });
                          connection_typeahead.hideLayout();
                          connection_typeahead.resetInput();
                        }
                      })
                    );
                  });

                  // Execute all promises within a chained fashion.
                  jQuery.when.apply(null, promises)
                  .done(result => {

                    // Executing callback if available; once all promises have finished.
                    if (callback) {
                      callback();
                    }
                  });
                }
                break;
              }
              case 'location': {

                // Short-circuit main parent callback flow.
                execute_callback = false;

                // First attempt to obtain a handle onto recently instantiated typeahead object.
                let location_typeahead_field_input = '.js-typeahead-' + field_id;
                let location_typeahead = window.Typeahead[location_typeahead_field_input];
                if ( location_typeahead ) {

                  // Support multiple post ids; by first concatenating corresponding promises.
                  let promises = [];
                  jQuery.each(String(details.value).split(','), function (idx, id) {
                      promises.push(
                          window.makeRequest('POST', window.dtMetricsProject['root'] + 'dt/v1/mapping_module/get_map_by_grid_id', {
                              'grid_id': id
                          }).then(location => {

                              // On a successful hit, add as item to multi select typeahead field.
                              if ( location && location['self'] && location['self']['id'] && location['self']['name'] ) {
                                  location_typeahead.addMultiselectItemLayout({
                                      'ID': location['self']['id'],
                                      'matchedKey': 'name',
                                      'name': location['self']['name']
                                  });
                                  location_typeahead.hideLayout();
                                  location_typeahead.resetInput();
                              }
                          })
                      );
                  });

                  // Execute all promises within a chained fashion.
                  jQuery.when.apply(null, promises)
                  .done(result => {

                    // Executing callback if available; once all promises have finished.
                    if (callback) {
                      callback();
                    }
                  });
                }
                break;
              }
              case 'user_select': {

                // Short-circuit main parent callback flow.
                execute_callback = false;

                // First attempt to obtain a handle onto recently instantiated typeahead object.
                let user_typeahead_field_input = '.js-typeahead-' + field_id;
                let user_typeahead = window.Typeahead[user_typeahead_field_input];
                if ( user_typeahead ) {

                  // Next, proceed with attempting to locate corresponding location grid record from the backend.
                  window.makeRequest('GET', window.dtMetricsProject['root'] + 'dt/v1/users/contact-id', {
                    'user_id': details.value
                  }).then(contact_id => {

                    // Next, attempt to fetch corresponding contact post record.
                    if (contact_id) {
                      window.API.get_post('contacts', contact_id)
                      .then(post => {
                        if (post && post['ID'] && post['title']) {

                          // On a successful hit, add as item to multi select typeahead field.
                          user_typeahead.item = {
                            'ID': details.value,
                            'contact_id': post['ID'],
                            'matchedKey': 'name',
                            'name': post['title']
                          };
                          user_typeahead.hideLayout();
                          user_typeahead.resetInput();
                          jQuery(`input.js-typeahead-${field_id}`).val(post['title']);

                          if ( callback ) {
                            callback();
                          }
                        }
                      });
                    }
                  });
                }
                break;
              }
            }
          }
        }

        if ( execute_callback && callback ) {
          callback();
        }

      }).catch((error) => {
        console.log(error);
      });

    } else {
      entry_div.html('<input type="hidden" id="post-field-value" value="" />');

      if ( callback ) {
        callback();
      }
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
          source: window.TYPEAHEADS.typeaheadPostsSource(post_type, field_id),
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
              let text = window.TYPEAHEADS.typeaheadHelpText(resultCount, query, result);
              jQuery(`#${field_id}-result-container`).html(text);
            },
            onHideLayout: function () {
              jQuery(`#${field_id}-result-container`).html("");
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
            template: window.SHAREDFUNCTIONS.escapeHTML(window.dtMetricsProject['translations']['regions_of_focus']),
            all: window.SHAREDFUNCTIONS.escapeHTML(window.dtMetricsProject['translations']['all_locations'])
          }],
          source: {
            focus: {
              display: "name",
              ajax: {
                url: window.dtMetricsProject['root'] + 'dt/v1/mapping_module/search_location_grid_by_name',
                data: {
                  s: "{{query}}",
                  filter: function () {
                    return window.lodash.get(window.Typeahead[location_typeahead_field_input].filters.dropdown, 'value', 'all');
                  }
                },
                beforeSend: function (xhr) {
                  xhr.setRequestHeader('X-WP-Nonce', window.dtMetricsProject['nonce']);
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
                template: window.SHAREDFUNCTIONS.escapeHTML(window.dtMetricsProject['translations']['regions_of_focus'])
              };
              this.container
              .removeClass("filter")
              .find("." + this.options.selector.filterButton)
              .html(window.SHAREDFUNCTIONS.escapeHTML(window.dtMetricsProject['translations']['regions_of_focus']));
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
        source: window.TYPEAHEADS.typeaheadUserSource(),
        templateValue: "{{name}}",
        template: function (query, item) {
          return `<div class="assigned-to-row" dir="auto">
                  <span>
                      <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
                      ${window.SHAREDFUNCTIONS.escapeHTML(item.name)}
                  </span>
                  ${item.status_color ? `<span class="status-square" style="background-color: ${window.SHAREDFUNCTIONS.escapeHTML(item.status_color)};">&nbsp;</span>` : ''}
                  ${item.update_needed && item.update_needed > 0 ? `<span>
                    <img style="height: 12px;" src="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.template_dir)}/dt-assets/images/broken.svg"/>
                    <span style="font-size: 14px">${window.SHAREDFUNCTIONS.escapeHTML(item.update_needed)}</span>
                  </span>` : ''}
                </div>`;
        },
        dynamic: true,
        hint: true,
        emptyTemplate: window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.translations.no_records_found),
        callback: {
          onClick: function (node, a, item) {
          },
          onResult: function (node, query, result, resultCount) {
            let text = window.TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            jQuery(`#${field_id}-result-container`).html(text);
          },
          onHideLayout: function () {
            jQuery(`.${field_id}-result-container`).html("");
          }
        }
      });
      break;
    }
  }
}
