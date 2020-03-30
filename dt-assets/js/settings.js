jQuery(document).ready(function() {
  if ( typeof dtMapbox !== 'undefined' ) {

    dtMapbox.post_type = 'contacts'
    dtMapbox.post_id = wpApiSettingsPage.associated_contact_id
    dtMapbox.post = wpApiSettingsPage.associated_contact
    load_mapbox_location()
    write_results_box()
    jQuery( '#new-mapbox-search' ).on( "click", function() {
      write_input_widget()
    });

  } else {
    window.DRILLDOWN.add_user_location = function( grid_id ) {
      jQuery('#add_location_location_grid_value').val(grid_id)
    }
    load_settings_locations()
  }

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

/* Locations with or without mapbox */
function load_mapbox_location() {
    jQuery('#manage_locations_section' ).empty().html(`<div id="mapbox-wrapper"></div><button id="new-mapbox-search" class="button">${_.escape( wpApiSettingsPage.translations.add ) /* Add */}</button>`)
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
        cl.append(`<strong>${_.escape(wpApiSettingsPage.translations.responsible_for_locations)}:</strong><br>`)
        jQuery.each( wpApiSettingsPage.custom_data.current_locations, function(i,v) {
            cl.append(`${_.escape(v.name)}, ${_.escape(v.country_code)} <a style="padding:0 10px;" onclick="delete_location(${_.escape(v.grid_id)})"><img src="${_.escape(wpApiSettingsPage.template_dir)}/dt-assets/images/invalid.svg"></a><br>`)
        })
        cl.append(`<br>`)
    } else {

        makeRequest('get', 'users/current_locations', { "contact_id": wpApiSettingsPage.associated_contact_id } ).done(data => {
            if (data ) {
                cl.append(`<strong>${_.escape(wpApiSettingsPage.translations.responsible_for_locations)}:</strong><br>`)
                jQuery.each( data, function(i,v) {
                    cl.append(`${_.escape(v.name)}, ${_.escape(v.country_code)} <a style="padding:0 10px;" onclick="delete_location(${_.escape(v.grid_id)})"><img src="${_.escape(wpApiSettingsPage.template_dir)}/dt-assets/images/invalid.svg"></a><br>`)
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
            <button type="button" class="button" onclick="save_new_location()">${_.escape( wpApiSettingsPage.translations.save ) /* Save */}</button>`
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





let update_user = ( key, value )=>{
    let data =  {
      [key]: value
    }
    return makeRequest( "POST", `user/update`, data , 'dt/v1/' )
  }
/**
 * Set availability dates
 */
let dateFields = [ "start_date", "end_date" ]
  dateFields.forEach(key=>{
    let datePicker = $(`#${key}.date-picker`)
    datePicker.datepicker({
      onSelect: function (date) {
        let start_date = $('#start_date').val()
        let end_date = $('#end_date').val()
        if ( start_date && end_date && ( moment(start_date) < moment(end_date) )){
          $('#add_unavailable_dates').removeAttr("disabled");
        } else {
          $('#add_unavailable_dates').attr("disabled", true);
        }
      },
      dateFormat: 'yy-mm-dd',
      changeMonth: true,
      changeYear: true
    })
  })

$('#add_unavailable_dates').on('click', function () {
  let start_date = $('#start_date').val()
  let end_date = $('#end_date').val()
  $('#add_unavailable_dates_spinner').addClass('active')
  update_user( 'add_unavailability', {start_date, end_date}).then((resp)=>{
    $('#add_unavailable_dates_spinner').removeClass('active')
    $('#start_date').val('')
    $('#end_date').val('')
    display_dates_unavailable(resp)
  })
})
let display_dates_unavailable = (list = [], first_run )=>{
  let date_unavailable_table = $('#unavailable-list')
  let rows = ``
  list = _.orderBy( list, [ "start_date" ], "desc")
  list.forEach(range=>{
    rows += `<tr>
        <td>${_.escape(range.start_date)}</td>
        <td>${_.escape(range.end_date)}</td>
        <td>
            <button class="button hollow tiny alert remove_dates_unavailable" data-id="${_.escape(range.id)}" style="margin-bottom: 0">
            <i class="fi-x"></i> ${_.escape( wpApiSettingsPage.translations.delete )}</button>
        </td>
      </tr>`
  })
  if ( rows || ( !rows && !first_run ) ){
    date_unavailable_table.html(rows)
  }
}
display_dates_unavailable( wpApiSettingsPage.custom_data.availability, true )
$( document).on( 'click', '.remove_dates_unavailable', function () {
  let id = $(this).data('id');
  update_user( 'remove_unavailability', id).then((resp)=>{
    display_dates_unavailable(resp)
  })
})
