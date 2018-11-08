/* global jQuery:false, wpApiGroupsSettings:false, _:false */

let typeaheadTotals = {}
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
  typeaheadTotals.assigned_to = 0;
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
        editFieldsUpdate.assigned_to = item.ID
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
        if (_.get(group,  "assigned_to.display")){
          assignedToInput.val(group.assigned_to.display)
        }
        assignedToInput.focus()
        $('.assigned_to-result-container').html("");
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
   * Locations
   */
  typeaheadTotals.locations = 0;
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
        resultCount = typeaheadTotals.locations
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#locations-result-container').html(text);
      },
      onHideLayout: function () {
        $('#locations-result-container').html("");
      }
    }
  });

  let peopleGroupList = $('.people_groups-list')
  /**
   * People_groups
   */
  typeaheadTotals.people_groups = 0;
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
        resultCount = typeaheadTotals.people_groups
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
  typeaheadTotals.groups = 0;
  $.typeahead({
    input: '.js-typeahead-parent_groups',
    minLength: 0,
    accent: true,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      if (item.ID == "new-item"){
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
  /**
   * Child Groups
   */
  typeaheadTotals.groups = 0;
  $.typeahead({
    input: '.js-typeahead-child_groups',
    minLength: 0,
    accent: true,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      if (item.ID == "new-item"){
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

  $("#add-new-address").click(function () {
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
  typeaheadTotals.members = 0;
  $.typeahead({
    input: '.js-typeahead-members',
    minLength: 0,
    accent: true,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      return `<span>${_.escape(item.name)}</span>`
    },
    source: TYPEAHEADS.typeaheadSource('members', 'dt/v1/contacts/compact/'),
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

          }).catch(err => { console.error(err) })
        }
      },
      href: window.wpApiShare.site_url + "/contacts/{{ID}}"
    },
    callback: {
      onClick: function(node, a, item, event){
        API.save_field_api('group', groupId, {'members': {values:[{value:item.ID}]}}).then((addedItem)=>{
        }).catch(err => { console.error(err) })
        masonGrid.masonry('layout')
      },
      onResult: function (node, query, result, resultCount) {
        resultCount = typeaheadTotals.members
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
  typeaheadTotals.coaches = 0;
  $.typeahead({
    input: '.js-typeahead-coaches',
    minLength: 0,
    accent: true,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      return `<span>${_.escape(item.name)}</span>`
    },
    source: TYPEAHEADS.typeaheadSource('coaches', 'dt/v1/contacts/compact/'),
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
        resultCount = typeaheadTotals.coaches
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#coaches-result-container').html(text);
      },
      onHideLayout: function () {
        $('#coaches-result-container').html("");
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
    accent: true,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      return `<span>${_.escape(item.name)}</span>`
    },
    source: TYPEAHEADS.typeaheadSource('leaders', 'dt/v1/contacts/compact/'),
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
          _.pullAllBy(editFieldsUpdate.leaders.values, [{value:item.ID}], "value")
          editFieldsUpdate.leaders.values.push({value:item.ID, delete:true})
        }
      },
      href: window.wpApiShare.site_url + "/contacts/{{ID}}"
    },
    callback: {
      onClick: function(node, a, item, e){
        _.pullAllBy(editFieldsUpdate.leaders.values, [{value:item.ID}], "value")
        editFieldsUpdate.leaders.values.push({value:item.ID})
        this.addMultiselectItemLayout(item)
        event.preventDefault()
        this.hideLayout();
        this.resetInput();
      },
      onResult: function (node, query, result, resultCount) {
        resultCount = typeaheadTotals.leaders
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#leaders-result-container').html(text);
      },
      onHideLayout: function () {
        $('#leaders-result-container').html("");
      }
    }
  });


  /**
   * Setup group fields
   */

  if (group.assigned_to){
    $('.current-assigned').text(_.get(group, "assigned_to.display"))
  }


  $("#open-edit").on("click", function () {
    editFieldsUpdate = {
      locations : { values: [] },
      people_groups : { values: [] },
      leaders : { values: [] },
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
    ["locations", "people_groups", "leaders"].forEach(t=>{
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
    let assignedHtml = $(`.assigned_to.details-list`).empty()
    if ( group.assigned_to ){
      assignedHtml.html(group.assigned_to.display)
    } else {
      assignedHtml.html(wpApiGroupsSettings.translations["not-set"]["assigned_to"])
    }


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

  //check if we still need to wait for the svg to load.
  let svgWrapper = $('#church-svg-wrapper')[0].contentDocument
  if (svgWrapper == null || _.get(svgWrapper, "length", 0) === 0) {
    $('#church-svg-wrapper').on('load', function() {
      fillOutChurchHealthMetrics()
    })
  } else {
    fillOutChurchHealthMetrics()
  }

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
    let field = jQuery("#" + optionKey)
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







  //leave at the end
  masonGrid.masonry({
    itemSelector: '.grid-item',
    percentPosition: true
  });

})



