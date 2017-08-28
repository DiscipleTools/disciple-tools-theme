/* global jQuery:false, wpApiSettings:false */

jQuery.ajaxSetup({
  beforeSend: function(xhr) {
    xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
  },
})


function save_seeker_milestones(contactId, fieldKey, fieldValue){
  var data = {}
  var field = jQuery("#" + fieldKey)
  field.addClass("submitting-select-button")
  if (field.hasClass("selected-select-button")){
    fieldValue = "no"
  } else {
    field.removeClass("empty-select-button")
    field.addClass("selected-select-button")
    fieldValue = "yes"
  }
  data[fieldKey] = fieldValue
  jQuery.ajax({
    type:"POST",
    data:JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/'+contactId,

    success: function(data) {
      field.removeClass("submitting-select-button selected-select-button")
      field.addClass( fieldValue === "no" ? "empty-select-button" : "selected-select-button")
      get_activity(contactId)
    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").text(err.responseText)
      field.removeClass("submitting-select-button selected-select-button")
      field.addClass( fieldValue === "yes" ? "empty-select-button" : "selected-select-button")
    },
  })
}
function save_quick_action(contactId, fieldKey){
  var data = {}
  var numberIndicator = jQuery("." + fieldKey +  " span")
  var newNumber = parseInt(numberIndicator.first().text()) + 1
  data[fieldKey] = newNumber
  jQuery.ajax({
    type:"POST",
    data:JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/'+ contactId +'/quick_action_button',
    success: function(data, two, three) {
      console.log("updated " + fieldKey + " to: " + newNumber)
      if (fieldKey.indexOf("quick_button")>-1){
        if (data.seeker_path){
          jQuery("#current_seeker_path").text(data.seeker_path.current)
          if (data.seeker_path.next){
            jQuery("#next_seeker_path").text(data.seeker_path.next)
          }
        }
      }
      get_activity(contactId)
    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })

  if (fieldKey.indexOf("quick_button")>-1){
    numberIndicator.text(newNumber)
  }
}

function post_comment(contactId) {
  jQuery("#add-comment-button").toggleClass('loading')
  let comment = jQuery("#comment-input").val()
  console.log(comment);
  var data = {}
  data["comment"] = comment
  jQuery.ajax({
    type:"POST",
    data:JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/'+ contactId +'/comment',
    success: function(data, two, three) {
      console.log(data)
      console.log(`added comment ${comment}`)
      let commentsWrapper = $("#comments-wrapper")
      jQuery("#comment-input").val("")
      jQuery("#add-comment-button").toggleClass('loading')
      commentsWrapper.prepend(commentTemplate({
        date:data.comment.comment_date,
        comment:data.comment.comment_content,
        name:data.comment.comment_author
      }))
    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })
}

var commentTemplate = _.template(`
  <div>
    <div><span><strong><%- name %></strong></span> <span class="comment-date"> <%- date %> </span></div>
    <p class="comment-bubble"> <%- comment %></p>
  </div>`
)
var activityTemplate = _.template(`
  <div>
    <div><span><strong><%- name %></strong></span> <span class="comment-date"> <%- date %> </span></div>
    <!--<p class=""><strong><%- activity_key %> </strong> <%- value %></p>-->
    <p class="comment-bubble"> <%- activity %></p>
  </div>`
)

let comments = []
let activity = []
jQuery(document).ready(function($) {
  let id = $("#contact-id").text()


  jQuery.when(
    jQuery.ajax({
      type:"GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + 'dt-hooks/v1/contact/'+ id +'/comments',
      success: function(data) {
        data.forEach(comment=>{
          comment.date = new Date(comment.comment_date)
        })
        comments = data
      },
      error: function(err) {
        console.log("error")
        console.log(err)
        jQuery("#errors").append(err.responseText)
      },
    }),
    jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + 'dt-hooks/v1/contact/' + id + "/activity",
    })
      .done(function (data) {
        data.forEach(d=>{
          d.date = new Date(d.hist_time*1000)
        })
        activity = data
      })
      .fail(function (err) {
        console.log("error")
        console.log(err)
        jQuery("#errors").append(err.responseText)
      })

  ).then(function () {
    console.log("done")
    display_activity_comment("all")
  })
})

