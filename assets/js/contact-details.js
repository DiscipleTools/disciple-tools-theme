/* global jQuery:false, contactsDetailsWpApiSettings:false */

function save_seeker_milestones(contactId, fieldKey, fieldValue){
  let data = {}
  let field = jQuery("#" + fieldKey)
  field.addClass("submitting-select-button")
  if (field.hasClass("selected-select-button")){
    fieldValue = "no"
  } else {
    field.removeClass("empty-select-button")
    field.addClass("selected-select-button")
    fieldValue = "yes"
  }
  data[fieldKey] = fieldValue
  API.save_field_api('contact', contactId, data).then(()=>{
    field.removeClass("submitting-select-button selected-select-button")
    field.addClass( fieldValue === "no" ? "empty-select-button" : "selected-select-button")
  }).catch(err=>{
      console.log("error")
      console.log(err)
      jQuery("#errors").text(err.responseText)
      field.removeClass("submitting-select-button selected-select-button")
      field.addClass( fieldValue === "yes" ? "empty-select-button" : "selected-select-button")
  })
}
function save_quick_action(contactId, fieldKey){
  let data = {}
  let numberIndicator = jQuery("." + fieldKey +  " span")
  let newNumber = parseInt(numberIndicator.first().text()) + 1
  data[fieldKey] = newNumber
  jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: contactsDetailsWpApiSettings.root + 'dt-hooks/v1/contact/' + contactId + '/quick_action_button',
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', contactsDetailsWpApiSettings.nonce);
    }
  }).then(data=>{
      console.log("updated " + fieldKey + " to: " + newNumber)
      if (fieldKey.indexOf("quick_button")>-1){
        if (data.seeker_path){
          jQuery("#current_seeker_path").text(data.seeker_path.current)
          if (data.seeker_path.next){
            jQuery("#next_seeker_path").text(data.seeker_path.next)
          }
        }
      }
  }).catch(err=>{
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
  })

  if (fieldKey.indexOf("quick_button")>-1){
    numberIndicator.text(newNumber)
  }
}



function post_comment(contactId) {
  jQuery("#add-comment-button").toggleClass('loading')
  let comment = jQuery("#comment-input").val()
  console.log(comment);
  let data = {}
  data["comment"] = comment
  API.post_comment('contact', contactId, comment).then(data=>{
    console.log(`added comment ${comment}`)
    jQuery("#comment-input").val("")
    jQuery("#add-comment-button").toggleClass('loading')
    data.comment.date = new Date(data.comment.comment_date_gmt + "Z")
    comments.push(data.comment)
    display_activity_comment()
  }).catch(err=>{
    console.log("error")
    console.log(err)
    jQuery("#errors").append(err.responseText)
  })
}

let commentTemplate = _.template(`
  <div class="activity-block">
    <div><span><strong><%- name %></strong></span> <span class="comment-date"> <%- date %> </span></div>
    <div class="activity-text">
    <% _.forEach(activity, function(a){ 
        if (a.comment){ %>
            <p dir="auto" class="comment-bubble"> <%- a.text %> </p>
      <% } else { %> 
            <p class="activity-bubble">  <%- a.text %> </p>
    <%  } 
    }); %>
    </div>
  </div>`
)


