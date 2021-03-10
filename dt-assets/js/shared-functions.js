/* global wpApiShare:false */
_ = _ || window.lodash; // make sure lodash is defined so plugins like gutenberg don't break it.

jQuery(document).ready(function ($) {
  // Adds an active state to the top bar navigation
  let ref = "";
  if (wpApiShare && wpApiShare.site_url) {
    ref = window.location.href.replace(wpApiShare.site_url + "/", "");
  } else {
    ref = window.location.pathname;
  }
  let page = `${ref.replace(wpApiShare.site_url, "").split("/")[0] + ""}`;
  $(`div.top-bar-left ul.menu [href^="${wpApiShare.site_url + "/" + page}"]`)
    .parent()
    .addClass("active");

  let collapsed_tiles = window.SHAREDFUNCTIONS.get_json_cookie(
    "collapsed_tiles"
  );
  // expand and collapse tiles, only when a section chevron icon is clicked for that given tile.
  $(".section-header .section-chevron").on("click", function () {
    let tile = $(this).closest(".bordered-box");
    tile.toggleClass("collapsed");
    let tile_id = tile.attr("id");
    if (tile_id && tile_id.includes("-tile")) {
      if (collapsed_tiles.includes(tile_id)) {
        collapsed_tiles = window.lodash.pull(collapsed_tiles, tile_id);
      } else {
        collapsed_tiles.push(tile_id);
      }
      window.SHAREDFUNCTIONS.save_json_cookie(
        "collapsed_tiles",
        collapsed_tiles,
        wpApiShare.post_type
      );
    }
    $(".grid").masonry("layout");
  });
  $(".bordered-box").each((index, item) => {
    let id = $(item).attr("id");
    if (id && id.includes("-tile") && collapsed_tiles.includes(id)) {
      $(item).addClass("collapsed");
    }
  });
});

/**
 *
 * @param type: GET POST DELETE
 * @param url: users/get_users
 * @param data
 * @param base, when using a custom D.T endpoint that does not start with dt/v1
 * @returns {jQuery}
 */
function makeRequest(type, url, data, base = "dt/v1/") {
  const options = {
    type: type,
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: url.startsWith("http") ? url : `${wpApiShare.root}${base}${url}`,
    beforeSend: (xhr) => {
      xhr.setRequestHeader("X-WP-Nonce", wpApiShare.nonce);
    },
  };

  if (data) {
    options.data = type === "GET" ? data : JSON.stringify(data);
  }

  return jQuery.ajax(options);
}

function makeRequestOnPosts(type, url, data) {
  const options = {
    type: type,
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: url.startsWith("http") ? url : `${wpApiShare.root}dt-posts/v2/${url}`,
    beforeSend: (xhr) => {
      xhr.setRequestHeader("X-WP-Nonce", wpApiShare.nonce);
    },
  };
  if (data && !window.lodash.isEmpty(data)) {
    options.data = type === "GET" ? data : JSON.stringify(data);
  }
  return jQuery.ajax(options);
}

window.API = {
  get_post: (post_type, postId) =>
    makeRequestOnPosts("GET", `${post_type}/${postId}`),

  create_post: (post_type, fields) =>
    makeRequestOnPosts("POST", `${post_type}`, fields),

  update_post: (post_type, postId, postData) =>
    makeRequestOnPosts("POST", `${post_type}/${postId}`, postData),

  delete_post: (post_type, postId) =>
    makeRequestOnPosts("DELETE", `${post_type}/${postId}`),

  post_comment: (post_type, postId, comment, comment_type = "comment") =>
    makeRequestOnPosts("POST", `${post_type}/${postId}/comments`, {
      comment,
      comment_type,
    }),

  delete_comment: (post_type, postId, comment_ID) =>
    makeRequestOnPosts(
      "DELETE",
      `${post_type}/${postId}/comments/${comment_ID}`
    ),

  update_comment: (
    post_type,
    postId,
    comment_ID,
    comment_content,
    commentType = "comment"
  ) =>
    makeRequestOnPosts(
      "POST",
      `${post_type}/${postId}/comments/${comment_ID}`,
      { comment: comment_content, comment_type: commentType }
    ),

  get_comments: (post_type, postId) =>
    makeRequestOnPosts("GET", `${post_type}/${postId}/comments`),

  get_activity: (post_type, postId) =>
    makeRequestOnPosts("GET", `${post_type}/${postId}/activity`),

  get_single_activity: (post_type, postId, activityId) =>
    makeRequestOnPosts("GET", `${post_type}/${postId}/activity/${activityId}`),

  get_shared: (post_type, postId) =>
    makeRequestOnPosts("GET", `${post_type}/${postId}/shares`),

  add_shared: (post_type, postId, userId) =>
    makeRequestOnPosts("POST", `${post_type}/${postId}/shares`, {
      user_id: userId,
    }),

  remove_shared: (post_type, postId, userId) =>
    makeRequestOnPosts("DELETE", `${post_type}/${postId}/shares`, {
      user_id: userId,
    }),

  save_field_api: (post_type, postId, postData) =>
    makeRequestOnPosts("POST", `${post_type}/${postId}`, postData),

  revert_activity: (post_type, postId, activityId) =>
    makeRequestOnPosts("GET", `${post_type}/${postId}/revert/${activityId}`),

  search_users: (query) => makeRequest("GET", `users/get_users?s=${query}`),

  get_filters: () => makeRequest("GET", "users/get_filters"),

  save_filters: (post_type, filter) =>
    makeRequest("POST", "users/save_filters", { filter, post_type }),

  delete_filter: (post_type, id) =>
    makeRequest("DELETE", "users/save_filters", { id, post_type }),

  get_duplicates_on_post: (post_type, postId, args) =>
    makeRequestOnPosts("GET", `${post_type}/${postId}/all_duplicates`, args),

  create_user: (user) => makeRequest("POST", "users/create", user),

  transfer_contact: (contactId, siteId) =>
    makeRequestOnPosts("POST", "contacts/transfer", {
      contact_id: contactId,
      site_post_id: siteId,
    }),
};

