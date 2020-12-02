/** Mapbox search box widget */
jQuery(document).ready(function(){

  // load widget
  if ( dtMapbox.post.length !== 0 ) {
    write_results_box()
  }
  jQuery( '#new-mapbox-search' ).on( "click", function() {
    write_input_widget()
  });
})

// write location list from post contents
function write_results_box() {
  jQuery('#mapbox-wrapper').empty().append(`
        <div id="location-grid-meta-results"></div>
        <div class="reveal" id="mapping-modal" data-v-offset="0" data-reveal>
          <div id="mapping-modal-contents"></div>
          <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
    `)

  let lgm_results = jQuery('#location-grid-meta-results')

  if ( ( dtMapbox.post.location_grid_meta !== undefined && dtMapbox.post.location_grid_meta.length !== 0 ) || ( dtMapbox.post.contact_address !== undefined && dtMapbox.post.contact_address.length !== 0 ) ) {

    if ( dtMapbox.post.location_grid_meta !== undefined && dtMapbox.post.location_grid_meta.length !== 0 ) {
      jQuery.each( dtMapbox.post.location_grid_meta, function(i,v) {
        lgm_results.append(`<div class="input-group">
                              <input type="text" class="active-location input-group-field" id="location-${_.escape( v.grid_meta_id )}" value="${_.escape( v.label )}" readonly />
                              <div class="input-group-button">
                                <button type="button" class="button success delete-button-style open-mapping-grid-modal" title="${ _.escape( dtMapbox.translations.open_mapping ) /*Open Modal*/}" data-id="${_.escape( v.grid_meta_id )}"><i class="fi-map"></i></button>
                                <button type="button" class="button alert delete-button-style delete-button mapbox-delete-button" title="${ _.escape( dtMapbox.translations.delete_location ) /*Delete Location*/}" data-id="${_.escape( v.grid_meta_id )}">&times;</button>
                              </div>
                            </div>`)
      })
    }

    if ( dtMapbox.post.contact_address !== undefined && dtMapbox.post.contact_address.length !== 0 ) {
      jQuery.each( dtMapbox.post.contact_address, function(i,v) {
        lgm_results.append(`<div class="input-group">
                              <input type="text" class="dt-communication-channel input-group-field" id="${_.escape( v.key )}" value="${_.escape( v.value )}" data-field="contact_address" />
                              <div class="input-group-button">
                                <button type="button" class="button success delete-button-style open-mapping-address-modal" title="${ _.escape( dtMapbox.translations.open_mapping ) /*Open Modal*/}" data-id="${_.escape( v.key )}" data-field="contact_address" data-key="${_.escape( v.key )}"><i class="fi-map"></i></button>
                                <button type="button" class="button alert input-height delete-button-style channel-delete-button delete-button" title="${ _.escape( dtMapbox.translations.delete_location ) /*Delete Location*/}" data-id="${_.escape( v.key )}" data-field="contact_address" data-key="${_.escape( v.key )}">&times;</button>
                              </div>
                            </div>`)
      })
    }

    delete_click_listener()
    open_modal_grid_listener()
    open_modal_address_listener()
    reset_tile_spacing()
  } /*end valid check*/

  new Foundation.Reveal(jQuery('#mapping-modal'))

  if ( lgm_results.children().length === 0 ) {
    write_input_widget()
  }
}

// adds listener for delete buttons
function delete_click_listener() {
  jQuery( '.mapbox-delete-button' ).on( "click", function(e) {

    let data = {
      location_grid_meta: {
        values: [
          {
            grid_meta_id: jQuery(this).data("id"),
            delete: true,
          }
        ]
      }
    }

    API.update_post( dtMapbox.post_type, dtMapbox.post_id, data ).then(function (response) {
      dtMapbox.post = response
      write_results_box()
    }).catch(err => { console.error(err) })

  });
}

function open_modal_grid_listener(){
  jQuery('.open-mapping-grid-modal').on("click", function(e){
    let grid_meta_id = e.currentTarget.dataset.id

    jQuery.each( dtMapbox.post.location_grid_meta, function(i,v){
      if ( grid_meta_id === v.grid_meta_id ) {
        console.log(v)
        return load_modal( v.lng, v.lat, v.level, v.label, v.grid_id )
      }
    })
  })
}

function open_modal_address_listener(){
  jQuery('.open-mapping-address-modal').on("click", function(e){

    let selected_key = jQuery(this).data('key')
    let selected_value = jQuery(`#${selected_key}`).val()
    if ( selected_value !== '' ){
      write_input_widget()

      let mabox_search_input = jQuery('#mapbox-search')
      mabox_search_input.val( selected_value )

      if ( dtMapbox.google_map_key ) {
        google_autocomplete( mabox_search_input.val() )
      } else {
        mapbox_autocomplete( mabox_search_input.val() )
      }

      jQuery(this).parent().parent().hide()
    }

  })
}