let comments = []
let activity = []
let contact = {}
jQuery(document).ready(function($) {

  console.log(contactsDetailsWpApiSettings)

  let contactId = $("#contact-id").text()
  $( document ).ajaxComplete(function(event, xhr, settings) {
    if (settings && settings.type && (settings.type === "POST" || settings.type === "DELETE")){
      API.get_activity('contact', contactId).then(activityData=>{
        activityData.forEach(d=>{
          d.date = new Date(d.hist_time*1000)
        })
        activity = activityData
        display_activity_comment()
      })
    }
  });


  $.when(
    API.get_comments('contact', contactId),
    API.get_activity('contact', contactId)
  ).then(function(commentData, activityData) {
    commentData[0].forEach(comment => {
      comment.date = new Date(comment.comment_date_gmt + "Z")
    })
    comments = commentData[0]
    activityData[0].forEach(d => {
      d.date = new Date(d.hist_time * 1000)
    })
    activity = activityData[0]
    display_activity_comment("all")
  })

  let searchAnyPieceOfWord = function(d) {
    let tokens = [];
    //the available string is 'name' in your datum
    let stringSize = d.name.length;
    //multiple combinations for every available size
    //(eg. dog = d, o, g, do, og, dog)
    for (let size = 1; size <= stringSize; size++) {
      for (let i = 0; i + size <= stringSize; i++) {
        tokens.push(d.name.substr(i, size));
      }
    }
    return tokens;
  }

  // https://typeahead.js.org/examples/
  /**
   * Groups
   */
  let groups = new Bloodhound({
    datumTokenizer: searchAnyPieceOfWord,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    identify: function (obj) {
      return obj.name
    },
    prefetch: {
      url: contactsDetailsWpApiSettings.root + 'dt-hooks/v1/groups-compact/',
      cache:false,
      prepare : API.typeaheadPrefetchPrepare
    },
    remote: {
      url: contactsDetailsWpApiSettings.root + 'dt-hooks/v1/groups-compact/?s=%QUERY',
      wildcard: '%QUERY',
      prepare : API.typeaheadRemotePrepare
    }
  })
  function defaultGroups(q, sync, async) {
    if (q === '') {
      sync(groups.all());
    }
    else {
      groups.search(q, sync, async);
    }
  }

  let groupsTypeahead = $('#groups .typeahead')
  groupsTypeahead.typeahead({
      highlight: true,
      minLength: 0,
      autoselect: true,
    },
    {
      async:false,
      name: 'groups',
      source: defaultGroups,
      display: 'name'
    })
    .bind('typeahead:select', function (ev, sug) {
      groupsTypeahead.typeahead('val', '')
      groupsTypeahead.blur()
      add_typeahead_item(contactId, 'groups', sug.ID)
    })

  /**
   * Baptized by, Baptized, Coaching, Coached By
   */
  let contacts = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('post_title'),
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.post_title
    },
    prefetch: {
      url: contactsDetailsWpApiSettings.root + 'dt-hooks/v1/contacts/',
      prepare : API.typeaheadPrefetchPrepare
    },
    remote: {
      url: contactsDetailsWpApiSettings.root + 'dt-hooks/v1/contacts/?s=%QUERY',
      wildcard: '%QUERY',
      prepare : API.typeaheadRemotePrepare
    }
  });
  function defaultcontacts(q, sync) {
    if (q === '') {
      sync(contacts.all());
    }
    else {
      contacts.search(q, sync);
    }
  }

  //autocomplete for dealing with contacts
  ["baptized_by", "baptized", "coached_by", "coaching"].forEach(field_id=>{
    let typeahead = $(`#${field_id} .typeahead`)
    typeahead.typeahead({
        highlight: true,
        minLength: 0,
        autoselect: true,
      },
      {
        name: 'contacts',
        source: defaultcontacts,
        display: 'post_title'
      })
      .bind('typeahead:select', function (ev, sug) {
        typeahead.typeahead('val', '')
        typeahead.blur()
        add_typeahead_item(contactId, field_id, sug.ID)
      })
  })

  /**
   * Assigned to
   */
  let users = new Bloodhound({
    datumTokenizer: API.searchAnyPieceOfWord,
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.ID
    },
    prefetch: {
      url: contactsDetailsWpApiSettings.root + 'dt/v1/users/',
      prepare : API.typeaheadPrefetchPrepare,
      transform: function (data) {
        return API.filterTypeahead(data, _.get(contact, "fields.assigned_to") ? [{ID:contact.fields.assigned_to.ID}] : [])
      },
    },
    remote: {
      url: contactsDetailsWpApiSettings.root + 'dt/v1/users/?s=%QUERY',
      wildcard: '%QUERY',
      prepare : API.typeaheadRemotePrepare,
      transform: function (data) {
        return API.filterTypeahead(data, _.get(contact, "fields.assigned_to") ? [{ID:contact.fields.assigned_to.ID}] : [])
      }
    },
    initialize: false,
  });

  let assigned_to_typeahead = $('.assigned_to .typeahead')
  function loadAssignedToTypeahead() {

    assigned_to_typeahead.typeahead({
        highlight: true,
        minLength: 0,
        autoselect: true,
      },
      {
        name: 'users',
        source: function (q, sync, async) {
          return API.defaultFilter(q, sync, async, users, _.get(contact, "fields.assigned_to") ? [{ID:contact.fields.assigned_to.ID}] : [])
        },
        display: 'name'
      })
  }
  assigned_to_typeahead.bind('typeahead:select', function (ev, sug) {
    API.save_field_api('contact', contactId, {assigned_to: 'user-' + sug.ID}).then(function () {
      assigned_to_typeahead.typeahead('val', '')
      jQuery('.current-assigned').text(sug.name)
      contact.fields.assigned_to.ID = sug.ID
      assigned_to_typeahead.typeahead('destroy')
      users.initialize()
      loadAssignedToTypeahead()

    }).catch(err=>{
      console.trace("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
  }).bind('blur', ()=>{
    // toggleEdit('assigned_to')
  })
  loadAssignedToTypeahead()

  /**
   * Locations
   */
  let locations = new Bloodhound({
    datumTokenizer: API.searchAnyPieceOfWord,
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.ID
    },
    prefetch: {
      url: contactsDetailsWpApiSettings.root + 'dt/v1/locations-compact/',
      prepare : API.typeaheadPrefetchPrepare,
      transform: function(data){
        return API.filterTypeahead(data, _.get(contact, "fields.locations") || [])
      },
    },
    remote: {
      url: contactsDetailsWpApiSettings.root + 'dt/v1/locations-compact/?s=%QUERY',
      wildcard: '%QUERY',
      prepare : API.typeaheadRemotePrepare,
      transform: function(data){
        return API.filterTypeahead(data, _.get(contact, "fields.locations") || [])
      }
    },
    initialize: false,
  });

  let locationsTypeahead = $('.locations .typeahead')
  function loadLocationsTypeahead() {
    locationsTypeahead.typeahead({
        highlight: true,
        minLength: 0,
        autoselect: true,
      },
      {
        name: 'locations',
        source: function (q, sync, async) {
          return API.defaultFilter(q, sync, async, locations, _.get(contact, "fields.locations"))
        },
        display: 'name'
      })
  }
  locationsTypeahead.bind('typeahead:select', function (ev, sug) {
    locationsTypeahead.typeahead('val', '')
    contact.fields.locations.push(sug)
    add_typeahead_item(contactId, 'locations', sug.ID, sug.name)
    $("#no-location").remove()
    locationsTypeahead.typeahead('destroy')
    locations.initialize()
    loadLocationsTypeahead()
  })
  loadLocationsTypeahead()

  /**
   * Get the contact
   */
  console.log("getting")
  API.get_post('contact', contactId).then(function(data) {
    contact = data
    console.log(contact)
    locations.initialize()
    users.initialize()
    if (_.get(contact, "fields.assigned_to")){
      $('.current-assigned').text(_.get(contact, "fields.assigned_to.display"))
    }
  }).catch(err=> {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)

  })


  jQuery('#add-comment-button').on('click', function () {
    post_comment(contactId)
  })


  let editDetailsToggle = $('#edit-button-label')
  function toggleEditAll() {
    $(`.details-list`).toggle()
    $(`.details-edit`).toggle()
    editingAll = !editingAll
    editDetailsToggle.text( editingAll ? "Back": "Edit")
  }
  $('#edit-details').on('click', function () {
    toggleEditAll()
  })

  $(document).on('click', '.details-status-button.verify', function () {
    let id = $(this).data('id')
    let verified = $(this).data('verified')
    API.update_contact_method_detail('contact', contactId, id, {"verified":!verified}).then(()=>{
      $(this).data('verified', !verified)
      if (verified){
        jQuery(`#${id}-verified`).hide()
      } else {
        jQuery(`#${id}-verified`).show()

      }
      jQuery(this).html(verified ? "Verify" : "Unverify")
    })
  })
  $(document).on('click', '.details-status-button.invalid', function () {
    let id = $(this).data('id')
    let invalid = $(this).data('invalid')
    API.update_contact_method_detail('contact', contactId, id, {"invalid":!invalid}).then(()=>{
      $(this).data('invalid', !invalid)
      if (invalid){
        jQuery(`#${id}-invalid`).hide()
      } else  {
        jQuery(`#${id}-invalid`).show()
      }
      jQuery(this).html(invalid? "Invalidate" : "Uninvalidate")
    })
  })
  $(document).on('click', '.details-remove-button.connection', function () {
    let fieldId = $(this).data('field')
    let itemId = $(this).data('id')

    if (fieldId && itemId){
      API.remove_item_from_field('contact', contactId, fieldId, itemId).then(()=>{
        $(`.${fieldId}-list .${itemId}`).remove()

        //add the item back to the locations list
        if (fieldId === 'locations'){
          locations.add([{ID:itemId, name: $(this).data('name')}])
        }
      }).catch(err=>{
        console.log(err)
      })
    }
  })
  $(document).on('click', '.details-remove-button.social', function () {
    let fieldId = $(this).data('id')
    if (fieldId){
      API.remove_field('contact', contactId, fieldId).then(()=>{

        $(`ul.social .${fieldId}`).remove()

      }).catch(err=>{
        console.log(err)
      })
    }
  })

  $(document).on('change', '.details-edit.social-input', function () {
    let id = $(this).attr('id')
    let value = $(this).val();
    console.log(id)
    console.log(value)
    API.save_field_api('contact', contactId, {[id]: value}).then(()=>{
      $(`.social.details-list .${id} .social-text`).text(value)
    })

  })

  let addSocial = $("#add-social-media")
  addSocial.on('click', function () {
    let channel_type = $('#social-channels').val()
    let inputForNewValue = $('#new-social-media')
    let text = inputForNewValue.val()
    addSocial.toggleClass('loading')
    API.add_item_to_field('contact', contactId, {['new-'+channel_type]: text}).then((newId)=>{
      console.log(newId);
      addSocial.toggleClass('loading')
      let label = _.get(contactsDetailsWpApiSettings, `channels[${channel_type}].label`) || channel_type
      $('.social.details-edit').append(
        `<li class="${newId}">
          <span>${label}</span>
          <input id="${newId}"
                 value="${text}" style="display: inline-block"   
                 class="details-edit social-input" >
          <ul class='dropdown menu' data-click-open='true' 
              data-dropdown-menu data-disable-hover='true' 
              style='display:inline-block'>
            <li><button><i class='fi-pencil' style='padding:3px 3px'></button></i>
              <ul class='menu'>
                  <li><button class='details-remove-button social' data-id='${newId}' data-field >Remove<button></li>
                  <li><button class='details-status-button verify' data-verified='0' data-id='${newId}'>Verify</button></li>
                  <li><button class='details-status-button invalid' data-verified='0' data-id='${newId}'>Invalidate</button></li>
              </ul>
            </li>
          </ul>
                
        </li>`)
      $(`.${newId} .dropdown.menu`).foundation()

      $('.social.details-list').append(
        `<li class="${newId}">
          <span>${label}</span>
          <span class="social-text">${text}</span>
          <img id="${newId}-verified" class="details-status" style="display:none" src="${contactsDetailsWpApiSettings.template_dir}/assets/images/verified.svg"/>
          <img id="${newId}-invalid" class="details-status" style="display:none" src="${contactsDetailsWpApiSettings.template_dir}/assets/images/verified.svg"/>
        </li>`)
      inputForNewValue.val('')
    })
  })

})