function handleAjaxError(err) {
  if (
    window.lodash.get(err, "statusText") !== "abortPromise" &&
    err.responseText
  ) {
    console.trace("error");
    console.log(err);
    // jQuery("#errors").append(err.responseText)
  }
}

jQuery(document)
  .ajaxComplete((event, xhr, settings) => {
    if (xhr && xhr.responseJSON) {
      // Event that a contact record has been updated
      if (xhr.responseJSON.ID && xhr.responseJSON.post_type) {
        let request = settings.data ? JSON.parse(settings.data) : {};
        $(document).trigger("dt_record_updated", [xhr.responseJSON, request]);
      }
    }

    if (window.lodash.get(xhr, "responseJSON.data.status") === 401) {
      window.location.reload();
    }
  })
  .ajaxError((event, xhr) => {
    handleAjaxError(xhr);
  });

jQuery(document).on("click", ".help-button", function () {
  jQuery("#help-modal").foundation("open");
  let section = jQuery(this).data("section");
  jQuery(".help-section").hide();
  jQuery(`#${section}`).show();
});
jQuery(document).on("click", ".help-button-tile", function () {
  jQuery("#help-modal-field").foundation("open");
  let section = jQuery(this).data("tile");
  jQuery(".help-section").hide();
  let tile = window.wpApiShare.tiles[section];
  if (tile && window.post_type_fields) {
    if (tile.label) {
      $("#help-modal-field-title").html(tile.label);
    }
    if (tile.description) {
      $("#help-modal-field-description").html(tile.description);
    }
    let html = ``;
    window.lodash.forOwn(window.post_type_fields, (field, field_key) => {
      if (
        field.tile === section &&
        (field.description || window.lodash.isObject(field.default)) &&
        !field.hidden
      ) {
        html += `<h2>${window.lodash.escape(field.name)}</h2>`;
        html += `<p>${window.lodash.escape(field.description)}</p>`;

        if (window.lodash.isObject(field.default)) {
          let list_html = `<ul>`;
          window.lodash.forOwn(field.default, (field_options, field_key) => {
            list_html += `<li><strong>${window.lodash.escape(
              field_options.label
            )}</strong> ${window.lodash.escape(
              !field_options.description ? "" : "- " + field_options.description
            )}</li>`;
          });
          list_html += `</ul>`;
          html += list_html;
        }
      }
    });
    $("#help-modal-field-body").html(html);
  }
  jQuery(`#${section}`).show();
});
jQuery(document).on("click", ".help-button-field", function () {
  jQuery("#help-modal-field").foundation("open");
  let section = jQuery(this).data("section").replace("-help-text", "");
  jQuery(".help-section").hide();

  if (window.post_type_fields && window.post_type_fields[section]) {
    let field = window.post_type_fields[section];
    if (field.description) {
      $("#help-modal-field-title").html(field.name);
      $("#help-modal-field-description").html(field.description);
    }
    if (window.lodash.isObject(field.default)) {
      let html = `<ul>`;
      window.lodash.forOwn(field.default, (field_options, field_key) => {
        html += `<li><strong>${window.lodash.escape(
          field_options.label
        )}</strong> ${window.lodash.escape(
          !field_options.description ? "" : "- " + field_options.description
        )}</li>`;
      });
      html += `</ul>`;
      $("#help-modal-field-body").html(html);
    }
  }
  jQuery(`#${section}`).show();
});