function load_modal( lng, lat, level, label, grid_id ){
  let spinner = '<span class="loading-spinner active"></span>'

  let container = jQuery('#mapping-modal')
  container.foundation('open')

  let content = jQuery('#mapping-modal-contents')
  content.empty().append(`
           <div class="grid-x">
            <div class="cell"><strong>${_.escape( label )}</strong></div>
            <div class="cell">
                <div id="map-wrapper">
                    <div id='map'>${spinner}</div>
                </div>
            </div>
           </div>
        `)

  let zoom = 15
  if ( 'admin0' === level ){
    zoom = 3
  } else if ( 'admin1' === level ) {
    zoom = 6
  } else if ( 'admin2' === level ) {
    zoom = 10
  }

  jQuery('#map').empty()
  mapboxgl.accessToken = dtMapbox.map_key;
  var map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [lng, lat],
    minZoom: 1,
    zoom: zoom
  });

  var marker = new mapboxgl.Marker()
    .setLngLat([lng, lat])
    .addTo(map);


}

// resets the tiles for new spacing
function reset_tile_spacing() {
  let masonGrid = jQuery('.grid')
  masonGrid.masonry({
    itemSelector: '.grid-item',
    percentPosition: true
  });
}

// writes the geocoding field at the top of the mapping area for adding a new location
function write_input_widget() {

  if ( jQuery('#mapbox-autocomplete').length === 0 ) {
    jQuery('#mapbox-wrapper').prepend(`
    <div id="mapbox-autocomplete" class="mapbox-autocomplete input-group" data-autosubmit="true">
        <input id="mapbox-search" type="text" name="mapbox_search" class="input-group-field" placeholder="${ dtMapbox.translations.search_location /*Search Location*/ }" />
        <div class="input-group-button">
            <button id="mapbox-spinner-button" class="button hollow" style="display:none;"><span class="loading-spinner active"></span></button>
            <button id="mapbox-clear-autocomplete" class="button alert input-height delete-button-style mapbox-delete-button" type="button" title="${ _.escape( dtMapbox.translations.clear ) /*Delete Location*/}" style="display:none;">&times;</button>
        </div>
        <div id="mapbox-autocomplete-list" class="mapbox-autocomplete-items"></div>
    </div>
  `)
  }

  let mapbox_search = jQuery('#mapbox-search')

  window.currentfocus = -1

  mapbox_search.on("keyup", function(e){
    var x = document.getElementById("mapbox-autocomplete-list");
    if (x) x = x.getElementsByTagName("div");
    if (e.which === 40) {
      /*If the arrow DOWN key is pressed,
      increase the currentFocus variable:*/
      console.log('down')
      window.currentfocus++;
      /*and and make the current item more visible:*/
      add_active(x);
    } else if (e.which === 38) { //up
      /*If the arrow UP key is pressed,
      decrease the currentFocus variable:*/
      console.log('up')
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

// delay location lookup (this saves unnecessary geocoding requests and keeps DT usage in the free tier of Mapbox
// essentially the timer is reset each time a new character is added, and waits 1 second after key strokes stop
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

// main processor and router for selection of autocomplete results
function close_all_lists(selection_id) {

  /* if Geocoding overridden, and plain text address selected */
  if( 'address' === selection_id ) {
    jQuery('#mapbox-autocomplete-list').empty()
    let address = jQuery('#mapbox-search').val()
    let update = { value: address }
    post_contact_address( update )
  }

  /* if Google Geocoding enabled*/
  else if ( dtMapbox.google_map_key ) {
    jQuery('#mapbox-search').val(window.mapbox_result_features[selection_id].description)
    jQuery('#mapbox-autocomplete-list').empty()

    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ placeId: window.mapbox_result_features[selection_id].place_id }, (results, status) => {
      if (status !== "OK") {
        console.log("Geocoder failed due to: " + status);
        return;
      }

      window.location_data = {
        location_grid_meta: {
          values: [
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

    /* if Mapbox enabled */
  } else {
    jQuery('#mapbox-search').val(window.mapbox_result_features[selection_id].place_name)
    jQuery('#mapbox-autocomplete-list').empty()

    window.location_data = {
      location_grid_meta: {
        values: [
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

// builds the mapbox autocomplete list for selection
function mapbox_autocomplete(address){
  console.log('mapbox_autocomplete: ' + address )
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
      console.log('no results')
      return
    }

    let list = jQuery('#mapbox-autocomplete-list')
    list.empty()

    jQuery.each( data.features, function( index, value ) {
      if ( 4 > index ){
        list.append(`<div data-value="${_.escape( index )}">${_.escape( value.place_name )}</div>`)
      }
    })

    list.append(`<div data-value="address" style="font-weight:bold;">${_.escape( window.dtMapbox.translations.use )}: "${_.escape( address )}"</div>`)

    jQuery('#mapbox-autocomplete-list div').on("click", function (e) {
      close_all_lists(e.target.attributes['data-value'].value);
    });

    // Set globals
    window.mapbox_result_features = data.features


  }); // end get request
} // end validate

// builds the autocomplete list from Google (if Google key is installed)
function google_autocomplete(address){
  console.log('google_autocomplete: ' + address )
  if ( address.length < 1 ) {
    jQuery('#mapbox-clear-autocomplete').hide()
    return;
  }

  let service = new google.maps.places.AutocompleteService();
  service.getPlacePredictions({ 'input': address }, function(predictions, status ) {
    let list = jQuery('#mapbox-autocomplete-list')
    list.empty()

    if ( status === 'OK' ) {
      jQuery.each( predictions, function( index, value ) {
        if ( 4 > index ) {
          list.append(`<div data-value="${index}">${_.escape(value.description)}</div>`)
        }
      })

      list.append(`<div data-value="address" style="font-weight:bold;">${_.escape( window.dtMapbox.translations.use )}: "${_.escape( address )}"</div>`)

      jQuery('#mapbox-autocomplete-list div').on("click", function (e) {
        close_all_lists(e.target.attributes['data-value'].value);
      });

      // Set globals
      window.mapbox_result_features = predictions
    }
    else if ( status === 'ZERO_RESULTS' ) {
      list.append(`<div>No Results Found</div>`)
      list.append(`<div data-value="address" style="font-weight:bold;">${_.escape( window.dtMapbox.translations.use )}: "${_.escape( address )}"</div>`)

      jQuery('#mapbox-autocomplete-list div').on("click", function (e) {
        close_all_lists(e.target.attributes['data-value'].value);
      });
    }
    else {
      console.log('Predictions was not successful for the following reason: ' + status)
    }
  })
}

// submits geocoded results and resets list
function post_geocoded_location() {
  if ( jQuery('#mapbox-autocomplete').data('autosubmit') ) {
    /* if post_type = user, else all other post types */
    jQuery('#mapbox-spinner-button').show()

    API.update_post( dtMapbox.post_type, dtMapbox.post_id, window.location_data ).then(function (response) {
      console.log( response )

      dtMapbox.post = response
      jQuery('#mapbox-wrapper').empty()
      write_results_box()

    }).catch(err => { console.error(err) })

  } else {
    window.selected_location_grid_meta = window.location_data
    jQuery('#mapbox-spinner-button').hide()
    jQuery('#mapbox-clear-autocomplete').show()
  }
}

// submits address override and resets list
function post_contact_address( update ) {

  let mapbox_autocomplete = jQuery('#mapbox-autocomplete')

  // if autosubmit true (normal behavior on details pages)
  if ( mapbox_autocomplete.data('autosubmit') ) {
    jQuery('#mapbox-spinner-button').show()

    API.update_post(window.dtMapbox.post_type, window.dtMapbox.post_id, {["contact_address"]: [update]}).then((updatedContact) => {
      dtMapbox.post = updatedContact
      jQuery('#mapbox-wrapper').empty()
      write_results_box()
    }).catch(handleAjaxError)

  } else {

    // if autosubmit false (primarily used on new post template)
    jQuery(`<div class="input-group" id="new_contact_address_container">
                <input type="text"
                       id="new_contact_address"
                       data-field="contact_address"
                       value="${update.value}"
                       class="dt-communication-channel input-group-field" />
                <div class="input-group-button">
                  <button class="button alert input-height delete-button-style channel-delete-button delete-button new-contact_address" data-field="contact_address" data-key="contact_address">&times;</button>
                </div>
           </div>`).insertAfter(mapbox_autocomplete)
    jQuery('button.delete-button.new-contact_address').on('click', function(){
      jQuery('#new_contact_address_container').remove()
    })
    clear_autocomplete()
  }
}

function clear_autocomplete(){
  jQuery('#mapbox-search').val('')
  jQuery('#mapbox-autocomplete-list').empty()
  jQuery('#mapbox-spinner-button').hide()
  jQuery('#mapbox-clear-autocomplete').hide()
}

// converts the long admin level response to a location grid version
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
