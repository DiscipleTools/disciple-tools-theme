'use strict';
/**
 * Export Operations for Modular List
 * Handles CSV export, email BCC list, phone list, and map exports
 */
(function ($, list_settings, Foundation) {
  // Wait for DT_List to be available
  if (!window.DT_List) {
    console.error(
      'DT_List namespace not found. modular-list.js must be loaded first.',
    );
    return;
  }

  const DT_List = window.DT_List;
  const esc = window.SHAREDFUNCTIONS.escapeHTML;

  // Helper to get current_filter from main module
  function getCurrentFilter() {
    return DT_List.current_filter;
  }

  // Helper to get fields_to_show_in_table from main module
  function getFieldsToShowInTable() {
    return DT_List.fields_to_show_in_table;
  }

  /**
   * List Exports
   */

  function export_list_display(type, title, display_function) {
    let modal = null;

    // Adjust export reveal model accordingly, based on incoming type.
    switch (type) {
      case 'csv':
      case 'email':
      case 'phone': {
        modal = $('#modal-large');
        $('#modal-large-title').html(
          title +
            '<span class="loading-spinner" style="margin-left: 10px;"></span>',
        );
        break;
      }
      case 'map': {
        modal = $('#modal-full');
        break;
      }
    }

    if (modal) {
      display_function();
      $(modal).foundation('open');
    }
  }

  $('#export_csv_list').on('click', function (e) {
    export_list_display('csv', $(e.currentTarget).text(), function () {
      const fields_to_show_in_table = getFieldsToShowInTable();

      // Show spinners.
      const spinner = $('#modal-large-title').find('.loading-spinner');
      $(spinner).addClass('active');

      // Identify fields to be exported; ignoring hidden and private fields.
      let exporting_fields_all = [];
      let exporting_fields_visible = [];

      const magic_link_app_keys = Object.keys(
        window.list_settings.post_type_settings.magic_link_apps,
      );
      Object.keys(window.list_settings.post_type_settings.fields).forEach(
        (field_id) => {
          const field_setting =
            window.list_settings.post_type_settings.fields[field_id];
          if (
            magic_link_app_keys.includes(field_id) ||
            ((field_setting['private'] === undefined ||
              !field_setting['private']) &&
              (field_setting['hidden'] === undefined ||
                !field_setting['hidden']) &&
              !['task', 'array'].includes(field_setting['type']))
          ) {
            let setting = field_setting;
            setting['field_id'] = field_id;
            exporting_fields_all.push(setting);

            // Separately capture currently shown table fields.
            if (fields_to_show_in_table.includes(field_id)) {
              exporting_fields_visible.push(setting);
            }

            // Insert additional locations id column, if needed.
            if (['location', 'location_meta'].includes(setting['type'])) {
              let location_settings = JSON.parse(JSON.stringify(setting));
              location_settings['name'] = `${location_settings['name']} [ID]`;
              location_settings['dynamic_csv_col'] = true;
              exporting_fields_all.push(location_settings);

              // Separately capture currently shown table fields.
              if (fields_to_show_in_table.includes(field_id)) {
                exporting_fields_visible.push(location_settings);
              }
            }
          }
        },
      );

      // Sort identified fields by name into ascending order.
      exporting_fields_all.sort(function (a, b) {
        if (a.name.trim() < b.name.trim()) {
          return -1;
        }
        if (a.name.trim() > b.name.trim()) {
          return 1;
        }
        return 0;
      });

      exporting_fields_visible.sort(function (a, b) {
        if (a.show_in_table === undefined && b.show_in_table !== undefined) {
          return 1;
        }
        if (a.show_in_table !== undefined && b.show_in_table === undefined) {
          return -1;
        }
        if (parseInt(a.show_in_table) < parseInt(b.show_in_table)) {
          return -1;
        }
        if (parseInt(a.show_in_table) > parseInt(b.show_in_table)) {
          return 1;
        }
        return 0;
      });

      // Create shown table fields supporting html.
      let fields_to_show_in_table_html = ``;
      exporting_fields_visible.forEach((field) => {
        fields_to_show_in_table_html += `<span class="current-filter-list" style="padding: 4px;">${window.SHAREDFUNCTIONS.escapeHTML(field['name'])}</span>`;
      });

      // Take a two-step approach; first, display fields to be exported and on-demand, obtain records upon export request.
      let html = `<div class="grid-x">`;

      html += `
        <div class="cell">
          <label>
            <input type="radio" name="csv_exported_list_fields" value="all" checked />
            ${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['csv']['fields_msg_all'].replaceAll('%2$s', exporting_fields_all.length))}
          </label>
          <label>
            <input type="radio" name="csv_exported_list_fields" value="visible" />
            ${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['csv']['fields_msg_visible'].replaceAll('%2$s', exporting_fields_visible.length))}
          </label>
          ${fields_to_show_in_table_html}
        </div>`;

      let record_total = window.records_list['total'];
      html += `<div class="cell"><hr></div>
        <div class="cell">
            <button class="button" id="export_csv_list_download" ${record_total <= 0 ? 'disabled' : ''}>
            <i class="mdi mdi-cloud-download-outline" style="font-size: 22px; margin-right: 10px;"></i>
            <span style="font-size: 20px;">[ ${window.SHAREDFUNCTIONS.escapeHTML(record_total)} ]</span>
            </button>
        </div>
      </div>`;

      $('#modal-large-content').html(html);

      // Terminate any spinners and prepare download button event listener.
      $(spinner).removeClass('active');
      $('#export_csv_list_download').on('click', function () {
        $(spinner).addClass('active');
        $('#export_csv_list_download').prop('disabled', true);

        // Ensure to determine which field groupings to move forward with....? All or Currently Visible?
        export_csv_list_download(
          $('input[name="csv_exported_list_fields"]:checked').val() ===
            'visible'
            ? exporting_fields_visible
            : exporting_fields_all,
          function () {
            $(spinner).removeClass('active');
            $('#modal-large').foundation('close');
          },
        );
      });
    });
  });

  function export_csv_list_download(exporting_fields, callback = null) {
    const exporting_field_ids = exporting_fields.map(
      (field) => field['field_id'],
    );
    if (!exporting_field_ids.includes('name')) {
      exporting_fields.push(
        window.list_settings.post_type_settings.fields.name,
      );
    }
    const magic_link_app_keys = Object.keys(
      window.list_settings.post_type_settings.magic_link_apps,
    );

    // First retrieve all records associated with currently selected filter.
    recursively_fetch_posts(
      0,
      500,
      window.records_list['total'],
      [],
      function (posts) {
        if (posts && posts.length > 0) {
          let csv_export = [];

          // Structure csv shape to be downloaded.
          $.each(posts, function (post_idx, post) {
            let csv_row = [];
            let token_array_delimiter = ';';

            // Capture ID & Name.
            csv_row.push(post['ID']);
            csv_row.push(post['name']);

            // Proceed with extraction of remaining fields.
            $.each(exporting_fields, function (field_idx, field) {
              let field_id = field['field_id'];

              // As well as names, also ignore dynamic csv columns; which are typically populated via other means.
              if (
                !['name'].includes(field_id) &&
                (field['dynamic_csv_col'] === undefined ||
                  !field['dynamic_csv_col'])
              ) {
                // Next, extract post field value accordingly, based on field type.
                switch (field['type']) {
                  case 'text':
                  case 'number':
                  case 'boolean':
                  case 'textarea': {
                    let token = post[field_id] ? post[field_id] : '';
                    csv_row.push(token);
                    break;
                  }
                  case 'user_select': {
                    let token =
                      post[field_id] && post[field_id]['display']
                        ? post[field_id]['display']
                        : '';
                    csv_row.push(token);
                    break;
                  }
                  case 'key_select': {
                    let token =
                      post[field_id] && post[field_id]['label']
                        ? post[field_id]['label']
                        : '';
                    csv_row.push(token);
                    break;
                  }
                  case 'date':
                  case 'datetime': {
                    let token =
                      post[field_id] && post[field_id]['formatted']
                        ? post[field_id]['formatted']
                        : '';
                    csv_row.push(token);
                    break;
                  }
                  case 'multi_select': {
                    let token_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        token_array.push(field['default'][cell_value]['label']);
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    break;
                  }
                  case 'connection': {
                    let token_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        token_array.push(cell_value['post_title']);
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    break;
                  }
                  case 'communication_channel': {
                    let token_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        token_array.push(cell_value['value']);
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    break;
                  }
                  case 'location':
                  case 'location_meta': {
                    let token_array = [];
                    let id_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        token_array.push(cell_value['label']);

                        // Extract id accordingly based on value shape; to be appended within dynamic csv column.
                        let grid_id = '';
                        if (cell_value['id']) {
                          grid_id = cell_value['id'];
                        } else if (cell_value['grid_id']) {
                          grid_id = cell_value['grid_id'];
                        }
                        id_array.push(grid_id);
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    csv_row.push(id_array.join(token_array_delimiter));
                    break;
                  }
                  case 'tags': {
                    let token_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        token_array.push(cell_value);
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    break;
                  }
                  case 'link': {
                    let token_array = [];
                    if (post[field_id]) {
                      $.each(post[field_id], function (cell_index, cell_value) {
                        if (field['default'][cell_value['type']]) {
                          let category_label =
                            field['default'][cell_value['type']]['label'];
                          let token =
                            category_label + ': ' + cell_value['value'];
                          token_array.push(token);
                        }
                      });
                    }
                    csv_row.push(token_array.join(token_array_delimiter));
                    break;
                  }
                  case 'hash':
                  case 'magic_link':
                    {
                      if (
                        magic_link_app_keys.includes(field_id) &&
                        post[field_id]
                      ) {
                        const token = post[field_id] ? post[field_id] : '';
                        const url =
                          window.wpApiShare.site_url +
                          '/' +
                          window.list_settings.post_type_settings
                            .magic_link_apps[field_id]['url_base'] +
                          '/' +
                          token;
                        csv_row.push(url);
                      }
                    }
                    break;
                  case 'task':
                  case 'array':
                  default: {
                    csv_row.push('');
                    break;
                  }
                }
              }
            });

            // Assuming counts match, assign to parent csv download array.
            if (csv_row.length <= exporting_fields.length + 1) {
              csv_export.push(csv_row);
            }
          });

          // Generate export csv headers and prefix to parent csv download array.
          let csv_headers = ['ID', 'Name'].concat(
            exporting_fields
              .filter((field) => !['name'].includes(field['field_id']))
              .map((field) => field['name']),
          );
          csv_export.unshift(csv_headers);

          // Convert csv arrays into raw downloadable data.
          // --- ADD BOM for UTF-8 ---
          const BOM = '\uFEFF';
          const csv =
            BOM +
            csv_export
              .map((row) => {
                return row.map((item) => {
                  let escapeditem = item;
                  //if the string contains a doublequote escape it by doubling the double quoate like "" - https://stackoverflow.com/a/769675
                  if (String(item).includes('"')) {
                    escapeditem = item.replaceAll('"', '""');
                  }

                  return `"${escapeditem}"`;
                });
              })
              .join('\r\n');

          // Finally, automatically execute a download of generated csv data.
          let csv_download_link = document.createElement('a');
          let date = new Date();
          let year = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(
            date,
          );
          let month = new Intl.DateTimeFormat('en', {
            month: 'numeric',
          }).format(date);
          let day = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(
            date,
          );
          let hour = new Intl.DateTimeFormat('en', {
            hour: 'numeric',
            hour12: false,
          }).format(date);
          let minute = new Intl.DateTimeFormat('en', {
            minute: 'numeric',
          }).format(date);
          let second = new Intl.DateTimeFormat('en', {
            second: 'numeric',
          }).format(date);
          csv_download_link.download = `${year}_${month}_${day}_${hour}_${minute}_${second}_${window.list_settings.post_type}_list_export.csv`;
          csv_download_link.href =
            'data:text/csv;charset=utf-8;base64,' + window.Base64.encode(csv);
          csv_download_link.click();
          csv_download_link.remove();
        }

        // Callback to original caller...! ;)
        if (callback) {
          callback();
        }
      },
    );
  }

  function recursively_fetch_posts(
    offset,
    limit,
    total,
    posts,
    callback = null,
  ) {
    const current_filter = getCurrentFilter();
    // Build query based on current filter, to be executed.
    let query = current_filter.query;
    query['offset'] = offset;
    query['limit'] = limit;
    query['fields_to_return'] = [];

    window
      .makeRequestOnPosts(
        'POST',
        `${window.list_settings.post_type}/list`,
        JSON.parse(JSON.stringify(query)),
      )
      .promise()
      .then((response) => {
        // Concat any returned posts to main parent posts array.
        posts = posts.concat(
          response && response['posts'] ? response['posts'] : [],
        );

        // Recurse if more records are available, otherwise proceed with export callback.
        if (offset + limit < total) {
          recursively_fetch_posts(
            offset + limit,
            limit,
            total,
            posts,
            callback,
          );
        } else if (callback) {
          callback(posts);
        }
      })
      .catch((error) => {
        console.log(error);
        if (callback) {
          callback(posts);
        }
      });
  }

  $('#export_bcc_email_list').on('click', function (e) {
    export_list_display('email', $(e.currentTarget).text(), function () {
      // Show spinners.
      const spinner = $('#modal-large-title').find('.loading-spinner');
      $(spinner).addClass('active');

      let html = `
        <div class="grid-x">
            <div class="cell">
               <table><tbody id="grouping-table"></tbody></table>
            </div>

            <div class="cell">
                <a onclick="jQuery('#email-list-print').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['full_list'])} (<span id="list-count-full"></span>)</strong></a>
                <div class="cell" id="email-list-print" style="display:none;"></div>
            </div>
            <div class="cell">
                <a onclick="jQuery('#contacts-without').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['no_addr'])} (<span id="list-count-without"></span>)</strong></a>
                <div id="contacts-without" style="display:none;"></div>
            </div>
            <div class="cell">
                <a onclick="jQuery('#contacts-with').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['with_addr'])} (<span id="list-count-with"></span>)</strong></a>
                <div id="contacts-with" style="display:none;"></div>
            </div>
        </div>`;

      $('#modal-large-content').html(html);

      // Recursively fetch all filtered list posts, to be processed.
      recursively_fetch_posts(
        0,
        500,
        window.records_list['total'],
        [],
        function (posts) {
          let email_totals = [];
          let list_count = {
            with: 0,
            without: 0,
            full: 0,
          };
          let count = 0;
          let group = 0;
          let contacts_with = jQuery('#contacts-with');
          let contacts_without = jQuery('#contacts-without');

          // Generate totals.
          $.each(posts, function (i, v) {
            let has_email = false;
            if (
              typeof v.contact_email !== 'undefined' &&
              v.contact_email !== ''
            ) {
              if (typeof email_totals[group] === 'undefined') {
                email_totals[group] = [];
              }
              let non_empty_values = v.contact_email.filter((val) => val.value);
              non_empty_values.forEach((vv) => {
                let email = window.SHAREDFUNCTIONS.escapeHTML(vv.value);
                if (validate_email_address(email)) {
                  email_totals[group].push(email);
                  count++;
                  list_count['full']++;
                  has_email = true;
                } else {
                  console.log(`Invalid Email Format: ${email}`);
                }
              });
              if (count > 50) {
                group++;
                count = 0;
              }
              if (non_empty_values.length > 1) {
                contacts_with.append(
                  `<a href="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url)}/contacts/${window.SHAREDFUNCTIONS.escapeHTML(v.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(v.post_title)}</a><br>`,
                );
                list_count['with']++;
              }
            }
            if (!has_email) {
              contacts_without.append(
                `<a href="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url)}/contacts/${window.SHAREDFUNCTIONS.escapeHTML(v.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(v.post_title)}</a><br>`,
              );
              list_count['without']++;
            }
          });

          // Update count findings.
          let list_print = jQuery('#email-list-print');
          let all_emails = [];
          $.each(email_totals, function (index, values) {
            all_emails.push(values.join(', '));
          });
          list_print.append(
            window.SHAREDFUNCTIONS.escapeHTML(all_emails.join(', ')),
          );

          jQuery('#list-count-with').html(list_count['with']);
          jQuery('#list-count-without').html(list_count['without']);
          jQuery('#list-count-full').html(list_count['full']);

          // Generate links.
          let email_links = [];
          group = 0;

          $.each(posts, function (i, v) {
            if (
              typeof v.contact_email !== 'undefined' &&
              v.contact_email !== ''
            ) {
              if (typeof email_links[group] === 'undefined') {
                email_links[group] = [];
              }
              $.each(v.contact_email, function (ii, vv) {
                let email = window.SHAREDFUNCTIONS.escapeHTML(vv.value);
                if (validate_email_address(email)) {
                  email_links[group].push(email);
                }
              });
              if (email_links[group].length > 50) {
                group++;
              }
            }
          });

          // loop 50 each
          let grouping_table = $('#grouping-table');
          let email_strings = [];
          $.each(email_links, function (index, values) {
            index++;
            email_strings = [];
            email_strings = window.SHAREDFUNCTIONS.escapeHTML(
              values.join(', '),
            );
            email_strings.replace(/,/g, ', ');

            grouping_table.append(`
            <tr><td style="vertical-align:top; width:50%;"><a href="mailto:?subject=group${index}&bcc=${email_strings}" id="group-link-${index}" class="button expanded export-link-button">${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['open_email'])} ${index}</a></td>
            <td><a onclick="jQuery('#group-addresses-${index}').toggle()">${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['show_group_addrs'])}</a> <p style="display:none;overflow-wrap: break-word;" id="group-addresses-${index}">${email_strings.replace(/,/g, ', ')}</p></td></tr>
          `);
          });
          grouping_table.append(`
            <tr><td style="vertical-align:top; text-align:center; width:50%;"><a class="button expanded export-link-button" id="open_all">${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['bcc']['open_all'])}</a></td><td></td></tr>
        `);

          $('.export-link-button').on('click', function () {
            $(this).addClass('warning');
          });
          $('#open_all').on('click', function () {
            $('.export-link-button').each(function (i, v) {
              document.getElementById(v.id).click();
            });
          });

          // Hide spinners.
          $(spinner).removeClass('active');
        },
      );
    });
  });

  function validate_email_address(email) {
    const regex =
      /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
  }

  $('#export_phone_list').on('click', function (e) {
    export_list_display('phone', $(e.currentTarget).text(), function () {
      // Show spinners.
      const spinner = $('#modal-large-title').find('.loading-spinner');
      $(spinner).addClass('active');

      let html = `
        <div class="grid-x">
            <a onclick="jQuery('#email-list-print').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['phone']['full_list'])} (<span id="list-count-full"></span>)</strong></a>
            <div class="cell" id="email-list-print"></div>
        </div>
        <hr>
        <div class="grid-x">
            <div class="cell">
                <a onclick="jQuery('#contacts-without').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['phone']['no_phone'])} (<span id="list-count-without"></span>)</strong></a>
                <div id="contacts-without" style="display:none;"></div>
            </div>
            <div class="cell">
                <a onclick="jQuery('#contacts-with').toggle();"><strong>${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['phone']['with_phone'])} (<span id="list-count-with"></span>)</strong></a>
                <div id="contacts-with" style="display:none;"></div>
            </div>
        </div>`;

      $('#modal-large-content').html(html);

      // Recursively fetch all filtered list posts, to be processed.
      recursively_fetch_posts(
        0,
        500,
        window.records_list['total'],
        [],
        function (posts) {
          let phone_list = [];
          let list_count = {
            with: 0,
            without: 0,
            full: 0,
          };
          let group = 0;
          let contacts_with = jQuery('#contacts-with');
          let contacts_without = jQuery('#contacts-without');

          // Generate totals.
          $.each(posts, function (i, v) {
            let has_phone = false;
            if (
              typeof v.contact_phone !== 'undefined' &&
              v.contact_phone !== ''
            ) {
              if (typeof phone_list[group] === 'undefined') {
                phone_list[group] = [];
              }
              let non_empty_values = v.contact_phone.filter((val) => val.value);
              non_empty_values.forEach((vv) => {
                phone_list[group].push(vv.value);
                list_count['full']++;
                has_phone = true;
              });
              if (non_empty_values.length > 1) {
                contacts_with.append(
                  `<a  href="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url)}/contacts/${window.SHAREDFUNCTIONS.escapeHTML(v.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(v.post_title)}</a><br>`,
                );
                list_count['with']++;
              }
              if (phone_list.length > 50) {
                group++;
              }
            }
            if (!has_phone) {
              contacts_without.append(
                `<a  href="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url)}/contacts/${window.SHAREDFUNCTIONS.escapeHTML(v.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(v.post_title)}</a><br>`,
              );
              list_count['without']++;
            }
          });

          // Update count findings.
          let list_print = jQuery('#email-list-print');
          let all_numbers = [];
          $.each(phone_list, function (index, values) {
            all_numbers = all_numbers.concat(values);
          });
          list_print.append(
            window.SHAREDFUNCTIONS.escapeHTML(all_numbers.join(', ')),
          );

          jQuery('#list-count-with').html(list_count['with']);
          jQuery('#list-count-without').html(list_count['without']);
          jQuery('#list-count-full').html(list_count['full']);

          // Hide spinners.
          $(spinner).removeClass('active');
        },
      );
    });
  });

  $('.export_map_list').on('click', function (e) {
    if (window.list_settings['translations']['exports']['map']['mapbox_key']) {
      const title = $(e.currentTarget).text();
      export_list_display('map', title, function () {
        // Generate modal html.
        let html = `
        <span class="section-header"> ${window.SHAREDFUNCTIONS.escapeHTML(title)} | ${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['map']['mapped_locations'])}: <span id="mapped" class="loading-spinner active"></span> | ${window.SHAREDFUNCTIONS.escapeHTML(window.list_settings['translations']['exports']['map']['without_locations'])}: <span id="unmapped" class="loading-spinner active"></span> </span><br><br>
        <div id="dynamic-styles"></div>
        <div class="grid-x">
          <div class="cell medium-9" id="export_map_container">
            <div id="map-wrapper">
                <div id='map'></div>
            </div>
          </div>
          <div class="cell medium-3" id="export_map_sidebar_menu">
            <!-- details panel -->
            <h3 style="margin-left: 10px;">${esc(window.list_settings.translations.exports.map['records_on_zoomed_map'])}</h3>
            <button id="export_map_sidebar_menu_open_but" class="button" style="min-width: 100%; margin-left: 10px;">${esc(window.list_settings.translations.exports.map['open_zoomed_map'])}</button>
            <div id="export_map_sidebar_menu_details_panel" style="margin-left: 10px; max-height: 500px; overflow-y: scroll;">
              <table id="export_map_sidebar_menu_details_panel_table"></table>
            </div>
          </div>
        </div>`;

        $('#modal-full-content').html(html);

        // Show spinners.
        const spinner = $('#modal-full').find('.loading-spinner');
        $(spinner).addClass('active');

        // Insert dynamic styles.
        let map_content = jQuery('#dynamic-styles');
        map_content.append(`
            <style>
                #map-wrapper {
                    height: ${window.innerHeight - 100}px !important;
                    position:relative;
                }
                #map {
                    height: ${window.innerHeight - 100}px !important;
                }
            </style>
          `);

        // Recursively fetch all filtered list posts, to be processed.
        recursively_fetch_posts(
          0,
          500,
          window.records_list['total'],
          [],
          function (posts) {
            window.mapboxgl.accessToken =
              window.list_settings['translations']['exports']['map'][
                'mapbox_key'
              ];
            var map = new window.mapboxgl.Map({
              container: 'map',
              style: 'mapbox://styles/mapbox/light-v10',
              center: [-30, 20],
              minZoom: 1,
              zoom: 2,
            });

            // Handle open zoomed map records button clicks.
            $('#export_map_sidebar_menu_open_but').on('click', function (e) {
              e.preventDefault();
              const current_filter = getCurrentFilter();

              // Obtain handle to current map bounds.
              const bounds = map.getBounds();
              const bounds_ne = bounds.getNorthEast();
              const bounds_sw = bounds.getSouthWest();

              // Build query based on current filter and identified map bounds.
              let query = current_filter.query;
              query['offset'] = 0;
              query['limit'] = 500;
              query['fields_to_return'] = [];
              query['map_bounds'] = {
                ne: {
                  lat: bounds_ne['lat'],
                  lng: bounds_ne['lng'],
                },
                sw: {
                  lat: bounds_sw['lat'],
                  lng: bounds_sw['lng'],
                },
              };

              window
                .makeRequestOnPosts(
                  'POST',
                  `${window.list_settings.post_type}/list`,
                  JSON.parse(JSON.stringify(query)),
                )
                .promise()
                .then((response) => {
                  if (response?.posts?.length > 0) {
                    let items = response.posts || [];
                    DT_List.items = items;
                    window.records_list.posts = items; // adds global access to current list for plugins
                    window.records_list.total = response.total;

                    // save
                    if (
                      Object.prototype.hasOwnProperty.call(response, 'posts') &&
                      response.posts.length > 0
                    ) {
                      let records_list_ids_and_type = [];

                      $.each(items, function (id, post_object) {
                        records_list_ids_and_type.push({ ID: post_object.ID });
                      });

                      window.SHAREDFUNCTIONS.save_json_cookie(
                        `records_list`,
                        records_list_ids_and_type,
                        list_settings.post_type,
                      );
                    }

                    $('#bulk_edit_master_checkbox').prop('checked', false); //unchecks the bulk edit master checkbox when the list reloads.
                    $('#load-more').toggle(
                      items.length !== parseInt(response.total),
                    );
                    let result_text = list_settings.translations.txt_info
                      .replace('_START_', items.length)
                      .replace('_TOTAL_', response.total);
                    $('.filter-result-text').html(result_text);
                    DT_List.build_table(items);

                    // Generate corresponding custom filter.
                    DT_List.reset_split_by_filters();
                    DT_List.add_custom_filter(
                      esc(
                        window.list_settings.translations.exports.map[
                          'filter_name'
                        ],
                      ),
                      'custom-filter',
                      query,
                      DT_List.new_filter_labels,
                      false,
                    );

                    // Capture custom filter label and refresh ui.
                    DT_List.current_filter['labels'] = [
                      {
                        id: 'map',
                        name: esc(
                          window.list_settings.translations.exports.map[
                            'filter_label'
                          ],
                        ),
                      },
                    ];
                    DT_List.setup_current_filter_labels();

                    // Persist updated custom filter url parameters.
                    DT_List.update_url_query(DT_List.current_filter);

                    // Reset vertical scrollbar to start position.
                    window.setTimeout(function () {
                      $(window).scrollTop(0);
                    }, 0);

                    // Close modal window to display updated records list.
                    $('#modal-full').foundation('close');
                  } else {
                    alert(
                      `${esc(window.list_settings.translations.exports.map['no_records_on_zoomed_map_alert'])}`,
                    );
                  }
                })
                .catch((error) => {
                  console.log(error);
                });
            });

            // disable map rotation using right click + drag
            map.dragRotate.disable();
            map.touchZoomRotate.disableRotation();

            // load sources
            map.on('load', function () {
              let features = [];
              let mapped = 0;
              let unmapped = 0;
              $.each(posts, function (i, v) {
                if (typeof v.location_grid_meta !== 'undefined') {
                  features.push({
                    type: 'Feature',
                    geometry: {
                      type: 'Point',
                      coordinates: [
                        v.location_grid_meta[0].lng,
                        v.location_grid_meta[0].lat,
                      ],
                    },
                    properties: {
                      id: v.ID,
                      post_type: v.post_type,
                      title: v.post_title,
                      label: v.location_grid_meta[0].label,
                    },
                  });
                  mapped++;
                } else {
                  unmapped++;
                }
              });

              $('#mapped').html('(' + mapped + ')');
              $('#unmapped').html('(' + unmapped + ')');

              let geojson = {
                type: 'FeatureCollection',
                features: features,
              };

              map.addSource('dt-records-source', {
                type: 'geojson',
                data: geojson,
                cluster: true,
                clusterMaxZoom: 14,
                clusterRadius: 50,
              });

              const layer_color =
                '#' +
                ((Math.random() * 0xffffff) << 0).toString(16).padStart(6, '0');
              map.addLayer({
                id: 'dt-records-clusters',
                type: 'circle',
                source: 'dt-records-source',
                filter: ['has', 'point_count'],
                paint: {
                  'circle-color': [
                    'step',
                    ['get', 'point_count'],
                    layer_color,
                    100,
                    layer_color,
                    750,
                    layer_color,
                  ],
                  'circle-radius': [
                    'step',
                    ['get', 'point_count'],
                    20,
                    100,
                    30,
                    750,
                    40,
                  ],
                  'circle-stroke-width': 2,
                  'circle-stroke-color': '#fff',
                },
              });

              map.addLayer({
                id: 'dt-records-clusters-count',
                type: 'symbol',
                source: 'dt-records-source',
                filter: ['has', 'point_count'],
                layout: {
                  'text-field': '{point_count_abbreviated}',
                  'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                  'text-size': 12,
                },
              });

              map.addLayer({
                id: 'dt-records-unclustered-points',
                type: 'circle',
                source: 'dt-records-source',
                filter: ['!', ['has', 'point_count']],
                paint: {
                  'circle-color': layer_color,
                  'circle-radius': 5,
                  'circle-stroke-width': 2,
                  'circle-stroke-color': '#fff',
                },
              });

              map.on('click', () => {
                render_map_feature_details(
                  map,
                  map.queryRenderedFeatures({
                    layers: [
                      'dt-records-clusters',
                      'dt-records-unclustered-points',
                    ],
                  }),
                );
              });
              map.on('dragend', () => {
                render_map_feature_details(
                  map,
                  map.queryRenderedFeatures({
                    layers: [
                      'dt-records-clusters',
                      'dt-records-unclustered-points',
                    ],
                  }),
                );
              });
              map.on('zoomend', () => {
                render_map_feature_details(
                  map,
                  map.queryRenderedFeatures({
                    layers: [
                      'dt-records-clusters',
                      'dt-records-unclustered-points',
                    ],
                  }),
                );
              });

              map.on('mouseenter', 'dt-records-clusters', () => {
                map.getCanvas().style.cursor = 'pointer';
              });

              map.on('mouseenter', 'dt-records-clusters-count', () => {
                map.getCanvas().style.cursor = 'pointer';
              });

              map.on('mouseenter', 'dt-records-unclustered-points', () => {
                map.getCanvas().style.cursor = 'pointer';
              });

              map.on('mouseleave', 'dt-records-clusters', () => {
                map.getCanvas().style.cursor = '';
              });

              map.on('mouseleave', 'dt-records-clusters-count', () => {
                map.getCanvas().style.cursor = '';
              });

              map.on('mouseleave', 'dt-records-unclustered-points', () => {
                map.getCanvas().style.cursor = '';
              });

              // To avoid unwanted exceptions, ensure valid features are available, prior to rendering.
              if (geojson.features && geojson.features.length > 0) {
                var bounds = new window.mapboxgl.LngLatBounds();
                geojson.features.forEach(function (feature) {
                  if (
                    feature.geometry.coordinates &&
                    !feature.geometry.coordinates.includes(undefined)
                  ) {
                    bounds.extend(feature.geometry.coordinates);
                  }
                });
                map.fitBounds(bounds);

                // Display initial feature details.
                const details_panel = $(
                  '#export_map_sidebar_menu_details_panel',
                );
                const map_height = $(map.getContainer()).height();
                $(details_panel).css('height', map_height - 100 + 'px');
                $(details_panel).css('min-height', map_height - 100 + 'px');
                $(details_panel).css('max-height', map_height - 100 + 'px');
                render_map_feature_details(
                  map.queryRenderedFeatures({
                    layers: [
                      'dt-records-clusters',
                      'dt-records-unclustered-points',
                    ],
                  }),
                );
              }

              // Hide spinners.
              $(spinner).removeClass('active');
            });
          },
        );
      });
    }
  });

  function render_map_feature_details(map, features) {
    if (features) {
      // First, reset details html.
      const details_table = $('#export_map_sidebar_menu_details_panel_table');
      $(details_table).html(`<tbody></tbody>`);

      // Next, proceeding in display points; handling each accordingly based on parent layer.
      features.forEach(function (feature) {
        if (feature?.layer?.id === 'dt-records-unclustered-points') {
          let feature_html = render_map_feature_details_point_html(feature);
          if (feature_html) {
            $(details_table).find(`tbody`).append(feature_html);
          }
        } else if (feature?.layer?.id === 'dt-records-clusters') {
          render_map_feature_details_for_clusters(map, feature);
        }
      });
    }
  }

  function render_map_feature_details_for_clusters(map, cluster) {
    if (cluster?.source) {
      const cluster_source = map.getSource(cluster.source);
      const cluster_id = cluster?.properties.cluster_id;
      const point_count = cluster?.properties.point_count;

      if (cluster_source && cluster_id && point_count) {
        // Get all points under given cluster.
        cluster_source.getClusterLeaves(
          cluster_id,
          point_count,
          0,
          function (err, features) {
            // Ensure feature is a valid sought after record.
            features.forEach(function (feature) {
              let feature_html = render_map_feature_details_point_html(feature);
              if (feature_html) {
                $('#export_map_sidebar_menu_details_panel_table')
                  .find(`tbody`)
                  .append(feature_html);
              }
            });
          },
        );
      }
    }
  }

  function render_map_feature_details_point_html(feature) {
    if (
      feature?.properties?.id &&
      feature?.properties?.post_type &&
      feature?.properties?.title &&
      feature?.properties?.label
    ) {
      return `<tr><td><a target="_blank" href="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url)}/${window.SHAREDFUNCTIONS.escapeHTML(feature?.properties?.post_type)}/${window.SHAREDFUNCTIONS.escapeHTML(feature?.properties?.id)}">${window.SHAREDFUNCTIONS.escapeHTML(feature?.properties?.title)}</a> <span style="color: #808080;">${window.SHAREDFUNCTIONS.escapeHTML(feature?.properties?.label)}</span></td></tr>`;
    }

    return null;
  }

  // Register this module with DT_List
  DT_List.exports = {
    export_list_display: export_list_display,
  };
})(window.jQuery, window.list_settings, window.Foundation);
