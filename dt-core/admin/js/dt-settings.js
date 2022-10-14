jQuery(document).ready(function($) {
    $('#add-new-tile-link').on('click', function(event){
        event.preventDefault();
        showOverlayModal();
        showOverlayModalContentBox('addNewTile');
    });
    function showOverlayModal() {
        $('.dt-admin-modal-overlay').fadeIn(150, 'swing');
        $('.dt-admin-modal-box').slideDown(150, 'swing');
    }

    function showOverlayModalContentBox(modalName) {
        if ( modalName == 'addNewTile') {
            loadAddTileContentBox();
        }
        showOverlayModal();
    }

    function loadAddTileContentBox() {
        var post_type = window.field_settings.post_type;
        var add_field_html_content = `
        <tr>
            <th colspan="2">
                <h3 class="modal-box-title">Add New Tile</h3>
            </th>
        </tr>
        <tr>
            <td><label>Post Type:</label></td>
            <td>${post_type}</td>
        </tr>
        <tr>
            <td>
                <label for="new_tile_name">New Tile Name:</label>
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
        $('#modal-overlay-content-table').html(add_field_html_content);       
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
            $('#add-new-tile-link').before(`
            <li>
                <a href="admin.php?page=dt_customizations&post_type=${window.field_settings.post_type}&tab=tiles&post_tile_key=${tile_key}">${tile_value}</a>
            </li>
            `);
        });
    });

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