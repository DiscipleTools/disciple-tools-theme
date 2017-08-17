(function($, wpApiSettings) {
  "use strict";
  let contacts;
  let filterFunctions = [];
  let dataTable;

  $.ajax({
    url: wpApiSettings.root + "dt-hooks/v1/contacts",
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function(data) {
      const statusNames = wpApiSettings.contacts_custom_fields_settings.overall_status.default;
      contacts = data;
      $(function() {
        displayContacts();
        setUpFilterPane();
        $(".js-priorities-show").on("click", function(e) {
          priorityShow($(this).data("priority"));
          e.preventDefault();
        });
        $(".js-contacts-clear-filters").on("click", function() {
          clearFilters();
        });
        $(".js-contacts-my-contacts").on("click", function() {
          showMyContacts();
        });
      });
    },
    error: function(jqXHR, textStatus, errorThrown) {
      $(function() {
        $(".js-list-contacts-loading > td").html(
            "<div>" + wpApiSettings.txt_error + "</div>" +
            "<div>" + jqXHR.responseText + "</div>"
        );
      });
    },
  });


  function displayContacts() {
    const $table = $(".js-list-contacts");
    if (! $table.length) {
      return;
    }
    $table.find("> tbody").empty();
    const template = _.template(`<tr data-contact-index="<%- index %>">
      <td><img src="<%- template_directory_uri %>/assets/images/star.svg" width=13 height=12></td>
      <td>
        <a href="<%- permalink %>"><%- post_title %></a>
        <br>
        <%- phone_numbers.join(", ") %>
      </td>
      <td><span class="status status--<%- overall_status %>"><%- status %></td>
      <td>
        <span class="milestone milestone--<%- sharing_milestone_key %>"><%- sharing_milestone %></span>
        <br>
        <span class="milestone milestone--<%- belief_milestone_key %>"><%- belief_milestone %></span>
      </td>
      <td><%- assigned_to ? assigned_to.name : "" %></td>
      <td><%- locations.join(", ") %></td>
      <td><%= group_links %></td>
    </tr>`);
    const ccfs = wpApiSettings.contacts_custom_fields_settings;
    _.forEach(contacts, function(contact, index) {
      const belief_milestone_key = _.find(
        ['baptizing', 'baptized', 'belief'],
        function(key) { return contact["milestone_" + key]; }
      );
      const sharing_milestone_key = _.find(
        ['planting', 'in_group', 'sharing', 'can_share'],
        function(key) { return contact["milestone_" + key]; }
      );
      let status = "";
      if (contact.overall_status === "accepted") {
        status = ccfs.seeker_path.default[contact.seeker_path];
      } else {
        status = ccfs.overall_status.default[contact.overall_status];
      }
      const group_links = _.map(contact.groups, function(group) {
          return '<a href="' + _.escape(group.permalink) + '">' + _.escape(group.post_title) + "</a>";
        }).join(", ");
      const context = _.assign({}, contact, wpApiSettings, {
        index,
        status,
        belief_milestone_key,
        sharing_milestone_key,
        belief_milestone: (ccfs["milestone_" + belief_milestone_key] || {}).name || "",
        sharing_milestone: (ccfs["milestone_" + sharing_milestone_key] || {}).name || "",
        group_links,
      });
      context.assigned_to = context.assigned_to;
      $table.append(
        $.parseHTML(template(context))
      );
    });
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
      const contact = contacts[dataIndex];
      return _.every(filterFunctions, function(filterFunction) { return filterFunction(contact); });
    });
    dataTable = $table.DataTable({
      responsive: true,
      iDisplayLength: 100,
      bLengthChange: false,
      sDom: 'firtlp<"clearfix">'
    });
  }


  function setUpFilterPane() {
    if (! $(".js-list-contacts").length) {
      return;
    }
    const counts = {
      assigned_login: _.countBy(_(contacts).map('assigned_to.user_login').filter().value()),
      overall_status: _.countBy(_.map(contacts, 'overall_status')),
      locations: _.countBy(_.flatten(_.map(contacts, 'locations'))),
      seeker_path: _.countBy(contacts, 'seeker_path'),
      requires_update: _.countBy(contacts, 'requires_update'),
    };

    $(".js-contacts-filter :not(.js-contacts-filter-title)").remove();
    Object.keys(counts).forEach(function(filterType) {
      $(".js-contacts-filter[data-filter='" + filterType + "']")
        .append(createFilterCheckboxes(filterType, counts[filterType]));
    });
    $(".js-contacts-filter-title").on("click", function() {
      const $title = $(this);
      $title.parents(".js-contacts-filter").toggleClass("filter--closed");
    });
  }

  function createFilterCheckboxes(filterType, counts) {
    const $div = $("<div>");
    const ccfs = wpApiSettings.contacts_custom_fields_settings;
    Object.keys(counts).sort().forEach(function(key) {
      let humanText;
      if (filterType === 'seeker_path' || filterType === 'overall_status') {
        humanText = ccfs[filterType].default[key];
      } else if (filterType === 'requires_update') {
        humanText = key === "true" ? wpApiSettings.txt_yes : wpApiSettings.txt_no;
      } else {
        humanText = key;
      }
      $div.append(
        $("<div>").append(
          $("<label>")
            .css("cursor", "pointer")
            .addClass("js-filter-checkbox-label")
            .data("filter-type", filterType)
            .data("filter-value", key)
            .append(
              $("<input>")
              .attr("type", "checkbox")
              .on("change", function() {
                updateFilterFunctions();
                updateButtonStates();
                dataTable.draw();
              })
            )
            .append(document.createTextNode(humanText))
            .append($("<span>")
              .css("float", "right")
              .append(document.createTextNode(counts[key]))
            )
        )
      );
    });
    if ($.isEmptyObject(counts)) {
      $div.append(
          document.createTextNode(wpApiSettings.txt_no_filters)
      );
    }
    return $div;
  }

  function updateButtonStates() {
    $(".js-contacts-clear-filters").prop("disabled", filterFunctions.length == 0);
  }

  function updateFilterFunctions() {
    filterFunctions = [];
    {
      const $checkedStatusLabels = $(".js-filter-checkbox-label")
        .filter(function() { return $(this).data("filter-type") === "overall_status"; })
        .filter(function() { return $(this).find("input[type=checkbox]")[0].checked; });

      if ($checkedStatusLabels.length > 0) {
        filterFunctions.push(function(contact) {
          return _.some($checkedStatusLabels, function(label) {
            return $(label).data("filter-value") === contact.overall_status;
          });
        });
      }
    }

    {
      const $checkedLocationsLabels = $(".js-filter-checkbox-label")
        .filter(function() { return $(this).data("filter-type") === "locations"; })
        .filter(function() { return $(this).find("input[type=checkbox]")[0].checked; });

      if ($checkedLocationsLabels.length > 0) {
        filterFunctions.push(function(contact) {
          return _.some($checkedLocationsLabels, function(label) {
            return _.includes(contact.locations, $(label).data("filter-value"));
          });
        });
      }
    }

    {
      const $checkedAssignedLabels = $(".js-filter-checkbox-label")
        .filter(function() { return $(this).data("filter-type") === "assigned_login"; })
        .filter(function() { return $(this).find("input[type=checkbox]")[0].checked; });

      if ($checkedAssignedLabels.length > 0) {
        filterFunctions.push(function(contact) {
          return _.some($checkedAssignedLabels, function(label) {
            return $(label).data("filter-value") === _.get(contact, "assigned_to.user_login");
          });
        });
      }
    }

    {
      const $checkedSeekerPathLabels = $(".js-filter-checkbox-label")
        .filter(function() { return $(this).data("filter-type") === "seeker_path"; })
        .filter(function() { return $(this).find("input[type=checkbox]")[0].checked; });

      if ($checkedSeekerPathLabels.length > 0) {
        filterFunctions.push(function(contact) {
          return _.some($checkedSeekerPathLabels, function(label) {
            return $(label).data("filter-value") === contact.seeker_path;
          });
        });
      }
    }

    {
      const $checkedRequiresUpdateLabels = $(".js-filter-checkbox-label")
        .filter(function() { return $(this).data("filter-type") === "requires_update"; })
        .filter(function() { return $(this).find("input[type=checkbox]")[0].checked; });

      if ($checkedRequiresUpdateLabels.length > 0) {
        filterFunctions.push(function(contact) {
          return _.some($checkedRequiresUpdateLabels, function(label) {
            const value = $(label).data("filter-value") === "true";
            return value === contact.requires_update;
          });
        });
      }
    }

  }

  function priorityShow(priority) {
    $(".js-filter-checkbox-label input[type=checkbox]").each(function() {
      this.checked = false;
    });
    tickFilters("assigned_login", wpApiSettings.current_user_login);
    tickFilters("overall_status", "accepted");

    if (priority === "update_needed") {
      tickFilters("requires_update", "true");
    } else if (priority === "meeting_scheduled") {
      tickFilters("seeker_path", "scheduled");
    } else if (priority === "contact_unattempted") {
      tickFilters("seeker_path", "none");
    } else {
      throw new Error("Priority not recognized: " + priority);
    }

    updateFilterFunctions();
    updateButtonStates();
    dataTable.draw();
  }

  function showMyContacts() {
    $(".js-filter-checkbox-label input[type=checkbox]").each(function() {
      this.checked = false;
    });
    tickFilters("assigned_login", wpApiSettings.current_user_login);
    updateFilterFunctions();
    updateButtonStates();
    dataTable.draw();
  }

  function tickFilters(filterType, filterValue) {
    $(".js-filter-checkbox-label")
      .filter(function() { return $(this).data("filter-type") == filterType; })
      .each(function() {
        if ($(this).data("filter-value") === filterValue) {
          $(this).find("input[type=checkbox]")[0].checked = true;
        }
      });
    $(".js-contacts-filter[data-filter=" + filterType + "]").removeClass("filter--closed");
  }

  function clearFilters() {
    $(".js-filter-checkbox-label input[type=checkbox]").each(function() {
      this.checked = false;
    });
    updateFilterFunctions();
    updateButtonStates();
    dataTable.draw();
  }


})(window.jQuery, window.wpApiSettings);
