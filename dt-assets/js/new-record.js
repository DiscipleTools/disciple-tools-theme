jQuery(function($) {
  let new_post = {}
  $('.type-option').on('click', function(){
    let type = $(this).attr('id')
    $('.type-option.selected').removeClass('selected')
    $(this).addClass('selected')
    $(`#${type} input`).prop('checked', true)
    $('.form-fields').show();
    $(`.form-field`).hide()
    $(`.type-control-field`).hide()
    $(`.form-field.all`).show()
    $(`.form-field.${type}`).show()
    $('#show-shield-banner').show()
    $('#show-hidden-fields').show();
    $('#hide-hidden-fields').hide();
    new_post.type = type
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
  $('button.dt_multi_select').on('click',function () {
    let fieldKey = $(this).data("field-key")
    let optionKey = $(this).attr('id')
    let field = jQuery(`[data-field-key="${fieldKey}"]#${optionKey}`)
    if (field.hasClass("selected-select-button")){
      field.addClass("empty-select-button")
      field.removeClass("selected-select-button")
    } else {
      field.removeClass("empty-select-button")
      field.addClass("selected-select-button")
    }
  })
  $('.js-create-post').on('click', '.delete-button', function () {
    $(this).parent().remove()
  })

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
      this.value = window.SHAREDFUNCTIONS.formatDate(moment.utc(date).unix());
    },
    changeMonth: true,
    changeYear: true,
    yearRange: "1900:2050",
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

  $(".typeahead__query input").each((key, el)=>{
    let field_key = $(el).data('field')
    let post_type = $(el).data('post_type')
    let field_type = $(el).data('field_type')
    typeaheadTotals = {}
    if (!window.Typeahead[`.js-typeahead-${field_key}`]) {

      if ( field_type === "connection"){

        $.typeahead({
          input: `.js-typeahead-${field_key}`,
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
                window.lodash.pullAllBy(new_post[field_key].values, [{value:item.ID}], "value")
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
              if ( !new_post[field_key] ){
                new_post[field_key] = { values: [] }
              }
              new_post[field_key].values.push({value:item.ID})
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
          input: '.js-typeahead-location_grid',
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
                    return window.lodash.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
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
                window.lodash.pullAllBy(new_post[field_key].values, [{value:item.ID}], "value")
              }
            }
          },
          callback: {
            onClick: function(node, a, item, event){
              if ( !new_post[field_key] ){
                new_post[field_key] = { values: [] }
              }
              new_post[field_key].values.push({value:item.ID})
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
          input: `.js-typeahead-${field_key}`,
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
              new_post[field_key] = item.ID
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
  })

  let field_settings = window.new_record_localized.post_type_settings.fields
  //multi-select typeaheads
  for (let input of $(".multi_select .typeahead__query input")) {
    let field = $(input).data('field')
    let typeahead_name = `.js-typeahead-${field}`

    if (window.Typeahead[typeahead_name]) {
      return
    }

    let source_data =  { data: [] }
    let field_options = window.lodash.get(field_settings, `${field}.default`, {})
    if ( Object.keys(field_options).length > 0 ){
      window.lodash.forOwn(field_options, (val, key)=>{
        if ( !val.deleted ){
          source_data.data.push({
            key: key,
            name:key,
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
      input: `.js-typeahead-${field}`,
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
            window.lodash.pullAllBy(new_post[field].values, [{value:item.key}], "value")
          }
        }
      },
      callback: {
        onClick: function(node, a, item, event){
          if ( !new_post[field] ){
            new_post[field] = { values: [] }
          }
          new_post[field].values.push({value:item.key})
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
        }
      }
    });
  }

  /**
   * Tags
   */
  $('.tags .typeahead__query input').each((key, input)=>{
    let field = $(input).data('field') || 'tags'
    let typeahead_name = `.js-typeahead-${field}`
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
      emptyTemplate: function(query) {
        const { addNewTagText, tagExistsText} = this.node[0].dataset
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
          if ( !new_post[field] ){
            new_post[field] = { values: [] }
          }
          new_post[field].values.push({value:tag})
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
            window.lodash.pullAllBy(new_post[field_key].values, [{value:item.ID}], "value")
          }
        },
      },
      callback: {
        onClick: function (node, a, item, event) {
          if ( !new_post[field] ){
            new_post[field] = { values: [] }
          }
          new_post[field].values.push({value:item.value})
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
  })

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
});
