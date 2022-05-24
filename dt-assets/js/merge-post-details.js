jQuery(function ($) {

  /**
   * Initial States...
   */

  $(document).ready(function () {

    // Auto select primary post and trigger event call & response
    $('#dt_current_post').prop('checked', true);
    $('#dt_current_post').trigger('change');

  });

  /**
   * Event Listeners
   */

  $(document).on('change', '.select-current-primary-record', function (evt) {
    handle_primary_post_Selection();
  });

  $(document).on('change', '.field-select', function (evt) {
    handle_field_selection(evt);
  });

  $(document).on('click', '.submit-merge', function (evt) {
    console.log('TODO....');
    // TODO: handle_merge();
  });

  /**
   * Helper Functions
   */

  function handle_primary_post_Selection() {

    // Fetch primary & archiving posts
    let primary_post = fetch_post_by_merge_type(true);
    let archiving_post = fetch_post_by_merge_type(false);

    // Assuming valid posts have been located, proceed with refreshing layout view
    if (primary_post && archiving_post) {

      // Update merge column titles
      $('#main_archiving_post_id_title').text(archiving_post['record']['ID']);
      $('#main_primary_post_id_title').text(primary_post['record']['ID']);
      $('#main_updated_post_id_title').text(primary_post['record']['ID']);

      // Refresh post fields
      refresh_fields(primary_post, archiving_post);

    }
  }

  function fetch_post_by_merge_type(is_primary) {

    // Currently selected id to be viewed as primary
    let primary_post_id = $('input[name="select_current_primary_record"]:checked').data('post_id');

    // Now identify archiving id
    let archiving_post_id = $('input[name="select_current_primary_record"]:not(:checked)').data('post_id');

    // Return identified post
    return (is_primary) ? window.merge_post_details['posts'][primary_post_id] : window.merge_post_details['posts'][archiving_post_id];
  }

  function refresh_fields(primary_post, archiving_post) {

    // Obtain dom-ref handles
    let main_archiving_fields_div = $('#main_archiving_fields_div');
    let main_primary_fields_div = $('#main_primary_fields_div');
    let main_updated_fields_div = $('#main_updated_fields_div');

    // Hide, whilst we work!
    main_archiving_fields_div.fadeOut('fast', function () {
      main_primary_fields_div.fadeOut('fast', function () {
        main_updated_fields_div.fadeOut('fast', function () {

          // Reset archiving & primary post fields accordingly
          main_archiving_fields_div.html(archiving_post['html']);
          main_primary_fields_div.html(primary_post['html']);

          // Updating fields to revert back to default blank state
          main_updated_fields_div.html(window.merge_post_details['post_fields_default_html']);

          // Initialise/Activate recently added html fields
          init_fields(archiving_post['record'], main_archiving_fields_div, true);
          init_fields(primary_post['record'], main_primary_fields_div, true);
          init_fields(null, main_updated_fields_div, false);

          // Adjust & trigger field selections to default states; which should set updating fields accordingly
          main_primary_fields_div.find('.field-select').each(function (idx, input) {
            $(input).prop('checked', true);
            $(input).trigger('change');
          });

          // Select any archiving fields suitable for auto merging
          main_archiving_fields_div.find('.field-select').each(function (idx, input) {
            if ($(input).attr('type') === 'checkbox') {
              let post_field_id = $(input).data('merge_update_field_id');
              if (archiving_post['record'][post_field_id]) {
                $(input).prop('checked', true);
                $(input).trigger('change');
              }
            }
          });

          // Display refreshed post fields
          main_archiving_fields_div.fadeIn('fast');
          main_primary_fields_div.fadeIn('fast');
          main_updated_fields_div.fadeIn('fast');

        });
      });
    });
  }

  function init_fields(post, fields_div, read_only) {

    let url_root = window.merge_post_details['url_root'];
    let post_type = window.merge_post_details['post_settings']['post_type'];
    let nonce = window.merge_post_details['nonce'];

    $(fields_div).find('table tbody tr .td-field-input').each(function (idx, td) {

      // Determine field id and type + meta
      let field_id = $(td).find('#merge_field_id').val();
      let field_type = $(td).find('#merge_field_type').val();
      let field_meta = $(td).find('#field_meta');

      // Remove field prefix, ahead of further downstream processing
      let post_field_id = post ? window.lodash.replace(field_id, post['ID'] + '_', '') : field_id;

      // Activate field accordingly, based on type and read-only flag
      switch (field_type) {
        case 'text':
        case 'key_select':

          // Disable field accordingly, based on read-only flag
          $(td).find('#' + field_id).prop('disabled', read_only);
          break;

        case 'date':

          /**
           * Load Date Range Picker
           */

          let date_config = {
            singleDatePicker: true,
            timePicker: true,
            locale: {
              format: 'MMMM D, YYYY'
            }
          };

          // Adjust start date based on post's date timestamp; if present
          let post_timestamp = $(td).find('#' + field_id).val();
          if (post_timestamp) {
            date_config['startDate'] = moment.unix(post_timestamp);
            field_meta.val(post_timestamp);
          }

          // Initialise date range picker and respond to selections
          $(td).find('#' + field_id).daterangepicker(date_config, function (start, end, label) {
            if (start) {
              field_meta.val(start.unix());
            }
          });

          // Disable field accordingly, based on read-only flag
          $(td).find('#' + field_id).prop('disabled', read_only);
          $(td).find('.clear-date-button').prop('disabled', read_only);

          /**
           * Clear Date
           */

          $(td).find('.clear-date-button').on('click', evt => {
            let input_id = $(evt.currentTarget).data('inputid');

            if (input_id) {
              $(td).find('#' + input_id).val('');
              field_meta.val('');
            }
          });

          break;

        case 'multi_select':

          /**
           * Handle Selections
           */

          if (!read_only) {
            $(td).find('.dt_multi_select').on("click", function (evt) {
              let multi_select = $(evt.currentTarget);
              if (multi_select.hasClass('empty-select-button')) {
                multi_select.removeClass('empty-select-button');
                multi_select.addClass('selected-select-button');
              } else {
                multi_select.removeClass('selected-select-button');
                multi_select.addClass('empty-select-button');
              }
            });
          }

          break;

        case 'tags':

          /**
           * Activate
           */

          let typeahead_tags_field_input = '.js-typeahead-' + field_id;

          // Disable field accordingly, based on read-only flag
          $(td).find(typeahead_tags_field_input).prop('disabled', read_only);

          // Hide new button and default to single entry
          $(td).find('.create-new-tag').hide();

          if (!window.Typeahead[typeahead_tags_field_input]) {
            $(td).find(typeahead_tags_field_input).typeahead({
              input: typeahead_tags_field_input,
              minLength: 0,
              maxItem: 20,
              searchOnFocus: true,
              source: {
                tags: {
                  display: ["name"],
                  ajax: {
                    url: url_root + `dt-posts/v2/${post_type}/multi-select-values`,
                    data: {
                      s: "{{query}}",
                      field: field_id
                    },
                    beforeSend: function (xhr) {
                      xhr.setRequestHeader('X-WP-Nonce', nonce);
                    },
                    callback: {
                      done: function (data) {
                        return (data || []).map(tag => {
                          return {name: tag}
                        })
                      }
                    }
                  }
                }
              },
              display: "name",
              templateValue: "{{name}}",
              emptyTemplate: function (query) {
                const {addNewTagText, tagExistsText} = this.node[0].dataset
                if (this.comparedItems.includes(query)) {
                  return tagExistsText.replace('%s', query)
                }
                const liItem = jQuery('<li>')
                const button = jQuery('<button>', {
                  class: "button primary",
                  text: addNewTagText.replace('%s', query),
                })
                const tag = this.query
                button.on("click", function () {
                  window.Typeahead[typeahead_tags_field_input].addMultiselectItemLayout({name: tag});
                })
                liItem.append(button);
                return liItem;
              },
              dynamic: true,
              multiselect: {
                matchOn: ["name"],
                data: function () {
                  if (post && post[post_field_id]) {
                    return (post[post_field_id] || []).map(t => {
                      return {name: t}
                    })
                  } else {
                    return {};
                  }
                },
                callback: {
                  onCancel: function (node, item, event) {
                    // Keep a record of deleted tags
                    let deleted_items = (field_meta.val()) ? JSON.parse(field_meta.val()) : [];
                    deleted_items.push(item);
                    field_meta.val(JSON.stringify(deleted_items));
                  }
                },
                href: function (item) {
                },
              },
              callback: {
                onClick: function (node, a, item, event) {
                  event.preventDefault();
                  this.addMultiselectItemLayout({name: item.name});
                },
                onResult: function (node, query, result, resultCount) {
                  let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
                  $(td).find(`#${field_id}-result-container`).html(text);
                },
                onHideLayout: function () {
                  $(td).find(`#${field_id}-result-container`).html("");
                },
                onShowLayout() {
                }
              }
            });
          }

          /**
           * Load
           */

          // If available, load previous post record tags
          if (post && post[post_field_id]) {
            let typeahead_tags = window.Typeahead[typeahead_tags_field_input];
            let post_tags = post[post_field_id];

            if ((post_tags !== undefined) && typeahead_tags) {
              jQuery.each(post_tags, function (idx, tag) {
                typeahead_tags.addMultiselectItemLayout({
                  name: window.lodash.escape(tag)
                });
              });
            }
          }

          break;

        case 'communication_channel':

          // Disable field accordingly, based on read-only flag
          $(td).find('input.dt-communication-channel').prop('disabled', read_only);

          /**
           * Add
           */

          $(td).find('button.add-button').on('click', evt => {
            let field = $(evt.currentTarget).data('list-class');
            let list = $(td).find(`#edit-${field}`);

            list.append(`
                <div class="input-group">
                    <input type="text" data-field="${window.lodash.escape(field)}" class="dt-communication-channel input-group-field" dir="auto" />
                    <div class="input-group-button">
                        <button class="button alert input-height delete-button-style channel-delete-button delete-button new-${window.lodash.escape(field)}" data-key="new" data-field="${window.lodash.escape(field)}">&times;</button>
                    </div>
                </div>`);
          });

          /**
           * Remove
           */

          $(document).on('click', '.channel-delete-button', evt => {
            let field = $(evt.currentTarget).data('field');
            let key = $(evt.currentTarget).data('key');

            // If needed, keep a record of key for future api removal.
            if (key !== 'new') {
              /*let deleted_keys = (field_meta.val()) ? JSON.parse(field_meta.val()) : [];
              deleted_keys.push(key);
              field_meta.val(JSON.stringify(deleted_keys));*/
            }

            // Final removal of input group
            $(evt.currentTarget).parent().parent().remove();
          });

          break;

        case 'location_meta':

          let mapbox = window.merge_post_details['mapbox'];

          /**
           * Load
           */

          $(td).find('#mapbox-wrapper').empty().append(`
              <div id="location-grid-meta-results"></div>
              <div class="reveal" id="mapping-modal" data-v-offset="0" data-reveal>
                <div id="mapping-modal-contents"></div>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
          `);

          // Display previously saved locations
          let lgm_results = $(td).find('#location-grid-meta-results');
          if (post && post['location_grid_meta'] !== undefined && post['location_grid_meta'].length !== 0) {
            $.each(post['location_grid_meta'], function (i, v) {
              if (v.grid_meta_id) {
                lgm_results.append(`<div class="input-group">
                    <input type="text" class="active-location input-group-field" id="location-${window.lodash.escape(v.grid_meta_id)}" dir="auto" value="${window.lodash.escape(v.label)}" readonly />
                    <div class="input-group-button">
                      <button type="button" class="button success delete-button-style open-mapping-grid-modal" title="${window.lodash.escape(mapbox['translations']['open_modal'])}" data-id="${window.lodash.escape(v.grid_meta_id)}"><i class="fi-map"></i></button>
                      <button type="button" class="button alert delete-button-style delete-button mapbox-delete-button" title="${window.lodash.escape(mapbox['translations']['delete_location'])}" data-id="${window.lodash.escape(v.grid_meta_id)}">&times;</button>
                    </div>
                  </div>`);
              } else {
                lgm_results.append(`<div class="input-group">
                    <input type="text" class="dt-communication-channel input-group-field" id="${window.lodash.escape(v.key)}" value="${window.lodash.escape(v.label)}" dir="auto" data-field="contact_address" />
                    <div class="input-group-button">
                      <button type="button" class="button success delete-button-style open-mapping-address-modal"
                          title="${window.lodash.escape(mapbox['translations']['open_modal'])}"
                          data-id="${window.lodash.escape(v.key)}"
                          data-field="contact_address"
                          data-key="${window.lodash.escape(v.key)}">
                          <i class="fi-pencil"></i>
                      </button>
                      <button type="button" class="button alert input-height delete-button-style channel-delete-button delete-button" title="${window.lodash.escape(mapbox['translations']['delete_location'])}" data-id="${window.lodash.escape(v.key)}" data-field="contact_address" data-key="${window.lodash.escape(v.key)}">&times;</button>
                    </div>
                  </div>`);
              }
            })
          }

          /**
           * Add
           */

          if (!read_only) {
            $(td).find('#new-mapbox-search').on('click', evt => {

              // Display search field with autosubmit disabled!
              if ($(td).find('#mapbox-autocomplete').length === 0) {
                $(td).find('#mapbox-wrapper').prepend(`
              <div id="mapbox-autocomplete" class="mapbox-autocomplete input-group" data-autosubmit="false">
                  <input id="mapbox-search" type="text" name="mapbox_search" placeholder="${window.lodash.escape(mapbox['translations']['search_location'])}" autocomplete="off" dir="auto" />
                  <div class="input-group-button">
                      <button id="mapbox-spinner-button" class="button hollow" style="display:none;"><span class="loading-spinner active"></span></button>
                      <button id="mapbox-clear-autocomplete" class="button alert input-height delete-button-style mapbox-delete-button" type="button" title="${window.lodash.escape(mapbox['translations']['delete_location'])}" >&times;</button>
                  </div>
                  <div id="mapbox-autocomplete-list" class="mapbox-autocomplete-items"></div>
              </div>`);
              }

              // Switch over to standard workflow, with autosubmit disabled!
              write_input_widget();
            });

            // Hide new button and default to single entry
            $(td).find('#new-mapbox-search').hide();
            $(td).find('#new-mapbox-search').trigger('click');
          }

          /**
           * Remove
           */

          $(document).on('click', '.mapbox-delete-button', evt => {
            let id = $(evt.currentTarget).data('id');

            // If needed, keep a record of key for future api removal.
            if (id !== undefined) {
              let deleted_ids = (field_meta.val()) ? JSON.parse(field_meta.val()) : [];
              deleted_ids.push(id);
              field_meta.val(JSON.stringify(deleted_ids));

              // Final removal of input group
              $(evt.currentTarget).parent().parent().remove();

            } else {

              // Remove global selected location
              window.selected_location_grid_meta = null;
            }
          });

          /**
           * Open Modal
           */

          $(td).find('.open-mapping-grid-modal').on('click', evt => {
            let grid_meta_id = $(evt.currentTarget).data('id');
            let post_location_grid_meta = post ? post['location_grid_meta'] : undefined;

            if (post_location_grid_meta !== undefined && post_location_grid_meta.length !== 0) {
              $.each(post_location_grid_meta, function (i, v) {
                if (String(grid_meta_id) === String(v.grid_meta_id)) {
                  return load_modal(v.lng, v.lat, v.level, v.label, v.grid_id);
                }
              });
            }
          });

          // Disable field accordingly, based on read-only flag
          $(td).find('#mapbox-search').prop('disabled', read_only);

          break;

        case 'location':

          let translations = window.merge_post_details['translations'];

          let typeahead_field_input = '.js-typeahead-' + field_id;

          // Disable field accordingly, based on read-only flag
          $(td).find(typeahead_field_input).prop('disabled', read_only);

          /**
           * Load Typeahead
           */

          if (!window.Typeahead[typeahead_field_input]) {
            $(td).find(typeahead_field_input).typeahead({
              input: typeahead_field_input,
              minLength: 0,
              accent: true,
              searchOnFocus: true,
              maxItem: 20,
              dropdownFilter: [{
                key: 'group',
                value: 'focus',
                template: window.lodash.escape(translations['regions_of_focus']),
                all: window.lodash.escape(translations['all_locations'])
              }],
              source: {
                focus: {
                  display: "name",
                  ajax: {
                    url: url_root + 'dt/v1/mapping_module/search_location_grid_by_name',
                    data: {
                      s: "{{query}}",
                      filter: function () {
                        return window.lodash.get(window.Typeahead[typeahead_field_input].filters.dropdown, 'value', 'all');
                      }
                    },
                    beforeSend: function (xhr) {
                      xhr.setRequestHeader('X-WP-Nonce', nonce);
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
                    // Keep a record of deleted options
                    let deleted_items = (field_meta.val()) ? JSON.parse(field_meta.val()) : [];
                    deleted_items.push(item);
                    field_meta.val(JSON.stringify(deleted_items));
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
                    template: window.lodash.escape(translations['regions_of_focus'])
                  };
                  this.container
                    .removeClass("filter")
                    .find("." + this.options.selector.filterButton)
                    .html(window.lodash.escape(translations['regions_of_focus']));
                }
              }
            });
          }

          // If available, load previous post record locations
          let typeahead = window.Typeahead[typeahead_field_input];
          let post_locations = post ? post[post_field_id] : undefined;

          if ((post_locations !== undefined) && typeahead) {
            $.each(post_locations, function (idx, location) {
              typeahead.addMultiselectItemLayout({
                ID: location['id'],
                name: window.lodash.escape(location['label'])
              });
            });
          }

          break;
      }

    });
  }

  function handle_field_selection(evt) {

    // Determine selection state, field id and type
    let is_selected = $(evt.currentTarget).is(':checked');
    let update_field_id = $(evt.currentTarget).data('merge_update_field_id');
    let field_id = $(evt.currentTarget).data('merge_field_id');
    let field_type = $(evt.currentTarget).data('merge_field_type');

    // Update selected fields accordingly
    update_fields($(evt.currentTarget), is_selected, update_field_id, field_id, field_type);

  }

  function update_fields(selector, is_selected, update_field_id, field_id, field_type) {

    switch (field_type) {
      case 'text':
      case 'key_select':
        if (is_selected) {
          $('#' + update_field_id).val($('#' + field_id).val());
        }
        break;

      case 'date':
        if (is_selected) {

          let source_date_range_picker = $('#' + field_id).data('daterangepicker');
          let update_date_range_picker = $('#' + update_field_id).data('daterangepicker');

          // Determine values to be updated
          let updated_date = source_date_range_picker.startDate;
          let updated_date_ts = $('#' + field_id).val();

          // Update values accordingly
          update_date_range_picker.setStartDate(updated_date);
          update_date_range_picker.setEndDate(updated_date);
          $('#' + update_field_id).val(updated_date_ts);

        }
        break;

      case 'multi_select':

        // Determine values to be updated
        let updated_selections = $(selector.parent().parent()).find('button[data-field-key="' + update_field_id + '"].selected-select-button');

        // Update values accordingly
        $.each(updated_selections, function (idx, source_button) {
          let update_button = $('#main_updated_fields_div').find('#' + $(source_button).attr('id'));
          if (update_button) {
            $(update_button).toggleClass('selected-select-button', is_selected);
          }
        });

        break;

      case 'tags':

        // Determine values to be updated
        let source_typeahead_tags = window.Typeahead['.js-typeahead-' + field_id];
        let update_typeahead_tags = window.Typeahead['.js-typeahead-' + update_field_id];

        // Update values accordingly
        if (source_typeahead_tags && update_typeahead_tags) {
          $.each(source_typeahead_tags.items, function (idx, source_tag) {
            if (is_selected) { // Add, if not already present

              if (!window.lodash.includes(update_typeahead_tags.items, source_tag)) {
                update_typeahead_tags.addMultiselectItemLayout(source_tag);
              }

            } else { // Remove, if present

              if (window.lodash.includes(update_typeahead_tags.items, source_tag)) {

                // Remove item object
                window.lodash.remove(update_typeahead_tags.items, function (tag) {
                  return tag['name'] === source_tag['name'];
                });

                // Remove compared item string
                window.lodash.remove(update_typeahead_tags.comparedItems, function (tag) {
                  return tag === source_tag['name'];
                });

                // Remove matching label container
                $(update_typeahead_tags.label.container).find('.typeahead__label').each(function (idx, label) {
                  if ($(label).find('a').text() === source_tag['name']) {
                    $(label).remove();
                  }
                });

              }
            }
          });
        }

        break;

      case 'communication_channel':

        // Determine values to be updated
        let comm_values = [];
        let comm_elements = $(selector.parent().parent()).find('input[data-field="' + update_field_id + '"].input-group-field');
        $.each(comm_elements, function (idx, element) {
          if ($(element).val()) {
            comm_values.push($(element).val());
          }
        });

        // Update values accordingly
        if (comm_values) {

          // Obtain handle to existing list
          let list = $('#main_updated_fields_div').find(`#edit-${update_field_id}`);

          // Iterate over values; processing accordingly
          $.each(comm_values, function (idx, value) {

            // Determine if the value already exists within update list
            let has_value = false;
            let value_ele = null;

            $(list).find('input[data-field="' + update_field_id + '"].input-group-field')
              .each(function (idx, input) {
                if ($(input).val() === value) {
                  has_value = true;
                  value_ele = input;
                }
              });

            // Add/Remove accordingly
            if (is_selected && !has_value) {

              // Add, if not already present
              list.append(`
                <div class="input-group">
                    <input type="text" data-field="${window.lodash.escape(update_field_id)}" class="dt-communication-channel input-group-field" dir="auto" value="${window.lodash.escape(value)}" />
                    <div class="input-group-button">
                        <button class="button alert input-height delete-button-style channel-delete-button delete-button new-${window.lodash.escape(update_field_id)}" data-key="new" data-field="${window.lodash.escape(update_field_id)}">&times;</button>
                    </div>
                </div>`);

            } else if (!is_selected && has_value && value_ele) {

              // Remove, if present
              $(value_ele).parent().remove();

            }

          });

        }

        break;

      case 'location_meta':

        // Determine values to be updated
        let location_elements = $(selector.parent().parent()).find('input.input-group-field');

        // Update values accordingly
        if (location_elements) {

          // Obtain handle to existing list
          let td = $('#main_updated_fields_div').find('#mapbox-autocomplete').parent();

          // Iterate over values; processing accordingly
          $.each(location_elements, function (idx, element) {

            // Determine if the value already exists within update list
            let has_value = false;
            let value_ele = null;

            $(td).find('input.input-group-field').each(function (idx, input) {
              if ($(input).val() === $(element).val()) {
                has_value = true;
                value_ele = input;
              }
            });

            // Add/Remove accordingly
            if (is_selected && !has_value) {

              // Add, if not already present
              td.append($(element).clone());

            } else if (!is_selected && has_value && value_ele) {

              // Remove, if present
              $(value_ele).remove();

            }

          });

        }

        break;

      case 'location':

        // Determine values to be updated
        let source_typeahead_location = window.Typeahead['.js-typeahead-' + field_id];
        let update_typeahead_location = window.Typeahead['.js-typeahead-' + update_field_id];

        // Update values accordingly
        if (source_typeahead_location && update_typeahead_location) {
          $.each(source_typeahead_location.items, function (idx, source_location) {

            if (is_selected) { // Add, if not already present

              if (!window.lodash.includes(update_typeahead_location.items, source_location['ID'])) {
                update_typeahead_location.addMultiselectItemLayout(source_location);
              }

            } else { // Remove, if present

              // Remove item object
              window.lodash.remove(update_typeahead_location.items, function (location) {
                return location['name'] === source_location['name'];
              });

              // Remove compared item string
              window.lodash.remove(update_typeahead_location.comparedItems, function (location) {
                return new String(location).valueOf() == new String(source_location['ID'].valueOf());
              });

              // Remove matching label container
              $(update_typeahead_location.label.container).find('.typeahead__label').each(function (idx, label) {
                if ($(label).find('span').not('span.typeahead__cancel-button').text() === source_location['name']) {
                  $(label).remove();
                }
              });

            }
          });
        }

        break;
    }

  }

  function handle_merge() {
    $('#main_updated_fields_div').find('.td-field-input').each(function (idx, td) {

      console.log(td);

      let field_id = $(td).find('#merge_field_id').val();
      let field_type = $(td).find('#merge_field_type').val();
      let post_field_id = $(td).find('#post_field_id').val();
      let field_meta = $(td).find('#field_meta');

      let values = {};
      switch (field_type) {

        case 'date':
        case 'text':
        case 'key_select':
          values[post_field_id] = $('#' + field_id).val();
          break;

        case 'multi_select':
          let options = [];
          $(td).find('button').each(function () {
            options.push({
              'value': $(this).attr('id'),
              'delete': $(this).hasClass('empty-select-button')
            });
          });

          if (options) {
            values[post_field_id] = options;
          }
          break;

        case 'tags':
        case 'location':
          let typeahead = window.Typeahead['.js-typeahead-' + field_id];
          if (typeahead) {

            let key = (field_type === 'tags') ? 'name' : 'ID';
            let items = typeahead.items;
            let deletions = field_meta.val() ? JSON.parse(field_meta.val()) : [];

            let entries = [];
            $.each(items, function (idx, item) {
              entries.push({
                'value': item[key]
              });
            });
            $.each(deletions, function (idx, item) {
              entries.push({
                'value': item[key],
                'delete': true
              });
            });

            if (entries) {
              values[post_field_id]['values'] = entries;
            }
          }
          break;

        case 'location_meta':
          let location_meta_entries = [];
          let location_meta_deletions = field_meta.val() ? JSON.parse(field_meta.val()) : [];

          $(td).find('input.input-group-field').each(function (idx, input) {
            if ($(input).val()) {
              location_meta_entries.push({
                'value': $(input).val()
              });
            }
          });

          if (window.selected_location_grid_meta !== undefined) {
            console.log(selected_location_grid_meta);
            // TODO....
            /*location_meta_entries.push({
              'value': $(input).val()
            });*/
          }

          $.each(location_meta_deletions, function (idx, id) {
            location_meta_entries.push({
              'grid_meta_id': id,
              'delete': true
            });
          });
          break;

        case 'communication_channel':
          // TODO...
          break;
      }

    });
  }

});
