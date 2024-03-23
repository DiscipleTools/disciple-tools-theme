jQuery(document).ready(function () {
  window.current_user_lookup = window.wpApiSettingsPage.current_user_id;
  load_locations();
});

function app_switch(app_key = null) {
  let a = jQuery("#app_link_" + app_key);
  a.empty().html(`<span class="loading-spinner active"></span>`);
  window
    .makeRequest("post", "users/app_switch", { app_key })
    .done(function (data) {
      if ("removed" === data) {
        jQuery("#app_link_" + app_key).empty();
      } else {
        let u = a.data("url-base");
        a.empty().html(
          `<a class="button small"  href="${u}${data}" title="${window.wpApiSettingsPage.translations.link}"><i class="fi-link"></i></a>
            <button class="button small copy_to_clipboard" data-value="${u}${data}" title="${window.wpApiSettingsPage.translations.copy}"><i class="fi-page-copy"></i></button>`
        );
        load_user_app_copy_to_clipboard_listener();
      }
    })
    .fail(function (err) {
      console.log("error");
      console.log(err);
      a.empty().html(`error`);
    });
}

function load_user_app_copy_to_clipboard_listener() {
  jQuery(".copy_to_clipboard").on("click", function () {
    let str = jQuery(this).data("value");
    const el = document.createElement("textarea");
    el.value = str;
    el.setAttribute("readonly", "");
    el.style.position = "absolute";
    el.style.left = "-9999px";
    document.body.appendChild(el);
    const selected =
      document.getSelection().rangeCount > 0
        ? document.getSelection().getRangeAt(0)
        : false;
    el.select();
    document.execCommand("copy");
    document.body.removeChild(el);
    if (selected) {
      document.getSelection().removeAllRanges();
      document.getSelection().addRange(selected);
    }
    alert("Copied");
  });
}
load_user_app_copy_to_clipboard_listener();

/**
 * Password reset
 *
 * @param preference_key
 * @param type
 * @returns {*}
 */
function switch_preference(preference_key, type = null) {
  return window.makeRequest("post", "users/switch_preference", {
    preference_key,
    type,
  });
}

function change_password() {
  let translation = window.wpApiSettingsPage.translations;
  // test matching passwords
  const p1 = jQuery("#password1");
  const p2 = jQuery("#password2");
  const message = jQuery("#password-message");

  message.empty();

  if (p1.val() !== p2.val()) {
    message.append(translation.pass_does_not_match);
    return;
  }

  window
    .makeRequest("post", "users/change_password", { password: p1 })
    .done((data) => {
      console.log(data);
      message.html(translation.changed);
    })
    .fail(window.handleAjaxError);
}

function load_locations() {
  window
    .makeRequest("GET", `user/my`)
    .done((data) => {
      if (typeof window.dtMapbox !== "undefined") {
        window.dtMapbox.post_type = "user";
        window.write_results_box();
      } else {
        //locations
        let typeahead = window.Typeahead[".js-typeahead-location_grid"];
        if (typeahead) {
          typeahead.items = [];
          typeahead.comparedItems = [];
          typeahead.label.container.empty();
          typeahead.adjustInputSize();
        }
        if (typeof data.locations.location_grid !== "undefined") {
          data.locations.location_grid.forEach((location) => {
            typeahead.addMultiselectItemLayout({
              ID: location.id.toString(),
              name: location.label,
            });
          });
        }
      }
    })
    .catch((e) => {
      console.log("error in locations");
      console.log(e);
    });
}

