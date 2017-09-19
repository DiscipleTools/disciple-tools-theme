/* global jQuery:false, wpApiSettings:false */

function save_seeker_milestones(contactId, fieldKey, fieldValue){
  var data = {}
  var field = jQuery("#" + fieldKey)
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
  var data = {}
  var numberIndicator = jQuery("." + fieldKey +  " span")
  var newNumber = parseInt(numberIndicator.first().text()) + 1
  data[fieldKey] = newNumber
  jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/' + contactId + '/quick_action_button',
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
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
  var data = {}
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

var commentTemplate = _.template(`
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
    var tokens = [];
    //the available string is 'name' in your datum
    var stringSize = d.name.length;
    //multiple combinations for every available size
    //(eg. dog = d, o, g, do, og, dog)
    for (var size = 1; size <= stringSize; size++) {
      for (var i = 0; i + size <= stringSize; i++) {
        tokens.push(d.name.substr(i, size));
      }
    }
    return tokens;
  }

  // https://typeahead.js.org/examples/
  /**
   * Groups
   */
  var groups = new Bloodhound({
    datumTokenizer: searchAnyPieceOfWord,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    identify: function (obj) {
      return obj.name
    },
    prefetch: {
      url: wpApiSettings.root + 'dt-hooks/v1/groups-compact/',
      cache:false,
      prepare : API.typeaheadPrefetchPrepare
    },
    remote: {
      url: wpApiSettings.root + 'dt-hooks/v1/groups-compact/?s=%QUERY',
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
  var contacts = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('post_title'),
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.post_title
    },
    prefetch: {
      url: wpApiSettings.root + 'dt-hooks/v1/contacts/',
      prepare : API.typeaheadPrefetchPrepare
    },
    remote: {
      url: wpApiSettings.root + 'dt-hooks/v1/contacts/?s=%QUERY',
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
      url: wpApiSettings.root + 'dt/v1/users/',
      prepare : API.typeaheadPrefetchPrepare,
      transform: function (data) {
        return API.filterTypeahead(data)
      },
      cache:false
    },
    remote: {
      url: wpApiSettings.root + 'dt/v1/users/?s=%QUERY',
      wildcard: '%QUERY',
      prepare : API.typeaheadRemotePrepare,
      transform: function (data) {
        return API.filterTypeahead(data)
      }
    }
  });

  let assigned_to_typeahead = $('.assigned_to .typeahead')
  assigned_to_typeahead.typeahead({
    highlight: true,
    minLength: 0,
    autoselect: true,
  },
  {
    name: 'users',
    source: function (q, sync, async) {
      return API.defaultFilter(q, sync, async, users, [])
    },
    display: 'name'
  })
  .bind('typeahead:select', function (ev, sug) {
    API.save_field_api('contact', contactId, {assigned_to: 'user-' + sug.ID}).then(()=> {
      assigned_to_typeahead.typeahead('val', '')
      $('.current-assigned').text(sug.name)
    }).catch(err=>{
      console.trace("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
  })

  /**
   * Locations
   */
  var locations = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('post_title'),
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.post_title
    },
    prefetch: {
      url: wpApiSettings.root + 'dt/v1/locations/',
      prepare : API.typeaheadPrefetchPrepare
    },
    remote: {
      url: wpApiSettings.root + 'dt/v1/locations/?s=%QUERY',
      wildcard: '%QUERY',
      prepare : API.typeaheadRemotePrepare
    }
  });


  function defaultLocations(q, sync) {
    if (q === '') {
      sync(locations.all());
    }
    else {
      locations.search(q, sync);
    }
  }

  let locationsTypeahead = $('#locations .typeahead')
  locationsTypeahead.typeahead({
    highlight: true,
    minLength: 0,
    autoselect: true,
  },
  {
    name: 'locations',
    source: defaultLocations,
    display: 'post_title'
  })
  .bind('typeahead:select', function (ev, sug) {
    locationsTypeahead.typeahead('val', '')
    locationsTypeahead.blur()
    add_typeahead_item(contactId, 'locations', sug.ID)
  })

  /**
   * Get the contact
   */
  // jQuery.ajax({
  //   type:"GET",
  //   contentType: "application/json; charset=utf-8",
  //   dataType: "json",
  //   url: wpApiSettings.root + 'dt-hooks/v1/contact/'+ id,
  //   success: function(data) {
  //     contact = data
  //     console.log(contact)
  //     assigned_to_typeahead.typeahead('val', _.get(contact, "fields.assigned_to.display"))
  //   },
  //   error: function(err) {
  //     console.log("error")
  //     console.log(err)
  //     jQuery("#errors").append(err.responseText)
  //   },
  // })


  jQuery('#add-comment-button').on('click', function () {
    post_comment(contactId)
  })

})



function formatDate(date) {
  var hours = date.getHours();
  var minutes = date.getMinutes();
  var ampm = hours >= 12 ? 'pm' : 'am';
  hours = hours % 12;
  hours = hours ? hours : 12; // the hour '0' should be '12'
  minutes = minutes < 10 ? '0'+minutes : minutes;
  var strTime = hours + ':' + minutes + ' ' + ampm;
  var month = date.getMonth()+1
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



function edit_fields() {
  jQuery(".display-fields").toggle()
  jQuery(".edit-fields").toggle()
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
    var newInput = `<li><input id="${inputId}" onchange="new_contact_input_added(${contactId},'${inputId}')"\>`
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
    let closedLabel = wpApiSettings.contacts_custom_fields_settings.overall_status.default.closed;
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
    let pausedLabel = wpApiSettings.contacts_custom_fields_settings.overall_status.default.paused;
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
    let activeLabel = wpApiSettings.contacts_custom_fields_settings.overall_status.default.active;
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
    <button class="details-remove-button connections-edit" onclick="remove_item(${contactId}, '${fieldId}', ${addedItem.ID})">Remove</button>
    </li>`)
    jQuery(".connections-edit").show()
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
    url: wpApiSettings.root + 'dt-hooks/v1/contact/' + contactId + "/accept",
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
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