function formatDate(date) {
  let hours = date.getHours();
  let minutes = date.getMinutes();
  let ampm = hours >= 12 ? 'pm' : 'am';
  hours = hours % 12;
  hours = hours ? hours : 12; // the hour '0' should be '12'
  minutes = minutes < 10 ? '0'+minutes : minutes;
  let strTime = hours + ':' + minutes + ' ' + ampm;
  let month = date.getMonth()+1
  month = month < 10 ? "0"+month.toString() : month
  return date.getFullYear() + "/" + date.getDate() + "/" + month + "  " + strTime;
}

let current_section = "all"
function display_activity_comment(section) {
  current_section = section || current_section

  let commentsWrapper = $("#comments-wrapper")
  commentsWrapper.empty()
  let displayed = []
  if (current_section === "all"){
    displayed = _.union(comments, activity)
  } else if (current_section === "comments"){
    displayed = comments
  } else if ( current_section === "activity"){
    displayed = activity
  }
  displayed = _.orderBy(displayed, "date", "desc")
  let array = []

  displayed.forEach(d=>{
    let first = _.first(array)
    let name = d.comment_author || d.name
    let obj = {
      name: name,
      date: d.date,
      text:d.object_note ||  d.comment_content,
      comment: !!d.comment_content
    }


    let diff = (first ? first.date.getTime() : new Date().getTime()) - obj.date.getTime()
    if (!first || (first.name === name && diff < 60 * 60 * 1000) ){
      array.push(obj)
    } else {
      commentsWrapper.append(commentTemplate({
        name: array[0].name,
        date:formatDate(array[0].date),
        activity: array
      }))
      array = [obj]
    }
  })
  if (array.length > 0){
    commentsWrapper.append(commentTemplate({
      name: array[0].name,
      date:formatDate(array[0].date),
      activity: array
    }))
  }
}

