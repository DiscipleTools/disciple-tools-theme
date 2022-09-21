/*global amcharts:false, am4maps:false */
"use strict";
let translations = window.mappingModule.mapping_module.translations

let MAPPINGDATA = window.mappingModule.mapping_module

let openChart = null

let mapFillColor = "rgb(217, 217, 217)"

//called when a drilldown option is selected or when the map clicked
window.DRILLDOWN.map_chart_drilldown = function( grid_id ) {
  MAPPINGDATA.settings.current_map = grid_id || 'world'
  location_grid_map( 'map_chart', grid_id )
  data_type_list( 'data_type_list' )
}


/**********************************************************************************************************************
 *
 * VISUAL MAP
 *
 * This displays a vision map and allows for drill down through clicking on map sections.
 *
 **********************************************************************************************************************/
function page_mapping_view( rest_endpoints_base = null ) {
  MAPPINGDATA.rest_endpoints_base = rest_endpoints_base
  let chartDiv = jQuery('#mapping_chart')
  chartDiv.empty().html(`
    <style>#chart { height: ${window.innerHeight - 100}px !important; }
    #map_chart { height: ${window.innerHeight - 300}px !important; }
    </style>
    <div id="map_wrapper" class="map_wrapper">
      <div  id="map_header_wrapper" class="grid-x map_header_wrapper">
        <div style="display: inline-block" class="loading-spinner"></div>
        <div id="map_chart_drilldown" class="cell medium-6 map_chart_drilldown"></div>
        <div id="map_title_wrapper" class="cell medium-6 map_title_wrapper">
          <strong id="section_title" class="section_title"></strong>
          <br>
          <span id="current_level" class="current_level"></span>
        </div>
      </div>
      <hr id="map_hr_1" class="map_hr" style="">

      <div id="data_type_list" class="small button-group data_type_list"></div>


      <div id="map_body_wrapper" class="grid-x grid-margin-x map_body_wrapper">
        <div id="map_chart_wrapper" class="cell medium-10 map_chart_wrapper">
            <div id="map_chart" class="map_chart"></div>
        </div>
        <div id="child_list_container" class="cell medium-2 left-border-grey child_list_container">
          <div id="minimap" class="minimap"></div>
          <div id="self_info"></div>
        </div>
      </div>
      <hr id="map_hr_2" class="map_hr">
    </div>

    <span id="refresh_data" class="refresh_data"><a onclick="get_data(true)">${window.lodash.escape( translations.refresh_data )}</a></span>
  `);

  if ( MAPPINGDATA.data ){
    DRILLDOWN.get_drill_down('map_chart_drilldown', MAPPINGDATA.settings.current_map)
  } else {
    return get_data(false).then(response=>{
      MAPPINGDATA.data = response
      // set the depth of the drill down
      MAPPINGDATA.settings.hide_final_drill_down = false
      // load drill down
      DRILLDOWN.get_drill_down('map_chart_drilldown', MAPPINGDATA.settings.current_map)
    }).fail(err=>{
      console.log(err)
    })
  }

}


