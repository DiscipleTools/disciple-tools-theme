/* Functions to support the notifications system. */
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
      jQuery('#toggle-area-'+notification_id).html(`<a id="read-button-` + notification_id + `" class="read-button button hollow small" style="border-radius:100px; margin: .7em 0 0;"
                  onclick="mark_unread( ` + notification_id + ` )">
                  <i class="fi-minus hollow"></i>
               </a>`)

    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}

function mark_unread(notification_id){
  return jQuery.ajax({
    type: "POST",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiNotifications.root + 'dt/v1/notifications/mark_unread/'+notification_id,
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiNotifications.nonce);
    },
  })
    .done(function (data) {
      get_new_notification_count()
      jQuery('#toggle-area-'+notification_id).html(`<a id="new-button-` + notification_id + `" class="new-button button small" style="border-radius:100px; margin: .7em 0 0;"
                  onclick="mark_viewed( ` + notification_id + ` )">
                  <i class="fi-check"></i>
               </a>`)
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
      jQuery('.new-cell').html(`<a id="read-button-` + id + `" class="read-button button hollow small" style="border-radius:100px; margin: .7em 0 0;"
                  onclick="mark_unread( ` + id + ` )">
                  <i class="fi-minus hollow"></i>
               </a>`)
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}

function notification_template( id, note, is_new, pretty_time ) {
  "use strict";
  let button = ``
  let label = `` // used by the mark_all_viewed()

  if ( is_new === '1' ) {
    button = `<a id="new-button-` + id + `" class="new-button button small" style="border-radius:100px; margin: .7em 0 0;"
                  onclick="mark_viewed( ` + id + ` )">
                  <i class="fi-check"></i>
               </a>`;
    label = `new-cell` // used by the mark_all_viewed()
  } else {
    button = `<a id="read-button-` + id + `" class="read-button button hollow small" style="border-radius:100px; margin: .7em 0 0;"
                  onclick="mark_unread( ` + id + ` )">
                  <i class="fi-minus hollow"></i>
               </a>`;
  }

  return `
            <div class="cell" id="row-` + id + `">
              <div class="grid-x grid-margin-x grid-padding-y bottom-border">
                <div class="cell medium-1 hide-for-small-only">
                    <img src="http://via.placeholder.com/50x50?text=icon" width="50px" height="50px"/>
                </div>
                <div class="auto cell">
                   ` + note + `<br>
                   <span class="grey">` + pretty_time + `</span>
                </div>
                <div class="small-2 medium-1 cell padding-5 ` + label + `" id="toggle-area-` + id + `">
                    ` + button + `
                </div>
              </div>
            </div>`
}

/* Variables for get_notifications */
let all = true
let all_offset
let new_offset
let page
let limit = 20

function get_notifications( all, reset) {

  /* Processing the offset of the query request. Using the limit variable to increment the sql offset. */
  if ( all === true ) {
    new_offset = 0
    if (all_offset === 0 || !all_offset) {
      page = 0
      all_offset = limit
    }
    else if (all_offset === limit) {
      page = limit
      all_offset = limit + limit
    }
    else {
      page = all_offset + limit
      all_offset = all_offset + limit
    }
  } else {
    all_offset = 0
    if (new_offset === 0 || !new_offset) {
      page = 0
      new_offset = limit
    }
    else if (new_offset === limit) {
      page = limit
      new_offset = limit + limit
    }
    else {
      page = new_offset + limit
      new_offset = new_offset + limit
    }
  }

  /* query for the data */
  let data = {"all": all, "page": page, "limit": limit}
  return jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiNotifications.root + 'dt/v1/notifications/get_notifications',
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiNotifications.nonce);
    }
  })
    .done(function (data) { // return notifications if query successful
      if (data) {

        if (reset) {
          jQuery('#notification-list').empty()
        }

        jQuery.each(data, function (i, item) {
          jQuery('#notification-list').append(notification_template(data[i].id, data[i].notification_note, data[i].is_new, data[i].pretty_time))
        })
      }
      else if (( all === true && (all_offset === 0 || !all_offset ) ) || all === false && (new_offset === 0 || !new_offset)) { // determines if this is the first query (offset 0) and there is nothing returned.

        jQuery('#notification-list').html('<div class="cell center">Nothing here! :)</div>')
        jQuery('#next-all').hide()
        jQuery('#next-new').hide()

      } else { // therefore if no data is returned, but this is not the first query, then just remove the option to load more content

        jQuery('#next-all').hide()
        jQuery('#next-new').hide()

      }
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
}

function toggle_buttons( state ) {
  if ( state === 'all' ) {
    jQuery('#all').attr('class', 'button')
    jQuery('#new').attr('class', 'button hollow')
    jQuery('#next-all').show()
    jQuery('#next-new').hide()
  } else {
    jQuery('#all').attr('class', 'button hollow')
    jQuery('#new').attr('class', 'button')
    jQuery('#next-all').hide()
    jQuery('#next-new').show()
  }

}

