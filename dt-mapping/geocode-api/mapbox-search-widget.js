/** Mapbox search box widget */
jQuery(document).ready(function () {
  // load widget
  if (window.dtMapbox.post.length !== 0) {
    window.write_results_box();
  }
  jQuery('#new-mapbox-search').on('click', function () {
    window.write_input_widget();
  });
});

//declare escapeHTML function if it doesn't exist
//this file function might be included in other systems and should not depend on window.SHAREDFUNCTIONS
if (typeof window.escapeHTML === 'undefined') {
  window.escapeHTML = function (str) {
    if (typeof str === 'undefined') return '';
    if (typeof str !== 'string') return str;
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&apos;');
  };
}

// write location list from post contents
function write_results_box() {
  jQuery('#mapbox-wrapper').empty().append(`
        <div id="location-grid-meta-results"></div>
        <div class="reveal" id="mapping-modal" data-v-offset="0" data-reveal>
          <div id="mapping-modal-contents"></div>
          <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>`);

  let lgm_results = jQuery('#location-grid-meta-results');

  if (
    (window.dtMapbox.post.location_grid_meta !== undefined &&
      window.dtMapbox.post.location_grid_meta.length !== 0) ||
    (window.dtMapbox.post.contact_address !== undefined &&
      window.dtMapbox.post.contact_address.length !== 0)
  ) {
    if (
      window.dtMapbox.post.location_grid_meta !== undefined &&
      window.dtMapbox.post.location_grid_meta.length !== 0
    ) {
      jQuery.each(window.dtMapbox.post.location_grid_meta, function (i, v) {
        if (v.grid_meta_id) {
          lgm_results.append(`<div class="input-group">
            <input type="text" class="active-location input-group-field" id="location-${window.escapeHTML(v.grid_meta_id)}" dir="auto" value="${window.escapeHTML(v.label)}" readonly />
            <div class="input-group-button">
              <button type="button" class="button success delete-button-style open-mapping-grid-modal" title="${window.escapeHTML(window.dtMapbox.translations.open_mapping) /*Open Modal*/}" data-id="${window.escapeHTML(v.grid_meta_id)}"><i class="fi-map"></i></button>
              <button type="button" class="button alert delete-button-style delete-button mapbox-delete-button" title="${window.escapeHTML(window.dtMapbox.translations.delete_location) /*Delete Location*/}" data-id="${window.escapeHTML(v.grid_meta_id)}">&times;</button>
            </div>
          </div>`);
        } else {
          lgm_results.append(`<div class="input-group">
            <input type="text" class="dt-communication-channel input-group-field" id="${window.escapeHTML(v.key)}" value="${window.escapeHTML(v.label)}" dir="auto" data-field="contact_address" />
            <div class="input-group-button">
              <button type="button" class="button success delete-button-style open-mapping-address-modal"
                  title="${window.escapeHTML(window.dtMapbox.translations.open_mapping) /*Open Modal*/}"
                  data-id="${window.escapeHTML(v.key)}"
                  data-field="contact_address"
                  data-key="${window.escapeHTML(v.key)}">
                  <i class="fi-pencil"></i>
              </button>
              <button type="button" class="button alert input-height delete-button-style channel-delete-button delete-button" title="${window.escapeHTML(window.dtMapbox.translations.delete_location) /*Delete Location*/}" data-id="${window.escapeHTML(v.key)}" data-field="contact_address" data-key="${window.escapeHTML(v.key)}">&times;</button>
            </div>
          </div>`);
        }
      });
    }

    delete_click_listener();
    open_modal_grid_listener();
    open_modal_address_listener();
    reset_tile_spacing();
  } /*end valid check*/

  new window.Foundation.Reveal(jQuery('#mapping-modal'));

  if (lgm_results.children().length === 0) {
    window.write_input_widget();
  }
}

