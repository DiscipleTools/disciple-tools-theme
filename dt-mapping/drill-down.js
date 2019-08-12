window.DRILLDOWNDATA = mappingModule.mapping_module
window.DRILLDOWN = {

    get_drill_down( bindFunction, grid_id ) {
        DRILLDOWN.show_spinner()

        if ( ! grid_id ) {
            grid_id = 'top_map_level'
        }

        let drill_down = jQuery('#'+bindFunction)
        let rest = DRILLDOWNDATA.settings.endpoints.get_drilldown_endpoint

        jQuery.ajax({
            type: rest.method,
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify( {  "bind_function": bindFunction, "grid_id": grid_id } ),
            dataType: "json",
            url: DRILLDOWNDATA.settings.root + rest.namespace + rest.route,
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', rest.nonce);
            },
        })
        .done( function( response ) {

            let html = ``

            html += `<ul class="drill_down">`
            let selectedGeonameLabel = '';
            jQuery.each( response, function(i,section) {

                // check if section is a link or a dropdown list
                if ( section.link ) {

                    // highlight the active button
                    let hollowClass = 'hollow'
                    if ( section.active ) {
                        hollowClass = ''
                        grid_id = section.selected
                        selectedGeonameLabel = section.selected_name
                    }
                    let disabled = !response[i+2]

                    // create button
                    html += `<li><button id="${_.escape( section.parent )}" type="button" ${disabled ? "disabled" : ""}
                        onclick="DRILLDOWN.get_drill_down( '${_.escape( bindFunction )}', '${_.escape( section.selected )}' )"
                        class="button ${hollowClass} geocode-link">${_.escape( section.selected_name )}</button></li>`

                } else { // it is a list
                    // check if list is not empty
                    if (!DRILLDOWN.isEmpty(section.list)) {

                        // check if hide final drilldown is set and that there are no deeper levels
                        if ( DRILLDOWN.isEmpty(section.deeper_levels) && DRILLDOWNDATA.settings.hide_final_drill_down === true) {
                            console.log('no additional dropdown triggered')
                        } else {
                            // make select
                            html += `<li><select id="${_.escape( section.parent )}"
                            onchange="DRILLDOWN.get_drill_down( '${_.escape( bindFunction )}', this.value )"
                            class="geocode-select">`

                            // make initial option
                            html += `<option value="${_.escape( section.parent )}"></option>`

                            // make option list
                            jQuery.each(section.list, function (ii, item) {
                                html += `<option value="${_.escape( item.grid_id )}" `
                                if (item.grid_id === section.selected) {
                                    html += ` selected`
                                }
                                html += `>${_.escape( item.name )}</option>`
                            })

                            html += `</select></li>`
                        }
                    }
                }

            })

            // close unordered list
            html += `</ul>`

            // clear and apply new list
            drill_down.empty().append(html)

            // trigger supplied bind event
            if ( typeof DRILLDOWN[bindFunction] !== "undefined" ) {
                DRILLDOWN[bindFunction]( grid_id, selectedGeonameLabel )
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
            if( Object.prototype.hasOwnProperty.call(obj, key) )
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