if (typeof window.dtMapbox === "undefined") {
  let typeaheadTotals = {};
  if (!window.Typeahead[".js-typeahead-location_grid"]) {
    jQuery.typeahead({
      input: ".js-typeahead-location_grid",
      minLength: 0,
      accent: true,
      searchOnFocus: true,
      maxItem: 20,
      dropdownFilter: [
        {
          key: "group",
          value: "focus",
          template: window.SHAREDFUNCTIONS.escapeHTML(
            window.wpApiShare.translations.regions_of_focus
          ),
          all: window.SHAREDFUNCTIONS.escapeHTML(
            window.wpApiShare.translations.all_locations
          ),
        },
      ],
      source: {
        focus: {
          display: "name",
          ajax: {
            url:
              window.wpApiShare.root +
              "dt/v1/mapping_module/search_location_grid_by_name",
            data: {
              s: "{{query}}",
              filter: function () {
                // return window.lodash.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
                const { dropdown } =
                  window.Typeahead[".js-typeahead-location_grid"].filters;
                const value = dropdown?.value ?? "all";
                return value;
              },
            },
            beforeSend: function (xhr) {
              xhr.setRequestHeader("X-WP-Nonce", window.wpApiShare.nonce);
            },
            callback: {
              done: function (data) {
                if (typeof window.typeaheadTotals !== "undefined") {
                  window.typeaheadTotals.field = data.total;
                }
                return data.location_grid;
              },
            },
          },
        },
      },
      display: "name",
      templateValue: "{{name}}",
      dynamic: true,
      multiselect: {
        matchOn: ["ID"],
        data: function () {
          return [];
        },
        callback: {
          onCancel: function (node, item) {
            delete_location_grid(item.ID);
          },
        },
      },
      callback: {
        onClick: function (node, a, item, event) {
          add_location_grid(item.ID);
        },
        onReady() {
          this.filters.dropdown = {
            key: "group",
            value: "focus",
            template: window.SHAREDFUNCTIONS.escapeHTML(
              window.wpApiShare.translations.regions_of_focus
            ),
          };
          this.container
            .removeClass("filter")
            .find("." + this.options.selector.filterButton)
            .html(
              window.SHAREDFUNCTIONS.escapeHTML(
                window.wpApiShare.translations.regions_of_focus
              )
            );
        },
        onResult: function (node, query, result, resultCount) {
          resultCount = typeaheadTotals.location_grid;
          let text = window.TYPEAHEADS.typeaheadHelpText(
            resultCount,
            query,
            result
          );
          jQuery("#location_grid-result-container").html(text);
        },
        onHideLayout: function () {
          jQuery("#location_grid-result-container").html("");
        },
      },
    });
  }
}
let add_location_grid = (value) => {
  let data = {
    grid_id: value,
  };
  return window.makeRequest("POST", `users/user_location`, data);
};
let delete_location_grid = (value) => {
  let data = {
    grid_id: value,
  };
  return window.makeRequest("DELETE", `users/user_location`, data);
};

let update_user = (key, value) => {
  let data = {
    [key]: value,
  };
  return window.makeRequest("POST", `user/update`, data, "dt/v1/");
};

/**
 * Set availability dates
 */
let dateFields = ["start_date", "end_date"];
dateFields.forEach((key) => {
  let datePicker = jQuery(`#${key}.date-picker`);
  datePicker.datepicker({
    onSelect: function (date) {
      let start_date = jQuery("#start_date").val();
      let end_date = jQuery("#end_date").val();
      if (
        start_date &&
        end_date &&
        window.moment(start_date) < window.moment(end_date)
      ) {
        jQuery("#add_unavailable_dates").removeAttr("disabled");
      } else {
        jQuery("#add_unavailable_dates").attr("disabled", true);
      }
    },
    dateFormat: "yy-mm-dd",
    changeMonth: true,
    changeYear: true,
    yearRange: "-20:+10",
  });
});

jQuery("#add_unavailable_dates").on("click", function () {
  let start_date = jQuery("#start_date").val();
  let end_date = jQuery("#end_date").val();
  jQuery("#add_unavailable_dates_spinner").addClass("active");
  update_user("add_unavailability", { start_date, end_date }).then((resp) => {
    jQuery("#add_unavailable_dates_spinner").removeClass("active");
    jQuery("#start_date").val("");
    jQuery("#end_date").val("");
    display_dates_unavailable(resp);
  });
});
let display_dates_unavailable = (list = [], first_run) => {
  let date_unavailable_table = jQuery("#unavailable-list");
  let rows = ``;
  // list = window.lodash.orderBy( list, [ "start_date" ], "desc")
  list.sort((a, b) => new Date(b.start_date) - new Date(a.start_date));
  list.forEach((range) => {
    rows += `<tr>
        <td>${window.SHAREDFUNCTIONS.escapeHTML(range.start_date)}</td>
        <td>${window.SHAREDFUNCTIONS.escapeHTML(range.end_date)}</td>
        <td>
            <button class="button hollow tiny alert remove_dates_unavailable" data-id="${window.SHAREDFUNCTIONS.escapeHTML(
              range.id
            )}" style="margin-bottom: 0">
            <i class="fi-x"></i> ${window.SHAREDFUNCTIONS.escapeHTML(
              window.wpApiSettingsPage.translations.delete
            )}</button>
        </td>
      </tr>`;
  });
  if (rows || (!rows && !first_run)) {
    date_unavailable_table.html(rows);
  }
};
display_dates_unavailable(
  window.wpApiSettingsPage.custom_data.availability,
  true
);
jQuery(document).on("click", ".remove_dates_unavailable", function () {
  let id = jQuery(this).data("id");
  update_user("remove_unavailability", id).then((resp) => {
    display_dates_unavailable(resp);
  });
});

