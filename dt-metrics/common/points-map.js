jQuery(document).ready(function($) {
  console.log(dt_mapbox_metrics)

  function write_all_points( ) {
    let obj = dt_mapbox_metrics

    window.post_type = obj.settings.post_type
    let title = obj.settings.title
    let status = obj.settings.status_list

    jQuery('#metrics-sidemenu').foundation('down', jQuery(`#${obj.settings.menu_slug}-menu`));

    let chart = jQuery('#chart')
    window.spinner = ' <span class="loading-spinner active"></span> '

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
                                <select id="status" class="small" style="width:100%;">
                                    ${status_list}
                                </select>
                            </div>
                            <div class="cell small-2 center border-left">

                            </div>

                        </div>
                    </div>
                    <div id="spinner">${window.spinner}</div>
                    <div id="cross-hair">&#8982</div>
                    <div id="geocode-details" class="geocode-details">
                        ${title}<span class="close-details" style="float:right;"><i class="fi-x"></i></span>
                        <hr style="margin:10px 5px;">
                        <div id="geocode-details-content"></div>
                    </div>
                </div>
             `)


    mapboxgl.accessToken = obj.settings.map_key;
    var map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/light-v10',
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

      makeRequest('POST', obj.settings.points_rest_url, { post_type: window.post_type, status: null }, obj.settings.points_rest_base_url )
        .then(points => {
          load_layer( points )
        })
    })

    function load_layer( points ) {

      var mapLayer = map.getLayer('pointsLayer');
      if(typeof mapLayer !== 'undefined') {
        map.off('click', 'pointsLayer', on_click );
        map.removeLayer( 'pointsLayer' )
      }
      var mapSource= map.getSource('pointsSource');
      if(typeof mapSource !== 'undefined') {
        map.removeSource( 'pointsSource' )
      }

      map.addSource('pointsSource', {
        'type': 'geojson',
        'data': points
      });
      map.addLayer({
        id: 'pointsLayer',
        type: 'circle',
        source: 'pointsSource',
        paint: {
          'circle-color': '#11b4da',
          'circle-radius': 12,
          'circle-stroke-width': 1,
          'circle-stroke-color': '#fff'
        }

      });

      map.on('click', 'pointsLayer', on_click );

      map.on('mouseenter', 'pointsLayer', function () {
        map.getCanvas().style.cursor = 'pointer';
      });
      map.on('mouseleave', 'pointsLayer', function () {
        map.getCanvas().style.cursor = '';
      });

      let spinner = jQuery('#spinner')
      spinner.hide()
    }
    function on_click(e) {
      window.list = []
      jQuery('#geocode-details').show()

      let content = jQuery('#geocode-details-content')
      content.empty().html( window.spinner )
      console.log(e.features)

      jQuery.each(e.features, function(i,v) {
        content.append(`<div class="grid-x" id="list-${i}"></div>`)
        makeRequest('GET', window.post_type +'/'+e.features[i].properties.pid+'/', null, 'dt-posts/v2/' )
          .done(details=>{
            window.list[i] = jQuery('#list-'+i)

            let status = ''
            if ( window.post_type === 'contacts') {
              status = details.overall_status.label
            } else if ( window.post_type === 'groups' ) {
              status = details.group_status.label
            }

            window.list[i].append(`
                      <div class="cell"><h4>${details.title}</h4></div>
                      <div class="cell">Status: ${status}</div>
                      <div class="cell">Assigned To: ${details.assigned_to.display}</div>
                      <div class="cell"><a href="/${window.post_type}/${details.ID}">View Record</a></div>
                      <div class="cell"><hr></div>
                  `)

            jQuery('.loading-spinner').hide()
          })
      })
      jQuery('.close-details').on('click', function() {
        close_details()
      })
    }
    function close_details() {
      jQuery('#geocode-details').hide()
    }

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

    /* status change */
    jQuery('#status').on('change', function() {
      let spinner = jQuery('#spinner')
      spinner.show()
      close_details()
      window.current_status = jQuery('#status').val()
      makeRequest('POST', obj.settings.points_rest_url, { post_type: window.post_type, status: window.current_status }, obj.settings.points_rest_base_url )
        .then(points => {
          load_layer( points )
        })
    })

  }
  if ( typeof dt_mapbox_metrics.settings !== undefined ) {
    write_all_points()
  }
})
