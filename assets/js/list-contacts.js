/* global jQuery:false, List:false, wpApiSettings:false, _:false */


jQuery(document).ready(function($) {
  "use strict";

  if (! $("#my-contacts").length || ! $("#my-contacts .list").length) {
    return;
  }
  const myContacts = new List('my-contacts', {
    valueNames: [
      'post_title',
      'assigned_name',
      { name: 'permalink', attr: 'href' },
    ],
    page: 30,
    pagination: true,
  });

  $.ajax({
    url: wpApiSettings.root + "dt-hooks/v1/user/1/contacts",
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function(data) {
      const statusNames = wpApiSettings.contacts_custom_fields_settings.overall_status.default;
      _.forEach(data, function(contact) {
        if (contact.status) { throw new Exception("Did not expect 'status' to be defined"); }
        contact.status = statusNames[contact.status_number];
      });
      myContacts.clear();
      myContacts.add(data);
      setUpFilters(data);
    },
    error: function() {
      $(".js-list-contacts-loading").text(wpApiSettings.txt_error);
    },
    complete: function() {
      $("#my-contacts .js-search-tools")
        .removeClass("faded-out")
        .find("button.sort[data-sort]")
          .removeAttr("disabled")
        .end()
        .find("input.search")
          .removeAttr("disabled");
    },
  });

  function setUpFilters(data) {
    const counts = {
      status: _.countBy(_.map(data, 'status')),
      locations: _.countBy(_.flatten(_.map(data, 'locations'))),
    };

    $(".js-contacts-filter :not(summary)").remove();
    _.forEach(["status", "locations"], function(filterType) {
      $(".js-contacts-filter[data-filter='" + filterType + "']")
        .append(createFilterCheckboxes(filterType, counts.status));
    });
  }

  function createFilterCheckboxes(filterType, counts) {
    const $div = $("<div>");
    Object.keys(counts).forEach(function(key) {
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
              .on("change", function() { updateFilters(); })
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

  function updateFilters() {
    const filterFunctions = [];

    {
      const $checkedStatusLabels = $(".js-filter-checkbox-label")
        .filter(function() { return $(this).data("filter-type") === "status_number"; })
        .filter(function() { return $(this).find("input[type=checkbox]")[0].checked; });

      if ($checkedStatusLabels.length > 0) {
        filterFunctions.push(function(item) {
          const values = item.values();
          return _.some($checkedStatusLabels, function(label) {
            return $(label).data("filter-value") === values.status;
          });
        });
      }
    }

    {
      const $checkedLocationsLabels = $(".js-filter-checkbox-label")
        .filter(function() { return $(this).data("filter-type") === "locations"; })
        .filter(function() { return $(this).find("input[type=checkbox]")[0].checked; });

      if ($checkedLocationsLabels.length > 0) {
        filterFunctions.push(function(item) {
          const values = item.values();
          return _.some($checkedLocationsLabels, function(label) {
            return _.includes(values.locations, $(label).data("filter-value"));
          });
        });
      }
    }

    if (filterFunctions.length > 0) {
      myContacts.filter(function(item) {
        return _.every(filterFunctions, function(filterFunction) { return filterFunction(item); });
      });
    } else {
      myContacts.filter(); // reset filters
    }
  }

});
