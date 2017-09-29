function change_notification_preference( preference_key ){
  let data = { "preference_key": preference_key }
  return jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettingsPage.root + 'dt/v1/users/change_notification_preference',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettingsPage.nonce);
    },
  })
}

function change_availability( ){
  return jQuery.ajax({
    type: "POST",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettingsPage.root + 'dt/v1/users/change_availability',
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettingsPage.nonce);
    }
  })
}
