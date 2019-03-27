jQuery(document).ready(function() {
    
    if('#mapping_view' === window.location.hash) {
        console.log(DRILLDOWNDATA)
        page_mapping_view()
    }
    if('#mapping_list' === window.location.hash) {
        console.log(DRILLDOWNDATA)
        page_mapping_list()
    }
})

_ = _ || window.lodash

window.DRILLDOWN.location_list = function(  geonameid ) {
    if ( geonameid !== 'top_map_list' ) {
        location_list( 'location_list', geonameid )
    } else {
        jQuery('#location_list').empty().append(`Select list above.`)
        location_list( 'location_list' )
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
            <div class="cell medium-6">
                <ul id="drill_down"></ul>
            </div>
            <div class="cell medium-6" style="text-align:right;">
               <strong id="section-title" style="font-size:2em;"></strong><br>
                <span id="current_level"></span>
            </div>
        </div>
        
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
        
        <span style="float:right;font-size:.8em;"><a onclick="DRILLDOWN.load_drill_down( null, 'map_chart' )" >return to top level</a></span>
        <br>
        `);

    DRILLDOWN.load_drill_down( null, 'map_chart' )
    data_type_list( 'data-type-list' )
}

window.DRILLDOWN.map_chart = function( geonameid ) {
    if ( geonameid !== 'top_map_list' && geonameid !== 'world' ) { // make sure this is not a top level continent or world request
        console.log('map_chart: geonameid available')
        geoname_map( 'map_chart', geonameid )
    }
    else { // top_level maps
        console.log('map_chart: top level')
        top_level_map( 'map_chart' )
    }
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

        case 'world':
            console.log('top_level_map: world')
            let map_data = DRILLDOWNDATA.data.world

            // set title
            title.empty().html(map_data.self.name)

            // sort custom start level url
            mapUrl = DRILLDOWNDATA.settings.mapping_source_url + 'maps/world.geojson'

            // get geojson
            jQuery.getJSON( mapUrl, function( data ) {
                // Set map definition
                let mapData = data

                // prepare country/child data
                jQuery.each( mapData.features, function(i, v ) {
                    if ( map_data.children[v.id] !== undefined ) {
                        mapData.features[i].properties.geonameid = map_data.children[v.id].geonameid
                        mapData.features[i].properties.population = map_data.children[v.id].population


                        // custom columns
                        if ( DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid] ) {
                            jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                                mapData.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid][ii]
                                mapData.features[i].properties.value = mapData.features[i].properties[vv.key]
                            })
                        } else {
                            jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                                mapData.features[i].properties[vv.key] = 0
                                mapData.features[i].properties.value = 0
                            })
                        }
                    }
                })

                chart.geodata = mapData;

                // initialize polygonseries
                let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
                polygonSeries.exclude = ["AQ","GL"];
                polygonSeries.useGeodata = true;

                let template = polygonSeries.mapPolygons.template;

                // create tool tip
                let toolTipContent = `<strong>{name}</strong><br>
                            ---------<br>
                            Population: {population}<br>
                            `;
                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vc) {
                    toolTipContent += vc.label + ': {' + vc.key + '}<br>'
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

                var homeButton = new am4core.Button();
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

                    if( map_data.deeper_levels[ev.target.dataItem.dataContext.geonameid] )
                    {
                        jQuery('select#world option[value='+ev.target.dataItem.dataContext.geonameid+']').attr('selected', true)
                        return DRILLDOWN.geoname_drill_down( ev.target.dataItem.dataContext.geonameid, 'map_chart' )
                    }

                }, this);

                let coordinates = []
                coordinates[0] = {
                    "latitude": 0,
                    "longitude": 0,
                    "title": 'World'
                }
                mini_map( 'minimap', coordinates )


            }) // end success statement
                .fail(function (err) {
                    jQuery('#map_chart').empty().append(`No polygon available.`)
                    console.log(`No polygon available.`)
                })

            break;

        case 'country':
            console.log('top_level_map: country')

            if( Object.keys(top_map_list).length === 1 ) { // if only one country selected
                jQuery.each(top_map_list, function(i,v) {
                    geoname_map( div, i )
                })
            } else {
                // multiple countries selected. So load the world and reduce the polygons

                mapUrl = DRILLDOWNDATA.settings.mapping_source_url + 'maps/world.geojson'
                jQuery.getJSON( mapUrl, function( data ) {

                    // set title
                    title.empty().html('Multiple Countries')

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
                    title.empty()

                    // prepare country/child data
                    jQuery.each( mapData.features, function(i, v ) {

                        if ( DRILLDOWNDATA.data[v.properties.geonameid] !== undefined ) {
                            mapData.features[i].properties.geonameid = v.properties.geonameid
                            mapData.features[i].properties.population = DRILLDOWNDATA.data[v.properties.geonameid].self.population


                            // custom columns
                            if ( DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid] ) {
                                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                                    mapData.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid][ii]
                                    mapData.features[i].properties.value = mapData.features[i].properties[vv.key]
                                })
                            } else {
                                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                                    mapData.features[i].properties[vv.key] = 0
                                    mapData.features[i].properties.value = 0
                                })
                            }

                            title.append(DRILLDOWNDATA.data[v.properties.geonameid].self.name)
                            if ( title.html().length !== '' ) {
                                title.append(', ')
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
                            Population: {population}<br>
                            `;
                    jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vc) {
                        toolTipContent += vc.label + ': {' + vc.key + '}<br>'
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

                    var homeButton = new am4core.Button();
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
                            jQuery("select#drill_down_top_level option[value="+ev.target.dataItem.dataContext.geonameid+"]").attr('selected', true)
                            return DRILLDOWN.geoname_drill_down( ev.target.dataItem.dataContext.geonameid, 'map_chart' )
                        }
                    }, this);

                    mini_map( 'minimap', coordinates )

                }).fail(function (err) {
                    jQuery('#map_chart').empty().append(`No polygon available.`)
                    console.log(`No polygon available.`)
                })
            }

            break;

        case 'state':
            console.log('top_level_map: state')

            if( Object.keys(top_map_list).length === 1 ) { // if only one country selected
                jQuery.each(top_map_list, function(i,v) {
                    geoname_map( div, i )
                })
            } else {
                // multiple countries selected. So load the world and reduce the polygons

                mapUrl = DRILLDOWNDATA.settings.mapping_source_url + 'maps/' +default_map_settings.parent+ '.geojson'
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


                            // custom columns
                            if ( DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid] ) {
                                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                                    mapData.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid][ii]
                                    mapData.features[i].properties.value = mapData.features[i].properties[vv.key]
                                })
                            } else {
                                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                                    mapData.features[i].properties[vv.key] = 0
                                    mapData.features[i].properties.value = 0
                                })
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
                            Population: {population}<br>
                            `;
                    jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vc) {
                        toolTipContent += vc.label + ': {' + vc.key + '}<br>'
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

                    var homeButton = new am4core.Button();
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
                            jQuery("select#drill_down_top_level option[value="+ev.target.dataItem.dataContext.geonameid+"]").attr('selected', true)
                            return DRILLDOWN.geoname_drill_down( ev.target.dataItem.dataContext.geonameid, 'map_chart' )
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

function geoname_map( div, geonameid ) {
    am4core.useTheme(am4themes_animated);

    let chart = am4core.create( div, am4maps.MapChart);
    let title = jQuery('#section-title')
    let rest = DRILLDOWNDATA.settings.endpoints.get_map_by_geonameid_endpoint

    chart.projection = new am4maps.projections.Miller(); // Set projection

    title.empty()

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

            title.html(response.self.name)

            jQuery.getJSON( DRILLDOWNDATA.settings.mapping_source_url + 'maps/' + geonameid+'.geojson', function( data ) { // get geojson data

                // load geojson with additional parameters
                let mapData = data

                jQuery.each( mapData.features, function(i, v ) {
                    if ( response.children[mapData.features[i].properties.geonameid] !== undefined ) {

                        mapData.features[i].properties.population = response.children[mapData.features[i].properties.geonameid].population

                        // custom columns
                        if ( DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid] ) {
                            jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                                mapData.features[i].properties[vv.key] = DRILLDOWNDATA.data.custom_column_data[mapData.features[i].properties.geonameid][ii]
                                mapData.features[i].properties.value = mapData.features[i].properties[vv.key]
                            })
                        } else {
                            jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vv) {
                                mapData.features[i].properties[vv.key] = 0
                                mapData.features[i].properties.value = 0
                            })
                        }

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
                            Population: {population}<br>
                            `;
                jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii, vc) {
                    toolTipContent += vc.label + ': {' + vc.key + '}<br>'
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

                /* Click navigation */
                template.events.on("hit", function(ev) {
                    console.log(ev.target.dataItem.dataContext.geonameid)
                    console.log(ev.target.dataItem.dataContext.name)

                    if( response.deeper_levels[ev.target.dataItem.dataContext.geonameid] )
                    {
                        jQuery("select#"+response.self.geonameid+" option[value="+ev.target.dataItem.dataContext.geonameid+"]").attr('selected', true)
                        return DRILLDOWN.map_chart( ev.target.dataItem.dataContext.geonameid, div )
                    }
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
                jQuery('#map_chart').empty().append(`No polygon available.`)
            })
        }) // end success statement
        .fail(function (err) {

            console.log("error")
            console.log(err)
        })
}