function get_activity(id){
  return jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/' + id + "/activity",
  })
    .done(function (data) {
      data.forEach(d=>{
        d.date = new Date(d.hist_time*1000)
      })
      activity = data
      display_activity_comment()
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}


function formatDate(date) {
  var hours = date.getHours();
  var minutes = date.getMinutes();
  var ampm = hours >= 12 ? 'pm' : 'am';
  hours = hours % 12;
  hours = hours ? hours : 12; // the hour '0' should be '12'
  minutes = minutes < 10 ? '0'+minutes : minutes;
  var strTime = hours + ':' + minutes + ' ' + ampm;
  var month = date.getMonth()+1
  month = month < 10 ? "0"+month.toString() : month
  return date.getFullYear() + "/" + date.getDate() + "/" + month + "  " + strTime;
}

let current_section = "all"
function display_activity_comment(section) {
  current_section = section || current_section

  let commentsWrapper = $("#comments-wrapper")
  commentsWrapper.empty()
  let displayed = []
  if (current_section === "all"){
    displayed = _.union(comments, activity)
  } else if (current_section === "comments"){
    displayed = comments
  } else if ( current_section === "activity"){
    displayed = activity
  }
  displayed = _.orderBy(displayed, "date", "desc")
  displayed.forEach(d=>{
    let c = ""
    if (d.comment_content){
      c = commentTemplate({
        name: d.comment_author,
        date:formatDate(d.date),
        comment:d.object_note ?  `<strong>${d.meta_key}</strong>` : d.comment_content
      })
    } else {
      c = activityTemplate({
        name: d.name,
        date:formatDate(d.date),
        activity: d.object_note,
        activity_key:d.meta_key,
        value: d.meta_value
      })
    }
    commentsWrapper.append(c)
  })
}



function edit_fields() {
  jQuery(".display-fields").toggle()
  jQuery(".edit-fields").toggle()
}

function save_field(contactId, fieldKey, inputId){
  let field = jQuery("#"+ (inputId || fieldKey))
  let val = field.val()
  let data = {}
  data[fieldKey] = val
  jQuery.ajax({
    type:"POST",
    data:JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/'+ contactId,
    success: function(data) {
      console.log("updated " + fieldKey + " to: " + val)
      if (fieldKey === "assigned_to"){
        jQuery('#assigned-to').text(field.find(`option:selected`).text())
        jQuery('.assigned_to_select').val(val)
      }
      get_activity(contactId)
    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })
}

function add_contact_detail(contactId, fieldKey, callback){
  var input = jQuery("#"+fieldKey)
  var data = {}
  data[fieldKey] = input.val()
  jQuery.ajax({
    type:"POST",
    data:JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/'+ contactId + '/details',
    success: function(data) {
      if (data != contactId && fieldKey.indexOf("new-")>-1){
        input.removeAttr('onchange');
        input.attr('id', data)
        input.change(function () {
          save_field(contactId, data)
        })
      }
      callback(data)
      console.log("updated " + fieldKey + " to: " + input.val())
      get_activity(contactId)
    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })
}

function update_contact_method_detail(contactId, fieldKey, values, callback) {
  let data = {key: fieldKey, values: values}
  jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/' + contactId + '/details_update',
    success: function (data) {
      console.log("updated " + fieldKey + " to: " + JSON.stringify(values))
      callback(data)
      get_activity(contactId)
    },
    error: function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })
}


function remove_contact_detail(contactId, fieldKey, valueId, callback) {
  let data = {key: fieldKey, value: valueId}
  jQuery.ajax({
    type: "DELETE",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/' + contactId + '/details',
    success: function (data) {
      console.log(data)
      if (data!==false){
        console.log("delete " + fieldKey + " at: " + JSON.stringify(valueId))
        callback(data)
      }
      get_activity(contactId)
    },
    error: function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })
}



function add_contact_input(contactId, inputId, listId){
  if (jQuery(`#${inputId}`).length === 0 ){
    var newInput = `<li><input id="${inputId}" onchange="add_contact_detail(${contactId},'${inputId}', function(){})"\>`
    jQuery(`#${listId}`).append(newInput)
  }
}

function verify_contact_method(contactId, fieldId) {
  update_contact_method_detail(contactId, fieldId, {"verified":true}, function (){
    jQuery(`#${fieldId}-verified`).show()
    jQuery(`#${fieldId}-verify`).hide()
  })
}

function invalidate_contact_method(contactId, fieldId) {
  update_contact_method_detail(contactId, fieldId, {"invalid":true}, function (){
    jQuery(`#${fieldId}-invalid`).show()
    jQuery(`#${fieldId}-invalidate`).hide()
  })
}

function add_location(contactId, fieldId) {
  let select = jQuery(`#${fieldId}`)
  if (select.val() !== "0"){
    add_contact_detail(contactId, fieldId, function (location){
      select.val("0")
      jQuery(".locations-list").append(`<li>
        <a href="${location.permalink}">${location.post_title}</a>
        <button class="details-remove-button edit-fields" onclick="remove_item(${contactId}, '${fieldId}', ${location.ID})">Remove</button>
      </li>`)
      select.find(`option[value='${location.ID}']`).remove()
    })
  }
}

function remove_item(contactId, fieldId, itemId){
  remove_contact_detail(contactId, fieldId, itemId, function () {
    jQuery(`.${fieldId}-list .${itemId}`).remove()
  })
}




function close_contact(contactId){
  jQuery("#confirm-close").toggleClass('loading')
  let reasonClosed = jQuery('#reason-closed-options')
  let data = {overall_status:"closed", "reason_closed":reasonClosed.val()}
  jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/' + contactId,
  })
    .done(function (data) {
      let closedLabel = wpApiSettings.contacts_custom_fields_settings.overall_status.default.closed;
      jQuery('#overall-status').text(closedLabel)
      jQuery("#confirm-close").toggleClass('loading')
      jQuery('#close-contact-modal').foundation('close')
      jQuery('#reason').text(`(${reasonClosed.find('option:selected').text()})`)
      get_activity(contactId)
    })
}

