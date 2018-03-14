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
   * Typpahead Fuctions
   */
  let typeaheadSource = function (field, url) {
    return {
      contacts: {
        display: "name",
        ajax: {
          url: contactsDetailsWpApiSettings.root + url,
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
    source: typeaheadSource('groups', 'dt/v1/groups-compact/'),
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
        return contact.locations.map(g=>{
          return {ID:g.ID, name:g.post_title}
        })
      }, callback: {
        onCancel: function (node, item) {
          API.save_field_api('contact', contactId, {'locations': {values:[{value:item.ID, delete:true}]}}).then(()=>{
            $(`.locations-list .${item.ID}`).remove()
            let listItems = $(`.locations-list li`)
            if (listItems.length === 0){
              $(`.locations-list.details-list`).append(`<li id="no-location">${contactsDetailsWpApiSettings.translations["not-set"]["location"]}</li>`)
            }
          })
        }
      }
    },
    callback: {
      onClick: function(node, a, item, event){
        API.save_field_api('contact', contactId, {locations: {values:[{value:item.ID}]}}).then((addedItem)=>{
          $('.locations-list').append(`<li class="${addedItem.ID}">
            ${_.escape(addedItem.post_title)}
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
        return contact.people_groups.map(g=>{
          return {ID:g.ID, name:g.post_title}
        })
      },
      callback: {
        onCancel: function (node, item) {
          API.save_field_api('contact', contactId, {people_groups: {values:[{value:item.ID, delete:true}]}}).then(()=>{
            $(`.people_groups-list .${item.ID}`).remove()
            let listItems = $(`.people_groups-list li`)
            if (listItems.length === 0){
              $(`.people_groups-list.details-list`).append(`<li id="no-people-group">${contactsDetailsWpApiSettings.translations["not-set"]["people-group"]}</li>`)
            }
          })
        }
      },
    },
    callback: {
      onClick: function(node, a, item, event){
        API.save_field_api('contact', contactId, {people_groups: {values:[{value:item.ID}]}}).then((addedItem)=>{
          $("#no-people-group").remove()
          $('.people_groups-list').append(`<li class="${addedItem.ID}">
            ${_.escape(addedItem.post_title)}
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
        let text = typeaheadHelpText(resultCount, query, result)
        $('#assigned_to-result-container').html(text);
      },
      onHideLayout: function () {
        $('.assigned_to-result-container').html("");
      },
      onReady: function () {
        $('.details.assigned_to').addClass('details-edit')
        if (_.get(contact,  "assigned_to.display")){
          $('.js-typeahead-assigned_to').val(contact.assigned_to.display)
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
            $('.subassigned').addClass('details-edit')
          }
        }
      }
    })
  })

  /**
   * Contact details
   */
  const $editDetailsToggle = $('span#edit-button-label')
  const $body = $('body')

  // Add listing by default
  $body.addClass('listing')

  function toggleEditAll() {
    editingAll = !editingAll

    if (editingAll) {
      $('button.show-button').trigger('click')
    }

    // Toggle the body class, so we don't have to target individual elements
    // And the display properties can be handled with css
    $body.toggleClass('editing').toggleClass('listing')

    $editDetailsToggle.text(editingAll ? 'Save': 'Edit')
  }

  $('button#edit-details').click(() => {
    toggleEditAll()
  })

  // Removing an item from the contact details
  $(document).on('click', 'button.details-remove-button.delete-method', e => {
    const $button = $(e.currentTarget)
    const fieldId = $button.data('id')
    const fieldType = $button.data('field')

    if (fieldId) {
      API.save_field_api('contact', contactId, { [`contact_${fieldType}`] : [{ key:fieldId, delete:true }] }).then(() => {
        $(`.${fieldId}`).remove()

        const listItems = $(`.${fieldType}-list li`)

        if (listItems.length === 0) {
          $(`.${fieldType}.details-list`).append(`<li id="no-${fieldType}">${contactsDetailsWpApiSettings.translations["not-set"][fieldType]}</li>`)
        }
      }).catch(handelAjaxError)
    }
  }).on('change', 'input.social-input', e => { // Adding new social
    const id = $(e.currentTarget).attr('id')
    const value = $(e.currentTarget).val()

    API.save_field_api('contact', contactId, { [id]: value }).then(() => {
      $(`.social.details-list .${id} .social-text`).text(value)
    }).catch(handelAjaxError);
  }).on('change', 'input.contact-input', e => {
    const fieldId = $(e.currentTarget).attr('id')
    const val = $(e.currentTarget).val()

    API.save_field_api('contact', contactId, { [fieldId]: val }).then(() => {
      $(`li.details-list.${fieldId} span.details-text`).text(val)
    }).catch(handelAjaxError)
  }).on('change', 'ul.address textarea', e => {
    const $textarea = $(e.currentTarget)
    const id = $textarea.attr('id')
    const text = $textarea.val()

    if (id && id !== 'new-address') {
      API.save_field_api('contact', contactId, { [id]: text }).then(() => {
        $(`ul.address .${id} .address-text`).text(text)
      }).catch(handelAjaxError)
    }
  }).on('change', '.new-contact-details', e => {
    const $input = $(e.currentTarget)
    const field = $input.data('id')
    const value = $input.val()

    API.save_field_api('contact', contactId, { [`contact_${field}`]: [{ value }] }).then(contact => {
      console.log(contact)

      const newId = _.get(_.last(_.get(contact, `contact_${field}`) || []), 'key')

      if (newId && newId != contactId) {
        //change the it to the created field
        $input.attr('id', newId)

        // Append the new list item
        $(`ul.${field}`).append(`
            <li class="details-list ${newId}">
              ${value}
              <img id="${newId}-verified" class="details-status" style="display:none" src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/verified.svg"/>
              <img id="${newId}-invalid" class="details-status" style="display:none" src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/broken.svg"/>
            </li>
        `)

        $(`li.new-${field}`)
          .append(editContactDetailsOptions(newId, field))
          .removeClass(`new-${field}`)
          .addClass(`details-edit has-options ${newId}`)

        $(`li.${newId} .dropdown.menu`).foundation()

        $input.removeClass(`new-contact-details`).addClass('contact-input')

        $(`li#no-${field}`).remove()
      }
    }).catch(handelAjaxError)
  }).on('click', '.details-status-button.field-status', function () {
    let status = $(this).data('status')
    let id = $(this).data('id')
    let field = $(this).data('field')
    let fields = {
      key : id,
      verified : status === 'valid',
      invalid : status === "invalid"
    }
    API.save_field_api('contact', contactId, {[`contact_${field}`]:[fields]}).then(()=>{
      $(`#${id}-verified`).toggle(fields.verified)
      $(`#${id}-invalid`).toggle(fields.invalid)
    }).catch(err=>{
      handelAjaxError(err)
    })
  })

  $('button#add-social-media').click(e => {
    const $addSocial = $(e.currentTarget)
    const channel_type = $('select#social-channels').val()
    const $inputForNewValue = $('input#new-social-media')
    const text = $inputForNewValue.val()

    $addSocial.toggleClass('loading')

    API.save_field_api('contact', contactId, { ['contact_'+channel_type]: [{ value: text }] }).then(contact => {
      const newId = _.get(contact, `added_fields.new-${channel_type}`)

      console.log(newId)

      $addSocial.toggleClass('loading')

      const label = _.get(contactsDetailsWpApiSettings, `channels[${channel_type}].label`) || channel_type

      $('ul.social').append(`
        <li class="details-edit">${label}:</li>
        <li class="details-edit has-options">
          <input id="${newId}"
            type="text"
            value="${text}"
            class="social-input">
          ${editContactDetailsOptions(newId, "social")}
        </li>
        <li class="details-list ${newId}">
          <span class="social-text">${text}</span>
          <img id="${newId}-verified" class="details-status" style="display:none" src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/verified.svg"/>
          <img id="${newId}-invalid" class="details-status" style="display:none" src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/broken.svg"/>
        </li>`)

      $(`.${newId} .dropdown.menu`).foundation()
      $inputForNewValue.val('')

      $('li#no-social').remove()
    }).catch(handelAjaxError)
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

  $('input.text-field.details-edit').change(e => {
    const id = $(e.currentTarget).attr('id')
    const val = $(e.currentTarget).val()

    API.save_field_api('contact', contactId, { [id]: val }).then(() => {
      $(`.${id}`).text(val)
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
    if ($('textarea#new-address').length === 0) {
      $('ul.address').append(`
        <li class="new-address details-edit">
          <textarea rows="3" class="new-contact-details" data-id="address" id="new-address"></textarea>
      </li>`)
    }
  })

  // Clicking the plus sign next to the field label
  $('button.add-button').click(e => {
    const listClass = $(e.currentTarget).data('list-class')
    const $list = $(`ul.${listClass}`)

    if ($list.find(`li.new-${listClass}`).length === 0) {
      $list.append(`<li class="details-edit new-${listClass}"><input type="text" id="new-${listClass}" class="new-contact-details" data-id="${listClass}"/></li>`)
    }
  })

  const editContactDetailsOptions = (field_id, field_type) => {
    return `
      <ul class='dropdown menu' data-click-open='true'
        data-dropdown-menu data-disable-hover='true'
        style='display:inline-block'>
        <li>
          <button class="social-details-options-button">
            <img src="${contactsDetailsWpApiSettings.template_dir}/dt-assets/images/menu-dots.svg">
          </button>
          <ul class='menu'>
            <li>
              <button class='details-status-button field-status verify'
                data-status='valid'
                data-id='${field_id}'>
                ${contactsDetailsWpApiSettings.translations.valid}
              </button>
            </li>
            <li>
              <button class='details-status-button field-status invalid'
                data-status="invalid"
                data-id="${field_id}">
                ${contactsDetailsWpApiSettings.translations.invalid}
              </button>
            </li>
            <li>
              <button class='details-status-button field-status'
                data-status="reset"
                data-id='${field_id}'>
                ${contactsDetailsWpApiSettings.translations.unconfirmed}
              </button>
            </li>
            <li>
              <button class='details-remove-button delete-method'
                data-field='${field_type}'
                data-id='${field_id}'>
                ${contactsDetailsWpApiSettings.translations["delete"]}
              <button>
            </li>
          </ul>
          </li>
      </ul>
    `
  }

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
    }

    $('#reason').text(reasonLabel ? `(${reasonLabel})` : '')
    //toggle which reason field is show in the edit details pane.
    $('.reason-field').hide()
    if (reasonLabel){
      $(`.reason-field.reason-${status}`).show()
    } else {
      $('.reason-fields').hide()
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