let status_buttons = jQuery(".status-button");
let color_workload_buttons = (name) => {
  status_buttons.css("background-color", "");
  status_buttons.addClass("hollow");
  if (name) {
    let selected = jQuery(`.status-button[name=${name}]`);
    selected.removeClass("hollow");
    const color =
      window.wpApiSettingsPage?.workload_status_options?.[name]?.color;
    selected.css("background-color", color);
    // selected.css('background-color', window.lodash.get(window.wpApiSettingsPage, `workload_status_options.${name}.color`))
    selected.blur();
  }
};
color_workload_buttons(window.wpApiSettingsPage.workload_status);
status_buttons.on("click", function () {
  jQuery("#workload-spinner").addClass("active");
  let name = jQuery(this).attr("name");
  color_workload_buttons(name);
  update_user("workload_status", name)
    .then(() => {
      jQuery("#workload-spinner").removeClass("active");
    })
    .fail(() => {
      status_buttons.css("background-color", "");
      jQuery("#workload-spinner").removeClass("active");
      status_buttons.addClass("hollow");
    });
});

jQuery("button.dt_multi_select").on("click", function () {
  let fieldKey = jQuery(this).data("field-key");
  let optionKey = jQuery(this).attr("id");
  jQuery(`#${fieldKey}-spinner`).addClass("active");
  let field = jQuery(`[data-field-key="${fieldKey}"]#${optionKey}`);
  field.addClass("submitting-select-button");
  let action = "add";
  let update_request = null;
  if (field.hasClass("selected-select-button")) {
    action = "delete";
    update_request = update_user("remove_" + fieldKey, optionKey);
  } else {
    field.removeClass("empty-select-button");
    field.addClass("selected-select-button");
    update_request = update_user("add_" + fieldKey, optionKey);
  }
  update_request
    .then(() => {
      field.removeClass("submitting-select-button selected-select-button");
      field.blur();
      field.addClass(
        action === "delete" ? "empty-select-button" : "selected-select-button"
      );
      jQuery(`#${fieldKey}-spinner`).removeClass("active");
    })
    .catch((err) => {
      field.removeClass("submitting-select-button selected-select-button");
      field.addClass(
        action === "add" ? "empty-select-button" : "selected-select-button"
      );
      window.handleAjaxError(err);
    });
});
jQuery("select.select-field").change((e) => {
  const id = jQuery(e.currentTarget).attr("id");
  const val = jQuery(e.currentTarget).val();
  jQuery(`#${id}-spinner`).addClass("active");
  update_user(id, val)
    .then(() => {
      jQuery(`#${id}-spinner`).removeClass("active");
    })
    .catch(window.handleAjaxError);
});
jQuery('input[name="email-preference"]').on("change", (e) => {
  const optionId = e.target.id.replace("-preference", "");
  const loadingSpinner = jQuery("#email-preference-spinner");
  loadingSpinner.addClass("active");
  update_user("email-preference", optionId)
    .then(() => {
      loadingSpinner.removeClass("active");
    })
    .fail(() => {
      loadingSpinner.removeClass("active");
    });
});

/**
 * People groups
 */
if (jQuery(".js-typeahead-people_groups").length) {
  jQuery.typeahead({
    input: ".js-typeahead-people_groups",
    minLength: 0,
    accent: true,
    searchOnFocus: true,
    maxItem: 20,
    template: window.TYPEAHEADS.contactListRowTemplate,
    source: window.TYPEAHEADS.typeaheadPostsSource("peoplegroups"),
    display: ["name", "label"],
    templateValue: function () {
      if (this.items[this.items.length - 1].label) {
        return "{{label}}";
      } else {
        return "{{name}}";
      }
    },
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      data: function () {
        return window.wpApiSettingsPage.user_people_groups.map((g) => {
          return { ID: g.ID, name: g.post_title };
        });
      },
      callback: {
        onCancel: function (node, item) {
          update_user("remove_people_groups", item.ID);
        },
      },
    },
    callback: {
      onClick: function (node, a, item, event) {
        update_user("add_people_groups", item.ID);
        this.addMultiselectItemLayout(item);
        event.preventDefault();
        this.hideLayout();
        this.resetInput();
      },
      onResult: function (node, query, result, resultCount) {
        let text = window.TYPEAHEADS.typeaheadHelpText(
          resultCount,
          query,
          result
        );
        jQuery("#people_groups-result-container").html(text);
      },
      onHideLayout: function () {
        jQuery("#people_groups-result-container").html("");
      },
    },
  });
}
