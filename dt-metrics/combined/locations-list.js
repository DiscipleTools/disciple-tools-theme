"use strict";
let LISTDATA = null

jQuery(document).ready(function() {
  jQuery('#metrics-sidemenu').foundation('down', jQuery(`#${window.wp_js_object.base_slug}-menu`));

  let chartDiv = jQuery('#chart')
  chartDiv.empty().html(`
    <div id="mapping_chart"></div>
  `)


  if( window.wpApiShare.url_path.startsWith( 'metrics/combined/locations_list' )) {
    LISTDATA = window.wp_js_object.mapping_module
    page_mapping_list()
  }

})

/**********************************************************************************************************************
 *
 * LIST
 *
 * This page allows for drill-down into the locations and related reports.
 *
 **********************************************************************************************************************/

function page_mapping_list() {
  "use strict";
  let chartDiv = jQuery('#chart')
  chartDiv.empty().html(`
    <style>
      .map_wrapper {}
      .map_header_wrapper {
          float:left;
          position:absolute;
      }
      .section_title {
          font-size:1.5em;
      }
      .current_level {}
      .location_list {
      }
      .map_hr {
        max-width:100%;
        margin: 10px 0;
      }
      @media screen and (max-width : 640px){
        #country-list-table {
          margin-left: 5px !important;
        }
        .map_header_wrapper {
          position:relative;
          text-align: center;
          width: 100%;
        }
      }
    </style>

    <!-- List Widget -->
    <div id="map_wrapper" class="map_wrapper">
      <div id="map_drill_wrapper" class="grid-x grid-margin-x map_drill_wrapper">
        <div id="location_list_drilldown" class="cell auto location_list_drilldown"></div>
      </div>
      <hr id="map_hr_1" class="map_hr">

      <div id="map_header_wrapper" class="map_header_wrapper">
        <strong id="section_title" class="section_title" ></strong><br>
        <span id="current_level" class="current_level"></span>
      </div>

      <div id="location_list" class="location_list"></div>
      <hr id="map_hr_2" class="map_hr">
    </div> <!-- end widget -->
  `);

  get_data(false).then(response=>{
    LISTDATA.data = response
    // set the depth of the drill down
    LISTDATA.settings.hide_final_drill_down = false
    // load drill down
    window.DRILLDOWN.get_drill_down('location_list_drilldown', LISTDATA.settings.current_map, LISTDATA.settings.cached)
  }).fail(err=>{
    console.log(err)
  })
}

window.DRILLDOWN.location_list_drilldown = function( grid_id ) {
  location_grid_list( 'location_list', grid_id )
}
function get_data( force_refresh = false ) {
  let spinner = jQuery('.loading-spinner')
  spinner.addClass('active')
  return jQuery.ajax({
    type: "GET",
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: `${window.wp_js_object.rest_endpoints_base}/data?refresh=${force_refresh}`,
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', window.wp_js_object.nonce );
    },
  })
    .then( function( response ) {
      spinner.removeClass('active')
      return response
    })
    .fail(function (err) {
      spinner.removeClass('active')
      console.log("error")
      console.log(err)
    })
}

