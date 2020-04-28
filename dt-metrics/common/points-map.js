jQuery(document).ready(function($) {
  console.log(dt_mapbox_metrics)

  if('/metrics/contacts/mapbox_points_map' === window.location.pathname || '/metrics/contacts/mapbox_points_map/' === window.location.pathname) {
    write_points('contact_settings' )
  }
  if('/metrics/groups/mapbox_points_map' === window.location.pathname || '/metrics/groups/mapbox_points_map/' === window.location.pathname ) {
    write_points('group_settings' )
  }

  function write_points( settings ) {
    let obj = dt_mapbox_metrics

    let post_type = obj[settings].post_type
    let title = obj[settings].title
    let status = obj[settings].status_list

    jQuery('#metrics-sidemenu').foundation('down', jQuery(`#${post_type}-menu`));

    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner active"></span> '

    chart.empty().html(spinner)

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
                                    ${status}
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


    mapboxgl.accessToken = obj.map_key;
    var map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/light-v10',
      // style: 'mapbox://styles/mapbox/streets-v11',
      center: [-98, 38.88],
      minZoom: 1,
      zoom: 1
    });

    // disable map rotation using right click + drag
    map.dragRotate.disable();

    // disable map rotation using touch rotation gesture
    map.touchZoomRotate.disableRotation();

    // load sources
    map.on('load', function () {
      let spinner = jQuery('#spinner')
      spinner.show()

      makeRequest('POST', 'points_geojson', { post_type: post_type, status: null }, 'dt-metrics/mapbox/' )
        .then(points => {
          map.addSource('points', {
            'type': 'geojson',
            'data': points
          });
        })
      jQuery.get('https://storage.googleapis.com/location-grid-mirror/collection/1.geojson', null, null, 'json')
        .done(function (geojson) {
          window.world_geojson = geojson
          makeRequest( "POST", `get_grid_list`, { post_type: post_type, status: null }, 'dt-metrics/mapbox/' )
            .done(response=>{
              window.grid_list = response
              console.log('LIST')
              console.log(response)

              jQuery.each(geojson.features, function (i, v) {
                if (window.grid_list[window.world_geojson.features[i].properties.id]) {
                  window.world_geojson.features[i].properties.value = parseInt(window.grid_list[geojson.features[i].properties.id].count)
                } else {
                  window.world_geojson.features[i].properties.value = 0
                }
              })

              map.addSource('world', {
                'type': 'geojson',
                'data': window.world_geojson
              });
              map.addLayer({
                'id': 'world',
                'type': 'fill',
                'source': 'world',
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
                    '#22346a',
                    400000,
                    '#22346a'
                  ],
                  'fill-opacity': 0.75
                }
              });

              spinner.hide()

            }).catch((e)=>{
            console.log( 'error in activity')
            console.log( e)
          })
        })
    })

    // cross-hair
    map.on('zoomstart', function () {
      jQuery('#cross-hair').show()
    })
    map.on('zoomend', function () {
      jQuery('#cross-hair').hide()
    })
    map.on('dragstart', function () {
      jQuery('#cross-hair').show()
    })
    map.on('dragend', function () {
      jQuery('#cross-hair').hide()
    })

    window.previous_grid_id = '0'

    function load_world() {
      // remove previous layer
      if (window.previous_grid_id > '0' && window.previous_grid_id !== '1') {
        map.removeLayer(window.previous_grid_id.toString() + 'line')
        map.removeLayer(window.previous_grid_id.toString() + 'points')
        map.removeSource(window.previous_grid_id.toString())
      }
      window.previous_grid_id = '0'

      map.setLayoutProperty('world', 'visibility', 'visible');
    }


    // load layer events
    // zoom
    map.on('zoomend', function () {
      let lnglat = map.getCenter()
      if (map.getZoom() <= 2) {
        load_world()
      } else {
        load_layer(lnglat.lng, lnglat.lat)
      }
    })
    // drag pan
    map.on('dragend', function () {
      let lnglat = map.getCenter()
      if (map.getZoom() <= 2) {
        load_world()
      } else {
        load_layer(lnglat.lng, lnglat.lat)
      }
    })

    function load_layer(lng, lat) {
      let spinner = jQuery('#spinner')
      spinner.show()

      map.setLayoutProperty('world', 'visibility', 'none');

      // set geocode level
      let level = 'admin0'

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
        }, null, 'json').done(function (data) {

        // default layer to world
        if (data.grid_id === undefined) {
          load_world()
        }

        // load layer, if new
        else if (window.previous_grid_id !== data.grid_id) {

          // remove previous layer
          if (window.previous_grid_id > 0 && map.getLayer(window.previous_grid_id.toString() + 'line')) {
            map.removeLayer(window.previous_grid_id.toString() + 'line')
            map.removeSource(window.previous_grid_id.toString())
            var mapPointsLayer = map.getLayer(window.previous_grid_id.toString() + 'points');
            if (typeof mapPointsLayer !== 'undefined') {
              map.removeLayer(window.previous_grid_id.toString() + 'points')
            }
          }
          window.previous_grid_id = data.grid_id

          // add layer
          var mapLayer = map.getLayer(data.grid_id);
          if (typeof mapLayer === 'undefined') {

            // get geojson collection
            jQuery.get('https://storage.googleapis.com/location-grid-mirror/low/' + data.grid_id + '.geojson', null, null, 'json')
              .done(function (geojson) {

                // add source
                map.addSource(data.grid_id.toString(), {
                  'type': 'geojson',
                  'data': geojson
                });
                // add border lines
                map.addLayer({
                  'id': data.grid_id.toString() + 'line',
                  'type': 'line',
                  'source': data.grid_id.toString(),
                  'paint': {
                    'line-color': '#22346a',
                    'line-width': 2
                  }
                });
                map.addLayer({
                  id: data.grid_id.toString() + 'points',
                  type: 'circle',
                  source: 'points',
                  paint: {
                    'circle-color': '#11b4da',
                    'circle-radius': 12,
                    'circle-stroke-width': 1,
                    'circle-stroke-color': '#fff'
                  },
                  filter: ["==", data.grid_id.toString(), ["get", "a0"]]
                });
                map.on('click', data.grid_id.toString() + 'points', function (e) {
                  console.log(e.features)
                  let dataDiv = jQuery('#data')
                  dataDiv.empty()

                  jQuery.each(e.features, function (i, v) {
                    var address = v.properties.l;
                    var post_id = v.properties.pid;
                    var name = v.properties.n

                    dataDiv.append(`<p><a href="/trainings/${post_id}">${name}</a><br>${address}</p>`)
                  })

                });
                map.on('mouseenter', data.grid_id.toString() + 'points', function () {
                  map.getCanvas().style.cursor = 'pointer';
                });
                map.on('mouseleave', data.grid_id.toString() + 'points', function () {
                  map.getCanvas().style.cursor = '';
                });
              }) // end get geojson collection
          } // end add layer
        } // end load new layer
        spinner.hide()
      }); // end geocode
    } // end load section function



  }

})
