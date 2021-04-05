/* Functions to support the notifications system. */
function get_new_notification_count() {
  return makeRequest('post', 'notifications/get_new_notifications_count').done(data => {
    if (data > 0) {
      jQuery('.notification-count').text(data).show().css('display', 'inline-block')
      return
    }

    jQuery('.notification-count').hide()
  }).fail(handleAjaxError)
}

setTimeout(get_new_notification_count, 2000)

const notificationRead = notification_id => `
  <a class="read-button-${window.lodash.escape( notification_id )} read-button button hollow small" style="border-radius:100px; margin: 0;"
      onclick="mark_unread( ${window.lodash.escape( notification_id )} )">
      <!--<i class="fi-minus hollow"></i>-->
   </a>
`
const notificationNew = notification_id => `
  <a class="new-button-${window.lodash.escape( notification_id )} new-button button small" style="border-radius:100px; margin: 0;"
     onclick="mark_viewed( ${window.lodash.escape( notification_id )} )">
     <!--<i class="fi-check"></i>-->
  </a>
`

function mark_viewed (notification_id) {
  return makeRequest('post', 'notifications/mark_viewed/' + notification_id).done(() => {
    get_new_notification_count()
    jQuery(`.row-${notification_id} .notification-row`).removeClass("unread-notification-row")
    jQuery('.toggle-area-'+notification_id).html(notificationRead(notification_id))
    // TODO toggle the .new-cell class off
  }).fail(handleAjaxError)
}

function mark_unread (notification_id) {
  return makeRequest('post', 'notifications/mark_unread/' + notification_id).done(() => {
    get_new_notification_count()
    jQuery(`.row-${notification_id} .notification-row`).addClass("unread-notification-row")
    jQuery('.toggle-area-'+notification_id).html(notificationNew(notification_id))
    // TODO toggle the .new-cell class on
  }).fail(handleAjaxError)
}

function mark_all_viewed () {
  const id = wpApiNotifications.current_user_id

  return makeRequest('post', 'notifications/mark_all_viewed/' + id).done(() => {
    get_new_notification_count()
    jQuery('.new-cell').html(notificationRead(id))
    // TODO also change the backgrounds of the notifications to indicate their read status?
  }).fail(handleAjaxError)
}

const notification_group_header = (record_title) => `
<div class="cell notification-group-header">
    ${window.lodash.escape( record_title )}
</div>
`

function notification_template (id, note, is_new, pretty_time) {
  let button = ``
  let label = `` // used by the mark_all_viewed()

  if (is_new === '1') {
    button = notificationNew(id)
    label = `new-cell` // used by the mark_all_viewed()
  } else {
    button = notificationRead(id)
  }


  return `
  <div class="row-${id} cell" id="">
      <div class="grid-x grid-padding-x grid-padding-y bottom-border notification-row ${is_new ==='1' ? 'unread-notification-row' : ''} ">

        <div class="auto cell">
           ${note}<br>
           <span><small><strong>${window.lodash.escape(pretty_time[0])}</strong> | ${window.lodash.escape(pretty_time[1])}</small></span>
        </div>
        <div class="small-2 medium-1 cell padding-5 grid-x align-center align-middle ${window.lodash.escape( label )} toggle-area-${window.lodash.escape( id )}">
            ${button}
        </div>
      </div>
    </div>`
}

/* Variables for get_notifications */
let all = true
let all_offset
let new_offset
let page