function setCommonMapSettings( chart ) {
  let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
  polygonSeries.exclude = ["AQ","GL"];
  polygonSeries.useGeodata = true;
  let template = polygonSeries.mapPolygons.template;

  // create tool tip
  let toolTipContent = `<strong>{name}</strong><br>
                            ---------<br>
                            ${window.lodash.escape(translations.population)}: {population}<br>
                            `;
  jQuery.each( MAPPINGDATA.data.custom_column_labels, function(labelIndex, vc) {
    toolTipContent += `${window.lodash.escape(vc.label)}: {${window.lodash.escape( vc.key )}}<br>`
  })

  template.tooltipHTML = toolTipContent

  // Create hover state and set alternative fill color
  let hs = template.states.create("hover");
  hs.properties.fill = am4core.color("#000");

  template.propertyFields.fill = "fill";
  polygonSeries.tooltip.label.interactionsEnabled = true;
  polygonSeries.tooltip.pointerOrientation = "vertical";
  template.fill = am4core.color("#FFFFFF");
  // template.stroke = am4core.color("rgba(89,89,89,0.51)");

  polygonSeries.heatRules.push({
    property: "fill",
    target: template,
    min: chart.colors.getIndex(1).brighten(1.5),
    max: chart.colors.getIndex(1).brighten(-0.3)
  });
  // Zoom control
  chart.zoomControl = new am4maps.ZoomControl();

  let homeButton = new am4core.Button();
  homeButton.events.on("hit", function(){
    chart.goHome();
  });

  homeButton.icon = new am4core.Sprite();
  homeButton.padding(7, 5, 7, 5);
  homeButton.width = 30;
  homeButton.icon.path = "M16,8 L14,8 L14,16 L10,16 L10,10 L6,10 L6,16 L2,16 L2,8 L0,8 L8,0 L16,8 Z M16,8";
  homeButton.marginBottom = 10;
  homeButton.parent = chart.zoomControl;
  homeButton.insertBefore(chart.zoomControl.plusButton);


  /* Click navigation */
  template.events.on("hit", function(ev) {
    // if (MAPPINGDATA.data[ev.target.dataItem.dataContext.grid_id]) {
    return DRILLDOWN.get_drill_down('map_chart_drilldown', ev.target.dataItem.dataContext.grid_id)
    // }
  }, this);


  let default_map_settings = MAPPINGDATA.settings.default_map_settings
  if ( default_map_settings.children.length > 1 && default_map_settings.type !== "world" && MAPPINGDATA.settings.current_map == default_map_settings.parent ){
    // Pre-zoom to a list of countries
    let zoomTo = MAPPINGDATA.settings.default_map_settings.children;
    chart.events.on("appeared", function(ev) {
      // Init extrems
      let north, south, west, east;

      // Find extreme coordinates for all pre-zoom countries
      for(let i = 0; i < zoomTo.length; i++) {
        let country = polygonSeries.getPolygonById(zoomTo[i]);
        if (north === undefined || (country.north > north)) {
          north = country.north;
        }
        if (south === undefined || (country.south < south)) {
          south = country.south;
        }
        if (west === undefined || (country.west < west)) {
          west = country.west;
        }
        if (east === undefined || (country.east > east)) {
          east = country.east;
        }

        country.isActive = true;
      }
      chart.zoomToRectangle(north, east, south, west, 1, true);
    })
  }
}

function setUpData( features, map_data ){
  jQuery.each( features, function(featureIndex, mapFeature ) {
    let grid_id =  mapFeature.properties.grid_id
    let locationData =  window.lodash.get( map_data, `children[${grid_id}]` ) || window.lodash.get(map_data, `children[${mapFeature.id}]`)  || window.lodash.get( map_data, `${grid_id}.self` );
    if ( locationData ) {
      mapFeature.properties.grid_id = locationData.grid_id
      mapFeature.properties.population = locationData.population
      mapFeature.properties.name = locationData.name

      /* custom columns */
      if ( MAPPINGDATA.data.custom_column_data[grid_id] ) {
        /* Note: Amcharts calculates heatmap off last variable. So this section moves selected
        * heatmap variable to the end of the array */
        let focus = MAPPINGDATA.settings.heatmap_focus
        jQuery.each( MAPPINGDATA.data.custom_column_labels, function(labelIndex, label) {
          mapFeature.properties.fill = null;
          if ( labelIndex !== focus ) {
            mapFeature.properties[label.key] = MAPPINGDATA.data.custom_column_data[grid_id][labelIndex]
            mapFeature.properties.value = mapFeature.properties[label.key]
          }
        })
        jQuery.each( MAPPINGDATA.data.custom_column_labels, function(labelIndex, label) {
          if ( labelIndex === focus ) {
            mapFeature.properties[label.key] = MAPPINGDATA.data.custom_column_data[grid_id][labelIndex]
            mapFeature.properties.value = mapFeature.properties[label.key]
            if ( mapFeature.properties.value === 0 ){
              mapFeature.properties.fill = am4core.color(mapFillColor);
            }
          }
        })
      } else {
        jQuery.each( MAPPINGDATA.data.custom_column_labels, function(labelIndex, label) {
          mapFeature.properties[label.key] = 0
          mapFeature.properties.value = 0
          mapFeature.properties.fill = am4core.color(mapFillColor);
        })
      }
      /* end custom column */
    }
  })
  return features
}


