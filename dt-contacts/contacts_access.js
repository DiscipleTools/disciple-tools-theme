let post_id = window.detailsSettings.post_id
let post_type = window.detailsSettings.post_type
let post = window.detailsSettings.post_fields


function setStatus(contact, openModal) {
  let statusSelect = jQuery('#overall_status')
  let status = window.lodash.get(contact, "overall_status.key")
  let reasonLabel = window.lodash.get(contact, `reason_${status}.label`)
  let statusColor = window.lodash.get(window.detailsSettings,
    `post_settings.fields.overall_status.default.${status}.color`
  )
  statusSelect.val(status)

  if (openModal){
    if (status === "paused"){
      jQuery('#paused-contact-modal').foundation('open');
    } else if (status === "closed"){
      jQuery('#closed-contact-modal').foundation('open');
    } else if (status === 'unassignable'){
      jQuery('#unassignable-contact-modal').foundation('open');
    }
  }

  if (statusColor){
    statusSelect.css("background-color", statusColor)
  } else {
    statusSelect.css("background-color", "#366184")
  }

  if (["paused", "closed", "unassignable"].includes(status)){
    jQuery('#reason').text(`(${reasonLabel})`)
    jQuery(`#edit-reason`).show()
  } else {
    jQuery('#reason').text(``)
    jQuery(`#edit-reason`).hide()
  }
}

