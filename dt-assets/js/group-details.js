/* global jQuery:false, wpApiGroupsSettings:false, _:false */

jQuery(document).ready(function($) {

  let group = wpApiGroupsSettings.group
  let masonGrid = $('.grid')
  let groupId = group.ID
  let editFieldsUpdate = {}

  /**
   * Date pickers
   */
  let dateFields = [ "start_date", "church_start_date", "end_date" ]
  dateFields.forEach(key=>{
    let datePicker = $(`#${key}.date-picker`)
    datePicker.datepicker({
      dateFormat: 'yy-mm-dd',
      onSelect: function (date) {
        editFieldsUpdate[key] = date
      },
      changeMonth: true,
      changeYear: true
    })

  })
  /**
   * Assigned_to
   */
  let assignedToInput = $('.js-typeahead-assigned_to');
  $.typeahead({
    input: '.js-typeahead-assigned_to',
    minLength: 0,
    accent: true,
    searchOnFocus: true,
    source: TYPEAHEADS.typeaheadUserSource(),
    display: "name",
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
      onClick: function(node, a, item){
        API.save_field_api('group', groupId, {assigned_to: 'user-' + item.ID}).then(function (response) {
          group = response
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
        if (_.get(group,  "assigned_to.display")){
          assignedToInput.val(group.assigned_to.display)
        }
      }
    },
    debug:true
  });
  $('.search_assigned_to').on('click', function () {
    assignedToInput.val("")
    assignedToInput.trigger('input.typeahead')
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
   * Follow
   */
  $('.follow.switch-input').change(function () {
    let follow = $(this).is(':checked')
    let update = {
      follow: {values:[{value:wpApiGroupsSettings.current_user_id, delete:!follow}]},
      unfollow: {values:[{value:wpApiGroupsSettings.current_user_id, delete:follow}]}
    }
    API.save_field_api( "group", groupId, update)
  })


  /**
   * Update Needed
   */
  $('.update-needed.switch-input').change(function () {
    let updateNeeded = $(this).is(':checked')
    $('.update-needed-notification').toggle(updateNeeded)
    API.save_field_api( "group", groupId, {"requires_update":updateNeeded})
  })
  $('#update-needed')[0].addEventListener('comment_posted', function (e) {
    if ( $(e.target).prop('checked') ){
      API.get_post("group",  groupId ).then(g=>{
        group = g
        $('.update-needed-notification').toggle(group.requires_update === true)
        $('#update-needed').prop("checked", group.requires_update === true)

      }).catch(err => { console.error(err) })
    }
  }, false);


  /**
   * Locations
   */
  $.typeahead({
    input: '.js-typeahead-locations',
    minLength: 0,
    accent: true,
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
        return group.locations.map(g=>{
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
        _.pullAllBy(editFieldsUpdate.locations.values, [{value:item.ID}], "value")
        editFieldsUpdate.locations.values.push({value:item.ID})
        this.addMultiselectItemLayout(item)
        event.preventDefault()
        this.hideLayout();
        this.resetInput();
      },
      onResult: function (node, query, result, resultCount) {
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#locations-result-container').html(text);
      },
      onHideLayout: function () {
        $('#locations-result-container').html("");
      }
    }
  });

    /**
     * Geocoding
     * @param geonameid
     */
    window.GEOCODING.edit_group_geocodeid = function( geonameid ) {
        editFieldsUpdate['geonameid'] = geonameid
    }
    // End Geocode Section

  let peopleGroupList = $('.people_groups-list')
  /**
   * People groups
   */
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
        return group.people_groups.map(g=>{
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

  /**
   * parent Groups
   */
  $.typeahead({
    input: '.js-typeahead-parent_groups',
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
    source: TYPEAHEADS.typeaheadSource('groups', 'dt/v1/groups/compact/'),
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
          return `${window.wpApiShare.site_url}/groups/${item.ID}`
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

  /**
   * peer Groups
   */
  $.typeahead({
    input: '.js-typeahead-peer_groups',
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
    source: TYPEAHEADS.typeaheadSource('groups', 'dt/v1/groups/compact/'),
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      data: function () {
        return (group.peer_groups||[]).map(g=>{
          return {ID:g.ID, name:g.post_title}
        })
      }, callback: {
        onCancel: function (node, item) {
          API.save_field_api('group', groupId, {'peer_groups': {values:[{value:item.ID, delete:true}]}})
        }
      },
      href: function(item){
        if (item){
          return `${window.wpApiShare.site_url}/groups/${item.ID}`
        }
      }
    },
    callback: {
      onClick: function(node, a, item, event){
        if(item.ID === "new-item"){
          event.preventDefault();
          $('#create-group-modal').foundation('open');
        } else {
          API.save_field_api('group', groupId, {'peer_groups': {values:[{value:item.ID}]}})
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
  /**
   * Child Groups
   */
  $.typeahead({
    input: '.js-typeahead-child_groups',
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
    source: TYPEAHEADS.typeaheadSource('groups', 'dt/v1/groups/compact/'),
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
          return `${window.wpApiShare.site_url}/groups/${item.ID}`
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
          this.addMultiselectItemLayout(item)
          event.preventDefault()
          this.hideLayout();
          this.resetInput();
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
  //reset new group modal on close.
  $('#create-contact-modal').on("closed.zf.reveal", function () {
    $(".reveal-after-contact-create").hide()
    $("#create-contact-modal input[name='title']").val('')
    $(".hide-after-contact-create").show()
  })

  //create new group
  $(".js-create-group").on("submit", function(e) {
    e.preventDefault();
    let title = $(".js-create-group input[name=title]").val()
    API.create_group({title, parent_group_id: groupId, group_type:"group"})
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

  $("#add-new-address").on("click", function () {
    $('#edit-contact_address').append(`
      <li style="display: flex">
        <textarea rows="3" class="contact-input" data-type="contact_address"></textarea>
        <button class="button clear delete-button" data-id="new">
          <img src="${wpApiGroupsSettings.template_dir}/dt-assets/images/invalid.svg">
        </button>
    </li>`)
  })

  /**
   * members
   */
  $.typeahead({
    input: '.js-typeahead-members',
    minLength: 0,
    accent: true,
    searchOnFocus: true,
    maxItem: 20,
    template: window.TYPEAHEADS.contactListRowTemplate,
    source: TYPEAHEADS.typeaheadContactsSource(),
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
          API.save_field_api('group', groupId, {'members': {values:[{value:item.ID, delete:true}]}}).then((g)=>{
            group = g
            populateMembersList()
            masonGrid.masonry('layout')
          }).catch(err => { console.error(err) })
        }
      },
      href: window.wpApiShare.site_url + "/contacts/{{ID}}"
    },
    callback: {
      onClick: function(node, a, item, event){
        API.save_field_api('group', groupId, {'members': {values:[{value:item.ID}]}}).then((addedItem)=>{
          group = addedItem
          populateMembersList()
          masonGrid.masonry('layout')
        }).catch(err => { console.error(err) })
        masonGrid.masonry('layout')
      },
      onResult: function (node, query, result, resultCount) {
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#members-result-container').html(text);
      },
      onHideLayout: function () {
        $('#members-result-container').html("");
      }
    }
  });
  
  /**
   * coaches
   */
  $.typeahead({
    input: '.js-typeahead-coaches',
    minLength: 0,
    accent: true,
    searchOnFocus: true,
    maxItem: 20,
    template: window.TYPEAHEADS.contactListRowTemplate,
    source: TYPEAHEADS.typeaheadContactsSource(),
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      data: function () {
        return (group.coaches || []).map(g=>{
          return {ID:g.ID, name:g.post_title}
        })
      }, callback: {
        onCancel: function (node, item) {
          API.save_field_api('group', groupId, {'coaches': {values:[{value:item.ID, delete:true}]}}).then(()=>{
          }).catch(err => { console.error(err) })
        }
      },
      href: window.wpApiShare.site_url + "/contacts/{{ID}}"
    },
    callback: {
      onClick: function(node, a, item, event){
        API.save_field_api('group', groupId, {'coaches': {values:[{value:item.ID}]}}).then((addedItem)=>{
        }).catch(err => { console.error(err) })
        masonGrid.masonry('layout')
      },
      onResult: function (node, query, result, resultCount) {
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#coaches-result-container').html(text);
      },
      onHideLayout: function () {
        $('#coaches-result-container').html("");
      }
    }
  });



  /**
   * Setup group fields
   */

  $("#open-edit").on("click", function () {
    editFieldsUpdate = {
      locations : { values: [] },
      people_groups : { values: [] },
    }
    $('#group-details-edit #title').val( group.name );
    let addressHTML = "";
    (group.contact_address|| []).forEach(field=>{
      addressHTML += `<li style="display: flex">
        <textarea class="contact-input" type="text" id="${_.escape(field.key)}" data-type="contact_address">${field.value}</textarea>
        <button class="button clear delete-button" data-id="${_.escape(field.key)}" data-type="contact_address">
            <img src="${wpApiGroupsSettings.template_dir}/dt-assets/images/invalid.svg">
        </button>
      </li>`
    })
    $("#edit-contact_address").html(addressHTML)


    $('#group-details-edit').foundation('open');
    ["locations", "people_groups"].forEach(t=>{
      Typeahead[`.js-typeahead-${t}`].adjustInputSize()
    })
  })


  /**
   * Save group details updates
   */
  $('#save-edit-details').on('click', function () {
    $(this).toggleClass("loading")
      API.save_field_api( "group", groupId, editFieldsUpdate).then((updatedGroup)=>{
      group = updatedGroup
      $(this).toggleClass("loading")
      resetDetailsFields(group)
      $(`#group-details-edit`).foundation('close')
    }).catch(handelAjaxError)
  })

  $("#group-details-edit").on('change', '.contact-input', function() {
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

  let resetDetailsFields = (group=>{
    $('.title').html(_.escape(group.title))
    let contact_methods = ["contact_address"]
    contact_methods.forEach(contact_method=>{
      let fieldDesignator = contact_method.replace('contact_', '')
      let htmlField = $(`ul.${fieldDesignator}`)
      htmlField.empty()
      let fields = group[contact_method]
      let allEmptyValues = true
      ;(fields || []).forEach(field=>{
        if (field.value){
          allEmptyValues = false
        }
        htmlField.append(`<li class="details-list ${_.escape(field.key)}">
            ${_.escape(field.value)}
              <img id="${_.escape(field.key)}-verified" class="details-status" ${!field.verified ? 'style="display:none"': ""} src="${wpApiGroupsSettings.template_dir}/dt-assets/images/verified.svg"/>
              <img id="${_.escape(field.key)}-invalid" class="details-status" ${!field.invalid ? 'style="display:none"': ""} src="${wpApiGroupsSettings.template_dir}/dt-assets/images/broken.svg"/>
            </li>
          `)
      })
      if (!fields || fields.length === 0 || allEmptyValues){
        htmlField.append(`<li id="no-${fieldDesignator}">${wpApiGroupsSettings.translations["not-set"][fieldDesignator]}</li>`)
      }
    })

    let connections = [ "locations", "people_groups", "leaders" ]
    connections.forEach(connection=>{
      let htmlField = $(`.${connection}-list`).empty()
      if ( !group[connection] || group[connection].length === 0 ){
        htmlField.append(`<li id="no-${connection}">${wpApiGroupsSettings.translations["not-set"][connection]}</li>`)
      } else {
        group[connection].forEach(field=>{
          let title = `${_.escape(field.post_title)}`
          if ( connection === "leaders" ){
            title = `<a href="${_.escape(field.permalink)}">${title}</a>`
          }
          htmlField.append(`<li class="details-list ${_.escape(field.key)}">
            ${title}
              <img id="${_.escape(field.ID)}-verified" class="details-status" ${!field.verified ? 'style="display:none"': ""} src="${wpApiGroupsSettings.template_dir}/dt-assets/images/verified.svg"/>
              <img id="${_.escape(field.ID)}-invalid" class="details-status" ${!field.invalid ? 'style="display:none"': ""} src="${wpApiGroupsSettings.template_dir}/dt-assets/images/broken.svg"/>
            </li>
          `)
        })
      }
    })

    dateFields.forEach(dateField=>{
      if ( group[dateField] ){
        $(`#${dateField}.date-picker`).datepicker('setDate', moment.unix(group[dateField]["timestamp"]).format("YYYY-MM-DD"))
        $(`.${dateField}.details-list`).html(group[dateField]["formatted"])
      } else {
        $(`.${dateField}.details-list`).html(wpApiGroupsSettings.translations["not-set"][dateField])
      }
    })

  })
  resetDetailsFields(group)


  /**
   * Group Status
   */

  let selectFiled = $('select.select-field')
  selectFiled.on('change', function () {
    let id = $(this).attr('id')
    let val = $(this).val()
    API.save_field_api(
      'group',
      groupId,
      {[id]:val}
    ).then(resp=>{
      group = resp
      resetDetailsFields(group);
      if ( id === 'group_status' ){
        statusChanged()
      }
    }).catch(err=>{
      console.log(err)
    })
  })
  $('input.text-input').change(function(){
    const id = $(this).attr('id')
    const val = $(this).val()

    API.save_field_api('group', groupId, { [id]: val })
      .catch(handelAjaxError)
  })
  $('input.number-input').on("blur", function(){
    const id = $(this).attr('id')
    const val = $(this).val()

    API.save_field_api('group', groupId, { [id]: val }).then((groupResp)=>{
      group = groupResp
    }).catch(handelAjaxError)
  })

  let statusChanged = ()=>{
    let statusSelect = $('#group_status')
    let status = _.get(group, "group_status.key")
    let statusColor = _.get(wpApiGroupsSettings,
      `groups_custom_fields_settings.group_status.default.${status}.color`
    )
    if (statusColor){
      statusSelect.css("background-color", statusColor)
    } else {
      statusSelect.css("background-color", "#4CAF50")
    }
  }
  /**
   * Church fields
   */
  let health_keys = Object.keys(wpApiGroupsSettings.groups_custom_fields_settings.health_metrics.default)

  function fillOutChurchHealthMetrics() {
    let svgItem = document.getElementById("church-svg-wrapper").contentDocument

    let churchWheel = $(svgItem).find('svg')
    health_keys.forEach(m=>{
      if (group[`health_metrics`] && group.health_metrics.includes(m) ){
        churchWheel.find(`#${m.replace("church_", "")}`).css("opacity", "1")
        $(`#${m}`).css("opacity", "1")
      } else {
        churchWheel.find(`#${m.replace("church_", "")}`).css("opacity", ".1")
        $(`#${m}`).css("opacity", ".4")
      }
    })
    if ( !(group.health_metrics ||[]).includes("church_commitment") ){
      churchWheel.find('#group').css("opacity", "1")
      $(`#church_commitment`).css("opacity", ".4")
    } else {
      churchWheel.find('#group').css("opacity", ".1")
      $(`#church_commitment`).css("opacity", "1")
    }

    $(".js-progress-bordered-box").removeClass("half-opacity")
  }

  $('#church-svg-wrapper').on('load', function() {
    fillOutChurchHealthMetrics()
  })
  fillOutChurchHealthMetrics()

  $('.group-progress-button').on('click', function () {
    let fieldId = $(this).attr('id')
    $(this).css('opacity', ".6");
    let already_set = _.get(group, `health_metrics`, []).includes(fieldId)
    let update = {values:[{value:fieldId}]}
    if ( already_set ){
      update.values[0].delete = true;
    }
    API.save_field_api('group', groupId, {"health_metrics": update })
      .then(groupData=>{
        group = groupData
        fillOutChurchHealthMetrics()
      }).catch(err=>{
        console.log(err)
    })
  })

  $('button.dt_multi_select').on('click',function () {
    let fieldKey = $(this).data("field-key")
    let optionKey = $(this).attr('id')
    let fieldValue = {}
    let data = {}
    let field = jQuery(`[data-field-key="${fieldKey}"]#${optionKey}`)
    field.addClass("submitting-select-button")
    let action = "add"
    if (field.hasClass("selected-select-button")){
      fieldValue = {values:[{value:optionKey,delete:true}]}
      action = "delete"
    } else {
      field.removeClass("empty-select-button")
      field.addClass("selected-select-button")
      fieldValue = {values:[{value:optionKey}]}
    }
    data[optionKey] = fieldValue
    API.save_field_api('group', groupId, {[fieldKey]: fieldValue}).then((resp)=>{
      field.removeClass("submitting-select-button selected-select-button")
      field.blur();
      field.addClass( action === "delete" ? "empty-select-button" : "selected-select-button");
    }).catch(err=>{
      console.log("error")
      console.log(err)
      jQuery("#errors").text(err.responseText)
      field.removeClass("submitting-select-button selected-select-button")
      field.addClass( action === "add" ? "empty-select-button" : "selected-select-button")
    })
  })
  $('.dt_date_picker').datepicker({
    dateFormat: 'yy-mm-dd',
    onSelect: function (date) {
      let id = $(this).attr('id')
      API.save_field_api('group', groupId, { [id]: date }).catch(handelAjaxError)
    },
    changeMonth: true,
    changeYear: true
  })

  let memberList = $('.member-list')
  let memberCountInput = $('#member_count')
  let populateMembersList = ()=>{
    memberList.empty()

    group.members.forEach(m=>{
      if ( _.find( group.leaders || [], {ID: m.ID} ) ){
        m.leader = true
      }
    })
    group.members = _.sortBy( group.members, ["leader"])
    group.members.forEach(member=>{
      let leaderHTML = '';
      if( member.leader ){
        leaderHTML = `<i class="fi-foot small leader"></i>`
      }
      let memberHTML = `<div class="member-row" style="" data-id="${member.ID}">
          <div style="flex-grow: 1" class="member-status">
              <i class="fi-torso small"></i>
              <a href="${window.wpApiShare.site_url}/contacts/${member.ID}">${_.escape(member.post_title)}</a>
              ${leaderHTML}
          </div>
          <button class="button clear make-leader member-row-actions" data-id="${member.ID}">
            <i class="fi-foot small"></i>
          </button>
          <button class="button clear delete-member member-row-actions" data-id="${member.ID}">
            <i class="fi-x small"></i>
          </button>
        </div>`
      memberList.append(memberHTML)
    })
    memberCountInput.val( group.member_count )
  }
  populateMembersList()

  $(document).on("click", ".delete-member", function () {
    let id = $(this).data('id')
    $(`.member-row[data-id="${id}"]`).remove()
    window.API.save_field_api("group", groupId, {'members': {values:[{value:id, delete:true}]}}).then(groupRes=>{
      group=groupRes
      populateMembersList()
      masonGrid.masonry('layout')
    })
    if( _.find( group.leaders || [], {ID: id}) ) {
      window.API.save_field_api("group", groupId, {'leaders': {values: [{value: id, delete: true}]}})
    }
  })
  $(document).on("click", ".make-leader", function () {
    let id = $(this).data('id')
    let remove = false
    let existingLeaderIcon = $(`.member-row[data-id="${id}"] .leader`)
    if( _.find( group.leaders || [], {ID: id}) || existingLeaderIcon.length !== 0){
      remove = true
      existingLeaderIcon.remove()
    } else {
      $(`.member-row[data-id="${id}"] .member-status`).append(`<i class="fi-foot small leader"></i>`)
    }
    window.API.save_field_api("group", groupId, {'leaders': {values:[{value:id, delete:remove}]}}).then(groupRes=>{
      group=groupRes
      populateMembersList()
      masonGrid.masonry('layout')
    })
  })
  $('.add-new-member').on("click", function () {
    $('#add-new-group-member').foundation('open');
    ["members"].forEach(t=>{
      Typeahead[`.js-typeahead-${t}`].adjustInputSize()
    })
  })
  //create new group
  $(".js-create-contact").on("submit", function(e) {
    e.preventDefault();
    let title = $(".js-create-contact input[name=title]").val()
    API.create_contact({
      title,
      groups:{values:[{value:groupId}]},
      requires_update: true,
      overall_status: "active"
    }).then((newContact)=>{
        $(".reveal-after-contact-create").show()
        $("#new-contact-link").html(`<a href="${newContact.permalink}">${title}</a>`)
        $(".hide-after-contact-create").hide()
        $('#go-to-contact').attr('href', newContact.permalink);
        group.members.push({post_title:title, ID:newContact.post_id})
        if ( group.members.length > group.member_count ){
          group.member_count = group.members.length
        }
        populateMembersList()
        masonGrid.masonry('layout')
      })
      .catch(function(error) {
        $(".js-create-contact-button").removeClass("loading").addClass("alert");
        $(".js-create-contact").append(
          $("<div>").html(error.responseText)
        );
        console.error(error);
      });
  })







  //leave at the end
  masonGrid.masonry({
    itemSelector: '.grid-item',
    percentPosition: true
  });

})



