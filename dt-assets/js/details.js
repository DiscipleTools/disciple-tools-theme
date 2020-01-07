jQuery(document).ready(function($) {
  let post_id = detailsSettings.post_id
  let post_type = detailsSettings.post_type
  let post = detailsSettings.post_fields
  let rest_api = window.API
  // if ( ['contacts', 'groups'].includes(detailsSettings.post_type ) ){
  //   post_type = post_type.substring(0, detailsSettings.post_type.length - 1);
  //   rest_api = window.API
  // }

  let masonGrid = $('.grid') // responsible for resizing and moving the tiles

  $('input.text-input').change(function(){
    const id = $(this).attr('id')
    const val = $(this).val()
    rest_api.update_post(post_type, post_id, { [id]: val }).then((newPost)=>{
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
    rest_api.update_post(post_type, post_id, {[fieldKey]: fieldValue}).then((resp)=>{
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
      rest_api.update_post( post_type, post_id, { [id]: date }).then((resp)=>{
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

    rest_api.update_post(post_type, post_id, { [id]: val }).then(resp => {
      $( document ).trigger( "select-field-updated", [ resp, id, val ] );
    }).catch(handleAjaxError)
  })

  $('input.number-input').on("blur", function(){
    const id = $(this).attr('id')
    const val = $(this).val()

    rest_api.update_post(post_type, post_id, { [id]: val }).then((resp)=>{
      $( document ).trigger( "number-input-updated", [ resp, id, val ] );
    }).catch(handleAjaxError)
  })

  $('input.four_fields').on("blur", function(){
    const id = $(this).attr('id')
    const val = $(this).val()

    rest_api.update_post(post_type, post_id, { [id]: val }).then((resp)=>{
      $( document ).trigger( "text-input-updated", [ resp, id, val ] );
    }).catch(handleAjaxError)
  })


  $('.dt_typeahead').each((key, el)=>{
    let field_id = $(el).attr('id').replace('_connection', '')
    let listing_post_type = _.get(detailsSettings.post_settings.fields[field_id], "p2p_listing", 'contacts')
    $.typeahead({
      input: `.js-typeahead-${field_id}`,
      minLength: 0,
      accent: true,
      maxItem: 30,
      searchOnFocus: true,
      template: window.TYPEAHEADS.contactListRowTemplate,
      matcher: function (item) {
        return item.ID !== post_id
      },
      source: window.TYPEAHEADS.typeaheadPostsSource(listing_post_type),
      display: "name",
      templateValue: "{{name}}",
      dynamic: true,
      multiselect: {
        matchOn: ["ID"],
        data: function () {
          return (post[field_id] || [] ).map(g=>{
            return {ID:g.ID, name:g.post_title}
          })
        }, callback: {
          onCancel: function (node, item) {
            API.update_post(post_type, post_id, {[field_id]: {values:[{value:item.ID, delete:true}]}})
              .catch(err => { console.error(err) })
          }
        },
        href: window.wpApiShare.site_url + `/${listing_post_type}/{{ID}}`
      },
      callback: {
        onClick: function(node, a, item, event){
          API.update_post(post_type, post_id, {[field_id]: {values:[{"value":item.ID}]}}).catch(err => { console.error(err) })
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
        onShowLayout (){
          masonGrid.masonry('layout')
        }
      }
    })
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
      follow: {values:[{value:detailsSettings.current_user_id, delete:!following}]},
      unfollow: {values:[{value:detailsSettings.current_user_id, delete:following}]}
    }
    rest_api.update_post( post_type, post_id, update )
  })

  let collapsed_tiles = window.SHAREDFUNCTIONS.get_json_cookie('collapsed_tiles')
  // expand and collapse tiles, only when a section chevron icon is clicked for that given tile.
  $(".section-header .section-chevron").on("click", function () {
    let tile =$(this).closest('.bordered-box')
    tile.toggleClass("collapsed")
    let tile_id = tile.attr("id")
    if ( tile_id && tile_id.includes('-tile')){
      if ( collapsed_tiles.includes(tile_id) ){
        collapsed_tiles = _.pull(collapsed_tiles, tile_id)
      } else {
        collapsed_tiles.push(tile_id)
      }
      window.SHAREDFUNCTIONS.save_json_cookie('collapsed_tiles', collapsed_tiles, detailsSettings.post_type)
    }
    $('.grid').masonry('layout')
  })
  $(".bordered-box").each((index, item)=>{
    let id = $(item).attr('id')
    if ( id && id.includes('-tile') && collapsed_tiles.includes(id) ){
      $(item).addClass('collapsed')
    }
  })

  /**
   * Share
   */
  let shareTypeahead = null
  $('.open-share').on("click", function(){
    $('#share-contact-modal').foundation('open');
    if  (!shareTypeahead) {
      shareTypeahead = TYPEAHEADS.share(post_type, post_id, !['contacts', 'groups'].includes(detailsSettings.post_type ) )
    }
  })


  // /*
  //  * Custom post types
  //  */
  // let details_section_dom = $('#details-section')
  // let details_fields_html = ''
  // _.forOwn( detailsSettings.post_settings.fields, ( field_settings, field_key )=>{
  //   if ( field_settings.tile === 'details' ){
  //     let field_value = _.get( detailsSettings.post_fields, field_key, false )
  //     if ( field_value !== false ){
  //       let values_html = '';
  //       if ( field_settings.type === 'text' ){
  //         values_html = _.escape( field_value )
  //       } else if ( field_settings.type === 'date' ){
  //         values_html = _.escape( field_value.formatted )
  //       } else if ( field_settings.type === 'key_select' ){
  //         values_html = _.escape( field_value.label )
  //       } else if ( field_settings.type === 'multi_select' ){
  //         field_value.push('test')
  //         values_html = field_value.map(v=>{
  //           return `<li>${_.escape( _.get( field_settings, `default[${v}].label`, v ))}</li>`;
  //         }).join('')
  //       }
  //       // @todo connections maybe
  //
  //       details_fields_html += `
  //         <div style="flex-basis: 33%">
  //           <div class="section-subheader">
  //             <img src="${_.escape( field_settings.icon )}">
  //             ${ _.escape( field_settings.name )}
  //           </div>
  //           <ul>
  //             ${ values_html }
  //           </ul>
  //         </div>
  //       `
  //     }
  //   }
  // })
  // details_section_dom.html(details_fields_html)



  //leave at the end of this file
  masonGrid.masonry({
    itemSelector: '.grid-item',
    percentPosition: true
  });
  //leave at the end of this file
})
