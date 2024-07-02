jQuery(function ($) {
  window.post_type_fields =
    window.new_record_localized.post_type_settings.fields;
  let new_post = {};
  let temp_type = $('.type-options .selected').attr('id');
  if (temp_type) {
    new_post.type = temp_type;
  }
  document.querySelector('.form-fields input').focus();
  $('.type-option').on('click', function () {
    let type = $(this).attr('id');
    $('.type-option.selected').removeClass('selected');
    $(this).addClass('selected');
    $(`#${type} input`).prop('checked', true);
    $('.form-fields').show();
    $(`.form-field`).hide();
    $(`.form-field.all`).show();
    $(`.form-field.${type}`).show();
    $('#show-shield-banner').show();
    $('#show-hidden-fields').show();
    $('#hide-hidden-fields').hide();
    new_post.type = type;
    /* Focus first field in form */
    document.querySelector('.form-fields input').focus();

    //change type field in fields list too
    const type_field = $('.form-field #type');
    if (type_field.length) {
      type_field.val(type);
    }
  });
  $('#show-hidden-fields').on('click', function () {
    $('.form-field').show();
    $('#show-hidden-fields').hide();
    $('#hide-hidden-fields').show();
  });
  $('#hide-hidden-fields').on('click', function () {
    $('.form-field').hide();
    $(`.form-field.all`).show();
    $(`.form-field.${new_post.type}`).show();
    $('#hide-hidden-fields').hide();
    $('#show-hidden-fields').show();
  });

  $('.js-create-post-button').removeAttr('disabled');

  // Clicking the plus sign next to the field label
  $('button.add-button').on('click', (e) => {
    const listClass = $(e.currentTarget).data('list-class');
    const $list = $(`#edit-${listClass}`);
    const field = $(e.currentTarget).data('list-class');
    const fieldName =
      window.new_record_localized.post_type_settings.fields[field].name;
    const fieldType = $(e.currentTarget).data('field-type');
    var elementIndex = $(`input[data-${field}-index]`).length;

    if (fieldType === 'link') {
      const addLinkForm = $(`.add-link-${field}`);
      addLinkForm.show();

      $(`#cancel-link-button-${field}`).on('click', () => addLinkForm.hide());
    } else {
      $list.append(`<li style="display: flex">
                <input type="text" class="dt-communication-channel" data-field="${window.SHAREDFUNCTIONS.escapeHTML(listClass)}" data-${field}-index="${window.SHAREDFUNCTIONS.escapeHTML(elementIndex)}"/>
                <button class="button clear delete-button new-${window.SHAREDFUNCTIONS.escapeHTML(listClass)}" type="button" data-${field}-index="${elementIndex}">
                    <img src="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.template_dir)}/dt-assets/images/invalid.svg">
                </button>
                <span class="loading-spinner" data-${field}-index="${window.SHAREDFUNCTIONS.escapeHTML(elementIndex)}" style="margin: 0.5rem;"></span>
              </li>
              <div class="communication-channel-error" data-${field}-index="${window.SHAREDFUNCTIONS.escapeHTML(elementIndex)}" style="display: none;">
                ${window.new_record_localized.translations.value_already_exists.replace('%s', fieldName)}:
                <span class="duplicate-ids" data-${field}-index="${window.SHAREDFUNCTIONS.escapeHTML(elementIndex)}" style="color: #3f729b;"></span>
              </div>`);
    }
  });

  /* breadcrumb: new-field-type Add anything that the field type needs for creating a new record */

  $('.add-link-dropdown[data-only-one-option]').on(
    'click',
    window.SHAREDFUNCTIONS.addLink,
  );

  $('.add-link__option').on('click', (event) => {
    window.SHAREDFUNCTIONS.addLink(event);
    $(event.target).parent().hide();
    setTimeout(() => {
      event.target.parentElement.removeAttribute('style');
    }, 100);
  });

  $(document).on('click', '.link-delete-button', function () {
    $(this).closest('.link-section').remove();
  });

  $('.js-create-post').on('click', '.delete-button', function () {
    var field_type = $(this).prev('input').data('field');
    var element_index = $(this).data(`${field_type}-index`);
    console.log(field_type, element_index);
    $(
      `.communication-channel-error[data-${field_type}-index="${element_index}"]`,
    ).remove();
    $(this).parent().remove();
  });

  /* breadcrumb: new-field-type Add the new link type data to the new_post array */
  $('.js-create-post').on('submit', function () {
    $('.js-create-post-button').attr('disabled', true).addClass('loading');
    new_post.title = $('.js-create-post input[name=title]').val();
    $('.select-field').each((index, entry) => {
      if ($(entry).val()) {
        new_post[$(entry).attr('id')] = $(entry).val();
      }
    });
    $('.text-input').each((index, entry) => {
      if ($(entry).val()) {
        new_post[$(entry).attr('id')] = $(entry).val();
      }
    });
    $('.link-input').each((index, entry) => {
      let fieldKey = $(entry).data('field-key');
      let type = $(entry).data('type');
      if ($(entry).val()) {
        if (!Object.prototype.hasOwnProperty.call(new_post, fieldKey)) {
          new_post[fieldKey] = { values: [] };
        }
        new_post[fieldKey].values.push({
          value: $(entry).val(),
          type: type,
        });
      }
    });
    $('.dt_textarea').each((index, entry) => {
      if ($(entry).val()) {
        new_post[$(entry).attr('id')] = $(entry).val();
      }
    });
    $('.dt-communication-channel').each((index, entry) => {
      let val = $(entry).val();
      if (val.length > 0) {
        let channel = $(entry).data('field');
        if (!new_post[channel]) {
          new_post[channel] = [];
        }
        new_post[channel].push({
          value: $(entry).val(),
        });
      }
    });
    $('.selected-select-button').each((index, entry) => {
      let optionKey = $(entry).attr('id');
      let fieldKey = $(entry).data('field-key');
      if (!new_post[fieldKey]) {
        new_post[fieldKey] = { values: [] };
      }
      new_post[fieldKey].values.push({
        value: optionKey,
      });
    });
    if (typeof window.selected_location_grid_meta !== 'undefined') {
      for (const [field_id, location_data] of Object.entries(
        window.selected_location_grid_meta,
      )) {
        new_post[field_id] = location_data;
      }
    }

    window.API.create_post(window.new_record_localized.post_type, new_post)
      .promise()
      .then(function (data) {
        window.location = data.permalink;
      })
      .catch(function (error) {
        const message = error.responseJSON?.message || error.responseText;
        $('.js-create-post-button')
          .removeClass('loading')
          .addClass('alert')
          .attr('disabled', false);
        $('.error-text').html(message);
      });
    return false;
  });

  let field_settings = window.new_record_localized.post_type_settings.fields;

  function date_picker_init(is_bulk = false, bulk_id = 0) {
    // Determine field class name to be used.
    let field_class = !is_bulk
      ? `.dt_date_picker`
      : `.dt_date_picker-${bulk_id}`;

    // Assign on click listener.
    $(field_class).datepicker({
      constrainInput: false,
      dateFormat: 'yy-mm-dd',
      onClose: function (date) {
        date = window.SHAREDFUNCTIONS.convertArabicToEnglishNumbers(date);
        if (!$(this).val()) {
          date = ' '; //null;
        }
        let id = $(this).attr('id');
        new_post[id] = date;
        if (this.value) {
          this.value = window.SHAREDFUNCTIONS.formatDate(
            window.moment.utc(date).unix(),
          );
        }

        // If bulk related, capture epoch
        if (is_bulk) {
          $(this).data('selected-date-epoch', window.moment.utc(date).unix());
        }
      },
      changeMonth: true,
      changeYear: true,
      yearRange: '1900:2050',
    });
  }

  function button_multi_select_init(is_bulk = false, bulk_id = 0) {
    // Determine field class name to be used.
    let field_class = !is_bulk
      ? `.dt_multi_select`
      : `.dt_multi_select-${bulk_id}`;

    // Assign on click listener.
    $(field_class).on('click', function () {
      let field = $(this);

      if (field.hasClass('selected-select-button')) {
        field.addClass('empty-select-button');
        field.removeClass('selected-select-button');
      } else {
        field.removeClass('empty-select-button');
        field.addClass('selected-select-button');
      }
    });
  }

  function typeahead_general_init(is_bulk = false, bulk_id = 0) {
    $('.typeahead__query input').each((key, el) => {
      let field_key = $(el).data('field');
      let post_type = $(el).data('post_type');
      let field_type = $(el).data('field_type');
      window.typeaheadTotals = {};

      // Determine field class name to be used.
      let field_class = !is_bulk
        ? `.js-typeahead-${field_key}`
        : `.js-typeahead-${field_key}-${bulk_id}`;

      if (!window.Typeahead[field_class]) {
        if (field_type === 'connection') {
          $.typeahead({
            input: field_class,
            minLength: 0,
            accent: true,
            searchOnFocus: true,
            maxItem: 20,
            template: window.TYPEAHEADS.contactListRowTemplate,
            source: window.TYPEAHEADS.typeaheadPostsSource(
              post_type,
              field_key,
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
                  if (!is_bulk) {
                    window.lodash.pullAllBy(
                      new_post[field_key].values,
                      [{ value: item.ID }],
                      'value',
                    );
                  }
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
                $(`#${field_key}-result-container`).html(text);
              },
              onHideLayout: function () {
                $(`#${field_key}-result-container`).html('');
              },
              onClick: function (node, a, item, event) {
                if (!is_bulk) {
                  if (!new_post[field_key]) {
                    new_post[field_key] = { values: [] };
                  }
                  new_post[field_key].values.push({ value: item.ID });
                }
                //get list from opening again
                this.addMultiselectItemLayout(item);
                event.preventDefault();
                this.hideLayout();
                this.resetInput();
              },
            },
          });
        } else if (field_type === 'location') {
          if (
            window.post_type_fields[field_key] &&
            window.post_type_fields[field_key]?.mode === 'normal'
          ) {
            $.typeahead({
              input: field_class,
              minLength: 0,
              accent: true,
              searchOnFocus: true,
              maxItem: 20,
              dropdownFilter: [
                {
                  key: 'group',
                  value: 'focus',
                  template: window.SHAREDFUNCTIONS.escapeHTML(
                    window.wpApiShare.translations.regions_of_focus,
                  ),
                  all: window.SHAREDFUNCTIONS.escapeHTML(
                    window.wpApiShare.translations.all_locations,
                  ),
                },
              ],
              source: {
                focus: {
                  display: 'name',
                  ajax: {
                    url:
                      window.wpApiShare.root +
                      'dt/v1/mapping_module/search_location_grid_by_name',
                    data: {
                      s: '{{query}}',
                      filter: function () {
                        return window.lodash.get(
                          window.Typeahead[field_class].filters.dropdown,
                          'value',
                          'all',
                        );
                      },
                    },
                    beforeSend: function (xhr) {
                      xhr.setRequestHeader(
                        'X-WP-Nonce',
                        window.wpApiShare.nonce,
                      );
                    },
                    callback: {
                      done: function (data) {
                        if (typeof window.typeaheadTotals !== 'undefined') {
                          window.typeaheadTotals.field = data.total;
                        }
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
                data: [],
                callback: {
                  onCancel: function (node, item) {
                    if (!is_bulk) {
                      window.lodash.pullAllBy(
                        new_post[field_key].values,
                        [{ value: item.ID }],
                        'value',
                      );
                    }
                  },
                },
              },
              callback: {
                onClick: function (node, a, item, event) {
                  if (!is_bulk) {
                    if (!new_post[field_key]) {
                      new_post[field_key] = { values: [] };
                    }
                    new_post[field_key].values.push({ value: item.ID });
                  }
                  //get list from opening again
                  this.addMultiselectItemLayout(item);
                  event.preventDefault();
                  this.hideLayout();
                  this.resetInput();
                },
                onReady() {
                  this.filters.dropdown = {
                    key: 'group',
                    value: 'focus',
                    template: window.SHAREDFUNCTIONS.escapeHTML(
                      window.wpApiShare.translations.regions_of_focus,
                    ),
                  };
                  this.container
                    .removeClass('filter')
                    .find('.' + this.options.selector.filterButton)
                    .html(
                      window.SHAREDFUNCTIONS.escapeHTML(
                        window.wpApiShare.translations.regions_of_focus,
                      ),
                    );
                },
                onResult: function (node, query, result, resultCount) {
                  resultCount = window.typeaheadTotals.location_grid;
                  let text = window.TYPEAHEADS.typeaheadHelpText(
                    resultCount,
                    query,
                    result,
                  );
                  $('#location_grid-result-container').html(text);
                },
                onHideLayout: function () {
                  $('#location_grid-result-container').html('');
                },
              },
            });
          }
        } else if (field_type === 'user_select') {
          $.typeahead({
            input: field_class,
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
              onClick: function (node, a, item) {
                if (!is_bulk) {
                  new_post[field_key] = item.ID;
                }
              },
              onResult: function (node, query, result, resultCount) {
                let text = window.TYPEAHEADS.typeaheadHelpText(
                  resultCount,
                  query,
                  result,
                );
                $(`#${field_key}-result-container`).html(text);
              },
              onHideLayout: function () {
                $(`.${field_key}-result-container`).html('');
              },
            },
          });
          let user_input = $(`.js-typeahead-${field_key}`);
          $(`.search_${field_key}`).on('click', function () {
            user_input.val('');
            user_input.trigger('input.typeahead');
            user_input.focus();
          });
        }
      }
    });
  }

  function typeahead_multi_select_init(is_bulk = false, bulk_id = 0) {
    //multi-select typeaheads
    for (let input of $('.multi_select .typeahead__query input')) {
      let field = $(input).data('field');
      let typeahead_name = !is_bulk
        ? `.js-typeahead-${field}`
        : `.js-typeahead-${field}-${bulk_id}`;
      const post_type = window.new_record_localized.post_type;

      if (window.Typeahead[typeahead_name]) {
        return;
      }

      let source_data = { data: [] };
      let field_options = window.lodash.get(
        field_settings,
        `${field}.default`,
        {},
      );
      if (Object.keys(field_options).length > 0) {
        window.lodash.forOwn(field_options, (val, key) => {
          if (!val.deleted) {
            source_data.data.push({
              key: key,
              name: key,
              value: val.label || key,
            });
          }
        });
      } else {
        source_data = {
          [field]: {
            display: ['value'],
            ajax: {
              url:
                window.wpApiShare.root +
                `dt-posts/v2/${post_type}/multi-select-values`,
              data: {
                s: '{{query}}',
                field,
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  return (data || []).map((tag) => {
                    let label = window.lodash.get(
                      field_options,
                      tag + '.label',
                      tag,
                    );
                    return { value: label, key: tag };
                  });
                },
              },
            },
          },
        };
      }
      $.typeahead({
        input: typeahead_name,
        minLength: 0,
        maxItem: 20,
        searchOnFocus: true,
        template: function (query, item) {
          return `<span>${window.SHAREDFUNCTIONS.escapeHTML(item.value)}</span>`;
        },
        source: source_data,
        display: 'value',
        templateValue: '{{value}}',
        dynamic: true,
        multiselect: {
          matchOn: ['key'],
          data: [],
          callback: {
            onCancel: function (node, item, event) {
              if (!is_bulk) {
                window.lodash.pullAllBy(
                  new_post[field].values,
                  [{ value: item.key }],
                  'value',
                );
              }
            },
          },
        },
        callback: {
          onClick: function (node, a, item, event) {
            if (!is_bulk) {
              if (!new_post[field]) {
                new_post[field] = { values: [] };
              }
              new_post[field].values.push({ value: item.key });
            }
            this.addMultiselectItemLayout(item);
            event.preventDefault();
            this.hideLayout();
            this.resetInput();
          },
          onResult: function (node, query, result, resultCount) {
            let text = window.TYPEAHEADS.typeaheadHelpText(
              resultCount,
              query,
              result,
            );
            //adding the result text moves the input. timeout keeps the dropdown from closing as the user clicks and cursor moves away from the input.
            setTimeout(() => {
              $(`#${field}-result-container`).html(text);
            }, 200);
          },
          onHideLayout: function () {
            $(`#${field}-result-container`).html('');
          },
        },
      });
    }
  }

  function typeahead_tags_init(is_bulk = false, bulk_id = 0) {
    /**
     * Tags
     */
    $('.tags .typeahead__query input').each((key, input) => {
      let field = $(input).data('field') || 'tags';
      let typeahead_name = !is_bulk
        ? `.js-typeahead-${field}`
        : `.js-typeahead-${field}-${bulk_id}`;

      const post_type = window.new_record_localized.post_type;
      $.typeahead({
        input: typeahead_name,
        minLength: 0,
        maxItem: 20,
        searchOnFocus: true,
        source: {
          tags: {
            display: ['value'],
            ajax: {
              url:
                window.wpApiShare.root +
                `dt-posts/v2/${post_type}/multi-select-values`,
              data: {
                s: '{{query}}',
                field: field,
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  return (data || []).map((tag) => {
                    return { value: tag };
                  });
                },
              },
            },
          },
        },
        display: 'value',
        templateValue: '{{value}}',
        emptyTemplate: function (query) {
          const { addNewTagText, tagExistsText } = this.node[0].dataset;
          if (this.comparedItems.includes(query)) {
            return tagExistsText.replace('%s', query);
          }
          const liItem = $('<li>');
          const button = $('<button>', {
            class: 'button primary',
            text: addNewTagText.replace('%s', query),
          });
          const tag = this.query;
          const typeahead = this;
          button.on('click', function (event) {
            if (!is_bulk) {
              if (!new_post[field]) {
                new_post[field] = { values: [] };
              }
              new_post[field].values.push({ value: tag });
            }
            typeahead.addMultiselectItemLayout({ value: tag });
            event.preventDefault();
            typeahead.hideLayout();
            typeahead.resetInput();
          });
          liItem.append(button);
          return liItem;
        },
        dynamic: true,
        multiselect: {
          matchOn: ['value'],
          data: [],
          callback: {
            onCancel: function (node, item) {
              if (!is_bulk) {
                window.lodash.pullAllBy(
                  new_post[field].values,
                  [{ value: item.value }],
                  'value',
                );
              }
            },
          },
        },
        callback: {
          onClick: function (node, a, item, event) {
            if (!is_bulk) {
              if (!new_post[field]) {
                new_post[field] = { values: [] };
              }
              new_post[field].values.push({ value: item.value });
            }
            this.addMultiselectItemLayout(item);
            event.preventDefault();
            this.hideLayout();
            this.resetInput();
          },
          onResult: function (node, query, result, resultCount) {
            let text = window.TYPEAHEADS.typeaheadHelpText(
              resultCount,
              query,
              result,
            );
            $(`#${field}-result-container`).html(text);
          },
          onHideLayout: function () {
            $(`#${field}-result-container`).html('');
          },
        },
      });
    });
  }
  $('.js-create-post').on('click', '.create-new-tag', function () {
    let field = $(this).data('field');
    $('#create-tag-modal').data('field', field);
  });
  $('#create-tag-return').on('click', function () {
    let field = $('#create-tag-modal').data('field');
    let tag = $('#new-tag').val();
    $('#new-tag').val('');
    if (!new_post[field]) {
      new_post[field] = { values: [] };
    }
    new_post[field].values.push({ value: tag });
    window.Typeahead['.js-typeahead-' + field].addMultiselectItemLayout({
      value: tag,
    });
  });

  /**
   * ============== [ BULK RECORD ADDING FUNCTIONALITY ] ==============
   */

  /*
   * Instantiate elements accordingly, based on new record creation type:
   *  - Normal
   *  - Bulk
   */

  let bulk_record_id_counter = 1;
  let is_normal_new_record = !$('#form_fields_records').length ? true : false;

  $(document).ready(function () {
    if (!is_normal_new_record) {
      // If enabled, alter mapbox search location input shape, within first record
      alter_mapbox_search_location_input_shape(
        $('#form_fields_records').find('.form-fields-record').last(),
      );

      // Default to currently selected contact type.
      let selected_contact_type = $('.type-option.selected').attr('id');
      $('#' + selected_contact_type + '.type-option').trigger('click');

      // Display initial bulk records to get started.
      for (let i = 0; i < 4; i++) {
        $('#add_new_bulk_record').trigger('click');
      }
    }
  });

  if (is_normal_new_record) {
    date_picker_init();
    button_multi_select_init();
    typeahead_general_init();
    typeahead_multi_select_init();
    typeahead_tags_init();
  } else {
    let bulk_record_id = bulk_record_id_counter;

    adjust_new_button_multi_select_class_names(bulk_record_id);
    adjust_new_typeahead_general_element_class_names(bulk_record_id);
    adjust_new_typeahead_multi_select_element_class_names(bulk_record_id);
    adjust_new_typeahead_tags_element_class_names(bulk_record_id);
    adjust_new_date_picker_class_names(bulk_record_id);

    date_picker_init(true, bulk_record_id);
    button_multi_select_init(true, bulk_record_id);
    typeahead_general_init(true, bulk_record_id);
    typeahead_multi_select_init(true, bulk_record_id);
    typeahead_tags_init(true, bulk_record_id);
  }

  /*
   * Respond to new bulk record addition requests.
   */

  $('#add_new_bulk_record').on('click', function () {
    let fields_html = window.new_record_localized.bulk_record_fields;

    if (fields_html) {
      let new_records_count = ++bulk_record_id_counter;
      let html = `<div class="form-fields-record form-fields-record-subsequent">
        <input type="hidden" id="bulk_record_id" value="${new_records_count}">
        <div class="record-divider"><span>${generate_record_removal_button_html(new_records_count)}</span></div>
        <span class="landscape-record-removal-button">${generate_record_removal_button_html(new_records_count)}</span>
        ${fields_html}
      </div>`;
      let updated_records = $('#form_fields_records').append(html);

      // Adjust relevant class names for recently added record elements.
      adjust_new_button_multi_select_class_names(new_records_count);
      adjust_new_typeahead_general_element_class_names(new_records_count);
      adjust_new_typeahead_multi_select_element_class_names(new_records_count);
      adjust_new_typeahead_tags_element_class_names(new_records_count);
      adjust_new_date_picker_class_names(new_records_count);

      // Initialise newly added element specific functionality; e.g. typeaheads.
      date_picker_init(true, new_records_count);
      button_multi_select_init(true, new_records_count);
      typeahead_general_init(true, new_records_count);
      typeahead_multi_select_init(true, new_records_count);
      typeahead_tags_init(true, new_records_count);

      // Apply and initialise copy controls
      let new_record = $('#form_fields_records')
        .find('.form-fields-record')
        .last();
      apply_field_value_copy_controls_by_record(new_record);
      field_value_copy_controls_button_init();

      // If enabled, alter mapbox search location input shape
      alter_mapbox_search_location_input_shape(new_record);

      // Ensure new record fields adhere to existing displayed fields shape
      refresh_displayed_fields_by_record(
        list_currently_displayed_fields(),
        new_record,
      );
    }
  });

  function adjust_new_button_multi_select_class_names(bulk_id) {
    $('#form_fields_records')
      .find('.form-fields-record')
      .last()
      .find('.dt_multi_select')
      .each((key, el) => {
        adjust_new_button_multi_select_class_name(bulk_id, el);
      });
  }

  function adjust_new_button_multi_select_class_name(bulk_id, element) {
    let old_field_class = `dt_multi_select`;

    if ($(element).hasClass(old_field_class)) {
      let new_field_class = `dt_multi_select-${bulk_id}`;
      $(element).removeClass(old_field_class).addClass(new_field_class);
    }
  }

  function adjust_new_typeahead_general_element_class_names(bulk_id) {
    $('#form_fields_records')
      .find('.form-fields-record')
      .last()
      .find('.typeahead__query input')
      .each((key, el) => {
        adjust_new_typeahead_class_name(bulk_id, el);
      });
  }

  function adjust_new_typeahead_multi_select_element_class_names(bulk_id) {
    $('#form_fields_records')
      .find('.form-fields-record')
      .last()
      .find('.multi_select .typeahead__query input')
      .each((key, el) => {
        adjust_new_typeahead_class_name(bulk_id, el);
      });
  }

  function adjust_new_typeahead_tags_element_class_names(bulk_id) {
    $('#form_fields_records')
      .find('.form-fields-record')
      .last()
      .find('.tags .typeahead__query input')
      .each((key, el) => {
        adjust_new_typeahead_class_name(bulk_id, el);
      });
  }

  function adjust_new_typeahead_class_name(bulk_id, element) {
    let field_key = $(element).data('field');
    let old_field_class = `js-typeahead-${field_key}`;

    if ($(element).hasClass(old_field_class)) {
      let new_field_class = `js-typeahead-${field_key}-${bulk_id}`;
      $(element).removeClass(old_field_class).addClass(new_field_class);
    }
  }

  function adjust_new_date_picker_class_names(bulk_id) {
    $('#form_fields_records')
      .find('.form-fields-record')
      .last()
      .find('.dt_date_picker')
      .each((key, el) => {
        adjust_new_date_picker_class_name(bulk_id, el);
      });
  }

  function adjust_new_date_picker_class_name(bulk_id, element) {
    let old_field_class = `dt_date_picker`;

    if ($(element).hasClass(old_field_class)) {
      let new_field_class = `dt_date_picker-${bulk_id}`;
      let new_field_id = $(element).attr('id') + '-' + bulk_id;

      $(element).data('orig-field-id', $(element).attr('id')); // Keep a record of original id
      $(element).removeClass(old_field_class).addClass(new_field_class);
      $(element).prop('id', new_field_id);
    }
  }

  /*
   * Alter the shape of mapbox-search location input fields.
   */

  function alter_mapbox_search_location_input_shape(record, field_id = '') {
    let mapbox_autocomplete = $(record).find(
      `#${field_id}_mapbox-autocomplete`,
    );
    let mapbox_search = $(record).find(`#${field_id}_mapbox-search`);
    let mapbox_autocomplete_list = $(record).find(
      `#${field_id}_mapbox-autocomplete-list`,
    );
    let mapbox_spinner_button = $(record).find(
      `#${field_id}_mapbox-spinner-button`,
    );
    let mapbox_clear_autocomplete = $(record).find(
      `#${field_id}_mapbox-clear-autocomplete`,
    );
    if (
      mapbox_autocomplete &&
      mapbox_search &&
      mapbox_autocomplete_list &&
      mapbox_spinner_button &&
      mapbox_clear_autocomplete
    ) {
      // Adjust attributes in order to force shape change.
      $(mapbox_autocomplete).prop('id', 'mapbox-autocomplete-altered');
      $(mapbox_autocomplete_list).prop(
        'id',
        'mapbox-autocomplete-list-altered',
      );
      $(mapbox_spinner_button).prop('id', 'mapbox-spinner-button-altered');
      $(mapbox_clear_autocomplete).prop(
        'id',
        'mapbox-clear-autocomplete-altered',
      );
      $(mapbox_search).prop('id', 'mapbox-search-altered');
      $(mapbox_search).prop('type', 'button');
      $(mapbox_search).prop(
        'value',
        window.SHAREDFUNCTIONS.escapeHTML(
          window.new_record_localized.bulk_mapbox_placeholder_txt,
        ),
      );
      $(mapbox_search).addClass('button');
      $(mapbox_search).addClass('mapbox-altered-input');
      $(mapbox_search).data('field', field_id);

      // Capture record id for processing further downstream.
      $(mapbox_search).data(
        'bulk_record_id',
        $(record).find('#bulk_record_id').val(),
      );
    }
  }

  /*
   * Respond to altered mapbox-search button clicks & modal events.
   */

  $(document).on('click', '.mapbox-altered-input', function (evt) {
    evt.preventDefault();

    // Reset mapbox values.
    reset_altered_mapbox_search_field_values();

    // Persist bulk record id and display modal.
    $('#altered_mapbox_search_modal').data(
      'bulk_record_id',
      $(evt.currentTarget).data('bulk_record_id'),
    );
    $('#altered_mapbox_search_modal').foundation('open');
  });

  $(document).on('open.zf.reveal', '[data-reveal]', function (evt) {
    // Switch over to standard mapbox activation workflow, with autosubmit disabled!
    window.write_input_widget();
  });

  $(document).on('closed.zf.reveal', '[data-reveal]', function (evt) {});

  $('#altered_mapbox_search_modal_but_cancel').on('click', function () {
    reset_altered_mapbox_search_field_values();
    $('#altered_mapbox_search_modal').foundation('close');
  });

  $('#altered_mapbox_search_modal_but_update').on('click', function () {
    let bulk_record_id = $('#altered_mapbox_search_modal').data(
      'bulk_record_id',
    );
    if (bulk_record_id && window.selected_location_grid_meta !== undefined) {
      for (const [field_id, selected_location] of Object.entries(
        window.selected_location_grid_meta,
      )) {
        // Search for corresponding record.
        $('#form_fields_records')
          .find('.form-fields-record')
          .each((key, record) => {
            if ($(record).find('#bulk_record_id').val() === bulk_record_id) {
              let mapbox_search = $(record).find('#mapbox-search-altered');

              // Update button text to selected location.
              mapbox_search.val(
                window.lodash.truncate(
                  window.SHAREDFUNCTIONS.escapeHTML(
                    selected_location['values'][0]['label'],
                  ),
                ),
              );

              // Capture selected location within data attribute.
              mapbox_search.data(
                'selected_location',
                JSON.stringify(selected_location),
              );
            }
          });
      }
    }
    $('#altered_mapbox_search_modal').foundation('close');
  });

  function reset_altered_mapbox_search_field_values() {
    $('#mapbox-search').val('');

    if (typeof window.selected_location_grid_meta !== 'undefined') {
      Object.keys(window.selected_location_grid_meta).forEach(
        (field_id) =>
          (window.selected_location_grid_meta[field_id] = undefined),
      );
    }
  }

  /*
   * Respond to bulk save requests.
   */

  $('.js-create-post-bulk-button').removeAttr('disabled');
  $('.js-create-post-bulk').on('submit', function (evt) {
    evt.preventDefault();

    // Capture parent level settings
    let type = $('.type-options .selected').attr('id');

    // Change submit button loading state
    $('.js-create-post-bulk-button').attr('disabled', true).addClass('loading');

    // Iterate over form records to be added
    let records_counter = 0;
    let records_total = $('#form_fields_records').find(
      '.form-fields-record',
    ).length;
    $('#form_fields_records')
      .find('.form-fields-record')
      .each((key, record) => {
        let bulk_record_id = $(record).find('#bulk_record_id').val();

        // Start to build new post object
        let new_post = {};
        if (type) {
          new_post.type = type;
        }

        $(record)
          .find('.select-field')
          .each((index, entry) => {
            if ($(entry).val()) {
              new_post[$(entry).attr('id')] = $(entry).val();
            }
          });

        $(record)
          .find('.text-input')
          .each((index, entry) => {
            if ($(entry).val()) {
              new_post[$(entry).attr('id')] = $(entry).val();
            }
          });

        $(record)
          .find('.dt_textarea')
          .each((index, entry) => {
            if ($(entry).val()) {
              new_post[$(entry).attr('id')] = $(entry).val();
            }
          });

        $(record)
          .find('.dt-communication-channel')
          .each((index, entry) => {
            let val = $(entry).val();
            if (val.length > 0) {
              let channel = $(entry).data('field');
              if (!new_post[channel]) {
                new_post[channel] = [];
              }
              new_post[channel].push({
                value: $(entry).val(),
              });
            }
          });

        $(record)
          .find('.selected-select-button')
          .each((index, entry) => {
            let optionKey = $(entry).attr('id');
            let fieldKey = $(entry).data('field-key');
            if (!new_post[fieldKey]) {
              new_post[fieldKey] = { values: [] };
            }
            new_post[fieldKey].values.push({
              value: optionKey,
            });
          });

        // Capture available mapbox related locations
        let mapbox_search = $(record).find('#mapbox-search-altered');
        if (mapbox_search && mapbox_search.data('selected_location')) {
          if (typeof window.selected_location_grid_meta !== 'undefined') {
            for (const [field_id, location_data] of Object.entries(
              window.selected_location_grid_meta,
            )) {
              new_post[field_id] = location_data;
            }
          }
        }

        // Package any available typeahead values
        $(record)
          .find('.typeahead__query input')
          .each((index, entry) => {
            let field_id = $(entry).data('field');
            let typeahead_selector =
              '.js-typeahead-' + field_id + '-' + bulk_record_id;
            let typeahead = window.Typeahead[typeahead_selector];

            // Ensure typeahead contains stuff...!
            if (typeahead && typeahead.items && typeahead.items.length > 0) {
              // Instantiate new values array if required.
              if (!new_post[field_id]) {
                new_post[field_id] = { values: [] };
              }

              // Populate values accordingly.
              $.each(typeahead.items, function (idx, item) {
                if (item.ID) {
                  new_post[field_id].values.push({
                    value: item.ID,
                  });
                }
              });
            }
          });

        // Package any available dates
        $(record)
          .find('.dt_date_picker-' + bulk_record_id)
          .each((index, entry) => {
            let field_id = $(entry).data('orig-field-id');
            let date_epoch = $(entry).data('selected-date-epoch');

            if (date_epoch) {
              new_post[field_id] = date_epoch;
            }
          });

        // Save new record post!
        window.API.create_post(window.new_record_localized.post_type, new_post)
          .promise()
          .then(function (data) {
            // Only redirect once all records have been processed!
            if (++records_counter >= records_total) {
              window.location =
                window.new_record_localized.bulk_save_redirect_uri;
            } else {
              $(record).slideUp('slow');
            }
          })
          .catch(function (error) {
            console.error(error);
          });
      });
  });

  /*
   * Handle cherry-picking of fields to be displayed.
   */

  let default_filter_fields = [];

  if (!is_normal_new_record) {
    // Initial displayed fields, to be captured as defualts; ready for use further down stream!
    default_filter_fields = list_currently_displayed_fields();
  }

  $('#choose_fields_to_show_in_records').on('click', function (evt) {
    evt.preventDefault();

    adjust_selected_field_filters_by_currently_displayed_record_fields();

    $('#list_fields_picker').toggle();
  });

  $('#save_fields_choices').on('click', function (evt) {
    evt.preventDefault();

    apply_field_filters();
  });

  $('#reset_fields_choices').on('click', function (evt) {
    evt.preventDefault();

    reset_field_filters();
  });

  function adjust_selected_field_filters_by_currently_displayed_record_fields() {
    let fields = list_currently_displayed_fields();
    if (fields) {
      // Select corresponding field filters
      $('#list_fields_picker input').each((index, elem) => {
        // Select checkbox accordingly
        $(elem).prop('checked', window.lodash.includes(fields, $(elem).val()));
      });
    }
  }

  function list_currently_displayed_fields() {
    // Field checks to be based on shape of first record.
    let record = $('#form_fields_records').find('.form-fields-record').first();
    let bulk_id = $(record).find('#bulk_record_id').val();
    let fields = [];

    $(record)
      .find('.select-field')
      .each((index, entry) => {
        if ($(entry).is(':visible')) {
          fields.push($(entry).attr('id'));
        }
      });

    $(record)
      .find('.text-input')
      .each((index, entry) => {
        if ($(entry).is(':visible')) {
          fields.push($(entry).attr('id'));
        }
      });

    $(record)
      .find('.dt_textarea')
      .each((index, entry) => {
        if ($(entry).is(':visible')) {
          fields.push($(entry).attr('id'));
        }
      });

    $(record)
      .find('.dt-communication-channel')
      .each((index, entry) => {
        if ($(entry).is(':visible')) {
          fields.push($(entry).data('field'));
        }
      });

    $(record)
      .find('.selected-select-button')
      .each((index, entry) => {
        if ($(entry).is(':visible')) {
          fields.push($(entry).data('field-key'));
        }
      });

    $(record)
      .find(`.dt_multi_select-${bulk_id}`)
      .each((index, entry) => {
        if ($(entry).is(':visible')) {
          fields.push($(entry).data('field-key'));
        }
      });

    $(record)
      .find('.typeahead__query input')
      .each((index, entry) => {
        if ($(entry).is(':visible')) {
          fields.push($(entry).data('field'));
        }
      });

    $(record)
      .find('.multi_select .typeahead__query input')
      .each((index, entry) => {
        if ($(entry).is(':visible')) {
          fields.push($(entry).data('field'));
        }
      });

    $(record)
      .find('.tags .typeahead__query input')
      .each((index, entry) => {
        if ($(entry).is(':visible')) {
          fields.push($(entry).data('field'));
        }
      });

    $(record)
      .find('#mapbox-search-altered')
      .each((index, entry) => {
        if ($(entry).is(':visible')) {
          fields.push($(entry).data('field'));
        }
      });

    $(record)
      .find(`.dt_date_picker-${bulk_id}`)
      .each((index, entry) => {
        if ($(entry).is(':visible')) {
          fields.push($(entry).data('orig-field-id'));
        }
      });

    return window.lodash.uniq(fields);
  }

  function refresh_displayed_fields(filter_fields) {
    $('#form_fields_records')
      .find('.form-fields-record')
      .each((key, record) => {
        refresh_displayed_fields_by_record(filter_fields, record);
      });
  }

  function refresh_displayed_fields_by_record(filter_fields, record) {
    let bulk_record_id = $(record).find('#bulk_record_id').val();

    $(record)
      .find('.select-field')
      .each((index, entry) => {
        let target_parent = $(entry).parent();
        let is_displayed = window.lodash.includes(
          filter_fields,
          $(entry).attr('id'),
        );
        $(target_parent).toggle(is_displayed);
      });

    $(record)
      .find('.text-input')
      .each((index, entry) => {
        let target_parent = $(entry).parent();
        let is_displayed = window.lodash.includes(
          filter_fields,
          $(entry).attr('id'),
        );
        $(target_parent).toggle(is_displayed);
      });

    $(record)
      .find('.dt_textarea')
      .each((index, entry) => {
        let target_parent = $(entry).parent();
        let is_displayed = window.lodash.includes(
          filter_fields,
          $(entry).attr('id'),
        );
        $(target_parent).toggle(is_displayed);
      });

    $(record)
      .find('.dt-communication-channel')
      .each((index, entry) => {
        let target_parent = $(entry).parent().parent().parent();
        let is_displayed = window.lodash.includes(
          filter_fields,
          $(entry).data('field'),
        );
        $(target_parent).toggle(is_displayed);
      });

    $(record)
      .find('.selected-select-button')
      .each((index, entry) => {
        let target_parent = $(entry).parent();
        let is_displayed = window.lodash.includes(
          filter_fields,
          $(entry).data('field-key'),
        );
        $(target_parent).toggle(is_displayed);
      });

    $(record)
      .find(`.dt_multi_select-${bulk_record_id}`)
      .each((index, entry) => {
        let target_parent = $(entry).parent().parent();
        let is_displayed = window.lodash.includes(
          filter_fields,
          $(entry).data('field-key'),
        );
        $(target_parent).toggle(is_displayed);
      });

    $(record)
      .find('.typeahead__query input')
      .each((index, entry) => {
        let target_parent = $(entry)
          .parent()
          .parent()
          .parent()
          .parent()
          .parent()
          .parent();
        let is_displayed = window.lodash.includes(
          filter_fields,
          $(entry).data('field'),
        );
        $(target_parent).toggle(is_displayed);
      });

    $(record)
      .find('.multi_select .typeahead__query input')
      .each((index, entry) => {
        let target_parent = $(entry)
          .parent()
          .parent()
          .parent()
          .parent()
          .parent()
          .parent();
        let is_displayed = window.lodash.includes(
          filter_fields,
          $(entry).data('field'),
        );
        $(target_parent).toggle(is_displayed);
      });

    $(record)
      .find('.tags .typeahead__query input')
      .each((index, entry) => {
        let target_parent = $(entry)
          .parent()
          .parent()
          .parent()
          .parent()
          .parent()
          .parent();
        let is_displayed = window.lodash.includes(
          filter_fields,
          $(entry).data('field'),
        );
        $(target_parent).toggle(is_displayed);
      });

    $(record)
      .find('#mapbox-search-altered')
      .each((index, entry) => {
        let target_parent = $(entry).parent().parent();
        let is_displayed = window.lodash.includes(
          filter_fields,
          $(entry).data('field'),
        );
        $(target_parent).toggle(is_displayed);
      });

    $(record)
      .find(`.dt_date_picker-${bulk_record_id}`)
      .each((index, entry) => {
        let target_parent = $(entry).parent().parent();
        let is_displayed = window.lodash.includes(
          filter_fields,
          $(entry).data('orig-field-id'),
        );
        $(target_parent).toggle(is_displayed);
      });
  }

  function apply_field_filters() {
    let new_selected = [];
    $('#list_fields_picker input:checked').each((index, elem) => {
      new_selected.push($(elem).val());
    });

    let fields_to_show_in_records = window.lodash.intersection(
      [],
      new_selected,
    ); // remove unchecked
    fields_to_show_in_records = window.lodash.uniq(
      window.lodash.union(fields_to_show_in_records, new_selected),
    );

    refresh_displayed_fields(fields_to_show_in_records);
    $('#list_fields_picker').toggle(false);
    adjust_selected_field_filters_by_currently_displayed_record_fields();
  }

  function reset_field_filters() {
    // Default to currently selected contact type or just revert back to plain-old-defaults!
    let selected_contact_type = $('.type-option.selected').attr('id');
    if (selected_contact_type) {
      $('#' + selected_contact_type + '.type-option').trigger('click');
    } else {
      refresh_displayed_fields(default_filter_fields);
    }

    $('#list_fields_picker').toggle(false);
    adjust_selected_field_filters_by_currently_displayed_record_fields();
  }

  /*
   * Handle field value copying across records.
   */

  if (!is_normal_new_record) {
    apply_field_value_copy_controls();
    field_value_copy_controls_button_init();
  }

  function field_value_copy_controls_button_init() {
    $('.field-value-copy-controls-button').on('click', function (evt) {
      let field_div = $(evt.currentTarget).parent().parent().parent();
      let record_id = $(evt.currentTarget).data('record-id');
      let field_class = $(evt.currentTarget).data('field-class');
      let field_id = $(evt.currentTarget).data('field-id');
      copy_field_value_across_records(
        record_id,
        field_div,
        field_class,
        field_id,
      );
    });
  }

  function apply_field_value_copy_controls() {
    $('#form_fields_records')
      .find('.form-fields-record')
      .each((key, record) => {
        apply_field_value_copy_controls_by_record(record);
      });
  }

  function apply_field_value_copy_controls_by_record(record) {
    let bulk_record_id = $(record).find('#bulk_record_id').val();

    $(record)
      .find('.form-field')
      .each((index, field_div) => {
        // Only focus on required class field types.
        if ($(field_div).find('.text-input').length !== 0) {
          apply_field_value_copy_controls_button(
            bulk_record_id,
            field_div,
            'text-input',
            $(field_div).find('.text-input').attr('id'),
          );
        } else if ($(field_div).find('.select-field').length !== 0) {
          apply_field_value_copy_controls_button(
            bulk_record_id,
            field_div,
            'select-field',
            $(field_div).find('.select-field').attr('id'),
          );
        } else if ($(field_div).find('.typeahead__query input').length !== 0) {
          apply_field_value_copy_controls_button(
            bulk_record_id,
            field_div,
            'typeahead__query input',
            $(field_div).find('.typeahead__query input').data('field'),
          );
        }
      });
  }

  function apply_field_value_copy_controls_button(
    record_id,
    field_div,
    field_class,
    field_id,
  ) {
    if ($(field_div).find('.field-value-copy-controls').length === 0) {
      let button_html =
        '<button style="margin-left: 10px;" data-field-class="' +
        field_class +
        '" data-field-id="' +
        field_id +
        '" data-record-id="' +
        record_id +
        '" class="field-value-copy-controls-button" type="button"><img src="' +
        window.SHAREDFUNCTIONS.escapeHTML(
          window.new_record_localized.bulk_copy_control_but_img_uri,
        ) +
        '"></button>';
      $(field_div)
        .find('.section-subheader')
        .append(
          '<span class="field-value-copy-controls" style="float: right; padding: 0; margin: 0;">' +
            button_html +
            '</span>',
        );
    }
  }

  function copy_field_value_across_records(
    record_id,
    field_div,
    field_class,
    field_id,
  ) {
    // First, source field value to be copied. Ensure typeaheads are handled with a little more tlc..! ;)
    let value = null;
    if (field_class === 'typeahead__query input') {
      let typeahead_selector = '.js-typeahead-' + field_id + '-' + record_id;
      let typeahead = window.Typeahead[typeahead_selector];

      value = {
        items: typeahead.items,
        items_compared: typeahead.comparedItems,
        items_label_container: typeahead.label.container,
      };
    } else {
      value = $(field_div)
        .find('.' + field_class)
        .val();
    }

    // Assuming we have a valid value, proceed with copying across records.
    if (value) {
      $('#form_fields_records')
        .find('.form-fields-record')
        .each((key, record) => {
          // Ignore primary source record!
          if (record_id != $(record).find('#bulk_record_id').val()) {
            // Copy value across to other record fields.
            $(record)
              .find('.text-input')
              .each((index, field) => {
                if ($(field).attr('id') == field_id) {
                  $(field).val(value);
                }
              });

            $(record)
              .find('.select-field')
              .each((index, field) => {
                if ($(field).attr('id') == field_id) {
                  $(field).val(value);
                }
              });

            // Again, handle typeaheads slightly differently!
            $(record)
              .find('.typeahead__query input')
              .each((index, field) => {
                if ($(field).data('field') == field_id) {
                  let typeahead_selector =
                    '.js-typeahead-' +
                    field_id +
                    '-' +
                    $(record).find('#bulk_record_id').val();
                  let typeahead = window.Typeahead[typeahead_selector];

                  // Assuming we have a valid handle, proceed.
                  if (typeahead) {
                    // Clear down existing typeahead arrays and containers
                    typeahead.items = [];
                    typeahead.comparedItems = [];
                    jQuery(typeahead.label.container).empty();

                    // Append copied items.
                    $.each(value.items, function (idx, item) {
                      typeahead.addMultiselectItemLayout(item);
                    });
                  }
                }
              });
          }
        });
    }
  }

  /*
   * Handle record removals.
   */

  $(document).on('click', '.record-removal-button', function (evt) {
    delete_record_by_id($(evt.currentTarget).data('record-id'));
  });

  function generate_record_removal_button_html(record_id) {
    let button_html = `<button data-record-id="${record_id}" class="record-removal-button" type="button" ><img src="${window.SHAREDFUNCTIONS.escapeHTML(window.new_record_localized.bulk_record_removal_but_img_uri)}"></button>`;
    return button_html;
  }

  function delete_record_by_id(record_id) {
    if (record_id) {
      let deleted_record = null;

      // Locate corresponding record.
      $('#form_fields_records')
        .find('.form-fields-record')
        .each((key, record) => {
          if ($(record).find('#bulk_record_id').val() == record_id) {
            deleted_record = record;
          }
        });

      // If found, remove identified record from list.
      if (deleted_record) {
        $(deleted_record).remove();
      }
    }
  }

  // Check for phone and email duplication
  let non_duplicable_fields = ['contact_phone', 'contact_email'];
  $.each(non_duplicable_fields, function (field_key, field_type) {
    if (window.new_record_localized.post_type_settings.fields[field_type]) {
      var field_name =
        window.new_record_localized.post_type_settings.fields[field_type].name;
      $(`input[data-field="${field_type}"]`).attr(
        `data-${field_type}-index`,
        '0',
      );
      $(`input[data-field="${field_type}"]`).after(
        `<span class="loading-spinner" data-${field_type}-index="0" style="margin: 0.5rem;"></span>`,
      );
      $(`input[data-field="${field_type}"]`).parent().after(`
        <div class="communication-channel-error" data-${field_type}-index="0" style="display: none;">
          ${window.new_record_localized.translations.value_already_exists.replace('%s', field_name)}:
          <span class="duplicate-ids" data-${field_type}-index="0" style="color: #3f729b;"></span>
          </div>`);
    }
  });

  function check_field_value_exists(field_type, element_index) {
    var email = $(`input[data-${field_type}-index="${element_index}"]`).val();
    $(`.loading-spinner[data-${field_type}-index="${element_index}"]`).attr(
      'class',
      'loading-spinner active',
    );
    if (!email) {
      $(
        `.communication-channel-error[data-${field_type}-index="${element_index}"]`,
      ).hide();
      $(`.loading-spinner[data-${field_type}-index="${element_index}"]`).attr(
        'class',
        'loading-spinner',
      );
      return;
    }
    var post_type = window.wpApiShare.post_type;
    var data = { communication_channel: `${field_type}`, field_value: email };
    jQuery
      .ajax({
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        url:
          window.wpApiShare.root +
          `dt-posts/v2/${post_type}/check_field_value_exists`,
        beforeSend: (xhr) => {
          xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
        },
      })
      .then((result) => {
        if (!$.isEmptyObject(result)) {
          var duplicate_ids_html = '';
          $.each(result, function (k, v) {
            if (k > 0) {
              duplicate_ids_html += ', ';
            }
            duplicate_ids_html += `<a href="/${post_type}/${v.post_id}" target="_blank">${window.new_record_localized.translations.contact} #${v.post_id}</a>`;
          });
          $(`.duplicate-ids[data-${field_type}-index="${element_index}"]`).html(
            duplicate_ids_html,
          );
          $(
            `.communication-channel-error[data-${field_type}-index="${element_index}"]`,
          ).show();
        } else {
          $(
            `.communication-channel-error[data-${field_type}-index="${element_index}"]`,
          ).hide();
          $(`.duplicate-ids[data-${field_type}-index="${element_index}"]`).html(
            '',
          );
        }
        $(`.loading-spinner[data-${field_type}-index="${element_index}"]`).attr(
          'class',
          'loading-spinner',
        );
      });
  }

  $('.form-fields').on('change', 'input[data-field^="contact_"]', function () {
    var post_type = $(this).data('field');
    var element_index = $(this).data(`${post_type}-index`);
    check_field_value_exists(post_type, element_index);
  });

  /**
   * ============== [ BULK RECORD ADDING FUNCTIONALITY ] ==============
   */
});
