(function($, wpApiSettings) {
  "use strict";
  let contacts;
  let searchFilterFunction;
  let otherFilterFunctions = [];

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
      contacts = data;
      $(function() {
        displayContacts();
        setUpFilterPane();
        /* The page could have been loaded with this search field already
         * filled in, for instance, after clicking the back button, so it's
         * useful to make sure the search filter is still applied after
         * clicking the back button. */
        $(".js-list-contacts-search").trigger("input");
      });
    },
    error: function() {
      $(function() {
        $(".js-list-contacts-loading").text(wpApiSettings.txt_error);
      });
    },
    complete: function() {
      $(function() {
        $(".js-search-tools").removeClass("faded-out");
        $(".js-list-contacts-search").removeAttr("disabled");
        $(".js-list-contacts-sort").removeAttr("disabled");
      });
    },
  });

  $(function() {
    $(".js-list-contacts-search").on("input", function() {
      const searchString = $(this).val().trim();
      if (searchString) {
        searchFilterFunction = function(contact) {
          return contact.post_title.toLowerCase().indexOf(searchString) !== -1;
        };
      } else {
        searchFilterFunction = null;
      }
      filterContacts();
    });

    $(".js-list-contacts-sort").on("click", function() {
      const sortBy = $(this).data("sort");
      const sortOrder = $(this).data("order") || "asc";
      sortList(sortBy, sortOrder);
      $(this).data("order", sortOrder === "asc" ? "desc" : "asc");
    });
  });

  function sortList(sortBy, sortOrder) {
    const $list = $(".js-list-contacts");
    _($(".js-list-contacts > li").get())
      .orderBy(function(item) { return contacts[$(item).data("contact-index")][sortBy]; }, sortOrder)
      .forEach(function(item) { $list.append(item); });
  }



  function displayContacts() {
    const $ul = $(".js-list-contacts");
    if (! $ul.length) {
      $ul.find(":not(summary)").remove();
      return;
    }
    $ul.empty();
    _.forEach(contacts, function(contact, index) {
      $ul.append(
        $("<li>")
          .data("contact-index", index)
          .append(
            $("<a>")
              .attr("href", contact.permalink)
              .append(document.createTextNode(contact.post_title))
          )
          .append(
            $("<span>")
              .addClass("float-right")
              .addClass("grey")
              .append(document.createTextNode(contact.assigned_name))
          )
      );
    });
  }


  function setUpFilterPane() {
    if (! $(".js-list-contacts").length) {
      return;
    }
    const counts = {
      status: _.countBy(_.map(contacts, 'status')),
      locations: _.countBy(_.flatten(_.map(contacts, 'locations'))),
    };

    $(".js-contacts-filter :not(summary)").remove();
    _.forEach(["status", "locations"], function(filterType) {
      $(".js-contacts-filter[data-filter='" + filterType + "']")
        .append(createFilterCheckboxes(filterType, counts[filterType]));
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
              .on("change", function() { updateOtherFilters(); })
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

  function updateOtherFilters() {
    otherFilterFunctions = [];
    {
      const $checkedStatusLabels = $(".js-filter-checkbox-label")
        .filter(function() { return $(this).data("filter-type") === "status"; })
        .filter(function() { return $(this).find("input[type=checkbox]")[0].checked; });

      if ($checkedStatusLabels.length > 0) {
        otherFilterFunctions.push(function(contact) {
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
        otherFilterFunctions.push(function(contact) {
          return _.some($checkedLocationsLabels, function(label) {
            return _.includes(contact.locations, $(label).data("filter-value"));
          });
        });
      }
    }

    filterContacts();
  }

  function filterContacts() {
    const filterFunctions = _.clone(otherFilterFunctions);
    if (searchFilterFunction) {
      filterFunctions.push(searchFilterFunction);
    }
    if (filterFunctions.length > 0) {
      $(".js-list-contacts > li").each(function() {
        const contact = contacts[$(this).data("contact-index")];
        const show = _.every(filterFunctions, function(filterFunction) {
          return filterFunction(contact);
        });
        if (show) {
          $(this).removeAttr("hidden");
        } else {
          $(this).attr("hidden", true);
        }
      });
    } else {
      $(".js-list-contacts > li").removeAttr("hidden");
    }
  }

})(window.jQuery, window.wpApiSettings);
