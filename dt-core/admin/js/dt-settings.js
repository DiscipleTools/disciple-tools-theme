"use strict";

let all_settings = window.field_settings
let dt_shared = window.dt_admin_shared

function makeRequest(type, url, data, base = "dt/v1/") {
  //make sure base has a trailing slash if url does not start with one
  if ( !base.endsWith('/') && !url.startsWith('/')){
    base += '/'
  }
  const options = {
    type: type,
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: url.startsWith("http") ? url : `${window.field_settings.root}${base}${url}`,
    beforeSend: (xhr) => {
      xhr.setRequestHeader("X-WP-Nonce", window.field_settings.nonce);
    },
  };

  if (data) {
    options.data = type === "GET" ? data : JSON.stringify(data);
  }

  return jQuery.ajax(options);
}

jQuery(document).ready(function($) {
  $( document ).tooltip();

  window.API = {};
  window.API.create_new_post_type = (new_key, new_name_single, new_name_plural) => makeRequest("POST", `create-new-post-type`, {
    key: new_key,
    single: new_name_single,
    plural: new_name_plural
  }, `dt-admin-settings/`);

  window.API.update_post_type = (key, name_singular, name_plural, displayed) => makeRequest("POST", `update-post-type`, {
    key: key,
    single: name_singular,
    plural: name_plural,
    displayed: displayed
  }, `dt-admin-settings/`);

  window.API.delete_post_type = (key, delete_all_records) => makeRequest("POST", `delete-post-type`, {
    key: key,
    delete_all_records: delete_all_records
  }, `dt-admin-settings/`);

  window.API.update_roles = (post_type, roles) => makeRequest("POST", `update-roles`, {
    post_type: post_type,
    roles: roles
  }, `dt-admin-settings/`);

  window.API.create_new_tile = (post_type, new_tile_name, new_tile_description) => makeRequest("POST", `create-new-tile`, {
    post_type: post_type,
    new_tile_name: new_tile_name,
    new_tile_description: new_tile_description,
  }, `dt-admin-settings/`);

  window.API.get_tile = (post_type, tile_key) => makeRequest("POST", `get-tile`, {
    post_type: post_type,
    tile_key: tile_key,
  }, `dt-admin-settings`);

  window.API.edit_tile = (post_type, tile_key, tile_label, tile_description, hide_tile) => makeRequest("POST", `edit-tile`, {
    post_type: post_type,
    tile_key: tile_key,
    tile_label: tile_label,
    tile_description: tile_description,
    hide_tile: hide_tile,
  }, `dt-admin-settings/`);

  window.API.delete_tile = (post_type, tile_key) => makeRequest("POST", `delete-tile`, {
    post_type: post_type,
    tile_key: tile_key,
  }, `dt-admin-settings/`);

  window.API.edit_translations = (translation_type, post_type, tile_key, translations, field_key=null, field_option_key=null) => makeRequest("POST", `edit-translations`, {
    translation_type: translation_type,
    post_type: post_type,
    tile_key: tile_key,
    translations: translations,
    field_key: field_key,
    field_option_key: field_option_key,
  }, `dt-admin-settings`);

  window.API.new_field = (post_type, tile_key, new_field_name, new_field_type, new_field_private, connection_field_options) => makeRequest("POST", `new-field`, {
    post_type,
    tile_key,
    new_field_name,
    new_field_type,
    new_field_private,
    connection_field_options
  }, `dt-admin-settings/`);

  window.API.edit_field = (post_type, tile_key, field_key, custom_name, tile_select, field_description, field_icon, visibility) => makeRequest("POST", `edit-field`, {
    post_type: post_type,
    tile_key: tile_key,
    field_key: field_key,
    custom_name: custom_name,
    tile_select: tile_select,
    field_description: field_description,
    field_icon: field_icon,
    visibility: visibility,
  }, `dt-admin-settings/`);

  window.API.delete_field = ( post_type, field_key ) => makeRequest("DELETE", `field`, {
    post_type,
    field_key,
  }, `dt-admin-settings/`);

  window.API.new_field_option = (post_type, tile_key, field_key, field_option_name, field_option_description, field_option_icon) => makeRequest("POST", `new-field-option`, {
    post_type: post_type,
    tile_key: tile_key,
    field_key: field_key,
    field_option_name: field_option_name,
    field_option_description: field_option_description,
    field_option_icon: field_option_icon,
  }, `dt-admin-settings/`);

  window.API.edit_field_option = (post_type, tile_key, field_key, field_option_key, new_field_option_label, new_field_option_description, field_option_icon, visibility) => makeRequest("POST", `edit-field-option`, {
    post_type: post_type,
    tile_key: tile_key,
    field_key: field_key,
    field_option_key: field_option_key,
    new_field_option_label: new_field_option_label,
    new_field_option_description: new_field_option_description,
    field_option_icon: field_option_icon,
    visibility: visibility,
  }, `dt-admin-settings/`);

  window.API.delete_field_option = ( post_type, field_key, field_option_key ) => makeRequest("DELETE", `field-option`, {
    post_type,
    field_key,
    field_option_key,
  }, `dt-admin-settings/`);

  window.API.update_tile_and_fields_order = (post_type, dt_custom_tiles_and_fields_ordered) => makeRequest("POST", `update-tiles-and-fields-order`, {
    post_type: post_type,
    dt_custom_tiles_and_fields_ordered: dt_custom_tiles_and_fields_ordered,
  }, `dt-admin-settings/`);

  window.API.update_field_options_order = (post_type, field_key, sortable_field_options_ordering) => makeRequest("POST", `update-field-options-order`, {
    post_type: post_type,
    field_key: field_key,
    sortable_field_options_ordering: sortable_field_options_ordering,
  }, `dt-admin-settings/`);

  window.API.remove_custom_field_name = (post_type, field_key) => makeRequest("POST", `remove-custom-field-name`, {
    post_type: post_type,
    field_key: field_key,
  }, `dt-admin-settings/`);

  window.API.remove_custom_field_option_label = (post_type, field_key, field_option_key) => makeRequest("POST", `remove-custom-field-option-label`, {
    post_type: post_type,
    field_key: field_key,
    field_option_key: field_option_key,
  }, `dt-admin-settings/`);

  function autonavigate_to_menu() {
    let tile_key = get_tile_from_uri();
    let field_key = get_field_from_uri();
    click_tile(tile_key);
    click_field(field_key);
  }

  function get_tile_from_uri() {
    let tile = window.location.search.match('tile=(.*)');
    if ( tile !== null ) {
      return tile[1];
    }
    return null;
  }

  function get_field_from_uri() {
    let field = window.location.hash;
    field = field.replace('#','');
    return field;
  }

  function click_tile(tile_key) {
    $(`.field-settings-table-tile-name[data-key="${tile_key}"]`).ready(function() {
      $(`.field-settings-table-tile-name[data-key="${tile_key}"]`).addClass('menu-highlight');
      $(`.field-settings-table-tile-name[data-key="${tile_key}"]`).trigger('click');
    });
  }

  function click_field(field_key) {
    $(`.field-settings-table-field-name[data-key="${field_key}"]`).ready(function() {
      $(`.field-settings-table-field-name[data-key="${field_key}"]`).addClass('menu-highlight');
      $(`.field-settings-table-field-name[data-key="${field_key}"]`).trigger('click');
    });
  }

  autonavigate_to_menu();

  function get_post_type() {
    return window.field_settings.post_type;
  }

  let sortable_options = {
    update: function(event,ui) {
      if (!$(event)[0].originalEvent.target.dataset) {
        return;
      }
      let post_type = get_post_type();
      let moved_element = ui.item[0];
      let tile_key = moved_element.dataset.parentTileKey || moved_element.id;

      // Check if moved element is a field option
      if (moved_element.dataset['fieldOptionKey']) {
        let field_key = moved_element.dataset['fieldKey'];
        let sortable_field_options_ordering = [];
        let field_options = $(`.field-name-content[data-field-key=${field_key}]`);

        $.each(field_options, function(option_index, option_element) {
          sortable_field_options_ordering.push(option_element.dataset['fieldOptionKey']);
        });
        window.API.update_field_options_order(post_type, field_key, sortable_field_options_ordering).promise().then(function(new_order){
          let old_order = all_settings.post_type_settings.fields[field_key]['default'];
          all_settings.post_type_settings.fields[field_key]['default'] = order_field_option_keys_by_array(old_order, new_order);

          tile_key = window.field_settings.post_type_settings.fields[field_key].tile;
          show_preview_tile(tile_key);
        });
        return;
      }
      let dt_custom_tiles_and_fields_ordered = get_dt_custom_tiles_and_fields_ordered();
      window.API.update_tile_and_fields_order(post_type, dt_custom_tiles_and_fields_ordered).promise().then(function(result) {
        if ( result[post_type][tile_key] ) {
          window.field_settings.post_type_settings.tiles[tile_key].order = result[post_type][tile_key]['order'];
        }
        show_preview_tile(tile_key);
      });
    },
  }
  $('.field-settings-table, .tile-rundown-elements, .field-settings-table-child-toggle').sortable(sortable_options);

  function order_field_option_keys_by_array(old_order, new_order) {
    return Object.fromEntries(
      Object.entries(old_order).sort((a, b) => new_order.indexOf(a[0]) - new_order.indexOf(b[0]))
    );
  }

  function get_dt_custom_tiles_and_fields_ordered() {
    let dt_custom_tiles_and_fields_ordered = {};
    let tiles = $('.field-settings-table-tile-name').map((index, tile)=>{
      return tile.dataset.key;
    })

    let tile_priority = 10;

    $.each(tiles, function(tile_index, tile_key) {
      if (tile_key === '' ) {
        return;
      }
      dt_custom_tiles_and_fields_ordered[tile_key] = {};
      let tile_label = $(`#tile-key-${tile_key}`).prop('innerText');
      let fields = $(document).find(`.field-settings-table-field-name[data-parent-tile-key="${tile_key}"]`);
      let field_order = [];
      $.each(fields, function(field_index, field_element) {
        field_order.push(field_element.dataset.key);
      });

      dt_custom_tiles_and_fields_ordered[tile_key]['order'] = field_order;
      dt_custom_tiles_and_fields_ordered[tile_key]['tile_priority'] = tile_priority;
      dt_custom_tiles_and_fields_ordered[tile_key]['label'] = tile_label;
      tile_priority += 10;
    });
    return dt_custom_tiles_and_fields_ordered;
  }

  function get_field_defaults(tile_key, field_key) {
    let field_options = $(`.field-name-content[data-parent-tile-key="${tile_key}"][data-field-key="${field_key}"]`);
    let field_default = [];
    $.each(field_options, function(field_index, field_element){
      let field_option_key = $(field_element).data('field-option-key');
      let field_option_label = $(field_element).prop('innerText');
      field_default[field_option_key] = {'label': field_option_label};
    });
    return field_default;
  }

  let field_settings_table = $('.field-settings-table');
  let dt_admin_modal_box = $('.dt-admin-modal-box');
  let dt_admin_modal_overlay = $('.dt-admin-modal-overlay');
  let dt_overlay_form = $('#modal-overlay-form');

  field_settings_table.on('click', '.field-settings-table-tile-name', function() {
    let tile_key = $(this).data('key');
    if (!tile_key || tile_key === 'no-tile-hidden') {
      hide_preview_tile();
      return;
    }
    show_preview_tile(tile_key);
  });

  field_settings_table.on('click', '.edit-icon', function() {
    let edit_modal = $(this).parent().data('modal');
    let data = {};
    if (edit_modal === 'edit-field') {
      data['tile_key'] = $(this).parent().data('parent-tile-key');
      data['field_key'] = $(this).parent().data('key');
    } else {
      data = $(this).parent().data('key');
    }
    showOverlayModal(edit_modal, data);
  });

  field_settings_table.on('click', '.edit-icon[data-modal="edit-field-option"]', function() {
    let edit_modal = $(this).data('modal');
    let data = {};
    if (edit_modal === 'edit-field-option') {
      data['tile_key'] = $(this).data('parent-tile-key');
      data['field_key'] = $(this).data('field-key');
      data['option_key'] = $(this).data('field-option-key');
    }
    showOverlayModal(edit_modal, data);
  });

  field_settings_table.on('click', "div[class*='expandable']", function(event) {
    if ( event.target.className !== 'edit-icon' ) {
      $(this).next().slideToggle(333, 'swing');
      if ($(this).children('.expand-icon').text() === '+'){
        $(this).children('.expand-icon').text('-');
        $(this).addClass('outset-shadow');
      } else {
        $(this).children('.expand-icon').text('+');
        $(this).removeClass('outset-shadow');
      }
    }
  });

  $('#add-new-tile-link').on('click', function(event){
    event.preventDefault();
    showOverlayModal('add-new-tile');
  });

  $('#add_new_post_type').on('click', function(event){
    event.preventDefault();
    showOverlayModal('add-new-post-type');
  });

  field_settings_table.on('click', '.add-new-field', function() {
    let tile_key = $(this).data('parent-tile-key');
    showOverlayModal('add-new-field', tile_key);
  });

  function show_preview_tile(tile_key) {
    if (tile_key === 'no_tile') {
      return;
    }
    let tile_html = `
        <div class="dt-tile-preview">
            <div class="section-header">
                <h3 class="section-header">${dt_shared.escape(all_settings['post_type_tiles'][tile_key]['label'])}</h3>
                <img src="${window.field_settings.template_dir}/dt-assets/images/chevron_up.svg" class="chevron">
            </div>
            <div class="section-body">`;

    let fields = [];
    let all_fields = all_settings.post_type_settings.fields;
    $.each(all_fields, function(field_index, field_value) {
      if( field_value['tile'] === tile_key ) {
        fields.push(field_index);
      }
    });

    let field_order = all_settings['post_type_tiles'][tile_key]['order'];
    if (field_order) {
      fields = field_order;
    }


    $.each(fields, function(field_index, field_key) {
      let field = all_settings.post_type_settings.fields[field_key];
      if( !field?.type ){
        return;
      }

      let icon_html = '';
      let icon = (field['icon'] && field['icon'] !== '') ? field['icon'] : field['font-icon'];
      if (icon && (typeof icon !== 'undefined') && (icon !== 'undefined')) {
        icon_html = '<span class="field-icon-wrapper">' + (icon.trim().toLowerCase().startsWith('mdi') ? `<i class="${dt_shared.escape(icon)} dt-icon lightgray" style="font-size: 20px;"></i>`:`<img src="${icon}" class="dt-icon lightgray">`) + '</span>';
      }

      tile_html += `
          <div class="section-subheader">
              ${icon_html}
              ${dt_shared.escape(field['name'])}
          </div>
      `;


      /*** TEXT - START ***/
      if ( [ 'text', 'communication_channel', 'location', 'location_meta' ].indexOf(field['type']) > -1 ) {
        tile_html += `
            <input type="text" class="text-input">
        `;
      }
      /*** TEXT - END ***/



      /*** TEXTAREA - START ***/
      if ( field['type'] === 'textarea' ) {
        tile_html += `
            <textarea style="width: 100%;" class="textarea dt_textarea"></textarea>
        `;
      }
      /*** TEXTAREA - END ***/



      /*** NUMBER - START ***/
      if ( field['type'] === 'number' ) {
        tile_html += `
            <input type="number" class="text-input" value="1" min="" max=""></input>
        `;
      }
      /*** NUMBER - END ***/



      /*** DATE - START ***/
      if ( field['type'] === 'date' ) {
        tile_html += `
            <div class="typeahead-container">
                <input class="typeahead-input">
                <button class="typeahead-delete-button">x</button>
            </div>
        `;
      }
      /*** DATE - END ***/



      /*** USER_SELECT - START ***/
      if ( field['type'] === 'user_select' ) {
        tile_html += `
            <div class="typeahead-container">
                <span class="typeahead-cancel-button">×</span>
                <input class="typeahead-input" placeholder="Search Users">
                <button class="typeahead-button">
                    <img src="${window.field_settings.template_dir}/dt-assets/images/search.svg">
                </button>
            </div>
        `;
      }
      /*** USER_SELECT - END ***/



      /*** CONNECTION - START ***/
      if ( ['connection', 'tags'].indexOf(field['type'] ) > -1 ) {
        tile_html += `
            <div class="typeahead-container">
                <input class="typeahead-input" placeholder="Search ${field['name']}">
                <button class="typeahead-button">
                    <img src="${window.field_settings.template_dir}/dt-assets/images/add-contact.svg">
                </button>
            </div>
        `;
      }
      /*** CONNECTION - END ***/



      /*** MULTISELECT - START ***/
      if ( field['type'] === 'multi_select' ) {
        tile_html += `<div class="button-group" style="display: inline-flex;">`;
        $.each( field['default'], function(k,f) {
          let multi_select_icon_html = '';
          let multi_icon = (f['icon'] && f['icon'] !== '') ? f['icon'] : f['font-icon'];
          if (multi_icon && (typeof multi_icon !== 'undefined') && (multi_icon !== 'undefined')) {
            multi_select_icon_html = '<span class="field-icon-wrapper">' + (multi_icon.trim().toLowerCase().startsWith('mdi') ? `<i class="${multi_icon} dt-icon lightgray" style="font-size: 20px;"></i>`:`<img src="${multi_icon}" class="dt-icon lightgray">`) + '</span>';
          }
          tile_html += `
              <button>
                  ${multi_select_icon_html}
                  ${f['label']}
              </button>
          `;
        });
        tile_html += `</div>`;
      }
      /*** MULTISELECT - START ***/



      /*** KEY_SELECT - START ***/
      if ( field['type'] === 'key_select' ) {
        let color_select = '';
        if ( field['custom_display'] ) {
          color_select = 'color-select';
        }
        tile_html += `<select class="select-field ${color_select}" style="width: 100%;">`;
        $.each( field['default'], function(k,f) {
          tile_html += `<option>${f['label']}</option>`;
        });
        tile_html += `</select>`;
      }
      /*** KEY_SELECT - END ***/
    });
    tile_html += `
        </div>
    </div>`;
    $('.fields-table-right').html(tile_html);
  }

  function hide_preview_tile() {
    $('.fields-table-right').html('');
  }

  function showOverlayModal(modalName, data=null) {
    unflip_card();
    dt_admin_modal_overlay.fadeIn(150, 'swing');
    dt_admin_modal_box.slideDown(150, 'swing');
    showOverlayModalContentBox(modalName, data);
  }

  function showOverlayModalContentBox(modalName, data=null) {
    if ( modalName === 'delete-post-type') {
      loadDeletePostTypeContentBox(data);
    }
    if ( modalName === 'add-new-post-type') {
      loadAddPostTypeContentBox();
    }
    if ( modalName === 'add-new-tile' ) {
      loadAddTileContentBox();
    }
    if ( modalName === 'edit-tile' ) {
      loadEditTileContentBox(data);
    }
    if ( modalName === 'add-new-field' ) {
      loadAddFieldContentBox(data);
    }
    if ( modalName === 'edit-field' ) {
      loadEditFieldContentBox(data);
    }
    if ( modalName === 'edit-field-option' ) {
      loadEditFieldOptionContentBox(data);
    }
    if ( modalName === 'new-field-option') {
      loadAddFieldOptionBox(data);
    }
  }

  function flip_card() {
    $('.dt-admin-modal-box-inner').addClass('flip-card');
  }

  function unflip_card() {
    $('.dt-admin-modal-box-inner').removeClass('flip-card');
  }

  function closeModal() {
    dt_admin_modal_overlay.fadeOut(150, 'swing');
    dt_admin_modal_box.slideUp(150, 'swing');
    $('#modal-overlay-content-table').html('');
  }

  function scrollTo(target_element, offset=0) {
    $([document.documentElement, document.body]).animate({
      scrollTop: target_element.offset().top + offset
    }, 500);
  }

  // Delete Post Type Modal
  function loadDeletePostTypeContentBox(data) {
    let modal_html_content = `
        <tr>
            <th colspan="2">
                <h3 class="modal-box-title">Delete Record Type</h3>
            </th>
        </tr>
        <tr>
            <td colspan="2">
                Are you sure you want to delete ${data['post_type']} and field settings?
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <input name="delete_post_type_records" id="delete_post_type_records" type="checkbox">
                <label for="delete_post_type_records"><b>Delete all records of type ${data['post_type']}?</b></label>
            </td>
        </tr>
        <tr class="last-row">
            <td colspan="2">
                <div id="delete_post_type_msg"></div><br>
                <button class="button dt-admin-modal-box-close" type="button">Cancel</button>
                <button class="button button-primary" type="submit" id="js-delete-post-type" data-post_type="${data['post_type']}">
                Delete Record Type
                <span id="js_delete_post_type_icon"></span>
                </button>
            </td>
        </tr>`;
    $('#modal-overlay-content-table').html(modal_html_content);
  }

  // Add Post Type Modal
  function loadAddPostTypeContentBox() {
    let modal_html_content = `
        <tr>
            <th colspan="2">
                <h3 class="modal-box-title">Add New Record Type</h3>
            </th>
        </tr>
        <tr>
            <td>
                <label for="new_post_type_name_single"><b>Single Name</b></label>
            </td>
            <td>
                <input name="new_post_type_name_single" id="new_post_type_name_single" type="text" required>
            </td>
        </tr>
        <tr>
            <td>
                <label for="new_post_type_key"><b>Key</b></label>
            </td>
            <td>
                <input name="new_post_type_key" id="new_post_type_key" type="text" required>
            </td>
        </tr>
        <tr>
            <td>
                <label for="new_post_type_name_plural"><b>Plural Name</b></label>
            </td>
            <td>
                <input name="new_post_type_name_plural" id="new_post_type_name_plural" type="text" required>
            </td>
        </tr>
        <tr class="last-row">
            <td colspan="2">
                <div id="new_post_type_msg"></div><br>
                <button class="button dt-admin-modal-box-close" type="button">Cancel</button>
                <button class="button button-primary" type="submit" id="js-add-post-type">
                Create Record Type
                <span id="js_add_post_type_icon"></span>
                </button>
            </td>
        </tr>`;
    $('#modal-overlay-content-table').html(modal_html_content);
  }

  // Add Tile Modal
  function loadAddTileContentBox() {
    let modal_html_content = `
        <tr>
            <th colspan="2">
                <h3 class="modal-box-title">Add New Tile</h3>
            </th>
        </tr>
        <tr>
            <td>
                <label for="new_tile_name"><b>Name</b></label>
            </td>
            <td>
                <input name="new_tile_name" id="new_tile_name" type="text" required>
            </td>
        </tr>
        <tr>
            <td>
                <label for="new_tile_description"><b>Description</b></label>
            </td>
            <td>
                <input name="new_tile_description" id="new_tile_description" type="text">
            </td>
        </tr>
        <tr class="last-row">
            <td colspan="2">
                <button class="button dt-admin-modal-box-close" type="button">Cancel</button>
                <button class="button button-primary" type="submit" id="js-add-tile">Create Tile</button>
            </td>
        </tr>`;
    $('#modal-overlay-content-table').html(modal_html_content);
  }

  // Edit Tile Modal
  function loadEditTileContentBox(tile_key) {
    let post_type = get_post_type();

    let translations_count = 0;
    if (all_settings['post_type_tiles'][tile_key]['translations']) {
      translations_count = Object.values(all_settings['post_type_tiles'][tile_key]['translations']).filter(function(t) {return t;}).length;
    }

    let description_translations_count = 0;
    if (all_settings['post_type_tiles'][tile_key]['description_translations']) {
      description_translations_count = Object.values(all_settings['post_type_tiles'][tile_key]['description_translations']).filter(function(t) {return t;}).length;
    }

    window.API.get_tile(post_type, tile_key).promise().then(function(data) {
      let tile_description = '';
      if ( data['description'] ) {
        tile_description = data['description'];
      }
      let hide_tile = '';
      if (data['hidden']) {
        hide_tile = 'checked';
      }

      let delete_tile_html_content = '';
      if (window.field_settings.default_tiles.includes(tile_key) === false) {
        delete_tile_html_content = `<a id="delete-tile-text" class="delete-text" data-tile-key="${tile_key}">Delete Tile</a>`;
      }

      let key_tag = `<span title="Tile key" class="dt-tag dt-tag-grey">${tile_key}</span>`

      let modal_html_content = `
          <tr>
              <th colspan="2">
                  <h3 class="modal-box-title">Edit '${data['label']}' Tile</h3>
              </th>
          </tr>
          <tr>
              <td>
                  <label><b>Details</b></label>
              </td>
              <td>
                  ${key_tag}
              </td>
          </tr>
          <tr>
              <td>
                  <label for="edit-tile-label"><b>Label</b></label>
              </td>
              <td>
                  <div class="input-group">
                      <input name="edit-tile-label" id="edit-tile-label-${tile_key}" type="text" value="${data['label']}" required>
                      <button class="button expand_translations" name="translate-label-button" data-translation-type="tile-label" data-post-type="${post_type}" data-tile-key="${tile_key}">
                          <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                          (${translations_count})
                      </button>
                  </div>
              </td>
          </tr>
          <tr>
              <td>
                  <label for="edit-tile-description"><b>Description</b></label>
              </td>
              <td>
                  <div class="input-group">
                      <input name="edit-tile-description" id="edit-tile-description-${tile_key}" type="text" value="${tile_description}">
                      <button class="button expand_translations" name="translate-description-button" data-translation-type="tile-description" data-post-type="${post_type}" data-tile-key="${tile_key}">
                          <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                          (${description_translations_count})
                      </button>
                  </div>
              </td>
          </tr>
          <tr>
              <td>
                  <label for="hide_tile"><b>Hide tile</b></label>
              </td>
              <td>
                  <input name="hide-tile" id="hide-tile-${tile_key}" type="checkbox" ${hide_tile}>
              </td>
          </tr>
          <tr>
              <td>
                  <label for="hide_tile"><b>Visibility</b></label>
              </td>
              <td>
                  See <a target="_blank" href="${window.field_settings.site_url}/wp-admin/admin.php?page=dt_options&tab=custom-tiles&post_type=${post_type}&tile=${tile_key}">Legacy Settings</a> for more advanced visibility options
              </td>
          </tr>
          <tr class="last-row">
              <td>
                  ${delete_tile_html_content}
              </td>
              <td>
                  <button class="button dt-admin-modal-box-close" type="button">Cancel</button>
                  <button class="button button-primary" type="submit" id="js-edit-tile" data-tile-key="${tile_key}">Save</button>
              </td>
          </tr>`;

      $('#modal-overlay-content-table').html(modal_html_content);
    });
  }

  function enableModalBackDiv(divId) {
    $('.modal-back-div').fadeOut('fast');
    $('#' + divId).fadeIn('fast');
  }

  dt_admin_modal_box.on('click', '.change-icon-button', function (e) {
    e.preventDefault();
    enableModalBackDiv('modal-back-icon-picker');
    flip_card();
  });

  $('.dt-admin-modal-icon-picker-box-close-button').on('click', function () {
    unflip_card();
  });

  // Delete Tile Text Click
  dt_overlay_form.on('click', '#delete-tile-text', function(e) {
    $(this).blur();
    if( $('#delete-confirmation-container').length > 0 ) {
      return;
    }
    let tile_key = $(this).data('tile-key');
    $(this).parent().append(`
        <div id="delete-confirmation-container" style="cursor: pointer;">
            <svg id="delete-tile-confirmation-confirm" data-tile-key="${tile_key}" stroke="#e14d43" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <svg id="delete-confirmation-cancel" stroke="#e14d43" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </div>
    `);
  });

  // Delete Field Text Click
  dt_overlay_form.on('click', '#delete-field-text', function(e) {
    $(this).blur();
    if( $('#delete-confirmation-container').length > 0 ) {
      return;
    }
    let field_key = $(this).data('field-key');
    $(this).parent().append(`
        <div id="delete-confirmation-container" style="cursor: pointer;">
            <svg id="delete-field-confirmation-confirm" data-field-key="${field_key}" stroke="#e14d43" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <svg id="delete-confirmation-cancel" stroke="#e14d43" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </div>
    `);
  });

  // Delete Field Option Text Click
  dt_overlay_form.on('click', '#delete-field-option-text', function(e) {
    $(this).blur();
    if( $('#delete-confirmation-container').length > 0 ) {
      return;
    }
    let field_key = $(this).data('field-key');
    let field_option_key = $(this).data('field-option-key');
    $(this).parent().append(`
        <div id="delete-confirmation-container" style="cursor: pointer;">
            <svg id="delete-field-option-confirmation-confirm" data-field-key="${field_key}" data-field-option-key="${field_option_key}" stroke="#e14d43" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <svg id="delete-confirmation-cancel" stroke="#e14d43" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </div>
    `);
  })

  // Delete Confirmation Confirm
  dt_overlay_form.on('click', '#delete-tile-confirmation-confirm', function(e) {
    let post_type = get_post_type();
    let tile_key = $(this).data('tile-key');
    window.API.delete_tile(post_type, tile_key).promise().then(function() {
      let tile_submenu = $(`div.field-settings-table-field-name[data-parent-tile-key="${tile_key}"]`);
      closeModal();
      if (tile_submenu.is(':visible')) {
        let tile_expand_icon = $(`.field-settings-table-tile-name[data-key="${tile_key}"]>span.expand-icon`);
        tile_expand_icon.click();
      }

      let no_tile_menu = $('.tile-rundown-elements[data-parent-tile-key="no_tile"]');
      if (no_tile_menu.is(':hidden')) {
        let no_tile_expand_icon = $('.field-settings-table-tile-name[data-key="no_tile"] > span.expand-icon');
        no_tile_expand_icon.click();
      }

      let deleted_tile_field_options =  $(`.tile-rundown-elements[data-parent-tile-key="${tile_key}"]`).children().not('.add-new-item');
      deleted_tile_field_options.each(function() {
        $(this).removeClass('inset-shadow');
        let no_tile_menu_last_submenu = $('.tile-rundown-elements[data-parent-tile-key="no_tile"] > .add-new-item');
        no_tile_menu_last_submenu.before($(this));
        $(this).attr('data-parent-tile-key', 'no_tile');
        $(this).find('.field-name-content').attr('data-parent-tile-key', 'no_tile');
        $(this).find('.field-settings-table-field-name').attr('data-parent-tile-key', 'no_tile');
        $(this).find('.field-settings-table-field-name').addClass('menu-highlight');
      });

      let tile_element = $(`.field-settings-table-tile-name[data-key="${tile_key}"]`);
      tile_element.css('background', '#e14d43');
      tile_element.fadeOut(500, function(){
        tile_element.parent().remove();
      });
    });
  });

  dt_overlay_form.on('click', '#delete-field-confirmation-confirm', function(e) {
    let post_type = get_post_type();
    let field_key = $(this).data('field-key');
    window.API.delete_field(post_type, field_key).promise().then(function() {
      closeModal();
      let field_element = $(`.sortable-field[data-key="${field_key}"]`);
      field_element.css('background', '#e14d43');
      field_element.fadeOut(500, function(){
        field_element.remove();
      });
    })
  });


  dt_overlay_form.on('click', '#delete-field-option-confirmation-confirm', function(e) {
    let post_type = get_post_type();
    let field_key = $(this).data('field-key');
    let field_option_key = $(this).data('field-option-key');
    window.API.delete_field_option(post_type, field_key, field_option_key).promise().then(function() {
      closeModal();
      let field_element = $(`.sortable-field[data-key="${field_key}"] .field-settings-table-field-option[data-field-option-key="${field_option_key}"]` );
      field_element.css('background', '#e14d43');
      field_element.fadeOut(500, function(){
        field_element.remove();
      });
    })
  });

  // Delete Confirmation Cancel
  dt_overlay_form.on('click', '#delete-tile-confirmation-cancel', function(e) {
    $(this).parent().remove();
  });

  // Add Field Modal
  function loadAddFieldContentBox(tile_key) {
    let post_type = get_post_type();
    let all_post_types = window.field_settings.all_post_types;
    let selected_post_type_label = all_post_types[post_type];
    let tile_key_label = window.field_settings.post_type_tiles[tile_key].label || tile_key;
    if (!tile_key) {
      tile_key_label = `<i>This post type doesn't have any tiles</i>`;
    }
    let modal_html_content = `
        <tr>
            <th colspan="2">
                <h3 class="modal-box-title">Add New Field</h3>
            </th>
        </tr>
        <tr>
            <td>
                <label for="new_tile_name"><b>Tile</b></label>
            </td>
            <td>
                ${tile_key_label}
            </td>
        </tr>
        <tr>
            <td>
                <label for="new-field-name"><b>New Field Name</b></label>
            </td>
            <td>
                <input name="new-field-name" id="new-field-name" type="text" value="" required>
            </td>
            <td class="spinner-box">
                <span class="loading-spinner"></span>
            </td>
        </tr>
        <tr id="field-exists-message" style="display:none;">
            <td colspan="2" class="error-message">
                Field already exists
            </td>
        </tr>
        <tr>
            <td>
                <label for="tile_label"><b>Field Type</b></label>
            </td>
            <td>
                <select id="new-field-type" name="new-field-type" required>
                    <option value="key_select">Dropdown</option>
                    <option value="multi_select">Multi Select</option>
                    <option value="tags">Tags</option>
                    <option value="text">Text</option>
                    <option value="textarea">Text Area</option>
                    <option value="number">Number</option>
                    <option value="link">Link</option>
                    <option value="date">Date</option>
                    <option value="connection">Connection</option>
                </select>
                <p id="field-type-select-description" style="margin:0.2em 0">
                    ${window.field_settings.field_types.key_select.description}
                </p>
            </td>
        </tr>
        <tr class="connection_field_target_row" style="display: none;">
            <td><label for="connection-target"><b>Connected To</label></b></td>
            <td>
                <select name="connection-target" id="connection-field-target">
                    <option></option>
                    ${Object.keys(all_post_types).map((k)=>`<option value="${k}">${all_post_types[k]}</option>`)}
                </select>
            </td>
        </tr>
        <tr class="same_post_type_row" style="display: none" >
            <td title='By default a connection is bi-directional.\n\nIt does not matter if contact A in connected to contact B, or contact B is connected to contact A.\n\nUncheck if the direction does matter, example: contact A baptised contact B.'>
                Bi-directional <img src="${window.field_settings.template_dir}/dt-assets/images/help.svg" class="help-icon" >
            </td>
            <td>
                <input type="checkbox" id="multidirectional_checkbox" name="multidirectional" checked>
            </td>
        </tr>
        <tr class="connection_field_reverse_name_row" style="display: none;">
            <td>
                Field name when shown on:
                <span class="connected_post_type"></span>
            </td>
            <td>
                <input type="text" name="other_field_name" id="other_field_name">
            </td>
        </tr>
        <tr id="connection_field_reverse_hide_row" style="display: none;">
            <td title="Hide the connection field on the other record type">
                Hide connection field on: <span class="connected_post_type"></span> <img src="${window.field_settings.template_dir}/dt-assets/images/help.svg" class="help-icon" >
            </td>
            <td>
                <input type="checkbox" id="hide_reverse_connection" name="hide_reverse_connection">
            </td>
        </tr>
        <tr id="connection_field_reverse_directional_name_row" style="display: none;">
            <td title="Two fields are created when creating a directional connection, one for each direction.\n\nGive the name for the field going in reverse direction. Example for the Baptized field: Baptized By">
                Reverse connection field name <img src="${window.field_settings.template_dir}/dt-assets/images/help.svg" class="help-icon" >
            </td>
            <td>
                <input type="text" name="reverse_connection_direction_field_name" id="reverse_connection_direction_field_name">
            </td>
        </tr>
        <tr id="connection_field_hide_reverse_directional_row" style="display: none;">
            <td title="Hide the reverse connection field to only show the field going one direction.">
                Hide reverse connection field on: <span class="connected_post_type"></span> <img src="${window.field_settings.template_dir}/dt-assets/images/help.svg" class="help-icon" >
            </td>
            <td>
                <input type="checkbox" id="hide_reverse_directional_connection" name="hide_reverse_directional_connection">
            </td>
        </tr>

        <tr>
            <td title="The content of private fields can only be seen by the user who creates it and will not be shared with other DT users.">
                <label for="new_tile_name"><b>Private Field</b> <img src="${window.field_settings.template_dir}/dt-assets/images/help.svg" class="help-icon"></label>
            </td>
            <td>
                <input name="new_field_private" id="new-field-private" type="checkbox">
            </td>
        </tr>
        <tr class="last-row">
            <td colspan="2">
                <button class="button dt-admin-modal-box-close" type="button">Cancel</button>
                <button class="button button-primary" type="submit" id="js-add-field" data-tile-key="${tile_key}">Save</button>
            </td>
        </tr>
    `;
    $('#modal-overlay-content-table').html(modal_html_content);
  }

  // Edit Field Modal
  function loadEditFieldContentBox(field_data) {
    let post_type = get_post_type();
    let tile_key = field_data['tile_key'];
    let field_key = field_data['field_key'];
    let field_settings = all_settings.post_type_settings.fields[field_key];
    let field_type = field_settings['type'];

    let name_is_custom = false;
    if ( field_settings['default_name'] ) {
      name_is_custom = true;
    }

    let translations_count = 0;
    if (all_settings.post_type_settings.fields[field_key]['translations']) {
      translations_count = Object.values(all_settings.post_type_settings.fields[field_key]['translations']).filter(function(t){return t;}).length;
    }

    let description_translations_count = 0;
    if ( all_settings.post_type_settings.fields[field_key]['description_translations'] ) {
      description_translations_count = Object.values(all_settings.post_type_settings.fields[field_key]['description_translations']).filter(function(t){return t;}).length;
    }

    let field_icon_image_html = '';
    let icon = (field_settings['icon'] && field_settings['icon'] !== '') ? field_settings['icon'] : field_settings['font-icon'];
    if ( icon && (typeof icon !== 'undefined') && (icon !== 'undefined') ) {
      if ( icon.trim().toLowerCase().startsWith('mdi') ){
        field_icon_image_html = `<i class="${icon} field-icon" style="font-size: 30px; vertical-align: middle;"></i>`;
      } else {
        field_icon_image_html = `<img src="${icon}" class="field-icon" style="vertical-align: middle;">`;
      }

    } else icon = '';

    if ( !field_settings['description'] ) {
      field_settings['description'] = '';
    }

    if ( !field_settings['icon'] ) {
      field_settings['icon'] = '';
    }

    let delete_field_html_content = '';
    if (field_settings.is_custom) {
      delete_field_html_content = `<a id="delete-field-text" class="delete-text" data-field-key="${field_key}">Delete Field</a>`;
    }

    let key_tag = `<span title="Field key" class="dt-tag dt-tag-grey">${field_key}</span>`
    let field_type_tag = `<span title="Field type" class="dt-tag dt-tag-teal">${window.field_settings.field_types[field_type]?.label || field_type.replace('_', ' ')}</span>`
    let private_tag = ``;
    if ( field_settings['private'] ) {
      private_tag = `<span title="The content of private fields can only be seen by the user who creates it and will not be shared with other DT users." class="dt-tag dt-tag-orange">Private Field</span>`
    }

    let type_visibility_html = ''
    if ( window.field_settings?.post_type_settings?.fields['type']?.default ) {
      type_visibility_html = `
          <tr>
              <td>
                  <strong>Show For</strong>
              </td>
              <td class="checkbox-group" id="type-visibility">
                  ${Object.keys(window.field_settings?.post_type_settings?.fields['type']?.default).map((option_key)=>{
                    let type_option = window.field_settings.post_type_settings.fields['type'].default[option_key]
                    let type_selected = field_settings.only_for_types === undefined || field_settings.only_for_types === true || Object.values(field_settings.only_for_types).includes(option_key)
                    return `<label><input type="checkbox" value="${option_key}" name="type-visibility" ${type_selected?'checked':''}>
                      ${type_option.label}
                    </label>`
                  }).join('')}
              </td>
          </tr>
      `
    }

    let modal_html_content = `
        <tr>
            <th colspan="2">
                <h3 class="modal-box-title">Edit '${field_settings['name']}' Field Settings</h3>
            </th>
        </tr>
        <tr>
            <td>
                <label><b>Details</label></b>
            </td>
            <td>
                ${key_tag} ${field_type_tag} ${private_tag}
            </td>
        </tr>
    `

    let name_section_html = `
        <tr>
            <td>
                <label for="edit-field-custom-name"><b>Name</b></label>
            </td>
            <td>
                <div class="input-group">
                    <input name="edit-field-custom-name" id="edit-field-custom-name" type="text" value="${field_settings['name']}">
                    <button class="button small expand_translations" name="translate-label-button" data-translation-type="field-label" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}">
                        <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                        (${translations_count})
                    </button>
                </div>
            </td>
        </tr>`;

    if (name_is_custom) {
      name_section_html = `
          <tr>
              <td>
                  <label><b>Default Name</b></label>
              </td>
              <td>
                  ${field_settings['default_name']}
              </td>
          </tr>
          <tr>
              <td>
                  <label for="edit-field-custom-name"><b>Custom Name</b></label>
              </td>
              <td>
                  <div class="input-group">
                      <input name="edit-field-custom-name" id="edit-field-custom-name" type="text" value="${field_settings['name']}">
                      <button class="button small" id="remove-custom-name" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}">Remove Custom Name</button>
                      <button class="button small expand_translations" name="translate-label-button" data-translation-type="field-label" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}">
                          <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                          (${translations_count})
                      </button>
                    </div>
              </td>
          </tr>`;
    }
    modal_html_content += name_section_html;
    modal_html_content += `
        <tr>
            <td>
                <label for="edit-field-description"><b>Description</b></label>
            </td>
            <td>
                <div class="input-group">
                    <input name="edit-field-description" id="edit-field-description" type="text" value="${field_settings['description']}">
                    <button class="button small expand_translations" name="translate-description-button" data-translation-type="field-description" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}">
                        <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                        (${description_translations_count})
                    </button>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <label for="tile-select"><b>Tile</b></label>
            </td>
            <td>
                <select name="tile-select" id="tile_select">
                    <option value="no_tile">No tile / hidden</option>
                    ${Object.keys(window.field_settings.post_type_tiles).map(k=>{
                      let tile = window.field_settings.post_type_tiles[k]
                      let selected = field_settings.tile === k ? 'selected' : ''
                      return `<option value="${k}" ${selected}>${tile['label']}</option>`  
                    })}
                    
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <label for="edit-field-icon"><b>Icon</b></label>
            </td>
            <td>
                <div class="input-group">
                    ${field_icon_image_html}
                    <input name="edit-field-icon" id="edit-field-icon" type="text" value="${icon}" style="vertical-align: middle;">
                    <button class="button change-icon-button" style="vertical-align: middle;"
                            data-icon-input="edit-field-icon">Change Icon</button>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <b>Hide Field</b>    
            </td>
            <td>
                <input type="checkbox" name="hide-field" id="hide-field" ${field_settings.hidden ? 'checked' : ''}>
            </td>
        </tr>
        ${type_visibility_html}
        <tr class="last-row">
            <td>
                ${delete_field_html_content}
            </td>
            <td>
                <button class="button dt-admin-modal-box-close" type="button">Cancel</button>
                <button class="button button-primary" type="submit" id="js-edit-field" data-tile-key="${tile_key}" data-field-key="${field_key}">Save</button>
            </td>
        </tr>`;
    $('#modal-overlay-content-table').html(modal_html_content);
  }

  // Add Field Option Modal
  function loadAddFieldOptionBox(data) {
    let tile_key = data['tile_key'];
    let field_key = data['field_key'];
    let modal_html_content = `
        <tr>
            <th colspan="2">
                <h3 class="modal-box-title">Add New Field Option</h3>
            </th>
        </tr>
        <tr>
            <td>
                <label for="new_field_option_name"><b>Label</label></b>
            </td>
            <td>
                <input type="text" name="new_field_option_name" class="new-field-option-name" data-tile-key="${tile_key}" data-field-key="${field_key}" required>
            </td>
        </tr>
        <tr>
            <td>
                <label><b>Description</b></label>
            </td>
            <td>
                <input name="new_field_option_description" class="new-field-option-description" type="text">
            </td>
        </tr>
        <tr>
            <td>
                <label for="edit-field-icon"><b>Icon</b></label>
            </td>
            <td>
              <div class="input-group">
                  <input name="edit-field-icon" id="edit-field-icon" type="text" style="vertical-align: middle;">
                  <button class="button change-icon-button" style="vertical-align: middle;"
                          data-icon-input="edit-field-icon">Change Icon</button>
              </div>
            </td>
        </tr>
        <tr class="last-row">
            <td colspan="2">
                <button class="button dt-admin-modal-box-close" type="button">Cancel</button>
                <button class="button button-primary" type="submit" id="js-add-field-option" data-tile-key="${tile_key}" data-field-key="${field_key}">Add</button>
            </td>
        </tr>`;
    $('#modal-overlay-content-table').html(modal_html_content);
  }

  // Edit Field Option Modal
  function loadEditFieldOptionContentBox(data) {
    let post_type = get_post_type();
    let tile_key = data['tile_key'];
    let field_key = data['field_key'];
    let field_option_key = data['option_key'];
    let field_settings = all_settings.post_type_settings.fields[field_key];
    let field_option = field_settings['default'][field_option_key];
    let option_description = '';

    let name_is_custom = false;
    if ( field_option['default_name'] ) {
      name_is_custom = true;
    }

    if ( 'description' in field_settings['default'][field_option_key] ) {
      option_description = field_settings['default'][field_option_key]['description'];
    }

    let translations_count = 0;
    if (field_settings['default'][field_option_key]['translations']) {
      translations_count = Object.values(field_settings['default'][field_option_key]['translations']).filter(function(t) {return t;}).length;
    }

    let description_translations_count = 0;
    if (field_settings['default'][field_option_key]['description_translations']) {
      description_translations_count = Object.values(field_settings['default'][field_option_key]['description_translations']).filter(function(t) {return t;}).length;
    }

    let field_icon_url = (field_settings['default'][field_option_key]['font-icon']) ? field_settings['default'][field_option_key]['font-icon']:field_settings['default'][field_option_key]['icon'];
    let field_icon_image_html = '';
    if( field_icon_url && (typeof field_icon_url !== 'undefined') && (field_icon_url !== 'undefined') ){
      field_icon_image_html = '<span class="field-icon-wrapper">' + (field_icon_url.trim().toLowerCase().startsWith('mdi') ? `<i class="${field_icon_url} field-icon" style="font-size: 30px; vertical-align: middle;"></i>` : `<img src="${field_icon_url}" class="field-icon" style="vertical-align: middle;">`) + '</span>';

    } else field_icon_url = '';

    let delete_field_option_html_content = '';
    if (field_settings.is_custom || field_option.is_custom) {
      delete_field_option_html_content = `<a id="delete-field-option-text" class="delete-text" data-field-key="${field_key}" data-field-option-key="${field_option_key}">Delete Field Option</a>`;
    }

    let key_tag = `<span title="Field option key" class="dt-tag dt-tag-grey">${field_option_key}</span>`

    let modal_html_content = `
        <tr>
            <th colspan="2">
                <h3 class="modal-box-title">Edit Field Option</h3>
            </th>
        </tr>
        <tr>
            <td>
                <label><b>Details</b></label>
            </td>
            <td>
                ${key_tag}
            </td>
        </tr>`;

    let name_section_html = `
        <tr>
            <td>
                <label><b>Name</b></label>
            </td>
            <td>
                <div class="input-group">
                  <input name="edit-option-label" id="new-option-name" type="text" value="${field_option['label']}" required>
                  <button class="button expand_translations" name="translate-label-button" data-translation-type="field-option-label" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${field_option_key}">
                      <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                      (${translations_count})
                  </button>
                </div>
            </td>
        </tr>`;

    if (name_is_custom) {
      name_section_html = `
          <tr>
              <td>
                  <label><b>Default Name</b></label>
              </td>
              <td>
                  ${field_option['default_name']}
              </td>
          </tr>
          <tr>
              <td>
                  <label><b>Custom Label</b></label>
              </td>
              <td>
                  <div class="input-group">
                    <input name="edit-option-label" id="new-option-name" type="text" value="${field_option['label']}" required>
                    <button class="button small" id="remove-custom-label" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${field_option_key}">Remove Custom Label</button>
                    <button class="button expand_translations" name="translate-label-button" data-translation-type="field-option-label" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${field_option_key}">
                        <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                        (${translations_count})
                    </button>
                  </div>
              </td>
          </tr>`;
    }

    modal_html_content += name_section_html;
    modal_html_content += `
        <tr>
            <td>
                <label><b>Description</b></label>
            </td>
            <td>
                <div class="input-group">
                    <input name="edit-option-description" id="new-option-description" type="text" value="${option_description}">
                    <button class="button expand_translations" name="translate-description-button" data-translation-type="field-option-description" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${field_option_key}">
                        <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                        (${description_translations_count})
                    </button>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <label for="edit-field-icon"><b>Icon</b></label>
            </td>
            <td>
                <div class="input-group">
                    ${field_icon_image_html}
                    <input name="edit-field-icon" id="edit-field-icon" type="text" value="${field_icon_url}" style="vertical-align: middle;">
                    <button class="button change-icon-button" style="vertical-align: middle;"
                            data-icon-input="edit-field-icon">Change Icon</button>
                  </div>
            </td>
        </tr>
        <tr>
            <td>
                <b>Hide Option</b>    
            </td>
            <td>
                <input type="checkbox" name="hide-field" id="hide-field-option" ${field_option.deleted ? 'checked' : ''}>
            </td>
        </tr>
        <tr class="last-row">
            <td>
                ${delete_field_option_html_content}
            </td>
            <td>
                <button class="button dt-admin-modal-box-close" type="button">Cancel</button>
                <button class="button button-primary" type="submit" id="js-edit-field-option" data-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${field_option_key}">Save</button>
            </td>
        </tr>`;
    $('#modal-overlay-content-table').html(modal_html_content);
  }


  dt_overlay_form.on('submit', function(event){
    event.preventDefault();
  });

  // Update Roles & Permissions
  $(document).on('click', '#roles_settings_update_but', function (e) {
    e.preventDefault();
    let post_type = $(e.currentTarget).data('post_type');

    // Activate spinner.
    let button_icon = $('#roles_settings_update_but_icon');
    button_icon.removeClass('mdi mdi-comment-check-outline');
    button_icon.removeClass('mdi mdi-comment-remove-outline');
    button_icon.addClass('active');
    button_icon.addClass('loading-spinner');

    let roles = {};
    $('.roles-settings-capability').each(function (idx, capability) {
      let enabled = $(capability).prop('checked');
      let role_key = $(capability).data('role_key');
      let capability_key = $(capability).data('capability_key');

      // Assign to specific role.
      if (roles[role_key] === undefined) {
        roles[role_key] = [];
      }
      roles[role_key].push({
        'key': capability_key,
        'enabled': enabled
      });
    });

    // Dispatch update endpoint api request.
    window.API.update_roles(post_type, roles).promise().then(function (data) {
      button_icon.removeClass('active');
      button_icon.removeClass('loading-spinner');
      button_icon.css('color', '#ffffff');

      if (data && data['updated']) {
        button_icon.addClass('mdi mdi-comment-check-outline');

      } else {
        button_icon.addClass('mdi mdi-comment-remove-outline');
      }
    });
  });

  // Process Add Post Type - Single Name Events
  dt_overlay_form.on('keyup', '#new_post_type_name_single', function (e) {

    // Generate corresponding key.
    let single_name = $('#new_post_type_name_single').val().trim();
    let key = single_name.toLowerCase().replaceAll(/[!-\/:-@[-`{-~\s*]/ig, '_');
    $('#new_post_type_key').val(key);

    // Generate corresponding plural name.
    $('#new_post_type_name_plural').val(single_name + 's');
  });

  // Delete Post Type - Show Modal
  $(document).on('click', '#post_type_settings_delete_but', function (e) {
    e.preventDefault();

    showOverlayModal('delete-post-type', {
      'post_type': $('#post_type_settings_key').html()
    });
  });

  // Delete Post Type
  $(document).on('click', '#js-delete-post-type', function (e) {
    e.preventDefault();

    let post_type = $(e.currentTarget).data('post_type');
    let delete_all_records = $('#delete_post_type_records').prop('checked');
    let delete_post_type_msg = $('#delete_post_type_msg');
    $(delete_post_type_msg).html('');

    // Activate spinner.
    let button_icon = $('#js_delete_post_type_icon');
    button_icon.addClass('active');
    button_icon.addClass('loading-spinner');

    window.API.delete_post_type(post_type, delete_all_records).promise().then(function (data) {
      button_icon.removeClass('active');
      button_icon.removeClass('loading-spinner');

      if(data && data['deleted']) {
        window.location.href = window.dt_admin_scripts.site_url + '/wp-admin/admin.php?page=dt_customizations&post_type=contacts&tab=tiles';
      } else {
        $(delete_post_type_msg).html(dt_shared.escape(data['msg']));
      }
    });
  });

  // Update Post Type
  $(document).on('click', '#post_type_settings_update_but', function (e) {
    e.preventDefault();

    let post_type_settings_singular = $('#post_type_settings_singular');
    let post_type_settings_plural = $('#post_type_settings_plural');

    let post_type = $('#post_type_settings_key').html();
    let singular = $(post_type_settings_singular).val();
    let plural = $(post_type_settings_plural).val();
    let displayed = $('#post_type_settings_frontend_displayed').prop('checked');

    // Reset any previous failed validation highlights.
    $(post_type_settings_singular).css('border', '');
    $(post_type_settings_plural).css('border', '');

    // Activate spinner.
    let button_icon = $('#post_type_settings_update_but_icon');
    button_icon.removeClass('mdi mdi-comment-check-outline');
    button_icon.removeClass('mdi mdi-comment-remove-outline');
    button_icon.addClass('active');
    button_icon.addClass('loading-spinner');

    // Ensure we have valid entries.
    if (singular.trim() === '') {
      alert('Please ensure to enter a valid Singular value.');
      $(post_type_settings_singular).css('border', '2px solid #e14d43');
      button_icon.removeClass('active');
      button_icon.removeClass('loading-spinner');

    } else if (plural.trim() === '') {
      alert('Please ensure to enter a valid Plural value.');
      $(post_type_settings_plural).css('border', '2px solid #e14d43');
      button_icon.removeClass('active');
      button_icon.removeClass('loading-spinner');

    } else {

      window.API.update_post_type(post_type, singular, plural, displayed).promise().then(function (data) {
        button_icon.removeClass('active');
        button_icon.removeClass('loading-spinner');
        button_icon.css('color', '#ffffff');

        if (data && data['updated']) {
          button_icon.addClass('mdi mdi-comment-check-outline');

        } else {
          button_icon.addClass('mdi mdi-comment-remove-outline');
        }
      });
    }
  });

  // Process Add Post Type
  dt_overlay_form.on('click', '#js-add-post-type', function (e) {
    let new_post_type_key = $('#new_post_type_key');
    let new_post_type_name_single = $('#new_post_type_name_single');
    let new_post_type_name_plural = $('#new_post_type_name_plural');
    let new_post_type_msg = $('#new_post_type_msg');

    // Reset any previous failed validation highlights.
    $(new_post_type_name_single).css('border', '');
    $(new_post_type_key).css('border', '');
    $(new_post_type_name_plural).css('border', '');
    $(new_post_type_msg).html('');

    // Activate spinner.
    let button_icon = $('#js_add_post_type_icon');
    button_icon.css('margin', '2px 0px 4px  10px');
    button_icon.addClass('active');
    button_icon.addClass('loading-spinner');

    // Validate initial field entries.
    let key = $(new_post_type_key).val().trim();
    let single_name = $(new_post_type_name_single).val().trim();
    let plural_name = $(new_post_type_name_plural).val().trim();

    if(single_name === '') {
      $(new_post_type_name_single).css('border', '2px solid #e14d43');
      button_icon.css('margin', '');
      button_icon.removeClass('active');
      button_icon.removeClass('loading-spinner');
      return false;

    } else if (key === '') {
      $(new_post_type_key).css('border', '2px solid #e14d43');
      button_icon.css('margin', '');
      button_icon.removeClass('active');
      button_icon.removeClass('loading-spinner');
      return false;

    } else if(plural_name === '') {
      $(new_post_type_name_plural).css('border', '2px solid #e14d43');
      button_icon.css('margin', '');
      button_icon.removeClass('active');
      button_icon.removeClass('loading-spinner');
      return false;

    } else {

      // Submit post type creation request.
      window.API.create_new_post_type(key, single_name, plural_name).promise().then(function (data) {

        button_icon.css('margin', '');
        button_icon.removeClass('active');
        button_icon.removeClass('loading-spinner');

        if (data && data['success'] && data['post_type'] && data['post_type_label']) {

          // Unselect all/any primary buttons.
          let latest_post_type_buttons = $('.latest-post-type-buttons');
          $(latest_post_type_buttons).find('.button').removeClass('button-primary');

          // Generate new post type button.
          let pill_link = window.dt_admin_scripts.site_url + '/wp-admin/admin.php?page=dt_customizations&post_type=' + data['post_type'] + '&tab=tiles';
          let pill_link_html = `<a href="${pill_link}" class="button button-primary">${data['post_type_label']}</a>`;
          $(latest_post_type_buttons).append(pill_link_html);

          // Refresh page with new post type selected
          window.location.href = pill_link;
        } else {
          $(new_post_type_msg).html(dt_shared.escape(data['msg']));
        }
      });
    }
  });

  // Process Add Tile
  dt_overlay_form.on('click', '#js-add-tile', function(e) {
    let post_type = get_post_type();
    let new_tile_name = $('#new_tile_name').val().trim();
    if (new_tile_name === '') {
      $('#new_tile_name').css('border', '2px solid #e14d43');
      return false;
    }
    let new_tile_description = $('#new_tile_description').val().trim();

    window.API.create_new_tile(post_type, new_tile_name, new_tile_description).promise().then(function(data) {
      let tile_key = data['key'];
      let tile_label = data['label'];
      window.field_settings.post_type_tiles[tile_key] = {'label':tile_label};
      closeModal();
      $('#no_tile').before(`
          <div class="sortable-tile" id="${tile_key}">
              <div class="field-settings-table-tile-name expandable menu-highlight" data-modal="edit-tile" data-key="${tile_key}">
                  <span class="sortable ui-icon ui-icon-arrow-4"></span>
                  <span class="expand-icon">+</span>
                  <span id="tile-key-${tile_key}" style="vertical-align: sub;">
                      ${tile_label}
                      <svg style="width:24px;height:24px;margin-left:6px;vertical-align:middle;" viewBox="0 0 24 24">
                          <path fill="green" d="M20,4C21.11,4 22,4.89 22,6V18C22,19.11 21.11,20 20,20H4C2.89,20 2,19.11 2,18V6C2,4.89 2.89,4 4,4H20M8.5,15V9H7.25V12.5L4.75,9H3.5V15H4.75V11.5L7.3,15H8.5M13.5,10.26V9H9.5V15H13.5V13.75H11V12.64H13.5V11.38H11V10.26H13.5M20.5,14V9H19.25V13.5H18.13V10H16.88V13.5H15.75V9H14.5V14A1,1 0 0,0 15.5,15H19.5A1,1 0 0,0 20.5,14Z" />
                      </svg>
                  </span>
                  <span class="edit-icon"></span>
              </div>
              <div class="tile-rundown-elements" data-parent-tile-key="${tile_key}">
                  <div class="field-settings-table-field-name expandable add-new-item">
                      <span class="add-new-field" data-parent-tile-key="${tile_key}">
                          <a>add new field</a>
                      </span>
                  </div>
              </div>
          </div>
      `);
      $('.field-settings-table, .tile-rundown-elements, .field-settings-table-child-toggle').sortable(sortable_options);
      show_preview_tile(tile_key);
    });
  });

  // Process Edit Tile
  dt_overlay_form.on('click', '#js-edit-tile', function(e) {
    let post_type = get_post_type();
    let tile_key = $(this).data('tile-key');
    let tile_label = $(`#edit-tile-label-${tile_key}`).val().trim();
    let tile_description = $(`#edit-tile-description-${tile_key}`).val().trim();
    let hide_tile = $(`#hide-tile-${tile_key}`).is(':checked');

    if (tile_label === '') {
      $(`#edit-tile-label-${tile_key}`).css('border', '2px solid #e14d43');
      return false;
    }

    window.API.edit_tile(post_type, tile_key, tile_label, tile_description, hide_tile).promise().then(function(response) {
      $(`#tile-key-${tile_key}`).parent().removeClass('menu-highlight');
      all_settings['post_type_tiles'][tile_key] = response;
      $(`#tile-key-${tile_key}`).html(tile_label);
      show_preview_tile(tile_key);
      closeModal();
      $(`#tile-key-${tile_key}`).parent().addClass('menu-highlight');
    });
  });

  // Process Add Field
  dt_overlay_form.on('click', '#js-add-field', function(e) {
    let post_type = get_post_type();
    let tile_key = $(this).data('tile-key');
    let new_field_name = $(`#new-field-name`).val().trim();
    let new_field_type = $(`#new-field-type`).val().trim();
    let new_field_private = $(`#new-field-private`).is(':checked');

    //connection field options
    let connection_field_options = {
      connection_target: $('#connection-field-target').val().trim(),
      other_field_name: $('#other_field_name').val().trim(),
      disable_other_post_type_field: $('#hide_reverse_connection').is(':checked'),
      multidirectional: $('#multidirectional_checkbox').is(':checked'),
      reverse_connection_name: $('#reverse_connection_direction_field_name').val().trim(),
      disable_reverse_connection: $('#hide-reverse-connection').is(':checked'),
    }

    if (new_field_name === '') {
      $('#new-field-name').css('border', '2px solid #e14d43');
      return false;
    }

    window.API.new_field(post_type, tile_key, new_field_name, new_field_type, new_field_private, connection_field_options ).promise().then(function(response) {
      let field_key = response['key'];
      all_settings.post_type_settings.fields[field_key] = response;
      let new_field_nonexpandable_html = `
          <div class="sortable-field" id="${field_key}" data-parent-tile-key="${tile_key}">
              <div class="field-settings-table-field-name submenu-highlight" id="${field_key}" data-parent-tile-key="${tile_key}" data-key="${field_key}" data-modal="edit-field">
                 <span class="sortable ui-icon ui-icon-arrow-4"></span>
                  <span class="field-name-content" data-parent-tile="${tile_key}" data-key="${field_key}">
                      ${new_field_name}
                      <svg style="width:24px;height:24px;margin-left:6px;vertical-align:middle;" viewBox="0 0 24 24">
                          <path fill="green" d="M20,4C21.11,4 22,4.89 22,6V18C22,19.11 21.11,20 20,20H4C2.89,20 2,19.11 2,18V6C2,4.89 2.89,4 4,4H20M8.5,15V9H7.25V12.5L4.75,9H3.5V15H4.75V11.5L7.3,15H8.5M13.5,10.26V9H9.5V15H13.5V13.75H11V12.64H13.5V11.38H11V10.26H13.5M20.5,14V9H19.25V13.5H18.13V10H16.88V13.5H15.75V9H14.5V14A1,1 0 0,0 15.5,15H19.5A1,1 0 0,0 20.5,14Z" />
                      </svg>
                  </span>
                  <span class="edit-icon"></span>
              </div>
          </div>
      `;

      let new_field_expandable_html = `
            <div class="sortable-field" data-parent-tile-key="${tile_key}" data-key="${field_key}">
                <div class="field-settings-table-field-name expandable submenu-highlight" data-parent-tile-key="${tile_key}" data-key="${field_key}" data-modal="edit-field">
                   <span class="sortable ui-icon ui-icon-arrow-4"></span>
                    <span class="expand-icon">+</span>
                    <span class="field-name-content" data-parent-tile="${tile_key}" data-key="${field_key}">
                        ${new_field_name}
                        <svg style="width:24px;height:24px;margin-left:6px;vertical-align:middle;" viewBox="0 0 24 24">
                            <path fill="green" d="M20,4C21.11,4 22,4.89 22,6V18C22,19.11 21.11,20 20,20H4C2.89,20 2,19.11 2,18V6C2,4.89 2.89,4 4,4H20M8.5,15V9H7.25V12.5L4.75,9H3.5V15H4.75V11.5L7.3,15H8.5M13.5,10.26V9H9.5V15H13.5V13.75H11V12.64H13.5V11.38H11V10.26H13.5M20.5,14V9H19.25V13.5H18.13V10H16.88V13.5H15.75V9H14.5V14A1,1 0 0,0 15.5,15H19.5A1,1 0 0,0 20.5,14Z" />
                        </svg>
                    </span>
                    <span class="edit-icon"></span>
                </div>
                <!-- START TOGGLED ITEMS -->
                <div class="field-settings-table-child-toggle">
                    <div class="field-settings-table-field-option">
                       <span class="sortable ui-icon ui-icon-arrow-4"></span>
                        <span class="field-name-content">default blank</span>
                    </div>
                    <div class="field-settings-table-field-option new-field-option" data-parent-tile-key="${tile_key}" data-field-key="${field_key}">
                        <span class="sortable ui-icon ui-icon-arrow-4"></span>
                        <span class="field-name-content">new field option</span>
                    </div>
                </div>
                <!-- END TOGGLED ITEMS -->
            </div>
            `;
      let new_field_html = new_field_nonexpandable_html;
      if(['key_select', 'multi_select'].indexOf(new_field_type) > -1) {
        new_field_html = new_field_expandable_html;
      }
      if (tile_key){
        $(`.add-new-field[data-parent-tile-key='${tile_key}']`).parent().before(new_field_html);
        show_preview_tile(tile_key);
      } else {
        $('.add-new-field').parent().before(new_field_html);
      }
      closeModal();
      $(document).find('.field-settings-table, .tile-rundown-elements, .field-settings-table-child-toggle').sortable(sortable_options)
    });
  });

  // Process Edit Field
  dt_overlay_form.on('click', '#js-edit-field', function() {
    let post_type = get_post_type();
    let tile_key = $(this).data('tile-key');
    let field_key = $(this).data('field-key');
    let custom_name = $('#edit-field-custom-name').val().trim();
    let tile_select = $('#tile_select').val().trim();
    let field_description = $('#edit-field-description').val().trim();
    let field_icon = $('#edit-field-icon').val().trim();
    let visibility = {
      hidden: $('#hide-field').is(':checked'),
      type_visibility: $('#type-visibility input:checked').map((index, obj)=>{
        return $(obj).val()
      }).get()
    }

    if (custom_name === '') {
      $('#edit-field-custom-name').css('border', '2px solid #e14d43');
      return false;
    }

    window.API.edit_field(post_type, tile_key, field_key, custom_name, tile_select, field_description, field_icon, visibility).promise().then(function(result){
      $.extend(window.field_settings.post_type_settings.fields[field_key], result);

      let edited_field_menu_element = $(`.sortable-field[data-key=${field_key}]`)

      let edited_field_submenu_element = edited_field_menu_element.find('.field-settings-table-child-toggle')
      let edited_field_menu_name_element = edited_field_menu_element.find('.field-settings-table-field-name');
      let hidden_icon = edited_field_menu_name_element.find('.hidden-icon')

      edited_field_menu_name_element.removeClass('submenu-highlight').removeClass('menu-highlight');
      edited_field_submenu_element.children('.field-settings-table-field-option').removeClass('submenu-highlight');

      if ( custom_name !== '' ) {
        $(`.sortable-field[data-key=${field_key}] .field-settings-table-field-name .field-name-content`).html(custom_name);
      }

      // Check if rundown element and sub element need to be moved to another tile
      if ( tile_key !== tile_select ) {
        let target_tile_menu = $(`.field-settings-table-tile-name[data-key="${tile_select}"]`);
        let target_tile_submenu = $(`.tile-rundown-elements[data-parent-tile-key="${tile_select}"]`);

        if ( target_tile_submenu.is(':visible') === false ) {
          target_tile_menu.trigger('click');
        }

        target_tile_submenu.prepend(edited_field_menu_element);

        scrollTo(target_tile_menu, -32);

        edited_field_menu_element.data('parent-tile-key', tile_select);
        edited_field_menu_name_element.data('parent-tile-key', tile_select);
        edited_field_submenu_element.data('parent-tile-key', tile_select);
      }

      // hide or show field hidden icon
      hidden_icon.toggle(result.hidden)

      show_preview_tile(tile_key);
      closeModal();
      edited_field_menu_name_element.addClass('menu-highlight');
      edited_field_submenu_element.children('.field-settings-table-field-option').addClass('submenu-highlight');
      $(document).find('.field-settings-table, .tile-rundown-elements, .field-settings-table-child-toggle').sortable(sortable_options)
    });
    return;
  });

  // Process Add Field Option
  dt_overlay_form.on('click', '#js-add-field-option', function(e) {
    let post_type = get_post_type();
    let tile_key = $(this).data('tile-key');
    let field_key = $(this).data('field-key');
    let field_option_name = $('.new-field-option-name').val().trim();
    let field_option_description = $('.new-field-option-description').val().trim();
    let field_option_icon = $('#edit-field-icon').val().trim();

    if (field_option_name === '') {
      $('.new-field-option-name').css('border', '2px solid #e14d43');
      return false;
    }

    window.API.new_field_option(post_type, tile_key, field_key, field_option_name, field_option_description, field_option_icon).promise().then(function(new_field_option_key) {
      all_settings.post_type_settings.fields[field_key]['default'][new_field_option_key] = {
        label:field_option_name,
        description:field_option_description,
        icon:field_option_icon,
      };

      let new_field_option_html = `
          <div class="field-settings-table-field-option">
             <span class="sortable ui-icon ui-icon-arrow-4"></span>
              <span class="field-name-content" data-parent-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${new_field_option_key}" >
                  ${field_option_name}
                  <svg style="width:24px;height:24px;margin-left:6px;vertical-align:middle;" viewBox="0 0 24 24">
                      <path fill="green" d="M20,4C21.11,4 22,4.89 22,6V18C22,19.11 21.11,20 20,20H4C2.89,20 2,19.11 2,18V6C2,4.89 2.89,4 4,4H20M8.5,15V9H7.25V12.5L4.75,9H3.5V15H4.75V11.5L7.3,15H8.5M13.5,10.26V9H9.5V15H13.5V13.75H11V12.64H13.5V11.38H11V10.26H13.5M20.5,14V9H19.25V13.5H18.13V10H16.88V13.5H15.75V9H14.5V14A1,1 0 0,0 15.5,15H19.5A1,1 0 0,0 20.5,14Z" />
                  </svg>
              </span>
              <span class="edit-icon" data-modal="edit-field-option" data-parent-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${new_field_option_key}"></span>
          </div>`;
      $(`.new-field-option[data-parent-tile-key="${tile_key}"][data-field-key="${field_key}"]`).before(new_field_option_html);
      show_preview_tile(tile_key);
      closeModal();
    });
  });

  // Process Edit Field Option
  dt_overlay_form.on('click', '#js-edit-field-option', function(e) {
    let post_type = get_post_type();
    let tile_key = $(this).data('tile-key');
    let field_key = $(this).data('field-key');
    let field_option_key = $(this).data('field-option-key');
    let new_field_option_label = $('#new-option-name').val().trim();
    let new_field_option_description = $('#new-option-description').val().trim();
    let field_option_icon = $('#edit-field-icon').val().trim();
    let visibility = {
      hidden: $('#hide-field-option').is(':checked'),
    }

    if (new_field_option_label === '') {
      $('#new-option-name').css('border', '2px solid #e14d43');
      return false;
    }

    window.API.edit_field_option(post_type, tile_key, field_key, field_option_key, new_field_option_label, new_field_option_description, field_option_icon, visibility).promise().then(function(result) {
      all_settings.post_type_settings.fields[field_key]['default'][field_option_key] = result;
      let edited_field_option_element = $(`.field-name-content[data-parent-tile-key="${tile_key}"][data-field-key="${field_key}"][data-field-option-key="${field_option_key}"]`);
      edited_field_option_element.parent().removeClass('submenu-highlight');
      edited_field_option_element[0].innerText = new_field_option_label;

      //show or hide the hidden icon
      $(`.field-settings-table-field-option[data-field-key=${field_key}][data-field-option-key="${field_option_key}"] .hidden-icon`).toggle(result.deleted)
      show_preview_tile(tile_key);
      closeModal();
      edited_field_option_element.parent().addClass('submenu-highlight');
    });
  });

  // Process Remove Custom Field Name
  dt_admin_modal_box.on('click', '#remove-custom-name', function() {
    let post_type = $(this).data('post-type');
    let tile_key = $(this).data('tile-key');
    let field_key = $(this).data('field-key');

    window.API.remove_custom_field_name(post_type, field_key).promise().then(function(default_name) {
      all_settings.post_type_settings.fields[field_key]['name'] = default_name;
      delete all_settings.post_type_settings.fields[field_key]['default_name'];
      let edited_field_content_element = $(`.field-name-content[data-parent-tile-key="${tile_key}"][data-key="${field_key}"]`);
      let edited_field_parent_element = $(`.field-name-content[data-parent-tile-key="${tile_key}"][data-key="${field_key}"]`).parent();
      edited_field_parent_element.removeClass('menu-highlight');
      closeModal();
      edited_field_content_element.html(default_name);
      edited_field_parent_element.addClass('menu-highlight');
    });
  });

  // Process Remove Custom Field Option Label
  dt_admin_modal_box.on('click', '#remove-custom-label', function() {
    let post_type = $(this).data('post-type');
    let tile_key = $(this).data('tile-key');
    let field_key = $(this).data('field-key');
    let field_option_key = $(this).data('field-option-key');

    window.API.remove_custom_field_option_label(post_type, field_key, field_option_key).promise().then(function(default_label) {
      all_settings.post_type_settings.fields[field_key]['default'][field_option_key]['label'] = default_label;
      delete all_settings.post_type_settings.fields[field_key]['default_name'];

      let edited_field_option_content_element = $(`.field-name-content[data-parent-tile-key="${tile_key}"][data-field-key="${field_key}"][data-field-option-key="${field_option_key}"]`);
      let edited_field_option_parent_element = $(`.field-name-content[data-parent-tile-key="${tile_key}"][data-field-key="${field_key}"][data-field-option-key="${field_option_key}"]`).parent();

      edited_field_option_parent_element.removeClass('submenu-highlight');
      closeModal();
      edited_field_option_content_element.html(default_label);
      edited_field_option_parent_element.addClass('submenu-highlight');
    });
  });

  // Translation for Tiles
  dt_admin_modal_box.on('click', '.expand_translations', function() {
    let translation_type = $(this).data('translation-type');
    let post_type = $(this).data('post-type');
    let tile_key = $(this).data('tile-key');
    let field_key = $(this).data('field-key');
    let field_option_key = $(this).data('field-option-key');
    let languages = all_settings['languages'];
    let available_translations = {};

    let element_type = '';
    let element_key = '';
    if ( translation_type === 'tile-label' ) {
      element_type = 'Tile Label';
      element_key = tile_key;
    }

    if ( translation_type === 'tile-description' ) {
      element_type = 'Tile Description';
      element_key = tile_key;
    }

    if ( translation_type === 'field-label' ) {
      element_type = 'Field Label';
      element_key = field_key;
    }

    if ( translation_type === 'field-description' ) {
      element_type = 'Field Description';
      element_key = field_key;
    }

    if ( translation_type === 'field-option-label' ) {
      element_type = 'Field Option Label';
      element_key = field_option_key;
    }

    if ( translation_type === 'field-option-description' ) {
      element_type = 'Field Option Description';
      element_key = field_option_key;
    }

    let translations_html = `
        <table class="modal-translations-overlay-content-table" id="modal-translations-overlay-content-table">
            <tr>
                <th colspan="2">Translate "${element_key}" ${element_type}</th>
            </tr>`;

    if ( translation_type === 'tile-label' ) {
      if ( all_settings['post_type_tiles'][tile_key]['translations'] ) {
        available_translations = all_settings['post_type_tiles'][tile_key]['translations'];
      }
    }

    if ( translation_type === 'tile-description' ) {
      if ( all_settings['post_type_tiles'][tile_key]['description_translations'] ) {
        available_translations = all_settings['post_type_tiles'][tile_key]['description_translations'];
      }
    }

    if ( translation_type === 'field-label' ) {
      if ( field_key === 'undefined' ) {
        return;
      }
      if ( all_settings.post_type_settings.fields[field_key]['translations'] ) {
        available_translations = all_settings.post_type_settings.fields[field_key]['translations'];
      }
    }

    if ( translation_type === 'field-description' ) {
      if ( field_key === 'undefined' ) {
        return;
      }
      if ( all_settings.post_type_settings.fields[field_key]['description_translations'] ) {
        available_translations = all_settings.post_type_settings.fields[field_key]['description_translations'];
      }
    }

    if ( translation_type === 'field-option-label' ) {
      if( field_key === 'undefined' || field_option_key === 'undefined' ) {
        return;
      }
      if ( all_settings.post_type_settings.fields[field_key]['default'][field_option_key]['translations'] ) {
        available_translations = all_settings.post_type_settings.fields[field_key]['default'][field_option_key]['translations'];
      }
    }

    if ( translation_type === 'field-option-description' ) {
      if ( field_key === 'undefined' ) {
        return;
      }
      if ( all_settings.post_type_settings.fields[field_key]['default'][field_option_key]['description_translations'] ) {
        available_translations = all_settings.post_type_settings.fields[field_key]['default'][field_option_key]['description_translations'];
      }
    }

    let current_translation = '';
    $.each( languages, function(key, lang) {
      available_translations[key] ? current_translation = available_translations[key] : current_translation = '';
      translations_html += `
          <tr>
              <td><label for="tile_label_translation-${key}">${lang['flag']} ${lang['native_name']}</label></td>
              <td><input name="tile_label_translation-${key}" type="text" data-translation-key="${key}" value="${current_translation}"/></td>
          </tr>`;
    });

    translations_html += `
        </table>
        <div class="translations-save-row">
            <button class="button cancel-translations-button">Cancel</button>
            <button class="button button-primary save-translations-button" data-translation-type="${translation_type}" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${field_option_key}">Save</button>
        </div>`;

    enableModalBackDiv('modal-back-translations');
    $('#modal-translations-overlay-form').html(translations_html);
    flip_card();

  });

  $('.dt-admin-modal-translations-box-close-button').on('click', function() {
    unflip_card();
  });

  $('#modal-translations-overlay-form').on('click', '.cancel-translations-button', function() {
    unflip_card();
  });

  $('#modal-translations-overlay-form').on('click', '.save-translations-button', function() {
    let translation_type = $(this).data('translation-type');
    let post_type = $(this).data('post-type');
    let tile_key = $(this).data('tile-key');
    let field_key = $(this).data('field-key');
    let field_option_key = $(this).data('field-option-key');

    let translations = {};
    let translation_inputs = $('#modal-translations-overlay-form input');
    $.each(translation_inputs, function(key, t) {
      let translation_value = $(t).val();
      let translation_key = $(t).data('translation-key');
      if (translation_value) {
        translations[translation_key] = translation_value;
      }
    });

    translations = JSON.stringify(translations);
    let element_button_selector = $('.expand_translations[name="translate-label-button"]');
    window.API.edit_translations(translation_type, post_type, tile_key, translations, field_key, field_option_key).promise().then(function(response) {
      let translations_count = 0;
      if ( translation_type === 'tile-label' ) {
        all_settings['post_type_tiles'][tile_key]['translations'] = response;
        translations_count = Object.values(all_settings['post_type_tiles'][tile_key]['translations']).filter(function(t) {return t;}).length;
        element_button_selector = $('.expand_translations[name="translate-label-button"]');
      }

      if ( translation_type === 'tile-description' ) {
        all_settings['post_type_tiles'][tile_key]['description_translations'] = response;
        translations_count = Object.values(all_settings['post_type_tiles'][tile_key]['description_translations']).filter(function(t) {return t;}).length;
        element_button_selector = $('.expand_translations[name="translate-description-button"]');
      }

      if ( translation_type === 'field-label' ) {
        all_settings.post_type_settings.fields[field_key]['translations'] = response;
        translations_count = Object.values(all_settings.post_type_settings.fields[field_key]['translations']).filter(function(t) {return t;}).length;
        element_button_selector = $('.expand_translations[name="translate-label-button"]');
      }

      if ( translation_type === 'field-description' ) {
        all_settings.post_type_settings.fields[field_key]['description_translations'] = response;
        translations_count = Object.values(all_settings.post_type_settings.fields[field_key]['description_translations']).filter(function(t) {return t;}).length;
        element_button_selector = $('.expand_translations[name="translate-description-button"]');
      }

      if ( translation_type === 'field-option-label' ) {
        all_settings.post_type_settings.fields[field_key]['default'][field_option_key]['translations'] = response;
        translations_count = Object.values(all_settings.post_type_settings.fields[field_key]['default'][field_option_key]['translations']).filter(function(t) {return t;}).length;
        element_button_selector = $('.expand_translations[name="translate-label-button"]');
      }

      if ( translation_type === 'field-option-description' ) {
        all_settings.post_type_settings.fields[field_key]['default'][field_option_key]['description_translations'] = response;
        translations_count = Object.values(all_settings.post_type_settings.fields[field_key]['default'][field_option_key]['description_translations']).filter(function(t) {return t;}).length;
        element_button_selector = $('.expand_translations[name="translate-description-button"]');
      }

      $(element_button_selector).html(`
          <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                      (${translations_count})
          `);
      unflip_card();
    });
  });

  $('.dt-admin-modal-box-close-button').on('click', function() {
    closeModal();
  });
  $(document).on( 'click', '.dt-admin-modal-box-close', function() {
    closeModal();
  });

  dt_admin_modal_overlay.on('click', function(e) {
    if (e.target === this) {
      closeModal();
    }
  });

  $('.field-name').on('click', function() {
    $(this).find('.field-name-icon-arrow:not(.disabled)').toggleClass('arrow-expanded');
    $(this).find('.field-elements-list').slideToggle(333, 'swing');
  });

  field_settings_table.on('click', '.new-field-option', function() {
    let data = [];
    data['tile_key'] = $(this).data('parent-tile-key');
    data['field_key'] = $(this).data('field-key');
    showOverlayModal('new-field-option', data);
  });

  // Display 'connected to' dropdown if 'connection' post type field is selected
  dt_admin_modal_box.on('change', '[id^=new-field-type]', function() {
    let selected_type = $(this).val();
    if ( window.field_settings.field_types[selected_type]?.description ){
      $('#field-type-select-description').html(window.field_settings.field_types[selected_type].description);
    }

    if ( $(this).val() === 'connection' ) {
      $('.connection_field_target_row').show();
    } else {
      $('.connection_field_target_row').hide();
      $('.same_post_type_row').hide();
      $('.connection_field_reverse_name_row').hide();
      $('#connection-field-target option').prop('selected', false);
    }
  });

  dt_admin_modal_box.on('change', '#connection-field-target', function() {
    let selected_field_target = $(this).find(':selected').val();
    let same_post_type = selected_field_target === window.post_type;
    $('.same_post_type_row').toggle( same_post_type );
    $('.connection_field_reverse_name_row').toggle( !same_post_type );
    $('#connection_field_reverse_hide_row').toggle( !same_post_type )

    let bidirectional_checked = $('#multidirectional_checkbox').prop('checked');
    $('#connection_field_reverse_directional_name_row').toggle( same_post_type && !bidirectional_checked );
    $('#connection_field_hide_reverse_directional_row').toggle( same_post_type && !bidirectional_checked );

    $('.connected_post_type').text(window.field_settings.all_post_types[selected_field_target]);
  });

  dt_admin_modal_box.on('change', '#multidirectional_checkbox', function() {
    let checked = $(this).prop('checked');
    $('#connection_field_hide_reverse_directional_row').toggle( !checked )
    $('#connection_field_reverse_directional_name_row').toggle( !checked )
  })

  dt_admin_modal_box.on('input', '#new-field-name', function() {
    $('.loading-spinner').addClass('active');
    let field_name = $(this).val();
    if ( field_name.length === 0 ) {
      $('#field-exists-message').hide();
      $('#js-add-field').prop('disabled', false);
      $('.loading-spinner').removeClass('active');
      return;
    }
    let field_key = field_name.toLowerCase().replace(' ', '_');

    if (window.field_settings.post_type_settings.fields[field_key]) {
      $('#field-exists-message').show();
      $('#js-add-field').prop('disabled', true);
    } else {
      $('#field-exists-message').hide();
      $('#js-add-field').prop('disabled', false);
    }
    $('.loading-spinner').removeClass('active');
  });

  $.typeahead({
    input: '.js-typeahead-settings',
    order: "desc",
    cancelButton: false,
    dynamic: false,
    emptyTemplate: '<em style="padding-left:12px;">No results for "{{query}}"</em>',
    template: '<a href="' + window.location.origin + window.location.pathname + '?page=dt_customizations&post_type={{post_type}}&tab=tiles&tile={{post_tile}}#{{post_setting}}">{{label}}</a>',
    correlativeTemplate: true,
    source: {
      ajax: {
        type: "POST",
        url: window.wpApiSettings.root + 'dt-admin-settings/get-post-fields',
        beforeSend: function(xhr) {
          xhr.setRequestHeader('X-WP-Nonce', window.wpApiSettings.nonce);
        },
      }
    },
    callback: {
      onResult: function() {
        $(`.typeahead__result`).show();
      },
      onHideLayout: function () {
        $(`.typeahead__result`).hide();
      }
    }
  });
});
