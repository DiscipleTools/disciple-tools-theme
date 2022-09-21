jQuery(function ($) {

  /**
   * Initial States...
   */

  $(document).ready(function () {
    // Auto select primary post and trigger event call & response
    $('#main_archiving_primary_switch_but').trigger('click');
  });

  /**
   * Event Listeners
   */

  $(document).on('click', '#main_archiving_primary_switch_but', function (evt) {
    handle_primary_post_Selection();
  });

  $(document).on('change', '.field-select', function (evt) {
    handle_field_selection(evt);
  });

  $(document).on('click', '.submit-merge', function (evt) {
    handle_merge();
  });

  /**
   * Helper Functions
   */

  function handle_primary_post_Selection() {

    // First, toggle post id previous selections
    let toggled_archiving_post_id = $('#main_archiving_current_post_id').val();
    let toggled_primary_post_id = $('#main_primary_current_post_id').val();
    $('#main_primary_current_post_id').val(toggled_archiving_post_id);
    $('#main_archiving_current_post_id').val(toggled_primary_post_id);

    // Fetch primary & archiving posts
    let primary_post = fetch_post_by_merge_type(true);
    let archiving_post = fetch_post_by_merge_type(false);

    // Assuming valid posts have been located, proceed with refreshing layout view
    if (primary_post && archiving_post) {

      // Update merge column titles & post url links
      $('#main_archiving_post_id_title').text(archiving_post['record']['title'] + ' #' + archiving_post['record']['ID']);
      $('#main_primary_post_id_title').text(primary_post['record']['title'] + ' #' + primary_post['record']['ID']);
      $('#main_updated_post_id_title').text(primary_post['record']['ID']);

      let archiving_id_link = window.merge_post_details['site_url'] + window.merge_post_details['post_settings']['post_type'] + '/' + archiving_post['record']['ID'];
      let primary_id_link = window.merge_post_details['site_url'] + window.merge_post_details['post_settings']['post_type'] + '/' + primary_post['record']['ID'];
      $('#main_archiving_post_id_title_link').attr('href', archiving_id_link);
      $('#main_primary_post_id_title_link').attr('href', primary_id_link);

      // Refresh post fields
      refresh_fields(primary_post, archiving_post);

    }
  }

  function fetch_post_by_merge_type(is_primary) {

    // Currently selected id to be viewed as primary
    let primary_post_id = $('#main_primary_current_post_id').val();

    // Now identify archiving id
    let archiving_post_id = $('#main_archiving_current_post_id').val();

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
            let post_field_id = $(input).data('merge_update_field_id');

            if (primary_post['record'][post_field_id] && can_select_field($(input).parent().parent())) {
              $(input).prop('checked', true);
              $(input).trigger('change');

            } else {

              // Otherwise, attempt to default to valid corresponding archiving field
              if ($(input).attr('type') === 'radio') {

                // Attempt to identify corresponding archive radio input
                let archive_input = main_archiving_fields_div.find('input[data-merge_field_id="' + archiving_post['record']['ID'] + '_' + post_field_id + '"]');
                if (archiving_post['record'][post_field_id] && archive_input && can_select_field($(archive_input).parent().parent())) {
                  $(archive_input).prop('checked', true);
                  $(archive_input).trigger('change');

                }
              }
            }
          });

          // Select any archiving fields suitable for auto merging
          main_archiving_fields_div.find('.field-select').each(function (idx, input) {
            if ($(input).attr('type') === 'checkbox') {
              let post_field_id = $(input).data('merge_update_field_id');
              if (archiving_post['record'][post_field_id]) {
                $(input).prop('checked', can_select_field($(input).parent().parent()));
                $(input).trigger('change');
              }
            }
          });

          // Hide empty fields across all merging columns
          hide_empty_fields();

          // Determine how archiving and primary field values are to be shown
          handle_showing_of_field_values();

          // Display refreshed post fields
          main_archiving_fields_div.fadeIn('fast', function () {
            main_primary_fields_div.fadeIn('fast', function () {
              main_updated_fields_div.fadeIn('fast', function () {

                // Housekeeping - Ensure all typeahead input fields are displayed nicely ;-)
                $.each(window.Typeahead, function (key, typeahead) {
                  if ((typeof typeahead.adjustInputSize === 'function') && !$.isEmptyObject(typeahead.label)) {
                    typeahead.adjustInputSize();
                  }
                });

              });
            });
          });

        });
      });
    });
  }

  function can_select_field(td_field_input) {

    // Ensure field has a value in order to be selected
    let field_id = $(td_field_input).find('#merge_field_id').val();
    let field_type = $(td_field_input).find('#merge_field_type').val();
    let post_field_id = $(td_field_input).find('#post_field_id').val();

    switch (field_type) {
      case 'textarea':
      case 'number':
      case 'boolean':
      case 'text':
      case 'date':
        return !window.lodash.isEmpty($('#' + field_id).val());

      case 'key_select':
        return !window.lodash.isEmpty($('#' + field_id).val()) || !window.lodash.isEmpty($('#' + post_field_id).val());

      case 'multi_select':
        return !window.lodash.isEmpty($(td_field_input).find('button.selected-select-button'));

      case 'tags':
      case 'location': {
        let typeahead = window.Typeahead['.js-typeahead-' + field_id];
        return typeahead && !window.lodash.isEmpty(typeahead.items);
      }

      case 'communication_channel':
      case 'location_meta':
        return !window.lodash.isEmpty($(td_field_input).find('input.input-group-field').not('[value=""]'));

      case 'user_select': {
        let user_select_typeahead = window.Typeahead['.js-typeahead-' + field_id];
        return user_select_typeahead && !window.lodash.isEmpty(user_select_typeahead.item);
      }

      case 'connection': {
        let connection_typeahead = window.Typeahead['.js-typeahead-' + field_id];
        return connection_typeahead && !window.lodash.isEmpty(connection_typeahead.items);
      }
    }

    return false;

  }

  function hide_empty_fields() {
    $.each(window.merge_post_details['post_settings']['fields'], function (key, field) {

      // Identify corresponding field div element
      let archiving_field_input = $('#main_archiving_fields_div .td-field-input').find('#post_field_id[value="' + key + '"]');
      let primary_field_input = $('#main_primary_fields_div .td-field-input').find('#post_field_id[value="' + key + '"]');
      let updated_field_input = $('#main_updated_fields_div .td-field-input').find('#post_field_id[value="' + key + '"]');

      // Assuming valid elements, determine current value states
      if (archiving_field_input.length > 0 || primary_field_input.length > 0) {

        let archiving_field_tr = $(archiving_field_input).parent().parent();
        let primary_field_tr = $(primary_field_input).parent().parent();
        let updated_field_tr = $(updated_field_input).parent().parent();

        // Remove all field entries if no archiving/primary values detected
        if (!can_select_field(archiving_field_tr) && !can_select_field(primary_field_tr)) {
          $(archiving_field_tr).remove();
          $(primary_field_tr).remove();
          $(updated_field_tr).remove();
        }
      }
    });
  }

  function handle_showing_of_field_values() {
    if (window.merge_post_details['only_show_field_values']) {

      // Build merging columns of interest
      let merging_columns = [
        {
          'post': fetch_post_by_merge_type(false)['record'],
          'fields_div': 'main_archiving_fields_div'
        },
        {
          'post': fetch_post_by_merge_type(true)['record'],
          'fields_div': 'main_primary_fields_div'
        }
      ];

      // Iterate over merging column fields, only displaying values
      $.each(merging_columns, function (idx, column) {
        $('#' + column['fields_div']).find('table tbody tr .td-field-input').each(function (idx, td) {

          // Determine field id and type + meta
          let post_field_id = $(td).find('#post_field_id').val();
          let merge_field_id = $(td).find('#merge_field_id').val();
          let merge_field_type = $(td).find('#merge_field_type').val();

          // Switch out elements for value only display
          switch (merge_field_type) {
            case 'textarea':
            case 'number':
            case 'boolean':
            case 'text': {
              $(td).find('#' + merge_field_id).fadeOut('fast');
              $(td).append('<span>' + column['post'][post_field_id] + '</span>');
              break;
            }

            case 'key_select': {
              let key_select = null;

              // Determine select type
              if ($(td).find('#' + merge_field_id).length > 0) {
                key_select = $(td).find('#' + merge_field_id);
              } else if (($(td).find('#' + post_field_id).length > 0) && !window.lodash.isEmpty($(td).find('#' + post_field_id).css('background-color'))) {
                key_select = $(td).find('#' + post_field_id);
              }

              // Assuming we have a valid select handle
              if (key_select) {
                $(key_select).fadeOut('fast');
              }

              // Display value only
              $(td).append('<span>' + column['post'][post_field_id]['label'] + '</span>');
              break;
            }

            case 'date': {
              $(td).find('.' + merge_field_id).fadeOut('fast');
              $(td).append('<span>' + column['post'][post_field_id]['formatted'] + '</span>');
              break;
            }

            case 'multi_select': {
              $(td).find('.button-group').fadeOut('fast');

              // Build list html with select options
              let select_html = '<ul>';
              $.each(column['post'][post_field_id], function (option_idx, option) {
                let option_label = window.merge_post_details['post_settings']['fields'][post_field_id]['default'][option]['label'];
                select_html += '<li>' + option_label + '</li>'
              });
              select_html += '</ul>';

              $(td).append(select_html);
              break;
            }

            case 'tags': {
              $(td).find('#' + merge_field_id).fadeOut('fast');

              // Build list html with identified tags
              let tags_html = '<ul>';
              $.each(column['post'][post_field_id], function (tag_idx, tag) {
                tags_html += '<li>' + tag + '</li>'
              });
              tags_html += '</ul>';

              $(td).append(tags_html);
              break;
            }

            case 'communication_channel': {
              $(td).find('#edit-' + post_field_id).fadeOut('fast');

              // Build list html with identified communication channels
              let comms_html = '<ul>';
              $.each(column['post'][post_field_id], function (comms_idx, comms) {
                comms_html += '<li>' + comms['value'] + '</li>'
              });
              comms_html += '</ul>';

              $(td).append(comms_html);
              break;
            }

            case 'location_meta': {
              $(td).find('#mapbox-wrapper').fadeOut('fast');

              // Build list html with identified locations
              let locate_meta_html = '<ul>';
              $.each(column['post'][post_field_id], function (locate_meta_idx, locate_meta) {
                locate_meta_html += '<li>' + locate_meta['label'] + '</li>'
              });
              locate_meta_html += '</ul>';

              $(td).append(locate_meta_html);
              break;
            }

            case 'user_select': {
              $(td).find('#' + post_field_id).fadeOut('fast');
              $(td).append('<span>' + column['post'][post_field_id]['display'] + '</span>');
              break;
            }

            case 'connection': {
              $(td).find('.dt_typeahead').fadeOut('fast');

              // Build list html with identified connections
              let connection_html = '<ul>';
              $.each(column['post'][post_field_id], function (connection_idx, connection) {
                connection_html += '<li>' + connection['post_title'] + '</li>'
              });
              connection_html += '</ul>';

              $(td).append(connection_html);
              break;
            }
          }
        });
      });

    }
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
      let post_field_id = post ? window.lodash.replace(field_id, post['ID'] + '_', '') : $(td).find('#post_field_id').val();

      // Activate field accordingly, based on type and read-only flag
      switch (field_type) {
        case 'textarea':
        case 'number':
        case 'boolean':
        case 'text':

          // Disable field accordingly, based on read-only flag
          $(td).find('#' + field_id).prop('disabled', read_only);
          break;

        case 'key_select': {

          // Disable field accordingly, based on read-only flag and select type
          let key_select = null;

          // Determine select type
          if ($(td).find('#' + field_id).length > 0) {
            key_select = $(td).find('#' + field_id);
          } else if (($(td).find('#' + post_field_id).length > 0) && !window.lodash.isEmpty($(td).find('#' + post_field_id).css('background-color'))) {
            key_select = $(td).find('#' + post_field_id);
          }

          // Assuming we have a handle on a valid select, disable accoridngly
          if (key_select) {
            key_select.prop('disabled', read_only);
          }

          break;
        }

        case 'date': {

          /**
           * Load Date Range Picker
           */

          let date_format = 'MMMM D, YYYY';
          let date_config = {
            autoUpdateInput: false,
            singleDatePicker: true,
            timePicker: true,
            locale: {
              format: date_format
            }
          };

          // Adjust start date based on post's date timestamp; if present
          let post_timestamp = $(td).find('#' + field_id).val();
          let hasStartDate = (post_timestamp && post && post[post_field_id] !== undefined);
          if (hasStartDate) {
            date_config['startDate'] = moment.unix(post_timestamp);
            field_meta.val(post_timestamp);
          }

          // Initialise date range picker and respond to selections
          $(td).find('#' + field_id).daterangepicker(date_config, function (start, end, label) {
            if (start) {
              field_meta.val(start.unix());
            }
          });

          // Render start date display accoridngly, post initialisation
          if (hasStartDate) {
            let start_date = $(td).find('#' + field_id).data('daterangepicker').startDate.format(date_format);
            $(td).find('#' + field_id).val(start_date);
          }

          // Respond to apply date events
          $(td).find('#' + field_id).on('apply.daterangepicker', function (evt, picker) {
            $(td).find('#' + field_id).val(picker.startDate.format(date_format));
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
        }

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

        case 'tags': {

          /**
           * Activate
           */

          let typeahead_tags_field_input = '.js-typeahead-' + field_id;

          // Disable field accordingly, based on read-only flag
          $(td).find(typeahead_tags_field_input).prop('disabled', read_only);

          // Hide new button and default to single entry
          $(td).find('.create-new-tag').hide();

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
        }

        case 'communication_channel':

          // Disable/Display field accordingly, based on read-only flag
          $(td).find('input.dt-communication-channel').prop('disabled', read_only);

          if (!read_only) {
            $(td).find('input.dt-communication-channel').each(function (idx, input) {
              if (window.lodash.isEmpty($(input).val())) {
                $(input).parent().hide();
              }
            });
          }

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
              let deleted_keys = (field_meta.val()) ? JSON.parse(field_meta.val()) : [];
              deleted_keys.push(key);
              field_meta.val(JSON.stringify(deleted_keys));
            }

            // Final removal of input group
            $(evt.currentTarget).parent().parent().remove();
          });

          break;

        case 'location_meta': {

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
        }

        case 'location': {

          let translations = window.merge_post_details['translations'];

          let typeahead_field_input = '.js-typeahead-' + field_id;

          // Disable field accordingly, based on read-only flag
          $(td).find(typeahead_field_input).prop('disabled', read_only);

          /**
           * Load Typeahead
           */

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

        case 'user_select': {

          let user_select_typeahead_field_input = '.js-typeahead-' + field_id;

          // Disable field accordingly, based on read-only flag
          $(td).find(user_select_typeahead_field_input).prop('disabled', read_only);

          /**
           * Load Typeahead
           */

          $(td).find(user_select_typeahead_field_input).typeahead({
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

          // If available, load previous post record user selection
          let user_select_typeahead = window.Typeahead[user_select_typeahead_field_input];
          let post_user_select = post ? post[post_field_id] : undefined;

          if ((post_user_select !== undefined) && user_select_typeahead) {
            $(user_select_typeahead_field_input).val(post_user_select['display']);
            user_select_typeahead.item = {
              ID: post_user_select['id'],
              name: post_user_select['display']
            };
          }

          break;
        }

        case 'connection': {

          let connection_typeahead_field_input = '.js-typeahead-' + field_id;

          // Disable field accordingly, based on read-only flag
          $(td).find(connection_typeahead_field_input).prop('disabled', read_only);

          // Hide typeahead search button
          $(td).find('.typeahead__button').hide();

          /**
           * Load Typeahead
           */

          if ($(td).find(connection_typeahead_field_input).length) {
            $(td).find(connection_typeahead_field_input).typeahead({
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
                    // Keep a record of deleted options
                    let deleted_items = (field_meta.val()) ? JSON.parse(field_meta.val()) : [];
                    deleted_items.push(item);
                    field_meta.val(JSON.stringify(deleted_items));
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
          }

          // If available, load previous post record locations
          let connection_typeahead = window.Typeahead[connection_typeahead_field_input];
          let post_connections = post ? post[post_field_id] : undefined;

          if ((post_connections !== undefined) && connection_typeahead) {
            $.each(post_connections, function (idx, connection) {
              connection_typeahead.addMultiselectItemLayout({
                ID: connection['ID'],
                name: window.lodash.escape(connection['post_title'])
              });
            });
          }

          break;
        }
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
      case 'textarea':
      case 'number':
      case 'boolean':
      case 'text':
        if (is_selected) {
          $('#' + update_field_id).val($('#' + field_id).val());
        }
        break;

      case 'key_select':
        if (is_selected) {

          // Determine select type
          let key_select = null;
          let tr = $(selector).parent().parent();
          if ($(tr).find('#' + field_id).length > 0) {
            key_select = $(tr).find('#' + field_id);
          } else if (($(tr).find('#' + update_field_id).length > 0) && !window.lodash.isEmpty($(tr).find('#' + update_field_id).css('background-color'))) {
            key_select = $(tr).find('#' + update_field_id);
          }

          // Assuming we have a handle on a valid select, update accoridngly
          if (key_select) {
            $('#main_updated_fields_div').find('#' + update_field_id).val($(key_select).val());
          }
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

          // Transfer metadata info
          let source_date_field_meta = $('#' + field_id).parent().parent().find('#field_meta');
          let update_date_field_meta = $('#' + update_field_id).parent().parent().find('#field_meta');
          $(update_date_field_meta).val($(source_date_field_meta).val());

        }
        break;

      case 'multi_select': {

        // Determine values to be updated
        let updated_selections = $(selector.parent().parent()).find('button[data-field-key="' + update_field_id + '"].selected-select-button');

        // Update values accordingly
        $.each(updated_selections, function (idx, source_button) {
          let update_button = $('#main_updated_fields_div').find('#' + $(source_button).attr('id'));
          if (update_button) {
            $(update_button).toggleClass('selected-select-button', is_field_value_still_selected(update_field_id, field_type, source_button));
          }
        });

        break;
      }

      case 'tags': {

        // Determine values to be updated
        let source_typeahead_tags = window.Typeahead['.js-typeahead-' + field_id];
        let update_typeahead_tags = window.Typeahead['.js-typeahead-' + update_field_id];

        // Update values accordingly
        if (source_typeahead_tags && update_typeahead_tags) {

          // Obtain handle onto update field meta - deletion array
          let update_tags_field_meta = $('#main_updated_fields_div').find('#' + update_field_id).parent().find('#field_meta');
          let deleted_items = (update_tags_field_meta && ($(update_tags_field_meta).length > 0) && $(update_tags_field_meta).val()) ? JSON.parse($(update_tags_field_meta).val()) : [];

          // Iterate source tags, updating accordingly
          $.each(source_typeahead_tags.items, function (idx, source_tag) {
            if (is_selected) { // Add, if not already present

              if (!window.lodash.includes(update_typeahead_tags.items, source_tag)) {
                update_typeahead_tags.addMultiselectItemLayout(source_tag);

                // Keep deleted items in sync
                let found_tag = window.lodash.find(deleted_items, function (item) {
                  return new String(item['name']).valueOf() == new String(source_tag['name'].valueOf());
                });

                if (found_tag) {
                  window.lodash.remove(deleted_items, found_tag);
                  $(update_tags_field_meta).val(JSON.stringify(deleted_items));
                }
              }

            } else { // Remove, if present and not still selected anywhere else!

              if (!is_field_value_still_selected(update_field_id, field_type, source_tag)) {

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

                // Keep deleted items in sync
                let found_tag = window.lodash.find(deleted_items, function (item) {
                  return new String(item['name']).valueOf() == new String(source_tag['name'].valueOf());
                });

                if (!found_tag) {
                  deleted_items.push(source_tag);
                  $(update_tags_field_meta).val(JSON.stringify(deleted_items));
                }
              }
            }
          });

          update_typeahead_tags.adjustInputSize();

        }

        break;
      }

      case 'communication_channel': {

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

          // Obtain handle onto update field meta - deletion array
          let update_field_meta = $('#main_updated_fields_div').find('#merge_field_id[value="' + update_field_id + '"]').parent().find('#field_meta');
          let deleted_items = (update_field_meta && ($(update_field_meta).length > 0) && $(update_field_meta).val()) ? JSON.parse($(update_field_meta).val()) : [];

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

              // If present, remove from deleted list
              let purged_items = window.lodash.remove(deleted_items, function (deleted) {
                return window.lodash.includes(value, deleted['value']);
              });

              if (purged_items && purged_items.length > 0) {
                $(update_field_meta).val(JSON.stringify(deleted_items));
              }

            } else if (!is_selected && has_value && value_ele) {

              // Remove, if present and not still selected anywhere else!
              if (!is_field_value_still_selected(update_field_id, field_type, value)) {
                $(value_ele).parent().remove();

                // Keep deleted items in sync
                if ($(value_ele).attr('id') && $(value_ele).attr('id').length > 0) {
                  deleted_items.push({
                    'key': $(value_ele).attr('id'),
                    'value': value
                  });
                  $(update_field_meta).val(JSON.stringify(deleted_items));
                }

              }

            }

          });

        }

        break;
      }

      case 'location_meta': {

        // Determine values to be updated
        let location_elements = $(selector.parent().parent()).find('input.input-group-field');

        // Update values accordingly
        if (location_elements) {

          // Obtain handle to existing list
          let td = $('#main_updated_fields_div').find('#mapbox-autocomplete').parent();

          // Obtain handle onto update field meta - deletion array
          let update_field_meta = $('#main_updated_fields_div').find('#merge_field_id[value="' + update_field_id + '"]').parent().find('#field_meta');
          let deleted_items = (update_field_meta && ($(update_field_meta).length > 0) && $(update_field_meta).val()) ? JSON.parse($(update_field_meta).val()) : [];

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
              let clone = $(element).clone();
              $(clone).css('margin-bottom', '10px');
              td.append(clone);

              // Keep deleted items in sync
              if ($(clone).attr('id') && ($(clone).attr('id').length > 0) && ($(clone).attr('id').indexOf('-') >= 0)) {
                let id = $(clone).attr('id').substring($(clone).attr('id').indexOf('-') + 1);
                if (window.lodash.includes(deleted_items, id)) {
                  window.lodash.remove(deleted_items, function (item) {
                    return new String(item).valueOf() == new String(id);
                  });

                  $(update_field_meta).val(JSON.stringify(deleted_items));
                }
              }

            } else if (!is_selected && has_value && value_ele) {

              // Remove, if present and not still selected anywhere else!
              if (!is_field_value_still_selected(update_field_id, field_type, $(value_ele).val())) {
                $(value_ele).remove();

                // Keep deleted items in sync
                if ($(value_ele).attr('id') && ($(value_ele).attr('id').length > 0) && ($(value_ele).attr('id').indexOf('-') >= 0)) {
                  let id = $(value_ele).attr('id').substring($(value_ele).attr('id').indexOf('-') + 1);
                  if (!window.lodash.includes(deleted_items, id)) {
                    deleted_items.push(id);
                    $(update_field_meta).val(JSON.stringify(deleted_items));
                  }
                }
              }
            }

          });

        }

        break;
      }

      case 'connection':
      case 'location': {

        // Determine values to be updated
        let source_typeahead = window.Typeahead['.js-typeahead-' + field_id];
        let update_typeahead = window.Typeahead['.js-typeahead-' + update_field_id];

        // Update values accordingly
        if (source_typeahead && update_typeahead) {

          // Obtain handle onto update field meta - deletion array
          let update_field_meta = $('#main_updated_fields_div').find('#merge_field_id[value="' + update_field_id + '"]').parent().find('#field_meta');
          let deleted_items = (update_field_meta && ($(update_field_meta).length > 0) && $(update_field_meta).val()) ? JSON.parse($(update_field_meta).val()) : [];

          // Iterate source tags, updating accordingly
          $.each(source_typeahead.items, function (idx, source) {

            if (is_selected) { // Add, if not already present

              if (!window.lodash.includes(update_typeahead.items, source['ID'])) {
                update_typeahead.addMultiselectItemLayout(source);

                // Keep deleted items in sync
                let found = window.lodash.find(deleted_items, function (item) {
                  return new String(item['ID']).valueOf() == new String(source['ID'].valueOf());
                });

                if (found) {
                  window.lodash.remove(deleted_items, found);
                  $(update_field_meta).val(JSON.stringify(deleted_items));
                }
              }

            } else { // Remove, if present and not still selected anywhere else!

              if (!is_field_value_still_selected(update_field_id, field_type, source)) {

                // Remove item object
                window.lodash.remove(update_typeahead.items, function (value) {
                  return value['name'] === source['name'];
                });

                // Remove compared item string
                window.lodash.remove(update_typeahead.comparedItems, function (value) {
                  return new String(value).valueOf() == new String(source['ID'].valueOf());
                });

                // Remove matching label container
                $(update_typeahead.label.container).find('.typeahead__label').each(function (idx, label) {
                  if ($(label).find('span').not('span.typeahead__cancel-button').text() === source['name']) {
                    $(label).remove();
                  }
                });

                // Keep deleted items in sync
                let found = window.lodash.find(deleted_items, function (item) {
                  return new String(item['ID']).valueOf() == new String(source['ID'].valueOf());
                });

                if (!found) {
                  deleted_items.push(source);
                  $(update_field_meta).val(JSON.stringify(deleted_items));
                }

              }
            }
          });

          update_typeahead.adjustInputSize();

        }

        break;
      }

      case 'user_select': {

        // Determine values to be updated
        let source_user_select_typeahead_field_input = '.js-typeahead-' + field_id;
        let update_user_select_typeahead_field_input = '.js-typeahead-' + update_field_id;
        let source_typeahead_user_select = window.Typeahead[source_user_select_typeahead_field_input];
        let update_typeahead_user_select = window.Typeahead[update_user_select_typeahead_field_input];

        // Update values accordingly
        if (source_typeahead_user_select && update_typeahead_user_select) {
          if (is_selected) {
            $(update_user_select_typeahead_field_input).val($(source_user_select_typeahead_field_input).val());
            update_typeahead_user_select.item = source_typeahead_user_select.item;
          }
        }

        break;
      }
    }

  }

  function is_field_value_still_selected(field_id, field_type, field_value) {

    let still_selected = false;

    // Determine current merge state post ids + supporting meta info
    let merging_objs = [
      {
        'post_id': $('#main_archiving_current_post_id').val(),
        'fields_div': 'main_archiving_fields_div'
      },
      {
        'post_id': $('#main_primary_current_post_id').val(),
        'fields_div': 'main_primary_fields_div'
      }];

    // Iterate over both ids, in search of matching field values
    $.each(merging_objs, function (idx, merge_obj) {

      // Obtain handle on td field select input widget
      let td_field_select_input = $('#' + merge_obj['fields_div']).find('.td-field-select input[data-merge_field_id="' + merge_obj['post_id'] + "_" + field_id + '"]');

      // Only proceed if input is selected
      if (td_field_select_input && $(td_field_select_input).is(':checked')) {

        // Compare values (by field type) to determine if there is still a match
        switch (field_type) {
          case 'textarea':
          case 'number':
          case 'boolean':
          case 'text':
          case 'date': {
            break;
          }


          case 'key_select': {
            break;
          }

          case 'multi_select': {
            $(td_field_select_input).parent().parent().find('button.selected-select-button')
              .each(function (idx, button) {
                if (button && ($(button).length > 0) && field_value && $(button).attr('id').valueOf() == $(field_value).attr('id').valueOf()) {
                  still_selected = true;
                }
              });

            break;
          }

          case 'tags':
          case 'location': {
            let typeahead = window.Typeahead['.js-typeahead-' + merge_obj['post_id'] + "_" + field_id];
            if (typeahead) {
              let matched_value = window.lodash.find(typeahead.items, function (item) {
                return new String(item['name']).valueOf() == new String(field_value['name'].valueOf());
              });

              if (matched_value && $(matched_value).length > 0) {
                still_selected = true;
              }
            }

            break;
          }

          case 'communication_channel':
          case 'location_meta': {
            let matched_value = $(td_field_select_input).parent().parent().find('input.input-group-field[value="' + field_value + '"]');
            if (matched_value && $(matched_value).length > 0) {
              still_selected = true;
            }

            break;
          }

          case 'user_select': {
            break;
          }

          case 'connection': {
            let connection_typeahead = window.Typeahead['.js-typeahead-' + merge_obj['post_id'] + "_" + field_id];
            if (connection_typeahead) {
              let matched_value = window.lodash.find(connection_typeahead.items, function (item) {
                return new String(item['ID']).valueOf() == new String(field_value['ID'].valueOf());
              });

              if (matched_value && $(matched_value).length > 0) {
                still_selected = true;
              }
            }

            break;
          }
        }
      }

    });

    return still_selected;
  }

  function is_field_value_already_in_primary(field_id, field_type, field_value) {
    let is_already_in_primary = false;

    // First, obtain handle onto current primary post
    let primary_post = fetch_post_by_merge_type(true)['record'];

    // Ensure primary post contains field in question
    if (primary_post && primary_post[field_id]) {

      // Parse value accordingly, based on field type
      switch (field_type) {
        case 'communication_channel': {

          $.each(primary_post[field_id], function (idx, value) {
            if (window.lodash.includes(value, field_value)) {
              is_already_in_primary = true;
            }
          });

          break;
        }
      }

    }

    return is_already_in_primary;
  }

  function handle_merge() {

    // Disable submit button
    $('.submit-merge').toggleClass('loading').attr("disabled", true);

    // Start packaging updated fields
    let values = {};
    $('#main_updated_fields_div').find('.td-field-input').each(function (idx, td) {

      let field_id = $(td).find('#merge_field_id').val();
      let field_type = $(td).find('#merge_field_type').val();
      let post_field_id = $(td).find('#post_field_id').val();
      let field_meta = $(td).find('#field_meta');

      switch (field_type) {

        case 'textarea':
        case 'number':
        case 'boolean':
        case 'text':
        case 'key_select':
          values[post_field_id] = $(td).find('#' + field_id).val();
          break;

        case 'date':
          values[post_field_id] = $(field_meta).val();
          break;

        case 'multi_select': {
          let options = [];
          $(td).find('button').each(function () {
            options.push({
              'value': $(this).attr('id'),
              'delete': !$(this).hasClass('selected-select-button')
            });
          });

          if (options) {
            values[post_field_id] = {
              'values': options
            };
          }
          break;
        }

        case 'tags':
        case 'location':
        case 'connection': {
          let typeahead = window.Typeahead['.js-typeahead-' + field_id];
          if (typeahead) {

            // Determine values to be processed
            let key = (field_type === 'tags') ? 'name' : 'ID';
            let items = typeahead.items;
            let deletions = field_meta.val() ? JSON.parse(field_meta.val()) : [];

            // Package values and any deletions
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

            // If present, capture entries
            if (entries) {
              values[post_field_id] = {
                'values': entries
              };
            }
          }
          break;
        }

        case 'location_meta': {

          // Determine values to be processed
          let location_meta_entries = [];
          let location_meta_deletions = field_meta.val() ? JSON.parse(field_meta.val()) : [];

          // Package values and any deletions
          $(td).find('input.input-group-field').each(function (idx, input) {
            if ($(input).val()) {
              location_meta_entries.push({
                'value': $(input).val()
              });
            }
          });

          if (window.selected_location_grid_meta !== undefined) {
            location_meta_entries.push({
              'value': window.selected_location_grid_meta
            });
          }

          $.each(location_meta_deletions, function (idx, id) {
            location_meta_entries.push({
              'grid_meta_id': id,
              'delete': true
            });
          });

          // If present, capture entries
          if (location_meta_entries) {
            values[post_field_id] = {
              'values': location_meta_entries
            };
          }
          break;
        }

        case 'communication_channel': {

          // Determine values to be processed
          let comm_entries = [];
          let comm_deletions = field_meta.val() ? JSON.parse(field_meta.val()) : [];

          // Package values and any deletions
          $(td).find('.input-group').each(function () {
            let comm_key = $(this).find('button').data('key');
            let comm_val = $(this).find('input').val();

            if (comm_val && !is_field_value_already_in_primary(post_field_id, field_type, comm_val)) {
              let comm_entry = {
                'value': comm_val
              };

              if (comm_key && comm_key !== 'new') {
                comm_entry['key'] = comm_key;
              }

              comm_entries.push(comm_entry);
            }
          });

          $.each(comm_deletions, function (idx, deleted) {
            comm_entries.push({
              'key': deleted['key'],
              'delete': true
            });
          });

          // If present, capture entries
          if (comm_entries) {
            values[post_field_id] = comm_entries;
          }
          break;
        }

        case 'user_select': {
          let user_select_typeahead = window.Typeahead['.js-typeahead-' + field_id];
          if (user_select_typeahead && user_select_typeahead.item) {
            values[post_field_id] = 'user-' + user_select_typeahead.item['ID'];
          }
          break;
        }
      }

    });

    // Submit packaged fields to backend for further processing
    if (values) {

      // Determine current primary & archiving post records, + others
      let post_type = window.merge_post_details['post_settings']['post_type'];
      let primary_post = fetch_post_by_merge_type(true);
      let archiving_post = fetch_post_by_merge_type(false);

      // Build payload accordingly, based on updated values
      let payload = {
        'post_type': post_type,
        'primary_post_id': primary_post['record']['ID'],
        'archiving_post_id': archiving_post['record']['ID'],
        'merge_comments': $('#merge_comments').is(':checked'),
        'values': values
      };

      console.log(payload);

      // Finally dispatch payload to corresponding post type merge endpoint
      window.makeRequestOnPosts("POST", window.lodash.escape(post_type) + '/merge', payload).then(resp => {
        window.location = primary_post['record']['ID'];

      }).catch(err => {
        console.error(err);
        $('.submit-merge').toggleClass('loading').attr("disabled", false);
        $('#merge_errors').html(window.lodash.escape(window.merge_post_details['translations']['error_msg']) + ' : ' + window.lodash.escape(window.lodash.get(err, 'responseJSON.message', err)));
      });

    } else {
      $('.submit-merge').toggleClass('loading').attr("disabled", false);
    }

  }

});
