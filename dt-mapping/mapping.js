jQuery(document).ready(function() {
    let mapping_module = mappingModule.mapping_module
    if('#mapping_view' === window.location.hash) {
        console.log(mapping_module)
        page_mapping_view()
    }
    if('#mapping_list' === window.location.hash) {
        console.log(mapping_module)
        page_mapping_list()
    }
})

/**********************************************************************************************************************
 *
 * VISUAL MAP
 *
 * This displays a vision map and allows for drill down through clicking on map sections.
 *
 **********************************************************************************************************************/
function page_mapping_view() {
    "use strict";
    let mapping_module = mappingModule.mapping_module
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        
        <div class="grid-x grid-margin-y">
            <div class="cell auto">
                <!-- Drill Down -->
                <ul id="drill_down">
                    
                </ul>
                <!-- Breadcrumbs -->
                <!--<div id="breadcrumbs">-->
                    <!--<span id="world"><a onclick="map_chart( 'locations-home', false ) ">World</a></span>-->
                <!--</div>-->
            </div>
            <div class="cell medium-4" style="text-align:right;">
               <strong id="section-title" style="font-size:2em;"></strong><br>
                <span id="current_level"></span>
            </div>
            <!--<div class="cell medium-4">-->
                <!--&lt;!&ndash; Dropdown &ndash;&gt;-->
                <!--<span id="dropdown-box-container" class="ui-widget" style="float:right;margin-right:30px;"></span>-->
            <!--</div>-->
        </div>
        
        
        <hr style="max-width:100%;">
        
        <!-- Section Title -->
        <div class="grid-x"></div>
       
       <!-- Map -->
       <div class="grid-x grid-margin-x">
            <div class="cell">
                <div id="minimap" style="position:absolute;z-index:1001;float:right;width:200px; margin-top: 543px;"></div>
                <div id="locations-home" style="width: 100%;max-height: 700px;height: 100vh;vertical-align: text-top;"></div>
            </div>
            <!--<div class="cell medium-2 left-border-grey">-->
                <!--<div class="grid-y">-->
                    <!---->
                    <!--<div class="cell" style="overflow-y: scroll; height:700px;" id="child-list-container">-->
                        <!--<ul class="accordion" data-accordion id="child-list">-->
                        <!--</ul>-->
                    <!--</div>-->
                <!--</div>-->
            <!--</div>-->
        </div>
        
        <hr style="max-width:100%;">
        <div id="page-header" style="float:left;">
            <strong id="section-title" style="font-size:1.5em;"></strong><br>
            <span id="current_level"></span>
        </div>
        
        <div id="location_list"></div>
        
        <hr style="max-width:100%;">
        
        
        
        <span style="float:right;font-size:.8em;"><a onclick="map_chart( 'locations-home' )" >return to world view</a></span>
        
        <br>
        
        <style>/* custom css for dropdown box */
          .custom-combobox {
            position: relative;
            display: inline-block;
          }
          .custom-combobox-toggle {
            position: absolute;
            top: 0;
            bottom: 0;
            margin-left: -1px;
            padding: 0;
          }
          .custom-combobox-input {
            margin: 0;
            padding:5px 10px;
          }
          
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
            #drill_down {
                margin-bottom: 0;
                list-style-type: none;
            }
            #drill_down li {
                display:inline;
                padding: 0 10px;
            }
            #drill_down li select {
                width:150px;
            }
          </style>
        `);

    map_chart( 'locations-home' )
    load_drill_down( 'drill_down' )
}

function map_chart( div, geonameid ) {
    let mapping_module = mappingModule.mapping_module
    let initial_map_level = mapping_module.data.initial_map_level

    /*******************************************************************************************************************
     *
     * Load Requested Geonameid
     *
     *****************************************************************************************************************/
    if ( geonameid ) { // make sure this is not a top level continent or world request
        console.log('geonameid available')
        geoname_map( div, geonameid )
    }
    /*******************************************************************************************************************
     *
     * Initialize Country Based Top Level Maps
     *
     *****************************************************************************************************************/
    else if ( initial_map_level.type === 'country' ) {
        console.log('country available')
        geoname_map( div, initial_map_level.geonameid )
    }
    /*******************************************************************************************************************
     *
     * Initialize Top Level Maps
     *
     *****************************************************************************************************************/
    else { // top_level maps
        console.log('top level')
        top_level_map( div )
    } // end if

}

function top_level_map( div ) {
    let mapping_module = mappingModule.mapping_module
    am4core.useTheme(am4themes_animated);

    let chart = am4core.create( div, am4maps.MapChart);
    let initial_map_level = mapping_module.data.initial_map_level

    chart.projection = new am4maps.projections.Miller(); // Set projection

    let start_level = mapping_module.data.start_level
    let title = jQuery('#section-title')

    title.empty().html(start_level.self.name)

    // sort custom start level url
    let mapUrl = ''
    if ( mapping_module.data.start_level.self.unique_source_url /* This is available only for top level */ ) {
        mapUrl = mapping_module.data.start_level.self.url
    } else {
        mapUrl = mapping_module.mapping_source_url + 'top_level_maps/' + initial_map_level.geonameid + '.geojson'
    }

    // get geojson
    jQuery.getJSON( mapUrl, function( data ) {
        // Set map definition
        let map_data = data
        let custom_label = ''

        // prepare country/child data
        jQuery.each( map_data.features, function(i, v ) {
            if ( start_level.children[v.id] !== undefined ) {
                map_data.features[i].properties.geonameid = start_level.children[v.id].geonameid
                map_data.features[i].properties.population = start_level.children[v.id].population
                map_data.features[i].properties.value = start_level.children[v.id].population
                map_data.features[i].properties.fill = start_level.children[v.id].fill

                if ( mapping_module.data.custom_column_data[i] ) {
                    console.log('test')
                    jQuery.each( mapping_module.data.custom_column_data[i], function(i,v) {
                        custom_label = mapping_module.data.custom_column_labels[i]
                        map_data.features[i].properties[custom_label] = v
                    })

                } else {
                    jQuery.each( mapping_module.data.custom_column_data[i], function(i,v) {
                        custom_label = mapping_module.data.custom_column_labels[i]
                        map_data.features[i].properties[custom_label] = 0
                    })
                }

            }
        })

        chart.geodata = map_data;

        // initialize polygonseries
        let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
        polygonSeries.exclude = ["AQ","GL"];
        polygonSeries.useGeodata = true;

        let template = polygonSeries.mapPolygons.template;
        template.tooltipHTML = `<strong>{name}</strong><br>
                ---------<br>
                population: {population}<br>
                workers: {workers}<br> 
                groups: {groups}<br> 
                contacts: {contacts}<br> 
                `;
        template.propertyFields.fill = "fill";
        polygonSeries.tooltip.label.interactionsEnabled = true;
        polygonSeries.tooltip.pointerOrientation = "vertical";

        polygonSeries.heatRules.push({
            property: "fill",
            target: template,
            min: chart.colors.getIndex(1).brighten(1),
            max: chart.colors.getIndex(1).brighten(-0.3)
        });

        /* Click navigation */
        template.events.on("hit", function(ev) {
            console.log(ev.target.dataItem.dataContext.name)
            console.log(ev.target.dataItem.dataContext.geonameid)

            if( start_level.deeper_levels[ev.target.dataItem.dataContext.geonameid] )
            {
                return map_chart( div, ev.target.dataItem.dataContext.geonameid )
            }

        }, this);

        // update breadcrumbs
        load_breadcrumbs( div, false, start_level.self.name )
        // add dropdown box
        load_dropdown_content( div, start_level.children, start_level.deeper_levels )

        if ( start_level.self.geonameid !== 6295630 ) {
            mini_map( 'minimap', start_level.self.name, start_level.self.latitude, start_level.self.longitude )
        }

        // child_list( div, start_level.children, start_level.deeper_levels )

    }) // end success statement
        .fail(function (err) {
            console.log("error")
            console.log(err)
        })
}

function geoname_map( div, geonameid ) {
    let mapping_module = mappingModule.mapping_module
    am4core.useTheme(am4themes_animated);

    let chart = am4core.create( div, am4maps.MapChart);
    // let initial_map_level = mapping_module.data.initial_map_level
    let title = jQuery('#section-title')
    let rest = mapping_module.endpoints.map_level_endpoint

    chart.projection = new am4maps.projections.Miller(); // Set projection

    title.empty()
    jQuery.ajax({
        type: rest.method,
        contentType: "application/json; charset=utf-8",
        data: JSON.stringify( { 'geonameid': geonameid } ),
        dataType: "json",
        url: mapping_module.root + rest.namespace + rest.route,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', rest.nonce);
        },
    })
        .done( function( response ) {

            title.html(response.self.name)
            console.log(response)

            jQuery.getJSON( mapping_module.mapping_source_url + 'maps/' + geonameid+'.geojson', function( data ) { // get geojson data

                // load geojson with additional parameters
                let map_data = data
                let custom_label = ''
                jQuery.each( map_data.features, function(i, v ) {
                    if ( response.children[map_data.features[i].properties.geonameid] !== undefined ) {

                        map_data.features[i].properties.population = response.children[map_data.features[i].properties.geonameid].population
                        map_data.features[i].properties.value = response.children[map_data.features[i].properties.geonameid].population
                        map_data.features[i].properties.fill = response.children[map_data.features[i].properties.geonameid].fill

                        // custom columns
                        if ( mapping_module.data.custom_column_data[map_data.features[i].properties.geonameid] ) {
                            console.log( 'found geonameid')
                            jQuery.each( mapping_module.data.custom_column_labels, function(ii, vv) {
                                map_data.features[i].properties[vv.key] = mapping_module.data.custom_column_data[map_data.features[i].properties.geonameid][ii]
                            })
                        } else {
                            jQuery.each( mapping_module.data.custom_column_labels, function(ii, vv) {
                                map_data.features[i].properties[vv.key] = 0
                            })
                        }

                    }
                })

                // create polygon series
                let polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());
                polygonSeries.geodata = map_data
                polygonSeries.useGeodata = true;
                console.log(map_data)

                /* Heat map @see https://www.amcharts.com/demos/us-heat-map/ */
                polygonSeries.heatRules.push({
                    property: "fill",
                    target: polygonSeries.mapPolygons.template,
                    min: chart.colors.getIndex(1).brighten(1),
                    max: chart.colors.getIndex(1).brighten(-0.3)
                });

                // Configure series tooltip
                let template = polygonSeries.mapPolygons.template;

                // create tool tip
                let toolTipContent = `<strong>{name}</strong><br>
                            ---------<br>
                            Population: {population}<br>
                            `;
                jQuery.each( mapping_module.data.custom_column_labels, function(ii, vc) {
                    toolTipContent += vc.label + ': {' + vc.key + '}<br>'
                })
                template.tooltipHTML = toolTipContent

                // Create hover state and set alternative fill color
                let hs = template.states.create("hover");
                hs.properties.fill = am4core.color("#3c5bdc");


                /* Click navigation */
                template.events.on("hit", function(ev) {
                    console.log(ev.target.dataItem.dataContext.geonameid)
                    console.log(ev.target.dataItem.dataContext.name)

                    if( response.deeper_levels[ev.target.dataItem.dataContext.geonameid] )
                    {
                        return map_chart( div, ev.target.dataItem.dataContext.geonameid)
                    }
                }, this);

                // update breadcrumbs
                load_breadcrumbs( div, response.self.geonameid, response.self.name )
                // refresh and add dropdown box
                load_dropdown_content( div, response.children, response.deeper_levels )

                mini_map( 'minimap', response.self.name, response.self.latitude, response.self.longitude )

                child_list( div, response.children, response.deeper_levels)

            }) // end get geojson
        }) // end success statement
        .fail(function (err) {
            console.log("error")
            console.log(err)
        })
}

function load_breadcrumbs( div, id, parent_name ) {
    let mapping_module = mappingModule.mapping_module
    let separator = ` > `

    if ( mapping_module.breadcrumbs === undefined) {
        mapping_module.breadcrumbs = []
    }

    for(let i = 0; i < mapping_module.breadcrumbs.length; i++ ) {
        if ( mapping_module.breadcrumbs[i].id === id ) {
            let reset = mapping_module.breadcrumbs.slice(0,i)
            mapping_module.breadcrumbs = []
            mapping_module.breadcrumbs = reset
        }
    }

    mapping_module.breadcrumbs.push({id,parent_name})

    // clear breadcrumbs
    let content = jQuery('#breadcrumbs')
    content.empty()

    for(let i = 0; i < mapping_module.breadcrumbs.length; i++ ) {
        let separator = ` > `
        if ( i === 0 ) {
            separator = ''
        }
        if ( mapping_module.breadcrumbs[i].id === id ) {
            // mapping_module.breadcrumbs.slice(0,i)
            return false;
        }
        content.append(`<span id="${mapping_module.breadcrumbs[i].id}">${separator}<a onclick="map_chart('${div}', ${mapping_module.breadcrumbs[i].id} ) ">${mapping_module.breadcrumbs[i].parent_name}</a></span>`)
    }

    content.append(`<span id="${id}" data-value="${id}">${separator}<a onclick="map_chart('${div}', ${id} ) ">${parent_name}</a></span>`)

    console.log(mapping_module.breadcrumbs)

}

function load_dropdown_content( div, locations, deeper_levels ) {
    let mapping_module = mappingModule.mapping_module
    let input_select = `<select id="combobox" style="display:none;"><option value="">Deeper Levels</option>`

    jQuery.each( locations, function( i, v ) {
        if ( deeper_levels[v.geonameid] ) {
            input_select += `<option value="${v.geonameid }">${v.name}</option>`
        }
    })

    input_select += `</select>`

    jQuery('#dropdown-box-container').empty().html(input_select)

    setup_dropdown_script( div )
}

function setup_dropdown_script( div ) {
    let mapping_module = mappingModule.mapping_module
    /* Supports for combo box dropdown */
    jQuery(document).ready(function () {
        jQuery.widget("custom.combobox", {
            _create: function () {
                this.wrapper = jQuery("<span>")
                    .addClass("custom-combobox")
                    .insertAfter(this.element);

                this.element.hide();
                this._createAutocomplete();
                this._createShowAllButton();
            },

            _createAutocomplete: function () {
                var selected = this.element.children(":selected"),
                    value = selected.val() ? selected.val() : "";

                this.input = jQuery("<input>")
                    .appendTo(this.wrapper)
                    .val(value)
                    .attr("title", "")
                    .addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left")
                    .autocomplete({
                        delay: 0,
                        minLength: 0,
                        source: jQuery.proxy(this, "_source")
                    })
                    .tooltip({
                        classes: {
                            "ui-tooltip": "ui-state-highlight"
                        }
                    });

                this._on(this.input, {
                    autocompleteselect: function (event, ui) {
                        /* call new map chart */
                        console.log( ui.item.option.value )
                        console.log( ui.item.option.text )
                        map_chart( div, ui.item.option.value )
                    },

                    autocompletechange: "_removeIfInvalid"
                });
            },

            _createShowAllButton: function () {
                var input = this.input,
                    wasOpen = false;

                jQuery("<a>")
                    .attr("tabIndex", -1)
                    // .attr("title", "Show All Items")
                    .tooltip()
                    .appendTo(this.wrapper)
                    .button({
                        icons: {
                            primary: "ui-icon-triangle-1-s"
                        },
                        text: false
                    })
                    .removeClass("ui-corner-all")
                    .addClass("custom-combobox-toggle ui-corner-right")
                    .on("mousedown", function () {
                        wasOpen = input.autocomplete("widget").is(":visible");
                    })
                    .on("click", function () {
                        input.trigger("focus");

                        // Close if already visible
                        if (wasOpen) {
                            return;
                        }

                        // Pass empty string as value to search for, displaying all results
                        input.autocomplete("search", "");
                    });
            },

            _source: function (request, response) {
                var matcher = new RegExp(jQuery.ui.autocomplete.escapeRegex(request.term), "i");
                response(this.element.children("option").map(function () {
                    var text = jQuery(this).text();
                    if (this.value && (!request.term || matcher.test(text)))
                        return {
                            label: text,
                            value: text,
                            option: this
                        };
                }));
            },

            _removeIfInvalid: function (event, ui) {

                // Selected an item, nothing to do
                if (ui.item) {
                    return;
                }

                // Search for a match (case-insensitive)
                var value = this.input.val(),
                    valueLowerCase = value.toLowerCase(),
                    valid = false;
                this.element.children("option").each(function () {
                    if (jQuery(this).text().toLowerCase() === valueLowerCase) {
                        this.selected = valid = true;
                        return false;
                    }
                });

                // Found a match, nothing to do
                if (valid) {
                    return;
                }

                // Remove invalid value
                this.input
                    .val("")
                    .attr("title", value + " didn't match any item")
                    .tooltip("open");
                this.element.val("");
                this._delay(function () {
                    this.input.tooltip("close").attr("title", "");
                }, 2500);
                this.input.autocomplete("instance").term = "";
            },

            _destroy: function () {
                this.wrapper.remove();
                this.element.show();
            }
        });

        jQuery("#combobox").combobox();
        jQuery('.custom-combobox input.custom-combobox-input').prop('placeholder', 'Deeper Levels')

    })
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function mini_map( div, name, lat, lng ) {
    let mapping_module = mappingModule.mapping_module

    jQuery.getJSON( mapping_module.mapping_source_url + 'top_level_maps/world.geojson', function( data ) {
        am4core.useTheme(am4themes_animated);

        var chart = am4core.create( div, am4maps.MapChart);

        chart.projection = new am4maps.projections.Orthographic(); // Set projection

        chart.seriesContainer.draggable = false;
        chart.seriesContainer.resizable = false;

        // chart.deltaLongitude = parseInt(lng);
        if (  parseInt(lng) < 0 ) {
            chart.deltaLongitude = parseInt(Math.abs(lng));
        } else {
            chart.deltaLongitude = parseInt(-Math.abs(lng));
        }


        chart.geodata = data;
        var polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());

        polygonSeries.exclude = ["AQ",];
        polygonSeries.useGeodata = true;

        var imageSeries = chart.series.push(new am4maps.MapImageSeries());

        var imageSeriesTemplate = imageSeries.mapImages.template;
        var circle = imageSeriesTemplate.createChild(am4core.Circle);
        circle.radius = 4;
        circle.fill = am4core.color("#B27799");
        circle.stroke = am4core.color("#FFFFFF");
        circle.strokeWidth = 2;
        circle.nonScaling = true;
        // circle.tooltipText = "{title}";

        imageSeriesTemplate.propertyFields.latitude = "latitude";
        imageSeriesTemplate.propertyFields.longitude = "longitude";

        imageSeries.data = [{
            "latitude": lat,
            "longitude": lng,
            "title": name
        }];
    })



}

function child_list( mapDiv, children, deeper_levels ) {

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

    var e = document.getElementById('child-list-container');
    e.scrollTop = 0;

    var elem = new Foundation.Accordion(container);
}

/**********************************************************************************************************************
 *
 * MAP LIST
 *
 * This page allows for drill-down into the locations and related reports.
 * 
 **********************************************************************************************************************/
function page_mapping_list() {
    "use strict";
    let mapping_module = mappingModule.mapping_module
    let chartDiv = jQuery('#chart')
    chartDiv.empty().html(`
        <div class="grid-x grid-margin-x">
            <div class="cell auto">
                <!-- Drill Down -->
                <ul id="drill_down">
                    
                </ul>
            </div>
            <div class="cell small-1">
                <span id="spinner" style="display:none;" class="float-right">${mapping_module.spinner_large}</span>
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
            #drill_down {
                margin-bottom: 0;
                list-style-type: none;
            }
            #drill_down li {
                display:inline;
                padding: 0 10px;
            }
            #drill_down li select {
                width:150px;
            }
        </style>
        `);
    load_drill_down( 'drill_down' )
}

function location_list( div, geonameid ) {
    let mapping_module = mappingModule.mapping_module
    let initial_map_level = mapping_module.data.initial_map_level

    /*******************************************************************************************************************
     *
     * Load Requested Geonameid
     *
     *****************************************************************************************************************/
    if ( geonameid ) { // make sure this is not a top level continent or world request
        console.log('geonameid available')
        geoname_list( div, geonameid )
    }
    /*******************************************************************************************************************
     *
     * Initialize Country Based Top Level Maps
     *
     *****************************************************************************************************************/
    else if ( initial_map_level.type === 'country' ) {
        console.log('country available')
        geoname_list( div, initial_map_level.geonameid )
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
    let mapping_module = mappingModule.mapping_module
    show_spinner()

    // Initialize Location Data
    let start_level = mapping_module.data.start_level

    // Place Title
    let title = jQuery('#section-title')
    title.empty().html(start_level.self.name)

    // Population Division and Check for Custom Division
    let pd_settings = mapping_module.data.population_division
    let population_division = pd_settings.base
    if ( ! isEmpty( pd_settings.custom ) ) {
        jQuery.each( pd_settings.custom, function(i,v) {
            if ( start_level.self.geonameid === i ) {
                population_division = v
            }
        })
    }

    // Self Data
    let self_population = numberWithCommas( start_level.self.population )
    jQuery('#current_level').empty().html(`Population: ${self_population}`)


    // Build List
    let locations = jQuery('#location_list')
    locations.empty()

    // Header Section
    let header = `<div class="grid-x grid-padding-x grid-padding-y" style="border-bottom:1px solid grey">
                    <div class="cell small-3">Name</div>
                    <div class="cell small-3">Population</div>`

        /* Additional Columns */
        if ( mapping_module.data.custom_column_labels ) {
            jQuery.each( mapping_module.data.custom_column_labels, function(i,v) {
                header += `<div class="cell small-3">${v}</div>`
            })
        }
        /* End Additional Columns */

    header += `</div>`
    locations.empty().append( header )

    // Children List Section

    let sorted_children =  _.sortBy(start_level.children, [function(o) { return o.name; }]);

    jQuery.each( sorted_children, function(i, v) {
        let population = numberWithCommas( v.population )
        let html = `<div class="grid-x grid-padding-x grid-padding-y">
                        <div class="cell small-3"><strong>${v.name}</strong></div>
                        <div class="cell small-3">${population}</div>`


        /* Additional Columns */
        if ( mapping_module.data.custom_column_data[i] ) {
            jQuery.each( mapping_module.data.custom_column_data[i], function(ii,v) {
                html += `<div class="cell small-3">${v}</div>`
            })
        } else {
            jQuery.each( mapping_module.data.custom_column_labels, function(ii,v) {
                html += `<div class="cell small-3"></div>`
            })
        }
        /* End Additional Columns */

        html += `</div>`
        locations.append(html)
    })

    hide_spinner()
}

function geoname_list( div, geonameid ) {
    let mapping_module = mappingModule.mapping_module
    show_spinner()
    if ( mapping_module.data[geonameid] === undefined ) {
        let rest = mapping_module.endpoints.map_level_endpoint

        jQuery.ajax({
            type: rest.method,
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify( { 'geonameid': geonameid } ),
            dataType: "json",
            url: mapping_module.root + rest.namespace + rest.route,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', rest.nonce );
            },
        })
            .done( function( response ) {
                mapping_module.data[geonameid] = response
                build_geoname_list( div, mapping_module.data[geonameid] )
            })
            .fail(function (err) {
                console.log("error")
                console.log(err)
                hide_spinner()
            })

    } else {
        build_geoname_list( div, mapping_module.data[geonameid] )
    }

    function build_geoname_list( div, start_level ) {

        // Place Title
        let title = jQuery('#section-title')
        title.empty().html(start_level.self.name)

        // Population Division and Check for Custom Division
        let pd_settings = mapping_module.data.population_division
        let population_division = pd_settings.base
        if ( ! isEmpty( pd_settings.custom ) ) {
            jQuery.each( pd_settings.custom, function(i,v) {
                if ( start_level.self.geonameid === i ) {
                    population_division = v
                }
            })
        }

        // Self Data
        let self_population = numberWithCommas( start_level.self.population )
        jQuery('#current_level').empty().html(`Population: ${self_population}`)

        // Build List
        let locations = jQuery('#location_list')
        locations.empty()

        let html = `<table id="country-list-table" class="display">`

        // Header Section
        html += `<thead><tr><th>Name</th><th>Population</th>`

        /* Additional Columns */
        if ( mapping_module.data.custom_column_labels ) {
            jQuery.each( mapping_module.data.custom_column_labels, function(i,v) {
                html += `<th>${v.label}</th>`
            })
        }
        /* End Additional Columns */

        html += `</tr></thead>`
        // End Header Section

        // Children List Section
        let sorted_children =  _.sortBy(start_level.children, [function(o) { return o.name; }]);

        html += `<tbody>`
        jQuery.each( sorted_children, function(i, v) {
            let population = numberWithCommas( v.population )
            html += `<tr>
                        <td><strong>${v.name}</strong></td>
                        <td>${population}</td>`

            /* Additional Columns */
            if ( mapping_module.data.custom_column_data[v.geonameid] ) {
                jQuery.each( mapping_module.data.custom_column_data[v.geonameid], function(ii,vv) {
                    html += `<td><strong>${vv}</strong></td>`
                })
            } else {
                jQuery.each( mapping_module.data.custom_column_labels, function(ii,vv) {
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

       hide_spinner()
    }
}

function load_drill_down( div, geonameid ) {
    let mapping_module = mappingModule.mapping_module
    let initial_map_level = mapping_module.data.initial_map_level

    /*******************************************************************************************************************
     *
     * Load Requested Geonameid
     *
     *****************************************************************************************************************/
    if ( geonameid ) { // make sure this is not a top level continent or world request
        geoname_drill_down( div, geonameid )
    }
    /*******************************************************************************************************************
     *
     * Initialize Country Based Top Level Maps
     *
     *****************************************************************************************************************/
    else if ( initial_map_level.type === 'country' ) {
        top_level_drill_down( div )
        // geoname_drill_down( div, initial_map_level.geonameid )
    }
    /*******************************************************************************************************************
     *
     * Initialize Top Level Maps
     *
     *****************************************************************************************************************/
    else { // top_level maps
        top_level_drill_down( div )
    } // end if
}

function top_level_drill_down( div ) {
    let mapping_module = mappingModule.mapping_module
    show_spinner()

    jQuery('#'+div).empty().append(`<li>${mapping_module.data.start_level.self.name}</li><li><select id="${mapping_module.data.start_level.self.geonameid}" onchange="geoname_drill_down( '${div}', this.value );jQuery(this).parent().nextAll().remove();"><option>Select</option></select></li>`)

    jQuery.each( mapping_module.data.start_level.children, function(i,v) {
        jQuery('#'+mapping_module.data.start_level.self.geonameid).append(`<option value="${v.id}">${v.name}</option>`)
    })

    bind_drill_down( mapping_module.data.start_level.self.geonameid )

    hide_spinner()
}

function geoname_drill_down( div, id ) {
    let mapping_module = mappingModule.mapping_module
    show_spinner()
    let rest = mapping_module.endpoints.map_level_endpoint

    let drill_down = jQuery('#'+div)

    jQuery.ajax({
        type: rest.method,
        contentType: "application/json; charset=utf-8",
        data: JSON.stringify( { 'geonameid': id } ),
        dataType: "json",
        url: mapping_module.root + rest.namespace + rest.route,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', rest.nonce );
        },
    })
        .done( function( response ) {
            console.log(response)
            mapping_module.data[response.self.geonameid] = response

            if ( ! isEmpty( response.children ) ) {
                drill_down.append(`<li><select id="${response.self.geonameid}" onchange="geoname_drill_down( '${div}', this.value );jQuery(this).parent().nextAll().remove();"><option>Select</option></select></li>`)
                let sorted_children =  _.sortBy(response.children, [function(o) { return o.name; }]);

                jQuery.each( sorted_children, function(i,v) {
                    jQuery('#'+id).append(`<option value="${v.id}">${v.name}</option>`)
                })
            }

            bind_drill_down( response.self.geonameid )

            hide_spinner()
        }) // end success statement
        .fail(function (err) {
            console.log("error")
            console.log(err)
            hide_spinner()
        })
}

function isEmpty(obj) {
    for(let key in obj) {
        if(obj.hasOwnProperty(key))
            return false;
    }
    return true;
}

function show_spinner() {
    jQuery('#spinner').show()
}

function hide_spinner() {
    jQuery('#spinner').hide()
}

function bind_drill_down( geonameid ) {
    location_list( 'location_list', geonameid )
}