let translations = window.mappingModule.mapping_module.translations

jQuery(document).ready(function() {
    let mapUrl = ''
    if('#mapping_view' === window.location.hash) {
        if ( window.am4geodata_worldLow === undefined ) {
          mapUrl = DRILLDOWNDATA.settings.mapping_source_url + 'polygon_collection/world.geojson'
          jQuery.getJSON( mapUrl, function( data ) {
            window.am4geodata_worldLow = data
            page_mapping_view()
          })
            .fail(function (err) {
              console.log(`No polygon available.`)
            })
        } else {
          page_mapping_view()
        }
    }
    if('#mapping_list' === window.location.hash) {
        if ( window.am4geodata_worldLow === undefined ) {
          mapUrl = DRILLDOWNDATA.settings.mapping_source_url + 'polygon_collection/world.geojson'
          jQuery.getJSON( mapUrl, function( data ) {
            window.am4geodata_worldLow = data
            page_mapping_list()
          })
            .fail(function (err) {
              console.log(`No polygon available.`)
            })
        } else {
          page_mapping_list()
        }
    }
})

_ = _ || window.lodash


window.DRILLDOWN.map_chart_drilldown = function( geonameid ) {
    if ( geonameid !== 'top_map_level' ) { // make sure this is not a top level continent or world request
        DRILLDOWNDATA.settings.current_map = parseInt(geonameid)
        geoname_map( 'map_chart', parseInt(geonameid) )
        data_type_list( 'data-type-list' )
    }
    else { // top_level maps
        DRILLDOWNDATA.settings.current_map = 'top_map_level'
        top_level_map( 'map_chart' )
        data_type_list( 'data-type-list' )
    }
}


/**********************************************************************************************************************
 *
 * VISUAL MAP
 *
 * This displays a vision map and allows for drill down through clicking on map sections.
 *
 **********************************************************************************************************************/