function data_type_list( div ) {
    let list = jQuery('#'+div )
    jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(i,v) {
        list.append(`<a onclick="" class="button hollow" id="${v.key}">${v.label}</a>`)
    })
}

function load_breadcrumbs( div, id, parent_name ) {
    let separator = ` > `

    if ( DRILLDOWNDATA.breadcrumbs === undefined) {
        DRILLDOWNDATA.breadcrumbs = []
    }

    for(let i = 0; i < DRILLDOWNDATA.breadcrumbs.length; i++ ) {
        if ( DRILLDOWNDATA.breadcrumbs[i].id === id ) {
            let reset = DRILLDOWNDATA.breadcrumbs.slice(0,i)
            DRILLDOWNDATA.breadcrumbs = []
            DRILLDOWNDATA.breadcrumbs = reset
        }
    }

    DRILLDOWNDATA.breadcrumbs.push({id,parent_name})

    // clear breadcrumbs
    let content = jQuery('#breadcrumbs')
    content.empty()

    for(let i = 0; i < DRILLDOWNDATA.breadcrumbs.length; i++ ) {
        let separator = ` > `
        if ( i === 0 ) {
            separator = ''
        }
        if ( DRILLDOWNDATA.breadcrumbs[i].id === id ) {
            // DRILLDOWNDATA.breadcrumbs.slice(0,i)
            return false;
        }
        content.append(`<span id="${DRILLDOWNDATA.breadcrumbs[i].id}">${separator}<a onclick="map_chart('${div}', ${DRILLDOWNDATA.breadcrumbs[i].id} ) ">${DRILLDOWNDATA.breadcrumbs[i].parent_name}</a></span>`)
    }

    content.append(`<span id="${id}" data-value="${id}">${separator}<a onclick="map_chart('${div}', ${id} ) ">${parent_name}</a></span>`)

    console.log(DRILLDOWNDATA.breadcrumbs)

} // @todo remove?