let editingAll = false



function edit_fields() {
  let editDetailsToggle = $('#edit-button-label')
  jQuery(".display-fields").toggle()
  jQuery(".edit-fields").toggle()
  editingAll = !editingAll
  editDetailsToggle.text( editingAll ? "Back": "Edit")

}

function handelAjaxError(err) {
    console.trace("error")
    console.log(err)
    jQuery("#errors").append(err.responseText)

}

function save_field(contactId, fieldKey, inputId){
  let field = jQuery("#"+ (inputId || fieldKey))
  let val = field.val()
  let data = {}
  data[fieldKey] = val
  API.save_field_api('contact', contactId, data).catch(err=>{
    handelAjaxError(err)
  })
}


function new_contact_input_added(contactId, inputId){
  let input = jQuery("#"+inputId)
  API.add_item_to_field('contact', contactId, {[inputId]: input.val()}).then(data=>{
    if (data != contactId && inputId.indexOf("new-")>-1){
      input.removeAttr('onchange');
      input.attr('id', data)
      input.change(function () {
        save_field(contactId, data)
      })
    }
  })
}

function add_contact_input(contactId, inputId, listId){
  if (jQuery(`#${inputId}`).length === 0 ){
    let newInput = `<li><input id="${inputId}" onchange="new_contact_input_added(${contactId},'${inputId}')"\>`
    jQuery(`#${listId}`).append(newInput)
  }
}

