jQuery(document).ready(function ($) {
  $('.expand_translations').click(function (e) {
    e.preventDefault();
    display_translation_dialog($(this).siblings(), $(this).data('form_name'), $(this).data('source'));
  });

  $('.change-icon-button').click(function (e) {
    e.preventDefault();

    // Fetch handle to key workflow elements
    let parent_form = $("form[name='" + $(e.currentTarget).data('form') + "']");
    let icon_input = $("input[name='" + $(e.currentTarget).data('icon-input') + "']");

    // Display icon selector dialog
    display_icon_selector_dialog(parent_form, icon_input);
  });

  // Support DT customization icon picker requests.
  $('.dt-admin-modal-box').on('click', '.change-icon-button', function (e) {
    let icon_input = $("input[name='" + $(e.currentTarget).data('icon-input') + "']");
    let dialog = $('#dt_icon_selector_dialog');

    if (dialog) {

      dialog.dialog({
        modal: false,
        autoOpen: false,
        hide: 0,
        show: 0,
        height: 'auto',
        width: 'auto',
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
            }
          },
          {
            text: 'Upload Custom Icon',
            icon: 'ui-icon-circle-zoomout',
            click: function () {
            }
          }
        ],
        open: function (event, ui) {
          let ui_dialog = $(document).find('.ui-dialog')[0];

          // Fetch and set font icon picker dialog contents.
          if (ui_dialog) {

            let cloned = $(ui_dialog).clone();
            let html = `
              <table>
                <tbody>
                    <tr>
                        <td>
                        ${$(cloned).find('.ui-dialog-titlebar').html()}
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <br>
                        ${$(cloned).find('.ui-dialog-content').html()}
                        </td>
                    </tr>
                    <tr>
                        <td>
                        <br>
                        ${$(cloned).find('.ui-dialog-buttonpane').html()}
                        </td>
                    </tr>
                </tbody>
              </table>
            `;

            // Set some initial defaults to aid downstream processing.
            let content = $('.dt-admin-modal-icon-picker-box-content');
            content.html(html);
            content.find('button.ui-dialog-titlebar-close').hide();
            content.find('span.ui-dialog-title').css('font-weight', 'bold');
            content.find('button.ui-button').data('icon-input', icon_input.attr('name'));

          }

          // Force an immediate close, to draw attention to flipped content!
          $(this).dialog('close');

          // Display some initial icons
          execute_icon_selection_filter_query(false);
        },
        close: function (event, ui) {
        }
      });

      // Insert selection area div, within dialog button footer
      let ui_dialog_buttonset = $('.ui-dialog-buttonset');
      ui_dialog_buttonset.css('margin', '1.5em');
      ui_dialog_buttonset.prepend($('<span>')
        .attr('id', 'dialog_icon_selector_icon_selection_div')
        .css('display', 'inline-block')
        .css('vertical-align', 'middle')
        .css('padding', '0')
        .css('margin-right', '175px')
      );

      // Display updated dialog
      dialog.dialog('open');

    }
  });

  $('.dt-admin-modal-box').on('click', '.ui-button', function (e) {

    // Determine action to be taken.
    let button = $(e.currentTarget);
    let icon_input = $("input[name='" + $(button).data('icon-input') + "']");
    let close_button = button.find('span.ui-icon-close');
    let save_button = button.find('span.ui-icon-copy');
    let upload_button = button.find('span.ui-icon-circle-zoomout');

    if (close_button && close_button.length > 0) {
      $('.dt-admin-modal-icon-picker-box-close-button').click();

    } else if (save_button && save_button.length > 0) {
      handle_icon_save(null, null, icon_input, function (source) {

        // Refresh icon image accordingly, to capture any changes.
        let icon_img_wrapper = $(icon_input).parent().find('.field-icon-wrapper');
        if (icon_img_wrapper) {
          let icon = $(icon_input).val();
          $(icon_img_wrapper).html((icon && icon.trim().toLowerCase().startsWith('mdi') ? `<i class="${icon} field-icon" style="font-size: 30px; vertical-align: middle;"></i>`:`<img src="${icon}" class="field-icon" style="vertical-align: middle;">`));
        }

        $('.dt-admin-modal-icon-picker-box-close-button').click();

      });

    } else if (upload_button && upload_button.length > 0) {
      handle_icon_upload(null, null, icon_input, function (source) {

        // Refresh icon image accordingly, to capture any changes.
        let icon_img_wrapper = $(icon_input).parent().find('.field-icon-wrapper');
        if (icon_img_wrapper) {
          let icon = $(icon_input).val();
          $(icon_img_wrapper).html((icon && icon.trim().toLowerCase().startsWith('mdi') ? `<i class="${icon} field-icon" style="font-size: 30px; vertical-align: middle;"></i>`:`<img src="${icon}" class="field-icon" style="vertical-align: middle;">`));
        }

        $('.dt-admin-modal-icon-picker-box-close-button').click();

      });

    }

  });

  /**
   * Icon selector modal dialog - Process icon selection filter queries & selections
   */

  $(document).on('keyup', '#dialog_icon_selector_filter_input', function (e) {
    let code = (e.keyCode || e.which);

    // Only get excited over specific key codes.
    if ((code===8) ||
      (code===13) ||
      ((code >= 48) && (code <= 90))) {

      execute_icon_selection_filter_query();
    }
  });

  $(document).on('click', '.dialog-icon-selector-icon', function (e) {
    handle_icon_selection($(e.currentTarget));
  });

  // Load available icon class names, ahead of further downstream processing
  let icons = build_icon_class_name_list();

  /**
   * Translation modal dialog
   */

  function display_translation_dialog(container, form_name, source = '') {
    let dialog = $('#dt_translation_dialog');
    if (container && form_name && dialog) {

      // Update dialog div
      $(dialog).empty().append($($(container).find('table')[0]).clone());

      // Refresh dialog config
      dialog.dialog({
        modal: true,
        autoOpen: false,
        hide: 'fade',
        show: 'fade',
        height: 1000,
        width: 'auto',
        resizable: true,
        title: 'Translation Dialog',
        buttons: {
          Update: function () {

            // Update source translation container
            $(container).empty().append($(this).children());

            // Close dialog
            $(this).dialog('close');

            // Finally, auto save changes, accordingly, based on source.
            if (window.lodash.includes(['fields'], source)) {
              handle_custom_field_save_request(null, $('.dt-custom-fields-save-button')[0], true);
            } else {
              $('form[name="' + form_name + '"]').submit();
            }

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

  function display_icon_selector_dialog(parent_form, icon_input, callback = function (source) {
  }) {
    let dialog = $('#dt_icon_selector_dialog');
    if (dialog) {

      // Refresh dialog config
      dialog.dialog({
        modal: true,
        autoOpen: false,
        hide: 'fade',
        show: 'fade',
        height: 'auto',
        width: 'auto',
        resizable: false,
        title: 'Icon Selector Dialog',
        buttons: [
          {
            text: 'Cancel',
            icon: 'ui-icon-close',
            click: function () {
              $(this).dialog('close');
              callback('cancel');
            }
          },
          {
            text: 'Save',
            icon: 'ui-icon-copy',
            click: function () {
              handle_icon_save(this, parent_form, icon_input, callback);
            }
          },
          {
            text: 'Upload Custom Icon',
            icon: 'ui-icon-circle-zoomout',
            click: function () {
              handle_icon_upload(this, parent_form, icon_input, callback);
            }
          }
        ],
        open: function (event, ui) {

          // Display some initial icons
          execute_icon_selection_filter_query();
        },
        close: function (event, ui) {
          callback('dialogclose');
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

  function execute_icon_selection_filter_query(enable_tooltips = true) {

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

        // If requested, activate icon tooltips
        if (enable_tooltips) {
          $('#dialog_icon_selector_icons_table > tbody').find('.mdi').each(function (idx, icon) {
            $(icon).tooltip({
              show: {effect: 'fade', duration: 100}
            });
          });
        }

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

  function handle_icon_save(dialog, parent_form, icon_input, callback = function (source) {
  }) {

    // Determine if there is a valid selection
    let selected_icon = $('#dialog_icon_selector_icon_selection_div').find('.dialog-icon-selector-icon-selected');
    if ($(selected_icon).length) {

      // Update form icon class input
      icon_input.val('mdi ' + $(selected_icon).data('icon_class'));

      // If present, close dialog
      if (dialog) {
        $(dialog).dialog('close');
      }

      // If present, auto-submit; to refresh changes
      if (parent_form) {
        parent_form.submit();
      }

      // Execute callback with relevant source flag.
      callback('save');
    }
  }

  /**
   * Icon selector modal dialog - Handle Icon Uploads
   */

  function handle_icon_upload(dialog, parent_form, icon_input, callback = function (source) {
  }) {

    // Build media uploader modal
    let mediaFrame = window.wp.media({

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

      // If present, close dialog
      if (dialog) {
        $(dialog).dialog('close');
      }

      // If present, auto-submit; to refresh changes
      if (parent_form) {
        parent_form.submit();
      }

      // Execute callback with relevant source flag.
      callback('upload');

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

  /**
   * Tile Display Conditions - [START]
   */

  $(document).on('click', 'input:radio[name="tile_display_option"]', function (e) {
    handle_tile_display_condition_selection($(e.currentTarget));
  });

  function handle_tile_display_condition_selection(display_condition) {
    let show_custom = display_condition && $(display_condition).val() == 'custom';
    let custom_elements = $('#tile_display_custom_elements');
    show_custom ? $(custom_elements).slideDown('slow') : $(custom_elements).slideUp('slow');
  }

  /**
   * Tile Display Conditions - [END]
   */

  /**
   * Tile Display Help Modal - [START]
   */

  $(document).on('click', '.help-button', function (e) {
    handle_tile_display_help_modal($(e.currentTarget));
  });

  function handle_tile_display_help_modal(help_button) {
    let dialog = $('#' + $(help_button).data('dialog_id'));
    if (dialog) {

      // Refresh help dialog config
      dialog.dialog({
        modal: true,
        autoOpen: false,
        hide: 'fade',
        show: 'fade',
        height: 600,
        width: 450,
        resizable: true,
        title: 'Help Dialog',
        buttons: [
          {
            text: 'OK',
            icon: 'ui-icon-check',
            click: function () {
              $(this).dialog('close');
            }
          }
        ],
        open: function (event, ui) {
        }
      });

      // Display help dialog
      dialog.dialog('open');

    } else {
      console.log('Unable to reference a valid: [dialog]');
    }
  }

  /**
   * Tile Display Help Modal - [END]
   */

  /**
   * Alternative Save Flow - [START]
   */

  $(document).on('click', '.dt-custom-fields-save-button', function (e) {
    handle_custom_field_save_request(e, $(e.currentTarget), false);
  });

  function handle_custom_field_save_request(event, save_button, translate_update_only) {

    // If defined, short-circuit default save flow and adopt ajax approach if needed.
    if (event) {
      event.preventDefault();
    }

    // Determine which save path is to be taken.
    if (!translate_update_only) {
      $('form[name="' + $(save_button).data('form_id') + '"]').submit();
    } else {

      // Always capture field parent level name & description translations; which is present across all fields.
      let payload = {
        'post_type': $(save_button).data('post_type'),
        'field_id': $(save_button).data('field_id'),
        'field_type': $(save_button).data('field_type'),
        'translations': package_custom_field_translations($(save_button).data('field_id')),
        'option_translations': window.lodash.includes(['key_select', 'multi_select', 'link'], $(save_button).data('field_type')) ? package_custom_field_option_translations():[]
      };

      // Have core endpoint process field translations accordingly.
      $.ajax({
        type: 'POST',
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        data: JSON.stringify(payload),
        url: `${window.dt_admin_scripts.rest_root}dt-admin/scripts/update_custom_field_translations`,
        beforeSend: (xhr) => {
          xhr.setRequestHeader('X-WP-Nonce', window.dt_admin_scripts.nonce);
        }
      }).done(function (response) {

        // Update translation counts.
        $('#custom_name_translation_count').html((response['translations']) ? Object.keys(response['translations']).length:0);
        $('#custom_description_translation_count').html((response['description_translations']) ? Object.keys(response['description_translations']).length:0);
        if ((response['defaults']) && window.lodash.includes(['key_select', 'multi_select', 'link'], $(save_button).data('field_type'))) {
          $('.sortable-field-options').find('tr.ui-sortable-handle').each(function (idx, tr) {
            let option_key = $(tr).find('.sortable-field-options-key').text().trim();
            $(tr).find('#option_name_translation_count').html((response['defaults'] && response['defaults'][option_key] && response['defaults'][option_key]['translations']) ? Object.keys(response['defaults'][option_key]['translations']).length:0);
            $(tr).find('#option_description_translation_count').html((response['defaults'] && response['defaults'][option_key] && response['defaults'][option_key]['description_translations']) ? Object.keys(response['defaults'][option_key]['description_translations']).length:0);
          });
        }

      }).fail(function (error) {
        console.log("error");
        console.log(error);
      });
    }
  }

  function package_custom_field_translations(field_id) {
    let packaged_translations = {
      'translations': [],
      'description_translations': []
    };

    // Locate field name translations.
    let field_name_prefix = 'field_key_' + field_id + '_translation-';
    $("input[id^='" + field_name_prefix + "']").each(function (idx, input) {
      let locale = window.lodash.split($(input).attr('id'), '-')[1];
      let value = $(input).val();
      if (locale && value) {
        packaged_translations['translations'].push({
          'locale': locale,
          'value': value
        });
      }
    });

    // Locate field description translations.
    let field_description_prefix = 'field_description_translation-';
    $("input[id^='" + field_description_prefix + "']").each(function (idx, input) {
      let locale = window.lodash.split($(input).attr('id'), '-')[1];
      let value = $(input).val();
      if (locale && value) {
        packaged_translations['description_translations'].push({
          'locale': locale,
          'value': value
        });
      }
    });

    return packaged_translations;
  }

  function package_custom_field_option_translations() {
    let packaged_translations = [];

    $('.sortable-field-options').find('tr.ui-sortable-handle').each(function (idx, tr) {
      let translations = {
        'option_key': '',
        'option_translations': [],
        'option_description_translations': []
      };

      // Determine option key.
      let option_key = $(tr).find('.sortable-field-options-key').text().trim();
      if (option_key) {
        translations['option_key'] = option_key;

        // Locate option key translations.
        let option_key_prefix = 'field_option_' + option_key + '_translation-';
        $(tr).find("input[id^='" + option_key_prefix + "']").each(function (okt_idx, okt_input) {
          let locale = window.lodash.split($(okt_input).attr('id'), '-')[1];
          let value = $(okt_input).val();
          if (locale && value) {
            translations['option_translations'].push({
              'locale': locale,
              'value': $(okt_input).val()
            });
          }
        });

        // Locate option key description translations.
        let option_key_description_prefix = 'option_description_' + option_key + '_translation-';
        $(tr).find("input[id^='" + option_key_description_prefix + "']").each(function (okdt_idx, okdt_input) {
          let locale = window.lodash.split($(okdt_input).attr('id'), '-')[1];
          let value = $(okdt_input).val();
          if (locale && value) {
            translations['option_description_translations'].push({
              'locale': locale,
              'value': $(okdt_input).val()
            });
          }
        });

        // Package recent translations.
        packaged_translations.push(translations);
      }
    });

    return packaged_translations;
  }

  /**
   * Alternative Save Flow - [END]
   */


})
