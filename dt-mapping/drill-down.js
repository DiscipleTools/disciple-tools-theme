/* global jQuery:false, wpApiShare:false */
_ = _ || window.lodash // make sure lodash is defined so plugins like gutenberg don't break it.

window.DRILLDOWNDATA = mappingModule.mapping_module
window.DRILLDOWN = {

    load_drill_down( geonameid, bindFunction ) {
        if ( geonameid ) {
            DRILLDOWN.geoname_drill_down( geonameid, bindFunction )
        }
        else {
            DRILLDOWN.top_level_drill_down( bindFunction )
        }
    },

    top_level_drill_down( bindFunction ) {
        let top_map_list = DRILLDOWNDATA.data.top_map_list
        let drill_down = jQuery('#drill_down')

        DRILLDOWN.show_spinner()

        drill_down.empty().append(`<li><select id="drill_down_top_level" onchange="DRILLDOWN.geoname_drill_down( this.value, '${bindFunction}' );jQuery(this).parent().nextAll().remove();"><option value=" "></option></select></li>`)
        let drill_down_select = jQuery('#drill_down_top_level')

        if( Object.keys(top_map_list).length === 1 ) { // single top level
            jQuery.each(top_map_list, function(i,v) {
                drill_down_select.append(`<option value="${i}" selected>${v}</option>`)

                if ( ! DRILLDOWN.isEmpty( DRILLDOWNDATA.data[i].children ) ) {

                    drill_down.append(`<li><select id="${i}" onchange="DRILLDOWN.geoname_drill_down( this.value, '${bindFunction}' );jQuery(this).parent().nextAll().remove();"><option value="${i}"></option></select></li>`)

                    let sorted_children = _.sortBy(DRILLDOWNDATA.data[i].children, [function (o) {
                        return o.name;
                    }]);

                    jQuery.each(sorted_children, function (ii, vv) {
                        jQuery('#' + i).append(`<option value="${vv.id}">${vv.name}</option>`)
                    })

                    if ( typeof DRILLDOWN[bindFunction] !== "undefined" ) {
                        DRILLDOWN[bindFunction]( i )
                    }
                }
            })
        } else { // multi-top level
            drill_down_select.append(`<option value=""></option>`)

            jQuery.each(top_map_list, function(i,v) {
                drill_down_select.append(`<option value="${i}">${v}</option>`)
            })

            if (typeof DRILLDOWN[bindFunction] !== "undefined" ) {
                DRILLDOWN[bindFunction]( 'top_map_list' )
            }
        }

        DRILLDOWN.hide_spinner()
    },

    geoname_drill_down( geonameid, bindFunction ) {
        let rest = DRILLDOWNDATA.settings.endpoints.get_map_by_geonameid_endpoint
        let drill_down = jQuery('#drill_down')

        DRILLDOWN.show_spinner()

        if ( geonameid !== undefined  && geonameid !== ' ' ) {
            DRILLDOWNDATA.settings.current_map = geonameid

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
                    console.log(response)
                    DRILLDOWNDATA.data[geonameid] = response

                    if ( ! DRILLDOWN.isEmpty( response.children ) ) {
                        /* Hide next level drill down if 'hide_final_drill_down' is set to true. This can be defined externally. @see example mapping-admin.php:858 */
                        if ( ! DRILLDOWN.isEmpty( response.deeper_levels ) || ! DRILLDOWNDATA.settings.hide_final_drill_down === true ) {
                            drill_down.append(`<li><select id="${response.self.geonameid}" class="geocode-select" onchange="DRILLDOWN.geoname_drill_down( this.value, '${bindFunction}' );jQuery(this).parent().nextAll().remove();"><option value="${response.self.geonameid}"></option></select></li>`)
                            let sorted_children =  _.sortBy(response.children, [function(o) { return o.name; }]);

                            jQuery.each( sorted_children, function(i,v) {
                                jQuery('#'+geonameid).append(`<option value="${v.id}">${v.name}</option>`)
                            })
                        }
                    }

                    if ( typeof DRILLDOWN[bindFunction] !== "undefined" ) {
                        DRILLDOWN[bindFunction]( geonameid )
                    }

                }) // end success statement
                .fail(function (err) {
                    console.log("error")
                    console.log(err)
                })
        }

        DRILLDOWN.hide_spinner()
    },

    get_drill_down( bind_function, geonameid ) {
    if ( ! geonameid ) {
        geonameid = 'top_map_level'
    }
    console.log(geonameid)

    let drill_down = jQuery('#drill_down_container')
    let rest = DRILLDOWNDATA.settings.endpoints.get_drilldown_endpoint
    jQuery.ajax({
        type: rest.method,
        contentType: "application/json; charset=utf-8",
        data: JSON.stringify( {  "bind_function": bind_function, "geonameid": geonameid } ),
        dataType: "json",
        url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', rest.nonce);
        },
    })
        .done( function( response ) {
            console.log(response)

            drill_down.empty()
            let html = ``

            html += `<ul id="drill_down">`

            jQuery.each( response, function(i,section) {
                if ( section.link ) {
                    html += `<li><button id="${section.parent}" style="margin-top:1em;"
                        onclick="DRILLDOWN.get_drill_down( 'drill', '${section.selected}' )"
                        class="button hollow">${section.selected_name}</button></li>`
                } else {
                    if ( ! isEmpty( section.list ) ) {
                        html += `<li><select id="${section.parent}" 
                        onchange="DRILLDOWN.get_drill_down( 'drill', this.value )"
                        class="geocode-select">`

                        html += `<option value="${section.parent}"></option>`

                        jQuery.each( section.list, function( ii, item ) {
                            html += `<option value="${item.geonameid}" `
                            if ( item.geonameid === section.selected ) {
                                html += ` selected`
                            }
                            html += `>${item.name}</option>`
                        })

                        html += `</select></li>`
                    }
                }

            })

            html += `</ul>`
            drill_down.append(html)


        }) // end success statement
        .fail(function (err) {
            console.log("error")
            console.log(err)
        })
    },

    isEmpty(obj) {
        for(let key in obj) {
            if(obj.hasOwnProperty(key))
                return false;
        }
        return true;
    },

    show_spinner() {
        jQuery('#spinner').show()
    },

    hide_spinner() {
        jQuery('#spinner').hide()
    }

}