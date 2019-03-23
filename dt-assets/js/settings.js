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

window.DRILLDOWN.add_settings_location = function( geonameid ) {
    jQuery('#add_location_geoname_value').val(geonameid)
}

jQuery(document).ready(function() {
    load_settings_locations()
})

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
        cl.append('<strong>Current Locations:</strong><br>')
        jQuery.each( wpApiSettingsPage.custom_data.current_locations, function(i,v) {
            cl.append(`${v.full_name} <a>delete</a><br>`) // @todo add delete function
        })
        cl.append('<br>')
    } else {

        jQuery.ajax({
            type: "GET",
            data: JSON.stringify({"contact_id": wpApiSettingsPage.associated_contact_id }),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: wpApiSettingsPage.root + 'dt/v1/users/current_locations',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiSettingsPage.nonce);
            },
        })
            .done(function (data) {
                console.log( data )
                if (data ) {
                    cl.append('<strong>Current Locations:</strong><br>')
                    jQuery.each( data, function(i,v) {
                        cl.append(`${v.full_name}<br>`)
                    })
                    cl.append('<br>')
                }
            })
            .fail(function (err) {
                console.log("error")
                console.log(err)
            })

    }
}

function add_drill_down_selector() {
    jQuery('#new_locations').empty().append(
            `<ul id="drill_down"></ul>
            <input type="hidden" id="add_location_geoname_value" />
            <button type="button" class="button" onclick="save_location()">Save</button>`
    )
    window.DRILLDOWN.top_level_drill_down( 'add_settings_location' )
    jQuery('#locations_add_button').hide()
}

function save_location() {
    let geonameid = jQuery('#add_location_geoname_value').val()

    // @todo insert REST API call to add new location to user contact record

    load_settings_locations( true )
}