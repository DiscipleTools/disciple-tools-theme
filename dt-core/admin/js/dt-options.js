jQuery(document).ready(function ($) {
  $('.expand_translations').click(function () {
    event.preventDefault();
    display_translation_dialog($(this).siblings(), $(this).data('form_name'));
  });

  $('.change-icon-button').click(function (e) {
    e.preventDefault();

    // Fetch handle to key workflow elements
    let parent_form = $("form[name='" + $(e.currentTarget).data('form') + "']");
    let icon_input = $("input[name='" + $(e.currentTarget).data('icon-input') + "']");

    // Display icon selector dialog
    display_icon_selector_dialog(parent_form, icon_input);
  });

  /**
   * Icon selector modal dialog - Process icon selection filter queries & selections
   */

  $(document).on('keyup', '#dialog_icon_selector_filter_input', function (e) {
    execute_icon_selection_filter_query();
  });

  $(document).on('click', '.dialog-icon-selector-icon', function (e) {
    handle_icon_selection($(e.currentTarget));
  });

  // Load available icon class names, ahead of further downstream processing
  let icons = build_icon_class_name_list();

  /**
   * Translation modal dialog
   */

  function display_translation_dialog(container, form_name) {
    let dialog = $('#dt_translation_dialog');
    if (container && form_name && dialog) {

      // Update dialog div
      $(dialog).empty().append($(container).find('table').clone());

      // Refresh dialog config
      dialog.dialog({
        modal: true,
        autoOpen: false,
        hide: 'fade',
        show: 'fade',
        height: 600,
        width: 350,
        resizable: false,
        title: 'Translation Dialog',
        buttons: {
          Update: function () {

            // Update source translation container
            $(container).empty().append($(this).children());

            // Close dialog
            $(this).dialog('close');

            // Finally, auto save changes
            $('form[name="' + form_name + '"]').submit();

          }
        }
      });

      // Display updated dialog
      dialog.dialog('open');

    } else {
      console.log('Unable to reference a valid: [container, form-name, dialog]');
    }
  }

  /**
   * Icon selector modal dialog
   */

  function display_icon_selector_dialog(parent_form, icon_input) {
    let dialog = $('#dt_icon_selector_dialog');
    if (dialog) {

      // Refresh dialog config
      dialog.dialog({
        modal: true,
        autoOpen: false,
        hide: 'fade',
        show: 'fade',
        height: 600,
        width: 750,
        resizable: false,
        title: 'Icon Selector Dialog',
        buttons: [
          {
            text: 'Cancel',
            icon: 'ui-icon-close',
            click: function () {
              $(this).dialog('close');
            }
          },
          {
            text: 'Save',
            icon: 'ui-icon-copy',
            click: function () {
              handle_icon_save(this, parent_form, icon_input);
            }
          },
          {
            text: 'Upload Custom Icon',
            icon: 'ui-icon-circle-zoomout',
            click: function () {
              handle_icon_upload(this, parent_form, icon_input);
            }
          }
        ],
        open: function (event, ui) {

          // Display some initial icons
          execute_icon_selection_filter_query();
        }
      });

      // Insert selection area div, within dialog button footer
      $('.ui-dialog-buttonset').prepend($('<span>')
        .attr('id', 'dialog_icon_selector_icon_selection_div')
        .css('display', 'inline-block')
        .css('vertical-align', 'middle')
        .css('padding', '0')
        .css('margin-right', '175px')
      );

      // Display updated dialog
      dialog.dialog('open');

    } else {
      console.log('Unable to reference a valid: [dialog]');
    }
  }

  /**
   * Icon selector modal dialog - Build Icon Class Name List
   */

  function build_icon_class_name_list() {
    let icon_class_names = [];
    $.each(document.styleSheets, function (idx, style_sheet) {
      if (window.lodash.includes(style_sheet.href, 'dt-core/dependencies/mdi/css/materialdesignicons.min.css')) {
        $.each(style_sheet.cssRules, function (key, rule) {
          if (rule.constructor.name === 'CSSStyleRule') {
            icon_class_names.push({
              class: rule.selectorText.substring(1, rule.selectorText.indexOf(':'))
            });
          }
        });
      }
    });

    /*
     * If filtering search performance becomes an issue, comment the below return statement
     * and swicth to returning the sliced, reshuffled dataset below.
     */

    return icon_class_names;

    /*
     * Due to the large (6K+) icon data set, a re-shuffled (1K) sample set will
     * be returned at any given time; to aid performance!
     *
     * Todo:
     *  In order to accommodate the entire data set, an indexed based search
     *  framework could be introduced.
     */

    // return window.lodash.slice(window.lodash.shuffle(icon_class_names), 0, 1000);
  }

  /**
   * Icon selector modal dialog - Execute Filtering Request
   */

  function execute_icon_selection_filter_query() {

    // Always default to a somewhat wildcard search if input text is blank
    let query = $('#dialog_icon_selector_filter_input').val().trim();
    query = window.lodash.isEmpty(query) ? 'a' : query;

    // Proceed with icon display refresh
    $('#dialog_icon_selector_icons_div').fadeOut('fast', function () {
      $('#dialog_icon_selector_icons_search_msg').text('').fadeOut('fast');
      $('#dialog_icon_selector_icons_search_spinner').addClass('active').fadeIn('fast', function () {

        // Clear currently displayed icons
        $('#dialog_icon_selector_icons_table > tbody > tr').remove();

        // Obtain filtered icon list
        let filtered_icons = window.lodash.filter(icons, function (icon) {
          return icon['class'] && window.lodash.includes(icon['class'], query);
        });

        // Truncate filtered list for performance purposes
        filtered_icons = filtered_icons.slice(0, 200);

        // Populate icons table
        let loop_counter = 0;
        let icon_counter = 0;
        let tds = '';

        $.each(filtered_icons, function (idx, filtered_icon) {
          loop_counter++;

          let icon_class_name = filtered_icon['class'];
          if (icon_class_name && is_icon_valid(icon_class_name)) {
            tds += '<td><i title="' + icon_class_name + '" class="dialog-icon-selector-icon mdi ' + icon_class_name + '" data-icon_class="' + icon_class_name + '"></i></td>'

            if ((++icon_counter > 5) || (loop_counter >= filtered_icons.length)) {
              $('#dialog_icon_selector_icons_table > tbody').append('<tr>' + tds + '</tr>');
              icon_counter = 0;
              tds = '';
            }
          }
        });

        // Activate icon tooltips
        $('#dialog_icon_selector_icons_table > tbody').find('.mdi').each(function (idx, icon) {
          $(icon).tooltip({
            show: {effect: 'fade', duration: 100}
          });
        });

        $('#dialog_icon_selector_icons_search_spinner').removeClass('active').fadeOut('fast', function () {

          // Display results or no icons found message
          if (filtered_icons.length > 0) {
            $('#dialog_icon_selector_icons_div').fadeIn('fast');

          } else {
            $('#dialog_icon_selector_icons_search_msg').text('No Icons Found').fadeIn('fast');
          }

        });
      });
    });
  }

  /**
   * Icon selector modal dialog - Determine Icon Validity
   */

  function is_icon_valid(icon_class_name) {

    // Firstly, empty sandbox...
    $('#dialog_icon_selector_icons_sandbox_div').empty();

    // Add corresponding icon
    let icon = $('<i>')
      .addClass('mdi ' + icon_class_name)
      .appendTo('#dialog_icon_selector_icons_sandbox_div');

    // Determine icon validity
    let valid = window.getComputedStyle(icon[0], ':before')['content'] !== 'none';

    // Clear down sandbox and return findings
    $('#dialog_icon_selector_icons_sandbox_div').empty();

    return valid;
  }

  /**
   * Icon selector modal dialog - Handle Icon Selections
   */

  function handle_icon_selection(icon) {
    if (icon) {

      // Create a clone element, to be assigned to dialog footer
      let cloned_icon = $(icon).clone(true);

      // Using some fancy transitions, assign new cloned selection
      $('#dialog_icon_selector_icon_selection_div').fadeOut('fast', function () {

        // Clear out previous selections
        $('#dialog_icon_selector_icon_selection_div').empty();

        // Make use of selection css class
        $(cloned_icon).removeClass('dialog-icon-selector-icon');
        $(cloned_icon).addClass('dialog-icon-selector-icon-selected');
        $(cloned_icon).attr('title', $(icon).data('icon_class'));

        // Append and display selection
        $('#dialog_icon_selector_icon_selection_div').append($(cloned_icon));
        $('#dialog_icon_selector_icon_selection_div').fadeIn('fast');

      });
    }
  }

  /**
   * Icon selector modal dialog - Handle Icon Save
   */

  function handle_icon_save(dialog, parent_form, icon_input) {

    // Determine if there is a valid selection
    let selected_icon = $('#dialog_icon_selector_icon_selection_div').find('.dialog-icon-selector-icon-selected');
    if ($(selected_icon).length) {

      // Update form icon class input
      icon_input.val('mdi ' + $(selected_icon).data('icon_class'));

      // Close dialog
      $(dialog).dialog('close');

      // Auto-submit so as to refresh changes
      parent_form.submit();
    }
  }

  /**
   * Icon selector modal dialog - Handle Icon Uploads
   */

  function handle_icon_upload(dialog, parent_form, icon_input) {

    // Build media uploader modal
    let mediaFrame = wp.media({

      // Accepts [ 'select', 'post', 'image', 'audio', 'video' ]
      // Determines what kind of library should be rendered.
      frame: 'select',

      // Modal title.
      title: window.lodash.escape(window.dt_admin_scripts.upload.title),

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
        text: window.lodash.escape(window.dt_admin_scripts.upload.button_txt)
      }

    });

    // Handle selected files
    mediaFrame.on('select', function () {

      // Fetch and convert selected into json object
      let selected = mediaFrame.state().get('selection').first().toJSON();

      // Update form icon link
      icon_input.val(selected.url);

      // Close dialog
      $(dialog).dialog('close');

      // Auto-submit so as to refresh changes
      parent_form.submit();

    });

    // Open the media uploader.
    mediaFrame.open();
  }

  /**
   * Sorting code for tiles
   */
  $( ".connectedSortable" ).sortable({
    connectWith: ".connectedSortable",
    placeholder: "ui-state-highlight"
  }).disableSelection();

  $( "#sort-tiles" ).sortable({
    items: "div.sort-tile:not(.disabled-drag)",
    placeholder: "ui-state-highlight",
    cancel: ".connectedSortable",
  }).disableSelection();

  $(".save-drag-changes").on( "click", function (){
    let order = [];
    $(".sort-tile").each((a, b)=>{
      let tile_key = $(b).attr("id")
      let tile = {
        key: tile_key,
        fields: []
      }
      $(`#${tile_key} .connectedSortable li`).each((field_index, field)=>{
        tile.fields.push($(field).attr('id'))
      })
      order.push(tile)
    })
    let input = $("<input>")
               .attr("type", "hidden")
               .attr("name", "order").val(JSON.stringify(order));
    $('#tile-order-form').append(input).submit();

  })


  /**
   * new fields
   */
  //show more fields when connection option selected

  $('#new_field_type_select').on('change', function (){
    if ( this.value === "connection" ){
      $('.connection_field_target_row').show()
      $('#private_field_row').hide()
      $('#connection_field_target').prop('required', true);
    } else {
      $('.connection_field_reverse_row').hide()
      $('.connection_field_target_row').hide()
      $('#private_field_row').show()
      $('#connection_field_target').prop('required', false);
    }
  })

  //show the reverse connection field name row if the post type is not "self"
  $('#connection_field_target').on("change", function (){
    let post_type_label = $( "#connection_field_target option:selected" ).text();
    $('.connected_post_type').html(post_type_label)
    if ( this.value === $('#current_post_type').val()){
      $('.same_post_type_other_field_name').toggle(!$('#multidirectional_checkbox').is(':checked'))
      $('.connection_field_reverse_row').hide()
      $('.same_post_type_row').show()
    } else {
      $('.same_post_type_other_field_name').hide()
      $('.connection_field_reverse_row').show()
      $('.same_post_type_row').hide()
    }
  })


  $('#multidirectional_checkbox').on("change", function (){
    $('.same_post_type_other_field_name').toggle(!this.checked)
  })

  /**
   * Sorting code for field options
   */

  $('.sortable-field-options').sortable({
    connectWith: '.sortable-field-options',
    placeholder: 'ui-state-highlight',
    update: function (evt, ui) {

      let updated_field_options_ordering = [];

      // Snapshot updated field options ordering by key.
      $('.sortable-field-options').find('.sortable-field-options-key').each(function (idx, key_div) {
        let key = $(key_div).text().trim();
        if (key) {
          updated_field_options_ordering.push(encode_field_key_special_characters(key));
        }
      });

      // Persist updated field options ordering.
      $('#sortable_field_options_ordering').val(JSON.stringify(updated_field_options_ordering));

    }
  }).disableSelection();

  function encode_field_key_special_characters(key) {
    key = window.lodash.replace(key, '<', '_less_than_');
    key = window.lodash.replace(key, '>', '_more_than_');

    return key;
  }

})
