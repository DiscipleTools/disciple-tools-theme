/* global jQuery:false, contactsDetailsWpApiSettings:false, moment:false, _:false */

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
    field.blur()
    field.addClass( fieldValue === "no" ? "empty-select-button" : "selected-select-button")
  }).catch(err=>{
      console.log("error")
      console.log(err)
      jQuery("#errors").text(err.responseText)
      field.removeClass("submitting-select-button selected-select-button")
      field.addClass( fieldValue === "yes" ? "empty-select-button" : "selected-select-button")
  })
}

let refresh_quick_action_buttons = (contact)=>{
  Object.keys(contactsDetailsWpApiSettings.contacts_custom_fields_settings).forEach(field=>{
    if (field.includes("quick_button_")){
      if ( contact[field] ){
        jQuery("." + field +  " span").text(contact[field])
      }
    }
  })
}

function save_quick_action(contactId, fieldKey){
  let data = {}
  let numberIndicator = jQuery("." + fieldKey +  " span")
  let newNumber = parseInt(numberIndicator.first().text() || "0" ) + 1
  data[fieldKey] = newNumber
  API.save_field_api("contact", contactId, data)
  .then(data=>{
    console.log(data);
    console.log("updated " + fieldKey + " to: " + newNumber)
    if (fieldKey.indexOf("quick_button")>-1){
      if (_.get(data, "seeker_path.key")){
        updateCriticalPath(data.seeker_path.key)
      }
    }
    contactUpdated(false)
  }).catch(err=>{
      console.log("error")
      console.log(err)
      jQuery("#errors").append(err.responseText)
  })

  if (fieldKey.indexOf("quick_button")>-1){
    numberIndicator.text(newNumber)
  }
}

function updateCriticalPath(key) {
  $('#seeker_path').val(key)
  let seekerPathKeys = _.keys(contactsDetailsWpApiSettings.contacts_custom_fields_settings.seeker_path.default)
  let percentage = (_.indexOf(seekerPathKeys, key) || 0) / (seekerPathKeys.length-1) * 100
  $('#seeker-progress').css("width", `${percentage}%`)
}

function contactUpdated(updateNeeded) {
  $('.update-needed-notification').hide()
  $('#update-needed').prop("checked", updateNeeded)

}



let contact = {}
let typeaheadTotals = {};

