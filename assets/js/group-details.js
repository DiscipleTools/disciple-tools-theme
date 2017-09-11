/* global jQuery:false, wpApiSettings:false */

jQuery(document).ready(function($) {
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

/**
 * Typeahead functions
 */

function add_typeahead_item(groupId, fieldId, val, name) {
  add_item_to_field(groupId, { [fieldId]: val }).done(function (addedItem){
    jQuery(`.${fieldId}-list`).append(`<li class="${addedItem.ID}">
    <a href="${addedItem.permalink}">${addedItem.post_title}</a>
    <button class="details-remove-button details-edit"
            data-field="locations" data-id="${val}"
            data-name="${name}"  
            style="display: inline-block">
      Remove
    </button>
    </li>`)
    jQuery(".connections-edit").show()
  })
}

function filterTypeahead(array, existing){
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
    remove_item_from_field(groupId, fieldId, itemId).done(()=>{
      $(`.${fieldId}-list .${itemId}`).remove()

      //add the item back to the locations list
      if (fieldId === 'locations'){
        locations.add([{ID:itemId, name: $(this).data('name')}])
      }
    })
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
  let locations = new Bloodhound({
    datumTokenizer: searchAnyPieceOfWord,
    queryTokenizer: Bloodhound.tokenizers.ngram,
    identify: function (obj) {
      return obj.ID
    },
    prefetch: {
      url: wpApiSettings.root + 'dt/v1/locations-compact/',
      transform: function(data){
        return filterTypeahead(data, group.locations || [])
      },
      cache: false
    },
    remote: {
      url: wpApiSettings.root + 'dt/v1/locations-compact/?s=%QUERY',
      wildcard: '%QUERY',
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
   * Get the group fields from the api
   */

  get_group(groupId).done(function (groupData) {
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
  })



})



