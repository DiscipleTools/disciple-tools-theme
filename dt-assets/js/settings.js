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

  initSettingsWebComponents();
});
window.wpApiSettingsPage.translations = window.SHAREDFUNCTIONS.escapeObject(
  window.wpApiSettingsPage.translations,
);

function app_switch(user_id = null, app_key = null, onFail = null) {
  let a = jQuery('#app_link_' + app_key);
  return window
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
      if (typeof onFail === 'function') {
        onFail();
      } else {
        a.empty().html(`error`);
      }
    });
}

/**
 * Password reset
 *
 * @param preference_key
 * @param type
 * @returns {*}
 */
function switch_preference(preference_key, type = null, onFail = null) {
  return window
    .makeRequest('post', 'users/switch_preference', {
      preference_key,
      type,
    })
    .fail(function () {
      if (typeof onFail === 'function') {
        onFail();
      }
    });
}

function settingsEffectiveMultiValues(arr) {
  if (!arr || !arr.length) {
    return [];
  }
  return arr.filter((x) => !String(x).startsWith('-')).map(String);
}

function settingsDiffStringSets(prev, next) {
  const a = new Set(prev);
  const b = new Set(next);
  return {
    added: [...b].filter((x) => !a.has(x)),
    removed: [...a].filter((x) => !b.has(x)),
  };
}

function initSettingsWebComponents() {
  initSettingsAppToggles();
  initSettingsLanguageMultiselect();
  initSettingsPeopleGroupsMultiselect();
  initSettingsNotificationToggles();
}

function initSettingsAppToggles() {
  document.querySelectorAll('.settings-app-toggle').forEach((el) => {
    el.addEventListener('change', (e) => {
      const appKey = e.currentTarget.dataset.appKey;
      const oldChecked = e.detail.oldValue;
      app_switch(window.wpApiSettingsPage.current_user_id, appKey, () => {
        e.currentTarget.checked = oldChecked;
      });
    });
  });
}

function initSettingsLanguageMultiselect() {
  const el = document.querySelector('#settings-user-languages');
  if (!el) {
    return;
  }
  el.addEventListener('change', (e) => {
    const prev = settingsEffectiveMultiValues(e.detail.oldValue);
    const next = settingsEffectiveMultiValues(e.detail.newValue);
    const { added, removed } = settingsDiffStringSets(prev, next);
    let chain = Promise.resolve();
    removed.forEach((id) => {
      chain = chain.then(() => update_user('remove_languages', id));
    });
    added.forEach((id) => {
      chain = chain.then(() => update_user('add_languages', id));
    });
    el.setAttribute('loading', true);
    chain
      .then(() => {
        el.removeAttribute('loading');
        el.setAttribute('saved', true);
      })
      .catch((err) => {
        el.removeAttribute('loading');
        el.value = [...prev];
        window.handleAjaxError(err);
      });
  });
}

function initSettingsPeopleGroupsMultiselect() {
  const el = document.querySelector('#settings-people-groups');
  if (!el) {
    return;
  }

  const mergeCompactPosts = (posts) => {
    const byId = new Map((el.options || []).map((o) => [String(o.id), o]));
    for (const p of posts || []) {
      const id = String(p.ID);
      const label = p.label || p.name || p.post_title || id;
      if (!byId.has(id)) {
        byId.set(id, { id, label });
      }
    }
    el.options = Array.from(byId.values());
  };

  const attachInputHandlers = () => {
    const input =
      el.shadowRoot && el.shadowRoot.querySelector('input[part=input]');
    if (!input) {
      return;
    }
    let debounceTimer;
    const runFetch = (q) => {
      el.setAttribute('loading', true);
      window
        .makeRequestOnPosts('GET', 'peoplegroups/compact', { s: q || '' })
        .done((data) => {
          mergeCompactPosts(data.posts);
        })
        .always(() => {
          el.removeAttribute('loading');
        });
    };
    input.addEventListener('focusin', () => {
      if (!el._dtPgFocusFetched) {
        el._dtPgFocusFetched = true;
        runFetch('');
      }
    });
    input.addEventListener('keyup', (e) => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        runFetch((e.target.value || '').trim());
      }, 200);
    });
  };

  if (el.shadowRoot) {
    attachInputHandlers();
  } else {
    customElements.whenDefined('dt-multi-select').then(() => {
      Promise.resolve().then(() => attachInputHandlers());
    });
  }

  el.addEventListener('change', (e) => {
    const prev = settingsEffectiveMultiValues(e.detail.oldValue);
    const next = settingsEffectiveMultiValues(e.detail.newValue);
    const { added, removed } = settingsDiffStringSets(prev, next);
    let chain = Promise.resolve();
    removed.forEach((id) => {
      chain = chain.then(() => update_user('remove_people_groups', id));
    });
    added.forEach((id) => {
      chain = chain.then(() => update_user('add_people_groups', id));
    });
    el.setAttribute('loading', true);
    chain
      .then(() => {
        el.removeAttribute('loading');
        el.setAttribute('saved', true);
      })
      .catch((err) => {
        el.removeAttribute('loading');
        el.value = [...prev];
        window.handleAjaxError(err);
      });
  });
}

function initSettingsNotificationToggles() {
  document.querySelectorAll('.settings-notification-toggle').forEach((el) => {
    el.addEventListener('change', (e) => {
      const key = e.currentTarget.dataset.preferenceKey;
      if (!key) {
        return;
      }
      const type = e.currentTarget.dataset.preferenceType ?? null;
      const oldVal = e.detail.oldValue;
      switch_preference(key, type, () => {
        e.currentTarget.checked = oldVal;
      });
    });
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
