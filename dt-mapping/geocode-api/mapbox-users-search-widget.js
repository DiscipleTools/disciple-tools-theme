/** Mapbox search box widget for users */
jQuery(document).ready(function(){

  // load widget
  if ( dtMapbox.user_location.length !== 0 ) {
    write_results_box()
  }
  jQuery( '#new-mapbox-search' ).on( "click", function() {
    write_input_widget()
  });

})

function write_results_box() {
  jQuery('#mapbox-wrapper').empty().append(`<div id="location-grid-meta-results"></div>`)

  if ( dtMapbox.user_location.location_grid_meta !== undefined && dtMapbox.user_location.location_grid_meta.length !== 0 ) {
    let lgm_results = jQuery('#location-grid-meta-results')
    jQuery.each( dtMapbox.user_location.location_grid_meta, function(i,v) {
      lgm_results.append(`<div class="input-group">
                              <input type="text" class="active-location input-group-field " id="location-${_.escape( v.grid_meta_id )}" value="${_.escape( v.label )}" readonly />
                              <div class="input-group-button">
                                <button type="button" class="button alert clear-date-button delete-button mapbox-delete-button" title="${ _.escape( dtMapbox.translations.delete_location ) /*Delete Location*/}" data-id="${_.escape( v.grid_meta_id )}">&times;</button>
                              </div>
                            </div>`)
    })
    delete_location_listener()
    reset_tile_spacing()
  } /*end valid check*/
}

function delete_location_listener() {
  jQuery( '.mapbox-delete-button' ).on( "click", function(e) {

    let data = {
      user_id: dtMapbox.user_id,
      user_location: {
        location_grid_meta: [
          {
            grid_meta_id: jQuery(this).data("id"),
          }
        ]
      }
    }

    makeRequest( "DELETE", `users/user_location`, data )
    .then(function (response) {
      dtMapbox.user_location = response.user_location
      dtMapbox.user_id = response.user_id
      write_results_box()
    }).catch(err => { console.error(err) })

  });
}

function reset_tile_spacing() {
  let masonGrid = jQuery('.grid')
  masonGrid.masonry({
    itemSelector: '.grid-item',
    percentPosition: true
  });
}

function write_input_widget() {

  if ( jQuery('#mapbox-autocomplete').length === 0 ) {
    jQuery('#mapbox-wrapper').prepend(`
    <div id="mapbox-autocomplete" class="mapbox-autocomplete input-group" data-autosubmit="true">
        <input id="mapbox-search" type="text" name="mapbox_search" placeholder="Search Location" />
        <div class="input-group-button">
            <button id="mapbox-spinner-button" class="button hollow" style="display:none;"><span class="loading-spinner active"></span></button>
            <button id="mapbox-clear-autocomplete" class="button alert input-height delete-button-style mapbox-delete-button" type="button" title="${ _.escape( dtMapbox.translations.clear ) /*Delete Location*/}" >&times;</button>
        </div>
        <div id="mapbox-autocomplete-list" class="mapbox-autocomplete-items"></div>
    </div>
  `)
  }

  window.currentfocus = -1

  jQuery('#mapbox-search').on("keyup", function(e){

    var x = document.getElementById("mapbox-autocomplete-list");
    if (x) x = x.getElementsByTagName("div");
    if (e.which === 40) {
      /*If the arrow DOWN key is pressed,
      increase the currentFocus variable:*/
      window.currentfocus++;
      /*and and make the current item more visible:*/
      add_active(x);
    } else if (e.which === 38) { //up
      /*If the arrow UP key is pressed,
      decrease the currentFocus variable:*/
      window.currentfocus--;
      /*and and make the current item more visible:*/
      add_active(x);
    } else if (e.which === 13) {
      /*If the ENTER key is pressed, prevent the form from being submitted,*/
      e.preventDefault();
      if (window.currentfocus > -1) {
        /*and simulate a click on the "active" item:*/
        close_all_lists(window.currentfocus);
      }
    } else {
      validate_timer()
    }

  })

  let mapbox_clear = jQuery('#mapbox-clear-autocomplete')
  mapbox_clear.hide().on('click', function(){
    clear_autocomplete()
  })

  reset_tile_spacing()

}

// delay location lookup
window.validate_timer_id = '';
function validate_timer() {

  clear_timer()

  // toggle buttons
  jQuery('#mapbox-spinner-button').show()

  // set timer
  window.validate_timer_id = setTimeout(function(){
    // call geocoder
    if ( dtMapbox.google_map_key ) {
      google_autocomplete( jQuery('#mapbox-search').val() )
    } else {
      mapbox_autocomplete( jQuery('#mapbox-search').val() )
    }

    // toggle buttons back
    jQuery('#mapbox-spinner-button').hide()
    jQuery('#mapbox-clear-autocomplete').show()
  }, 1000);

}
function clear_timer() {
  clearTimeout(window.validate_timer_id);
}
// end delay location lookup

