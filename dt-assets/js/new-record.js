jQuery(function($) {
  window.post_type_fields = window.new_record_localized.post_type_settings.fields
  let new_post = {}
  let temp_type = $('.type-options .selected').attr('id')
  if ( temp_type ){
    new_post.type = temp_type;
  }
  document.querySelector('.form-fields input').focus()
  $('.type-option').on('click', function(){
    let type = $(this).attr('id')
    $('.type-option.selected').removeClass('selected')
    $(this).addClass('selected')
    $(`#${type} input`).prop('checked', true)
    $('.form-fields').show();
    $(`.form-field`).hide()
    $(`.form-field.all`).show()
    $(`.form-field.${type}`).show()
    $('#show-shield-banner').show()
    $('#show-hidden-fields').show();
    $('#hide-hidden-fields').hide();
    new_post.type = type
    /* Focus first field in form */
    document.querySelector('.form-fields input').focus()
  })
  $('#show-hidden-fields').on('click', function (){
    $('.form-field').show()
    $('#show-hidden-fields').hide()
    $('#hide-hidden-fields').show();
  })
  $('#hide-hidden-fields').on('click', function () {
    $('.form-field').hide();
    $(`.form-field.all`).show();
    $(`.form-field.${new_post.type}`).show();
    $('#hide-hidden-fields').hide();
    $('#show-hidden-fields').show();
  });

  $(".js-create-post-button").removeAttr("disabled");

  // Clicking the plus sign next to the field label
  $('button.add-button').on('click', e => {
    const listClass = $(e.currentTarget).data('list-class')
    const $list = $(`#edit-${listClass}`)

    $list.append(`<li style="display: flex">
              <input type="text" class="dt-communication-channel" data-field="${window.lodash.escape( listClass )}"/>
              <button class="button clear delete-button new-${window.lodash.escape( listClass )}" type="button">
                  <img src="${window.lodash.escape( window.wpApiShare.template_dir )}/dt-assets/images/invalid.svg">
              </button>
            </li>`)
  })

  $('.js-create-post').on('click', '.delete-button', function () {
    $(this).parent().remove()
  })

  $(".js-create-post").on("submit", function() {
    $(".js-create-post-button")
    .attr("disabled", true)
    .addClass("loading");
    new_post.title = $(".js-create-post input[name=title]").val()
    $('.select-field').each((index, entry)=>{
      if ( $(entry).val() ){
        new_post[$(entry).attr('id')] = $(entry).val()
      }
    })
    $('.text-input').each((index, entry)=>{
      if ( $(entry).val() ){
        new_post[$(entry).attr('id')] = $(entry).val()
      }
    })
    $('.dt_textarea').each((index, entry)=>{
      if ( $(entry).val() ){
        new_post[$(entry).attr('id')] = $(entry).val()
      }
    });
    $('.dt-communication-channel').each((index, entry)=>{
      let val = $(entry).val()
      if ( val.length > 0 ){
        let channel = $(entry).data('field')
        if ( !new_post[channel]){
          new_post[channel] =[]
        }
        new_post[channel].push({
          value: $(entry).val()
        })
      }
    })
    $('.selected-select-button').each((index, entry)=>{
      let optionKey = $(entry).attr('id')
      let fieldKey = $(entry).data("field-key")
      if ( !new_post[fieldKey]){
        new_post[fieldKey] = {values:[]};
      }
      new_post[fieldKey].values.push({
        "value": optionKey
      })
    })
    if ( typeof window.selected_location_grid_meta !== 'undefined' ){
      new_post['location_grid_meta'] = window.selected_location_grid_meta.location_grid_meta
    }


    API.create_post( window.new_record_localized.post_type, new_post).promise().then(function(data) {
      window.location = data.permalink;
    }).catch(function(error) {
      $(".js-create-post-button").removeClass("loading").addClass("alert");
      $(".js-create-post").append(
        $("<div>").html(error.responseText)
      );
      console.error(error);
    });
    return false;
  });

  let field_settings = window.new_record_localized.post_type_settings.fields

  function date_picker_init() {
    $('.dt_date_picker').datepicker({
      constrainInput: false,
      dateFormat: 'yy-mm-dd',
      onClose: function (date) {
        date = window.SHAREDFUNCTIONS.convertArabicToEnglishNumbers(date);
        if (!$(this).val()) {
          date = " ";//null;
        }
        let id = $(this).attr('id')
        new_post[id] = date
        if (this.value) {
          this.value = window.SHAREDFUNCTIONS.formatDate(moment.utc(date).unix());
        }
      },
      changeMonth: true,
      changeYear: true,
      yearRange: "1900:2050",
    });
  }

  function button_multi_select_init(is_bulk = false, bulk_id = 0) {

    // Determine field class name to be used.
    let field_class = (!is_bulk) ? `.dt_multi_select` : `.dt_multi_select-${bulk_id}`;

    // Assign on click listener.
    $(field_class).on('click', function () {

      let field = $(this);

      if (field.hasClass("selected-select-button")) {
        field.addClass("empty-select-button")
        field.removeClass("selected-select-button")
      } else {
        field.removeClass("empty-select-button")
        field.addClass("selected-select-button")
      }

    });
  }

  function typeahead_general_init(is_bulk = false, bulk_id = 0) {
    $(".typeahead__query input").each((key, el)=>{
      let field_key = $(el).data('field')
      let post_type = $(el).data('post_type')
      let field_type = $(el).data('field_type')
      typeaheadTotals = {}

      // Determine field class name to be used.
      let field_class = (!is_bulk) ? `.js-typeahead-${field_key}` : `.js-typeahead-${field_key}-${bulk_id}`;

      if (!window.Typeahead[field_class]) {

        if ( field_type === "connection"){

          $.typeahead({
            input: field_class,
            minLength: 0,
            accent: true,
            searchOnFocus: true,
            maxItem: 20,
            template: window.TYPEAHEADS.contactListRowTemplate,
            source: TYPEAHEADS.typeaheadPostsSource(post_type, field_key),
            display: ["name", "label"],
            templateValue: function() {
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
                  if (!is_bulk) {
                    window.lodash.pullAllBy(new_post[field_key].values, [{value: item.ID}], "value");
                  } else {
                    handle_bulk_typeahead_on_cancel(bulk_id, field_key, item.ID);
                  }
                }
              }
            },
            callback: {
              onResult: function (node, query, result, resultCount) {
                let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
                $(`#${field_key}-result-container`).html(text);
              },
              onHideLayout: function () {
                $(`#${field_key}-result-container`).html("");
              },
              onClick: function (node, a, item, event ) {
                if (!is_bulk) {
                  if (!new_post[field_key]) {
                    new_post[field_key] = {values: []}
                  }
                  new_post[field_key].values.push({value: item.ID})
                } else {
                  handle_bulk_typeahead_on_click(bulk_id, field_key, item.ID);
                }
                //get list from opening again
                this.addMultiselectItemLayout(item)
                event.preventDefault()
                this.hideLayout();
                this.resetInput();
              }
            }
          });
        } else if ( field_type === "location" ){
          $.typeahead({
            input: field_class,
            minLength: 0,
            accent: true,
            searchOnFocus: true,
            maxItem: 20,
            dropdownFilter: [{
              key: 'group',
              value: 'focus',
              template: window.lodash.escape(window.wpApiShare.translations.regions_of_focus),
              all: window.lodash.escape(window.wpApiShare.translations.all_locations),
            }],
            source: {
              focus: {
                display: "name",
                ajax: {
                  url: window.wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
                  data: {
                    s: "{{query}}",
                    filter: function () {
                      return window.lodash.get(window.Typeahead[field_class].filters.dropdown, 'value', 'all')
                    }
                  },
                  beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
                  },
                  callback: {
                    done: function (data) {
                      if (typeof typeaheadTotals !== "undefined") {
                        typeaheadTotals.field = data.total
                      }
                      return data.location_grid
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
              data: [],
              callback: {
                onCancel: function (node, item) {
                  if (!is_bulk) {
                    window.lodash.pullAllBy(new_post[field_key].values, [{value: item.ID}], "value");
                  } else {
                    handle_bulk_typeahead_on_cancel(bulk_id, field_key, item.ID);
                  }
                }
              }
            },
            callback: {
              onClick: function(node, a, item, event){
                if (!is_bulk) {
                  if (!new_post[field_key]) {
                    new_post[field_key] = {values: []}
                  }
                  new_post[field_key].values.push({value: item.ID})
                } else {
                  handle_bulk_typeahead_on_click(bulk_id, field_key, item.ID);
                }
                //get list from opening again
                this.addMultiselectItemLayout(item)
                event.preventDefault()
                this.hideLayout();
                this.resetInput();
              },
              onReady(){
                this.filters.dropdown = {key: "group", value: "focus", template: window.lodash.escape(window.wpApiShare.translations.regions_of_focus)}
                this.container
                  .removeClass("filter")
                  .find("." + this.options.selector.filterButton)
                  .html(window.lodash.escape(window.wpApiShare.translations.regions_of_focus));
              },
              onResult: function (node, query, result, resultCount) {
                resultCount = typeaheadTotals.location_grid
                let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
                $('#location_grid-result-container').html(text);
              },
              onHideLayout: function () {
                $('#location_grid-result-container').html("");
              }
            }
          });
        } else if ( field_type === "user_select" ){
          $.typeahead({
            input: field_class,
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
                  ${window.lodash.escape( item.name )}
              </span>
              ${ item.status_color ? `<span class="status-square" style="background-color: ${window.lodash.escape(item.status_color)};">&nbsp;</span>` : '' }
              ${ item.update_needed && item.update_needed > 0 ? `<span>
                <img style="height: 12px;" src="${window.lodash.escape( window.wpApiShare.template_dir )}/dt-assets/images/broken.svg"/>
                <span style="font-size: 14px">${window.lodash.escape(item.update_needed)}</span>
              </span>` : '' }
            </div>`
            },
            dynamic: true,
            hint: true,
            emptyTemplate: window.lodash.escape(window.wpApiShare.translations.no_records_found),
            callback: {
              onClick: function(node, a, item){
                if (!is_bulk) {
                  new_post[field_key] = item.ID;
                } else {
                  handle_bulk_typeahead_on_click(bulk_id, field_key, item.ID);
                }
              },
              onResult: function (node, query, result, resultCount) {
                let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
                $(`#${field_key}-result-container`).html(text);
              },
              onHideLayout: function () {
                $(`.${field_key}-result-container`).html("");
              }
            },
          });
          let user_input = $(`.js-typeahead-${field_key}`)
          $(`.search_${field_key}`).on('click', function () {
            user_input.val("")
            user_input.trigger('input.typeahead')
            user_input.focus()
          })
        }
      }
    });
  }

  function typeahead_multi_select_init(is_bulk = false, bulk_id = 0) {
    //multi-select typeaheads
    for (let input of $(".multi_select .typeahead__query input")) {
      let field = $(input).data('field')
      let typeahead_name = (!is_bulk) ? `.js-typeahead-${field}` : `.js-typeahead-${field}-${bulk_id}`;

      if (window.Typeahead[typeahead_name]) {
        return
      }

      let source_data = {data: []}
      let field_options = window.lodash.get(field_settings, `${field}.default`, {})
      if (Object.keys(field_options).length > 0) {
        window.lodash.forOwn(field_options, (val, key) => {
          if (!val.deleted) {
            source_data.data.push({
              key: key,
              name: key,
              value: val.label || key
            })
          }
        })
      } else {
        source_data = {
          [field]: {
            display: ["value"],
            ajax: {
              url: window.wpApiShare.root + `dt-posts/v2/${post_type}/multi-select-values`,
              data: {
                s: "{{query}}",
                field
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  return (data || []).map(tag => {
                    let label = window.lodash.get(field_options, tag + ".label", tag)
                    return {value: label, key: tag}
                  })
                }
              }
            }
          }
        }
      }
      $.typeahead({
        input: typeahead_name,
        minLength: 0,
        maxItem: 20,
        searchOnFocus: true,
        template: function (query, item) {
          return `<span>${window.lodash.escape(item.value)}</span>`
        },
        source: source_data,
        display: "value",
        templateValue: "{{value}}",
        dynamic: true,
        multiselect: {
          matchOn: ["key"],
          data: [],
          callback: {
            onCancel: function (node, item, event) {
              if (!is_bulk) {
                window.lodash.pullAllBy(new_post[field].values, [{value: item.key}], "value");
              } else {
                handle_bulk_typeahead_on_cancel(bulk_id, field, item.key);
              }
            }
          }
        },
        callback: {
          onClick: function (node, a, item, event) {
            if (!is_bulk) {
              if (!new_post[field]) {
                new_post[field] = {values: []}
              }
              new_post[field].values.push({value: item.key})
            } else {
              handle_bulk_typeahead_on_click(bulk_id, field, item.key);
            }
            this.addMultiselectItemLayout(item)
            event.preventDefault()
            this.hideLayout();
            this.resetInput();

          },
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            //adding the result text moves the input. timeout keeps the dropdown from closing as the user clicks and cursor moves away from the input.
            setTimeout(() => {
              $(`#${field}-result-container`).html(text);
            }, 200);
          },
          onHideLayout: function () {
            $(`#${field}-result-container`).html("");
          }
        }
      });
    }
  }

  function typeahead_tags_init(is_bulk = false, bulk_id = 0) {
    /**
     * Tags
     */
    $('.tags .typeahead__query input').each((key, input) => {
      let field = $(input).data('field') || 'tags'
      let typeahead_name = (!is_bulk) ? `.js-typeahead-${field}` : `.js-typeahead-${field}-${bulk_id}`;

      const post_type = window.new_record_localized.post_type
      $.typeahead({
        input: typeahead_name,
        minLength: 0,
        maxItem: 20,
        searchOnFocus: true,
        source: {
          tags: {
            display: ["value"],
            ajax: {
              url: window.wpApiShare.root + `dt-posts/v2/${post_type}/multi-select-values`,
              data: {
                s: "{{query}}",
                field: field
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  return (data || []).map(tag => {
                    return {value: tag}
                  })
                }
              }
            }
          }
        },
        display: "value",
        templateValue: "{{value}}",
        emptyTemplate: function (query) {
          const {addNewTagText, tagExistsText} = this.node[0].dataset
          if (this.comparedItems.includes(query)) {
            return tagExistsText.replace('%s', query)
          }
          const liItem = $('<li>')
          const button = $('<button>', {
            class: "button primary",
            text: addNewTagText.replace('%s', query),
          })
          const tag = this.query
          const typeahead = this
          button.on("click", function (event) {
            if (!is_bulk) {
              if (!new_post[field]) {
                new_post[field] = {values: []}
              }
              new_post[field].values.push({value: tag})
            } else {
              handle_bulk_typeahead_on_click(bulk_id, field, tag);
            }
            typeahead.addMultiselectItemLayout({value: tag})
            event.preventDefault()
            typeahead.hideLayout();
            typeahead.resetInput();
          })
          liItem.append(button)
          return liItem
        },
        dynamic: true,
        multiselect: {
          matchOn: ["value"],
          data: [],
          callback: {
            onCancel: function (node, item) {
              if (!is_bulk) {
                window.lodash.pullAllBy(new_post[field].values, [{value: item.value}], "value");
              } else {
                handle_bulk_typeahead_on_cancel(bulk_id, field, item.value);
              }
            }
          },
        },
        callback: {
          onClick: function (node, a, item, event) {
            if (!is_bulk) {
              if (!new_post[field]) {
                new_post[field] = {values: []}
              }
              new_post[field].values.push({value: item.value})
            } else {
              handle_bulk_typeahead_on_click(bulk_id, field, item.value);
            }
            this.addMultiselectItemLayout(item)
            event.preventDefault()
            this.hideLayout();
            this.resetInput();
          },
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $(`#${field}-result-container`).html(text);
          },
          onHideLayout: function () {
            $(`#${field}-result-container`).html("");
          },
        }
      });
    });
  }
  $('.js-create-post').on('click', '.create-new-tag', function () {
    let field = $(this).data("field");
    $("#create-tag-modal").data("field", field)

  });
  $("#create-tag-return").on("click", function () {
    let field = $("#create-tag-modal").data("field");
    let tag = $("#new-tag").val()
    $('#new-tag').val("")
    if ( !new_post[field] ){
      new_post[field] = { values: [] }
    }
    new_post[field].values.push({value: tag})
    Typeahead['.js-typeahead-' + field].addMultiselectItemLayout({value: tag})
  })

  /**
   * ============== [ BULK RECORD ADDING FUNCTIONALITY ] ==============
   */

  /*
   * Instantiate elements accordingly, based on new record creation type:
   *  - Normal
   *  - Bulk
   */

  let is_normal_new_record = (!$('#form_fields_records').length) ? true : false;
  if (is_normal_new_record) {

    date_picker_init();
    button_multi_select_init();
    typeahead_general_init();
    typeahead_multi_select_init();
    typeahead_tags_init();

  } else {

    let bulk_record_id = 1;

    adjust_new_button_multi_select_class_names(bulk_record_id);
    adjust_new_typeahead_general_element_class_names(bulk_record_id);
    adjust_new_typeahead_multi_select_element_class_names(bulk_record_id);
    adjust_new_typeahead_tags_element_class_names(bulk_record_id);

    date_picker_init();
    button_multi_select_init(true, bulk_record_id);
    typeahead_general_init(true, bulk_record_id);
    typeahead_multi_select_init(true, bulk_record_id);
    typeahead_tags_init(true, bulk_record_id);

  }

  /*
   * Typeahead helper functions
   */

  let typeahead_staging_states = {};

  function handle_bulk_typeahead_on_cancel(bulk_record_id, field_key, value) {
    if (typeahead_staging_states[bulk_record_id][field_key]) {
      window.lodash.pullAllBy(typeahead_staging_states[bulk_record_id][field_key].values, [{value: value}], "value");
    }
  }

  function handle_bulk_typeahead_on_click(bulk_record_id, field_key, value) {
    if (!typeahead_staging_states[bulk_record_id]) {
      typeahead_staging_states[bulk_record_id] = {}
    }

    if (!typeahead_staging_states[bulk_record_id][field_key]) {
      typeahead_staging_states[bulk_record_id][field_key] = {values: []}
    }

    typeahead_staging_states[bulk_record_id][field_key].values.push({value: value});
  }

  /*
   * Respond to new bulk record addition requests.
   */

  $('#add_new_bulk_record').on('click', function () {
    API.get_new_bulk_record_fields(window.new_record_localized.post_type).promise().then(function (data) {
      let new_records_count = $('#form_fields_records').find('.form-fields-record').length + 1;
      let html = '<div class="form-fields-record"><input type="hidden" id="bulk_record_id" value="' + new_records_count + '"><div style="background-color:rgb(236, 245, 252); margin: 3px -15px 15px -15px;">&nbsp;</div><div id="bulk_record_landscape_layout"></div>' + data + '</div>';
      let updated_records = $('#form_fields_records').append(html);

      // Adjust relevant class names for recently added record elements.
      adjust_new_button_multi_select_class_names(new_records_count);
      adjust_new_typeahead_general_element_class_names(new_records_count);
      adjust_new_typeahead_multi_select_element_class_names(new_records_count);
      adjust_new_typeahead_tags_element_class_names(new_records_count);

      // Initialise newly added element specifal functionality; e.g. typeaheads.
      date_picker_init();
      button_multi_select_init(true, new_records_count);
      typeahead_general_init(true, new_records_count);
      typeahead_multi_select_init(true, new_records_count);
      typeahead_tags_init(true, new_records_count);

      // Apply latest layout orientation
      let new_record = $('#form_fields_records').find('.form-fields-record').last();
      is_landscape_layout() ? switch_to_landscape_layout(new_record) : switch_to_portrait_layout(new_record);

      // Apply latest field filters
      apply_field_filters();

    }).catch(function (error) {
      console.error(error);
    });
  });

  function adjust_new_button_multi_select_class_names(bulk_id) {
    $('#form_fields_records').find('.form-fields-record').last().find('.dt_multi_select').each((key, el) => {
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
    $('#form_fields_records').find('.form-fields-record').last().find('.typeahead__query input').each((key, el) => {
      adjust_new_typeahead_class_name(bulk_id, el);
    });
  }

  function adjust_new_typeahead_multi_select_element_class_names(bulk_id) {
    $('#form_fields_records').find('.form-fields-record').last().find('.multi_select .typeahead__query input').each((key, el) => {
      adjust_new_typeahead_class_name(bulk_id, el);
    });
  }

  function adjust_new_typeahead_tags_element_class_names(bulk_id) {
    $('#form_fields_records').find('.form-fields-record').last().find('.tags .typeahead__query input').each((key, el) => {
      adjust_new_typeahead_class_name(bulk_id, el);
    });
  }

  function adjust_new_typeahead_class_name(bulk_id, element) {
    let field_key = $(element).data('field')
    let old_field_class = `js-typeahead-${field_key}`;

    if ($(element).hasClass(old_field_class)) {
      let new_field_class = `js-typeahead-${field_key}-${bulk_id}`;
      $(element).removeClass(old_field_class).addClass(new_field_class);
    }
  }

  /*
   * Respond to bulk save requests.
   */

  $(".js-create-post-bulk-button").removeAttr("disabled");
  $(".js-create-post-bulk").on("submit", function (evt) {
    evt.preventDefault();

    // Capture parent level settings
    let type = $('.type-options .selected').attr('id');

    // Change submit button loading state
    $(".js-create-post-bulk-button").attr("disabled", true).addClass("loading");

    // Iterate over form records to be added
    let records_counter = 0;
    let records_total = $('#form_fields_records').find('.form-fields-record').length;
    $('#form_fields_records').find('.form-fields-record').each((key, record) => {

      // Start to build new post object
      let new_post = {}
      if (type) {
        new_post.type = type;
      }

      $(record).find('.select-field').each((index, entry) => {
        if ($(entry).val()) {
          new_post[$(entry).attr('id')] = $(entry).val()
        }
      });

      $(record).find('.text-input').each((index, entry) => {
        if ($(entry).val()) {
          new_post[$(entry).attr('id')] = $(entry).val()
        }
      });

      $(record).find('.dt_textarea').each((index, entry) => {
        if ($(entry).val()) {
          new_post[$(entry).attr('id')] = $(entry).val()
        }
      });

      $(record).find('.dt-communication-channel').each((index, entry) => {
        let val = $(entry).val()
        if (val.length > 0) {
          let channel = $(entry).data('field')
          if (!new_post[channel]) {
            new_post[channel] = []
          }
          new_post[channel].push({
            value: $(entry).val()
          });
        }
      });

      $(record).find('.selected-select-button').each((index, entry) => {
        let optionKey = $(entry).attr('id')
        let fieldKey = $(entry).data("field-key")
        if (!new_post[fieldKey]) {
          new_post[fieldKey] = {values: []};
        }
        new_post[fieldKey].values.push({
          "value": optionKey
        });
      });

      if (typeof window.selected_location_grid_meta !== 'undefined') {
        new_post['location_grid_meta'] = window.selected_location_grid_meta.location_grid_meta
      }

      // Package any available typeahead values
      let bulk_record_id = $(record).find('#bulk_record_id').val();
      if (typeahead_staging_states[bulk_record_id]) {
        for (let field_key in typeahead_staging_states[bulk_record_id]) {
          new_post[field_key] = typeahead_staging_states[bulk_record_id][field_key];
        }
      }

      // Save new record post!
      API.create_post(window.new_record_localized.post_type, new_post).promise().then(function (data) {

        // Only redirect once all records have been processed!
        if (++records_counter >= records_total) {
          window.location = data.permalink;
        } else {
          $(record).slideUp('slow');
        }

      }).catch(function (error) {
        console.error(error);
      });
    });
  });

  /*
   * Handle cherry-picking of fields to be displayed.
   */

  let default_filter_fields = [];

  if (!is_normal_new_record) {
    adjust_selected_field_filters_by_currently_displayed_record_fields();

    // Initial displayed fields, to be captured as defualts; ready for use further down stream!
    default_filter_fields = list_currently_displayed_fields();
  }

  $('#choose_fields_to_show_in_records').on('click', function (evt) {
    evt.preventDefault();
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

    $(record).find('.select-field').each((index, entry) => {
      if ($(entry).is(':visible')) {
        fields.push($(entry).attr('id'));
      }
    });

    $(record).find('.text-input').each((index, entry) => {
      if ($(entry).is(':visible')) {
        fields.push($(entry).attr('id'));
      }
    });

    $(record).find('.dt_textarea').each((index, entry) => {
      if ($(entry).is(':visible')) {
        fields.push($(entry).attr('id'));
      }
    });

    $(record).find('.dt-communication-channel').each((index, entry) => {
      if ($(entry).is(':visible')) {
        fields.push($(entry).data('field'));
      }
    });

    $(record).find('.selected-select-button').each((index, entry) => {
      if ($(entry).is(':visible')) {
        fields.push($(entry).data('field-key'));
      }
    });

    $(record).find(`.dt_multi_select-${bulk_id}`).each((index, entry) => {
      if ($(entry).is(':visible')) {
        fields.push($(entry).data('field-key'));
      }
    });

    $(record).find('.typeahead__query input').each((index, entry) => {
      if ($(entry).is(':visible')) {
        fields.push($(entry).data('field'));
      }
    });

    $(record).find('.multi_select .typeahead__query input').each((index, entry) => {
      if ($(entry).is(':visible')) {
        fields.push($(entry).data('field'));
      }
    });

    $(record).find('.tags .typeahead__query input').each((index, entry) => {
      if ($(entry).is(':visible')) {
        fields.push($(entry).data('field'));
      }
    });

    return window.lodash.uniq(fields);
  }

  function refresh_displayed_fields(filter_fields) {
    $('#form_fields_records').find('.form-fields-record').each((key, record) => {

      let bulk_record_id = $(record).find('#bulk_record_id').val();

      $(record).find('.select-field').each((index, entry) => {
        let target_parent = $(entry).parent();
        let is_displayed = window.lodash.includes(filter_fields, $(entry).attr('id'));
        $(target_parent).toggle(is_displayed);

        // Also accommodate landscape tabular layouts
        if (is_landscape_layout()) {
          $(target_parent).parent().toggle(is_displayed);
        }
      });

      $(record).find('.text-input').each((index, entry) => {
        let target_parent = $(entry).parent();
        let is_displayed = window.lodash.includes(filter_fields, $(entry).attr('id'));
        $(target_parent).toggle(is_displayed);

        // Also accommodate landscape tabular layouts
        if (is_landscape_layout()) {
          $(target_parent).parent().toggle(is_displayed);
        }
      });

      $(record).find('.dt_textarea').each((index, entry) => {
        let target_parent = $(entry).parent();
        let is_displayed = window.lodash.includes(filter_fields, $(entry).attr('id'));
        $(target_parent).toggle(is_displayed);

        // Also accommodate landscape tabular layouts
        if (is_landscape_layout()) {
          $(target_parent).parent().toggle(is_displayed);
        }
      });

      $(record).find('.dt-communication-channel').each((index, entry) => {
        let target_parent = $(entry).parent().parent().parent();
        let is_displayed = window.lodash.includes(filter_fields, $(entry).data('field'));
        $(target_parent).toggle(is_displayed);

        // Also accommodate landscape tabular layouts
        if (is_landscape_layout()) {
          $(target_parent).parent().toggle(is_displayed);
        }
      });

      $(record).find('.selected-select-button').each((index, entry) => {
        let target_parent = $(entry).parent();
        let is_displayed = window.lodash.includes(filter_fields, $(entry).data('field-key'));
        $(target_parent).toggle(is_displayed);

        // Also accommodate landscape tabular layouts
        if (is_landscape_layout()) {
          $(target_parent).parent().toggle(is_displayed);
        }
      });

      $(record).find(`.dt_multi_select-${bulk_record_id}`).each((index, entry) => {
        let target_parent = $(entry).parent().parent();
        let is_displayed = window.lodash.includes(filter_fields, $(entry).data('field-key'));
        $(target_parent).toggle(is_displayed);

        // Also accommodate landscape tabular layouts
        if (is_landscape_layout()) {
          $(target_parent).parent().toggle(is_displayed);
        }
      });

      $(record).find('.typeahead__query input').each((index, entry) => {
        let target_parent = $(entry).parent().parent().parent().parent().parent().parent();
        let is_displayed = window.lodash.includes(filter_fields, $(entry).data('field'));
        $(target_parent).toggle(is_displayed);

        // Also accommodate landscape tabular layouts
        if (is_landscape_layout()) {
          $(target_parent).parent().toggle(is_displayed);
        }
      });

      $(record).find('.multi_select .typeahead__query input').each((index, entry) => {
        let target_parent = $(entry).parent().parent().parent().parent().parent().parent();
        let is_displayed = window.lodash.includes(filter_fields, $(entry).data('field'));
        $(target_parent).toggle(is_displayed);

        // Also accommodate landscape tabular layouts
        if (is_landscape_layout()) {
          $(target_parent).parent().toggle(is_displayed);
        }
      });

      $(record).find('.tags .typeahead__query input').each((index, entry) => {
        let target_parent = $(entry).parent().parent().parent().parent().parent().parent();
        let is_displayed = window.lodash.includes(filter_fields, $(entry).data('field'));
        $(target_parent).toggle(is_displayed);

        // Also accommodate landscape tabular layouts
        if (is_landscape_layout()) {
          $(target_parent).parent().toggle(is_displayed);
        }
      });
    });
  }

  function apply_field_filters() {
    let new_selected = [];
    $('#list_fields_picker input:checked').each((index, elem) => {
      new_selected.push($(elem).val())
    });

    let fields_to_show_in_records = window.lodash.intersection([], new_selected); // remove unchecked
    fields_to_show_in_records = window.lodash.uniq(window.lodash.union(fields_to_show_in_records, new_selected));

    refresh_displayed_fields(fields_to_show_in_records);
    $('#list_fields_picker').toggle(false);
    adjust_selected_field_filters_by_currently_displayed_record_fields();
  }

  function reset_field_filters() {
    refresh_displayed_fields(default_filter_fields);
    $('#list_fields_picker').toggle(false);
    adjust_selected_field_filters_by_currently_displayed_record_fields();
  }

  /*
   * Handle dynamic layout views -> Landscape or Portrait.
   */

  if (!is_normal_new_record) {

    // Determine current layout and adjust accordingly, so as to force an initial layout refresh!
    $('#bulk_records_current_layout').val(is_landscape_layout() ? 'portrait' : 'landscape');
    apply_dynamic_layout();

    // Remain sensitive to window resizing and adjust layout accordingly
    $(window).resize(function () {
      apply_dynamic_layout();
    });

    function is_landscape_layout() {
      return $(window).width() > 1000;
    }

    function apply_dynamic_layout() {

      // Landscape
      if (is_landscape_layout() && $('#bulk_records_current_layout').val() !== 'landscape') {

        // Update current layout flag
        $('#bulk_records_current_layout').val('landscape');

        // Iterate over available records in order to start reformatting layout.
        $('#form_fields_records').find('.form-fields-record').each((key, record) => {
          switch_to_landscape_layout(record);
        });

        // Portrait
      } else if (!is_landscape_layout() && $('#bulk_records_current_layout').val() !== 'portrait') {

        // Update current layout flag
        $('#bulk_records_current_layout').val('portrait');

        // Iterate over available records in order to start reformatting layout.
        $('#form_fields_records').find('.form-fields-record').each((key, record) => {
          switch_to_portrait_layout(record);
        });

      }
    }

    function switch_to_landscape_layout(record) {
      if (record) {

        // Obtain handle onto landscape section parent.
        let landscape_layout = $(record).find('#bulk_record_landscape_layout');

        // Prepare layout table.
        let landscape_table = $('<table>');
        let landscape_table_row = $('<tr>');

        // Start re-housing elements within new landscape tabular layout.
        $(record).find('.form-field').each((key, field_div) => {

          let landscape_table_row_data = $('<td>');
          landscape_table_row_data.css('padding', '10px');
          landscape_table_row_data.css('vertical-align', 'top');

          // Determine visibility.
          let is_visible = $(field_div).is(':visible');

          // All fields to be placed into respective column; however, only visible field columns to be shown.
          landscape_table_row_data.append($(field_div));

          // Hide column accordingly.
          if (!is_visible) {
            landscape_table_row_data.hide();
          }

          // Append to respective parents.
          $(landscape_table_row).append($(landscape_table_row_data));
          $(landscape_table).append($(landscape_table_row));
          $(landscape_layout).append($(landscape_table));

        });
      }
    }

    function switch_to_portrait_layout(record) {
      if (record) {

        // Obtain handle onto landscape section parent.
        let landscape_layout = $(record).find('#bulk_record_landscape_layout');

        // Ensure we have stuff to work with.
        if ($(landscape_layout).contents().length > 0) {

          // Move all field elements back to record root.
          $(record).append($(landscape_layout).find('.form-field'));

          // Reset landscape layout section.
          $(landscape_layout).empty();
        }
      }
    }
  }

  /**
   * ============== [ BULK RECORD ADDING FUNCTIONALITY ] ==============
   */

});