function updateCriticalPath(key) {
  jQuery('#seeker_path').val(key)
  let seekerPathKeys = window.lodash.keys(post.seeker_path.default)
  let percentage = (window.lodash.indexOf(seekerPathKeys, key) || 0) / (seekerPathKeys.length-1) * 100
  jQuery('#seeker-progress').css("width", `${percentage}%`)
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
      if (key === "overall_status" || key === "assigned_to"){
        setStatus(response)
      }
    })
  })
  $('#content')[0].addEventListener('comment_posted', function (e) {
    if ( $('.update-needed').prop("checked") === true ){
      window.API.get_post("contacts",  post_id ).then(resp=>{
        post = resp
        window.record_updated(window.lodash.get(resp, "requires_update") === true )
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
    window.API.update_post('contacts', post_id, data).then(contactData=>{
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
    window.makeRequestOnPosts( "POST", `contacts/${post_id}/accept`, data)
    .then(function (resp) {
      setStatus(resp)
      jQuery('#accept-contact').hide()
    }).catch(err=>{
      console.log('error')
      console.log(err.responseText)
    })
  })

  let dispatch_users = [];
  let selected_role = "multiplier";
  let dispatch_users_promise = null
  let list_filters = $('#user-list-filters')
  let defined_list_section = $('#defined-lists')
  let populated_list = $('.populated-list')

  jQuery('.advanced_user_select').on('click', function (){
    $('#assigned_to_user_modal').foundation('open');
    if ( dispatch_users_promise === null ){
      $('#assigned_to_user_modal #dispatch-tile-loader').addClass('active')
      dispatch_users_promise = window.makeRequest( 'GET', 'assignment-list', {location_ids: (post.location_grid||[]).map(l=>l.id), 'post_id': post_id, 'post_type': post_type}, 'dt-posts/v2/contacts' )
      dispatch_users_promise.then(response=>{
        $('#assigned_to_user_modal #dispatch-tile-loader').removeClass('active')
        dispatch_users = response
        $('.users-select-panel').show()
        show_assignment_tab( selected_role )
      })
    } else {
      $('.users-select-panel').show()
      show_assignment_tab( selected_role )
    }
  })

  //change tab
  $('#assign-role-tabs a').on('click', function () {
    selected_role = $(this).data('field')
    $('#search-users-filtered').attr("placeholder", $(this).text().trim())
    show_assignment_tab( selected_role )
  })

  function show_assignment_tab( tab = 'multiplier' ){
    const contact_languages = (window.lodash.get(window.detailsSettings, "post_fields.languages"))
      ? window.detailsSettings.post_fields.languages
      : []
    const contact_gender = (window.lodash.get(window.detailsSettings, "post_fields.gender"))
      ? window.detailsSettings.post_fields.gender
      : { key: null, label: "" }

    let filters = `<a data-id="all" style="color: black; font-weight: bold">${window.lodash.escape(window.dt_contacts_access.translations.all)}</a> | `

    defined_list_section.show()
    let users_with_role = dispatch_users.filter(u => u.roles.includes(tab))
    let filter_options = {
      all: users_with_role.sort((a, b) => {
        if (a.weight && b.weight) {
          if (a.weight === b.weight) {
            return 0;
          } else {
            return (a.weight > b.weight) ? -1 : 1;
          }
        } else {
          return a.name.localeCompare(b.name);
        }
      }),
      ready: users_with_role.filter(m=>m.status==='active'),
      recent: users_with_role.concat().sort((a,b)=>b.last_assignment-a.last_assignment),
      language: users_with_role.filter(({ languages }) => languages.some(language => contact_languages.includes(language))),
      gender: users_with_role.filter(m => contact_gender.label !== "" && m.gender === contact_gender.key),
      location: users_with_role.concat().filter(m=>m.location!==null).sort((a,b)=>a.location-b.location)
    }
    populate_users_list( users_with_role )
    filters += `<a data-id="ready">${window.lodash.escape(window.dt_contacts_access.translations.ready)}</a> | `
    filters += `<a data-id="recent">${window.lodash.escape(window.dt_contacts_access.translations.recent)}</a> | `
    filters += `<a data-id="language">${window.lodash.escape(window.dt_contacts_access.translations.language)}</a> | `
    filters += `<a data-id="gender">${window.lodash.escape(window.dt_contacts_access.translations.gender)}</a> | `
    filters += `<a data-id="location">${window.lodash.escape(window.dt_contacts_access.translations.location)}</a>`
    list_filters.html(filters)


    $('#user-list-filters a').on('click', function () {
      $( '#user-list-filters a' ).css("color","").css("font-weight","")
      $(this).css("color", "black").css("font-weight", "bold")
      let key = $(this).data('id')
      populate_users_list( filter_options[key] || [], key )
    })
  }

  function populate_users_list(users, tab = 'all') {
    let user_rows = '';
    users.forEach( m => {
      user_rows += `<div class="assigned-to-row" dir="auto">
        <span>
          <span class="avatar"><img style="vertical-align: text-bottom" src="${window.lodash.escape( m.avatar )}"/></span>
          ${window.lodash.escape(m.name)}
        </span>
        ${ m.status_color ? `<span class="status-square" style="background-color: ${ window.lodash.escape(m.status_color) }">&nbsp;</span>` : '' }
        ${ m.update_needed ? `
          <span>
            <img style="height: 12px;" src="${window.lodash.escape(window.wpApiShare.template_dir)}/dt-assets/images/broken.svg"/>
            <span style="font-size: 14px">${ window.lodash.escape(m.update_needed) }</span>
          </span>` : ''
      }
        ${ m.best_location_match ? `<span>(${ window.lodash.escape(m.best_location_match) })</span>` : ''

      }
        <div style="flex-grow: 1"></div>
        <button class="button hollow tiny trigger-assignment" data-id="${ window.lodash.escape(m.ID) }" style="margin-bottom: 3px">
           ${window.lodash.escape(window.dt_contacts_access.translations.assign)}
        </button>
      </div>
      `
    })
    if ( user_rows.length === 0 ){
      user_rows = `<p style="padding:1rem">${window.lodash.escape(window.wpApiShare.translations.no_records_found.replace("{{query}}", window.dt_contacts_access.translations[tab]))}</p>`
    }
    populated_list.html(user_rows)

  }

  $(document).on('click', '.trigger-assignment', function () {
    let user_id = $(this).data('id')
    $('#dispatch-tile-loader').addClass('active')
    let status = selected_role === "dispatcher" ? "unassigned" : "assigned"
    window.API.update_post(
      'contacts',
      window.detailsSettings.post_fields.ID,
      {
        assigned_to: 'user-' + user_id,
        overall_status: status
      }
    ).then(function (response) {
      $('#dispatch-tile-loader').removeClass('active')
      setStatus(response)
      $(`.js-typeahead-assigned_to`).val(window.lodash.escape(response.assigned_to.display)).blur()
      $('#assigned_to_user_modal').foundation('close');
    })
  })

  /**
   * search name in list
   */
  $('#search-users-filtered').on('input', function () {
    $( '#user-list-filters a' ).css("color","").css("font-weight","")
    let search_text = $(this).val().normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase()
    let users_with_role = dispatch_users.filter(u => u.roles.includes(selected_role) )
    let match_name = users_with_role.filter(u =>
      u.name.normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase().includes( search_text )
    )
    populate_users_list(match_name)
  })
})