// adds listener for delete buttons
function delete_click_listener() {
  jQuery('.mapbox-delete-button').on('click', function () {
    let data = {
      location_grid_meta: {
        values: [
          {
            grid_meta_id: jQuery(this).data('id'),
            delete: true,
          },
        ],
      },
    };

    window.API.update_post(
      window.dtMapbox.post_type,
      window.dtMapbox.post_id,
      data,
    )
      .then(function (response) {
        window.dtMapbox.post = response;
        window.write_results_box();
      })
      .catch((err) => {
        console.error(err);
      });
  });
}

function open_modal_grid_listener() {
  jQuery('.open-mapping-grid-modal').on('click', function (e) {
    let grid_meta_id = e.currentTarget.dataset.id;

    jQuery.each(window.dtMapbox.post.location_grid_meta, function (i, v) {
      if (grid_meta_id === v.grid_meta_id) {
        return load_modal(v.lng, v.lat, v.level, v.label, v.grid_id);
      }
    });
  });
}

function open_modal_address_listener() {
  jQuery('.open-mapping-address-modal').on('click', function () {
    let selected_key = jQuery(this).data('key');
    let selected_value = jQuery(`#${selected_key}`).val();
    if (selected_value !== '') {
      window.write_input_widget();

      let mabox_search_input = jQuery('#mapbox-search');
      mabox_search_input.val(selected_value);

      if (window.dtMapbox.google_map_key) {
        google_autocomplete(mabox_search_input.val());
      } else {
        mapbox_autocomplete(mabox_search_input.val());
      }

      jQuery(this).parent().parent().hide();
    }
  });
}

function load_modal(lng, lat, level, label, grid_id) {
  let spinner = '<span class="loading-spinner active"></span>';

  let container = jQuery('#mapping-modal');
  container.foundation('open');

  let content = jQuery('#mapping-modal-contents');
  content.empty().append(`
           <div class="grid-x">
            <div class="cell"><strong>${window.escapeHTML(label)}</strong></div>
            <div class="cell">
                <div id="map-wrapper">
                    <div id='map'>${spinner}</div>
                </div>
            </div>
           </div>`);

  let zoom = 15;
  if ('admin0' === level) {
    zoom = 3;
  } else if ('admin1' === level) {
    zoom = 6;
  } else if ('admin2' === level) {
    zoom = 10;
  }

  jQuery('#map').empty();
  window.mapboxgl.accessToken = window.dtMapbox.map_key;
  var map = new window.mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [lng, lat],
    minZoom: 1,
    zoom: zoom,
  });

  var marker = new window.mapboxgl.Marker().setLngLat([lng, lat]).addTo(map);
}

// resets the tiles for new spacing
function reset_tile_spacing() {
  let masonGrid = jQuery('.grid');
  if (typeof masonGrid.masonry !== 'undefined') {
    masonGrid.masonry({
      itemSelector: '.grid-item',
      percentPosition: true,
    });
  }
}