function location_grid_list( div, grid_id ) {
  DRILLDOWN.show_spinner()

  // Find data source before build
  if ( grid_id === 'top_map_level' ) {
    let map_data = null
    let default_map_settings = LISTDATA.settings.default_map_settings

    if ( DRILLDOWN.isEmpty( default_map_settings.children ) ) {
      map_data = LISTDATA.data[default_map_settings.parent]
    }
    else {
      if ( default_map_settings.children.length < 2 ) {
        // single child
        map_data = LISTDATA.data[default_map_settings.children[0]]
      } else {
        // multiple child
        jQuery('#section_title').empty()
        jQuery('#current_level').empty()
        jQuery('#location_list').empty().append('Select Location')
        DRILLDOWN.hide_spinner()
        return;
      }
    }

    // Initialize Location Data
    if ( map_data === undefined ) {
      console.log('error getting map_data')
      return;
    }

    build_location_grid_list( div, map_data )
  }
  else if ( LISTDATA.data[grid_id] === undefined ) {
    let rest = LISTDATA.settings.endpoints.get_map_by_grid_id_endpoint

    jQuery.ajax({
      type: rest.method,
      contentType: "application/json; charset=utf-8",
      data: JSON.stringify( { 'grid_id': grid_id, 'cached': LISTDATA.settings.cached, 'cached_length': LISTDATA.settings.cached_length } ),
      dataType: "json",
      url: LISTDATA.settings.root + rest.namespace + rest.route,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', rest.nonce );
      },
    })
      .done( function( response ) {
        LISTDATA.data[grid_id] = response
        build_location_grid_list( div, LISTDATA.data[grid_id] )
      })
      .fail(function (err) {
        console.log("error")
        console.log(err)
        DRILLDOWN.hide_spinner()
      })

  } else {
    build_location_grid_list( div, LISTDATA.data[grid_id] )
  }

  // build list
  function build_location_grid_list( div, map_data ) {
    let translations = window.wp_js_object.translations

    // Place Title
    let title = jQuery('#section_title')
    title.empty().html(map_data.self.name)

    // Population Division and Check for Custom Division
    let pd_settings = LISTDATA.settings.population_division
    let population_division = pd_settings.base
    if ( ! DRILLDOWN.isEmpty( pd_settings.custom ) ) {
      jQuery.each( pd_settings.custom, function(i,v) {
        if ( map_data.self.grid_id === i ) {
          population_division = v
        }
      })
    }

    // Self Data
    let self_population = map_data.self.population_formatted
    jQuery('#current_level').empty().html(`${window.lodash.escape(translations.population)} ${window.lodash.escape( self_population )}`)

    // Build List
    let locations = jQuery('#location_list')
    locations.empty()

    let html = `<table id="country-list-table" class="display">`

    // Header Section
    html += `<thead><tr><th>${window.lodash.escape(translations.name)}</th><th>${window.lodash.escape(translations.population)}</th>`

    /* Additional Columns */
    if ( LISTDATA.data.custom_column_labels ) {
      jQuery.each( LISTDATA.data.custom_column_labels, function(i,v) {
        html += `<th>${window.lodash.escape( v.label )}</th>`
      })
    }
    /* End Additional Columns */

    html += `</tr></thead>`
    // End Header Section

    // Children List Section
    let sorted_children =  window.lodash.sortBy(map_data.children, [function(o) { return o.name; }]);

    html += `<tbody>`

    jQuery.each( sorted_children, function(i, v) {
      let population = v.population_formatted

      html += `<tr>
                    <td><strong><a onclick="DRILLDOWN.get_drill_down('location_list_drilldown', ${window.lodash.escape( v.grid_id )} )">${window.lodash.escape( v.name )}</a></strong></td>
                    <td>${window.lodash.escape( population )}</td>`

      /* Additional Columns */
      if ( LISTDATA.data.custom_column_data[v.grid_id] ) {
        jQuery.each( LISTDATA.data.custom_column_data[v.grid_id], function(ii,vv) {
          html += `<td><strong>${window.lodash.escape( vv )}</strong></td>`
        })
      } else {
        jQuery.each( LISTDATA.data.custom_column_labels, function(ii,vv) {
          html += `<td class="grey">0</td>`
        })
      }
      /* End Additional Columns */

      html += `</tr>`

    })
    html += `</tbody>`
    // end Child section

    html += `</table>`
    locations.append(html)

    let isMobile = window.matchMedia("only screen and (max-width: 760px)").matches;

    if (isMobile) {
      jQuery('#country-list-table').DataTable({
        "paging":   false,
        "scrollX": true
      });
    } else {
      jQuery('#country-list-table').DataTable({
        "paging":   false
      });
    }

    DRILLDOWN.hide_spinner()
  }
}
