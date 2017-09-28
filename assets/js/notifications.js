jQuery.ajaxSetup({
  beforeSend: function(xhr) {
    xhr.setRequestHeader('X-WP-Nonce', wpApiNotifications.nonce);
  },
})


function get_new_notification_count(){
  return jQuery.ajax({
    type: "POST",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiNotifications.root + 'dt/v1/notifications/get_new_notifications_count',
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
    url: wpApiNotifications.root + 'dt/v1/notifications/mark_viewed/'+notification_id,
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
  let id = wpApiNotifications.current_user_id
  return jQuery.ajax({
    type: "POST",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiNotifications.root + 'dt/v1/notifications/mark_all_viewed/'+id,
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