function page_mapping_view() {
    "use strict";
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        
        <div class="grid-x grid-margin-y">
            <div class="cell medium-6" id="map_chart_drilldown"></div>
            <div class="cell medium-6" style="text-align:right;">
               <strong id="section-title" style="font-size:2em;"></strong><br>
                <span id="current_level"></span>
            </div>
        </div>
        <span style="font-size:.8em; margin-left:20px"><a onclick="refresh_data('get_geoname_totals')">${_.escape( translations.refresh_data )}</a></span>
        <hr style="max-width:100%;">
        
       <!-- Map -->
       <div class="grid-x grid-margin-x">
            <div class="cell medium-10">
                <div id="map_chart" style="width: 100%;max-height: 700px;height: 100vh;vertical-align: text-top;"></div>
            </div>
            <div class="cell medium-2 left-border-grey">
                <div class="grid-y">
                    <div class="cell" style="overflow-y: scroll; height:700px; padding:0 .4em;" id="child-list-container">
                        <div id="minimap"></div><br><br>
                        <div class="button-group expanded stacked" id="data-type-list">
                         </div>
                    </div>
                </div>
            </div>
        </div>
        
        <hr style="max-width:100%;">
        
        <br>
        `);

    // set the depth of the drill down
    DRILLDOWNDATA.settings.hide_final_drill_down = false
    // load drill down
    DRILLDOWN.get_drill_down('map_chart_drilldown')

}


function top_level_map( div ) {
    am4core.useTheme(am4themes_animated);
    let chart = am4core.create( div, am4maps.MapChart);
    chart.projection = new am4maps.projections.Miller(); // Set projection

    let default_map_settings = DRILLDOWNDATA.settings.default_map_settings
    let mapUrl = ''
    let top_map_list = DRILLDOWNDATA.data.top_map_list
    let title = jQuery('#section-title')

    switch ( default_map_settings.type ) {

        case 'world': {
          let map_data = DRILLDOWNDATA.data.world
          let geoJSON = window.am4geodata_worldLow

          // set title
          title.empty().html(map_data.self.name)

          // prepare country/child data
          jQuery.each( geoJSON.features, function(i, v ) {
            if ( map_data.children[v.id] !== undefined ) {
              geoJSON.features[i].properties.geonameid = map_data.children[v.id].geonameid
              geoJSON.features[i].properties.population = map_data.children[v.id].population


              /* custom columns */
              if ( DRILLDOWNDATA.data.custom_column_data[geoJSON.features[i].properties.geonameid] ) {
                /* Note: Amcharts calculates heatmap off last variable. So this section moves selected
                * heatmap variable to the end of the array */
                let focus = DRILLDOWNDATA.settings.heatmap_focus
                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                  if ( ii !== focus ) {
                    geoJSON.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[geoJSON.features[i].properties.geonameid][ii]
                    geoJSON.features[i].properties.value = geoJSON.features[i].properties[vv.key]
                  }
                })
                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                  if ( ii === focus ) {
                    geoJSON.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[geoJSON.features[i].properties.geonameid][ii]
                    geoJSON.features[i].properties.value = geoJSON.features[i].properties[vv.key]
                  }
                })
              } else {
                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                  geoJSON.features[i].properties[vv.key] = 0
                  geoJSON.features[i].properties.value = 0
                })
              }
              /* end custom column */
            }
          })

          chart.geodata = geoJSON;

          // initialize polygonseries
          let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
          polygonSeries.exclude = ["AQ","GL"];
          polygonSeries.useGeodata = true;

          let template = polygonSeries.mapPolygons.template;

          // create tool tip
          let toolTipContent = `<strong>{name}</strong><br>
                            ---------<br>
                            ${_.escape(translations.population)}: {population}<br>
                            `;
          jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vc) {
            toolTipContent += `${_.escape(vc.label)}: ${_.escape( vc.key )}<br>`
          })

          template.tooltipHTML = toolTipContent

          // Create hover state and set alternative fill color
          let hs = template.states.create("hover");
          hs.properties.fill = am4core.color("#3c5bdc");


          template.propertyFields.fill = "fill";
          polygonSeries.tooltip.label.interactionsEnabled = true;
          polygonSeries.tooltip.pointerOrientation = "vertical";

          polygonSeries.heatRules.push({
            property: "fill",
            target: template,
            min: chart.colors.getIndex(1).brighten(1.5),
            max: chart.colors.getIndex(1).brighten(-0.3)
          });


          // add slider to chart container in order not to occupy space
          let slider = chart.chartContainer.createChild(am4core.Slider);
          slider.start = .5;
          slider.valign = "bottom";
          slider.width = 400;
          slider.align = "center";
          slider.marginBottom = 15;
          slider.start = .5;
          slider.events.on("rangechanged", () => {
            chart.deltaLongitude = 720 * slider.start;
          })


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
            console.log(ev.target.dataItem.dataContext.name)
            console.log(ev.target.dataItem.dataContext.geonameid)
            return DRILLDOWN.get_drill_down( 'map_chart_drilldown', ev.target.dataItem.dataContext.geonameid )
          }, this);

          let coordinates = []
          coordinates[0] = {
            "latitude": 0,
            "longitude": 0,
            "title": 'World'
          }
          mini_map( 'minimap', coordinates )

            break;
        }
        case 'country': {
            console.log('top_level_map: country')

            if( Object.keys(top_map_list).length === 1 ) { // if only one country selected
                jQuery.each(top_map_list, function(i,v) {
                    geoname_map( div, i )
                })
            } else {
                // multiple countries selected. So load the world and reduce the polygons

              let geoJSON = window.am4geodata_worldLow

              // create a new geojson, including only the top level maps
              let new_geojson = jQuery.extend({}, geoJSON )
              new_geojson.features = []

              jQuery.each(geoJSON.features, function(i,v) {
                if ( top_map_list[ v.properties.geonameid ] ) {
                  new_geojson.features.push(v)
                }
              })

              // Set map definition
              let mapData = new_geojson
              let coordinates = []
              title.empty()

              // prepare country/child data
              jQuery.each( mapData.features, function(i, v ) {

                if ( DRILLDOWNDATA.data[v.properties.geonameid] !== undefined ) {
                  mapData.features[i].properties.geonameid = v.properties.geonameid
                  mapData.features[i].properties.population = DRILLDOWNDATA.data[v.properties.geonameid].self.population


                  /* custom columns */
                  if ( DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid] ) {
                    /* Note: Amcharts calculates heatmap off last variable. So this section moves selected
                    * heatmap variable to the end of the array */
                    let focus = DRILLDOWNDATA.settings.heatmap_focus
                    jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                      if ( ii !== focus ) {
                        mapData.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid][ii]
                        mapData.features[i].properties.value = mapData.features[i].properties[vv.key]
                      }
                    })
                    jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                      if ( ii === focus ) {
                        mapData.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid][ii]
                        mapData.features[i].properties.value = mapData.features[i].properties[vv.key]
                      }
                    })
                  } else {
                    jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                      mapData.features[i].properties[vv.key] = 0
                      mapData.features[i].properties.value = 0
                    })
                  }
                  /* end custom column */

                  if ( mapData.features.length > 3 ) {
                    // set title
                    title.empty().html('Multiple Countries')
                  } else {
                    title.append(DRILLDOWNDATA.data[v.properties.geonameid].self.name)
                    if ( title.html().length !== '' ) {
                      title.append(', ')
                    }
                  }


                  coordinates[i] = {
                    "latitude": DRILLDOWNDATA.data[v.properties.geonameid].self.latitude,
                    "longitude": DRILLDOWNDATA.data[v.properties.geonameid].self.longitude,
                    "title": DRILLDOWNDATA.data[v.properties.geonameid].self.name
                  }

                }
              })

              chart.geodata = mapData;

              // initialize polygonseries
              let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
              polygonSeries.useGeodata = true;

              let template = polygonSeries.mapPolygons.template;

              // create tool tip
              let toolTipContent = `<strong>{name}</strong><br>
                            ---------<br>
                            ${_.escape(translations.population)}: {population}<br>
                            `;
              jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vc) {
                toolTipContent += `${_.escape(vc.label)}: ${_.escape( vc.key )}<br>`
              })
              template.tooltipHTML = toolTipContent

              // Create hover state and set alternative fill color
              let hs = template.states.create("hover");
              hs.properties.fill = am4core.color("#3c5bdc");


              template.propertyFields.fill = "fill";
              polygonSeries.tooltip.label.interactionsEnabled = true;
              polygonSeries.tooltip.pointerOrientation = "vertical";

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
                console.log(ev.target.dataItem.dataContext.name)
                console.log(ev.target.dataItem.dataContext.geonameid)

                if( DRILLDOWNDATA.data[ev.target.dataItem.dataContext.geonameid] )
                {
                  return DRILLDOWN.get_drill_down( 'map_chart_drilldown', ev.target.dataItem.dataContext.geonameid )
                }
              }, this);

              mini_map( 'minimap', coordinates )

            }

            break;
        }
        case 'state': {
            console.log('top_level_map: state')

            if( Object.keys(top_map_list).length === 1 ) { // if only one country selected
                jQuery.each(top_map_list, function(i,v) {
                    geoname_map( div, i )
                })
            } else {
                // multiple countries selected. So load the world and reduce the polygons

                mapUrl = DRILLDOWNDATA.settings.mapping_source_url + `polygon_collection/${_.escape( default_map_settings.parent )}.geojson`
                jQuery.getJSON( mapUrl, function( data ) {

                    // set title

                    title.empty().append(DRILLDOWNDATA.data[default_map_settings.parent].self.name)

                    // create a new geojson, including only the top level maps
                    let new_geojson = jQuery.extend({}, data )
                    new_geojson.features = []

                    jQuery.each(data.features, function(i,v) {
                        if ( top_map_list[ v.properties.geonameid ] ) {
                            new_geojson.features.push(v)
                        }
                    })


                    // Set map definition
                    let mapData = new_geojson
                    let coordinates = []

                    // prepare country/child data
                    jQuery.each( mapData.features, function(i, v ) {

                        if ( DRILLDOWNDATA.data[v.properties.geonameid] !== undefined ) {
                            mapData.features[i].properties.geonameid = v.properties.geonameid
                            mapData.features[i].properties.population = DRILLDOWNDATA.data[v.properties.geonameid].self.population


                            /* custom columns */
                            if ( DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid] ) {
                                /* Note: Amcharts calculates heatmap off last variable. So this section moves selected
                                * heatmap variable to the end of the array */
                                let focus = DRILLDOWNDATA.settings.heatmap_focus
                                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                                    if ( ii !== focus ) {
                                        mapData.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid][ii]
                                        mapData.features[i].properties.value = mapData.features[i].properties[vv.key]
                                    }
                                })
                                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                                    if ( ii === focus ) {
                                        mapData.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid][ii]
                                        mapData.features[i].properties.value = mapData.features[i].properties[vv.key]
                                    }
                                })
                            } else {
                                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                                    mapData.features[i].properties[vv.key] = 0
                                    mapData.features[i].properties.value = 0
                                })
                            }
                            /* end custom column */

                            coordinates[i] = {
                                "latitude": DRILLDOWNDATA.data[v.properties.geonameid].self.latitude,
                                "longitude": DRILLDOWNDATA.data[v.properties.geonameid].self.longitude,
                                "title": DRILLDOWNDATA.data[v.properties.geonameid].self.name
                            }

                        }
                    })

                    chart.geodata = mapData;

                    // initialize polygonseries
                    let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
                    polygonSeries.useGeodata = true;

                    let template = polygonSeries.mapPolygons.template;

                    // create tool tip
                    let toolTipContent = `<strong>{name}</strong><br>
                            ---------<br>
                            ${_.escape(translations.population)}: {population}<br>
                            `;
                    jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vc) {
                        toolTipContent += `${_.escape(vc.label)}: ${_.escape( vc.key )}<br>`
                    })
                    template.tooltipHTML = toolTipContent

                    // Create hover state and set alternative fill color
                    let hs = template.states.create("hover");
                    hs.properties.fill = am4core.color("#3c5bdc");


                    template.propertyFields.fill = "fill";
                    polygonSeries.tooltip.label.interactionsEnabled = true;
                    polygonSeries.tooltip.pointerOrientation = "vertical";

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
                        console.log(ev.target.dataItem.dataContext.name)
                        console.log(ev.target.dataItem.dataContext.geonameid)

                        if( DRILLDOWNDATA.data[ev.target.dataItem.dataContext.geonameid] )
                        {
                            return DRILLDOWN.get_drill_down( 'map_chart_drilldown', ev.target.dataItem.dataContext.geonameid )
                        }
                    }, this);

                    mini_map( 'minimap', coordinates )

                }).fail(function (err) {
                    jQuery('#map_chart').empty().append(`No polygon available.`)
                    console.log(`No polygon available.`)
                })
            }
            break;
        }
    }
}

function geoname_map( div, geonameid ) {
    am4core.useTheme(am4themes_animated);

    let chart = am4core.create( div, am4maps.MapChart);
    let title = jQuery('#section-title')
    let rest = DRILLDOWNDATA.settings.endpoints.get_map_by_geonameid_endpoint

    chart.projection = new am4maps.projections.Miller(); // Set projection

    title.empty()

    if ( DRILLDOWNDATA.data[geonameid] ) {
        build_map( DRILLDOWNDATA.data[geonameid] )
    } else {
      jQuery.ajax({
        type: rest.method,
        contentType: "application/json; charset=utf-8",
        data: JSON.stringify( { 'geonameid': geonameid } ),
        dataType: "json",
        url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
        beforeSend: function(xhr) {
          xhr.setRequestHeader('X-WP-Nonce', rest.nonce);
        },
      })
        .done( function( response ) {
          DRILLDOWNDATA.data[geonameid] = response
          build_map(response)

        }) // end success statement
        .fail(function (err) {
          console.log("error")
          console.log(err)
        })
    }

    function build_map( response ) {
      title.html(response.self.name)

      jQuery.getJSON( DRILLDOWNDATA.settings.mapping_source_url + 'polygon_collection/' + geonameid+'.geojson', function( data ) { // get geojson data

        // load geojson with additional parameters
        let mapData = data

        jQuery.each( mapData.features, function(i, v ) {
          if ( response.children[mapData.features[i].properties.geonameid] !== undefined ) {

            mapData.features[i].properties.population = response.children[mapData.features[i].properties.geonameid].population

            /* custom columns */
            if ( DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid] ) {
              /* Note: Amcharts calculates heatmap off last variable. So this section moves selected
              * heatmap variable to the end of the array */
              let focus = DRILLDOWNDATA.settings.heatmap_focus
              jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                if ( ii !== focus ) {
                  mapData.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid][ii]
                  mapData.features[i].properties.value = mapData.features[i].properties[vv.key]
                }
              })
              jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                if ( ii === focus ) {
                  mapData.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid][ii]
                  mapData.features[i].properties.value = mapData.features[i].properties[vv.key]
                }
              })
            } else {
              jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                mapData.features[i].properties[vv.key] = 0
                mapData.features[i].properties.value = 0
              })
            }
            /* end custom column */
          }
        })

        // create polygon series
        let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
        polygonSeries.geodata = mapData
        polygonSeries.useGeodata = true;

        // Configure series tooltip
        let template = polygonSeries.mapPolygons.template;

        // create tool tip
        let toolTipContent = `<strong>{name}</strong><br>
                            ---------<br>
                            ${_.escape(translations.population)}: {population}<br>
                            `;
        jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vc) {
          toolTipContent += `${_.escape(vc.label)}: ${_.escape( vc.key )}<br>`
        })
        template.tooltipHTML = toolTipContent

        // Create hover state and set alternative fill color
        let hs = template.states.create("hover");
        hs.properties.fill = am4core.color("#3c5bdc");


        template.propertyFields.fill = "fill";
        polygonSeries.tooltip.label.interactionsEnabled = true;
        polygonSeries.tooltip.pointerOrientation = "vertical";

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
          console.log(ev.target.dataItem.dataContext.geonameid)
          console.log(ev.target.dataItem.dataContext.name)
          return DRILLDOWN.get_drill_down( 'map_chart_drilldown', ev.target.dataItem.dataContext.geonameid )
        }, this);

        let coordinates = []
        coordinates.push({
          "latitude": response.self.latitude,
          "longitude": response.self.longitude,
          "title": response.self.name
        })

        mini_map( 'minimap', coordinates )

      }) // end get geojson


        .fail(function() {
          // if failed to get multi polygon map, then get boundary map and fill with placemarks

          jQuery.getJSON( DRILLDOWNDATA.settings.mapping_source_url + 'polygon/' + geonameid+'.geojson', function( data ) {
            // Create map polygon series

            let polygon = data

            chart.geodata = polygon;

            chart.projection = new am4maps.projections.Miller();
            let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
            polygonSeries.useGeodata = true;


            let imageSeries = chart.series.push(new am4maps.MapImageSeries());

            let locations = []
            jQuery.each( DRILLDOWNDATA.data[geonameid].children, function(i, v) {

              /* custom columns */
              if ( DRILLDOWNDATA.data.custom_column_data[v.geonameid] ) {
                /* Note: Amcharts calculates heatmap off last variable. So this section moves selected
                * heatmap variable to the end of the array */
                let focus = DRILLDOWNDATA.settings.heatmap_focus
                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                  if ( ii !== focus ) {
                    v[vv.key] = DRILLDOWNDATA.data.custom_column_data[v.geonameid][ii]
                  }
                })
                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                  if ( ii === focus ) {
                    v[vv.key] = DRILLDOWNDATA.data.custom_column_data[v.geonameid][ii]
                  }
                })
              } else {
                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                  v[vv.key] = 0
                })
              }
              /* end custom column */

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
              console.log(ev.target.dataItem.dataContext.name)
              console.log(ev.target.dataItem.dataContext.geonameid)

              return DRILLDOWN.get_drill_down( 'map_chart_drilldown', ev.target.dataItem.dataContext.geonameid )

            }, this);

            let circleTipContent = `<strong>{name}</strong><br>
                            ---------<br>
                            ${_.escape(translations.population)}: {population}<br>
                            `;
            jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vc) {
              circleTipContent += `${_.escape(vc.label)}: ${_.escape( vc.key )}<br>`
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
    let focus = DRILLDOWNDATA.settings.heatmap_focus

    jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(i,v) {
        let hollow = 'hollow'
        if ( i === focus ) {
            hollow = ''
        }
        list.append(`<a onclick="heatmap_focus_change( ${_.escape( i )}, '${DRILLDOWNDATA.settings.current_map}' )" class="button ${hollow}" id="${_.escape( v.key )}">${_.escape( v.label )}</a>`)
    })
}

function heatmap_focus_change( focus_id, current_map ) {
    DRILLDOWNDATA.settings.heatmap_focus = focus_id

    if ( current_map !== 'top_map_level' ) { // make sure this is not a top level continent or world request
        DRILLDOWN.get_drill_down( 'map_chart_drilldown', current_map )
    }
    else { // top_level maps
        DRILLDOWN.get_drill_down('map_chart_drilldown')
    }
}

function mini_map( div, marker_data ) {

  am4core.useTheme(am4themes_animated);

  var chart = am4core.create( div, am4maps.MapChart);

  chart.projection = new am4maps.projections.Orthographic(); // Set projection

  chart.seriesContainer.draggable = false;
  chart.seriesContainer.resizable = false;

  if ( parseInt(marker_data[0].longitude) < 0 ) {
    chart.deltaLongitude = parseInt(Math.abs(marker_data[0].longitude));
  } else {
    chart.deltaLongitude = parseInt(-Math.abs(marker_data[0].longitude));
  }

  chart.geodata = window.am4geodata_worldLow;
  var polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());

  polygonSeries.useGeodata = true;

  var imageSeries = chart.series.push(new am4maps.MapImageSeries());

  imageSeries.data = marker_data;

  var imageSeriesTemplate = imageSeries.mapImages.template;
  var circle = imageSeriesTemplate.createChild(am4core.Circle);
  circle.radius = 4;
  circle.fill = am4core.color("#B27799");
  circle.stroke = am4core.color("#FFFFFF");
  circle.strokeWidth = 2;
  circle.nonScaling = true;
  circle.tooltipText = "{title}";
  imageSeriesTemplate.propertyFields.latitude = "latitude";
  imageSeriesTemplate.propertyFields.longitude = "longitude";

}

function refresh_data( key ) {
  let rest = DRILLDOWNDATA.settings.endpoints.delete_transient_endpoint

  jQuery.ajax({
    type: rest.method,
    contentType: "application/json; charset=utf-8",
    data: JSON.stringify( { 'key': key } ),
    dataType: "json",
    url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', rest.nonce );
    },
  })
    .done( function( response ) {
        if ( DRILLDOWNDATA.settings.current_map !== undefined ) {
          DRILLDOWN.get_drill_down('map_chart_drilldown', DRILLDOWNDATA.settings.current_map )
        } else {
          DRILLDOWN.get_drill_down('map_chart_drilldown')
        }
    })
    .fail(function (err) {
      console.log("error")
      console.log(err)
    })
}



/**********************************************************************************************************************
 *
 * LIST
 *
 * This page allows for drill-down into the locations and related reports.
 *
 **********************************************************************************************************************/


function page_mapping_list() {
    "use strict";
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <div class="grid-x grid-margin-x">
            <div class="cell auto" id="location_list_drilldown"></div>
            <div class="cell small-1">
                <span id="spinner" style="display:none;" class="float-right">${DRILLDOWNDATA.settings.spinner_large}</span>
            </div>
        </div>
        
        <hr style="max-width:100%;">
        
        <div id="page-header" style="float:left;">
            <strong id="section-title" style="font-size:1.5em;"></strong><br>
            <span id="current_level"></span>
        </div>
        
        <div id="location_list"></div>
        
        <hr style="max-width:100%;">
        
        <br>
        <style> /* @todo move these definitions to site style sheet. */
            #page-header {
                position:absolute;
            }
            @media screen and (max-width : 640px){
                #page-header {
                    position:relative;
                    text-align: center;
                    width: 100%;
                }
            }
           
        </style>
        `);

    // set the depth of the drill down
    DRILLDOWNDATA.settings.hide_final_drill_down = false
    // load drill down
    window.DRILLDOWN.get_drill_down('location_list_drilldown')
}