function verify_contact_method(contactId, fieldId) {
  API.update_contact_method_detail('contacts', contactId, fieldId, {"verified":true}).then(()=>{
    jQuery(`#${fieldId}-verified`).show()
    jQuery(`#${fieldId}-verify`).hide()
  })
}

function invalidate_contact_method(contactId, fieldId) {
  API.update_contact_method_detail('contacts', contactId, fieldId, {"invalid":true}).then(()=>{
    jQuery(`#${fieldId}-invalid`).show()
    jQuery(`#${fieldId}-invalidate`).hide()
  })
}


function remove_item(contactId, fieldId, itemId){
  API.remove_item_from_field('contact', contactId, fieldId, itemId).then(()=>{
    jQuery(`.${fieldId}-list .${itemId}`).remove()
  })
}


function close_contact(contactId){
  jQuery("#confirm-close").toggleClass('loading')
  let reasonClosed = jQuery('#reason-closed-options')
  let data = {overall_status:"closed", "reason_closed":reasonClosed.val()}
  API.save_field_api('contact', contactId, data).then(()=>{
    let closedLabel = contactsDetailsWpApiSettings.contacts_custom_fields_settings.overall_status.default.closed;
    jQuery('#overall-status').text(closedLabel)
    jQuery("#confirm-close").toggleClass('loading')
    jQuery('#close-contact-modal').foundation('close')
    jQuery('#reason').text(`(${reasonClosed.find('option:selected').text()})`)
    jQuery('#return-active').show()
  })
}

