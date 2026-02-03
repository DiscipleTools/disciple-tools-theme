jQuery(function ($) {
  /**
   * Initial States...
   */

  $(document).ready(function () {
    // Auto select primary post and trigger event call & response
    $('#main_archiving_primary_switch_but').trigger('click');

    if (window.DtWebComponents && window.DtWebComponents.ComponentService) {
      let postId = $('#main_primary_current_post_id').val();
      const service = new window.DtWebComponents.ComponentService(
        window.merge_post_details['post_settings']['post_type'],
        postId,
        window.wpApiShare.nonce,
        window.wpApiShare.root,
      );
      window.componentService = service;
    }
  });

  /**
   * Event Listeners
   */

  $(document).on('click', '#main_archiving_primary_switch_but', function (evt) {
    handle_primary_post_Selection();
  });

  $(document).on('change', '.field-select', function (evt) {
    handle_field_selection(evt);
  });

  $(document).on('click', '.submit-merge', function (evt) {
    handle_merge();
  });

  /**
   * Helper Functions
   */

  function handle_primary_post_Selection() {
    // First, toggle post id previous selections
    let toggled_archiving_post_id = $('#main_archiving_current_post_id').val();
    let toggled_primary_post_id = $('#main_primary_current_post_id').val();
    $('#main_primary_current_post_id').val(toggled_archiving_post_id);
    $('#main_archiving_current_post_id').val(toggled_primary_post_id);

    // Fetch primary & archiving posts
    let primary_post = fetch_post_by_merge_type(true);
    let archiving_post = fetch_post_by_merge_type(false);

    // Assuming valid posts have been located, proceed with refreshing layout view
    if (primary_post && archiving_post) {
      // Update merge column titles & post url links
      $('#main_archiving_post_id_title').text(
        archiving_post['record']['title'] +
          ' #' +
          archiving_post['record']['ID'],
      );
      $('#main_primary_post_id_title').text(
        primary_post['record']['title'] + ' #' + primary_post['record']['ID'],
      );
      $('#main_updated_post_id_title').text(primary_post['record']['ID']);

      let archiving_id_link =
        window.merge_post_details['site_url'] +
        window.merge_post_details['post_settings']['post_type'] +
        '/' +
        archiving_post['record']['ID'];
      let primary_id_link =
        window.merge_post_details['site_url'] +
        window.merge_post_details['post_settings']['post_type'] +
        '/' +
        primary_post['record']['ID'];
      $('#main_archiving_post_id_title_link').attr('href', archiving_id_link);
      $('#main_primary_post_id_title_link').attr('href', primary_id_link);

      // Refresh post fields
      refresh_fields(primary_post, archiving_post);
    }
  }

  function fetch_post_by_merge_type(is_primary) {
    // Currently selected id to be viewed as primary
    let primary_post_id = $('#main_primary_current_post_id').val();

    // Now identify archiving id
    let archiving_post_id = $('#main_archiving_current_post_id').val();

    // Return identified post
    return is_primary
      ? window.merge_post_details['posts'][primary_post_id]
      : window.merge_post_details['posts'][archiving_post_id];
  }

  function refresh_fields(primary_post, archiving_post) {
    // Obtain dom-ref handles
    let main_archiving_fields_div = $('#main_archiving_fields_div');
    let main_primary_fields_div = $('#main_primary_fields_div');
    let main_updated_fields_div = $('#main_updated_fields_div');

    // Hide, whilst we work!
    main_archiving_fields_div.fadeOut('fast', function () {
      main_primary_fields_div.fadeOut('fast', function () {
        main_updated_fields_div.fadeOut('fast', function () {
          // Reset archiving & primary post fields accordingly
          main_archiving_fields_div.html(archiving_post['html']);
          main_primary_fields_div.html(primary_post['html']);

          // Updating fields to revert back to default blank state
          main_updated_fields_div.html(
            window.merge_post_details['post_fields_default_html'],
          );

          // Initialise/Activate recently added html fields
          init_fields(
            archiving_post['record'],
            main_archiving_fields_div,
            true,
          );
          init_fields(primary_post['record'], main_primary_fields_div, true);
          init_fields(null, main_updated_fields_div, false);

          // Adjust & trigger field selections to default states; which should set updating fields accordingly
          main_primary_fields_div
            .find('.field-select')
            .each(function (idx, input) {
              let post_field_id = $(input).data('merge_update_field_id');

              if (
                primary_post['record'][post_field_id] &&
                can_select_field($(input).parent().parent())
              ) {
                $(input).prop('checked', true);
                $(input).trigger('change');
              } else {
                // Otherwise, attempt to default to valid corresponding archiving field
                if ($(input).attr('type') === 'radio') {
                  // Attempt to identify corresponding archive radio input
                  let archive_input = main_archiving_fields_div.find(
                    'input[data-merge_field_id="' +
                      archiving_post['record']['ID'] +
                      '_' +
                      post_field_id +
                      '"]',
                  );
                  if (
                    archiving_post['record'][post_field_id] &&
                    archive_input &&
                    can_select_field($(archive_input).parent().parent())
                  ) {
                    $(archive_input).prop('checked', true);
                    $(archive_input).trigger('change');
                  }
                }
              }
            });

          // Select any archiving fields suitable for auto merging
          main_archiving_fields_div
            .find('.field-select')
            .each(function (idx, input) {
              if ($(input).attr('type') === 'checkbox') {
                let post_field_id = $(input).data('merge_update_field_id');
                if (archiving_post['record'][post_field_id]) {
                  $(input).prop(
                    'checked',
                    can_select_field($(input).parent().parent()),
                  );
                  $(input).trigger('change');
                }
              }
            });

          // Display refreshed post fields
          main_archiving_fields_div.fadeIn('fast', function () {
            main_primary_fields_div.fadeIn('fast', function () {
              main_updated_fields_div.fadeIn('fast', function () {
                // Housekeeping - Ensure all typeahead input fields are displayed nicely ;-)
                $.each(window.Typeahead, function (key, typeahead) {
                  if (
                    typeof typeahead.adjustInputSize === 'function' &&
                    !$.isEmptyObject(typeahead.label)
                  ) {
                    typeahead.adjustInputSize();
                  }
                });
              });
            });
          });
        });
      });
    });
  }

  function can_select_field(td_field_input) {
    // Ensure field has a value in order to be selected
    let field_id = $(td_field_input).find('#merge_field_id').val();
    let field_type = $(td_field_input).find('#merge_field_type').val();
    let post_field_id = $(td_field_input).find('#post_field_id').val();

    switch (field_type) {
      case 'textarea':
      case 'number':
      case 'boolean':
      case 'text':
      case 'date':
        return !window.lodash.isEmpty($('#' + field_id).val());

      case 'key_select':
        return (
          !window.lodash.isEmpty($('#' + field_id).val()) ||
          !window.lodash.isEmpty($('#' + post_field_id).val())
        );

      case 'multi_select':
        return !window.lodash.isEmpty(
          $(td_field_input).find('button.selected-select-button'),
        );

      case 'tags':
      case 'location': {
        let typeahead = window.Typeahead['.js-typeahead-' + field_id];
        return typeahead && !window.lodash.isEmpty(typeahead.items);
      }

      case 'link':
        return !window.lodash.isEmpty(
          $(td_field_input).find('input.link-input').not('[value=""]'),
        );

      case 'communication_channel':
      case 'location_meta':
        return !window.lodash.isEmpty(
          $(td_field_input).find('input.input-group-field').not('[value=""]'),
        );

      case 'user_select': {
        let user_select_typeahead =
          window.Typeahead['.js-typeahead-' + field_id];
        return (
          user_select_typeahead &&
          !window.lodash.isEmpty(user_select_typeahead.item)
        );
      }

      case 'connection': {
        let connection_typeahead =
          window.Typeahead['.js-typeahead-' + field_id];
        return (
          connection_typeahead &&
          !window.lodash.isEmpty(connection_typeahead.items)
        );
      }
    }

    return false;
  }

  function init_fields(post, fields_div, read_only) {
    let url_root = window.merge_post_details['url_root'];
    let post_type = window.merge_post_details['post_settings']['post_type'];
    let nonce = window.merge_post_details['nonce'];

    $(fields_div)
      .find('table tbody tr .td-field-input')
      .each(function (idx, td) {
        // Determine field id and type + meta
        let field_id = $(td).find('#merge_field_id').val();
        let field_type = $(td).find('#merge_field_type').val();
        let field_meta = $(td).find('#field_meta');

        // Remove field prefix, ahead of further downstream processing
        let post_field_id = post
          ? window.lodash.replace(field_id, post['ID'] + '_', '')
          : $(td).find('#post_field_id').val();

        // Activate field accordingly, based on type and read-only flag
        switch (field_type) {
          case 'textarea':
          case 'number':
          case 'boolean':
          case 'text':
          case 'key_select':
          case 'date':
          case 'multi_select':
          case 'tags':
          case 'connection':
          case 'communication_channel':
          case 'location_meta':
          case 'location':
            // Disable field accordingly, based on read-only flag
            $(td)
              .find('#' + field_id)
              .prop('disabled', read_only);
            break;

          case 'link': {
            // Disable/Display field accordingly, based on read-only flag
            $(td).find('input.link-input').prop('disabled', read_only);
            $(td).find('button.link-delete-button').prop('disabled', read_only);

            // Ensure add link functionality is suppressed.
            $(td).find('div.add-link-dropdown').remove();

            if (!read_only) {
              $(td)
                .find('input.link-input')
                .each(function (idx, input) {
                  if (window.lodash.isEmpty($(input).val())) {
                    $(input).parent().hide();
                  }
                });

              /**
               * Remove
               */

              $(document).on('click', '.link-delete-button', (evt) => {
                const delete_but = $(evt.currentTarget);

                // Keep a record of deleted meta_ids.
                let meta_id = $(delete_but).data('meta-id');
                let deleted_items = $(field_meta).val()
                  ? JSON.parse($(field_meta).val())
                  : [];
                if (!window.lodash.includes(deleted_items, meta_id)) {
                  deleted_items.push(meta_id);
                  $(field_meta).val(JSON.stringify(deleted_items));
                }

                // Finally, remove from parent.
                $(delete_but).parent().parent().remove();
              });
            }

            break;
          }

          case 'user_select': {
            let user_select_typeahead_field_input = '.js-typeahead-' + field_id;

            // Disable field accordingly, based on read-only flag
            $(td)
              .find(user_select_typeahead_field_input)
              .prop('disabled', read_only);

            /**
             * Load Typeahead
             */

            $(td)
              .find(user_select_typeahead_field_input)
              .typeahead({
                input: user_select_typeahead_field_input,
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
                  onClick: function (node, a, item) {},
                  onResult: function (node, query, result, resultCount) {
                    let text = window.TYPEAHEADS.typeaheadHelpText(
                      resultCount,
                      query,
                      result,
                    );
                    $(`#${field_id}-result-container`).html(text);
                  },
                  onHideLayout: function () {
                    $(`.${field_id}-result-container`).html('');
                  },
                },
              });

            // If available, load previous post record user selection
            let user_select_typeahead =
              window.Typeahead[user_select_typeahead_field_input];
            let post_user_select = post ? post[post_field_id] : undefined;

            if (post_user_select !== undefined && user_select_typeahead) {
              $(user_select_typeahead_field_input).val(
                post_user_select['display'],
              );
              user_select_typeahead.item = {
                ID: post_user_select['id'],
                name: post_user_select['display'],
              };
            }

            break;
          }
        }
      });

    if (window.componentService) {
      window.componentService.attachLoadEvents();
    }
  }

  function handle_field_selection(evt) {
    // Determine selection state, field id and type
    let is_selected = $(evt.currentTarget).is(':checked');
    let update_field_id = $(evt.currentTarget).data('merge_update_field_id');
    let field_id = $(evt.currentTarget).data('merge_field_id');
    let field_type = $(evt.currentTarget).data('merge_field_type');

    // Update selected fields accordingly
    update_fields(
      $(evt.currentTarget),
      is_selected,
      update_field_id,
      field_id,
      field_type,
    );
  }

  function update_fields(
    selector,
    is_selected,
    update_field_id,
    field_id,
    field_type,
  ) {
    const sourceField = $(`#${field_id}`);
    const mergedField = $(`#${update_field_id}`);

    switch (field_type) {
      case 'textarea':
      case 'number':
      case 'text':
        if (is_selected) {
          mergedField.val(sourceField.val());
        }
        break;

      case 'boolean': {
        // Web component: dt-toggle uses .value property (or .checked)
        const sourceElement = sourceField[0];
        const mergedElement = mergedField[0];
        if (sourceElement && mergedElement && is_selected) {
          // dt-toggle uses checked property for boolean values
          mergedElement.checked = sourceElement.checked;
          // Also update value property if it exists
          if (sourceElement.value !== undefined) {
            mergedElement.value = sourceElement.value;
          }
          // Trigger change event to update the component
          mergedElement.dispatchEvent(new Event('change', { bubbles: true }));
        }
        break;
      }

      case 'key_select': {
        // Web component: dt-single-select uses .value property
        const sourceElement = sourceField[0];
        const mergedElement = mergedField[0];
        if (sourceElement && mergedElement && is_selected) {
          mergedElement.value = sourceElement.value;
          // Trigger change event to update the component
          mergedElement.dispatchEvent(new Event('change', { bubbles: true }));
        }
        break;
      }

      case 'date':
        if (is_selected) {
          mergedField.removeAttr('timestamp');
          mergedField.val(sourceField.val());
        }
        break;

      case 'multi_select':
      case 'tags': {
        // Determine values to be updated
        const sourceValue = sourceField.val() || [];
        let mergedValue = mergedField.val() || [];

        // Update values accordingly
        for (const sourceTag of sourceValue) {
          const valIdx = mergedValue.findIndex((x) => x === sourceTag);
          if (is_selected) {
            // Add, if not already present
            if (valIdx < 0) {
              mergedValue.push(sourceTag);
            }
          } else {
            // Remove, if present and not still selected anywhere else!
            if (
              !is_field_value_still_selected(
                update_field_id,
                field_type,
                sourceTag,
              )
            ) {
              mergedValue.splice(valIdx, 1);
              // mergedValue = mergedValue.filter((x) => x !== sourceTag);
            }
          }
        }

        mergedField.val(mergedValue);
        break;
      }

      case 'link': {
        // Determine selector source field link inputs to be processed.
        let source_field_link_inputs = [];
        let tr = $(selector).parent().parent();
        $(tr)
          .find('.td-field-input input.link-input')
          .each(function (idx, input) {
            if ($(input).val()) {
              source_field_link_inputs.push(input);
            }
          });

        // Delete/Add updated post record, based on identified source field inputs.
        let main_updated_fields_div = $('#main_updated_fields_div');
        let link_field_meta_input = $(main_updated_fields_div)
          .find(`.link-list-${update_field_id}`)
          .parent()
          .parent()
          .find('#field_meta');
        let deleted_items = $(link_field_meta_input).val()
          ? JSON.parse($(link_field_meta_input).val())
          : [];

        // Locate by link field values.
        $.each(source_field_link_inputs, function (idx, input) {
          let link_list_section_div = $(main_updated_fields_div).find(
            `.link-list-${update_field_id} .link-section--${$(input).data('type')}`,
          );
          let matched_input = $(link_list_section_div).find(
            `.input-group input[value="${$(input).val()}"].link-input`,
          );

          // Handle accordingly, based on incoming selected state.
          if (is_selected) {
            // Add new updated link fields.
            if (matched_input.length === 0) {
              $(link_list_section_div).append(`
                <div class="input-group">
                    <input type="text" class="link-input input-group-field" value="${window.SHAREDFUNCTIONS.escapeHTML($(input).val())}" data-meta-id="${window.SHAREDFUNCTIONS.escapeHTML($(input).data('meta-id'))}" data-field-key="${window.SHAREDFUNCTIONS.escapeHTML(update_field_id)}" data-type="${window.SHAREDFUNCTIONS.escapeHTML($(input).data('type'))}">
                    <div class="input-group-button">
                        <button class="button alert delete-button-style input-height link-delete-button delete-button" data-meta-id="${window.SHAREDFUNCTIONS.escapeHTML($(input).data('meta-id'))}" data-field-key="${window.SHAREDFUNCTIONS.escapeHTML(update_field_id)}">&times;</button>
                    </div>
                </div>`);

              // Remove any previously deleted entries.
              window.lodash.remove(deleted_items, function (meta_id) {
                return meta_id === $(input).data('meta-id');
              });
              $(link_field_meta_input).val(JSON.stringify(deleted_items));
            }
          } else {
            // Remove new updated link fields.
            if (matched_input.length > 0) {
              $(matched_input).parent().remove();

              // Keep a record of deleted meta_ids.
              if (
                !window.lodash.includes(
                  deleted_items,
                  $(matched_input).data('meta-id'),
                )
              ) {
                deleted_items.push($(matched_input).data('meta-id'));
                $(link_field_meta_input).val(JSON.stringify(deleted_items));
              }
            }
          }
        });

        break;
      }

      case 'communication_channel': {
        // Determine values to be updated
        const sourceValue = sourceField.val() || [];
        let mergedValue = mergedField.val() || [];

        // Update values accordingly
        for (const sourceItem of sourceValue) {
          const valIdx = mergedValue.findIndex((x) => x.key === sourceItem.key);
          if (is_selected) {
            // Add, if not already present
            if (valIdx < 0) {
              mergedValue.push(sourceItem);
            }
          } else {
            // Remove, if present and not still selected anywhere else!
            if (
              !is_field_value_still_selected(
                update_field_id,
                field_type,
                sourceItem,
              )
            ) {
              mergedValue.splice(valIdx, 1);
            }
          }
        }

        // if there is an empty value, remove it
        const emptyIdx = mergedValue.findIndex((x) => !x.value && x.tempKey);
        if (mergedValue.length > 1 && emptyIdx > -1) {
          mergedValue.splice(emptyIdx, 1);
        }

        // set value attribute of element
        mergedField.attr('value', JSON.stringify(mergedValue));

        break;
      }

      case 'location_meta': {
        // Web component: dt-location-map stores value as JSON string in 'value' attribute
        const sourceElement = sourceField[0];
        const mergedElement = mergedField[0];

        if (sourceElement && mergedElement) {
          // Read from attribute first (as JSON string), fallback to property if needed
          let sourceValueStr =
            sourceElement.getAttribute('value') ||
            sourceField.attr('value') ||
            sourceElement.value ||
            '[]';
          let mergedValueStr =
            mergedElement.getAttribute('value') ||
            mergedField.attr('value') ||
            mergedElement.value ||
            '[]';

          // Parse JSON strings to arrays
          let sourceValue = [];
          let mergedValue = [];

          try {
            // Handle both string and already-parsed values
            if (typeof sourceValueStr === 'string') {
              sourceValue = JSON.parse(sourceValueStr);
            } else if (Array.isArray(sourceValueStr)) {
              sourceValue = sourceValueStr;
            }
          } catch (e) {
            sourceValue = [];
          }

          try {
            if (typeof mergedValueStr === 'string') {
              mergedValue = JSON.parse(mergedValueStr);
            } else if (Array.isArray(mergedValueStr)) {
              mergedValue = mergedValueStr;
            }
          } catch (e) {
            mergedValue = [];
          }

          // Ensure they are arrays
          if (!Array.isArray(sourceValue)) {
            sourceValue = [];
          }
          if (!Array.isArray(mergedValue)) {
            mergedValue = [];
          }

          // Obtain handle onto update field meta - deletion array
          let update_field_meta = $('#main_updated_fields_div')
            .find('#merge_field_id[value="' + update_field_id + '"]')
            .parent()
            .find('#field_meta');
          let deleted_items =
            update_field_meta &&
            $(update_field_meta).length > 0 &&
            $(update_field_meta).val()
              ? JSON.parse($(update_field_meta).val())
              : [];

          // Update values accordingly
          for (const sourceItem of sourceValue) {
            // Compare by grid_meta_id if available, otherwise by label/value
            const valIdx = mergedValue.findIndex((x) => {
              if (sourceItem.grid_meta_id && x.grid_meta_id) {
                return (
                  String(x.grid_meta_id) === String(sourceItem.grid_meta_id)
                );
              }
              // Fallback comparison by label or value
              const sourceLabel = sourceItem.label || sourceItem.value || '';
              const xLabel = x.label || x.value || '';
              return sourceLabel === xLabel && sourceLabel !== '';
            });

            if (is_selected) {
              // Add, if not already present
              if (valIdx < 0) {
                mergedValue.push(sourceItem);

                // Remove from deleted items if it was previously deleted
                if (sourceItem.grid_meta_id) {
                  const deletedIdx = deleted_items.findIndex(
                    (id) => String(id) === String(sourceItem.grid_meta_id),
                  );
                  if (deletedIdx >= 0) {
                    deleted_items.splice(deletedIdx, 1);
                    $(update_field_meta).val(JSON.stringify(deleted_items));
                  }
                }
              }
            } else {
              // Remove, if present and not still selected anywhere else!
              if (
                valIdx >= 0 &&
                !is_field_value_still_selected(
                  update_field_id,
                  field_type,
                  sourceItem,
                )
              ) {
                mergedValue.splice(valIdx, 1);

                // Keep deleted items in sync
                if (sourceItem.grid_meta_id) {
                  if (
                    !window.lodash.includes(
                      deleted_items,
                      sourceItem.grid_meta_id,
                    )
                  ) {
                    deleted_items.push(sourceItem.grid_meta_id);
                    $(update_field_meta).val(JSON.stringify(deleted_items));
                  }
                }
              }
            }
          }

          // Update the web component's value attribute (as JSON string)
          // Set both attribute and property for compatibility
          mergedField.attr('value', JSON.stringify(mergedValue));
          if (mergedElement) {
            mergedElement.setAttribute('value', JSON.stringify(mergedValue));
            // Also set property if component supports it
            if (mergedElement.value !== undefined) {
              mergedElement.value = mergedValue;
            }
            // Trigger change event to update the component UI
            mergedElement.dispatchEvent(new Event('change', { bubbles: true }));
          }
        }

        break;
      }

      case 'connection': {
        // Determine values to be updated
        const sourceValue = sourceField.val() || [];
        let mergedValue = mergedField.val() || [];

        // Update values accordingly
        for (const sourceItem of sourceValue) {
          const valIdx = mergedValue.findIndex((x) => x.id === sourceItem.id);
          if (is_selected) {
            // Add, if not already present
            if (valIdx < 0) {
              mergedValue.push(sourceItem);
            }
          } else {
            // Remove, if present and not still selected anywhere else!
            if (
              !is_field_value_still_selected(
                update_field_id,
                field_type,
                sourceItem,
              )
            ) {
              mergedValue.splice(valIdx, 1);
            }
          }
        }

        // set value attribute of element
        mergedField.attr('value', JSON.stringify(mergedValue));

        break;
      }
      case 'location': {
        // Determine values to be updated
        let source_typeahead = window.Typeahead['.js-typeahead-' + field_id];
        let update_typeahead =
          window.Typeahead['.js-typeahead-' + update_field_id];

        // Update values accordingly
        if (source_typeahead && update_typeahead) {
          // Obtain handle onto update field meta - deletion array
          let update_field_meta = $('#main_updated_fields_div')
            .find('#merge_field_id[value="' + update_field_id + '"]')
            .parent()
            .find('#field_meta');
          let deleted_items =
            update_field_meta &&
            $(update_field_meta).length > 0 &&
            $(update_field_meta).val()
              ? JSON.parse($(update_field_meta).val())
              : [];

          // Iterate source tags, updating accordingly
          $.each(source_typeahead.items, function (idx, source) {
            if (is_selected) {
              // Add, if not already present

              if (
                !window.lodash.includes(update_typeahead.items, source['ID'])
              ) {
                update_typeahead.addMultiselectItemLayout(source);

                // Keep deleted items in sync
                let found = window.lodash.find(deleted_items, function (item) {
                  return (
                    new String(item['ID']).valueOf() ==
                    new String(source['ID'].valueOf())
                  );
                });

                if (found) {
                  window.lodash.remove(deleted_items, found);
                  $(update_field_meta).val(JSON.stringify(deleted_items));
                }
              }
            } else {
              // Remove, if present and not still selected anywhere else!

              if (
                !is_field_value_still_selected(
                  update_field_id,
                  field_type,
                  source,
                )
              ) {
                // Remove item object
                window.lodash.remove(update_typeahead.items, function (value) {
                  return value['name'] === source['name'];
                });

                // Remove compared item string
                window.lodash.remove(
                  update_typeahead.comparedItems,
                  function (value) {
                    return (
                      new String(value).valueOf() ==
                      new String(source['ID'].valueOf())
                    );
                  },
                );

                // Remove matching label container
                $(update_typeahead.label.container)
                  .find('.typeahead__label')
                  .each(function (idx, label) {
                    if (
                      $(label)
                        .find('span')
                        .not('span.typeahead__cancel-button')
                        .text() === source['name']
                    ) {
                      $(label).remove();
                    }
                  });

                // Keep deleted items in sync
                let found = window.lodash.find(deleted_items, function (item) {
                  return (
                    new String(item['ID']).valueOf() ==
                    new String(source['ID'].valueOf())
                  );
                });

                if (!found) {
                  deleted_items.push(source);
                  $(update_field_meta).val(JSON.stringify(deleted_items));
                }
              }
            }
          });

          update_typeahead.adjustInputSize();
        }

        break;
      }

      case 'user_select': {
        // Determine values to be updated
        let source_user_select_typeahead_field_input =
          '.js-typeahead-' + field_id;
        let update_user_select_typeahead_field_input =
          '.js-typeahead-' + update_field_id;
        let source_typeahead_user_select =
          window.Typeahead[source_user_select_typeahead_field_input];
        let update_typeahead_user_select =
          window.Typeahead[update_user_select_typeahead_field_input];

        // Update values accordingly
        if (source_typeahead_user_select && update_typeahead_user_select) {
          if (is_selected) {
            $(update_user_select_typeahead_field_input).val(
              $(source_user_select_typeahead_field_input).val(),
            );
            update_typeahead_user_select.item =
              source_typeahead_user_select.item;
          }
        }

        break;
      }
    }
  }

  function is_field_value_still_selected(field_id, field_type, field_value) {
    let still_selected = false;

    // Determine current merge state post ids + supporting meta info
    let merging_objs = [
      {
        post_id: $('#main_archiving_current_post_id').val(),
        fields_div: 'main_archiving_fields_div',
      },
      {
        post_id: $('#main_primary_current_post_id').val(),
        fields_div: 'main_primary_fields_div',
      },
    ];

    // Iterate over both ids, in search of matching field values
    for (const merge_obj of merging_objs) {
      // Obtain handle on td field select input widget
      let td_field_select_input = $('#' + merge_obj['fields_div']).find(
        '.td-field-select input[data-merge_field_id="' +
          merge_obj['post_id'] +
          '_' +
          field_id +
          '"]',
      );

      // Only proceed if input is selected
      if (td_field_select_input && $(td_field_select_input).is(':checked')) {
        // Compare values (by field type) to determine if there is still a match
        switch (field_type) {
          case 'textarea':
          case 'number':
          case 'boolean':
          case 'text':
          case 'date': {
            break;
          }

          case 'key_select': {
            break;
          }

          case 'multi_select':
          case 'tags': {
            const selectedValue = $(td_field_select_input)
              .parent()
              .parent()
              .find(`[name=${field_id}]`)
              .val();

            if (selectedValue && selectedValue.includes(field_value)) {
              still_selected = true;
            }

            break;
          }

          case 'location': {
            let typeahead =
              window.Typeahead[
                '.js-typeahead-' + merge_obj['post_id'] + '_' + field_id
              ];
            if (typeahead) {
              let matched_value = window.lodash.find(
                typeahead.items,
                function (item) {
                  return (
                    new String(item['name']).valueOf() ==
                    new String(field_value['name'].valueOf())
                  );
                },
              );

              if (matched_value && $(matched_value).length > 0) {
                still_selected = true;
              }
            }

            break;
          }

          case 'communication_channel': {
            const selectedValue = $(td_field_select_input)
              .parent()
              .parent()
              .find(`[name=${field_id}]`)
              .val();

            if (
              selectedValue &&
              selectedValue.some((x) => x.key === field_value.key)
            ) {
              still_selected = true;
            }

            break;
          }

          case 'location_meta': {
            // Web component: dt-location-map stores value as JSON string in 'value' attribute
            const fieldElement = $(
              '#' + merge_obj['post_id'] + '_' + field_id,
            )[0];
            const fieldJQuery = $('#' + merge_obj['post_id'] + '_' + field_id);

            if (fieldElement) {
              // Read from attribute first (as JSON string), fallback to property if needed
              let selectedValueStr =
                fieldElement.getAttribute('value') ||
                fieldJQuery.attr('value') ||
                fieldElement.value ||
                '[]';

              let selectedValue = [];
              // Parse JSON string to array
              try {
                if (typeof selectedValueStr === 'string') {
                  selectedValue = JSON.parse(selectedValueStr);
                } else if (Array.isArray(selectedValueStr)) {
                  selectedValue = selectedValueStr;
                }
              } catch (e) {
                selectedValue = [];
              }

              if (!Array.isArray(selectedValue)) {
                selectedValue = [];
              }

              // Compare field_value (which is a location object) with selectedValue array
              // field_value could be a string (old format) or an object (new format)
              if (typeof field_value === 'string') {
                // Old format: compare by string value
                const matched = selectedValue.some(
                  (item) =>
                    item.label === field_value || item.value === field_value,
                );
                if (matched) {
                  still_selected = true;
                }
              } else if (field_value && typeof field_value === 'object') {
                // New format: compare by grid_meta_id or label
                const matched = selectedValue.some((item) => {
                  if (field_value.grid_meta_id && item.grid_meta_id) {
                    return (
                      String(item.grid_meta_id) ===
                      String(field_value.grid_meta_id)
                    );
                  }
                  // Fallback: compare by label
                  const fieldLabel =
                    field_value.label || field_value.value || '';
                  const itemLabel = item.label || item.value || '';
                  return fieldLabel === itemLabel && fieldLabel !== '';
                });
                if (matched) {
                  still_selected = true;
                }
              }
            }

            break;
          }

          case 'user_select': {
            break;
          }

          case 'connection': {
            const selectedValue = $(td_field_select_input)
              .parent()
              .parent()
              .find(`[name=${field_id}]`)
              .val();

            if (
              selectedValue &&
              selectedValue.some((x) => x.id === field_value.id)
            ) {
              still_selected = true;
            }

            break;
          }
        }
      }
    }

    return still_selected;
  }

  function is_field_value_already_in_primary(
    field_id,
    field_type,
    field_value,
  ) {
    let is_already_in_primary = false;

    // First, obtain handle onto current primary post
    let primary_post = fetch_post_by_merge_type(true)['record'];

    // Ensure primary post contains field in question
    if (primary_post && primary_post[field_id]) {
      // Parse value accordingly, based on field type
      switch (field_type) {
        case 'link':
        case 'communication_channel': {
          $.each(primary_post[field_id], function (idx, value) {
            if (window.lodash.includes(value, field_value)) {
              is_already_in_primary = true;
            }
          });

          break;
        }
      }
    }

    return is_already_in_primary;
  }

  function is_link_field_value_already_in_primary(
    field_id,
    link_type,
    link_meta_id,
    link_value,
    check_by_value,
  ) {
    let is_already_in_primary = false;

    // First, obtain handle onto current primary post
    let primary_post = fetch_post_by_merge_type(true)['record'];

    // Ensure primary post contains field in question
    if (primary_post && primary_post[field_id]) {
      // Parse value accordingly, based on link type, meta_id & value.
      $.each(primary_post[field_id], function (idx, value) {
        if (value['type'] && value['meta_id'] && value['value']) {
          if (value['type'] === link_type) {
            // Accommodate both checks by meta_id (in the event of value changes) and existing unchanged values.
            if (
              !check_by_value &&
              String(value['meta_id']) === String(link_meta_id)
            ) {
              is_already_in_primary = true;
            } else if (
              check_by_value &&
              String(value['value']) === String(link_value)
            ) {
              is_already_in_primary = true;
            }
          }
        }
      });
    }

    return is_already_in_primary;
  }

  function handle_merge() {
    // Disable submit button
    $('.submit-merge').toggleClass('loading').attr('disabled', true);

    // Start packaging updated fields
    let values = {};

    // process web component values
    const form = document.getElementById('main_updated_fields_div');
    Array.from(form.querySelectorAll('*')).forEach((el) => {
      // skip fields not from web components
      if (!el.tagName || !el.tagName.startsWith('DT-')) {
        return;
      }

      if (el.value) {
        let value = window.DtWebComponents.ComponentService.convertValue(
          el.tagName,
          el.value,
        );
        switch (el.tagName.toLowerCase()) {
          case 'dt-multi-text':
            value = value.map((x) => {
              const retVal = {
                value: x.value,
              };
              if (x.key) {
                retVal.key = x.key;
              }
              return retVal;
            });
            break;
          default:
            break;
        }
        values[el.name.trim()] = value;
      }
    });

    $('#main_updated_fields_div')
      .find('.td-field-input')
      .each(function (idx, td) {
        let field_id = $(td).find('#merge_field_id').val();
        let field_type = $(td).find('#merge_field_type').val();
        let post_field_id = $(td).find('#post_field_id').val();
        let field_meta = $(td).find('#field_meta');

        switch (field_type) {
          case 'number':
          case 'boolean':
            values[post_field_id] = $(td)
              .find('#' + field_id)
              .val();
            break;

          case 'location': {
            let typeahead = window.Typeahead['.js-typeahead-' + field_id];
            if (typeahead) {
              // Determine values to be processed
              let key = field_type === 'tags' ? 'name' : 'ID';
              let items = typeahead.items;
              let deletions = field_meta.val()
                ? JSON.parse(field_meta.val())
                : [];

              // Package values and any deletions
              let entries = [];
              $.each(items, function (idx, item) {
                entries.push({
                  value: item[key],
                });
              });
              $.each(deletions, function (idx, item) {
                entries.push({
                  value: item[key],
                  delete: true,
                });
              });

              // If present, capture entries
              if (entries) {
                values[post_field_id] = {
                  values: entries,
                };
              }
            }
            break;
          }

          case 'location_meta': {
            // Get the web component element (dt-location-map)
            const fieldElement = $(td).find('#' + field_id)[0];

            if (fieldElement) {
              // Read from value attribute (JSON string)
              let locationValueStr =
                fieldElement.getAttribute('value') ||
                $(td)
                  .find('#' + field_id)
                  .attr('value') ||
                fieldElement.value ||
                '[]';

              // Parse JSON to get array of location objects
              let locationObjects = [];
              try {
                if (typeof locationValueStr === 'string') {
                  locationObjects = JSON.parse(locationValueStr);
                } else if (Array.isArray(locationValueStr)) {
                  locationObjects = locationValueStr;
                }
              } catch (e) {
                locationObjects = [];
              }

              if (!Array.isArray(locationObjects)) {
                locationObjects = [];
              }

              // Get deletions from field_meta
              let location_meta_deletions = field_meta.val()
                ? JSON.parse(field_meta.val())
                : [];

              // Format location objects for backend
              let location_meta_entries = [];

              // Add each location with required fields
              locationObjects.forEach((location) => {
                // Backend requires: grid_id, label, level, lng, lat
                if (
                  location.grid_id &&
                  location.label &&
                  location.level &&
                  location.lng &&
                  location.lat
                ) {
                  location_meta_entries.push({
                    grid_id: location.grid_id,
                    label: location.label,
                    level: location.level,
                    lng: location.lng,
                    lat: location.lat,
                    source: location.source || 'user', // Default to 'user' if not set
                  });
                }
              });

              // Add deletions (format: { grid_meta_id: id, delete: true })
              location_meta_deletions.forEach((id) => {
                location_meta_entries.push({
                  grid_meta_id: id,
                  delete: true,
                });
              });

              // Package for submission
              if (location_meta_entries.length > 0) {
                values[post_field_id] = {
                  values: location_meta_entries,
                };
              }
            }
            break;
          }

          case 'link': {
            // Determine values to be processed
            let link_entries = [];
            let link_deletions = field_meta.val()
              ? JSON.parse(field_meta.val())
              : [];

            // Package values and any deletions
            $(td)
              .find('.input-group input.link-input')
              .each(function (idx, input) {
                let link_type = $(input).data('type');
                let link_meta_id = $(input).data('meta-id');
                let link_val = $(input).val();

                let has_value = is_link_field_value_already_in_primary(
                  post_field_id,
                  link_type,
                  link_meta_id,
                  link_val,
                  true,
                );
                let matched_meta_id = is_link_field_value_already_in_primary(
                  post_field_id,
                  link_type,
                  link_meta_id,
                  link_val,
                  false,
                );

                if (link_val && !has_value) {
                  link_entries.push({
                    value: link_val,
                    type: link_type,
                    meta_id: matched_meta_id ? link_meta_id : '',
                  });
                }
              });

            $.each(link_deletions, function (idx, deleted_meta_id) {
              link_entries.push({
                meta_id: deleted_meta_id,
                delete: true,
              });
            });

            // If present, capture entries
            if (link_entries) {
              values[post_field_id] = {
                values: link_entries,
              };
            }
            break;
          }

          case 'user_select': {
            let user_select_typeahead =
              window.Typeahead['.js-typeahead-' + field_id];
            if (user_select_typeahead && user_select_typeahead.item) {
              values[post_field_id] =
                'user-' + user_select_typeahead.item['ID'];
            }
            break;
          }
        }
      });

    // Submit packaged fields to backend for further processing
    if (values) {
      // Determine current primary & archiving post records, + others
      let post_type = window.merge_post_details['post_settings']['post_type'];
      let primary_post = fetch_post_by_merge_type(true);
      let archiving_post = fetch_post_by_merge_type(false);

      // Build payload accordingly, based on updated values
      let payload = {
        post_type: post_type,
        primary_post_id: primary_post['record']['ID'],
        archiving_post_id: archiving_post['record']['ID'],
        merge_comments: $('#merge_comments').is(':checked'),
        values: values,
      };

      console.log(payload);

      // Finally dispatch payload to corresponding post type merge endpoint
      window
        .makeRequestOnPosts(
          'POST',
          window.SHAREDFUNCTIONS.escapeHTML(post_type) + '/merge',
          payload,
        )
        .then((resp) => {
          window.location = primary_post['record']['ID'];
        })
        .catch((err) => {
          console.error(err);
          $('.submit-merge').toggleClass('loading').attr('disabled', false);
          $('.merge_errors').html(
            window.SHAREDFUNCTIONS.escapeHTML(
              window.merge_post_details['translations']['error_msg'],
            ) +
              ' : ' +
              window.SHAREDFUNCTIONS.escapeHTML(
                window.lodash.get(err, 'responseJSON.message', err),
              ),
          );
        });
    } else {
      $('.submit-merge').toggleClass('loading').attr('disabled', false);
    }
  }

  // sync scroll across different posts
  $('.post-scroll-window').on('scroll', function (evt) {
    const scrollWindow = evt.currentTarget;
    $('.post-scroll-window').each((idx, el) => {
      if (el.id !== scrollWindow.id) {
        el.scrollTop = scrollWindow.scrollTop;
      }
    });
  });
});
