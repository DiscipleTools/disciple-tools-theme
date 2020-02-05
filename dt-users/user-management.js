jQuery(document).ready(function($) {

  multipliers_js()

  function multipliers_js(){
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
        { "orderSequence": [ "asc", "desc" ] },
      ],
      columnDefs: [ {
        sortable: false,
        "class": "index",
        targets: 0
      } ],
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

    let user_id = 0;
    let open_multiplier_modal = (user_id)=>{
      $('#user_modal').foundation('open');
      $('.users-spinner').addClass("active")
      $('#user_modal_content').hide()
      makeRequest( "get", `user?user=${user_id}`, null , 'user-management/v1/')
      .then(response=>{
        $('#user_modal_content').show()
        $('.users-spinner').removeClass("active")
        $("#user_name").html(_.escape(response.display_name))

        //status
        $('#status-select').val(response.user_status)
        if ( response.user_status !== "0" ){
        }
        $('#workload-select').val(response.workload_status)

        //locations
        let typeahead = Typeahead['.js-typeahead-location_grid']
        if ( typeahead ){
          for (let i = 0; i < typeahead.items.length; i ){
            typeahead.cancelMultiselectItem(0)
          }

        }
        response.locations.forEach( location=>{
          typeahead.addMultiselectItemLayout({ID:location.grid_id.toString(), name:location.name})
        })

        //availability
        display_dates_unavailable( response.dates_unavailable )

        //stats
        $('#update_needed_count').html(response.update_needed["total"])
        $('#needs_accepted_count').html(response.needs_accepted["total"])
        $('#active_contacts').html(response.active_contacts)
        $('#unread_notifications').html(response.unread_notifications)
        $('#assigned_this_month').text(response.assigned_counts.this_month)
        $('#assigned_last_month').text(response.assigned_counts.last_month)
        $('#assigned_this_year').text(response.assigned_counts.this_year)
        $('#assigned_all_time').text(response.assigned_counts.all_time)

        day_activity_chart(response.days_active)
        status_pie_chart( response.contact_statuses )

        // 10s
        // contacts assigned but not accepted
        let unaccepted_contacts_html = ``
        response.times.unaccepted_contacts.forEach(contact=>{
          let days = contact.time / 60 / 60 / 24;
          unaccepted_contacts_html += `<li>
            <a href="${window.wpApiShare.site_url}/contacts/${_.escape(contact.ID)}" target="_blank">
                ${_.escape(contact.name)} has be waiting to be accepted for ${days.toFixed(1)} days
                </a> </li>`
        })
        $('#unaccepted_contacts').html(unaccepted_contacts_html)

        // assigned to contact accept
        let accepted_contacts_html = ``
        let avg_contact_accept = 0
        response.times.contact_accepts.forEach(contact=>{
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
        $('#avg_contact_accept').html( avg_contact_accept === 0 ? '-' : (avg_contact_accept / response.times.contact_accepts.length).toFixed(1))

        //contacts assigned with no contact attempt
        let unattemped_contacts_html = ``
        response.times.unattempted_contacts.forEach(contact=>{
          let days = contact.time / 60 / 60 / 24;
          let line =  dt_user_management_localized.translations.no_contact_attempt_time
            .replace('%1$s', contact.name)
            .replace('%2$s', days.toFixed(1))
          unattemped_contacts_html += `<li>
            <a href="${window.wpApiShare.site_url}/contacts/${_.escape(contact.ID)}" target="_blank">
                ${_.escape(line)}
            </a> </li>`
        })
        $('#unattempted_contacts').html(unattemped_contacts_html)

        //contact assigned to contact attempt
        let attempted_contacts_html = ``
        let avg_contact_attempt = 0
        response.times.contact_attempts.forEach(contact=>{
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
        $('#avg_contact_attempt').html( avg_contact_attempt === 0 ? '-' : (avg_contact_attempt / response.times.contact_attempts.length).toFixed(1))

        let update_needed_list_html = ``
        response.update_needed.contacts.forEach(contact=>{
          update_needed_list_html += `<li>
            <a href="${window.wpApiShare.site_url}/contacts/${_.escape(contact.ID)}" target="_blank">
                ${_.escape(contact.post_title)}:  ${_.escape(contact.last_modified_msg)}
            </a>
          </li>`
        })
        $('#update_needed_list').html(update_needed_list_html)


        //Activity history
        let activity_div = $('#activity')
        let activity_html = ``;
        response.user_activity.forEach((a)=>{
          activity_html += `<div>
              <strong>${moment.unix(a.hist_time).format('YYYY-MM-DD')}</strong>
              ${a.object_note}
            </div>`
        })
        activity_div.html(activity_html)

        setup_user_roles(response);
      }).catch(()=>{
        $('#user_modal').foundation('close');
      })
    }

    $('#refresh_cached_data').on('click', function () {
      $('#loading-page').addClass('active')
      makeRequest( "get", `get_users?refresh=1`, null , 'user-management/v1/').then(()=>{
        location.reload()
      })
    })

    $('.user_row').on("click", function () {
      user_id = $(this).data("user")
      open_multiplier_modal(user_id)
    })

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
    let typeaheadTotals = {}
    if (!window.Typeahead['.js-typeahead-location_grid']){
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

    let day_activity_chart = (days_active)=>{
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
  }

  function status_pie_chart(contact_statuses){

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
    chart2.width = am4core.percent(30);
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


})
