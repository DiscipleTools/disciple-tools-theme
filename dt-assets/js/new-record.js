// Check if the web component services and ComponentService are available
if (window.DtWebComponents && window.DtWebComponents.ComponentService) {
  // Create a new instance of ComponentService
  const service = new window.DtWebComponents.ComponentService(
    window.new_record_localized.post_type,
    '',
    window.wpApiShare.nonce,
    window.wpApiShare.root,
  );
  // Initialize the ComponentService
  service.initialize();
  window.componentService = service;
}

jQuery(function ($) {
  window.post_type_fields =
    window.new_record_localized.post_type_settings.fields;

  // focus first field in the form
  document.querySelector('.form-fields [name]').focus();

  let new_post = {};
  let temp_type = $('.type-options .selected').attr('id');
  if (temp_type) {
    new_post.type = temp_type;
  }
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

    // focus first field in the form
    document.querySelector('.form-fields [name]').focus();
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

  /* field type: tags */
  let tags_field = null;
  $('dt-tags').on('dt:add-new', (e) => {
    tags_field = e.detail.field;
    $('#create-tag-modal').foundation('open');
    $('.js-create-tag input[name=title]').val(e.detail.value);
  });

  $('.js-create-tag').on('submit', (e) => {
    e.preventDefault();

    const tag = $('#new-tag').val();
    const field = document.querySelector(`#${tags_field}`);
    if (field) {
      // select the tag and the change event will handle saving it
      field._select(tag);
      field._clearSearch();
    }
  });

  // Clicking the plus sign next to the field label (field type: link)
  $('button.add-button').on('click', (e) => {
    const field = $(e.currentTarget).data('list-class');
    const fieldType = $(e.currentTarget).data('field-type');

    if (fieldType === 'link') {
      const addLinkForm = $(`.add-link-${field}`);
      addLinkForm.show();

      $(`#cancel-link-button-${field}`).on('click', () => addLinkForm.hide());
    }
  });

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

  function check_field_value_exists(el, field, value) {
    const postType = el.getAttribute('posttype');

    // show loading indicator and reset error message
    el.setAttribute('loading', true);
    el.setAttribute('error', '');

    if (value && Array.isArray(value)) {
      const apiRequests = [];
      // for each value in the communication channel,
      // send API request to check for duplicate
      for (const valueItem of value) {
        const data = {
          communication_channel: field,
          field_value: valueItem?.value,
        };
        apiRequests.push(
          window.componentService.api.checkFieldValueExists(postType, data),
        );
      }

      // Process API response after all have completed
      Promise.all(apiRequests)
        .then((results) => {
          for (const result of results) {
            // if any value has a duplicate, set error
            if (result && result.length) {
              const fieldName = el.getAttribute('label');
              // localize error message
              const msg =
                window.new_record_localized.translations.value_already_exists.replace(
                  '%s',
                  fieldName,
                );
              el.setAttribute('error', msg);
            }
          }
        })
        .catch((error) => {
          console.error('Error fetching data:', error);
        })
        .finally(() => {
          el.removeAttribute('loading');
        });
    }
  }

  // check for duplicates on field change events
  const nonDuplicableFields = document.querySelectorAll(
    '#contact_phone, #contact_email',
  );
  for (const nonDuplicableField of nonDuplicableFields) {
    nonDuplicableField.addEventListener('change', (event) => {
      check_field_value_exists(
        nonDuplicableField,
        event.detail.field,
        event.detail.newValue,
      );
    });
  }

  $('.js-create-post').on('click', '.delete-button', function () {
    $(this).parent().remove();
  });

  // handle form submission
  document
    .querySelector('.js-create-post')
    .addEventListener('submit', function (event) {
      if (event) {
        event.preventDefault();
      }

      // disable submit button to prevent re-submit
      $('.js-create-post-button').attr('disabled', true).addClass('loading');

      // build form values
      const form = event.target;
      Array.from(form.elements).forEach((el) => {
        // skip fields like `field_name[query]` that are from typeaheads
        // and skip values not from web components
        if (el.name.includes('[') || !el.tagName.startsWith('DT-')) {
          return;
        }

        if (el.value) {
          new_post[el.name.trim()] =
            window.DtWebComponents.ComponentService.convertValue(
              el.tagName,
              el.value,
            );
        }
      });

      // legacy number field
      $('.text-input').each((index, entry) => {
        if ($(entry).val()) {
          new_post[$(entry).attr('id')] = $(entry).val();
        }
      });

      // used for legacy boolean field
      $('.select-field').each((index, entry) => {
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

      // location_meta
      if (typeof window.selected_location_grid_meta !== 'undefined') {
        new_post['location_grid_meta'] =
          window.selected_location_grid_meta.location_grid_meta;
      }

      window.componentService.api
        .createPost(window.new_record_localized.post_type, new_post)
        .then((response) => {
          window.location = response.permalink;
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

  /**
   * Initialize legacy datetime field
   * @deprecated Remove once web components are integrated
   * @param is_bulk
   * @param bulk_id
   */
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

  /**
   * Initialize legacy typeaheads for:
   * - location
   * - user_select
   * @deprecated Remove once web components are integrated
   * @param is_bulk
   * @param bulk_id
   */
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
        if (field_type === 'location') {
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
                    xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
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

  date_picker_init();
  typeahead_general_init();
});
