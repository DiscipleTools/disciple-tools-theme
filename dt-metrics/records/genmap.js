const DATA_LAYER_SETTINGS_LOCAL_STORAGE_KEY = 'data_layer_settings';

jQuery(document).ready(function ($) {
  if (
    window.wpApiShare.url_path.startsWith(
      `metrics/${window.dtMetricsProject.base_slug}/genmap`,
    )
  ) {
    project_records_genmap();
  }

  let orgchart_container = null;
  function project_records_genmap() {
    'use strict';
    let chart = jQuery('#chart');
    let spinner = ' <span class="loading-spinner active"></span> ';

    chart.empty().html(spinner);
    jQuery('#metrics-sidemenu').foundation(
      'down',
      jQuery(`#${window.dtMetricsProject.base_slug}-menu`),
    );

    let translations = window.dtMetricsProject.translations;

    chart.empty().html(`
          <div class="grid-x grid-padding-x">
              <div class="cell medium-10">
                  <span>
                    <select id="select_post_types" style="width: 200px;"></select>
                  </span>
                  <span>
                    <select id="select_post_type_fields" style="width: 200px;"></select>
                  </span>
                  <span id="show_data_layer_title" class="button select-button empty-select-button" style="margin-top: 5px; margin-left: 10px;">
                    <i class="mdi mdi-filter-plus-outline" style="font-size: 15px;"></i>
                  </span>
                  <span style="display: inline-block; margin-right: 10px; margin-left: 10px;" class="show-closed-switch">
                      ${window.lodash.escape(translations.show_archived)}
                      <div class="switch tiny">
                          <input class="switch-input" id="archivedToggle" type="checkbox" name="archivedToggle">
                          <label class="switch-paddle" for="archivedToggle">
                              <span class="show-for-sr">${window.lodash.escape(translations.show_archived)}</span>
                          </label>
                      </div>
                  </span>
                  <span>
                    <i class="fi-loop" onclick="window.load_genmap()" style="font-size: 1.5em; padding:.5em;cursor:pointer;"></i>
                  </span>
                  <br>
              </div>
              <div class="cell medium-2" >
                <h2 style="float:right;">${window.lodash.escape(translations.title)}</h2>
              </div>
          </div>
          <div class="grid-x grid-padding-x">
            <div class="cell medium-10">
              <div id="data_layer_settings" style="display: none;">
                <label for="data_layer_settings_color">${window.lodash.escape(translations.data_layer_settings_color_label)}<br>
                  <select id="data_layer_settings_color" name="data_layer_settings_color" style="max-width: 70%;"></select>
                </label>
                <br>
                <label for="data_layer_settings_table">${window.lodash.escape(translations.data_layer_title)}<br>
                  <table id="data_layer_settings_table" name="data_layer_settings_table">
                    <tbody style="border: none;"></tbody>
                  </table>
                </label>
                <span id="add_data_layer" class="button">${window.lodash.escape(translations.add_data_layer)}</span>
              </div>
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
          </div>

          <div class="grid-x grid-padding-x" id="infinite_loops_grid_div" style="display: none;">
            <div class="cell medium-12">
              <br>
              <h2 style="float:right;">${window.escape(translations.infinite_loops.title).replace('%20', ' ')}</h2>
              <hr>
              <div id="infinite_loops_div"></div>
            </div>
          </div>

          <div id="modal" class="reveal" data-reveal></div>

          <style>
            .orgchart .hierarchy .custom-adjusted-connect-left:first-child::before {
                left: 60px;
            }
            .orgchart .hierarchy .custom-adjusted-connect-width:last-child::before {
                width: 60px;
            }
          </style>
       `);

    window.load_genmap = (focus_id = null) => {
      jQuery('#infinite_loops_grid_div').fadeOut('fast');
      jQuery('#genmap-details').empty();
      let select_post_type_fields = jQuery('#select_post_type_fields');

      let selected_post_type = jQuery('#select_post_types').val();
      let payload = {
        p2p_type: jQuery(select_post_type_fields)
          .find('option:selected')
          .data('p2p_key'),
        p2p_direction: jQuery(select_post_type_fields)
          .find('option:selected')
          .data('p2p_direction'),
        post_type: selected_post_type,
        gen_depth_limit: 100,
        show_archived: jQuery('#archivedToggle').prop('checked'),
        data_layers: package_data_layer_settings(),
        slug: window.dtMetricsProject.base_slug,
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
      window
        .makeRequest(
          'POST',
          `metrics/${window.dtMetricsProject.base_slug}/genmap`,
          payload,
        )
        .promise()
        .then((response) => {
          let { genmap, data_layers } = response;
          window.dtMetricsProject.data_layers = data_layers;

          let container = jQuery('#genmap');
          container.empty();

          let loops = identify_infinite_loops(genmap, []);
          if (loops.length > 0) {
            display_infinite_loops(loops);
          }

          const has_data_layers = payload?.data_layers?.layers.length > 0;
          var nodeTemplate = function (data) {
            return `
            <div class="title" data-item-id="${window.lodash.escape(data.id)}">${window.lodash.escape(data.name)}</div>
            <div class="content" style="${has_data_layers ? 'height: 90px;' : ''} padding-left: 5px; padding-right: 5px; ">${window.lodash.escape(data.content)}</div>
          `;
          };

          var createNode = function ($node, data) {
            if (has_data_layers) {
              $node.css('width', '120px');
            }
          };

          // Ensure no result responses are reshaped accordingly.
          if (
            genmap &&
            typeof genmap === 'string' &&
            genmap.includes('No Results')
          ) {
            genmap = {};
          }

          orgchart_container = container.orgchart({
            data: genmap,
            nodeContent: 'content',
            direction: 'l2r',
            nodeTemplate: nodeTemplate,
            createNode: createNode,
            initCompleted: function (chart) {
              const post_types = window.dtMetricsProject.post_types;

              /**
               * Non-Shared Items
               */

              // Identify and obfuscate items not shared with current user.
              const non_shared_items = identify_items_by_field_value(
                genmap,
                'shared',
                0,
                {},
              );

              // Obfuscate identified items.
              if (non_shared_items) {
                for (const [id, item] of Object.entries(non_shared_items)) {
                  const node = $(chart).find(`#${id}.node`);
                  if (node) {
                    const color = '#808080';
                    $(node).css('background-color', color);
                    $(node).find('.title').text('.......');
                    $(node).find('.title').css('background-color', color);
                    $(node).find('.content').css('background-color', color);
                    $(node).find('.content').css('border', '0px');

                    $(node).data('shared', '0');
                  }
                }
              }

              /**
               * Archived Items
               */

              // Identify archived items, in order to update corresponding node color.
              if (
                post_types &&
                post_types[selected_post_type] &&
                post_types[selected_post_type]?.status_field?.archived_key
              ) {
                const archived_items = identify_items_by_field_value(
                  genmap,
                  'status',
                  post_types[selected_post_type]['status_field'][
                    'archived_key'
                  ],
                  {},
                );

                // Tweak node colouring of identified items; which have been archived.
                if (archived_items) {
                  for (const [id, item] of Object.entries(archived_items)) {
                    const node = $(chart).find(`#${id}.node`);
                    if (node) {
                      const color = '#808080';
                      $(node).css('background-color', color);
                      $(node).find('.title').css('background-color', color);
                      $(node).find('.content').css('background-color', color);
                      $(node).find('.content').css('border', '0px');
                    }
                  }
                }
              }

              /**
               * Data Layer Settings
               */

              const data_layer_local_storage_settings =
                window.SHAREDFUNCTIONS.get_json_from_local_storage(
                  DATA_LAYER_SETTINGS_LOCAL_STORAGE_KEY,
                  {},
                  jQuery('#select_post_types').val(),
                );

              const post_type_field_settings =
                post_types[selected_post_type]['fields'];
              const data_layer_node_color_field = $(
                '#data_layer_settings_color',
              ).val();
              for (const [post_id, layer_settings] of Object.entries(
                data_layers,
              )) {
                // First, ensure there is a valid node handle corresponding to post_id; also ensuring non-shared items are ignored.
                const node = $(chart).find(`#${post_id}.node`);
                if (node && !non_shared_items[post_id]) {
                  // Next, determine node color to be adopted.
                  let data_layer_node_color = null;
                  if (
                    layer_settings[data_layer_node_color_field] &&
                    layer_settings[data_layer_node_color_field][0]['value'] &&
                    post_type_field_settings[data_layer_node_color_field] &&
                    post_type_field_settings[data_layer_node_color_field][
                      'default'
                    ] &&
                    post_type_field_settings[data_layer_node_color_field][
                      'default'
                    ][
                      layer_settings[data_layer_node_color_field][0]['value']
                    ] &&
                    post_type_field_settings[data_layer_node_color_field][
                      'default'
                    ][layer_settings[data_layer_node_color_field][0]['value']][
                      'color'
                    ]
                  ) {
                    data_layer_node_color =
                      post_type_field_settings[data_layer_node_color_field][
                        'default'
                      ][
                        layer_settings[data_layer_node_color_field][0]['value']
                      ]['color'];
                  }

                  // Next, collate data layer values to be appended within node content.
                  let collated_data_layer_content =
                    collate_data_layer_content(post_id);

                  let data_layer_content_html = '';

                  // Loop, extract & display accordingly, by data layer order.
                  data_layer_local_storage_settings.layers.forEach(
                    (field_id) => {
                      const content = collated_data_layer_content[field_id];
                      if (content) {
                        // Convert collated data layer content to html representation.
                        data_layer_content_html += `<br>`;

                        // Toggle between label/icon node displays, accordingly by local storage setting.
                        const use_icons =
                          data_layer_local_storage_settings[
                            'show_icons_for_fields'
                          ] !== undefined
                            ? data_layer_local_storage_settings.show_icons_for_fields.includes(
                                field_id,
                              )
                            : false;

                        if (!use_icons) {
                          switch (post_type_field_settings[field_id]['type']) {
                            case 'date':
                            case 'key_select': {
                              data_layer_content_html += `${content['content']}`;
                              break;
                            }
                            case 'tags':
                            case 'multi_select': {
                              data_layer_content_html +=
                                content['content'].join(', ');
                              break;
                            }
                          }
                        } else {
                          let icons_total = 0;
                          let icons_total_limit = 4;
                          let icons_total_limit_counter = 0;

                          const collated_data_layer_field_icons =
                            collate_data_layer_field_icons(
                              post_type_field_settings[field_id],
                              content['content'],
                            );

                          // Ensure to default to showing default icons over field icon.
                          let icons = [];
                          if (
                            collated_data_layer_field_icons['default_icons']
                              .length > 0
                          ) {
                            icons =
                              collated_data_layer_field_icons['default_icons'];
                          } else if (
                            collated_data_layer_field_icons['field_icons']
                              .length > 0
                          ) {
                            icons =
                              collated_data_layer_field_icons['field_icons'];
                          }

                          // Proceed with displaying icons, accordingly by line limits.
                          icons_total +=
                            icons.length +
                            collated_data_layer_field_icons['no_icons_counter'];
                          icons.forEach((icon) => {
                            if (icons_total_limit_counter < icons_total_limit) {
                              data_layer_content_html += icon + '&nbsp;';
                            } else if (
                              icons_total_limit_counter === icons_total_limit
                            ) {
                              // Determine if a plus extra count is required.
                              let plus_count =
                                icons_total - icons_total_limit_counter;
                              if (plus_count > 0) {
                                data_layer_content_html += `<span class="dt-metrics-plus-count">+${plus_count}</span>`;
                              }
                            }
                            icons_total_limit_counter++;
                          });
                        }
                      }
                    },
                  );

                  // Update post node's content.
                  const initial_node_content = $(node)
                    .find('.content')
                    .first()
                    .text();
                  $(node)
                    .find('.content')
                    .html(`${initial_node_content}${data_layer_content_html}`);

                  // Finally, adjust node color accordingly, by specified node color.
                  if (data_layer_node_color) {
                    $(node).css('background-color', data_layer_node_color);
                    $(node)
                      .find('.title')
                      .css('background-color', data_layer_node_color);
                    $(node)
                      .find('.content')
                      .css('background-color', data_layer_node_color);
                    $(node).find('.content').css('border', '0px');
                  }
                }
              }
            },
          });

          // Adjust node sizes accordingly, if data layers have been assigned.
          if (has_data_layers) {
            $(container)
              .find('.node')
              .each(function (idx, node) {
                const children = orgchart_container.getChildren($(node));

                // Ensure node has multiple children; before extracting the first & last siblings.
                if (children.length > 1) {
                  const first_child = $(children).first();
                  if (first_child) {
                    $(first_child)
                      .parent()
                      .toggleClass('custom-adjusted-connect-left');
                  }

                  const last_child = $(children).last();
                  if (last_child) {
                    $(last_child)
                      .parent()
                      .toggleClass('custom-adjusted-connect-width');
                  }
                } else if (children.length === 1) {
                  $(children)
                    .parent()
                    .toggleClass('custom-adjusted-connect-left');
                }
              });
          }

          let container_height = window.innerHeight - 200; // because it is rotated
          container.height(container_height);

          container.off('click', '.node');
          container.on('click', '.node', function () {
            let node = jQuery(this);
            let node_id = node.attr('id');
            let node_parent_id = node.data('parent');

            // Ensure non-shared item nodes are ignored.
            const node_shared = node.data('shared');
            if (!node_shared || String(node_shared) !== '0') {
              open_modal_details(node_id, node_parent_id, selected_post_type);
            } else {
              jQuery('#genmap-details').empty();
            }
          });
        })
        .catch((error) => {
          let msg =
            error.responseJSON !== undefined && error.responseJSON['message']
              ? error.responseJSON['message']
              : error.statusText;
          alert(window.lodash.escape(msg));
        });
    };

    // Set initial states and default to any specified url parameters.
    refresh_post_type_select_list(function () {
      const request_params = fetch_url_search_params();

      // Ensure required parts are present, in order to proceed.
      if (request_params && request_params.record_type) {
        jQuery('#select_post_types').val(request_params.record_type);
      }

      // Refresh data layer node color options for selected post type.
      refresh_data_layer_node_color();

      // Load data layer settings.
      load_data_layer_settings(false, function () {
        refresh_post_type_field_select_list(function () {
          if (request_params && request_params.field) {
            jQuery('#select_post_type_fields').val(request_params.field);
            window.load_genmap(
              request_params.focus_id ? request_params.focus_id : null,
            );
          } else {
            window.load_genmap();
          }
        });
      });
    });

    jQuery('#select_post_types').on('change', function (e) {
      refresh_post_type_field_select_list(function () {
        // Refresh data layer node color options for selected post type.
        refresh_data_layer_node_color();

        // Load data layer settings.
        load_data_layer_settings(false, window.load_genmap);
      });
    });

    jQuery('#select_post_type_fields').on('change', function (e) {
      window.load_genmap();
    });

    jQuery(document).on('click', '.genmap-details-add-child', function (e) {
      let control = jQuery(e.currentTarget);
      display_add_child_modal(
        jQuery(control).data('post_type'),
        jQuery(control).data('post_id'),
        jQuery(control).data('post_name'),
      );
    });

    jQuery(document).on('click', '.genmap-details-add-focus', function (e) {
      let control = jQuery(e.currentTarget);
      let post_type = jQuery(control).data('post_type');
      let post_id = jQuery(control).data('post_id');
      let p2p_key = jQuery('#select_post_type_fields')
        .find('option:selected')
        .data('p2p_key');

      handle_focus(post_type, post_id, p2p_key);
    });

    jQuery(document).on(
      'click',
      '.genmap-details-toggle-child-display',
      function (e) {
        toggle_child_display(
          jQuery(e.currentTarget).data('post_id'),
          jQuery(e.currentTarget).data('parent_id'),
        );
      },
    );

    jQuery(document).on('click', '#gen_tree_add_child_but', function (e) {
      handle_add_child();
    });

    jQuery(document).on('click', '#archivedToggle', function (e) {
      window.load_genmap();
    });

    jQuery(document).on('click', '#show_data_layer_title', function (e) {
      const data_layer_settings = jQuery('#data_layer_settings');
      const show_data_layer_title = jQuery('#show_data_layer_title');
      if (data_layer_settings.is(':visible')) {
        data_layer_settings.slideUp('fast', function () {
          show_data_layer_title.removeClass('selected-select-button');
          show_data_layer_title.addClass('empty-select-button');
        });
      } else {
        data_layer_settings.slideDown('fast', function () {
          show_data_layer_title.removeClass('empty-select-button');
          show_data_layer_title.addClass('selected-select-button');
        });
      }
    });

    jQuery(document).on('click', '#add_data_layer', function (e) {
      add_data_layer_settings();
    });

    jQuery(document).on('click', '.del-data-layer', function (e) {
      $(e.target).parent().parent().remove();

      // Save updated data layers settings.
      window.SHAREDFUNCTIONS.save_json_to_local_storage(
        DATA_LAYER_SETTINGS_LOCAL_STORAGE_KEY,
        package_data_layer_settings(),
        jQuery('#select_post_types').val(),
      );

      // Refresh tree shape and subsequent node data.
      window.load_genmap();
    });

    jQuery(document).on('change', '.show-data-layer-icons-input', function (e) {
      // Save updated data layers settings.
      window.SHAREDFUNCTIONS.save_json_to_local_storage(
        DATA_LAYER_SETTINGS_LOCAL_STORAGE_KEY,
        package_data_layer_settings(),
        jQuery('#select_post_types').val(),
      );

      // Refresh tree shape and subsequent node data.
      window.load_genmap();
    });

    jQuery(document).on('change', '.data-layer-field', function (e) {
      // Save updated data layers settings.
      window.SHAREDFUNCTIONS.save_json_to_local_storage(
        DATA_LAYER_SETTINGS_LOCAL_STORAGE_KEY,
        package_data_layer_settings(),
        jQuery('#select_post_types').val(),
      );

      // Refresh tree shape and subsequent node data.
      window.load_genmap();
    });

    jQuery(document).on('change', '#data_layer_settings_color', function (e) {
      // Save updated data layers settings.
      window.SHAREDFUNCTIONS.save_json_to_local_storage(
        DATA_LAYER_SETTINGS_LOCAL_STORAGE_KEY,
        package_data_layer_settings(),
        jQuery('#select_post_types').val(),
      );

      // Refresh tree shape and subsequent node data.
      window.load_genmap();
    });
  }

  function collate_data_layer_content(post_id, convert_raw_values = true) {
    let data_layer_content_raw = {};
    const data_layers = window.dtMetricsProject.data_layers;
    if (data_layers && data_layers[post_id]) {
      const selected_post_type = jQuery('#select_post_types').val();
      const post_types = window.dtMetricsProject.post_types;
      const post_type_field_settings = post_types[selected_post_type]['fields'];

      for (const [field_id, values] of Object.entries(data_layers[post_id])) {
        if (post_type_field_settings[field_id]) {
          // Iterate over values, extracting content accordingly, by field type.
          values.forEach(function (value) {
            switch (post_type_field_settings[field_id]['type']) {
              case 'date': {
                let date_value = convert_raw_values
                  ? new Date(value['value'] * 1000).toLocaleString()
                  : value['value'];
                data_layer_content_raw[field_id] = {
                  label: value['label'],
                  content: date_value,
                };
                break;
              }
              case 'key_select': {
                let key_select_value = value['value'];
                if (
                  convert_raw_values &&
                  post_type_field_settings[field_id]['default'] &&
                  post_type_field_settings[field_id]['default'][
                    value['value']
                  ] &&
                  post_type_field_settings[field_id]['default'][value['value']][
                    'label'
                  ]
                ) {
                  key_select_value =
                    post_type_field_settings[field_id]['default'][
                      value['value']
                    ]['label'];
                }
                data_layer_content_raw[field_id] = {
                  label: value['label'],
                  content: key_select_value,
                };
                break;
              }
              case 'tags':
              case 'multi_select': {
                if (!data_layer_content_raw[field_id]) {
                  data_layer_content_raw[field_id] = {
                    label: value['label'],
                    content: [],
                  };
                }
                let multi_select_value = value['value'];
                if (
                  convert_raw_values &&
                  !['tags'].includes(
                    post_type_field_settings[field_id]['type'],
                  ) &&
                  post_type_field_settings[field_id]['default'] &&
                  post_type_field_settings[field_id]['default'][
                    value['value']
                  ] &&
                  post_type_field_settings[field_id]['default'][value['value']][
                    'label'
                  ]
                ) {
                  multi_select_value =
                    post_type_field_settings[field_id]['default'][
                      value['value']
                    ]['label'];
                }
                data_layer_content_raw[field_id]['content'].push(
                  multi_select_value,
                );
                break;
              }
            }
          });
        }
      }
    }

    return data_layer_content_raw;
  }

  function collate_data_layer_field_icons(field_settings, content) {
    let field_icons = [];
    let default_icons = [];
    let no_icons_counter = 0;

    // Determine if there are any available field level icons.
    let field_icon = null;
    if (field_settings['icon']) {
      field_icon = field_settings['icon'];
    } else if (field_settings['font-icon']) {
      field_icon = field_settings['font-icon'];
    }

    // Generate corresponding icon html accordingly, based on identified field icon.
    if (field_icon) {
      field_icon = field_icon.trim().toLowerCase();
      if (field_icon.startsWith('mdi')) {
        field_icons.push(
          `<i class="mdi ${field_icon} dt-metrics-node-icon-small"></i>`,
        );
      } else {
        field_icons.push(
          `<img class="dt-white-icon dt-metrics-node-icon-small" src="${field_icon}" alt="${field_settings['name']}"/>`,
        );
      }
    } else {
      no_icons_counter++;
    }

    // Extract default icons for specific field types.
    switch (field_settings['type']) {
      case 'key_select':
      case 'multi_select': {
        if (field_settings['default'] && content.length > 0) {
          for (const [option_id, option] of Object.entries(
            field_settings['default'],
          )) {
            if (content.includes(option['label'])) {
              if (
                option?.icon &&
                option.icon.trim().toLowerCase().startsWith('mdi')
              ) {
                default_icons.push(
                  `<i class="mdi ${option.icon} dt-metrics-node-icon-small"></i>`,
                );
              } else if (
                option?.icon &&
                option.icon.trim().toLowerCase().startsWith('http')
              ) {
                default_icons.push(
                  `<img class="dt-white-icon dt-metrics-node-icon-small" src="${option.icon}" alt="${option['label']}"/>`,
                );
              } else if (
                option['font-icon'] &&
                option['font-icon'].trim().toLowerCase().startsWith('mdi')
              ) {
                default_icons.push(
                  `<i class="mdi ${option['font-icon']} dt-metrics-node-icon-small"></i>`,
                );
              } else if (
                option['font-icon'] &&
                option['font-icon'].trim().toLowerCase().startsWith('http')
              ) {
                default_icons.push(
                  `<img class="dt-white-icon dt-metrics-node-icon-small" src="${option['font-icon']}" alt="${option['label']}"/>`,
                );
              } else {
                no_icons_counter++;
              }
            }
          }
        }
        break;
      }
    }

    return {
      field_icons: field_icons,
      default_icons: default_icons,
      no_icons_counter: no_icons_counter,
    };
  }

  function load_data_layer_settings(show_data_layer_settings, callback = null) {
    const data_layer_settings_div = $('#data_layer_settings');
    const data_layer_settings_color = $('#data_layer_settings_color');
    const show_data_layer_title = $('#show_data_layer_title');
    const data_layer_settings_table = $('#data_layer_settings_table');

    // First, reset data layer elements.
    data_layer_settings_div.slideUp('fast', function () {
      show_data_layer_title.removeClass('selected-select-button');
      show_data_layer_title.addClass('empty-select-button');
      data_layer_settings_table.find('tbody').empty();

      // Next, proceed with loading and displaying any previously stored data layers.
      const data_layer_settings =
        window.SHAREDFUNCTIONS.get_json_from_local_storage(
          DATA_LAYER_SETTINGS_LOCAL_STORAGE_KEY,
          {},
          jQuery('#select_post_types').val(),
        );
      if (data_layer_settings?.color && data_layer_settings?.layers) {
        // Set default color and data layers.
        data_layer_settings_color.val(data_layer_settings.color);
        data_layer_settings.layers.forEach(function (field_id, idx) {
          add_data_layer_settings(field_id);
        });

        // Select any identified icon toggles.
        if (data_layer_settings['show_icons_for_fields'] !== undefined) {
          data_layer_settings.show_icons_for_fields.forEach(
            function (field_id, idx) {
              const icons_input = $(
                `.show-data-layer-icons-input[data-field_id='${field_id}']`,
              );
              if (icons_input) {
                $(icons_input).prop('checked', true);
              }
            },
          );
        }

        // Once re-populated, display loaded data layers.
        if (show_data_layer_settings) {
          data_layer_settings_div.slideDown('fast', function () {
            show_data_layer_title.removeClass('empty-select-button');
            show_data_layer_title.addClass('selected-select-button');

            if (callback) {
              callback();
            }
          });
        } else if (callback) {
          callback();
        }
      } else if (callback) {
        callback();
      }
    });
  }

  function package_data_layer_settings() {
    let packaged_data_layer_settings = {
      color: $('#data_layer_settings_color').val(),
      layers: [],
      show_icons_for_fields: [],
    };

    // Iterate and capture specified data field layers.
    $('#data_layer_settings_table')
      .find('tbody tr')
      .each(function (idx, tr) {
        // Capture data layer fields.
        const data_layer_field = $(tr).find('.data-layer-field').val();
        if (data_layer_field) {
          packaged_data_layer_settings['layers'].push(data_layer_field);
        }

        // Capture data layers to be shown as icons.
        const show_data_layer_icons = $(tr).find(
          '.show-data-layer-icons-input',
        );
        const show_icons = $(show_data_layer_icons).prop('checked');
        if (data_layer_field && show_icons) {
          packaged_data_layer_settings['show_icons_for_fields'].push(
            data_layer_field,
          );
        }
      });

    return packaged_data_layer_settings;
  }

  function refresh_data_layer_node_color() {
    const post_type = jQuery('#select_post_types').val();
    const post_type_settings = window.dtMetricsProject.post_types[post_type];

    if (post_type && post_type_settings) {
      const data_layer_settings_color = $('#data_layer_settings_color');
      data_layer_settings_color.empty();
      data_layer_settings_color.append(
        $('<option />')
          .val('default-node-color')
          .text(
            `${window.lodash.escape(window.dtMetricsProject.translations.data_layer_settings_color_default_label)}`,
          ),
      );

      // Determine status field for given post type.
      if (
        post_type_settings?.status_field?.status_key &&
        post_type_settings['fields'] &&
        post_type_settings['fields'][
          post_type_settings['status_field']['status_key']
        ]
      ) {
        data_layer_settings_color.append(
          $('<option />')
            .val(post_type_settings['status_field']['status_key'])
            .text(
              post_type_settings['fields'][
                post_type_settings['status_field']['status_key']
              ]['name'],
            ),
        );
      }
    }
  }

  function add_data_layer_settings(selected_field_id = null) {
    const post_type = jQuery('#select_post_types').val();
    const post_type_settings = window.dtMetricsProject.post_types[post_type];

    if (post_type && post_type_settings) {
      const supported_field_types =
        window.dtMetricsProject.data_layer_supported_field_types;
      let selected_fields = [];

      // Filter out fields, suitable for data layer settings.
      for (const [field_id, field_setting] of Object.entries(
        post_type_settings.fields,
      )) {
        if (
          supported_field_types.includes(field_setting['type']) &&
          (!Object.prototype.hasOwnProperty.call(field_setting, 'hidden') ||
            field_setting['hidden'] === false) &&
          (!Object.prototype.hasOwnProperty.call(field_setting, 'private') ||
            field_setting['private'] === false)
        ) {
          selected_fields.push({
            id: field_id,
            label: field_setting['name'].trim(),
          });
        }
      }

      // Ensure suitable fields have been identified.
      if (selected_fields.length > 0) {
        selected_fields = window.lodash.sortBy(selected_fields, [
          function (o) {
            return o.label;
          },
        ]);

        const translations = window.dtMetricsProject.translations;
        const data_layer_settings_table = $('#data_layer_settings_table');
        const data_row_count = $(data_layer_settings_table).find('tr').length;
        $(data_layer_settings_table).find('tbody').append(`
          <tr style="border: none;">
            <td style="padding-left: 0;">
              <select class="data-layer-field" style="margin-top: 15px;">
                <option selected disabled value="">--- ${window.lodash.escape(window.dtMetricsProject.translations.select_data_layer_field)} ---</option>
                ${(function (options, option_id) {
                  let options_html = ``;
                  options.forEach(function (field) {
                    let selected =
                      option_id && option_id === field['id'] ? 'selected' : '';
                    options_html += `<option value="${field['id']}" ${selected}>${field['label']}</option>`;
                  });

                  return options_html;
                })(selected_fields, selected_field_id)}
              </select>
            </td>
            <td style="vertical-align: top; text-align: center;">
                <span style="display: inline-block; margin-right: 10px; margin-left: 10px;" class="show-closed-switch">
                    ${window.lodash.escape(translations.icon_data_layer)}
                    <br>
                    <div class="switch tiny">
                        <input class="switch-input show-data-layer-icons-input" id="show_data_layer_icons_${data_row_count}" type="checkbox" name="show_data_layer_icons_${data_row_count}" data-field_id="${selected_field_id}">
                        <label class="switch-paddle show-data-layer-icons-label" for="show_data_layer_icons_${data_row_count}" data-field_id="${selected_field_id}">
                            <span class="show-for-sr">${window.lodash.escape(translations.icon_data_layer)}</span>
                        </label>
                    </div>
                </span>
            </td>
            <td><button class="button clear-date-button del-data-layer" style="border: 1px solid #cacaca;">x</button></td>
          </tr>
        `);
      }
    }
  }

  function identify_items_by_field_value(data, field, value, items) {
    if (String(data?.[field]) === String(value)) {
      items[data['id']] = {
        id: data['id'],
        name: data['name'],
        status: data['status'],
        shared: data['shared'],
      };
    }

    if (data?.['children']) {
      data['children'].forEach(function (item) {
        items = identify_items_by_field_value(item, field, value, items);
      });
    }

    return items;
  }

  function identify_infinite_loops(data, loops) {
    if (data?.['children']) {
      data['children'].forEach(function (item) {
        if (item['has_infinite_loop']) {
          loops.push({
            id: item['id'],
            name: item['name'],
            loop: extract_infinite_loop(
              item['id'],
              item['children'],
              [],
              false,
            ),
          });
        } else if (item?.['children'].length > 0) {
          loops = identify_infinite_loops(item, loops);
        }
      });
    }

    return loops;
  }

  function extract_infinite_loop(parent_id, children, loop, loop_closed) {
    children.forEach(function (item) {
      if (parent_id === item['id']) {
        loop_closed = true;
      } else if (!loop_closed) {
        loop.push(item);
        loop = extract_infinite_loop(
          parent_id,
          item['children'],
          loop,
          loop_closed,
        );
      }
    });

    return loop;
  }

  function display_infinite_loops(loops) {
    let loops_grid_div = jQuery('#infinite_loops_grid_div');
    let loops_div = jQuery('#infinite_loops_div');
    let selected_post_type = jQuery('#select_post_types').val();

    jQuery(loops_grid_div).fadeOut('fast', function () {
      jQuery(loops_div).empty();

      // Ensure duplicate loops are removed.
      let processed_loop_ids = [];
      let filtered_loops = loops.filter((loop) => {
        if (!processed_loop_ids.includes(loop['id'])) {
          processed_loop_ids.push(loop['id']);
          return true;
        } else {
          return false;
        }
      });

      // Proceed with filtered loops display
      filtered_loops.forEach(function (item) {
        let html = `
        <table>
            <thead>
                <tr>
                    <td>
                        <a style="margin-right: 10px;" href="${window.dtMetricsProject.site_url}/${window.escape(selected_post_type).replaceAll('%20', ' ')}/${window.escape(item['id']).replaceAll('%20', ' ')}" target="_blank" class="button">
                            <i class="mdi mdi-id-card" style="font-size: 15px;"></i>
                        </a>
                        ${window.escape(item['name']).replaceAll('%20', ' ')}
                    </td>
                </tr>
            </thead>
            <tbody>
              ${(function func(loop, post_type) {
                let tbody_html = ``;
                loop.forEach(function (child) {
                  tbody_html += `
                  <tr>
                    <td>
                        <a href="${window.dtMetricsProject.site_url}/${window.escape(post_type).replaceAll('%20', ' ')}/${window.escape(child['id']).replaceAll('%20', ' ')}" target="_blank">
                          ${window.escape(child['name']).replaceAll('%20', ' ')}
                        </a>
                    </td>
                  </tr>
                  `;
                });
                return tbody_html;
              })(item['loop'], selected_post_type)}
            </tbody>
        </table>`;

        // Append to infinite loops display area.
        jQuery(loops_div).append(html);
      });

      jQuery(loops_grid_div).fadeIn('fast');
    });
  }

  function fetch_url_search_params() {
    const url_search_params = new URLSearchParams(window.location.search);

    let request_params = {};
    for (const param of url_search_params) {
      if (Array.isArray(param) && param.length === 2) {
        request_params[param[0]] = param[1];
      }
    }

    return request_params;
  }

  function display_add_child_modal(post_type, post_id, post_name) {
    let list_html = `
    <input id="gen_tree_add_child_post_type" type="hidden" value="${window.lodash.escape(post_type)}" />
    <input id="gen_tree_add_child_post_id" type="hidden" value="${window.lodash.escape(post_id)}" />
    <label>
      ${window.lodash.escape(window.dtMetricsProject.translations.modal.add_child_name_title)}
      <input id="gen_tree_add_child_name" type="text" />
    </label>`;

    let buttons_html = `<button id="gen_tree_add_child_but" class="button" type="button">${window.lodash.escape(window.dtMetricsProject.translations.modal.add_child_but)}</button>`;

    let modal = jQuery('#template_metrics_modal');
    let modal_buttons = jQuery('#template_metrics_modal_buttons');
    let title =
      window.dtMetricsProject.translations.modal.add_child_title +
      ` [ ${window.lodash.escape(post_name)} ]`;
    let content = jQuery('#template_metrics_modal_content');

    jQuery(modal_buttons).empty().html(buttons_html);

    jQuery('#template_metrics_modal_title')
      .empty()
      .html(window.lodash.escape(title));
    jQuery(content).css('max-height', '300px');
    jQuery(content).css('overflow', 'auto');
    jQuery(content).empty().html(list_html);
    jQuery(modal).foundation('open');
  }

  $(document).on(
    'open.zf.reveal',
    '#template_metrics_modal[data-reveal]',
    function () {
      jQuery('#gen_tree_add_child_name').focus();
    },
  );

  function handle_add_child() {
    let post_type = jQuery('#gen_tree_add_child_post_type').val();
    let parent_id = jQuery('#gen_tree_add_child_post_id').val();
    let child_title = jQuery('#gen_tree_add_child_name').val();
    let field_id = jQuery('#select_post_type_fields').val();

    if (post_type && parent_id && child_title && field_id) {
      window.API.create_post(post_type, {
        title: child_title,
        additional_meta: {
          created_from: parent_id,
          add_connection: field_id,
        },
      })
        .then((new_post) => {
          // Close modal and refresh generation tree, accordingly, based on focussed state.
          jQuery('#template_metrics_modal').foundation('close');

          // Ensure to respect any existing focussed selections.
          const request_params = fetch_url_search_params();
          window.load_genmap(
            request_params && request_params.focus_id
              ? request_params.focus_id
              : null,
          );
        })
        .catch(function (error) {
          console.error(error);
        });
    }
  }

  function handle_focus(post_type, post_id, p2p_key) {
    if (post_id) {
      window.load_genmap(post_id);
    }
  }

  function toggle_child_display(post_id, parent_id) {
    if (post_id && orgchart_container) {
      let query =
        parent_id === 0
          ? `#${post_id}.node`
          : `#${post_id}.node[data-parent='${parent_id}']`;
      let node = jQuery('#genmap').find(query);
      if (node) {
        let children = orgchart_container.getNodeState(node, 'children');
        if (children.exist === true) {
          if (children.visible === true) {
            orgchart_container.hideChildren(node);
          } else {
            toggle_child_display_show_children(node);
          }
        }
      }
    }
  }

  function toggle_child_display_show_children(node) {
    if (node && orgchart_container) {
      orgchart_container.showChildren(node);

      // Recursively display nested children.
      let children = orgchart_container.getChildren(node);
      if (children !== undefined && children.length > 0) {
        children.each(function (idx, child) {
          toggle_child_display_show_children(jQuery(child));
        });
      }
    }
  }

  function refresh_post_type_select_list(callback = null) {
    let post_types = window.dtMetricsProject.post_types;
    if (post_types) {
      let post_type_select = jQuery('#select_post_types');
      jQuery(post_type_select).empty();

      // Only focus on post types with valid connection types.
      let filtered_post_types = [];
      jQuery.each(post_types, function (post_type, post_type_obj) {
        if (
          post_type_obj &&
          post_type_obj.connection_types &&
          Array.isArray(post_type_obj.connection_types) &&
          filter_post_type_connection_fields(post_type).length > 0
        ) {
          filtered_post_types.push({
            value: post_type,
            text: post_type_obj.label_plural,
          });
        }
      });

      let sorted_post_types = window.lodash.sortBy(filtered_post_types, [
        function (o) {
          return o.text;
        },
      ]);
      jQuery.each(sorted_post_types, function (idx, option) {
        jQuery(post_type_select).append(
          jQuery('<option>', {
            value: option.value,
            text: option.text,
          }),
        );
      });
    }

    if (callback) {
      callback();
    }
  }

  function refresh_post_type_field_select_list(callback = null) {
    jQuery('#infinite_loops_grid_div').fadeOut('fast');
    let post_types = window.dtMetricsProject.post_types;
    let selected_post_type = jQuery('#select_post_types').val();
    if (post_types && selected_post_type && post_types[selected_post_type]) {
      let post_type_fields_select = jQuery('#select_post_type_fields');
      jQuery(post_type_fields_select).empty();

      // Capture related connection type fields.
      let filtered_post_type_fields =
        filter_post_type_connection_fields(selected_post_type);

      let uniq_post_type_fields = window.lodash.uniqWith(
        filtered_post_type_fields,
        function (a, b) {
          return a.p2p_key === b.p2p_key && a.p2p_direction === b.p2p_direction;
        },
      );

      let sorted_post_type_fields = window.lodash.sortBy(
        uniq_post_type_fields,
        [
          function (o) {
            return o.text;
          },
        ],
      );

      jQuery.each(sorted_post_type_fields, function (idx, option) {
        jQuery('<option>')
          .val(option.value)
          .text(option.text)
          .attr('data-p2p_key', option.p2p_key)
          .attr('data-p2p_direction', option.p2p_direction)
          .appendTo(post_type_fields_select);
      });
    }

    if (callback) {
      callback();
    }
  }

  function filter_post_type_connection_fields(post_type) {
    const post_types = window.dtMetricsProject.post_types;
    let filtered_post_type_fields = [];
    if (
      post_types &&
      post_type &&
      post_types[post_type] &&
      post_types[post_type].connection_types
    ) {
      // Only capture related connection type fields.
      post_types[post_type].connection_types.forEach((field_id, idx) => {
        if (
          post_types[post_type]['fields'][field_id] &&
          post_types[post_type]['fields'][field_id]['post_type'] &&
          post_types[post_type]['fields'][field_id]['post_type'] === post_type
        ) {
          // Hard filter some specific fields.
          let to_be_filtered = true;
          if (
            post_type === 'contacts' &&
            ['baptized_by', 'coached_by', 'subassigned'].includes(field_id)
          ) {
            to_be_filtered = false;
          }
          if (post_type === 'groups' && ['parent_groups'].includes(field_id)) {
            to_be_filtered = false;
          }

          if (to_be_filtered) {
            filtered_post_type_fields.push({
              value: field_id,
              text: post_types[post_type]['fields'][field_id]['name'],
              p2p_key: post_types[post_type]['fields'][field_id]['p2p_key'],
              p2p_direction:
                post_types[post_type]['fields'][field_id]['p2p_direction'],
            });
          }
        }
      });
    }

    return filtered_post_type_fields;
  }

  function open_modal_details(id, parent_id, post_type) {
    if (id) {
      let spinner = ' <span class="loading-spinner active"></span> ';
      jQuery('#genmap-details').html(spinner);

      window
        .makeRequest('GET', post_type + '/' + id, null, 'dt-posts/v2/')
        .promise()
        .then((data) => {
          let container = jQuery('#genmap-details');
          container.empty();
          if (data) {
            container.html(window.detail_template(parent_id, post_type, data));
          }
        })
        .catch((error) => {
          jQuery('#genmap-details').html('');
        });
    }
  }

  window.detail_template = (parent_id, post_type, data) => {
    let escaped_translations = window.SHAREDFUNCTIONS.escapeObject(
      window.dtMetricsProject.translations,
    );

    // Determine orgchart node state.
    let orgchart_node_state = {};
    if (data.ID && orgchart_container) {
      let orgchart_node = jQuery('#genmap').find(`#${data.ID}.node`);
      let orgchart_node_children = orgchart_container.getNodeState(
        orgchart_node,
        'children',
      );

      // Capture associated children state.
      orgchart_node_state['children_exist'] = orgchart_node_children.exist
        ? orgchart_node_children.exist
        : false;
      orgchart_node_state['children_visible'] = orgchart_node_children.visible
        ? orgchart_node_children.visible
        : false;
    }
    let toggle_child_displayed_but_state =
      orgchart_node_state['children_exist'] !== undefined &&
      orgchart_node_state['children_exist'] === false
        ? 'disabled'
        : '';

    // Capture any identified data layers.
    let data_layer_template = '';
    const collated_data_layer_content = collate_data_layer_content(data.ID);
    if (Object.keys(collated_data_layer_content).length > 0) {
      const post_type_field_settings =
        window.dtMetricsProject.post_types[jQuery('#select_post_types').val()][
          'fields'
        ];

      data_layer_template += `
        <div class="cell">`;

      for (const [field_id, content] of Object.entries(
        collated_data_layer_content,
      )) {
        data_layer_template += `${content['label']}:`;

        switch (post_type_field_settings[field_id]['type']) {
          case 'date':
          case 'key_select': {
            data_layer_template += `
            <ul>
              <li>${content['content']}</li>
            </ul>`;
            break;
          }
          case 'tags':
          case 'multi_select': {
            data_layer_template += `
            <ul>
              ${content['content'].map((x) => `<li>${x}</li>`).join('')}
            </ul>`;
            break;
          }
        }
      }

      data_layer_template += `</div>`;
    }

    let template = '';

    if (post_type === 'contacts') {
      let assign_to = '';
      if (typeof data.assigned_to !== 'undefined') {
        assign_to = data.assigned_to.display;
      }
      let coach_list = '';
      if (typeof data.coached_by !== 'undefined') {
        coach_list = '<ul>';
        jQuery.each(data.coached_by, function (index, value) {
          coach_list +=
            '<li>' + window.lodash.escape(value['post_title']) + '</li>';
        });
        coach_list += '</ul>';
      }
      let group_list = '';
      if (typeof data.groups !== 'undefined') {
        group_list = '<ul>';
        jQuery.each(data.groups, function (index, value) {
          group_list +=
            '<li>' + window.lodash.escape(value['post_title']) + '</li>';
        });
        group_list += '</ul>';
      }
      let status = '';
      if (typeof data.overall_status !== 'undefined') {
        status = data.overall_status['label'];
      }
      template = `
        <div class="grid-x grid-padding-x">
          <div class="cell">
            <h2>${window.lodash.escape(data.title)}</h2><hr>
          </div>
          ${data_layer_template}
          <div class="cell">
            ${escaped_translations.details.status}: ${window.lodash.escape(status)}
          </div>
          <div class="cell">
            ${escaped_translations.details.groups}:
            ${group_list}
          </div>
          <div class="cell">
            ${escaped_translations.details.assigned_to}:
            ${window.lodash.escape(assign_to)}
          </div>
          <div class="cell">
            ${escaped_translations.details.coaches}: <br>
            ${coach_list}
          </div>
        </div>
      `;
    } else if (post_type === 'groups') {
      let members_count = 0;
      if (typeof data.member_count !== 'undefined') {
        members_count = data.member_count;
      }
      let assign_to = '';
      if (typeof data.assigned_to !== 'undefined') {
        assign_to = data.assigned_to.display;
      }

      let member_list = '';
      if (typeof data.members !== 'undefined') {
        member_list = '<ul>';
        jQuery.each(data.members, function (index, value) {
          member_list +=
            '<li>' + window.lodash.escape(value['post_title']) + '</li>';
        });
        member_list += '</ul>';
      }
      let coach_list = '';
      if (typeof data.coached_by !== 'undefined') {
        coach_list = '<ul>';
        jQuery.each(data.coached_by, function (index, value) {
          coach_list +=
            '<li>' + window.lodash.escape(value['post_title']) + '</li>';
        });
        coach_list += '</ul>';
      }
      let status = '';
      if (typeof data.group_status !== 'undefined') {
        status = data.group_status['label'];
      }
      let type = '';
      if (typeof data.group_type !== 'undefined') {
        type = data.group_type['label'];
      }
      template = `
        <div class="grid-x grid-padding-x">
          <div class="cell">
            <h2>${window.lodash.escape(data.title)}</h2><hr>
          </div>
          ${data_layer_template}
          <div class="cell">
            ${escaped_translations.details.status}: ${window.lodash.escape(status)}
          </div>
          <div class="cell">
            ${escaped_translations.details.type}: ${window.lodash.escape(type)}
          </div>
          <div class="cell">
            ${escaped_translations.details.member_count}: ${window.lodash.escape(members_count)}
          </div>
          <div class="cell">
            ${escaped_translations.details.members}: <br>
            ${member_list}
          </div>
          <div class="cell">
            ${escaped_translations.details.assigned_to}:
            ${window.lodash.escape(assign_to)}
          </div>
          <div class="cell">
            ${escaped_translations.details.coaches}: <br>
            ${coach_list}
          </div>
        </div>
      `;
    } else {
      template = `
        <div class="grid-x grid-padding-x">
          <div class="cell">
            <h2>${window.lodash.escape(data.title)}</h2>
          </div>
          ${data_layer_template}
        </div>
      `;
    }
    template += `
      <div class="cell">
        <hr>
        <div>
            <a href="${window.dtMetricsProject.site_url}/${window.lodash.escape(post_type)}/${window.lodash.escape(data.ID)}" target="_blank" class="button">
              <i class="mdi mdi-id-card" style="font-size: 20px;"></i>
              <span style="display: flex">${escaped_translations.details.open}</span>
          </a>
          <a href="#" class="button genmap-details-add-child" data-post_type="${window.lodash.escape(data.post_type)}" data-post_id="${window.lodash.escape(data.ID)}" data-post_name="${window.lodash.escape(data.title)}">
              <i class="mdi mdi-account-multiple-plus-outline" style="font-size: 20px;"></i>
              <span style="display: flex">${escaped_translations.details.add}</span>

          </a>
          <a href="#" class="button genmap-details-add-focus" data-post_type="${window.lodash.escape(data.post_type)}" data-post_id="${window.lodash.escape(data.ID)}" data-post_name="${window.lodash.escape(data.title)}">
              <i class="mdi mdi-bullseye-arrow gen-node-control-focus" style="font-size: 20px;"></i>
              <span style="display: flex">${escaped_translations.details.focus}</span>
          </a>
          <a href="#" class="button genmap-details-toggle-child-display" data-post_type="${window.lodash.escape(data.post_type)}" data-post_id="${window.lodash.escape(data.ID)}" data-post_name="${window.lodash.escape(data.title)}" data-parent_id="${window.lodash.escape(parent_id !== undefined ? parent_id : 0)}" ${toggle_child_displayed_but_state}>
              <i class="mdi mdi-file-tree" style="font-size: 20px;"></i>
              <span style="display: flex">${escaped_translations.details.hide}</span>
          </a>
        </div>
      </div>
      `;
    return template;
  };
});
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
