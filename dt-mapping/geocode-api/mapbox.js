jQuery(document).ready(function(){
  console.log(dtMapbox)

  // load widget
  if ( dtMapbox.post.length !== 0 ) {
    write_results_box()
  }
  jQuery( '#new-mapbox-search' ).on( "click", function() {
    write_input_widget()
  });

})

function write_results_box() {
  if ( dtMapbox.post.location_grid_meta !== undefined && dtMapbox.post.location_grid_meta.length !== 0 ) {

    jQuery('#mapbox-wrapper').append(`<div class="grid-x" id="location-grid-meta-results"></div>`)
    let lgm_results = jQuery('#location-grid-meta-results')
    jQuery.each( dtMapbox.post.location_grid_meta, function(i,v) {
      lgm_results.append(`<div class="cell small-10">
                             ${v.label}
                          </div>
                          <div class="cell small-2 float-right">
                              <button class="button clear delete-button mapbox-delete-button small" data-id="${v.grid_meta_id}">
                                  <img src="${dtMapbox.theme_uri}/dt-assets/images/invalid.svg" alt="delete">
                              </button>
                          </div>`)
    })

    lgm_results.append(`
      <div class="cell center">
        <button type="button" class="button clear small padding-bottom-0" onclick="open_map()">show map</button>
      </div>
      <!-- reveal -->
      <div class="reveal large" id="show-map" data-reveal>
          <div id="map-reveal-content"><!-- load content here --><div class="loader">Loading...</div></div>
          <button class="close-button" data-close aria-label="Close modal" type="button">
              <span aria-hidden="true">&times;</span>
          </button>
      </div>
    `)

    delete_location_listener()
    reset_tile_spacing()
    jQuery('#show-map').foundation()


  } /*end valid check*/
}

function open_map() {
  jQuery('#show-map').foundation('open')
}

function delete_location_listener() {
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
      console.log( response )
      location.reload()
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
    <div id="mapbox-autocomplete" class="mapbox-autocomplete input-group">
        <input id="mapbox-search" type="text" name="mapbox_search" placeholder="Search Location" />
        <div class="input-group-button">
            <button class="button hollow" id="mapbox-spinner-button" style="display:none;"><img src="${dtMapbox.spinner_url}" alt="spinner" style="width: 18px;" /></button>
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
    mapbox_autocomplete( jQuery('#mapbox-search').val() )

    // toggle buttons back
    jQuery('#mapbox-spinner-button').hide()
  }, 1000);

}
function clear_timer() {
  clearTimeout(window.validate_timer_id);
}
// end delay location lookup

function mapbox_autocomplete(address){
  console.log('mapbox_autocomplete: ' + address )
  if ( address.length < 1 ) {
    return;
  }

  let root = 'https://api.mapbox.com/geocoding/v5/mapbox.places/'
  let settings = '.json?types=country,region,postcode,district,place,locality,neighborhood,address&limit=6&access_token='
  let key = dtMapbox.map_key

  let url = root + encodeURI( address ) + settings + key

  jQuery.get( url, function( data ) {
    console.log(data)
    if( data.features.length < 1 ) {
      // destroy lists
      console.log('no results')
      return
    }

    let list = jQuery('#mapbox-autocomplete-list')
    list.empty()

    jQuery.each( data.features, function( index, value ) {
      list.append(`<div data-value="${index}">${_.escape( value.place_name )}</div>`)
    })

    jQuery('#mapbox-autocomplete-list div').on("click", function (e) {
      close_all_lists(e.target.attributes['data-value'].value);
    });

    // Set globals
    window.mapbox_result_features = data.features


  }); // end get request
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

  jQuery('#mapbox-search').val(window.mapbox_result_features[selection_id].place_name)
  jQuery('#mapbox-autocomplete-list').empty()
  jQuery('#mapbox-spinner-button').show()

  let data = {
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

  API.update_post( dtMapbox.post_type, dtMapbox.post_id, data ).then(function (response) {
    console.log( response )

    dtMapbox.post = response
    jQuery('#mapbox-wrapper').empty()
    write_results_box()

  }).catch(err => { console.error(err) })

}

function get_label_without_country( label, feature ) {

  if ( feature.context !== undefined ) {
    let newLabel = ''
    jQuery.each( feature.context, function(i,v) {

      if ( v.id.substring(0,7) === 'country' ) {
        label = label.replace( ', ' + v.text, '' ).trim()
      }

    } )
  }

  return label
}
