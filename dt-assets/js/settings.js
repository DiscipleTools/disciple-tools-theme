jQuery(document).ready(function() {
    load_settings_locations()
})

/**
 * Password reset
 *
 * @param preference_key
 * @param type
 * @returns {*}
 */
function switch_preference (preference_key, type = null) {
    return makeRequest('post', 'users/switch_preference', { preference_key, type})
}

function change_password() {
    let translation = wpApiSettingsPage.translations
    // test matching passwords
    const p1 = jQuery('#password1')
    const p2 = jQuery('#password2')
    const message = jQuery('#password-message')

    message.empty()

    if (p1.val() !== p2.val()) {
        message.append(translation.pass_does_not_match)
        return
    }

    makeRequest('post', 'users/change_password', { password: p1 }).done(data => {
        console.log( data )
        message.html(translation.changed)
    }).fail(handleAjaxError)
}

/**
 * Locations
 */
window.DRILLDOWN.add_user_location = function( grid_id ) {
    jQuery('#add_location_location_grid_value').val(grid_id)
}

function load_settings_locations( reload = false ) {
    let section = jQuery('#manage_locations_section')

    section.empty().append(
        `<div id="current_locations"></div>
        <div id="new_locations"></div>
        <div id="locations_add_button">
            <p><button type="button" onclick="add_drill_down_selector()" class="button">Add</button></p>
        </div>`
    )

    let cl = jQuery('#current_locations')

    if ( wpApiSettingsPage.custom_data.current_locations !== undefined && ! reload ) {
        cl.append(`<strong>Current Locations:</strong><br>`)
        jQuery.each( wpApiSettingsPage.custom_data.current_locations, function(i,v) {
            cl.append(`${v.name}, ${v.country_code} <a style="padding:0 10px;" onclick="delete_location(${v.grid_id})"><img src="${wpApiSettingsPage.template_dir}/dt-assets/images/invalid.svg"></a><br>`)
        })
        cl.append(`<br>`)
    } else {

        makeRequest('get', 'users/current_locations', { "contact_id": wpApiSettingsPage.associated_contact_id } ).done(data => {
            console.log( data )
            if (data ) {
                cl.append(`<strong>Current Locations:</strong><br>`)
                jQuery.each( data, function(i,v) {
                    cl.append(`${v.name}, ${v.country_code} <a style="padding:0 10px;" onclick="delete_location(${v.grid_id})"><img src="${wpApiSettingsPage.template_dir}/dt-assets/images/invalid.svg"></a><br>`)
                })
                cl.append(`<br>`)
            }
        }).fail(handleAjaxError)
    }
}



function add_drill_down_selector() {
    jQuery('#new_locations').empty().append(
            `<div id="add_user_location"><ul class="drill_down"></ul></div>
            <input type="hidden" id="add_location_location_grid_value" />
            <button type="button" class="button" onclick="save_new_location()">Save</button>`
    )
    window.DRILLDOWN.get_drill_down( 'add_user_location' )
    jQuery('#locations_add_button').hide()
}

function save_new_location() {
    let grid_id = jQuery('#add_location_location_grid_value').val()

    makeRequest('post', 'users/user_location', { grid_id: grid_id } ).done(data => {
        console.log( data )
        load_settings_locations( true )
    }).fail(handleAjaxError)

}

function delete_location( grid_id ) {
    makeRequest('delete', 'users/user_location', { grid_id: grid_id } ).done(data => {
        console.log( data )
        load_settings_locations( true )
    }).fail(handleAjaxError)
}
