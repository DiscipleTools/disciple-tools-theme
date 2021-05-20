"use strict";
(function($, list_settings, Foundation) {
  let selected_filters = $("#selected-filters")
  let new_filter_labels = []
  let custom_filters = []
  let filter_to_save = "";
  let filter_to_delete = "";
  let filterToEdit = "";
  let filter_accordions = $('#list-filter-tabs')
  let currentFilters = $("#current-filters")
  let cookie = window.SHAREDFUNCTIONS.getCookie("last_view");
  let cached_filter
  let get_records_promise = null
  let loading_spinner = $("#list-loading-spinner")
  let old_filters = JSON.stringify(list_settings.filters)
  let table_header_row = $('.js-list thead .sortable th')
  let fields_to_show_in_table = window.SHAREDFUNCTIONS.get_json_cookie( 'fields_to_show_in_table', [] );
  let fields_to_search = window.SHAREDFUNCTIONS.get_json_cookie( 'fields_to_search', [] );
  let current_user_id = wpApiNotifications.current_user_id;
  let mobile_breakpoint = 1024
  let clearSearchButton = $('.search-input__clear-button')
  window.post_type_fields = list_settings.post_type_settings.fields

  let items = []
  try {
    cached_filter = JSON.parse(cookie)
  } catch (e) {
    cached_filter = {}
  }

  const query_param_custom_filter = create_custom_filter_from_query_params()

  let current_filter
  if (query_param_custom_filter && !window.lodash.isEmpty(query_param_custom_filter)) {
    current_filter = query_param_custom_filter
  } else if (cached_filter && !window.lodash.isEmpty(cached_filter)) {
    current_filter = cached_filter
  } else {
    current_filter =  { query:{} }
  }

  //set up main filters
  setup_filters()

  let check_first_filter = function (){
    $('#list-filter-tabs .accordion-item a')[0].click()
    $($('.js-list-view')[0]).prop('checked', true)
  }

  //set up custom cached filter
  if ( query_param_custom_filter && !window.lodash.isEmpty(query_param_custom_filter) && query_param_custom_filter.type === "custom_filter" ){
    query_param_custom_filter.query.offset = 0;
    add_custom_filter(query_param_custom_filter.name, "default", query_param_custom_filter.query, query_param_custom_filter.labels, false)
  } else if ( cached_filter && !window.lodash.isEmpty(cached_filter) && cached_filter.type === "custom_filter" ) {
    cached_filter.query.offset = 0;
    add_custom_filter(cached_filter.name, "default", cached_filter.query, cached_filter.labels, false)
  } else {
    //check select filter
    if ( current_filter.ID ){
      //open the filter tabs
      $(`#list-filter-tabs [data-id='${window.lodash.escape( current_filter.tab )}'] a`).click()
      let filter_element = $(`input[name=view][data-id="${window.lodash.escape( current_filter.ID )}"].js-list-view`)
      if ( filter_element.length ){
        filter_element.prop('checked', true);
      } else {
        check_first_filter()
      }
    } else {
      check_first_filter()
    }
  }

  //determine list columns
  if ( window.lodash.isEmpty(fields_to_show_in_table)){
    window.lodash.forOwn( list_settings.post_type_settings.fields, (field_settings, field_key)=> {
      if (window.lodash.get(field_settings, 'show_in_table')===true || window.lodash.get(field_settings, 'show_in_table') > 0) {
        fields_to_show_in_table.push(field_key)
      }
    })
    fields_to_show_in_table.sort((a,b)=>{
      let a_order = list_settings.post_type_settings.fields[a].show_in_table ? ( list_settings.post_type_settings.fields[a].show_in_table === true ? 50 : list_settings.post_type_settings.fields[a].show_in_table ) : 200
      let b_order = list_settings.post_type_settings.fields[b].show_in_table ? ( list_settings.post_type_settings.fields[b].show_in_table === true ? 50 : list_settings.post_type_settings.fields[b].show_in_table ) : 200
      return a_order > b_order ? 1 : -1
    })
  }

  // get records on load and when a filter is clicked
  get_records_for_current_filter()
  $(document).on('change', '.js-list-view', () => {
    get_records_for_current_filter()
  });

  //load record for the first filter when a tile is clicked
  $(document).on('click', '.accordion-title', function(){
    let selected_filter = $(".js-list-view:checked").data('id')
    let tab = $(this).data('id');
    if ( selected_filter ){
      $(`.accordion-item[data-id='${tab}'] .js-list-view`).first().prop('checked', true);
      get_records_for_current_filter()
    }
  })

  /**
   * Looks for all query params called 'filter' (allows for multiple filters to be applied)
   * from url like base_url?filter=foo&filter=bar
   *
   * filter values are exected to be created by encodeURI(JSON.stringify({ id, name, field }))
   * where the id, name and field are the relevant field and id to search for. (filters with
   * incorrect fields will be removed)
   *
   * If any part of the filter doesn't decode or JSON.parse properly the function returns
   * no filter.
   */
  function create_custom_filter_from_query_params() {
    const url = new URL(window.location)

    let filters = []
    try {
      filters = get_encoded_query_param_filters(url)
    } catch (error) {
      // the uri is corrupted
    }
    /* make sure filter fields are in the list of allowed fields */
    filters = filters.filter(({ field }) => Object.keys(window.list_settings.post_type_settings.fields).includes(field))
    if (filters.length == 0) return {}

    /* Creating object the same shape as cached_filter */
    let query_custom_filter = {
      ID: Date.now() / 1000,
      name: 'Custom Filter',
      type: 'custom_filter',
      labels: [],
      query: {},
    }

    const labels = [ ...filters ]
    const query = { fields: [], offset: 0, sort: 'name'}
    let labelsSortedByField = {}
    labels.forEach(({field, id}) => {
      if (!labelsSortedByField[field]) labelsSortedByField[field] = []
      labelsSortedByField[field].push(id)
    })
    query.fields = Object.entries(labelsSortedByField).map(([key, ids]) => ({[key]: ids}))

    query_custom_filter.labels = labels
    query_custom_filter.query = query

    return query_custom_filter
  }

  function get_encoded_query_param_filters(url) {
    const filters = url.searchParams.getAll('fieldQuery')
    return filters.map((filter) =>JSON.parse(decodeURI(filter)))
  }

  function get_records_for_current_filter(){
    let checked = $(".js-list-view:checked")
    let current_view = checked.val()
    let filter_id = checked.data("id") || current_view || ""
    let sort = current_filter.query.sort || null;
    if ( current_view === "custom_filter" ){
      let filterId = checked.data("id")
      current_filter = window.lodash.find(custom_filters, {ID:filterId})
      current_filter.type = current_view
    } else {
      current_filter = window.lodash.find(list_settings.filters.filters, {ID:filter_id}) || window.lodash.find(list_settings.filters.filters, {ID:filter_id.toString()}) || current_filter
      current_filter.type = 'default'
      current_filter.labels = current_filter.labels || [{ id:filter_id, name:current_filter.name}]
    }
    sort = sort || current_filter.query.sort;
    current_filter.query.sort = (typeof sort === "string") ? sort : "name"

    clear_search_query()

    get_records()
  }

  function clear_search_query() {
    // clear query if the current_filter is not a search query with the same text as the search-query
    const searchLabel = current_filter.labels.find((label) => label.id === 'search')
    if ( searchLabel && ( searchLabel.name === $("#search-query").val() ||       searchLabel.name === $("#search-query-mobile").val() ) ) {
      return
    }
    if ($("#search-query").val() !== ""){
      $("#search-query").val("")
    }
    if ($("#search-query-mobile").val() !== "") {
      $("#search-query-mobile").val("")
    }
  }

  function setup_filters(){
    if ( !list_settings.filters.tabs){
      return;
    }
    let selected_tab = $('.accordion-item.is-active').data('id');
    let selected_filter = $(".js-list-view:checked").data('id')
    let html = ``;
    list_settings.filters.tabs.forEach( tab =>{
      html += `
      <li class="accordion-item" data-accordion-item data-id="${window.lodash.escape(tab.key)}">
        <a href="#" class="accordion-title" data-id="${window.lodash.escape(tab.key)}">
          ${window.lodash.escape(tab.label)}
          <span class="tab-count-span" data-tab="${window.lodash.escape(tab.key)}">
              ${tab.count || tab.count >= 0 ? `(${window.lodash.escape(tab.count)})`: ``}
          </span>
        </a>
        <div class="accordion-content" data-tab-content>
          <div class="list-views">
            ${  list_settings.filters.filters.map( filter =>{
              if (filter.tab===tab.key && filter.tab !== 'custom') {
                let indent = filter.subfilter && Number.isInteger(filter.subfilter) ? 15 * filter.subfilter : 15;
                return `
                  <label class="list-view" style="${ filter.subfilter ? `margin-left:${indent}px` : ''}">
                    <input type="radio" name="view" value="${window.lodash.escape(filter.ID)}" data-id="${window.lodash.escape(filter.ID)}" class="js-list-view" autocomplete="off">
                    <span id="total_filter_label">${window.lodash.escape(filter.name)}</span>
                    <span class="list-view__count js-list-view-count" data-value="${window.lodash.escape(filter.ID)}">${window.lodash.escape(filter.count )}</span>
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

    let saved_filters_list = $(`#list-filter-tabs [data-id='custom'] .list-views`)
    saved_filters_list.empty()
    if ( list_settings.filters.filters.filter(t=>t.tab==='custom').length === 0 ) {
      saved_filters_list.html(`<span>${window.lodash.escape(list_settings.translations.empty_custom_filters)}</span>`)
    }
    list_settings.filters.filters.filter(t=>t.tab==='custom').forEach(filter=>{
      if ( filter && filter.visible === ''){
        return
      }
      let delete_filter = $(`<span style="float:right" data-filter="${window.lodash.escape( filter.ID )}">
        <img style="padding: 0 4px" src="${window.wpApiShare.template_dir}/dt-assets/images/trash.svg">
      </span>`)
      delete_filter.on("click", function () {
        $(`.delete-filter-name`).html(filter.name)
        $('#delete-filter-modal').foundation('open');
        filter_to_delete = filter.ID;
      })
      let edit_filter = $(`<span style="float:right" data-filter="${window.lodash.escape( filter.ID )}">
          <img style="padding: 0 4px" src="${window.wpApiShare.template_dir}/dt-assets/images/edit.svg">
      </span>`)
      edit_filter.on("click", function () {
        edit_saved_filter( filter )
        filterToEdit = filter.ID;
      })
      let filterName =  `<span class="filter-list-name" data-filter="${window.lodash.escape( filter.ID )}">${window.lodash.escape( filter.name )}</span>`
      const radio = $(`<input name='view' class='js-list-view' autocomplete='off' data-id="${window.lodash.escape( filter.ID )}" >`)
      .attr("type", "radio")
      .val("saved-filters")
      .on("change", function() {
      });
      saved_filters_list.append(
        $("<div>").append(
          $("<label>")
          .css("cursor", "pointer")
          .addClass("js-filter-checkbox-label")
          .data("filter-value", status)
          .append(radio)
          .append(filterName)
          .append(delete_filter)
          .append(edit_filter)
        )
      )
    })
    new Foundation.Accordion(filter_accordions, {
      slideSpeed: 100,
      allowAllClosed: true
    });
    if ( selected_tab ){
      $(`#list-filter-tabs [data-id='${window.lodash.escape( selected_tab )}'] a`).click()
    }
    if ( selected_filter ){
      $(`[data-id="${window.lodash.escape( selected_filter )}"].js-list-view`).prop('checked', true);
    }
  }

  let getFilterCountsPromise = null
  let get_filter_counts = ()=>{
    if ( getFilterCountsPromise && window.lodash.get( getFilterCountsPromise, "readyState") !== 4 ){
      getFilterCountsPromise.abort()
    }
    getFilterCountsPromise = $.ajax({
      url: `${window.wpApiShare.root}dt/v1/users/get_filters?post_type=${list_settings.post_type}&force_refresh=1`,
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
      }
    })
    getFilterCountsPromise.then(filters=>{
      if ( old_filters !== JSON.stringify(filters) ){
        list_settings.filters = filters
        setup_filters()
      }
    }).catch(err => {
      if ( window.lodash.get( err, "statusText" ) !== "abort" ){
        console.error(err)
      }
    })
  }
  get_filter_counts()


  function setup_current_filter_labels() {
    let html = ""
    let filter = current_filter
    if (filter && filter.labels){
      filter.labels.forEach(label=>{
        html+= `<span class="current-filter ${window.lodash.escape( label.field )}">${window.lodash.escape( label.name )}</span>`
      })
    } else {
      let query = filter.query
      window.lodash.forOwn( query, query_key=> {
        if (Array.isArray(query[query_key])) {

          query[query_key].forEach(q => {

            html += `<span class="current-filter ${window.lodash.escape( query_key )}">${window.lodash.escape( q )}</span>`
          })
        } else {
          html += `<span class="current-filter search">${window.lodash.escape( query[query_key] )}</span>`
        }
      })
    }

    if ( filter.query.sort ){
      let sortLabel = filter.query.sort
      if ( sortLabel.includes('last_modified') ){
        sortLabel = list_settings.translations.date_modified
      } else if (  sortLabel.includes('post_date') ) {
        sortLabel = list_settings.translations.creation_date
      } else  {

        // remove leading dash from sort filter key when reverse sorting
        const leadingDashSearch = new RegExp('^-')
        const querySortKey = (sortLabel.search(leadingDashSearch) > -1) ? sortLabel.replace(leadingDashSearch, '') : sortLabel
        sortLabel = window.lodash.get( list_settings, `post_type_settings.fields[${querySortKey}].name`, sortLabel)
      }
      html += `<span class="current-filter" data-id="sort">
          ${window.lodash.escape( list_settings.translations.sorting_by )}: ${window.lodash.escape( sortLabel )}
      </span>`
    }
    currentFilters.html(html)
  }


  let sort_field = window.lodash.get( current_filter, "query.sort", "name" )
  //reset sorting in table header
  table_header_row.removeClass("sorting_asc")
  table_header_row.removeClass("sorting_desc")
  let header_cell = $(`.js-list thead .sortable th[data-id="${window.lodash.escape( sort_field.replace("-", "") )}"]`)
  header_cell.addClass(`sorting_${ sort_field.startsWith('-') ? 'desc' : 'asc'}`)
  table_header_row.data("sort", '')
  header_cell.data("sort", 'asc')

  $('.js-sort-by').on("click", function () {
    table_header_row.removeClass("sorting_asc")
    table_header_row.removeClass("sorting_desc")
    let dir = $(this).data('order')
    let field = $(this).data('field')
    get_records( 0, (dir === "asc" ? "" : '-') + field )
  })

  //sort the table by clicking the header
  $('.js-list th').on("click", function () {
    //check is this is the bulk_edit_master checkbox
    if ( this.id == "bulk_edit_master") {
      return;
    }
    let id = $(this).data('id');
    let sort = $(this).data('sort')
    table_header_row.removeClass("sorting_asc")
    table_header_row.removeClass("sorting_desc")
    table_header_row.data("sort", '')
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
    get_records(0, id)
  })

  $('#choose_fields_to_show_in_table').on('click', function(){
    $('#list_column_picker').toggle()
  })
  $('#save_column_choices').on('click', function(){
    let new_selected = [];
    $('#list_column_picker input:checked').each((index, elem)=>{
      new_selected.push($(elem).val())
    })
    fields_to_show_in_table = window.lodash.intersection( fields_to_show_in_table, new_selected ) // remove unchecked
    fields_to_show_in_table = window.lodash.uniq(window.lodash.union( fields_to_show_in_table, new_selected ))
    window.SHAREDFUNCTIONS.save_json_cookie('fields_to_show_in_table', fields_to_show_in_table, list_settings.post_type )
    window.location.reload()
  })
  $('#reset_column_choices').on('click', function(){
    fields_to_show_in_table = []
    window.SHAREDFUNCTIONS.save_json_cookie('fields_to_show_in_table', fields_to_show_in_table, list_settings.post_type )
    window.location.reload()
  })


  $('#records-table').dragableColumns({
    drag: true,
    dragClass: 'drag',
    overClass: 'over',
    movedContainerSelector: '.dnd-moved',
    onDragEnd: ()=>{
      fields_to_show_in_table = []
      $('.table-headers th').each((i, e)=>{
        let field = $(e).data('id')
        if ( field ){
          fields_to_show_in_table.push(field)
        }
      })
      window.SHAREDFUNCTIONS.save_json_cookie('fields_to_show_in_table', fields_to_show_in_table, list_settings.post_type )
    }
  }).on('click', 'tbody tr', function(event){
    //open the record if the row is clicked. Give priority to normal browser behavior with links.
    if(!event.target.href) {
      window.location = $(this).data('link')
    }
  })

  let build_table = (records)=>{
    let table_rows = ``
    let mobile = $(window).width() < mobile_breakpoint
    records.forEach( ( record, index )=>{
      let row_fields_html = ''
      fields_to_show_in_table.forEach(field_key=>{
        let values_html = '';
        let values = [];
        if ( field_key === "name" ){
          if ( mobile ){ return }
          values_html = `<a href="${ window.lodash.escape( record.permalink ) }" title="${ window.lodash.escape( record.post_title ) }">${ window.lodash.escape( record.post_title ) }</a>`
        } else if ( list_settings.post_type_settings.fields[field_key] ) {
          let field_settings = list_settings.post_type_settings.fields[field_key]
          let field_value = window.lodash.get( record, field_key, false )

          if ( field_value ) {
            if (['text', 'textarea', 'number'].includes(field_settings.type)) {
              values = [window.lodash.escape(field_value)]
            } else if (field_settings.type === 'date') {
              values = [window.lodash.escape(window.SHAREDFUNCTIONS.formatDate(field_value.timestamp))]
            } else if (field_settings.type === 'user_select') {
              values = [window.lodash.escape(field_value.display)]
            } else if (field_settings.type === 'key_select') {
              values = [window.lodash.escape(field_value.label)]
            } else if (field_settings.type === 'multi_select') {
              values = field_value.map(v => {
                return `${window.lodash.escape(window.lodash.get(field_settings, `default[${v}].label`, v))}`;
              })
            } else if (field_settings.type === 'tags') {
              values = field_value.map(v => {
                return `${window.lodash.escape(window.lodash.get(field_settings, `default[${v}].label`, v))}`;
              })
            } else if ( field_settings.type === "location" || field_settings.type === "location_meta" ){
              values = field_value.map(v => {
                return `${window.lodash.escape( v.label )}`;
              })
            } else if ( field_settings.type === "communication_channel" ){
              values = field_value.map(v => {
                return `${window.lodash.escape( v.value )}`;
              })
            } else if ( field_settings.type === "connection" ){
              values = field_value.map(v => {
                return `${window.lodash.escape( v.post_title )}`;
              })
            } else if ( field_settings.type === "boolean" ){
              if (field_key === "favorite") {
                values = [`<svg class='icon-star selected' viewBox="0 0 32 32" data-id=${record.ID}><use xlink:href="${window.wpApiShare.template_dir}/dt-assets/images/star.svg#star"></use></svg>`]
              } else {
                values = ['&check;']
              }
            }
          } else if ( !field_value && field_settings.type === "boolean" && field_key === "favorite") {
            values = [`<svg class='icon-star' viewBox="0 0 32 32" data-id=${record.ID}><use xlink:href="${window.wpApiShare.template_dir}/dt-assets/images/star.svg#star"></use></svg>`]
          }
        } else {
          return;
        }
        values_html += values.map( (v, index)=>{
          return `<li>${v}</li>`
        }).join('')
        if ( $(window).width() < mobile_breakpoint ){
          row_fields_html += `
            <td>
              <div class="mobile-list-field-name">
                <ul>
                ${window.lodash.escape(window.lodash.get(list_settings, `post_type_settings.fields[${field_key}].name`, field_key))}
                </ul>
              </div>
              <div class="mobile-list-field-value">
                <ul style="line-height:20px" >
                  ${values.join(', ')}
                </ul>
              </div>

            </td>
          `
        } else {
          //this looks for the star SVG from the favorited fields and changes the value to a checkmark like other boolean fields to be used in the title element on desktop lists.
          if (values[0] === `<svg class='icon-star selected' viewBox="0 0 32 32" data-id=${record.ID}><use xlink:href="${window.wpApiShare.template_dir}/dt-assets/images/star.svg#star"></use></svg>` ) {
            values[0] = '&#9734;'
          }
          if (values[0] === `<svg class='icon-star' viewBox="0 0 32 32" data-id=${record.ID}><use xlink:href="${window.wpApiShare.template_dir}/dt-assets/images/star.svg#star"></use></svg>`) {
            values[0] = '&#9733;'
          }
          row_fields_html += `
            <td title="${values.join(', ')}">
              <ul>
                ${values_html}
              </ul>
            </td>
          `
        }
      })

      if ( mobile ){
        table_rows += `<tr data-link="${window.lodash.escape(record.permalink)}">
          <td class="bulk_edit_checkbox">
              <input class="bulk_edit_checkbox" type="checkbox" name="bulk_edit_id" value="${record.ID}">
          </td>
          <td>
              <div class="mobile-list-field-name">
                ${index+1}.
              </div>
              <div class="mobile-list-field-value">
                  <a href="${ window.lodash.escape( record.permalink ) }">${ window.lodash.escape( record.post_title ) }</a>
              </div>
          </td>
          ${ row_fields_html }
        `
      } else {
        table_rows += `<tr class="dnd-moved" data-link="${window.lodash.escape(record.permalink)}">
          <td class="bulk_edit_checkbox" ><input type="checkbox" name="bulk_edit_id" value="${record.ID}"></td>
          <td style="white-space: nowrap" >${index+1}.</td>
          ${ row_fields_html }
        `
      }
    })
    if ( records.length === 0 ){
      table_rows = `<tr><td colspan="10">${window.lodash.escape(list_settings.translations.empty_list)}</td></tr>`
    }

    let table_html = `
      ${table_rows}
    `
    $('#table-content').html(table_html)
    bulk_edit_checkbox_event();
    favorite_edit_event();
  }

  function get_records( offset = 0, sort = null ){
    loading_spinner.addClass("active");
    let query = current_filter.query
    if ( offset ){
      query["offset"] = offset
    }
    if ( sort ){
      query.sort = sort
      query.offset = 0
    }

    window.SHAREDFUNCTIONS.save_json_cookie(`last_view`, current_filter, list_settings.post_type )
    if ( get_records_promise && window.lodash.get(get_records_promise, "readyState") !== 4){
      get_records_promise.abort()
    }
    query.fields_to_return = fields_to_show_in_table
    get_records_promise = window.makeRequestOnPosts( 'GET', `${list_settings.post_type}`, JSON.parse(JSON.stringify(query)))
    get_records_promise.then(response=>{
      if (offset){
        items = window.lodash.unionBy(items, response.posts || [], "ID")
      } else  {
        items = response.posts || []
      }
      window.records_list = response // adds global access to current list for plugins

      // save
      if (response.hasOwnProperty('posts') && response.posts.length > 0) {
        let records_list_ids_and_type = [];

        $.each(response.posts, function(id, post_object ) {
          records_list_ids_and_type.push({ ID: post_object.ID });
        });

        window.SHAREDFUNCTIONS.save_json_cookie(`records_list`, records_list_ids_and_type, list_settings.post_type);

      }


      $('#bulk_edit_master_checkbox').prop("checked", false); //unchecks the bulk edit master checkbox when the list reloads.

      $('#load-more').toggle(items.length !== parseInt( response.total ))
      let result_text = list_settings.translations.txt_info.replace("_START_", items.length).replace("_TOTAL_", response.total)
      $('.filter-result-text').html(result_text)
      build_table(items)
      setup_current_filter_labels()
      loading_spinner.removeClass("active")
    }).catch(err => {
      loading_spinner.removeClass("active")
      if ( window.lodash.get( err, "statusText" ) !== "abort" ) {
        console.error(err)
      }
    })
  }

  $('#load-more').on('click', function () {
    get_records( items.length )
  })


  /**
   * Modal options
   */


  //add the new filter in the filters list
  function add_custom_filter(name, type, query, labels, load_records = true) {
    query = query || current_filter.query
    let ID = new Date().getTime() / 1000;
    current_filter = {ID, type, name: window.lodash.escape( name ), query:JSON.parse(JSON.stringify(query)), labels:labels}
    custom_filters.push(JSON.parse(JSON.stringify(current_filter)))

    let save_filter = $(`<a style="float:right" data-filter="${window.lodash.escape( ID.toString() )}">
        ${window.lodash.escape( list_settings.translations.save )}
    </a>`).on("click", function () {
      $("#filter-name").val(name)
      $('#save-filter-modal').foundation('open');
      filter_to_save = ID;
    })
    let filterRow = $(`<label class='list-view ${window.lodash.escape( ID.toString() )}'>`).append(`
      <input type="radio" name="view" value="custom_filter" data-id="${window.lodash.escape( ID.toString() )}" class="js-list-view" checked autocomplete="off">
        ${window.lodash.escape( name )}
    `).append(save_filter)
    $(".custom-filters").append(filterRow)
    if ( load_records ){
      get_records_for_current_filter()
    }
  }
  let get_custom_filter_search_query = ()=>{
    let search_query = []
    let fields_filtered = window.lodash.uniq(new_filter_labels.map(f=>f.field))
    fields_filtered.forEach(field=>{
      let type = window.lodash.get(list_settings, `post_type_settings.fields.${field}.type` )
      if ( type === "connection" || type === "user_select" ){
        const allConnections = $(`#${field} .all-connections`)
        if (type === "connection" && allConnections.prop('checked') === true) {
          search_query.push( { [field] : ['*'] } )
        } else {
          search_query.push( { [field] : window.lodash.map(window.lodash.get(Typeahead[`.js-typeahead-${field}`], "items"), "ID") })
        }
      } else if ( type === "multi_select" ){
        search_query.push( {[field] : window.lodash.map(window.lodash.get(Typeahead[`.js-typeahead-${field}`], "items"), "key") })
      } else if ( type === "tags" ){
        search_query.push( {[field] : window.lodash.map(window.lodash.get(Typeahead[`.js-typeahead-${field}`], "items"), "key") })
      } else if ( type === "location" || type === "location_meta" ){
        search_query.push({ 'location_grid' : window.lodash.map( window.lodash.get(Typeahead[`.js-typeahead-${field}`], "items"), 'ID') })
      } else if ( type === "date" ) {
        let date = {}
        let start = $(`.dt_date_picker[data-field="${field}"][data-delimit="start"]`).val()
        if ( start ){
          date.start = start
        }
        let end = $(`.dt_date_picker[data-field="${field}"][data-delimit="end"]`).val()
        if ( end ){
          date.end = end
        }
        search_query.push({[field]: date})
      } else {
        let options = []
        $(`#${field}-options input:checked`).each(function(){
          options.push( $(this).val() )
        })
        if ( options.length ){
          search_query.push({ [field]: options })
        }
      }
    })
    search_query = {
      fields: search_query
    }
    if ( list_settings.post_type === "contacts" ){
      if ( $("#combine_subassigned").is(":checked") ){
        let assigned_to = search_query.fields.filter(a=>a.assigned_to)
        let subassigned = search_query.fields.filter(a=>a.subassigned)
        search_query.fields = search_query.fields.filter(a=>{return !a.assigned_to && !a.subassigned})
        search_query.fields.push([assigned_to[0], subassigned[0]])
        search_query.combine = [ "subassigned" ] // to select checkbox in filter modal
      }
    }

    return search_query
  }
  $("#confirm-filter-records").on("click", function () {
    let search_query = get_custom_filter_search_query()
    let filterName = window.lodash.escape( $('#new-filter-name').val() )
    add_custom_filter( filterName || "Custom Filter", "custom-filter", search_query, new_filter_labels)
  })

  function allConnectionsClickHandler() {
    const tabsPanel = $(this).closest('.tabs-panel')
    const field = tabsPanel.length === 1 ? tabsPanel[0].id : ''
    const typeaheadQueryElement = tabsPanel.find('.typeahead__query')
    const typeaheadCancelButtons = tabsPanel.find('.typeahead__cancel-button')
    const typeahead = tabsPanel.find(`.js-typeahead-${field}`)

    if ($(this).prop('checked') === true) {
      typeahead.prop('disabled', true)
      typeaheadQueryElement.addClass('disabled')
      // remove the current filters and leave anything in the typeahead as it is
      removeAllFilterLabels(field)
      const fieldLabel = list_settings.post_type_settings.fields[field] ? list_settings.post_type_settings.fields[field].name : ''
      const filterName = `${window.lodash.escape( fieldLabel )}: ${window.lodash.escape( list_settings.translations.all )}`
      selected_filters.append(`<span class="current-filter ${window.lodash.escape( field )}" data-id="*">${filterName}</span>`)
      new_filter_labels.push({id: '*', name: filterName, field: field})
    } else {
      typeahead.prop('disabled', false)
      typeaheadQueryElement.removeClass('disabled')
      removeFilterLabels('*', field)
      // clear the typeahead by manually clicking each selected item.
      // This is done at this point as it triggers the typeahead to open which we don't want just after we have disabled it.
      typeaheadCancelButtons.each(function () {
        $(this).trigger('click', { botClick: true })
      })
    }
  }

  $('.all-connections').on("click", allConnectionsClickHandler)


  let load_multi_select_typeaheads = async function load_multi_select_typeaheads() {
    for (let input of $(".multi_select .typeahead__query input")) {
      let field = $(input).data('field')
      let typeahead_name = `.js-typeahead-${field}`

      if (window.Typeahead[typeahead_name]) {
        return
      }

      let source_data =  { data: [] }
      let field_options = window.lodash.get(list_settings, `post_type_settings.fields.${field}.default`, {})
      if ( Object.keys(field_options).length > 0 ){
        window.lodash.forOwn(field_options, (val, key)=>{
          if ( !val.deleted ){
            source_data.data.push({
              key: key,
              name:key,
              value: val.label || key
            })
          }
        })
      } else {
        source_data = {
          [field]: {
            display: ["value"],
            ajax: {
              url: window.wpApiShare.root + `dt-posts/v2/${list_settings.post_type}/multi-select-values`,
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
                    let label = window.lodash.get(field_options, tag + ".label", tag)
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
          return `<span>${window.lodash.escape(item.value)}</span>`
        },
        source: source_data,
        display: "value",
        templateValue: "{{value}}",
        dynamic: true,
        multiselect: {
          matchOn: ["key"],
          data: [],
          callback: {
            onCancel: function (node, item) {
              $(`.current-filter[data-id="${item.key}"].${field}`).remove()
              window.lodash.pullAllBy(new_filter_labels, [{id:item.key}], "id")
            }
          }
        },
        callback: {
          onClick: function(node, a, item){
            let name = window.lodash.get(list_settings, `post_type_settings.fields.${field}.name`, field)
            selected_filters.append(`<span class="current-filter ${window.lodash.escape( field )}" data-id="${window.lodash.escape( item.key )}">${window.lodash.escape( name )}:${window.lodash.escape( item.value )}</span>`)
            new_filter_labels.push({id:item.key, name:`${name}: ${item.value}`, field})
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

  let load_post_type_typeaheads = ()=>{
    $(".typeahead__query [data-type='connection']").each((key, el)=>{
      let field_key = $(el).data('field')
      let post_type = window.lodash.get( list_settings, `post_type_settings.fields.${field_key}.post_type`, field_key)
      if (!window.Typeahead[`.js-typeahead-${field_key}`]) {
        $.typeahead({
          input: `.js-typeahead-${field_key}`,
          minLength: 0,
          accent: true,
          searchOnFocus: true,
          maxItem: 20,
          template: function (query, item) {
            return `<span dir="auto">${window.lodash.escape(item.name)} (#${window.lodash.escape( item.ID )})</span>`
          },
          source: TYPEAHEADS.typeaheadPostsSource(post_type),
          display: "name",
          templateValue: "{{name}}",
          dynamic: true,
          multiselect: {
            matchOn: ["ID"],
            data: [],
            callback: {
              onCancel: function (node, item, event) {
                removeFilterLabels(item.ID, field_key)
              }
            }
          },
          callback: {
            onResult: function (node, query, result, resultCount) {
              let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
              $(`#${field_key}-result-container`).html(text);
            },
            onHideLayout: function () {
              $(`#${field_key}-result-container`).html("");
            },
            onClick: function (node, a, item) {
              new_filter_labels.push({id: item.ID, name: item.name, field: field_key})
              selected_filters.append(`<span class="current-filter ${field_key}" data-id="${window.lodash.escape( item.ID )}">${window.lodash.escape( item.name )}</span>`)
            }
          }
        });
      }
    })
  }

  const removeFilterLabels = (id, field_key) => {
    $(`.current-filter[data-id="${id}"].${field_key}`).remove()
    window.lodash.pullAllBy(new_filter_labels, [{id: id}], "id")
  }

  const removeAllFilterLabels = (field_key) => {
    // get all id's for this field_key
    let ids = []
    document.querySelectorAll(`.current-filter.${field_key}`).forEach((element) => {
      ids.push(element.dataset.id)
    })
    ids.forEach((id) => removeFilterLabels(id, field_key))
  }

  let load_user_select_typeaheads = ()=>{
    $(".typeahead__query [data-type='user_select']").each((key, el)=>{
      let field_key = $(el).data('field')
      if (!window.Typeahead[`.js-typeahead-${field_key}`]) {
        $.typeahead({
          input: `.js-typeahead-${field_key}`,
          minLength: 0,
          accent: true,
          searchOnFocus: true,
          maxItem: 20,
          template: function (query, item) {
            return `<span dir="auto">${window.lodash.escape(item.name)} (#${window.lodash.escape( item.ID )})</span>`
          },
          source: TYPEAHEADS.typeaheadUserSource(),
          display: "name",
          templateValue: "{{name}}",
          dynamic: true,
          multiselect: {
            matchOn: ["ID"],
            data: [],
            callback: {
              onCancel: function (node, item) {
                $(`.current-filter[data-id="${item.ID}"].${field_key}`).remove()
                window.lodash.pullAllBy(new_filter_labels, [{id: item.ID}], "id")
              }
            }
          },
          callback: {
            onResult: function (node, query, result, resultCount) {
              let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
              $(`#${field_key}-result-container`).html(text);
            },
            onHideLayout: function () {
              $(`#${field_key}-result-container`).html("");
            },
            onClick: function (node, a, item) {
              new_filter_labels.push({id: item.ID, name: item.name, field: field_key})
              selected_filters.append(`<span class="current-filter ${field_key}" data-id="${window.lodash.escape( item.ID )}">${window.lodash.escape( item.name )}</span>`)
            }
          }
        });
      }
    })
  }

  /**
   * Location
   */
   $('#mapbox-clear-autocomplete').click("input", function(){
       delete window.location_data;
    });

  let loadLocationTypeahead = ()=> {
    let key = 'location_grid'
    if ( $('.js-typeahead-location_grid_meta').length){
      key = 'location_grid_meta';
    }
    if ( !window.Typeahead[`.js-typeahead-${key}`]) {
      $.typeahead({
        input: `.js-typeahead-${window.lodash.escape(key)}`,
        minLength: 0,
        accent: true,
        searchOnFocus: true,
        maxItem: 20,
        dropdownFilter: [{
          key: 'group',
          value: 'used',
          template: window.lodash.escape(window.wpApiShare.translations.used_locations),
          all: window.lodash.escape(window.wpApiShare.translations.all_locations)
        }],
        source: {
          used: {
            display: "name",
            ajax: {
              url: window.wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
              data: {
                s: "{{query}}",
                filter: function () {
                  return window.lodash.get(window.Typeahead[`.js-typeahead-${key}`].filters.dropdown, 'value', 'all')
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
              window.lodash.pullAllBy(new_filter_labels, [{id: item.ID}], "id")
            }
          }
        },
        callback: {
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#location_grid-result-container').html(text);
          },
          onReady(){
            this.filters.dropdown = {key: "group", value: "used", template: window.lodash.escape(window.wpApiShare.translations.used_locations)}
            this.container
            .removeClass("filter")
            .find("." + this.options.selector.filterButton)
            .html(window.lodash.escape(window.wpApiShare.translations.used_locations));
          },
          onHideLayout: function () {
            $('#location_grid-result-container').html("");
          },
          onClick: function (node, a, item) {
            let name = window.lodash.get(list_settings, `post_type_settings.fields.location_grid.name`, 'location_grid')
            new_filter_labels.push({id: item.ID, name: `${name}: ${item.name}`, field: key, type: 'location_grid'})
            selected_filters.append(`<span class="current-filter location_grid" data-id="${window.lodash.escape( item.ID )}">${window.lodash.escape( name )}:${window.lodash.escape( item.name )}</span>`)
          }
        }
      });
    }
  }

  /*
   * Setup filter box
   */
  let typeaheads_loaded = null
  $('#filter-modal').on("open.zf.reveal", function () {
    new_filter_labels=[]
    loadLocationTypeahead()
    load_post_type_typeaheads()
    load_user_select_typeaheads()
    typeaheads_loaded = load_multi_select_typeaheads().catch(err => { console.error(err) })
    $('#new-filter-name').val('')
    $("#filter-modal input.dt_date_picker").each(function () {
      $(this).val('')
    })
    $("#filter-modal input:checked").each(function () {
      $(this).prop('checked', false)
    })
    $("#filter-modal input:disabled").each(function () {
      $(this).prop('disabled', false)
    })
    $('#filter-modal .typeahead__query.disabled').each(function () {
      $(this).removeClass('disabled')
    })
    selected_filters.empty();
    $(".typeahead__query input").each(function () {
      let typeahead = Typeahead['.'+$(this).attr("class").split(/\s+/)[0]]
      if ( typeahead && typeahead.items ){
        for (let i = 0; i < typeahead.items.length; i ){
          typeahead.cancelMultiselectItem(0)
        }
        typeahead.node.trigger('propertychange.typeahead')
      }
    });
    $('#confirm-filter-records').show()
    $('#save-filter-edits').hide()
  })

  $("#filter-modal input.dt_date_picker").on('blur', function (e) {
    // delay the blur so that if the user has clicked we get the correct date from the input
    setTimeout(() => {
      if (!e.target.value) {
        const clearButton = $(this).prev('.clear-date-picker')
        clearButton.click()
        return
      }
      $(this).datepicker('setDate', e.target.value)
      $('.ui-datepicker-current-day').click()
    }, 100);
  })

  let edit_saved_filter = function( filter ){
    $('#filter-modal').foundation('open');
    typeaheads_loaded.then(()=>{
      let connectionTypeKeys = list_settings.post_type_settings.connection_types
      connectionTypeKeys.push("location_grid")
      filter.labels.forEach(label=>{
        selected_filters.append(`<span class="current-filter ${window.lodash.escape( label.field )}" data-id="${window.lodash.escape( label.id )}">${window.lodash.escape( label.name )}</span>`)
        let type = window.lodash.get(list_settings, `post_type_settings.fields.${label.field}.type`)
        if ( type === "key_select" || type === "boolean" ){
          $(`#filter-modal #${label.field}-options input[value="${label.id}"]`).prop('checked', true)
        } else if ( type === "date" ){
          $(`#filter-modal #${label.field}-options #${label.id}`).datepicker('setDate', label.date)
        } else if ( connectionTypeKeys.includes( label.field ) ){
          if (label.id === '*') {
            const fieldAllConnectionsElement = document.querySelector(`#filter-modal #${label.field} .all-connections`)
            const boundAllConnectionsClickHandler = allConnectionsClickHandler.bind(fieldAllConnectionsElement)
            $(fieldAllConnectionsElement).prop('checked', true)
            boundAllConnectionsClickHandler()
          } else {
            Typeahead[`.js-typeahead-${label.field}`].addMultiselectItemLayout({ID:label.id, name:label.name})
          }
        } else if ( type === "multi_select" ){
          Typeahead[`.js-typeahead-${label.field}`].addMultiselectItemLayout({key:label.id, value:label.name})
        } else if ( type === "tags" ){
          Typeahead[`.js-typeahead-${label.field}`].addMultiselectItemLayout({key:label.id, value:label.id})
        } else if ( type === "user_select" ){
          Typeahead[`.js-typeahead-${label.field}`].addMultiselectItemLayout({name:label.name, ID:label.id})
        }
      })
      // moved this below the forEach as the global new_filter_labels was messing with the loop.
      new_filter_labels = filter.labels
      ;(filter.query.combine || []).forEach(c=>{
        $(`#combine_${c}`).prop('checked', true)
      })
      $('#new-filter-name').val(filter.name)
      $('#confirm-filter-records').hide()
      $('#save-filter-edits').data("filter-id", filter.ID).show()
    })
  }

  $('#save-filter-edits').on('click', function () {
    let search_query = get_custom_filter_search_query()
    let filter_id = $('#save-filter-edits').data("filter-id")
    let filter = window.lodash.find(list_settings.filters.filters, {ID:filter_id})
    filter.name = $('#new-filter-name').val()
    $(`.filter-list-name[data-filter="${filter_id}"]`).text(filter.name)
    filter.query = search_query
    filter.labels = new_filter_labels
    API.save_filters( list_settings.post_type, filter )
    get_records_for_current_filter()
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
    let option_id = $(this).val()
    if ($(this).is(":checked")){
      let field_options = window.lodash.get( list_settings, `post_type_settings.fields.${field_key}.default` )
      let option_name = field_options[option_id] ? field_options[option_id]["label"] : '';
      let name = window.lodash.get(list_settings, `post_type_settings.fields.${field_key}.name`, field_key)
      new_filter_labels.push({id:$(this).val(), name:`${name}: ${option_name}`, field:field_key})
      selected_filters.append(`<span class="current-filter ${window.lodash.escape( field_key )}" data-id="${window.lodash.escape( option_id )}">${window.lodash.escape( name )}:${window.lodash.escape( option_name )}</span>`)
    } else {
      $(`.current-filter[data-id="${$(this).val()}"].${field_key}`).remove()
      window.lodash.pullAllBy(new_filter_labels, [{id:option_id}], "id")
    }
  })
  //watch bool checkboxes
  $('#filter-modal .boolean_options input').on("change", function() {
    let field_key = $(this).data('field');
    let option_id = $(this).val()
    let label = $(this).data('label');
    if ($(this).is(":checked")){
      let field = window.lodash.get( list_settings, `post_type_settings.fields.${field_key}` )
      new_filter_labels.push({id:$(this).val(), name:`${field.name}: ${label}`, field:field_key})
      selected_filters.append(`<span class="current-filter ${window.lodash.escape( field_key )}" data-id="${window.lodash.escape( option_id )}">${window.lodash.escape( field.name )}:${window.lodash.escape( label )}</span>`)
    } else {
      $(`.current-filter[data-id="${$(this).val()}"].${field_key}`).remove()
      window.lodash.pullAllBy(new_filter_labels, [{id:option_id}], "id")
    }
  })

  $('#filter-modal .dt_date_picker').datepicker({
    constrainInput: false,
    dateFormat: 'yy-mm-dd',
    onSelect: function (date) {
      let id = $(this).data('field')
      let delimiter = $(this).data('delimit')
      let delimiter_label = list_settings.translations[`range_${delimiter}`]
      let field_name = window.lodash.get( list_settings, `post_type_settings.fields.${id}.name` , id)
      //remove existing filters
      window.lodash.pullAllBy(new_filter_labels, [{id:`${id}_${delimiter}`}], "id")
      $(`.current-filter[data-id="${id}_${delimiter}"]`).remove()
      //add new filters
      new_filter_labels.push({id:`${id}_${delimiter}`, name:`${field_name} ${delimiter_label}: ${date}`, field:id, date:date})
      selected_filters.append(`
        <span class="current-filter ${id}_${delimiter}"
              data-id="${id}_${delimiter}">
                ${field_name} ${delimiter_label}:${date}
        </span>
      `)
    },
    changeMonth: true,
    changeYear: true,
    yearRange: "-20:+10",
  })

  $('#filter-modal .clear-date-picker').on('click', function () {
    let id = $(this).data('for')
    $(`#filter-modal #${id}`).datepicker('setDate', null)
    window.lodash.pullAllBy(new_filter_labels, [{id:`${id}`}], "id")
    $(`.current-filter[data-id="${id}"]`).remove()
  })

  //save the filter in the user meta
  $(`#confirm-filter-save`).on('click', function () {
    let filterName = $('#filter-name').val()
    let filter = window.lodash.find(custom_filters, {ID:filter_to_save})
    filter.name = window.lodash.escape( filterName )
    filter.tab = 'custom'
    if (filter.query){
      list_settings.filters.filters.push(filter)
      API.save_filters(list_settings.post_type,filter).then(()=>{
        $(`.custom-filters [class*="list-view ${filter_to_save}`).remove()
        setup_filters()
        let active_tab = $('.accordion-item.is-active ').data('id');
        if ( active_tab !== 'custom' ){
          $(`#list-filter-tabs [data-id='custom'] a`).click()
        }
        $(`input[name="view"][value="saved-filters"][data-id='${filter_to_save}']`).prop('checked', true);
        get_records_for_current_filter()
        $('#filter-name').val("")
      }).catch(err => { console.error(err) })
    }
  })

  //delete a filter
  $(`#confirm-filter-delete`).on('click', function () {

    let filter = window.lodash.find(list_settings.filters.filters, {ID:filter_to_delete})
    if ( filter && ( filter.visible === true || filter.visible === '1' ) ){
      filter.visible = false;
      API.save_filters(list_settings.post_type,filter).then(()=>{
        window.lodash.pullAllBy(list_settings.filters.filters, [{ID:filter_to_delete}], "ID")
        setup_filters()
        $(`#list-filter-tabs [data-id='custom'] a`).click()
      }).catch(err => { console.error(err) })
    } else {
      API.delete_filter(list_settings.post_type, filter_to_delete).then(()=>{
        window.lodash.pullAllBy(list_settings.filters.filters, [{ID:filter_to_delete}], "ID")
        setup_filters()
        check_first_filter()
        get_records_for_current_filter()
      }).catch(err => { console.error(err) })
    }
  })

  $('#advanced_search').on('click', function() {
    $('#advanced_search_picker').toggle();
  });

  $('#advanced_search_mobile').on('click', function() {
    $('#advanced_search_picker_mobile').toggle();
  });

  $('#advanced_search_reset').on('click', function(){
    let fields_to_search = []
    window.SHAREDFUNCTIONS.save_json_cookie('fields_to_search', fields_to_search, list_settings.post_type )

    //clear all checkboxes
    $('#advanced_search_picker ul li input:checked').each(function( index ) {
        $( this ).prop('checked', false);
    });
    $('#search').click();

  });

  $('#advanced_search_reset_mobile').on('click', function(){
    let fields_to_search = []
    window.SHAREDFUNCTIONS.save_json_cookie('fields_to_search', fields_to_search, list_settings.post_type )

    //clear all checkboxes
    $('#advanced_search_picker_mobile ul li input:checked').each(function( index ) {
        $( this ).prop('checked', false);
    });
    $('#search-mobile').click();

  });

  $('#save_advanced_search_choices').on("click", function() {
    let fields_to_search = [];
    $('#advanced_search_picker ul li input:checked').each(function( index ) {
      fields_to_search.push($( this ).val());
   });
    window.SHAREDFUNCTIONS.save_json_cookie('fields_to_search', fields_to_search, list_settings.post_type );
    if ($("#search-query").val() !== "") {
      $('#search').click();
    } else {
      $('#advanced_search_picker').hide();
    }
  })

  $('#save_advanced_search_choices_mobile').on("click", function() {
    let fields_to_search = [];
    $('#advanced_search_picker_mobile ul li input:checked').each(function( index ) {
      fields_to_search.push($( this ).val());
   });
    window.SHAREDFUNCTIONS.save_json_cookie('fields_to_search', fields_to_search, list_settings.post_type );
    if ($("#search-query-mobile").val() !== "") {
      $('#search-mobile').click();
    } else {
      $('#advanced_search_picker_mobile').hide();
    }
  })
  $("#search").on("click", function () {
    let searchText = $("#search-query").val()
    let fieldsToSearch = [];
    $('#advanced_search_picker ul li input:checked').each(function( index ) {
      fieldsToSearch.push($( this ).val());
   });
   window.SHAREDFUNCTIONS.save_json_cookie('fields_to_search', fieldsToSearch, list_settings.post_type );

   if (fieldsToSearch.length > 0 ) {
    $('.advancedSearch-count').text(fieldsToSearch.length).css('display', 'inline-block')
   } else {
    $('.advancedSearch-count').text('fields_to_search.length').hide();
   }

    let query = {text:searchText}

    if (fieldsToSearch.length !== 0) {
      query.fields_to_search = fieldsToSearch;
    }

    let labels = [{ id:"search", name:searchText, field: "search"}]
    add_custom_filter(searchText, "search", query, labels);

    $('#advanced_search_picker').hide();
  })

  $("#search-mobile").on("click", function () {
    let searchText = window.lodash.escape( $("#search-query-mobile").val() )
    let fieldsToSearch = [];
    $('#advanced_search_picker_mobile ul li input:checked').each(function( index ) {
      fieldsToSearch.push($( this ).val());
    });
    window.SHAREDFUNCTIONS.save_json_cookie('fields_to_search', fieldsToSearch, list_settings.post_type );

    if (fieldsToSearch.length > 0 ) {
      $('.advancedSearch-count').text(fieldsToSearch.length).css('display', 'inline-block')
     } else {
      $('.advancedSearch-count').text('fields_to_search.length').hide();
     }

    let query = {text:searchText}

    if (fieldsToSearch.length !== 0) {
      query.fields_to_search = fieldsToSearch;
    }

    let labels = [{ id:"search", name:searchText, field: "search"}]
    add_custom_filter(searchText, "search", query, labels);

    $('#advanced_search_picker_mobile').hide();

  });

  $('.search-input--desktop').on('keyup', function (e) {
    clearSearchButton.css({'display': this.value.length ? 'flex' : 'none'})
    if ( e.keyCode === 13 ){
      $("#search").trigger("click")
    }
  })

  $('.search-input--mobile').on('keyup', function (e) {
    clearSearchButton.css({'display': this.value.length ? 'flex' : 'none'})
    if ( e.keyCode === 13 ){
      $("#search-mobile").trigger("click")
    }
  })

  clearSearchButton.on('click', function () {
    $('.search-input').val('')
    clearSearchButton.css({'display': 'none'})
  })

  //toggle show search input on mobile
  $("#open-search").on("click", function () {
    $(".hideable-search").toggle()
  })


  /***
   * Favorite from List
   */
   function favorite_edit_event() {
      $("svg.icon-star").on('click', function(e) {
        e.stopImmediatePropagation();
        let post_id = this.dataset.id
        let favoritedValue;
        if ( $(this).hasClass('selected') ) {
          favoritedValue = false;
        } else {
          favoritedValue = true;
        }
        API.update_post(list_settings.post_type, post_id, {'favorite': favoritedValue}).then((new_post)=>{
          $(this).toggleClass('selected');
        })
      })
   }

  /***
   * Bulk Edit
   */

  $('#bulk_edit_controls').on('click', function(){
    $('#bulk_edit_picker').toggle();
    $('#records-table').toggleClass('bulk_edit_on');
  })

  $('#bulk_edit_seeMore').on('click', function(){
    $('#bulk_more').toggle();
    $('#bulk_edit_seeMore').children().toggle()
  })

  function bulk_edit_checkbox_event() {
    $("tbody tr td.bulk_edit_checkbox").on('click', function(e) {
      e.stopImmediatePropagation();
      bulk_edit_count();
    });
  }

  $('#bulk_edit_master').on('click', function(e){
    e.stopImmediatePropagation();
    let checked = $(this).children('input').is(':checked');
        $('.bulk_edit_checkbox input').each(function() {
        $(this).prop('checked', checked);
        bulk_edit_count();
    })
  })
  /**
   * Bulk_Assigned_to
   */
  let bulk_edit_submit_button = $('#bulk_edit_submit');

  bulk_edit_submit_button.on('click', function(e) {
    bulk_edit_submit();
  });

  function bulk_edit_submit() {
    $('#bulk_edit_submit-spinner').addClass('active');
    let allInputs = $('#bulk_edit_picker input, #bulk_edit_picker select, #bulk_edit_picker .button').not('#bulk_share');
    let multiSelectInputs = $('#bulk_edit_picker .dt_multi_select')
    let shareInput = $('#bulk_share');
    let updatePayload = {};
    let sharePayload;

    allInputs.each(function () {
        let inputData = $(this).data();
        $.each(inputData, function (key, value) {
          if (key.includes('bulk_key_') && value) {
            let field_key = key.replace('bulk_key_', '');
            if(field_key) {
              updatePayload[field_key] = value;
            }
          }
        })
    })
    if (window.location_data) {
      updatePayload['location_grid_meta'] = window.location_data.location_grid_meta;
    }

    let multiSelectUpdatePayload = {};
    multiSelectInputs.each(function () {
      let inputData = $(this).data();
      $.each(inputData, function (key, value) {
        if (key.includes('bulk_key_') && value) {
          let field_key = key.replace('bulk_key_', '');
          if (!multiSelectUpdatePayload[field_key]) {
            multiSelectUpdatePayload[field_key] = {'values': []};
          }
          multiSelectUpdatePayload[field_key].values.push(value.values);
        }
      })

    })
    const multiSelectKeys = Object.keys(multiSelectUpdatePayload);

    multiSelectKeys.forEach((key, index) => {
      console.log(`${key}: ${multiSelectUpdatePayload[key]}`);
      updatePayload[key] = multiSelectUpdatePayload[key];
    });

    shareInput.each(function () {
      sharePayload = $(this).data('bulk_key_share');
    })

    let queue =  [];
    let count = 0;
    $('.bulk_edit_checkbox input').each(function () {
      if (this.checked && this.id !== 'bulk_edit_master_checkbox') {
        let postId = parseInt($(this).val());
        queue.push( postId );
      }
    });
    process(queue, 10, doEach, doDone, updatePayload, sharePayload);
  }

  function bulk_edit_count() {
    let bulk_edit_total_checked = $('.bulk_edit_checkbox:not(#bulk_edit_master) input:checked').length;
    let bulk_edit_submit_button_text = $('#bulk_edit_submit_text')

    if (bulk_edit_total_checked == 0) {
      bulk_edit_submit_button_text.text(`Update ${list_settings.post_type}`)
    } else {
      bulk_edit_submit_button_text.text(`Update ${bulk_edit_total_checked} ${list_settings.post_type}`)
    }
  }

  let bulk_edit_picker_checkboxes = $('#bulk_edit_picker .update-needed');
  bulk_edit_picker_checkboxes.on('click', function(e) {
    if ($(this).is(':checked')) {
      $(this).data('bulk_key_requires_update', true);
    }
  })

  let bulk_edit_picker_select_field = $('#bulk_edit_picker select');
  bulk_edit_picker_select_field.on('change', function(e) {
      let field_key = this.id.replace('bulk_', '');
      $(this).data(`bulk_key_${field_key}`, this.value);
  })

  let bulk_edit_picker_button_groups= $('#bulk_edit_picker .select-button');
  bulk_edit_picker_button_groups.on('click', function(e) {
      let field_key = $(this).data('field-key').replace('bulk_', '');
      let optionKey = $(this).attr('id')

      let fieldValue = {};

      fieldValue.values = {value:optionKey};


      $(this).addClass('selected-select-button');
      $(this).data(`bulk_key_${field_key}`, fieldValue);
  })

  //Bulk Update Queue
  function process( q, num, fn, done, update, share ) {
    // remove a batch of items from the queue
    let items = q.splice(0, num),
        count = items.length;

    // no more items?
    if ( !count ) {
        // exec done callback if specified
        done && done();
        // quit
        return;
    }

    // loop over each item
    for ( let i = 0; i < count; i++ ) {
        // call callback, passing item and
        // a "done" callback
        fn(items[i], function() {
            // when done, decrement counter and
            // if counter is 0, process next batch
            --count || process(q, num, fn, done, update, share);
        }, update, share);

    }
  }

  // a per-item action
  function doEach( item, done, update, share ) {
    let promises = [];

    if (Object.keys(update).length) {
      promises.push( API.update_post(list_settings.post_type, item, update).catch(err => { console.error(err);}));
    }

    if (share) {
      share.forEach(function(value) {
        promises.push( API.add_shared(list_settings.post_type, item, value).catch(err => { console.error(err) }));
      })
    }

    Promise.all(promises).then( function() {
        done();
    });
  }

  function doDone() {
    $('#bulk_edit_submit-spinner').removeClass('active');
    window.location.reload();
  }



  let bulk_assigned_to_input = $(`.js-typeahead-bulk_assigned_to`)
  if( bulk_assigned_to_input.length ) {
    $.typeahead({
      input: '.js-typeahead-bulk_assigned_to',
      minLength: 0,
      maxItem: 0,
      accent: true,
      searchOnFocus: true,
      source: TYPEAHEADS.typeaheadUserSource(),
      templateValue: "{{name}}",
      template: function (query, item) {
        return `<div class="assigned-to-row" dir="auto">
        <span>
            <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
            ${window.lodash.escape(item.name)}
        </span>
        ${item.status_color ? `<span class="status-square" style="background-color: ${window.lodash.escape(item.status_color)};">&nbsp;</span>`:''}
        ${item.update_needed && item.update_needed > 0 ? `<span>
          <img style="height: 12px;" src="${window.lodash.escape(window.wpApiShare.template_dir)}/dt-assets/images/broken.svg"/>
          <span style="font-size: 14px">${window.lodash.escape(item.update_needed)}</span>
        </span>`:''}
      </div>`
      },
      dynamic: true,
      hint: true,
      emptyTemplate: window.lodash.escape(window.wpApiShare.translations.no_records_found),
      callback: {
        onClick: function (node, a, item) {
          node.data('bulk_key_assigned_to', `user-${item.ID}`);
        },
        onResult: function (node, query, result, resultCount) {
          let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $('#bulk_assigned_to-result-container').html(text);
        },
        onHideLayout: function () {
          $('.bulk_assigned_to-result-container').html("");
        },
        onReady: function () {
        }
      },
    });
  }


  /**
   * Bulk share
  */
  $.typeahead({
    input: '#bulk_share',
    minLength: 0,
    maxItem: 0,
    accent: true,
    searchOnFocus: true,
    source: TYPEAHEADS.typeaheadUserSource(),
    templateValue: "{{name}}",
    dynamic: true,
    multiselect: {
      matchOn: ["ID"],
      callback: {
        onCancel: function (node, item) {
          $(node).removeData( `bulk_key_bulk_share` );
          $('#share-result-container').html("");

        }
      },
    },
    callback: {
      onClick: function (node, a, item, event) {
        let shareUserArray;
        if (node.data('bulk_key_share')) {
          shareUserArray = node.data('bulk_key_share');
        } else {
          shareUserArray = [];
        }
        shareUserArray.push(item.ID);
        node.data(`bulk_key_share`, shareUserArray);
      },
      onResult: function (node, query, result, resultCount) {
        if (query) {
          let text = window.TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $('#share-result-container').html(text);
        }
      },
      onHideLayout: function () {
        $('#share-result-container').html("");
      }
    }
  });

  /**
 * Bulk Typeahead
 */

  let field_settings = window.list_settings.post_type_settings.fields;

  $('#bulk_edit_picker .dt_typeahead').each((key, el)=>{
    let field_id = $(el).attr('id').replace('_connection', '').replace('bulk_', '');
    let element_id =  $(el).attr('id').replace('_connection', '');
    if (element_id !== "bulk_share") {
      let listing_post_type = window.lodash.get(window.list_settings.post_type_settings.fields[field_id], "post_type", 'contacts')
      $.typeahead({
        input: `.js-typeahead-${element_id}`,
        minLength: 0,
        accent: true,
        maxItem: 30,
        searchOnFocus: true,
        template: window.TYPEAHEADS.contactListRowTemplate,
        source: window.TYPEAHEADS.typeaheadPostsSource(listing_post_type, {field_key:field_id}),
        display: "name",
        templateValue: "{{name}}",
        dynamic: true,
        multiselect: {
          matchOn: ["ID"],
          data: '',
          callback: {
            onCancel: function (node, item) {
              $(node).removeData( `bulk_key_${field_id}` );
            }
          },
          href: window.wpApiShare.site_url + `/${listing_post_type}/{{ID}}`
        },
        callback: {
          onClick: function(node, a, item, event){
            let multiUserArray;
            if ( node.data(`bulk_key_${field_id}`) ) {
              multiUserArray = node.data(`bulk_key_${field_id}`).values;
            } else {
              multiUserArray = [];
            }
            multiUserArray.push({"value":item.ID});

            node.data(`bulk_key_${field_id}`, {values: multiUserArray});
            this.addMultiselectItemLayout(item)
            event.preventDefault()
            this.hideLayout();
            this.resetInput();
            //masonGrid.masonry('layout')
          },
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $(`#${element_id}-result-container`).html(text);
          },
          onHideLayout: function (event, query) {
            if ( !query ){
              $(`#${element_id}-result-container`).empty()
            }
            //masonGrid.masonry('layout')
          },
          onShowLayout (){
            //masonGrid.masonry('layout')
          }
        }
      })
    }
  })

  $('#bulk_edit_picker .dt_location_grid').each(()=> {
    let field_id = 'location_grid';
    let typeaheadTotals = {};
    $.typeahead({
      input: '.js-typeahead-bulk_location_grid',
      minLength: 0,
      accent: true,
      searchOnFocus: true,
      maxItem: 20,
      dropdownFilter: [{
        key: 'group',
        value: 'focus',
        template: window.lodash.escape(window.wpApiShare.translations.regions_of_focus),
        all: window.lodash.escape(window.wpApiShare.translations.all_locations),
      }],
      source: {
        focus: {
          display: "name",
          ajax: {
            url: window.wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
            data: {
              s: "{{query}}",
              filter: function () {
                // return window.lodash.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
              }
            },
            beforeSend: function (xhr) {
              xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
            },
            callback: {
              done: function (data) {
                if (typeof typeaheadTotals!=="undefined") {
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
        data: '',
        callback: {
          onCancel: function (node, item) {
            $(node).removeData( `bulk_key_${field_id}` );
          }
        }
      },
      callback: {
        onClick: function (node, a, item, event) {
          // $(`#${element_id}-spinner`).addClass('active');
          node.data(`bulk_key_${field_id}`, {values:[{"value":item.ID}]});
        },
        onReady() {
          this.filters.dropdown = {key: "group", value: "focus", template: window.lodash.escape(window.wpApiShare.translations.regions_of_focus)}
          this.container
          .removeClass("filter")
          .find("." + this.options.selector.filterButton)
          .html(window.lodash.escape(window.wpApiShare.translations.regions_of_focus));
        },
        onResult: function (node, query, result, resultCount) {
          resultCount = typeaheadTotals.location_grid
          let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
          $('#location_grid-result-container').html(text);
        },
        onHideLayout: function () {
          $('#location_grid-result-container').html("");
        }
      }
    });
  })

  $('button.follow').on("click", function () {
    let following = !($(this).data('value') === "following")
    $(this).data("value", following ? "following" : "" )
    $(this).html( following ? "Unfollow" : "Follow")
    $(this).toggleClass( "hollow" )
    let follow = { values:[{value:current_user_id, delete:!following}] }

    let unfollow = {values:[{value:current_user_id, delete:following}]}

    $(this).data('bulk_key_follow', follow);
    $(this).data('bulk_key_unfollow', unfollow);
  })

  $('#bulk_edit_picker input.text-input').change(function(){
    const val = $(this).val()
    let field_key = this.id.replace('bulk_', '')
    $(this).data(`bulk_key_${field_key}`, val);
  });

  $('#bulk_edit_picker .dt_textarea').change(function(){
    const val = $(this).val()
    let field_key = this.id.replace('bulk_', '')
    $(this).data(`bulk_key_${field_key}`, val);
  })

  $('#bulk_edit_picker .dt_date_picker').datepicker({
    constrainInput: false,
    dateFormat: 'yy-mm-dd',
    onClose: function (date) {
      if (document.querySelector('#group-details-edit-modal') && document.querySelector('#group-details-edit-modal').contains( this)) {
        // do nothing
      } else {
        date = window.SHAREDFUNCTIONS.convertArabicToEnglishNumbers(date);

        if (!$(this).val()) {
          date = " ";//null;
        }

        let formattedDate = moment.utc(date).unix();

        let field_key = this.id.replace('bulk_', '')
        $(this).data(`bulk_key_${field_key}`, formattedDate);
      }
    },
    changeMonth: true,
    changeYear: true,
    yearRange: "1900:2050",
  }).each(function() {
    if (this.value && moment.unix(this.value).isValid()) {
      this.value = window.SHAREDFUNCTIONS.formatDate(this.value);
    }
  })


  let mcleardate = $("#bulk_edit_picker .clear-date-button");
  mcleardate.click(function() {
    let input_id = this.dataset.inputid;
    let date = null;
    // $(`#${input_id}-spinner`).addClass('active')
    let field_key = this.id.replace('bulk_', '')
    $(this).removeData(`bulk_key_${field_key}`);
    $(`#${input_id}`).val("");
  });

  $('#bulk_edit_picker select.select-field').change(e => {
    const val = $(e.currentTarget).val()

    if (val === "paused") {
      $('#reason-paused-options').parent().toggle()
    }

    let field_key = e.currentTarget.id.replace('bulk_', '')
    $(e.currentTarget).data(`bulk_key_${field_key}`, val);

  })

  $('#bulk_edit_picker input.number-input').on("blur", function(){
    const id = $(this).attr('id')
    const val = $(this).val()

    let field_key = this.id.replace('bulk_', '')
    $(this).data(`bulk_key_${field_key}`, val);
  })

  $('#bulk_edit_picker .dt_contenteditable').on('blur', function(){
    const id = $(this).attr('id')
    let val = $(this).html()

    let field_key = this.id.replace('bulk_', '')
    $(this).data(`bulk_key_${field_key}`, val);
  })




})(window.jQuery, window.list_settings, window.Foundation);
