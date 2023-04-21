function makeRequest(type, url, data, base = "dt/v1/") {
  //make sure base has a trailing slash if url does not start with one
  if ( !base.endsWith('/') && !url.startsWith('/')){
    base += '/'
  }
  const options = {
    type: type,
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: url.startsWith("http") ? url : `${field_settings.root}${base}${url}`,
    beforeSend: (xhr) => {
      xhr.setRequestHeader("X-WP-Nonce", field_settings.nonce);
    },
  };

  if (data) {
    options.data = type === "GET" ? data : JSON.stringify(data);
  }

  return jQuery.ajax(options);
}

jQuery(document).ready(function($) {

    window.API = {};
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

    window.API.edit_translations = (translation_type, post_type, tile_key, translations, field_key=null, field_option_key=null) => makeRequest("POST", `edit-translations`, {
        translation_type: translation_type,
        post_type: post_type,
        tile_key: tile_key,
        translations: translations,
        field_key: field_key,
        field_option_key: field_option_key,
    }, `dt-admin-settings`);

    window.API.new_field = (post_type, tile_key, new_field_name, new_field_type, new_field_private, connection_target, multidirectional, other_field_name) => makeRequest("POST", `new-field`, {
        post_type: post_type,
        tile_key: tile_key,
        new_field_name: new_field_name,
        new_field_type: new_field_type,
        new_field_private: new_field_private,
        connection_target: connection_target,
        multidirectional: multidirectional,
        other_field_name: other_field_name,
    }, `dt-admin-settings/`);

    window.API.edit_field = (post_type, tile_key, field_key, custom_name, field_private, tile_select, field_description, field_icon) => makeRequest("POST", `edit-field`, {
        post_type: post_type,
        tile_key: tile_key,
        field_key: field_key,
        custom_name: custom_name,
        field_private: field_private,
        tile_select: tile_select,
        field_description: field_description,
        field_icon: field_icon,
    }, `dt-admin-settings/`);

    window.API.new_field_option = (post_type, tile_key, field_key, field_option_name, field_option_description, field_option_icon) => makeRequest("POST", `new-field-option`, {
        post_type: post_type,
        tile_key: tile_key,
        field_key: field_key,
        field_option_name: field_option_name,
        field_option_description: field_option_description,
        field_option_icon: field_option_icon,
    }, `dt-admin-settings/`);

    window.API.edit_field_option = (post_type, tile_key, field_key, field_option_key, new_field_option_label, new_field_option_description, field_option_icon) => makeRequest("POST", `edit-field-option`, {
        post_type: post_type,
        tile_key: tile_key,
        field_key: field_key,
        field_option_key: field_option_key,
        new_field_option_label: new_field_option_label,
        new_field_option_description: new_field_option_description,
        field_option_icon: field_option_icon,
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
        var tile_key = get_tile_from_uri();
        var field_key = get_field_from_uri();
        click_tile(tile_key);
        click_field(field_key);
    }

    function get_tile_from_uri() {
        var tile = window.location.search.match('post_tile_key=(.*)');
        if ( tile !== null ) {
            return tile[1];
        }
        return;
    }

    function get_field_from_uri() {
        var field = window.location.hash;
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

    $('.field-settings-table, .tile-rundown-elements, .field-settings-table-child-toggle').sortable({
        update: function(event,ui) {
            if (!$(event)[0].originalEvent.target.nextElementSibling.dataset) {
                return;
            }
            var post_type = get_post_type();
            var tile_key = $(event)[0].target.dataset.parentTileKey;
            var moved_element = $(event)[0].originalEvent.target.nextElementSibling;

            // Check if moved element is a field option
            if (moved_element.dataset['fieldOptionKey']) {
                var field_key = moved_element.dataset['fieldKey'];
                var sortable_field_options_ordering = [];
                var field_options = $(`.field-name-content[data-field-key=${field_key}]`);

                $.each(field_options, function(option_index, option_element) {
                    sortable_field_options_ordering.push(option_element.dataset['fieldOptionKey']);
                });
                return;
            }

            dt_custom_tiles_and_fields_ordered = get_dt_custom_tiles_and_fields_ordered();
            API.update_tile_and_fields_order(post_type, dt_custom_tiles_and_fields_ordered).promise().then(function(result) {
                var new_order = result['contacts'][tile_key]['order'];
                window['field_settings']['post_type_settings']['tiles'][tile_key]['order'] = new_order;
                show_preview_tile(tile_key);
            });
        },
    });

    function get_dt_custom_tiles_and_fields_ordered() {
        var dt_custom_tiles_and_fields_ordered = {};
        var tiles = jQuery('.field-settings-table').sortable('toArray');
        var tile_priority = 10;
        tiles.pop(); // remove the 'add new tile' link

        jQuery.each(tiles, function(tile_index, tile_key) {
            if (tile_key === '' ) {
                return;
            }
            dt_custom_tiles_and_fields_ordered[tile_key] = {};
            var tile_label = jQuery(`#tile-key-${tile_key}`).prop('innerText');
            var fields = jQuery(`.field-settings-table-field-name[data-parent-tile-key="${tile_key}"]`);
            var field_order = [];
            jQuery.each(fields, function(field_index, field_element) {
                var field_key = field_element.id;
                field_order.push(field_key);
            });

            dt_custom_tiles_and_fields_ordered[tile_key]['order'] = field_order;
            dt_custom_tiles_and_fields_ordered[tile_key]['tile_priority'] = tile_priority;
            dt_custom_tiles_and_fields_ordered[tile_key]['label'] = tile_label;
            tile_priority += 10;
        });
        return dt_custom_tiles_and_fields_ordered;
    }

    function get_field_defaults(tile_key, field_key) {
        var field_options = jQuery(`.field-name-content[data-parent-tile-key="${tile_key}"][data-field-key="${field_key}"]`);
        var field_default = [];
        jQuery.each(field_options, function(field_index, field_element){
            var field_option_key = jQuery(field_element).data('field-option-key');
            var field_option_label = jQuery(field_element).prop('innerText');
            field_default[field_option_key] = {'label': field_option_label};
        });
        return field_default;
    }

    $('.field-settings-table').on('click', '.field-settings-table-tile-name', function() {
        var tile_key = $(this).data('key');
        if (!tile_key || tile_key === 'no-tile-hidden') {
            hide_preview_tile();
            return;
        }
            show_preview_tile(tile_key);
        render_element_shadows();
    });

    $('.field-settings-table').on('click', '.edit-icon', function() {
        var edit_modal = $(this).parent().data('modal');
        if (edit_modal === 'edit-field') {
            var data = [];
            data['tile_key'] = $(this).parent().data('parent-tile-key');
            data['field_key'] = $(this).parent().data('key');
        } else {
            data = $(this).parent().data('key');
        }
        showOverlayModal(edit_modal, data);
    });

    $('.field-settings-table').on('click', '.edit-icon[data-modal="edit-field-option"]', function() {
        var edit_modal = $(this).data('modal');
        if (edit_modal === 'edit-field-option') {
            var data = [];
            data['tile_key'] = $(this).data('parent-tile-key');
            data['field_key'] = $(this).data('field-key');
            data['option_key'] = $(this).data('field-option-key');
        }
        showOverlayModal(edit_modal, data);
    });

    $('.field-settings-table').on('click', "div[class*='expandable']", function(event) {
        if ( event.target.className != 'edit-icon' ) {
            $(this).next().slideToggle(333, 'swing');
            if ($(this).children('.expand-icon').text() === '+'){
                $(this).children('.expand-icon').text('-');
            } else {
                $(this).children('.expand-icon').text('+');
            }
        }
        render_element_shadows();
    });

    $('#add-new-tile-link').on('click', function(event){
        event.preventDefault();
        showOverlayModal('add-new-tile');
    });

    $('.field-settings-table').on('click', '.add-new-field', function() {
        var tile_key = $(this).data('parent-tile-key');
        showOverlayModal('add-new-field', tile_key);
    });

    function show_preview_tile(tile_key) {
        var tile_html = `
            <div class="dt-tile-preview">
                <div class="section-header">
                    <h3 class="section-header">${window['field_settings']['post_type_tiles'][tile_key]['label']}</h3>
                    <img src="${window.field_settings.template_dir}/dt-assets/images/chevron_up.svg" class="chevron">
                </div>
                <div class="section-body">`;

        field_order = window['field_settings']['post_type_settings']['tiles'][tile_key]['order'];
        $.each(field_order, function(field_index, field_key) {
            var field = window['field_settings']['post_type_settings']['fields'][field_key];

                var icon_html = '';
                if ( field['icon'] ) {
                    icon_html = `<img src="${field['icon']}" class="dt-icon lightgray"></img>`
                }

                tile_html += `
                        <div class="section-subheader">
                            ${icon_html}
                            ${field['name']}
                        </div>
                `;


                /*** TEXT - START ***/
                if ( [ 'text', 'communication_channel', 'location', 'location_meta' ].indexOf(field['type']) > -1 ) {
                    tile_html += `
                        <input type="text" class="text-input">
                    `;
                }
                /*** TEXT - END ***/



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
                        var multi_select_icon_html = '';
                        $.each( field['default'], function(k,f) {
                            if ( f['icon'] ) {
                                multi_select_icon_html = `<img src="${f['icon']}" class="dt-icon">`;
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
                    var color_select = '';
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
        $('.dt-admin-modal-overlay').fadeIn(150, 'swing');
        $('.dt-admin-modal-box').slideDown(150, 'swing');
        showOverlayModalContentBox(modalName, data);
    }

    function showOverlayModalContentBox(modalName, data=null) {
        if ( modalName == 'add-new-tile' ) {
            loadAddTileContentBox();
        }
        if ( modalName == 'edit-tile' ) {
            loadEditTileContentBox(data);
        }
        if ( modalName == 'add-new-field' ) {
            loadAddFieldContentBox(data);
        }
        if ( modalName == 'edit-field' ) {
            loadEditFieldContentBox(data);
        }
        if ( modalName == 'edit-field-option' ) {
            loadEditFieldOptionContentBox(data);
        }
        if ( modalName == 'new-field-option') {
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
        $('.dt-admin-modal-overlay').fadeOut(150, 'swing');
        $('.dt-admin-modal-box').slideUp(150, 'swing');
        $('#modal-overlay-content-table').html('');
    }

    function scrollTo(target_element, offset=0) {
        $([document.documentElement, document.body]).animate({
            scrollTop: target_element.offset().top + offset
        }, 500);
    }

    // Add Tile Modal
    function loadAddTileContentBox() {
        var modal_html_content = `
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
        <tr>
            <td colspan="2">
                <button class="button" type="submit" id="js-add-tile">Create Tile</button>
            </td>
        </tr>`;
        $('#modal-overlay-content-table').html(modal_html_content);
    }

    // Edit Tile Modal
    function loadEditTileContentBox(tile_key) {
        var post_type = get_post_type();

        var translations_count = 0;
        if (window['field_settings']['post_type_tiles'][tile_key]['translations']) {
            translations_count = Object.values(window['field_settings']['post_type_tiles'][tile_key]['translations']).filter(function(t) {return t;}).length;
        }

        var description_translations_count = 0;
        if (window['field_settings']['post_type_tiles'][tile_key]['description_translations']) {
            description_translations_count = Object.values(window['field_settings']['post_type_tiles'][tile_key]['description_translations']).filter(function(t) {return t;}).length;
        }

        API.get_tile(post_type, tile_key).promise().then(function(data) {
            var tile_description = '';
            if ( data['description'] ) {
                tile_description = data['description'];
            }
            var hide_tile = '';
            if (data['hidden']) {
                hide_tile = 'checked';
            }

            var modal_html_content = `
            <tr>
                <th colspan="2">
                    <h3 class="modal-box-title">Edit '${data['label']}' Tile</h3>
                </th>
            </tr>
            <tr>
                <td>
                    <label><b>Key</b></label>
                </td>
                <td>
                    ${tile_key}
                </td>
            </tr>
            <tr>
                <td>
                    <label for="edit-tile-label"><b>Label</b></label>
                </td>
                <td>
                    <input name="edit-tile-label" id="edit-tile-label-${tile_key}" type="text" value="${data['label']}" required>
                    <button class="button expand_translations" name="translate-label-button" data-translation-type="tile-label" data-post-type="${post_type}" data-tile-key="${tile_key}">
                        <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                        (${translations_count})
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="edit-tile-description"><b>Description</b></label>
                </td>
                <td>
                <input name="edit-tile-description" id="edit-tile-description-${tile_key}" type="text" value="${tile_description}">
                <button class="button expand_translations" name="translate-description-button" data-translation-type="tile-description" data-post-type="${post_type}" data-tile-key="${tile_key}">
                    <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                    (${description_translations_count})
                </button>
            </td>
            </tr>
            <tr>
                <td>
                    <label for="hide_tile"><b>Hide tile on page</b></label>
                </td>
                <td>
                    <input name="hide-tile" id="hide-tile-${tile_key}" type="checkbox" ${hide_tile}>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button class="button" type="submit" id="js-edit-tile" data-tile-key="${tile_key}">Save</button>
                </td>
            </tr>`;
            $('#modal-overlay-content-table').html(modal_html_content);
        });
    }

    // Add Field Modal
    function loadAddFieldContentBox(tile_key) {
        post_type = get_post_type();
        all_post_types = window.field_settings.all_post_types;
        selected_post_type_label = all_post_types[post_type];
        var tile_key_label = tile_key;
        if (!tile_key) {
            tile_key_label = `<i>This post type doesn't have any tiles</i>`;
        }
        var modal_html_content = `
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
                    <label for="tile_label"><b>New Field Name</b></label>
                </td>
                <td>
                    <input name="edit-tile-label" id="new-field-name-${tile_key}" type="text" value="" required>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="tile_label"><b>Field Type</b></label>
                </td>
                <td>
                    <select id="new-field-type-${tile_key}" name="new-field-type" required>
                        <option></option>
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
                </td>
            </tr>
            <tr class="connection_field_target_row" style="display: none;">
                <td><label for="connection-target"><b>Connected To</label></b></td>
                <td>
                    <select name="connection-target" id="connection-field-target">
                        <option></option>`;

                        $.each(all_post_types, function(k,v) {
                            modal_html_content += `
                            <option value="${k}">
                                ${v}
                            </option>`;
                        });

                    modal_html_content += `</select>
                </td>
            </tr>
            <tr class="same_post_type_row" style="display: none">
                <td>
                    Bi-directional
                </td>
                <td>
                    <input type="checkbox" id="multidirectional_checkbox" name="multidirectional" checked>
                </td>
            </tr>
            <tr class="connection_field_reverse_row" style="display: none;">
                <td>
                    Field name when shown on:
                    <span class="connected_post_type"></span>
                </td>
                <td>
                    <input name="other_field_name" id="other_field_name">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="new_tile_name"><b>Private Field</b></label>
                </td>
                <td>
                    <input name="new_field_private" id="new-field-private-${tile_key}" type="checkbox">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button class="button" type="submit" id="js-add-field" data-tile-key="${tile_key}">Save</button>
                </td>
            </tr>
        `;
        $('#modal-overlay-content-table').html(modal_html_content);
    }

    // Edit Field Modal
    function loadEditFieldContentBox(field_data) {
        var post_type = get_post_type();
        var tile_key = field_data['tile_key'];
        var field_key = field_data['field_key'];
        var field_settings = window['field_settings']['post_type_settings']['fields'][field_key];

        var name_is_custom = false;
        if ( field_settings['default_name'] ) {
            name_is_custom = true;
        }

        var translations_count = 0;
        if (window['field_settings']['post_type_settings']['fields'][field_key]['translations']) {
            translations_count = Object.values(window['field_settings']['post_type_settings']['fields'][field_key]['translations']).filter(function(t){return t;}).length;
        }

        var description_translations_count = 0;
        if ( window['field_settings']['post_type_settings']['fields'][field_key]['description_translations'] ) {
            description_translations_count = Object.values(window['field_settings']['post_type_settings']['fields'][field_key]['description_translations']).filter(function(t){return t;}).length;
        }

        var field_icon_image_html = '';
        if ( field_settings['icon'] ) {
            field_icon_image_html = `<img src="${field_settings['icon']}" class="field-icon">`;
        }

        var private_field = '';
        if ( field_settings['private'] ) {
            private_field = 'checked';
        }

        if ( !field_settings['description'] ) {
            field_settings['description'] = '';
        }

        if ( !field_settings['icon'] ) {
            field_settings['icon'] = '';
        }

        var modal_html_content = `
            <tr>
                <th colspan="2">
                    <h3 class="modal-box-title">Edit '${field_settings['name']}' Field Settings</h3>
                </th>
            </tr>
            <tr>
                <td>
                    <label><b>Key</label></b>
                </td>
                <td>
                    ${field_key}
                </td>
            </tr>`;

            var name_section_html = `
                <tr>
                    <td>
                        <label for="edit-field-custom-name"><b>Name</b></label>
                    </td>
                    <td>
                        <input name="edit-field-custom-name" id="edit-field-custom-name" type="text" value="${field_settings['name']}">
                        <button class="button small expand_translations" name="translate-label-button" data-translation-type="field-label" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}">
                            <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                            (${translations_count})
                        </button>
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
                            <input name="edit-field-custom-name" id="edit-field-custom-name" type="text" value="${field_settings['name']}">
                            <button class="button small" id="remove-custom-name" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}">Remove Custom Name</button>
                            <button class="button small expand_translations" name="translate-label-button" data-translation-type="field-label" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}">
                                <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                                (${translations_count})
                            </button>
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
                    <input name="edit-field-description" id="edit-field-description" type="text" value="${field_settings['description']}">
                    <button class="button small expand_translations" name="translate-description-button" data-translation-type="field-description" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}">
                        <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                        (${description_translations_count})
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="edit-field-private"><b>Private Field</b></label>
                </td>
                <td>
                    <input name="edit-field-private" id="edit-field-private" type="checkbox" ${private_field}>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="tile-select"><b>Tile</b></label>
                </td>
                <td>
                    <select name="tile-select" id="tile_select">
                        <option value="no_tile">No tile / hidden</option>`;
                        $.each(window.field_settings.post_type_tiles, function (k, tile) {
                            if ( k === tile_key  ) {
                                modal_html_content += `<option value="${k}" selected>${tile['label']}</option>`;
                            } else {
                                modal_html_content += `<option value="${k}">${tile['label']}</option>`;
                            }
                        });
                modal_html_content += `
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="edit-field-icon"><b>Icon</b></label>
                </td>
                <td>
                    ${field_icon_image_html}
                    <input name="edit-field-icon" id="edit-field-icon" type="text" value="${field_settings['icon']}">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button class="button" type="submit" id="js-edit-field" data-tile-key="${tile_key}" data-field-key="${field_key}">Save</button>
                </td>
            </tr>`;
        $('#modal-overlay-content-table').html(modal_html_content);
    }

    // Add Field Option Modal
    function loadAddFieldOptionBox(data) {
        var tile_key = data['tile_key'];
        var field_key = data['field_key'];
        var modal_html_content = `
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
                <input name="edit-field-icon" id="edit-field-icon" type="text">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button class="button" style="margin-top: 12px;" type="submit" id="js-add-field-option" data-tile-key="${tile_key}" data-field-key="${field_key}">Add</button>
            </td>
        </tr>`;
        $('#modal-overlay-content-table').html(modal_html_content);
    }

    // Edit Field Option Modal
    function loadEditFieldOptionContentBox(data) {
        var post_type = get_post_type();
        var tile_key = data['tile_key'];
        var field_key = data['field_key'];
        var field_option_key = data['option_key'];
        var field_settings = window['field_settings']['post_type_settings']['fields'][field_key];
        var field_option = field_settings['default'][field_option_key];
        var option_description = '';

        var name_is_custom = false;
        if ( field_option['default_name'] ) {
            name_is_custom = true;
        }

        if ( 'description' in field_settings['default'][field_option_key] ) {
            option_description = field_settings['default'][field_option_key]['description'];
        }

        var translations_count = 0;
        if (field_settings['default'][field_option_key]['translations']) {
            translations_count = Object.values(field_settings['default'][field_option_key]['translations']).filter(function(t) {return t;}).length;
        }

        var description_translations_count = 0;
        if (field_settings['default'][field_option_key]['description_translations']) {
            description_translations_count = Object.values(field_settings['default'][field_option_key]['description_translations']).filter(function(t) {return t;}).length;
        }

        var field_icon_url = '';
        var field_icon_image_html = '';

        if ( field_settings['default'][field_option_key]['icon'] ) {
            field_icon_url = field_settings['default'][field_option_key]['icon'];
            field_icon_image_html = `<img src="${field_icon_url}" class="field-icon">`;
        }
        var modal_html_content = `
        <tr>
            <th colspan="2">
                <h3 class="modal-box-title">Edit Field Option</h3>
            </th>
        </tr>
        <tr>
            <td>
                <label><b>Key</b></label>
            </td>
            <td>
                ${field_option_key}
            </td>
        </tr>`;

        var name_section_html = `
        <tr>
            <td>
                <label><b>Name</b></label>
            </td>
            <td>
                <input name="edit-option-label" id="new-option-name" type="text" value="${field_option['label']}" required>
                <button class="button expand_translations" name="translate-label-button" data-translation-type="field-option-label" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${field_option_key}">
                    <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                    (${translations_count})
                </button>
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
                    <input name="edit-option-label" id="new-option-name" type="text" value="${field_option['label']}" required>
                    <button class="button small" id="remove-custom-label" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${field_option_key}">Remove Custom Label</button>
                    <button class="button expand_translations" name="translate-label-button" data-translation-type="field-option-label" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${field_option_key}">
                        <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                        (${translations_count})
                    </button>
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
                <input name="edit-option-description" id="new-option-description" type="text" value="${option_description}">
                <button class="button expand_translations" name="translate-description-button" data-translation-type="field-option-description" data-post-type="${post_type}" data-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${field_option_key}">
                    <img style="height: 15px; vertical-align: middle" src="${window.field_settings.template_dir}/dt-assets/images/languages.svg">
                    (${description_translations_count})
                </button>
            </td>
        </tr>
        <tr>
            <td>
                <label for="edit-field-icon"><b>Icon</b></label>
            </td>
            <td>
                ${field_icon_image_html}
                <input name="edit-field-icon" id="edit-field-icon" type="text" value="${field_icon_url}">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button class="button" type="submit" id="js-edit-field-option" data-tile-key="${tile_key}" data-field-key="${field_key}" data-field-option-key="${field_option_key}">Save</button>
            </td>
        </tr>`;
        $('#modal-overlay-content-table').html(modal_html_content);
    }


    $('#modal-overlay-form').on('submit', function(event){
        event.preventDefault();
    });

    // Process Add Tile
    $('#modal-overlay-form').on('click', '#js-add-tile', function(e) {
        var post_type = get_post_type();
        var new_tile_name = $('#new_tile_name').val();
        var new_tile_description = $('#new_tile_description').val();

        API.create_new_tile(post_type, new_tile_name, new_tile_description).promise().then(function(data) {
            var tile_key = data['key'];
            var tile_label = data['label'];
            window.field_settings.post_type_tiles[tile_key] = {'label':tile_label};
            closeModal();
            $('#add-new-tile-link').parent().before(`
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
                <div class="hidden">
                    <div class="field-settings-table-field-name inset-shadow">
                       <span class="sortable ui-icon ui-icon-arrow-4"></span>
                        <span class="field-name-content add-new-field" data-parent-tile-key="${tile_key}">
                            <a>add new field</a>
                        </span>
                    </div>
                </div>
            </div>
            `);
            $(`.field-settings-table-tile-name`).eq(-2).attr('style', 'border-bottom: 0;');
        });
    });

    // Process Edit Tile
    $('#modal-overlay-form').on('click', '#js-edit-tile', function(e) {
        var post_type = get_post_type();
        var tile_key = $(this).data('tile-key');
        var tile_label = $(`#edit-tile-label-${tile_key}`).val();
        var tile_description = $(`#edit-tile-description-${tile_key}`).val();
        var hide_tile = $(`#hide-tile-${tile_key}`).is(':checked');
        API.edit_tile(post_type, tile_key, tile_label, tile_description, hide_tile).promise().then(function(response) {
            $(`#tile-key-${tile_key}`).parent().removeClass('menu-highlight');
            window['field_settings']['post_type_tiles'][tile_key] = response;
            $(`#tile-key-${tile_key}`).html(tile_label);
            show_preview_tile(tile_key);
            closeModal();
            $(`#tile-key-${tile_key}`).parent().addClass('menu-highlight');
        });
    });

    // Process Add Field
    $('#modal-overlay-form').on('click', '#js-add-field', function(e) {
        var post_type = get_post_type();
        var tile_key = $(this).data('tile-key');
        var new_field_name = $(`#new-field-name-${tile_key}`).val();
        var new_field_type = $(`#new-field-type-${tile_key}`).val();
        var new_field_private = $(`#new-field-private-${tile_key}`).is(':checked');
        var connection_target = $('#connection-field-target').val();
        var multidirectional = $('#multidirectional_checkbox').is(':checked');
        var other_field_name = $('#other_field_name').val();

        API.new_field(post_type, tile_key, new_field_name, new_field_type, new_field_private, connection_target, multidirectional, other_field_name ).promise().then(function(response) {
            var field_key = response['key'];
            window['field_settings']['post_type_settings']['fields'][field_key] = response;
            var new_field_nonexpandable_html = `
                <div class="sortable-fields" id="${field_key}">
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

            var new_field_expandable_html = `
            <div class="sortable-fields" id="${field_key}">
                <div class="field-settings-table-field-name expandable submenu-highlight" id="${field_key}" data-parent-tile-key="${tile_key}" data-key="${field_key}" data-modal="edit-field">
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
                    <div class="field-settings-table-field-option inset-shadow">
                       <span class="sortable ui-icon ui-icon-arrow-4"></span>
                        <span class="field-name-content"><i>default blank</i></span>
                    </div>
                    <div class="field-settings-table-field-option new-field-option" data-parent-tile-key="${tile_key}" data-field-key="${field_key}">
                        <span class="sortable ui-icon ui-icon-arrow-4"></span>
                        <span class="field-name-content">new field option</span>
                    </div>
                </div>
                <!-- END TOGGLED ITEMS -->
            </div>
            `;
            var new_field_html = new_field_nonexpandable_html;
            if(['key_select', 'multi_select'].indexOf(new_field_type) > -1) {
                new_field_html = new_field_expandable_html;
            }
            if (tile_key){
                $(`.add-new-field[data-parent-tile-key='${tile_key}']`).parent().before(new_field_html); //todo
                show_preview_tile(tile_key);
            } else {
                $('.add-new-field').parent().before(new_field_html);
            }
            render_element_shadows();
            closeModal();
        });
    });

    // Process Edit Field
    $('#modal-overlay-form').on('click', '#js-edit-field', function() {
        var post_type = get_post_type();
        var tile_key = $(this).data('tile-key');
        var field_key = $(this).data('field-key');
        var custom_name = $('#edit-field-custom-name').val();
        var field_private = $('#edit-field-private').is(':checked');
        var tile_select = $('#tile_select').val();
        var field_description = $('#edit-field-description').val();
        var field_icon = $('#edit-field-icon').val();

        API.edit_field(post_type, tile_key, field_key, custom_name, field_private, tile_select, field_description, field_icon).promise().then(function(result){
            window.field_settings.post_type_settings.fields[field_key] = result;

            var edited_field_menu_element = $('.field-settings-table-field-name').filter(function() {
                return $(this).data('parent-tile-key') == tile_key && $(this).data('key') == field_key;
            });

            var edited_field_submenu_element = $('.field-settings-table-child-toggle').filter(function(){
                return $(this).data('parent-tile-key') == tile_key && $(this).data('key') == field_key;
            });

            var edited_field_menu_name_element = edited_field_menu_element.children('.field-name-content');
            edited_field_menu_element.removeClass('menu-highlight');
            edited_field_submenu_element.children('.field-settings-table-field-option').removeClass('submenu-highlight');

            if ( custom_name != '' ) {
                edited_field_menu_name_element[0].innerText = custom_name;
            }

            // Check if rundown element and sub element need to be moved to another tile
            if ( tile_key != tile_select ) {
                var target_tile_menu = $(`.field-settings-table-tile-name[data-key="${tile_select}"]`);
                var target_tile_submenu = $(`.tile-rundown-elements[data-parent-tile-key="${tile_select}"]`);

                if ( target_tile_submenu.is(':visible') === false ) {
                    target_tile_menu.trigger('click');
                }

                target_tile_submenu.prepend(edited_field_menu_element);
                edited_field_menu_element.after(edited_field_submenu_element);

                scrollTo(target_tile_menu, -32);

                edited_field_menu_element.data('parent-tile-key', tile_select);
                edited_field_menu_name_element.data('parent-tile-key', tile_select);
                edited_field_submenu_element.data('parent-tile-key', tile_select);
            }
            show_preview_tile(tile_key);
            closeModal();
            edited_field_menu_element.addClass('menu-highlight');
            edited_field_submenu_element.children('.field-settings-table-field-option').addClass('submenu-highlight');
        });
        return;
    });

    // Process Add Field Option
    $('#modal-overlay-form').on('click', '#js-add-field-option', function(e) {
        var post_type = get_post_type();
        var tile_key = $(this).data('tile-key');
        var field_key = $(this).data('field-key');
        var field_option_name = $('.new-field-option-name').val();
        var field_option_description = $('.new-field-option-description').val();
        var field_option_icon = $('#edit-field-icon').val();

        API.new_field_option(post_type, tile_key, field_key, field_option_name, field_option_description, field_option_icon).promise().then(function(new_field_option_key) {
            window['field_settings']['post_type_settings']['fields'][field_key]['default'].push( new_field_option_key = {
                label:field_option_name,
                description:field_option_description,
                icon:field_option_icon,
            });

            var new_field_option_html = `
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
    $('#modal-overlay-form').on('click', '#js-edit-field-option', function(e) {
        var post_type = get_post_type();
        var tile_key = $(this).data('tile-key');
        var field_key = $(this).data('field-key');
        var field_option_key = $(this).data('field-option-key');
        var new_field_option_label = $('#new-option-name').val();
        var new_field_option_description = $('#new-option-description').val();
        var field_option_icon = $('#edit-field-icon').val();

        API.edit_field_option(post_type, tile_key, field_key, field_option_key, new_field_option_label, new_field_option_description, field_option_icon).promise().then(function(result) {
            window['field_settings']['post_type_settings']['fields'][field_key]['default'][field_option_key] = result;
            var edited_field_option_element = $(`.field-name-content[data-parent-tile-key="${tile_key}"][data-field-key="${field_key}"][data-field-option-key="${field_option_key}"]`);
            edited_field_option_element.parent().removeClass('submenu-highlight');
            edited_field_option_element[0].innerText = new_field_option_label;
            show_preview_tile(tile_key);
            closeModal();
            edited_field_option_element.parent().addClass('submenu-highlight');
        });
    });

    // Process Remove Custom Field Name
    $('.dt-admin-modal-box').on('click', '#remove-custom-name', function() {
        var post_type = $(this).data('post-type');
        var tile_key = $(this).data('tile-key');
        var field_key = $(this).data('field-key');

        API.remove_custom_field_name(post_type, field_key).promise().then(function(default_name) {
            window['field_settings']['post_type_settings']['fields'][field_key]['name'] = default_name;
            delete window['field_settings']['post_type_settings']['fields'][field_key]['default_name'];
            var edited_field_content_element = $(`.field-name-content[data-parent-tile-key="${tile_key}"][data-key="${field_key}"]`);
            var edited_field_parent_element = $(`.field-name-content[data-parent-tile-key="${tile_key}"][data-key="${field_key}"]`).parent();
            edited_field_parent_element.removeClass('menu-highlight');
            closeModal();
            edited_field_content_element.html(default_name);
            edited_field_parent_element.addClass('menu-highlight');
        });
    });

     // Process Remove Custom Field Option Label
     $('.dt-admin-modal-box').on('click', '#remove-custom-label', function() {
        var post_type = $(this).data('post-type');
        var tile_key = $(this).data('tile-key');
        var field_key = $(this).data('field-key');
        var field_option_key = $(this).data('field-option-key');

        API.remove_custom_field_option_label(post_type, field_key, field_option_key).promise().then(function(default_label) {
            window['field_settings']['post_type_settings']['fields'][field_key]['default'][field_option_key]['label'] = default_label;
            delete window['field_settings']['post_type_settings']['fields'][field_key]['default_name'];

            var edited_field_option_content_element = $(`.field-name-content[data-parent-tile-key="${tile_key}"][data-field-key="${field_key}"][data-field-option-key="${field_option_key}"]`);
            var edited_field_option_parent_element = $(`.field-name-content[data-parent-tile-key="${tile_key}"][data-field-key="${field_key}"][data-field-option-key="${field_option_key}"]`).parent();

            edited_field_option_parent_element.removeClass('submenu-highlight');
            closeModal();
            edited_field_option_content_element.html(default_label);
            edited_field_option_parent_element.addClass('submenu-highlight');
        });
    });

    // Translation for Tiles
    $('.dt-admin-modal-box').on('click', '.expand_translations', function() {
        var translation_type = $(this).data('translation-type');
        var post_type = $(this).data('post-type');
        var tile_key = $(this).data('tile-key');
        var field_key = $(this).data('field-key');
        var field_option_key = $(this).data('field-option-key');
        var languages = window['field_settings']['languages'];
        var available_translations = {};

        var element_type = '';
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

        var translations_html = `
        <table class="modal-translations-overlay-content-table" id="modal-translations-overlay-content-table">
            <tr>
                <th colspan="2">Translate "${element_key}" ${element_type}</th>
            </tr>`;

        if ( translation_type === 'tile-label' ) {
            if ( window['field_settings']['post_type_tiles'][tile_key]['translations'] ) {
                available_translations = window['field_settings']['post_type_tiles'][tile_key]['translations'];
            }
        }

        if ( translation_type === 'tile-description' ) {
            if ( window['field_settings']['post_type_tiles'][tile_key]['description_translations'] ) {
                available_translations = window['field_settings']['post_type_tiles'][tile_key]['description_translations'];
            }
        }

        if ( translation_type === 'field-label' ) {
            if ( field_key === 'undefined' ) {
                return;
            }
            if ( window['field_settings']['post_type_settings']['fields'][field_key]['translations'] ) {
                available_translations = window['field_settings']['post_type_settings']['fields'][field_key]['translations'];
            }
        }

        if ( translation_type === 'field-description' ) {
            if ( field_key === 'undefined' ) {
                return;
            }
            if ( window['field_settings']['post_type_settings']['fields'][field_key]['description_translations'] ) {
                available_translations = window['field_settings']['post_type_settings']['fields'][field_key]['description_translations'];
            }
        }

        if ( translation_type === 'field-option-label' ) {
            if( field_key === 'undefined' || field_option_key === 'undefined' ) {
                return;
            }
            if ( window['field_settings']['post_type_settings']['fields'][field_key]['default'][field_option_key]['translations'] ) {
                available_translations = window['field_settings']['post_type_settings']['fields'][field_key]['default'][field_option_key]['translations'];
            }
        }

        if ( translation_type === 'field-option-description' ) {
            if ( field_key === 'undefined' ) {
                return;
            }
            if ( window['field_settings']['post_type_settings']['fields'][field_key]['default'][field_option_key]['description_translations'] ) {
                available_translations = window['field_settings']['post_type_settings']['fields'][field_key]['default'][field_option_key]['description_translations'];
            }
        }

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

        $('#modal-translations-overlay-form').html(translations_html);
        flip_card();

    });

    $('.dt-admin-modal-translations-box-close-button').on('click', function() {
        unflip_card();
    });

    $('#modal-translations-overlay-form').on('click', '.cancel-translations-button', function() {
        event.preventDefault();
        unflip_card();
    });

    $('#modal-translations-overlay-form').on('click', '.save-translations-button', function() {
        event.preventDefault();
        var translation_type = $(this).data('translation-type');
        var post_type = $(this).data('post-type');
        var tile_key = $(this).data('tile-key');
        var field_key = $(this).data('field-key');
        var field_option_key = $(this).data('field-option-key');

        var translations = {};
        var translation_inputs = $('#modal-translations-overlay-form input');
        $.each(translation_inputs, function(key, t) {
            var translation_value = $(t).val();
            var translation_key = $(t).data('translation-key');
            if (translation_value) {
                translations[translation_key] = translation_value;
            }
        });

        translations = JSON.stringify(translations);
        var element_button_selector = $('.expand_translations[name="translate-label-button"]');
        API.edit_translations(translation_type, post_type, tile_key, translations, field_key, field_option_key).promise().then(function(response) {
            var translations_count = 0;
            if ( translation_type === 'tile-label' ) {
                window['field_settings']['post_type_tiles'][tile_key]['translations'] = response;
                translations_count = Object.values(window['field_settings']['post_type_tiles'][tile_key]['translations']).filter(function(t) {return t;}).length;
                element_button_selector = $('.expand_translations[name="translate-label-button"]');
            }

            if ( translation_type === 'tile-description' ) {
                window['field_settings']['post_type_tiles'][tile_key]['description_translations'] = response;
                translations_count = Object.values(window['field_settings']['post_type_tiles'][tile_key]['description_translations']).filter(function(t) {return t;}).length;
                element_button_selector = $('.expand_translations[name="translate-description-button"]');
            }

            if ( translation_type === 'field-label' ) {
                window['field_settings']['post_type_settings']['fields'][field_key]['translations'] = response;
                translations_count = Object.values(window['field_settings']['post_type_settings']['fields'][field_key]['translations']).filter(function(t) {return t;}).length;
                element_button_selector = $('.expand_translations[name="translate-label-button"]');
            }

            if ( translation_type === 'field-description' ) {
                window['field_settings']['post_type_settings']['fields'][field_key]['description_translations'] = response;
                translations_count = Object.values(window['field_settings']['post_type_settings']['fields'][field_key]['description_translations']).filter(function(t) {return t;}).length;
                element_button_selector = $('.expand_translations[name="translate-description-button"]');
            }

            if ( translation_type === 'field-option-label' ) {
                window['field_settings']['post_type_settings']['fields'][field_key]['default'][field_option_key]['translations'] = response;
                translations_count = Object.values(window['field_settings']['post_type_settings']['fields'][field_key]['default'][field_option_key]['translations']).filter(function(t) {return t;}).length;
                element_button_selector = $('.expand_translations[name="translate-label-button"]');
            }

            if ( translation_type === 'field-option-description' ) {
                window['field_settings']['post_type_settings']['fields'][field_key]['default'][field_option_key]['description_translations'] = response;
                translations_count = Object.values(window['field_settings']['post_type_settings']['fields'][field_key]['default'][field_option_key]['description_translations']).filter(function(t) {return t;}).length;
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

    $('.dt-admin-modal-overlay').on('click', function(e) {
        if (e.target == this) {
            closeModal();
        }
    });

    $('.field-name').on('click', function() {
            $(this).find('.field-name-icon-arrow:not(.disabled)').toggleClass('arrow-expanded');
            $(this).find('.field-elements-list').slideToggle(333, 'swing');
    });

    $('.field-settings-table').on('click', '.new-field-option', function() {
        var data = [];
        data['tile_key'] = $(this).data('parent-tile-key');
        data['field_key'] = $(this).data('field-key');
        showOverlayModal('new-field-option', data);
    });

    // Display 'connected to' dropdown if 'connection' post type field is selected
    $('.dt-admin-modal-box').on('change', '[id^=new-field-type-]', function() {
        if ( $(this).val() === 'connection' ) {
            $('.connection_field_target_row').show();
        } else {
            $('.connection_field_target_row').hide();
            $('.same_post_type_row').hide();
            $('.connection_field_reverse_row').hide();
            $('#connection-field-target option').prop('selected', false);
        }
    });

    $('.dt-admin-modal-box').on('change', '#connection-field-target', function() {
        var selected_field_target = $(this).find(':selected').val();
        if ( selected_field_target === window.post_type ) {
            $('.connection_field_reverse_row').hide();
            $('.same_post_type_row').show();
        } else {
            $('.same_post_type_row').hide();
            $('.connection_field_reverse_row').show();
            var selected_field_target_label = window.field_settings.all_post_types[selected_field_target];
            $('.connected_post_type').text(selected_field_target_label);
        }
    });

    function render_element_shadows() {
        $('.field-settings-table-tile-name').next().children().removeClass('inset-shadow');
        $('.tile-rundown-elements > div:first-child').addClass('inset-shadow');
        $('.field-settings-table-field-name.expandable').next().children().removeClass('inset-shadow');
        $('.field-settings-table-field-option:first-child').addClass('inset-shadow');

    }

    // Typeahead
    $.typeahead({
        input: '.js-typeahead-settings',
        order: "desc",
        cancelButton: false,
        dynamic: false,
        emptyTemplate: '<em style="padding-left:12px;">No results for "{{query}}"</em>',
        template: '<a href="' + window.location.origin + window.location.pathname + '?page=dt_customizations&post_type={{post_type}}&tab=tiles&post_tile_key={{post_tile}}#{{post_setting}}">{{label}}</a>',
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
