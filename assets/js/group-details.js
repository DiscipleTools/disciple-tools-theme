/* global jQuery:false, wpApiGroupsSettings:false */
jQuery(document).ready(function($) {

  $( document ).ajaxComplete(function(event, xhr, settings) {
    if (settings && settings.type && (settings.type === "POST" || settings.type === "DELETE")){
      refreshActivity()
    }
  });

  let group = wpApiGroupsSettings.group


  /**
   * Typeahead functions
   */

  function add_typeahead_item(groupId, fieldId, val, name) {
    let list = $(`.${fieldId}-list`)
    list.append(`<li class="temp-${fieldId}-${val}">Adding new Item</li>`)
    API.add_item_to_field( 'group', groupId, { [fieldId]: val }).then(function (addedItem){
      list.append(`<li class="${addedItem.ID}">
      <a href="${addedItem.permalink}">${_.escape(addedItem.post_title)}</a>
      <button class="details-remove-button details-edit"
              data-field="locations" data-id="${val}"
              data-name="${name}"
              style="display: inline-block">Remove</button>
      </li>`)
      $(`.temp-${fieldId}-${val}`).remove()
    }).catch(err=>{
      $(`.temp-${fieldId}-${val}`).text(`Could not add: ${name}`)
    })
  }


  let groupId = $('#group-id').text()
  let editingAll = false



  /**
   * Group details Info
   */
  let editDetailsToggle = $('#edit-button-label')
  function toggleEditAll() {
    $(`.details-list`).toggle()
    $(`.details-edit`).toggle()
    editingAll = !editingAll
    editDetailsToggle.text( editingAll ? "Save": "Edit")
    if(editingAll){
      $('.status.details-edit').show()
      $('.status.details-list').hide()
    }
  }
  $('#edit-details').on('click', function () {
    toggleEditAll()
  })

  $(document)
    .on('click', '.details-remove-button', function () {
    let fieldId = $(this).data('field')
    let itemId = $(this).data('id')

    if (fieldId && itemId){
      API.remove_item_from_field('group', groupId, fieldId, itemId).then(()=>{
        $(`.${fieldId}-list .${itemId}`).remove()

        //add the item back to the locations list
        if (fieldId === 'locations'){
          locations.add([{ID:itemId, name: $(this).data('name')}])
        }
        if (fieldId === "members"){
          members.add([{ID:itemId, name: $(this).data('name')}])
        }
      }).catch(err=>{
        console.log(err)
      })
    }
  })


  function toggleEdit(field){
    if (!editingAll){
      $(`.${field}.details-list`).toggle()
      $(`.${field}.details-edit`).toggle()
    }
  }


  /**
   * End Date
   */
  let endDateList = $('.end_date.details-list')
  let endDatePicker = $('.end_date #end-date-picker')
  endDatePicker.datepicker({
    onSelect: function (date) {
      API.save_field_api('group', groupId, {end_date:date}).then(function () {
        endDateList.text(date)
      })
    },
    onClose: function () {
      toggleEdit('end_date')
    },
    changeMonth: true,
    changeYear: true
  })
  endDateList.on('click', e=>{
    toggleEdit('end_date')
    endDatePicker.focus()
  })

  /**
   * Start date
   */
  let startDateList = $('.start_date.details-list')
  let startDatePicker = $('.start_date #start-date-picker')
  startDatePicker.datepicker({
    onSelect: function (date) {
      API.save_field_api('group', groupId, {start_date:date}).then(function () {
        startDateList.text(date)
      })
    },
    onClose: function () {
      toggleEdit('start_date')
    },
    changeMonth: true,
    changeYear: true
  })
  startDateList.on('click', e=>{
    toggleEdit('start_date')
    startDatePicker.focus()
  })

  /**
   * Assigned To
   */
  $('.assigned_to.details-list').on('click', e=>{
    toggleEdit('assigned_to')
    assigned_to_typeahead.focus()
  })
  let users = new Bloodhound({
    datumTokenizer: API.searchAnyPieceOfWord,
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.ID
    },
    prefetch: {
      url: wpApiGroupsSettings.root + 'dt/v1/users/get_users/',
      prepare : API.typeaheadPrefetchPrepare,
      transform: function (data) {
        return API.filterTypeahead(data, group.assigned_to ? [{ID:group.assigned_to.ID}] : [])
      },
    },
    remote: {
      url: wpApiGroupsSettings.root + 'dt/v1/users/get_users/?s=%QUERY',
      wildcard: '%QUERY',
      prepare : API.typeaheadRemotePrepare,
      transform: function (data) {
        return API.filterTypeahead(data, group.assigned_to ? [{ID:group.assigned_to.ID}] : [])
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
      limit: 15,
      source: function (q, sync, async) {
        return API.defaultFilter(q, sync, async, users, group.assigned_to ? [{ID:group.assigned_to.ID}] : [])
      },
      display: 'name'
    })
  }
  assigned_to_typeahead.bind('typeahead:select', function (ev, sug) {
    API.save_field_api('group', groupId, {assigned_to: 'user-' + sug.ID}).then(function () {
      assigned_to_typeahead.typeahead('val', '')
      jQuery('.current-assigned').text(sug.name)
      group.assigned_to.ID = sug.ID
      assigned_to_typeahead.typeahead('destroy')
      users.initialize()
      loadAssignedToTypeahead()

    }).catch(err=>{
      console.trace("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
  }).bind('blur', ()=>{
    toggleEdit('assigned_to')
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
      url: wpApiGroupsSettings.root + 'dt/v1/locations-compact/',
      prepare : API.typeaheadPrefetchPrepare,
      transform: function(data){
        return API.filterTypeahead(data, group.locations || [])
      },
    },
    remote: {
      url: wpApiGroupsSettings.root + 'dt/v1/locations-compact/?s=%QUERY',
      wildcard: '%QUERY',
      prepare : API.typeaheadRemotePrepare,
      transform: function(data){
        return API.filterTypeahead(data, group.locations || [])
      }
    },
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
      limit: 15,
      source: function (q, sync, async) {
        return API.defaultFilter(q, sync, async, locations, group.locations)
      },
      display: 'name'
    })
  }
  locationsTypeahead.bind('typeahead:select', function (ev, sug) {
    locationsTypeahead.typeahead('val', '')
    group.locations.push(sug)
    add_typeahead_item(groupId, 'locations', sug.ID, sug.name)
    $("#no-location").remove()
    locationsTypeahead.typeahead('destroy')
    locations.initialize()
    loadLocationsTypeahead()
  })
  loadLocationsTypeahead()

  /**
   * People Groups
   */
  let peopleGroups = new Bloodhound({
    datumTokenizer: API.searchAnyPieceOfWord,
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.ID
    },
    prefetch: {
      url: wpApiGroupsSettings.root + 'dt/v1/people-groups-compact/',
      prepare : API.typeaheadPrefetchPrepare,
    },
    remote: {
      url: wpApiGroupsSettings.root + 'dt/v1/people-groups-compact/?s=%QUERY',
      wildcard: '%QUERY',
      prepare : API.typeaheadRemotePrepare,
    },
  });

  let peopleGroupsTypeahead = $('.people-groups .typeahead')
  function loadPeopleGroupsTypeahead() {
    peopleGroupsTypeahead.typeahead({
        highlight: true,
        minLength: 0,
        autoselect: true,

      },
      {
        name: 'peopleGroups',
        limit: 15,
        source: function (q, sync, async) {
          return API.defaultFilter(q, sync, async, peopleGroups, _.get(group, "people_groups"))
        },
        display: 'name'
      })
  }
  peopleGroupsTypeahead.bind('typeahead:select', function (ev, sug) {
    peopleGroupsTypeahead.typeahead('val', '')
    group["people_groups"].push(sug)
    add_typeahead_item(groupId, 'people_groups', sug.ID, sug.name)
    $("#no-people-group").remove()
    peopleGroupsTypeahead.typeahead('destroy')
    peopleGroups.initialize()
    loadPeopleGroupsTypeahead()
  })
  loadPeopleGroupsTypeahead()

  $("#add-new-address").click(function () {
    if ($('#new-address').length === 0 ) {
      let newInput = `<div class="new-address">
        <textarea rows="3" id="new-address"></textarea>
      </div>`
      $('.details-edit#address-list').append(newInput)
    }
  })

  //for a new address field that has not been saved yet
  $(document).on('change', '#new-address', function (val) {
    let input = $('#new-address')
    API.add_item_to_field( 'group', groupId, {"new-address":input.val()}).then(function (newAddressId) {
      console.log(newAddressId)
      if (newAddressId != groupId){
        //change the it to the created field
        input.attr('id', newAddressId)
        $('.details-list.address').append(`
          <li class="${newAddressId} address-row">
            <div class="address-text">${input.val()}</div>
            <img id="${newAddressId}-verified" class="details-status" style="display:none" src="${wpApiGroupsSettings.template_dir}/assets/images/verified.svg"/>
            <img id="${newAddressId}-invalid" class="details-status" style="display:none" src="${wpApiGroupsSettings.template_dir}/assets/images/broken.svg"/>
          </li>
        `)
        $('.new-address')
          .append(editContactDetailsOptions(newAddressId))
          .removeClass('new-address')
          .addClass(newAddressId)
        $(`.${newAddressId} .dropdown.menu`).foundation()

      }
    })
  })
  let editContactDetailsOptions = function (field_id) {
    return `
      <ul class='dropdown menu' data-click-open='true'
              data-dropdown-menu data-disable-hover='true'
              style='display:inline-block'>
        <li>
          <button class="social-details-options-button">
            <img src="${wpApiGroupsSettings.template_dir}/assets/images/menu-dots.svg" style='padding:3px 3px'>
          </button>
          <ul class='menu'>
            <li>
              <button class='details-status-button field-status verify'
                      data-status='valid'
                      data-id='${field_id}'>
                  Valid
              </button>
            </li>
            <li>
              <button class='details-status-button field-status invalid'
                      data-status="invalid"
                      data-id="${field_id}">
                  Invalid
              </button>
            </li>
            <li>
              <button class='details-status-button field-status'
                      data-status="reset"
                      data-id='${field_id}'>
                  Unconfirmed
              </button>
            </li>
            <li>
              <button class='details-remove-button delete-method'
                      data-id='${field_id}'>
                      Delete item
              <button>
            </li>
          </ul>
          </li>
      </ul>
    `
  }

  $(document).on('click', '.details-remove-button.delete-method', function () {
    let fieldId = $(this).data('id')
    if (fieldId){
      API.remove_field('group', groupId, fieldId).then(()=>{
        $(`.${fieldId}`).remove()
      }).catch(err=>{
        console.log(err)
      })
    }
  })

  $(document).on('change', '#address-list textarea', function(){
    let id = $(this).attr('id')
    if (id && id !== "new-address"){
      API.save_field_api('group', groupId, {[id]: $(this).val()}).then(()=>{
        $(`.address.details-list .${id} .address-text`).text($(this).val())
      })

    }
  })


  /**
   * Members
   */

  $("#members-edit").on('click', function () {
    $('.members-edit').toggle()
  })
  let members = new Bloodhound({
    datumTokenizer: API.searchAnyPieceOfWord,
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.ID
    },
    prefetch: {
      url: wpApiGroupsSettings.root + 'dt/v1/contacts/compact',
      transform: function(data){
        loadMembersTypeahead()
        return API.filterTypeahead(data, group.members || [])
      },
      prepare : API.typeaheadPrefetchPrepare,
    },
    remote: {
      url: wpApiGroupsSettings.root + 'dt/v1/contacts/compact/?s=%QUERY',
      wildcard: '%QUERY',
      transform: function(data){
        return API.filterTypeahead(data, group.members || [])
      },
      prepare : API.typeaheadRemotePrepare,
    },
    initialize: false,
    local : []
  });

  let membersTypeahead = $('#members .typeahead')
  function loadMembersTypeahead() {
    membersTypeahead.typeahead('destroy')
    members.initialize()
    membersTypeahead.typeahead({
      highlight: true,
      minLength: 0,
      autoselect: true,
    },
    {
      name: 'members',
      limit: 15,
      source: function (q, sync, async) {
        return API.defaultFilter(q, sync, async, members, group.members)
      },
      display: 'name'
    })
  }
  membersTypeahead.bind('typeahead:select', function (ev, sug) {
    membersTypeahead.typeahead('val', '')
    group.members.push(sug)
    add_typeahead_item(groupId, 'members', sug.ID, sug.name)
    loadMembersTypeahead()
  })
  loadMembersTypeahead()



  /**
   * Setup group fields
   */

  if (group.end_date){
    endDatePicker.datepicker('setDate', group.end_date)
  }
  if (group.start_date){
    startDatePicker.datepicker('setDate', group.start_date)
  }
  if (group.assigned_to){
    $('.current-assigned').text(_.get(group, "assigned_to.display"))
  }
  locations.initialize()
  members.initialize()
  users.initialize()


  /**
   * Comments and Activity
   */

  let comments = []
  let activity = []

  function prepareActivityData(activityData) {
    /* Insert a "created group" item in the activity, even though it is not
      * stored in the database. It is not stored as an activity in the
      * database, to avoid duplicating data with the post's metadata. */
    const currentGroup = wpApiGroupsSettings.group_post
    const createdDate = moment.utc(currentGroup.post_date_gmt, "YYYY-MM-DD HH:mm:ss", true)
    const createdGroupActivityItem = {
      hist_time: createdDate.unix(),
      object_note: wpApiGroupsSettings.txt_created_group.replace("{}", formatDate(createdDate.local())),
      name: wpApiGroupsSettings.group_author_name,
      user_id: currentGroup.post_author,
    }
    activityData.push(createdGroupActivityItem)
    activityData.forEach(item => {
      item.date = moment.unix(item.hist_time)
    })
  }



  function refreshActivity() {
    API.get_activity('group', groupId).then(activityData=>{
      activity = activityData
      prepareActivityData(activity)
      display_activity_comment()
    })
  }

  let commentButton = $('#add-comment-button')
    .on('click', function () {
      commentButton.toggleClass('loading')
      let input = $("#comment-input")
      API.post_comment('group', groupId, input.val()).then(commentData=>{
        commentButton.toggleClass('loading')
        input.val('')
        commentData.comment.date = moment(commentData.comment.comment_date_gmt + "Z")
        comments.push(commentData.comment)
        display_activity_comment()
      })
    })

  $.when(
    API.get_comments('group', groupId),
    API.get_activity('group', groupId)
  ).then(function(commentData, activityData){
    commentData[0].forEach(comment=>{
      comment.date = moment(comment.comment_date_gmt + "Z")
    })
    comments = commentData[0]
    activity = activityData[0]
    prepareActivityData(activity)
    display_activity_comment("all")
  })

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


      let diff = first ? first.date.diff(obj.date, "hours") : 0
      if (!first || (first.name === name && diff < 1) ){
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

  function formatDate(date) {
    return date.format("YYYY-MM-DD h:mm a")
  }


  /**
   * Church fields
   */
  let metrics = [
    'baptism',
    'fellowship',
    'communion',
    'prayer',
    'praise',
    'giving',
    'bible',
    'leaders',
    'sharing',
    'commitment'
  ]

  function fillOutChurchHealthMetrics() {
    let svgItem = document.getElementById("church-svg-wrapper").contentDocument

    let churchWheel = $(svgItem).find('svg')
    metrics.forEach(m=>{
      if (group[`church_${m}`] && ["1", "Yes"].indexOf(group[`church_${m}`]["key"])> -1){
        churchWheel.find(`#${m}`).css("opacity", "1")
        $(`#church_${m}`).css("opacity", "1")
      } else {
        churchWheel.find(`#${m}`).css("opacity", ".1")
        $(`#church_${m}`).css("opacity", ".4")
      }
    })
    if (!group["church_commitment"] || group["church_commitment"]["key"] === '0'){
      churchWheel.find('#group').css("opacity", "1")
      $(`#church_commitment`).css("opacity", ".4")
    } else {
      churchWheel.find('#group').css("opacity", ".1")
      $(`#church_commitment`).css("opacity", "1")
    }

    $(".js-progress-bordered-box").removeClass("half-opacity")
  }

  if ($('#church-svg-wrapper')[0].getSVGDocument() == null) {
    $('#church-svg-wrapper').on('load', function() { fillOutChurchHealthMetrics() })
  } else {
    fillOutChurchHealthMetrics()
  }

  $('.group-progress-button').on('click', function () {
    let fieldId = $(this).attr('id')
    $(this).css('opacity', ".6");
    let field = _.get(group, `[${fieldId}]['key']`) === "1" ? "0" : "1"
    API.save_field_api('group', groupId, {[fieldId]: field})
      .then(groupData=>{
        group = groupData
        fillOutChurchHealthMetrics()
      }).catch(err=>{
        console.log(err)
    })
  })

  /**
   * sharing
   */
  $('#add-shared-button').on('click', function () {
    let select = jQuery(`#share-with`)
    let name = jQuery(`#share-with option:selected`)
    API.add_shared('group', groupId, select.val()).then(function (data) {
      jQuery(`#shared-with-list`).append(
        '<li class="'+select.val()+'">' +
        name.text()+
        '<button class="details-remove-button share" data-id="'+select.val()+'">' +
        'Unshare' +
        '</button></li>'
      );
    }).catch(err=>{
      console.log(err)
    })
  })


  $(document).on('click', '.details-remove-button.share', function () {
    let userId = $(this).data('id')
    API.remove_shared('group', groupId, userId).then(()=>{
      $("#shared-with-list ." + userId).remove()
    })
  })

  /**
   * Group Status
   */
  let statusSelect = $('#group-status-select')
  let statusLabel = $('#group-status-label')

  statusLabel.on('click', function () {
    toggleEdit('status')
  })
  statusSelect.on('change', function () {
    API.save_field_api('group', groupId, {group_status:statusSelect.val()}).then(group=>{
      statusLabel.text(`Status: ${_.get(group, "group_status.label")}`)
      toggleEdit('status')
    })
  })
  statusSelect.bind('blur', ()=>{
    toggleEdit('status')
  })


  $(document).on('click', '.details-status-button.verify', function () {
    let id = $(this).data('id')
    let verified = $(this).data('verified')
    if (id){
      console.log('verify')
      API.update_contact_method_detail('group', groupId, id, {"verified":!verified}).then(()=>{
        $(this).data('verified', !verified)
        if (verified){
          jQuery(`#${id}-verified`).hide()
        } else {
          jQuery(`#${id}-verified`).show()

        }
        jQuery(this).html(verified ? "Verify" : "Unverify")
      }).catch(err=>{
        console.log(err)
      })
    }
  })
  $(document).on('click', '.details-status-button.invalid', function () {
    let id = $(this).data('id')
    let invalid = $(this).data('invalid')
    API.update_contact_method_detail('group', groupId, id, {"invalid":!invalid}).then(()=>{
      $(this).data('invalid', !invalid)
      if (invalid){
        jQuery(`#${id}-invalid`).hide()
      } else  {
        jQuery(`#${id}-invalid`).show()
      }
      jQuery(this).html(invalid? "Invalidate" : "Uninvalidate")
    })
  })

  $('.text-field.details-edit').change(function () {
    let id = $(this).attr('id')
    let val = $(this).val()
    API.save_field_api(
      'group',
      groupId,
      {[id]:val}
    ).then(()=>{
      $(`.${id}`).text(val)
    }).catch(err=>{
      console.log(err)
    })
  })
})



