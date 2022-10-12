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
        $('.modal-box-title').text('Add New Field');
        $('.field-content-table').append('<tr><td><b>Foo:</b></td><td>Bar</td></tr>');
        
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