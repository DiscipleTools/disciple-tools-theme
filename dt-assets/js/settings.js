function switch_preference (preference_key, type = null) {
  return makeRequest('post', 'users/switch_preference', { preference_key, type})
}

function change_password() {
  // test matching passwords
  const p1 = jQuery('#password1')
  const p2 = jQuery('#password2')
  const message = jQuery('#password-message')

  message.empty()

  if (p1.val() !== p2.val()) {
    message.append('Your passwords do not match')
    return
  }

  makeRequest('post', 'users/change_password', { password: p1 }).done(data => {
    console.log( data )
    message.html('Password changed!')
  }).fail(err => {
    console.log('Password reset error', err)
    message.html('Password not changed! ' + err.responseText)
    jQuery("#errors").append(err.responseText)
  })
}



