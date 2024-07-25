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
      $('#main_archiving_post_id_title').text(
        archiving_post['record']['title'] +
          ' #' +
          archiving_post['record']['ID'],
      );
      $('#main_primary_post_id_title').text(
        primary_post['record']['title'] + ' #' + primary_post['record']['ID'],
      );
      $('#main_updated_post_id_title').text(primary_post['record']['ID']);

      let archiving_id_link =
        window.merge_post_details['site_url'] +
        window.merge_post_details['post_settings']['post_type'] +
        '/' +
        archiving_post['record']['ID'];
      let primary_id_link =
        window.merge_post_details['site_url'] +
        window.merge_post_details['post_settings']['post_type'] +
        '/' +
        primary_post['record']['ID'];
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
    return is_primary
      ? window.merge_post_details['posts'][primary_post_id]
      : window.merge_post_details['posts'][archiving_post_id];
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
          main_updated_fields_div.html(
            window.merge_post_details['post_fields_default_html'],
          );

          // Initialise/Activate recently added html fields
          init_fields(
            archiving_post['record'],
            main_archiving_fields_div,
            true,
          );
          init_fields(primary_post['record'], main_primary_fields_div, true);
          init_fields(null, main_updated_fields_div, false);

          // Adjust & trigger field selections to default states; which should set updating fields accordingly
          main_primary_fields_div
            .find('.field-select')
            .each(function (idx, input) {
              let post_field_id = $(input).data('merge_update_field_id');

              if (
                primary_post['record'][post_field_id] &&
                can_select_field($(input).parent().parent())
              ) {
                $(input).prop('checked', true);
                $(input).trigger('change');
              } else {
                // Otherwise, attempt to default to valid corresponding archiving field
                if ($(input).attr('type') === 'radio') {
                  // Attempt to identify corresponding archive radio input
                  let archive_input = main_archiving_fields_div.find(
                    'input[data-merge_field_id="' +
                      archiving_post['record']['ID'] +
                      '_' +
                      post_field_id +
                      '"]',
                  );
                  if (
                    archiving_post['record'][post_field_id] &&
                    archive_input &&
                    can_select_field($(archive_input).parent().parent())
                  ) {
                    $(archive_input).prop('checked', true);
                    $(archive_input).trigger('change');
                  }
                }
              }
            });

          // Select any archiving fields suitable for auto merging
          main_archiving_fields_div
            .find('.field-select')
            .each(function (idx, input) {
              if ($(input).attr('type') === 'checkbox') {
                let post_field_id = $(input).data('merge_update_field_id');
                if (archiving_post['record'][post_field_id]) {
                  $(input).prop(
                    'checked',
                    can_select_field($(input).parent().parent()),
                  );
                  $(input).trigger('change');
                }
              }
            });

          // Display refreshed post fields
          main_archiving_fields_div.fadeIn('fast', function () {
            main_primary_fields_div.fadeIn('fast', function () {
              main_updated_fields_div.fadeIn('fast', function () {
                // Housekeeping - Ensure all typeahead input fields are displayed nicely ;-)
                $.each(window.Typeahead, function (key, typeahead) {
                  if (
                    typeof typeahead.adjustInputSize === 'function' &&
                    !$.isEmptyObject(typeahead.label)
                  ) {
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
        return (
          !window.lodash.isEmpty($('#' + field_id).val()) ||
          !window.lodash.isEmpty($('#' + post_field_id).val())
        );

      case 'multi_select':
        return !window.lodash.isEmpty(
          $(td_field_input).find('button.selected-select-button'),
        );

      case 'tags':
      case 'location': {
        let typeahead = window.Typeahead['.js-typeahead-' + field_id];
        return typeahead && !window.lodash.isEmpty(typeahead.items);
      }

      case 'link':
        return !window.lodash.isEmpty(
          $(td_field_input).find('input.link-input').not('[value=""]'),
        );

      case 'communication_channel':
      case 'location_meta':
        return !window.lodash.isEmpty(
          $(td_field_input).find('input.input-group-field').not('[value=""]'),
        );

      case 'user_select': {
        let user_select_typeahead =
          window.Typeahead['.js-typeahead-' + field_id];
        return (
          user_select_typeahead &&
          !window.lodash.isEmpty(user_select_typeahead.item)
        );
      }

      case 'connection': {
        let connection_typeahead =
          window.Typeahead['.js-typeahead-' + field_id];
        return (
          connection_typeahead &&
          !window.lodash.isEmpty(connection_typeahead.items)
        );
      }
    }

    return false;
  }

  function init_fields(post, fields_div, read_only) {
    let url_root = window.merge_post_details['url_root'];
    let post_type = window.merge_post_details['post_settings']['post_type'];
    let nonce = window.merge_post_details['nonce'];

    $(fields_div)
      .find('table tbody tr .td-field-input')
      .each(function (idx, td) {
        // Determine field id and type + meta
        let field_id = $(td).find('#merge_field_id').val();
        let field_type = $(td).find('#merge_field_type').val();
        let field_meta = $(td).find('#field_meta');

        // Remove field prefix, ahead of further downstream processing
        let post_field_id = post
          ? window.lodash.replace(field_id, post['ID'] + '_', '')
          : $(td).find('#post_field_id').val();

        // Activate field accordingly, based on type and read-only flag
        switch (field_type) {
          case 'textarea':
          case 'number':
          case 'boolean':
          case 'text':
            // Disable field accordingly, based on read-only flag
            $(td)
              .find('#' + field_id)
              .prop('disabled', read_only);
            break;

          case 'key_select': {
            // Disable field accordingly, based on read-only flag and select type
            let key_select = null;

            // Determine select type
            if ($(td).find('#' + field_id).length > 0) {
              key_select = $(td).find('#' + field_id);
            } else if (
              $(td).find('#' + post_field_id).length > 0 &&
              !window.lodash.isEmpty(
                $(td)
                  .find('#' + post_field_id)
                  .css('background-color'),
              )
            ) {
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
                format: date_format,
              },
            };

            // Adjust start date based on post's date timestamp; if present
            let post_timestamp = $(td)
              .find('#' + field_id)
              .val();
            let hasStartDate =
              post_timestamp && post && post[post_field_id] !== undefined;
            if (hasStartDate) {
              date_config['startDate'] = window.moment.unix(post_timestamp);
              field_meta.val(post_timestamp);
            }

            // Initialise date range picker and respond to selections
            $(td)
              .find('#' + field_id)
              .daterangepicker(date_config, function (start, end, label) {
                if (start) {
                  field_meta.val(start.unix());
                }
              });

            // Render start date display accoridngly, post initialisation
            if (hasStartDate) {
              let start_date = $(td)
                .find('#' + field_id)
                .data('daterangepicker')
                .startDate.format(date_format);
              $(td)
                .find('#' + field_id)
                .val(start_date);
            }

            // Respond to apply date events
            $(td)
              .find('#' + field_id)
              .on('apply.daterangepicker', function (evt, picker) {
                $(td)
                  .find('#' + field_id)
                  .val(picker.startDate.format(date_format));
              });

            // Disable field accordingly, based on read-only flag
            $(td)
              .find('#' + field_id)
              .prop('disabled', read_only);
            $(td).find('.clear-date-button').prop('disabled', read_only);

            /**
             * Clear Date
             */

            $(td)
              .find('.clear-date-button')
              .on('click', (evt) => {
                let input_id = $(evt.currentTarget).data('inputid');

                if (input_id) {
                  $(td)
                    .find('#' + input_id)
                    .val('');
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
              $(td)
                .find('.dt_multi_select')
                .on('click', function (evt) {
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

            $(td)
              .find(typeahead_tags_field_input)
              .typeahead({
                input: typeahead_tags_field_input,
                minLength: 0,
                maxItem: 20,
                searchOnFocus: true,
                source: {
                  tags: {
                    display: ['name'],
                    ajax: {
                      url:
                        url_root +
                        `dt-posts/v2/${post_type}/multi-select-values`,
                      data: {
                        s: '{{query}}',
                        field: field_id,
                      },
                      beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', nonce);
                      },
                      callback: {
                        done: function (data) {
                          return (data || []).map((tag) => {
                            return { name: tag };
                          });
                        },
                      },
                    },
                  },
                },
                display: 'name',
                templateValue: '{{name}}',
                emptyTemplate: function (query) {
                  const { addNewTagText, tagExistsText } = this.node[0].dataset;
                  if (this.comparedItems.includes(query)) {
                    return tagExistsText.replace('%s', query);
                  }
                  const liItem = jQuery('<li>');
                  const button = jQuery('<button>', {
                    class: 'button primary',
                    text: addNewTagText.replace('%s', query),
                  });
                  const tag = this.query;
                  button.on('click', function () {
                    window.Typeahead[
                      typeahead_tags_field_input
                    ].addMultiselectItemLayout({ name: tag });
                  });
                  liItem.append(button);
                  return liItem;
                },
                dynamic: true,
                multiselect: {
                  matchOn: ['name'],
                  data: function () {
                    if (post && post[post_field_id]) {
                      return (post[post_field_id] || []).map((t) => {
                        return { name: t };
                      });
                    } else {
                      return {};
                    }
                  },
                  callback: {
                    onCancel: function (node, item, event) {
                      // Keep a record of deleted tags
                      let deleted_items = field_meta.val()
                        ? JSON.parse(field_meta.val())
                        : [];
                      deleted_items.push(item);
                      field_meta.val(JSON.stringify(deleted_items));
                    },
                  },
                  href: function (item) {},
                },
                callback: {
                  onClick: function (node, a, item, event) {
                    event.preventDefault();
                    this.addMultiselectItemLayout({ name: item.name });
                  },
                  onResult: function (node, query, result, resultCount) {
                    let text = window.TYPEAHEADS.typeaheadHelpText(
                      resultCount,
                      query,
                      result,
                    );
                    $(td).find(`#${field_id}-result-container`).html(text);
                  },
                  onHideLayout: function () {
                    $(td).find(`#${field_id}-result-container`).html('');
                  },
                  onShowLayout() {},
                },
              });

            /**
             * Load
             */

            // If available, load previous post record tags
            if (post && post[post_field_id]) {
              let typeahead_tags = window.Typeahead[typeahead_tags_field_input];
              let post_tags = post[post_field_id];

              if (post_tags !== undefined && typeahead_tags) {
                jQuery.each(post_tags, function (idx, tag) {
                  typeahead_tags.addMultiselectItemLayout({
                    name: window.SHAREDFUNCTIONS.escapeHTML(tag),
                  });
                });
              }
            }

            break;
          }

          case 'link': {
            // Disable/Display field accordingly, based on read-only flag
            $(td).find('input.link-input').prop('disabled', read_only);
            $(td).find('button.link-delete-button').prop('disabled', read_only);

            // Ensure add link functionality is suppressed.
            $(td).find('div.add-link-dropdown').remove();

            if (!read_only) {
              $(td)
                .find('input.link-input')
                .each(function (idx, input) {
                  if (window.lodash.isEmpty($(input).val())) {
                    $(input).parent().hide();
                  }
                });

              /**
               * Remove
               */

              $(document).on('click', '.link-delete-button', (evt) => {
                const delete_but = $(evt.currentTarget);

                // Keep a record of deleted meta_ids.
                let meta_id = $(delete_but).data('meta-id');
                let deleted_items = $(field_meta).val()
                  ? JSON.parse($(field_meta).val())
                  : [];
                if (!window.lodash.includes(deleted_items, meta_id)) {
                  deleted_items.push(meta_id);
                  $(field_meta).val(JSON.stringify(deleted_items));
                }

                // Finally, remove from parent.
                $(delete_but).parent().parent().remove();
              });
            }

            break;
          }

          case 'communication_channel':
            // Disable/Display field accordingly, based on read-only flag
            $(td)
              .find('input.dt-communication-channel')
              .prop('disabled', read_only);

            if (!read_only) {
              $(td)
                .find('input.dt-communication-channel')
                .each(function (idx, input) {
                  if (window.lodash.isEmpty($(input).val())) {
                    $(input).parent().hide();
                  }
                });
            }

            /**
             * Add
             */

            $(td)
              .find('button.add-button')
              .on('click', (evt) => {
                let field = $(evt.currentTarget).data('list-class');
                let list = $(td).find(`#edit-${field}`);

                list.append(`
                <div class="input-group">
                    <input type="text" data-field="${window.SHAREDFUNCTIONS.escapeHTML(field)}" class="dt-communication-channel input-group-field" dir="auto" />
                    <div class="input-group-button">
                        <button class="button alert input-height delete-button-style channel-delete-button delete-button new-${window.SHAREDFUNCTIONS.escapeHTML(field)}" data-key="new" data-field="${window.SHAREDFUNCTIONS.escapeHTML(field)}">&times;</button>
                    </div>
                </div>`);
              });

            /**
             * Remove
             */

            $(document).on('click', '.channel-delete-button', (evt) => {
              let field = $(evt.currentTarget).data('field');
              let key = $(evt.currentTarget).data('key');

              // If needed, keep a record of key for future api removal.
              if (key !== 'new') {
                let deleted_keys = field_meta.val()
                  ? JSON.parse(field_meta.val())
                  : [];
                deleted_keys.push(key);
                field_meta.val(JSON.stringify(deleted_keys));
              }

              // Final removal of input group
              $(evt.currentTarget).parent().parent().remove();
            });

            break;

          case 'location_meta':
          case 'location': {
            if (
              window.merge_post_details?.post_settings?.fields[post_field_id]
            ) {
              const field_settings =
                window.merge_post_details.post_settings.fields[post_field_id];
              if (
                field_type === 'location' &&
                field_settings?.mode === 'normal'
              ) {
                let translations = window.merge_post_details['translations'];

                let typeahead_field_input = '.js-typeahead-' + field_id;

                // Disable field accordingly, based on read-only flag
                $(td).find(typeahead_field_input).prop('disabled', read_only);

                /**
                 * Load Typeahead
                 */

                $(td)
                  .find(typeahead_field_input)
                  .typeahead({
                    input: typeahead_field_input,
                    minLength: 0,
                    accent: true,
                    searchOnFocus: true,
                    maxItem: 20,
                    dropdownFilter: [
                      {
                        key: 'group',
                        value: 'focus',
                        template: window.SHAREDFUNCTIONS.escapeHTML(
                          translations['regions_of_focus'],
                        ),
                        all: window.SHAREDFUNCTIONS.escapeHTML(
                          translations['all_locations'],
                        ),
                      },
                    ],
                    source: {
                      focus: {
                        display: 'name',
                        ajax: {
                          url:
                            url_root +
                            'dt/v1/mapping_module/search_location_grid_by_name',
                          data: {
                            s: '{{query}}',
                            filter: function () {
                              return window.lodash.get(
                                window.Typeahead[typeahead_field_input].filters
                                  .dropdown,
                                'value',
                                'all',
                              );
                            },
                          },
                          beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', nonce);
                          },
                          callback: {
                            done: function (data) {
                              return data.location_grid;
                            },
                          },
                        },
                      },
                    },
                    display: 'name',
                    templateValue: '{{name}}',
                    dynamic: true,
                    multiselect: {
                      matchOn: ['ID'],
                      data: function () {
                        return [];
                      },
                      callback: {
                        onCancel: function (node, item) {
                          // Keep a record of deleted options
                          let deleted_items = field_meta.val()
                            ? JSON.parse(field_meta.val())
                            : [];
                          deleted_items.push(item);
                          field_meta.val(JSON.stringify(deleted_items));
                        },
                      },
                    },
                    callback: {
                      onClick: function (node, a, item, event) {},
                      onReady() {
                        this.filters.dropdown = {
                          key: 'group',
                          value: 'focus',
                          template: window.SHAREDFUNCTIONS.escapeHTML(
                            translations['regions_of_focus'],
                          ),
                        };
                        this.container
                          .removeClass('filter')
                          .find('.' + this.options.selector.filterButton)
                          .html(
                            window.SHAREDFUNCTIONS.escapeHTML(
                              translations['regions_of_focus'],
                            ),
                          );
                      },
                    },
                  });

                // If available, load previous post record locations
                let typeahead = window.Typeahead[typeahead_field_input];
                let post_locations = post ? post[post_field_id] : undefined;

                if (post_locations !== undefined && typeahead) {
                  $.each(post_locations, function (idx, location) {
                    typeahead.addMultiselectItemLayout({
                      ID: location['id'],
                      name: window.SHAREDFUNCTIONS.escapeHTML(
                        location['label'],
                      ),
                    });
                  });
                }
              } else if (
                (field_type === 'location' &&
                  field_settings?.mode === 'geolocation') ||
                field_type === 'location_meta'
              ) {
                let mapbox = window.merge_post_details['mapbox'];

                /**
                 * Load
                 */

                $(td).find(`#${post_field_id}_mapbox-wrapper`).empty().append(`
                    <div id="${field_id}_location-grid-meta-results"></div>
                    <div class="reveal" id="${field_id}_mapping-modal" data-v-offset="0" data-reveal>
                      <div id="${field_id}_mapping-modal-contents"></div>
                      <button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                `);
                new window.Foundation.Reveal(
                  $(td).find(`#${field_id}_mapping-modal`),
                );

                // Display previously saved locations
                let lgm_results = $(td).find(
                  `#${field_id}_location-grid-meta-results`,
                );

                if (
                  post &&
                  post[post_field_id] !== undefined &&
                  post[post_field_id].length !== 0
                ) {
                  $.each(post[post_field_id], function (i, v) {
                    if (v.grid_meta_id) {
                      lgm_results.append(`<div class="input-group">
                        <input type="text" class="active-location input-group-field" id="${field_id}_location-${window.SHAREDFUNCTIONS.escapeHTML(v.grid_meta_id)}" dir="auto" value="${window.SHAREDFUNCTIONS.escapeHTML(v.label)}" readonly />
                        <div class="input-group-button">
                          <button type="button" class="button success delete-button-style open-mapping-grid-modal" title="${window.SHAREDFUNCTIONS.escapeHTML(mapbox['translations']['open_modal'])}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(v.grid_meta_id)}" data-field_id="${window.lodash.escape(field_id)}"><i class="fi-map"></i></button>
                          <button type="button" class="button alert delete-button-style delete-button mapbox-delete-button" title="${window.SHAREDFUNCTIONS.escapeHTML(mapbox['translations']['delete_location'])}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(v.grid_meta_id)}" data-field_id="${window.lodash.escape(field_id)}">&times;</button>
                        </div>
                      </div>`);
                    } else if (v.key) {
                      lgm_results.append(`<div class="input-group">
                        <input type="text" class="dt-communication-channel input-group-field" id="${window.SHAREDFUNCTIONS.escapeHTML(v.key)}" value="${window.SHAREDFUNCTIONS.escapeHTML(v.label)}" dir="auto" data-field="contact_address" />
                        <div class="input-group-button">
                          <button type="button" class="button success delete-button-style open-mapping-address-modal"
                              title="${window.SHAREDFUNCTIONS.escapeHTML(mapbox['translations']['open_modal'])}"
                              data-id="${window.SHAREDFUNCTIONS.escapeHTML(v.key)}"
                              data-field="contact_address"
                              data-key="${window.SHAREDFUNCTIONS.escapeHTML(v.key)}">
                              <i class="fi-pencil"></i>
                          </button>
                          <button type="button" class="button alert input-height delete-button-style channel-delete-button delete-button" title="${window.SHAREDFUNCTIONS.escapeHTML(mapbox['translations']['delete_location'])}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(v.key)}" data-field="contact_address" data-key="${window.SHAREDFUNCTIONS.escapeHTML(v.key)}">&times;</button>
                        </div>
                      </div>`);
                    }
                  });
                }

                /**
                 * Add
                 */

                if (!read_only) {
                  $(td)
                    .find('.new-mapbox-search')
                    .on('click', (evt) => {
                      // Display search field with autosubmit disabled!
                      if (
                        $(td).find(`#${post_field_id}_mapbox-autocomplete`)
                          .length === 0
                      ) {
                        $(td).find(`#${post_field_id}_mapbox-wrapper`).prepend(`
                        <div id="${post_field_id}_mapbox-autocomplete" class="mapbox-autocomplete input-group" data-autosubmit="false">
                            <input id="${field_id}_mapbox-search" type="text" name="mapbox_search" placeholder="${window.SHAREDFUNCTIONS.escapeHTML(mapbox['translations']['search_location'])}" autocomplete="off" dir="auto" />
                            <div class="input-group-button">
                                <button id="${field_id}_mapbox-spinner-button" class="button hollow" style="display:none;"><span class="loading-spinner active"></span></button>
                                <button id="${field_id}_mapbox-clear-autocomplete" class="button alert input-height delete-button-style mapbox-delete-button" type="button" title="${window.SHAREDFUNCTIONS.escapeHTML(mapbox['translations']['delete_location'])}" >&times;</button>
                            </div>
                            <div id="${field_id}_mapbox-autocomplete-list" class="mapbox-autocomplete-items"></div>
                        </div>`);
                      }

                      // Switch over to standard workflow, with autosubmit disabled!
                      window.write_input_widget();
                    });

                  // Hide new button and default to single entry
                  $(td).find('.new-mapbox-search').hide();
                  $(td).find('.new-mapbox-search').trigger('click');
                }

                /**
                 * Remove
                 */

                $(document).on('click', '.mapbox-delete-button', (evt) => {
                  let id = $(evt.currentTarget).data('id');

                  // If needed, keep a record of key for future api removal.
                  if (id !== undefined) {
                    let deleted_ids = field_meta.val()
                      ? JSON.parse(field_meta.val())
                      : [];
                    deleted_ids.push(id);
                    field_meta.val(JSON.stringify(deleted_ids));

                    // Final removal of input group
                    $(evt.currentTarget).parent().parent().remove();
                  } else {
                    // Remove global selected location
                    if (window.selected_location_grid_meta?.field_id) {
                      window.selected_location_grid_meta[field_id] = null;
                    }
                  }
                });

                /**
                 * Open Modal
                 */

                $(td)
                  .find('.open-mapping-grid-modal')
                  .on('click', (evt) => {
                    let grid_meta_id = $(evt.currentTarget).data('id');
                    let post_location_grid_meta = post
                      ? post[post_field_id]
                      : undefined;

                    if (
                      post_location_grid_meta !== undefined &&
                      post_location_grid_meta.length !== 0
                    ) {
                      $.each(post_location_grid_meta, function (i, v) {
                        if (String(grid_meta_id) === String(v.grid_meta_id)) {
                          return window.load_modal(
                            v.lng,
                            v.lat,
                            v.level,
                            v.label,
                            v.grid_id,
                            field_id,
                          );
                        }
                      });
                    }
                  });

                // Disable field accordingly, based on read-only flag
                $(td)
                  .find(`#${field_id}_mapbox-search`)
                  .prop('disabled', read_only);
              }
            }
            break;
          }

          case 'user_select': {
            let user_select_typeahead_field_input = '.js-typeahead-' + field_id;

            // Disable field accordingly, based on read-only flag
            $(td)
              .find(user_select_typeahead_field_input)
              .prop('disabled', read_only);

            /**
             * Load Typeahead
             */

            $(td)
              .find(user_select_typeahead_field_input)
              .typeahead({
                input: user_select_typeahead_field_input,
                minLength: 0,
                maxItem: 0,
                accent: true,
                searchOnFocus: true,
                source: window.TYPEAHEADS.typeaheadUserSource(),
                templateValue: '{{name}}',
                template: function (query, item) {
                  return `<div class="assigned-to-row" dir="auto">
                  <span>
                      <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
                      ${window.SHAREDFUNCTIONS.escapeHTML(item.name)}
                  </span>
                  ${item.status_color ? `<span class="status-square" style="background-color: ${window.SHAREDFUNCTIONS.escapeHTML(item.status_color)};">&nbsp;</span>` : ''}
                  ${
                    item.update_needed && item.update_needed > 0
                      ? `<span>
                    <img style="height: 12px;" src="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.template_dir)}/dt-assets/images/broken.svg"/>
                    <span style="font-size: 14px">${window.SHAREDFUNCTIONS.escapeHTML(item.update_needed)}</span>
                  </span>`
                      : ''
                  }
                </div>`;
                },
                dynamic: true,
                hint: true,
                emptyTemplate: window.SHAREDFUNCTIONS.escapeHTML(
                  window.wpApiShare.translations.no_records_found,
                ),
                callback: {
                  onClick: function (node, a, item) {},
                  onResult: function (node, query, result, resultCount) {
                    let text = window.TYPEAHEADS.typeaheadHelpText(
                      resultCount,
                      query,
                      result,
                    );
                    $(`#${field_id}-result-container`).html(text);
                  },
                  onHideLayout: function () {
                    $(`.${field_id}-result-container`).html('');
                  },
                },
              });

            // If available, load previous post record user selection
            let user_select_typeahead =
              window.Typeahead[user_select_typeahead_field_input];
            let post_user_select = post ? post[post_field_id] : undefined;

            if (post_user_select !== undefined && user_select_typeahead) {
              $(user_select_typeahead_field_input).val(
                post_user_select['display'],
              );
              user_select_typeahead.item = {
                ID: post_user_select['id'],
                name: post_user_select['display'],
              };
            }

            break;
          }

          case 'connection': {
            let connection_typeahead_field_input = '.js-typeahead-' + field_id;

            // Disable field accordingly, based on read-only flag
            $(td)
              .find(connection_typeahead_field_input)
              .prop('disabled', read_only);

            // Hide typeahead search button
            $(td).find('.typeahead__button').hide();

            /**
             * Load Typeahead
             */

            if ($(td).find(connection_typeahead_field_input).length) {
              $(td)
                .find(connection_typeahead_field_input)
                .typeahead({
                  input: connection_typeahead_field_input,
                  minLength: 0,
                  accent: true,
                  searchOnFocus: true,
                  maxItem: 20,
                  template: window.TYPEAHEADS.contactListRowTemplate,
                  source: window.TYPEAHEADS.typeaheadPostsSource(
                    post_type,
                    field_id,
                  ),
                  display: ['name', 'label'],
                  templateValue: function () {
                    if (this.items[this.items.length - 1].label) {
                      return '{{label}}';
                    } else {
                      return '{{name}}';
                    }
                  },
                  dynamic: true,
                  multiselect: {
                    matchOn: ['ID'],
                    data: [],
                    callback: {
                      onCancel: function (node, item) {
                        // Keep a record of deleted options
                        let deleted_items = field_meta.val()
                          ? JSON.parse(field_meta.val())
                          : [];
                        deleted_items.push(item);
                        field_meta.val(JSON.stringify(deleted_items));
                      },
                    },
                  },
                  callback: {
                    onResult: function (node, query, result, resultCount) {
                      let text = window.TYPEAHEADS.typeaheadHelpText(
                        resultCount,
                        query,
                        result,
                      );
                      $(`#${field_id}-result-container`).html(text);
                    },
                    onHideLayout: function () {
                      $(`#${field_id}-result-container`).html('');
                    },
                    onClick: function (node, a, item, event) {
                      // Stop list from opening again
                      this.addMultiselectItemLayout(item);
                      event.preventDefault();
                      this.hideLayout();
                      this.resetInput();
                    },
                  },
                });
            }

            // If available, load previous post record locations
            let connection_typeahead =
              window.Typeahead[connection_typeahead_field_input];
            let post_connections = post ? post[post_field_id] : undefined;

            if (post_connections !== undefined && connection_typeahead) {
              $.each(post_connections, function (idx, connection) {
                connection_typeahead.addMultiselectItemLayout({
                  ID: connection['ID'],
                  name: window.SHAREDFUNCTIONS.escapeHTML(
                    connection['post_title'],
                  ),
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
    update_fields(
      $(evt.currentTarget),
      is_selected,
      update_field_id,
      field_id,
      field_type,
    );
  }

  function update_fields(
    selector,
    is_selected,
    update_field_id,
    field_id,
    field_type,
  ) {
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
          } else if (
            $(tr).find('#' + update_field_id).length > 0 &&
            !window.lodash.isEmpty(
              $(tr)
                .find('#' + update_field_id)
                .css('background-color'),
            )
          ) {
            key_select = $(tr).find('#' + update_field_id);
          }

          // Assuming we have a handle on a valid select, update accoridngly
          if (key_select) {
            $('#main_updated_fields_div')
              .find('#' + update_field_id)
              .val($(key_select).val());
          }
        }
        break;

      case 'date':
        if (is_selected) {
          let source_date_range_picker = $('#' + field_id).data(
            'daterangepicker',
          );
          let update_date_range_picker = $('#' + update_field_id).data(
            'daterangepicker',
          );

          // Determine values to be updated
          let updated_date = source_date_range_picker.startDate;
          let updated_date_ts = $('#' + field_id).val();

          // Update values accordingly
          update_date_range_picker.setStartDate(updated_date);
          update_date_range_picker.setEndDate(updated_date);
          $('#' + update_field_id).val(updated_date_ts);

          // Transfer metadata info
          let source_date_field_meta = $('#' + field_id)
            .parent()
            .parent()
            .find('#field_meta');
          let update_date_field_meta = $('#' + update_field_id)
            .parent()
            .parent()
            .find('#field_meta');
          $(update_date_field_meta).val($(source_date_field_meta).val());
        }
        break;

      case 'multi_select': {
        // Determine values to be updated
        let updated_selections = $(selector.parent().parent()).find(
          'button[data-field-key="' +
            update_field_id +
            '"].selected-select-button',
        );

        // Update values accordingly
        $.each(updated_selections, function (idx, source_button) {
          let update_button = $('#main_updated_fields_div').find(
            '#' + $(source_button).attr('id'),
          );
          if (update_button) {
            $(update_button).toggleClass(
              'selected-select-button',
              is_field_value_still_selected(
                update_field_id,
                field_type,
                source_button,
              ),
            );
          }
        });

        break;
      }

      case 'tags': {
        // Determine values to be updated
        let source_typeahead_tags =
          window.Typeahead['.js-typeahead-' + field_id];
        let update_typeahead_tags =
          window.Typeahead['.js-typeahead-' + update_field_id];

        // Update values accordingly
        if (source_typeahead_tags && update_typeahead_tags) {
          // Obtain handle onto update field meta - deletion array
          let update_tags_field_meta = $('#main_updated_fields_div')
            .find('#' + update_field_id)
            .parent()
            .find('#field_meta');
          let deleted_items =
            update_tags_field_meta &&
            $(update_tags_field_meta).length > 0 &&
            $(update_tags_field_meta).val()
              ? JSON.parse($(update_tags_field_meta).val())
              : [];

          // Iterate source tags, updating accordingly
          $.each(source_typeahead_tags.items, function (idx, source_tag) {
            if (is_selected) {
              // Add, if not already present

              if (
                !window.lodash.includes(update_typeahead_tags.items, source_tag)
              ) {
                update_typeahead_tags.addMultiselectItemLayout(source_tag);

                // Keep deleted items in sync
                let found_tag = window.lodash.find(
                  deleted_items,
                  function (item) {
                    return (
                      new String(item['name']).valueOf() ==
                      new String(source_tag['name'].valueOf())
                    );
                  },
                );

                if (found_tag) {
                  window.lodash.remove(deleted_items, found_tag);
                  $(update_tags_field_meta).val(JSON.stringify(deleted_items));
                }
              }
            } else {
              // Remove, if present and not still selected anywhere else!

              if (
                !is_field_value_still_selected(
                  update_field_id,
                  field_type,
                  source_tag,
                )
              ) {
                // Remove item object
                window.lodash.remove(
                  update_typeahead_tags.items,
                  function (tag) {
                    return tag['name'] === source_tag['name'];
                  },
                );

                // Remove compared item string
                window.lodash.remove(
                  update_typeahead_tags.comparedItems,
                  function (tag) {
                    return tag === source_tag['name'];
                  },
                );

                // Remove matching label container
                $(update_typeahead_tags.label.container)
                  .find('.typeahead__label')
                  .each(function (idx, label) {
                    if ($(label).find('a').text() === source_tag['name']) {
                      $(label).remove();
                    }
                  });

                // Keep deleted items in sync
                let found_tag = window.lodash.find(
                  deleted_items,
                  function (item) {
                    return (
                      new String(item['name']).valueOf() ==
                      new String(source_tag['name'].valueOf())
                    );
                  },
                );

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

      case 'link': {
        // Determine selector source field link inputs to be processed.
        let source_field_link_inputs = [];
        let tr = $(selector).parent().parent();
        $(tr)
          .find('.td-field-input input.link-input')
          .each(function (idx, input) {
            if ($(input).val()) {
              source_field_link_inputs.push(input);
            }
          });

        // Delete/Add updated post record, based on identified source field inputs.
        let main_updated_fields_div = $('#main_updated_fields_div');
        let link_field_meta_input = $(main_updated_fields_div)
          .find(`.link-list-${update_field_id}`)
          .parent()
          .parent()
          .find('#field_meta');
        let deleted_items = $(link_field_meta_input).val()
          ? JSON.parse($(link_field_meta_input).val())
          : [];

        // Locate by link field values.
        $.each(source_field_link_inputs, function (idx, input) {
          let link_list_section_div = $(main_updated_fields_div).find(
            `.link-list-${update_field_id} .link-section--${$(input).data('type')}`,
          );
          let matched_input = $(link_list_section_div).find(
            `.input-group input[value="${$(input).val()}"].link-input`,
          );

          // Handle accordingly, based on incoming selected state.
          if (is_selected) {
            // Add new updated link fields.
            if (matched_input.length === 0) {
              $(link_list_section_div).append(`
                <div class="input-group">
                    <input type="text" class="link-input input-group-field" value="${window.SHAREDFUNCTIONS.escapeHTML($(input).val())}" data-meta-id="${window.SHAREDFUNCTIONS.escapeHTML($(input).data('meta-id'))}" data-field-key="${window.SHAREDFUNCTIONS.escapeHTML(update_field_id)}" data-type="${window.SHAREDFUNCTIONS.escapeHTML($(input).data('type'))}">
                    <div class="input-group-button">
                        <button class="button alert delete-button-style input-height link-delete-button delete-button" data-meta-id="${window.SHAREDFUNCTIONS.escapeHTML($(input).data('meta-id'))}" data-field-key="${window.SHAREDFUNCTIONS.escapeHTML(update_field_id)}">&times;</button>
                    </div>
                </div>`);

              // Remove any previously deleted entries.
              window.lodash.remove(deleted_items, function (meta_id) {
                return meta_id === $(input).data('meta-id');
              });
              $(link_field_meta_input).val(JSON.stringify(deleted_items));
            }
          } else {
            // Remove new updated link fields.
            if (matched_input.length > 0) {
              $(matched_input).parent().remove();

              // Keep a record of deleted meta_ids.
              if (
                !window.lodash.includes(
                  deleted_items,
                  $(matched_input).data('meta-id'),
                )
              ) {
                deleted_items.push($(matched_input).data('meta-id'));
                $(link_field_meta_input).val(JSON.stringify(deleted_items));
              }
            }
          }
        });

        break;
      }

      case 'communication_channel': {
        // Determine values to be updated
        let comm_values = [];
        let comm_elements = $(selector.parent().parent()).find(
          'input[data-field="' + update_field_id + '"].input-group-field',
        );
        $.each(comm_elements, function (idx, element) {
          if ($(element).val()) {
            comm_values.push($(element).val());
          }
        });

        // Update values accordingly
        if (comm_values) {
          // Obtain handle to existing list
          let list = $('#main_updated_fields_div').find(
            `#edit-${update_field_id}`,
          );

          // Obtain handle onto update field meta - deletion array
          let update_field_meta = $('#main_updated_fields_div')
            .find('#merge_field_id[value="' + update_field_id + '"]')
            .parent()
            .find('#field_meta');
          let deleted_items =
            update_field_meta &&
            $(update_field_meta).length > 0 &&
            $(update_field_meta).val()
              ? JSON.parse($(update_field_meta).val())
              : [];

          // Iterate over values; processing accordingly
          $.each(comm_values, function (idx, value) {
            // Determine if the value already exists within update list
            let has_value = false;
            let value_ele = null;

            $(list)
              .find(
                'input[data-field="' + update_field_id + '"].input-group-field',
              )
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
                    <input type="text" data-field="${window.SHAREDFUNCTIONS.escapeHTML(update_field_id)}" class="dt-communication-channel input-group-field" dir="auto" value="${window.SHAREDFUNCTIONS.escapeHTML(value)}" />
                    <div class="input-group-button">
                        <button class="button alert input-height delete-button-style channel-delete-button delete-button new-${window.SHAREDFUNCTIONS.escapeHTML(update_field_id)}" data-key="new" data-field="${window.SHAREDFUNCTIONS.escapeHTML(update_field_id)}">&times;</button>
                    </div>
                </div>`);

              // If present, remove from deleted list
              let purged_items = window.lodash.remove(
                deleted_items,
                function (deleted) {
                  return window.lodash.includes(value, deleted['value']);
                },
              );

              if (purged_items && purged_items.length > 0) {
                $(update_field_meta).val(JSON.stringify(deleted_items));
              }
            } else if (!is_selected && has_value && value_ele) {
              // Remove, if present and not still selected anywhere else!
              if (
                !is_field_value_still_selected(
                  update_field_id,
                  field_type,
                  value,
                )
              ) {
                $(value_ele).parent().remove();

                // Keep deleted items in sync
                if (
                  $(value_ele).attr('id') &&
                  $(value_ele).attr('id').length > 0
                ) {
                  deleted_items.push({
                    key: $(value_ele).attr('id'),
                    value: value,
                  });
                  $(update_field_meta).val(JSON.stringify(deleted_items));
                }
              }
            }
          });
        }

        break;
      }

      case 'location_meta':
      case 'location': {
        if (window.merge_post_details?.post_settings?.fields[update_field_id]) {
          const field_settings =
            window.merge_post_details.post_settings.fields[update_field_id];
          if (field_type === 'location' && field_settings?.mode === 'normal') {
            // Determine values to be updated
            let source_typeahead =
              window.Typeahead['.js-typeahead-' + field_id];
            let update_typeahead =
              window.Typeahead['.js-typeahead-' + update_field_id];

            // Update values accordingly
            if (source_typeahead && update_typeahead) {
              // Obtain handle onto update field meta - deletion array
              let update_field_meta = $('#main_updated_fields_div')
                .find('#merge_field_id[value="' + update_field_id + '"]')
                .parent()
                .find('#field_meta');
              let deleted_items =
                update_field_meta &&
                $(update_field_meta).length > 0 &&
                $(update_field_meta).val()
                  ? JSON.parse($(update_field_meta).val())
                  : [];

              // Iterate source tags, updating accordingly
              $.each(source_typeahead.items, function (idx, source) {
                if (is_selected) {
                  // Add, if not already present

                  if (
                    !window.lodash.includes(
                      update_typeahead.items,
                      source['ID'],
                    )
                  ) {
                    update_typeahead.addMultiselectItemLayout(source);

                    // Keep deleted items in sync
                    let found = window.lodash.find(
                      deleted_items,
                      function (item) {
                        return (
                          new String(item['ID']).valueOf() ==
                          new String(source['ID'].valueOf())
                        );
                      },
                    );

                    if (found) {
                      window.lodash.remove(deleted_items, found);
                      $(update_field_meta).val(JSON.stringify(deleted_items));
                    }
                  }
                } else {
                  // Remove, if present and not still selected anywhere else!

                  if (
                    !is_field_value_still_selected(
                      update_field_id,
                      field_type,
                      source,
                    )
                  ) {
                    // Remove item object
                    window.lodash.remove(
                      update_typeahead.items,
                      function (value) {
                        return value['name'] === source['name'];
                      },
                    );

                    // Remove compared item string
                    window.lodash.remove(
                      update_typeahead.comparedItems,
                      function (value) {
                        return (
                          new String(value).valueOf() ==
                          new String(source['ID'].valueOf())
                        );
                      },
                    );

                    // Remove matching label container
                    $(update_typeahead.label.container)
                      .find('.typeahead__label')
                      .each(function (idx, label) {
                        if (
                          $(label)
                            .find('span')
                            .not('span.typeahead__cancel-button')
                            .text() === source['name']
                        ) {
                          $(label).remove();
                        }
                      });

                    // Keep deleted items in sync
                    let found = window.lodash.find(
                      deleted_items,
                      function (item) {
                        return (
                          new String(item['ID']).valueOf() ==
                          new String(source['ID'].valueOf())
                        );
                      },
                    );

                    if (!found) {
                      deleted_items.push(source);
                      $(update_field_meta).val(JSON.stringify(deleted_items));
                    }
                  }
                }
              });

              update_typeahead.adjustInputSize();
            }
          } else if (
            (field_type === 'location' &&
              field_settings?.mode === 'geolocation') ||
            field_type === 'location_meta'
          ) {
            // Determine values to be updated
            let location_elements = $(selector.parent().parent()).find(
              'input.input-group-field',
            );

            // Update values accordingly
            if (location_elements) {
              // Obtain handle to existing list
              let td = $('#main_updated_fields_div')
                .find(`#${update_field_id}_mapbox-autocomplete`)
                .parent();

              // Obtain handle onto update field meta - deletion array
              let update_field_meta = $('#main_updated_fields_div')
                .find('#merge_field_id[value="' + update_field_id + '"]')
                .parent()
                .find('#field_meta');
              let deleted_items =
                update_field_meta &&
                $(update_field_meta).length > 0 &&
                $(update_field_meta).val()
                  ? JSON.parse($(update_field_meta).val())
                  : [];

              // Iterate over values; processing accordingly
              $.each(location_elements, function (idx, element) {
                // Determine if the value already exists within update list
                let has_value = false;
                let value_ele = null;

                $(td)
                  .find('input.input-group-field')
                  .each(function (idx, input) {
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
                  if (
                    $(clone).attr('id') &&
                    $(clone).attr('id').length > 0 &&
                    $(clone).attr('id').indexOf('-') >= 0
                  ) {
                    let id = $(clone)
                      .attr('id')
                      .substring($(clone).attr('id').indexOf('-') + 1);
                    if (window.lodash.includes(deleted_items, id)) {
                      window.lodash.remove(deleted_items, function (item) {
                        return new String(item).valueOf() == new String(id);
                      });

                      $(update_field_meta).val(JSON.stringify(deleted_items));
                    }
                  }
                } else if (!is_selected && has_value && value_ele) {
                  // Remove, if present and not still selected anywhere else!
                  if (
                    !is_field_value_still_selected(
                      update_field_id,
                      field_type,
                      $(value_ele).val(),
                    )
                  ) {
                    $(value_ele).remove();

                    // Keep deleted items in sync
                    if (
                      $(value_ele).attr('id') &&
                      $(value_ele).attr('id').length > 0 &&
                      $(value_ele).attr('id').indexOf('-') >= 0
                    ) {
                      let id = $(value_ele)
                        .attr('id')
                        .substring($(value_ele).attr('id').indexOf('-') + 1);
                      if (!window.lodash.includes(deleted_items, id)) {
                        deleted_items.push(id);
                        $(update_field_meta).val(JSON.stringify(deleted_items));
                      }
                    }
                  }
                }
              });
            }
          }
        }
        break;
      }

      case 'connection': {
        // Determine values to be updated
        let source_typeahead = window.Typeahead['.js-typeahead-' + field_id];
        let update_typeahead =
          window.Typeahead['.js-typeahead-' + update_field_id];

        // Update values accordingly
        if (source_typeahead && update_typeahead) {
          // Obtain handle onto update field meta - deletion array
          let update_field_meta = $('#main_updated_fields_div')
            .find('#merge_field_id[value="' + update_field_id + '"]')
            .parent()
            .find('#field_meta');
          let deleted_items =
            update_field_meta &&
            $(update_field_meta).length > 0 &&
            $(update_field_meta).val()
              ? JSON.parse($(update_field_meta).val())
              : [];

          // Iterate source tags, updating accordingly
          $.each(source_typeahead.items, function (idx, source) {
            if (is_selected) {
              // Add, if not already present

              if (
                !window.lodash.includes(update_typeahead.items, source['ID'])
              ) {
                update_typeahead.addMultiselectItemLayout(source);

                // Keep deleted items in sync
                let found = window.lodash.find(deleted_items, function (item) {
                  return (
                    new String(item['ID']).valueOf() ==
                    new String(source['ID'].valueOf())
                  );
                });

                if (found) {
                  window.lodash.remove(deleted_items, found);
                  $(update_field_meta).val(JSON.stringify(deleted_items));
                }
              }
            } else {
              // Remove, if present and not still selected anywhere else!

              if (
                !is_field_value_still_selected(
                  update_field_id,
                  field_type,
                  source,
                )
              ) {
                // Remove item object
                window.lodash.remove(update_typeahead.items, function (value) {
                  return value['name'] === source['name'];
                });

                // Remove compared item string
                window.lodash.remove(
                  update_typeahead.comparedItems,
                  function (value) {
                    return (
                      new String(value).valueOf() ==
                      new String(source['ID'].valueOf())
                    );
                  },
                );

                // Remove matching label container
                $(update_typeahead.label.container)
                  .find('.typeahead__label')
                  .each(function (idx, label) {
                    if (
                      $(label)
                        .find('span')
                        .not('span.typeahead__cancel-button')
                        .text() === source['name']
                    ) {
                      $(label).remove();
                    }
                  });

                // Keep deleted items in sync
                let found = window.lodash.find(deleted_items, function (item) {
                  return (
                    new String(item['ID']).valueOf() ==
                    new String(source['ID'].valueOf())
                  );
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
        let source_user_select_typeahead_field_input =
          '.js-typeahead-' + field_id;
        let update_user_select_typeahead_field_input =
          '.js-typeahead-' + update_field_id;
        let source_typeahead_user_select =
          window.Typeahead[source_user_select_typeahead_field_input];
        let update_typeahead_user_select =
          window.Typeahead[update_user_select_typeahead_field_input];

        // Update values accordingly
        if (source_typeahead_user_select && update_typeahead_user_select) {
          if (is_selected) {
            $(update_user_select_typeahead_field_input).val(
              $(source_user_select_typeahead_field_input).val(),
            );
            update_typeahead_user_select.item =
              source_typeahead_user_select.item;
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
        post_id: $('#main_archiving_current_post_id').val(),
        fields_div: 'main_archiving_fields_div',
      },
      {
        post_id: $('#main_primary_current_post_id').val(),
        fields_div: 'main_primary_fields_div',
      },
    ];

    // Iterate over both ids, in search of matching field values
    $.each(merging_objs, function (idx, merge_obj) {
      // Obtain handle on td field select input widget
      let td_field_select_input = $('#' + merge_obj['fields_div']).find(
        '.td-field-select input[data-merge_field_id="' +
          merge_obj['post_id'] +
          '_' +
          field_id +
          '"]',
      );

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
            $(td_field_select_input)
              .parent()
              .parent()
              .find('button.selected-select-button')
              .each(function (idx, button) {
                if (
                  button &&
                  $(button).length > 0 &&
                  field_value &&
                  $(button).attr('id').valueOf() ==
                    $(field_value).attr('id').valueOf()
                ) {
                  still_selected = true;
                }
              });

            break;
          }

          case 'tags':
          case 'location': {
            let typeahead =
              window.Typeahead[
                '.js-typeahead-' + merge_obj['post_id'] + '_' + field_id
              ];
            if (typeahead) {
              let matched_value = window.lodash.find(
                typeahead.items,
                function (item) {
                  return (
                    new String(item['name']).valueOf() ==
                    new String(field_value['name'].valueOf())
                  );
                },
              );

              if (matched_value && $(matched_value).length > 0) {
                still_selected = true;
              }
            }

            break;
          }

          case 'communication_channel':
          case 'location_meta': {
            let matched_value = $(td_field_select_input)
              .parent()
              .parent()
              .find('input.input-group-field[value="' + field_value + '"]');
            if (matched_value && $(matched_value).length > 0) {
              still_selected = true;
            }

            break;
          }

          case 'user_select': {
            break;
          }

          case 'connection': {
            let connection_typeahead =
              window.Typeahead[
                '.js-typeahead-' + merge_obj['post_id'] + '_' + field_id
              ];
            if (connection_typeahead) {
              let matched_value = window.lodash.find(
                connection_typeahead.items,
                function (item) {
                  return (
                    new String(item['ID']).valueOf() ==
                    new String(field_value['ID'].valueOf())
                  );
                },
              );

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

  function is_field_value_already_in_primary(
    field_id,
    field_type,
    field_value,
  ) {
    let is_already_in_primary = false;

    // First, obtain handle onto current primary post
    let primary_post = fetch_post_by_merge_type(true)['record'];

    // Ensure primary post contains field in question
    if (primary_post && primary_post[field_id]) {
      // Parse value accordingly, based on field type
      switch (field_type) {
        case 'link':
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

  function is_link_field_value_already_in_primary(
    field_id,
    link_type,
    link_meta_id,
    link_value,
    check_by_value,
  ) {
    let is_already_in_primary = false;

    // First, obtain handle onto current primary post
    let primary_post = fetch_post_by_merge_type(true)['record'];

    // Ensure primary post contains field in question
    if (primary_post && primary_post[field_id]) {
      // Parse value accordingly, based on link type, meta_id & value.
      $.each(primary_post[field_id], function (idx, value) {
        if (value['type'] && value['meta_id'] && value['value']) {
          if (value['type'] === link_type) {
            // Accommodate both checks by meta_id (in the event of value changes) and existing unchanged values.
            if (
              !check_by_value &&
              String(value['meta_id']) === String(link_meta_id)
            ) {
              is_already_in_primary = true;
            } else if (
              check_by_value &&
              String(value['value']) === String(link_value)
            ) {
              is_already_in_primary = true;
            }
          }
        }
      });
    }

    return is_already_in_primary;
  }

  function handle_merge() {
    // Disable submit button
    $('.submit-merge').toggleClass('loading').attr('disabled', true);

    // Start packaging updated fields
    let values = {};
    $('#main_updated_fields_div')
      .find('.td-field-input')
      .each(function (idx, td) {
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
            values[post_field_id] = $(td)
              .find('#' + field_id)
              .val();
            break;

          case 'date':
            values[post_field_id] = $(field_meta).val();
            break;

          case 'multi_select': {
            let options = [];
            $(td)
              .find('button')
              .each(function () {
                options.push({
                  value: $(this).attr('id'),
                  delete: !$(this).hasClass('selected-select-button'),
                });
              });

            if (options) {
              values[post_field_id] = {
                values: options,
              };
            }
            break;
          }

          case 'tags':
          case 'connection': {
            let typeahead = window.Typeahead['.js-typeahead-' + field_id];
            if (typeahead) {
              // Determine values to be processed
              let key = field_type === 'tags' ? 'name' : 'ID';
              let items = typeahead.items;
              let deletions = field_meta.val()
                ? JSON.parse(field_meta.val())
                : [];

              // Package values and any deletions
              let entries = [];
              $.each(items, function (idx, item) {
                entries.push({
                  value: item[key],
                });
              });
              $.each(deletions, function (idx, item) {
                entries.push({
                  value: item[key],
                  delete: true,
                });
              });

              // If present, capture entries
              if (entries) {
                values[post_field_id] = {
                  values: entries,
                };
              }
            }
            break;
          }

          case 'location_meta':
          case 'location': {
            if (window.merge_post_details?.post_settings?.fields[field_id]) {
              const field_settings =
                window.merge_post_details.post_settings.fields[field_id];
              if (
                field_type === 'location' &&
                field_settings?.mode === 'normal'
              ) {
                let typeahead = window.Typeahead['.js-typeahead-' + field_id];
                if (typeahead) {
                  // Determine values to be processed
                  let key = 'ID';
                  let items = typeahead.items;
                  let deletions = field_meta.val()
                    ? JSON.parse(field_meta.val())
                    : [];

                  // Package values and any deletions
                  let entries = [];
                  $.each(items, function (idx, item) {
                    entries.push({
                      value: item[key],
                    });
                  });
                  $.each(deletions, function (idx, item) {
                    entries.push({
                      value: item[key],
                      delete: true,
                    });
                  });

                  // If present, capture entries
                  if (entries) {
                    values[post_field_id] = {
                      values: entries,
                    };
                  }
                }
              } else if (
                (field_type === 'location' &&
                  field_settings?.mode === 'geolocation') ||
                field_type === 'location_meta'
              ) {
                // Determine values to be processed
                let location_meta_entries = [];
                let location_meta_deletions = field_meta.val()
                  ? JSON.parse(field_meta.val())
                  : [];

                // Package values and any deletions
                $(td)
                  .find('input.input-group-field')
                  .each(function (idx, input) {
                    if ($(input).val()) {
                      location_meta_entries.push({
                        value: $(input).val(),
                      });
                    }
                  });

                if (
                  window.selected_location_grid_meta !== undefined &&
                  window.selected_location_grid_meta[field_id]
                ) {
                  location_meta_entries.push({
                    value: window.selected_location_grid_meta[field_id],
                  });
                }

                $.each(location_meta_deletions, function (idx, id) {
                  location_meta_entries.push({
                    grid_meta_id: id,
                    delete: true,
                  });
                });

                // If present, capture entries
                if (location_meta_entries) {
                  values[post_field_id] = {
                    values: location_meta_entries,
                  };
                }
              }
            }
            break;
          }

          case 'link': {
            // Determine values to be processed
            let link_entries = [];
            let link_deletions = field_meta.val()
              ? JSON.parse(field_meta.val())
              : [];

            // Package values and any deletions
            $(td)
              .find('.input-group input.link-input')
              .each(function (idx, input) {
                let link_type = $(input).data('type');
                let link_meta_id = $(input).data('meta-id');
                let link_val = $(input).val();

                let has_value = is_link_field_value_already_in_primary(
                  post_field_id,
                  link_type,
                  link_meta_id,
                  link_val,
                  true,
                );
                let matched_meta_id = is_link_field_value_already_in_primary(
                  post_field_id,
                  link_type,
                  link_meta_id,
                  link_val,
                  false,
                );

                if (link_val && !has_value) {
                  link_entries.push({
                    value: link_val,
                    type: link_type,
                    meta_id: matched_meta_id ? link_meta_id : '',
                  });
                }
              });

            $.each(link_deletions, function (idx, deleted_meta_id) {
              link_entries.push({
                meta_id: deleted_meta_id,
                delete: true,
              });
            });

            // If present, capture entries
            if (link_entries) {
              values[post_field_id] = {
                values: link_entries,
              };
            }
            break;
          }

          case 'communication_channel': {
            // Determine values to be processed
            let comm_entries = [];
            let comm_deletions = field_meta.val()
              ? JSON.parse(field_meta.val())
              : [];

            // Package values and any deletions
            $(td)
              .find('.input-group')
              .each(function () {
                let comm_key = $(this).find('button').data('key');
                let comm_val = $(this).find('input').val();

                if (
                  comm_val &&
                  !is_field_value_already_in_primary(
                    post_field_id,
                    field_type,
                    comm_val,
                  )
                ) {
                  let comm_entry = {
                    value: comm_val,
                  };

                  if (comm_key && comm_key !== 'new') {
                    comm_entry['key'] = comm_key;
                  }

                  comm_entries.push(comm_entry);
                }
              });

            $.each(comm_deletions, function (idx, deleted) {
              comm_entries.push({
                key: deleted['key'],
                delete: true,
              });
            });

            // If present, capture entries
            if (comm_entries) {
              values[post_field_id] = comm_entries;
            }
            break;
          }

          case 'user_select': {
            let user_select_typeahead =
              window.Typeahead['.js-typeahead-' + field_id];
            if (user_select_typeahead && user_select_typeahead.item) {
              values[post_field_id] =
                'user-' + user_select_typeahead.item['ID'];
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
        post_type: post_type,
        primary_post_id: primary_post['record']['ID'],
        archiving_post_id: archiving_post['record']['ID'],
        merge_comments: $('#merge_comments').is(':checked'),
        values: values,
      };

      console.log(payload);

      // Finally dispatch payload to corresponding post type merge endpoint
      window
        .makeRequestOnPosts(
          'POST',
          window.SHAREDFUNCTIONS.escapeHTML(post_type) + '/merge',
          payload,
        )
        .then((resp) => {
          window.location = primary_post['record']['ID'];
        })
        .catch((err) => {
          console.error(err);
          $('.submit-merge').toggleClass('loading').attr('disabled', false);
          $('.merge_errors').html(
            window.SHAREDFUNCTIONS.escapeHTML(
              window.merge_post_details['translations']['error_msg'],
            ) +
              ' : ' +
              window.SHAREDFUNCTIONS.escapeHTML(
                window.lodash.get(err, 'responseJSON.message', err),
              ),
          );
        });
    } else {
      $('.submit-merge').toggleClass('loading').attr('disabled', false);
    }
  }
});
