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
      _.forEach(data, function(contact) {
        if (contact.status) { throw new Exception("Did not expect 'status' to be defined"); }
        contact.status = statusNames[contact.overall_status];
      });
      contacts = data;
      $(function() {
        displayContacts();
        setUpFilterPane();
      });
    },
    error: function() {
      $(function() {
        $(".js-list-contacts-loading > td").text(wpApiSettings.txt_error);
      });
    },
  });


  function displayContacts() {
    const $table = $(".js-list-contacts");
    if (! $table.length) {
      $ul.find(":not(summary)").remove();
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
      <td><%- assigned_to.user_login %></td>
      <td><%- locations.join(", ") %></td>
      <td><%- groups.join(", ") %></td>
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
      const context = _.assign({}, contact, wpApiSettings, {
        index,
        status,
        belief_milestone_key,
        sharing_milestone_key,
        belief_milestone: (ccfs["milestone_" + belief_milestone_key] || {}).name || "",
        sharing_milestone: (ccfs["milestone_" + sharing_milestone_key] || {}).name || "",
      });
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
      assigned_login: _.countBy(_.map(contacts, 'assigned_to.user_login')),
      status: _.countBy(_.map(contacts, 'status')),
      locations: _.countBy(_.flatten(_.map(contacts, 'locations'))),
    };

    $(".js-contacts-filter :not(.js-contacts-filter-title)").remove();
    _.forEach(["assigned_login", "status", "locations"], function(filterType) {
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
    Object.keys(counts).sort().forEach(function(key) {
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
                dataTable.draw();
              })
            )
            .append(document.createTextNode(key))
            .append($("<span>")
              .css("float", "right")
              .append(document.createTextNode(counts[key]))
            )
        )
      );
    });
    if ($.isEmptyObject(counts)) {
      $div.append(
          document.createTextNode(wpApiSettings.txt_no_records)
      );
    }
    return $div;
  }

  function updateFilterFunctions() {
    filterFunctions = [];
    {
      const $checkedStatusLabels = $(".js-filter-checkbox-label")
        .filter(function() { return $(this).data("filter-type") === "status"; })
        .filter(function() { return $(this).find("input[type=checkbox]")[0].checked; });

      if ($checkedStatusLabels.length > 0) {
        filterFunctions.push(function(contact) {
          return _.some($checkedStatusLabels, function(label) {
            return $(label).data("filter-value") === contact.status;
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
            return $(label).data("filter-value") === contact.assigned_to.user_login;
          });
        });
      }
    }

  }


})(window.jQuery, window.wpApiSettings);
