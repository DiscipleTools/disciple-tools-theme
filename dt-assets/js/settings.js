jQuery(document).ready(function() {
  window.current_user_lookup = wpApiSettingsPage.current_user_id
  load_locations()

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

function load_locations() {
  makeRequest( "GET", `user/my` )
    .done(data=>{
console.log(data)
      if ( typeof dtMapbox !== "undefined" ) {
        dtMapbox.post_type = 'user'
        write_results_box()

        jQuery( '#new-mapbox-search' ).on( "click", function() {
          dtMapbox.post_type = 'user'
          write_input_widget()
        });
      } else {
        //locations
        let typeahead = Typeahead['.js-typeahead-location_grid']
        if (typeahead) {
          typeahead.items = [];
          typeahead.comparedItems =[];
          typeahead.label.container.empty();
          typeahead.adjustInputSize()
        }
        if ( typeof data.locations.location_grid !== "undefined" ) {
          data.locations.location_grid.forEach(location => {
            typeahead.addMultiselectItemLayout({ID: location.id.toString(), name: location.label})
          })
        }

      }
    }).catch((e)=>{
    console.log( 'error in locations')
    console.log( e)
  })
}

if ( typeof dtMapbox === "undefined" ) {
  let typeaheadTotals = {}
  if (!window.Typeahead['.js-typeahead-location_grid'] ){
    $.typeahead({
      input: '.js-typeahead-location_grid',
      minLength: 0,
      accent: true,
      searchOnFocus: true,
      maxItem: 20,
      dropdownFilter: [{
        key: 'group',
        value: 'focus',
        template: _.escape(window.wpApiShare.translations.regions_of_focus),
        all: _.escape(window.wpApiShare.translations.all_locations),
      }],
      source: {
        focus: {
          display: "name",
          ajax: {
            url: wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
            data: {
              s: "{{query}}",
              filter: function () {
                return _.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
              }
            },
            beforeSend: function (xhr) {
              xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
            },
            callback: {
              done: function (data) {
                if (typeof typeaheadTotals !== "undefined") {
                  typeaheadTotals.field = data.total
                }
                return data.location_grid
              }
            }
          }
        }
      },
      display: "name",
      templateValue: "{{name}}",
      dynamic: true,
      multiselect: {
        matchOn: ["ID"],
        data: function () {
          return [];
        }, callback: {
          onCancel: function (node, item) {
            delete_location_grid( item.ID)
          }
        }
      },
      callback: {
        onClick: function(node, a, item, event){
          add_location_grid( item.ID)
        },
        onReady(){
          this.filters.dropdown = {key: "group", value: "focus", template: _.escape(window.wpApiShare.translations.regions_of_focus)}
          this.container
            .removeClass("filter")
            .find("." + this.options.selector.filterButton)
            .html(_.escape(window.wpApiShare.translations.regions_of_focus));
        },
        onResult: function (node, query, result, resultCount) {
          resultCount = typeaheadTotals.location_grid
          let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $('#location_grid-result-container').html(text);
        },
        onHideLayout: function () {
          $('#location_grid-result-container').html("");
        }
      }
    });
  }
}
let add_location_grid = ( value )=>{
  let data =  {
    grid_id: value
  }
  return makeRequest( "POST", `users/user_location`, data )
}
let delete_location_grid = ( value )=>{
  let data =  {
    grid_id: value
  }
  return makeRequest( "DELETE", `users/user_location`, data )
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