// writes the geocoding field at the top of the mapping area for adding a new location
window.write_input_widget = function write_input_widget() {
  if (jQuery('#mapbox-autocomplete').length === 0) {
    jQuery('#mapbox-wrapper').prepend(`
    <div id="mapbox-autocomplete" class="mapbox-autocomplete input-group" data-autosubmit="true" data-add-address="true">
        <input id="mapbox-search" type="text" name="mapbox_search" class="input-group-field" autocomplete="off" dir="auto" placeholder="${window.dtMapbox.translations.search_location /*Search Location*/}" />
        <div class="input-group-button">
            <button id="mapbox-spinner-button" class="button hollow" style="display:none;border-color:lightgrey;">
                <span class="" style="border-radius: 50%;width: 24px;height: 24px;border: 0.25rem solid lightgrey;border-top-color: black;animation: spin 1s infinite linear;display: inline-block;"></span>
            </button>
            <button id="mapbox-clear-autocomplete" class="button alert input-height delete-button-style mapbox-delete-button" type="button" title="${window.escapeHTML(window.dtMapbox.translations.clear) /*Delete Location*/}" style="display:none;">&times;</button>
        </div>
        <div id="mapbox-autocomplete-list" class="mapbox-autocomplete-items"></div>
    </div>`);
  }

  let mapbox_search = jQuery('#mapbox-search');

  window.currentfocus = -1;

  //hide the geocoding options when the user clicks out of the input.
  let hide_list_setup = false;
  let setup_hide_list = function () {
    if (!hide_list_setup) {
      hide_list_setup = true;
      jQuery(document).mouseup(function (e) {
        let container = jQuery('#mapbox-autocomplete');
        container.removeClass('active');
        let list = jQuery('#mapbox-autocomplete-list');
        let isEmpty = !jQuery.trim(list.html());
        // if the target of the click isn't the container nor a descendant of the container
        if (
          !container.is(e.target) &&
          container.has(e.target).length === 0 &&
          !isEmpty
        ) {
          list.empty();
        }
      });
    }
  };

  mapbox_search.on('keydown', function (e) {
    if (e.which === 13) {
      /*If the ENTER key is pressed, prevent the form from being submitted,*/
      e.preventDefault();
    }
  });
  mapbox_search.on('keyup', function (e) {
    setup_hide_list();
    let x = document.getElementById('mapbox-autocomplete-list');
    if (x) x = x.getElementsByTagName('div');
    if (e.which === 40) {
      /*If the arrow DOWN key is pressed,
      increase the currentFocus variable:*/
      window.currentfocus++;
      /*and and make the current item more visible:*/
      add_active(x);
    } else if (e.which === 38) {
      //up
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
        close_all_lists(
          jQuery(
            jQuery('#mapbox-autocomplete-list div')[window.currentfocus],
          ).data('value'),
        );
      }
    } else {
      validate_timer();
    }
  });

  let mapbox_clear = jQuery('#mapbox-clear-autocomplete');
  mapbox_clear.hide().on('click', function () {
    clear_autocomplete();
  });

  reset_tile_spacing();
};
function add_active(x) {
  /*a function to classify an item as "active":*/
  if (!x) return false;
  /*start by removing the "active" class on all items:*/
  remove_active(x);
  if (window.currentfocus >= x.length) window.currentfocus = 0;
  if (window.currentfocus < 0) window.currentfocus = x.length - 1;
  /*add class "autocomplete-active":*/
  x[window.currentfocus].classList.add('mapbox-autocomplete-active');
}
function remove_active(x) {
  /*a function to remove the "active" class from all autocomplete items:*/
  for (var i = 0; i < x.length; i++) {
    x[i].classList.remove('mapbox-autocomplete-active');
  }
}

// delay location lookup (this saves unnecessary geocoding requests and keeps DT usage in the free tier of Mapbox
// essentially the timer is reset each time a new character is added, and waits 1 second after key strokes stop
window.validate_timer_id = '';
function validate_timer() {
  clear_timer();

  // toggle buttons
  jQuery('#mapbox-spinner-button').show();

  // set timer
  window.validate_timer_id = setTimeout(function () {
    //add space under the location input and reset tiles so options are not hidden off the page.
    jQuery('#mapbox-autocomplete').addClass('active');
    reset_tile_spacing();

    // call geocoder
    if (window.dtMapbox.google_map_key) {
      google_autocomplete(jQuery('#mapbox-search').val());
    } else {
      mapbox_autocomplete(jQuery('#mapbox-search').val());
    }

    // toggle buttons back
    jQuery('#mapbox-spinner-button').hide();
    jQuery('#mapbox-clear-autocomplete').show();
  }, 700);
}
function clear_timer() {
  clearTimeout(window.validate_timer_id);
}
// end delay location lookup

