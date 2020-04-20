jQuery(document).ready(function($) {


  if( '/user-management/users/' === window.location.pathname ) {
    write_users_list()
  }
  if( '/user-management/map/' === window.location.pathname ) {
    write_users_map()
  }
  if( '/user-management/add-user/' === window.location.pathname ) {
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

    /* Load Modal */
    let user_id = 0;
    let open_multiplier_modal = (user_id)=>{

      window.current_user_lookup = user_id
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

      /* details */
      makeRequest( "get", `user?user=${user_id}&section=details`, null , 'user-management/v1/')
        .done(details=>{
          if ( window.current_user_lookup === user_id ) {
            $("#user_name").html(_.escape(details.display_name))

            $('#status-select').val(details.user_status)
            if ( details.user_status !== "0" ){
            }
            $('#workload-select').val(details.workload_status)

            //stats
            $('#update_needed_count').html(details.update_needed["total"])
            $('#needs_accepted_count').html(details.needs_accepted["total"])
            $('#active_contacts').html(details.active_contacts)
            $('#unread_notifications').html(details.unread_notifications)
            $('#assigned_this_month').text(details.assigned_counts.this_month)
            $('#assigned_last_month').text(details.assigned_counts.last_month)
            $('#assigned_this_year').text(details.assigned_counts.this_year)
            $('#assigned_all_time').text(details.assigned_counts.all_time)

            status_pie_chart( details.contact_statuses )
            setup_user_roles( details );

            //availability
            if ( details.dates_unavailable ) {
              display_dates_unavailable( details.dates_unavailable )
            }

            let update_needed_list_html = ``
            details.update_needed.contacts.forEach(contact => {
              update_needed_list_html += `<li>
            <a href="${window.wpApiShare.site_url}/contacts/${_.escape(contact.ID)}" target="_blank">
                ${_.escape(contact.post_title)}:  ${_.escape(contact.last_modified_msg)}
            </a>
          </li>`
            })
            $('#update_needed_list').html(update_needed_list_html)

            // console.log('details')
            // console.log(details)
          }
        }).catch((e)=>{
          console.log( 'error in details')
          console.log( e)
          // $('#user_modal').foundation('close');
        })

      /* locations */
      makeRequest( "get", `user?user=${user_id}&section=locations`, null , 'user-management/v1/')
        .done(locations=>{
          if ( window.current_user_lookup === user_id ) {
            if ( typeof dtMapbox !== "undefined" ) {
              dtMapbox.post_type = 'contacts'
              dtMapbox.post_id = locations.contact_id
              dtMapbox.post = locations.contact
              write_results_box()

              jQuery( '#new-mapbox-search' ).on( "click", function() {
                dtMapbox.post_type = 'contacts'
                dtMapbox.post_id = locations.contact_id
                dtMapbox.post = locations.contact
                write_input_widget()
              });
            } else {
              //locations
              let typeahead = Typeahead['.js-typeahead-location_grid']
              if (typeahead) {
                for (let i = 0; i < typeahead.items.length; i) {
                  typeahead.cancelMultiselectItem(0)
                }
              }
              locations.location_grid.forEach(location => {
                typeahead.addMultiselectItemLayout({ID: location.id.toString(), name: location.label})
              })
            }

            // console.log('locations')
            // console.log(locations)
          }
        }).catch((e)=>{
        console.log( 'error in locations')
        console.log( e)
        // $('#user_modal').foundation('close');
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
                  ${a.object_note}
                </div>`
              }
            })
            activity_div.html(activity_html)

            // console.log('activity')
            // console.log(activity)
          }
        }).catch((e)=>{
        console.log( 'error in activity')
        console.log( e)
        // $('#user_modal').foundation('close');
      })

      /* days active */
      makeRequest( "get", `user?user=${user_id}&section=days_active`, null , 'user-management/v1/')
        .done(days=>{
          if ( window.current_user_lookup === user_id ) {
            day_activity_chart(days.days_active)

            // console.log('days_active')
            // console.log(days)
          }
        }).catch((e)=>{
        console.log( 'error in days active')
        console.log( e)
        // $('#user_modal').foundation('close');
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
            <a href="${window.wpApiShare.site_url}/contacts/${_.escape(contact.ID)}" target="_blank">
                ${_.escape(contact.name)} has be waiting to be accepted for ${days.toFixed(1)} days
                </a> </li>`
              })
              $('#unaccepted_contacts').html(unaccepted_contacts_html)
          } else {
            $('#unaccepted_contacts').html('')
          }

        }).catch((e)=>{
        console.log( 'error in unaccepted_contacts')
        console.log( e)
        // $('#user_modal').foundation('close');
      })

      /* contact_accepts */
      makeRequest( "get", `user?user=${user_id}&section=contact_accepts`, null , 'user-management/v1/')
        .done(response=>{
          // console.log('contact_accepts')
          // console.log(response)

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
            <a href="${window.wpApiShare.site_url}/contacts/${_.escape(contact.ID)}" target="_blank">
                ${_.escape(accept_line)}
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
        // $('#user_modal').foundation('close');
      })

      /* unattempted_contacts */
      makeRequest( "get", `user?user=${user_id}&section=unattempted_contacts`, null , 'user-management/v1/')
        .done(response=>{
          // console.log('unattempted_contacts')
          // console.log(response)

          if ( window.current_user_lookup === user_id && response.unattempted_contacts.length > 0 ) {
            //contacts assigned with no contact attempt
            let unattemped_contacts_html = ``
            response.unattempted_contacts.forEach(contact => {
              let days = contact.time / 60 / 60 / 24;
              let line = dt_user_management_localized.translations.no_contact_attempt_time
                .replace('%1$s', contact.name)
                .replace('%2$s', days.toFixed(1))
              unattemped_contacts_html += `<li>
            <a href="${window.wpApiShare.site_url}/contacts/${_.escape(contact.ID)}" target="_blank">
                ${_.escape(line)}
            </a> </li>`
            })
            $('#unattempted_contacts').html(unattemped_contacts_html)
          } else {
            $('#unattempted_contacts').html('')
          }

        }).catch((e)=>{
        console.log( 'error in unattempted_contacts')
        console.log( e)
        // $('#user_modal').foundation('close');
      })

      /* contact_attempts */
      makeRequest( "get", `user?user=${user_id}&section=contact_attempts`, null , 'user-management/v1/')
        .done(response=>{
          // console.log('contact_attempts')
          // console.log(response)

          if ( window.current_user_lookup === user_id && response.contact_attempts.length > 0 ) {
            //contact assigned to contact attempt
            let attempted_contacts_html = ``
            let avg_contact_attempt = 0
            response.contact_attempts.forEach(contact => {
              let days = contact.time / 60 / 60 / 24;
              avg_contact_attempt += days
              let line = dt_user_management_localized.translations.contact_attempt_time
                .replace('%1$s', contact.name)
                .replace('%2$s', moment.unix(contact.date_attempted).format("MMM Do"))
                .replace('%3$s', days.toFixed(1))
              attempted_contacts_html += `<li>
            <a href="${window.wpApiShare.site_url}/contacts/${_.escape(contact.ID)}" target="_blank">
                ${_.escape(line)}
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
        // $('#user_modal').foundation('close');
      })


    }


    $('#refresh_cached_data').on('click', function () {
      $('#loading-page').addClass('active')
      makeRequest( "get", `get_users?refresh=1`, null , 'user-management/v1/').then(()=>{
        location.reload()
      })
    })

    $('.user_row').on("click", function (a) {
      if ( a.target._DT_CellIndex.column !== 0 ){
        user_id = $(this).data("user")
        open_multiplier_modal(user_id)
      }
    })

    $('#user_name').on( "click", function(e) {
      window.user_name = $(this).text()
      $(this).parent().prepend(`
              <div class="input-group" id="user-name-input-wrapper">
                  <input type="text" class="input-group-field" style="max-width: 50%;" id="user-name-input" value="${window.user_name}" />
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
        let new_name = $('#user-name-input').val()
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


    $.urlParam = function(name){
      var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
      if ( results == null ) {
        return 0;
      }
      return results[1] || 0;
    }
    if ( $.urlParam('user_id') ) {
      open_multiplier_modal(decodeURIComponent($.urlParam('user_id') ) )
    }



    let update_user = ( user_id, key, value )=>{
      let data =  {
        [key]: value
      }
      return makeRequest( "POST", `user?user=${user_id}`, data , 'user-management/v1/' )

    }


    /**
     * Status
     */
    $('#status-select').on('change', function () {
      let value = $(this).val()
      update_user( user_id, 'user_status', value)
    })
    $('#workload-select').on('change', function () {
      let value = $(this).val()
      update_user( user_id, 'workload_status', value)
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
      update_user( user_id, 'add_unavailability', {start_date, end_date}).then((resp)=>{
        $('#add_unavailable_dates_spinner').removeClass('active')
        unavailable_dates_picker.val('');
        display_dates_unavailable(resp.dates_unavailable)
      })
    })

    function setup_user_roles(user_data){
      if ( user_data.roles ){
        _.forOwn( user_data.roles, role=>{
          $(`#user_roles_list [value="${role}"]`).prop('checked', true)
        } )
      }
    }
    $('#save_roles').on("click", function () {
      $(this).toggleClass('loading', true)
      let roles = [];
      $('#user_roles_list input:checked').each(function () {
        roles.push($(this).val())
      })
      update_user( user_id, 'save_roles', roles).then(()=>{
        $(this).toggleClass('loading', false)
      }).catch(()=>{
        $(this).toggleClass('loading', false)
      })

    })

    let display_dates_unavailable = (list = [] )=>{
      let date_unavailable_table = $('#unavailable-list')
      date_unavailable_table.empty()
      let rows = ``
      list.forEach(range=>{
        rows += `<tr>
        <td>${_.escape(range.start_date)}</td>
        <td>${_.escape(range.end_date)}</td>
        <td><button class="button remove_dates_unavailable" data-id="${_.escape(range.id)}">Remove</button></td>
      </tr>`
      })
      date_unavailable_table.html(rows)
    }
    $( document).on( 'click', '.remove_dates_unavailable', function () {
      let id = $(this).data('id');
      update_user( user_id, 'remove_unavailability', id).then((resp)=>{
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
            template: _.escape(window.wpApiShare.translations.regions_of_focus),
            all: _.escape(window.wpApiShare.translations.all_locations),
          }],
          source: {
            focus: {
              display: "name",
              ajax: {
                url: wpApiShare.root + 'dt/v1/mapping_module/search_location_grid_by_name',
                data: {
                  s: "{{query}}",
                  filter: function () {
                    return _.get(window.Typeahead['.js-typeahead-location_grid'].filters.dropdown, 'value', 'all')
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
                update_user( user_id, 'remove_location', item.ID)
              }
            }
          },
          callback: {
            onClick: function(node, a, item, event){
              update_user( user_id, 'add_location', item.ID)
            },
            onReady(){
              this.filters.dropdown = {key: "group", value: "focus", template: _.escape(window.wpApiShare.translations.regions_of_focus)}
              this.container
                .removeClass("filter")
                .find("." + this.options.selector.filterButton)
                .html(_.escape(window.wpApiShare.translations.regions_of_focus));
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
    container.layout = "horizontal";


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

  function write_users_map() {
    let obj = dt_user_management_localized
    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner users-spinner active"></span> '

    chart.empty().html(spinner)

    makeRequest( "GET", `get_user_list`, null , 'user-management/v1/')
      .done(response=>{
        window.user_list = response
        // console.log('USER LIST')
        // console.log(response)
      }).catch((e)=>{
      console.log( 'error in activity')
      console.log( e)
    })

    makeRequest( "POST", `grid_totals`, { status: null }, 'user-management/v1/')
      .done(grid_data=>{
        // console.log('GRID TOTALS')
        // console.log(grid_data)
        window.grid_data = grid_data

        chart.empty().html(`
                <style>
                    #map-wrapper {
                        height: ${window.innerHeight - 100}px !important;
                    }
                    #map {
                        height: ${window.innerHeight - 100}px !important;
                    }
                    #geocode-details {
                        height: ${window.innerHeight - 250}px !important;
                        overflow: scroll;
                        opacity: 100%;
                    }
                    .accordion {
                        list-style-type:none;
                    }
                    .delete-button {
                        margin-bottom: 0 !important;
                    }
                    .add-user-button {
                        padding-top: 10px;
                    }
                </style>
                <div id="map-wrapper">
                    <div id='map'></div>
                    <div id='legend' class='legend'>
                        <div class="grid-x grid-margin-x grid-padding-x">
                            <div class="cell small-2 center info-bar-font">
                                Responsibility 
                            </div>
                            <div class="cell small-2 center border-left">
                                <select id="level" class="small" style="width:170px;">
                                    <option value="none" disabled></option>
                                    <option value="none" disabled>Zoom Level</option>
                                    <option value="none"></option>
                                    <option value="auto" selected>Auto Zoom</option>
                                    <option value="none" disabled>-----</option>
                                    <option value="world">World</option>
                                    <option value="admin0">Country</option>
                                    <option value="admin1">State</option>
                                    <option value="none" disabled></option>
                                </select> 
                            </div>
                            <div class="cell small-2 center border-left">
                                <select id="status" class="small" style="width:170px;">
                                    <option value="none" disabled></option>
                                    <option value="none" disabled>Status</option>
                                    <option value="none"></option>
                                    <option value="all" selected>Status - All</option>
                                    <option value="none" disabled>-----</option>
                                    <option value="active">Active</option>
                                    <option value="away">Away</option>
                                    <option value="inconsistent">Inconsistent</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="none" disabled></option>
                                </select> 
                            </div>
                            <div class="cell small-5 center border-left info-bar-font">
                                
                            </div>
                            
                            <div class="cell small-1 center border-left">
                                <div class="grid-y">
                                    <div class="cell center" id="admin">World</div>
                                    <div class="cell center" id="zoom" >0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="spinner">${spinner}</div>
                    <div id="cross-hair">&#8982</div>
                    <div id="geocode-details" class="geocode-details">
                        Leadership<span class="close-details" style="float:right;"><i class="fi-x"></i></span>
                        <hr style="margin:10px 5px;">
                        <div id="geocode-details-content"></div>
                    </div>
                </div>
             `)

        // set info box
        set_info_boxes()

        // init map
        mapboxgl.accessToken = obj.map_key;
        var map = new mapboxgl.Map({
          container: 'map',
          style: 'mapbox://styles/mapbox/light-v10',
          center: [-98, 38.88],
          minZoom: 1,
          zoom: 1.8
        });

        // disable map rotation using right click + drag
        map.dragRotate.disable();

        // disable map rotation using touch rotation gesture
        map.touchZoomRotate.disableRotation();

        // cross-hair
        map.on('zoomstart', function() {
          jQuery('#cross-hair').show()
        })
        map.on('zoomend', function() {
          jQuery('#cross-hair').hide()
        })
        map.on('dragstart', function() {
          jQuery('#cross-hair').show()
        })
        map.on('dragend', function() {
          jQuery('#cross-hair').hide()
        })

        // grid memory vars
        window.previous_grid_id = 0
        window.previous_grid_list = []

        // default load state
        map.on('load', function() {

          window.previous_grid_id = '1'
          window.previous_grid_list.push('1')
          jQuery.get('https://storage.googleapis.com/location-grid-mirror/collection/1.geojson', null, null, 'json')
            .done(function (geojson) {

              jQuery.each(geojson.features, function (i, v) {
                if (window.grid_data[geojson.features[i].properties.id]) {
                  geojson.features[i].properties.value = parseInt(window.grid_data[geojson.features[i].properties.id].count)
                } else {
                  geojson.features[i].properties.value = 0
                }
              })
              map.addSource('1', {
                'type': 'geojson',
                'data': geojson
              });
              map.addLayer({
                'id': '1',
                'type': 'fill',
                'source': '1',
                'paint': {
                  'fill-color': [
                    'interpolate',
                    ['linear'],
                    ['get', 'value'],
                    0,
                    'rgba(0, 0, 0, 0)',
                    1,
                    '#547df8',
                    50,
                    '#3754ab',
                    100,
                    '#22346a'
                  ],
                  'fill-opacity': 0.75
                }
              });
              map.addLayer({
                'id': '1line',
                'type': 'line',
                'source': '1',
                'paint': {
                  'line-color': 'black',
                  'line-width': 1
                }
              });
            })
        })

        // update info box on zoom
        map.on('zoom', function() {
          document.getElementById('zoom').innerHTML = Math.floor(map.getZoom())

          let level = get_level()
          let name = ''
          if ( level === 'world') {
            name = 'World'
          } else if ( level === 'admin0') {
            name = 'Country'
          } else if ( level === 'admin1' ) {
            name = 'State'
          }
          document.getElementById('admin').innerHTML = name
        })

        // click controls
        window.click_behavior = 'layer'

        map.on('click', function( e ) {
          // this section increments up the result on level because
          // it corresponds better to the viewable user intent for details
          let level = get_level()
          if ( level === 'world' ) {
            level = 'admin0'
          }
          else if ( level === 'admin0' ) {
            level = 'admin1'
          }
          else if ( level === 'admin1' ) {
            level = 'admin2'
          }
          load_detail_panel( e.lngLat.lng, e.lngLat.lat, level )
        })

        // Status
        jQuery('#status').on('change', function() {
          window.current_status = jQuery('#status').val()

          makeRequest( "POST", `grid_totals`, { status: window.current_status }, 'user-management/v1/')
            .done(grid_data=>{
              window.previous_grid_id = 0
              clear_layers()
              window.grid_data = grid_data

              let lnglat = map.getCenter()
              load_layer( lnglat.lng, lnglat.lat )
            }).catch((e)=>{
            console.log('error getting grid_totals')
            console.log(e)
          })

        })
        // load new layer on event
        map.on('zoomend', function() {
          let lnglat = map.getCenter()
          load_layer( lnglat.lng, lnglat.lat, 'zoom' )
        } )
        map.on('dragend', function() {
          let lnglat = map.getCenter()
          load_layer( lnglat.lng, lnglat.lat, 'drag' )
        } )
        function load_layer( lng, lat, event_type ) {
          let spinner = jQuery('#spinner')
          spinner.show()

          // set geocode level, default to auto
          let level = get_level()

          // standardize longitude
          if (lng > 180) {
            lng = lng - 180
            lng = -Math.abs(lng)
          } else if (lng < -180) {
            lng = lng + 180
            lng = Math.abs(lng)
          }

          // geocode
          jQuery.get(obj.theme_uri + 'dt-mapping/location-grid-list-api.php',
            {
              type: 'geocode',
              longitude: lng,
              latitude: lat,
              level: level,
              country_code: null,
              nonce: obj.nonce
            }, null, 'json')
            .done(function (data) {

              // default layer to world
              if ( data.grid_id === undefined || level === 'world' ) {
                data.grid_id = '1'
              }

              // is new test
              if ( window.previous_grid_id !== data.grid_id ) {

                // is defined test
                var mapLayer = map.getLayer(data.grid_id);
                if(typeof mapLayer === 'undefined') {

                  // get geojson collection
                  jQuery.ajax({
                    type: 'GET',
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: 'https://storage.googleapis.com/location-grid-mirror/collection/' + data.grid_id + '.geojson',
                    statusCode: {
                      404: function() {
                        console.log('404. Do nothing.')
                      }
                    }
                  })
                    .done(function (geojson) {

                      // add data to geojson properties
                      jQuery.each(geojson.features, function (i, v) {
                        if (window.grid_data[geojson.features[i].properties.id]) {
                          geojson.features[i].properties.value = parseInt(window.grid_data[geojson.features[i].properties.id].count)
                        } else {
                          geojson.features[i].properties.value = 0
                        }
                      })

                      // add source
                      map.addSource(data.grid_id.toString(), {
                        'type': 'geojson',
                        'data': geojson
                      });

                      // add fill layer
                      map.addLayer({
                        'id': data.grid_id.toString(),
                        'type': 'fill',
                        'source': data.grid_id.toString(),
                        'paint': {
                          'fill-color': [
                            'interpolate',
                            ['linear'],
                            ['get', 'value'],
                            0,
                            'rgba(0, 0, 0, 0)',
                            1,
                            '#547df8',
                            50,
                            '#3754ab',
                            100,
                            '#22346a'
                          ],
                          'fill-opacity': 0.75
                        }
                      });

                      // add border lines
                      map.addLayer({
                        'id': data.grid_id.toString() + 'line',
                        'type': 'line',
                        'source': data.grid_id.toString(),
                        'paint': {
                          'line-color': 'black',
                          'line-width': 1
                        }
                      });

                      remove_layer( data.grid_id, event_type )

                    }) // end get geojson collection

                }
              } // end load new layer
              spinner.hide()
            }); // end geocode

        } // end load section function
        function load_detail_panel( lng, lat, level ) {

          // standardize longitude
          if (lng > 180) {
            lng = lng - 180
            lng = -Math.abs(lng)
          } else if (lng < -180) {
            lng = lng + 180
            lng = Math.abs(lng)
          }

          if ( level === 'world' ) {
            level = 'admin0'
          }

          let content = jQuery('#geocode-details-content')
          content.empty().html(`<img src="${obj.theme_uri}spinner.svg" class="spinner-image" alt="spinner"/>`)

          jQuery('#geocode-details').show()

          // geocode
          makeRequest('GET', obj.theme_uri + 'dt-mapping/location-grid-list-api.php?type=geocode&longitude='+lng+'&latitude='+lat+'&level='+level+'&nonce='+obj.nonce )
            .done(details=>{
              /* hierarchy list*/
              content.empty().append(`<ul id="hierarchy-list" class="accordion" data-accordion></ul>`)
              let list = jQuery('#hierarchy-list')
              if ( details.admin0_grid_id ) {
                list.append( `
                              <li id="admin0_wrapper" class="accordion-item" data-accordion-item>
                               <a href="#" class="accordion-title">${details.admin0_name} :  <span id="admin0_count">0</span></a>
                                <div class="accordion-content grid-x" data-tab-content><div id="admin0_list" class="grid-x"></div></div>
                              </li>
                            `)
                let level_list = jQuery('#admin0_list')
                if ( details.admin0_grid_id in window.user_list ) {
                  jQuery('#admin0_count').html(window.user_list[details.admin0_grid_id].length)
                  jQuery.each(window.user_list[details.admin0_grid_id], function(i,v) {
                    level_list.append(`
                              <div class="cell small-10 align-self-middle" data-id="${v.grid_meta_id}">
                                <a href="/user-management/users/?user_id=${v.user_id}">
                                  ${v.name}
                                </a>
                              </div>
                              <div class="cell small-2" data-id="${v.grid_meta_id}">
                                <a class="button clear delete-button mapbox-delete-button small float-right" data-postid="${v.contact_id}" data-id="${v.grid_meta_id}">
                                  <img src="${obj.theme_uri}/dt-assets/images/invalid.svg" alt="delete">
                                </a>
                              </div>`)
                  })
                }
                level_list.append(`<div class="cell add-user-button"><button class="add-user small expanded button hollow" data-level="admin0" data-location="${details.admin0_grid_id}">add user to ${details.admin0_name}</button></div>`)

              }
              if ( details.admin1_grid_id ) {
                list.append( `
                              <li id="admin1_wrapper" class="accordion-item" data-accordion-item >
                                <a href="#" class="accordion-title">${details.admin1_name} : <span id="admin1_count">0</span></a>
                                <div class="accordion-content" data-tab-content><div id="admin1_list" class="grid-x"></div></div>
                              </li>
                            `)

                  let level_list = jQuery('#admin1_list')
                  if ( details.admin1_grid_id in window.user_list ) {
                    jQuery('#admin1_count').html(window.user_list[details.admin1_grid_id].length)
                    jQuery.each(window.user_list[details.admin1_grid_id], function(i,v) {
                      level_list.append(`
                              <div class="cell small-10 align-self-middle" data-id="${v.grid_meta_id}">
                                <a href="/user-management/users/?user_id=${v.user_id}">
                                  ${v.name}
                                </a>
                              </div>
                              <div class="cell small-2" data-id="${v.grid_meta_id}">
                                <a class="button clear delete-button mapbox-delete-button small float-right" data-postid="${v.contact_id}" data-id="${v.grid_meta_id}">
                                  <img src="${obj.theme_uri}/dt-assets/images/invalid.svg" alt="delete">
                                </a>
                              </div>`)
                    })
                  }
                level_list.append(`<div class="cell add-user-button"><button class="add-user small expanded button hollow" data-level="admin1" data-location="${details.admin1_grid_id}">add user to ${details.admin1_name}</button></div>`)
              }
              if ( details.admin2_grid_id ) {
                list.append( `
                              <li id="admin2_wrapper" class="accordion-item" data-accordion-item>
                                <a href="#" class="accordion-title">${details.admin2_name} : <span id="admin2_count">0</span></a>
                                <div class="accordion-content" data-tab-content><div id="admin2_list"  class="grid-x"></div></div>
                              </li>
                            `)

                  let level_list = jQuery('#admin2_list')
                  if ( details.admin2_grid_id in window.user_list ) {
                    jQuery('#admin2_count').html(window.user_list[details.admin2_grid_id].length)
                    jQuery.each(window.user_list[details.admin2_grid_id], function(i,v) {
                      level_list.append(`
                              <div class="cell small-10 align-self-middle" data-id="${v.grid_meta_id}">
                                <a href="/user-management/users/?user_id=${v.user_id}">
                                  ${v.name}
                                </a>
                              </div>
                              <div class="cell small-2" data-id="${v.grid_meta_id}">
                                <a class="button clear delete-button mapbox-delete-button small float-right" data-postid="${v.contact_id}" data-id="${v.grid_meta_id}">
                                  <img src="${obj.theme_uri}/dt-assets/images/invalid.svg" alt="delete">
                                </a>
                              </div>`)
                    })
                  }
                level_list.append(`<div class="cell add-user-button"><button class="add-user expanded small button hollow" data-level="admin2" data-location="${details.admin2_grid_id}">add user to ${details.admin2_name}</button></div>`)
              }

              jQuery('.accordion-item').last().addClass('is-active')
              list.foundation()
              /* end hierarchy list */

              /* build click function to add user to location */
              jQuery('.add-user').on('click', function() {
                jQuery('#add-user-wrapper').remove()
                let selected_location = jQuery(this).data('location')
                let list_level = jQuery(this).data('level')

                jQuery(this).parent().append(`
                <div id="add-user-wrapper">
                    <var id="add-user-location-result-container" class="result-container add-user-location-result-container"></var>
                    <div id="assigned_to_t" name="form-assigned_to">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-add-user input-height" dir="auto"
                                           name="assigned_to[query]" placeholder="Search Users"
                                           autocomplete="off">
                                </span>
                                <span class="typeahead__button">
                                    <button type="button" class="search_assigned_to typeahead__image_button input-height" data-id="assigned_to_t">
                                        <img src="${obj.theme_uri}/dt-assets/images/chevron_down.svg" alt="chevron"/>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                `)
                jQuery.typeahead({
                  input: '.js-typeahead-add-user',
                  minLength: 0,
                  accent: true,
                  searchOnFocus: true,
                  source: TYPEAHEADS.typeaheadUserSource(),
                  templateValue: "{{name}}",
                  template: function (query, item) {
                    return `<div class="assigned-to-row" dir="auto">
                              <span>
                                  <span class="avatar"><img style="vertical-align: text-bottom" src="{{avatar}}"/></span>
                                  ${_.escape( item.name )}
                              </span>
                              ${ item.status_color ? `<span class="status-square" style="background-color: ${_.escape(item.status_color)};">&nbsp;</span>` : '' }
                              ${ item.update_needed ? `<span>
                                <img style="height: 12px;" src="${_.escape( obj.theme_uri )}/dt-assets/images/broken.svg"/>
                                <span style="font-size: 14px">${_.escape(item.update_needed)}</span>
                              </span>` : '' }
                            </div>`
                  },
                  dynamic: true,
                  hint: true,
                  emptyTemplate: _.escape(window.wpApiShare.translations.no_records_found),
                  callback: {
                    onClick: function(node, a, item){
                      API.update_post('contacts', item.contact_id, {
                        location_grid_meta: {
                          values: [
                            {
                              grid_id: selected_location
                            }
                          ]
                        }}).then(function (response) {
                          console.log(response)

                          // update user list
                          makeRequest( "GET", `get_user_list`, null , 'user-management/v1/')
                            .done(response=>{
                              window.user_list = response
                              if ( selected_location in window.user_list ) {
                                jQuery('#'+list_level+'_count').html(response[selected_location].length)
                              }

                            }).catch((e)=>{
                            console.log( 'error in activity')
                            console.log( e)
                          })

                          // update grid totals
                          makeRequest( "POST", `grid_totals`, { status: window.current_status }, 'user-management/v1/')
                            .done(grid_data=>{
                              window.previous_grid_id = 0
                              clear_layers()
                              window.grid_data = grid_data

                              let lnglat = map.getCenter()
                              load_layer( lnglat.lng, lnglat.lat )
                            }).catch((e)=>{
                            console.log('error getting grid_totals')
                            console.log(e)
                          })

                          // remove user add input
                          jQuery('#add-user-wrapper').remove()

                          // add new user to list
                        let grid_meta = ''
                          jQuery.each(response.location_grid_meta, function(i,v) {
                             if ( v.grid_id === selected_location ) {
                               grid_meta = v.grid_meta_id
                             }
                          })
                          jQuery('#'+list_level+'_list').prepend(`
                              <div class="cell small-10 align-self-middle" data-id="${grid_meta}">
                                <a  href="/user-management/users/?user_id=${response.corresponds_to_user}">
                                  ${response.title}
                                </a>
                              </div>
                              <div class="cell small-2" data-id="${grid_meta}">
                                <a class="button clear delete-button mapbox-delete-button small float-right" data-postid="${response.ID}" data-id="${grid_meta}">
                                  <img src="${obj.theme_uri}/dt-assets/images/invalid.svg" alt="delete">
                                </a>
                              </div>`)

                      }).catch(err => { console.error(err) })
                    },
                    onResult: function (node, query, result, resultCount) {
                      let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
                      $('#add-user-location-result-container').html(text);
                    },
                    onHideLayout: function () {
                      $('.add-user-location-result-container').html("");
                    },
                    onReady: function () {

                    }
                  },
                });

              })
              /* end click add function */

              jQuery( '.mapbox-delete-button' ).on( "click", function(e) {

                let data = {
                  location_grid_meta: {
                    values: [
                      {
                        grid_meta_id: e.currentTarget.dataset.id,
                        delete: true,
                      }
                    ]
                  }
                }

                let post_id = e.currentTarget.dataset.postid

                API.update_post( 'contacts', post_id, data ).then(function (response) {
                  jQuery('div[data-id='+e.currentTarget.dataset.id+']').remove()
                }).catch(err => { console.error(err) })

              });

            }); // end geocode
        }
        function get_level( ) {
          let level = jQuery('#level').val()
          if ( level === 'auto' || level === 'none' ) { // if none, then auto set
            level = 'admin0'
            if ( map.getZoom() <= 3 ) {
              level = 'world'
            }
            else if ( map.getZoom() >= 5 ) {
              level = 'admin1'
            }
          }
          return level;
        }
        function set_level( auto = false) {
          if ( auto ) {
            jQuery('#level :selected').attr('selected', false)
            jQuery('#level').val('auto')
          } else {
            jQuery('#level :selected').attr('selected', false)
            jQuery('#level').val(get_level())
          }
        }
        function remove_layer( grid_id, event_type ) {
          window.previous_grid_list.push( grid_id )
          window.previous_grid_id = grid_id

          if ( event_type === 'click' && window.click_behavior === 'add' ) {
            window.click_add_list.push( grid_id )
          }
          else {
            clear_layers ( grid_id )
          }
        }
        function clear_layers ( grid_id = null ) {
          jQuery.each(window.previous_grid_list, function(i,v) {
            let mapLayer = map.getLayer(v.toString());
            if(typeof mapLayer !== 'undefined' && v !== grid_id) {
              map.removeLayer( v.toString() )
              map.removeLayer( v.toString() + 'line' )
              map.removeSource( v.toString() )
            }
          })
        }
        function set_info_boxes() {
          let map_wrapper = jQuery('#map-wrapper')
          jQuery('.legend').css( 'width', map_wrapper.innerWidth() - 20 )
          jQuery( window ).resize(function() {
            jQuery('.legend').css( 'width', map_wrapper.innerWidth() - 20 )
          });
          // jQuery('#geocode-details').css('height', map_wrapper.innerHeight() - 125 )
        }
        function close_geocode_details() {
          jQuery('#geocode-details').hide()
        }

        jQuery('.close-details').on('click', function() {
          jQuery('#geocode-details').hide()
        })

      }).catch(err=>{
      console.log("error")
      console.log(err)
    })

  }

  function write_add_user() {
    let obj = dt_user_management_localized
    let chart = jQuery('#chart')
    let spinner = ' <span class="loading-spinner users-spinner active"></span> '

    chart.empty().html(`
    
      <div class="grid-x">
        <div id="page-title" class="cell"><h3>Add New User</h3></div>
        <div class="cell medium-6">
          <form data-abide id="new-user-form">
            <div data-abide-error class="alert callout" style="display: none;">
              <p><i class="fi-alert"></i> There are some errors in your form.</p>
            </div>
            
            <dl>
              <dt>Contact to make a user (optional)</dt>
              <dd>
                <div class="subassigned details">
                    <var id="subassigned-result-container" class="result-container subassigned-result-container"></var>
                    <div id="subassigned_t" name="form-subassigned" class="scrollable-typeahead">
                        <div class="typeahead__container">
                            <div class="typeahead__field">
                                <span class="typeahead__query">
                                    <input class="js-typeahead-subassigned input-height"
                                           name="subassigned[query]" placeholder="Search multipliers and contacts"
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
              <dt>Nickname</dt>
              <dd><input type="text" class="input" id="name" placeholder="nick name" required /> </dd>
              <dt>Email</dt>
              <dd><input type="email" class="input" id="email" placeholder="email address" required /> </dd>
              
            </dl>
            
            <button type="submit" class="submit button" id="create-user">Create User</button> <span class="spinner"></span>
            
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
              console.log(response)
              result_div.html(`<a href="/user-management/users/?user_id=${response}">View New User</a>`)
              jQuery('#new-user-form').empty()
            })
            .catch(err=>{
              if ( err.status === 409) {
                spinner_span.html(``)
                submit_button.prop('disabled', false)

                if ( err.responseJSON.code === 'email_exists' ) {
                  result_div.html(`Email address is already in the system as a user!`)
                }
                else if ( err.responseJSON.code === 'username_exists' ) {
                  result_div.html(`Username is already in the system as a user!`)
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
            console.log(item)
            spinner_span.html(spinner)
            submit_button.prop('disabled', true)

            makeRequest('GET', 'contacts/'+item.ID, null, 'dt-posts/v2/' )
              .done(function(response){
                if ( item.user ) {
                  jQuery('#contact-result').html(`This contact is already a user. <a href="/user-management/users/?user_id=${response.corresponds_to_user}">View User</a>`)
                } else {
                  window.contact_record = response
                  submit_button.prop('disabled', false)
                  jQuery('#name').val( response.title)
                  if ( response.contact_email[0] !== 'undefined' ) {
                    jQuery('#email').val( response.contact_email[0].value )
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
