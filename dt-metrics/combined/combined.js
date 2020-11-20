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
    window.mapbox_library_api.points_map.load_layer( contact_points, "contacts_points_layer" )
  }

})
