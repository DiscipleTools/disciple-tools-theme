
window.DRILLDOWNDATA = mappingModule.mapping_module
window.DRILLDOWN = {

    get_drill_down( bindFunction, geonameid ) {
        DRILLDOWN.show_spinner()

        if ( ! geonameid ) {
            geonameid = 'top_map_level'
        }
        // console.log(geonameid)

        let drill_down = jQuery('#'+bindFunction)
        let rest = DRILLDOWNDATA.settings.endpoints.get_drilldown_endpoint

        jQuery.ajax({
            type: rest.method,
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify( {  "bind_function": bindFunction, "geonameid": geonameid } ),
            dataType: "json",
            url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', rest.nonce);
            },
        })
        .done( function( response ) {
            // console.log(response)

            let html = ``

            html += `<ul id="drill_down">`

            jQuery.each( response, function(i,section) {
                if ( section.link ) {
                    let hollowClass = 'hollow'
                    if ( section.active ) {
                        hollowClass = ''
                    }
                    html += `<li><button id="${section.parent}" style="margin-top:1em;"
                        onclick="DRILLDOWN.get_drill_down( '${bindFunction}', '${section.selected}' )"
                        class="button ${hollowClass} geocode-link">${section.selected_name}</button></li>`
                } else {
                    if ( ! DRILLDOWN.isEmpty( section.list ) ) {
                        html += `<li><select id="${section.parent}" 
                        onchange="DRILLDOWN.get_drill_down( '${bindFunction}', this.value )"
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

            drill_down.empty().append(html)

            if ( typeof DRILLDOWN[bindFunction] !== "undefined" ) {
                DRILLDOWN[bindFunction]( geonameid )
            }

        }) // end success statement
        .fail(function (err) {
            console.log("error")
            console.log(err)
        })

        DRILLDOWN.hide_spinner()
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