function mini_map( div, marker_data ) {

    jQuery.getJSON( DRILLDOWNDATA.settings.mapping_source_url + 'maps/world.geojson', function( data ) {
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

        chart.geodata = data;
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
    })



}

function child_list( mapDiv, children, deeper_levels ) { /* @todo consider removing or widgetizing */

    if ( ! children ) {
        return false;
    }

    let container = jQuery('#child-list')
    container.empty()

   let sorted_children =  _.sortBy(children, [function(o) { return o.name; }]);

    jQuery.each( sorted_children, function( i, v ) {
        let button = `<button class="button small" type="button" onclick="map_chart( '${mapDiv}', ${v.geonameid} )">Drill Down</button>`
        if (! deeper_levels[v.geonameid]) {
            button = ''
        }

        container.append(`<li class="accordion-item" data-accordion-item>
                            <a href="#" class="accordion-title">${v.name}</a>
                            <div class="accordion-content" data-tab-content>
                              <p>population: ${v.population}</p>
                              <p>workers: 0</p>
                              <p>contacts: 0</p>
                              <p>groups: 0</p>
                              <p>${button}</p>
                            </div>
                          </li>`)
    })

    // var e = document.getElementById('child-list-container');
    // e.scrollTop = 0;

    var elem = new Foundation.Accordion(container);
} // @todo remove?

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
            <div class="cell auto">
                <!-- Drill Down -->
                <ul id="drill_down"></ul>
            </div>
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
    window.DRILLDOWN.load_drill_down( null, 'location_list' )
}

