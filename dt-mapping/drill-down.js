/* global jQuery:false, wpApiShare:false */
_ = _ || window.lodash // make sure lodash is defined so plugins like gutenberg don't break it.

window.GEOCODINGDATA = mappingModule.mapping_module
window.GEOCODING = {

    load_drill_down( div, geonameid ) {
        if ( geonameid ) {
            GEOCODING.geoname_drill_down( div, geonameid )
        }
        else {
            GEOCODING.top_level_drill_down( div )
        }
    },

    top_level_drill_down( div ) {

        let mapping_module = GEOCODINGDATA
        let top_map_list = mapping_module.data.top_map_list
        let drill_down = jQuery('#drill_down')

        GEOCODING.show_spinner()

        drill_down.empty().append(`<li><select id="drill_down_top_level" onchange="geoname_drill_down( '${div}', this.value, null );jQuery(this).parent().nextAll().remove();"></select><option value=" "></option></li>`)
        let drill_down_select = jQuery('#drill_down_top_level')

        if( Object.keys(top_map_list).length === 1 ) {
            jQuery.each(top_map_list, function(i,v) {
                drill_down_select.append(`<option value="${i}" selected>${v}</option>`)

                if ( ! GEOCODING.isEmpty( mapping_module.data[i].children ) ) {

                    drill_down.append(`<li><select id="${i}" onchange="GEOCODING.geoname_drill_down( '${div}', this.value, ${i} );jQuery(this).parent().nextAll().remove();"><option value="${i}"></option></select></li>`)
                    let sorted_children =  _.sortBy(mapping_module.data[i].children, [function(o) { return o.name; }]);

                    jQuery.each( sorted_children, function(ii,vv) {
                        jQuery('#'+i).append(`<option value="${vv.id}">${vv.name}</option>`)
                    })

                    if ( i === 'world' ) {
                        GEOCODING.bind_drill_down( div )
                    } else {
                        GEOCODING.bind_drill_down( div, i )
                    }
                }
            })
        } else {
            drill_down_select.append(`<option value=""></option>`)
            jQuery.each(top_map_list, function(i,v) {
                drill_down_select.append(`<option value="${i}">${v}</option>`)
            })
            GEOCODING.bind_drill_down( div )
        }

        GEOCODING.hide_spinner()
    },

    geoname_drill_down( div, id) {
        let mapping_module = mappingModule.mapping_module
        let rest = mapping_module.settings.endpoints.get_map_by_geonameid_endpoint
        let drill_down = jQuery('#drill_down')

        GEOCODING.show_spinner()

        if ( id !== undefined ) {

            jQuery.ajax({
                type: rest.method,
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify( { 'geonameid': id } ),
                dataType: "json",
                url: mapping_module.settings.root + rest.namespace + rest.route,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', rest.nonce );
                },
            })
                .done( function( response ) {
                    console.log(response)
                    GEOCODINGDATA.data[response.self.geonameid] = response

                    if ( ! GEOCODING.isEmpty( response.children ) ) {

                        drill_down.append(`<li><select id="${response.self.geonameid}" class="geocode-select" onchange="GEOCODING.geoname_drill_down( '${div}', this.value, ${response.self.geonameid} );jQuery(this).parent().nextAll().remove();"><option value="${response.self.geonameid}"></option></select></li>`)
                        let sorted_children =  _.sortBy(response.children, [function(o) { return o.name; }]);

                        jQuery.each( sorted_children, function(i,v) {
                            jQuery('#'+id).append(`<option value="${v.id}">${v.name}</option>`)
                        })

                        GEOCODING.bind_drill_down( div, response.self.geonameid )
                    }

                }) // end success statement
                .fail(function (err) {
                    console.log("error")
                    console.log(err)
                })
        }

        GEOCODING.hide_spinner()
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
    },

    bind_drill_down( div, geonameid, previous ) {
        switch(div) {
            case 'location-list':
                if ( geonameid === undefined ) {
                    jQuery('#location-list').empty().append(`Select list above.`)
                }
                location_list( div, geonameid )
                break;
            case 'map-display':
                console.log('bind_drill_down: map-display: ')
                if ( geonameid !== undefined ) {
                    map_chart( div, geonameid )
                } else {
                    map_chart( div )
                }
                break;
            case 'geocode-selected-value':
                if ( geonameid !== previous ) {
                    console.log( geonameid )
                    jQuery('#geocode-selected-value').val( geonameid )
                }
                break;
            case 'name-select':
                let list_results = jQuery('#list_results')
                list_results.empty()
                jQuery.each( window.GEOCODINGDATA.data[geonameid].children, function(i,v) {
                    list_results.append( v.name + `<br>`)
                })

                break;
        }
    }
}