window.mapbox_library_api.current_map_type = 'points';
jQuery(document).ready(function ($) {
  let recursive_load = async function (
    body,
    data = [],
    offset = 0,
    limit = 50000,
  ) {
    body.offset = offset;
    body.limit = limit;
    let geojson = await window.makeRequest(
      'POST',
      window.mapbox_library_api.obj.settings.points_rest_url,
      body,
      window.mapbox_library_api.obj.settings.rest_base_url,
    );

    data = data.concat(geojson.features);
    jQuery('#loading-legend').html(
      `<span>(${data.length.toLocaleString()})</span>`,
    );

    // return data;
    if (geojson.features.length > 0 && geojson.features.length === limit) {
      return recursive_load(body, data, offset + limit, limit);
    }
    jQuery('#loading-legend').html('');
    return data;
  };

  //over-ride points setup so we can load 2 layers.
  window.mapbox_library_api.points_map.setup = async function () {
    let contact_points_data = await recursive_load({
      post_type: 'contacts',
      query: [],
    });
    let contact_points = {
      features: contact_points_data,
      type: 'FeatureCollection',
    };
    let group_points_data = await recursive_load({
      post_type: 'groups',
      query: [],
    });
    let group_points = {
      features: group_points_data,
      type: 'FeatureCollection',
    };

    window.mapbox_library_api.area_map.setup = async function () {
      let area_map = window.mapbox_library_api.area_map;
      area_map.grid_data = await window.makeRequest(
        'POST',
        window.mapbox_library_api.obj.settings.totals_rest_url,
        {
          post_type: window.mapbox_library_api.obj.settings.post_type,
          query: window.mapbox_library_api.query_args || {},
        },
        window.mapbox_library_api.obj.settings.rest_base_url,
      );
      await area_map.load_layer();
      // load new layer on event
      window.mapbox_library_api.map.on('zoomend', function () {
        area_map.load_layer();
      });
      window.mapbox_library_api.map.on('dragend', function () {
        area_map.load_layer();
      });
      window.mapbox_library_api.map.on('click', function (e) {
        // this section increments up the result on level because
        // it corresponds better to the viewable user intent for details
        let level = window.mapbox_library_api.get_level();
        area_map.load_detail_panel(e.lngLat.lng, e.lngLat.lat, level);
      });
    };
    window.mapbox_library_api.area_map.load_detail_panel = function () {};
    window.mapbox_library_api.area_map.behind_layer =
      'dt-maps-groups_points_layer';
    window.mapbox_library_api.points_map.load_layer(
      group_points,
      'groups_points_layer',
      '#cc4b37',
      10,
    );
    window.mapbox_library_api.points_map.load_layer(
      contact_points,
      'contacts_points_layer',
      '#11b4da',
    );
    await window.mapbox_library_api.area_map.setup();
    await window.mapbox_library_api.area_map.load_layer();
    jQuery('#loading-spinner').hide();
  };

  let split_by_html = `
    <div id="legend-icons" class="border-left">
      <span style="vertical-align: middle">
        <img
         style="filter: invert(41%) sepia(53%) saturate(2108%) hue-rotate(338deg) brightness(83%) contrast(91%);"
         src="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.template_dir)}/dt-assets/images/dot.svg">
         ${window.SHAREDFUNCTIONS.escapeHTML(window.dt_metrics_mapbox_caller_js.translations.groups)}
      </span>
      <span style="vertical-align: middle">
        <img
           style="filter: invert(74%) sepia(59%) saturate(6105%) hue-rotate(154deg) brightness(101%) contrast(87%);"
           src="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.template_dir)}/dt-assets/images/dot.svg">
           ${window.SHAREDFUNCTIONS.escapeHTML(window.dt_metrics_mapbox_caller_js.translations.contacts)}
      </span>

      <span style="vertical-align: middle">
        <span style="height:30px;width:30px;border:1px solid;background-color:rgb(155, 200, 254);display: inline-block;vertical-align: middle"></span>
        ${window.SHAREDFUNCTIONS.escapeHTML(window.dt_metrics_mapbox_caller_js.translations.active_users)}
       </span>
    </div>
  `;
  $('#legend-bar').append(split_by_html);
  $('#map-type').hide();
});
