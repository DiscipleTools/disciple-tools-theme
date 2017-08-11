/* global jQuery:false, wpApiSettings:false */


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
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function(data) {
      field.removeClass("submitting-select-button selected-select-button")
      field.addClass( fieldValue === "no" ? "empty-select-button" : "selected-select-button")
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
  var numberIndicator = jQuery("#" + fieldKey +  " span")
  var newNumber = parseInt(numberIndicator.text()) + 1
  data[fieldKey] = newNumber
  jQuery.ajax({
    type:"POST",
    data:JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/'+ contactId +'/quick_action_button',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
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
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function(data, two, three) {
      console.log(`added comment ${comment}`)
      jQuery("#comment-input").val("")

    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })
}

jQuery(document).ready(function($) {
  let id = $("#contact-id").text()
  jQuery.ajax({
    type:"GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/'+ id +'/comments',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function(data) {
      let commentsWrapper = $("#comments-wrapper")
      data.forEach(comment=>{
        let html = `<div class="comment-date">${comment.comment_date}</div>
            <p class="comment-bubble">${comment.comment_content}</p>`
        commentsWrapper.append(html)
      })
    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })
})


function edit_fields() {
  jQuery("#display-fields").toggle()
  jQuery("#edit-fields").toggle()
}

function save_field(contactId, fieldKey){
  var val = jQuery("#"+fieldKey).val()
  console.log(val)
  var data = {}
  data[fieldKey] = val
  jQuery.ajax({
    type:"POST",
    data:JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/'+ contactId,
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function(data) {
      console.log("updated " + fieldKey + " to: " + val)
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
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
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
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function (data) {
      console.log("updated " + fieldKey + " to: " + JSON.stringify(values))
      callback(data)
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
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function (data) {
      console.log("delete " + fieldKey + " at: " + JSON.stringify(valueId))
      callback(data)
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
    var newInput = `<li><input id="${inputId}" onchange="add_contact_detail(${contactId},'${inputId}')"\>`
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

function add_location(contactID, fieldId) {
  let select = jQuery(`#${fieldId}`)
  if (select.val() !== "0"){
    add_contact_detail(contactID, fieldId, function (location){
      select.val("0")
      jQuery(".locations-list").append(`<li><a href="${location.permalink}">${location.post_title}</a></li>`)
      select.find(`option[value='${location.ID}']`).remove()
    })
  }
}

function remove_location(contactId, fieldId, locationId){
  remove_contact_detail(contactId, fieldId, locationId, function () {
    jQuery(`.locations-list .${locationId}`).remove()
  })
}

function close_contact(contactId){
  let reasonClosed = jQuery('#reason-closed-options').val()
  let data = {overall_status:"closed", "reason_closed":reasonClosed}
  console.log(reasonClosed)
  jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/' + contactId,
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    }
  })
    .done(function (data) {
      console.log(data)
      jQuery('#close-contact-modal').foundation('close')
    })
}

function pause_contact(contactId){
  let reasonClosed = jQuery('#reason-paused-options').val()
  let data = {overall_status:"paused", "reason_paused":reasonClosed}
  console.log(reasonClosed)
  jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/' + contactId,
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    }
  })
    .done(function (data) {
      console.log(data)
      jQuery('#close-contact-modal').foundation('close')
    })
}
