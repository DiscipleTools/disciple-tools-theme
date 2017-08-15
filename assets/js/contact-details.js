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
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function(data, two, three) {
      console.log(data)
      console.log(`added comment ${comment}`)
      let commentsWrapper = $("#comments-wrapper")
      jQuery("#comment-input").val("")
      jQuery("#add-comment-button").toggleClass('loading')
      commentsWrapper.prepend(commentTemplate({date:data.comment.comment_date,comment:data.comment.comment_content}))
    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })
}

var commentTemplate = _.template(`<div class="comment-date"> <%- date %> </div>
                                <p class="comment-bubble"> <%- comment %></p>`)

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
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      },
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
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
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

function display_activity_comment(section) {
  let commentsWrapper = $("#comments-wrapper")
  commentsWrapper.empty()
  let displayed = []
  if (section === "all"){
    displayed = _.union(comments, activity)
  } else if (section === "comments"){
    displayed = comments
  } else if ( section === "activity"){
    displayed = activity
  }
  displayed = _.orderBy(displayed, "date", "desc")
  displayed.forEach(d=>{
    let c = commentTemplate({date:formatDate(d.date), comment:d.object_note || d.comment_content})
    commentsWrapper.append(c)
  })
}



function edit_fields() {
  jQuery(".display-fields").toggle()
  jQuery(".edit-fields").toggle()
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
      console.log(data)
      if (data!==false){
        console.log("delete " + fieldKey + " at: " + JSON.stringify(valueId))
        callback(data)
      }
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
      jQuery("#confirm-close").toggleClass('loading')
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