window.TYPEAHEADS = {
  typeaheadSource: function (field, url) {
    return {
      contacts: {
        display: ["name", "ID"],
        template: "<span>{{name}}</span>",
        ajax: {
          url: wpApiShare.root + url,
          data: {
            s: "{{query}}",
          },
          beforeSend: function (xhr) {
            xhr.setRequestHeader("X-WP-Nonce", wpApiShare.nonce);
          },
          callback: {
            done: function (data) {
              if (typeof typeaheadTotals !== "undefined") {
                typeaheadTotals.field = data.total;
              }
              return data.posts;
            },
          },
        },
      },
    };
  },
  typeaheadUserSource : function (field, url) {
    return {
      users: {
        display: ["name", "user"],
        ajax: {
          url: wpApiShare.root + "dt/v1/users/get_users",
          data: {
            s: "{{query}}",
          },
          beforeSend: function (xhr) {
            xhr.setRequestHeader("X-WP-Nonce", wpApiShare.nonce);
          },
          callback: {
            done: function (data) {
              return data.posts || data;
            },
          },
        },
      },
    };
  },
  typeaheadContactsSource: function () {
    return {
      contacts: {
        display: ["name", "ID"],
        ajax: {
          url: wpApiShare.root + "dt-posts/v2/contacts/compact",
          data: {
            s: "{{query}}",
          },
          beforeSend: function (xhr) {
            xhr.setRequestHeader("X-WP-Nonce", wpApiShare.nonce);
          },
          callback: {
            done: function (data) {
              return data.posts;
            },
          },
        },
      },
    };
  },
  typeaheadPostsSource: function (post_type, args = {}) {
    return {
      contacts: {
        display: [ "name", "ID", "label" ],
        ajax: {
          url: wpApiShare.root + `dt-posts/v2/${post_type}/compact`,
          data: Object.assign({ s: "{{query}}" }, args),
          beforeSend: function (xhr) {
            xhr.setRequestHeader("X-WP-Nonce", wpApiShare.nonce);
          },
          callback: {
            done: function (data) {
              return data.posts;
            },
          },
        },
      },
    };
  },
  typeaheadHelpText: function (resultCount, query, result) {
    let text = "";
    if (result.length > 0 && query) {
      text = wpApiShare.translations.showing_x_items_matching
        .replace(
          "%1$s",
          `<strong>${window.lodash.escape(result.length)}</strong>`
        )
        .replace("%2$s", `<strong>${window.lodash.escape(query)}</strong>`);
    } else if (result.length > 0) {
      text = wpApiShare.translations.showing_x_items.replace(
        "%s",
        `<strong>${window.lodash.escape(result.length)}</strong>`
      );
    } else {
      text = wpApiShare.translations.no_records_found.replace(
        '"{{query}}"',
        `<strong>${window.lodash.escape(query)}</strong>`
      );
    }
    return text;
  },
  contactListRowTemplate: function (query, item) {
    let img = item.user
      ? `<img src="${wpApiShare.template_dir}/dt-assets/images/profile.svg">`
      : "";
    let statusStyle = item.status === "closed" ? 'style="color:gray"' : "";
      return `<span dir="auto" ${statusStyle}>
        <span class="typeahead-user-row" style="width:20px">${img}</span>
        ${window.lodash.escape((item.label ? item.label : item.name))}
        <span dir="auto">(#${window.lodash.escape(item.ID)})</span>
    </span>`;
  },
  share(post_type, id, v2) {
    return $.typeahead({
      input: ".js-typeahead-share",
      minLength: 0,
      maxItem: 0,
      accent: true,
      searchOnFocus: true,
      source: this.typeaheadSource("share", "dt/v1/users/get_users"),
      display: "name",
      templateValue: "{{name}}",
      dynamic: true,
      multiselect: {
        matchOn: ["ID"],
        data: function () {
          var deferred = $.Deferred();
          return window.API.get_shared(post_type, id).then((sharedResult) => {
            return deferred.resolve(
              sharedResult.map((g) => {
                return { ID: g.user_id, name: g.display_name };
              })
            );
          });
        },
        callback: {
          onCancel: function (node, item) {
            $("#share-result-container").html("");
            window.API.remove_shared(post_type, id, item.ID).catch((err) => {
              Typeahead[".js-typeahead-share"].addMultiselectItemLayout({
                ID: item.ID,
                name: item.name,
              });
              $("#share-result-container").html(
                window.lodash.get(err, "responseJSON.message")
              );
            });
          },
        },
      },
      callback: {
        onClick: function (node, a, item, event) {
          window.API.add_shared(post_type, id, item.ID);
        },
        onResult: function (node, query, result, resultCount) {
          if (query) {
            let text = window.TYPEAHEADS.typeaheadHelpText(
              resultCount,
              query,
              result
            );
            $("#share-result-container").html(text);
          }
        },
        onHideLayout: function () {
          $("#share-result-container").html("");
        },
      },
    });
  },
  defaultContactTypeahead: function () {
    return {
      minLength: 0,
      accent: true,
      searchOnFocus: true,
      maxItem: 20,
      template: this.contactListRowTemplate,
      source: this.typeaheadContactsSource(),
      display: "name",
      templateValue: "{{name}}",
      dynamic: true,
    };
  },
};

