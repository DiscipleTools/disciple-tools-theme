window.mapbox_library_api.current_map_type = "area"
jQuery(document).ready(function($) {
  let user_list = []

  makeRequest( "POST", `get_user_list`, null , 'user-management/v1/')
  .done(response=>{
    user_list = response
  }).catch((e)=>{
    console.log( 'error in activity')
    console.log( e)
  })

  $('#map-type').hide()
  let field_key = "user_status_options"
  let options_html = `<button class="button small selected-select-button" data-key="all">
    ${window.lodash.escape(window.dt_mapbox_metrics.translations.all)}:
  </button>`
  window.lodash.forOwn(window.dt_mapbox_metrics.settings.user_status_options, (option, option_key)=>{
    options_html += `<button class="button small empty-select-button" data-key="${window.lodash.escape(option_key)}" style="">
      ${window.lodash.escape(option)}
    </button>`
  })
  let split_by_html = `
      <div id="${field_key}" class="border-left map-option-buttons">
        ${window.lodash.escape(window.dt_mapbox_metrics.translations.user_status)}:
        ${options_html}
      </div>
    `
  $('#legend-bar').append(split_by_html)
  $(`#${field_key} button`).on('click', function (e){
    let buttons = $(`#${field_key} button`)
    buttons.removeClass("selected-select-button")
    buttons.addClass("empty-select-button")
    $(this).addClass("selected-select-button")

    mapbox_library_api.query_args = { status: $(this).data('key') }
    mapbox_library_api.setup_map_type()
  })

  window.mapbox_library_api.area_map.load_detail_panel =  function ( lng, lat, level ) {

    lng = window.mapbox_library_api.standardize_longitude( lng )
    if ( level === 'world' ) {
      level = 'admin0'
    }

    let content = jQuery('#geocode-details-content')
    content.empty().html(`<span class="loading-spinner users-spinner active"></span>`)

    jQuery('#geocode-details').show()

    // geocode
    makeRequest('GET', window.wpApiShare.template_dir + '/dt-mapping/location-grid-list-api.php',
      {
        type:'geocode',
        longitude:lng,
        latitude:lat,
        level:level,
        nonce: window.mapbox_library_api.obj.settings.geocoder_nonce,
        query: window.mapbox_library_api.query_args || {}
      } )
    .done(details=>{
      /* hierarchy list*/
      content.empty().append(`<ul id="hierarchy-list" class="accordion" data-accordion></ul>`)
      let list = jQuery('#hierarchy-list')

      if ( details.admin0_grid_id ) {
        list.append( `
          <li id="admin0_wrapper" class="accordion-item" data-accordion-item>
            <a href="#" class="accordion-title">${window.lodash.escape(details.admin0_name)} :  <span id="admin0_count">0</span></a>
            <div class="accordion-content grid-x" data-tab-content><div id="admin0_list" class="grid-x"></div></div>
          </li>
        `)
        let level_list = jQuery('#admin0_list')
        if ( details.admin0_grid_id in user_list ) {
          jQuery('#admin0_count').html(user_list[details.admin0_grid_id].length)
          jQuery.each(user_list[details.admin0_grid_id], function(i,v) {
            level_list.append(`
              <div class="cell small-10 align-self-middle" data-id="${window.lodash.escape(v.grid_meta_id)}">
                <a href="${window.lodash.escape(window.wpApiShare.site_url)}/user-management/user/${window.lodash.escape(v.user_id)}">
                  ${window.lodash.escape(v.name)}
                </a>
              </div>
              <div class="cell small-2" data-id="${window.lodash.escape(v.grid_meta_id)}">
                <a class="button clear delete-button mapbox-delete-button small float-right" data-user_id="${window.lodash.escape(v.user_id)}" data-id="${window.lodash.escape(v.grid_meta_id)}" data-level="admin0" data-location="${window.lodash.escape(details.admin0_grid_id)}">
                  <img src="${window.lodash.escape(window.wpApiShare.template_dir)}/dt-assets/images/invalid.svg" alt="delete">
                </a>
              </div>`)
          })
        }
        level_list.append(`<div class="cell add-user-button"><button class="add-user small expanded button hollow" data-level="admin0" data-location="${window.lodash.escape(details.admin0_grid_id)}">
          ${window.lodash.escape(window.dt_mapbox_metrics.translations.add_user_to.replace('%s', details.admin0_name))}
        </button></div>`)

      }
      if ( details.admin1_grid_id ) {
        list.append( `
          <li id="admin1_wrapper" class="accordion-item" data-accordion-item >
            <a href="#" class="accordion-title">${window.lodash.escape(details.admin1_name)} : <span id="admin1_count">0</span></a>
            <div class="accordion-content" data-tab-content><div id="admin1_list" class="grid-x"></div></div>
          </li>
        `)

        let level_list = jQuery('#admin1_list')
        if ( details.admin1_grid_id in user_list ) {
          jQuery('#admin1_count').html(user_list[details.admin1_grid_id].length)
          jQuery.each(user_list[details.admin1_grid_id], function(i,v) {
            level_list.append(`
              <div class="cell small-10 align-self-middle" data-id="${window.lodash.escape(v.grid_meta_id)}">
                <a href="${window.lodash.escape(window.wpApiShare.site_url)}/user-management/user/${window.lodash.escape(v.user_id)}">
                  ${window.lodash.escape(v.name)}
                </a>
              </div>
              <div class="cell small-2" data-id="${window.lodash.escape(v.grid_meta_id)}">
                <a class="button clear delete-button mapbox-delete-button small float-right" data-user_id="${window.lodash.escape(v.user_id)}" data-id="${window.lodash.escape(v.grid_meta_id)}" data-level="admin1" data-location="${window.lodash.escape(details.admin1_grid_id)}">
                  <img src="${window.lodash.escape(window.wpApiShare.template_dir)}/dt-assets/images/invalid.svg" alt="delete">
                </a>
              </div>`)
          })
        }
        level_list.append(`<div class="cell add-user-button"><button class="add-user small expanded button hollow" data-level="admin1" data-location="${window.lodash.escape(details.admin1_grid_id)}">
          ${window.lodash.escape(window.dt_mapbox_metrics.translations.add_user_to.replace('%s', details.admin1_name))}
        </button></div>`)
      }
      if ( details.admin2_grid_id ) {
        list.append( `
          <li id="admin2_wrapper" class="accordion-item" data-accordion-item>
            <a href="#" class="accordion-title">${window.lodash.escape(details.admin2_name)} : <span id="admin2_count">0</span></a>
            <div class="accordion-content" data-tab-content><div id="admin2_list"  class="grid-x"></div></div>
          </li>
        `)

        let level_list = jQuery('#admin2_list')
        if ( details.admin2_grid_id in user_list ) {
          jQuery('#admin2_count').html(user_list[details.admin2_grid_id].length)
          jQuery.each(user_list[details.admin2_grid_id], function(i,v) {
            level_list.append(`
              <div class="cell small-10 align-self-middle" data-id="${window.lodash.escape(v.grid_meta_id)}">
                <a href="${window.lodash.escape(window.wpApiShare.site_url)}/user-management/user/${window.lodash.escape(v.user_id)}">
                  ${window.lodash.escape(v.name)}
                </a>
              </div>
              <div class="cell small-2" data-id="${window.lodash.escape(v.grid_meta_id)}">
                <a class="button clear delete-button mapbox-delete-button small float-right" data-user_id="${window.lodash.escape(v.user_id)}" data-id="${window.lodash.escape(v.grid_meta_id)}" data-level="admin2"  data-location="${window.lodash.escape(details.admin2_grid_id)}">
                  <img src="${window.lodash.escape(window.wpApiShare.template_dir)}/dt-assets/images/invalid.svg" alt="delete">
                </a>
              </div>`)
          })
        }
        level_list.append(`<div class="cell add-user-button"><button class="add-user expanded small button hollow" data-level="admin2" data-location="${window.lodash.escape(details.admin2_grid_id)}">
          ${window.lodash.escape(window.dt_mapbox_metrics.translations.add_user_to.replace('%s', details.admin2_name))}
        </button></div>`)
      }

      jQuery('.accordion-item').last().addClass('is-active')
      list.foundation()
      /* end hierarchy list */

      /* build click function to add user to location */
      jQuery('.add-user').on('click', function() {
        jQuery('#add-user-wrapper').remove()
        let selected_location = jQuery(this).data('location')
        let list_level = jQuery(this).data('level')

        jQuery(this).parent().append(`
          <div id="add-user-wrapper">
            <var id="add-user-location-result-container" class="result-container add-user-location-result-container"></var>
            <div id="assigned_to_t" name="form-assigned_to">
              <div class="typeahead__container">
                <div class="typeahead__field">
                  <span class="typeahead__query">
                    <input class="js-typeahead-add-user input-height" dir="auto"
                           name="assigned_to[query]" placeholder="Search Users"
                           autocomplete="off">
                  </span>
                  <span class="typeahead__button">
                    <button type="button" class="search_assigned_to typeahead__image_button input-height" data-id="assigned_to_t">
                      <img src="${window.lodash.escape(window.wpApiShare.template_dir)}/dt-assets/images/chevron_down.svg" alt="chevron"/>
                    </button>
                  </span>
                </div>
              </div>
            </div>
          </div>
          `)
        jQuery.typeahead({
          input: '.js-typeahead-add-user',
          minLength: 0,
          accent: true,
          searchOnFocus: true,
          source: TYPEAHEADS.typeaheadUserSource(),
          templateValue: "{{name}}",
          template: function (query, item) {
            return `<div class="assigned-to-row" dir="auto">
              <span>
                  <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
                  ${window.lodash.escape( item.name )}
              </span>
              ${ window.lodash.escape(item.status_color) ? `<span class="status-square" style="background-color: ${window.lodash.escape(item.status_color)};">&nbsp;</span>` : '' }
              ${ item.update_needed ? `<span>
                <img style="height: 12px;" src="${window.lodash.escape( window.wpApiShare.template_dir )}/dt-assets/images/broken.svg"/>
                <span style="font-size: 14px">${window.lodash.escape(item.update_needed)}</span>
              </span>` : '' }
            </div>`
          },
          dynamic: true,
          hint: true,
          emptyTemplate: window.lodash.escape(window.wpApiShare.translations.no_records_found),
          callback: {
            onClick: function(node, a, item){
              let data = {
                user_id: item.ID,
                user_location: {
                  location_grid_meta: [
                    {
                      grid_id: selected_location
                    }
                  ]
                }
              }
              makeRequest( "POST", `users/user_location`, data )
              .then(function (response) {
                makeRequest( "POST", `get_user_list`, null , 'user-management/v1/')
                .done(response=>{
                  user_list = response
                  if ( selected_location in user_list ) {
                    jQuery('#'+list_level+'_count').html(user_list[selected_location].length)
                  }
                }).catch((e)=>{
                  console.log( 'error in get_user_list')
                  console.log( e)
                })

                window.mapbox_library_api.load_map()

                // remove user add input
                jQuery('#add-user-wrapper').remove()

                // add new user to list
                jQuery.each(response.user_location.location_grid_meta, function(i,v) {
                  if ( v.grid_id.toString() === selected_location.toString() ) {
                    jQuery('#'+list_level+'_list').prepend(`
                      <div class="cell small-10 align-self-middle" data-id="${window.lodash.escape(v.grid_meta_id)}">
                        <a  href="${window.lodash.escape(window.wpApiShare.site_url)}/user-management/user/${window.lodash.escape(response.user_id)}">
                          ${window.lodash.escape(response.user_title)}
                        </a>
                      </div>
                      <div class="cell small-2"">
                        <a class="button clear delete-button mapbox-delete-button small float-right" data-user_id="${window.lodash.escape(response.user_id)}" data-id="${window.lodash.escape(v.grid_meta_id)}">
                          <img src="${window.lodash.escape(window.wpApiShare.template_dir)}/dt-assets/images/invalid.svg" alt="delete">
                        </a>
                      </div>`)
                  }
                })

                window.mapbox_library_api.area_map.delete_user_action()


              }).catch(err => { console.error(err) })
            },
            onResult: function (node, query, result, resultCount) {
              let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
              $('#add-user-location-result-container').html(text);
            },
            onHideLayout: function () {
              $('.add-user-location-result-container').html("");
            },
            onReady: function () {

            }
          },
        });

      })
      /* end click add function */

      window.mapbox_library_api.area_map.delete_user_action()

    }); // end geocode
  }

  window.mapbox_library_api.area_map.delete_user_action = function () {
    jQuery( '.mapbox-delete-button' ).on( "click", function(e) {

      let selected_location = jQuery(this).data('location')
      let list_level = jQuery(this).data('level')

      let level_count = jQuery('#'+list_level+'_count')
      level_count.html( (parseInt( level_count.html()) ) - 1)


      let data = {
        user_id: e.currentTarget.dataset.user_id,
        user_location: {
          location_grid_meta: [
            {
              grid_meta_id: e.currentTarget.dataset.id,
            }
          ]
        }
      }

      // let post_id = e.currentTarget.dataset.user_id
      makeRequest( "DELETE", `users/user_location`, data )
      .then(function (response) {

        jQuery('div[data-id=' + e.currentTarget.dataset.id + ']').remove()

        makeRequest( "POST", `get_user_list`, null , 'user-management/v1/')
        .done(response=>{
          user_list = response

          if ( selected_location in user_list ) {
            jQuery('#'+list_level+'_count').html(user_list[selected_location].length)
          }
        }).catch((e)=>{
          console.log( 'error in get_user_list')
          console.log( e)
        })
        //reload the map
        window.mapbox_library_api.load_map()
      }).catch(err => { console.error(err) })

    });
  }

})

