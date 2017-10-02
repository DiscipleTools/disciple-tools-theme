function get_new_notification_count(){
  return jQuery.ajax({
    type: "POST",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiNotifications.root + 'dt/v1/notifications/get_new_notifications_count',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiNotifications.nonce);
    },
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
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiNotifications.nonce);
    },
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
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiNotifications.nonce);
    },
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

/* TODO finish the template */
let notificationTemplate = _.template(`
    <div class="cell">
        <div class="grid-x grid-margin-x grid-padding-y bottom-border">
            <div class="cell medium-1 hide-for-small-only">
                <img src="http://via.placeholder.com/50x50?text=icon" width="50px" height="50px"/>
            </div>
            <div class="auto cell">
                 <%- note %>
            </div>
            
            <div class="small-2 medium-1 cell padding-5">
                 <a class="mark-viewed-<%- id %> button small" style="border-radius:100px; margin: .7em 0 0;">
                    <i class="fi-check"></i>
                 </a>
            </div>
            
        </div>                                 
    </div>`
)

/* TODO finish the notifications get call and template */
function get_notifications( limit, offset ) {
  "use strict";
  let data = { "limit": limit, "offset": offset }
  return jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiNotifications.root + 'dt/v1/notifications/get_notifications',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiNotifications.nonce);
    }
  })
    .done(function ( data ) {
      if( data ) {
        jQuery.each( data, function (i, item) {
          jQuery('#notification-list').append(notificationTemplate({
            note: data[i].notification_note,
            id: data[i].id,
            is_new: data[i].is_new
          }))
        })
      }
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}
get_notifications();