window.DRILLDOWN.location_list_drilldown = function( geonameid ) {
    geoname_list( 'location_list', geonameid )
}


function geoname_list( div, geonameid ) {
    DRILLDOWN.show_spinner()

    // Find data source before build
    if ( geonameid === 'top_map_level' ) {
        let map_data = null
        let default_map_settings = DRILLDOWNDATA.settings.default_map_settings

        if ( DRILLDOWN.isEmpty( default_map_settings.children ) ) {
            map_data = DRILLDOWNDATA.data[default_map_settings.parent]
        }
        else {
            if ( default_map_settings.children.length < 2 ) {
                // single child
                map_data = DRILLDOWNDATA.data[default_map_settings.children[0]]
            } else {
                // multiple child
                jQuery('#section-title').empty()
                jQuery('#current_level').empty()
                jQuery('#location_list').empty().append('Select Location')
                DRILLDOWN.hide_spinner()
                return;
            }
        }

        // Initialize Location Data
        if ( map_data === undefined ) {
            console.log('error getting map_data')
            return;
        }

        build_geoname_list( div, map_data )
    }
    else if ( DRILLDOWNDATA.data[geonameid] === undefined ) {
        let rest = DRILLDOWNDATA.settings.endpoints.get_map_by_geonameid_endpoint

        jQuery.ajax({
            type: rest.method,
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify( { 'geonameid': geonameid } ),
            dataType: "json",
            url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', rest.nonce );
            },
        })
            .done( function( response ) {
                DRILLDOWNDATA.data[geonameid] = response
                build_geoname_list( div, DRILLDOWNDATA.data[geonameid] )
            })
            .fail(function (err) {
                console.log("error")
                console.log(err)
                DRILLDOWN.hide_spinner()
            })

    } else {
        build_geoname_list( div, DRILLDOWNDATA.data[geonameid] )
    }

    // build list
    function build_geoname_list( div, map_data ) {

        // Place Title
        let title = jQuery('#section-title')
        title.empty().html(map_data.self.name)

        // Population Division and Check for Custom Division
        let pd_settings = DRILLDOWNDATA.settings.population_division
        let population_division = pd_settings.base
        if ( ! DRILLDOWN.isEmpty( pd_settings.custom ) ) {
            jQuery.each( pd_settings.custom, function(i,v) {
                if ( map_data.self.geonameid === i ) {
                    population_division = v
                }
            })
        }

        // Self Data
        let self_population = map_data.self.population_formatted
        jQuery('#current_level').empty().html(`${_.escape(translations.population)}: ${_.escape( self_population )}`)

        // Build List
        let locations = jQuery('#location_list')
        locations.empty()

        let html = `<table id="country-list-table" class="display">`

        // Header Section
        html += `<thead><tr><th>${_.escape(translations.name)}</th><th>${_.escape(translations.population)}</th>`

        /* Additional Columns */
        if ( DRILLDOWNDATA.data.custom_column_labels ) {
            jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(i,v) {
                html += `<th>${_.escape( v.label )}</th>`
            })
        }
        /* End Additional Columns */

        html += `</tr></thead>`
        // End Header Section

        // Children List Section
        let sorted_children =  _.sortBy(map_data.children, [function(o) { return o.name; }]);

        html += `<tbody>`

        jQuery.each( sorted_children, function(i, v) {
            let population = v.population_formatted

            html += `<tr>
                        <td><strong><a onclick="DRILLDOWN.get_drill_down('location_list_drilldown', ${_.escape( v.geonameid )} )">${_.escape( v.name )}</a></strong></td>
                        <td>${_.escape( population )}</td>`

            /* Additional Columns */
            if ( DRILLDOWNDATA.data.custom_column_data[v.geonameid] ) {
                jQuery.each( DRILLDOWNDATA.data.custom_column_data[v.geonameid], function(ii,vv) {
                    html += `<td><strong>${_.escape( vv )}</strong></td>`
                })
            } else {
                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii,vv) {
                    html += `<td class="grey">0</td>`
                })
            }
            /* End Additional Columns */

            html += `</tr>`

        })
        html += `</tbody>`
        // end Child section

        html += `</table>`
        locations.append(html)

        jQuery('#country-list-table').DataTable({
            "paging":   false
        });

        DRILLDOWN.hide_spinner()
    }
}
