/* global jQuery:false, wpApiSettings:false */

jQuery.ajaxSetup({
  beforeSend: function(xhr) {
    xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
  },
})


function get_notifications(limit = null, offset = null){
  let id = wpApiSettings.current_user_id
  let data = {limit: limit, offset: offset}
  return jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt/v1/notifications/' + id + "/get_notifications",
  })
    .done(function (data) {
      alert(data);
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}

function get_new_notification_count(){
  let id = wpApiSettings.current_user_id
  return jQuery.ajax({
    type: "POST",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt/v1/notifications/' + id + "/get_new_notifications_count",
  })
    .done(function (data) {
      if(data > 0) {
        jQuery('.notification-count').text(data).show()
      } else {
        jQuery('.notification-count').hide()
      }

    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}
get_new_notification_count()

function mark_viewed(notification_id){
  return jQuery.ajax({
    type: "POST",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt/v1/notifications/mark_viewed/'+notification_id,
  })
    .done(function (data) {
      get_new_notification_count()
      jQuery('#mark-viewed-'+notification_id).hide()
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}

function mark_all_viewed(){
  let id = wpApiSettings.current_user_id
  return jQuery.ajax({
    type: "POST",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt/v1/notifications/mark_all_viewed/'+id,
  })
    .done(function (data) {
      get_new_notification_count()
      jQuery('.mark-viewed').hide()
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}





