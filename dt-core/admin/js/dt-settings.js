jQuery(document).ready(function($) {
    function showAddNewFieldModal() {
        $('#add-new-field-modal-overview').fadeIn(150, 'swing');
        $('#add-new-field-modal-box').slideDown(150, 'swing');
    }
    
    function showAddNewFieldOptionModal() {
        $('#add-new-field-option-modal-overview').fadeIn(150, 'swing');
        $('#add-new-field-option-modal-box').slideDown(150, 'swing');
    }
    
    function showEditFieldOptionModal() {
        $('#edit-field-option-modal-overview').fadeIn(150, 'swing');
        $('#edit-field-option-modal-box').slideDown(150, 'swing');
    }

    function closeModals() {
        $('#add-new-field-modal-overview').fadeOut(150, 'swing');
        $('#add-new-field-modal-box').slideUp(150, 'swing');
        $('#add-new-field-option-modal-overview').fadeOut(150, 'swing');
        $('#add-new-field-option-modal-box').slideUp(150, 'swing');
        $('#edit-field-option-modal-overview').fadeOut(150, 'swing');
        $('#edit-field-option-modal-box').slideUp(150, 'swing');
    }

    $('.edit-option').on('click', function(e) {
            showEditFieldOptionModal();
    });
    
    $('.dt-admin-modal-box-close-button').on('click', function() {
        closeModals();
    });

    $('.dt-admin-modal-overlay').on('click', function(e) {
        if (e.target == this) {
            closeModals();
        }
    });

    $('.add-new-field').on('click', function(){
            showAddNewFieldModal();
    });

    $('.add-new-field-option').on('click', function(){
        showAddNewFieldOptionModal();
    });

    $('.field-name').hover(
        function() {
            $(this).children('.edit-option').show()
        },
        function(){
            $(this).children('.edit-option').hide()
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