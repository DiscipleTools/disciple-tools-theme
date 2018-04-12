/* global jQuery:false, wpApiGroupsSettings:false */
jQuery(document).ready(function($) {

  let group = wpApiGroupsSettings.group
  let typeaheadTotals = {}
  let masonGrid = $('.grid')
  /**
   * Typeahead functions
   */

  let typeaheadSource = function (field, url) {
    return {
      contacts: {
        display: "name",
        ajax: {
          url: wpApiGroupsSettings.root + url,
          data: {
            s: "{{query}}"
          },
          beforeSend: function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
          },
          callback: {
            done: function (data) {
              typeaheadTotals[field] = data.total
              return data.posts
            }
          }
        }
      }
    }
  }

  let typeaheadHelpText = (resultCount, query, result) =>{
    var text = "";
    if (result.length > 0 && result.length < resultCount) {
      text = "Showing <strong>" + result.length + "</strong> of <strong>" + resultCount + '</strong> ' + (query ? 'elements matching "' + query + '"' : '');
    } else if (result.length > 0 && query) {
      text = 'Showing <strong>' + result.length + '</strong> items matching "' + query + '"';
    } else if (result.length > 0) {
      text = 'Showing <strong>' + result.length + '</strong> items';
    } else {
      text = 'No results matching "' + query + '"';
    }
    return text
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
   * Assigned_to
   */
  typeaheadTotals.assigned_to = 0;
  $.typeahead({
    input: '.js-typeahead-assigned_to',
    minLength: 0,
    searchOnFocus: true,
    source: typeaheadSource('assigned_to', 'dt/v1/users/get_users'),
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    hint: true,
    emptyTemplate: 'No users found "{{query}}"',
    callback: {
      onClick: function(node, a, item, event){
        API.save_field_api('group', groupId, {assigned_to: 'user-' + item.ID}).then(function (response) {
          _.set(group, "assigned_to", response.assigned_to)
          $('.current-assigned').text(group.assigned_to.display)
          $('.js-typeahead-assigned_to').val(group.assigned_to.display)
          $('.js-typeahead-assigned_to').trigger('propertychange.typeahead')
        })
      },
      onResult: function (node, query, result, resultCount) {
        resultCount = typeaheadTotals.assigned_to
        let text = typeaheadHelpText(resultCount, query, result)
        $('#assigned_to-result-container').html(text);
      },
      onHideLayout: function () {
        $('.assigned_to-result-container').html("");
      },
      onReady: function () {
        $('.details.assigned_to').addClass('details-edit')
        if (_.get(group,  "assigned_to.display")){
          $('.js-typeahead-assigned_to').val(group.assigned_to.display)
        }
        $('.js-typeahead-assigned_to').trigger('propertychange.typeahead')
        $('.assigned_to-result-container').html("");
      }
    },
    debug:true
  });
  $('.search_assigned_to').on('click', function () {
    let id = $(this).data("id")
    $(`#${id} .js-typeahead-assigned_to`).val("")
    $(`#${id} .js-typeahead-assigned_to`).trigger('input.typeahead')
  })

  /**
   * Share
   */
  let shareTypeahead = null
  $('.open-share').on("click", function(){
    $('#share-contact-modal').foundation('open');
    if  (!shareTypeahead) {
      shareTypeahead = TYPEAHEADS.share("group", groupId)
    }
  })

  /**
   * Locations
   */
  typeaheadTotals.locations = 0;
  $.typeahead({
    input: '.js-typeahead-locations',
    minLength: 0,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      return `<span>${_.escape(item.name)}</span>`
    },
    source: typeaheadSource('locations', 'dt/v1/locations-compact/'),
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      data: function () {
        return group.locations.map(g=>{
          return {ID:g.ID, name:g.post_title}
        })
      }, callback: {
        onCancel: function (node, item) {
          API.save_field_api('group', groupId, {'locations': {values:[{value:item.ID, delete:true}]}}).then(()=>{            $(`.locations-list .${item.ID}`).remove()
            let listItems = $(`.locations-list li`)
            if (listItems.length === 0){
              $(`.locations-list.details-list`).append(`<li id="no-location">${wpApiGroupsSettings.translations["not-set"]["location"]}</li>`)
            }
          })
        }
      }
    },
    callback: {
      onClick: function(node, a, item, event){
        API.save_field_api('group', groupId, {'locations': {values:[{value:item.ID}]}}).then((addedItem)=>{
          $('.locations-list').append(`<li class="${addedItem.ID}">
            <a href="${addedItem.permalink}">${_.escape(addedItem.post_title)}</a>
          </li>`)
          $("#no-location").remove()
        })
      },
      onResult: function (node, query, result, resultCount) {
        resultCount = typeaheadTotals.locations
        let text = typeaheadHelpText(resultCount, query, result)
        $('#locations-result-container').html(text);
      },
      onHideLayout: function () {
        $('#locations-result-container').html("");
      },
      onReady: function () {
        console.log("ready");
        $('.locations').addClass('details-edit')
      }
    }
  });

  /**
   * People_groups
   */
  typeaheadTotals.people_groups = 0;
  $.typeahead({
    input: '.js-typeahead-people_groups',
    minLength: 0,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      return `<span>${_.escape(item.name)}</span>`
    },
    source: typeaheadSource('people_groups', 'dt/v1/people-groups-compact/'),
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      data: function () {
        return group.people_groups.map(g=>{
          return {ID:g.ID, name:g.post_title}
        })
      },
      callback: {
        onCancel: function (node, item) {
          API.save_field_api('group', groupId, {'people_groups': {values:[{value:item.ID, delete:true}]}}).then(()=>{
            $(`.people_groups-list .${item.ID}`).remove()
            let listItems = $(`.people_groups-list li`)
            if (listItems.length === 0){
              $(`.people_groups-list.details-list`).append(`<li id="no-people-group">${wpApiGroupsSettings.translations["not-set"]["people-group"]}</li>`)
            }
          })
        }
      },
    },
    callback: {
      onClick: function(node, a, item, event){
        API.save_field_api('group', groupId, {'people_groups': {values:[{value:item.ID}]}}).then((addedItem)=>{
          $("#no-people-group").remove()
          $('.people_groups-list').append(`<li class="${addedItem.ID}">
            <a href="${addedItem.permalink}">${_.escape(addedItem.post_title)}</a>
          </li>`)
        })
      },
      onResult: function (node, query, result, resultCount) {
        resultCount = typeaheadTotals.people_groups
        let text = typeaheadHelpText(resultCount, query, result)
        $('#people_groups-result-container').html(text);
      },
      onHideLayout: function () {
        $('#people_groups-result-container').html("");
      },
      onReady: function () {
        $('.people_groups').addClass('details-edit')
      }
    }
  });

  /**
   * parent Groups
   */
  typeaheadTotals.groups = 0;
  $.typeahead({
    input: '.js-typeahead-parent_groups',
    minLength: 0,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      if (item.ID == "new-item"){
        return "Create new Group"
      }
      return `<span>${_.escape(item.name)}</span>`
    },
    source: typeaheadSource('groups', 'dt/v1/groups-compact/'),
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      data: function () {
        return (group.parent_groups||[]).map(g=>{
          return {ID:g.ID, name:g.post_title}
        })
      }, callback: {
        onCancel: function (node, item) {
          API.save_field_api('group', groupId, {'parent_groups': {values:[{value:item.ID, delete:true}]}})
        }
      },
      href: function(item){
        if (item){
          return `/groups/${item.ID}`
        }
      }
    },
    callback: {
      onClick: function(node, a, item, event){
        if(item.ID === "new-item"){
          event.preventDefault();
          $('#create-group-modal').foundation('open');
        } else {
          API.save_field_api('group', groupId, {'parent_groups': {values:[{value:item.ID}]}})
          masonGrid.masonry('layout')
        }
      },
      onResult: function (node, query, result, resultCount) {
        resultCount = typeaheadTotals.groups
        let text = typeaheadHelpText(resultCount, query, result)
        result.push({
          ID: "new-item",
          group:"contacts"
        })
        $('#groups-result-container').html(text);
      },
      onHideLayout: function () {
        $('#groups-result-container').html("");
      }
    }
  });
  /**
   * Child Groups
   */
  typeaheadTotals.groups = 0;
  $.typeahead({
    input: '.js-typeahead-child_groups',
    minLength: 0,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      if (item.ID == "new-item"){
        return "Create new Group"
      }
      return `<span>${_.escape(item.name)}</span>`
    },
    source: typeaheadSource('groups', 'dt/v1/groups-compact/'),
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      data: function () {
        return (group.child_groups||[]).map(g=>{
          return {ID:g.ID, name:g.post_title}
        })
      }, callback: {
        onCancel: function (node, item) {
            API.save_field_api('group', groupId, {'child_groups': {values:[{value:item.ID, delete:true}]}})
          }
      },
      href: function(item){
        if (item){
          return `/groups/${item.ID}`
        }
      }
    },
    callback: {
      onClick: function(node, a, item, event){
        if(item.ID === "new-item"){
          event.preventDefault();
          $('#create-group-modal').foundation('open');
        } else {
          API.save_field_api('group', groupId, {'child_groups': {values:[{value:item.ID}]}})
        }
      },
      onResult: function (node, query, result, resultCount) {
        resultCount = typeaheadTotals.groups
        let text = typeaheadHelpText(resultCount, query, result)
        result.push({
          ID: "new-item",
          group:"contacts"
        })
        $('#groups-result-container').html(text);
      },
      onHideLayout: function () {
        $('#groups-result-container').html("");
      }
    }
  });

  //reset new group modal on close.
  $('#create-group-modal').on("closed.zf.reveal", function () {
    $(".reveal-after-group-create").hide()
    $(".hide-after-group-create").show()
  })

  //create new group
  $(".js-create-group").on("submit", function(e) {
    e.preventDefault();
    let title = $(".js-create-group input[name=title]").val()
    API.create_group(title, null, groupId)
      .then((newGroup)=>{
        $(".reveal-after-group-create").show()
        $("#new-group-link").html(`<a href="${newGroup.permalink}">${title}</a>`)
        $(".hide-after-group-create").hide()
        $('#go-to-group').attr('href', newGroup.permalink);
        Typeahead['.js-typeahead-child_groups'].addMultiselectItemLayout({ID:newGroup.post_id.toString(), name:title})
      })
      .catch(function(error) {
        $(".js-create-group-button").removeClass("loading").addClass("alert");
        $(".js-create-group").append(
          $("<div>").html(error.responseText)
        );
        console.error(error);
      });
  })

  $("#add-new-address").click(function () {
    if ($('#new-address').length === 0 ) {
      let newInput = `<div class="new-address">
        <textarea rows="3" id="new-address"></textarea>
      </div>`
      $('.details-edit#address-list').append(newInput)
    }
  })

  /**
   * members
   */
  typeaheadTotals.members = 0;
  $.typeahead({
    input: '.js-typeahead-members',
    minLength: 0,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      return `<span>${_.escape(item.name)}</span>`
    },
    source: typeaheadSource('members', 'dt/v1/contacts/compact/'),
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      data: function () {
        return group.members.map(g=>{
          return {ID:g.ID, name:g.post_title}
        })
      }, callback: {
        onCancel: function (node, item) {
          API.save_field_api('group', groupId, {'members': {values:[{value:item.ID, delete:true}]}}).then(()=>{
            $(`.members-list .${item.ID}`).remove()
            let listItems = $(`.members-list li`)
            if (listItems.length === 0){
              $(`.members-list.details-list`).append(`<li id="no-location">${wpApiGroupsSettings.translations["not-set"]["location"]}</li>`)
            }
          })
        }
      },
      href: "/contacts/{{ID}}"
    },
    callback: {
      onClick: function(node, a, item, event){
        API.save_field_api('group', groupId, {'members': {values:[{value:item.ID}]}}).then((addedItem)=>{
          $('.members-list').append(`<li class="${addedItem.ID}">
            <a href="${addedItem.permalink}">${_.escape(addedItem.post_title)}</a>
          </li>`)
          $("#no-location").remove()
        })
        masonGrid.masonry('layout')
      },
      onResult: function (node, query, result, resultCount) {
        resultCount = typeaheadTotals.members
        let text = typeaheadHelpText(resultCount, query, result)
        $('#members-result-container').html(text);
      },
      onHideLayout: function () {
        $('#members-result-container').html("");
      }
    }
  });

  /**
   * leaders
   */
  typeaheadTotals.leaders = 0;
  $.typeahead({
    input: '.js-typeahead-leaders',
    minLength: 0,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      return `<span>${_.escape(item.name)}</span>`
    },
    source: typeaheadSource('leaders', 'dt/v1/contacts/compact/'),
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      data: function () {
        return group.leaders.map(g=>{
          return {ID:g.ID, name:g.post_title}
        })
      }, callback: {
        onCancel: function (node, item) {
          API.save_field_api('group', groupId, {'leaders': {values:[{value:item.ID, delete:true}]}}).then(()=>{
            $(`.leaders-list .${item.ID}`).remove()
            let listItems = $(`.leaders-list li`)
            if (listItems.length === 0){
              $(`.leaders-list.details-list`).append(`<li id="no-location">${wpApiGroupsSettings.translations["not-set"]["location"]}</li>`)
            }
          })
        }
      },
      href: "/contacts/{{ID}}"
    },
    callback: {
      onClick: function(node, a, item, event){
        API.save_field_api('group', groupId, {'leaders': {values:[{value:item.ID}]}}).then((addedItem)=>{
          $('.leaders-list').append(`<li class="${addedItem.ID}">
            <a href="${addedItem.permalink}">${_.escape(addedItem.post_title)}</a>
          </li>`)
          $("#no-location").remove()
        })
        masonGrid.masonry('layout')
      },
      onResult: function (node, query, result, resultCount) {
        resultCount = typeaheadTotals.leaders
        let text = typeaheadHelpText(resultCount, query, result)
        $('#leaders-result-container').html(text);
      },
      onHideLayout: function () {
        $('#leaders-result-container').html("");
      },
      onReady: function () {
        $('.leaders').addClass('details-edit')
      }
    }
  });


  //for a new address field that has not been saved yet
  $(document).on('change', '#new-address', function (val) {
    let input = $('#new-address')
    API.save_field_api('group', groupId, {'contact_address': [{value:input.val()}]})
      .then(function (group) {
        console.log(group);
        let newAddressId = _.get(_.last(_.get(group, `contact_address`)), "key")
        console.log(newAddressId)
      if (newAddressId != groupId){
        //change the it to the created field
        input.attr('id', newAddressId)
        $('.details-list.address').append(`
          <li class="${newAddressId} address-row">
            <div class="address-text">${input.val()}</div>
            <img id="${newAddressId}-verified" class="details-status" style="display:none" src="${wpApiGroupsSettings.template_dir}/dt-assets/images/verified.svg"/>
            <img id="${newAddressId}-invalid" class="details-status" style="display:none" src="${wpApiGroupsSettings.template_dir}/dt-assets/images/broken.svg"/>
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
            <img src="${wpApiGroupsSettings.template_dir}/dt-assets/images/menu-dots.svg" style='padding:3px 3px'>
          </button>
          <ul class='menu'>
            <li>
              <button class='details-status-button field-status verify'
                      data-status='valid'
                      data-id='${field_id}'>
                  ${wpApiGroupsSettings.translations.valid}
              </button>
            </li>
            <li>
              <button class='details-status-button field-status invalid'
                      data-status="invalid"
                      data-id="${field_id}">
                  ${wpApiGroupsSettings.translations.invalid}
              </button>
            </li>
            <li>
              <button class='details-status-button field-status'
                      data-status="reset"
                      data-id='${field_id}'>
                  ${wpApiGroupsSettings.translations.unconfirmed}
              </button>
            </li>
            <li>
              <button class='details-remove-button delete-method'
                      data-id='${field_id}'>
                ${wpApiGroupsSettings.translations.delete}
              <button>
            </li>
          </ul>
          </li>
      </ul>
    `
  }

  $(document).on('click', '.details-remove-button.delete-method', function () {
    let fieldId = $(this).data('id')
    let fieldType = $(this).data('field')
    if (fieldId){
      API.save_field_api('group', groupId, {[`contact_${fieldType}`]:[{key:fieldId, delete:true}]}).then(()=>{
        $(`.${fieldId}`).remove()
        let listItems = $(`.${fieldType}-list li`)
        if (listItems.length === 0){
          $(`.${fieldType}.details-list`).append(`<li id="no-${fieldType}">${wpApiGroupsSettings.translations["not-set"][fieldType]}</li>`)
        }
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

  //check if we still need to wait for the svg to load.
  if ($('#church-svg-wrapper')[0].contentDocument == null) {
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
   * Group Status
   */

  let selectFiled = $('.select-field')
  selectFiled.on('change', function () {
    let id = $(this).attr('id')
    let val = $(this).val()
    API.save_field_api(
      'group',
      groupId,
      {[id]:val}
    ).catch(err=>{
      console.log(err)
    })
  })


  $(document).on('click', '.details-status-button.field-status', function () {
    let status = $(this).data('status')
    let id = $(this).data('id')
    let field = $(this).data('field')
    console.log(status, id)
    let fields = {
      key: id,
      verified : status === 'valid',
      invalid : status === "invalid"
    }
    API.save_field_api('group', groupId, {[`contact_${field}`]:[fields]}).then(()=>{
      $(`#${id}-verified`).toggle(fields.verified)
      $(`#${id}-invalid`).toggle(fields.invalid)
    }).catch(err=>{
      handelAjaxError(err)
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



  //leave at the end
  masonGrid.masonry({
    itemSelector: '.grid-item',
    percentPosition: true
  });

})