window.SHAREDFUNCTIONS = {
  getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(";");
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == " ") {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  },
  get_json_cookie(cname, default_val = []) {
    let cookie = this.getCookie(cname);
    try {
      default_val = JSON.parse(cookie);
    } catch (e) {}
    return default_val;
  },
  save_json_cookie(cname, json, path = "") {
    if (path) {
      path = window.location.pathname.split(path)[0] + path;
      path = path.replace(/^\/?([^\/]+(?:\/[^\/]+)*)\/?$/, "/$1"); // add leading and remove trailing slashes
    }
    document.cookie = `${cname}=${JSON.stringify(json)};path=${path}`;
  },
  formatDate(date, with_time = false) {
    let langcode = document.querySelector("html").getAttribute("lang")
      ? document.querySelector("html").getAttribute("lang").replace("_", "-")
      : "en"; // get the language attribute from the HTML or default to english if it doesn't exists.
    if (langcode === "fa-IR") {
      //This is a check so that we use the gergorian (Western) calendar if the users locale is Farsi. This is the calendar used primarily by Farsi speakers outside of Iran, and is easily understood by those inside.
      langcode = `${langcode}-u-ca-gregory`;
    }
    const options = { year: "numeric", month: "long", day: "numeric" };
    if (with_time) {
      options.hour = "numeric";
      options.minute = "numeric";
    } else {
      options.timeZone = "UTC";
    }

    const formattedDate = new Intl.DateTimeFormat(langcode, options).format(
      date * 1000
    );

    return formattedDate;
  },
  convertArabicToEnglishNumbers(string) {
    return string
      .replace(/[\u0660-\u0669]/g, function (c) {
        return c.charCodeAt(0) - 0x0660;
      })
      .replace(/[\u06f0-\u06f9]/g, function (c) {
        return c.charCodeAt(0) - 0x06f0;
      });
  },
  get_url_param(name) {
    let results = new RegExp("[?&]" + name + "=([^&#]*)").exec(
      window.location.search
    );
    return results !== null ? results[1] || 0 : false;
  },
};

window.METRICS = {
  setupDatePicker: function (endpoint_url, callback, startDate, endDate) {
    $(".date_range_picker").daterangepicker(
      {
        showDropdowns: true,
        ranges: {
          "All time": [moment(0), moment().endOf("year")],
          [moment().format("MMMM YYYY")]: [
            moment().startOf("month"),
            moment().endOf("month"),
          ],
          [moment().subtract(1, "month").format("MMMM YYYY")]: [
            moment().subtract(1, "month").startOf("month"),
            moment().subtract(1, "month").endOf("month"),
          ],
          [moment().format("YYYY")]: [
            moment().startOf("year"),
            moment().endOf("year"),
          ],
          [moment().subtract(1, "year").format("YYYY")]: [
            moment().subtract(1, "year").startOf("year"),
            moment().subtract(1, "year").endOf("year"),
          ],
          [moment().subtract(2, "year").format("YYYY")]: [
            moment().subtract(2, "year").startOf("year"),
            moment().subtract(2, "year").endOf("year"),
          ],
        },
        linkedCalendars: false,
        locale: {
          format: "YYYY-MM-DD",
        },
        startDate: startDate || moment(0),
        endDate: endDate || moment().endOf("year").format("YYYY-MM-DD"),
      },
      function (start, end, label) {
        $(".loading-spinner").addClass("active");
        jQuery
          .ajax({
            type: "GET",
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            url: `${endpoint_url}?start=${start.format(
              "YYYY-MM-DD"
            )}&end=${end.format("YYYY-MM-DD")}`,
            beforeSend: function (xhr) {
              xhr.setRequestHeader("X-WP-Nonce", wpApiShare.nonce);
            },
          })
          .done(function (data) {
            $(".loading-spinner").removeClass("active");
            if (label === "Custom Range") {
              label =
                start.format("MMMM D, YYYY") +
                " - " +
                end.format("MMMM D, YYYY");
            }
            callback(data, label, start, end);
          })
          .fail(function (err) {
            console.log("error");
            console.log(err);
            // jQuery("#errors").append(err.responseText)
          });
        // console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
      }
    );
  },
};

// nonce timeout fix
// every 5 minutes will check if nonce timed out
// if it did then it will redirect to login
window.fiveMinuteTimer = setInterval(function () {
  //check if timed out
  get_new_notification_count().fail(function (x) {
    window.location.reload();
  });
}, 300000); //300000 = five minutes
