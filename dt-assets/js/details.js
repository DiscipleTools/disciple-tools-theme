jQuery(document).ready(function($) {
  let post_id = detailsSettings.post_id
  let post_type = detailsSettings.post_type
  let rest_api = window.APIV2
  if ( ['contacts', 'groups'].includes(detailsSettings.post_type ) ){
    post_type = post_type.substring(0, detailsSettings.post_type.length - 1);
    rest_api = window.API
  }

  $('input.text-input').change(function(){
    const id = $(this).attr('id')
    const val = $(this).val()
    rest_api.save_field_api(post_type, post_id, { [id]: val }).then((newPost)=>{
      $( document ).trigger( "text-input-updated", [ newPost, id, val ] );
    }).catch(handleAjaxError)
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
    rest_api.save_field_api(post_type, post_id, {[fieldKey]: fieldValue}).then((resp)=>{
      field.removeClass("submitting-select-button selected-select-button")
      field.blur();
      field.addClass( action === "delete" ? "empty-select-button" : "selected-select-button");
      $( document ).trigger( "dt_multi_select-updated", [ resp, fieldKey, optionKey, action ] );
    }).catch(err=>{
      field.removeClass("submitting-select-button selected-select-button")
      field.addClass( action === "add" ? "empty-select-button" : "selected-select-button")
      handleAjaxError(err)
    })
  })

  $('.dt_date_picker').datepicker({
    dateFormat: 'yy-mm-dd',
    onSelect: function (date) {
      let id = $(this).attr('id')
      rest_api.save_field_api( post_type, post_id, { [id]: date }).then((resp)=>{
        $( document ).trigger( "dt_date_picker-updated", [ resp, id, date ] );
      }).catch(handleAjaxError)
    },
    changeMonth: true,
    changeYear: true,
    yearRange: "1900:2050",
  })

  $('select.select-field').change(e => {
    const id = $(e.currentTarget).attr('id')
    const val = $(e.currentTarget).val()

    rest_api.save_field_api(post_type, post_id, { [id]: val }).then(resp => {
      $( document ).trigger( "select-field-updated", [ resp, id, val ] );
    }).catch(handleAjaxError)
  })

  $('input.number-input').on("blur", function(){
    const id = $(this).attr('id')
    const val = $(this).val()

    rest_api.save_field_api('group', groupId, { [id]: val }).then((groupResp)=>{
      $( document ).trigger( "number-input-updated", [ resp, id, val ] );
    }).catch(handleAjaxError)
  })


  /**
   * Follow
   */
  $('button.follow').on("click", function () {
    let following = !($(this).data('value') === "following")
    $(this).data("value", following ? "following" : "" )
    $(this).html( following ? "Following" : "Follow")
    $(this).toggleClass( "hollow" )
    let update = {
      follow: {values:[{value:contactsDetailsWpApiSettings.current_user_id, delete:!following}]},
      unfollow: {values:[{value:contactsDetailsWpApiSettings.current_user_id, delete:following}]}
    }
    rest_api.save_field_api( post_type, post_id, update )
  })

  // expand and collapse tiles
  $(".section-header").on("click", function () {
    $(this).parent().toggleClass("collapsed")
    $('.grid').masonry('layout')
  })
})
