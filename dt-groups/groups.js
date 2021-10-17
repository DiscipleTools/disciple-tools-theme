"use strict"
jQuery(document).ready(function($) {

  let post_id        = window.detailsSettings.post_id;
  let post_type      = window.detailsSettings.post_type;
  let post           = window.detailsSettings.post_fields;
  let field_settings = window.detailsSettings.post_settings.fields;

  /* Health Metrics */
  let health_keys = Object.keys(field_settings.health_metrics.default);

  function fillOutChurchHealthMetrics() {
    let practiced_items = window.detailsSettings.post_fields.health_metrics || [];

    /* Make church commitment circle green */
    if ( practiced_items.indexOf( 'church_commitment' ) !== -1 ) {
      $('#health-items-container').addClass( 'committed' );
      $('#is-church-switch').prop('checked', true);
    }

    /* Color church circle items that are being practiced */
    let items = $( 'div[id^="icon_"]' );

    items.each( function( k, v ) {
        if ( practiced_items.indexOf( v.id.replace( 'icon_', '' ), practiced_items ) !== -1 ) {
            $( this ).children( 'img' ).attr( 'class','practiced-item' );
        }
    });

    /* Color group progress buttons */
    let icons = $( '.group-progress-button' );
    icons.each( function( k, v ) {
      if ( practiced_items.indexOf( v.id, practiced_items ) !== -1 ) {
        $( this ).addClass( 'practiced-button' );
      }
    });
  }

  fillOutChurchHealthMetrics();
  distributeItems();

  $('.health-item').on( 'click', function() {
    let fieldId = $( this ).attr( 'id' ).replace('icon_', '');
    let already_set = window.lodash.get(post, 'health_metrics', []).includes( fieldId );
    let update = { values: [ { value : fieldId } ] };
    if ( already_set ){
      update.values[0].delete = true;
    }
    API.update_post( post_type, post_id, { 'health_metrics': update })
      .then( groupData => {
        post = groupData;
        /* Update icon */
        if ( $( this ).attr( 'id' ) === 'church_commitment' ) {
          $( '#health-items-container' ).toggleClass( 'committed' );
          $( this ).toggleClass( 'practiced-button' );
          return true;
        }
        /* Toggle church health circle item color */
        $( this ).children( 'img' ).toggleClass( 'practiced-item' );
      }).catch( err=>{
        console.log( err );
    });
  });

  $('#is-church-switch').on( 'click', function() {
    let fieldId = 'church_commitment';
    let already_set = window.lodash.get(post, 'health_metrics', []).includes( fieldId );
    let update = { values: [ { value : fieldId } ] };
    if ( already_set ){
      update.values[0].delete = true;
    }
    API.update_post( post_type, post_id, { 'health_metrics': update })
      .then( groupData => {
        post = groupData;
        /* Update commitment circle */
        $( '#health-items-container' ).toggleClass( 'committed' );
      }).catch( err=>{
        console.log( err );
    });
  })

  /* Dynamically distribute items in Church Health Circle
     according to amount of health metric elements */
  function distributeItems() {
    let radius = 75;
    let items = $( '.health-item' ),
        container = $( '#health-items-container' ),
        item_count = items.length,
        fade_delay = 45,
        width = container.width(),
        height = container.height() + 66,
        angle = 0,
        step = (2*Math.PI) / items.length,
        y_offset = -35;

        if ( item_count >= 5 && item_count < 7 ) {
            radius = 90;
        }

        if ( item_count >= 7 & item_count < 11 ) {
            radius = 100;
        }

        if ( item_count >= 11 ) {
            radius = 110;
        }

        if ( item_count == 3 ) {
            angle = 22.5;
        }

    items.each(function() {
        let X = Math.round( width / 2 + radius * Math.cos(angle) - $( this ).width() / 2 );
        let y = Math.round( height / 2 + radius * Math.sin(angle) - $( this ).height() / 2 ) + y_offset;

        if ( item_count == 1 ) {
            X = 112.5;
            y = 68;
        }

        $(this).css({
            left: X + 'px',
            top: y + 'px',
        });
        $(this).delay(fade_delay).fadeIn( 1000, 'linear' );
        angle += step;
        fade_delay += 45;
    });
  }
  /* End Health Metrics*/


  /* Member List*/
  let memberList = $('.member-list')
  let memberCountInput = $('#member_count')
  let leaderCountInput = $('#leader_count')
  let populateMembersList = ()=>{
    memberList.empty()

    post.members.forEach(m=>{
      if ( window.lodash.find( post.leaders || [], {ID: m.ID} ) ){
        m.leader = true
      }
    })
    post.members = window.lodash.sortBy( post.members, ["leader"])
    post.members.forEach(member=>{
      let leaderHTML = '';
      if( member.leader ){
        leaderHTML = `<i class="fi-foot small leader"></i>`
      }
      const contactStatusHTML = ( member.data && member.data.overall_status )
        ? `<i class="fi-torso small" style="color: ${window.lodash.escape( member.data.overall_status.color )}" title="${window.lodash.escape( member.data.overall_status.label )}"></i>`
        : '<i class="fi-torso small"></i>'

      const milestonesHTML = member.data.milestones.reduce((htmlString, milestone) => {
        return milestone.icon
          ? htmlString + `<img class="dt-icon" src="${window.lodash.escape( milestone.icon )}" alt="${window.lodash.escape( milestone.label )}" title="${window.lodash.escape( milestone.label )}">`
          : htmlString
      }, '')
      let memberHTML = `<div class="member-row" style="" data-id="${window.lodash.escape( member.ID )}">
          <div style="flex-grow: 1" class="member-status">
              ${contactStatusHTML}
              <a href="${window.lodash.escape(window.wpApiShare.site_url)}/contacts/${window.lodash.escape( member.ID )}">${window.lodash.escape(member.post_title)}</a>
              ${leaderHTML}
              ${milestonesHTML}
          </div>
          <button class="button clear make-leader member-row-actions" data-id="${window.lodash.escape( member.ID )}">
            <i class="fi-foot small"></i>
          </button>
          <button class="button clear delete-member member-row-actions" data-id="${window.lodash.escape( member.ID )}">
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
    if( window.lodash.find( post.leaders || [], {ID: id}) ) {
      API.update_post( post_type, post_id, {'leaders': {values: [{value: id, delete: true}]}})
    }
  })
  $(document).on("click", ".make-leader", function () {
    let id = $(this).data('id')
    let remove = false
    let existingLeaderIcon = $(`.member-row[data-id="${id}"] .leader`)
    if( window.lodash.find( post.leaders || [], {ID: id}) || existingLeaderIcon.length !== 0){
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
    <label style="margin-left:33.3%;">
        <span></span>${window.lodash.escape(window.detailsSettings.post_settings.fields.four_fields_unbelievers.name)}
        <input class="four_fields" style="width: 60%;height: 25%;border: 1px solid #000;text-align: center;font-size: 24px;" type="text" name="four_fields_unbelievers" id="four_fields_unbelievers">
    </label>
    <div></div>
    <label style="margin-right:33.3%;">
        ${window.lodash.escape(window.detailsSettings.post_settings.fields.four_fields_believers.name)}
        <input class="four_fields" style="width: 60%;height: 25%;border: 1px solid #000;text-align: center;font-size: 24px;" type="text" name="four_fields_believers" id="four_fields_believers">
    </label>
    <div></div>
    <label style="text-align: center">
        ${window.lodash.escape(window.detailsSettings.post_settings.fields.four_fields_multiplying.name)}
        <input class="four_fields" style="width: 60%;height: 25%;border: 1px solid #000;text-align: center;font-size: 24px;margin:auto" type="text" name="four_fields_multiplying" id="four_fields_multiplying">
    </label>
    <div></div>
    <label style="margin-left:33.3%;">
        <input class="four_fields" style="width: 60%;height: 25%;border: 1px solid #000;text-align: center;font-size: 24px;margin-bottom:0" type="text" name="four_fields_accountable" id="four_fields_accountable">
        ${window.lodash.escape(window.detailsSettings.post_settings.fields.four_fields_accountable.name)}
    </label>
    <div></div>
    <label style="margin-right:33.3%;">
        <input class="four_fields" style="width: 60%;height: 25%;border: 1px solid #000;text-align: center;font-size: 24px;margin-bottom:0" type="text" name="four_fields_church_commitment" id="four_fields_church_commitment">
        ${window.lodash.escape(window.detailsSettings.post_settings.fields.four_fields_church_commitment.name)}
    </label>
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


  //update the end date input when group is closed.
  $( document ).on( 'select-field-updated', function (e, new_group, field_key, val) {
    if ( field_key === "group_status" && new_group.end_date){
      $('#end_date').val(window.SHAREDFUNCTIONS.formatDate( new_group.end_date.timestamp) )
    }
  })

})
