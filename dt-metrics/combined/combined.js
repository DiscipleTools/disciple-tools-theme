window.mapbox_library_api.current_map_type = "points"
jQuery(document).ready(function($) {

  //over-ride points setup so we can load 2 layers.
  window.mapbox_library_api.points_map.setup = async function (){
    let contact_points = await makeRequest('POST', mapbox_library_api.obj.settings.points_rest_url, {
      post_type: "contacts",
      query: []
    }, mapbox_library_api.obj.settings.rest_base_url)
    let group_points = await makeRequest('POST', mapbox_library_api.obj.settings.points_rest_url, {
      post_type: "groups",
      query: []
    }, mapbox_library_api.obj.settings.rest_base_url)

    window.mapbox_library_api.area_map.setup = async function (){
      area_map.grid_data = await makeRequest( "POST", mapbox_library_api.obj.settings.totals_rest_url, { post_type: mapbox_library_api.obj.settings.post_type, query: mapbox_library_api.query_args || {}} , mapbox_library_api.obj.settings.rest_base_url )
      await area_map.load_layer()
      // load new layer on event
      mapbox_library_api.map.on('zoomend', function() {
        area_map.load_layer()
      })
      mapbox_library_api.map.on('dragend', function() {
        area_map.load_layer()
      })
      mapbox_library_api.map.on('click', function( e ) {
        // this section increments up the result on level because
        // it corresponds better to the viewable user intent for details
        let level = mapbox_library_api.get_level()
        area_map.load_detail_panel( e.lngLat.lng, e.lngLat.lat, level )
      })
    }
    window.mapbox_library_api.area_map.load_detail_panel = function (){};
    window.mapbox_library_api.area_map.behind_layer = 'dt-maps-groups_points_layer'
    window.mapbox_library_api.points_map.load_layer( group_points, "groups_points_layer",'#cc4b37', 10 )
    window.mapbox_library_api.points_map.load_layer( contact_points, "contacts_points_layer", '#11b4da' )
    await window.mapbox_library_api.area_map.setup()
    await window.mapbox_library_api.area_map.load_layer()

  }

  let split_by_html = `
    <div id="legend-icons" class="border-left">
      <span style="vertical-align: middle">
        <img
         style="filter: invert(41%) sepia(53%) saturate(2108%) hue-rotate(338deg) brightness(83%) contrast(91%);"
         src="${window.lodash.escape(window.wpApiShare.template_dir)}/dt-assets/images/dot.svg">
         ${window.lodash.escape(window.dt_metrics_mapbox_caller_js.translations.groups)}
      </span>
      <span style="vertical-align: middle">
        <img
           style="filter: invert(74%) sepia(59%) saturate(6105%) hue-rotate(154deg) brightness(101%) contrast(87%);"
           src="${window.lodash.escape(window.wpApiShare.template_dir)}/dt-assets/images/dot.svg">
           ${window.lodash.escape(window.dt_metrics_mapbox_caller_js.translations.contacts)}
      </span>

      <span style="vertical-align: middle">
        <span style="height:30px;width:30px;border:1px solid;background-color:rgb(155, 200, 254);display: inline-block;vertical-align: middle"></span>
        ${window.lodash.escape(window.dt_metrics_mapbox_caller_js.translations.active_users)}
       </span>
    </div>
  `
  $('#legend-bar').append(split_by_html)
  $('#map-type').hide()

})