function location_grid_map( div, grid_id = 'world' ) {
  am4core.useTheme(am4themes_animated);

  let chart = null
  if ( openChart ){
    openChart.dispose()
  }
  chart = am4core.create( div, am4maps.MapChart);
  setCommonMapSettings( chart );
  chart.projection = new am4maps.projections.Miller(); // Set projection
  chart.reverseGeodata = true
  openChart = chart
  let title = jQuery('#section_title')
  let rest = MAPPINGDATA.settings.endpoints.get_map_by_grid_id_endpoint


  title.empty()


  if ( MAPPINGDATA.data[grid_id] ) {
    build_map( MAPPINGDATA.data[grid_id] )
  } else {
    jQuery.ajax({
      type: rest.method,
      contentType: "application/json; charset=utf-8",
      data: JSON.stringify( { 'grid_id': grid_id, 'cached': MAPPINGDATA.settings.cached, 'cached_length': MAPPINGDATA.settings.cached_length } ),
      dataType: "json",
      url: MAPPINGDATA.settings.root + rest.namespace + rest.route,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', rest.nonce);
      },
    })
    .done( function( response ) {
      MAPPINGDATA.data[grid_id] = response
      build_map(response)

    }) // end success statement
    .fail(function (err) {
      console.log("error")
      console.log(err)
    })
  }

  function build_map( response ) {
    title.html(response.self.name)

    jQuery.getJSON( MAPPINGDATA.settings.mapping_source_url + 'collection/' + grid_id+'.geojson', function( data ) { // get geojson data

      // load geojson with additional parameters
      let mapData = data
      mapData.features = setUpData( mapData.features, response )
      chart.geodata = mapData

      //minimap
      let coordinates = []
      coordinates.push({
        "latitude": response.self.latitude || 0,
        "longitude": response.self.longitude || 0,
        "title": response.self.name || 'World'
      })
      mini_map( 'minimap', coordinates )

      //add totals section under the minimap
      let totals = MAPPINGDATA.data.custom_column_data[grid_id] || []
      if ( grid_id === "world" ){
        totals = new Array(MAPPINGDATA.data.custom_column_labels.length).fill(0)
        Object.keys(MAPPINGDATA.data.world.children).forEach(id=>{
          if ( MAPPINGDATA.data.custom_column_data[id] ){
            totals = totals.map((num, idx)=>{
              return num + MAPPINGDATA.data.custom_column_data[id][idx]
            })
          }
        })
      }
      let self_info = jQuery('#self_info')
      self_info.empty()
      let self_html = `<ul class="ul-no-bullets">`
      jQuery.each( MAPPINGDATA.data.custom_column_labels, function(labelIndex, label) {
        let value = totals[labelIndex] || 0
        self_html += `<li><strong>${label.label}</strong>: ${value}</li>`

      })
      self_info.html(self_html + `</ul`)

    }) // end get geojson


    .fail(function() {
      // if failed to get multi polygon map, then get boundary map and fill with placemarks

      jQuery.getJSON( MAPPINGDATA.settings.mapping_source_url + 'low/' + grid_id+'.geojson' ).then(function( data ) {
        // Create map polygon series
        chart.geodata = data

        chart.projection = new am4maps.projections.Miller();
        chart.reverseGeodata = true
        let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
        polygonSeries.useGeodata = true;

        let imageSeries = chart.series.push(new am4maps.MapImageSeries());

        let locations = []
        jQuery.each( MAPPINGDATA.data[grid_id].children, function(i, v) {
          /* custom columns */
          let focus = MAPPINGDATA.settings.heatmap_focus
          jQuery.each( MAPPINGDATA.data.custom_column_labels, function(labelIndex, label) {
            v[label.key] = window.lodash.get( MAPPINGDATA.data.custom_column_data, `[${v.grid_id}][${labelIndex}]`, 0 )
          })

          locations.push( v )
        } )
        imageSeries.data = locations;


        let imageSeriesTemplate = imageSeries.mapImages.template;
        let circle = imageSeriesTemplate.createChild(am4core.Circle);
        circle.radius = 6;
        circle.fill = am4core.color("#3c5bdc");
        circle.stroke = am4core.color("#3c5bdc");
        circle.strokeWidth = 2;
        circle.nonScaling = true;

        // Click navigation
        circle.events.on("hit", function (ev) {

          return DRILLDOWN.get_drill_down( 'map_chart_drilldown', ev.target.dataItem.dataContext.grid_id, MAPPINGDATA.settings.cached )

        }, this);

        let circleTipContent = `<strong>{name}</strong><br>
                            ---------<br>
                            ${window.lodash.escape(translations.population)}: {population}<br>
                            `;
        jQuery.each( MAPPINGDATA.data.custom_column_labels, function(labelIndex, vc) {
          circleTipContent += `${window.lodash.escape(vc.label)}: {${window.lodash.escape( vc.key )}}<br>`
        })
        circle.tooltipHTML = circleTipContent

        imageSeries.heatRules.push({
          property: "fill",
          target: circle,
          min: chart.colors.getIndex(1).brighten(1.5),
          max: chart.colors.getIndex(1).brighten(-0.3)
        });

        imageSeriesTemplate.propertyFields.latitude = "latitude";
        imageSeriesTemplate.propertyFields.longitude = "longitude";
        imageSeriesTemplate.nonScaling = true;

      })
    })
  }
}