jQuery(document).ready(function($) {
  let contactId = $("#contact-id").text()
  contact = contactsDetailsWpApiSettings.contact
  let editFieldsUpdate = {}
  let masonGrid = $('.grid')


  $( document ).ajaxComplete(function(event, xhr, settings) {
    if (settings && settings.type && (settings.type === "POST" || settings.type === "DELETE")){
      if (_.get(xhr, "responseJSON.ID")){
        contact = xhr.responseJSON
        let updateNeeded = _.get(contact, "requires_update.key") === "yes"
        console.log("set to: " + updateNeeded)
        contactUpdated(updateNeeded)
      }
    }
  }).ajaxError(handelAjaxError)

  /**
   * Groups
   */
  typeaheadTotals.groups = 0;
  $.typeahead({
    input: '.js-typeahead-groups',
    minLength: 0,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      if (item.ID == "new-item"){
        return "Create new Group"
      }
      return `<span>${_.escape(item.name)}</span>`
    },
    source: TYPEAHEADS.typeaheadSource('groups', 'dt/v1/groups-compact/'),
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      data: function () {
        return contact.groups.map(g=>{
          return {ID:g.ID, name:g.post_title}
        })
      }, callback: {
        onCancel: function (node, item) {
          API.save_field_api('contact', contactId, {groups: {values:[{value:item.ID, delete:true}]}})
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
          API.save_field_api('contact', contactId, {groups: {values:[{value:item.ID}]}})
          masonGrid.masonry('layout')
        }
        console.log(node)
        console.log(a)
        console.log(event)
        node.blur()
        a.blur()
      },
      onResult: function (node, query, result, resultCount) {
        resultCount = typeaheadTotals.groups
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
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
    API.create_group(title,contactId)
      .then((newGroup)=>{
        $(".reveal-after-group-create").show()
        $("#new-group-link").html(`<a href="${newGroup.permalink}">${title}</a>`)
        $(".hide-after-group-create").hide()
        $('#go-to-group').attr('href', newGroup.permalink);
        Typeahead['.js-typeahead-groups'].addMultiselectItemLayout({ID:newGroup.post_id.toString(), name:title})
      })
      .catch(function(error) {
        $(".js-create-group-button").removeClass("loading").addClass("alert");
        $(".js-create-group").append(
          $("<div>").html(error.responseText)
        );
        console.error(error);
      });
  })


  /**
   * Sources
   */
  typeaheadTotals.sources = 0;
  let leadSourcesTypeahead = ()=>{
    if (!window.Typeahead['.js-typeahead-sources']){

      let sourcesData = []
      console.log(contact);
      _.forOwn(contactsDetailsWpApiSettings.contacts_custom_fields_settings.sources.default, (sourceValue, sourceKey)=>{
        sourcesData.push({key:sourceKey, value:sourceValue})
      })
      if (contactsDetailsWpApiSettings.can_view_all){
        $.typeahead({
          input: '.js-typeahead-sources',
          minLength: 0,
          searchOnFocus: true,
          maxItem: 20,
          source: {
            data: sourcesData
          },
          display: "value",
          templateValue: "{{value}}",
          dynamic: true,
          multiselect: {
            matchOn: ["key"],
            data: function () {
              return (contact.sources || []).map(sourceKey=>{
                return {
                  key:sourceKey,
                  value:_.get(contactsDetailsWpApiSettings, `contacts_custom_fields_settings.sources.default.${sourceKey}`) || sourceKey }
              })
            }, callback: {
              onCancel: function (node, item) {
                _.pullAllBy(editFieldsUpdate.sources.values, [{value:item.key}], "value")
                editFieldsUpdate.sources.values.push({value:item.key, delete:true})
              }
            }
          },
          callback: {
            onClick: function(node, a, item, event){
              _.pullAllBy(editFieldsUpdate.sources.values, [{value:item.key}], "value")
              editFieldsUpdate.sources.values.push({value:item.key})
            },
            onResult: function (node, query, result, resultCount) {
              resultCount = typeaheadTotals.sources
              let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
              $('#sources-result-container').html(text);
            },
            onHideLayout: function () {
              $('#sources-result-container').html("");
            },
          }
        });
      }
    }
  }


    /**
   * Locations
   */
  typeaheadTotals.locations = 0;
  let loadLocationTypeahead = ()=>{
    if (!window.Typeahead['.js-typeahead-locations']){
      $.typeahead({
        input: '.js-typeahead-locations',
        minLength: 0,
        searchOnFocus: true,
        maxItem: 20,
        template: function (query, item) {
          return `<span>${_.escape(item.name)}</span>`
        },
        source: TYPEAHEADS.typeaheadSource('locations', 'dt/v1/locations/compact/'),
        display: "name",
        templateValue: "{{name}}",
        dynamic: true,
        multiselect: {
          matchOn: ["ID"],
          data: function () {
            return contact.locations.map(g=>{
              return {ID:g.ID, name:g.post_title}
            })
          }, callback: {
            onCancel: function (node, item) {
              _.pullAllBy(editFieldsUpdate.locations.values, [{value:item.ID}], "value")
              editFieldsUpdate.locations.values.push({value:item.ID, delete:true})
            }
          }
        },
        callback: {
          onClick: function(node, a, item, event){
            if (!editFieldsUpdate.locations){
              editFieldsUpdate.locations = { "values": [] }
            }
            _.pullAllBy(editFieldsUpdate.locations.values, [{value:item.ID}], "value")
            editFieldsUpdate.locations.values.push({value:item.ID})
          },
          onResult: function (node, query, result, resultCount) {
            resultCount = typeaheadTotals.locations
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#locations-result-container').html(text);
          },
          onHideLayout: function () {
            $('#locations-result-container').html("");
          },
        }
      });
    }
  }


  /**
   * People_groups
   */
  typeaheadTotals.people_groups = 0;
  let loadPeopleGroupTypeahead = ()=>{
    if (!window.Typeahead['.js-typeahead-people_groups']){

      $.typeahead({
        input: '.js-typeahead-people_groups',
        minLength: 0,
        searchOnFocus: true,
        maxItem: 20,
        template: function (query, item) {
          return `<span>${_.escape(item.name)}</span>`
        },
        source: TYPEAHEADS.typeaheadSource('people_groups', 'dt/v1/people-groups-compact/'),
        display: "name",
        templateValue: "{{name}}",
        dynamic: true,
        multiselect: {
          matchOn: ["ID"],
          data: function () {
            return contact.people_groups.map(g=>{
              return {ID:g.ID, name:g.post_title}
            })
          },
          callback: {
            onCancel: function (node, item) {
              _.pullAllBy(editFieldsUpdate.people_groups.values, [{value:item.ID}], "value")
              editFieldsUpdate.people_groups.values.push({value:item.ID, delete:true})
            }
          },
        },
        callback: {
          onClick: function(node, a, item, event){
            _.pullAllBy(editFieldsUpdate.people_groups.values, [{value:item.ID}], "value")
            editFieldsUpdate.people_groups.values.push({value:item.ID})
          },
          onResult: function (node, query, result, resultCount) {
            resultCount = typeaheadTotals.people_groups
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#people_groups-result-container').html(text);
          },
          onHideLayout: function () {
            $('#people_groups-result-container').html("");
          },
        }
      });
    }
  }

  /**
   * Assigned_to
   */
  typeaheadTotals.assigned_to = 0;
  $.typeahead({
    input: '.js-typeahead-assigned_to',
    minLength: 0,
    searchOnFocus: true,
    source: {
      users: {
        display: ["name", "user"],
        ajax: {
          url: contactsDetailsWpApiSettings.root + 'dt/v1/users/get_users',
          data: {
            s: "{{query}}"
          },
          beforeSend: function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
          },
          callback: {
            done: function (data) {
              typeaheadTotals["assigned_id"] = data.total || data.length
              return data.posts || data
            }
          }
        }
      }
    },

    templateValue: "{{name}}",
    template: function (query, item) {
      return `<span class="row">
        <span class="avatar"><img src="{{avatar}}"/> </span>
        <span>${item.name}</span>      
      </span>`
    },
    dynamic: true,
    hint: true,
    emptyTemplate: 'No users found "{{query}}"',
    callback: {
      onClick: function(node, a, item, event){
        API.save_field_api('contact', contactId, {assigned_to: 'user-' + item.ID}).then(function (response) {
          _.set(contact, "assigned_to", response.assigned_to)
          $('.current-assigned').text(contact.assigned_to.display)
          setStatus(response)
          $('.js-typeahead-assigned_to').val(contact.assigned_to.display)
          $('.js-typeahead-assigned_to').trigger('propertychange.typeahead')
        })
      },
      onResult: function (node, query, result, resultCount) {
        resultCount = typeaheadTotals.assigned_to
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#assigned_to-result-container').html(text);
      },
      onHideLayout: function () {
        $('.assigned_to-result-container').html("");
      },
      onReady: function () {
        if (_.get(contact,  "assigned_to.display")){
          $('.js-typeahead-assigned_to').val(contact.assigned_to.display)
        }
        // $('.js-typeahead-assigned_to').trigger('propertychange.typeahead')
        // $('.assigned_to-result-container').html("");
      }
    },
  });
  $('.search_assigned_to').on('click', function () {
    let id = $(this).data("id")
    $(`#${id} .js-typeahead-assigned_to`).val("")
    $(`#${id} .js-typeahead-assigned_to`).trigger('input.typeahead')
  })
  if (_.get(contact, "assigned_to")){
    $('.current-assigned').text(_.get(contact, "assigned_to.display"))
  }

  /**
   * Share
   */
  let shareTypeahead = null
  $('.open-share').on("click", function(){
    $('#share-contact-modal').foundation('open');
    if  (!shareTypeahead) {
      shareTypeahead = TYPEAHEADS.share("contact", contactId)
    }
  })

  /**
   * connections to other contacts
   */
  ;["baptized_by", "baptized", "coached_by", "coaching", "subassigned"].forEach(field_id=>{
    typeaheadTotals[field_id] = 0
    $.typeahead({
      input: `.js-typeahead-${field_id}`,
      minLength: 0,
      maxItem: 30,
      searchOnFocus: true,
      template: function (query, item) {
        return `<span>${_.escape(item.name)}</span>`
      },
      matcher: function (item) {
        return item.ID !== contact.ID
      },
      source: {
        contacts: {
          display: "name",
          ajax: {
            url: contactsDetailsWpApiSettings.root + 'dt/v1/contacts/compact',
            data: {
              s: "{{query}}"
            },
            beforeSend: function(xhr) {
              xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
            },
            callback: {
              done: function (data) {
                typeaheadTotals[field_id] = data.total
                return data.posts
              }
            }
          }
        }
      },
      display: "name",
      templateValue: "{{name}}",
      dynamic: true,
      multiselect: {
        matchOn: ["ID"],
        data: function () {
          return (contact[field_id] || [] ).map(g=>{
            return {ID:g.ID, name:g.post_title}
          })
        }, callback: {
          onCancel: function (node, item) {
            API.save_field_api('contact', contactId, {[field_id]: {values:[{value:item.ID, delete:true}]}}).then(()=>{
              if(field_id === "subassigned"){
                $(`.${field_id}-list .${item.ID}`).remove()
                let listItems = $(`.${field_id}-list li`)
                if (listItems.length === 0){
                  $(`.${field_id}-list.details-list`).append(`<li id="no-${field_id}">${contactsDetailsWpApiSettings.translations["not-set"][field_id]}</li>`)
                }

              }
            })
          }
        },
        href: "/contacts/{{ID}}"
      },
      callback: {
        onClick: function(node, a, item, event){
          API.save_field_api('contact', contactId, {[field_id]: {values:[{"value":item.ID}]}}).then((addedItem)=>{
            if (field_id === "subassigned")
            $(`#no-${field_id}`).remove()
            $(`.${field_id}-list`).append(`<li class="${addedItem.ID}">
              <a href="${addedItem.permalink}">${_.escape(addedItem.post_title)}</a>
            </li>`)
          })
          masonGrid.masonry('layout')
        },
        onResult: function (node, query, result, resultCount) {
          resultCount = typeaheadTotals[field_id]
          var text = "";
          if (result.length > 0 && result.length < resultCount) {
            text = "Showing <strong>" + result.length + "</strong> of <strong>" + resultCount + '</strong> ' + (query ? 'elements matching "' + query + '"' : '');
          } else if (result.length > 0) {
            text = 'Showing <strong>' + result.length + '</strong> contacts matching "' + query + '"';
          } else {
            text = 'No results matching "' + query + '"';
          }
          $(`#${field_id}-result-container`).html(text);
        },
        onHideLayout: function () {
          $(`#${field_id}-result-container`).html("");
        },
        onReady: function () {
          if (field_id === "subassigned"){
          }
        }
      }
    })
  })

  /**
   * Contact details
   */


  $(document).on('change', 'div.reason-field select', e => {
    const $select = $(e.currentTarget)
    const field = $select.data('field')
    const value = $select.val()

    API.save_field_api('contact', contactId, { [field]: value }).catch(handelAjaxError)
  })

  $('button#add-social-media').click(e => {
    const channel_type = 'contact_' + $('select#social-channels').val()
    const $inputForNewValue = $('input#new-social-media')
    const text = $inputForNewValue.val()

    $('#edit-social').append(`<li style="display: flex">
        <input type="text" class="contact-input" data-type="${channel_type}" value="${text}"/>
        <button class="button clear delete-button" data-id="new">
          <img src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/invalid.svg">
        </button>
    </li>`)
    if (!editFieldsUpdate[channel_type]){
      editFieldsUpdate[channel_type] = { values: [] }
    }
    editFieldsUpdate[channel_type].values.push({value:text})
  })

  $('select.select-field').change(e => {
    const id = $(e.currentTarget).attr('id')
    const val = $(e.currentTarget).val()

    API.save_field_api('contact', contactId, { [id]: val }).then(contactResponse => {
      $(`.current-${id}`).text(_.get(contactResponse, `${id}.label`) || val)

      if (id === 'seeker_path') {
        updateCriticalPath(contactResponse.seeker_path.key)
        refresh_quick_action_buttons(contactResponse)
      } else if (id === 'reason_unassignable') {
        setStatus(contactResponse)
      } else if (id === 'overall_status') {
        setStatus(contactResponse, true)
      }
    }).catch(handelAjaxError)
  })


  // Baptism date
  $('input#baptism-date-picker').datepicker({
    dateFormat: 'yy-mm-dd',
    onSelect: function (date) {
      API.save_field_api('contact', contactId, { baptism_date: date }).catch(handelAjaxError)
    },
    changeMonth: true,
    changeYear: true
  })

  // Clicking plus sign for new address
  $('button#add-new-address').click(e => {
    $('#edit-contact_address').append(`
      <li style="display: flex">
        <textarea rows="3" class="contact-input" data-type="contact_address"></textarea>
        <button class="button clear delete-button" data-id="new">
          <img src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/invalid.svg">
        </button>
    </li>`)
  })

  // Clicking the plus sign next to the field label
  $('button.add-button').click(e => {
    const listClass = $(e.currentTarget).data('list-class')
    const $list = $(`#edit-${listClass}`)

    $list.append(`<li style="display: flex">
      <input type="text" class="contact-input" data-type="${listClass}"/>
      <button class="button clear delete-button new-${listClass}" data-id="new">
          <img src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/invalid.svg">
      </button>
    </li>`)
  })


  $('button.show-button').click(e => {
    $(e.currentTarget).toggleClass('showing-more')
    $('.show-content').toggle()
  })

  /**
   * Update Needed
   */
  $('.update-needed.switch-input').change(function (a,b) {
    let updateNeeded = $(this).is(':checked')
    API.save_field_api( "contact", contactId, {"requires_update":updateNeeded})
  })

  /**
   * Status
   */
  $('.make-active').click(function () {
    let data = {overall_status:"active"}
    API.save_field_api('contact', contactId, data).then((contact)=>{
      setStatus(contact)
    })
  })

  function setStatus(contact, openModal) {
    let status = _.get(contact, "overall_status.key")
    let reasonLabel = _.get(contact, `reason_${status}.label`)
    let statusColor = _.get(contactsDetailsWpApiSettings,
      `contacts_custom_fields_settings.overall_status.colors.${status}`)
    $('#overall_status').val(status)

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
      $('#overall_status').css("background-color", statusColor)
    } else {
      $('#overall_status').css("background-color", "#366184")
    }

    $('#reason').text(reasonLabel ? `(${reasonLabel})` : '')

    if (reasonLabel){
      $(`#edit-reason`).show()
    } else {
      $(`#edit-reason`).hide()
    }
  }

  //confirm setting a reason for a status.
  let confirmButton = $(".confirm-reason-button")
  confirmButton.on("click", function () {
    let field = $(this).data('field')
    let select = $(`#reason-${field}-options`)
    $(this).toggleClass('loading')
    let data = {overall_status:field}
    data[`reason_${field}`] = select.val()
    API.save_field_api('contact', contactId, data).then(contactData=>{
      $(this).toggleClass('loading')
      $(`#${field}-contact-modal`).foundation('close')
      setStatus(contactData)
    })
  })



  $("#open-edit").on("click", function () {

    editFieldsUpdate = {
      locations : { values: [] },
      people_groups : { values: [] },
      sources : { values: [] }
    }
    let phoneHTML = "";
    (contact.contact_phone|| []).forEach(field=>{
      phoneHTML += `<li style="display: flex">
          <input type="tel" id="${_.escape(field.key)}" value="${field.value}" data-type="contact_phone" class="contact-input"/>
          <button class="button clear delete-button" data-id="${_.escape(field.key)}" data-type="contact_phone" style="color: red">
            <img src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/invalid.svg">
          </button>
      </li>`
    })
    $("#edit-contact_phone").html(phoneHTML)
    let emailHTML = "";
    (contact.contact_email|| []).forEach(field=>{
      console.log(field);
      emailHTML += `<li style="display: flex">
        <input class="contact-input" type="email" id="${_.escape(field.key)}" value="${field.value}" data-type="contact_email"/>
        <button class="button clear delete-button" data-id="${_.escape(field.key)}" data-type="contact_email">
            <img src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/invalid.svg">
        </button>
      </li>`
    })
    $("#edit-contact_email").html(emailHTML)
    let addressHTML = "";
    (contact.contact_address|| []).forEach(field=>{
      addressHTML += `<li style="display: flex">
        <textarea class="contact-input" type="text" id="${_.escape(field.key)}" value="${field.value}" data-type="contact_address"/>
        <button class="button clear delete-button" data-id="${_.escape(field.key)}" data-type="contact_address">
            <img src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/invalid.svg">
        </button>
      </li>`
    })
    $("#edit-contact_address").html(addressHTML)

    let html = ""
    for( let field in contact ){
      if ( field.startsWith("contact_") && !["contact_email", "contact_phone", "contact_address"].includes(field) ){
        console.log(field);
        console.log(contact[field]);
        contact[field].forEach(socialField=>{
          html += `<li style="display: flex">
            <input class="contact-input" type="text" id="${socialField.key}" value="${socialField.value}" data-type="${field}"/>
            <button class="button clear delete-button" data-id="${socialField.key}" data-type="${field}">
                <img src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/invalid.svg">
            </button>
          </li>`
        })

      }
    }
    $('#edit-social').html(html)

    $('#contact-details-edit').foundation('open');
    loadLocationTypeahead()
    loadPeopleGroupTypeahead()
    leadSourcesTypeahead()
  })


  $('.select-input').on("change", function () {
    let key = $(this).attr('id')
    let val = $(this).val()
    editFieldsUpdate[key] = val
  })

  $(document).on('change', '.contact-input', function() {
    let value = $(this).val()
    let field = $(this).data("type")
    let key = $(this).attr('id')
    if (!editFieldsUpdate[field]){
      editFieldsUpdate[field] = { values: [] }
    }
    let existing = _.find(editFieldsUpdate[field].values, {key})
    if (existing){
      existing.value = value
    } else {
      editFieldsUpdate[field].values.push({ key, value })
    }
  }).on('click', '#contact-details-edit .delete-button', function () {
    let field = $(this).data('type')
    let key = $(this).data('id')
    if ( key !== 'new' ){
      if (!editFieldsUpdate[field]){
        editFieldsUpdate[field] = { values: [] }
      }
      _.pullAllBy(editFieldsUpdate[field].values, [{key}], "key")
      editFieldsUpdate[field].values.push({key, delete:true})
    }
    $(this).parent().remove()
  }).on('change', '.text-input', function () {
    let field = $(this).attr('id')
    editFieldsUpdate[field] = $(this).val()
  })

  /**
   * Save contact details updates
   */
  $('#save-edit-details').on('click', function () {
    $(this).toggleClass("loading")
    console.log(editFieldsUpdate);
    API.save_field_api( "contact", contactId, editFieldsUpdate).then((updatedContact)=>{
      contact = updatedContact
      $(this).toggleClass("loading")
      resetDetailsFields(contact)
      $(`#contact-details-edit`).foundation('close')
    }).catch(handelAjaxError)
  })

  $('#edit-reason').on('click', function () {
    setStatus(contact, true)
  })


  let resetDetailsFields = (contact=>{
    $('.title').html(contact.title)
    let contact_methods = ["contact_email", "contact_phone", "contact_address"]
    contact_methods.forEach(contact_method=>{
      let fieldDesignator = contact_method.replace('contact_', '')
      let htmlField = $(`ul.${fieldDesignator}`)
      htmlField.empty()
      let fields = contact[contact_method]
      let allEmptyValues = true
      ;(fields || []).forEach(field=>{
        if (field.value){
          allEmptyValues = false
        }
        htmlField.append(`<li class="details-list ${_.escape(field.key)}">
            ${_.escape(field.value)}
              <img id="${_.escape(field.key)}-verified" class="details-status" ${!field.verified ? 'style="display:none"': ""} src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/verified.svg"/>
              <img id="${_.escape(field.key)}-invalid" class="details-status" ${!field.invalid ? 'style="display:none"': ""} src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/broken.svg"/>
            </li>
          `)
      })
      if (!fields || fields.length === 0 || allEmptyValues){
        htmlField.append(`<li id="no-${fieldDesignator}">${contactsDetailsWpApiSettings.translations["not-set"][fieldDesignator]}</li>`)
      }
    })
    let socialHTMLField = $(`ul.social`).empty()
    let socialIsEmpty = true
    _.forOwn(contact, ( value, contact_method)=>{
      if ( contact_method.indexOf("contact_") === 0 && !contact_methods.includes( contact_method )){
        let fieldDesignator = contact_method.replace('contact_', '')
        let fields = contact[contact_method]
        fields.forEach(field=>{
          socialIsEmpty = false
          socialHTMLField.append(`<li class="details-list ${_.escape(field.key)}">
            <object data="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/${fieldDesignator}.svg" 
              type="image/jpg">${fieldDesignator}:</object>
            ${_.escape(field.value)}
              <img id="${_.escape(field.key)}-verified" class="details-status" ${!field.verified ? 'style="display:none"': ""} src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/verified.svg"/>
              <img id="${_.escape(field.key)}-invalid" class="details-status" ${!field.invalid ? 'style="display:none"': ""} src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/broken.svg"/>
            </li>
          `)
        })
      }
    })
    if ( socialIsEmpty ){
      socialHTMLField.append(`<li id="no-social">${contactsDetailsWpApiSettings.translations["not-set"]["social"]}</li>`)
    }
    let connections = [ "locations", "people_groups" ]
    connections.forEach(connection=>{
      let htmlField = $(`.${connection}-list`).empty()
      if ( !contact[connection] || contact[connection].length === 0 ){
        htmlField.append(`<li id="no-${connection}">${contactsDetailsWpApiSettings.translations["not-set"][connection]}</li>`)
      } else {
        contact[connection].forEach(field=>{
          console.log(field);
          htmlField.append(`<li class="details-list ${_.escape(field.key)}">
            ${_.escape(field.post_title)}
              <img id="${_.escape(field.ID)}-verified" class="details-status" ${!field.verified ? 'style="display:none"': ""} src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/verified.svg"/>
              <img id="${_.escape(field.ID)}-invalid" class="details-status" ${!field.invalid ? 'style="display:none"': ""} src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/broken.svg"/>
            </li>
          `)
        })
      }
    })
    let selectsFields = [ "age", "gender" ];
    selectsFields.forEach(selectField=>{
      if ( _.get(contact, `${selectField}.label`) ){
        $(`li.${selectField}`).html(_.escape(_.get(contact, `${selectField}.label`)))
      } else {
        $(`li.${selectField}`).html(`${contactsDetailsWpApiSettings.translations["not-set"][selectField]}`)
      }
    })
    //source
    let sourceHTML = $('.sources-list').empty()
    if ( contact.sources && contact.sources.length > 0 ){
      contact.sources.forEach(source=>{
        sourceHTML.append(`<li>
          ${_.escape(_.get(contactsDetailsWpApiSettings, "contacts_custom_fields_settings.sources.default." + source))}
        </li>`)
      })
    } else {
      sourceHTML.append(`<li id="no-source">${contactsDetailsWpApiSettings.translations["not-set"]["source"]}</li>`)
    }

  })
  resetDetailsFields(contact)

  //leave at the end
  masonGrid.masonry({
    itemSelector: '.grid-item',
    percentPosition: true
  });
})


let editingAll = false

function details_accept_contact(contactId, accept){
  console.log(contactId)

  let data = {accept:accept}
  jQuery.ajax({
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: contactsDetailsWpApiSettings.root + 'dt/v1/contact/' + contactId + "/accept",
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', contactsDetailsWpApiSettings.nonce);
    }
  }).then(function (data) {
    jQuery('#accept-contact').hide()
    if (data && data['overall_status']){
      jQuery('#overall-status').text(data['overall_status'])
    }
    if(data && data["assigned_to"]){
      jQuery('.current-assigned').text(data["assigned_to"])
    }
  }).catch(err=>{
    jQuery("#errors").append(err.responseText)
  })
}
