jQuery(document).ready(function($) {
    function showOverlayModal() {
        $('.dt-admin-modal-overlay').fadeIn(150, 'swing');
        $('.dt-admin-modal-box').slideDown(150, 'swing');
    }

    function showOverlayModalContentBox(modalName) {
        if ( modalName == 'addNewField' ) {
            loadAddFieldContentBox();
        }
        showOverlayModal();
    }

    function loadAddFieldContentBox() {
        var post_type = window.field_settings.post_type
        var post_type_label = window.field_settings.post_type_label;
        var post_type_tiles = window.field_settings.post_type_tiles;
        var add_field_html_content = `
        <tr>
            <th colspan="2">
                <h3 class="modal-box-title">Add New Field</h3>
            </th>
        </tr>
        <tr>
            <td><b>Post Type:</b></td>
            <td>${post_type}</td>
        </tr>
        <tr>
            <td>
                <b>New Field Name:</b>
                </td>
            <td>
                <input name="new_field_name" id="new_field_name" required>
            </td>
        </tr>
        <tr>
            <td>
                <b>Field Type:</b>
                </td>
            <td>
                <select id="new_field_type_select" name="new_field_type" required>
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
        <tr>
            <td>
                <b>Private Field:</b>
                </td>
            <td>
                <input name="new_field_private" id="new_field_private" type="checkbox">
            </td>
        </tr>
        <tr>
            <td>
                <b>Tile:</b>
                </td>
            <td>
                <select name="new_field_tile">
                    <option>No tile</option>
                        <option disabled>---${post_type_label} tiles---</option>`;
                        
                        $.each(post_type_tiles, function(k,v){
                            add_field_html_content += `
                            <option value="${k}">
                                ${v['label']}
                            </option>`;
                        });
                        
                        add_field_html_content += `
                            <option value="<?php echo esc_html( $option_key ) ?>">
                                <?php echo esc_html( $option_value["label"] ?? $option_key ) ?>
                            </option>
                        <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="submit" class="button">Create Field</button>
            </td>
        </tr>
        `;

        $('#field-content-table').html(add_field_html_content);         
    }

    function closeModal() {
        $('.dt-admin-modal-overlay').fadeOut(150, 'swing');
        $('.dt-admin-modal-box').slideUp(150, 'swing');
    }

    $('.edit-field-option').on('click', function(e) {
            showModal('edit-field-option');
    });
    
    $('.dt-admin-modal-box-close-button').on('click', function() {
        closeModal();
    });

    $('.dt-admin-modal-overlay').on('click', function(e) {
        if (e.target == this) {
            closeModal();
        }
    });

    $('.add-new-field').on('click', function(){
        showOverlayModalContentBox('addNewField');
    });

    $('.field-name').hover(
        function() {
            $(this).children('.edit-field').show()
        },
        function(){
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

    $('.field-name').on('click', function(e) {
            $(this).find('.field-name-icon-arrow').toggleClass('arrow-expanded');
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