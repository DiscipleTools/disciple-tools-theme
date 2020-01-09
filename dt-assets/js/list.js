(function($, wpApiListSettings, Foundation) {
  "use strict";

  let cachedFilter = window.SHAREDFUNCTIONS.get_json_cookie("last_view")

  $.urlParam = function (name) {
    let results = new RegExp('[\?&]' + name + '=([^&#]*)')
      .exec(window.location.search);

    return (results !== null) ? results[1] || 0 : false;
  }

  let tabQueryParam = $.urlParam( 'list-tab' )

  let showClosedCookie = window.SHAREDFUNCTIONS.getCookie("show_closed")
  let showClosedCheckbox = $('#show_closed')
  let currentFilter = {}
  let items = []
  let customFilters = []
  let filterToSave = ""
  let filterToDelete = ""
  let filterToEdit = ""
  let currentFilters = $("#current-filters")
  let newFilterLabels = []
  let loading_spinner = $("#list-loading-spinner")
  let count_spinner = $("#count-loading-spinner")
  let filter_accordions = $('#list-filter-tabs')
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
      data.sort = 'name';
      if ( wpApiListSettings.current_post_type === "contacts" ){
        data.sort = 'overall_status'
      } else if ( wpApiListSettings.current_post_type === "groups" ){
        data.sort = "group_type";
      }
    }
    currentFilter.query = data
    document.cookie = `last_view=${JSON.stringify(currentFilter)}`

    let currentView = $(".js-list-view:checked").val()
    let showClosed = showClosedCheckbox.prop("checked")
    if ( !showClosed && ( currentView === 'custom_filter' || currentView === 'saved-filters' ) && !data.text ){
      if ( wpApiListSettings.current_post_type === "contacts" ){
        if ( !data.overall_status ){
          data.overall_status = [];
        }
        if ( !data.overall_status.includes("-closed") ){
          data.overall_status.push( "-closed" )
        }
      } else if ( wpApiListSettings.current_post_type === "groups") {
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
      $('#load-more').toggle(items.length !== parseInt( data.total ))
      let result_text = wpApiListSettings.translations.txt_info.replace("_START_", items.length).replace("_TOTAL_", data.total)
      $('.filter-result-text').html(result_text)
      displayRows();
      setupCurrentFilterLabels()
      loading_spinner.removeClass("active")
    }).catch(err => {
      if ( _.get( err, "statusText" ) !== "abort" ) {
        console.error(err)
      }
    })
  }



  function setupFilters(){
    if ( !wpApiListSettings.filters.tabs){
      return;
    }
    let selectedTab = $('.accordion-item.is-active').data('id');
    let selectedFilter = $(".js-list-view:checked").data('id')
    let html = ``;
    wpApiListSettings.filters.tabs.forEach( tab =>{
      html += `
      <li class="accordion-item" data-accordion-item data-id="${_.escape(tab.key)}">
        <a href="#" class="accordion-title">
          ${_.escape(tab.label)}
          <span class="tab-count-span" data-tab="${_.escape(tab.key)}">
              ${tab.count || tab.count >= 0 ? `(${_.escape(tab.count)})`: ``} 
          </span>
        </a>
        <div class="accordion-content" data-tab-content>
          <div class="list-views">
            ${  wpApiListSettings.filters.filters.map( filter =>{
              if (filter.tab===tab.key && filter.tab !== 'custom') {
                return `
                <label class="list-view" style="${ filter.subfilter ? 'margin-left:15px' : ''}">
                  <input type="radio" name="view" value="${_.escape(filter.ID)}" data-id="${_.escape(filter.ID)}" class="js-list-view" autocomplete="off">
                  <span id="total_filter_label">${_.escape(filter.name)}</span>
                  <span class="list-view__count js-list-view-count" data-value="${_.escape(filter.ID)}">${_.escape(filter.count )}</span>
                </label>
                `  
              }
            }).join('')}
          </div>
        </div>
      </li>
      `
    } )
    filter_accordions.html(html)

    let savedFiltersList = $(`#list-filter-tabs [data-id='custom'] .list-views`)
    savedFiltersList.empty()
    if ( wpApiListSettings.filters.filters.filter(t=>t.tab==='custom').length === 0 ) {
      savedFiltersList.html(`<span>${_.escape(wpApiListSettings.translations.empty_custom_filters)}</span>`)
    }
    wpApiListSettings.filters.filters.filter(t=>t.tab==='custom').forEach(filter=>{
      if ( filter && filter.visible === ''){
        return
      }
      let deleteFilter = $(`<span style="float:right" data-filter="${_.escape( filter.ID )}">
        <img style="padding: 0 4px" src="${window.wpApiShare.template_dir}/dt-assets/images/trash.svg">
      </span>`)
      deleteFilter.on("click", function () {
          $(`.delete-filter-name`).html(filter.name)
          $('#delete-filter-modal').foundation('open');
          filterToDelete = filter.ID;
        })
      let editFilter = $(`<span style="float:right" data-filter="${_.escape( filter.ID )}">
          <img style="padding: 0 4px" src="${window.wpApiShare.template_dir}/dt-assets/images/edit.svg">
      </span>`)
      editFilter.on("click", function () {
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
            .data("filter-value", status)
            .append(radio)
            .append(filterName)
            .append(deleteFilter)
            .append(editFilter)
        )
      )
    })
    new Foundation.Accordion(filter_accordions, {
      slideSpeed: 100,
      allowAllClosed: true
    });
    if ( selectedTab ){
      $(`#list-filter-tabs [data-id='${_.escape( selectedTab )}'] a`).click()
    }
    if ( selectedFilter ){
      $(`[data-id="${_.escape( selectedFilter )}"].js-list-view`).prop('checked', true);
    }
  }

  //set the "show closed" checkbox
  if ( showClosedCookie === "true" ){
    showClosedCheckbox.prop('checked', true)
  }

  //look at the cookie to see what was the last selected view
  if ( tabQueryParam ){
    cachedFilter = {
      type: "default",
      tab: "my",
      ID: tabQueryParam
    }
  }
  let selectedFilter = ""
  if ( cachedFilter && !_.isEmpty(cachedFilter)){
    if ( cachedFilter.type==="default" ){
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
  setupFilters()

  $(`#list-filter-tabs [data-id='${_.escape( selectedFilterTab )}'] a`).click()
  if ( selectedFilter && selectedFilter !== "no_filter" ){
    $(`.is-active input[name=view][data-id="${_.escape( selectedFilter )}"].js-list-view`).prop('checked', true);
  } else {
    $('#list-filter-tabs .accordion-item a')[0].click()
    $($('.js-list-view')[0]).prop('checked', true)
  }


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
      <td class="hide-for-small-only">
        <span class="status status--<%- overall_status %>"><%- status %>
        <% if (update_needed){ %>
            <img style="" src="${_.escape( window.wpApiShare.template_dir )}/dt-assets/images/broken.svg"/>
        <% } %>
        </span>
      </td>
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
      <td class="hide-for-small-only"><%- last_modified %></td>
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
      <td class="hide-for-small-only">
        <span class="group-status group-status--<%- group_status %>"><%- status %>
        <% if (update_needed){ %>
            <img style="" src="${_.escape( window.wpApiShare.template_dir )}/dt-assets/images/broken.svg"/>
        <% } %>
        </span>
      </td>
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

    const last_modified = new Date(contact.last_modified*1000).toString().slice(0, 15);

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
      last_modified,
      update_needed : contact.requires_update
    });
    return $.parseHTML(template(context));
  }

  function buildGroupRow(group) {
    const template = templates[wpApiListSettings.current_post_type];
    const leader_links = _.map(group.leaders, function(leader) {
      return '<a href="' + _.escape(leader.permalink) + '">' + _.escape(leader.post_title) + "</a>";
    }).join(", ");
    const gcfs = wpApiListSettings.custom_fields_settings;
    const status = _.get( gcfs, `group_status.default[${group.group_status || "active"}]["label"]`, group.group_status )
    const type = _.get( gcfs, `group_type.default[${group.group_type || "group"}]["label"]`, group.group_type )
    const context = _.assign({}, group, wpApiListSettings, {
      leader_links,
      status,
      type,
      update_needed : group.requires_update
    });
    return $.parseHTML(template(context));
  }

  $(document).on('change', '.js-list-view', () => {
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
    if ( currentView === "custom_filter"){
      let filterId = checked.data("id")
      filter = _.find(customFilters, {ID:filterId})
      filter.type = currentView
      query = filter.query
    } else if ( currentView ) {
      filter = _.find(wpApiListSettings.filters.filters, {ID:filterId}) || _.find(wpApiListSettings.filters.filters, {ID:filterId.toString()}) || filter
      if ( filter ){
        filter.type = 'default'
        filter.labels =  [{ id:filterId, name:filter.name}]
        query = filter.query
      }
    }

    let closedSwitch = $(".show-closed-switch");
    if (currentView === "custom_filter" || currentView === "saved-filters" ){
      closedSwitch.show()
    } else {
      closedSwitch.hide()
    }

    filter.query = query
    let sortField = _.get(currentFilter, "query.sort", "overall_status").replace("-", "");
    filter.query.sort = _.get(currentFilter, "query.sort", "overall_status");
    if ( _.get( cachedFilter, "query.sort") ){
      filter.query.sort = cachedFilter.query.sort;
      sortField = _.get(cachedFilter, "query.sort", "overall_status").replace("-", "");
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
    filter.tab = 'custom'
    if (filter.query){
      wpApiListSettings.filters.filters.push(filter)
      API.save_filters(wpApiListSettings.current_post_type,filter).then(()=>{
        $(`.custom-filters [class*="list-view ${filterToSave}`).remove()
        setupFilters()
        let active_tab = $('.accordion-item.is-active ').data('id');
        if ( active_tab !== 'custom' ){
          $(`#list-filter-tabs [data-id='custom'] a`).click()
        }
        $(`input[name="view"][value="saved-filters"][data-id='${filterToSave}']`).prop('checked', true);
        getContactForCurrentView()
        $('#filter-name').val("")
      }).catch(err => { console.error(err) })
    }
  })

  //delete a filter
  $(`#confirm-filter-delete`).on('click', function () {
    let filter = _.find(wpApiListSettings.filters.filters, {ID:filterToDelete})
    if ( filter && ( filter.visible === true || filter.visible === '1' ) ){
      filter.visible = false;
      API.save_filters(wpApiListSettings.current_post_type,filter).then(()=>{
        _.pullAllBy(wpApiListSettings.filters.filters, [{ID:filterToDelete}], "ID")
        setupFilters()
        $(`#list-filter-tabs [data-id='custom'] a`).click()
      }).catch(err => { console.error(err) })
    } else {
      API.delete_filter(wpApiListSettings.current_post_type, filterToDelete).then(()=>{
        _.pullAllBy(wpApiListSettings.filters.filters, [{ID:filterToDelete}], "ID")
        setupFilters()
        $(`#list-filter-tabs [data-id='custom'] a`).click()
      }).catch(err => { console.error(err) })
    }
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
   * location_grid
   */
  let loadLocationTypeahead = ()=> {
    if (!window.Typeahead['.js-typeahead-location_grid']) {
      $.typeahead({
        input: '.js-typeahead-location_grid',
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        maxItem: 20,
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
              url: window.wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
              data: {
                s: "{{query}}",
                filter: function () {
                  return _.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
                }
              },
              beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
              },
              callback: {
                done: function (data) {
                  if (typeof typeaheadTotals !== "undefined") {
                    typeaheadTotals.field = data.total
                  }
                  return data.location_grid
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
              $(`.current-filter[data-id="${item.ID}"].location_grid`).remove()
              _.pullAllBy(newFilterLabels, [{id: item.ID}], "id")
            }
          }
        },
        callback: {
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#location_grid-result-container').html(text);
          },
          onReady(){
            this.filters.dropdown = {key: "group", value: "used", template: "Used Locations"}
            this.container
              .removeClass("filter")
              .find("." + this.options.selector.filterButton)
              .html("Used Locations");
          },
          onHideLayout: function () {
            $('#location_grid-result-container').html("");
          },
          onClick: function (node, a, item) {
            let name = _.get(wpApiListSettings, `custom_fields_settings.location_grid.name`, 'location_grid')
            newFilterLabels.push({id: item.ID, name: `${name}:${item.name}`, field: "location_grid"})
            selectedFilters.append(`<span class="current-filter location_grid" data-id="${_.escape( item.ID )}">${_.escape( name )}:${_.escape( item.name )}</span>`)
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
          onClick: function (node, a, item) {
            newFilterLabels.push({id: item.ID, name: item.name, field: "leaders"})
            selectedFilters.append(`<span class="current-filter leaders" data-id="${_.escape( item.ID )}">${_.escape( item.name )}</span>`)
          }
        }
      });
    }
  }

  /**
   * Subassigned
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
          onClick: function (node, a, item) {
            let name = _.get(wpApiListSettings, `custom_fields_settings.subassigned.name`, 'subassigned')
            newFilterLabels.push({id: item.ID, name: `${name}:${item.name}`, field: "subassigned"})
            selectedFilters.append(`<span class="current-filter subassigned" data-id="${_.escape( item.ID )}">${_.escape( name )}:${_.escape( item.name )}</span>`)
          }
        }
      });
    }
  }
    /**
   * Coached By
   */
  let loadCoachedByTypeahead = ()=> {
    if (!window.Typeahead['.js-typeahead-coached_by']) {
      $.typeahead({
        ...TYPEAHEADS.defaultContactTypeahead(),
        input: '.js-typeahead-coached_by',
        multiselect: {
          matchOn: ["ID"],
          data: [],
          callback: {
            onCancel: function (node, item) {
              $(`.current-filter[data-id="${_.escape( item.ID )}"].coached_by`).remove()
              _.pullAllBy(newFilterLabels, [{id: item.ID}], "id")
            }
          }
        },
        callback: {
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#coached_by-result-container').html(text);
          },
          onHideLayout: function () {
            $('#coached_by-result-container').html("");
          },
          onClick: function (node, a, item) {
            let name = _.get(wpApiListSettings, `custom_fields_settings.coached_by.name`, 'coached_by')
            newFilterLabels.push({id: item.ID, name: `${name}:${item.name}`, field: "coached_by"})
            selectedFilters.append(`<span class="current-filter coached_by" data-id="${_.escape( item.ID )}">${_.escape( name )}:${_.escape( item.name )}</span>`)
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
          onClick: function(node, a, item) {
            let name = _.get(wpApiListSettings, `custom_fields_settings.assigned_to.name`, 'assigned_to')
            selectedFilters.append(`<span class="current-filter assigned_to" data-id="${_.escape( item.ID )}">${_.escape( name )}:${_.escape( item.name )}</span>`)
            newFilterLabels.push({id:item.ID, name:`${name}:${item.name}`, field:"assigned_to"})

          }
        }
      });
    }
  }

  let loadMultiSelectTypeaheads = async function loadMultiSelectTypeaheads() {
    for (let input of $(".multi_select .typeahead__query input")) {
      let field = $(input).data('field')
      let typeahead_name = `.js-typeahead-${field}`

      if (window.Typeahead[typeahead_name]) {
        return
      }

      let sourceData =  { data: [] }
      let fieldOptions = _.get(wpApiListSettings, `custom_fields_settings.${field}.default`, {})
      if ( Object.keys(fieldOptions).length > 0 ){
        _.forOwn(fieldOptions, (val, key)=>{
          if ( !val.deleted ){
            sourceData.data.push({
              key: key,
              name:key,
              value: val.label || key
            })
          }
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
                xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
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
          onClick: function(node, a, item){
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
      loadCoachedByTypeahead()
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
      connectionTypeKeys.push("location_grid")
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
    let filter = _.find(wpApiListSettings.filters.filters, {ID:filterId})
    filter.name = $('#new-filter-name').val()
    $(`.filter-list-name[data-filter="${filterId}"]`).text(filter.name)
    filter.query = searchQuery
    filter.label = newFilterLabels
    API.save_filters( wpApiListSettings.current_post_type, filter )
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

  let getFilterCountsPromise = null
  let get_filter_counts = ()=>{
    if ( getFilterCountsPromise && _.get( getFilterCountsPromise, "readyState") !== 4 ){
      getFilterCountsPromise.abort()
    }
    getFilterCountsPromise = $.ajax({
      url: `${wpApiListSettings.root}dt/v1/users/get_filters?post_type=${wpApiListSettings.current_post_type}&force_refresh=1`,
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiListSettings.nonce);
      }
    })
    getFilterCountsPromise.then(filters=>{
      wpApiListSettings.filters = filters
      setupFilters()
    }).catch(err => {
      if ( _.get( err, "statusText" ) !== "abort" ){
        console.error(err)
      }
    })
  }
  get_filter_counts()

  //collapse the filters on small view.
  $(function() {
    $(window).resize(function() {
      if (Foundation.MediaQuery.is('small only') || Foundation.MediaQuery.is('medium only')) {
        setTimeout(()=>{
          $("#list-filters .bordered-box").toggleClass("collapsed");
        },100)
      }
    }).trigger("resize");
  });


})(window.jQuery, window.wpApiListSettings, window.Foundation);
