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

    window.mapbox_library_api.points_map.load_layer( group_points, "groups_points_layer",'#cc4b37', 10 )
    window.mapbox_library_api.points_map.load_layer( contact_points, "contacts_points_layer", '#11b4da' )
  }

  let split_by_html = `
    <div id="legend-icons" class="border-left">
      <span style="vertical-align: middle">
        <img
         style="filter: invert(41%) sepia(53%) saturate(2108%) hue-rotate(338deg) brightness(83%) contrast(91%);"
         src="${_.escape(window.wpApiShare.template_dir)}/dt-assets/images/dot.svg">
         ${_.escape(window.dt_metrics_mapbox_caller_js.translations.groups)}
       </span>
       <span style="vertical-align: middle">
       <img
         style="filter: invert(74%) sepia(59%) saturate(6105%) hue-rotate(154deg) brightness(101%) contrast(87%);"
         src="${_.escape(window.wpApiShare.template_dir)}/dt-assets/images/dot.svg">
         ${_.escape(window.dt_metrics_mapbox_caller_js.translations.contacts)}
       </span>
    </div>
  `
  $('#legend-bar').append(split_by_html)
  $('#map-type').hide()

})
