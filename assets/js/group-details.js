/* global jQuery:false, wpApiSettings:false */

function save_field_api(groupId, post_data){
  return jQuery.ajax({
    type:"POST",
    data:JSON.stringify(post_data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/group/'+ groupId,
  })
}

function get_group(groupId){
  return jQuery.ajax({
    type:"GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/group/'+ groupId
  })
}

function add_item_to_field(groupId, post_data) {
  return jQuery.ajax({
    type: "POST",
    data: JSON.stringify(post_data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/group/' + groupId + '/details',
  })
}

function remove_item_from_field(groupId, fieldKey, valueId) {
  let data = {key: fieldKey, value: valueId}
  return jQuery.ajax({
    type: "DELETE",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: wpApiSettings.root + 'dt-hooks/v1/group/' + groupId + '/details',
  })
}

function remove_item(groupId, fieldId, itemId) {
  remove_item_from_field(groupId, fieldId, itemId).done(()=>{
    jQuery(`.${fieldId}-list .${itemId}`).remove()
  })
}

function add_typeahead_item(groupId, fieldId, val) {
  add_item_to_field(groupId, { [fieldId]: val }).done(function (addedItem){
    jQuery(`.${fieldId}-list`).append(`<li class="${addedItem.ID}">
    <a href="${addedItem.permalink}">${addedItem.post_title}</a>
    <button class="details-remove-button details-edit" 
        onclick="remove_item(${groupId}, '${fieldId}', ${addedItem.ID})" 
        style="display: inline-block">
      Remove
    </button>
    </li>`)
    jQuery(".connections-edit").show()
  })
}


/**
 * Typeahead functions
 */
function defaultFilter(q, sync, local) {
  if (q === '') {
    sync(local.all());
  }
  else {
    local.search(q, sync);
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
      save_field_api(groupId, {end_date:date}).done(function () {
        endDateList.text(date)
        toggleEdit('end_date')
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
      save_field_api(groupId, {start_date:date}).done(function () {
        startDateList.text(date)
        toggleEdit('start_date')
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
    save_field_api(groupId, {assigned_to: 'user-' + sug.ID}).done(function () {
      assigned_to_typeahead.typeahead('val', '')
      jQuery('.current-assigned').text(sug.display_name)
    })
  }).bind('blur', ()=>{
    console.log("blue")
    toggleEdit('assigned_to')
  })

  /**
   * Locations
   */

  var locations = new Bloodhound({
    datumTokenizer: searchAnyPieceOfWord,
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.name
    },
    prefetch: {
      url: wpApiSettings.root + 'dt/v1/locations-compact/',
    },
    remote: {
      url: wpApiSettings.root + 'dt/v1/locations-compact/?s=%QUERY',
      wildcard: '%QUERY'
    }
  });

  let locationsTypeahead = $('.locations .typeahead')
  locationsTypeahead.typeahead({
      highlight: true,
      minLength: 0,
      autoselect: true,
    },
    {
      name: 'locations',
      source: function (q, sync) {
        return defaultFilter(q, sync, locations)
      },
      display: 'name'
    })
    .bind('typeahead:select', function (ev, sug) {
      locationsTypeahead.typeahead('val', '')
      locationsTypeahead.blur()
      add_typeahead_item(groupId, 'locations', sug.ID)
    })


  /**
   * Get the group fields from the api
   */
  get_group(groupId).done(function (group) {
    console.log(group)
    if (group.end_date){
      endDatePicker.datepicker('setDate', group.end_date)
    }
    if (group.start_date){
      startDatePicker.datepicker('setDate', group.start_date)
    }
    if (group.assigned_to){
      $('.current-assigned').text(_.get(group, "assigned_to.display"))
    }
  })



})



