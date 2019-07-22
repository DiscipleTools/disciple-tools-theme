/*global contactsDetailsWpApiSettings:false, moment:false, _:false */



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
  API.update_post('contacts', contactId, data)
  .then(data=>{
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
  $('.update-needed-notification').toggle(updateNeeded)
  $('#update-needed').prop("checked", updateNeeded)
}


/* The `contact` variable can be accessed outside this script, but not through
 * `window.` because `let` was used */
let contact = {}
let typeaheadTotals = {}

window.contactDetailsEvents = (function() {
  /* Simple publish/subscribe, based on https://davidwalsh.name/pubsub-javascript */
  let topics = {}
  return {
    subscribe(topic, listener) {
      if (! Object.prototype.hasOwnProperty.call( topics, topic ) ) {
        topics[topic] = []
      }
      let index = topics[topic].push(listener) - 1;
      return {
        unsubscribe() {
          delete topics[topic][index]
        }
      }
    },
    publish(topic, info) {
      if (! Object.prototype.hasOwnProperty.call( topics, topic )) {
        return
      }
      topics[topic].forEach(listener => listener(info))
    }
  }
})();

jQuery(document).ready(function($) {
  let contactId = $("#contact-id").text()
  contact = contactsDetailsWpApiSettings.contact
  let editFieldsUpdate = {}
  let masonGrid = $('.grid')


  $( document ).ajaxComplete(function(event, xhr, settings) {
    if (settings && settings.type && (settings.type === "POST" || settings.type === "DELETE")){
      if (_.get(xhr, "responseJSON.ID")){
        contact = xhr.responseJSON
        let updateNeeded = _.get(contact, "requires_update") === true
        contactUpdated(updateNeeded)
      }
    }
  }).ajaxError(handleAjaxError)

  /**
   * Groups
   */
  $.typeahead({
    input: '.js-typeahead-groups',
    minLength: 0,
    accent: true,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      if (item.ID === "new-item"){
        return "Create new Group"
      }
      return `<span>${_.escape(item.name)}</span>`
    },
    source: TYPEAHEADS.typeaheadSource('groups', 'dt-posts/v2/groups/compact/'),
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
          API.update_post('contacts', contactId, {groups: {values:[{value:item.ID, delete:true}]}})
        }
      },
      href: function(item){
        if (item){
          return `${_.escape( window.wpApiShare.site_url )}/groups/${item.ID}`
        }
      }
    },
    callback: {
      onClick: function(node, a, item, event){
        if(item.ID === "new-item"){
          event.preventDefault();
          $('#create-group-modal').foundation('open');
        } else {
          API.update_post('contacts', contactId, {groups: {values:[{value:item.ID}]}})
          this.addMultiselectItemLayout(item)
          event.preventDefault()
          this.hideLayout();
          this.resetInput();
          masonGrid.masonry('layout')
        }
      },
      onResult: function (node, query, result, resultCount) {
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
    let title = $("#create-group-modal .js-create-group input[name=title]").val()
    API.create_post('groups', {title, members: {values: [{value: contactId}]}})
      .then((newGroup)=>{
        $(".reveal-after-group-create").show()
        $("#new-group-link").html(`<a href="${_.escape( newGroup.permalink )}">${_.escape(title)}</a>`)
        $(".hide-after-group-create").hide()
        $('#go-to-group').attr('href', newGroup.permalink);
        Typeahead['.js-typeahead-groups'].addMultiselectItemLayout({ID:newGroup.ID.toString(), name:title})
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
   * Accept or decline a contact
   */
  $('.accept-decline').on('click', function () {
    let action = $(this).data("action")
    let data = {accept:action === "accept"}
    jQuery.ajax({
      type: "POST",
      data: JSON.stringify(data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: contactsDetailsWpApiSettings.root + 'dt-posts/v2/contacts/' + contactId + "/accept",
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', contactsDetailsWpApiSettings.nonce);
      }
    }).then(function (resp) {
      setStatus(resp)
      jQuery('#accept-contact').hide()
    }).catch(err=>{
      jQuery("#errors").append(err.responseText)
    })
  })

  /**
   * Sources
   */
  let leadSourcesTypeahead = async function leadSourcesTypeahead() {
    let sourceTypeahead =  $(".js-typeahead-sources");
    if (!window.Typeahead['.js-typeahead-sources']){
      /* Similar code is in list.js, copy-pasted for now. */
      sourceTypeahead.attr("disabled", true) // disable while loading AJAX
      const response = await fetch(contactsDetailsWpApiSettings.root + 'dt/v1/contact/list-sources', {
        credentials: 'same-origin', // needed for Safari
        headers: {
          'X-WP-Nonce': wpApiShare.nonce,
        },
      });
      let sourcesData = []
      _.forOwn(await response.json(), (sourceValue, sourceKey) => {
        sourcesData.push({key:sourceKey, value:sourceValue || ""})
      })
      sourceTypeahead.attr("disabled", false)
      $.typeahead({
        input: '.js-typeahead-sources',
        minLength: 0,
        accent: true,
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
                value: _.get(contactsDetailsWpApiSettings.sources, sourceKey) || sourceKey,
              }
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
            this.addMultiselectItemLayout(item)
            event.preventDefault()
            this.hideLayout();
            this.resetInput();
          },
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#sources-result-container').html(text);
          },
          onHideLayout: function () {
            $('#sources-result-container').html("");
          }
        }
      });
    }
  }




  /**
   * Geonames
   */
  let loadGeonameTypeahead = ()=>{
    if (!window.Typeahead['.js-typeahead-geonames']){
      $.typeahead({
        input: '.js-typeahead-geonames',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        maxItem: 20,
        template: function (query, item) {
          return `<span>${_.escape(item.name)}</span>`
        },
        dropdownFilter: [{
          key: 'group',
          value: 'focus',
          template: 'Regions of Focus',
          all: 'All Locations'
        }],
        source: {
          focus: {
            display: "name",
            ajax: {
              url: wpApiShare.root + 'dt/v1/mapping_module/search_geonames_by_name',
              data: {
                s: "{{query}}",
                filter: function () {
                  return _.get(window.Typeahead['.js-typeahead-geonames'].filters.dropdown, 'value', 'all')
                }
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  if (typeof typeaheadTotals !== "undefined") {
                    typeaheadTotals.field = data.total
                  }
                  return data.geonames
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
            return (contact.geonames || []).map(g=>{
              return {ID:g.id, name:g.label}
            })

          }, callback: {
            onCancel: function (node, item) {
              _.pullAllBy(editFieldsUpdate.geonames.values, [{value:item.ID}], "value")
              editFieldsUpdate.geonames.values.push({value:item.ID, delete:true})
            }
          }
        },
        callback: {
          onClick: function(node, a, item, event){
            if (!editFieldsUpdate.geonames){
              editFieldsUpdate.geonames = { "values": [] }
            }
            _.pullAllBy(editFieldsUpdate.geonames.values, [{value:item.ID}], "value")
            editFieldsUpdate.geonames.values.push({value:item.ID})
            this.addMultiselectItemLayout(item)
            event.preventDefault()
            this.hideLayout();
            this.resetInput();
          },
          onReady(){
            this.filters.dropdown = {key: "group", value: "focus", template: "Regions of Focus"}
            this.container
              .removeClass("filter")
              .find("." + this.options.selector.filterButton)
              .html("Regions of Focus");
          },
          onResult: function (node, query, result, resultCount) {
            resultCount = typeaheadTotals.geonames
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#geonames-result-container').html(text);
          },
          onHideLayout: function () {
            $('#geonames-result-container').html("");
          }
        }
      });
    }
  }



  /**
   * People groups
   */
  let loadPeopleGroupTypeahead = ()=>{
    if (!window.Typeahead['.js-typeahead-people_groups']){

      $.typeahead({
        input: '.js-typeahead-people_groups',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        maxItem: 20,
        template: function (query, item) {
          return `<span>${_.escape(item.name)}</span>`
        },
        source: TYPEAHEADS.typeaheadSource('people_groups', 'dt/v1/people-groups/compact/'),
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
            this.addMultiselectItemLayout(item)
            event.preventDefault()
            this.hideLayout();
            this.resetInput();
          },
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#people_groups-result-container').html(text);
          },
          onHideLayout: function () {
            $('#people_groups-result-container').html("");
          }
        }
      });
    }
  }

  /**
   * Assigned_to
   */
  let assigned_to_input = $(`.js-typeahead-assigned_to`)
  $.typeahead({
    input: '.js-typeahead-assigned_to',
    minLength: 0,
    accent: true,
    searchOnFocus: true,
    source: TYPEAHEADS.typeaheadUserSource(),
    templateValue: "{{name}}",
    template: function (query, item) {
      return `<span class="row" dir="auto">
        <span class="avatar"><img src="{{avatar}}"/> </span>
        <span>${_.escape( item.name )}</span>
      </span>`
    },
    dynamic: true,
    hint: true,
    emptyTemplate: 'No users found "{{query}}"',
    callback: {
      onClick: function(node, a, item){
        API.update_post('contacts', contactId, {assigned_to: 'user-' + item.ID}).then(function (response) {
          _.set(contact, "assigned_to", response.assigned_to)
          setStatus(response)
          assigned_to_input.val(contact.assigned_to.display)
          assigned_to_input.blur()
        }).catch(err => { console.error(err) })
      },
      onResult: function (node, query, result, resultCount) {
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
    assigned_to_input.val("")
    assigned_to_input.trigger('input.typeahead')
    assigned_to_input.focus()
  })



  /**
   * connections to other contacts
   */
  ;["relation", "baptized_by", "baptized", "coached_by", "coaching", "subassigned"].forEach(field_id=>{
    $.typeahead({
      input: `.js-typeahead-${field_id}`,
      minLength: 0,
      accent: true,
      maxItem: 30,
      searchOnFocus: true,
      template: window.TYPEAHEADS.contactListRowTemplate,
      matcher: function (item) {
        return item.ID !== contact.ID
      },
      source: window.TYPEAHEADS.typeaheadContactsSource(),
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
            API.update_post('contacts', contactId, {[field_id]: {values:[{value:item.ID, delete:true}]}}).then(()=>{
              if(field_id === "subassigned"){
                $(`.${field_id}-list .${item.ID}`).remove()
                let listItems = $(`.${field_id}-list li`)
                if (listItems.length === 0){
                  $(`.${field_id}-list.details-list`).append(`<li id="no-${_.escape( field_id )}">${_.escape( contactsDetailsWpApiSettings.translations["not-set"][field_id] )}</li>`)
                }

              }
            }).catch(err => { console.error(err) })
          }
        },
        href: _.escape( window.wpApiShare.site_url ) + "/contacts/{{ID}}"
      },
      callback: {
        onClick: function(node, a, item, event){
          API.update_post('contacts', contactId, {[field_id]: {values:[{"value":item.ID}]}}).then((addedItem)=>{
            if (field_id === "baptized_by"){
              openBaptismModal(addedItem)
            }
          }).catch(err => { console.error(err) })
          this.addMultiselectItemLayout(item)
          event.preventDefault()
          this.hideLayout();
          this.resetInput();
          masonGrid.masonry('layout')
        },
        onResult: function (node, query, result, resultCount) {
          let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $(`#${field_id}-result-container`).html(text);
        },
        onHideLayout: function () {
          $(`#${field_id}-result-container`).html("");
          masonGrid.masonry('layout')
        },
        onReady: function () {
          if (field_id === "subassigned"){
          }
        },
        onShowLayout (){
          masonGrid.masonry('layout')
        }
      }
    })
  })

  /**
   * Tags
   */
  $.typeahead({
    input: '.js-typeahead-tags',
    minLength: 0,
    maxItem: 20,
    searchOnFocus: true,
    template: function (query, item) {
      return `<span>${_.escape(item.name)}</span>`
    },
    source: {
      tags: {
        display: ["name"],
        ajax: {
          url: contactsDetailsWpApiSettings.root  + 'dt-posts/v2/contacts/multi-select-values',
          data: {
            s: "{{query}}",
            field: "tags"
          },
          beforeSend: function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
          },
          callback: {
            done: function (data) {
              return (data || []).map(tag=>{
                return {name:tag}
              })
            }
          }
        }
      }
    },
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["name"],
      data: function () {
        return (contact.tags || []).map(t=>{
          return {name:t}
        })
      }, callback: {
        onCancel: function (node, item) {
          API.update_post('contacts', contactId, {'tags': {values:[{value:item.name, delete:true}]}})
        }
      }
    },
    callback: {
      onClick: function(node, a, item, event){
        API.update_post('contacts', contactId, {tags: {values:[{value:item.name}]}})
        this.addMultiselectItemLayout(item)
        event.preventDefault()
        this.hideLayout();
        this.resetInput();
        masonGrid.masonry('layout')
      },
      onResult: function (node, query, result, resultCount) {
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#tags-result-container').html(text);
        masonGrid.masonry('layout')
      },
      onHideLayout: function () {
        $('#tags-result-container').html("");
        masonGrid.masonry('layout')
      },
      onShowLayout (){
        masonGrid.masonry('layout')
      }
    }
  });

  $("#create-tag-return").on("click", function () {
    let tag = $("#new-tag").val()
    Typeahead['.js-typeahead-tags'].addMultiselectItemLayout({name:tag})
    API.update_post('contacts', contactId, {tags: {values:[{value:tag}]}})

  })

  /**
   * Contact details
   */

  $( document ).on( 'select-field-updated', function (e, newContact, id, val) {
    if (id === 'seeker_path') {
      updateCriticalPath(newContact.seeker_path.key)
      refresh_quick_action_buttons(newContact)
    } else if (id === 'reason_unassignable') {
      setStatus(newContact)
    } else if (id === 'overall_status') {
      setStatus(newContact, true)
    }
  })

  $( document ).on( 'text-input-updated', function (e, newContact){})

  $( document ).on( 'dt_date_picker-updated', function (e, newContact, id, date){
    if ( id === 'baptism_date' ){
      openBaptismModal(newContact)
    }
  })

  $( document ).on( 'dt_multi_select-updated', function (e, newContact, fieldKey, optionKey, action) {
    if ( optionKey === 'milestone_baptized' && action === 'add' ){
      openBaptismModal(newContact)
    }
  })



  // Clicking plus sign for new address
  $('button#add-new-address').on('click', () => {
    $('#edit-contact_address').append(`
      <li style="display: flex">
        <textarea rows="3" class="contact-input" data-type="contact_address" dir="auto"></textarea>
        <button class="button clear delete-button" data-id="new">
          <img src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/invalid.svg">
        </button>
    </li>`)
  })


  let idOfNextNewField = 1
  $('button#add-new-social-media').on('click', ()=>{
    let channelOptions = ``
    _.forOwn( contactsDetailsWpApiSettings.channels, (val, key)=>{
      if ( ![ "phone", "email", "address"].includes( key ) ){
        channelOptions += `<option value="${_.escape(key)}">${escape(val.label)}</option>`
      }
    })
    idOfNextNewField++
    $('#edit-social').append(`
      <div class="grid-x grid-margin-x social-media-row" data-id="${idOfNextNewField}">
        <div class="cell small-4 new-social-type">
          <select data-id="${idOfNextNewField}">
            ${channelOptions}
          </select>
        </div>
        <div class="cell small-8" style="display:flex">
          <input type="text" class="new-social-text" data-id="${idOfNextNewField}">
          <button class="button clear delete-social-media" data-id="${idOfNextNewField}">
              <img src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/invalid.svg">
          </button>
        </div>
      </div>
    `)
  })
  $(document).on('click', '.delete-social-media', function () {
    let tempId = $(this).data('id')
    $(`.social-media-row[data-id="${tempId}"]`).remove()
  })

  // Clicking the plus sign next to the field label
  $('button.add-button').on('click', e => {
    const listClass = $(e.currentTarget).data('list-class')
    const $list = $(`#edit-${listClass}`)

    $list.append(`<li style="display: flex">
      <input type="text" class="contact-input" data-type="${_.escape( listClass )}"/>
      <button class="button clear delete-button new-${_.escape( listClass )}" data-id="new">
          <img src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/invalid.svg">
      </button>
    </li>`)
  })


  /**
   * Update Needed
   */
  $('#update-needed.dt-switch').change(function () {
    let updateNeeded = $(this).is(':checked')
    API.update_post('contacts', contactId, {"requires_update":updateNeeded}).then(resp=>{
      contact = resp
    })
  })
  $('#content')[0].addEventListener('comment_posted', function (e) {
    if ( _.get(contact, "requires_update") === true ){
      API.get_post("contacts",  contactId ).then(resp=>{
        contact = resp
        contactUpdated(_.get(resp, "requires_update") === true )
      }).catch(err => { console.error(err) })
    }
  }, false);

  /**
   * Status
   */
  $('.make-active').on('click', function () {
    let data = {overall_status:"active"}
    API.update_post('contacts', contactId, data).then((contact)=>{
      setStatus(contact)
    }).catch(err => { console.error(err) })
  })

  function setStatus(contact, openModal) {
    let statusSelect = $('#overall_status')
    let status = _.get(contact, "overall_status.key")
    let reasonLabel = _.get(contact, `reason_${status}.label`)
    let statusColor = _.get(contactsDetailsWpApiSettings,
      `contacts_custom_fields_settings.overall_status.default.${status}.color`
    )
    statusSelect.val(status)

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
      statusSelect.css("background-color", statusColor)
    } else {
      statusSelect.css("background-color", "#366184")
    }

    if (["paused", "closed", "unassignable"].includes(status)){
      $('#reason').text(`(${reasonLabel})`)
      $(`#edit-reason`).show()
    } else {
      $('#reason').text(``)
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
    API.update_post('contacts', contactId, data).then(contactData=>{
      $(this).toggleClass('loading')
      $(`#${field}-contact-modal`).foundation('close')
      setStatus(contactData)
    }).catch(err => { console.error(err) })
  })




  $("#open-edit").on("click", function () {

    editFieldsUpdate = {
      people_groups : { values: [] },
      sources : { values: [] },
      geonames : { values: [] }
    }
    let phoneHTML = "";
    (contact.contact_phone|| []).forEach(field=>{
      phoneHTML += `<li style="display: flex">
          <input type="tel" id="${_.escape(field.key)}" value="${_.escape(field.value)}" data-type="contact_phone" class="contact-input"/>
          <button class="button clear delete-button" data-id="${_.escape(field.key)}" data-type="contact_phone" style="color: red">
            <img src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/invalid.svg">
          </button>
      </li>`
    })
    $("#edit-contact_phone").html(phoneHTML)
    let emailHTML = "";
    (contact.contact_email|| []).forEach(field=>{
      emailHTML += `<li style="display: flex">
        <input class="contact-input" type="email" id="${_.escape(field.key)}" value="${_.escape(field.value)}" data-type="contact_email"/>
        <button class="button clear delete-button" data-id="${_.escape(field.key)}" data-type="contact_email">
            <img src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/invalid.svg">
        </button>
      </li>`
    })
    $("#edit-contact_email").html(emailHTML)
    let addressHTML = "";
    (contact.contact_address|| []).forEach(field=>{
      addressHTML += `<li style="display: flex">
        <textarea class="contact-input" type="text" id="${_.escape(field.key)}" data-type="contact_address" dir="auto">${_.escape(field.value)}</textarea>
        <button class="button clear delete-button" data-id="${_.escape(field.key)}" data-type="contact_address">
            <img src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/invalid.svg">
        </button>
      </li>`
    })
    $("#edit-contact_address").html(addressHTML)

    let html = ""
    _.forOwn( contact, (fieldVal, field)=>{
      if ( field.startsWith("contact_") && !["contact_email", "contact_phone", "contact_address"].includes(field) ){
        let channel = field.replace("contact_", "")
        contact[field].forEach(socialField=>{
          html += `<div class="grid-x grid-margin-x social-media-row" data-id="${_.escape(socialField.key)}">
              <div class="cell small-4">
                  ${_.escape( _.get(contactsDetailsWpApiSettings, 'channels[' + channel + '].label' ,field) )}
              </div>
              <div class="cell small-8" style="display: flex">
                <input class="contact-input" type="text" id="${_.escape(socialField.key)}" value="${_.escape(socialField.value)}" data-type="${_.escape( field )}"/>
                <button class="button clear delete-social-media delete-button" data-id="${_.escape(socialField.key)}" data-type="${_.escape( field )}">
                    <img src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/invalid.svg">
                </button>
              </div>
          </div>
          `
        })

      }
    })
    $('#edit-social').html(html)

    $('#contact-details-edit').foundation('open');
    loadGeonameTypeahead()
    loadPeopleGroupTypeahead()
    leadSourcesTypeahead().catch(err => { console.log(err) })
  })


  $("#merge-dupe-modal").on("click", function() {

    editFieldsUpdate = {
      // locations: {
      //   values: []
      // },
      people_groups: {
        values: []
      },
      sources: {
        values: []
      },
      geonames: { values: [] }
    }
    let phoneHTML = "";
    (contact.contact_phone || []).forEach(field => {
      phoneHTML += `<li style="display: flex">
          <input type="tel" id="${_.escape(field.key)}" value="${_.escape(field.value)}" data-type="contact_phone" class="contact-input"/>
          <button class="button clear delete-button" data-id="${_.escape(field.key)}" data-type="contact_phone" style="color: red">
            <img src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/invalid.svg">
          </button>
      </li>`
    })
    $("#edit-contact_phone").html(phoneHTML)
    let emailHTML = "";
    (contact.contact_email || []).forEach(field => {
      emailHTML += `<li style="display: flex">
        <input class="contact-input" type="email" id="${_.escape(field.key)}" value="${_.escape(field.value)}" data-type="contact_email"/>
        <button class="button clear delete-button" data-id="${_.escape(field.key)}" data-type="contact_email">
            <img src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/invalid.svg">
        </button>
      </li>`
    })
    $("#edit-contact_email").html(emailHTML)
    let addressHTML = "";
    (contact.contact_address || []).forEach(field => {
      addressHTML += `<li style="display: flex">
        <textarea class="contact-input" type="text" id="${_.escape(field.key)}" data-type="contact_address" dir="auto">${_.escape(field.value)}</textarea>
        <button class="button clear delete-button" data-id="${_.escape(field.key)}" data-type="contact_address">
            <img src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/invalid.svg">
        </button>
      </li>`
    })
    $("#edit-contact_address").html(addressHTML)

    let html = ""
    _.forOwn( contact, (fieldVal ,field) =>{
      if (field.startsWith("contact_") && !["contact_email", "contact_phone", "contact_address"].includes(field)) {
        contact[field].forEach(socialField => {
          html += `<li style="display: flex">
            <input class="contact-input" type="text" id="${_.escape(socialField.key)}" value="${_.escape(socialField.value)}" data-type="${_.escape( field )}"/>
            <button class="button clear delete-button" data-id="${_.escape(socialField.key)}" data-type="${_.escape( field )}">
                <img src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/invalid.svg">
            </button>
          </li>`
        })

      }
    })
    $('#edit-social').html(html)

    $('#merge-dupe-edit').foundation('open');
    // loadLocationTypeahead()
    loadPeopleGroupTypeahead()
    leadSourcesTypeahead()
    loadGeonameTypeahead()
  })

  $('.select-input').on("change", function () {
    let key = $(this).attr('id')
    editFieldsUpdate[key] = $(this).val()
  })

  $('#contact-details-edit').on('change', '.contact-input', function() {
    let value = $(this).val()
    let field = $(this).data("type")
    let key = $(this).attr('id')
    if ( key ){
      if (!editFieldsUpdate[field]){
        editFieldsUpdate[field] = { values: [] }
      }
      let existing = _.find(editFieldsUpdate[field].values, {key})
      if (existing){
        existing.value = value
      } else {
        editFieldsUpdate[field].values.push({ key, value })
      }
    }
  }).on('click', '.delete-button', function () {
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
  }).on('change', '.edit-text-input', function () {
    let field = $(this).attr('id')
    editFieldsUpdate[field] = $(this).val()
  })

  /**
   * Save contact details
   */
  $('#save-edit-details').on('click', function () {
    $(this).toggleClass("loading")

    let contactInput = $(".contact-input")
    contactInput.each((index, entry)=>{
      if ( !$(entry).attr("id") ){
        let val = $(entry).val()
        let channelType = $(entry).data("type")
        if ( !editFieldsUpdate[channelType]){
          editFieldsUpdate[channelType] = {values:[]}
        }
        editFieldsUpdate[channelType].values.push({value:val})
      }
    })
    let newSocialEntries = $(".new-social-text")
    newSocialEntries.each((index, entry) =>{
      let val = $(entry).val()
      let tempId = $(entry).data('id')
      let channelType = $(`.new-social-type [data-id=${tempId}]`).val()
      if ( val && tempId && channelType ){
        if ( !editFieldsUpdate[`contact_${channelType}`]){
          editFieldsUpdate[`contact_${channelType}`] = {values:[]}
        }
        editFieldsUpdate[`contact_${channelType}`].values.push({value:val})
      }
    })
    API.update_post('contacts', contactId, editFieldsUpdate).then((updatedContact)=>{
      contact = updatedContact
      $(this).toggleClass("loading")
      resetDetailsFields(contact)
      $(`#contact-details-edit`).foundation('close')
    }).catch(handleAjaxError)
  })

  $('#edit-reason').on('click', function () {
    setStatus(contact, true)
  })

  let upgradeUrl = (url)=>{
    if ( !url.includes("http")){
      url = "https://" + url
    }
    if ( !url.startsWith(contactsDetailsWpApiSettings.template_dir)){
      url = url.replace( 'http://', 'https://' )
    }
    return url
  }

  let urlRegex = /[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/gi
  let protocolRegex = /^(?:https?:\/\/)?(?:www.)?/gi
  let resetDetailsFields = (contact=>{
    $('.title').html(_.escape(contact.title))
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
        let link = _.escape(field.value);
        if (contact_method === "contact_email") {
          link = `<a href="mailto:${_.escape(field.value)}">${_.escape(field.value)}</a>`
        } else if (contact_method === "contact_phone") {
          link = `<a href="tel:${_.escape(field.value)}">${_.escape(field.value)}</a>`
        } else if (contact_method === "contact_address") {
          link = `<span dir="auto">${_.escape(field.value).replace(/\n+/g, "<br>\n")}</span>`
        }
        htmlField.append(`<li class="details-list ${_.escape(field.key)}">
              ${link}
              <img id="${_.escape(field.key)}-verified" class="details-status" ${!field.verified ? 'style="display:none"': ""} src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/verified.svg"/>
              <img id="${_.escape(field.key)}-invalid" class="details-status" ${!field.invalid ? 'style="display:none"': ""} src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/broken.svg"/>
            </li>
          `)
      })
      if (!fields || fields.length === 0 || allEmptyValues){
        htmlField.append(`<li id="no-${_.escape( fieldDesignator )}">${_.escape( contactsDetailsWpApiSettings.translations["not-set"][fieldDesignator] )}</li>`)
      }
    })
    let socialHTMLField = $(`ul.social`).empty()
    let socialIsEmpty = true
    _.forOwn(contact, ( value, contact_method)=>{
      if ( contact_method.indexOf("contact_") === 0 && !contact_methods.includes( contact_method )){
        let fieldDesignator = contact_method.replace('contact_', '')
        let channel = _.get(contactsDetailsWpApiSettings, `channels.${fieldDesignator}`, {})
        let fields = contact[contact_method]
        fields.forEach(field=>{
          socialIsEmpty = false
          let value = _.escape(field.value)
          let validURL = new RegExp(urlRegex).exec(value)
          let prefix = new RegExp(protocolRegex).exec(value)
          if (validURL && prefix){
            let urlToDisplay = ""
            if ( channel.hide_domain && channel.hide_domain === true ){
              urlToDisplay = validURL[1] || value
            } else {
              urlToDisplay = value.replace(prefix[0], "")
            }
            value = upgradeUrl( value )
            value = `<a href="${_.escape( value )}" target="_blank" >${_.escape(urlToDisplay)}</a>`
          }
          let label = _.get( channel, "label", fieldDesignator ) + ": "
          if ( channel.icon ){
            channel.icon = upgradeUrl( channel.icon )
            label = `<object data="${_.escape( channel.icon )}" height="10px" width="10px"
              type="image/jpg">${_.escape( label )}</object>`
          }
          socialHTMLField.append(`<li class="details-list ${_.escape(field.key)}">
            ${label}
              ${ value }
              <!--<img id="${_.escape(field.key)}-verified" class="details-status" ${!field.verified ? 'style="display:none"': ""} src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/verified.svg"/>-->
              <!--<img id="${_.escape(field.key)}-invalid" class="details-status" ${!field.invalid ? 'style="display:none"': ""} src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/broken.svg"/>-->
            </li>
          `)
        })
      }
    })
    if ( socialIsEmpty ){
      socialHTMLField.append(`<li id="no-social">${_.escape( contactsDetailsWpApiSettings.translations["not-set"]["social"] )}</li>`)
    }
    let connections = [ "people_groups", "geonames" ]
    connections.forEach(connection=>{
      let htmlField = $(`.${connection}-list`).empty()
      if ( !contact[connection] || contact[connection].length === 0 ){
        htmlField.append(`<li id="no-${connection}">${_.escape( contactsDetailsWpApiSettings.translations["not-set"][connection] )}</li>`)
      } else {
        contact[connection].forEach(field=>{
          htmlField.append(`<li class="details-list ${_.escape(field.key || field.id)}">
            ${_.escape(field.post_title || field.label)}
              <img id="${_.escape(field.ID)}-verified" class="details-status" ${!field.verified ? 'style="display:none"': ""} src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/verified.svg"/>
              <img id="${_.escape(field.ID)}-invalid" class="details-status" ${!field.invalid ? 'style="display:none"': ""} src="${_.escape( contactsDetailsWpApiSettings.template_dir )}/dt-assets/images/broken.svg"/>
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
        let translatedSourceHTML = _.escape(_.get(contactsDetailsWpApiSettings, `sources.${source}`))
        if (! translatedSourceHTML) {
          translatedSourceHTML = `<code>Unknown source: ${_.escape(source)}</code>`
        }
        sourceHTML.append(`<li>${translatedSourceHTML}</li>`)
      })
    } else {
      sourceHTML.append(`<li id="no-source">${_.escape( contactsDetailsWpApiSettings.translations["not-set"]["source"] )}</li>`)
    }

    contactDetailsEvents.publish('resetDetails', {
      newContactDetails: contact
    })

  })
  resetDetailsFields(contact)

  $('.quick-action-menu').on("click", function () {
    let fieldKey = $(this).data("id")

    let data = {}
    let numberIndicator = $(`span.${fieldKey}`)
    let newNumber = parseInt(numberIndicator.first().text() || "0" ) + 1
    data[fieldKey] = newNumber
    API.update_post('contacts', contactId, data)
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
  })

  $("#create-user-return").on("click", function (e) {
    e.preventDefault();
    $(this).toggleClass("loading")
    let $inputs = $('#create-user-form :input');
    let values = {};
    $inputs.each(function() {
        values[this.name] = $(this).val();
    });
    values["corresponds_to_contact"] = contact["ID"];
    window.API.create_user(values).then(()=>{
      $(this).removeClass("loading")
      $(`#make_user_from_contact`).foundation('close')
      location.reload();
    }).catch(err=>{
      $(this).removeClass("loading")
      $('#create-user-errors').html(_.get(err, "responseJSON.message", "Something went wrong"))
    })
    return false;
  })

  /**
   * User-select
   */
  if ( !contact.corresponds_to_user ) {
    $.typeahead({
      input: '.js-typeahead-user-select',
      minLength: 0,
      accent: true,
      searchOnFocus: true,
      source: TYPEAHEADS.typeaheadUserSource(),
      templateValue: "{{name}}",
      template: function (query, item) {
        return `<span class="row">
          <span class="avatar"><img src="{{avatar}}"/> </span>
          <span>${_.escape( item.name )}</span>
        </span>`
      },
      dynamic: true,
      hint: true,
      emptyTemplate: 'No users found "{{query}}"',
      callback: {
        onClick: function (node, a, item) {
          jQuery.ajax({
            type: "GET",
            data: {"user_id": item.ID},
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: contactsDetailsWpApiSettings.root + 'dt/v1/users/contact-id',
            beforeSend: function (xhr) {
              xhr.setRequestHeader('X-WP-Nonce', contactsDetailsWpApiSettings.nonce);
            }
          }).then(user_contact_id => {
            $('.confirm-merge-with-user').show()
            $('#confirm-merge-with-user-dupe-id').val(user_contact_id)
          })
        },
        onResult: function (node, query, result, resultCount) {
          let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $('#user-select-result-container').html(text);
        },
        onHideLayout: function () {
          $('.user-select-result-container').html("");
        },
      },
    });
    let user_select_input = $(`.js-typeahead-user-select`)
    $('.search_user-select').on('click', function () {
      user_select_input.val("")
      user_select_input.trigger('input.typeahead')
      user_select_input.focus()
    })
  }

  $('#open_merge_with_contact').on("click", function () {
    if (!window.Typeahead['.js-typeahead-merge_with']) {
      $.typeahead({
        input: '.js-typeahead-merge_with',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        source: TYPEAHEADS.typeaheadContactsSource(),
        templateValue: "{{name}}",
        template: window.TYPEAHEADS.contactListRowTemplate,
        dynamic: true,
        hint: true,
        emptyTemplate: _x( 'No contacts found matching "{{query}}"', 'disciple_tools', "Keep {{query}} as is" ),
        callback: {
          onClick: function (node, a, item) {
            console.log(item);
            $('.confirm-merge-with-contact').show()
            $('#confirm-merge-with-contact-id').val(item.ID)
            $('#name-of-contact-to-merge').html(item.name)
          },
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#merge_with-result-container').html(text);
          },
          onHideLayout: function () {
            $('.merge_with-result-container').html("");
          },
        },
      });
    }
    let user_select_input = $(`.js-typeahead-merge_with`)
    $('.search_merge_with').on('click', function () {
      user_select_input.val("")
      user_select_input.trigger('input.typeahead')
      user_select_input.focus()
    })
    $('#merge_with_contact_modal').foundation('open');
  })

  $('#transfer_confirm_button').on('click',function() {
    let status_spinner = $('#transfer_spinner')
    status_spinner.append('<img src="'+contactsDetailsWpApiSettings.spinner_url+'" width="20px" />')
    let siteId = $('#transfer_contact').val()
    if ( ! siteId ) {
      return;
    }
    API.transfer_contact( contactId, siteId )
      .then(data=>{
        if ( data ) {
          location.reload();
        }
      }).catch(err=>{
        jQuery('#transfer_spinner').empty().append(err.responseJSON.message).append('&nbsp;' + contactsDetailsWpApiSettings.translations.transfer_error )
        jQuery("#errors").empty()
        console.log("error")
        console.log(err)
      })
  });

  // Baptism date
  let modalBaptismDatePicker = $('input#modal-baptism-date-picker')
  modalBaptismDatePicker.datepicker({
    dateFormat: 'yy-mm-dd',
    onSelect: function (date) {
      API.update_post('contacts', contactId, { baptism_date: date }).catch(handleAjaxError)
    },
    changeMonth: true,
    changeYear: true
  })
  let openBaptismModal = function( newContact ){
    let modalBaptismGeneration = $('#modal-baptism_generation')
    if ( !contact.baptism_date || !(contact.milestones || []).includes('milestone_baptized') || (contact.baptized_by || []).length === 0 ){
      $('#baptism-modal').foundation('open');
      if (!window.Typeahead['.js-typeahead-modal_baptized_by']) {
        $.typeahead({
          input: '.js-typeahead-modal_baptized_by',
          minLength: 0,
          accent: true,
          searchOnFocus: true,
          source: TYPEAHEADS.typeaheadContactsSource(),
          templateValue: "{{name}}",
          template: window.TYPEAHEADS.contactListRowTemplate,
          matcher: function (item) {
            return item.ID !== contact.ID
          },
          dynamic: true,
          hint: true,
          emptyTemplate: 'No users found "{{query}}"',
          multiselect: {
            matchOn: ["ID"],
            data: function () {
              return (contact["baptized_by"] || [] ).map(g=>{
                return {ID:g.ID, name:g.post_title}
              })
            }, callback: {
              onCancel: function (node, item) {
                API.update_post('contacts', contactId, {"baptized_by": {values:[{value:item.ID, delete:true}]}})
                  .catch(err => { console.error(err) })
              }
            },
            href: _.escape( window.wpApiShare.site_url ) + "/contacts/{{ID}}"
          },
          callback: {
            onClick: function (node, a, item) {
              API.update_post('contacts', contactId, {"baptized_by": {values:[{"value":item.ID}]}})
                .catch(err => { console.error(err) })
              console.log(item);
              this.addMultiselectItemLayout(item)
              event.preventDefault()
              this.hideLayout();
              this.resetInput();
            },
            onResult: function (node, query, result, resultCount) {
              let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
              $('#modal_baptized_by-result-container').html(text);
            },
            onHideLayout: function () {
              $('.modal_baptized_by-result-container').html("");
            },
          },
        });
      }
      if ( _.get(newContact, "baptism_date.timestamp", 0) > 0){
        modalBaptismDatePicker.datepicker('setDate', moment.unix(newContact['baptism_date']["timestamp"]).format("YYYY-MM-DD"))
      }
      modalBaptismGeneration.val(newContact["baptism_generation"] || 0)
    }
    contact = newContact
  }
  $('#close-baptism-modal').on('click', function () {
    location.reload()
  })
  $('#modal-baptism_generation').change(function () {
    console.log($(this).val());
    API.update_post('contacts', contactId, {
      baptism_generation: $(this).val(),
      fixed_baptism_generation: true
    })
  })



  //leave at the end of this file
  masonGrid.masonry({
    itemSelector: '.grid-item',
    percentPosition: true
  });
  //leave at the end of this file
})

