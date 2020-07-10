jQuery(document).ready(function($) {
  // console.log(dt_mapbox_metrics)
  if ( typeof dt_mapbox_metrics.settings !== undefined ) {
    write_cluster()
  }
  function write_cluster( ) {
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
                      <option value="none" disabled>${_.escape( obj.translations.status ) /*Status*/}</option>
                      <option value="none"></option>
                      <option value="all" selected>${_.escape( obj.translations.status_all  )/*Status - All*/}</option>
                      <option value="none" disabled>-----</option>
                      `
    jQuery.each(status, function(i,v){
      status_list += `<option value="${_.escape( i )}">${_.escape( v.label )}</option>`
    })
    status_list += `<option value="none"></option>`

    makeRequest( "POST", obj.settings.rest_url, { post_type: window.post_type, status: null} , obj.settings.rest_base_url )
      .then(data=>{
        // console.log(data)

        chart.empty().html(`
            <style>
                    #map-wrapper {
                        position: relative;
                        height: ${window.innerHeight - 100}px;
                        width:100%;
                    }
                    #map {
                        position: absolute;
                        top: 0;
                        left: 0;
                        z-index: 1;
                        width:100%;
                        height: ${window.innerHeight - 100}px;
                     }
                     #legend {
                        position: absolute;
                        top: 10px;
                        left: 10px;
                        z-index: 2;
                     }
                     #data {
                        word-wrap: break-word;
                     }
                    .legend {
                        background-color: #fff;
                        border-radius: 3px;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.10);
                        font: 12px/20px 'Roboto', Arial, sans-serif;
                        padding: 10px;
                        opacity: .9;
                    }
                    .legend h4 {
                        margin: 0 0 10px;
                    }
                    .legend div span {
                        border-radius: 50%;
                        display: inline-block;
                        height: 10px;
                        margin-right: 5px;
                        width: 10px;
                    }
                    #cross-hair {
                        position: absolute;
                        z-index: 20;
                        font-size:30px;
                        font-weight: normal;
                        top:50%;
                        left:50%;
                        display:none;
                        pointer-events: none;
                    }
                    #spinner {
                        position: absolute;
                        top:50%;
                        left:50%;
                        z-index: 20;
                        display:none;
                    }
                    .spinner-image {
                        width: 30px;
                    }
                    .info-bar-font {
                        font-size: 1.5em;
                        padding-top: 9px;
                    }
                    .border-left {
                        border-left: 1px lightgray solid;
                    }
                    #geocode-details {
                        position: absolute;
                        top: 100px;
                        right: 10px;
                        z-index: 2;
                    }
                    .geocode-details {
                        background-color: #fff;
                        border-radius: 3px;
                        box-shadow: 0 1px 2px rgba(0,0,0,0.10);
                        font: 12px/20px 'Roboto', Arial, sans-serif;
                        padding: 10px;
                        opacity: .9;
                        width: 300px;
                        display:none;
                    }
                    .close-details {
                        cursor:pointer;
                    }
                </style>
            <div id="map-wrapper">
                <div id='map'></div>
                <div id='legend' class='legend'>
                    <div class="grid-x grid-margin-x grid-padding-x">
                        <div class="cell small-2 center info-bar-font">
                            ${_.escape( title )}
                        </div>
                        <div class="cell small-2 center border-left">
                            <select id="status" class="small" style="width:170px;">
                                ${status_list}
                            </select>
                        </div>
                    </div>
                </div>
                <div id="spinner">${spinner}</div>
                <div id="cross-hair">&#8982</div>
                <div id="geocode-details" class="geocode-details">
                    ${_.escape( title )}<span class="close-details" style="float:right;"><i class="fi-x"></i></span>
                    <hr style="margin:10px 5px;">
                    <div id="geocode-details-content"></div>
                </div>
            </div>
            `)

        set_info_boxes()
        function set_info_boxes() {
          let map_wrapper = jQuery('#map-wrapper')
          jQuery('.legend').css( 'width', map_wrapper.innerWidth() - 20 )
          jQuery( window ).resize(function() {
            jQuery('.legend').css( 'width', map_wrapper.innerWidth() - 20 )
          });
        }

        mapboxgl.accessToken = obj.settings.map_key;
        var map = new mapboxgl.Map({
          container: 'map',
          style: 'mapbox://styles/mapbox/light-v10',
          center: [-98, 38.88],
          minZoom: 0,
          zoom: 0
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

        map.on('load', function() {
          load_layer( data )
        });

        jQuery('#status').on('change', function() {
          window.current_status = jQuery('#status').val()
          close_details()
          makeRequest( "POST", obj.settings.rest_url, { post_type: window.post_type, status: window.current_status} , obj.settings.rest_base_url )
            .then(data=> {
              clear_layer()
              load_layer( data )
            })
        })
        function close_details() {
          jQuery('#geocode-details').hide()
        }
        function clear_layer() {
          map.removeLayer( 'clusters' )
          map.removeLayer( 'cluster-count' )
          map.removeLayer( 'unclustered-point' )
          map.removeSource( 'clusterSource' )
        }

        function load_layer( geojson ) {
          map.addSource('clusterSource', {
            type: 'geojson',
            data: geojson,
            cluster: true,
            clusterMaxZoom: 14,
            clusterRadius: 50
          });
          map.addLayer({
            id: 'clusters',
            type: 'circle',
            source: 'clusterSource',
            filter: ['has', 'point_count'],
            paint: {
              'circle-color': [
                'step',
                ['get', 'point_count'],
                '#51bbd6',
                100,
                '#f1f075',
                750,
                '#f28cb1'
              ],
              'circle-radius': [
                'step',
                ['get', 'point_count'],
                20,
                100,
                30,
                750,
                40
              ]
            }
          });
          map.addLayer({
            id: 'cluster-count',
            type: 'symbol',
            source: 'clusterSource',
            filter: ['has', 'point_count'],
            layout: {
              'text-field': '{point_count_abbreviated}',
              'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
              'text-size': 12
            }
          });
          map.addLayer({
            id: 'unclustered-point',
            type: 'circle',
            source: 'clusterSource',
            filter: ['!', ['has', 'point_count']],
            paint: {
              'circle-color': '#11b4da',
              'circle-radius':12,
              'circle-stroke-width': 1,
              'circle-stroke-color': '#fff'
            }
          });
          map.on('click', 'clusters', function(e) {
            var features = map.queryRenderedFeatures(e.point, {
              layers: ['clusters']
            });

            var clusterId = features[0].properties.cluster_id;
            map.getSource('clusterSource').getClusterExpansionZoom(
              clusterId,
              function(err, zoom) {
                if (err) return;

                map.easeTo({
                  center: features[0].geometry.coordinates,
                  zoom: zoom
                });
              }
            );
          })
          map.on('click', 'unclustered-point', on_click );
          map.on('mouseenter', 'clusters', function() {
            map.getCanvas().style.cursor = 'pointer';
          });
          map.on('mouseleave', 'clusters', function() {
            map.getCanvas().style.cursor = '';
          });
        }
        function on_click(e) {
          window.list = []
          jQuery('#geocode-details').show()

          let content = jQuery('#geocode-details-content')
          content.empty().html(spinner)
          // console.log(e.features)

          jQuery.each(e.features, function (i, v) {
            content.append(`<div class="grid-x" id="list-${_.escape( i )}"></div>`)
            makeRequest('GET', _.escape( window.post_type ) + '/' + _.escape( e.features[i].properties.post_id ) + '/', null, 'dt-posts/v2/')
              .done(details => {
                window.list[i] = jQuery('#list-' + _.escape( i ))

                let status = ''
                if (window.post_type === 'contacts') {
                  status = details.overall_status.label
                } else if (window.post_type === 'groups') {
                  status = details.group_status.label
                } else if ( typeof details.status.label !== "undefined") {
                  status = details.status.label
                }

                window.list[i].append(`
                      <div class="cell"><h4>${_.escape( details.title )}</h4></div>
                      <div class="cell">${_.escape( obj.translations.status)/*Status*/}: ${_.escape( status )}</div>
                      <div class="cell">${ _.escape( obj.translations.assigned_to  )/*Assigned To*/}: ${_.escape( details.assigned_to.display )}</div>
                      <div class="cell"><a href="/${_.escape( window.post_type )}/${_.escape( details.ID )}">${_.escape( obj.translations.view_record  )/*View Record*/}</a></div>
                      <div class="cell"><hr></div>
                  `)

                jQuery('.loading-spinner').hide()
              })
          })
        }

        jQuery('.close-details').on('click', function() {
          close_details()
        })

      }).catch(err=>{
      console.log("error")
      console.log(err)
    })
  }
})
