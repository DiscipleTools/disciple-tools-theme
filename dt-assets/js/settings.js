jQuery(document).ready(function () {
  window.current_user_lookup = window.wpApiSettingsPage.current_user_id;

  const componentService = new window.DtWebComponents.ComponentService(
    'users',
    window.current_user_lookup,
    window.wpApiSettingsPage.nonce,
  );
  componentService.attachLoadEvents();

  const locationElements = document.querySelectorAll(
    'dt-location, dt-location-map',
  );
  if (locationElements) {
    locationElements.forEach((el) => {
      el.addEventListener('change', handleLocationChangeEvent);
    });
  }
});
window.wpApiSettingsPage.translations = window.SHAREDFUNCTIONS.escapeObject(
  window.wpApiSettingsPage.translations,
);

function app_switch(user_id = null, app_key = null) {
  let a = jQuery('#app_link_' + app_key);
  window
    .makeRequest('post', 'users/app_switch', { user_id, app_key })
    .done(function (data) {
      if ('removed' === data) {
        a.children().hide();
      } else {
        let u = a.data('url-base');
        a.find('.app-link')
          .attr('href', u + data)
          .show();
        a.find('.app-copy')
          .attr('data-value', u + data)
          .show();
      }
    })
    .fail(function (err) {
      console.log('error');
      console.log(err);
      a.empty().html(`error`);
    });
}

/**
 * Password reset
 *
 * @param preference_key
 * @param type
 * @returns {*}
 */
function switch_preference(preference_key, type = null) {
  return window.makeRequest('post', 'users/switch_preference', {
    preference_key,
    type,
  });
}

function change_password() {
  let translation = window.wpApiSettingsPage.translations;
  // test matching passwords
  const p1 = jQuery('#password1');
  const p2 = jQuery('#password2');
  const message = jQuery('#password-message');

  message.empty();

  if (p1.val() !== p2.val()) {
    message.append(translation.pass_does_not_match);
    return;
  }

  window
    .makeRequest('post', 'users/change_password', { password: p1 })
    .done((data) => {
      console.log(data);
      message.html(translation.changed);
    })
    .fail(window.handleAjaxError);
}

function handleLocationChangeEvent(event) {
  const details = event.detail;
  if (details) {
    const { field, newValue, oldValue } = details;
    const component = event.target.tagName.toLowerCase();
    const valueDiff = window.DtWebComponents.ComponentService.valueArrayDiff(
      oldValue,
      newValue,
    );

    const request = {
      type: 'POST',
      url: 'users/user_location',
      data: null,
    };
    if (component === 'dt-location') {
      if (valueDiff.value2.length && !valueDiff.value1.length) {
        // added value
        request.data = {
          grid_id: valueDiff.value2[0].id,
        };
      } else if (valueDiff.value1.length && !valueDiff.value2.length) {
        // removed value
        request.type = 'DELETE';
        request.data = {
          grid_id: valueDiff.value1[0].id,
        };
      } else if (valueDiff.value2.length) {
        const item = valueDiff.value2[0];
        request.data = { grid_id: item.id };
        if (item.delete) {
          request.type = 'DELETE';
        }
      }
    } else if (component === 'dt-location-map') {
      if (valueDiff.value2.length && !valueDiff.value1.length) {
        // added value
        request.data = {
          user_id: window.wpApiSettingsPage.current_user_id,
          user_location: {
            location_grid_meta: valueDiff.value2,
          },
        };
      } else if (valueDiff.value1.length && !valueDiff.value2.length) {
        // removed value
        request.type = 'DELETE';
        request.data = {
          user_id: window.wpApiSettingsPage.current_user_id,
          user_location: {
            location_grid_meta: [
              {
                grid_meta_id: valueDiff.value1[0].grid_meta_id,
              },
            ],
          },
        };
      }
    }

    event.target.removeAttribute('saved');
    event.target.setAttribute('loading', true);

    window
      .makeRequest(request.type, request.url, request.data)
      .done((response) => {
        event.target.removeAttribute('loading');
        event.target.setAttribute('error', '');
        event.target.setAttribute('saved', true);

        if (response.user_location) {
          event.target.value = response.user_location.location_grid_meta;
        }
      })
      .catch((err) => {
        console.error(err);
        event.target.removeAttribute('loading');
        event.target.setAttribute('invalid', true); // this isn't hooked up yet
        event.target.setAttribute('error', err.message || err.toString());
      });
  }
}

let update_user = (key, value) => {
  let data = {
    [key]: value,
  };
  return window.makeRequest('POST', `user/update`, data, 'dt/v1/');
};

/**
 * Set availability dates
 */
let dateFields = ['start_date', 'end_date'];
dateFields.forEach((key) => {
  let datePicker = jQuery(`#${key}.date-picker`);
  datePicker.datepicker({
    onSelect: function (date) {
      let start_date = jQuery('#start_date').val();
      let end_date = jQuery('#end_date').val();
      if (
        start_date &&
        end_date &&
        window.moment(start_date) < window.moment(end_date)
      ) {
        jQuery('#add_unavailable_dates').removeAttr('disabled');
      } else {
        jQuery('#add_unavailable_dates').attr('disabled', true);
      }
    },
    dateFormat: 'yy-mm-dd',
    changeMonth: true,
    changeYear: true,
    yearRange: '-20:+10',
  });
});