function data_type_list( div ) {
  let list = jQuery('#'+div )
  list.empty()
  let focus = MAPPINGDATA.settings.heatmap_focus

  jQuery.each( MAPPINGDATA.data.custom_column_labels, function(i,v) {
    let hollow = 'hollow'
    if ( i === focus ) {
      hollow = ''
    }
    list.append(`
      <a onclick="heatmap_focus_change( ${window.lodash.escape( i )}, '${MAPPINGDATA.settings.current_map}' )"
        class="button ${hollow}"
        id="${window.lodash.escape( v.key )}">
        ${window.lodash.escape( v.label )}
      </a>
    `)
  })
}

function heatmap_focus_change( focus_id, current_map ) {

  MAPPINGDATA.settings.heatmap_focus = focus_id
  let geodata = openChart.geodata
  geodata.features = setUpData( geodata.features, MAPPINGDATA.data[MAPPINGDATA.settings.current_map])

  openChart.geodata = []
  openChart.geodata = geodata

  data_type_list( 'data_type_list' )
}


let minimapChart = null
function mini_map( div, marker_data ) {
  //if the minimap is not set
  if ( !jQuery('#' + div ).length ){
    return
  }

  if ( window.am4geodata_worldLow === undefined ) {
    let mapUrl = MAPPINGDATA.settings.mapping_source_url + 'collection/world.geojson'
    jQuery.getJSON( mapUrl, function( data ) {
      window.am4geodata_worldLow = data
      build_minimap()
    })
  } else {
    build_minimap()
  }

  function build_minimap(){
    if (minimapChart){
      minimapChart.dispose()
    }

    am4core.useTheme(am4themes_animated);

    minimapChart = am4core.create( div, am4maps.MapChart);
    let chart = minimapChart

    chart.projection = new am4maps.projections.Orthographic(); // Set projection
    chart.reverseGeodata = true

    chart.seriesContainer.draggable = false;
    chart.seriesContainer.resizable = false;

    if ( parseInt(marker_data[0].longitude) < 0 ) {
      chart.deltaLongitude = parseInt(Math.abs(marker_data[0].longitude));
    } else {
      chart.deltaLongitude = parseInt(-Math.abs(marker_data[0].longitude));
    }

    chart.geodata = window.am4geodata_worldLow;
    let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());

    polygonSeries.useGeodata = true;

    let imageSeries = chart.series.push(new am4maps.MapImageSeries());

    imageSeries.data = marker_data;

    let imageSeriesTemplate = imageSeries.mapImages.template;
    let circle = imageSeriesTemplate.createChild(am4core.Circle);
    circle.radius = 4;
    circle.fill = am4core.color("#B27799");
    circle.stroke = am4core.color("#FFFFFF");
    circle.strokeWidth = 2;
    circle.nonScaling = true;
    circle.tooltipText = "{title}";
    imageSeriesTemplate.propertyFields.latitude = "latitude";
    imageSeriesTemplate.propertyFields.longitude = "longitude";
  }
}

function get_data( force_refresh = false ) {
  let spinner = jQuery('.loading-spinner')
  spinner.addClass('active')
  return jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: `${MAPPINGDATA.rest_endpoints_base}/data?refresh=${force_refresh}`,
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', window.mappingModule.nonce );
    },
  })
  .then( function( response ) {
    spinner.removeClass('active')
    return response
  })
  .fail(function (err) {
    spinner.removeClass('active')
    console.log("error")
    console.log(err)
  })
}



