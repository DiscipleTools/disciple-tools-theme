"use strict"
jQuery(document).ready(function($) {

  let post_id = window.detailsSettings.post_id
  let post_type = window.detailsSettings.post_type
  let post = window.detailsSettings.post_fields
  let field_settings = window.detailsSettings.post_settings.fields

  /* Church Metrics */
  let health_keys = Object.keys(field_settings.health_metrics.default)
  function fillOutChurchHealthMetrics() {
    if ( $("#health-metrics").length ) {
      let svgItem = document.getElementById("church-svg-wrapper").contentDocument

      let churchWheel = $(svgItem).find('svg')
      health_keys.forEach(m=>{
        if (post[`health_metrics`] && post.health_metrics.includes(m) ){
          churchWheel.find(`#${m.replace("church_", "")}`).css("opacity", "1")
          $(`#${m}`).css("opacity", "1")
        } else {
          churchWheel.find(`#${m.replace("church_", "")}`).css("opacity", ".1")
          $(`#${m}`).css("opacity", ".4")
        }
      })
      if ( !(post.health_metrics ||[]).includes("church_commitment") ){
        churchWheel.find('#group').css("opacity", "1")
        $(`#church_commitment`).css("opacity", ".4")
      } else {
        churchWheel.find('#group').css("opacity", ".1")
        $(`#church_commitment`).css("opacity", "1")
      }

      $(".js-progress-bordered-box").removeClass("half-opacity")
    }
  }
  $('#church-svg-wrapper').on('load', function() {
    fillOutChurchHealthMetrics()
  })
  fillOutChurchHealthMetrics()

  $('.group-progress-button').on('click', function () {
    let fieldId = $(this).attr('id')
    $(this).css('opacity', ".6");
    let already_set = _.get(post, `health_metrics`, []).includes(fieldId)
    let update = {values:[{value:fieldId}]}
    if ( already_set ){
      update.values[0].delete = true;
    }
    API.update_post( post_type, post_id, {"health_metrics": update })
      .then(groupData=>{
        post = groupData
        fillOutChurchHealthMetrics()
      }).catch(err=>{
        console.log(err)
    })
  })
  /* end Church fields*/


  /* Member List*/
  let memberList = $('.member-list')
  let memberCountInput = $('#member_count')
  let leaderCountInput = $('#leader_count')
  let populateMembersList = ()=>{
    memberList.empty()

    post.members.forEach(m=>{
      if ( _.find( post.leaders || [], {ID: m.ID} ) ){
        m.leader = true
      }
    })
    post.members = _.sortBy( post.members, ["leader"])
    post.members.forEach(member=>{
      let leaderHTML = '';
      if( member.leader ){
        leaderHTML = `<i class="fi-foot small leader"></i>`
      }
      let memberHTML = `<div class="member-row" style="" data-id="${_.escape( member.ID )}">
          <div style="flex-grow: 1" class="member-status">
              <i class="fi-torso small"></i>
              <a href="${_.escape(window.wpApiShare.site_url)}/contacts/${_.escape( member.ID )}">${_.escape(member.post_title)}</a>
              ${leaderHTML}
          </div>
          <button class="button clear make-leader member-row-actions" data-id="${_.escape( member.ID )}">
            <i class="fi-foot small"></i>
          </button>
          <button class="button clear delete-member member-row-actions" data-id="${_.escape( member.ID )}">
            <i class="fi-x small"></i>
          </button>
        </div>`
      memberList.append(memberHTML)
    })
    if (post.members.length === 0) {
      $("#empty-members-list-message").show()
    } else {
      $("#empty-members-list-message").hide()
    }
    memberCountInput.val( post.member_count )
    leaderCountInput.val( post.leader_count )
    window.masonGrid.masonry('layout')
  }
  populateMembersList()

  $( document ).on( "dt-post-connection-created", function( e, new_post, field_key ){
    if ( field_key === "members" ){
      post = new_post
      populateMembersList()
    }
  } )
  $(document).on("click", ".delete-member", function () {
    let id = $(this).data('id')
    $(`.member-row[data-id="${id}"]`).remove()
    API.update_post( post_type, post_id, {'members': {values:[{value:id, delete:true}]}}).then(groupRes=>{
      post=groupRes
      populateMembersList()
      window.masonGrid.masonry('layout')
    })
    if( _.find( post.leaders || [], {ID: id}) ) {
      API.update_post( post_type, post_id, {'leaders': {values: [{value: id, delete: true}]}})
    }
  })
  $(document).on("click", ".make-leader", function () {
    let id = $(this).data('id')
    let remove = false
    let existingLeaderIcon = $(`.member-row[data-id="${id}"] .leader`)
    if( _.find( post.leaders || [], {ID: id}) || existingLeaderIcon.length !== 0){
      remove = true
      existingLeaderIcon.remove()
    } else {
      $(`.member-row[data-id="${id}"] .member-status`).append(`<i class="fi-foot small leader"></i>`)
    }
    API.update_post( post_type, post_id, {'leaders': {values:[{value:id, delete:remove}]}}).then(groupRes=>{
      post=groupRes
      populateMembersList()
      window.masonGrid.masonry('layout')
    })
  })
  $('.add-new-member').on("click", function () {
    $('#add-new-group-member-modal').foundation('open');
    Typeahead[`.js-typeahead-members`].adjustInputSize()
  })
  $( document ).on( "dt-post-connection-added", function( e, new_post, field_key ){
    post = new_post;
    if ( field_key === "members" ){
      populateMembersList()
    }
  })

  /* end Member List */

  /* Four Fields */
  let loadFourFields = ()=>{
    if ( $('#four-fields').length ) {
      $('#four_fields_unbelievers').val( post.four_fields_unbelievers )
      $('#four_fields_believers').val( post.four_fields_believers )
      $('#four_fields_accountable').val( post.four_fields_accountable )
      $('#four_fields_church_commitment').val( post.four_fields_church_commitment )
      $('#four_fields_multiplying').val( post.four_fields_multiplying )
    }
  }

  let ffInputs = `
    <input type="text" name="four_fields_unbelievers" id="four_fields_unbelievers" class="four_fields" style="width:60px; position:absolute; top:120px; left:75px;" />
    <input type="text" name="four_fields_believers" id="four_fields_believers" class="four_fields" style="width:60px; position:absolute; top:120px; right:75px;" />
    <input type="text" name="four_fields_accountable" id="four_fields_accountable" class="four_fields" style="width:60px; position:absolute; bottom:80px; right:75px;" />
    <input type="text" name="four_fields_church_commitment" id="four_fields_church_commitment" class="four_fields" style="width:60px; position:absolute; bottom:80px; left:75px;" />
    <input type="text" name="four_fields_multiplying" id="four_fields_multiplying" class="four_fields" style="width:60px; position:absolute; top:220px; left:170px;" />
  `
  $('#four-fields-inputs').append(ffInputs)
  loadFourFields()

  $('input.four_fields').on("blur", function(){
    const id = $(this).attr('id')
    const val = $(this).val()

    window.API.update_post( post_type, post_id, { [id]: val }).then((resp)=>{
      $( document ).trigger( "text-input-updated", [ resp, id, val ] );
    }).catch(handleAjaxError)
  })
  /* End Four Fields */

  /**
   * Assigned_to
   */
  let assigned_to_input = $(`.js-typeahead-assigned_to`)
  $.typeahead({
    input: '.js-typeahead-assigned_to',
    minLength: 0,
    maxItem: 0,
    accent: true,
    searchOnFocus: true,
    source: TYPEAHEADS.typeaheadUserSource(),
    templateValue: "{{name}}",
    template: function (query, item) {
      return `<div class="assigned-to-row" dir="auto">
        <span>
            <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
            ${_.escape( item.name )}
        </span>
        ${ item.status_color ? `<span class="status-square" style="background-color: ${_.escape(item.status_color)};">&nbsp;</span>` : '' }
        ${ item.update_needed && item.update_needed > 0 ? `<span>
          <img style="height: 12px;" src="${_.escape( window.wpApiShare.template_dir )}/dt-assets/images/broken.svg"/>
          <span style="font-size: 14px">${_.escape(item.update_needed)}</span>
        </span>` : '' }
      </div>`
    },
    dynamic: true,
    hint: true,
    emptyTemplate: _.escape(window.wpApiShare.translations.no_records_found),
    callback: {
      onClick: function(node, a, item){
        API.update_post('groups', post_id, {assigned_to: 'user-' + item.ID}).then(function (response) {
          _.set(post, "assigned_to", response.assigned_to)
          assigned_to_input.val(post.assigned_to.display)
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
        if (_.get(post,  "assigned_to.display")){
          $('.js-typeahead-assigned_to').val(post.assigned_to.display)
        }
      }
    },
  });
  $('.search_assigned_to').on('click', function () {
    assigned_to_input.val("")
    assigned_to_input.trigger('input.typeahead')
    assigned_to_input.focus()
  })

})