jQuery('#add_unavailable_dates').on('click', function () {
  let start_date = jQuery('#start_date').val();
  let end_date = jQuery('#end_date').val();
  jQuery('#add_unavailable_dates_spinner').addClass('active');
  update_user('add_unavailability', { start_date, end_date }).then((resp) => {
    jQuery('#add_unavailable_dates_spinner').removeClass('active');
    jQuery('#start_date').val('');
    jQuery('#end_date').val('');
    display_dates_unavailable(resp);
  });
});
let display_dates_unavailable = (list = [], first_run) => {
  let date_unavailable_table = jQuery('#unavailable-list');
  let rows = ``;
  list = [...list].sort((a, b) => {
    return new Date(b.start_date) - new Date(a.start_date);
  });
  list.forEach((range) => {
    rows += `<tr>
        <td>${window.SHAREDFUNCTIONS.escapeHTML(range.start_date)}</td>
        <td>${window.SHAREDFUNCTIONS.escapeHTML(range.end_date)}</td>
        <td>
            <button class="button hollow tiny alert remove_dates_unavailable" data-id="${window.SHAREDFUNCTIONS.escapeHTML(range.id)}" style="margin-bottom: 0">
            <i class="fi-x"></i> ${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiSettingsPage.translations.delete)}</button>
        </td>
      </tr>`;
  });
  if (rows || (!rows && !first_run)) {
    date_unavailable_table.html(rows);
  }
};
display_dates_unavailable(
  window.wpApiSettingsPage.custom_data.availability,
  true,
);
jQuery(document).on('click', '.remove_dates_unavailable', function () {
  let id = jQuery(this).data('id');
  update_user('remove_unavailability', id).then((resp) => {
    display_dates_unavailable(resp);
  });
});

let status_buttons = jQuery('.status-button');
let color_workload_buttons = (name) => {
  status_buttons.css('background-color', '');
  status_buttons.addClass('hollow');
  if (name) {
    let selected = jQuery(`.status-button[name=${name}]`);
    selected.removeClass('hollow');
    const color =
      window.wpApiSettingsPage?.workload_status_options?.[name]?.color ?? '';
    selected.css('background-color', color);
    selected.blur();
  }
};
color_workload_buttons(window.wpApiSettingsPage.workload_status);
status_buttons.on('click', function () {
  jQuery('#workload-spinner').addClass('active');
  let name = jQuery(this).attr('name');
  color_workload_buttons(name);
  update_user('workload_status', name)
    .then(() => {
      jQuery('#workload-spinner').removeClass('active');
    })
    .fail(() => {
      status_buttons.css('background-color', '');
      jQuery('#workload-spinner').removeClass('active');
      status_buttons.addClass('hollow');
    });
});

jQuery('button.dt_multi_select').on('click', function () {
  let fieldKey = jQuery(this).data('field-key');
  let optionKey = jQuery(this).attr('id');
  jQuery(`#${fieldKey}-spinner`).addClass('active');
  let field = jQuery(`[data-field-key="${fieldKey}"]#${optionKey}`);
  field.addClass('submitting-select-button');
  let action = 'add';
  let update_request = null;
  if (field.hasClass('selected-select-button')) {
    action = 'delete';
    update_request = update_user('remove_' + fieldKey, optionKey);
  } else {
    field.removeClass('empty-select-button');
    field.addClass('selected-select-button');
    update_request = update_user('add_' + fieldKey, optionKey);
  }
  update_request
    .then(() => {
      field.removeClass('submitting-select-button selected-select-button');
      field.blur();
      field.addClass(
        action === 'delete' ? 'empty-select-button' : 'selected-select-button',
      );
      jQuery(`#${fieldKey}-spinner`).removeClass('active');
    })
    .catch((err) => {
      field.removeClass('submitting-select-button selected-select-button');
      field.addClass(
        action === 'add' ? 'empty-select-button' : 'selected-select-button',
      );
      window.handleAjaxError(err);
    });
});
jQuery('select.select-field').change((e) => {
  const id = jQuery(e.currentTarget).attr('id');
  const val = jQuery(e.currentTarget).val();
  jQuery(`#${id}-spinner`).addClass('active');
  update_user(id, val)
    .then(() => {
      jQuery(`#${id}-spinner`).removeClass('active');
    })
    .catch(window.handleAjaxError);
});
jQuery('input[name="email-preference"]').on('change', (e) => {
  const optionId = e.target.id.replace('-preference', '');
  const loadingSpinner = jQuery('#email-preference-spinner');
  loadingSpinner.addClass('active');
  update_user('email-preference', optionId)
    .then(() => {
      loadingSpinner.removeClass('active');
    })
    .fail(() => {
      loadingSpinner.removeClass('active');
    });
});

/**
 * People groups
 */
if (jQuery('.js-typeahead-people_groups').length) {
  jQuery.typeahead({
    input: '.js-typeahead-people_groups',
    minLength: 0,
    accent: true,
    searchOnFocus: true,
    maxItem: 20,
    template: window.TYPEAHEADS.contactListRowTemplate,
    source: window.TYPEAHEADS.typeaheadPostsSource('peoplegroups'),
    display: ['name', 'label'],
    templateValue: function () {
      if (this.items[this.items.length - 1].label) {
        return '{{label}}';
      } else {
        return '{{name}}';
      }
    },
    dynamic: true,
    multiselect: {
      matchOn: ['ID'],
      data: function () {
        return window.wpApiSettingsPage.user_people_groups.map((g) => {
          return { ID: g.ID, name: g.post_title };
        });
      },
      callback: {
        onCancel: function (node, item) {
          update_user('remove_people_groups', item.ID);
        },
      },
    },
    callback: {
      onClick: function (node, a, item, event) {
        update_user('add_people_groups', item.ID);
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
        jQuery('#people_groups-result-container').html(text);
      },
      onHideLayout: function () {
        jQuery('#people_groups-result-container').html('');
      },
    },
  });
}