function get_notifications (all, reset, dropdown = false, limit = 20) {
  /* Processing the offset of the query request. Using the limit variable to increment the sql offset. */
  if (all === true) {
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
  if (reset === true) {
    page = 0
  }else if (reset === false) {
    page += 1
  }

  const jQueryElementsDict = {
    'default' : {
      notificationList: jQuery('.notifications-page #notification-list'),
      nextAll: jQuery('.notifications-page #next-all'),
      nextNew: jQuery('.notifications-page #next-new'),
    },
    'dropdown': {
      notificationList: jQuery('#notification-dropdown #notification-list'),
      nextAll: null,
      nextNew: null,
    },
  }

  const jQueryElements = jQueryElementsDict[dropdown ? 'dropdown' : 'default']

  // return notifications if query successful
  return makeRequest('post', 'notifications/get_notifications', { all, page, limit }).done(data => {
    if (data) {
      if (reset) {
        jQueryElements.notificationList.empty()
      }

      const groupedData = groupNotificationsByDayAndRecord(data)

      groupedData.forEach((dayGroupedByRecord) => {
        Object.entries(dayGroupedByRecord).forEach(([record_title, notifications]) => {
          jQueryElements.notificationList.append(notification_group_header(record_title))

          notifications.forEach((notification) => {
            jQueryElements.notificationList.append(notification_template(notification.id, notification.notification_note, notification.is_new, notification.pretty_time))
          })
        })
      })
      /* jQuery.each(data, function (i, item) {
        jQueryElements.notificationList.append(notification_template(data[i].id, data[i].notification_note, data[i].is_new, data[i].pretty_time))
      }) */
    } else if (
      (all === true && (all_offset === 0 || !all_offset )) ||
      all === false && (new_offset === 0 || !new_offset))
    { // determines if this is the first query (offset 0) and there is nothing returned.
      jQueryElements.notificationList.html(`<div class="cell center empty-notification-message">${window.lodash.escape( wpApiNotifications.translations["no-notifications"] )}</div>`)
      jQueryElements.nextAll && jQueryElements.nextAll.hide()
      jQueryElements.nextNew && jQueryElements.nextNew.hide()
    } else { // therefore if no data is returned, but this is not the first query, then just remove the option to load more content
      if (reset) {
        jQueryElements.notificationList.html(`<div class="cell center empty-notification-message">${window.lodash.escape( wpApiNotifications.translations["no-unread"] )}</div>`)
      }

      jQueryElements.nextAll && jQueryElements.nextAll.hide()
      jQueryElements.nextNew && jQueryElements.nextNew.hide()
    }
  }).fail(handleAjaxError)
}

function groupNotificationsByDayAndRecord(data) {

  function getDate(dateString) {
    if (!dateString) return ''
    return dateString.split(' ')[0];
  }

  const sortedByDayAndRecord = []
  let currentDay = ''
  let daysNotifications = {}
  data.forEach(notif => {
    // data is already in date order, so we can go day by day
    const date = getDate(notif.date_notified)
    if (date !== currentDay ) {
      currentDay = date
      daysNotifications = {}
      sortedByDayAndRecord.push(daysNotifications)
    }
    if (!daysNotifications[notif.post_title]) {
      daysNotifications[notif.post_title] = []
    }
    daysNotifications[notif.post_title].push(notif)
  });

  return sortedByDayAndRecord
}

function toggle_buttons( state ) {
  if ( state === 'all' ) {
    jQuery('.notifications-page #all').attr('class', 'button')
    jQuery('.notifications-page #new').attr('class', 'button hollow')
    jQuery('.notifications-page #next-all').show()
    jQuery('.notifications-page #next-new').hide()
  } else {
    jQuery('.notifications-page #all').attr('class', 'button hollow')
    jQuery('.notifications-page #new').attr('class', 'button')
    jQuery('.notifications-page #next-all').hide()
    jQuery('.notifications-page #next-new').show()
  }
}

function toggle_dropdown_buttons( state ) {
  if ( state === 'all' ) {
    jQuery('#notification-dropdown #dropdown-all').attr('class', 'button')
    jQuery('#notification-dropdown #dropdown-new').attr('class', 'button hollow')
  } else {
    jQuery('#notification-dropdown #dropdown-all').attr('class', 'button hollow')
    jQuery('#notification-dropdown #dropdown-new').attr('class', 'button')
  }
}
