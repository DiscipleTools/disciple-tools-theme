(function($, wpApiSettings, Foundation) {
  "use strict";
  var urlParams = new URLSearchParams(window.location.search);
  let searchQuery = {assigned_to:['me']};
  const current_username = wpApiSettings.current_user_login;
  let items = []
  let customFilters = []
  let savedFilters = wpApiSettings.filters || {[wpApiSettings.current_post_type]:[]}
  let filterToSave = ""
  let currentFilters = $("#current-filters")
  let newFilterLabels = []

  let loading_spinner = $(".loading-spinner")

  let viewParam = urlParams.get("view")
  if ( viewParam ){
    $('[name="view"]').removeAttr('checked');
    if ( viewParam === "saved-filters"){
      let id = urlParams.get("id");
      if ( id ){
        $(`input[name=view][value=saved-filters][data-id='${id}']`).prop('checked', true);
      }
    } else if ( viewParam === "custom_filter" ){
      let filterParams = urlParams.get("filter")
      if ( filterParams ){
        searchQuery = JSON.parse( decodeURIComponent( filterParams ));
        addCustomFilter("Custom Filter")
      }
    } else {
      $("input[name=view][value=" + viewParam + "]").prop('checked', true);
    }
  }
  getContactForCurrentView()

  function get_contacts(query, filter, offset) {
    loading_spinner.addClass("active")
    let data = query || searchQuery
    if ( offset ){
      data.offset = offset
    }
    $.ajax({
      url: wpApiSettings.root + "dt/v1/" + wpApiSettings.current_post_type + "/search",
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      },
      data: query || searchQuery,
    }).then(data=>{
      if (offset){
        items = _.unionBy(items, data[wpApiSettings.current_post_type] || [], "ID")
      } else  {
        items = data[wpApiSettings.current_post_type] || []
      }
      $('.filter-result-text').html(`Showing ${items.length} of ${data.total}`)
      $("#current-filters").html(selectedFilters.html())
      displayRows();
      setupCurrentFilterLabels(query || searchQuery, filter)
      loading_spinner.removeClass("active")
    })
  }


  let savedFiltersList = $("#saved-filters")
  function get_filters() {
    API.get_filters().then(filters=>{
      if ( filters[wpApiSettings.current_post_type ] ){
        savedFilters = filters
        setupFilters(filters[wpApiSettings.current_post_type])
      }
    })
  }
  // get_filters()
  function setupFilters(filters){
    savedFiltersList.html("")
    filters.forEach(filter=>{
      if (filter){
        const radio = $("<input name='view' class='js-list-view' autocomplete='off'>")
          .attr("type", "radio")
          .val("saved-filters")
          .data("id", filter.ID)
          .on("change", function() {
            getContactForCurrentView()
          });
        savedFiltersList.append(
          $("<div>").append(
            $("<label>")
              .css("cursor", "pointer")
              .addClass("js-filter-checkbox-label")
              // .data("filter-type", filterType)
              .data("filter-value", status)
              .append(radio)
              .append(document.createTextNode(filter.name))
            // .append($("<span>")
            //     .css("float", "right")
            //     .append(document.createTextNode(counts[key]))
            // )
          ))
      }
    })
  }

  $(function() {
    $(window).resize(function() {
      if (Foundation.MediaQuery.is('small only')) {
        if ($(".js-filters-accordion .js-filters-modal-content").length === 0) {
          $(".js-filters-accordion").append($(".js-filters-modal-content").detach());
        }
      } else {
        if ($(".js-pane-filters .js-filters-modal-contact").length === 0) {
          $(".js-pane-filters").append($(".js-filters-modal-content").detach());
        }
      }
    }).trigger("resize");
  });


  const templates = {
    contacts: _.template(`<tr>
      <!--<td><img src="<%- template_directory_uri %>/dt-assets/images/star.svg" width=13 height=12></td>-->
      <!--<td></td>-->
      <td>
        <a href="<%- permalink %>"><%- post_title %></a>
        <br>
        <%- phone_numbers.join(", ") %>
        <span class="show-for-small-only">
            <span class="milestone milestone--<%- sharing_milestone_key %>"><%- sharing_milestone %></span>
            <span class="milestone milestone--<%- belief_milestone_key %>"><%- belief_milestone %></span>
            <%- status %>
            <!--<%- assigned_to ? assigned_to.name : "" %>-->
            <%= locations.join(", ") %>
            <%= group_links %>
          </span>
      </td>
      <td class="hide-for-small-only"><span class="status status--<%- overall_status %>"><%- status %></span></td>
      <td class="hide-for-small-only">
        <span class="milestone milestone--<%- sharing_milestone_key %>"><%- sharing_milestone %></span>
        <br>
        <span class="milestone milestone--<%- belief_milestone_key %>"><%- belief_milestone %></span>
      </td>
      <td class="hide-for-small-only"><%- assigned_to ? assigned_to.name : "" %></td>
      <td class="hide-for-small-only"><%= locations.join(", ") %></td>
      <td class="hide-for-small-only"><%= group_links %></td>
      <!--<td><%- last_modified %></td>-->
    </tr>`),
    groups: _.template(`<tr>
      <!--<td><img src="<%- template_directory_uri %>/dt-assets/images/green_flag.svg" width=10 height=12></td>-->
      <td></td>
      <td><a href="<%- permalink %>"><%- post_title %></a></td>
      <td><span class="group-status group-status--<%- group_status %>"><%- status %></span></td>
      <td><span class="group-type group-type--<%- group_type %>"><%- type %></span></td>
      <td style="text-align: right"><%- member_count %></td>
      <td><%= leader_links %></td>
      <td><%- locations.join(", ") %></td>
      <td><%- last_modified %></td>
    </tr>`),
  };


  function displayRows() {
    const $table = $(".js-list");
    if (!$table.length) {
      return;
    }
    $table.find("> tbody").empty();
    let rows = ""
    _.forEach(items, function (item, index) {
      if (wpApiSettings.current_post_type === "contacts") {
        let row = buildContactRow(item, index);
        rows += row[0].outerHTML
      } else if (wpApiSettings.current_post_type === "groups") {
        rows += buildGroupRow(item, index)[0].outerHTML
      }
    });
    $table.append(rows)
  }

  function buildContactRow(contact, index) {
    const template = templates[wpApiSettings.current_post_type];
    const ccfs = wpApiSettings.contacts_custom_fields_settings;
    const belief_milestone_key = _.find(
      ['baptizing', 'baptized', 'belief'],
      function(key) { return contact["milestone_" + key]; }
    );
    const sharing_milestone_key = _.find(
      ['planting', 'in_group', 'sharing', 'can_share'],
      function(key) { return contact["milestone_" + key]; }
    );
    let status = "";
    if (contact.overall_status === "active") {
      status = ccfs.seeker_path.default[contact.seeker_path];
    } else {
      status = ccfs.overall_status.default[contact.overall_status];
    }
    const group_links = _.map(contact.groups, function(group) {
      return '<a href="' + _.escape(group.permalink) + '">' + _.escape(group.post_title) + "</a>";
    }).join(", ");
    const context = _.assign({last_modified: 0}, contact, wpApiSettings, {
      index,
      status,
      belief_milestone_key,
      sharing_milestone_key,
      belief_milestone: (ccfs["milestone_" + belief_milestone_key] || {}).name || "",
      sharing_milestone: (ccfs["milestone_" + sharing_milestone_key] || {}).name || "",
      group_links,
    });
    context.assigned_to = context.assigned_to;
    return $.parseHTML(template(context));
  }

  function buildGroupRow(group, index) {
    const template = templates[wpApiSettings.current_post_type];
    const leader_links = _.map(group.leaders, function(leader) {
      return '<a href="' + _.escape(leader.permalink) + '">' + _.escape(leader.post_title) + "</a>";
    }).join(", ");
    const gcfs = wpApiSettings.groups_custom_fields_settings;
    const status = gcfs.group_status.default[group.group_status || "active"];
    const type = gcfs.group_type.default[group.group_type || "active"];
    const context = _.assign({}, group, wpApiSettings, {
      leader_links,
      status,
      type
    });
    return $.parseHTML(template(context));
  }

  $(document).on('change', '.js-list-view', e => {
    getContactForCurrentView()
  });


  function setupCurrentFilterLabels(query, filter) {
    let html = ""
    if (filter && filter.labels){
      filter.labels.forEach(label=>{
        html+= `<span class="current-filter ${label.field}" id="${label.id}">${label.name}</span>`
      })
    } else {
      for( let query_key in query ) {
        if (Array.isArray(query[query_key])) {

          query[query_key].forEach(q => {

            html += `<span class="current-filter ${query_key}" id="${q}">${q}</span>`
          })
        } else {
          html += `<span class="current-filter search" id="${query[query_key]}">${query[query_key]}</span>`
        }
      }

    }
    currentFilters.html(html)
  }

  function getContactForCurrentView() {
    let checked = $(".js-list-view:checked")
    let currentView = checked.val()
    let query = {assigned_to:["me"]}
    let filter = null
    let viewGetParam = `?view=${currentView}`
    if ( currentView === "all_contacts" ){
      query.assigned_to = ["all"]
    } else if ( currentView === "contacts_shared_with_me" ){
      // query.include = ["shared"]
      query.assigned_to = ["shared"]
    }
    if ( currentView === "assignment_needed" ){
      query.overall_status = ["unassigned"]
    } else if ( currentView === "update_needed" ){
      query.requires_update = ["yes"]
    } else if ( currentView === "meeting_scheduled" ){
      query.overall_status = ["active"]
      query.seeker_path = ["scheduled"]
    } else if ( currentView === "contact_unattempted" ){
      query.overall_status = ["active"]
      query.seeker_path = ["none"]
    } else if ( currentView === "custom_filter"){
      let filterId = checked.data("id")
      filter = _.find(customFilters, {ID:filterId})
      query = filter.query
      viewGetParam += `&filter=${encodeURIComponent(JSON.stringify(query))}`
    } else if ( currentView === "saved-filters" ){
      let filterId = checked.data("id")
      filter = _.find(savedFilters[wpApiSettings.current_post_type], {ID:filterId})
      query = filter.query
      viewGetParam += `&id=${filterId}`
    }
    history.pushState(null, null, viewGetParam);
    searchQuery = JSON.parse(JSON.stringify(query))
    get_contacts(query, filter)

  }



  /**
   * Locations
   */
  $.typeahead({
    input: '.js-typeahead-locations',
    minLength: 0,
    searchOnFocus: true,
    maxItem: 20,
    template: function (query, item) {
      return `<span>${_.escape(item.name)}</span>`
    },
    source: TYPEAHEADS.typeaheadSource('locations', 'dt/v1/locations-compact/'),
    display: "name",
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      data: [],
      callback: {
        onCancel: function (node, item) {
          $(`#${item.ID}.locations`).remove()
          newFilterLabels = _.remove(newFilterLabels, l=>l.id===item.ID)
        }
      }
    },
    callback: {
      onResult: function (node, query, result, resultCount) {
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#locations-result-container').html(text);
      },
      onHideLayout: function () {
        $('#locations-result-container').html("");
      },
      onClick: function(node, a, item, event) {
        newFilterLabels.push({id:item.ID, name:item.name, field:"locations"})
        selectedFilters.append(`<span class="current-filter locations" id="${item.ID}">${item.name}</span>`)
      }
    }
  });

  /**
   * Assigned_to
   */
  $.typeahead({
    input: '.js-typeahead-assigned_to',
    minLength: 0,
    searchOnFocus: true,
    multiselect: {
      matchOn: ["ID"],
      data: [],
      callback: {
        onCancel: function (node, item) {
          $(`#${item.ID}.assigned_to`).remove()
          newFilterLabels = _.remove(newFilterLabels, l=>l.id===item.ID)
        }
      }
    },
    source: {
      users: {
        display: ["name", "user"],
        ajax: {
          url: wpApiSettings.root + 'dt/v1/users/get_users',
          data: {
            s: "{{query}}"
          },
          beforeSend: function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
          },
          // callback: {
          //   done: function (data) {
          //     return data.posts || data
          //   }
          // }
        }
      }
    },

    templateValue: "{{name}}",
    template: function (query, item) {
      return `<span>${item.name}</span>`
    },
    dynamic: true,
    hint: true,
    emptyTemplate: 'No users found "{{query}}"',
    callback: {
      onResult: function (node, query, result, resultCount) {
        let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
        $('#assigned_to-result-container').html(text);
      },
      onClick: function(node, a, item, event) {
        selectedFilters.append(`<span class="current-filter assigned_to" id="${item.ID}">${item.name}</span>`)
        newFilterLabels.push({id:item.ID, name:item.name, field:"assigned_to"})

      }
    }
  });


  //modal options
  let fields = ["overall_status", "seeker_path"]
  fields.forEach(field_key=>{
    let field_options = _.get(wpApiSettings, `contacts_custom_fields_settings.${field_key}.default`) || {}
    for( let status in  field_options ){

      const checkbox = $("<input autocomplete='off'>")
        .attr("type", "checkbox")
        .val(status)
        .on("change", function(a, b, c) {
          if ($(this).is(":checked")){
            let optionId = $(this).val()
            let optionName = field_options[optionId]
            newFilterLabels.push({id:$(this).val(), name:optionName, field:field_key})
            selectedFilters.append(`<span class="current-filter ${field_key}" id="${optionId}">${optionName}</span>`)
          } else {
            $(`#${$(this).val()}.${field_key}`).remove()
            newFilterLabels = _.remove(newFilterLabels, l=>l.id===optionId)
          }
        });
      $(`#${field_key}-options`).append(
        $("<div>").append(
          $("<label>")
            .css("cursor", "pointer")
            .data("filter-value", status)
            .append(checkbox)
            .append(document.createTextNode(field_options[status]))
        )
      );
    }
  })


  //create new filter
  let selectedFilters = $("#selected-filters")
  $("#confirm-filter-contacts").on("click", function () {

    searchQuery.assigned_to = _.map(_.get(Typeahead['.js-typeahead-assigned_to'], "items"), "ID")
    searchQuery.locations = _.map(_.get(Typeahead['.js-typeahead-locations'], "items"), "ID")
    searchQuery.overall_status = []
    searchQuery.seeker_path = []
    $("#overall_status-options input:checked").each(function(){
      searchQuery.overall_status.push($(this).val())
    })
    $("#seeker_path-options input:checked").each(function(){
      searchQuery.seeker_path.push($(this).val())
    })
    addCustomFilter("Custom Filter")
  })

  function addCustomFilter(name) {
    let ID = new Date().getTime() / 1000
    let newFilter = {ID:ID, name:name, query:JSON.parse(JSON.stringify(searchQuery)), labels:newFilterLabels}
    customFilters.push(newFilter)

    let saveFilter = $(`<span style="float:right" data-filter="${ID}">Save</span>`).on("click", function () {
      $('#save-filter-modal').foundation('open');
      filterToSave = ID;
    })
    let filterRow = $(`<label class='list-view ${ID}'>`).append(`
      <input type="radio" name="view" value="custom_filter" data-id="${ID}" class="js-list-view" checked autocomplete="off">
        ${name}
    `).append(saveFilter)
    $(".custom-filters").append(filterRow)
    $(".custom-filters input").on("change", function () {
      getContactForCurrentView()
    })
    getContactForCurrentView()
  }

  $(`#confirm-filter-save`).on('click', function () {
    let filterName = $('#filter-name').val()

    let filter = _.find(customFilters, {ID:filterToSave})
    if (filter.query){
      let newFilter = {
        name: filterName,
        ID: filterToSave,
        query:filter.query,
        labels: filter.labels
      };

      savedFilters[wpApiSettings.current_post_type].push(newFilter)
      API.save_filters(savedFilters).then(()=>{
        $(`.custom-filters .list-view.${filterToSave}`).remove()
        setupFilters(savedFilters[wpApiSettings.current_post_type])
      })
    }
  })


  $("#search-contacts").on("click", function () {
    let searchText = $("#search-query").val()
    console.log(searchText);
    searchQuery = {text:searchText, assigned_to:["all"]}
    addCustomFilter(searchText)

  })
  $("#search-contacts-mobile").on("click", function () {
    let searchText = $("#search-query-mobile").val()
    console.log(searchText);
    searchQuery = {text:searchText, assigned_to:["all"]}
    get_contacts(searchQuery)
  })


  $(".js-list-filter-title").on("click", function() {
    const $title = $(this);
    $title.parents(".js-list-filter").toggleClass("filter--closed");
  }).on("keydown", function(event) {
    if (event.keyCode === 13) {
      $(this).trigger("click");
    }
  });

  //toggle show search input on mobile
  $("#open-search").on("click", function () {
    $(".hideable-search").toggle()
  })

  $("#load-more").on('click', function () {
    get_contacts(null, null, items.length)
  })

})(window.jQuery, window.wpApiSettings, window.Foundation);