let confirmPauseButton = jQuery("#confirm-pause")
function pause_contact(contactId){
  confirmPauseButton.toggleClass('loading')
  let reasonPaused = jQuery('#reason-paused-options')
  let data = {overall_status:"paused", "reason_paused":reasonPaused.val()}
  jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/' + contactId,
  })
    .done(function (data) {
      let pausedLabel = wpApiSettings.contacts_custom_fields_settings.overall_status.default.paused;
      jQuery('#overall-status').text(pausedLabel)
      jQuery('#reason').text(`(${reasonPaused.find('option:selected').text()})`)
      jQuery('#pause-contact-modal').foundation('close')
      get_activity(contactId)
      confirmPauseButton.toggleClass('loading')
    })
}

/***
 * Connections
 */
function edit_connections() {
  console.log("edit_connections")
  jQuery(".connections-edit").toggle()
}

function add_input_item(contactId, fieldId) {
  let select = jQuery(`#${fieldId}`)
  console.log(select.val())
  if (select.val() !== "0"){
    add_contact_detail(contactId, fieldId, function (addedItem){
      console.log(addedItem)
      select.val("0")
      jQuery(`.${fieldId}-list`).append(`<li class="${addedItem.ID}">
        <a href="${addedItem.permalink}">${addedItem.post_title}</a>
        <button class="details-remove-button connections-edit" onclick="remove_item(${contactId}, '${fieldId}', ${addedItem.ID})">Remove</button>
        </li>`)
      select.find(`option[value='${addedItem.ID}']`).remove()
      jQuery(".connections-edit").show()
    })

  }
}

function details_accept_contact(contactId, accept){
  console.log(contactId)

  let data = {accept:accept}
  jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/' + contactId + "/accept",
  })
    .done(function (data) {
      console.log(data)
      jQuery('#accept-contact').hide()
      if (data['overall_status']){
        jQuery('#overall-status').text(data['overall_status'])
      }
    }).error(err=>{
    console.log(err)
  })
}
