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

  /**
   * Assigned_to
   */
  let assigned_to_input = $(`.js-typeahead-assigned_to`)
  if ( assigned_to_input.length ){
    $.typeahead({
      input: '.js-typeahead-assigned_to',
      minLength: 0,
      maxItem: 0,
      accent: true,
      searchOnFocus: true,
      source: TYPEAHEADS.typeaheadUserSource(),
      templateValue: "{{name}}",
      template: function (query, item) {
        return `<div class="assigned-to-row" dir="auto">
          <span>
              <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
              ${window.lodash.escape( item.name )}
          </span>
          ${ item.status_color ? `<span class="status-square" style="background-color: ${window.lodash.escape(item.status_color)};">&nbsp;</span>` : '' }
          ${ item.update_needed && item.update_needed > 0 ? `<span>
            <img style="height: 12px;" src="${window.lodash.escape( window.wpApiShare.template_dir )}/dt-assets/images/broken.svg"/>
            <span style="font-size: 14px">${window.lodash.escape(item.update_needed)}</span>
          </span>` : '' }
        </div>`
      },
      dynamic: true,
      hint: true,
      emptyTemplate: window.lodash.escape(window.wpApiShare.translations.no_records_found),
      callback: {
        onClick: function(node, a, item){
          API.update_post('contacts', post_id, {assigned_to: 'user-' + item.ID}).then(function (response) {
            window.lodash.set(post, "assigned_to", response.assigned_to)
            setStatus(response)
            assigned_to_input.val(post.assigned_to.display)
            assigned_to_input.blur()
          }).catch(err => { console.error(err) })
        },
        onResult: function (node, query, result, resultCount) {
          let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $('#assigned_to-result-container').html(text);
        },
        onHideLayout: function () {
          $('.assigned_to-result-container').html("");
        },
        onReady: function () {
          if (window.lodash.get(post,  "assigned_to.display")){
            $('.js-typeahead-assigned_to').val(post.assigned_to.display)
          }
        }
      },
    });
    $('.search_assigned_to').on('click', function () {
      assigned_to_input.val("")
      assigned_to_input.trigger('input.typeahead')
      assigned_to_input.focus()
    })
  }

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