// main processor and router for selection of autocomplete results
function close_all_lists(selection_id) {
  if (typeof selection_id === 'undefined' || selection_id === null) {
    return;
  }
  /* if Geocoding overridden, and plain text address selected */
  if ('address' === selection_id) {
    jQuery('#mapbox-autocomplete-list').empty();
    let address = jQuery('#mapbox-search').val();
    let update = { value: address };
    post_contact_address(update);
  } else if (window.dtMapbox.google_map_key) {
    /* if Google Geocoding enabled*/
    jQuery('#mapbox-search').val(
      window.mapbox_result_features[selection_id].description,
    );
    jQuery('#mapbox-autocomplete-list').empty();

    const geocoder = new window.google.maps.Geocoder();
    geocoder.geocode(
      { placeId: window.mapbox_result_features[selection_id].place_id },
      (results, status) => {
        if (status !== 'OK') {
          console.log('Geocoder failed due to: ' + status);
          return;
        }

        window.location_data = {
          location_grid_meta: {
            values: [
              {
                lng: results[0].geometry.location.lng(),
                lat: results[0].geometry.location.lat(),
                level: convert_level(results[0].types[0]),
                label:
                  window.mapbox_result_features[selection_id].description ||
                  results[0].formatted_address,
                source: 'user',
              },
            ],
          },
        };
        post_geocoded_location();
      },
    );

    /* if Mapbox enabled */
  } else {
    jQuery('#mapbox-search').val(
      window.mapbox_result_features[selection_id].place_name,
    );
    jQuery('#mapbox-autocomplete-list').empty();

    window.location_data = {
      location_grid_meta: {
        values: [
          {
            lng: window.mapbox_result_features[selection_id].center[0],
            lat: window.mapbox_result_features[selection_id].center[1],
            level: window.mapbox_result_features[selection_id].place_type[0],
            label: window.mapbox_result_features[selection_id].place_name,
            source: 'user',
          },
        ],
      },
    };
    post_geocoded_location();
  }
}

// builds the mapbox autocomplete list for selection
function mapbox_autocomplete(address) {
  if (address.length < 1) {
    jQuery('#mapbox-clear-autocomplete').hide();
    return;
  }

  let root = 'https://api.mapbox.com/geocoding/v5/mapbox.places/';
  let settings =
    '.json?types=country,region,postcode,district,place,locality,neighborhood,address&limit=6&access_token=';
  let key = window.dtMapbox.map_key;
  let url = root + encodeURI(address) + settings + key;

  fetch(url, {
    referrerPolicy: 'strict-origin-when-cross-origin',
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.features.length < 1) {
        // destroy lists
        console.log('no results');
        return;
      }

      let list = jQuery('#mapbox-autocomplete-list');
      list.empty();

      jQuery.each(data.features, function (index, value) {
        if (4 > index) {
          list.append(
            `<div data-value="${window.escapeHTML(index)}">${window.escapeHTML(value.place_name)}</div>`,
          );
        }
      });

      let add_address = jQuery('#mapbox-autocomplete').data('add-address');
      if (typeof add_address === 'undefined' || add_address === true) {
        list.append(
          `<div data-value="address" style="font-weight:bold;">${window.escapeHTML(window.dtMapbox.translations.use)}: <span dir="auto">"${window.escapeHTML(address)}"</span></div>`,
        );
      }

      jQuery('#mapbox-autocomplete-list div').on('click', function (e) {
        close_all_lists(e.target.attributes['data-value'].value);
      });

      // Set globals
      window.mapbox_result_features = data.features;
    }); // end get request
} // end validate

