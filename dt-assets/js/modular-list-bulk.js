'use strict';
/**
 * Bulk Edit Operations for Modular List
 * Handles bulk edit, bulk delete, bulk messaging functionality
 */
(function ($, list_settings, Foundation) {
  // Wait for DT_List to be available
  if (!window.DT_List) {
    console.error(
      'DT_List namespace not found. modular-list.js must be loaded first.',
    );
    return;
  }

  const DT_List = window.DT_List;

  // Bulk Edit Field Selection state
  let bulkEditSelectedFields = [];

  // Helper to get bulkEditSelectedFields
  function getBulkEditSelectedFields() {
    return bulkEditSelectedFields;
  }

  /***
   * Bulk Edit
   */

  $('#bulk_edit_controls').on('click', function () {
    $('#bulk_edit_picker').toggle();
    $('#records-table').toggleClass('bulk_edit_on');
  });

  // Old bulk_edit_seeMore handler removed - replaced with dynamic field selection

  function bulk_edit_checkbox_event() {
    $('tbody tr td.bulk_edit_checkbox').on('click', function (e) {
      e.stopImmediatePropagation();
      bulk_edit_count();
    });
  }

  $('#bulk_edit_master').on('click', function (e) {
    e.stopImmediatePropagation();
    let checked = $(this).children('input').is(':checked');
    $('.bulk_edit_checkbox input').each(function () {
      $(this).prop('checked', checked);
      bulk_edit_count();
    });
  });

  /***
   * Bulk Delete
   */

  let bulk_edit_delete_submit_button = $('#bulk_edit_delete_submit');
  bulk_edit_delete_submit_button.on('click', function (e) {
    let bulk_edit_total_checked = $(
      '.bulk_edit_checkbox:not(#bulk_edit_master) input:checked',
    ).length;

    $('#bulk_edit_delete_submit-spinner').addClass('active');

    if (bulk_edit_total_checked > 0) {
      let bulk_edit_delete_submit_button_span = $(
        '.bulk_edit_delete_submit_text',
      );
      let confirm_text = `${$(bulk_edit_delete_submit_button_span).data('pretext')} ${bulk_edit_total_checked} ${$(bulk_edit_delete_submit_button_span).data('posttext')}`;
      let confirm_text_capitalise = window.lodash.startCase(
        window.lodash.toLower(confirm_text),
      );

      if (
        list_settings.permissions.delete_any &&
        confirm(confirm_text_capitalise)
      ) {
        bulk_delete_submit();
      } else {
        window.location.reload();
      }
    } else {
      window.location.reload();
    }
  });

  function bulk_delete_submit() {
    // Build queue of post ids.
    let queue = [];
    $('.bulk_edit_checkbox input').each(function () {
      if (this.checked && this.id !== 'bulk_edit_master_checkbox') {
        let postId = parseInt($(this).val());
        queue.push(postId);
      }
    });
    process(queue, 10, do_each, do_done, {}, null, {}, 'delete');
  }

  /**
   * Bulk_Assigned_to
   */
  let bulk_edit_submit_button = $('#bulk_edit_submit');

  // Prevent form submission (button handles it via JavaScript)
  $('#bulk_edit_picker').on('submit', function (e) {
    e.preventDefault();
    e.stopPropagation();
    return false;
  });

  bulk_edit_submit_button.on('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    bulk_edit_submit();
    return false;
  });

  function bulk_edit_submit() {
    const bulkEditSelectedFields = getBulkEditSelectedFields();

    $('#bulk_edit_submit-spinner').addClass('active');
    let allInputs = $(
      '#bulk_edit_picker input, #bulk_edit_picker select, #bulk_edit_picker .button',
    ).not('#bulk_share');
    let multiSelectInputs = $('#bulk_edit_picker .dt_multi_select');
    let shareInput = $('#bulk_share');
    let commentPayload = {};
    // Check for comment field in dynamically selected fields
    const commentFieldSelected = bulkEditSelectedFields?.some(
      (f) => f.fieldKey === 'comments',
    );
    if (commentFieldSelected) {
      // Find the comment input for the comments field
      const commentInput = $(
        '#bulk_edit_selected_fields_container textarea[id^="bulk_comment-input_"]',
      );
      if (commentInput.length > 0) {
        commentPayload['commentText'] = commentInput.val();
        // Get the corresponding comment type selector
        const commentTypeSelector = commentInput
          .closest('.bulk-edit-field-input-container')
          .find('select[id^="comment_type_selector_"]');
        if (commentTypeSelector.length > 0) {
          commentPayload['commentType'] = commentTypeSelector.val();
        } else {
          // Fallback to default selector if exists
          commentPayload['commentType'] =
            $('#comment_type_selector').val() || 'comment';
        }
      }
    }

    let updatePayload = {};
    let sharePayload;

    // Process web component values
    const form = document.getElementById('bulk_edit_picker');
    Array.from(form.querySelectorAll('*')).forEach((el) => {
      // skip fields not from web components
      if (!el.tagName || !el.tagName.startsWith('DT-')) {
        return;
      }

      // Skip the field selector component - it's not a field to update
      const fieldName = el.name ? el.name.trim() : null;
      if (
        fieldName === 'bulk_edit_field_selector' ||
        el.id === 'bulk_edit_field_selector'
      ) {
        return;
      }

      // Skip any components inside the dynamically selected fields container
      // (they will be processed in the dynamically selected fields section)
      if (el.closest('#bulk_edit_selected_fields_container')) {
        return;
      }

      if (el.value) {
        const convertedValue =
          window.DtWebComponents.ComponentService.convertValue(
            el.tagName,
            el.value,
          );

        // Only add to payload if value is not empty
        // For array-based fields, check if values array has items
        if (convertedValue !== null && convertedValue !== undefined) {
          if (typeof convertedValue === 'object' && convertedValue.values) {
            // For array-based fields, only include if there are values or force_values is true
            if (
              convertedValue.values.length > 0 ||
              convertedValue.force_values === true
            ) {
              updatePayload[fieldName] = convertedValue;
            }
          } else {
            // For simple fields, include if not empty
            if (convertedValue !== '' && convertedValue !== null) {
              updatePayload[fieldName] = convertedValue;
            }
          }
        }
      }
    });

    // Process legacy components
    allInputs.each(function () {
      let inputData = $(this).data();
      $.each(inputData, function (key, value) {
        if (key.includes('bulk_key_') && value) {
          let field_key = key.replace('bulk_key_', '');
          if (field_key) {
            updatePayload[field_key] = value;
          }
        }
      });
    });
    if (window.location_data) {
      updatePayload['location_grid_meta'] =
        window.location_data.location_grid_meta;
    }

    let multiSelectUpdatePayload = {};
    multiSelectInputs.each(function () {
      // Skip if this input is inside the dynamically selected fields container
      if ($(this).closest('#bulk_edit_selected_fields_container').length > 0) {
        return; // Skip - will be handled in dynamically selected fields section
      }

      let inputData = $(this).data();
      $.each(inputData, function (key, value) {
        if (key.includes('bulk_key_') && value) {
          let field_key = key.replace('bulk_key_', '');
          // Skip if this field is in dynamically selected fields (regardless of cleared status)
          const fieldData = bulkEditSelectedFields?.find(
            (f) => f.fieldKey === field_key,
          );
          if (fieldData) {
            return; // Skip processing - will be handled in dynamically selected fields section
          }
          if (!multiSelectUpdatePayload[field_key]) {
            multiSelectUpdatePayload[field_key] = { values: [] };
          }
          multiSelectUpdatePayload[field_key].values.push(value.values);
        }
      });
    });
    const multiSelectKeys = Object.keys(multiSelectUpdatePayload);

    multiSelectKeys.forEach((key, index) => {
      // Double-check: don't overwrite if field is cleared in dynamically selected fields
      const fieldData = bulkEditSelectedFields?.find((f) => f.fieldKey === key);
      if (!fieldData || !fieldData.cleared) {
        // Also check if this field is already in updatePayload (from cleared fields)
        if (!updatePayload[key] || !updatePayload[key].force_values) {
          updatePayload[key] = multiSelectUpdatePayload[key];
        }
      }
    });

    // Process dynamically selected fields
    if (
      typeof bulkEditSelectedFields !== 'undefined' &&
      bulkEditSelectedFields.length > 0
    ) {
      bulkEditSelectedFields.forEach(function (fieldData) {
        const fieldKey = fieldData.fieldKey;
        const fieldType = fieldData.fieldType;

        // Skip if field is marked as cleared - set cleared value and ensure it's not overwritten
        if (fieldData.cleared) {
          const clearedValue = getClearedFieldValue(fieldType);
          // For communication_channel, backend expects: {"contact_email":[],"force_values":true}
          // We need to create an object that has array-like structure with force_values property
          if (fieldType === 'communication_channel') {
            // Create an object that will serialize correctly
            // Backend checks: is_array($fields[$details_key]) AND $fields[$details_key]['force_values']
            // In JavaScript, we can't have an array with properties that serialize, so we use an object
            // But backend also accepts: $fields[$details_key]['values'] format
            // So we'll use: {values: [], force_values: true} which backend will handle
            updatePayload[fieldKey] = {
              values: [],
              force_values: true,
            };
          } else {
            updatePayload[fieldKey] = clearedValue;
          }
          return;
        }

        // Collect value from field input
        const fieldWrapper = $(
          `.bulk-edit-field-wrapper[data-field-key="${fieldKey}"]`,
        );
        const fieldValue = collectFieldValue(fieldKey, fieldType, fieldWrapper);

        if (fieldValue !== null && fieldValue !== undefined) {
          // Handle communication_channel fields specially (direct array format, not wrapped)
          if (fieldType === 'communication_channel') {
            // Communication channel expects direct array: [{"value":"...","key":"..."}]
            if (Array.isArray(fieldValue)) {
              updatePayload[fieldKey] = fieldValue;
            }
          }
          // Handle array-based fields specially (multi_select, tags, connection, location_meta)
          else if (
            fieldType === 'multi_select' ||
            fieldType === 'tags' ||
            fieldType === 'connection' ||
            fieldType === 'location_meta'
          ) {
            // Array-based fields return { values: [...] } format
            // We need to preserve the structure and ensure force_values is set
            if (fieldValue.values && Array.isArray(fieldValue.values)) {
              updatePayload[fieldKey] = {
                values: fieldValue.values,
                force_values:
                  fieldValue.force_values !== undefined
                    ? fieldValue.force_values
                    : false,
              };
            }
          } else {
            // For boolean fields, include even if false (false is a valid value)
            if (fieldType === 'boolean') {
              updatePayload[fieldKey] = fieldValue === true;
            } else {
              updatePayload[fieldKey] = fieldValue;
            }
          }
        } else {
          // For boolean fields, if no value was collected, default to false
          if (fieldType === 'boolean') {
            updatePayload[fieldKey] = false;
          }
        }
      });
    }

    shareInput.each(function () {
      sharePayload = $(this).data('bulk_key_share');
    });

    let shares = {
      users: sharePayload,
      unshare: $('#bulk_share_unshare').length
        ? $('#bulk_share_unshare').prop('checked')
        : false,
    };

    let queue = [];
    let count = 0;
    $('.bulk_edit_checkbox input').each(function () {
      if (this.checked && this.id !== 'bulk_edit_master_checkbox') {
        let postId = parseInt($(this).val());
        queue.push(postId);
      }
    });

    // Process the queue to update records
    if (queue.length > 0) {
      process(
        queue,
        10,
        do_each,
        do_done,
        updatePayload,
        shares,
        commentPayload,
      );
    } else {
      $('#bulk_edit_submit-spinner').removeClass('active');
    }
  }

  function collectFieldValue(fieldKey, fieldType, fieldWrapper) {
    // Special case: comment field (not a real post field type)
    if (fieldType === 'comment') {
      const commentInputId = `bulk_comment-input_${fieldKey}`;
      const commentInput = fieldWrapper.find(`#${commentInputId}`);
      if (commentInput.length > 0) {
        const commentText = commentInput.val();
        return commentText && commentText.trim() !== ''
          ? commentText.trim()
          : null;
      }
      // Fallback: check the original comment input
      const originalCommentInput = $('#bulk_comment-input');
      if (originalCommentInput.length > 0) {
        const commentText = originalCommentInput.val();
        return commentText && commentText.trim() !== ''
          ? commentText.trim()
          : null;
      }
      return null;
    }

    // Special case: user_select (uses typeahead, not web component)
    if (fieldType === 'user_select') {
      const fieldId = `bulk_${fieldKey}`;
      const userInput = fieldWrapper.find(`.js-typeahead-${fieldId}`);
      if (userInput.length > 0) {
        const selectedUserId = userInput.data('selected-user-id');
        if (selectedUserId) {
          return `user-${selectedUserId}`;
        }
        // Fallback: check typeahead instance
        const typeaheadSelector = `.js-typeahead-${fieldId}`;
        const typeaheadInstance = window.Typeahead?.[typeaheadSelector];
        if (
          typeaheadInstance &&
          typeaheadInstance.items &&
          typeaheadInstance.items.length > 0
        ) {
          const selectedItem = typeaheadInstance.items[0];
          if (selectedItem && selectedItem.ID) {
            return `user-${selectedItem.ID}`;
          }
        }
      }
      return null;
    }

    // Special case: communication_channel (dt-multi-text needs special formatting)
    if (fieldType === 'communication_channel') {
      const multiTextComponent =
        fieldWrapper.find(`dt-multi-text#bulk_${fieldKey}`)[0] ||
        fieldWrapper.find(`dt-multi-text[name="${fieldKey}"]`)[0] ||
        fieldWrapper.find(`dt-multi-text`)[0];

      if (multiTextComponent && multiTextComponent.value) {
        // dt-multi-text stores values as array of objects
        // Backend expects direct array format: [{"verified":false,"value":"abc@email.com"}]
        if (Array.isArray(multiTextComponent.value)) {
          return multiTextComponent.value.map((item) => {
            const result = {
              value: item.value || '',
            };
            if (item.key && item.key !== 'new') {
              result.key = item.key;
            }
            if (item.verified !== undefined) {
              result.verified = item.verified;
            }
            return result;
          });
        }
      }
      return null;
    }

    // For all other fields: find web component and use ComponentService
    const component = fieldWrapper.find(
      'dt-text, dt-textarea, dt-number, dt-toggle, dt-date, dt-single-select, ' +
        'dt-multi-select, dt-multi-select-button-group, dt-multi-text, dt-tags, ' +
        'dt-connection, dt-location, dt-location-map, dt-user-select',
    )[0];

    if (!component?.value) {
      // Fallback for simple input fields (text, textarea, number, date, key_select)
      const input = fieldWrapper.find(`#bulk_${fieldKey}`);
      if (input.length > 0) {
        if (
          fieldType === 'text' ||
          fieldType === 'textarea' ||
          fieldType === 'number'
        ) {
          return input.val() || '';
        }
        if (fieldType === 'date' || fieldType === 'datetime') {
          return input.val() || null;
        }
        if (fieldType === 'key_select') {
          return input.val() || null;
        }
      }
      return null;
    }

    // Use ComponentService to convert component value
    return (
      window.DtWebComponents?.ComponentService?.convertValue(
        component.tagName,
        component.value,
      ) ?? component.value
    );
  }

  function getClearedFieldValue(fieldType) {
    // Return appropriate cleared value based on field type
    // Backend requires force_values: true for array-based fields to properly clear all values
    switch (fieldType) {
      case 'connection':
      case 'tags':
      case 'location':
      case 'multi_select':
      case 'location_meta':
        // Use force_values: true to clear all values (backend requirement)
        // Backend checks for force_values to delete all existing values before processing new ones
        return { values: [], force_values: true };

      case 'communication_channel':
        // Communication channel expects direct array format with force_values
        // Backend format: {"contact_email":[],"force_values":true}
        return [];

      case 'date':
      case 'datetime':
      case 'text':
      case 'textarea':
      case 'number':
      case 'key_select':
      case 'user_select':
        // Return empty string to reset field value
        return '';

      default:
        return null;
    }
  }

  function updateBulkEditButtonState() {
    const bulkEditSelectedFields = getBulkEditSelectedFields();
    const updateButton = $('#bulk_edit_submit');
    const hasSelectedFields =
      bulkEditSelectedFields && bulkEditSelectedFields.length > 0;
    const hasSelectedRecords =
      $('.bulk_edit_checkbox:not(#bulk_edit_master) input:checked').length > 0;

    // Enable update button only if both records and fields are selected
    if (hasSelectedRecords && hasSelectedFields) {
      updateButton.prop('disabled', false);
    } else {
      updateButton.prop('disabled', true);
    }
  }

  function bulk_edit_count() {
    let bulk_edit_total_checked = $(
      '.bulk_edit_checkbox:not(#bulk_edit_master) input:checked',
    ).length;
    let bulk_edit_submit_button_text = $('.bulk_edit_submit_text');
    let bulk_edit_delete_submit_button_text = $(
      '.bulk_edit_delete_submit_text',
    );
    let noSelectionMessage = $('#bulk_edit_no_selection_message');
    let actionButtons = $('#bulk_edit_action_buttons');

    if (bulk_edit_total_checked == 0) {
      // Hide buttons, show instruction message
      noSelectionMessage.show();
      actionButtons.hide();

      bulk_edit_submit_button_text.text(
        `${list_settings.translations.make_selections_below}`,
      );

      if (list_settings.permissions.delete_any) {
        bulk_edit_delete_submit_button_text.text(
          `${list_settings.translations.delete_selections_below}`,
        );
      }
    } else {
      // Show buttons, hide instruction message
      noSelectionMessage.hide();
      actionButtons.show();

      bulk_edit_submit_button_text.each(function (index) {
        let pretext = $(this).data('pretext');
        let posttext = $(this).data('posttext');
        $(this).text(`${pretext} ${bulk_edit_total_checked} ${posttext}`);
      });

      if (list_settings.permissions.delete_any) {
        bulk_edit_delete_submit_button_text.each(function (index) {
          let pretext = $(this).data('pretext');
          let posttext = $(this).data('posttext');
          $(this).text(`${pretext} ${bulk_edit_total_checked} ${posttext}`);
        });
      }

      // Update update button state based on field selection
      updateBulkEditButtonState();
    }
  }

  let bulk_edit_picker_checkboxes = $('#bulk_edit_picker .update-needed');
  bulk_edit_picker_checkboxes.on('click', function (e) {
    if ($(this).is(':checked')) {
      $(this).data('bulk_key_requires_update', true);
    }
  });

  let bulk_edit_picker_select_field = $('#bulk_edit_picker select');
  bulk_edit_picker_select_field.on('change', function (e) {
    let field_key = this.id.replace('bulk_', '');
    $(this).data(`bulk_key_${field_key}`, this.value);
  });

  let bulk_edit_picker_button_groups = $('#bulk_edit_picker .select-button');
  bulk_edit_picker_button_groups.on('click', function (e) {
    let field_key = $(this).data('field-key').replace('bulk_', '');
    let optionKey = $(this).attr('id');

    let fieldValue = {};

    fieldValue.values = { value: optionKey };

    $(this).addClass('selected-select-button');
    $(this).data(`bulk_key_${field_key}`, fieldValue);
  });

  //Bulk Update Queue
  function process(
    q,
    num,
    fn,
    done,
    update,
    share,
    comment,
    event_type = 'update',
    responses = [],
  ) {
    // remove a batch of items from the queue
    let items = q.splice(0, num),
      count = items.length;

    // no more items?
    if (!count) {
      // exec done callback if specified
      done && done(responses);
      // quit
      return;
    }

    // loop over each item
    for (let i = 0; i < count; i++) {
      // call callback, passing item and
      // a "done" callback
      fn(
        items[i],
        function (response) {
          // capture valid response
          if (response) {
            if (Array.isArray(response)) {
              response.forEach((element) => responses.push(element));
            } else {
              responses.push(response);
            }
          }

          // when done, decrement counter and
          // if counter is 0, process next batch
          --count ||
            process(
              q,
              num,
              fn,
              done,
              update,
              share,
              comment,
              event_type,
              responses,
            );
        },
        update,
        share,
        comment,
        event_type,
      );
    }
  }

  // a per-item action
  function do_each(item, done, update, share, comment, event_type) {
    let promises = [];

    switch (event_type) {
      case 'update': {
        if (Object.keys(update).length) {
          promises.push(
            window.API.update_post(list_settings.post_type, item, update).catch(
              (err) => {
                // Error handled silently - user will see error via UI feedback
              },
            ),
          );
        }

        if (share && share['users']) {
          share['users'].forEach(function (value) {
            let promise = share['unshare']
              ? window.API.remove_shared(
                  list_settings.post_type,
                  item,
                  value,
                ).catch((err) => {
                  console.error(err);
                })
              : window.API.add_shared(
                  list_settings.post_type,
                  item,
                  value,
                ).catch((err) => {
                  console.error(err);
                });
            promises.push(promise);
          });
        }

        if (comment.commentText) {
          promises.push(
            window.API.post_comment(
              list_settings.post_type,
              item,
              comment.commentText,
              comment.commentType,
            ).catch((err) => {
              console.error(err);
            }),
          );
        }

        break;
      }
      case 'delete': {
        if (list_settings.permissions.delete_any) {
          promises.push(
            window.API.delete_post(list_settings.post_type, item).catch(
              (err) => {
                console.error(err);
              },
            ),
          );
        }
        break;
      }
      case 'message': {
        if (
          update?.subject &&
          update?.from_name &&
          update?.send_method &&
          update?.message
        ) {
          promises.push(
            window
              .makeRequestOnPosts(
                'POST',
                `${list_settings.post_type}/${item}/post_messaging`,
                update,
              )
              .catch((err) => {
                console.error(err);
              }),
          );
        }
        break;
      }
    }
    Promise.all(promises).then(function (responses) {
      done(responses);
    });
  }

  function do_done() {
    $('#bulk_edit_submit-spinner').removeClass('active');
    $('#bulk_edit_delete_submit-spinner').removeClass('active');
    window.location.reload();
  }

  let bulk_assigned_to_input = $(`.js-typeahead-bulk_assigned_to`);
  if (bulk_assigned_to_input.length) {
    $.typeahead({
      input: '.js-typeahead-bulk_assigned_to',
      minLength: 0,
      maxItem: 0,
      accent: true,
      searchOnFocus: true,
      source: window.TYPEAHEADS.typeaheadUserSource(),
      templateValue: '{{name}}',
      template: function (query, item) {
        return `<div class="assigned-to-row" dir="auto">
        <span>
            <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
            ${window.SHAREDFUNCTIONS.escapeHTML(item.name)}
        </span>
        ${item.status_color ? `<span class="status-square" style="background-color: ${window.SHAREDFUNCTIONS.escapeHTML(item.status_color)};">&nbsp;</span>` : ''}
        ${
          item.update_needed && item.update_needed > 0
            ? `<span>
          <img style="height: 12px;" src="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.template_dir)}/dt-assets/images/broken.svg"/>
          <span style="font-size: 14px">${window.SHAREDFUNCTIONS.escapeHTML(item.update_needed)}</span>
        </span>`
            : ''
        }
      </div>`;
      },
      dynamic: true,
      hint: true,
      emptyTemplate: window.SHAREDFUNCTIONS.escapeHTML(
        window.wpApiShare.translations.no_records_found,
      ),
      callback: {
        onClick: function (node, a, item) {
          node.data('bulk_key_assigned_to', `user-${item.ID}`);
        },
        onResult: function (node, query, result, resultCount) {
          let text = window.TYPEAHEADS.typeaheadHelpText(
            resultCount,
            query,
            result,
          );
          $('#bulk_assigned_to-result-container').html(text);
        },
        onHideLayout: function () {
          $('.bulk_assigned_to-result-container').html('');
        },
        onReady: function () {},
      },
    });
  }

  /**
   * Bulk Send Message
   */

  $('#bulk_send_msg_submit').on('click', function (e) {
    handle_bulk_send_messages();
  });

  function handle_bulk_send_messages() {
    const spinner = $('#bulk_send_msg_submit-spinner');

    let subject = $('#bulk_send_msg_subject').val().trim();
    let from_name = $('#bulk_send_msg_from_name').val().trim();
    let reply_to = $('#bulk_send_msg_reply_to').val().trim();
    let send_method = 'email';
    let message = $('#bulk_send_msg').val().trim();

    // If multiple options detected, ensure correct selection is made.
    const checked_send_method = $('.bulk-send-msg-method:checked');
    if ($(checked_send_method).length > 0) {
      send_method = $(checked_send_method).val().trim();
    }

    let queue = [];
    $('.bulk_edit_checkbox input').each(function () {
      if (this.checked && this.id !== 'bulk_edit_master_checkbox') {
        queue.push(parseInt($(this).val()));
      }
    });

    // Validate entries.
    if (!subject) {
      $('#bulk_send_msg_subject_support_text').show();
      return;
    } else {
      $('#bulk_send_msg_subject_support_text').hide();
    }

    if (!from_name) {
      $('#bulk_send_msg_from_name_support_text').show();
      return;
    } else {
      $('#bulk_send_msg_from_name_support_text').hide();
    }

    if (!send_method) {
      $('#bulk_send_msg_method_support_text').show();
      return;
    } else {
      $('#bulk_send_msg_method_support_text').hide();
    }

    if (!message) {
      $('#bulk_send_msg_support_text').show();
      return;
    } else {
      $('#bulk_send_msg_support_text').hide();
    }

    if (!queue || queue.length < 1) {
      $('#bulk_send_msg_submit_support_text').show();
      return;
    } else {
      $('#bulk_send_msg_submit_support_text').hide();
    }

    // Proceed with staged-based message send requests.
    $(spinner).addClass('active');
    process(
      queue,
      10,
      do_each,
      function (responses) {
        // If available, extract response summary.
        if (responses && responses.length > 0) {
          let email_queue_link = `<a target="_blank" href="${window.wpApiShare.site_url + '/wp-admin/admin.php?page=dt_utilities&tab=background_jobs'}">${list_settings.translations.see_queue}</a>`;
          let count_sent = 0;
          let count_fails = 0;
          responses.forEach(function (response) {
            if (response && response['sent'] !== undefined) {
              if (response['sent']) {
                count_sent++;
              } else {
                count_fails++;
              }
            }
          });

          $('#bulk_send_msg_submit-message').html(
            `<strong>${count_sent}</strong> ${list_settings.translations.sent}! ${window.wpApiShare.can_manage_dt ? email_queue_link : ''}<br><strong>${count_fails}</strong> ${list_settings.translations.not_sent}`,
          );
        }

        // Reset record selections.
        $(spinner).removeClass('active');
        $('#bulk_edit_master_checkbox').prop('checked', false);
        $('.bulk_edit_checkbox input').prop('checked', false);
        bulk_edit_count();
        // window.location.reload();
      },
      {
        subject: subject,
        from_name: from_name,
        reply_to: reply_to,
        send_method: send_method,
        message: message,
      },
      {},
      {},
      'message',
    );
  }

  /**
   * Bulk share (only initialize if element exists - field may be dynamically added)
   */
  let bulk_share_input = $('#bulk_share');
  if (bulk_share_input.length) {
    $.typeahead({
      input: '#bulk_share',
      minLength: 0,
      maxItem: 0,
      accent: true,
      searchOnFocus: true,
      source: window.TYPEAHEADS.typeaheadUserSource(),
      templateValue: '{{name}}',
      dynamic: true,
      multiselect: {
        matchOn: ['ID'],
        callback: {
          onCancel: function (node, item) {
            $(node).removeData(`bulk_key_bulk_share`);
            $('#share-result-container').html('');
          },
        },
      },
      callback: {
        onClick: function (node, a, item, event) {
          let shareUserArray;
          if (node.data('bulk_key_share')) {
            shareUserArray = node.data('bulk_key_share');
          } else {
            shareUserArray = [];
          }
          shareUserArray.push(item.ID);
          node.data(`bulk_key_share`, shareUserArray);
        },
        onResult: function (node, query, result, resultCount) {
          if (query) {
            let text = window.TYPEAHEADS.typeaheadHelpText(
              resultCount,
              query,
              result,
            );
            $('#share-result-container').html(text);
          }
        },
        onHideLayout: function () {
          $('#share-result-container').html('');
        },
      },
    });
  }

  /**
   * Bulk Typeahead
   */
  let field_settings = window.list_settings.post_type_settings.fields;

  $('#bulk_edit_picker .dt_typeahead').each((key, el) => {
    let element_id = $(el)
      .attr('id')
      .replace(/_connection$/, '');
    let div_id = $(el).attr('id');
    let field_id = $(`#${div_id} input`).data('field');
    if (element_id !== 'bulk_share') {
      let listing_post_type = window.lodash.get(
        window.list_settings.post_type_settings.fields[field_id],
        'post_type',
        'contacts',
      );
      $.typeahead({
        input: `.js-typeahead-${element_id}`,
        minLength: 0,
        accent: true,
        maxItem: 30,
        searchOnFocus: true,
        template: window.TYPEAHEADS.contactListRowTemplate,
        source: window.TYPEAHEADS.typeaheadPostsSource(listing_post_type, {
          field_key: field_id,
        }),
        display: 'name',
        templateValue: '{{name}}',
        dynamic: true,
        multiselect: {
          matchOn: ['ID'],
          data: '',
          callback: {
            onCancel: function (node, item) {
              $(node).removeData(`bulk_key_${field_id}`);
            },
          },
          href: window.wpApiShare.site_url + `/${listing_post_type}/{{ID}}`,
        },
        callback: {
          onClick: function (node, a, item, event) {
            let multiUserArray;
            if (node.data(`bulk_key_${field_id}`)) {
              multiUserArray = node.data(`bulk_key_${field_id}`).values;
            } else {
              multiUserArray = [];
            }
            multiUserArray.push({ value: item.ID });

            node.data(`bulk_key_${field_id}`, { values: multiUserArray });
            this.addMultiselectItemLayout(item);
            event.preventDefault();
            this.hideLayout();
            this.resetInput();
          },
          onResult: function (node, query, result, resultCount) {
            let text = window.TYPEAHEADS.typeaheadHelpText(
              resultCount,
              query,
              result,
            );
            $(`#${element_id}-result-container`).html(text);
          },
          onHideLayout: function (event, query) {
            if (!query) {
              $(`#${element_id}-result-container`).empty();
            }
          },
          onShowLayout() {},
        },
      });
    }
  });

  if ($('#bulk_edit_picker .js-typeahead-bulk_location_grid').length) {
    $('#bulk_edit_picker .dt_location_grid').each(() => {
      let field_id = 'location_grid';
      let typeaheadTotals = {};
      $.typeahead({
        input: '.js-typeahead-bulk_location_grid',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        maxItem: 20,
        dropdownFilter: [
          {
            key: 'group',
            value: 'focus',
            template: window.SHAREDFUNCTIONS.escapeHTML(
              window.wpApiShare.translations.regions_of_focus,
            ),
            all: window.SHAREDFUNCTIONS.escapeHTML(
              window.wpApiShare.translations.all_locations,
            ),
          },
        ],
        source: {
          focus: {
            display: 'name',
            ajax: {
              url:
                window.wpApiShare.root +
                'dt/v1/mapping_module/search_location_grid_by_name',
              data: {
                s: '{{query}}',
                filter: function () {
                  // return window.lodash.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
                },
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  if (typeof window.typeaheadTotals !== 'undefined') {
                    window.typeaheadTotals.field = data.total;
                  }
                  return data.location_grid;
                },
              },
            },
          },
        },
        display: 'name',
        templateValue: '{{name}}',
        dynamic: true,
        multiselect: {
          matchOn: ['ID'],
          data: '',
          callback: {
            onCancel: function (node, item) {
              $(node).removeData(`bulk_key_${field_id}`);
            },
          },
        },
        callback: {
          onClick: function (node, a, item, event) {
            // $(`#${element_id}-spinner`).addClass('active');
            node.data(`bulk_key_${field_id}`, { values: [{ value: item.ID }] });
          },
          onReady() {
            this.filters.dropdown = {
              key: 'group',
              value: 'focus',
              template: window.SHAREDFUNCTIONS.escapeHTML(
                window.wpApiShare.translations.regions_of_focus,
              ),
            };
            this.container
              .removeClass('filter')
              .find('.' + this.options.selector.filterButton)
              .html(
                window.SHAREDFUNCTIONS.escapeHTML(
                  window.wpApiShare.translations.regions_of_focus,
                ),
              );
          },
          onResult: function (node, query, result, resultCount) {
            resultCount = typeaheadTotals.location_grid;
            let text = window.TYPEAHEADS.typeaheadHelpText(
              resultCount,
              query,
              result,
            );
            $('#location_grid-result-container').html(text);
          },
          onHideLayout: function () {
            $('#location_grid-result-container').html('');
          },
        },
      });
    });
  }

  $(
    '#bulk_edit_picker .tags input, #bulk_edit_picker .multi_select input',
  ).each((key, input) => {
    let field = $(input).data('field') || 'tags';
    let field_options = window.lodash.get(
      list_settings,
      `post_type_settings.fields.${field}.default`,
      {},
    );
    $.typeahead({
      input: input,
      minLength: 0,
      maxItem: 20,
      searchOnFocus: true,
      source: {
        tags: {
          display: ['name'],
          ajax: {
            url:
              window.wpApiShare.root +
              `dt-posts/v2/${list_settings.post_type}/multi-select-values`,
            data: {
              s: '{{query}}',
              field: field,
            },
            beforeSend: function (xhr) {
              xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
            },
            callback: {
              done: function (data) {
                return (data || []).map((tag) => {
                  let label = window.lodash.get(
                    field_options,
                    tag + '.label',
                    tag,
                  );
                  return { name: label || tag, key: tag };
                });
              },
            },
          },
        },
      },
      display: 'name',
      templateValue: '{{name}}',
      dynamic: true,
      multiselect: {
        matchOn: ['key'],
        callback: {
          onCancel: function (node, item) {
            $(node).removeData(`bulk_key_${field}`);
          },
        },
      },
      callback: {
        onClick: function (node, a, item, event) {
          let multiUserArray;
          if (node.data(`bulk_key_${field}`)) {
            multiUserArray = node.data(`bulk_key_${field}`).values;
          } else {
            multiUserArray = [];
          }
          multiUserArray.push({ value: item.key });

          node.data(`bulk_key_${field}`, { values: multiUserArray });
          this.addMultiselectItemLayout(item);
          event.preventDefault();
          this.hideLayout();
          this.resetInput();
        },
        onResult: function (node, query, result, resultCount) {
          let text = window.TYPEAHEADS.typeaheadHelpText(
            resultCount,
            query,
            result,
          );
          $(`#${field}-result-container`).html(text);
        },
        onHideLayout: function () {
          $(`#${field}-result-container`).html('');
        },
      },
    });
  });

  $('button.follow').on('click', function () {
    const current_user_id = DT_List.current_user_id;
    let following = !($(this).data('value') === 'following');
    $(this).data('value', following ? 'following' : '');
    $(this).html(following ? 'Unfollow' : 'Follow');
    $(this).toggleClass('hollow');
    let follow = { values: [{ value: current_user_id, delete: !following }] };

    let unfollow = { values: [{ value: current_user_id, delete: following }] };

    $(this).data('bulk_key_follow', follow);
    $(this).data('bulk_key_unfollow', unfollow);
  });

  $('#bulk_edit_picker input.text-input').change(function () {
    const val = $(this).val();
    let field_key = this.id.replace('bulk_', '');
    $(this).data(`bulk_key_${field_key}`, val);
  });

  $('#bulk_edit_picker .dt_textarea').change(function () {
    const val = $(this).val();
    let field_key = this.id.replace('bulk_', '');
    $(this).data(`bulk_key_${field_key}`, val);
  });

  $('#bulk_edit_picker .dt_date_picker')
    .datepicker({
      constrainInput: false,
      dateFormat: 'yy-mm-dd',
      onClose: function (date) {
        date = window.SHAREDFUNCTIONS.convertArabicToEnglishNumbers(date);

        if (!$(this).val()) {
          date = ' '; //null;
        }

        let formattedDate = window.moment.utc(date).unix();

        let field_key = this.id.replace('bulk_', '');
        $(this).data(`bulk_key_${field_key}`, formattedDate);
      },
      changeMonth: true,
      changeYear: true,
      yearRange: '1900:2050',
    })
    .each(function () {
      if (this.value && window.moment.unix(this.value).isValid()) {
        this.value = window.SHAREDFUNCTIONS.formatDate(this.value);
      }
    });

  let mcleardate = $('#bulk_edit_picker .clear-date-button');
  mcleardate.click(function () {
    let input_id = this.dataset.inputid;
    let date = null;
    // $(`#${input_id}-spinner`).addClass('active')
    let field_key = this.id.replace('bulk_', '');
    $(this).removeData(`bulk_key_${field_key}`);
    $(`#${input_id}`).val('');
  });

  $('#bulk_edit_picker dt-single-select').change((e) => {
    const val = $(e.currentTarget).val();

    if (val === 'paused') {
      $('#bulk_reason_paused').parent().toggle();
    }
  });

  $('#bulk_edit_picker input.number-input').on('blur', function () {
    const id = $(this).attr('id');
    const val = $(this).val();

    let field_key = this.id.replace('bulk_', '');
    $(this).data(`bulk_key_${field_key}`, val);
  });

  $('#bulk_edit_picker .dt_contenteditable').on('blur', function () {
    const id = $(this).attr('id');
    let val = $(this).html();

    let field_key = this.id.replace('bulk_', '');
    $(this).data(`bulk_key_${field_key}`, val);
  });

  $('.list-dropdown-submenu-item-link').on('click', function () {
    // Hide bulk select modals
    $('#records-table').removeClass('bulk_edit_on');

    // Close all open modals
    $('.list-dropdown-submenu-item-link').each(function () {
      let open_modals = $(this).data('modal');
      $('#' + open_modals).hide();
    });

    // Open modal for clicked menu item
    let display_modal = $(this).data('modal');
    $('#' + display_modal).show();

    // Show bulk select checkboxes if applicable
    if ($(this).data('checkboxes') === true) {
      $('#records-table').addClass('bulk_edit_on');
    }
  });

  $('.list-action-close-button').on('click', function () {
    let section = $(this).data('close');
    if (section) {
      $(`#${section}`).hide();
      if (section === 'bulk_edit_picker') {
        $('#records-table').toggleClass('bulk_edit_on');
      }
    } else {
      $('#list-actions .list_action_section').hide();
      $('#records-table').removeClass('bulk_edit_on');
    }
  });

  /*****
   * Bulk Send App
   */
  let bulk_send_app_button = $('#bulk_send_app_submit');
  bulk_send_app_button.on('click', function (e) {
    bulk_send_app();
  });

  function bulk_send_app() {
    let subject = $('#bulk_send_app_subject').val();
    let note = $('#bulk_send_app_msg').val();

    let selected_input = jQuery(
      '.bulk_send_app.dt-radio.button-group input:checked',
    );
    if (selected_input.length < 1) {
      $('#bulk_send_app_required_selection').show();
      return;
    } else {
      $('#bulk_send_app_required_selection').hide();
    }

    let root = selected_input.data('root');
    let type = selected_input.data('type');

    let queue = [];
    $('.bulk_edit_checkbox input').each(function () {
      if (this.checked && this.id !== 'bulk_edit_master_checkbox') {
        let postId = parseInt($(this).val());
        queue.push(postId);
      }
    });

    if (queue.length < 1) {
      $('#bulk_send_app_required_elements').show();
      return;
    } else {
      $('#bulk_send_app_required_elements').hide();
    }

    $('#bulk_send_app_submit-spinner').addClass('active');

    let email_queue_link = `<a target="_blank" href="${window.wpApiShare.site_url + '/wp-admin/admin.php?page=dt_utilities&tab=background_jobs'}">See queue</a>`;

    window
      .makeRequest('POST', list_settings.post_type + '/email_magic', {
        root: root,
        type: type,
        subject: subject,
        note: note,
        post_ids: queue,
      })
      .done((data) => {
        $('#bulk_send_app_submit-spinner').removeClass('active');
        $('#bulk_send_app_submit-message').html(
          `<strong>${data.total_sent}</strong> ${list_settings.translations.sent}! ${window.wpApiShare.can_manage_dt ? email_queue_link : ''}<br><strong>${data.total_unsent}</strong> ${list_settings.translations.not_sent}`,
        );
        $('#bulk_edit_master_checkbox').prop('checked', false);
        $('.bulk_edit_checkbox input').prop('checked', false);
        bulk_edit_count();
        // window.location.reload();
      })
      .fail((e) => {
        $('#bulk_send_app_submit-spinner').removeClass('active');
        $('#bulk_send_app_submit-message').html(
          'Oops. Something went wrong! Check log.',
        );
        console.log(e);
      });
  }

  // ============================================
  // Bulk Edit Field Selection with dt-multi-select
  // ============================================

  // Transform field definitions to dt-multi-select format
  // Note: We include ALL available fields (including selected ones) in options
  // The component's built-in _filterOptions() will automatically filter out selected items from the dropdown
  function transformFieldsForMultiSelect(fields) {
    const allowedTypes = [
      'user_select',
      'multi_select',
      'key_select',
      'date',
      'datetime',
      'location_meta',
      'tags',
      'text',
      'textarea',
      'number',
      'connection', // For share, coaches, etc.
      'boolean', // For requires_update, etc.
      'communication_channel', // Communication channel fields
      // Note: 'link' fields are hidden until dt-link web component is ready
      // Note: 'array' type is not supported as it's typically used for internal data structures
    ];

    const transformedFields = fields
      .filter((field) => {
        return (
          !field.hidden && // Exclude hidden fields
          !field.private && // Exclude private fields
          allowedTypes.includes(field.field_type)
        );
      })
      .sort((a, b) => {
        const nameA = (a.field_name || '').toLowerCase();
        const nameB = (b.field_name || '').toLowerCase();
        return nameA.localeCompare(nameB);
      })
      .map((field) => ({
        id: field.field_key,
        label: field.field_name,
        color: null,
        icon: field.icon || null,
      }));

    // Add synthetic "Comments" option (not a real post field)
    transformedFields.push({
      id: 'comments',
      label: window.wpApiShare?.translations?.comments || 'Comments',
      color: null,
      icon: null,
    });

    return transformedFields;
  }

  // Get all available fields
  function getAllAvailableFields() {
    if (!window.post_type_fields) {
      return [];
    }

    return Object.entries(window.post_type_fields)
      .map(([key, value]) => ({
        field_key: key,
        field_name: value.name,
        field_type: value.type,
        hidden: value.hidden || false,
        private: value.private || false, // Include private flag
        icon: value.icon || null,
      }))
      .filter((field) => {
        // Exclude hidden and private fields
        return !field.hidden && !field.private;
      });
  }

  // Update dt-multi-select options
  // Note: Include ALL available fields - component will auto-filter selected ones from dropdown
  function updateFieldSelectorOptions() {
    const fieldSelector = document.querySelector(
      'dt-multi-select[name="bulk_edit_field_selector"]',
    );
    if (!fieldSelector) {
      return;
    }

    const allFields = getAllAvailableFields();
    const allOptions = transformFieldsForMultiSelect(allFields);

    // Set all options (component will filter out selected ones automatically)
    fieldSelector.options = allOptions;
  }

  // Helper function to create icon element from field data
  function createFieldIconElement(fieldData) {
    if (!fieldData) {
      return $();
    }

    // Get icon from fieldData - check both icon and font-icon properties
    const icon = fieldData.icon || fieldData['font-icon'];

    // Validate icon exists and is a non-empty string
    if (
      !icon ||
      typeof icon !== 'string' ||
      icon.trim() === '' ||
      icon === 'undefined'
    ) {
      return $();
    }

    // Create icon element based on type
    let iconHtml;
    const iconLower = icon.trim().toLowerCase();
    if (iconLower.startsWith('mdi')) {
      // Font icon (Material Design Icons)
      iconHtml = `<i class="${icon.trim()} dt-icon lightgray" style="font-size: 20px;"></i>`;
    } else {
      // Image icon
      iconHtml = `<img src="${icon.trim()}" class="dt-icon lightgray" alt="">`;
    }

    return $(iconHtml);
  }

  // Global dt:get-data event listener for web component data fetching
  // Set up once at page load to handle all component data requests
  document.addEventListener('dt:get-data', async (e) => {
    const { field, query, onSuccess, postType } = e.detail;
    const postTypeToUse = postType || list_settings.post_type;

    try {
      // For tags and multi-select fields, use multi-select-values endpoint
      // For other fields, use field-options endpoint
      const fieldSettings = window.lodash.get(
        list_settings,
        `post_type_settings.fields.${field}`,
        {},
      );
      const fieldType = fieldSettings.type || '';

      let endpoint = 'field-options';
      if (fieldType === 'tags' || fieldType === 'multi_select') {
        endpoint = 'multi-select-values';
      }

      const response = await fetch(
        `${window.wpApiShare.root}dt-posts/v2/${postTypeToUse}/${endpoint}?field=${field}&s=${encodeURIComponent(query || '')}`,
        {
          headers: {
            'X-WP-Nonce': window.wpApiShare.nonce,
          },
        },
      );

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      if (onSuccess) {
        // Handle different response formats
        if (data.options) {
          onSuccess(data.options);
        } else if (Array.isArray(data)) {
          // For tags/multi-select, transform to component format
          if (fieldType === 'tags') {
            const fieldOptions = window.lodash.get(
              list_settings,
              `post_type_settings.fields.${field}.default`,
              {},
            );
            const options = data.map((tag) => {
              const label = window.lodash.get(
                fieldOptions,
                tag + '.label',
                tag,
              );
              return { id: tag, label: label || tag };
            });
            onSuccess(options);
          } else {
            onSuccess(data);
          }
        } else {
          onSuccess([]);
        }
      }
    } catch (error) {
      if (e.detail.onError) {
        e.detail.onError(error);
      }
    }
  });

  // Initialize dt-multi-select on page load
  function initializeBulkEditFieldSelector() {
    const fieldSelector = document.querySelector(
      'dt-multi-select[name="bulk_edit_field_selector"]',
    );
    if (!fieldSelector) {
      return;
    }

    // Set initial options
    updateFieldSelectorOptions();
    fieldSelector.value = [];

    // Handle change events
    fieldSelector.addEventListener('change', function (e) {
      // Extract field keys from newValue (could be strings or objects with id property)
      const selectedFieldKeys = (e.detail.newValue || []).map((v) =>
        typeof v === 'string' ? v : v.id || v,
      );
      // Extract field keys from oldValue
      const previousFieldKeys = (e.detail.oldValue || []).map((v) =>
        typeof v === 'string' ? v : v.id || v,
      );

      // Find newly selected fields
      const newlySelected = selectedFieldKeys.filter(
        (key) => !previousFieldKeys.includes(key),
      );

      // Find deselected fields
      const deselected = previousFieldKeys.filter(
        (key) => !selectedFieldKeys.includes(key),
      );

      // Add newly selected fields
      newlySelected.forEach((fieldKey) => {
        // Handle special 'comments' field (not a real post field)
        if (fieldKey === 'comments') {
          if (!bulkEditSelectedFields.some((f) => f.fieldKey === fieldKey)) {
            bulkEditSelectedFields.push({
              fieldKey: fieldKey,
              fieldType: 'comment',
              fieldName:
                window.wpApiShare?.translations?.comments || 'Comments',
              cleared: false,
            });

            // Render the comment field
            renderBulkEditField(
              fieldKey,
              'comment',
              window.wpApiShare?.translations?.comments || 'Comments',
              $(), // No icon for comments
            );
          }
        } else {
          // Handle regular post fields
          const fieldData = window.post_type_fields[fieldKey];
          if (
            fieldData &&
            !bulkEditSelectedFields.some((f) => f.fieldKey === fieldKey)
          ) {
            bulkEditSelectedFields.push({
              fieldKey: fieldKey,
              fieldType: fieldData.type,
              fieldName: fieldData.name,
              cleared: false,
            });

            // Create icon element from field data
            const iconElement = createFieldIconElement(fieldData);

            // Render the field
            renderBulkEditField(
              fieldKey,
              fieldData.type,
              fieldData.name,
              iconElement,
            );
          }
        }
      });

      // Remove deselected fields
      deselected.forEach((fieldKey) => {
        $(`.bulk-edit-field-wrapper[data-field-key="${fieldKey}"]`).remove();
        bulkEditSelectedFields = bulkEditSelectedFields.filter(
          (f) => f.fieldKey !== fieldKey,
        );
      });

      // Update hidden input and button state
      updateBulkEditSelectedFieldsInput();
      updateBulkEditButtonState();

      // Ensure dropdown closes after selection
      // Use requestAnimationFrame to ensure the component has processed the change
      requestAnimationFrame(() => {
        if (fieldSelector && fieldSelector.open) {
          fieldSelector.open = false;
        }
      });

      // Note: No need to update options - component auto-filters selected items from dropdown
      // But we do need to ensure options include all fields so selected ones can be displayed as tags
      updateFieldSelectorOptions();
    });
  }

  // Initialize on page load
  if ($('dt-multi-select[name="bulk_edit_field_selector"]').length) {
    // Wait for web components to be ready
    if (window.customElements && window.customElements.get('dt-multi-select')) {
      initializeBulkEditFieldSelector();
    } else {
      // Wait for component to be defined
      $(document).ready(function () {
        setTimeout(initializeBulkEditFieldSelector, 100);
      });
    }
  }

  function renderBulkEditField(
    fieldKey,
    fieldType,
    fieldName,
    fieldIconElement,
  ) {
    const container = $('#bulk_edit_selected_fields_container');
    const template = $('#bulk_edit_field_template').html();
    const wrapper = $(template);

    // Set field key
    wrapper.attr('data-field-key', fieldKey);
    wrapper.find('.bulk-edit-field-name').text(fieldName);
    wrapper
      .find('.bulk-edit-remove-field-btn')
      .attr('data-field-key', fieldKey);
    wrapper.find('.bulk-edit-clear-field-btn').attr('data-field-key', fieldKey);
    wrapper
      .find('.bulk-edit-restore-field-btn')
      .attr('data-field-key', fieldKey);

    // Set icon
    const iconContainer = wrapper.find('.bulk-edit-field-icon');
    if (fieldIconElement && fieldIconElement.length) {
      iconContainer.html(fieldIconElement.clone());
    } else {
      iconContainer.html(
        '<div style="width: 20px; display: inline-block;"></div>',
      );
    }

    // Render field input based on type
    const inputContainer = wrapper.find('.bulk-edit-field-input-container');
    renderBulkEditFieldInput(fieldKey, fieldType, inputContainer);

    // Show clear button for fields that support clearing (exclude comment fields)
    if (supportsFieldClearing(fieldType) && fieldType !== 'comment') {
      wrapper.find('.bulk-edit-clear-field-btn').show();
    }

    // Append to container
    container.append(wrapper);
  }

  function renderBulkEditFieldInput(fieldKey, fieldType, container) {
    // Handle comment field specially
    if (fieldType === 'comment') {
      // Create unique IDs for this comment field instance
      const commentInputId = `bulk_comment-input_${fieldKey}`;
      const commentTypeSelectorId = `comment_type_selector_${fieldKey}`;

      // Build comment HTML with proper spacing
      let commentHtml = '<div class="auto cell">';
      commentHtml +=
        '<textarea class="mention" dir="auto" id="' +
        commentInputId +
        '" placeholder="' +
        (window.wpApiShare?.translations?.write_comment_placeholder ||
          'Write your comment or note here') +
        '" style="margin-bottom: 15px;"></textarea>';

      // Add Type selector with proper spacing
      commentHtml += '<div class="grid-x" style="margin-top: 15px;">';
      commentHtml +=
        '<div class="section-subheader cell shrink">' +
        (window.wpApiShare?.translations?.type || 'Type:') +
        '</div>';
      commentHtml +=
        '<select id="' + commentTypeSelectorId + '" class="cell auto">';
      // Default option
      commentHtml +=
        '<option value="comment">' +
        (window.wpApiShare?.translations?.comments || 'Comments') +
        '</option>';
      commentHtml += '</select>';
      commentHtml += '</div>';
      commentHtml += '</div>';

      container.html(commentHtml);

      // Populate comment type selector options from hidden data element
      // Use requestAnimationFrame to ensure DOM is ready
      requestAnimationFrame(() => {
        const newSelector = $(`#${commentTypeSelectorId}`);

        if (newSelector.length === 0) {
          return;
        }

        // Get comment sections from hidden JSON data element
        const commentSectionsData = document.getElementById(
          'bulk_edit_comment_sections_data',
        );
        if (commentSectionsData) {
          try {
            // Get text content from the script element
            const jsonText =
              commentSectionsData.textContent || commentSectionsData.innerText;
            if (!jsonText || jsonText.trim() === '') {
              // Fallback: ensure default 'comment' option exists
              if (newSelector.find('option').length === 0) {
                newSelector.append(
                  '<option value="comment">' +
                    (window.wpApiShare?.translations?.comments || 'Comments') +
                    '</option>',
                );
                newSelector.val('comment');
              }
              return;
            }

            const commentSectionsRaw = JSON.parse(jsonText.trim());

            // Convert to array if it's an object with numeric keys
            let commentSections = [];
            if (Array.isArray(commentSectionsRaw)) {
              commentSections = commentSectionsRaw;
            } else if (
              typeof commentSectionsRaw === 'object' &&
              commentSectionsRaw !== null
            ) {
              // Convert object to array
              commentSections = Object.values(commentSectionsRaw);
            }

            if (commentSections.length > 0) {
              // Clear default option and add all sections
              newSelector.empty();

              // Add all comment sections
              commentSections.forEach((section) => {
                if (section && section.key) {
                  // Skip 'activity' as it's not a comment type
                  if (section.key !== 'activity') {
                    // Get label - check multiple possible properties
                    const label = section.label || section.name || section.key;
                    const enabled = section.enabled !== false; // Default to enabled if not specified

                    // Only add if enabled (unless it's the default comment type)
                    if (enabled || section.key === 'comment') {
                      newSelector.append(
                        `<option value="${section.key}">${label}</option>`,
                      );
                    }
                  }
                }
              });

              // Ensure at least one option exists (fallback to 'comment' if empty)
              if (newSelector.find('option').length === 0) {
                newSelector.append(
                  '<option value="comment">' +
                    (window.wpApiShare?.translations?.comments || 'Comments') +
                    '</option>',
                );
              }

              // Set default value to 'comment' if available, otherwise first option
              if (newSelector.find('option[value="comment"]').length > 0) {
                newSelector.val('comment');
              } else if (newSelector.find('option').length > 0) {
                newSelector.val(
                  newSelector.find('option').first().attr('value'),
                );
              }
            } else {
              // Fallback: ensure default 'comment' option exists
              if (newSelector.find('option').length === 0) {
                newSelector.append(
                  '<option value="comment">' +
                    (window.wpApiShare?.translations?.comments || 'Comments') +
                    '</option>',
                );
                newSelector.val('comment');
              }
            }
          } catch (e) {
            // Fallback: ensure default 'comment' option exists
            if (newSelector.find('option').length === 0) {
              newSelector.append(
                '<option value="comment">' +
                  (window.wpApiShare?.translations?.comments || 'Comments') +
                  '</option>',
              );
              newSelector.val('comment');
            }
          }
        } else {
          // Fallback: ensure default 'comment' option exists
          if (newSelector.find('option').length === 0) {
            newSelector.append(
              '<option value="comment">' +
                (window.wpApiShare?.translations?.comments || 'Comments') +
                '</option>',
            );
            newSelector.val('comment');
          }
        }
      });

      return;
    }

    // Get field settings from list_settings
    const fieldSettings = window.lodash.get(
      list_settings,
      `post_type_settings.fields.${fieldKey}`,
      null,
    );

    if (!fieldSettings) {
      container.html(
        '<div class="alert-box alert">Error: Field settings not found</div>',
      );
      return;
    }

    // Generate field HTML client-side using helper function
    const fieldHtml = window.SHAREDFUNCTIONS.renderField(
      fieldKey,
      fieldSettings,
      'bulk_',
    );

    if (!fieldHtml) {
      // Field type not supported
      container.html(
        '<div class="alert-box alert">This field type is not yet supported in bulk edit</div>',
      );
      return;
    }

    // Inject HTML directly into container
    container.html(fieldHtml);

    // Initialize web components after DOM injection
    requestAnimationFrame(() => {
      // Initialize ComponentService to set up all web components
      if (window.componentService && window.componentService.initialize) {
        try {
          window.componentService.initialize();
        } catch (e) {
          // ComponentService initialization error - components should still work
        }
      }

      // Initialize field-specific handlers if needed
      initializeBulkEditFieldHandlers(fieldKey, fieldType);
    });
  }

  function initializeBulkEditFieldHandlers(fieldKey, fieldType) {
    // Special case: user_select uses typeahead (not a web component)
    if (fieldType === 'user_select') {
      const fieldId = `bulk_${fieldKey}`;
      const userInput = $(`.js-typeahead-${fieldId}`);

      if (userInput.length && !window.Typeahead[`.js-typeahead-${fieldId}`]) {
        $.typeahead({
          input: `.js-typeahead-${fieldId}`,
          minLength: 0,
          maxItem: 0,
          accent: true,
          searchOnFocus: true,
          source: window.TYPEAHEADS.typeaheadUserSource(),
          templateValue: '{{name}}',
          template: function (query, item) {
            return `<div class="assigned-to-row" dir="auto">
              <span>
                  <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
                  ${window.SHAREDFUNCTIONS.escapeHTML(item.name)}
              </span>
              ${item.status_color ? `<span class="status-square" style="background-color: ${window.SHAREDFUNCTIONS.escapeHTML(item.status_color)};">&nbsp;</span>` : ''}
              ${
                item.update_needed && item.update_needed > 0
                  ? `<span>
                <img style="height: 12px;" src="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.template_dir)}/dt-assets/images/broken.svg"/>
                <span style="font-size: 14px">${window.SHAREDFUNCTIONS.escapeHTML(item.update_needed)}</span>
              </span>`
                  : ''
              }
            </div>`;
          },
          dynamic: true,
          hint: true,
          emptyTemplate: window.SHAREDFUNCTIONS.escapeHTML(
            window.wpApiShare.translations.no_records_found,
          ),
          callback: {
            onClick: function (node, a, item, event) {
              event.preventDefault();
              this.hideLayout();
              this.resetInput();

              // Set the selected user value
              const resultContainer = $(`#${fieldId}-result-container`);
              resultContainer.html(
                `<span class="selected-result">${window.SHAREDFUNCTIONS.escapeHTML(item.name)}</span>`,
              );

              // Store the selected user ID in data attributes for later collection
              userInput.data('selected-user-id', item.ID);
              userInput.data('selected-user-name', item.name);
              resultContainer.data('selected-user-id', item.ID);
              resultContainer.data('selected-user-name', item.name);
            },
            onResult: function (node, query, result, resultCount) {
              const resultContainer = $(`#${fieldId}-result-container`);
              if (resultCount > 0) {
                resultContainer.html(
                  `${resultCount} ${window.wpApiShare.translations.user_found || 'user(s) found'}`,
                );
              } else {
                resultContainer.html('');
              }
            },
            onHideLayout: function () {
              $(`#${fieldId}-result-container`).html('');
            },
          },
        });
      }
      return;
    }

    // For all web components: ComponentService.initialize() handles initialization
    // The global dt:get-data listener handles data fetching
    // No per-field-type initialization needed
  }

  // Remove field when clicking X button
  $(document).on('click', '.bulk-edit-remove-field-btn', function () {
    const fieldKey = $(this).data('field-key');

    // Remove from selected fields array
    bulkEditSelectedFields = bulkEditSelectedFields.filter(
      (f) => f.fieldKey !== fieldKey,
    );

    // Remove field wrapper from DOM
    $(`.bulk-edit-field-wrapper[data-field-key="${fieldKey}"]`).remove();

    // Update hidden input
    updateBulkEditSelectedFieldsInput();

    // Update update button state based on field selection
    updateBulkEditButtonState();

    // Update dt-multi-select to remove the field from selection
    const fieldSelector = document.querySelector(
      'dt-multi-select[name="bulk_edit_field_selector"]',
    );
    if (fieldSelector) {
      const currentValue = fieldSelector.value || [];
      fieldSelector.value = currentValue.filter((v) => {
        const id = typeof v === 'string' ? v : v.id;
        return id !== fieldKey;
      });
      // Update options to show the field again
      updateFieldSelectorOptions();
    }
  });

  function updateBulkEditSelectedFieldsInput() {
    const fieldKeys = bulkEditSelectedFields.map((f) => f.fieldKey);
    $('#bulk_edit_selected_fields_input').val(JSON.stringify(fieldKeys));
  }

  function supportsFieldClearing(fieldType) {
    // Fields that support clearing/unsetting
    const clearableTypes = [
      'connection',
      'multi_select',
      'tags',
      'user_select',
      'date',
      'datetime',
      'location',
      'location_meta',
      'text',
      'textarea',
      'number',
      'key_select',
      'communication_channel',
    ];
    return clearableTypes.includes(fieldType);
  }

  // Clear/unset field value
  $(document).on('click', '.bulk-edit-clear-field-btn', function () {
    const fieldKey = $(this).data('field-key');
    const fieldWrapper = $(
      `.bulk-edit-field-wrapper[data-field-key="${fieldKey}"]`,
    );
    const fieldData = bulkEditSelectedFields.find(
      (f) => f.fieldKey === fieldKey,
    );

    if (!fieldData) return;

    // Mark field as cleared
    fieldData.cleared = true;

    // Clear the input visually
    const inputContainer = fieldWrapper.find(
      '.bulk-edit-field-input-container',
    );
    inputContainer.html(
      '<div class="alert-box secondary" style="margin: 0;">Field will be cleared/unset</div>',
    );

    // Hide clear button, show restore button
    $(this).hide();
    fieldWrapper.find('.bulk-edit-restore-field-btn').show();
  });

  // Restore field value (undo clear)
  $(document).on('click', '.bulk-edit-restore-field-btn', function () {
    const fieldKey = $(this).data('field-key');
    const fieldWrapper = $(
      `.bulk-edit-field-wrapper[data-field-key="${fieldKey}"]`,
    );
    const fieldData = bulkEditSelectedFields.find(
      (f) => f.fieldKey === fieldKey,
    );

    if (!fieldData) return;

    // Remove cleared flag
    fieldData.cleared = false;

    // Re-render field input
    const inputContainer = fieldWrapper.find(
      '.bulk-edit-field-input-container',
    );
    renderBulkEditFieldInput(fieldKey, fieldData.fieldType, inputContainer);

    // Show clear button, hide restore button
    $(this).hide();
    fieldWrapper.find('.bulk-edit-clear-field-btn').show();

    // Update update button state (field is no longer cleared)
    updateBulkEditButtonState();
  });

  // Register this module with DT_List
  DT_List.bulk = {
    setupCheckboxEvent: bulk_edit_checkbox_event,
    bulk_edit_count: bulk_edit_count,
    updateBulkEditButtonState: updateBulkEditButtonState,
    getBulkEditSelectedFields: getBulkEditSelectedFields,
  };
})(window.jQuery, window.list_settings, window.Foundation);
