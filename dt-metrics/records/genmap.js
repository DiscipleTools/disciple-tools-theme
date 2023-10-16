jQuery(document).ready(function($) {
  if (window.wpApiShare.url_path.startsWith('metrics/records/genmap')) {
    project_records_genmap();
  }

  function project_records_genmap() {
    "use strict";
    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner active"></span> '

    chart.empty().html(spinner)
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#records-menu'));

    let translations = dtMetricsProject.translations

    chart.empty().html(`
          <div class="grid-x grid-padding-x">
              <div class="cell medium-8">
                  <span>
                    <select id="select_post_types" style="width: 200px;"></select>
                  </span>
                  <span>
                    <select id="select_post_type_fields" style="width: 200px;"></select>
                  </span>
                  <span>
                    <i class="fi-loop" onclick="window.load_genmap()" style="font-size: 1.5em; padding:.5em;cursor:pointer;"></i>
                  </span>
              </div>
              <div class="cell medium-4" >
                <h2 style="float:right;">${window.lodash.escape(translations.title)}</h2>
              </div>
          </div>
          <hr>
          <div class="grid-x grid-padding-x">
              <div class="cell medium-9">
                <div id="genmap" style="width: 100%; border: 1px solid lightgrey; overflow:scroll;"></div>
              </div>
              <div class="cell medium-3">
                <div id="genmap-details"></div>
              </div>

           <div id="modal" class="reveal" data-reveal></div>
       `)

    window.load_genmap = ( focus_id = null ) => {
      jQuery('#genmap-details').empty();
      let select_post_type_fields = jQuery('#select_post_type_fields');

      let selected_post_type = jQuery('#select_post_types').val();
      let payload = {
        'p2p_type': jQuery(select_post_type_fields).find('option:selected').data('p2p_key'),
        'p2p_direction': jQuery(select_post_type_fields).find('option:selected').data('p2p_direction'),
        'post_type': selected_post_type,
        'gen_depth_limit': 10
      };

      // Dynamically update URL parameters.
      const url = new URL(window.location);
      url.searchParams.set('record_type', selected_post_type);
      url.searchParams.set('field', jQuery(select_post_type_fields).val());

      if (focus_id) {
        payload['focus_id'] = focus_id;
        url.searchParams.set('focus_id', focus_id);
      } else {
          url.searchParams.delete('focus_id');
      }

      window.history.pushState(null, document.title, url.search);

      // Fetch generational map chart.
      makeRequest('POST', 'metrics/records/genmap', payload )
      .promise()
      .then(response => {
        console.log(response)
        let container = jQuery('#genmap')
        container.empty()

        var nodeTemplate = function(data) {
          return `
            <div class="title" data-item-id="${data.id}">${data.name}</div>
            <div class="content">${data.content}</div>
          `;
        };

        container.orgchart({
          'data': response,
          'nodeContent': 'content',
          'direction': 'l2r',
          'nodeTemplate': nodeTemplate,
        });

        let container_height = window.innerHeight - 200 // because it is rotated
        container.height(container_height)

        container.off('click', '.node' )
        container.on('click', '.node', function () {
          let node = jQuery(this)
          let node_id = node.attr('id')
          open_modal_details(node_id, selected_post_type)
        })

      })
    }

    // Set initial states and default to any specified url parameters.
    refresh_post_type_select_list(function () {

      const request_params = fetch_url_search_params();

      // Ensure required parts are present, in order to proceed.
      if ( request_params && request_params.record_type ) {
        jQuery('#select_post_types').val(request_params.record_type);
      }

      refresh_post_type_field_select_list( function() {
        if ( request_params && request_params.field ) {
          jQuery('#select_post_type_fields').val(request_params.field);
          window.load_genmap((request_params.focus_id) ? request_params.focus_id : null);

        } else {
          window.load_genmap();
        }
      });

    });

    jQuery('#select_post_types').on('change', function(e) {
        refresh_post_type_field_select_list(window.load_genmap);
    });

    jQuery('#select_post_type_fields').on('change', function(e) {
      window.load_genmap();
    });

    jQuery(document).on('click', '.genmap-details-add-child', function(e) {
      let control = jQuery(e.currentTarget);
      display_add_child_modal(jQuery(control).data('post_type'), jQuery(control).data('post_id'), jQuery(control).data('post_name'));
    });

    jQuery(document).on('click', '.genmap-details-add-focus', function(e) {
        let control = jQuery(e.currentTarget);
        let post_type = jQuery(control).data('post_type');
        let post_id = jQuery(control).data('post_id');
        let p2p_key = jQuery('#select_post_type_fields').find('option:selected').data('p2p_key');

        handle_focus(post_type, post_id, p2p_key);
    });

    jQuery(document).on('click', '#gen_tree_add_child_but', function (e) {
      handle_add_child();
    });
  }

  function fetch_url_search_params() {
      const url_search_params = new URLSearchParams(window.location.search);

      let request_params = {};
      for ( const param of url_search_params ) {
          if ( Array.isArray( param ) && param.length === 2 ) {
              request_params[ param[0] ] = param[1];
          }
      }

      return request_params;
  }

  function display_add_child_modal(post_type, post_id, post_name) {
    let list_html = `
    <input id="gen_tree_add_child_post_type" type="hidden" value="${post_type}" />
    <input id="gen_tree_add_child_post_id" type="hidden" value="${post_id}" />
    <label>
      ${window.lodash.escape(window.dtMetricsProject.translations.modal.add_child_name_title)}
      <input id="gen_tree_add_child_name" type="text" />
    </label>`;

    let buttons_html = `<button id="gen_tree_add_child_but" class="button" type="button">${window.lodash.escape(window.dtMetricsProject.translations.modal.add_child_but)}</button>`;

    let modal = jQuery('#template_metrics_modal');
    let modal_buttons = jQuery('#template_metrics_modal_buttons');
    let title = window.dtMetricsProject.translations.modal.add_child_title + ` [ ${post_name} ]`;
    let content = jQuery('#template_metrics_modal_content');

    jQuery(modal_buttons).empty().html(buttons_html);

    jQuery('#template_metrics_modal_title').empty().html(window.lodash.escape(title));
    jQuery(content).css('max-height', '300px');
    jQuery(content).css('overflow', 'auto');
    jQuery(content).empty().html(list_html);
    jQuery(modal).foundation('open');
  }

  function handle_add_child() {
    let post_type = jQuery('#gen_tree_add_child_post_type').val();
    let parent_id = jQuery('#gen_tree_add_child_post_id').val();
    let child_title = jQuery('#gen_tree_add_child_name').val();
    let field_id = jQuery('#select_post_type_fields').val();

    if (post_type && parent_id && child_title && field_id) {
      window.API.create_post(post_type, {
        'title': child_title,
        'additional_meta': {
          'created_from': parent_id,
          'add_connection': field_id
        }
      }).then(new_post => {

        // Close modal and refresh generation tree, accordingly, based on focussed state.
        jQuery('#template_metrics_modal').foundation('close');

        // Ensure to respect any existing focussed selections.
        const request_params = fetch_url_search_params();
        window.load_genmap( ( request_params && request_params.focus_id ) ? request_params.focus_id : null );

      }).catch(function (error) {
        console.error(error);
      });
    }
  }

  function handle_focus(post_type, post_id, p2p_key) {
    if ( post_id ) {
      window.load_genmap( post_id );
    }
  }

  function refresh_post_type_select_list(callback = null) {
    let post_types = dtMetricsProject.post_types;
    if ( post_types ) {
      let post_type_select = jQuery('#select_post_types');
      jQuery(post_type_select).empty();

      // Only focus on post types with valid connection types.
      let filtered_post_types = [];
      jQuery.each( post_types, function ( post_type, post_type_obj ) {
        if ( post_type_obj && post_type_obj.connection_types && Array.isArray( post_type_obj.connection_types ) && post_type_obj.connection_types.length > 0 ) {
          filtered_post_types.push({
            value: post_type,
            text: post_type_obj.label_plural
          });
        }
      });

      let sorted_post_types = window.lodash.sortBy(filtered_post_types, [function (o) {
        return o.text;
      }]);
      jQuery.each( sorted_post_types, function ( idx, option ) {
        jQuery(post_type_select).append(jQuery('<option>', {
          value: option.value,
          text: option.text
        }));
      });
    }

    if ( callback ) {
      callback();
    }
  }

  function refresh_post_type_field_select_list(callback = null) {
    let post_types = dtMetricsProject.post_types;
    let selected_post_type = jQuery('#select_post_types').val();
    if (post_types && selected_post_type && post_types[selected_post_type]) {
      let post_type_fields_select = jQuery('#select_post_type_fields');
      jQuery(post_type_fields_select).empty();

      // Only capture connection type fields.
      let filtered_post_type_fields = [];
      jQuery.each( post_types[selected_post_type].connection_types, function ( idx, field_id ) {
        if ( post_types[selected_post_type]['fields'][field_id] ) {
          filtered_post_type_fields.push({
            value: field_id,
            text: post_types[selected_post_type]['fields'][field_id]['name'],
            p2p_key: post_types[selected_post_type]['fields'][field_id]['p2p_key'],
            p2p_direction: post_types[selected_post_type]['fields'][field_id]['p2p_direction'],
          });
        }
      });

      let uniq_post_type_fields = window.lodash.uniqWith(filtered_post_type_fields, function (a, b) {
        return (a.p2p_key === b.p2p_key) && (a.p2p_direction === b.p2p_direction);
      });

      let sorted_post_type_fields = window.lodash.sortBy(uniq_post_type_fields, [function (o) {
        return o.text;
      }]);

      jQuery.each( sorted_post_type_fields, function ( idx, option ) {
        jQuery('<option>').val(option.value).text(option.text).attr('data-p2p_key', option.p2p_key).attr('data-p2p_direction', option.p2p_direction).appendTo(post_type_fields_select);
      });
    }

    if ( callback ) {
      callback();
    }
  }

  function open_modal_details( id, post_type ) {
    if ( id ) {
      let spinner = ' <span class="loading-spinner active"></span> '
      jQuery('#genmap-details').html(spinner)

      makeRequest('GET', post_type + '/' + id, null, 'dt-posts/v2/')
      .promise()
      .then(data => {
        console.log(data)
        let container = jQuery('#genmap-details')
        container.empty()
        if (data) {
          container.html(window.detail_template(post_type, data))
        }
      })
      .catch(error => {
        jQuery('#genmap-details').html('');
      });
    }
  }

  window.detail_template = ( post_type, data ) => {
    if ( post_type === 'contacts' ) {

      let assign_to = ''
      if ( typeof data.assigned_to !== 'undefined' ) {
        assign_to = data.assigned_to.display
      }
      let coach_list = ''
      if ( typeof data.coached_by !== 'undefined' ) {
        coach_list = '<ul>'
        jQuery.each( data.coached_by, function( index, value ) {
          coach_list += '<li>' + value['post_title'] + '</li>'
        })
        coach_list += '</ul>'
      }
      let group_list = ''
      if ( typeof data.groups !== 'undefined' ) {
        group_list = '<ul>'
        jQuery.each( data.groups, function( index, value ) {
          group_list += '<li>' + value['post_title'] + '</li>'
        })
        group_list += '</ul>'
      }
      let status = ''
      if ( typeof data.overall_status !== 'undefined' ) {
        status = data.overall_status['label']
      }
      return `
        <div class="grid-x grid-padding-x">
          <div class="cell">
            <h2>${data.title}</h2><hr>
          </div>
          <div class="cell">
            Status: ${status}
          </div>
          <div class="cell">
            Groups:
            ${group_list}
          </div>
          <div class="cell">
            Assigned To:
            ${assign_to}
          </div>
          <div class="cell">
            Coaches: <br>
            ${coach_list}
          </div>
          <div class="cell"><hr>
            <a href="${dtMetricsProject.site_url}/${post_type}/${data.ID}" target="_blank" class="button">
                <i class="mdi mdi-id-card" style="font-size: 20px;"></i>
            </a>
            <a href="#" class="button genmap-details-add-child" data-post_type="${window.lodash.escape(data.post_type)}" data-post_id="${window.lodash.escape(data.ID)}" data-post_name="${window.lodash.escape(data.title)}">
                <i class="mdi mdi-account-multiple-plus-outline" style="font-size: 20px;"></i>
            </a>
            <a href="#" class="button genmap-details-add-focus" data-post_type="${window.lodash.escape(data.post_type)}" data-post_id="${window.lodash.escape(data.ID)}" data-post_name="${window.lodash.escape(data.title)}">
                <i class="mdi mdi-bullseye-arrow gen-node-control-focus" style="font-size: 20px;"></i>
            </a>
          </div>
        </div>
      `;
    } else if ( post_type === 'groups' ) {

      let members_count = 0
      if ( typeof data.member_count !== 'undefined' ) {
        members_count = data.member_count
      }
      let assign_to = ''
      if ( typeof data.assigned_to !== 'undefined' ) {
        assign_to = data.assigned_to.display
      }

      let member_list = ''
      if ( typeof data.members !== 'undefined' ) {
        member_list = '<ul>'
        jQuery.each( data.members, function( index, value ) {
          member_list += '<li>' + value['post_title'] + '</li>'
        })
        member_list += '</ul>'
      }
      let coach_list = ''
      if ( typeof data.coached_by !== 'undefined' ) {
        coach_list = '<ul>'
        jQuery.each( data.coached_by, function( index, value ) {
          coach_list += '<li>' + value['post_title'] + '</li>'
        })
        coach_list += '</ul>'
      }
      let status = ''
      if ( typeof data.group_status !== 'undefined' ) {
        status = data.group_status['label']
      }
      let type = ''
      if ( typeof data.group_type !== 'undefined' ) {
        type = data.group_type['label']
      }
      return `
        <div class="grid-x grid-padding-x">
          <div class="cell">
            <h2>${data.title}</h2><hr>
          </div>
          <div class="cell">
            Type: ${status}
          </div>
          <div class="cell">
            Type: ${type}
          </div>
          <div class="cell">
            Member Count: ${members_count}
          </div>
          <div class="cell">
            Members: <br>
            ${member_list}
          </div>
          <div class="cell">
            Assigned To:
            ${assign_to}
          </div>
          <div class="cell">
            Coaches: <br>
            ${coach_list}
          </div>
          <div class="cell"><hr>
            <a href="${dtMetricsProject.site_url}/${post_type}/${data.ID}" target="_blank" class="button">
                <i class="mdi mdi-id-card" style="font-size: 20px;"></i>
            </a>
            <a href="#" class="button genmap-details-add-child" data-post_type="${window.lodash.escape(data.post_type)}" data-post_id="${window.lodash.escape(data.ID)}" data-post_name="${window.lodash.escape(data.title)}">
                <i class="mdi mdi-account-multiple-plus-outline" style="font-size: 20px;"></i>
            </a>
            <a href="#" class="button genmap-details-add-focus" data-post_type="${window.lodash.escape(data.post_type)}" data-post_id="${window.lodash.escape(data.ID)}" data-post_name="${window.lodash.escape(data.title)}">
                <i class="mdi mdi-bullseye-arrow gen-node-control-focus" style="font-size: 20px;"></i>
            </a>
          </div>
        </div>
      `
    } else {
      return `
        <div class="grid-x grid-padding-x">
          <div class="cell">
            <h2>${data.title}</h2>
          </div>
          <div class="cell"><hr>
            <a href="${dtMetricsProject.site_url}/${post_type}/${data.ID}" target="_blank" class="button">
                <i class="mdi mdi-id-card" style="font-size: 20px;"></i>
            </a>
            <a href="#" class="button genmap-details-add-child" data-post_type="${window.lodash.escape(data.post_type)}" data-post_id="${window.lodash.escape(data.ID)}" data-post_name="${window.lodash.escape(data.title)}">
                <i class="mdi mdi-account-multiple-plus-outline" style="font-size: 20px;"></i>
            </a>
            <a href="#" class="button genmap-details-add-focus" data-post_type="${window.lodash.escape(data.post_type)}" data-post_id="${window.lodash.escape(data.ID)}" data-post_name="${window.lodash.escape(data.title)}">
                <i class="mdi mdi-bullseye-arrow gen-node-control-focus" style="font-size: 20px;"></i>
            </a>
          </div>
        </div>
      `;
    }
  }

})
// {
//   'icons': {
//   'theme': 'oci',
//     'parentNode': 'oci-menu',
//     'expandToUp': 'oci-chevron-up',
//     'collapseToDown': 'oci-chevron-down',
//     'collapseToLeft': 'oci-chevron-left',
//     'expandToRight': 'oci-chevron-right',
//     'collapsed': 'oci-plus-square',
//     'expanded': 'oci-minus-square',
//     'spinner': 'oci-spinner'
// },
//   'nodeTitle': 'name',
//   'nodeId': 'id',
//   'toggleSiblingsResp': false,
//   'visibleLevel': 999,
//   'chartClass': '',
//   'exportButton': false,
//   'exportButtonName': 'Export',
//   'exportFilename': 'OrgChart',
//   'exportFileextension': 'png',
//   'draggable': false,
//   'direction': 't2b',
//   'pan': false,
//   'zoom': false,
//   'zoominLimit': 7,
//   'zoomoutLimit': 0.5
// };