function location_list( div, geonameid ) {
    let default_map_settings = DRILLDOWNDATA.settings.default_map_settings

    /*******************************************************************************************************************
     *
     * Load Requested Geonameid
     *
     *****************************************************************************************************************/
    if ( geonameid ) { // make sure this is not a top level continent or world request
        console.log('location_list: geonameid available')
        geoname_list( div, geonameid )
    }
    /*******************************************************************************************************************
     *
     * Initialize Country Based Top Level Maps
     *
     *****************************************************************************************************************/
    else if ( default_map_settings.type === 'country' ) {
        console.log('location_list: country available')
        if( Object.keys(default_map_settings.children).length === 1 ) {
            geoname_list( div, default_map_settings.children[0] )
        }
    }
    else if ( default_map_settings.type === 'state' ) {
        console.log('location_list: country available')
        if( Object.keys(default_map_settings.children).length === 1 ) {
            geoname_list( div, default_map_settings.children[0] )
        } else {
            geoname_list( div, default_map_settings.parent )
        }
    }
    /*******************************************************************************************************************
     *
     * Initialize Top Level Maps
     *
     *****************************************************************************************************************/
    else { // top_level maps
        top_level_location_list( div )
    } // end if
}

function top_level_location_list( div ) {
    let default_map_settings = DRILLDOWNDATA.settings.default_map_settings
    DRILLDOWN.show_spinner()

    // Initialize Location Data
    let map_data = DRILLDOWNDATA.data[default_map_settings.parent]
    if ( map_data === undefined ) {
        console.log('error getting map_data')
        return;
    }

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
    jQuery('#current_level').empty().html(`Population: ${self_population}`)


    // Build List
    let locations = jQuery('#location_list')
    locations.empty()

    // Header Section
    let header = `<div class="grid-x grid-padding-x grid-padding-y" style="border-bottom:1px solid grey">
                    <div class="cell small-3">Name</div>
                    <div class="cell small-3">Population</div>`

        /* Additional Columns */
        if ( DRILLDOWNDATA.data.custom_column_labels ) {
            jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(i,v) {
                header += `<div class="cell small-3">${v}</div>`
            })
        }
        /* End Additional Columns */

    header += `</div>`
    locations.empty().append( header )

    // Children List Section

    let sorted_children =  _.sortBy(map_data.children, [function(o) { return o.name; }]);

    jQuery.each( sorted_children, function(i, v) {
        let population =  v.population_formatted
        let html = `<div class="grid-x grid-padding-x grid-padding-y">
                        <div class="cell small-3"><strong>${v.name}</strong></div>
                        <div class="cell small-3">${population}</div>`


        /* Additional Columns */
        if ( DRILLDOWNDATA.data.custom_column_data[i] ) {
            jQuery.each( DRILLDOWNDATA.data.custom_column_data[i], function(ii,v) {
                html += `<div class="cell small-3">${v}</div>`
            })
        } else {
            jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(ii,v) {
                html += `<div class="cell small-3"></div>`
            })
        }
        /* End Additional Columns */

        html += `</div>`
        locations.append(html)
    })

    DRILLDOWN.hide_spinner()
}

function geoname_list( div, geonameid ) {
    DRILLDOWNDATA.settings.hide_final_drill_down = true
    DRILLDOWN.show_spinner()
    if ( DRILLDOWNDATA.data[geonameid] === undefined ) {
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
        jQuery('#current_level').empty().html(`Population: ${self_population}`)

        // Build List
        let locations = jQuery('#location_list')
        locations.empty()

        let html = `<table id="country-list-table" class="display">`

        // Header Section
        html += `<thead><tr><th>Name</th><th>Population</th>`

        /* Additional Columns */
        if ( DRILLDOWNDATA.data.custom_column_labels ) {
            jQuery.each( DRILLDOWNDATA.data.custom_column_labels, function(i,v) {
                html += `<th>${v.label}</th>`
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
                        <td><strong>${v.name}</strong></td>
                        <td>${population}</td>`

            /* Additional Columns */
            if ( DRILLDOWNDATA.data.custom_column_data[v.geonameid] ) {
                jQuery.each( DRILLDOWNDATA.data.custom_column_data[v.geonameid], function(ii,vv) {
                    html += `<td><strong>${vv}</strong></td>`
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
