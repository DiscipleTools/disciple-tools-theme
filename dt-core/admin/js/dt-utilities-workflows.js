jQuery(function ($) {

    /*** Event Listeners ***/
    $(document).on('click', '.workflows-post-types-section-buttons', function (e) {
      handle_workflow_post_type_select(e);
    });

    $(document).on('click', '.workflows-management-section-workflow-name', function (e) {
      handle_workflow_manage_select(e);
    });

    $(document).on('click', '#workflows_management_section_new_but', function () {
      handle_new_workflow_request();
    });

    $(document).on('click', '#workflows_design_section_step1_next_but', function () {
      handle_workflow_step1_next_request();
    });

    $(document).on('click', '#workflows_design_section_step2_next_but', function () {
      handle_workflow_step2_next_request();
    });

    $(document).on('change', '#workflows_design_section_step2_fields', function () {
      handle_workflow_step_fields_select(true, $('#workflows_design_section_step2_fields'), $('#workflows_design_section_step2_conditions'), $('#workflows_design_section_step2_condition_value_div'), $('#workflows_design_section_step2_condition_value_id'), $('#workflows_design_section_step2_condition_value_object_id'));
    });

    $(document).on('change', '#workflows_design_section_step2_conditions', function () {
      handle_workflow_step2_conditions_select();
    });

    $(document).on('click', '#workflows_design_section_step2_condition_add', function () {
      let field_id = $('#workflows_design_section_step2_fields').val();
      let field_name = $('#workflows_design_section_step2_fields option:selected').text();

      let condition_id = $('#workflows_design_section_step2_conditions').val();
      let condition_name = $('#workflows_design_section_step2_conditions option:selected').text();

      let condition_value_element = $('#' + $('#workflows_design_section_step2_condition_value_id').val());
      let condition_value_id = null;
      let condition_value_name = null;

      // Determine dynamic field element type, in order to know how best to extract values!
      if (condition_value_element.hasClass('dt-typeahead')) { // Typeahead
        condition_value_id = $('#workflows_design_section_step2_condition_value_object_id').val();
        condition_value_name = condition_value_element.val();

      } else if (condition_value_element.is('select')) { // Select Dropdown
        condition_value_id = condition_value_element.val();
        condition_value_name = condition_value_element.find('option:selected').text();

      } else if (condition_value_element.hasClass('dt-datepicker')) { // Date Range Picker
        condition_value_id = $('#workflows_design_section_step2_condition_value_object_id').val();
        condition_value_name = condition_value_element.val();

      } else { // Regular Textfield
        condition_value_id = condition_value_name = condition_value_element.val();
      }

      handle_workflow_step_event_add_request(true, field_id, field_name, condition_id, condition_name, condition_value_id, condition_value_name, $('#workflows_design_section_step2_exception_message'), function () {
        // Reset values following addition
        $('#workflows_design_section_step2_fields').val('');
        $('#workflows_design_section_step2_conditions').val('');
        let value_div = $('#workflows_design_section_step2_condition_value_div');
        value_div.html('--- dynamic condition value field ---');
        value_div.fadeIn('fast');
        $('#workflows_design_section_step2_exception_message').html('');
      });
    });

    $(document).on('click', '.workflows-design-section-step2-condition-remove', function (e) {
      handle_workflow_step_event_remove_request(e);
    });

    $(document).on('click', '#workflows_design_section_step3_next_but', function () {
      handle_workflow_step3_next_request();
    });

    $(document).on('change', '#workflows_design_section_step3_fields', function () {
      handle_workflow_step_fields_select(false, $('#workflows_design_section_step3_fields'), $('#workflows_design_section_step3_actions'), $('#workflows_design_section_step3_action_value_div'), $('#workflows_design_section_step3_action_value_id'), $('#workflows_design_section_step3_action_value_object_id'));
    });

    $(document).on('change', '#workflows_design_section_step3_actions', function () {
      handle_workflow_step3_actions_select();
    });

    $(document).on('click', '#workflows_design_section_step3_action_add', function () {
      let field_id = $('#workflows_design_section_step3_fields').val();
      let field_name = $('#workflows_design_section_step3_fields option:selected').text();

      let action_id = $('#workflows_design_section_step3_actions').val();
      let action_name = $('#workflows_design_section_step3_actions option:selected').text();

      let action_value_element = $('#' + $('#workflows_design_section_step3_action_value_id').val());
      let action_value_id = null;
      let action_value_name = null;

      // Determine dynamic field element type, in order to know how best to extract values!
      if (action_value_element.hasClass('dt-typeahead')) { // Typeahead
        action_value_id = $('#workflows_design_section_step3_action_value_object_id').val();
        action_value_name = action_value_element.val();

      } else if (action_value_element.is('select')) { // Select Dropdown
        action_value_id = action_value_element.val();
        action_value_name = action_value_element.find('option:selected').text();

      } else if (action_value_element.hasClass('dt-datepicker')) { // Date Range Picker
        action_value_id = $('#workflows_design_section_step3_action_value_object_id').val();
        action_value_name = action_value_element.val();

      } else { // Regular Textfield
        action_value_id = action_value_name = action_value_element.val();
      }

      handle_workflow_step_event_add_request(false, field_id, field_name, action_id, action_name, action_value_id, action_value_name, $('#workflows_design_section_step3_exception_message'), function () {
        // Reset values following addition
        $('#workflows_design_section_step3_fields').val('');
        $('#workflows_design_section_step3_actions').val('');
        let value_div = $('#workflows_design_section_step3_action_value_div');
        value_div.html('--- dynamic action value field ---');
        value_div.fadeIn('fast');
        $('#workflows_design_section_step3_exception_message').html('');
      });
    });

    $(document).on('click', '.workflows-design-section-step3-action-remove', function (e) {
      handle_workflow_step_event_remove_request(e);
    });

    $(document).on('click', '#workflows_design_section_save_but', function () {
      handle_workflow_save_request();
    });

    /*** Event Listeners ***/

    /*** Event Listeners - Header Functions ***/
    function handle_workflow_post_type_select(evt) {

      // Fetch post type details
      let post_type_but = $(evt.currentTarget);
      let td_parent = $(evt.currentTarget.parentNode);
      let post_type_id = td_parent.find('#workflows_post_types_section_post_type_id').val();
      let post_type_name = td_parent.find('#workflows_post_types_section_post_type_name').val();

      // Assuming we have a valid selection, post request
      if (post_type_id && post_type_name) {
        $('#workflows_post_types_section_form_post_type_id').val(post_type_id);
        $('#workflows_post_types_section_form_post_type_name').val(post_type_name);

        // Submit selection
        $('#workflows_post_types_section_form').submit();
      }
    }

    function handle_workflow_manage_select(evt) {
      let workflow_id = $(evt.currentTarget).data('workflowId');
      let workflow_name = $(evt.currentTarget).data('workflowName');

      if (workflow_id) {

        // Update hidden workflow id
        $('#workflows_design_section_hidden_workflow_id').val(workflow_id);

        $('#workflows_design_section_div').fadeOut('slow', function () {

          // Reset associated component views
          new_workflow_view_reset(function () {

            // Display new workflow canvas
            $('#workflows_design_section_div').fadeIn('fast', function () {

              // Parse hidden workflows in search of the one!
              let workflow = find_parsed_workflow(true, workflow_id);
              if (workflow) {

                display_regular_workflow(workflow);

              } else if (workflow_id.startsWith('default_')) {

                workflow = find_parsed_workflow(false, workflow_id.split('default_')[1]);
                display_default_workflow(workflow);

              }

              // If still unable to locate a valid workflow, default to just the first step!
              if (!workflow) {
                $('#workflows_design_section_step1').fadeIn('fast', function () {
                });
              }
            });
          });
        });
      }
    }

    function handle_new_workflow_request() {

      // Update hidden workflow id
      $('#workflows_design_section_hidden_workflow_id').val(Math.floor(Date.now() / 1000));

      $('#workflows_design_section_div').fadeOut('slow', function () {

        // Reset associated component views
        new_workflow_view_reset(function () {

          // Display new workflow canvas
          $('#workflows_design_section_div').fadeIn('fast', function () {

            // Display step 1
            $('#workflows_design_section_step1').fadeIn('fast', function () {
            });
          });
        });
      });
    }

    function handle_workflow_step1_next_request() {
      $('#workflows_design_section_step2').slideDown('fast', function () {
      });
    }

    function handle_workflow_step2_next_request() {
      $('#workflows_design_section_step3').slideDown('fast', function () {
      });
    }

    function handle_workflow_step_fields_select(is_condition, fields_select, events_select, event_value_div, event_value_id, event_value_object_id) {
      // Remove current options
      events_select.find('option[value != ""]').remove();

      if (fields_select.val()) {

        // If valid events found, reset options!
        let events = fetch_event_options(fields_select.val(), is_condition);
        if (events) {

          // Append new options
          events.forEach(function (event, idx) {
            if (event['id'] && event['name']) {
              let option = `<option value="${window.lodash.escape(event['id'])}">${window.lodash.escape(event['name'])}</option>`;
              events_select.append(option);
            }
          });
        }

        // Determine event value field state
        determine_event_value_state(fields_select.val(), event_value_div, event_value_id, event_value_object_id);

      }

      // Reset condition selection
      events_select.val("");
    }

    function handle_workflow_step2_conditions_select() {
      let condition = $('#workflows_design_section_step2_conditions').val();

      // Ensure value fields are disabled for is/not_set conditions
      if ((String(condition) === 'is_set') || (String(condition) === 'not_set')) {
        $('#workflows_design_section_step2_condition_value_div').fadeOut('fast');
      } else {
        $('#workflows_design_section_step2_condition_value_div').fadeIn('fast');
      }
    }

    function handle_workflow_step_event_add_request(is_condition, field_id, field_name, event_id, event_name, event_value_id, event_value_name, exception_element, callback) {

      let added_new_row = false;
      if (field_id && field_name && event_id && event_name) {

        if (is_condition) {

          let add_condition = false;

          // Ignore value on is/not_set conditions
          if ((String(event_id) === 'is_set') || (String(event_id) === 'not_set')) {
            event_value_id = '';
            event_value_name = '';
            add_condition = true;

          } else {
            add_condition = ((event_value_id !== null) && (event_value_id !== ''));
          }

          if (add_condition) {
            add_new_condition_row(field_id, field_name, event_id, event_name, event_value_id, event_value_name);
            added_new_row = true;
          }

        } else if (event_value_id) {
          add_new_action_row(field_id, field_name, event_id, event_name, event_value_id, event_value_name);
          added_new_row = true;
        }
      }

      // Only trigger callback following successful row addition
      if (added_new_row) {
        callback();
      } else {
        exception_element.html(`Please select a valid field, ${(is_condition ? 'condition' : 'action')} and value!`);
      }
    }

    function handle_workflow_step_event_remove_request(evt) {
      // Obtain handle onto deleted row
      let row = evt.currentTarget.parentNode.parentNode.parentNode;

      // Remove row from parent table
      row.parentNode.removeChild(row);
    }

    function handle_workflow_step3_actions_select() {
      let action = $('#workflows_design_section_step3_actions').val();

      // Display valid list of custom actions, accordingly
      if (String(action) === 'custom') {
        handle_custom_action_select();

      } else {
        // Revert back to value field shape based on selected field
        determine_event_value_state($('#workflows_design_section_step3_fields').val(), $('#workflows_design_section_step3_action_value_div'), $('#workflows_design_section_step3_action_value_id'), $('#workflows_design_section_step3_action_value_object_id'));
      }
    }

    function handle_workflow_step3_next_request() {
      $('#workflows_design_section_step4').slideDown('fast', function () {

        // Display workflow save option
        $('#workflows_design_section_save_but').fadeIn('fast', function () {
        });
      });
    }

    /*** Event Listeners - Header Functions ***/


    /*** Header Functions - Helpers ***/
    function handle_custom_action_select() {
      $('#workflows_design_section_step3_action_value_div').fadeOut('fast', function () {

        // Build custom actions select element
        let custom_actions_select_element = generate_custom_actions_list(window.dt_workflows.workflows_design_section_hidden_custom_actions);
        if (custom_actions_select_element) {
          let value_div = $('#workflows_design_section_step3_action_value_div');

          // Capture id of generated element for future reference
          $('#workflows_design_section_step3_action_value_id').val(custom_actions_select_element['id']);

          // Display available custom actions
          value_div.html(custom_actions_select_element['html']);
          value_div.fadeIn('fast');
        }
      });
    }

    function generate_custom_actions_list(actions) {
      if (actions) {

        let response = {};
        response['id'] = Date.now();

        let html = `<select style="min-width: 100%; max-width: 100px;" id="${window.lodash.escape(response['id'])}">`;
        html += '<option disabled selected value="">--- select custom action ---</option>';

        // Iterate over custom actions...
        for (const [key, action] of Object.entries(actions)) {
          if (action['id'] && action['name']) {
            html += `<option value="${window.lodash.escape(action['id'])}">${window.lodash.escape(action['name'])}</option>`;
          }
        }

        html += '</select>';

        response['html'] = html;

        return response;
      }
      return null;
    }

    function find_parsed_workflow(is_regular_workflow, workflow_id) {
      if (is_regular_workflow) {
        let parsed_workflows = JSON.parse($('#workflows_management_section_hidden_option_post_type_workflows').val());
        if (parsed_workflows && parsed_workflows['workflows']) {
          return parsed_workflows['workflows'][workflow_id];
        }
      } else {
        let parsed_workflows = JSON.parse($('#workflows_management_section_hidden_filtered_workflows_defaults').val());

        let workflow = null;
        if (parsed_workflows) {
          parsed_workflows.forEach(function (item) {
            if (String(item['id']) === String(workflow_id)) {
              workflow = item;
            }
          });
        }
        return workflow;
      }

      return null;
    }

    function update_default_workflow_state(workflow) {
      if (workflow) {

        // Fetch latest default workflow option values
        let parsed_default_options = JSON.parse($('#workflows_management_section_hidden_option_default_workflows').val());

        // Update relevant values
        let option = (parsed_default_options['workflows']) ? parsed_default_options['workflows'][workflow['id']] : null;
        if (option) {
          workflow['enabled'] = option['enabled'];
        }
      }

      return workflow;
    }

    function display_regular_workflow(workflow) {
      if (workflow) {
        display_workflow(true, workflow);
      }
    }

    function display_default_workflow(workflow) {
      /*
       * Apart from setting the default workflow's enabled state,
       * they will be shown in a read-only capacity, only!
       *
       * However, ensure to override configured attributes with most
       * recent values; where needed!
       */

      if (workflow) {
        display_workflow(false, update_default_workflow_state(workflow));
      }
    }

    function display_workflow(unlocked, workflow) {
      // Set step 1 elements and display
      set_elements_step1(workflow, function () {
        adjust_elements_step1_locked_state(unlocked, function () {
          $('#workflows_design_section_step1').fadeIn('fast', function () {
          });
        });
      });

      // Set step 2 elements and display
      set_elements_step2(workflow, function () {
        adjust_elements_step2_locked_state(unlocked, handle_workflow_step1_next_request);
      });

      // Set step 3 elements and display
      set_elements_step3(workflow, function () {
        adjust_elements_step3_locked_state(unlocked, handle_workflow_step2_next_request);
      });

      // Set step 4 elements and display
      set_elements_step4(workflow, function () {
        adjust_elements_step4_locked_state(unlocked, handle_workflow_step3_next_request);
      });
    }

    function adjust_elements_step1_locked_state(unlock, callback) {
      $('#workflows_design_section_step1_trigger_created').prop('disabled', !unlock);
      $('#workflows_design_section_step1_trigger_updated').prop('disabled', !unlock);

      let next_but = $('#workflows_design_section_step1_next_but');
      unlock ? next_but.show() : next_but.hide();

      callback();
    }

    function adjust_elements_step2_locked_state(unlock, callback) {
      let fields_tr = $('#workflows_design_section_step2_fields_tr');
      let conditions_tr = $('#workflows_design_section_step2_conditions_tr');
      let condition_value_tr = $('#workflows_design_section_step2_condition_value_tr');
      let condition_remove_but = $('.workflows-design-section-step2-condition-remove');
      let next_but = $('#workflows_design_section_step2_next_but');

      unlock ? fields_tr.show() : fields_tr.hide();
      unlock ? conditions_tr.show() : conditions_tr.hide();
      unlock ? condition_value_tr.show() : condition_value_tr.hide();
      unlock ? condition_remove_but.show() : condition_remove_but.hide();
      unlock ? next_but.show() : next_but.hide();

      callback();
    }

    function adjust_elements_step3_locked_state(unlock, callback) {
      let fields_tr = $('#workflows_design_section_step3_fields_tr');
      let actions_tr = $('#workflows_design_section_step3_actions_tr');
      let action_value_tr = $('#workflows_design_section_step3_action_value_tr');
      let action_remove_but = $('.workflows-design-section-step3-action-remove');
      let next_but = $('#workflows_design_section_step3_next_but');

      unlock ? fields_tr.show() : fields_tr.hide();
      unlock ? actions_tr.show() : actions_tr.hide();
      unlock ? action_value_tr.show() : action_value_tr.hide();
      unlock ? action_remove_but.show() : action_remove_but.hide();
      unlock ? next_but.show() : next_but.hide();

      callback();
    }

    function adjust_elements_step4_locked_state(unlock, callback) {
      $('#workflows_design_section_step4_title').prop('readonly', !unlock);

      callback();
    }

    function new_workflow_view_reset(callback) {

      // Reset workflow steps
      $('#workflows_design_section_save_but').fadeOut('fast', function () {
        $('#workflows_design_section_step4').fadeOut('fast', function () {

          // Reset step 4 elements!
          adjust_elements_step4_locked_state(true, reset_step4_elements);

          $('#workflows_design_section_step3').fadeOut('fast', function () {

            // Reset step 3 elements!
            adjust_elements_step3_locked_state(true, reset_step3_elements);

            $('#workflows_design_section_step2').fadeOut('fast', function () {

              // Reset step 2 elements!
              adjust_elements_step2_locked_state(true, reset_step2_elements);

              $('#workflows_design_section_step1').fadeOut('fast', function () {

                // Reset step 1 elements!
                adjust_elements_step1_locked_state(true, reset_step1_elements);

                // Execute callback
                callback();
              });
            });
          });
        });
      });
    }

    function reset_step1_elements() {
      $('#workflows_design_section_step1_trigger_created').prop('checked', true);
    }

    function reset_step2_elements() {
      let fields_select = $('#workflows_design_section_step2_fields');
      let conditions_select = $('#workflows_design_section_step2_conditions');
      let conditions_table = $('#workflows_design_section_step2_conditions_table');

      reset_elements(fields_select, conditions_select, conditions_table, function () {
        let value_div = $('#workflows_design_section_step2_condition_value_div');
        value_div.html('--- dynamic condition value field ---');
        value_div.fadeIn('fast');
        $('#workflows_design_section_step2_exception_message').html('');
      });
    }

    function reset_step3_elements() {
      let fields_select = $('#workflows_design_section_step3_fields');
      let actions_select = $('#workflows_design_section_step3_actions');
      let actions_table = $('#workflows_design_section_step3_actions_table');

      reset_elements(fields_select, actions_select, actions_table, function () {
        let value_div = $('#workflows_design_section_step3_action_value_div');
        value_div.html('--- dynamic action value field ---');
        value_div.fadeIn('fast');
        $('#workflows_design_section_step3_exception_message').html('');
      });
    }

    function reset_step4_elements() {
      $('#workflows_design_section_step4_title').val('');
      $('#workflows_design_section_step4_enabled').prop('checked', true);
      $('#workflows_design_section_step4_exception_message').html('');
    }

    function reset_elements(fields_select, events_select, events_table, callback) {
      let selected_post_type = $('#workflows_design_section_hidden_selected_post_type_id').val();
      let post_types = window.dt_workflows.workflows_design_section_hidden_post_types;

      // Remove previous options prior to re-populating
      fields_select.find('option[value != ""]').remove();
      events_select.find('option[value != ""]').remove();
      events_table.find('tbody tr').remove();

      // Re-populate field options
      let post_type = post_types[selected_post_type];
      if (post_type) {

        // Sort fields into ascending order
        let fields = post_type['fields'];
        fields.sort(function (a, b) {
          return sort_by_string(a['name'].toUpperCase(), b['name'].toUpperCase());
        });

        // Add sorted field names
        fields.forEach(function (field, idx) {
          if (field['id'] && field['name']) {
            let option = `<option value="${window.lodash.escape(field['id'])}">${window.lodash.escape(field['name'])}</option>`;
            fields_select.append(option);
          }
        });
      }

      // Reset select option selections
      fields_select.val("");
      events_select.val("");

      // Trigger callback
      callback();

    }

    function fetch_event_options(field_id, is_condition) {
      let selected_post_type = $('#workflows_design_section_hidden_selected_post_type_id').val();
      let post_types = window.dt_workflows.workflows_design_section_hidden_post_types;
      let post_field_types = window.dt_workflows.workflows_design_section_hidden_post_field_types;

      let events = [];

      // Need to determine field type in order to identify associated event options
      let post_type = post_types[selected_post_type];
      if (post_type) {

        let fields = post_type['fields'];
        fields.forEach(function (field, idx) {
          if (field['id'] === field_id) {

            // Return according to field type!
            if (is_condition) {
              events = fetch_condition_options(field['type']);
            } else {
              events = fetch_action_options(field['type']);
            }
          }
        });
      }

      return events;
    }

    function fetch_condition_options(field_type) {
      let conditions = [];

      let labels = {
        equals: "Equals",
        not_equals: "Doesn't equal",
        contains: "Contains",
        not_contain: "Doesn't contain",
        is_set: "Has any value and not empty",
        not_set: "Has no value or is empty",
        greater: "Greater than",
        less: "Less than",
        greater_equals: "Greater than or equals",
        less_equals: "Less than or equals",
      }

      switch (field_type) {
        case "text":
          conditions.push(
            {
              'id': 'equals',
              'name': labels.equals
            },
            {
              'id': 'not_equals',
              'name': labels.not_equals
            },
            {
              'id': 'contains',
              'name': labels.contains
            },
            {
              'id': 'not_contain',
              'name': labels.not_contain
            },
            {
              'id': 'is_set',
              'name': labels.is_set
            },
            {
              'id': 'not_set',
              'name': labels.not_set
            }
          );
          break;
        case "number":
        case "date":
          conditions.push(
            {
              'id': 'equals',
              'name': labels.equals
            },
            {
              'id': 'not_equals',
              'name': labels.not_equals
            },
            {
              'id': 'greater',
              'name': labels.greater
            },
            {
              'id': 'less',
              'name': labels.less
            },
            {
              'id': 'greater_equals',
              'name': labels.greater_equals
            },
            {
              'id': 'less_equals',
              'name': labels.less_equals
            },
            {
              'id': 'is_set',
              'name': labels.is_set
            },
            {
              'id': 'not_set',
              'name': labels.not_set
            }
          );
          break;
        case "boolean":
          conditions.push(
            {
              'id': 'equals',
              'name': labels.equals
            },
            {
              'id': 'not_equals',
              'name': labels.not_equals
            },
            {
              'id': 'is_set',
              'name': labels.is_set
            },
            {
              'id': 'not_set',
              'name': labels.not_set
            }
          );
          break;
        case "tags":
        case "multi_select":
        case "key_select":
        case "array":
        case "task":
        case "communication_channel":
        case "location":
        case "location_meta":
        case "connection":
        case "user_select":
        case "post_user_meta":
        case "datetime_series":
        case "hash":
          conditions.push(
            {
              'id': 'contains',
              'name': labels.contains
            },
            {
              'id': 'not_contain',
              'name': labels.not_contain
            },
            {
              'id': 'is_set',
              'name': labels.is_set
            },
            {
              'id': 'not_set',
              'name': labels.not_set
            }
          );
          break;
      }

      return conditions;
    }

    function fetch_action_options(field_type) {
      let actions = [];

      switch (field_type) {
        case "text":
        case "number":
        case "date":
        case "boolean":
        case "key_select":
        case "user_select":
          actions.push(
            {
              'id': 'update',
              'name': 'Update To'
            }
          );
          break;
        case "tags":
        case "array":
        case "task":
        case "communication_channel":
        case "location":
        case "location_meta":
        case "post_user_meta":
        case "datetime_series":
        case "hash":
          actions.push(
            {
              'id': 'append',
              'name': 'Appended With'
            }
          );
          break;
        case "multi_select":
          actions.push(
            {
              'id': 'append',
              'name': 'Add'
            },
            {
              'id': 'remove',
              'name': 'Remove'
            }
          );
          break;
        case "connection":
          actions.push(
            {
              'id': 'connect',
              'name': 'Connect To'
            },
            {
              'id': 'remove',
              'name': 'Removal Of'
            }
          );
          break;
      }

      // Append custom option if any custom actions are detected
      let custom_actions = window.dt_workflows.workflows_design_section_hidden_custom_actions;
      if (custom_actions && custom_actions.length > 0) {
        actions.push(
          {
            'id': 'custom',
            'name': 'Custom Action'
          }
        );
      }

      return actions;
    }

    function add_new_condition_row(field_id, field_name, condition_id, condition_name, condition_value_id, condition_value_name) {
      let html = '<tr>';

      // Field
      html += '<td style="vertical-align: middle;">';
      html += `<input id="workflows_design_section_step2_conditions_table_field_id" type="hidden" value="${window.lodash.escape(field_id)}">`;
      html += `<input id="workflows_design_section_step2_conditions_table_field_name" type="hidden" value="${window.lodash.escape(field_name)}">`;
      html += window.lodash.escape(field_name);
      html += '</td>';

      // Condition
      html += '<td style="vertical-align: middle;">';
      html += `<input id="workflows_design_section_step2_conditions_table_condition_id" type="hidden" value="${window.lodash.escape(condition_id)}">`;
      html += `<input id="workflows_design_section_step2_conditions_table_condition_name" type="hidden" value="${window.lodash.escape(condition_name)}">`;
      html += window.lodash.escape(condition_name);
      html += '</td>';

      // Value
      html += '<td style="vertical-align: middle;">';
      html += `<input id="workflows_design_section_step2_conditions_table_condition_value" type="hidden" value="${window.lodash.escape(condition_value_id)}">`;
      html += `<input id="workflows_design_section_step2_conditions_table_condition_value_name" type="hidden" value="${window.lodash.escape(condition_value_name)}">`;
      html += window.lodash.escape(condition_value_name);
      html += '</td>';

      // Removal Button
      html += '<td style="vertical-align: middle;">';
      html += '<span style="float:right;">';
      html += '<a id="workflows_design_section_step2_condition_remove" class="button float-right workflows-design-section-step2-condition-remove">Remove</a>';
      html += '</span>';
      html += '</td>';

      html += '</tr>';

      // Add newly formed row!
      $('#workflows_design_section_step2_conditions_table tbody').append(html);
    }

    function add_new_action_row(field_id, field_name, action_id, action_name, action_value_id, action_value_name) {
      let html = '<tr>';

      // Field
      html += '<td style="vertical-align: middle;">';
      html += `<input id="workflows_design_section_step3_actions_table_field_id" type="hidden" value="${window.lodash.escape(field_id)}">`;
      html += `<input id="workflows_design_section_step3_actions_table_field_name" type="hidden" value="${window.lodash.escape(field_name)}">`;
      html += window.lodash.escape(field_name);
      html += '</td>';

      // Condition
      html += '<td style="vertical-align: middle;">';
      html += `<input id="workflows_design_section_step3_actions_table_action_id" type="hidden" value="${window.lodash.escape(action_id)}">`;
      html += `<input id="workflows_design_section_step3_actions_table_action_name" type="hidden" value="${window.lodash.escape(action_name)}">`;
      html += window.lodash.escape(action_name);
      html += '</td>';

      // Value
      html += '<td style="vertical-align: middle;">';
      html += `<input id="workflows_design_section_step3_actions_table_action_value" type="hidden" value="${window.lodash.escape(action_value_id)}">`;
      html += `<input id="workflows_design_section_step3_actions_table_action_value_name" type="hidden" value="${window.lodash.escape(action_value_name)}">`;
      html += window.lodash.escape(action_value_name);
      html += '</td>';

      // Removal Button
      html += '<td style="vertical-align: middle;">';
      html += '<span style="float:right;">';
      html += '<a id="workflows_design_section_step3_action_remove" class="button float-right workflows-design-section-step3-action-remove">Remove</a>';
      html += '</span>';
      html += '</td>';

      html += '</tr>';

      // Add newly formed row!
      $('#workflows_design_section_step3_actions_table tbody').append(html);
    }

    function sort_by_string(a, b) {
      if (a < b) {
        return -1;
      }
      if (a > b) {
        return 1;
      }

      // strings must be equal
      return 0;
    }

    function handle_workflow_save_request() {

      // Fetch the various workflow values, to be packaged later
      let post_type_id = $('#workflows_design_section_hidden_selected_post_type_id').val();
      let post_type_name = $('#workflows_design_section_hidden_selected_post_type_name').val();

      let hidden_workflow_id = $('#workflows_design_section_hidden_workflow_id');
      let is_regular_workflow = !hidden_workflow_id.val().startsWith('default_');

      let workflow_id = is_regular_workflow ? hidden_workflow_id.val() : hidden_workflow_id.val().split('default_')[1];
      let workflow_name = $('#workflows_design_section_step4_title').val();
      let workflow_enabled = $('#workflows_design_section_step4_enabled').is(':checked');

      let trigger = fetch_trigger_value();

      let conditions = fetch_event_values(
        'workflows_design_section_step2_conditions_table',
        'workflows_design_section_step2_conditions_table_field_id',
        'workflows_design_section_step2_conditions_table_field_name',
        'workflows_design_section_step2_conditions_table_condition_id',
        'workflows_design_section_step2_conditions_table_condition_name',
        'workflows_design_section_step2_conditions_table_condition_value',
        'workflows_design_section_step2_conditions_table_condition_value_name'
      );

      let actions = fetch_event_values(
        'workflows_design_section_step3_actions_table',
        'workflows_design_section_step3_actions_table_field_id',
        'workflows_design_section_step3_actions_table_field_name',
        'workflows_design_section_step3_actions_table_action_id',
        'workflows_design_section_step3_actions_table_action_name',
        'workflows_design_section_step3_actions_table_action_value',
        'workflows_design_section_step3_actions_table_action_value_name'
      );

      // If all is valid, package into post type workflow object
      if (post_type_id && post_type_name && workflow_id && workflow_name && trigger && (actions.length > 0)) {

        // Package!
        let post_type_workflow_obj = {
          'post_type_id': post_type_id,
          'post_type_name': post_type_name,

          'is_regular_workflow': is_regular_workflow,
          'workflow_id': workflow_id,
          'workflow_name': workflow_name,
          'workflow_enabled': workflow_enabled,

          'trigger': trigger,
          'conditions': conditions,
          'actions': actions
        };
        $('#workflows_design_section_form_post_type_workflow').val(JSON.stringify(post_type_workflow_obj));

        // Post!
        $('#workflows_design_section_form').submit();

      } else {
        $('#workflows_design_section_step4_exception_message').html('Please ensure a valid workflow name and actions have been selected!');
      }
    }

    function fetch_trigger_value() {
      if ($('#workflows_design_section_step1_trigger_created').is(':checked')) {
        return 'created';

      } else if ($('#workflows_design_section_step1_trigger_updated').is(':checked')) {
        return 'updated';
      }

      return '';
    }

    function fetch_event_values(id_table, id_field_id, id_field_name, id_event_id, id_event_name, id_event_value, id_event_value_name) {
      let events = [];

      // Iterate over specified table
      $('#' + id_table + ' > tbody > tr').each(function (idx, tr) {

        let field_id = $(tr).find('#' + id_field_id).val();
        let field_name = $(tr).find('#' + id_field_name).val();

        let event_id = $(tr).find('#' + id_event_id).val();
        let event_name = $(tr).find('#' + id_event_name).val();

        let event_value = $(tr).find('#' + id_event_value).val();
        let event_value_name = $(tr).find('#' + id_event_value_name).val();

        // Allow blank values on is/not_set conditions
        let add_event = (String(event_id) === 'is_set') || (String(event_id) === 'not_set') ? true : ((event_value !== null) && (event_value !== ''));

        // Are values valid?
        if (field_id && field_name && event_id && event_name && add_event) {
          events.push({
            'id': event_id,
            'name': event_name,
            'value': event_value,
            'value_name': event_value_name,
            'field_id': field_id,
            'field_name': field_name
          });
        }
      });

      return events;
    }

    function set_elements_step1(workflow, callback) {
      let trigger = workflow['trigger'];
      if (trigger === 'created') {
        $('#workflows_design_section_step1_trigger_created').prop('checked', true);

      } else if (trigger === 'updated') {
        $('#workflows_design_section_step1_trigger_updated').prop('checked', true);
      }

      callback();
    }

    function set_elements_step2(workflow, callback) {
      let conditions = workflow['conditions'];
      if (conditions) {
        conditions.forEach(function (condition) {
          add_new_condition_row(
            condition['field_id'],
            condition['field_name'],
            condition['id'],
            condition['name'],
            condition['value'],
            condition['value_name']
          );
        });
      }

      callback();
    }

    function set_elements_step3(workflow, callback) {
      let actions = workflow['actions'];
      if (actions) {
        actions.forEach(function (action) {
          add_new_action_row(
            action['field_id'],
            action['field_name'],
            action['id'],
            action['name'],
            action['value'],
            action['value_name']
          );
        });
      }

      callback();
    }

    function set_elements_step4(workflow, callback) {
      let name = workflow['name'];
      let enabled = workflow['enabled'];

      $('#workflows_design_section_step4_title').val(name);
      $('#workflows_design_section_step4_enabled').prop('checked', enabled);

      callback();
    }

    function determine_event_value_state(field_id, event_value_div, event_value_id, event_value_object_id) {
      let selected_post_type = $('#workflows_design_section_hidden_selected_post_type_id').val();
      let post_types = window.dt_workflows.workflows_design_section_hidden_post_types;

      // Determine field's type
      let post_type = post_types[selected_post_type];
      if (post_type) {

        let fields = post_type['fields'];
        fields.forEach(function (field, idx) {
          if (field['id'] === field_id) {

            // Once field id has been found, determine value field state to be adopted
            let event_value_field = fetch_event_value_field(post_type['base_url'], field);
            if (event_value_field) {

              // Capture generated event value field's id; to be used during event add request
              event_value_id.val(event_value_field['id']);

              event_value_div.fadeOut('fast', function () {
                event_value_div.html(event_value_field['html']);

                // Handle typeahead dynamic fields
                if (event_value_field['typeahead']) {
                  handle_event_value_field_typeaheads(post_type['wp_nonce'], event_value_field, event_value_object_id);
                }

                // Handle datepicker dynamic fields
                if (event_value_field['datepicker']) {
                  handle_event_value_field_datepickers(event_value_field, event_value_object_id);
                }

                // Display dynamic fields
                event_value_div.fadeIn('fast', function () {
                });
              });
            }
          }
        });
      }
    }

    function handle_event_value_field_typeaheads(wp_nonce, field, event_value_object_id) {

      if (field['typeahead']) {
        let typeahead = field['typeahead'];

        // Instantiate the typeahead element
        let dynamic_field = $('#' + field['id']);
        dynamic_field.typeahead({
          order: "asc",
          accent: true,
          minLength: 0,
          maxItem: 10,
          dynamic: true,
          searchOnFocus: true,
          source: typeahead['endpoint'](wp_nonce),
          callback: {
            onClick: function (node, a, item, event) {
              let id = typeahead['id_func'](item);
              if (id) {
                event_value_object_id.val(id);
              }
            }
          }
        });
      }
    }

    function handle_event_value_field_datepickers(field, event_value_object_id) {

      let dynamic_field = $('#' + field['id']);

      // Instantiate date range picker dynamic field.
      dynamic_field.daterangepicker({
        singleDatePicker: true,
        timePicker: false,
        locale: {
          format: 'YYYY-MM-DD'
        }
      }, function (start, end, label) {
        // As we are in single date picker mode, just focus on start date and convert to epoch timestamp.
        if (start) {

          // Capture timestamp within hidden object id field for further processing downstream.
          event_value_object_id.val(start.unix());
        }
      });

      // Empty field value by default, so as to prompt a selection.
      dynamic_field.val('');

      // Bind to apply and cancel events and reset
      dynamic_field.on('apply.daterangepicker', function (ev, picker) {
        event_value_object_id.val(picker.startDate.unix());
      });

      dynamic_field.on('cancel.daterangepicker', function (ev, picker) {
        dynamic_field.val('');
      });
    }

    function fetch_event_value_field(base_url, field) {
      switch (field['type']) {
        case "text":
        case "number":
        case "tags":
        case "communication_channel":
          return generate_event_value_textfield();
        case "date":
          return generate_event_value_datepicker();
        case "boolean":
          return generate_event_value_boolean();
        case "multi_select":
        case "key_select":
          return generate_event_value_defaults(field['defaults']);
        case "location":
          return generate_event_value_locations(base_url);
        case "connection":
          return generate_event_value_connections(base_url, field);
        case "user_select":
          return generate_event_value_user_select(base_url);
        case "array":
        case "task":
        case "location_meta":
        case "post_user_meta":
        case "datetime_series":
        case "hash":
          // Ignored!
          break;
      }

      return null;
    }

    function generate_event_value_textfield() {
      let response = {};
      response['id'] = Date.now();
      response['html'] = `<input type="text" placeholder="Enter a value..." style="min-width: 100%;" id="${window.lodash.escape(response['id'])}">`;

      return response;
    }

    function generate_event_value_datepicker() {
      let response = {};
      response['id'] = Date.now();
      response['html'] = `<input type="text" class="dt-datepicker" placeholder="Enter a date..." style="min-width: 100%;" id="${window.lodash.escape(response['id'])}">`;
      response['datepicker'] = {};

      return response;
    }

    function generate_event_value_boolean() {
      let response = {};
      response['id'] = Date.now();

      let html = `<select style="min-width: 100%;" id="${window.lodash.escape(response['id'])}">`;
      html += '<option value="true" selected>True</option>';
      html += '<option value="false">False</option>';
      html += '</select>';

      response['html'] = html;

      return response;
    }

    function generate_event_value_defaults(defaults) {
      if (defaults) {

        let response = {};
        response['id'] = Date.now();

        let html = `<select style="min-width: 100%; max-width: 100px;" id="${window.lodash.escape(response['id'])}">`;
        html += '<option disabled selected value="">--- select value ---</option>';

        // Iterate over field defaults...
        for (const [key, value] of Object.entries(defaults)) {
          //if (value['label']) { // As an empty label string is actually a valid entry!
          html += `<option value="${window.lodash.escape(key)}">${window.lodash.escape(value['label'])}</option>`;
          //}
        }

        html += '</select>';

        response['html'] = html;

        return response;
      }
      return null;
    }

    function generate_event_value_locations(base_url) {
      let response = {};
      response['id'] = Date.now();

      let html = '<div class="typeahead__container"><div class="typeahead__field"><div class="typeahead__query">';
      html += `<input type="text" class="dt-typeahead" autocomplete="off" placeholder="Start typing a location..." style="min-width: 100%;" id="${window.lodash.escape(response['id'])}">`;
      html += '</div></div></div>';
      response['html'] = html;

      response['typeahead'] = {
        endpoint: function (wp_nonce) {
          return {
            locations: {
              display: ["name", "ID"],
              template: "<span>{{name}}</span>",
              ajax: {
                url: base_url + 'dt/v1/mapping_module/search_location_grid_by_name?filter=all',
                data: {
                  s: '{{query}}'
                },
                beforeSend: function (xhr) {
                  xhr.setRequestHeader("X-WP-Nonce", wp_nonce);
                },
                callback: {
                  done: function (response) {
                    return (response['location_grid']) ? response['location_grid'] : [];
                  }
                }
              }
            }
          }
        },
        id_func: function (item) {
          if (item && item['ID']) {
            return item['ID'];
          }
          return null;
        }
      };

      return response;
    }

    function generate_event_value_connections(base_url, field) {
      if (field && field['id'] && field['post_type']) {

        let response = {};
        response['id'] = Date.now();

        let html = '<div class="typeahead__container"><div class="typeahead__field"><div class="typeahead__query">';
        html += `<input type="text" class="dt-typeahead" autocomplete="off" placeholder="Start typing connection details..." style="min-width: 100%;" id="${window.lodash.escape(response['id'])}">`;
        html += '</div></div></div>';
        response['html'] = html;

        response['typeahead'] = {
          endpoint: function (wp_nonce) {
            return {
              connections: {
                display: ["name", "ID"],
                template: "<span>{{name}}</span>",
                ajax: {
                  url: base_url + 'dt-posts/v2/' + field['post_type'] + '/compact?field_key=' + field['id'],
                  data: {
                    s: '{{query}}'
                  },
                  beforeSend: function (xhr) {
                    xhr.setRequestHeader("X-WP-Nonce", wp_nonce);
                  },
                  callback: {
                    done: function (response) {
                      return (response['posts']) ? response['posts'] : [];
                    }
                  }
                }
              }
            }
          },
          id_func: function (item) {
            if (item && item['ID']) {
              return item['ID'];
            }
            return null;
          }
        };

        return response;
      }
      return null;
    }

    function generate_event_value_user_select(base_url) {
      let response = {};
      response['id'] = Date.now();

      let html = '<div class="typeahead__container"><div class="typeahead__field"><div class="typeahead__query">';
      html += `<input type="text" class="dt-typeahead" autocomplete="off" placeholder="Start typing user details..." style="min-width: 100%;" id="${window.lodash.escape(response['id'])}">`;
      html += '</div></div></div>';
      response['html'] = html;

      response['typeahead'] = {
        endpoint: function (wp_nonce) {
          return {
            users: {
              display: ["name", "ID"],
              template: "<span>{{name}}</span>",
              ajax: {
                url: base_url + 'dt/v1/users/get_users?get_all',
                data: {
                  s: '{{query}}'
                },
                beforeSend: function (xhr) {
                  xhr.setRequestHeader("X-WP-Nonce", wp_nonce);
                },
                callback: {
                  done: function (response) {
                    return response;
                  }
                }
              }
            }
          }
        },
        id_func: function (item) {
          if (item && item['ID']) {
            return 'user-' + item['ID'];
          }
          return null;
        }
      };

      return response;
    }

    /*** Header Functions - Helpers ***/
  }
);
