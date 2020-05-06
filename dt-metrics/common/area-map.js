jQuery(document).ready(function() {

  console.log(dt_mapbox_metrics)

  function write_area() {
    let obj = dt_mapbox_metrics

    window.post_type = obj.settings.post_type
    let title = obj.settings.title
    let status = obj.settings.status_list

    jQuery('#metrics-sidemenu').foundation('down', jQuery(`#${obj.settings.menu_slug}-menu`));

    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner users-spinner active"></span> '

    chart.empty().html(spinner)

    /* build status list */
    let status_list = `<option value="none" disabled></option>
                      <option value="none" disabled>Status</option>
                      <option value="none"></option>
                      <option value="all" selected>Status - All</option>
                      <option value="none" disabled>-----</option>
                      `
    jQuery.each(status, function(i,v){
      status_list += `<option value="${i}">${v.label}</option>`
    })
    status_list += `<option value="none"></option>`

    makeRequest( "POST", obj.settings.list_rest_url, { post_type: window.post_type, status: null} , obj.settings.list_rest_base_url )
      .done(response=>{
        window.totals_list = response
        console.log('LIST')
        console.log(response)
      }).catch((e)=>{
      console.log( 'error in activity')
      console.log( e)
    })

    makeRequest( "POST", obj.settings.totals_rest_url, { post_type: window.post_type, status: null} , obj.settings.totals_rest_base_url )
      .done(grid_data=>{
        window.grid_data = grid_data
        console.log('GRID TOTALS')
        console.log(grid_data)

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
                                ${title}
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
                                    ${status_list}
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
                        ${title}<span class="close-details" style="float:right;"><i class="fi-x"></i></span>
                        <hr style="margin:10px 5px;">
                        <div id="geocode-details-content"></div>
                    </div>
                </div>
             `)

        // set info box
        set_info_boxes()

        // init map
        mapboxgl.accessToken = obj.settings.map_key;
        var map = new mapboxgl.Map({
          container: 'map',
          style: 'mapbox://styles/mapbox/light-v10',
          center: [-98, 38.88],
          minZoom: 1,
          zoom: 1.8
        });

        // disable map rotation using right click + drag
        map.dragRotate.disable();

        // disable map rotation using touch rotation gesture
        map.touchZoomRotate.disableRotation();

        // cross-hair
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

        // grid memory vars
        window.previous_grid_id = 0
        window.previous_grid_list = []

        // default load state
        map.on('load', function() {

          window.previous_grid_id = '1'
          window.previous_grid_list.push('1')
          jQuery.get( obj.settings.map_mirror + 'collection/1.geojson', null, null, 'json')
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

          makeRequest( "POST", obj.settings.totals_rest_url, { post_type: window.post_type, status: window.current_status} , obj.settings.totals_rest_base_url )
            .done(grid_data=>{
              window.previous_grid_id = 0
              clear_layers()
              window.grid_data = grid_data
              close_geocode_details()

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
          makeRequest('GET', obj.settings.geocoder_url + 'dt-mapping/location-grid-list-api.php?type=geocode&longitude='+lng+'&latitude='+lat+'&level='+level+'&nonce='+obj.settings.geocoder_nonce )
            .done(data=>{


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
                  jQuery.get( obj.settings.map_mirror + 'collection/' + data.grid_id + '.geojson', null, null, 'json')
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
          content.empty().html( spinner )

          jQuery('#geocode-details').show()

          // geocode
          makeRequest('GET', obj.settings.geocoder_url + 'dt-mapping/location-grid-list-api.php?type=geocode&longitude='+lng+'&latitude='+lat+'&level='+level+'&nonce='+obj.settings.geocoder_nonce )
            .done(details=>{
              console.log('Details')
              console.log(details)

              /* hierarchy list*/
              content.empty().append(`<ul id="hierarchy-list" class="accordion" data-accordion></ul>`)
              let list = jQuery('#hierarchy-list')
              if ( details.admin0_grid_id ) {
                list.append( `
                              <li id="admin0_wrapper" class="accordion-item" data-accordion-item>
                               <a href="#" class="accordion-title">${details.admin0_name} :  <span id="admin0_count">0</span></a>
                                <div class="accordion-content grid-x" data-tab-content><div id="admin0_list" class="grid-x"></div></div>
                              </li>
                            `)
                if ( details.admin0_grid_id in window.grid_data ) {
                  jQuery('#admin0_count').html(window.grid_data[details.admin0_grid_id].count)
                }

              }
              if ( details.admin1_grid_id ) {
                list.append( `
                              <li id="admin1_wrapper" class="accordion-item" data-accordion-item >
                                <a href="#" class="accordion-title">${details.admin1_name} : <span id="admin1_count">0</span></a>
                                <div class="accordion-content" data-tab-content><div id="admin1_list" class="grid-x"></div></div>
                              </li>
                            `)

                if ( details.admin1_grid_id in window.grid_data ) {
                  jQuery('#admin1_count').html(window.grid_data[details.admin1_grid_id].count)
                }

              }
              if ( details.admin2_grid_id ) {
                list.append( `
                              <li id="admin2_wrapper" class="accordion-item" data-accordion-item>
                                <a href="#" class="accordion-title">${details.admin2_name} : <span id="admin2_count">0</span></a>
                                <div class="accordion-content" data-tab-content><div id="admin2_list" class="grid-x"></div></div>
                              </li>
                            `)

                if ( details.admin2_grid_id in window.grid_data ) {
                  jQuery('#admin2_count').html(window.grid_data[details.admin2_grid_id].count)
                }
              }

              jQuery('.accordion-item').last().addClass('is-active')
              list.foundation()
              /* end hierarchy list */


              if ( details.admin2_grid_id !== null ) {
                jQuery('#admin2_list').html( spinner )
                makeRequest( "POST", obj.settings.list_by_grid_rest_url, { grid_id: details.admin2_grid_id, status: window.current_status } , obj.settings.list_by_grid_rest_base_url )
                  .done(list_by_grid=>{
                    if ( list_by_grid.length > 0 ) {
                      write_list( 'admin2_list', list_by_grid )
                    } else {
                      jQuery('#admin2_list').html( '' )
                    }
                  })
              } else if ( details.admin1_grid_id !== null ) {
                jQuery('#admin1_list').html( spinner )
                makeRequest( "POST", obj.settings.list_by_grid_rest_url, { grid_id: details.admin1_grid_id, status: window.current_status } , obj.settings.list_by_grid_rest_base_url )
                  .done(list_by_grid=>{
                    if ( list_by_grid.length > 0 ) {
                      write_list( 'admin1_list', list_by_grid )
                    } else {
                      jQuery('#admin1_list').html( '' )
                    }
                  })
              } else if ( details.admin0_grid_id !== null ) {
                jQuery('#admin0_list').html( spinner )
                makeRequest( "POST", obj.settings.list_by_grid_rest_url, { grid_id: details.admin0_grid_id, status: window.current_status } , obj.settings.list_by_grid_rest_base_url )
                  .done(list_by_grid=>{
                    if ( list_by_grid.length > 0 ) {
                      write_list( 'admin0_list', list_by_grid )
                    } else {
                      jQuery('#admin0_list').html( '' )
                    }
                  })
              }

              function write_list( level, list_by_grid ) {
                let level_list = jQuery('#'+level)
                level_list.empty()
                jQuery.each(list_by_grid, function(i,v) {
                  level_list.append(`<div class="cell"><a href="/${window.post_type}/${v.post_id}">${v.post_title }</a></div>`)
                })
              }


            }); // end geocode
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
          close_geocode_details()
        })

      }).catch(err=>{
      console.log("error")
      console.log(err)
    })

  }
  if ( typeof dt_mapbox_metrics.settings !== undefined ) {
    write_area()
  }

})
