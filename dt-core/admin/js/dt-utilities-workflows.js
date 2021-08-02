jQuery(function ($) {

  /*** Event Listeners ***/
  $(document).on('click', '.workflows-post-types-section-buttons', function (e) {
    handle_workflow_post_type_select(e);
  });

  $(document).on('change', '#workflows_management_section_select', function () {
    handle_workflow_manage_select();
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
    handle_workflow_step_fields_select(true, $('#workflows_design_section_step2_fields'), $('#workflows_design_section_step2_conditions'));
  });

  $(document).on('click', '#workflows_design_section_step2_condition_add', function () {
    let field_id = $('#workflows_design_section_step2_fields').val();
    let field_name = $('#workflows_design_section_step2_fields option:selected').text();

    let condition_id = $('#workflows_design_section_step2_conditions').val();
    let condition_name = $('#workflows_design_section_step2_conditions option:selected').text();
    let condition_value = $('#workflows_design_section_step2_condition_value').val();

    handle_workflow_step_event_add_request(true, field_id, field_name, condition_id, condition_name, condition_value);
  });

  $(document).on('click', '.workflows-design-section-step2-condition-remove', function (e) {
    handle_workflow_step_event_remove_request(e);
  });

  $(document).on('click', '#workflows_design_section_step3_next_but', function () {
    handle_workflow_step3_next_request();
  });

  $(document).on('change', '#workflows_design_section_step3_fields', function () {
    handle_workflow_step_fields_select(false, $('#workflows_design_section_step3_fields'), $('#workflows_design_section_step3_actions'));
  });

  $(document).on('click', '#workflows_design_section_step3_action_add', function () {
    let field_id = $('#workflows_design_section_step3_fields').val();
    let field_name = $('#workflows_design_section_step3_fields option:selected').text();

    let action_id = $('#workflows_design_section_step3_actions').val();
    let action_name = $('#workflows_design_section_step3_actions option:selected').text();
    let action_value = $('#workflows_design_section_step3_action_value').val();

    handle_workflow_step_event_add_request(false, field_id, field_name, action_id, action_name, action_value);
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

  function handle_workflow_manage_select() {

    let workflow_id = $('#workflows_management_section_select').val();

    if (workflow_id) {

      // Update hidden workflow id
      $('#workflows_design_section_hidden_workflow_id').val(workflow_id);

      $('#workflows_design_section_div').fadeOut('slow', function () {

        // Reset associated component views
        new_workflow_view_reset(false, function () {

          // Display new workflow canvas
          $('#workflows_design_section_div').fadeIn('fast', function () {

            // Parse hidden workflows in search of the one!
            let parsed_workflows = JSON.parse($('#workflows_management_section_hidden_post_type_workflows').val());
            let workflow = parsed_workflows['workflows'][workflow_id];

            if (workflow) {

              // Set step 1 elements and display
              set_elements_step1(workflow, function () {
                $('#workflows_design_section_step1').fadeIn('fast', function () {
                });
              });

              // Set step 2 elements and display
              set_elements_step2(workflow, handle_workflow_step1_next_request);

              // Set step 3 elements and display
              set_elements_step3(workflow, handle_workflow_step2_next_request);

              // Set step 4 elements and display
              set_elements_step4(workflow, handle_workflow_step3_next_request);

            } else {

              // Just display step 1
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
      new_workflow_view_reset(true, function () {

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

  function handle_workflow_step_fields_select(is_condition, fields_select, events_select) {
    // Remove current options
    events_select.find('option[value != ""]').remove();

    if (fields_select.val()) {

      // If valid events found, reset options!
      let events = fetch_event_options(fields_select.val(), is_condition);
      if (events) {

        // Append new options
        events.forEach(function (event, idx) {
          if (event['id'] && event['name']) {
            let option = '<option value="' + event['id'] + '">' + event['name'] + '</option>';
            events_select.append(option);
          }
        });
      }
    }

    // Reset condition selection
    events_select.val("");
  }

  function handle_workflow_step_event_add_request(is_condition, field_id, field_name, event_id, event_name, event_value) {
    if (field_id && field_name && event_id && event_name && event_value) {
      if (is_condition) {
        add_new_condition_row(field_id, field_name, event_id, event_name, event_value);
      } else {
        add_new_action_row(field_id, field_name, event_id, event_name, event_value);
      }
    }
  }

  function handle_workflow_step_event_remove_request(evt) {
    // Obtain handle onto deleted row
    let row = evt.currentTarget.parentNode.parentNode.parentNode;

    // Remove row from parent table
    row.parentNode.removeChild(row);
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
  function new_workflow_view_reset(reset_workflow_select, callback) {

    // Reset management panel elements
    if (reset_workflow_select) {
      $('#workflows_management_section_select').val('');
    }

    // Reset workflow steps
    $('#workflows_design_section_save_but').fadeOut('fast', function () {
      $('#workflows_design_section_step4').fadeOut('fast', function () {

        // Reset step 4 elements!
        reset_step4_elements();

        $('#workflows_design_section_step3').fadeOut('fast', function () {

          // Reset step 3 elements!
          reset_step3_elements();

          $('#workflows_design_section_step2').fadeOut('fast', function () {

            // Reset step 2 elements!
            reset_step2_elements();

            $('#workflows_design_section_step1').fadeOut('fast', function () {

              // Reset step 1 elements!
              reset_step1_elements();

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
    let condition_value = $('#workflows_design_section_step2_condition_value');
    let conditions_table = $('#workflows_design_section_step2_conditions_table');

    reset_elements(fields_select, conditions_select, condition_value, conditions_table);
  }

  function reset_step3_elements() {
    let fields_select = $('#workflows_design_section_step3_fields');
    let actions_select = $('#workflows_design_section_step3_actions');
    let action_value = $('#workflows_design_section_step3_action_value');
    let actions_table = $('#workflows_design_section_step3_actions_table');

    reset_elements(fields_select, actions_select, action_value, actions_table);
  }

  function reset_step4_elements() {
    $('#workflows_design_section_step4_title').val('');
    $('#workflows_design_section_step4_enabled').prop('checked', true);
  }

  function reset_elements(fields_select, events_select, event_value, events_table) {
    let selected_post_type = $('#workflows_design_section_hidden_selected_post_type_id').val();
    let post_types = JSON.parse($('#workflows_design_section_hidden_post_types').val());

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
          let option = '<option value="' + field['id'] + '">' + field['name'] + '</option>';
          fields_select.append(option);
        }
      });
    }

    // Reset select option selections
    fields_select.val("");
    events_select.val("");
    event_value.val("");

    // TODO: Add saved conditions/actions back to table; filtered by workflow id and selected post type

  }

  function fetch_event_options(field_id, is_condition) {
    let selected_post_type = $('#workflows_design_section_hidden_selected_post_type_id').val();
    let post_types = JSON.parse($('#workflows_design_section_hidden_post_types').val());
    let post_field_types = JSON.parse($('#workflows_design_section_hidden_post_field_types').val());

    let events = [];

    // Need to determine field type in order to identify associated condition options
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

    switch (field_type) {
      case "text":
        conditions.push(
          {
            'id': 'equals',
            'name': 'Equals'
          },
          {
            'id': 'not_equals',
            'name': 'Not Equal'
          },
          {
            'id': 'contains',
            'name': 'Contains'
          },
          {
            'id': 'not_contain',
            'name': 'Not Contain'
          }
        );
        break;
      case "number":
      case "date":
        conditions.push(
          {
            'id': 'equals',
            'name': 'Equals'
          },
          {
            'id': 'not_equals',
            'name': 'Not Equal'
          },
          {
            'id': 'greater',
            'name': 'Greater Than'
          },
          {
            'id': 'less',
            'name': 'Less Than'
          }
        );
        break;
      case "boolean":
        conditions.push(
          {
            'id': 'equals',
            'name': 'Equals'
          },
          {
            'id': 'not_equals',
            'name': 'Not Equal'
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
            'name': 'Contains'
          },
          {
            'id': 'not_contain',
            'name': 'Not Contain'
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
        actions.push(
          {
            'id': 'update',
            'name': 'Updated To'
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
        actions.push(
          {
            'id': 'append',
            'name': 'Appended With'
          }
        );
        break;
    }

    return actions;
  }

  function add_new_condition_row(field_id, field_name, condition_id, condition_name, condition_value) {
    let html = '<tr>';

    // Field
    html += '<td style="vertical-align: middle;">';
    html += '<input id="workflows_design_section_step2_conditions_table_field_id" type="hidden" value="' + field_id + '">';
    html += '<input id="workflows_design_section_step2_conditions_table_field_name" type="hidden" value="' + field_name + '">';
    html += field_name;
    html += '</td>';

    // Condition
    html += '<td style="vertical-align: middle;">';
    html += '<input id="workflows_design_section_step2_conditions_table_condition_id" type="hidden" value="' + condition_id + '">';
    html += '<input id="workflows_design_section_step2_conditions_table_condition_name" type="hidden" value="' + condition_name + '">';
    html += condition_name;
    html += '</td>';

    // Value
    html += '<td style="vertical-align: middle;">';
    html += '<input id="workflows_design_section_step2_conditions_table_condition_value" type="hidden" value="' + condition_value + '">';
    html += condition_value;
    html += '</td>';

    // Removal Button
    html += '<td>';
    html += '<span style="float:right;">';
    html += '<a id="workflows_design_section_step2_condition_remove" class="button float-right workflows-design-section-step2-condition-remove">Remove</a>';
    html += '</span>';
    html += '</td>';

    html += '</tr>';

    // Add newly formed row!
    $('#workflows_design_section_step2_conditions_table tbody').append(html);
  }

  function add_new_action_row(field_id, field_name, action_id, action_name, action_value) {
    let html = '<tr>';

    // Field
    html += '<td style="vertical-align: middle;">';
    html += '<input id="workflows_design_section_step3_actions_table_field_id" type="hidden" value="' + field_id + '">';
    html += '<input id="workflows_design_section_step3_actions_table_field_name" type="hidden" value="' + field_name + '">';
    html += field_name;
    html += '</td>';

    // Condition
    html += '<td style="vertical-align: middle;">';
    html += '<input id="workflows_design_section_step3_actions_table_action_id" type="hidden" value="' + action_id + '">';
    html += '<input id="workflows_design_section_step3_actions_table_action_name" type="hidden" value="' + action_name + '">';
    html += action_name;
    html += '</td>';

    // Value
    html += '<td style="vertical-align: middle;">';
    html += '<input id="workflows_design_section_step3_actions_table_action_value" type="hidden" value="' + action_value + '">';
    html += action_value;
    html += '</td>';

    // Removal Button
    html += '<td>';
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

    let workflow_id = $('#workflows_design_section_hidden_workflow_id').val();
    let workflow_name = $('#workflows_design_section_step4_title').val();
    let workflow_enabled = $('#workflows_design_section_step4_enabled').is(':checked');

    let trigger = fetch_trigger_value();

    let conditions = fetch_event_values(
      'workflows_design_section_step2_conditions_table',
      'workflows_design_section_step2_conditions_table_field_id',
      'workflows_design_section_step2_conditions_table_field_name',
      'workflows_design_section_step2_conditions_table_condition_id',
      'workflows_design_section_step2_conditions_table_condition_name',
      'workflows_design_section_step2_conditions_table_condition_value'
    );

    let actions = fetch_event_values(
      'workflows_design_section_step3_actions_table',
      'workflows_design_section_step3_actions_table_field_id',
      'workflows_design_section_step3_actions_table_field_name',
      'workflows_design_section_step3_actions_table_action_id',
      'workflows_design_section_step3_actions_table_action_name',
      'workflows_design_section_step3_actions_table_action_value'
    );

    // If all is valid, package into post type workflow object
    if (post_type_id && post_type_name && workflow_id && workflow_name && trigger && (conditions.length > 0) && (actions.length > 0)) {

      // Package!
      let post_type_workflow_obj = {
        'post_type_id': post_type_id,
        'post_type_name': post_type_name,

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

  function fetch_event_values(id_table, id_field_id, id_field_name, id_event_id, id_event_name, id_event_value) {
    let events = [];

    // Iterate over specified table
    $('#' + id_table + ' > tbody > tr').each(function (idx, tr) {

      let field_id = $(tr).find('#' + id_field_id).val();
      let field_name = $(tr).find('#' + id_field_name).val();

      let event_id = $(tr).find('#' + id_event_id).val();
      let event_name = $(tr).find('#' + id_event_name).val();
      let event_value = $(tr).find('#' + id_event_value).val();

      // Are values valid?
      if (field_id && field_name && event_id && event_name && event_value) {
        events.push({
          'id': event_id,
          'name': event_name,
          'value': event_value,
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
          condition['value']
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
          action['value']
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

  /*** Header Functions - Helpers ***/
});
