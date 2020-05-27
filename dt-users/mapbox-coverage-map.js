jQuery(document).ready(function() {

  write_users_map()
  function write_users_map() {
    let obj = dt_user_management_localized
    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner users-spinner active"></span> '

    chart.empty().html(spinner)

    makeRequest( "POST", `get_user_list`, null , 'user-management/v1/')
      .done(response=>{
        // console.log('user_list')
        // console.log(response)
        window.user_list = response
      }).catch((e)=>{
      console.log( 'error in activity')
      console.log( e)
    })

    makeRequest( "POST", `grid_totals`, { status: null }, 'user-management/v1/')
      .done(grid_data=>{
        window.grid_data = grid_data

        chart.empty().html(`
                <style>
                    #map-wrapper {
                        height: ${window.innerHeight - 100}px !important;
                    }
                    #map {
                        height: ${window.innerHeight - 100}px !important;
                    }
                    #geocode-details {
                        height: ${window.innerHeight - 250}px !important;
                        overflow: scroll;
                        opacity: 100%;
                    }
                    .accordion {
                        list-style-type:none;
                    }
                    .delete-button {
                        margin-bottom: 0 !important;
                    }
                    .add-user-button {
                        padding-top: 10px;
                    }
                </style>
                <div id="map-wrapper">
                    <div id='map'></div>
                    <div id='legend' class='legend'>
                        <div class="grid-x grid-margin-x grid-padding-x">
                            <div class="cell small-2 center info-bar-font">
                                Responsibility
                            </div>
                            <div class="cell small-2 center border-left">
                                <select id="level" class="small" style="width:170px;">
                                    <option value="none" disabled></option>
                                    <option value="none" disabled>Zoom Level</option>
                                    <option value="none"></option>
                                    <option value="auto" selected>Auto Zoom</option>
                                    <option value="none" disabled>-----</option>
                                    <option value="world">World</option>
                                    <option value="admin0">Country</option>
                                    <option value="admin1">State</option>
                                    <option value="none" disabled></option>
                                </select>
                            </div>
                            <div class="cell small-2 center border-left">
                                <select id="status" class="small" style="width:170px;">
                                    <option value="none" disabled></option>
                                    <option value="none" disabled>Status</option>
                                    <option value="none"></option>
                                    <option value="all" selected>Status - All</option>
                                    <option value="none" disabled>-----</option>
                                    <option value="active">Active</option>
                                    <option value="away">Away</option>
                                    <option value="inconsistent">Inconsistent</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="none" disabled></option>
                                </select>
                            </div>
                            <div class="cell small-5 center border-left info-bar-font">

                            </div>

                            <div class="cell small-1 center border-left">
                                <div class="grid-y">
                                    <div class="cell center" id="admin">World</div>
                                    <div class="cell center" id="zoom" >0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="spinner">${spinner}</div>
                    <div id="cross-hair">&#8982</div>
                    <div id="geocode-details" class="geocode-details">
                        Response Coverage<span class="close-details" style="float:right;"><i class="fi-x"></i></span>
                        <hr style="margin:10px 5px;">
                        <div id="geocode-details-content"></div>
                    </div>
                </div>
             `)

        // set info box
        set_info_boxes()

        // init map
        mapboxgl.accessToken = obj.map_key;
        var map = new mapboxgl.Map({
          container: 'map',
          style: 'mapbox://styles/mapbox/light-v10',
          center: [-98, 38.88],
          minZoom: 1,
          zoom: 1.8
        });

        // SET BOUNDS
        window.map_bounds_token = 'user_coverage_map'
        window.map_start = get_map_start( window.map_bounds_token )
        if ( window.map_start ) {
          map.fitBounds( window.map_start, {duration: 0});
        }
        map.on('zoomend', function() {
          set_map_start( window.map_bounds_token, map.getBounds() )
        })
        map.on('dragend', function() {
          set_map_start( window.map_bounds_token, map.getBounds() )
        })
        // end set bounds

        // disable map rotation using right click + drag
        map.dragRotate.disable();

        // disable map rotation using touch rotation gesture
        map.touchZoomRotate.disableRotation();

        // CROSS HAIR
        map.on('zoomstart', function() {
          jQuery('#cross-hair').show()
        })
        map.on('zoomend', function() {
          jQuery('#cross-hair').hide()
        })
        map.on('dragstart', function() {
          jQuery('#cross-hair').show()
        })
        map.on('dragend', function() {
          jQuery('#cross-hair').hide()
        })
        // end cross-hair

        // grid memory vars
        window.previous_grid_id = 0
        window.previous_grid_list = []

        // LOAD
        map.on('load', function() {

          if ( window.map_start ) { // if bounds defined
            let lnglat = map.getCenter()
            load_layer( lnglat.lng, lnglat.lat, 'zoom' )

          } else { // if no bounds defined

            window.previous_grid_id = '1'
            window.previous_grid_list.push('1')
            jQuery.get('https://storage.googleapis.com/location-grid-mirror/collection/1.geojson', null, null, 'json')
              .done(function (geojson) {

                jQuery.each(geojson.features, function (i, v) {
                  if (window.grid_data[geojson.features[i].properties.id]) {
                    geojson.features[i].properties.value = parseInt(window.grid_data[geojson.features[i].properties.id].count)
                  } else {
                    geojson.features[i].properties.value = 0
                  }
                })
                map.addSource('1', {
                  'type': 'geojson',
                  'data': geojson
                });
                map.addLayer({
                  'id': '1',
                  'type': 'fill',
                  'source': '1',
                  'paint': {
                    'fill-color': [
                      'interpolate',
                      ['linear'],
                      ['get', 'value'],
                      0,
                      'rgba(0, 0, 0, 0)',
                      1,
                      '#547df8',
                      50,
                      '#3754ab',
                      100,
                      '#22346a'
                    ],
                    'fill-opacity': 0.75
                  }
                });
                map.addLayer({
                  'id': '1line',
                  'type': 'line',
                  'source': '1',
                  'paint': {
                    'line-color': 'black',
                    'line-width': 1
                  }
                });
              })
          }
        })

        // update info box on zoom
        map.on('zoom', function() {
          document.getElementById('zoom').innerHTML = Math.floor(map.getZoom())

          let level = get_level()
          let name = ''
          if ( level === 'world') {
            name = 'World'
          } else if ( level === 'admin0') {
            name = 'Country'
          } else if ( level === 'admin1' ) {
            name = 'State'
          }
          document.getElementById('admin').innerHTML = name
        })

        // click controls
        window.click_behavior = 'layer'

        map.on('click', function( e ) {
          // this section increments up the result on level because
          // it corresponds better to the viewable user intent for details
          let level = get_level()
          if ( level === 'world' ) {
            level = 'admin0'
          }
          else if ( level === 'admin0' ) {
            level = 'admin1'
          }
          else if ( level === 'admin1' ) {
            level = 'admin2'
          }
          load_detail_panel( e.lngLat.lng, e.lngLat.lat, level )
        })

        // Status
        jQuery('#status').on('change', function() {
          window.current_status = jQuery('#status').val()

          makeRequest( "POST", `grid_totals`, { status: window.current_status }, 'user-management/v1/')
            .done(grid_data=>{
              window.previous_grid_id = 0
              clear_layers()
              window.grid_data = grid_data

              let lnglat = map.getCenter()
              load_layer( lnglat.lng, lnglat.lat )
            }).catch((e)=>{
            console.log('error getting grid_totals')
            console.log(e)
          })

        })
        // load new layer on event
        map.on('zoomend', function() {
          let lnglat = map.getCenter()
          load_layer( lnglat.lng, lnglat.lat, 'zoom' )
        } )
        map.on('dragend', function() {
          let lnglat = map.getCenter()
          load_layer( lnglat.lng, lnglat.lat, 'drag' )
        } )
        function load_layer( lng, lat, event_type ) {
          let spinner = jQuery('#spinner')
          spinner.show()

          // set geocode level, default to auto
          let level = get_level()

          // standardize longitude
          if (lng > 180) {
            lng = lng - 180
            lng = -Math.abs(lng)
          } else if (lng < -180) {
            lng = lng + 180
            lng = Math.abs(lng)
          }

          // geocode
          jQuery.get(obj.theme_uri + 'dt-mapping/location-grid-list-api.php',
            {
              type: 'geocode',
              longitude: lng,
              latitude: lat,
              level: level,
              country_code: null,
              nonce: obj.nonce
            }, null, 'json')
            .done(function (data) {

              // default layer to world
              if ( data.grid_id === undefined || level === 'world' ) {
                data.grid_id = '1'
              }

              // is new test
              if ( window.previous_grid_id !== data.grid_id ) {

                // is defined test
                var mapLayer = map.getLayer(data.grid_id);
                if(typeof mapLayer === 'undefined') {

                  // get geojson collection
                  jQuery.ajax({
                    type: 'GET',
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: 'https://storage.googleapis.com/location-grid-mirror/collection/' + data.grid_id + '.geojson',
                    statusCode: {
                      404: function() {
                        console.log('404. Do nothing.')
                      }
                    }
                  })
                    .done(function (geojson) {

                      // add data to geojson properties
                      jQuery.each(geojson.features, function (i, v) {
                        if (window.grid_data[geojson.features[i].properties.id]) {
                          geojson.features[i].properties.value = parseInt(window.grid_data[geojson.features[i].properties.id].count)
                        } else {
                          geojson.features[i].properties.value = 0
                        }
                      })

                      // add source
                      map.addSource(data.grid_id.toString(), {
                        'type': 'geojson',
                        'data': geojson
                      });

                      // add fill layer
                      map.addLayer({
                        'id': data.grid_id.toString(),
                        'type': 'fill',
                        'source': data.grid_id.toString(),
                        'paint': {
                          'fill-color': [
                            'interpolate',
                            ['linear'],
                            ['get', 'value'],
                            0,
                            'rgba(0, 0, 0, 0)',
                            1,
                            '#547df8',
                            50,
                            '#3754ab',
                            100,
                            '#22346a'
                          ],
                          'fill-opacity': 0.75
                        }
                      });

                      // add border lines
                      map.addLayer({
                        'id': data.grid_id.toString() + 'line',
                        'type': 'line',
                        'source': data.grid_id.toString(),
                        'paint': {
                          'line-color': 'black',
                          'line-width': 1
                        }
                      });

                      remove_layer( data.grid_id, event_type )

                    }) // end get geojson collection

                }
              } // end load new layer
              spinner.hide()
            }); // end geocode

        } // end load section function
        function load_detail_panel( lng, lat, level ) {

          // standardize longitude
          if (lng > 180) {
            lng = lng - 180
            lng = -Math.abs(lng)
          } else if (lng < -180) {
            lng = lng + 180
            lng = Math.abs(lng)
          }

          if ( level === 'world' ) {
            level = 'admin0'
          }

          let content = jQuery('#geocode-details-content')
          content.empty().html(`<img src="${obj.theme_uri}spinner.svg" class="spinner-image" alt="spinner"/>`)

          jQuery('#geocode-details').show()

          // geocode
          makeRequest('GET', obj.theme_uri + 'dt-mapping/location-grid-list-api.php?type=geocode&longitude='+lng+'&latitude='+lat+'&level='+level+'&nonce='+obj.nonce )
            .done(details=>{
              /* hierarchy list*/
              content.empty().append(`<ul id="hierarchy-list" class="accordion" data-accordion></ul>`)
              let list = jQuery('#hierarchy-list')

              if ( details.admin0_grid_id ) {
                list.append( `
                  <li id="admin0_wrapper" class="accordion-item" data-accordion-item>
                   <a href="#" class="accordion-title">${_.escape(details.admin0_name)} :  <span id="admin0_count">0</span></a>
                    <div class="accordion-content grid-x" data-tab-content><div id="admin0_list" class="grid-x"></div></div>
                  </li>
                `)
                let level_list = jQuery('#admin0_list')
                if ( details.admin0_grid_id in window.user_list ) {
                  jQuery('#admin0_count').html(window.user_list[details.admin0_grid_id].length)
                  jQuery.each(window.user_list[details.admin0_grid_id], function(i,v) {
                    level_list.append(`
                      <div class="cell small-10 align-self-middle" data-id="${_.escape(v.grid_meta_id)}">
                        <a href="/user-management/users/${_.escape(v.user_id)}">
                          ${_.escape(v.name)}
                        </a>
                      </div>
                      <div class="cell small-2" data-id="${_.escape(v.grid_meta_id)}">
                        <a class="button clear delete-button mapbox-delete-button small float-right" data-user_id="${_.escape(v.user_id)}" data-id="${_.escape(v.grid_meta_id)}" data-level="admin0" data-location="${_.escape(details.admin0_grid_id)}">
                          <img src="${_.escape(obj.theme_uri)}/dt-assets/images/invalid.svg" alt="delete">
                        </a>
                      </div>`)
                  })
                }
                level_list.append(`<div class="cell add-user-button"><button class="add-user small expanded button hollow" data-level="admin0" data-location="${_.escape(details.admin0_grid_id)}">add user to ${_.escape(details.admin0_name)}</button></div>`)

              }
              if ( details.admin1_grid_id ) {
                list.append( `
                  <li id="admin1_wrapper" class="accordion-item" data-accordion-item >
                    <a href="#" class="accordion-title">${_.escape(details.admin1_name)} : <span id="admin1_count">0</span></a>
                    <div class="accordion-content" data-tab-content><div id="admin1_list" class="grid-x"></div></div>
                  </li>
                `)

                let level_list = jQuery('#admin1_list')
                if ( details.admin1_grid_id in window.user_list ) {
                  jQuery('#admin1_count').html(window.user_list[details.admin1_grid_id].length)
                  jQuery.each(window.user_list[details.admin1_grid_id], function(i,v) {
                    level_list.append(`
                        <div class="cell small-10 align-self-middle" data-id="${_.escape(v.grid_meta_id)}">
                          <a href="/user-management/users/${_.escape(v.user_id)}">
                            ${_.escape(v.name)}
                          </a>
                        </div>
                        <div class="cell small-2" data-id="${_.escape(v.grid_meta_id)}">
                          <a class="button clear delete-button mapbox-delete-button small float-right" data-user_id="${_.escape(v.user_id)}" data-id="${_.escape(v.grid_meta_id)}" data-level="admin1" data-location="${_.escape(details.admin1_grid_id)}">
                            <img src="${_.escape(obj.theme_uri)}/dt-assets/images/invalid.svg" alt="delete">
                          </a>
                        </div>`)
                  })
                }
                level_list.append(`<div class="cell add-user-button"><button class="add-user small expanded button hollow" data-level="admin1" data-location="${_.escape(details.admin1_grid_id)}">add user to ${_.escape(details.admin1_name)}</button></div>`)
              }
              if ( details.admin2_grid_id ) {
                list.append( `
                  <li id="admin2_wrapper" class="accordion-item" data-accordion-item>
                    <a href="#" class="accordion-title">${_.escape(details.admin2_name)} : <span id="admin2_count">0</span></a>
                    <div class="accordion-content" data-tab-content><div id="admin2_list"  class="grid-x"></div></div>
                  </li>
                `)

                let level_list = jQuery('#admin2_list')
                if ( details.admin2_grid_id in window.user_list ) {
                  jQuery('#admin2_count').html(window.user_list[details.admin2_grid_id].length)
                  jQuery.each(window.user_list[details.admin2_grid_id], function(i,v) {
                    level_list.append(`
                        <div class="cell small-10 align-self-middle" data-id="${_.escape(v.grid_meta_id)}">
                          <a href="/user-management/users/${_.escape(v.user_id)}">
                            ${_.escape(v.name)}
                          </a>
                        </div>
                        <div class="cell small-2" data-id="${_.escape(v.grid_meta_id)}">
                          <a class="button clear delete-button mapbox-delete-button small float-right" data-user_id="${_.escape(v.user_id)}" data-id="${_.escape(v.grid_meta_id)}" data-level="admin2"  data-location="${_.escape(details.admin2_grid_id)}">
                            <img src="${_.escape(obj.theme_uri)}/dt-assets/images/invalid.svg" alt="delete">
                          </a>
                        </div>`)
                  })
                }
                level_list.append(`<div class="cell add-user-button"><button class="add-user expanded small button hollow" data-level="admin2" data-location="${_.escape(details.admin2_grid_id)}">add user to ${_.escape(details.admin2_name)}</button></div>`)
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
                                        <img src="${_.escape(obj.theme_uri)}/dt-assets/images/chevron_down.svg" alt="chevron"/>
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
                          ${_.escape( item.name )}
                      </span>
                      ${ item.status_color ? `<span class="status-square" style="background-color: ${_.escape(item.status_color)};">&nbsp;</span>` : '' }
                      ${ item.update_needed ? `<span>
                        <img style="height: 12px;" src="${_.escape( obj.theme_uri )}/dt-assets/images/broken.svg"/>
                        <span style="font-size: 14px">${_.escape(item.update_needed)}</span>
                      </span>` : '' }
                    </div>`
                  },
                  dynamic: true,
                  hint: true,
                  emptyTemplate: _.escape(window.wpApiShare.translations.no_records_found),
                  callback: {
                    onClick: function(node, a, item){
                      console.log(item)
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
                          console.log(response)

                          makeRequest( "POST", `get_user_list`, null , 'user-management/v1/')
                            .done(user_list=>{
                              window.user_list = user_list

                              if ( selected_location in window.user_list ) {
                                jQuery('#'+list_level+'_count').html(user_list[selected_location].length)
                              }
                            }).catch((e)=>{
                            console.log( 'error in get_user_list')
                            console.log( e)
                          })

                          makeRequest( "POST", `grid_totals`, { status: window.current_status }, 'user-management/v1/')
                            .done(grid_data=>{
                              window.previous_grid_id = 0
                              clear_layers()
                              window.grid_data = grid_data

                              let lnglat = map.getCenter()
                              load_layer( lnglat.lng, lnglat.lat )

                            }).catch((e)=>{
                            console.log('error getting grid_totals')
                            console.log(e)
                          })

                          // remove user add input
                          jQuery('#add-user-wrapper').remove()

                          // add new user to list
                          let grid_meta = ''
                          console.log(response.user_location.location_grid_meta)
                          jQuery.each(response.user_location.location_grid_meta, function(i,v) {
                            if ( v.grid_id.toString() === selected_location.toString() ) {

                              jQuery('#'+list_level+'_list').prepend(`
                              <div class="cell small-10 align-self-middle" data-id="${_.escape(v.grid_meta_id)}">
                                <a  href="/user-management/users/${_.escape(response.user_id)}">
                                  ${_.escape(response.user_title)}
                                </a>
                              </div>
                              <div class="cell small-2" data-id="${_.escape(grid_meta)}">
                                <a class="button clear delete-button mapbox-delete-button small float-right" data-user_id="${_.escape(response.user_id)}" data-id="${_.escape(v.grid_meta_id)}">
                                  <img src="${_.escape(obj.theme_uri)}/dt-assets/images/invalid.svg" alt="delete">
                                </a>
                              </div>`)
                            }
                          })

                          delete_user_action()


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

              delete_user_action()

            }); // end geocode
        }

       function delete_user_action() {
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
               // console.log( response )

               jQuery('div[data-id=' + e.currentTarget.dataset.id + ']').remove()

               makeRequest( "POST", `get_user_list`, null , 'user-management/v1/')
                 .done(user_list=>{
                   window.user_list = user_list

                   if ( selected_location in window.user_list ) {
                     console.log('here')
                     jQuery('#'+list_level+'_count').html(user_list[selected_location].length)
                   }
                 }).catch((e)=>{
                 console.log( 'error in get_user_list')
                 console.log( e)
               })

               makeRequest( "POST", `grid_totals`, { status: window.current_status }, 'user-management/v1/')
                 .done(grid_data=>{
                   window.previous_grid_id = 0
                   clear_layers()
                   window.grid_data = grid_data

                   let lnglat = map.getCenter()
                   load_layer( lnglat.lng, lnglat.lat )

                 }).catch((e)=>{
                 console.log('error getting grid_totals')
                 console.log(e)
               })


             }).catch(err => { console.error(err) })

         });
       }
        function get_level( ) {
          let level = jQuery('#level').val()
          if ( level === 'auto' || level === 'none' ) { // if none, then auto set
            level = 'admin0'
            if ( map.getZoom() <= 3 ) {
              level = 'world'
            }
            else if ( map.getZoom() >= 5 ) {
              level = 'admin1'
            }
          }
          return level;
        }
        function set_level( auto = false) {
          if ( auto ) {
            jQuery('#level :selected').attr('selected', false)
            jQuery('#level').val('auto')
          } else {
            jQuery('#level :selected').attr('selected', false)
            jQuery('#level').val(get_level())
          }
        }
        function remove_layer( grid_id, event_type ) {
          window.previous_grid_list.push( grid_id )
          window.previous_grid_id = grid_id

          if ( event_type === 'click' && window.click_behavior === 'add' ) {
            window.click_add_list.push( grid_id )
          }
          else {
            clear_layers ( grid_id )
          }
        }
        function clear_layers ( grid_id = null ) {
          jQuery.each(window.previous_grid_list, function(i,v) {
            let mapLayer = map.getLayer(v.toString());
            if(typeof mapLayer !== 'undefined' && v !== grid_id) {
              map.removeLayer( v.toString() )
              map.removeLayer( v.toString() + 'line' )
              map.removeSource( v.toString() )
            }
          })
        }
        function set_info_boxes() {
          let map_wrapper = jQuery('#map-wrapper')
          jQuery('.legend').css( 'width', map_wrapper.innerWidth() - 20 )
          jQuery( window ).resize(function() {
            jQuery('.legend').css( 'width', map_wrapper.innerWidth() - 20 )
          });
          // jQuery('#geocode-details').css('height', map_wrapper.innerHeight() - 125 )
        }
        function close_geocode_details() {
          jQuery('#geocode-details').hide()
        }

        jQuery('.close-details').on('click', function() {
          jQuery('#geocode-details').hide()
        })

      }).catch(err=>{
      console.log("error")
      console.log(err)
    })

  }
})

