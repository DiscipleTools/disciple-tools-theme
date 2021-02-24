jQuery(document).ready(function($) {
  if( window.wpApiShare.url_path.includes('user-management/users') ) {
    write_users_list()
  } else if ( window.wpApiShare.url_path.includes('user-management/user/')){
    write_users_list()
    open_user_modal( window.wpApiShare.url_path.replace( 'user-management/user','').replace('/','') )
  }
  if( 'user-management/add-user' === window.wpApiShare.url_path ) {
    write_add_user()
  }

  /* List Table */
  function write_users_list(){
    let multipliers_table = $('#multipliers_table').DataTable({
      "paging":   false,
      "order": [[ 1, "asc" ]],
      "aoColumns": [
        { "orderSequence": [ "asc", "desc" ] },
        { "orderSequence": [ "asc", "desc" ] },
        { "orderSequence": [ "desc", "asc" ] },
        { "orderSequence": [ "desc", "asc" ] },
        { "orderSequence": [ "desc", "asc" ] },
        { "orderSequence": [ "desc", "asc" ] },
        { "orderSequence": [ "desc", "asc" ] },
        { "orderSequence": [ "desc", "asc" ] },
        { "orderSequence": [ "asc", "desc" ] },
      ],
      columnDefs: [ {
        sortable: false,
        "class": "index",
        targets: 0
      } ],
      responsive: true
    });

    multipliers_table.columns( '.select-filter' ).every( function () {
      var that = this;
      // Create the select list and search operation
      var select = $('<select />')
      .appendTo(
        this.header()
      )
      .on( 'change', function () {
        that
        .search( '^'+$(this).val() , true, false )
        .draw();
      } );

      // Get the search data for the first column and add to the select list
      this
      .cache( 'search' )
      .sort()
      .unique()
      .each( function ( d ) {
        select.append( $('<option value="'+d+'">'+d+'</option>') );
      } );
    } );
    multipliers_table.on( 'order.dt search.dt', function () {
      multipliers_table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
        cell.innerHTML = i+1 + '.';
      } );
    } ).draw();

    $('#page-title').show()

    $('#refresh_cached_data').on('click', function () {
      $('#loading-page').addClass('active')
      makeRequest( "get", `get_users?refresh=1`, null , 'user-management/v1/').then(()=>{
        location.reload()
      })
    })

    $('.user_row').on("click", function (a) {
      if ( a.target._DT_CellIndex.column !== 0 ){
        user_id = $(this).data("user")
        open_user_modal( user_id )
      }
    })




    $.urlParam = function(name){
      var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
      if ( results == null ) {
        return 0;
      }
      return results[1] || 0;
    }
    if ( $.urlParam('dt_user_id') ) {
      open_user_modal(decodeURIComponent($.urlParam('user_id') ) )
    }
    if ( window.selected_user_id ) {
      open_user_modal(window.selected_user_id )
    }

  }


  function open_user_modal( user_id ) {

    let update_user = ( user_id, key, value )=>{
      let data =  {
        [key]: value
      }
      return makeRequest( "POST", `user?user=${user_id}`, data , 'user-management/v1/' )
    }

    $('#user_name').on( "click", function(e) {
      window.user_name = $(this).text()
      $(this).parent().prepend(`
          <div class="input-group" id="user-name-input-wrapper">
              <input type="text" class="input-group-field" style="max-width: 50%;" id="user-name-input" value="${window.lodash.escape(window.user_name)}" />
              <div class="input-group-button">
                  <input type="button" class="button hollow" id="reset-user-name" value="Reset">
                  <input type="button" class="button" id="update-user-name" value="Save">
              </div>
          </div>`)
      $(this).hide()
      $('#reset-user-name').on("click", function(){
        $('#user_name').show()
        $('#user-name-input-wrapper').hide()
      })
      $('#update-user-name').on('click', function(){
        let new_name = window.lodash.escape($('#user-name-input').val())
        if ( window.user_name !== new_name ) {
          update_user(user_id, 'update_nickname', new_name )
            .done(function(data) {
              if ( data ) {
                window.user_name = new_name
                $('#user_name').html(new_name).show()
                $('#user-name-input-wrapper').hide()
              } else {
                $('#user_name').show().append(' <span class="error"><i class="fi-alert"></i></span>')
                $('#user-name-input-wrapper').hide()
              }
            })
        }
      })
    })


    /**
     * Status
     */
    $('#status-select').on('change', function () {
      let value = $(this).val()
      update_user( window.current_user_lookup, 'user_status', value)
    })
    $('#workload-select').on('change', function () {
      let value = $(this).val()
      update_user( window.current_user_lookup, 'workload_status', value)
    })

    /**
     * Set availability dates
     */
    let unavailable_dates_picker = $('#date_range')
    unavailable_dates_picker.daterangepicker({
      "singleDatePicker": false,
      autoUpdateInput: false,
      "locale": {
        "format": "YYYY/MM/DD",
        "separator": " - ",
        "daysOfWeek": window.wpApiShare.translations.days_of_the_week,
        "monthNames": window.wpApiShare.translations.month_labels,
      },
      "firstDay": 1,
      "opens": "center",
      "drops": "down"
    }).on('apply.daterangepicker', function (ev, picker) {
      $(this).val(picker.startDate.format('YYYY/MM/DD') + ' - ' + picker.endDate.format('YYYY/MM/DD'));
      let start_date = picker.startDate.format('YYYY/MM/DD')
      let end_date = picker.endDate.format('YYYY/MM/DD')
      $('#add_unavailable_dates_spinner').addClass('active')
      update_user( window.current_user_lookup, 'add_unavailability', {start_date, end_date}).then((resp)=>{
        $('#add_unavailable_dates_spinner').removeClass('active')
        unavailable_dates_picker.val('');
        display_dates_unavailable(resp.dates_unavailable)
      })
    })


    $('#save_roles').on("click", function () {
      $(this).toggleClass('loading', true)
      let roles = [];
      $('#user_roles_list input:checked').each(function () {
        roles.push($(this).val())
      })
      update_user( window.current_user_lookup, 'save_roles', roles).then((user_data)=>{
        setup_user_roles( user_data )
        $(this).toggleClass('loading', false)
      }).catch(()=>{
        $(this).toggleClass('loading', false)
      })

    })
    $('#save_allowed_sources').on("click", function () {
      $(this).toggleClass('loading', true)
      let sources = [];
      $('#allowed_sources_options input:checked').each(function () {
        sources.push($(this).val())
      })
      update_user( window.current_user_lookup, 'allowed_sources', sources).then((user_data)=>{
        setup_user_roles( user_data )
        $(this).toggleClass('loading', false)
      }).catch(()=>{
        $(this).toggleClass('loading', false)
      })
    })

    let date_unavailable_table = $('#unavailable-list')
    date_unavailable_table.empty()
    let display_dates_unavailable = (list = [] )=>{
      date_unavailable_table.empty()
      let rows = ``
      list.forEach(range=>{
        rows += `<tr>
        <td>${window.lodash.escape(range.start_date)}</td>
        <td>${window.lodash.escape(range.end_date)}</td>
        <td><button class="button remove_dates_unavailable" data-id="${window.lodash.escape(range.id)}">${ window.lodash.escape( dt_user_management_localized.translations.remove ) }</button></td>
      </tr>`
      })
      date_unavailable_table.html(rows)
    }
    $( document).on( 'click', '.remove_dates_unavailable', function () {
      let id = $(this).data('id');
      update_user( window.current_user_lookup, 'remove_unavailability', id).then((resp)=>{
        display_dates_unavailable(resp)
      })
    })

    /**
     * Locations
     */
    if ( typeof dtMapbox === "undefined" ) {
      let typeaheadTotals = {}
      if (!window.Typeahead['.js-typeahead-location_grid'] ){
        $.typeahead({
          input: '.js-typeahead-location_grid',
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
                url: wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
                data: {
                  s: "{{query}}",
                  filter: function () {
                    return window.lodash.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
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
            data: function () {
              return [];
            }, callback: {
              onCancel: function (node, item) {
                update_user( window.current_user_lookup, 'remove_location', item.ID)
              }
            }
          },
          callback: {
            onClick: function(node, a, item, event){
              update_user( window.current_user_lookup, 'add_location', item.ID)
            },
            onReady(){
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
      }
    }




    window.current_user_lookup = user_id

    $('#user-id-reveal').html(window.current_user_lookup)

    $('#user_modal').foundation('open');

    $('.users-spinner').addClass("active")

    // load spinners
    let spinner = ' <span class="loading-spinner users-spinner active"></span> '
    $("#user_name").html(spinner)
    $('#update_needed_count').html(spinner)
    $('#needs_accepted_count').html(spinner)
    $('#active_contacts').html(spinner)
    $('#unread_notifications').html(spinner)
    $('#assigned_this_month').html(spinner)
    $('#assigned_last_month').html(spinner)
    $('#assigned_this_year').html(spinner)
    $('#assigned_all_time').html(spinner)
    $('#unaccepted_contacts').html(spinner)
    $('#contact_accepts').html(spinner)
    $('#avg_contact_accept').html(spinner)
    $('#unattempted_contacts').html(spinner)
    $('#contact_attempts').html(spinner)
    $('#avg_contact_attempt').html(spinner)
    $('#update_needed_list').html(spinner)
    $('#status_chart_div').html(spinner)
    $('#activity').html(spinner)
    $('#day_activity_chart').html(spinner)
    $('#mapbox-wrapper').html(spinner)
    $('#location-grid-meta-results').html(spinner)

    $('#status-select').val('')
    $('#workload-select').val('')


    /* details */
    makeRequest( "get", `user?user=${user_id}&section=details`, null , 'user-management/v1/')
      .done(details=>{
        if ( window.current_user_lookup === user_id ) {
          $("#user_name").html(window.lodash.escape(details.display_name))

          $('#status-select').val(window.lodash.escape(details.user_status))
          if ( details.user_status !== "0" ){
          }
          $('#workload-select').val(window.lodash.escape(details.workload_status))

          //stats
          $('#update_needed_count').html(window.lodash.escape(details.update_needed["total"]))
          $('#needs_accepted_count').html(window.lodash.escape(details.needs_accepted["total"]))
          $('#active_contacts').html(window.lodash.escape(details.active_contacts))
          $('#unread_notifications').html(window.lodash.escape(details.unread_notifications))
          $('#assigned_this_month').text(window.lodash.escape(details.assigned_counts.this_month))
          $('#assigned_last_month').text(window.lodash.escape(details.assigned_counts.last_month))
          $('#assigned_this_year').text(window.lodash.escape(details.assigned_counts.this_year))
          $('#assigned_all_time').text(window.lodash.escape(details.assigned_counts.all_time))

          status_pie_chart( details.contact_statuses )
          setup_user_roles( details );

          //availability
          if ( details.dates_unavailable ) {
            display_dates_unavailable( details.dates_unavailable )
          }

          let update_needed_list_html = ``;
          (details.update_needed.contacts||[]).forEach(contact => {
            update_needed_list_html += `<li>
            <a href="${window.wpApiShare.site_url}/contacts/${window.lodash.escape(contact.ID)}" target="_blank">
                ${window.lodash.escape(contact.post_title)}:  ${window.lodash.escape(contact.last_modified_msg)}
            </a>
          </li>`
          })
          $('#update_needed_list').html(update_needed_list_html)

        }
      }).catch((e)=>{
      console.log( 'error in details')
      console.log( e)
    })

    //clear the locations typeahead of previous values when the modal is opened
    let typeahead = Typeahead['.js-typeahead-location_grid']
    if (typeahead) {
      typeahead.items = [];
      typeahead.comparedItems =[];
      typeahead.label.container.empty();
      typeahead.adjustInputSize()
    }

    /* locations */
    makeRequest( "get", `user?user=${user_id}&section=locations`, null , 'user-management/v1/')
      .done(locations=>{
        if ( window.current_user_lookup === user_id ) {
          if ( typeof dtMapbox !== "undefined" ) {
            dtMapbox.post_type = 'users'
            dtMapbox.user_id = user_id
            dtMapbox.user_location = locations.user_location
            write_results_box()

            jQuery( '#new-mapbox-search' ).on( "click", function() {
              dtMapbox.post_type = 'users'
              dtMapbox.user_id = user_id
              dtMapbox.user_location = locations.user_location
              write_input_widget()
            });
          } else {
            //locations
            if (typeahead) {
              typeahead.items = [];
              typeahead.comparedItems =[];
              typeahead.label.container.empty();
              typeahead.adjustInputSize()
            }
            (locations.user_location.location_grid || []).forEach(location => {
              typeahead.addMultiselectItemLayout({ID: location.id.toString(), name: location.label})
            })
          }
        }
      }).catch((e)=>{
      console.log( 'error in locations')
      console.log( e)
    })

    /* activity */
    makeRequest( "get", `user?user=${user_id}&section=activity`, null , 'user-management/v1/')
      .done(activity=>{
        if ( window.current_user_lookup === user_id ) {
          let activity_div = $('#activity')
          let activity_html = ``;
          activity.user_activity.forEach((a) => {
            if ( a.object_note !== '' ) {
              activity_html += `<div>
                <strong>${moment.unix(a.hist_time).format('YYYY-MM-DD')}</strong>
                ${window.lodash.escape(a.object_note)}
              </div>`
            }
          })
          activity_div.html(activity_html)
        }
      }).catch((e)=>{
      console.log( 'error in activity')
      console.log( e)
    })

    /* days active */
    makeRequest( "get", `user?user=${user_id}&section=days_active`, null , 'user-management/v1/')
      .done(days=>{
        if ( window.current_user_lookup === user_id ) {
          day_activity_chart(days.days_active)
        }
      }).catch((e)=>{
      console.log( 'error in days active')
      console.log( e)
    })

    /* unaccepted_contacts */
    makeRequest( "get", `user?user=${user_id}&section=unaccepted_contacts`, null , 'user-management/v1/')
      .done(response=>{
        // console.log('unaccepted_contacts')
        // console.log(response)

        if ( window.current_user_lookup === user_id && response.unaccepted_contacts.length > 0 ) {
          let unaccepted_contacts_html = ``
          response.unaccepted_contacts.forEach(contact => {
            let days = contact.time / 60 / 60 / 24;
            unaccepted_contacts_html += `<li>
          <a href="${window.wpApiShare.site_url}/contacts/${window.lodash.escape(contact.ID)}" target="_blank">
              ${window.lodash.escape(contact.name)} has be waiting to be accepted for ${days.toFixed(1)} days
              </a> </li>`
          })
          $('#unaccepted_contacts').html(unaccepted_contacts_html)
        } else {
          $('#unaccepted_contacts').html('')
        }

      }).catch((e)=>{
      console.log( 'error in unaccepted_contacts')
      console.log( e)
    })

    /* contact_accepts */
    makeRequest( "get", `user?user=${user_id}&section=contact_accepts`, null , 'user-management/v1/')
      .done(response=>{

        if ( window.current_user_lookup === user_id && response.contact_accepts.length > 0 ) {
          // assigned to contact accept
          let accepted_contacts_html = ``
          let avg_contact_accept = 0
          response.contact_accepts.forEach(contact => {
            let days = contact.time / 60 / 60 / 24;
            avg_contact_accept += days
            let accept_line = dt_user_management_localized.translations.accept_time
              .replace('%1$s', contact.name)
              .replace('%2$s', moment.unix(contact.date_accepted).format("MMM Do"))
              .replace('%3$s', days.toFixed(1))
            accepted_contacts_html += `<li>
          <a href="${window.wpApiShare.site_url}/contacts/${window.lodash.escape(contact.ID)}" target="_blank">
              ${window.lodash.escape(accept_line)}
          </a> </li>`
          })
          $('#contact_accepts').html(accepted_contacts_html)
          $('#avg_contact_accept').html(avg_contact_accept === 0 ? '-' : (avg_contact_accept / response.contact_accepts.length).toFixed(1))
        } else {
          $('#contact_accepts').html('')
          $('#avg_contact_accept').html('')
        }

      }).catch((e)=>{
      console.log( 'error in contact_accepts')
      console.log( e)
    })

    /* unattempted_contacts */
    makeRequest( "get", `user?user=${user_id}&section=unattempted_contacts`, null , 'user-management/v1/')
      .done(response=>{

        if ( window.current_user_lookup === user_id && response.unattempted_contacts.length > 0 ) {
          //contacts assigned with no contact attempt
          let unattemped_contacts_html = ``
          response.unattempted_contacts.forEach(contact => {
            let days = contact.time / 60 / 60 / 24;
            let line = window.lodash.escape(dt_user_management_localized.translations.no_contact_attempt_time)
              .replace('%1$s', window.lodash.escape(contact.name))
              .replace('%2$s', days.toFixed(1))
            unattemped_contacts_html += `<li>
          <a href="${window.wpApiShare.site_url}/contacts/${window.lodash.escape(contact.ID)}" target="_blank">
              ${window.lodash.escape(line)}
          </a> </li>`
          })
          $('#unattempted_contacts').html(unattemped_contacts_html)
        } else {
          $('#unattempted_contacts').html('')
        }

      }).catch((e)=>{
      console.log( 'error in unattempted_contacts')
      console.log( e)
    })

    /* contact_attempts */
    makeRequest( "get", `user?user=${user_id}&section=contact_attempts`, null , 'user-management/v1/')
      .done(response=>{

        if ( window.current_user_lookup === user_id && response.contact_attempts.length > 0 ) {
          //contact assigned to contact attempt
          let attempted_contacts_html = ``
          let avg_contact_attempt = 0
          response.contact_attempts.forEach(contact => {
            let days = contact.time / 60 / 60 / 24;
            avg_contact_attempt += days
            let line = window.lodash.escape(dt_user_management_localized.translations.contact_attempt_time)
              .replace('%1$s', window.lodash.escape(contact.name))
              .replace('%2$s', moment.unix(contact.date_attempted).format("MMM Do"))
              .replace('%3$s', days.toFixed(1))
            attempted_contacts_html += `<li>
          <a href="${window.wpApiShare.site_url}/contacts/${window.lodash.escape(contact.ID)}" target="_blank">
              ${window.lodash.escape(line)}
          </a> </li>`
          })
          $('#contact_attempts').html(attempted_contacts_html)
          $('#avg_contact_attempt').html(avg_contact_attempt === 0 ? '-' : (avg_contact_attempt / response.contact_attempts.length).toFixed(1))
        } else {
          $('#contact_attempts').html('')
          $('#avg_contact_attempt').html('')
        }

      }).catch((e)=>{
      console.log( 'error in contact_attempts')
      console.log( e)
    })
    function setup_user_roles(user_data){
      $('#user_roles_list input').prop('checked', false);
      if ( user_data.roles ){
        window.lodash.forOwn( user_data.roles, role=>{
          $(`#user_roles_list [value="${role}"]`).prop('checked', true)
          if ( role === "partner" || role === "marketer" ){
            $(`#allowed_sources_options`).show()
            $('#allowed_sources_options input').prop('checked', false);
            user_data.allowed_sources.forEach(source=>{
              $(`#allowed_sources_options [value="${source}"]`).prop('checked', true)
            })
            if ( user_data.length === 0 ){
              $(`#allowed_sources_options [value="all"]`).prop('checked', true)
            }
          } else {
            $(`#allowed_sources_options`).hide()
          }
        })
      }


    }
  }

  function day_activity_chart( days_active ) {
    am4core.ready(function() {

      am4core.useTheme(am4themes_animated);

      let chart = am4core.create("day_activity_chart", am4charts.XYChart);
      chart.maskBullets = false;

      let xAxis = chart.xAxes.push(new am4charts.CategoryAxis());
      let yAxis = chart.yAxes.push(new am4charts.CategoryAxis());

      xAxis.dataFields.category = "week_start";
      yAxis.dataFields.category = "weekday";

      // xAxis.renderer.grid.template.disabled = true;
      xAxis.renderer.minGridDistance = 100;

      // yAxis.renderer.grid.template.disabled = true;
      yAxis.renderer.inversed = true;
      yAxis.renderer.minGridDistance = 10;

      let series = chart.series.push(new am4charts.ColumnSeries());
      series.dataFields.categoryY = "weekday";
      series.dataFields.categoryX = "week_start";
      series.dataFields.value = "activity";
      series.sequencedInterpolation = true;
      series.defaultState.transitionDuration = 3000;

      let bgColor = new am4core.InterfaceColorSet().getFor("background");

      let columnTemplate = series.columns.template;
      columnTemplate.strokeWidth = 1;
      columnTemplate.strokeOpacity = 0.2;
      // columnTemplate.stroke = bgColor;
      columnTemplate.tooltipText = "{weekday}, {day}: {activity_count}";
      columnTemplate.width = am4core.percent(100);
      columnTemplate.height = am4core.percent(100);

      series.heatRules.push({
        target: columnTemplate,
        property: "fill",
        // min: am4core.color('#deeff8'),
        min: am4core.color(bgColor),
        max: chart.colors.getIndex(0)
      });

      chart.data = days_active
    });
  }

  function status_pie_chart(contact_statuses){

    if ( contact_statuses.length === 0 ) {
      $('#status_chart_div').empty()
      return
    }

    am4core.useTheme(am4themes_animated);

    let container = am4core.create("status_chart_div", am4core.Container);
    container.width = am4core.percent(100);
    container.height = am4core.percent(100);
    container.layout = "vertical";


    let chart = container.createChild(am4charts.PieChart);

    // Add data
    chart.data = contact_statuses

    // Add and configure Series
    let pieSeries = chart.series.push(new am4charts.PieSeries());
    pieSeries.dataFields.value = "count";
    pieSeries.dataFields.category = "status";
    pieSeries.slices.template.states.getKey("active").properties.shiftRadius = 0;
    pieSeries.labels.template.text = "{category}: {value.percent.formatNumber('#.#')}% ({value}) ";

    pieSeries.slices.template.events.on("hit", function(event) {
      selectSlice(event.target.dataItem);
    })

    let chart2 = container.createChild(am4charts.PieChart);
    chart2.width = am4core.percent(80);
    chart2.radius = am4core.percent(80);

    // Add and configure Series
    let pieSeries2 = chart2.series.push(new am4charts.PieSeries());
    pieSeries2.dataFields.value = "count";
    pieSeries2.dataFields.category = "reason";
    pieSeries2.slices.template.states.getKey("active").properties.shiftRadius = 0;
    pieSeries2.labels.template.disabled = true;
    pieSeries2.ticks.template.disabled = true;
    pieSeries2.alignLabels = false;
    pieSeries2.events.on("positionchanged", updateLines);

    let interfaceColors = new am4core.InterfaceColorSet();

    let line1 = container.createChild(am4core.Line);
    line1.strokeDasharray = "2,2";
    line1.strokeOpacity = 0.5;
    line1.stroke = interfaceColors.getFor("alternativeBackground");
    line1.isMeasured = false;

    let line2 = container.createChild(am4core.Line);
    line2.strokeDasharray = "2,2";
    line2.strokeOpacity = 0.5;
    line2.stroke = interfaceColors.getFor("alternativeBackground");
    line2.isMeasured = false;

    let selectedSlice;

    function selectSlice(dataItem) {
      selectedSlice = dataItem.slice;
      let fill = selectedSlice.fill;
      let count = dataItem.dataContext.reasons.length;
      pieSeries2.colors.list = [];
      for (let i = 0; i < count; i++) {
        pieSeries2.colors.list.push(fill.brighten(i * 2 / count));
      }
      chart2.data = dataItem.dataContext.reasons;
      pieSeries2.appear();

      let middleAngle = selectedSlice.middleAngle;
      let firstAngle = pieSeries.slices.getIndex(0).startAngle;
      let animation = pieSeries.animate([{ property: "startAngle", to: firstAngle - middleAngle }, { property: "endAngle", to: firstAngle - middleAngle + 360 }], 600, am4core.ease.sinOut);
      animation.events.on("animationprogress", updateLines);

      selectedSlice.events.on("transformed", updateLines);
    }

    function updateLines() {
      if (selectedSlice) {
        let p11 = { x: selectedSlice.radius * am4core.math.cos(selectedSlice.startAngle), y: selectedSlice.radius * am4core.math.sin(selectedSlice.startAngle) };
        let p12 = { x: selectedSlice.radius * am4core.math.cos(selectedSlice.startAngle + selectedSlice.arc), y: selectedSlice.radius * am4core.math.sin(selectedSlice.startAngle + selectedSlice.arc) };

        p11 = am4core.utils.spritePointToSvg(p11, selectedSlice);
        p12 = am4core.utils.spritePointToSvg(p12, selectedSlice);

        let p21 = { x: 0, y: -pieSeries2.pixelRadius };
        let p22 = { x: 0, y: pieSeries2.pixelRadius };

        p21 = am4core.utils.spritePointToSvg(p21, pieSeries2);
        p22 = am4core.utils.spritePointToSvg(p22, pieSeries2);

        line1.x1 = p11.x;
        line1.x2 = p21.x;
        line1.y1 = p11.y;
        line1.y2 = p21.y;

        line2.x1 = p12.x;
        line2.x2 = p22.x;
        line2.y1 = p12.y;
        line2.y2 = p22.y;
      }
    }

  }

  function write_add_user() {
    let obj = dt_user_management_localized
    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner users-spinner active"></span> '

    chart.empty().html(`

      <div class="grid-x">
        <div id="page-title" class="cell"><h3>${ window.lodash.escape( dt_user_management_localized.translations.add_new_user ) }</h3></div>
        <div class="cell medium-6">
          <form data-abide id="new-user-form">
            <div data-abide-error class="alert callout" style="display: none;">
              <p><i class="fi-alert"></i> ${ window.lodash.escape( dt_user_management_localized.translations.there_are_some_errors ) }</p>
            </div>

            <dl>
              <dt>${ window.lodash.escape( dt_user_management_localized.translations.contact_to_user ) }</dt>
              <dd>
                <div class="subassigned details">
                    <var id="subassigned-result-container" class="result-container subassigned-result-container"></var>
                    <div id="subassigned_t" name="form-subassigned" class="scrollable-typeahead">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-subassigned input-height"
                                           name="subassigned[query]" placeholder="${ window.lodash.escape( dt_user_management_localized.translations.search ) }"
                                           autocomplete="off">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
              </dd>
            </dl>
            <div id="contact-result"></div>

            <dl>
              <dt>${ window.lodash.escape( dt_user_management_localized.translations.nickname ) }</dt>
              <dd><input type="text" class="input" id="name" placeholder="${ window.lodash.escape( dt_user_management_localized.translations.nickname ) }" required /> </dd>
              <dt>${ window.lodash.escape( dt_user_management_localized.translations.email ) }</dt>
              <dd><input type="email" class="input" id="email" placeholder="${ window.lodash.escape( dt_user_management_localized.translations.email ) }" required /> </dd>
            </dl>

            <button type="submit" class="submit button" id="create-user">${ window.lodash.escape( dt_user_management_localized.translations.create_user ) }</button> <span class="spinner"></span>
          </form>

        </div>
        <div class="cell medium-6"></div>
        <div class="cell" id="result-link"></div>
        <div class="cell" style="height:20rem;"></div>
      </div>

    `)

    let result_div = jQuery('#result-link')
    let submit_button = jQuery('#create-user')
    let spinner_span = jQuery('.spinner')

    jQuery(document).on("submit", function(ev) {
      ev.preventDefault();
      let name = jQuery('#name').val()
      let email = jQuery('#email').val()

      let corresponds_to_contact = null
      if ( typeof window.contact_record !== 'undefined' ) {
        corresponds_to_contact = window.contact_record.ID
      }

      if ( name !== '' && email !== '' )  {
        spinner_span.html(spinner)
        submit_button.prop('disabled', true)

        makeRequest( "POST", `users/create`, { "user-email": email, "user-display": name, "corresponds_to_contact": corresponds_to_contact })
          .done(response=>{
            result_div.html(`<a href="${window.lodash.escape(window.wpApiShare.site_url)}/user-management/user/${window.lodash.escape(response)}">
              ${ window.lodash.escape( dt_user_management_localized.translations.view_new_user ) }</a>
            `)
            jQuery('#new-user-form').empty()
          })
          .catch(err=>{
            if ( err.status === 409) {
              spinner_span.html(``)
              submit_button.prop('disabled', false)

              if ( err.responseJSON.code === 'email_exists' ) {
                result_div.html(`${ window.lodash.escape( dt_user_management_localized.translations.email_already_in_system ) }`)
              }
              else if ( err.responseJSON.code === 'username_exists' ) {
                result_div.html(`${ window.lodash.escape( dt_user_management_localized.translations.username_in_system ) }`)
              }

            } else {
              spinner_span.html(``)
              submit_button.prop('disabled', false)
              result_div.html(`Oops. Something went wrong.`)
            }
          })
      }
    });

    ["subassigned"].forEach(field_id=>{
      $.typeahead({
        input: `.js-typeahead-${field_id}`,
        minLength: 0,
        accent: true,
        maxItem: 30,
        searchOnFocus: true,
        template: window.TYPEAHEADS.contactListRowTemplate,
        source: window.TYPEAHEADS.typeaheadContactsSource(),
        display: "name",
        templateValue: "{{name}}",
        dynamic: true,
        callback: {
          onClick: function(node, a, item, event){
            spinner_span.html(spinner)
            submit_button.prop('disabled', true)

            makeRequest('GET', 'contacts/'+item.ID, null, 'dt-posts/v2/' )
              .done(function(response){
                if ( item.user ) {
                  jQuery('#contact-result').html(`${window.lodash.escape(dt_user_management_localized.translations.already_user)} <a href="${window.lodash.escape(window.wpApiShare.site_url)}/user-management/user/${window.lodash.escape(response.corresponds_to_user)}">${window.lodash.escape(dt_user_management_localized.translations.view_user)}</a>`)
                } else {
                  window.contact_record = response
                  submit_button.prop('disabled', false)
                  jQuery('#name').val( window.lodash.escape(response.title) )
                  if ( response.contact_email[0] !== 'undefined' ) {
                    jQuery('#email').val( window.lodash.escape(response.contact_email[0].value) )
                  }

                }
                spinner_span.html(``)
              })
          },
          onResult: function (node, query, result, resultCount) {
            let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $(`#${field_id}-result-container`).html(text);
            submit_button.prop('disabled', false)
            $('#contact-result').html(``)
          },
          onHideLayout: function () {
            $(`#${field_id}-result-container`).html("");
          },
          onReady: function () {
            if (field_id === "subassigned"){
            }
          },
          onShowLayout (){
          }
        }
      })
    })
  }

})
