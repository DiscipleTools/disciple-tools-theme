/* global jQuery:false, wpApiGroupsSettings:false */

let typeaheadTotals = {}
jQuery(document).ready(function($) {

  let group = wpApiGroupsSettings.group
  let masonGrid = $('.grid')
  let groupId = group.ID
  let editFieldsUpdate = {}


  /**
   * End Date picker
   */
  let endDatePicker = $('.end_date #end-date-picker')
  endDatePicker.datepicker({
    onSelect: function (date) {
      editFieldsUpdate.end_date = date
    },
    changeMonth: true,
    changeYear: true
  })

  /**
   * Start date picker
   */
  let startDatePicker = $('.start_date #start-date-picker')
  startDatePicker.datepicker({
    onSelect: function (date) {
      editFieldsUpdate.start_date = date
    },
    changeMonth: true,
    changeYear: true
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
      onClick: function(node, a, item, event){
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

  let locationsList = $('.locations-list')
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
            $(`.members-list .${item.ID}`).remove()
            let listItems = $(`.members-list li`)
            if (listItems.length === 0){
              $(`.members-list.details-list`).append(`<li id="no-locations">${wpApiGroupsSettings.translations["not-set"]["location"]}</li>`)
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
          $("#no-locations").remove()
        })
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
      href: "/contacts/{{ID}}"
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

  if (group.end_date){
    endDatePicker.datepicker('setDate', group.end_date)
  }
  if (group.start_date){
    startDatePicker.datepicker('setDate', group.start_date)
  }
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
  }).on('change', '.text-input', function () {
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

    let dates = ["start_date", "end_date"]
    dates.forEach(dateField=>{
      if ( group[dateField] ){
        $(`.${dateField}.details-list`).html(group[dateField])
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
    ).catch(err=>{
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
  //for custom fields
  Object.keys(group).forEach(m=>{
    m = m.replace("church_custom_", "");
    if (group[`church_custom_${m}`] && ["1", "Yes"].indexOf(group[`church_custom_${m}`]["key"])> -1){
      churchWheel.find(`#${m}`).css("opacity", "1")
      $(`#church_custom_${m}`).css("opacity", "1")
    } else {
      churchWheel.find(`#${m}`).css("opacity", ".1")
      $(`#church_custom_${m}`).css("opacity", ".4")
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







  //leave at the end
  masonGrid.masonry({
    itemSelector: '.grid-item',
    percentPosition: true
  });

})