function mapbox_autocomplete(address){
  if ( address.length < 1 ) {
    jQuery('#mapbox-clear-autocomplete').hide()
    return;
  }

  let root = 'https://api.mapbox.com/geocoding/v5/mapbox.places/'
  let settings = '.json?types=country,region,postcode,district,place,locality,neighborhood,address&limit=6&access_token='
  let key = dtMapbox.map_key

  let url = root + encodeURI( address ) + settings + key

  jQuery.get( url, function( data ) {
    if( data.features.length < 1 ) {
      // destroy lists
      return
    }

    let list = jQuery('#mapbox-autocomplete-list')
    list.empty()

    jQuery.each( data.features, function( index, value ) {
      list.append(`<div data-value="${_.escape(index)}">${_.escape(value.place_name)}</div>`)
    })

    jQuery('#mapbox-autocomplete-list div').on("click", function (e) {
      close_all_lists(e.target.attributes['data-value'].value);
    });

    // Set globals
    window.mapbox_result_features = data.features


  }); // end get request
} // end validate

function google_autocomplete(address){
  if ( address.length < 1 ) {
    jQuery('#mapbox-clear-autocomplete').hide()
    return;
  }

  let service = new google.maps.places.AutocompleteService();
  service.getPlacePredictions({ 'input': address }, function(predictions, status ) {
    let list = jQuery('#mapbox-autocomplete-list')
    list.empty()
    if (status === 'OK') {

      jQuery.each( predictions, function( index, value ) {
        list.append(`<div data-value="${_.escape(index)}">${_.escape(value.description)}</div>`)
      })

      jQuery('#mapbox-autocomplete-list div').on("click", function (e) {
        close_all_lists(e.target.attributes['data-value'].value);
      });

      // Set globals
      window.mapbox_result_features = predictions

    }
    else if ( status === 'ZERO_RESULTS' ) {
      list.append(`<div>No Results Found</div>`)
    }
    else {
      console.log('Predictions was not successful for the following reason: ' + status)
    }
  } )

} // end validate

function add_active(x) {
  /*a function to classify an item as "active":*/
  if (!x) return false;
  /*start by removing the "active" class on all items:*/
  remove_active(x);
  if (window.currentfocus >= x.length) window.currentfocus = 0;
  if (window.currentfocus < 0) window.currentfocus = (x.length - 1);
  /*add class "autocomplete-active":*/
  x[window.currentfocus].classList.add("mapbox-autocomplete-active");
}
function remove_active(x) {
  /*a function to remove the "active" class from all autocomplete items:*/
  for (var i = 0; i < x.length; i++) {
    x[i].classList.remove("mapbox-autocomplete-active");
  }
}
function close_all_lists(selection_id) {

  if ( dtMapbox.google_map_key ) {
    jQuery('#mapbox-search').val(window.mapbox_result_features[selection_id].description)
    jQuery('#mapbox-autocomplete-list').empty()

    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ placeId: window.mapbox_result_features[selection_id].place_id }, (results, status) => {
      if (status !== "OK") {
        console.log("Geocoder failed due to: " + status);
        return;
      }

      window.location_data = {
        user_id: dtMapbox.user_id,
        user_location: {
          location_grid_meta: [
            {
              lng: results[0].geometry.location.lng(),
              lat: results[0].geometry.location.lat(),
              level: convert_level( results[0].types[0] ),
              label: results[0].formatted_address,
              source: 'user'
            }
          ]
        }
      }
      post_geocoded_location()
    });
  } else {
    jQuery('#mapbox-search').val(window.mapbox_result_features[selection_id].place_name)
    jQuery('#mapbox-autocomplete-list').empty()

    window.location_data = {
      user_id: dtMapbox.user_id,
      user_location: {
        location_grid_meta: [
          {
            lng: window.mapbox_result_features[selection_id].center[0],
            lat: window.mapbox_result_features[selection_id].center[1],
            level: window.mapbox_result_features[selection_id].place_type[0],
            label: window.mapbox_result_features[selection_id].place_name,
            source: 'user'
          }
        ]
      }
    }
    post_geocoded_location()
  }
}

function post_geocoded_location(){
  if ( jQuery('#mapbox-autocomplete').data('autosubmit') ) {
    jQuery('#mapbox-spinner-button').show()
    makeRequest( "POST", `users/user_location`, window.location_data )
    .done(response => {
      dtMapbox.user_location = response.user_location
      dtMapbox.user_id = response.user_id
      write_results_box()
    })
    .catch(err => { console.error(err) })
  } else {
    window.selected_location_grid_meta = window.location_data
    jQuery('#mapbox-spinner-button').hide()
    jQuery('#mapbox-clear-autocomplete').show()
  }
}

function clear_autocomplete(){
  jQuery('#mapbox-search').val('')
  jQuery('#mapbox-autocomplete-list').empty()
  jQuery('#mapbox-spinner-button').hide()
  jQuery('#mapbox-clear-autocomplete').hide()
}

function convert_level( level ) {
  switch(level){
    case 'administrative_area_level_0':
      level = 'admin0'
      break
    case 'administrative_area_level_1':
      level = 'admin1'
      break
    case 'administrative_area_level_2':
      level = 'admin2'
      break
    case 'administrative_area_level_3':
      level = 'admin3'
      break
    case 'administrative_area_level_4':
      level = 'admin4'
      break
    case 'administrative_area_level_5':
      level = 'admin5'
      break
  }
  return level
}