let confirmPauseButton = jQuery("#confirm-pause")
function pause_contact(contactId){
  confirmPauseButton.toggleClass('loading')
  let reasonPaused = jQuery('#reason-paused-options')
  let data = {overall_status:"paused", "reason_paused":reasonPaused.val()}
  API.save_field_api('contact', contactId, data).then(()=>{
    let pausedLabel = contactsDetailsWpApiSettings.contacts_custom_fields_settings.overall_status.default.paused;
    jQuery('#overall-status').text(pausedLabel)
    jQuery('#reason').text(`(${reasonPaused.find('option:selected').text()})`)
    jQuery('#pause-contact-modal').foundation('close')
    jQuery('#return-active').show()
    confirmPauseButton.toggleClass('loading')
  })
}


function make_active(contactId) {
  let data = {overall_status:"active"}
  API.save_field_api('contact', contactId, data).then(()=>{
    let activeLabel = contactsDetailsWpApiSettings.contacts_custom_fields_settings.overall_status.default.active;
    jQuery('#return-active').toggle()
    jQuery('#overall-status').text(activeLabel || "Active")
    jQuery('#reason').text(``)
  })
}

/***
 * Connections
 */
function edit_connections() {
  console.log("edit_connections")
  jQuery(".connections-edit").toggle()
}

function add_typeahead_item(contactId, fieldId, val) {
  API.add_item_to_field('contact', contactId, {[fieldId]: val}).then(addedItem=>{
    jQuery(`.${fieldId}-list`).append(`<li class="${addedItem.ID}">
    <a href="${addedItem.permalink}">${addedItem.post_title}</a>
    <button class="details-remove-button connection details-edit"
              data-field="locations" data-id="${val}"
              data-name="${name}"  
              style="display: inline-block">Remove</button>
      </li>`)
  })
}

function details_accept_contact(contactId, accept){
  console.log(contactId)

  let data = {accept:accept}
  jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: contactsDetailsWpApiSettings.root + 'dt-hooks/v1/contact/' + contactId + "/accept",
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', contactsDetailsWpApiSettings.nonce);
    }
  }).then(function (data) {
    jQuery('#accept-contact').hide()
    if (data['overall_status']){
      jQuery('#overall-status').text(data['overall_status'])
    }
  }).catch(err=>{
    console.log(err)
  })
}


function add_shared(contactId, selectId){
  let select = jQuery(`#${selectId}`)
  let name = jQuery(`#${selectId} option:selected`)
  console.log(select.val())
  API.add_shared('contact', contactId, select.val()).then(function (data) {
    jQuery(`#shared-with-list`).append(
      '<li class="'+select.val()+'">' +
      name.text()+
      '<button class="details-remove-button" onclick="remove_shared(${contactId},${select.val()})">' +
      'Unshare' +
      '</button></li>'
    );
  }).catch(err=>{
    console.log(err)
  })
}


function remove_shared(contactId, user_id){
  API.remove_shared('contact', contactId, user_id).then(function (data) {
    jQuery("#shared-with-list ." + user_id).remove()
  }).catch(err=>{
    console.log(err)
  })
}
