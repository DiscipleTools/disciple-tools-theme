jQuery(document).ready(function ($) {
  function make_admin_request(type, part, data) {
    const options = {
      type: type,
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: `${window.dt_admin_scripts.rest_root}dt-admin/scripts/${part}`,
      beforeSend: (xhr) => {
        xhr.setRequestHeader("X-WP-Nonce", window.dt_admin_scripts.nonce);
      },
    };
    if (data && !window.lodash.isEmpty(data)) {
      options.data = type === "GET" ? data : JSON.stringify(data);
    }
    return jQuery.ajax(options);
  }


  $('.reset_count_button').on('click', function (){
    let post_type = $(this).data('post-type')
    let field_key = $(this).data('key')
    $(`#${post_type}_${field_key} .progress .loading-spinner`).addClass( "active" )
    make_admin_request( "POST", "reset_count_field", { post_type, field_key }).then(resp=>{
      let interval = setInterval( ()=>{
        make_admin_request( "GET", 'reset_count_field_progress', { post_type, field_key } ).then(status=>{
          $(`#${post_type}_${field_key} .progress .current`).text(resp.count - status.count)
          if ( status.count === 0 ){
            show_done()
          }
        })
      }, 5000)
      let check_status = function (){
        make_admin_request( "GET", 'reset_count_field_progress', { post_type, field_key, process:true } ).then(status=>{
          $(`#${post_type}_${field_key} .progress .current`).text(resp.count - status.count)
          if ( status.count === 0 ){
            show_done()
          } else {
            check_status()
          }
        }).catch(err=>{
          if ( err?.statusText === "timeout" ){
            check_status();
          }
        })
      }
      check_status();
      let show_done = ()=>{
        $(`#${post_type}_${field_key} .progress .current`).text("done")
        $(`#${post_type}_${field_key} .progress .total`).text("")
        clearInterval( interval )
        $(`#${post_type}_${field_key} .progress .loading-spinner`).removeClass( "active" )
      }
      $(`#${post_type}_${field_key} .progress .current`).text( 0 )
      $(`#${post_type}_${field_key} .progress .total`).text( '/' + resp.count)
    })
  })

  $('.process-jobs-button').on('click', function() {
    $(`#process-jobs-loading-spinner.loading-spinner`).addClass( "active" )
    make_admin_request("GET", 'process_jobs').then(status => {
        if (status.success === true) {
            $('.process-jobs-result-text').html('Done!')
            $(`#process-jobs-loading-spinner.loading-spinner`).removeClass( "active" )
        }
    })
})
  /**
   * DATA CLEAN-UP
   */

  $('.data-clean-up-button').on('click', function () {

    // Confirm data clean up is to proceed.
    if (confirm(window.lodash.escape($(this).data('delete_label')) + '?')) {
      let post_type = $(this).data('post_type');
      let tr = $(this).parent().parent();
      let spinner = $(tr).find('.progress .loading-spinner');

      // Indicate processing and submit data clean up request.
      $(spinner).addClass('active');
      make_admin_request( 'POST', 'data_clean_up', { post_type })
      .then(response => {
        $(spinner).removeClass('active');
        $(tr).find('.progress .current').text('done');
        $(tr).find('.progress .total').text('');
      });
    }
  });

  $('#locations-clean-up-button').on('click', function () {

    // Confirm data clean up is to proceed.
    if (confirm(window.lodash.escape($(this).data('delete_label')) + '?')) {
      let tr = $(this).parent().parent();
      let spinner = $(tr).find('.progress .loading-spinner');

      // Indicate processing and submit data clean up request.
      $(spinner).addClass('active');
      make_admin_request( 'POST', 'locations_clean_up')
      .then(response => {
        $(spinner).removeClass('active');
        $(tr).find('.progress .current').text('done');
        $(tr).find('.progress .total').text('');
      });
    }
  });



  /**
   * DATA CLEAN-UP
   */

  /**
   * FILE UPLOADS
   */
  $('.file-upload-display-uploader').on('click', function (e) {
    e.preventDefault();

    // Fetch handle to key workflow elements
    let parent_form = $("form[name='" + $(e.currentTarget).data('form') + "']");
    let icon_input = $("input[name='" + $(e.currentTarget).data('icon-input') + "']");

    // Only proceed if we have valid handles
    if (parent_form && icon_input) {

      // Build media uploader modal
      let mediaFrame = window.wp.media({

        // Accepts [ 'select', 'post', 'image', 'audio', 'video' ]
        // Determines what kind of library should be rendered.
        frame: 'select',

        // Modal title.
        title: window.dt_admin_shared.escape(window.dt_admin_scripts.upload.title),

        // Enable/disable multiple select
        multiple: false,

        // Library wordpress query arguments.
        library: {
          order: 'DESC',

          // [ 'name', 'author', 'date', 'title', 'modified', 'uploadedTo', 'id', 'post__in', 'menuOrder' ]
          orderby: 'date',

          // mime type. e.g. 'image', 'image/jpeg'
          type: ['image'],

          // Searches the attachment title.
          search: null,

          // Includes media only uploaded to the specified post (ID)
          uploadedTo: null // wp.media.view.settings.post.id (for current post ID)
        },

        button: {
          text: window.dt_admin_shared.escape(window.dt_admin_scripts.upload.button_txt)
        }

      });

      // Handle selected files
      mediaFrame.on('select', function () {

        // Fetch and convert selected into json object
        let selected = mediaFrame.state().get('selection').first().toJSON();

        // Update form icon link
        icon_input.val(selected.url);

        // Auto-submit so as to refresh changes
        parent_form.submit();

      });

      // Open the media uploader.
      mediaFrame.open();
    }
  });
  /**
   * FILE UPLOADS
   */

  /**
   * COLOR PICKER
   */
  $('.color-display-picker').wpColorPicker();

   /**
   * Flyout menu
   */
  const details = [...document.querySelectorAll('details.flyout')];
  document.addEventListener('click', function(e) {
    if (!details.some(f => f.contains(e.target))) {
      details.forEach(f => f.removeAttribute('open'));
    } else {
      details.forEach(f => !f.contains(e.target) ? f.removeAttribute('open') : '');
    }
  })


  /**
   * Roles manager source filter
   */
  const filter = document.querySelector('#role-manager #source-filter')
  if (filter) {
    const capabilities = document.querySelectorAll('#role-manager .capability')
    const showCapsForSource = () => {
      capabilities.forEach((capability) => {
        if (capability.dataset.source === filter.value) {
          capability.classList.remove('hide')
        } else {
          capability.classList.add('hide')
        }
      })
    }
    filter.addEventListener('input', showCapsForSource)
    showCapsForSource()
  }

  /**
   * DT EXPORTS
   */

  $('.dt-export-submit-but').on('click', function (e) {
    e.preventDefault();

    // Package service findings.
    let services = {};
    let service_id = $(e.currentTarget).data('service_id');
    services[service_id] = {
      'id': service_id
    };

    // Update export form variables and submit.
    $('#dt_export_selected_services').val(JSON.stringify(services));
    $('#dt_export_form').submit();
  });

  /**
   * DT EXPORTS
   */


  /**
   * DT IMPORTS
   */

  $('.dt-import-post-type-but').on('click', function (e) {
    const post_type_but = $(e.currentTarget);
    $('.dt-import-post-type-but').css('background-color', '');
    $(post_type_but).css('background-color', '#efe0e3');

    refresh_post_type_meta_details( $(post_type_but).data('post_type') );
  });

  $('#dt_import_details_record_type_settings_checkbox').on('change', function (e) {
    update_config_selections( $(e.currentTarget).data('post_type'), 'update', update_config_selections_display );
  });

  $(document).on('change', '.dt-tile-select-settings-checkbox', function (e) {
    update_config_selections( $(e.currentTarget).data('post_type'), 'update', update_config_selections_display );
  });

  $(document).on('change', '.dt-tile-select-fields-checkbox', function (e) {
    let tile_checkbox = $(e.currentTarget);
    let post_type = $(tile_checkbox).data('post_type');
    let tile_id = $(tile_checkbox).data('tile_id');
    let tile_table = $(`table[data-tile_id="${window.dt_admin_shared.escape(tile_id)}"]`);

    // Assuming a valid handle to tile table has been found, check field selections accordingly.
    if ( tile_table ) {
      $(tile_table).find('input.dt-field-checkbox').prop('checked', $(tile_checkbox).prop('checked'));
      update_config_selections( post_type, 'update', update_config_selections_display );
    }
  });

  $(document).on('click', '.dt-field-checkbox', function (e) {
    let post_type = $(e.currentTarget).data('post_type');
    update_config_selections( post_type, 'update', update_config_selections_display );
  });

  $('#dt_import_submit_but').on('click', function (e) {
    e.preventDefault();

    // Prompt user accordingly based on the current shape of things.
    let dt_import_uploaded_config_selections = JSON.parse( $('#dt_import_uploaded_config_selections').text() );
    if ( $.isEmptyObject( dt_import_uploaded_config_selections ) ) {
      alert(`Nothing to import; please ensure valid selections have been made.`);

    } else if ( confirm(`Are you sure you wish to import selected record types, tiles & fields?`) ) {

      // Update import form variables and submit.
      $('#dt_import_uploaded_config').val($('#dt_import_uploaded_config_raw').text());
      $('#dt_import_selections').val(JSON.stringify(dt_import_uploaded_config_selections));
      $('#dt_import_form').submit();
    }
  });

  function auto_select_new_post_types() {

    // Ensure a valid config file has been selected and uploaded.
    const dt_import_uploaded_config_raw = $('#dt_import_uploaded_config_raw');
    if ( $(dt_import_uploaded_config_raw).text() ) {
      let dt_import_uploaded_config = JSON.parse( atob( $(dt_import_uploaded_config_raw).text(), true ) );

      let dt_import_uploaded_config_selections = $('#dt_import_uploaded_config_selections');
      let config_selections = JSON.parse( $(dt_import_uploaded_config_selections).text() );

      let dt_import_existing_post_types = JSON.parse( $('#dt_import_existing_post_types').text() );
      let dt_import_config_setting_keys = JSON.parse( $('#dt_import_config_setting_keys').text() );

      if (dt_import_uploaded_config['dt_settings'] && dt_import_uploaded_config['dt_settings'][dt_import_config_setting_keys['post_types_settings_key']] && dt_import_uploaded_config['dt_settings'][dt_import_config_setting_keys['post_types_settings_key']]['values']) {
        let auto_display_post_type = null;
        for (const [post_type, post_type_config] of Object.entries(dt_import_uploaded_config['dt_settings'][dt_import_config_setting_keys['post_types_settings_key']]['values'])) {

          // Identify new post types; which will default to an auto selection.
          if ( !dt_import_existing_post_types.includes(post_type) ) {

            // Group fields to tiles and auto select tile meta settings.
            const tile_buckets = group_tiles_fields_buckets( post_type );
            let updated_tile_buckets = {};
            for (const [tile, fields] of Object.entries(tile_buckets)) {
              updated_tile_buckets[tile] = {
                'import_meta': true,
                'fields': fields
              };
            }

            // Should not be the case, but if so, override pre-existing selections.
            config_selections[post_type] = {
              'import_meta': true,
              'tiles': updated_tile_buckets
            };

            // Capture post type to be auto displayed.
            if ( !auto_display_post_type ) {
              auto_display_post_type = post_type;
            }
          }
        }

        // Update config selections and auto display various import views.
        if ( auto_display_post_type ) {
          $(dt_import_uploaded_config_selections).text( JSON.stringify( config_selections ) );
          refresh_post_type_meta_details( auto_display_post_type );
        }
      }
    }
  }

  function group_tiles_fields_buckets( post_type ) {
    let dt_import_uploaded_config = JSON.parse( atob( $('#dt_import_uploaded_config_raw').text(), true ) );
    let dt_import_config_setting_keys = JSON.parse( $('#dt_import_config_setting_keys').text() );
    let dt_settings = dt_import_uploaded_config['dt_settings'];
    let tile_settings = dt_settings[dt_import_config_setting_keys['tiles_settings_key']]['values'][post_type];
    let custom_tile_settings = dt_settings[dt_import_config_setting_keys['custom_tiles_settings_key']]['values'][post_type];
    let field_settings = dt_settings[dt_import_config_setting_keys['fields_settings_key']]['values'][post_type];

    let is_custom_import = dt_import_config_setting_keys['type'] === 'custom';

    // If custom; filter out non-custom fields.
    if ( is_custom_import ) {
      let filtered_field_settings = {};
      for (const [field_key, field] of Object.entries(field_settings)) {
        if ( field['customizable'] && field['customizable'] !== false ) {
          filtered_field_settings[field_key] = field;
        }
      }

      field_settings = filtered_field_settings;
    }

    // Place importing post type fields into their respective tile buckets.
    let tile_buckets = {};
    let no_tile = [];
    for (const [field_key, field] of Object.entries(field_settings)) {
      if (field['tile'] && tile_settings[field['tile']]) {
        if (tile_buckets[field['tile']] === undefined) {
          tile_buckets[field['tile']] = [];
        }
        tile_buckets[field['tile']].push(field_key);
      } else {
        no_tile.push(field_key);
      }
    }
    tile_buckets['no_tile'] = no_tile;

    // Ensure no custom tiles have been missed!
    if ( custom_tile_settings ) {
      for (const [tile_key, tile] of Object.entries(custom_tile_settings)) {
        if (tile_buckets[tile_key] === undefined) {
          tile_buckets[tile_key] = [];
        }
      }
    }

    return tile_buckets;
  }

  function refresh_post_type_meta_details( post_type ) {
    let dt_import_post_type_meta_div = $('#dt_import_post_type_meta_div');
    let dt_import_tiles_fields_div = $('#dt_import_tiles_fields_div');
    let dt_import_selections_div = $('#dt_import_selections_div');
    let dt_import_uploaded_config = JSON.parse( atob( $('#dt_import_uploaded_config_raw').text(), true ) );
    let dt_import_uploaded_config_selections = JSON.parse( $('#dt_import_uploaded_config_selections').text() );
    let dt_import_existing_post_types = JSON.parse( $('#dt_import_existing_post_types').text() );
    let dt_import_config_setting_keys = JSON.parse( $('#dt_import_config_setting_keys').text() );

    // Refresh selected post type details.
    $(dt_import_tiles_fields_div).fadeOut('fast');
    $(dt_import_post_type_meta_div).fadeOut('fast', function () {
      let dt_settings = dt_import_uploaded_config['dt_settings'];
      let post_type_settings = dt_settings[dt_import_config_setting_keys['post_types_settings_key']]['values'][post_type];
      let custom_post_type_settings = dt_settings[dt_import_config_setting_keys['custom_post_types_settings_key']]['values'][post_type];
      let key = post_type_settings['post_type'];
      let already_exists = dt_import_existing_post_types.includes(post_type);

      // Default to custom settings, if present.
      let label_singular = (custom_post_type_settings?.['label_singular']) ? custom_post_type_settings['label_singular'] : post_type_settings['label_singular'];
      let label_plural = (custom_post_type_settings?.['label_plural']) ? custom_post_type_settings['label_plural'] : post_type_settings['label_plural'];
      let is_custom = (custom_post_type_settings?.['is_custom']) ? custom_post_type_settings['is_custom'] : post_type_settings['is_custom'];
      let import_meta = ( dt_import_uploaded_config_selections[post_type]?.['import_meta'] !== undefined );

      $('#dt_import_details_key_td').text(key);
      $('#dt_import_details_already_installed_td').text(already_exists ? 'Yes' : 'No');
      $('#dt_import_details_label_singular_td').text(label_singular);
      $('#dt_import_details_label_plural_td').text(label_plural);
      $('#dt_import_details_record_type_td').text(is_custom ? 'Custom' : 'Default');

      // Any detected selections to result in the displaying of tiles & fields section.
      let import_record_type_settings_checkbox = $('#dt_import_details_record_type_settings_checkbox');
      $(import_record_type_settings_checkbox).data('post_type', post_type);
      $(import_record_type_settings_checkbox).prop('checked', import_meta);

      // Automatically display tiles & fields.
      refresh_tiles_fields( post_type );

      // Display details section.
      $(dt_import_post_type_meta_div).fadeIn('fast');
      $(dt_import_selections_div).fadeIn('fast');
    });
  }

  function refresh_tiles_fields( post_type ) {
    let dt_import_tiles_fields_div = $('#dt_import_tiles_fields_div');
    let dt_import_tiles_fields_content_div = $('#dt_import_tiles_fields_content_div');
    let dt_import_uploaded_config = JSON.parse( atob( $('#dt_import_uploaded_config_raw').text(), true ) );
    let dt_import_uploaded_config_selections = JSON.parse( $('#dt_import_uploaded_config_selections').text() );
    let dt_import_config_setting_keys = JSON.parse( $('#dt_import_config_setting_keys').text() );
    let dt_settings = dt_import_uploaded_config['dt_settings'];
    let tile_settings = dt_settings[dt_import_config_setting_keys['tiles_settings_key']]['values'][post_type];
    let field_settings = dt_settings[dt_import_config_setting_keys['fields_settings_key']]['values'][post_type];

    $(dt_import_tiles_fields_div).fadeOut('fast', function () {

      // Next, iterate over tile buckets and display.
      let html = ``;
      for (const [tile, bucket] of Object.entries(group_tiles_fields_buckets( post_type ))) {
        let tile_label = (tile === 'no_tile') ? 'No Tile' : tile_settings[tile]['label'];
        if ( (tile !== 'no_tile') || ((tile === 'no_tile') && (bucket.length > 0)) ) {
          let import_meta = dt_import_uploaded_config_selections[post_type] && dt_import_uploaded_config_selections[post_type]['tiles'][tile] && dt_import_uploaded_config_selections[post_type]['tiles'][tile]['import_meta'] === true;

          html += `
          <table class="widefat striped" style="margin-bottom: 10px;" data-tile_id="${window.dt_admin_shared.escape(tile)}">
            <thead>
              <tr>
                <th>${window.dt_admin_shared.escape(tile_label)}</th>
                <th style="text-align: right;">
                    <label>
                        <span style="font-size: 10px;">Import Tile Settings</span>
                        <input  type="checkbox"
                          class="dt-tile-select-settings-checkbox"
                          style="margin-right: 4px;"
                          data-post_type="${window.dt_admin_shared.escape(post_type)}"
                          data-tile_id="${window.dt_admin_shared.escape(tile)}"
                          ${(import_meta) ? 'checked' : ''}/>
                    </label>
                </th>
              </tr>`;

            if (bucket.length > 0) {
              html += `<tr>
                  <th></th>
                  <th style="text-align: right;">
                      <label>
                          <span style="font-size: 10px;">Un/Check Fields</span>
                          <input  type="checkbox"
                            class="dt-tile-select-fields-checkbox"
                            style="margin-right: 4px;"
                            data-post_type="${window.dt_admin_shared.escape(post_type)}"
                            data-tile_id="${window.dt_admin_shared.escape(tile)}"/>
                      </label>
                  </th>
                </tr>`;
            }

            html += `</thead>
            <tbody>`;

            bucket.forEach((field_id) => {
              let checked = (dt_import_uploaded_config_selections[post_type] && dt_import_uploaded_config_selections[post_type]['tiles'][tile] && dt_import_uploaded_config_selections[post_type]['tiles'][tile]['fields'].includes(field_id));
              let field_name = field_settings[field_id]['name'];
              html += `
              <tr>
                <td>
                  ${window.dt_admin_shared.escape(field_name)}
                </td>
                <td style="text-align: right;">
                  <input    type="checkbox"
                            class="dt-field-checkbox"
                            data-post_type="${window.dt_admin_shared.escape(post_type)}"
                            data-tile_id="${window.dt_admin_shared.escape(tile)}"
                            data-field_id="${window.dt_admin_shared.escape(field_id)}"
                            ${(checked ? 'checked':'')}/>
                </td>
              </tr>`;
            });

            html += `</tbody>
          </table>`;
        }
      }
      $(dt_import_tiles_fields_content_div).html(html);

      // Update json selections & then refresh display.
      update_config_selections( post_type, 'update', function() {
        update_config_selections_display();

        // Display tiles & fields section.
        $(dt_import_tiles_fields_content_div).fadeIn('fast');
        $(dt_import_tiles_fields_div).fadeIn('fast');
      });
    });
  }

  function update_config_selections( post_type, update_type = 'update', callback = undefined ) {
    let dt_import_tiles_fields_content_div = $('#dt_import_tiles_fields_content_div');
    let dt_import_uploaded_config_selections = $('#dt_import_uploaded_config_selections');
    let config_selections = JSON.parse( $(dt_import_uploaded_config_selections).text() );

    switch( update_type ) {
      case 'update': {

        // Ensure the has_selection default state, is governed by post type's import_meta flag.
        let post_type_import_meta = $('#dt_import_details_record_type_settings_checkbox').prop('checked');
        let has_selections = post_type_import_meta;
        let updated_tiles = {};
        $(dt_import_tiles_fields_content_div).find('table').each(function (idx, table) {

          // Identify table tile id and meta settings status.
          const tile_select_settings_checkbox = $(table).find('input.dt-tile-select-settings-checkbox');
          if ( tile_select_settings_checkbox ) {
            let tile_import_meta = $(tile_select_settings_checkbox).prop('checked');
            let tile_id = $(tile_select_settings_checkbox).data('tile_id');
            let selected_fields = [];

            // We have a selection if a tile import meta check is detected.
            if ( tile_import_meta ) {
              has_selections = true;
            }

            // Proceed with identifying selected fields.
            $(table).find('input.dt-field-checkbox').each(function (field_idx, input) {

              // Only concern ourselves with selected fields.
              if ( $(input).prop('checked') ) {
                let field_id = $(input).data('field_id');
                selected_fields.push(field_id);
                has_selections = true;
              }
            });

            // Only select tiles which have either import meta checked or has selected fields.
            if ( ( tile_import_meta && tile_import_meta === true ) || selected_fields.length > 0 ) {
              updated_tiles[tile_id] = {
                'import_meta': tile_import_meta,
                'fields': selected_fields
              };
            }
          }
        });

        // Update global selections shape.
        config_selections[post_type] = {
          'import_meta': post_type_import_meta,
          'tiles': updated_tiles
        };

        // If no tile selections have been identified for given post type; delete global config entry.
        if ( !has_selections ) {
          delete config_selections[post_type];
        }
        $(dt_import_uploaded_config_selections).text( JSON.stringify( config_selections ) );
        break;
      }
      case 'delete': {
        if ( config_selections[post_type] ) {
          delete config_selections[post_type];
          $(dt_import_uploaded_config_selections).text( JSON.stringify( config_selections ) );
        }
        break;
      }
    }

    if(callback) {
      callback();
    }
  }

  function update_config_selections_display() {
    let dt_import_selections_content_div = $('#dt_import_selections_content_div');
    let dt_import_uploaded_config = JSON.parse( atob( $('#dt_import_uploaded_config_raw').text(), true ) );
    let dt_import_uploaded_config_selections = JSON.parse( $('#dt_import_uploaded_config_selections').text() );
    let dt_import_config_setting_keys = JSON.parse( $('#dt_import_config_setting_keys').text() );
    let dt_settings = dt_import_uploaded_config['dt_settings'];

    let html = ``;

    // Iterate over selected post types and their corresponding tiles & fields.
    for (const [post_type, config_selection] of Object.entries(dt_import_uploaded_config_selections)) {
      let post_type_settings = dt_settings[dt_import_config_setting_keys['post_types_settings_key']]['values'][post_type];
      let custom_post_type_settings = dt_settings[dt_import_config_setting_keys['custom_post_types_settings_key']]['values'][post_type];
      let tile_settings = dt_settings[dt_import_config_setting_keys['tiles_settings_key']]['values'][post_type];
      let custom_tile_settings = dt_settings[dt_import_config_setting_keys['custom_tiles_settings_key']]['values'][post_type];
      let field_settings = dt_settings[dt_import_config_setting_keys['fields_settings_key']]['values'][post_type];
      let custom_field_settings = dt_settings[dt_import_config_setting_keys['custom_fields_settings_key']]['values'][post_type];

      if ( post_type_settings && tile_settings && field_settings ) {
        let post_type_name = post_type_settings['label_plural'] ? post_type_settings['label_plural'] : post_type;
        if ( custom_post_type_settings['label_plural'] ) {
          post_type_name = custom_post_type_settings['label_plural'];
        }

        html += `
        <span style="font-weight: bold;">${window.dt_admin_shared.escape(post_type_name)}</span>
        <span style="float: right;">${ config_selection['import_meta'] ? '<i class="mdi mdi-card-bulleted-outline" style="font-size: 15px;"></i>' : '' }</span>
        <hr>
        `;

        // Proceed with the building of selected tiles & fields.
        for (const [tile, tile_config_selection] of Object.entries(config_selection['tiles'])) {
          let tile_import_meta = tile_config_selection['import_meta'] === true;
          const fields = tile_config_selection['fields'];
          let tile_label = ( tile === 'no_tile' ) ? 'No Tile' : tile_settings[tile]['label'];
          if ( custom_tile_settings?.[tile]?.['label'] ) {
            tile_label = custom_tile_settings[tile]['label'];
          }

          if ( fields.length > 0 || tile_import_meta ) {
            html += `
            <table class="widefat striped" style="margin-bottom: 20px;">
              <thead>
                  <tr>
                      <th>${window.dt_admin_shared.escape(tile_label)} <span style="float: right;">${ tile_import_meta ? '<i class="mdi mdi-card-bulleted-outline" style="font-size: 15px;"></i>' : '' }</span></th>
                  </tr>
              </thead>
              <tbody>`;

            fields.forEach((field_id) => {
              let field_name = field_settings[field_id]['name'] ? field_settings[field_id]['name'] : field_id;
              if ( custom_field_settings[field_id]?.name ) {
                field_name = custom_field_settings[field_id]['name'];
              }

              html += `
                <tr>
                  <td>
                    ${window.dt_admin_shared.escape(field_name)}
                  </td>
                </tr>`;
            });

            html += `
              </tbody>
            </table>
            `;
          }
        }
      }
    }

    $(dt_import_selections_content_div).html(html);
  }

  /**
   * DT IMPORTS
   */
})
