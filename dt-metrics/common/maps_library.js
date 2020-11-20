let spinner_html = '<span class="loading-spinner users-spinner active"></span>'
let mapbox_library_api = {
  container_set_up: false,
  current_map_type: 'area',
  obj: window.dt_mapbox_metrics,
  post_type: window.dt_mapbox_metrics.settings.post_type,
  title: window.dt_mapbox_metrics.settings.title,
  map: null,
  spinner: null,
  setup_container: function (){
    if ( this.container_set_up ){ return; }
    if ( typeof window.dt_mapbox_metrics.settings === undefined ) { return; }

    let chart = jQuery('#chart')

    chart.empty().html(spinner_html)

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
                ${_.escape( this.title )}
            </div>
            <div id="map-type" class="small button-group border-left" style="padding: 0 10px 0 10px; margin-bottom: 0">
              <a class="button ${mapbox_library_api.current_map_type === 'cluster' ? '': 'hollow' }"
                id="cluster">
                Cluster
              </a>
              <a class="button ${mapbox_library_api.current_map_type === 'points' ? '': 'hollow' }"
                id="points">
                Points
              </a>
              <a class="button ${mapbox_library_api.current_map_type === 'area' ? '': 'hollow' }"
                id="area">
                Area
              </a>
            </div>

          </div>
        </div>
        <div id="spinner">${spinner_html}</div>
        <div id="geocode-details" class="geocode-details">
          ${_.escape( this.title )}<span class="close-details" style="float:right;"><i class="fi-x"></i></span>
          <hr style="margin:10px 5px;">
          <div id="geocode-details-content"></div>
        </div>
      </div>
    `)
    this.spinner = $("#spinner")

    //set_info_boxes
    let map_wrapper = jQuery('#map-wrapper')
    jQuery('.legend').css( 'width', map_wrapper.innerWidth() - 20 )
    jQuery( window ).resize(function() {
      jQuery('.legend').css( 'width', map_wrapper.innerWidth() - 20 )
    });


    // init map
    window.mapboxgl.accessToken = this.obj.settings.map_key;
    mapbox_library_api.map = new window.mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/light-v10',
      center: [2, 46],
      minZoom: 1,
      zoom: 1.8
    });
    // SET BOUNDS
    map_bounds_token = this.obj.settings.post_type + this.obj.settings.menu_slug
    map_start = get_map_start( map_bounds_token )
    if ( map_start ) {
      mapbox_library_api.map.fitBounds( map_start, {duration: 0});
    }
    mapbox_library_api.map.on('zoomend', function() {
      set_map_start( map_bounds_token, mapbox_library_api.map.getBounds() )
    })
    mapbox_library_api.map.on('dragend', function() {
      set_map_start( map_bounds_token, mapbox_library_api.map.getBounds() )
    })
    // end set bounds
    // disable map rotation using right click + drag
    mapbox_library_api.map.dragRotate.disable();

    // disable map rotation using touch rotation gesture
    mapbox_library_api.map.touchZoomRotate.disableRotation();

    $('#map-type a').on('click', function (e){
      $('#map-type a').addClass("hollow")
      $(this).removeClass("hollow")
      mapbox_library_api.current_map_type = $(this).attr('id');
      mapbox_library_api.load_map(mapbox_library_api.current_map_type)
    })
    mapbox_library_api.map.on('load', function() {
      mapbox_library_api.load_map(mapbox_library_api.current_map_type)
    });
  },
  load_map: function (map_type){
    let style = mapbox_library_api.map.getStyle()
    style.layers.forEach( layer=>{
      if ( layer.id.startsWith("dt-maps-")){
        mapbox_library_api.map.removeLayer( layer.id )
      }
    } )
    mapbox_library_api.spinner.show()

    if ( mapbox_library_api.current_map_type === "cluster" ){
      mapbox_library_api.cluster_map.default_setup()
    } else if ( mapbox_library_api.current_map_type === "area" ){
      mapbox_library_api.area_map.setup()
    } else {
      mapbox_library_api.points_map.setup()
    }
  },
  get_level: function (){
    let level = 'world'
    if ( mapbox_library_api.map.getZoom() >= 4 ) {
      level = 'admin1'
    } if ( mapbox_library_api.map.getZoom() >= 6 ){
      level = 'admin2'
    }
    return level;
  },

  points_map: {
    setup: async function () {
      let points = await makeRequest('POST', mapbox_library_api.obj.settings.points_rest_url, {
        post_type: mapbox_library_api.post_type,
      }, mapbox_library_api.obj.settings.rest_base_url)
      this.load_layer(points)
    },
    load_layer: function ( points, layer_key = 'pointsLayer', color = '#11b4da', size = 6 ) {
      layer_key = 'dt-maps-' + layer_key
      let mapLayer = mapbox_library_api.map.getLayer(layer_key);
      if (typeof mapLayer!=='undefined') {
        mapbox_library_api.map.off('click', layer_key, mapbox_library_api.points_map.on_click);
        mapbox_library_api.map.removeLayer(layer_key)
      }
      let mapSource = mapbox_library_api.map.getSource(`${layer_key}_pointsSource`);
      if (typeof mapSource=='undefined') {
        // mapbox_library_api.map.removeSource(`${layer_key}_pointsSource`)
        mapbox_library_api.map.addSource(`${layer_key}_pointsSource`, {
          'type': 'geojson',
          'data': points
        });
      }

      mapbox_library_api.map.addLayer({
        id: layer_key,
        type: 'circle',
        source: `${layer_key}_pointsSource`,
        paint: {
          'circle-color': color,
          'circle-radius': size,
          'circle-stroke-width': 0.5,
          'circle-stroke-color': '#fff'
        }
      });

      mapbox_library_api.map.on('click', layer_key, mapbox_library_api.points_map.on_click);

      mapbox_library_api.map.on('mouseenter', layer_key, function () {
        mapbox_library_api.map.getCanvas().style.cursor = 'pointer';
      });
      mapbox_library_api.map.on('mouseleave', layer_key, function () {
        mapbox_library_api.map.getCanvas().style.cursor = '';
      });

      mapbox_library_api.spinner.hide()
    },
    on_click: function (e) {
      let list = []
      jQuery('#geocode-details').show()

      let content = jQuery('#geocode-details-content')
      content.empty().html( mapbox_library_api.spinner )

      jQuery.each(e.features, function(i,v) {
        if ( i > 20 ){ return }
        let post_id = e.features[i].properties.post_id;
        let post_type = e.features[i].properties.post_type
        content.append(`<div class="grid-x" id="list-${_.escape( i )}"></div>`)
        makeRequest('GET', _.escape( post_type ) +'/'+_.escape( post_id )+'/', null, 'dt-posts/v2/' )
        .done(details=>{
          list[i] = jQuery('#list-'+i)

          list[i].append(`
            <div class="cell"><a href="${_.escape(window.wpApiShare.site_url)}/${_.escape( post_type )}/${_.escape( details.ID )}">${_.escape( details.title )/*View Record*/}</a></div>
          `)

          jQuery('.loading-spinner').hide()
        })
      })
    }
  }

}

function standardize_longitude(lng){
  if (lng > 180) {
    lng = lng - 180
    lng = -Math.abs(lng)
  } else if (lng < -180) {
    lng = lng + 180
    lng = Math.abs(lng)
  }
  return lng;
}
jQuery('.close-details').on('click', function() {
  jQuery('#geocode-details').hide()
})



let cluster_map = {
  default_setup: async function (){
    let data = await makeRequest( "POST", mapbox_library_api.obj.settings.rest_url, { post_type: mapbox_library_api.post_type} , mapbox_library_api.obj.settings.rest_base_url )
    this.load_layer( data )
  },
  load_layer: function ( geojson ) {

    let mapSource = mapbox_library_api.map.getSource(`clusterSource`);
    if (typeof mapSource==='undefined') {
      mapbox_library_api.map.addSource('clusterSource', {
        type: 'geojson',
        data: geojson,
        cluster: true,
        clusterMaxZoom: 14,
        clusterRadius: 50
      });

    }
    mapbox_library_api.map.addLayer({
      id: 'dt-maps-clusters',
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
    mapbox_library_api.map.addLayer({
      id: 'dt-maps-cluster-count',
      type: 'symbol',
      source: 'clusterSource',
      filter: ['has', 'point_count'],
      layout: {
        'text-field': '{point_count_abbreviated}',
        'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
        'text-size': 12
      }
    });
    mapbox_library_api.map.addLayer({
      id: 'dt-maps-unclustered-point',
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
    mapbox_library_api.map.on('click', 'clusters', function(e) {
      let features =mapbox_library_api.map.queryRenderedFeatures(e.point, {
        layers: ['clusters']
      });

      let clusterId = features[0].properties.cluster_id;
      mapbox_library_api.map.getSource('clusterSource').getClusterExpansionZoom(
        clusterId,
        function(err, zoom) {
          if (err) return;

          mapbox_library_api.map.easeTo({
            center: features[0].geometry.coordinates,
            zoom: zoom
          });
        }
      );
    })
    mapbox_library_api.map.on('click', 'unclustered-point', cluster_map.on_click );
    mapbox_library_api.map.on('mouseenter', 'clusters', function() {
      mapbox_library_api.map.getCanvas().style.cursor = 'pointer';
    });
    mapbox_library_api.map.on('mouseleave', 'clusters', function() {
      mapbox_library_api.map.getCanvas().style.cursor = '';
    });
    mapbox_library_api.spinner.hide()
  },
  on_click: function (e) {
    let list = []
    jQuery('#geocode-details').show()

    let content = jQuery('#geocode-details-content')
    content.empty().html(spinner_html)

    jQuery.each(e.features, function (i, v) {
      if ( i > 10 ){
        return;
      }
      content.append(`<div class="grid-x" id="list-${_.escape( i )}"></div>`)
      window.API.get_post( _.escape( mapbox_library_api.post_type), _.escape( e.features[i].properties.post_id ))
      .done(details => {
        list[i] = jQuery('#list-' + _.escape( i ))

        let status = ''
        if (mapbox_library_api.post_type === 'contacts') {
          status = details.overall_status.label
        } else if (mapbox_library_api.post_type === 'groups') {
          status = details.group_status.label
        } else if ( typeof details.status.label !== "undefined") {
          status = details.status.label
        }

        list[i].append(`
            <div class="cell"><h4>${_.escape( details.title )}</h4></div>
            <div class="cell">${_.escape( mapbox_library_api.obj.translations.status)/*Status*/}: ${_.escape( status )}</div>
            <div class="cell">${ _.escape( mapbox_library_api.obj.translations.assigned_to  )/*Assigned To*/}: ${_.escape( details.assigned_to.display )}</div>
            <div class="cell"><a target="_blank" href="${_.escape(window.wpApiShare.site_url)}/${_.escape( mapbox_library_api.post_type )}/${_.escape( details.ID )}">${_.escape( mapbox_library_api.obj.translations.view_record  )/*View Record*/}</a></div>
            <div class="cell"><hr></div>
        `)

        jQuery('.loading-spinner').hide()
      })
    })
  },
}

let area_map = {
  grid_data: null,
  previous_grid_list:[],
  setup: async function (){
    if ( !area_map.grid_data ){
      area_map.grid_data = await makeRequest( "POST", mapbox_library_api.obj.settings.totals_rest_url, { post_type: mapbox_library_api.obj.settings.post_type} , mapbox_library_api.obj.settings.rest_base_url )
    }
    await area_map.load_layer()
    // load new layer on event
    mapbox_library_api.map.on('zoomend', function() {
      if ( mapbox_library_api.current_map_type !== 'area'){return;}
      area_map.load_layer()
    })
    mapbox_library_api.map.on('dragend', function() {
      if ( mapbox_library_api.current_map_type !== 'area'){return;}
      area_map.load_layer()
    })
    mapbox_library_api.map.on('click', function( e ) {
      if ( mapbox_library_api.current_map_type !== 'area'){return;}
      // this section increments up the result on level because
      // it corresponds better to the viewable user intent for details
      let level = mapbox_library_api.get_level()
      area_map.load_detail_panel( e.lngLat.lng, e.lngLat.lat, level )
    })
  },
  load_layer: async function (){
    mapbox_library_api.spinner.show()
    // set geocode level, default to auto
    let level = mapbox_library_api.get_level()

    let bbox =mapbox_library_api.map.getBounds()

    let data = [{ grid_id:'1', parent_id:'1'}]
    if ( level !== "world" ){
      data = await makeRequest('GET', `${mapbox_library_api.obj.settings.geocoder_url}dt-mapping/location-grid-list-api.php`,
        {
          type: 'match_within_bbox',
          north_latitude: bbox._ne.lat,
          south_latitude: bbox._sw.lat,
          west_longitude: standardize_longitude(bbox._sw.lng),
          east_longitude: standardize_longitude(bbox._ne.lng),
          level: level,
          nonce: mapbox_library_api.obj.settings.geocoder_nonce
        }
      )
    }

    // default layer to world
    if ( level === 'world' ) {
      data = [{ grid_id:'1', parent_id:'1'}]
    }

    let status404 = window.SHAREDFUNCTIONS.get_json_cookie('geojson_failed', [] )

    let loaded_ids = [];
    data.forEach( res=>{
      let grid_id = res.grid_id
      let parent_id = res.parent_id
      let layer_id = 'dt-maps-' + parent_id.toString()
      // is new test
      if ( !_.find(area_map.previous_grid_list, {parent_id:parent_id}) && !status404.includes(parent_id) && !loaded_ids.includes(parent_id) ) {
        loaded_ids.push(parent_id)

        // is defined test
        let mapLayer = mapbox_library_api.map.getLayer(layer_id);
        if(typeof mapLayer === 'undefined') {

          // get geojson collection
          jQuery.get( mapbox_library_api.obj.settings.map_mirror + 'collection/' + parent_id + '.geojson', null, null, 'json')
          .done(function (geojson) {
            // add data to geojson properties
            let highest_value = 1
            jQuery.each(geojson.features, function (i, v) {
              if (area_map.grid_data[geojson.features[i].properties.id]) {
                geojson.features[i].properties.value = parseInt(area_map.grid_data[geojson.features[i].properties.id].count)
              } else {
                geojson.features[i].properties.value = 0
              }
              highest_value = Math.max(highest_value,  geojson.features[i].properties.value)
            })

            // add source
            let mapSource = mapbox_library_api.map.getSource(layer_id);
            if (typeof mapSource==='undefined') {
              mapbox_library_api.map.addSource(layer_id, {
                'type': 'geojson',
                'data': geojson
              });
            }

            // add fill layer
            let mapLayer = mapbox_library_api.map.getLayer(layer_id);
            if ( mapLayer === undefined ){
              mapbox_library_api.map.addLayer({
                'id': layer_id,
                'type': 'fill',
                'source': layer_id,
                'paint': {
                  'fill-color': {
                    property: 'value',
                    stops: [[0, 'rgba(0, 0, 0, 0)'], [1, 'rgb(155, 200, 254)'], [highest_value, 'rgb(37, 82, 154)']]
                  },
                  'fill-opacity': 0.75,
                  'fill-outline-color': '#707070',
                }
              });
            }
          }).catch(()=>{
            status404.push(parent_id)
            window.SHAREDFUNCTIONS.save_json_cookie( 'geojson_failed', status404, 'metrics' )
          })// end get geojson collection
        }
      } // end load new layer
    })
    area_map.previous_grid_list.forEach(grid_item=>{
      let layer_id = 'dt-maps-' + grid_item.parent_id
      let mapLayer =mapbox_library_api.map.getLayer(layer_id);
      if(typeof mapLayer !== 'undefined' && !_.find(data, {parent_id:grid_item.parent_id})) {
        mapbox_library_api.map.removeLayer( layer_id )
        mapbox_library_api.map.removeSource( layer_id )
      }
    })
    area_map.previous_grid_list = data
    mapbox_library_api.spinner.hide()
  },
  load_detail_panel: function (lng, lat, level){
    lng = standardize_longitude( lng )
    if ( level === 'world' ) {
      level = 'admin0'
    }

    let content = jQuery('#geocode-details-content')
    content.empty().html( spinner_html )

    jQuery('#geocode-details').show()

    // geocode
    makeRequest('GET', mapbox_library_api.obj.settings.geocoder_url + 'dt-mapping/location-grid-list-api.php',
      {
        type:'geocode',
        longitude:lng,
        latitude:lat,
        level:level,
        nonce:mapbox_library_api.obj.settings.geocoder_nonce
      }).done(details=>{

      /* hierarchy list*/
      content.empty().append(`<ul id="hierarchy-list" class="accordion" data-accordion></ul>`)
      let list = jQuery('#hierarchy-list')
      if ( details.admin0_grid_id ) {
        list.append( `
          <li id="admin0_wrapper" class="accordion-item" data-accordion-item>
           <a href="#" class="accordion-title">${_.escape( details.admin0_name )} :  <span id="admin0_count">0</span></a>
            <div class="accordion-content grid-x" data-tab-content><div id="admin0_list" class="grid-x"></div></div>
          </li>
        `)
        if ( details.admin0_grid_id in area_map.grid_data ) {
          jQuery('#admin0_count').html(area_map.grid_data[details.admin0_grid_id].count)
        }

      }
      if ( details.admin1_grid_id ) {
        list.append( `
          <li id="admin1_wrapper" class="accordion-item" data-accordion-item >
            <a href="#" class="accordion-title">${_.escape( details.admin1_name )} : <span id="admin1_count">0</span></a>
            <div class="accordion-content" data-tab-content><div id="admin1_list" class="grid-x"></div></div>
          </li>
        `)

        if ( details.admin1_grid_id in area_map.grid_data ) {
          jQuery('#admin1_count').html(area_map.grid_data[details.admin1_grid_id].count)
        }

      }
      if ( details.admin2_grid_id ) {
        list.append( `
          <li id="admin2_wrapper" class="accordion-item" data-accordion-item>
            <a href="#" class="accordion-title">${_.escape( details.admin2_name )} : <span id="admin2_count">0</span></a>
            <div class="accordion-content" data-tab-content><div id="admin2_list" class="grid-x"></div></div>
          </li>
        `)

        if ( details.admin2_grid_id in area_map.grid_data ) {
          jQuery('#admin2_count').html(area_map.grid_data[details.admin2_grid_id].count)
        }
      }

      jQuery('.accordion-item').last().addClass('is-active')
      list.foundation()
      /* end hierarchy list */

      if ( details.admin2_grid_id !== null ) {
        jQuery('#admin2_list').html( spinner_html )
        makeRequest( "POST", mapbox_library_api.obj.settings.list_by_grid_rest_url, { grid_id: details.admin2_grid_id, post_type: mapbox_library_api.post_type } , mapbox_library_api.obj.settings.rest_base_url )
        .done(list_by_grid=>{
          if ( list_by_grid.length > 0 ) {
            write_list( 'admin2_list', list_by_grid )
          } else {
            jQuery('#admin2_list').html( '' )
          }
        })
      } else if ( details.admin1_grid_id !== null ) {
        jQuery('#admin1_list').html( spinner_html )
        makeRequest( "POST", mapbox_library_api.obj.settings.list_by_grid_rest_url, { grid_id: details.admin1_grid_id, post_type: mapbox_library_api.post_type } , mapbox_library_api.obj.settings.rest_base_url )
        .done(list_by_grid=>{
          if ( list_by_grid.length > 0 ) {
            write_list( 'admin1_list', list_by_grid )
          } else {
            jQuery('#admin1_list').html( '' )
          }
        })
      } else if ( details.admin0_grid_id !== null ) {
        jQuery('#admin0_list').html( spinner_html )
        makeRequest( "POST", mapbox_library_api.obj.settings.list_by_grid_rest_url, { grid_id: details.admin0_grid_id, post_type: mapbox_library_api.post_type } , mapbox_library_api.obj.settings.rest_base_url )
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
          if ( i > 20 ){ return }
          level_list.append(`<div class="cell"><a target="_blank" href="${_.escape(window.wpApiShare.site_url)}/${_.escape( mapbox_library_api.post_type )}/${_.escape( v.post_id )}">${_.escape( v.post_title ) }</a></div>`)
        })
        if ( list_by_grid.length > 20 ){
          level_list.append(`<div class="cell">...</div>`)
        }
      }
    });
  }
}

mapbox_library_api.cluster_map = cluster_map
mapbox_library_api.area_map = area_map
window.mapbox_library_api = mapbox_library_api;

window.mapbox_library_api.setup_container()

jQuery(document).ready(function($) {
  let obj = window.dt_mapbox_metrics
  jQuery('#metrics-sidemenu').foundation('down', jQuery(`#${obj.settings.menu_slug}-menu`));
})
