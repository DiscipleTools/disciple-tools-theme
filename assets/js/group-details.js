/* global jQuery:false, wpApiSettings:false */

jQuery(document).ready(function($) {


  $( document ).ajaxComplete(function(event, xhr, settings) {
    if (settings && settings.type && (settings.type === "POST" || settings.type === "DELETE")){
      refreshActivity()
    }
  });

  /**
   * Typeahead functions
   */

  function add_typeahead_item(groupId, fieldId, val, name) {
    API.add_item_to_field( 'group', groupId, { [fieldId]: val }).then(function (addedItem){
      jQuery(`.${fieldId}-list`).append(`<li class="${addedItem.ID}">
      <a href="${addedItem.permalink}">${_.escape(addedItem.post_title)}</a>
      <button class="details-remove-button details-edit"
              data-field="locations" data-id="${val}"
              data-name="${name}"  
              style="display: inline-block">Remove</button>
      </li>`)
    })
  }

  function filterTypeahead(array, existing = []){
    return _.differenceBy(array, existing.map(l=>{
      return {ID:l.ID, name:l.display_name}
    }), "ID")
  }

  function defaultFilter(q, sync, async, local, existing) {
    if (q === '') {
      sync(filterTypeahead(local.all(), existing));
    }
    else {
      local.search(q, sync, async);
    }
  }
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


  let group = {}
  let groupId = $('#group-id').text()
  let editingAll = false



  /**
   * Group details Info
   */
  function toggleEditAll() {
    $(`.details-list`).toggle()
    $(`.details-edit`).toggle()
    editingAll = !editingAll
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
    console.log(e)
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
    console.log(sug)
    API.save_field_api('group', groupId, {assigned_to: 'user-' + sug.ID}).then(function () {
      assigned_to_typeahead.typeahead('val', '')
      jQuery('.current-assigned').text(sug.name)
    }).catch(err=>{
      console.trace("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    })
  }).bind('blur', ()=>{
    toggleEdit('assigned_to')
  })


  /**
   * Locations
   */
  let locations = new Bloodhound({
    datumTokenizer: searchAnyPieceOfWord,
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.ID
    },
    prefetch: {
      url: wpApiSettings.root + 'dt/v1/locations-compact/',
      prepare : API.typeaheadPrefetchPrepare,
      transform: function(data){
        return filterTypeahead(data, group.locations || [])
      },
      cache: false
    },
    remote: {
      url: wpApiSettings.root + 'dt/v1/locations-compact/?s=%QUERY',
      wildcard: '%QUERY',
      prepare : API.typeaheadRemotePrepare,
      transform: function(data){
        return filterTypeahead(data, group.locations || [])
      }
    },
    initialize: false,
    local : []
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
        return defaultFilter(q, sync, async, locations, group.locations)
      },
      display: 'name'
    })
  }
  locationsTypeahead.bind('typeahead:select', function (ev, sug) {
    locationsTypeahead.typeahead('val', '')
    group.locations.push(sug)
    add_typeahead_item(groupId, 'locations', sug.ID, sug.name)
    locationsTypeahead.typeahead('destroy')
    loadLocationsTypeahead()
  })
  loadLocationsTypeahead()


  /**
   * Addresses
   */
  var button = $('.address.details-edit.add-button')
  console.log(button)
  button.on('click', e=>{
    console.log(e)
    if ($('#new-address').length === 0 ) {
      let newInput = `<li>
        <textarea id="new-address"></textarea>
      </li>`
      $('.details-edit.address-list').append(newInput)
    }
  })
  //for a new address field that has not been saved yet
  $(document).on('change', '#new-address', function (val) {
    let input = $('#new-address')
    API.add_item_to_field( 'group', groupId, {"new-address":input.val()}).then(function (data) {
      if (data != groupId){
        //change the it to the created field
        input.attr('id', data)
        $('.details-list.address').append(`<li class="${data}">${input.val()}</li>`)
      }

    })
  })
  $(document).on('change', '.address-list textarea', function(){
    let id = $(this).attr('id')
    if (id && id !== "new-address"){
      API.save_field_api('group', groupId, {[id]: $(this).val()}).then(()=>{
        $(`.address.details-list .${id}`).text($(this).val())
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
    datumTokenizer: searchAnyPieceOfWord,
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.ID
    },
    prefetch: {
      url: wpApiSettings.root + 'dt-hooks/v1/contacts/compact',
      transform: function(data){
        loadMembersTypeahead()
        return filterTypeahead(data, group.members || [])
      },
      prepare : API.typeaheadPrefetchPrepare,
      cache: false
    },
    remote: {
      url: wpApiSettings.root + 'dt-hooks/v1/contacts/compact/?s=%QUERY',
      wildcard: '%QUERY',
      transform: function(data){
        return filterTypeahead(data, group.members || [])
      },
      prepare : API.typeaheadRemotePrepare,
    },
    initialize: false,
    local : []
  });

  let membersTypeahead = $('#members .typeahead')
  function loadMembersTypeahead() {
    membersTypeahead.typeahead('destroy')
    membersTypeahead.typeahead({
        highlight: true,
        minLength: 0,
        autoselect: true,
      },
      {
        name: 'members',
        source: function (q, sync, async) {
          return defaultFilter(q, sync, async, members, group.members)
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
   * Get the group fields from the api
   */

  API.get_post( 'group', groupId).then(function (groupData) {
    console.log(groupData)
    group = groupData
    if (groupData.end_date){
      endDatePicker.datepicker('setDate', groupData.end_date)
    }
    if (groupData.start_date){
      startDatePicker.datepicker('setDate', groupData.start_date)
    }
    if (groupData.assigned_to){
      $('.current-assigned').text(_.get(groupData, "assigned_to.display"))
    }
    locations.initialize()
    members.initialize()
    fillOutChurchHealthMetrics()
  })


  /**
   * Comments and Activity
   */

  let comments = []
  let activity = []

  function refreshActivity() {
    API.get_activity('group', groupId).then(activityData=>{
      activityData.forEach(d=>{
        d.date = new Date(d.hist_time*1000)
      })
      activity = activityData
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
        commentData.comment.date = new Date(commentData.comment.comment_date_gmt + "Z")
        comments.push(commentData.comment)
        display_activity_comment()
      })
    })

  $.when(
    API.get_comments('group', groupId),
    API.get_activity('group', groupId)
  ).then(function(commentData, activityData){
    commentData[0].forEach(comment=>{
      comment.date = new Date(comment.comment_date_gmt + "Z")
    })
    comments = commentData[0]
    activityData[0].forEach(d=>{
      d.date = new Date(d.hist_time*1000)
    })
    activity = activityData[0]
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
        if (group[`church_${m}`] && ["1", "Yes"].indexOf(group[`church_${m}`])> -1){
          churchWheel.find(`#${m}`).css("opacity", "1")
        } else {
          churchWheel.find(`#${m}`).css("opacity", ".1")
        }
      })
      if (!group["church_commitment"] || group["church_commitment"] === '0'){
        churchWheel.find('#group').css("opacity", "1")
      } else {
        churchWheel.find('#group').css("opacity", ".1")
      }
  }

  $('.group-progress-button').on('click', function () {
    let fieldId = $(this).attr('id')
    $(this).css('opacity', ".5");
    let field = group[fieldId] === "1" ? "0" : "1"
    API.save_field_api('group', groupId, {[fieldId]: field})
      .then(groupData=>{
        group = groupData
        fillOutChurchHealthMetrics()
        $(this).css('opacity', "1");
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
    console.log(select.val())
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

})



