jQuery(document).ready(function($) {
    console.log(3);
    $('.field-settings-table').on('click', '.field-settings-table-tile-name', function() {
        var tile_key = $(this).data('key');
        show_preview_tile(tile_key);
    });

    $('.field-settings-table').on('click', '.edit-icon', function(){
        var edit_modal = $(this).parent().data('modal');
        var data = $(this).parent().data('key');
        if (edit_modal === 'edit-field') {
            var data = [];
            data['tile_key'] = $(this).parent().data('parent-tile-key');
            data['field_key'] = $(this).parent().data('key');
        }
        showOverlayModal(edit_modal, data);
    });

    function show_preview_tile(tile_key) {        
        var tile_html = `
            <div class="dt-tile-preview">
                <div class="section-header">
                    <h3 class="section-header">${window['field_settings']['post_type_tiles'][tile_key]['label']}</h3>
                    <img src="${window.wpApiShare.template_dir}/dt-assets/images/chevron_up.svg" class="chevron">
                </div>
                <div class="section-body">`;
        
        var all_fields = window.field_settings.post_type_settings.fields;
        $.each(all_fields, function(key, field) {
            if( field['tile'] === tile_key ) {
                var icon_html = '';
                if ( field['icon'] ) {
                    icon_html = `<img src="${field['icon']}" alt="${field['name']}" class="dt-icon lightgray"></img>`
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
                                <img src="${window.wpApiShare.template_dir}/dt-assets/images/search.svg">
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
                                <img src="${window.wpApiShare.template_dir}/dt-assets/images/add-contact.svg">
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
            }
        });
        tile_html += `
                </div>
            </div>`;
        $('.fields-table-right').html(tile_html);
    }

    $('.field-settings-table').on('click', "div[class*='expandable']", function() {
        $(this).next().slideToggle(333, 'swing');
        if ($(this).children('.expand-icon').text() === '+'){
            $(this).children('.expand-icon').text('-');
        } else {
            $(this).children('.expand-icon').text('+');
        }
    });

    $('#add-new-tile-link').on('click', function(event){
        event.preventDefault();
        showOverlayModal('add-new-tile');
    });

    function showOverlayModal(modalName, data=null) {
        $('.dt-admin-modal-overlay').fadeIn(150, 'swing');
        $('.dt-admin-modal-box').slideDown(150, 'swing');
        showOverlayModalContentBox(modalName, data);
    }

    function showOverlayModalContentBox(modalName, data=null) {
        if ( modalName == 'add-new-tile') {
            loadAddTileContentBox();
        }
        if ( modalName == 'edit-tile' ) {
            loadEditTileContentBox(data);
        }
        if ( modalName == 'edit-field' ) {
            loadEditFieldContentBox(data);
        }
    }

    function loadAddTileContentBox() {
        var post_type = window.field_settings.post_type;
        var modal_html_content = `
        <tr>
            <th colspan="2">
                <h3 class="modal-box-title">Add New Tile</h3>
            </th>
        </tr>
        <tr>
            <td><label><b>Post Type:</b></label></td>
            <td>${post_type}</td>
        </tr>
        <tr>
            <td>
                <label for="new_tile_name"><b>New Tile Name:</b></label>
            </td>
            <td>
                <input name="new_tile_name" id="new_tile_name" type="text" required>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button class="button" type="submit" id="js-add-tile">Create Tile</button>
            </td>
        </tr>`;
        $('#modal-overlay-content-table').html(modal_html_content);
    }

    function loadEditTileContentBox(tile_key) {
        var post_type = window.field_settings.post_type;
        API.get_tile(post_type, tile_key).promise().then(function(data) {
            var modal_html_content = `
            <tr>
                <th colspan="2">
                    <h3 class="modal-box-title">Edit '${data['label']}' Tile</h3>
                </th>
            </tr>
            <tr>
                <td>
                    <label><b>Post Type:</label></b>
                </td>
                <td>
                    ${post_type}
                </td>
            </tr>
            <tr>
                <td>
                    <label for="new_tile_name"><b>Key:</b></label>
                </td>
                <td>
                    ${tile_key}
                </td>
            </tr><tr>
            <td>
                <label for="tile_label"><b>Label:</b></label>
            </td>
            <td>
                <input name="edit-tile-label" id="edit-tile-label-${tile_key}" type="text" value="${data['label']}"required>
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

    function loadEditFieldContentBox(field_data) {
        var tile_key = field_data['tile_key'];
        var field_key = field_data['field_key'];
        var field_settings = window['field_settings']['post_type_settings']['fields'][field_key];
        var number_of_translations = 0; //Todo: softcode this variable
        
        var field_icon_image_html = '';
        if ( field_settings['icon'] ) {
            field_icon_image_html = `<img src="${field_settings['icon']}">`;
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
            </tr>
            <tr>
                <td>
                    <label><b>Default Name</b></label>
                </td>
                <td>
                    ${field_settings['name']}
                </td>
            </tr>
            <tr>
                <td>
                    <label for="edit-field-custom-name"><b>Custom Name</b></label>
                </td>
                <td>
                    <input name="edit-field-custom-name" id="edit-field-custom-name-${field_key}" type="text" value="">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="edit-field-private-field"><b>Private Field</b></label>
                </td>
                <td>
                    <input name="edit-field-private-field" id="edit-field-private-field-${field_key}" type="checkbox" disabled>
                </td>
            </tr>
            <tr>
                <td>
                    <b>Translation</b>
                </td>
                <td>
                    <button class="button small expand_translations" data-form_name="field-edit-form">
                        <img style="height: 15px; vertical-align: middle" src="${window.wpApiShare.template_dir}/dt-assets/images/languages.svg">
                        (${number_of_translations})
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="tile-select"><b>Tile</b></label>
                </td>
                <td>
                    <select name="tile-select">
                        <option value="no_tile">No tile / hidden</option>`;
                        $.each(window.field_settings.post_type_tiles, function (k, tile) {
                            if ( k === tile_key  ) {
                                modal_html_content += `<option value="${tile_key}" selected>${tile['label']}</option>`;
                            }
                            modal_html_content += `<option value="${tile_key}">${tile['label']}</option>`;
                        });
                modal_html_content += `
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="edit-field-description"><b>Description</b></label>
                </td>
                <td>
                    <input name="edit-field-description" id="edit-field-description-${field_key}" type="text" value="${field_settings['description']}">
                </td>
            </tr>
            <tr>
                <td>
                    <b>Description Translation</b>
                </td>
                <td>
                    <button class="button small expand_translations" data-form_name="field-edit-form">
                        <img style="height: 15px; vertical-align: middle" src="${window.wpApiShare.template_dir}/dt-assets/images/languages.svg">
                        (${number_of_translations})
                    </button>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="edit-field-custom-name"><b>Icon</b></label>
                </td>
                <td>
                    ${field_icon_image_html}
                    <input name="edit-field-icon" id="edit-field-icon-${field_key}" type="text" value="${field_settings['icon']}">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <button class="button" type="submit" id="js-edit-tile" data-tile-key="${field_key}">Save</button>
                </td>
            </tr>`;
        $('#modal-overlay-content-table').html(modal_html_content);   
    }

    $('#modal-overlay-form').on('submit', function(event){
        event.preventDefault();
    });

    $('#modal-overlay-form').on('click', '#js-add-tile', function(e) {
        var post_type = window.field_settings.post_type;
        var new_tile_name = $('#new_tile_name').val();

        API.create_new_tile(post_type, new_tile_name).promise().then(function(data) {
            var tile_key = data['key'];
            var tile_label = data['label'];
            window['field_settings']['post_type_tiles'][tile_key] = {'label':tile_label};
            closeModal();
            $('#add-new-tile-link').parent().before(`
            <div class="field-settings-table-tile-name expandable" data-modal="edit-tile" data-key="${tile_key}">
                <span class="sortable">⋮⋮</span>
                <span class="expand-icon">+</span>
                <span id="tile-key-${tile_key}" style="vertical-align: sub;">
                    ${tile_label}
                </span>
                <span class="edit-icon"></span>
            </div>
            <div style="display: none;">
                <div class="field-settings-table-field-name">
                    <span class="sortable">⋮⋮</span>
                    <span class="field-name-content" data-key="_add_new_field" data-parent-tile-key="${tile_key}">
                        add new field
                    </span>
                </div>
            </div>
            `);
        });
    });

    $('#modal-overlay-form').on('click', '#js-edit-tile', function(e) {
        var post_type = window.field_settings.post_type;
        var tile_key = $(this).data('tile-key');
        var tile_label = $(`#edit-tile-label-${tile_key}`).val();
        API.edit_tile(post_type, tile_key, tile_label).promise().then(function() {
            $(`#tile-key-${tile_key}`).html(tile_label);
            closeModal();
        });
    });

    function closeModal() {
        $('.dt-admin-modal-overlay').fadeOut(150, 'swing');
        $('.dt-admin-modal-box').slideUp(150, 'swing');
        $('#modal-overlay-content-table').html('');
    }
    
    $('.dt-admin-modal-box-close-button').on('click', function() {
        closeModal();
    });

    $('.dt-admin-modal-overlay').on('click', function(e) {
        if (e.target == this) {
            closeModal();
        }
    });

    $('.field-option-name').hover(
        function() {
            $(this).children('.edit-field-option').show()
        },
        function(){
            $(this).children('.edit-field-option').hide()
        }
    );

    $('.field-name').on('click', function() {
            $(this).find('.field-name-icon-arrow:not(.disabled)').toggleClass('arrow-expanded');
            $(this).find('.field-elements-list').slideToggle(333, 'swing');
    });

    // *** TYPEAHEAD : START ***
    var input_text = $('.js-typeahead-settings')[0].value;
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
                url: window.wpApiSettings.root+ 'dt-public/dt-core/v1/get-post-fields',
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
    // *** TYPEAHEAD : END ***
});