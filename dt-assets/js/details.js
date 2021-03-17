"use strict";
jQuery(document).ready(function($) {
  let post_id = window.detailsSettings.post_id
  let post_type = window.detailsSettings.post_type
  let post = window.detailsSettings.post_fields
  let field_settings = window.detailsSettings.post_settings.fields
  window.post_type_fields = field_settings
  let rest_api = window.API
  let typeaheadTotals = {}

  window.masonGrid = $('.grid') // responsible for resizing and moving the tiles

  $('input.text-input').change(function(){
    const id = $(this).attr('id')
    const val = $(this).val()
    $(`#${id}-spinner`).addClass('active')
    rest_api.update_post(post_type, post_id, { [id]: val }).then((newPost)=>{
      $(`#${id}-spinner`).removeClass('active')
      $( document ).trigger( "text-input-updated", [ newPost, id, val ] );
    }).catch(handleAjaxError)
  })
  $('.dt_textarea').change(function(){
    const id = $(this).attr('id')
    const val = $(this).val()
    $(`#${id}-spinner`).addClass('active')
    rest_api.update_post(post_type, post_id, { [id]: val }).then((newPost)=>{
      $(`#${id}-spinner`).removeClass('active')
      $( document ).trigger( "text-input-updated", [ newPost, id, val ] );
    }).catch(handleAjaxError)
  })

  $('button.dt_multi_select').on('click',function () {
    let fieldKey = $(this).data("field-key")
    let optionKey = $(this).attr('id')
    let fieldValue = {}
    let data = {}
    let field = jQuery(`[data-field-key="${fieldKey}"]#${optionKey}`)
    field.addClass("submitting-select-button")
    let action = "add"
    if (field.hasClass("selected-select-button")){
      fieldValue.values = [{value:optionKey,delete:true}]
      action = "delete"
    } else {
      field.removeClass("empty-select-button")
      field.addClass("selected-select-button")
      fieldValue.values = [{value:optionKey}]
    }
    data[optionKey] = fieldValue
    $(`#${fieldKey}-spinner`).addClass('active')
    rest_api.update_post(post_type, post_id, {[fieldKey]: fieldValue}).then((resp)=>{
      $(`#${fieldKey}-spinner`).removeClass('active')
      field.removeClass("submitting-select-button selected-select-button")
      field.blur();
      field.addClass( action === "delete" ? "empty-select-button" : "selected-select-button");
      $( document ).trigger( "dt_multi_select-updated", [ resp, fieldKey, optionKey, action ] );
    }).catch(err=>{
      field.removeClass("submitting-select-button selected-select-button")
      field.addClass( action === "add" ? "empty-select-button" : "selected-select-button")
      handleAjaxError(err)
    })
  })


  $('.dt_date_picker').datepicker({
    constrainInput: false,
    dateFormat: 'yy-mm-dd',
    onClose: function (date) {
      if (document.querySelector('#group-details-edit-modal') && document.querySelector('#group-details-edit-modal').contains( this)) {
        // do nothing
      } else {
        date = window.SHAREDFUNCTIONS.convertArabicToEnglishNumbers(date);

        if (!$(this).val()) {
          date = " ";//null;
        }
        let id = $(this).attr('id')
        $(`#${id}-spinner`).addClass('active')
        rest_api.update_post( post_type, post_id, { [id]: moment.utc(date).unix() }).then((resp)=>{
          $(`#${id}-spinner`).removeClass('active')
          if (this.value) {
            this.value = window.SHAREDFUNCTIONS.formatDate(resp[id]["timestamp"]);
          }
          $( document ).trigger( "dt_date_picker-updated", [ resp, id, date ] );
        }).catch(handleAjaxError)
      }
    },
    changeMonth: true,
    changeYear: true,
    yearRange: "1900:2050",
  }).each(function() {
    if (this.value && moment.unix(this.value).isValid()) {
      this.value = window.SHAREDFUNCTIONS.formatDate(this.value);
    }
  })


  let mcleardate = $(".clear-date-button");
  mcleardate.click(function() {
    let input_id = this.dataset.inputid;
    $(`#${input_id}`).val("");
    let date = null;
    $(`#${input_id}-spinner`).addClass('active')
    rest_api.update_post(post_type, post_id, { [input_id]: date }).then((resp) => {
      $(`#${input_id}-spinner`).removeClass('active')
      $(document).trigger("dt_date_picker-updated", [resp, input_id, date]);

    }).catch(handleAjaxError)
  });

  $('select.select-field').change(e => {
    const id = $(e.currentTarget).attr('id')
    const val = $(e.currentTarget).val()
    $(`#${id}-spinner`).addClass('active')

    rest_api.update_post(post_type, post_id, { [id]: val }).then(resp => {
      $(`#${id}-spinner`).removeClass('active')
      $( document ).trigger( "select-field-updated", [ resp, id, val ] );
      if ( $(e.currentTarget).hasClass( "color-select")){
        $(`#${id}`).css("background-color", window.lodash.get(window.detailsSettings, `post_settings.fields[${id}].default[${val}].color`) )
      }
    }).catch(handleAjaxError)
  })

  $('input.number-input').on("blur", function(){
    const id = $(this).attr('id')
    const val = $(this).val()
    $(`#${id}-spinner`).addClass('active')
    rest_api.update_post(post_type, post_id, { [id]: val }).then((resp)=>{
      $(`#${id}-spinner`).removeClass('active')
      $( document ).trigger( "number-input-updated", [ resp, id, val ] );
    }).catch(handleAjaxError)
  })

  $('.dt_contenteditable').on('blur', function(){
    const id = $(this).attr('id')
    let val = $(this).text();
    rest_api.update_post(post_type, post_id, { [id]: val }).then((resp)=>{
      $( document ).trigger( "contenteditable-updated", [ resp, id, val ] );
    }).catch(handleAjaxError)
  })

  // Clicking the plus sign next to the field label
  $('button.add-button').on('click', e => {
    const field = $(e.currentTarget).data('list-class')
    const $list = $(`#edit-${field}`)

    $list.append(`<div class="input-group">
            <input type="text" data-field="${window.lodash.escape( field )}" class="dt-communication-channel input-group-field" dir="auto" />
            <div class="input-group-button">
            <button class="button alert input-height delete-button-style channel-delete-button delete-button new-${window.lodash.escape( field )}" data-key="new" data-field="${window.lodash.escape( field )}">&times;</button>
            </div></div>`)
  })
  $(document).on('click', '.channel-delete-button', function(){
    let field = $(this).data('field')
    let key = $(this).data('key')
    let update = { delete:true }
    if ( key === 'new' ){
      $(this).parent().remove()
    } else if ( key ){
      $(`#${field}-spinner`).addClass('active')
      update["key"] = key;
      API.update_post(post_type, post_id, { [field]: [update]}).then((updatedContact)=>{
        $(this).parent().parent().remove()
        let list = $(`#edit-${field}`)
        if ( list.children().length === 0 ){
          list.append(`<div class="input-group">
            <input type="text" data-field="${window.lodash.escape( field )}" class="dt-communication-channel input-group-field" dir="auto" />
            </div>`)
        }
        $(`#${field}-spinner`).removeClass('active')
        post = updatedContact
        resetDetailsFields()
      }).catch(handleAjaxError)
    }
  })

  $(document).on('blur', 'input.dt-communication-channel', function(){
    let field_key = $(this).data('field')
    let value = $(this).val()
    let id = $(this).attr('id')
    let update = { value }
    if ( id ) {
      update["key"] = id;
    }
    $(`#${field_key}-spinner`).addClass('active')
    API.update_post(post_type, post_id, { [field_key]: [update]}).then((updatedContact)=>{
      $(`#${field_key}-spinner`).removeClass('active')
      let key = window.lodash.last(updatedContact[field_key]).key
      $(this).attr('id', key)
      if ( $(this).next('div.input-group-button').length === 1 ) {
        $(this).parent().find('.channel-delete-button').data('key', key)
      } else {
        $(this).parent().append(`<div class="input-group-button">
            <button class="button alert delete-button-style input-height channel-delete-button delete-button" data-key="${window.lodash.escape( key )}" data-field="${window.lodash.escape( field_key )}">&times;</button>
        </div>`)
      }
      post = updatedContact
      resetDetailsFields()
    }).catch(handleAjaxError)
  })

  $( document ).on( 'select-field-updated', function (e, newContact, id, val) {
  })

  $( document ).on( 'text-input-updated', function (e, newContact, id, val){
    if ( id === "name" ){
      $("#title").html(window.lodash.escape(val))
      $("#second-bar-name").text(window.lodash.escape(val))
    }
  })

  $( document ).on( 'contenteditable-updated', function (e, newContact, id, val){
    if ( id === "title" ){
      $("#name").val(window.lodash.escape(val))
      $("#second-bar-name").text(window.lodash.escape(val))
    }
  })

  $( document ).on( 'dt_date_picker-updated', function (e, newContact, id, date){
  })

  $( document ).on( 'dt_multi_select-updated', function (e, newContact, fieldKey, optionKey, action) {
  })

  $( document ).on( 'dt_record_updated', function (e, response, request ){
    post = response;
    resetDetailsFields()
    record_updated(window.lodash.get(response, "requires_update", false));

  })



  /**
   * Update Needed
   */
  $('.update-needed.dt-switch').change(function () {
    let updateNeeded = $(this).is(':checked')
    API.update_post( post_type, post_id, {"requires_update":updateNeeded}).then(resp=>{
      post = resp
    })
  })



  $('.show-details-section').on( "click", function (){
    $('#details-section').toggle()
    $('#show-details-edit-button').toggle()
    $(`#details-section .typeahead__query input`).each((i, element)=>{
      let field_key = $(element).data("field")
      if ( Typeahead[`.js-typeahead-${field_key}`]){
        Typeahead[`.js-typeahead-${field_key}`].adjustInputSize()
      }
    })
  })


  $('.dt_typeahead').each((key, el)=>{
    let field_id = $(el).attr('id').replace('_connection', '')
    let listing_post_type = window.lodash.get(window.detailsSettings.post_settings.fields[field_id], "post_type", 'contacts')
    $.typeahead({
      input: `.js-typeahead-${field_id}`,
      minLength: 0,
      accent: true,
      maxItem: 30,
      searchOnFocus: true,
      template: window.TYPEAHEADS.contactListRowTemplate,
      matcher: function (item) {
        return parseInt(item.ID) !== parseInt(post_id)
      },
      source: window.TYPEAHEADS.typeaheadPostsSource(listing_post_type, {field_key:field_id}),
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
        data: function () {
          return (post[field_id] || [] ).map(g=>{
            return {ID:g.ID, name:g.post_title, label: g.label}
          })
        },
        callback: {
          onCancel: function (node, item) {
            $(`#${field_id}-spinner`).addClass('active')
            API.update_post(post_type, post_id, {[field_id]: {values:[{value:item.ID, delete:true}]}}).then(()=>{
              $(`#${field_id}-spinner`).removeClass('active')
            }).catch(err => { console.error(err) })
          }
        },
        href: function (item) {
          if (listing_post_type === 'peoplegroups') {
            return null;
          } else {
            return window.wpApiShare.site_url + `/${listing_post_type}/${item.ID}`
          }
        }
      },
      callback: {
        onClick: function(node, a, item, event){
          $(`#${field_id}-spinner`).addClass('active')
          API.update_post(post_type, post_id, {[field_id]: {values:[{"value":item.ID}]}}).then(new_post=>{
            $(`#${field_id}-spinner`).removeClass('active')
            $( document ).trigger( "dt-post-connection-added", [ new_post, field_id ] );
          }).catch(err => { console.error(err) })
          this.addMultiselectItemLayout(item)
          event.preventDefault()
          this.hideLayout();
          this.resetInput();
          masonGrid.masonry('layout')
        },
        onResult: function (node, query, result, resultCount) {
          let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $(`#${field_id}-result-container`).html(text);
        },
        onHideLayout: function (event, query) {
          if ( !query ){
            $(`#${field_id}-result-container`).empty()
          }
          masonGrid.masonry('layout')
        },
        onShowLayout (){
          masonGrid.masonry('layout')
        }
      }
    })
  })

  //multi_select typeaheads
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
        data: function (){
          return (post[field] || [] ).map(g=>{
            return {key:g, value:window.lodash.get(field_settings, `${field}.default.${g}.label`, g)}
          })
        },
        callback: {
          onCancel: function (node, item, event) {
            $(`#${field}-spinner`).addClass('active')
            API.update_post(post_type, post_id, {[field]: {values:[{value:item.key, delete:true}]}}).then((new_post)=>{
              $(`#${field}-spinner`).removeClass('active')
              this.hideLayout();
              this.resetInput();
              $( document ).trigger( "dt_multi_select-updated", [ new_post, field ] );
            }).catch(err => { console.error(err) })
          }
        }
      },
      callback: {
        onClick: function(node, a, item, event){
          $(`#${field}-spinner`).addClass('active')
          API.update_post(post_type, post_id, {[field]: {values:[{"value":item.key}]}}).then(new_post=>{
            $(`#${field}-spinner`).removeClass('active')
            $( document ).trigger( "dt_multi_select-updated", [ new_post, field ] );
            this.addMultiselectItemLayout(item)
            event.preventDefault()
            this.hideLayout();
            this.resetInput();
          }).catch(err => { console.error(err) })
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


  let connection_type = null
  //new record off a typeahead
  $('.create-new-record').on('click', function(){
    connection_type = $(this).data('connection-key');
    $('#create-record-modal').foundation('open');
    $('.js-create-record .error-text').empty();
    $(".js-create-record-button").attr("disabled", false).removeClass("alert")
    $(".reveal-after-record-create").hide()
    $(".hide-after-record-create").show()
    $(".js-create-record input[name=title]").val('')
    //create new record
  })
  $(".js-create-record").on("submit", function(e) {
    e.preventDefault();
    $(".js-create-record-button").attr("disabled", true).addClass("loading");
    let title = $(".js-create-record input[name=title]").val()
    if ( !connection_type){
      $(".js-create-record .error-text").text(
        "Something went wrong. Please refresh and try again"
      );
      return;
    }
    let update_field = connection_type;
    API.create_post( field_settings[update_field].post_type, {
      title,
    }).then((newRecord)=>{
      return API.update_post( post_type, post_id, { [update_field]: { values: [ { value:newRecord.ID }]}}).then(response=>{
        $(".js-create-record-button").attr("disabled", false).removeClass("loading");
        $(".reveal-after-record-create").show()
        $("#new-record-link").html(`<a href="${window.lodash.escape( newRecord.permalink )}">${window.lodash.escape( title )}</a>`)
        $(".hide-after-record-create").hide()
        $('#go-to-record').attr('href', window.lodash.escape( newRecord.permalink ));
        post = response
        $( document ).trigger( "dt-post-connection-created", [ post, update_field ] );
        if ( Typeahead[`.js-typeahead-${connection_type}`] ){
          Typeahead[`.js-typeahead-${connection_type}`].addMultiselectItemLayout({ID:newRecord.ID.toString(), name:title})
          masonGrid.masonry('layout')
        }
      })
    })
    .catch(function(error) {
      $(".js-create-record-button").removeClass("loading").addClass("alert");
      $(".js-create-record .error-text").text(
        window.lodash.get( error, "responseJSON.message", "Something went wrong. Please refresh and try again" )
      );
      console.error(error);
    });
  })

  $('.dt_location_grid').each((key, el)=> {
    let field_id = $(el).data('id') || 'location_grid'
    $.typeahead({
      input: `.js-typeahead-${field_id}`,
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
                return window.lodash.get(window.Typeahead[`.js-typeahead-${field_id}`].filters.dropdown, 'value', 'all')
              }
            },
            beforeSend: function (xhr) {
              xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
            },
            callback: {
              done: function (data) {
                if (typeof typeaheadTotals!=="undefined") {
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
        data: function () {
          return (post[field_id] || []).map(g => {
            return {ID: g.id, name: g.label}
          })

        }, callback: {
          onCancel: function (node, item) {
            API.update_post(post_type, post_id, {[field_id]: {values:[{value:item.ID, delete:true}]}})
            .catch(err => { console.error(err) })
          }
        }
      },
      callback: {
        onClick: function (node, a, item, event) {
          API.update_post(post_type, post_id, {[field_id]: {values:[{"value":item.ID}]}}).catch(err => { console.error(err) })
          this.addMultiselectItemLayout(item)
          event.preventDefault()
          this.hideLayout();
          this.resetInput();
          masonGrid.masonry('layout')
        },
        onReady() {
          this.filters.dropdown = {key: "group", value: "focus", template: window.lodash.escape(window.wpApiShare.translations.regions_of_focus)}
          this.container
          .removeClass("filter")
          .find("." + this.options.selector.filterButton)
          .html(window.lodash.escape(window.wpApiShare.translations.regions_of_focus));
        },
        onResult: function (node, query, result, resultCount) {
          resultCount = typeaheadTotals[field_id]
          let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $(`#${field_id}-result-container`).html(text);
        },
        onHideLayout: function () {
          $(`#${field_id}-result-container`).html("");
        }
      }
    });
  })

  /**
   * Follow
   */
  $('button.follow').on("click", function () {
    let following = !($(this).data('value') === "following")
    $(this).data("value", following ? "following" : "" )
    if ($(this).hasClass('mobile')) {
      $(this).html( following ? "<i class='fi-eye'></i>" : "<i class='fi-eye'></i>")
    } else {
      $(this).html( following ? "Following <i class='fi-eye'></i>" : "Follow <i class='fi-eye'></i>")
    }
    $(this).toggleClass( "hollow" )
    let update = {
      follow: {values:[{value:window.detailsSettings.current_user_id, delete:!following}]},
      unfollow: {values:[{value:window.detailsSettings.current_user_id, delete:following}]}
    }
    rest_api.update_post( post_type, post_id, update )
  })



  /**
   * Share
   */
  let shareTypeahead = null
  $('.open-share').on("click", function(){
    $('#share-contact-modal').foundation('open');
    if  (!shareTypeahead) {
      shareTypeahead = TYPEAHEADS.share(post_type, post_id, !['contacts', 'groups'].includes(window.detailsSettings.post_type ) )
    }
  })



  let build_task_list = ()=>{
    let tasks = window.lodash.sortBy(post.tasks || [], ['date']).reverse()
    let html = ``
    tasks.forEach(task=>{
      let task_done = ( task.category === "reminder" && task.value.notification === 'notification_sent' )
                      || ( task.category !== "reminder" && task.value.status === 'task_complete' )
      let show_complete_button = task.category !== "reminder" && task.value.status !== 'task_complete'
      let task_row = `<strong>${window.lodash.escape( moment(task.date).format("MMM D YYYY") )}</strong> `
      if ( task.category === "reminder" ){
        task_row += window.lodash.escape( window.detailsSettings.translations.reminder )
        if ( task.value.note ){
          task_row += ' ' + window.lodash.escape(task.value.note)
        }
      } else {
         task_row += window.lodash.escape(task.value.note || window.detailsSettings.translations.no_note )
      }
      html += `<li>
        <span style="${task_done ? 'text-decoration:line-through' : ''}">
        ${task_row}
        ${ show_complete_button ? `<button type="button" data-id="${window.lodash.escape(task.id)}" class="existing-task-action complete-task">${window.lodash.escape(window.detailsSettings.translations.complete).toLowerCase()}</button>` : '' }
        <button type="button" data-id="${window.lodash.escape(task.id)}" class="existing-task-action remove-task" style="color: red;">${window.lodash.escape(window.detailsSettings.translations.remove).toLowerCase()}</button>
      </li>`
    })
    if (!html ){
      $('#tasks-modal .existing-tasks').html(`<li>${window.lodash.escape(window.detailsSettings.translations.no_tasks)}</li>`)
    } else {
      $('#tasks-modal .existing-tasks').html(html)
    }

    $('.complete-task').on("click", function () {
      $('#tasks-spinner').addClass('active')
      let id = $(this).data('id')
      API.update_post(post_type, post_id, {
          "tasks": { values: [ { id, value: {status: 'task_complete'}, } ] }
      }).then(resp => {
        post = resp
        build_task_list()
        $('#tasks-spinner').removeClass('active')
      })
    })
    $('.remove-task').on("click", function () {
      $('#tasks-spinner').addClass('active')
      let id = $(this).data('id')
      API.update_post(post_type, post_id, {
          "tasks": { values: [ { id, delete: true } ] }
      }).then(resp => {
        post = resp
        build_task_list()
        $('#tasks-spinner').removeClass('active')
      })
    })
  }
  //open the create task modal
  $('.open-set-task').on( "click", function () {
    $('.js-add-task-form .error-text').empty();
    build_task_list()
    $('#tasks-modal').foundation('open');
  })
  $('#task-custom-text').on('click', function () {
    $('input:radio[name="task-type"]').filter('[value="custom"]').prop('checked', true);
  })
  $('#create-task-date').daterangepicker({
    "singleDatePicker": true,
    // "autoUpdateInput": false,
    // "timePicker": true,
    // "timePickerIncrement": 60,
    "locale": {
      "format": "YYYY/MM/DD",
      "separator": " - ",
      "daysOfWeek": window.wpApiShare.translations.days_of_the_week,
      "monthNames": window.wpApiShare.translations.month_labels,
    },
    "firstDay": 1,
    "startDate": moment().add(1, "day"),
    "opens": "center",
    "drops": "down"
  });
  let task_note = $('#tasks-modal #task-custom-text')
  //submit the create task form
  $(".js-add-task-form").on("submit", function(e) {
    e.preventDefault();
    $("#create-task")
      .attr("disabled", true)
      .addClass("loading");
    let date = $('#create-task-date').data('daterangepicker').startDate
    let note = task_note.val()
    let task_type = $('#tasks-modal input[name="task-type"]:checked').val()
    API.update_post(post_type, post_id, {
      "tasks":{
        values: [
          {
            date: date.startOf('day').add(8, "hours").format(), //time 8am
            value: {note: note},
            category: task_type
          }
        ]
      }
    }).then( resp => {
      post = resp
      $("#create-task")
      .attr("disabled", false)
      .removeClass("loading");
      task_note.val('')
      $('#tasks-modal').foundation('close');
    }).catch(err => {
      $("#create-task")
      .attr("disabled", false)
      .removeClass("loading");
      $('.js-add-task-form .error-text').html(window.lodash.escape(window.lodash.get(err, "responseJSON.message")));
      console.error(err)
    })
  })


  /**
   * Tags
   */
  if( $('.js-typeahead-tags').length ) {
    $.typeahead({
      input: '.js-typeahead-tags',
      minLength: 0,
      maxItem: 20,
      searchOnFocus: true,
      source: {
        tags: {
          display: ["name"],
          ajax: {
            url: window.wpApiShare.root + `dt-posts/v2/${post_type}/multi-select-values`,
            data: {
              s: "{{query}}",
              field: "tags"
            },
            beforeSend: function (xhr) {
              xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
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
      dynamic: true,
      multiselect: {
        matchOn: ["name"],
        data: function () {
          return (post.tags || []).map(t => {
            return {name: t}
          })
        }, callback: {
          onCancel: function (node, item) {
            API.update_post(post_type, post_id, {'tags': {values: [{value: item.name, delete: true}]}})
          }
        }
      },
      callback: {
        onClick: function (node, a, item, event) {
          API.update_post(post_type, post_id, {tags: {values: [{value: item.name}]}})
          this.addMultiselectItemLayout(item)
          event.preventDefault()
          this.hideLayout();
          this.resetInput();
          masonGrid.masonry('layout')
        },
        onResult: function (node, query, result, resultCount) {
          let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $('#tags-result-container').html(text);
          masonGrid.masonry('layout')
        },
        onHideLayout: function () {
          $('#tags-result-container').html("");
          masonGrid.masonry('layout')
        },
        onShowLayout() {
          masonGrid.masonry('layout')
        }
      }
    });
    $("#create-tag-return").on("click", function () {
      let tag = $("#new-tag").val()
      Typeahead['.js-typeahead-tags'].addMultiselectItemLayout({name: tag})
      API.update_post(post_type, post_id, {tags: {values: [{value: tag}]}})
    })
  }


  let upgradeUrl = (url)=>{
    if ( !url.includes("http")){
      url = "https://" + url
    }
    if ( !url.startsWith(window.wpApiShare.template_dir)){
      url = url.replace( 'http://', 'https://' )
    }
    return url
  }

  let urlRegex = /[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/gi
  let protocolRegex = /^(?:https?:\/\/)?(?:www.)?/gi
  function resetDetailsFields(){
    window.lodash.forOwn( field_settings, (field_options, field_key)=>{

      if ( field_options.tile === 'details' && !field_options.hidden && post[field_key]){

        if ( field_options.only_for_types && ( post["type"] && !field_options.only_for_types.includes(post["type"].key) ) ){
          return
        }
        let field_value = window.lodash.get( post, field_key, false )
        let values_html = ``
        if ( field_options.type === 'text' ){
          values_html = window.lodash.escape( field_value )
        } else if ( field_options.type === 'date' ){
          values_html = window.lodash.escape( window.SHAREDFUNCTIONS.formatDate( field_value.timestamp ) )
        } else if ( field_options.type === 'key_select' ){
          values_html = window.lodash.escape( field_value.label )
        } else if ( field_options.type === 'multi_select' ){
          values_html = field_value.map(v=>{
            return `${window.lodash.escape( window.lodash.get( field_options, `default[${v}].label`, v ))}`;
          }).join(', ')
        } else if ( ['location', 'location_meta' ].includes(field_options.type) ){
          values_html = field_value.map(v=>{
            return window.lodash.escape(v.matched_search || v.label);
          }).join(' / ')
        } else if ( field_options.type === 'communication_channel' ){
          field_value.forEach((v, index)=>{
            if ( index > 0 ){
              values_html += ', '
            }
            let value = window.lodash.escape(v.value)
            if ( field_key === 'contact_phone' ){
              values_html += `<a dir="auto" href="tel:${value}" title="${value}">${value}</a>`
            } else if (field_key === "contact_email") {
              values_html += `<a dir="auto" href="mailto:${value}" title="${value}">${value}</a>`
            } else {
              let validURL = new RegExp(urlRegex).exec(value)
              let prefix = new RegExp(protocolRegex).exec(value)
              if (validURL && prefix) {
                let urlToDisplay = ""
                if (field_options.hide_domain && field_options.hide_domain===true) {
                  urlToDisplay = validURL[1] || value
                } else {
                  urlToDisplay = value.replace(prefix[0], "")
                }
                value = upgradeUrl(value)
                value = `<a href="${window.lodash.escape(value)}" target="_blank" >${window.lodash.escape(urlToDisplay)}</a>`
              }
              values_html += value
            }
          })
          let labels = field_value.map(v=>{
            return window.lodash.escape(v.value);
          }).join(', ')
          $(`#collapsed-detail-${field_key} .collapsed-items`).html(`<span title="${labels}">${values_html}</span>`)

        } else if ( ['connection'].includes(field_options.type) ){
          values_html = field_value.map(v=>{
            return window.lodash.escape(v.label);
          }).join(', ')
        }
        $(`#collapsed-detail-${field_key}`).toggle(values_html !== ``)
        if (field_options.type !== 'communication_channel') {
          $(`#collapsed-detail-${field_key} .collapsed-items`).html(`<span title="${values_html}">${values_html}</span>`)
        }
      }

    })
    $( document ).trigger( "dt_record_details_reset", [post] );
  }
  resetDetailsFields()

  $('#delete-record').on('click', function(){
    $(this).attr("disabled", true).addClass("loading");
    API.delete_post( post_type, post_id ).then(()=>{
      window.location = '/' + post_type
    })
  })
  $('#archive-record').on('click', function(){
    $(this).attr("disabled", true).addClass("loading");
    API.update_post( post_type, post_id, {overall_status:"closed"} ).then(()=>{
      $(this).attr("disabled", false).removeClass("loading");
      $('#archive-record-modal').foundation('close');
      $('.archived-notification').show()
    })
  })
  $('#unarchive-record').on('click', function(){
    $(this).attr("disabled", true).addClass("loading");
    API.update_post( post_type, post_id, {overall_status:"active"} ).then(()=>{
      $(this).attr("disabled", false).removeClass("loading");
      $('.archived-notification').hide()
    })
  })

  //autofocus the first input when a modal is opened.
  $(".reveal").on("open.zf.reveal", function () {
    const firstField = $(this).find("input").filter(
      ":not([disabled],[hidden],[opacity=0]):visible:first"
    );
    if (firstField.length !== 0) {
      firstField.focus();
    }
  });

  //leave at the end of this file
  masonGrid.masonry({
    itemSelector: '.grid-item',
    percentPosition: true
  });
  //leave at the end of this file
})


// change update needed notification and switch if needed.
function record_updated(updateNeeded) {
  $('.update-needed-notification').toggle(updateNeeded)
  $('.update-needed').prop("checked", updateNeeded)
}
