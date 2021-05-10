let post_id = window.detailsSettings.post_id
let post_type = window.detailsSettings.post_type
let post = window.detailsSettings.post_fields

function setStatus(contact, openModal) {
  let statusSelect = $('#overall_status')
  let status = window.lodash.get(contact, "overall_status.key")
  let reasonLabel = window.lodash.get(contact, `reason_${status}.label`)
  let statusColor = window.lodash.get(window.detailsSettings,
    `post_settings.fields.overall_status.default.${status}.color`
  )
  statusSelect.val(status)

  if (openModal){
    if (status === "paused"){
      $('#paused-contact-modal').foundation('open');
    } else if (status === "closed"){
      $('#closed-contact-modal').foundation('open');
    } else if (status === 'unassignable'){
      $('#unassignable-contact-modal').foundation('open');
    }
  }

  if (statusColor){
    statusSelect.css("background-color", statusColor)
  } else {
    statusSelect.css("background-color", "#366184")
  }

  if (["paused", "closed", "unassignable"].includes(status)){
    $('#reason').text(`(${reasonLabel})`)
    $(`#edit-reason`).show()
  } else {
    $('#reason').text(``)
    $(`#edit-reason`).hide()
  }
}

function updateCriticalPath(key) {
  $('#seeker_path').val(key)
  let seekerPathKeys = window.lodash.keys(post.seeker_path.default)
  let percentage = (window.lodash.indexOf(seekerPathKeys, key) || 0) / (seekerPathKeys.length-1) * 100
  $('#seeker-progress').css("width", `${percentage}%`)
}


jQuery(document).ready(function($) {


  $( document ).on( 'dt_record_updated', function (e, response, request ){
    post = response
    window.lodash.forOwn(request, (val, key)=>{
      if (key.indexOf("quick_button")>-1){
        if (window.lodash.get(response, "seeker_path.key")){
          updateCriticalPath(response.seeker_path.key)
        }
      }
    })
  })
  $('#content')[0].addEventListener('comment_posted', function (e) {
    if ( $('.update-needed').prop("checked") === true ){
      API.get_post("contacts",  post_id ).then(resp=>{
        post = resp
        record_updated(window.lodash.get(resp, "requires_update") === true )
      }).catch(err => { console.error(err) })
    }
  }, false);

  $( document ).on( 'select-field-updated', function (e, newContact, id, val) {
    if (id === 'seeker_path') {
      // updateCriticalPath(newContact.seeker_path.key)
      // refresh_quick_action_buttons(newContact)
    } else if (id === 'reason_unassignable') {
      setStatus(newContact)
    } else if (id === 'overall_status') {
      setStatus(newContact, true)
    }
  })
   //confirm setting a reason for a status.
  let confirmButton = $(".confirm-reason-button")
  confirmButton.on("click", function () {
    let field = $(this).data('field')
    let select = $(`#reason-${field}-options`)
    $(this).toggleClass('loading')
    let data = {overall_status:field}
    data[`reason_${field}`] = select.val()
    API.update_post('contacts', post_id, data).then(contactData=>{
      $(this).toggleClass('loading')
      $(`#${field}-contact-modal`).foundation('close')
      setStatus(contactData)
    }).catch(err => { console.error(err) })
  })

  $('#edit-reason').on('click', function () {
    setStatus(post, true)
  })
  /**
   * Accept or decline a contact
   */
  $('.accept-decline').on('click', function () {
    let action = $(this).data("action")
    let data = {accept:action === "accept"}
    makeRequestOnPosts( "POST", `contacts/${post_id}/accept`, data)
    .then(function (resp) {
      setStatus(resp)
      jQuery('#accept-contact').hide()
    }).catch(err=>{
      console.log('error')
      console.log(err.responseText)
    })
  })

})
