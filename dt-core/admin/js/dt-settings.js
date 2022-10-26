jQuery(document).ready(function($) {
    $('.field-settings-table-tile-name').on('click', function() {
        var tile_key = $(this).data('tile-key');
        show_preview_tile(tile_key);
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

    $("div[class*='expandable']").on('click', function() {
        $(this).next().slideToggle(333, 'swing');
        if ($(this).children('.expand-icon').text() === '+'){
            $(this).children('.expand-icon').text('-');
        } else {
            $(this).children('.expand-icon').text('+');
        }
    });

    $('#add-new-tile-link').on('click', function(event){
        event.preventDefault();
        showOverlayModal('addNewTile');
    });

    function showOverlayModal(modalName, data=null) {
        $('.dt-admin-modal-overlay').fadeIn(150, 'swing');
        $('.dt-admin-modal-box').slideDown(150, 'swing');
        showOverlayModalContentBox(modalName, data);
    }

    function showOverlayModalContentBox(modalName, data=null) {
        if ( modalName == 'addNewTile') {
            loadAddTileContentBox();
        }
        if ( modalName == 'editTile' ) {
            loadEditTileContentBox(data);
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
                <button class="button" type="submit" id="js-create-tile">Create Tile</button>
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

    $('#modal-overlay-form').on('submit', function(event){
        event.preventDefault();
    });

    $('#modal-overlay-form').on('click', '#js-create-tile', function(e) {
        var post_type = window.field_settings.post_type;
        var new_tile_name = $('#new_tile_name').val();

        API.create_new_tile(post_type, new_tile_name).promise().then(function(data) {
            var tile_key = data['key'];
            var tile_value = data['label'];
            closeModal();
            $('#add-new-tile-link').parent().before(`
            <li>
                <a href="admin.php?page=dt_customizations&post_type=${window.field_settings.post_type}&tab=tiles&post_tile_key=${tile_key}">${tile_value}</a>
                <svg style="width:24px;height:24px;margin-left:6px;vertical-align:sub;" viewBox="0 0 24 24">
                    <path fill="green" d="M20,4C21.11,4 22,4.89 22,6V18C22,19.11 21.11,20 20,20H4C2.89,20 2,19.11 2,18V6C2,4.89 2.89,4 4,4H20M8.5,15V9H7.25V12.5L4.75,9H3.5V15H4.75V11.5L7.3,15H8.5M13.5,10.26V9H9.5V15H13.5V13.75H11V12.64H13.5V11.38H11V10.26H13.5M20.5,14V9H19.25V13.5H18.13V10H16.88V13.5H15.75V9H14.5V14A1,1 0 0,0 15.5,15H19.5A1,1 0 0,0 20.5,14Z" />
                </svg>
            </li>
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
    }

    $('.edit-field-option').on('click', function(e) {
            // showModal('edit-field-option');
    });
    
    $('.dt-admin-modal-box-close-button').on('click', function() {
        closeModal();
    });

    $('.dt-admin-modal-overlay').on('click', function(e) {
        if (e.target == this) {
            closeModal();
        }
    });

    $('.tile-name').hover(
        function() {
            $(this).children('.edit-tile').show()
        },
        function() {
            $(this).children('.edit-tile').hide()
        }
    );

    $('.field-name').hover(
        function() {
            $(this).children('.edit-field').show()
        },
        function() {
            $(this).children('.edit-field').hide()
        }
    );

    $('.field-option-name').hover(
        function() {
            $(this).children('.edit-field-option').show()
        },
        function(){
            $(this).children('.edit-field-option').hide()
        }
    );
    
    $('.edit-tile').on('click', function() {
        showOverlayModal('editTile', $(this).data('tile-key'));
    });

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