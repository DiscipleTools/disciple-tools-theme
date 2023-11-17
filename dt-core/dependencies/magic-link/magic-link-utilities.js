function ml_utility_submit_success_function(message, success_callback_func) {
  Toastify({
    text: message,
    close: true,
    gravity: "bottom",
    duration: 1500,
    callback: function () {
    }
  }).showToast();
  success_callback_func();
}


function ml_utility_submit_error_function(error, error_callback_func) {
  Toastify({
    text: error,
    close: true,
    gravity: "bottom",
    position: "center",
    duration: 1500,
    style: {
      background: "#d25e5e"
    },
    callback: function () {
    }
  }).showToast();
  error_callback_func();
}

function ml_utility_submit_field_validation_function(field_settings, fields, keys, labels) {
  let validated = {
    'success': true,
    'message': ''
  };

  jQuery.each(fields, function (idx, field) {
    switch (field[keys['type']]) {
      case 'number': {
        if (field_settings[field[keys['id']]]) {
          let field_setting = field_settings[field[keys['id']]];

          // Ensure submitted field value is within min/max range.
          if (field[keys['value']] && field_setting['min_option'] && field_setting['max_option']) {
            let value = parseInt(field[keys['value']]);
            let min = parseInt(field_setting['min_option']);
            let max = parseInt(field_setting['max_option']);

            if ((value < min) || (value > max)) {
              validated['success'] = false;
              validated['message'] = field_setting['name'] + ': ' + window.SHAREDFUNCTIONS.escapeHTML(labels[field[keys['type']]]['out_of_range']);
            }
          }
        }
        break;
      }
    }
  });

  return validated;
}
