function switch_preference(preference_key) {
  let data = {"preference_key": preference_key}
  return jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettingsPage.root + 'dt/v1/users/switch_preference',
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettingsPage.nonce);
    },
  })
}



