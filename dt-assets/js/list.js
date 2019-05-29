(function($, wpApiListSettings, Foundation) {
  "use strict";

  function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) === ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) === 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }
  let cookie = getCookie("last_view");
  let cachedFilter = {}
  try {
    cachedFilter = JSON.parse(cookie)
  } catch (e) {
    cachedFilter = {}
  }
  let showClosedCookie = getCookie("show_closed")
  let showClosedCheckbox = $('#show_closed')
  let currentFilter = {}
  let items = []
  let customFilters = []
  let savedFilters = wpApiListSettings.filters || {[wpApiListSettings.current_post_type]:[]}
  if (Array.isArray(savedFilters)){
    savedFilters = {}
  }
  if ( !savedFilters[wpApiListSettings.current_post_type]){
    savedFilters[wpApiListSettings.current_post_type] = []
  }
  let filterToSave = ""
  let filterToDelete = ""
  let filterToEdit = ""
  let currentFilters = $("#current-filters")
  let newFilterLabels = []
  let loading_spinner = $(".loading-spinner")
  let tableHeaderRow = $('.js-list thead .sortable th')
  let getContactsPromise = null
  let selectedFilterTab = "all"

  function get_contacts( offset = 0, sort ) {
    loading_spinner.addClass("active")
    let data = currentFilter.query
    if ( offset ){
      data.offset = offset
    }
    if ( sort ){
      data.sort = sort
      data.offset = 0
    } else if (!data.sort) {
      data.sort = wpApiListSettings.current_post_type === "contacts" ? "overall_status" : "group_type";
    }
    currentFilter.query = data
    document.cookie = `last_view=${JSON.stringify(currentFilter)}`
    let showClosed = showClosedCheckbox.prop("checked")
    if ( !showClosed ){
      if ( wpApiListSettings.current_post_type === "contacts" ){
        if ( !data.overall_status ){
          data.overall_status = [];
        }
        if ( !data.overall_status.includes("-closed") ){
          data.overall_status.push( "-closed" )
        }
      } else {
        if ( !data.group_status ){
          data.group_status = [];
        }
        data.group_status.push( "-inactive" )
      }
    }

    //abort previous promise if it is not finished.
    if (getContactsPromise && _.get(getContactsPromise, "readyState") !== 4){
      getContactsPromise.abort()
    }
    getContactsPromise = $.ajax({
      url: wpApiListSettings.root + "dt/v1/" + wpApiListSettings.current_post_type + "/search",
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiListSettings.nonce);
      },
      data: data,
    })
    getContactsPromise.then((data)=>{
      if (offset){
        items = _.unionBy(items, data[wpApiListSettings.current_post_type] || [], "ID")
      } else  {
        items = data[wpApiListSettings.current_post_type] || []
      }
      let result_text = wpApiListSettings.translations.txt_info.replace("_START_", items.length).replace("_TOTAL_", data.total)
      $('.filter-result-text').html(result_text)
      displayRows();
      setupCurrentFilterLabels()
      loading_spinner.removeClass("active")
    }).catch(err => {
      if ( !_.get( err, "statusText" ) === "abort" ) {
        console.error(err)
      }
    })
  }


  let savedFiltersList = $("#saved-filters")
  function setupFilters(filters){
    savedFiltersList.empty()
    filters.forEach(filter=>{
      if (filter){
        let deleteFilter = $(`<span style="float:right" data-filter="${_.escape( filter.ID )}">
            <img style="padding: 0 4px" src="${wpApiShare.template_dir}/dt-assets/images/trash.svg">
        </span>`).on("click", function () {
          $(`.delete-filter-name`).html(filter.name)
          $('#delete-filter-modal').foundation('open');
          filterToDelete = filter.ID;
        })
        let editFilter = $(`<span style="float:right" data-filter="${_.escape( filter.ID )}">
            <img style="padding: 0 4px" src="${wpApiShare.template_dir}/dt-assets/images/edit.svg">
        </span>`).on("click", function () {
          editSavedFilter( filter )
          filterToEdit = filter.ID;
        })
        let filterName =  `<span class="filter-list-name" data-filter="${_.escape( filter.ID )}">${_.escape( filter.name )}</span>`
        const radio = $(`<input name='view' class='js-list-view' autocomplete='off' data-id="${_.escape( filter.ID )}" >`)
          .attr("type", "radio")
          .val("saved-filters")
          .on("change", function() {
          });
        savedFiltersList.append(
          $("<div>").append(
            $("<label>")
              .css("cursor", "pointer")
              .addClass("js-filter-checkbox-label")
              // .data("filter-type", filterType)
              .data("filter-value", status)
              .append(radio)
              .append(filterName)
              .append(deleteFilter)
              .append(editFilter)

          )
        )
      }
    })
  }

  //set the "show closed" checkbox
  if ( showClosedCookie === "true" ){
    showClosedCheckbox.prop('checked', true)
  }
  setupFilters(savedFilters[wpApiListSettings.current_post_type])
  //look at the cookie to see what was the last selected view
  let selectedFilter = ""
  if ( cachedFilter && !_.isEmpty(cachedFilter)){
    if (cachedFilter.type==="saved-filters"){
      if ( _.find(savedFilters[wpApiListSettings.current_post_type], {ID: cachedFilter.ID})){
        $(`input[name=view][value=saved-filters][data-id='${_.escape( cachedFilter.ID )}']`).prop('checked', true);
      }
    } else if ( cachedFilter.type==="default" ){
      if ( cachedFilter.tab ){
        selectedFilterTab = cachedFilter.tab
      }
      selectedFilter = cachedFilter.ID || "no_filter"
    } else if ( cachedFilter.type === "custom_filter" ){
      addCustomFilter(cachedFilter.name, "default", cachedFilter.query, cachedFilter.labels)
    }
  } else {
    selectedFilter = "no_filter"
  }
  $(`#list-filter-tabs [data-id='${_.escape( selectedFilterTab )}'] a`).click()
  if ( selectedFilter ){
    $(`.is-active input[name=view][value="${_.escape( selectedFilter )}"].js-list-view`).prop('checked', true);
  }


  $(function() {
    $(window).resize(function() {
      if (Foundation.MediaQuery.is('small only') || Foundation.MediaQuery.is('medium only')) {
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



  $("#list-filter-tabs .accordion-item").on("click", function (a, b) {

    let newFilterTab = $(this).data("id")
    if ( selectedFilterTab !== newFilterTab ){
      selectedFilterTab = newFilterTab
      let checked = $(".js-list-view:checked").val() || "no_filter"
      if ( checked === "saved-filters" || checked === "custom-filter"){
        checked = "no_filter"
      }
      $(".js-list-view-count").text("")
      $(`.is-active input[name="view"][value="${checked}"].js-list-view`).prop("checked", true)
      getContactForCurrentView()
      get_filter_counts()
    }
  })


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
      <td class="hide-for-small-only"><span class="status status--<%- seeker_path %>"><%- seeker_path %></span></td>
      <td class="hide-for-small-only">
        <span class="milestone milestone--<%- access_milestone_key %>"><%- access_milestone %></span>
        <% if (access_milestone){ %>
            <br>
        <% } %>
        <span class="milestone milestone--<%- sharing_milestone_key %>"><%- sharing_milestone %></span>
        <% if (sharing_milestone){ %>
            <br>
        <% } %>
        <span class="milestone milestone--<%- belief_milestone_key %>"><%- belief_milestone %></span>
      </td>
      <td class="hide-for-small-only"><%- assigned_to ? assigned_to.name : "" %></td>
      <td class="hide-for-small-only"><%= locations.join(", ") %></td>
      <td class="hide-for-small-only"><%= group_links %></td>
      <!--<td><%- last_modified %></td>-->
    </tr>`),
    groups: _.template(`<tr>
      <!--<td><img src="<%- template_directory_uri %>/dt-assets/images/green_flag.svg" width=10 height=12></td>-->
      <!--<td></td>-->
      <td class="show-for-small-only">
        <a href="<%- permalink %>"><%- post_title %></a>
        <br>
        <%- status %> <%- type %> <%- member_count %>
        <%- locations.join(", ") %>
        <%= leader_links %>
      </td>
      <td class="hide-for-small-only"><a href="<%- permalink %>"><%- post_title %></a></td>
      <td class="hide-for-small-only"><span class="group-status group-status--<%- group_status %>"><%- status %></span></td>
      <td class="hide-for-small-only"><span class="group-type group-type--<%- group_type %>"><%- type %></span></td>
      <td class="hide-for-small-only" style="text-align: center"><%- member_count %></td>
      <td class="hide-for-small-only"><%= leader_links %></td>
      <td class="hide-for-small-only"><%- locations.join(", ") %></td>
      <!--<td><%- last_modified %></td>-->
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
      if (wpApiListSettings.current_post_type === "contacts") {
        rows += buildContactRow(item, index)[0].outerHTML;
      } else if (wpApiListSettings.current_post_type === "groups") {
        rows += buildGroupRow(item, index)[0].outerHTML
      }
    });
    $table.append(rows)
  }

  function buildContactRow(contact, index) {
    const template = templates[wpApiListSettings.current_post_type];
    const ccfs = wpApiListSettings.custom_fields_settings;
    const access_milestone_key = _.find(
      ["has_bible", "reading_bible"],
      function (key) { return (contact["milestones"] || []).includes(`milestone_${_.escape( key )}`); }
    )
    const belief_milestone_key = _.find(
      ['baptizing', 'baptized', 'belief'],
      function(key) { return (contact["milestones"] || []).includes(`milestone_${_.escape( key )}`); }
    );
    const sharing_milestone_key = _.find(
      ['planting', 'in_group', 'sharing', 'can_share'],
      function(key) { return (contact["milestones"] || []).includes(`milestone_${_.escape( key )}`); }
    );
    let status = _.get( ccfs, `overall_status.default[${_.escape( contact.overall_status )}]["label"]`, contact.overall_status )
    let seeker_path = _.get( ccfs, `seeker_path.default[${_.escape( contact.seeker_path )}]["label"]`, contact.seeker_path )
    // if (contact.overall_status === "active") {
    //   status = ccfs.seeker_path.default[contact.seeker_path];
    // } else {
    //   status = ccfs.overall_status.default[contact.overall_status];
    // }
    const group_links = _.map(contact.groups, function(group) {
      return '<a href="' + _.escape(group.permalink) + '">' + group.post_title + "</a>";
    }).join(", ");
    const context = _.assign({last_modified: 0}, contact, wpApiListSettings, {
      index,
      status,
      belief_milestone_key,
      sharing_milestone_key,
      access_milestone_key,
      seeker_path,
      access_milestone: _.get(ccfs, `milestones.default["milestone_${access_milestone_key}"].label`, ""),
      belief_milestone: _.get(ccfs, `milestones.default["milestone_${belief_milestone_key}"].label`, ""),
      sharing_milestone: _.get(ccfs, `milestones.default["milestone_${sharing_milestone_key}"].label`, ""),
      group_links,
    });
    return $.parseHTML(template(context));
  }

  function buildGroupRow(group, index) {
    const template = templates[wpApiListSettings.current_post_type];
    const leader_links = _.map(group.leaders, function(leader) {
      return '<a href="' + _.escape(leader.permalink) + '">' + _.escape(leader.post_title) + "</a>";
    }).join(", ");
    const gcfs = wpApiListSettings.custom_fields_settings;
    const status = _.get( gcfs, `group_status.default[${group.group_status || "active"}]["label"]`, group.group_status )
    const type = _.get( gcfs, `gcfs.group_type.default[${group.group_type || "group"}]["label"]`, group.group_type )
    const context = _.assign({}, group, wpApiListSettings, {
      leader_links,
      status,
      type
    });
    return $.parseHTML(template(context));
  }

  $(document).on('change', '.js-list-view', e => {
    getContactForCurrentView()
  });


  function setupCurrentFilterLabels() {
    let html = ""
    let filter = currentFilter
    if (filter && filter.labels){
      filter.labels.forEach(label=>{
        html+= `<span class="current-filter ${_.escape( label.field )}">${_.escape( label.name )}</span>`
      })
    } else {
      let query = filter.query
      _.forOwn( query, query_key=> {
        if (Array.isArray(query[query_key])) {

          query[query_key].forEach(q => {

            html += `<span class="current-filter ${_.escape( query_key )}">${_.escape( q )}</span>`
          })
        } else {
          html += `<span class="current-filter search">${_.escape( query[query_key] )}</span>`
        }
      })
    }

    if ( filter.query.sort ){
      let sortLabel = filter.query.sort
      if ( sortLabel.includes('last_modified') ){
        sortLabel = wpApiListSettings.translations.date_modified
      } else if (  sortLabel.includes('post_date') ) {
        sortLabel = wpApiListSettings.translations.creation_date
      } else  {
        //get label for table header
        sortLabel = $(`.sortable [data-id="${_.escape( sortLabel.replace('-', '') )}"]`).text()
      }
      html += `<span class="current-filter" data-id="sort">
          ${_.escape( wpApiListSettings.translations.sorting_by )}: ${_.escape( sortLabel )}
      </span>`
    }
    currentFilters.html(html)
  }

  function getContactForCurrentView() {
    let checked = $(".js-list-view:checked")
    let currentView = checked.val()
    let filterId = checked.data("id") || currentView
    let query = {}
    let filter = {
      type:"default",
      ID:currentView,
      query:{},
      labels:[{ id:"all", name:wpApiListSettings.translations.filter_all, field: "assigned"}]
    }
    if ( currentView !== "custom_filter"){
      if ( wpApiListSettings.current_post_type === "groups" ){
        selectedFilterTab = currentView
      }
      filter.tab = selectedFilterTab
      if ( selectedFilterTab === "all" ){
        query.assigned_to = ["all"]
        filter.labels = [{ id:"all", name:wpApiListSettings.translations.filter_all, field: "assigned"}]
      } else if ( selectedFilterTab === "shared" ){
        query.assigned_to = ["shared"]
        filter.labels = [{ id:"shared", name:wpApiListSettings.translations.filter_shared, field: "assigned"}]
      } else if ( selectedFilterTab === "subassigned" ){
        query.subassigned = [wpApiListSettings.current_user_contact_id]
        filter.labels = [{ id:"subbassigned", name:wpApiListSettings.translations.filter_subassigned, field: "assigned"}]
      }
      else if ( selectedFilterTab === "my" ){
        query.assigned_to = ["me"]
        filter.labels = [{ id:"me", name:wpApiListSettings.translations.filter_my, field: "assigned"}]
      }
    }
    let filter_name = wpApiListSettings.translations[`filter_${currentView}`]
    if ( currentView === "needs_accepted" ){
      query.overall_status = ["assigned"]
      filter.labels = [{ id:"needs_accepted", name:filter_name, field: "accepted"}]
    } else if ( currentView === "new") {
      query.overall_status = ["new"]
      filter.labels = [{ id:"new", name:filter_name, field: "overall_status"}]
    } else if ( currentView === "active") {
      query.overall_status = ["active"]
      filter.labels = [{ id:"active", name:filter_name, field: "overall_status"}]
    } else if ( currentView === "assignment_needed" ){
      query.overall_status = ["unassigned"]
      filter.labels = [{ id:"unassigned", name:filter_name, field: "assigned"}]
    } else if ( currentView === "update_needed" ){
      filter.labels = [{ id:"update_needed", name:filter_name, field: "requires_update"}]
      query.requires_update = [true]
    } else if ( currentView === "meeting_scheduled" ){
      query.overall_status = ["active"]
      query.seeker_path = ["scheduled"]
      filter.labels = [{ id:"active", name:filter_name, field: "seeker_path"}]
    } else if ( currentView === "contact_unattempted" ){
      query.overall_status = ["active"]
      query.seeker_path = ["none"]
      filter.labels = [{ id:"all", name:filter_name, field: "seeker_path"}]
    } else if ( currentView === "custom_filter"){
      let filterId = checked.data("id")
      filter = _.find(customFilters, {ID:filterId})
      filter.type = currentView
      query = filter.query
    } else if ( currentView === "saved-filters" ){
      filter = _.find(savedFilters[wpApiListSettings.current_post_type], {ID:filterId}) || _.find(savedFilters[wpApiListSettings.current_post_type], {ID:filterId.toString()})
      filter.type = currentView
      query = filter.query
    }

    filter.query = query
    let sortField = _.get(currentFilter, "query.sort", "overall_status").replace("-", "");
    filter.query.sort = _.get(currentFilter, "query.sort", "overall_status");
    if ( _.get( cachedFilter, "query.sort") ){
      filter.query.sort = cachedFilter.query.sort;
      sortField = cachedFilter.query.sort.replace("-", "");
    }
    //reset sorting in table header
    tableHeaderRow.removeClass("sorting_asc")
    tableHeaderRow.removeClass("sorting_desc")
    let headerCell = $(`.js-list thead .sortable th[data-id="${_.escape( sortField )}"]`)
    headerCell.addClass("sorting_asc")
    tableHeaderRow.data("sort", '')
    headerCell.data("sort", 'asc')

    currentFilter = JSON.parse(JSON.stringify(filter))
    get_contacts()
  }
  if (!getContactsPromise){
    getContactForCurrentView()
  }



  $('#filter-modal .tabs-title a').on("click", function () {
    let id = $(this).attr('href').replace('#', '')
    $(`.js-typeahead-${id}`).trigger('input')
  })

  //create new custom filter from modal
  let selectedFilters = $("#selected-filters")
  $("#confirm-filter-contacts").on("click", function () {
    let searchQuery = getSearchQuery()
    let filterName = _.escape( $('#new-filter-name').val() )
    addCustomFilter( filterName || "Custom Filter", "custom-filter", searchQuery, newFilterLabels)
  })

  let getSearchQuery = ()=>{
    let searchQuery = {}
    let fieldsFiltered = newFilterLabels.map(f=>f.field)
    fieldsFiltered.forEach(field=>{
      searchQuery[field] =[]
      let type = _.get(wpApiListSettings, `custom_fields_settings.${field}.type` )
      if ( type === "connection" || type === "user_select" ){
        searchQuery[field] = _.map(_.get(Typeahead[`.js-typeahead-${field}`], "items"), "ID")
      }  if ( type === "multi_select" ){
        searchQuery[field] = _.map(_.get(Typeahead[`.js-typeahead-${field}`], "items"), "key")
      } if ( type === "location" ){
        searchQuery[field] = _.map(_.get(Typeahead[`.js-typeahead-${field}`], "items"), "ID")
      } else if ( type === "date" || field === "created_on" ) {
        searchQuery[field] = {}
        let start = $(`.dt_date_picker[data-field="${field}"][data-delimit="start"]`).val()
        if ( start ){
          searchQuery[field]["start"] = start
        }
        let end = $(`.dt_date_picker[data-field="${field}"][data-delimit="end"]`).val()
        if ( end ){
          searchQuery[field]["end"]  = end
        }
      } else {
        $(`#${field}-options input:checked`).each(function(){
          searchQuery[field].push($(this).val())
        })
      }
      if ( wpApiListSettings.current_post_type === "contacts" ){
        if ( $("#combine_subassigned").is(":checked") ){
          searchQuery["combine"] = ["subassigned"]
        }
      }
    })
    return searchQuery
  }


  //add the new filter in the filters list
  function addCustomFilter(name, type, query, labels) {
    query = query || currentFilter.query
    let ID = new Date().getTime() / 1000
    currentFilter = {ID, type, name: _.escape( name ), query:JSON.parse(JSON.stringify(query)), labels:labels}
    customFilters.push(JSON.parse(JSON.stringify(currentFilter)))

    let saveFilter = $(`<a style="float:right" data-filter="${_.escape( ID )}">
        ${_.escape( wpApiListSettings.translations.save )}
    </a>`).on("click", function () {
      $("#filter-name").val(name)
      $('#save-filter-modal').foundation('open');
      filterToSave = ID;
    })
    let filterRow = $(`<label class='list-view ${_.escape( ID )}'>`).append(`
      <input type="radio" name="view" value="custom_filter" data-id="${_.escape( ID )}" class="js-list-view" checked autocomplete="off">
        ${_.escape( name )}
    `).append(saveFilter)
    $(".custom-filters").append(filterRow)
    $(".custom-filters input").on("change", function () {
      getContactForCurrentView()
    })
    getContactForCurrentView()
  }

  //save the filter in the user meta
  $(`#confirm-filter-save`).on('click', function () {
    let filterName = $('#filter-name').val()
    let filter = _.find(customFilters, {ID:filterToSave})
    filter.name = _.escape( filterName )
    if (filter.query){
      savedFilters[wpApiListSettings.current_post_type].push(filter)
      API.save_filters(savedFilters).then(()=>{
        $(`.custom-filters [class*="list-view ${filterToSave}`).remove()
        setupFilters(savedFilters[wpApiListSettings.current_post_type])
        $(`input[name="view"][value="saved-filters"][data-id='${filterToSave}']`).prop('checked', true);
        getContactForCurrentView()
        $('#filter-name').val("")
      }).catch(err => { console.error(err) })
    }
  })

  //delete a filter
  $(`#confirm-filter-delete`).on('click', function () {
    _.pullAllBy(savedFilters[wpApiListSettings.current_post_type], [{ID:filterToDelete}], "ID")
    API.save_filters(savedFilters).then(()=>{
      setupFilters(savedFilters[wpApiListSettings.current_post_type])
    }).catch(err => { console.error(err) })
  })


  $("#search").on("click", function () {
    let searchText = _.escape( $("#search-query").val() )
    let query = {text:searchText, assigned_to:["all"]}
    let labels = [{ id:"search", name:searchText, field: "search"}]
    addCustomFilter(searchText, "search", query, labels)
  })
  $("#search-mobile").on("click", function () {
    let searchText = _.escape( $("#search-query-mobile").val() )
    let query = {text:searchText, assigned_to:["all"]}
    let labels = [{ id:"search", name:searchText, field: "search"}]
    addCustomFilter(searchText, "search", query, labels)
  })
  $('.search-input').on('keyup', function (e) {
    if ( e.keyCode === 13 ){
      $("#search").trigger("click")
    }
  })
  $('.search-input-mobile').on('keyup', function (e) {
    if ( e.keyCode === 13 ){
      $("#search-mobile").trigger("click")
    }
  })

  //pressing enter launches a search
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
    get_contacts( items.length )
  })

  //sort the table by clicking the header
  $('.js-list th').on("click", function () {
    let id = $(this).data('id')
    let sort = $(this).data('sort')
    tableHeaderRow.removeClass("sorting_asc")
    tableHeaderRow.removeClass("sorting_desc")
    tableHeaderRow.data("sort", '')
    if ( !sort || sort === 'desc' ){
      $(this).data('sort', 'asc')
      $(this).addClass("sorting_asc")
      $(this).removeClass("sorting_desc")
    } else {
      $(this).data('sort', 'desc')
      $(this).removeClass("sorting_asc")
      $(this).addClass("sorting_desc")
      id = `-${id}`
    }
    get_contacts(0, id)
  })

  $('.js-sort-by').on("click", function () {
    tableHeaderRow.removeClass("sorting_asc")
    tableHeaderRow.removeClass("sorting_desc")
    let dir = $(this).data('order')
    let field = $(this).data('field')
    get_contacts(0, (dir === "asc" ? "" : '-') + field)
  })


  /**
   * Modal options
   */

  /**
   * geonames
   */
  let loadLocationTypeahead = ()=> {
    if (!window.Typeahead['.js-typeahead-geonames']) {
      $.typeahead({
        input: '.js-typeahead-geonames',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        maxItem: 20,
        template: function (query, item) {
          return `<span>${_.escape(item.name)}</span>`
        },
        dropdownFilter: [{
          key: 'group',
          value: 'used',
          template: 'Used Locations',
          all: 'All Locations'
        }],
        source: {
          used: {
            display: "name",
            ajax: {
              url: wpApiShare.root + 'dt/v1/mapping_module/search_geonames_by_name',
              data: {
                s: "{{query}}",
                filter: function () {
                  return _.get(window.Typeahead['.js-typeahead-geonames'].filters.dropdown, 'value', 'all')
                }
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  if (typeof typeaheadTotals !== "undefined") {
                    typeaheadTotals.field = data.total
                  }
                  return data.posts
                }
              }
            }
          }
        },
        display: "name",
        templateValue: "{{name}}",
        dynamic: true,
        multiselect: {
          matchOn: ["ID"],
          data: [],
          callback: {
            onCancel: function (node, item) {
              $(`.current-filter[data-id="${item.ID}"].geonames`).remove()
              _.pullAllBy(newFilterLabels, [{id: item.ID}], "id")
            }
          }
        },
        callback: {
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#geonames-result-container').html(text);
          },
          onReady(){
            this.filters.dropdown = {key: "group", value: "used", template: "Used Locations"}
            this.container
              .removeClass("filter")
              .find("." + this.options.selector.filterButton)
              .html("Used Locations");
          },
          onHideLayout: function () {
            $('#geonames-result-container').html("");
          },
          onClick: function (node, a, item) {
            let name = _.get(wpApiListSettings, `custom_fields_settings.geonames.name`, 'geonames')
            newFilterLabels.push({id: item.ID, name: `${name}:${item.name}`, field: "geonames"})
            selectedFilters.append(`<span class="current-filter geonames" data-id="${_.escape( item.ID )}">${_.escape( name )}:${_.escape( item.name )}</span>`)
          }
        }
      });
    }
  }

  /**
   * Leaders
   */
  let loadLeadersTypeahead = ()=> {
    if (!window.Typeahead['.js-typeahead-leaders']) {
      $.typeahead({
        input: '.js-typeahead-leaders',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        maxItem: 20,
        template: window.TYPEAHEADS.contactListRowTemplate,
        source: TYPEAHEADS.typeaheadContactsSource(),
        display: "name",
        templateValue: "{{name}}",
        dynamic: true,
        multiselect: {
          matchOn: ["ID"],
          data: [],
          callback: {
            onCancel: function (node, item) {
              $(`.current-filter[data-id="${item.ID}"].leaders`).remove()
              _.pullAllBy(newFilterLabels, [{id: item.ID}], "id")
            }
          }
        },
        callback: {
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#leaders-result-container').html(text);
          },
          onHideLayout: function () {
            $('#leaders-result-container').html("");
          },
          onClick: function (node, a, item, event) {
            newFilterLabels.push({id: item.ID, name: item.name, field: "leaders"})
            selectedFilters.append(`<span class="current-filter leaders" data-id="${_.escape( item.ID )}">${_.escape( item.name )}</span>`)
          }
        }
      });
    }
  }

  /**
   * Leaders
   */
  let loadSubassignedTypeahead = ()=> {
    if (!window.Typeahead['.js-typeahead-subassigned']) {
      $.typeahead({
        input: '.js-typeahead-subassigned',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        maxItem: 20,
        template: window.TYPEAHEADS.contactListRowTemplate,
        source: TYPEAHEADS.typeaheadContactsSource(),
        display: "name",
        templateValue: "{{name}}",
        dynamic: true,
        multiselect: {
          matchOn: ["ID"],
          data: [],
          callback: {
            onCancel: function (node, item) {
              $(`.current-filter[data-id="${_.escape( item.ID )}"].subassigned`).remove()
              _.pullAllBy(newFilterLabels, [{id: item.ID}], "id")
            }
          }
        },
        callback: {
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#subassigned-result-container').html(text);
          },
          onHideLayout: function () {
            $('#subassigned-result-container').html("");
          },
          onClick: function (node, a, item, event) {
            let name = _.get(wpApiListSettings, `custom_fields_settings.subassigned.name`, 'subassigned')
            newFilterLabels.push({id: item.ID, name: `${name}:${item.name}`, field: "subassigned"})
            selectedFilters.append(`<span class="current-filter subassigned" data-id="${_.escape( item.ID )}">${_.escape( name )}:${_.escape( item.name )}</span>`)
          }
        }
      });
    }
  }

  /**
   * Assigned_to
   */
  let loadAssignedToTypeahead = ()=>{
    if ( !window.Typeahead[".js-typeahead-assigned_to"]){
      $.typeahead({
        input: '.js-typeahead-assigned_to',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        multiselect: {
          matchOn: ["ID"],
          data: [],
          callback: {
            onCancel: function (node, item) {
              $(`.current-filter[data-id="${item.ID}"].assigned_to`).remove()
              _.pullAllBy(newFilterLabels, [{id:item.ID}], "id")
            }
          }
        },
        source: {
          users: {
            display: ["name", "user"],
            ajax: {
              url: wpApiListSettings.root + 'dt/v1/users/get_users',
              data: {
                s: "{{query}}"
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiListSettings.nonce);
              },
            }
          }
        },

        templateValue: "{{name}}",
        template: function (query, item) {
          return `<span class="row">
            <span class="avatar"><img src="{{avatar}}"/> </span>
            <span>${_.escape( item.name )}</span>
          </span>`
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
            let name = _.get(wpApiListSettings, `custom_fields_settings.assigned_to.name`, 'assigned_to')
            selectedFilters.append(`<span class="current-filter assigned_to" data-id="${_.escape( item.ID )}">${_.escape( name )}:${_.escape( item.name )}</span>`)
            newFilterLabels.push({id:item.ID, name:`${name}:${item.name}`, field:"assigned_to"})

          }
        }
      });
    }
  }
  let sourcesTypeahead = $(".js-typeahead-sources")
  let loadMultiSelectTypeaheads = async function loadMultiSelectTypeaheads() {
    for (let input of $(".multi_select .typeahead__query input")) {
      let field = $(input).data('field')
      let typeahead_name = `.js-typeahead-${field}`

      if (window.Typeahead[typeahead_name]) {
        return
      }

      let sourceData =  { data: [] }
      let fieldOptions = _.get(wpApiListSettings, `custom_fields_settings.${field}.default`, {})
      if (field === 'sources') {
        /* Similar code is in contact-details.js, copy-pasted for now. */
        sourcesTypeahead.attr("disabled", true) // disable while loading AJAX
        const response = await fetch(wpApiListSettings.root + 'dt/v1/contact/list-sources', {
          credentials: 'same-origin', // needed for Safari
          headers: {
            'X-WP-Nonce': wpApiShare.nonce,
          },
        });
        _.forOwn(await response.json(), (sourceValue, sourceKey) => {
          sourceData.data.push({
            key: sourceKey,
            value: sourceValue || "",
            name: sourceKey, // name is used for building URL params later
          })
        })
        sourcesTypeahead.attr("disabled", false)
      } else if ( Object.keys(fieldOptions).length > 0 ){
        _.forOwn(fieldOptions, (val, key)=>{
            sourceData.data.push({
              key: key,
              name:key,
              value: val.label || key
            })
          })
      } else {
        sourceData = {
          [field]: {
            display: ["value"],
            ajax: {
              url: `${wpApiListSettings.root}dt-posts/v2/contacts/multi-select-values`,
              data: {
                s: "{{query}}",
                field
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  return (data || []).map(tag => {
                    let label = _.get(fieldOptions, tag + ".label", tag)
                    return {value: label, key: tag}
                  })
                }
              }
            }
          }
        }
      }
      $.typeahead({
        input: `.js-typeahead-${field}`,
        minLength: 0,
        maxItem: 20,
        searchOnFocus: true,
        template: function (query, item) {
          return `<span>${_.escape(item.value)}</span>`
        },
        source: sourceData,
        display: "value",
        templateValue: "{{value}}",
        dynamic: true,
        multiselect: {
          matchOn: ["key"],
          data: [],
          callback: {
            onCancel: function (node, item) {
              $(`.current-filter[data-id="${item.key}"].${field}`).remove()
              _.pullAllBy(newFilterLabels, [{id:item.key}], "id")
            }
          }
        },
        callback: {
          onClick: function(node, a, item, event){
            let name = _.get(wpApiListSettings, `custom_fields_settings.${field}.name`, field)
            selectedFilters.append(`<span class="current-filter ${_.escape( field )}" data-id="${_.escape( item.key )}">${_.escape( name )}:${_.escape( item.value )}</span>`)
            newFilterLabels.push({id:item.key, name:`${name}:${item.value}`, field})
          },
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $(`#${field}-result-container`).html(text);
          },
          onHideLayout: function () {
            $(`#${field}-result-container`).html("");
          }
        }
      });
    }
  }

  /*
   * Setup filter box
   */
  let typeaheadsLoaded = null
  $('#filter-modal').on("open.zf.reveal", function () {
    newFilterLabels=[]
    if ( wpApiListSettings.current_post_type === "groups" ){
      loadLocationTypeahead()
      loadAssignedToTypeahead()
      // loadLeadersTypeahead()
      typeaheadsLoaded = loadMultiSelectTypeaheads().catch(err => { console.error(err) })
    } else if ( wpApiListSettings.current_post_type === "contacts" ){
      loadLocationTypeahead()
      loadAssignedToTypeahead()
      loadSubassignedTypeahead()
      typeaheadsLoaded = loadMultiSelectTypeaheads().catch(err => { console.error(err) })
    }
    $('#new-filter-name').val('')
    $("#filter-modal input.dt_date_picker").each(function () {
      $(this).val('')
    })
    $("#filter-modal input:checked").each(function () {
      $(this).prop('checked', false)
    })
    selectedFilters.empty();
    $(".typeahead__query input").each(function () {
      let typeahead = Typeahead['.'+$(this).attr("class").split(/\s+/)[0]]
      if ( typeahead ){
        for (let i = 0; i < typeahead.items.length; i ){
          typeahead.cancelMultiselectItem(0)
        }
        typeahead.node.trigger('propertychange.typeahead')
      }
    })
    $('#confirm-filter-contacts').show()
    $('#save-filter-edits').hide()
  })

  let editSavedFilter = function( filter ){
    $('#filter-modal').foundation('open');
    typeaheadsLoaded.then(()=>{
      newFilterLabels = filter.labels
      let connectionTypeKeys = Object.keys(wpApiListSettings.connection_types)
      connectionTypeKeys.push("assigned_to")
      connectionTypeKeys.push("geonames")
      newFilterLabels.forEach(label=>{
        selectedFilters.append(`<span class="current-filter ${_.escape( label.field )}" data-id="${_.escape( label.id )}">${_.escape( label.name )}</span>`)
        let type = _.get(wpApiListSettings, `custom_fields_settings.${label.field}.type`)
        if ( type === "key_select" || type === "boolean" ){
          $(`#filter-modal #${label.field}-options input[value="${label.id}"]`).prop('checked', true)
        } else if ( type === "date" ){
          $(`#filter-modal #${label.field}-options #${label.id}`).datepicker('setDate', label.date)
        } else if ( connectionTypeKeys.includes( label.field ) ){
          Typeahead[`.js-typeahead-${label.field}`].addMultiselectItemLayout({ID:label.id, name:label.name})
        } else if ( type === "multi_select" ){
          Typeahead[`.js-typeahead-${label.field}`].addMultiselectItemLayout({key:label.id, value:label.name})
        }
      })
      ;(filter.query.combine || []).forEach(c=>{
        $(`#combine_${c}`).prop('checked', true)
      })
      $('#new-filter-name').val(filter.name)
      $('#confirm-filter-contacts').hide()
      $('#save-filter-edits').data("filter-id", filter.ID).show()
    })
  }
  $('#save-filter-edits').on('click', function () {
    let searchQuery = getSearchQuery()
    let filterId = $('#save-filter-edits').data("filter-id")
    let filter = _.find(savedFilters[wpApiListSettings.current_post_type], {ID:filterId})
    filter.name = $('#new-filter-name').val()
    $(`.filter-list-name[data-filter="${filterId}"]`).text(filter.name)
    filter.query = searchQuery
    filter.label = newFilterLabels
    API.save_filters(savedFilters)
    getContactForCurrentView()
  })

  $('#filter-tabs').on('change.zf.tabs', function (a, b) {
    let field = $(b).data("field")
    $(`.tabs-panel`).removeClass('is-active')
    $(`#${field}.tabs-panel`).addClass('is-active')
    if (field &&  Typeahead[`.js-typeahead-${field}`]){
      Typeahead[`.js-typeahead-${field}`].adjustInputSize()
    }
  })

  //watch all other checkboxes
  $('#filter-modal .key_select_options input').on("change", function() {
    let field_key = $(this).data('field');
    let optionId = $(this).val()
    if ($(this).is(":checked")){
      let field_options = _.get( wpApiListSettings, `custom_fields_settings.${field_key}.default` )
      let optionName = field_options[optionId]["label"]
      let name = _.get(wpApiListSettings, `custom_fields_settings.${field_key}.name`, field_key)
      newFilterLabels.push({id:$(this).val(), name:`${name}:${optionName}`, field:field_key})
      selectedFilters.append(`<span class="current-filter ${_.escape( field_key )}" data-id="${_.escape( optionId )}">${_.escape( name )}:${_.escape( optionName )}</span>`)
    } else {
      $(`.current-filter[data-id="${$(this).val()}"].${field_key}`).remove()
      _.pullAllBy(newFilterLabels, [{id:optionId}], "id")
    }
  })
  //watch bool checkboxes
  $('#filter-modal .boolean_options input').on("change", function() {
    let field_key = $(this).data('field');
    let optionId = $(this).val()
    let label = $(this).data('label');
    if ($(this).is(":checked")){
      let field = _.get( wpApiListSettings, `custom_fields_settings.${field_key}` )
      newFilterLabels.push({id:$(this).val(), name:`${field.name}:${label}`, field:field_key})
      selectedFilters.append(`<span class="current-filter ${_.escape( field_key )}" data-id="${_.escape( optionId )}">${_.escape( field.name )}:${_.escape( label )}</span>`)
    } else {
      $(`.current-filter[data-id="${$(this).val()}"].${field_key}`).remove()
      _.pullAllBy(newFilterLabels, [{id:optionId}], "id")
    }
  })
  $('#filter-modal .dt_date_picker').datepicker({
    dateFormat: 'yy-mm-dd',
    onSelect: function (date) {
      let id = $(this).data('field')
      let delimiter = $(this).data('delimit')
      let delimiterLabel = wpApiListSettings.translations[`range_${delimiter}`]
      let fieldName = _.get( wpApiListSettings, `custom_fields_settings.${id}.name` , id)
      if ( id === "created_on" ){
        fieldName = wpApiListSettings.translations.creation_date
      }
      //remove existing filters
      _.pullAllBy(newFilterLabels, [{id:`${id}_${delimiter}`}], "id")
      $(`.current-filter[data-id="${id}_${delimiter}"]`).remove()
      //add new filters
      newFilterLabels.push({id:`${id}_${delimiter}`, name:`${fieldName} ${delimiterLabel}:${date}`, field:id, date:date})
      selectedFilters.append(`
        <span class="current-filter ${id}_${delimiter}" 
              data-id="${id}_${delimiter}">
                ${fieldName} ${delimiterLabel}:${date}
        </span>
      `)
    },
    changeMonth: true,
    changeYear: true
  })
  $('#filter-modal .clear-date-picker').on('click', function () {
      let id = $(this).data('for')
      $(`#filter-modal #${id}`).datepicker('setDate', null)
      _.pullAllBy(newFilterLabels, [{id:`${id}`}], "id")
      $(`.current-filter[data-id="${id}"]`).remove()
  })

  let type = "contact"
  if ( wpApiListSettings.current_post_type === "groups"){
    type = "group"
  }

  let getFilterCountsPromise = null
  let get_filter_counts = ()=>{
    let showClosed = showClosedCheckbox.prop("checked")
    if ( getFilterCountsPromise && _.get( getFilterCountsPromise, "readyState") !== 4 ){
      getFilterCountsPromise.abort()
    }
    getFilterCountsPromise = $.ajax({
      url: `${wpApiListSettings.root}dt/v1/${type}/counts?tab=${selectedFilterTab}&closed=${showClosed}`,
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiListSettings.nonce);
      }
    })
    getFilterCountsPromise.then(counts=>{
      $(".js-list-view-count").each(function() {
        const $el = $(this);
        let view_id = $el.data("value")
        if ( counts && counts[view_id] ){
          $el.text( counts[view_id] );
        }
      });
      $(".tab-count-span").each(function () {
        const $el = $(this)
        let tab = $el.data("tab")
        if ( counts && counts[tab] ){
          if ( wpApiListSettings.current_post_type === "groups" ){
            $el.text( ` ${counts[tab]}` )
          } else {
            $el.text( ` (${counts[tab]})` )
          }
        }
      })
    }).catch(err => {
      if ( !_.get( err, "statusText" ) === "abort" ){
        console.error(err)
      }
    })
  }
  get_filter_counts()
  showClosedCheckbox.on("click", function () {
    document.cookie = `show_closed=${$(this).prop('checked')}`
    get_filter_counts()
    getContactForCurrentView()
  })

})(window.jQuery, window.wpApiListSettings, window.Foundation);
