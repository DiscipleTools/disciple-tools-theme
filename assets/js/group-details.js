/* global jQuery:false, wpApiSettings:false */

function save_field_api(groupId, post_data, callback){
  jQuery.ajax({
    type:"POST",
    data:JSON.stringify(post_data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/group/'+ groupId,
    success: function(data) {
      console.log("updated " + JSON.stringify(post_data))
      console.log(data)
      callback(data)
      // @todo
      // get_activity(contactId)
    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })
}

function get_group(groupId, callback){
  jQuery.ajax({
    type:"GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/group/'+ groupId,
    success: function(data) {
      console.log(data)
      callback(data)
      // @todo
      // get_activity(contactId)
    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
    },
  })
}


jQuery(document).ready(function($) {

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
      console.log(date)
      save_field_api(groupId, {end_date:date}, function () {
        endDateList.text(date)
        $('.end_date.details-edit').hide()
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
      console.log(date)
      save_field_api(groupId, {start_date:date}, function () {
        startDateList.text(date)
        $('.start_date.details-edit').hide()
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
  var users = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('display_name'),
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.display_name
    },
    prefetch: {
      url: wpApiSettings.root + 'dt/v1/users/',
    },
    remote: {
      url: wpApiSettings.root + 'dt/v1/users/?s=%QUERY',
      wildcard: '%QUERY'
    }
  });

  function defaultusers(q, sync, async) {
    if (q === '') {
      sync(users.all());
    }
    else {
      users.search(q, sync, async);
    }
  }

  let assigned_to_typeahead = $('.assigned_to .typeahead')
  assigned_to_typeahead.typeahead({
    highlight: true,
    minLength: 0,
    autoselect: true,
  },
  {
    name: 'users',
    source: defaultusers,
    display: 'display_name'
  })
  .bind('typeahead:select', function (ev, sug) {
    console.log(sug)
    save_field_api(groupId, {assigned_to: 'user-' + sug.ID}, function () {
      assigned_to_typeahead.typeahead('val', '')
      jQuery('.current-assigned').text(sug.display_name)
    })
  }).bind('blur', ()=>{
    console.log("blue")
    toggleEdit('assigned_to')
  })


  get_group(groupId, function (group) {
    console.log(group)
    if (group.end_date){
      endDatePicker.datepicker('setDate', group.end_date)
    }
    if (group.assigned_to){
      $('.current-assigned').text(_.get(group, "assigned_to.display"))
    }
  })



})



