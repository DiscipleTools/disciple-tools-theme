/* global jQuery:false, List:false, wpApiSettings:false */


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
function save_quick_action(contactId, fieldKey, fieldValue){
  var data = {}
  data[fieldKey] = fieldValue
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
      console.log("updated " + fieldKey + " to: " + fieldValue)
      if (fieldKey.indexOf("contact_quick_button")>-1){
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

  if (fieldKey.indexOf("contact_quick_button")>-1){

    jQuery("#" + fieldKey +  " span").text(fieldValue)
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
    success: function(data, two, three) {
      console.log("updated " + fieldKey + " to: " + val)
    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })
}
