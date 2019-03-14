window.GEOCODING = {
    load_drill_down( div, geonameid ) {

        /*******************************************************************************************************************
         *
         * Load Requested Geonameid
         *
         *****************************************************************************************************************/
        if ( geonameid ) { // make sure this is not a top level continent or world request
            GEOCODING.geoname_drill_down( div, geonameid )
        }
        /*******************************************************************************************************************
         *
         * Initialize Top Level Maps
         *
         *****************************************************************************************************************/
        else { // top_level maps
            GEOCODING.top_level_drill_down( div )
        } // end if
    },

    top_level_drill_down( div ) {
        let mapping_module = mappingModule.mapping_module
        let top_map_list = mapping_module.data.top_map_list
        let drill_down = jQuery('#drill_down')

        GEOCODING.show_spinner()

        drill_down.empty().append(`<li><select id="drill_down_top_level" onchange="geoname_drill_down( '${div}', this.value );jQuery(this).parent().nextAll().remove();"></select></li>`)
        let drill_down_select = jQuery('#drill_down_top_level')

        if( Object.keys(top_map_list).length === 1 ) {
            jQuery.each(top_map_list, function(i,v) {
                drill_down_select.append(`<option value="${i}" selected>${v}</option>`)

                if ( ! isEmpty( mapping_module.data[i].children ) ) {
                    if ( ! isEmpty( mapping_module.data[i].deeper_levels ) ) {
                        drill_down.append(`<li><select id="${i}" onchange="geoname_drill_down( '${div}', this.value );jQuery(this).parent().nextAll().remove();"><option>Select</option></select></li>`)
                        let sorted_children =  _.sortBy(mapping_module.data[i].children, [function(o) { return o.name; }]);

                        jQuery.each( sorted_children, function(ii,vv) {
                            jQuery('#'+i).append(`<option value="${vv.id}">${vv.name}</option>`)
                        })
                    }

                    if ( i === 'world' ) {
                        GEOCODING.bind_drill_down( div )
                    } else {
                        GEOCODING.bind_drill_down( div, i )
                    }

                } else {
                    drill_down.append(`<li>deepest level</li>`)
                }

            })
        } else {
            drill_down_select.append(`<option>Select</option>`)
            jQuery.each(top_map_list, function(i,v) {
                drill_.down_select.append(`<option value="${i}">${v}</option>`)
            })
            jQuery('#location-list').empty().append(`Select list above.`)

            GEOCODING.bind_drill_down( div )
        }

        GEOCODING.hide_spinner()
    },

    geoname_drill_down( div, id ) {
        let mapping_module = mappingModule.mapping_module
        show_spinner()
        let rest = mapping_module.settings.endpoints.get_map_by_geonameid_endpoint

        let drill_down = jQuery('#drill_down')

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
                mapping_module.data[response.self.geonameid] = response

                if ( ! isEmpty( response.children ) ) {
                    if ( ! isEmpty( response.deeper_levels ) ) {
                        drill_down.append(`<li><select id="${response.self.geonameid}" onchange="geoname_drill_down( '${div}', this.value );jQuery(this).parent().nextAll().remove();"><option>Select</option></select></li>`)
                        let sorted_children =  _.sortBy(response.children, [function(o) { return o.name; }]);

                        jQuery.each( sorted_children, function(i,v) {
                            jQuery('#'+id).append(`<option value="${v.id}">${v.name}</option>`)
                        })
                    }

                    bind_drill_down( div, response.self.geonameid )
                } else {
                    drill_down.append(`<li>deepest level</li>`)
                }


                hide_spinner()
            }) // end success statement
            .fail(function (err) {
                console.log("error")
                console.log(err)
                hide_spinner()
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
    },

    bind_drill_down( div, geonameid ) {

        switch(div) {
            case 'location-list':
                console.log('bind_drill_down: location-list')
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

        }
    }
}