// builds the autocomplete list from Google (if Google key is installed)
function google_autocomplete(address) {
  if (address.length < 1) {
    jQuery('#mapbox-clear-autocomplete').hide();
    return;
  }

  let service = new window.google.maps.places.AutocompleteService();
  service.getPlacePredictions(
    { input: address },
    function (predictions, status) {
      let list = jQuery('#mapbox-autocomplete-list');
      list.empty();

      if (status === 'OK') {
        jQuery.each(predictions, function (index, value) {
          if (4 > index) {
            list.append(
              `<div data-value="${index}">${window.escapeHTML(value.description)}</div>`,
            );
          }
        });

        let add_address = jQuery('#mapbox-autocomplete').data('add-address');
        if (typeof add_address === 'undefined' || add_address === true) {
          list.append(
            `<div data-value="address" style="font-weight:bold;">${window.escapeHTML(window.dtMapbox.translations.use)}: <span dir="auto">"${window.escapeHTML(address)}"</span></div>`,
          );
        }

        jQuery('#mapbox-autocomplete-list div').on('click', function (e) {
          close_all_lists(e.target.attributes['data-value'].value);
        });

        // Set globals
        window.mapbox_result_features = predictions;
      } else if (status === 'ZERO_RESULTS') {
        list.append(`<div>No Results Found</div>`);
        list.append(
          `<div data-value="address" style="font-weight:bold;">${window.escapeHTML(window.dtMapbox.translations.use)}: <span dir="auto">"${window.escapeHTML(address)}"</span></div>`,
        );

        jQuery('#mapbox-autocomplete-list div').on('click', function (e) {
          close_all_lists(e.target.attributes['data-value'].value);
        });
      } else {
        console.log(
          'Predictions was not successful for the following reason: ' + status,
        );
      }
    },
  );
}

// submits geocoded results and resets list
function post_geocoded_location() {
  if (jQuery('#mapbox-autocomplete').data('autosubmit')) {
    /* if post_type = user, else all other post types */
    jQuery('#mapbox-spinner-button').show();

    window.API.update_post(
      window.dtMapbox.post_type,
      window.dtMapbox.post_id,
      window.location_data,
    )
      .then(function (response) {
        window.dtMapbox.post = response;
        jQuery('#mapbox-wrapper').empty();
        window.write_results_box();
      })
      .catch((err) => {
        console.error(err);
      });
  } else {
    window.selected_location_grid_meta = window.location_data;
    jQuery('#mapbox-spinner-button').hide();
    jQuery('#mapbox-clear-autocomplete').show();
  }
}

// submits address override and resets list
function post_contact_address(update) {
  let mapbox_autocomplete = jQuery('#mapbox-autocomplete');

  // if autosubmit true (normal behavior on details pages)
  if (mapbox_autocomplete.data('autosubmit')) {
    jQuery('#mapbox-spinner-button').show();

    window.API.update_post(window.dtMapbox.post_type, window.dtMapbox.post_id, {
      ['contact_address']: [update],
    })
      .then((updatedContact) => {
        window.dtMapbox.post = updatedContact;
        jQuery('#mapbox-wrapper').empty();
        window.write_results_box();
      })
      .catch(window.handleAjaxError);
  } else {
    // if autosubmit false (primarily used on new post template)
    jQuery(`<div class="input-group" id="new_contact_address_container">
                <input type="text"
                       id="new_contact_address"
                       data-field="contact_address"
                       value="${update.value}"
                       dir="auto"
                       class="dt-communication-channel input-group-field" />
                <div class="input-group-button">
                  <button class="button alert input-height delete-button-style channel-delete-button delete-button new-contact_address" data-field="contact_address" data-key="contact_address">&times;</button>
                </div>
           </div>`).insertAfter(mapbox_autocomplete);
    jQuery('button.delete-button.new-contact_address').on('click', function () {
      jQuery('#new_contact_address_container').remove();
    });
    clear_autocomplete();
  }
}

function clear_autocomplete() {
  jQuery('#mapbox-search').val('');
  jQuery('#mapbox-autocomplete-list').empty();
  jQuery('#mapbox-spinner-button').hide();
  jQuery('#mapbox-clear-autocomplete').hide();
}

// converts the long admin level response to a location grid version
function convert_level(level) {
  switch (level) {
    case 'administrative_area_level_0':
      level = 'admin0';
      break;
    case 'administrative_area_level_1':
      level = 'admin1';
      break;
    case 'administrative_area_level_2':
      level = 'admin2';
      break;
    case 'administrative_area_level_3':
      level = 'admin3';
      break;
    case 'administrative_area_level_4':
      level = 'admin4';
      break;
    case 'administrative_area_level_5':
      level = 'admin5';
      break;
  }
  return level;
}
