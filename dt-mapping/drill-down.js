"use strict";
let rebuild_drill_down = ( response, bindFunction, grid_id, cached = true )=>{
    let drill_down = jQuery('#'+bindFunction)

    let final_list = [];
    let current_selection = {};
    let html = ``

    html += `<ul style="margin-left:0" class="drill_down">`
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
            html += `<li><button id="${window.lodash.escape( section.parent )}" type="button" ${disabled ? "disabled" : ""}
                onclick="DRILLDOWN.get_drill_down( '${window.lodash.escape( bindFunction )}', '${window.lodash.escape( section.selected )}', ${cached} )"
                class="button ${hollowClass} geocode-link">${window.lodash.escape( section.selected_name )}</button></li>`

            current_selection = section

        } else { // it is a list
            // check if list is not empty
            if (!DRILLDOWN.isEmpty(section.list)) {

                // check if hide final drilldown is set and that there are no deeper levels
                if ( DRILLDOWN.isEmpty(section.deeper_levels) && window.drilldownModule.settings.hide_final_drill_down === true) {
                    console.log('no additional dropdown triggered')
                } else {
                    // make select
                    html += `<li><select id="${window.lodash.escape( section.parent )}" style="vertical-align: top"
                    onchange="DRILLDOWN.get_drill_down( '${window.lodash.escape( bindFunction )}', this.value )"
                    class="geocode-select">`

                    // make initial option
                    html += `<option value="${window.lodash.escape( section.parent )}"></option>`

                    // make option list
                    jQuery.each(section.list, function (ii, item) {
                        html += `<option value="${window.lodash.escape( item.grid_id )}" `
                        if (item.grid_id === section.selected) {
                            html += ` selected`
                        }
                        html += `>${window.lodash.escape( item.name )}</option>`
                    })

                    html += `</select></li>`

                    final_list = section.list;
                }
            }
        }

    })

    html += `<li><span id="spinner" style="display:none;">${window.drilldownModule.settings.spinner_large}</span></li>`

    // close unordered list
    html += `</ul>`

    // clear and apply new list
    drill_down.empty().append(html)

    // trigger supplied bind event
    if ( typeof DRILLDOWN[bindFunction] !== "undefined" ) {
        current_selection.list = final_list
        DRILLDOWN[bindFunction]( grid_id, selectedGeonameLabel, current_selection )
    }

    DRILLDOWN.hide_spinner()
    return final_list;
}
window.DRILLDOWN = {
    get_drill_down( bindFunction, grid_id, cached = true ) {
        DRILLDOWN.show_spinner()


        if ( ! grid_id ) {
            grid_id = window.drilldownModule.current_map || 'world'
        }

        let rest = window.drilldownModule.settings.endpoints.get_drilldown_endpoint
        
        if ( cached && window.drilldownModule.drilldown && window.drilldownModule.drilldown[grid_id] ){
            rebuild_drill_down( window.drilldownModule.drilldown[grid_id], bindFunction, grid_id, cached )
        } else {
            return jQuery.ajax({
                type: rest.method,
                contentType: "application/json; charset=utf-8",
                data: JSON.stringify( {  "bind_function": bindFunction, "grid_id": grid_id, "cached": cached } ),
                dataType: "json",
                url: window.drilldownModule.settings.root + rest.namespace + rest.route,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', rest.nonce);
                },
            })
            .then( function( response ) {
                window.drilldownModule.drilldown[grid_id] = response
                rebuild_drill_down( response, bindFunction, grid_id, cached )
                
    
            }) // end success statement
            .fail(function (err) {
                console.log("error")
                console.log(err)
                DRILLDOWN.hide_spinner()
            })
        }


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
