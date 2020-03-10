jQuery(document).ready(function() {
    if( ! window.location.hash || '#project_overview' === window.location.hash  ) {
      jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
      project_overview()
    }
    if( '#group_tree' === window.location.hash  ) {
      jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
      project_group_tree()
    }
    if( '#baptism_tree' === window.location.hash  ) {
      jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
      project_baptism_tree()
    }
    if( '#coaching_tree' === window.location.hash  ) {
      jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
      project_coaching_tree()
    }

})

function project_overview() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let translations = dtMetricsProject.data.translations

    chartDiv.empty().html(`
        <div class="cell center">
            <h3>${ translations.title_overview }</h3>
        </div>
        <div class="medium reveal" id="dt-project-legend" data-reveal>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            
            <h3 class="section-header">${ translations.title_contacts }</h3>
            <div class="cell center callout">
                <div class="cell center">
                </div>
                <div class="grid-x">
                    <div class="medium-3 cell center">
                        <h5>${ translations.title_waiting_on_accept }<br><span id="needs_accepted">0</span></h5>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h5>${ translations.title_waiting_on_update }<br><span id="updates_needed">0</span></h5>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h5>${ translations.title_active_contacts }<br><span id="active_contacts">0</span></h5>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h5>${ translations.title_all_contacts }<br><span id="all_contacts">0</span></h5>
                    </div>
                </div>
            </div>
            <div class="cell">
                <div id="my_contacts_progress" style="height: 350px; width=100%"></div>
            </div>
            <div class="cell">
                <div id="status_chart_div" style="height: 500px; width=100%"></div>
            </div>
            <h3 class="section-header" style="margin-top:40px;">${ translations.title_groups }</h3>
            <div class="cell">
                <div class="cell center callout">
                    <div class="grid-x">
                        <div class="medium-4 cell center">
                            <h5>${ translations.title_total_groups }<br><span id="total_groups">0</span></h5>
                        </div>
                        <div class="medium-4 cell center left-border-grey">
                            <h5>${ translations.title_teams }<br><span id="teams">0</span></h5>
                        </div>
                   </div> 
                </div>
            </div>
            <div class="cell" id="my_groups_health_container">
                <div id="my_groups_health" style="height: 500px;"></div>
                <hr>
            </div>
            <div class="cell">
                
                <div class="grid-x">
                    <div class="cell medium-6 center">
                        <div id="group_types" style="height: 400px;"></div>
                    </div>
                    <div class="cell medium-6">
                        <div id="group_generations" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
            
        </div>
        `)

    let hero = sourceData.hero_stats
    jQuery('#active_contacts').html( numberWithCommas( hero.active_contacts ) )
    jQuery('#needs_accepted').html( numberWithCommas( hero.needs_accepted ) )
    jQuery('#updates_needed').html( numberWithCommas( hero.updates_needed ) )
    jQuery('#all_contacts').html( numberWithCommas( hero.total_contacts ) )

    jQuery('#total_groups').html( numberWithCommas( hero.total_groups ) )
    jQuery('#needs_training').html( numberWithCommas( hero.needs_training ) )
    jQuery('#teams').html( numberWithCommas( hero.teams ) )

    // build charts
    drawMyContactsProgress();
    if ( sourceData.preferences.groups.church_metrics ) {
      drawMyGroupHealth();
    } else {
      jQuery('#my_groups_health_container').remove()
    }
    drawGroupTypes();
    drawGroupGenerations();
    draw_status_pie_chart()

    function drawMyContactsProgress() {
      let chart = am4core.create("my_contacts_progress", am4charts.XYChart)
      let title = chart.titles.create()
      title.text = `[bold]${ window.dtMetricsProject.data.translations.label_follow_up_progress }[/]`
      chart.data = sourceData.contacts_progress.reverse()
      let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "label";
      categoryAxis.renderer.grid.template.location = 0;
      categoryAxis.renderer.minGridDistance = 30;

      let valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
      valueAxis.title.text = "Number of contacts"

      let series = chart.series.push(new am4charts.ColumnSeries());
      series.dataFields.valueX = "value";
      series.dataFields.categoryY = "label";
      series.columns.template.tooltipText = "Total: [bold]{valueX}[/]";

      // field value label
      let valueLabel = series.bullets.push(new am4charts.LabelBullet());
      valueLabel.label.text = "{valueX}";
      valueLabel.label.horizontalCenter = "left";
      valueLabel.label.dx = 10;
      valueLabel.label.hideOversized = false;
      valueLabel.label.truncate = false;

    }

    function drawMyGroupHealth() {
      let chart = am4core.create("my_groups_health", am4charts.XYChart);
      chart.data = sourceData.group_health
      let title = chart.titles.create()
      title.text = `[bold]${dtMetricsProject.data.translations.label_group_needs_training}[/]`
      let categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "label";
      categoryAxis.renderer.grid.template.location = 0;
      categoryAxis.renderer.minGridDistance = 20;
      categoryAxis.renderer.labels.template.wrap = true
      categoryAxis.events.on("sizechanged", function(ev) {
        var axis = ev.target;
        var cellWidth = axis.pixelWidth / (axis.endIndex - axis.startIndex);
        axis.renderer.labels.template.maxWidth = cellWidth > 70 ? cellWidth : 70;
        axis.renderer.labels.template.disabled = cellWidth < 70;
      });

      let valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
      valueAxis.min = 0;
      valueAxis.max = 100;
      valueAxis.strictMinMax = true;
      valueAxis.calculateTotals = true;
      valueAxis.renderer.minWidth = 50;
      valueAxis.renderer.labels.template.adapter.add("text", function(text) {
        return text + "%";
      });

      let series1 = chart.series.push(new am4charts.ColumnSeries());
      series1.columns.template.width = am4core.percent(80);
      series1.columns.template.tooltipText = "{name}: {valueY}";
      series1.name = "Practicing";
      series1.dataFields.categoryX = "label";
      series1.dataFields.valueY = "practicing";
      series1.dataFields.valueYShow = "totalPercent";
      series1.dataItems.template.locations.categoryX = 0.5;
      series1.stacked = true;
      series1.tooltip.pointerOrientation = "vertical";

      let series2 = chart.series.push(new am4charts.ColumnSeries());
      series2.stroke = am4core.color("#da7070"); // red
      series2.fill = am4core.color("#da7070"); // red
      series2.columns.template.width = am4core.percent(80);
      series2.columns.template.tooltipText =
        "{name}: {valueY}";
      series2.name = "Not Practicing";
      series2.dataFields.categoryX = "label";
      series2.dataFields.valueY = "remaining";
      series2.dataFields.valueYShow = "totalPercent";
      series2.dataItems.template.locations.categoryX = 0.5;
      series2.stacked = true;
      series2.tooltip.pointerOrientation = "vertical";
      chart.legend = new am4charts.Legend();
    }

    function drawGroupTypes() {
      let chart = am4core.create("group_types", am4charts.PieChart);
      let title = chart.titles.create()
      title.text = `[bold]${dtMetricsProject.data.translations.label_group_types}[/]`
      chart.data = sourceData.group_types
      let pieSeries = chart.series.push(new am4charts.PieSeries());
      pieSeries.dataFields.value = "count";
      pieSeries.dataFields.category = "label";
      pieSeries.labels.template.disabled = true;
      chart.innerRadius = am4core.percent(30);
      chart.legend = new am4charts.Legend();
    }

    function drawGroupGenerations() {
      let chart = am4core.create("group_generations", am4charts.XYChart);
      let title = chart.titles.create()
      title.text = `[bold]${ dtMetricsProject.data.translations.title_generations }[/]`

      chart.data = sourceData.group_generations.reverse()

      let categoryAxis = chart.yAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "generation";
      categoryAxis.renderer.grid.template.location = 0;
      categoryAxis.renderer.labels.template.adapter.add("text", function(text) {
        return dtMetricsProject.data.translations.label_generation + ' ' + text;
      });

      let valueAxis = chart.xAxes.push(new am4charts.ValueAxis());
      valueAxis.renderer.inside = true;
      valueAxis.renderer.labels.template.disabled = true;
      valueAxis.min = 0;

      function createSeries(field, name) {
        let series = chart.series.push(new am4charts.ColumnSeries());
        series.name = name;
        series.dataFields.valueX = field;
        series.dataFields.categoryY = "generation";
        series.stacked = true;
        series.columns.template.width = am4core.percent(60);
        series.columns.template.tooltipText = "[bold]{name}[/]\n {valueX}";
        let labelBullet = series.bullets.push(new am4charts.LabelBullet());
        labelBullet.label.text = "{valueX}";
        labelBullet.locationX = 0.5;
        return series;
      }

      createSeries("pre-group", dtMetricsProject.data.translations.label_pre_group );
      createSeries("group", dtMetricsProject.data.translations.label_group );
      createSeries("church", dtMetricsProject.data.translations.label_church );
      chart.legend = new am4charts.Legend();
    }

    function draw_status_pie_chart(){
      let contact_statuses = sourceData.contact_statuses
      am4core.useTheme(am4themes_animated);

      let container = am4core.create("status_chart_div", am4core.Container);
      container.width = am4core.percent(100);
      container.height = am4core.percent(100);
      container.layout = "horizontal";


      let chart = container.createChild(am4charts.PieChart);
      let title = chart.titles.create()
      title.text = `[bold]${ window.dtMetricsProject.data.translations.title_status_chart }[/]`
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


    new Foundation.Reveal(jQuery('#dt-project-legend'));
}


function numberWithCommas(x) {
  x = (x || 0).toString();
    let pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(x))
        x = x.replace(pattern, "$1,$2");
    return x;
}

function project_group_tree() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let translations = dtMetricsProject.data.translations

    let height = $(window).height()
    let chartHeight = height - ( height * .15 )

    chartDiv.empty().html(`
        <span class="section-header">${ _.escape( translations.title_group_tree ) }</span><hr>
        
        <br clear="all">
        <div class="grid-x grid-padding-x">
        <div class="cell">
             <span>
                <button class="button hollow toggle-singles" id="highlight-active" onclick="highlight_active();">Highlight Active</button>
            </span>
            <span>
                <button class="button hollow toggle-singles" id="highlight-churches" onclick="highlight_churches();">Highlight Churches</button>
            </span>
        </div>
            <div class="cell">
                <div class="scrolling-wrapper" id="generation_map"><img src="${dtMetricsProject.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" /></div>
            </div>
        </div>
        <div id="modal" class="reveal" data-reveal></div>
    `)

    jQuery.ajax({
        type: "POST",
        contentType: "application/json; charset=utf-8",
        data:JSON.stringify({ "type": "groups" }),
        dataType: "json",
        url: dtMetricsProject.root + 'dt/v1/metrics/project/tree/',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
        },
    })
        .done(function (data) {
            if( data ) {
                jQuery('#generation_map').empty().html(data)
                jQuery('#generation_map li:last-child').addClass('last');
            }
        })
        .fail(function (err) {
            console.log("error")
            console.log(err)
            jQuery("#errors").append(err.responseText)
        })

    new Foundation.Reveal(jQuery('#modal'))
}
function open_modal_details( id ) {
    let modal = jQuery('#modal')
    let spinner = `<img src="${dtMetricsProject.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" />`
    modal.empty().html(spinner).foundation('open')
    jQuery.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtMetricsProject.root + 'dt-posts/v2/groups/'+id,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
        },
    })
        .done(function (data) {
            if( data ) {
              let list = '<dt>Members</dt><ul>'
                jQuery.each(data.members, function(i, v)  { list += `<li><a href="/contacts/${_.escape( data.members[i].ID )}">${_.escape( data.members[i].post_title )}</a></li>` } )
                list += '</ul>'
                let content = `
                <div class="grid-x">
                    <div class="cell"><span class="section-header">${_.escape( data.title )}</span><hr style="max-width:100%;"></div>
                    <div class="cell">
                        <dl>
                            <dd><strong>Status: </strong>${_.escape( data.group_status.label )}</dd>
                            <dd><strong>Assigned to: </strong>${_.escape( data.assigned_to['display'] )}</dd>
                            <dd><strong>Total Members: </strong>${_.escape( data.member_count )}</dd>
                            ${list}
                        </dl>
                    </div>
                    <div class="cell center"><hr><a href="/groups/${_.escape( id )}">View Group</a></div>
                </div>
                <button class="close-button" data-close aria-label="Close modal" type="button">
                    <span aria-hidden="true">&times;</span>
                  </button>
                `
                modal.empty().html(content)
            }
        })
        .fail(function (err) {
            console.log("error")
            console.log(err)
            jQuery("#errors").append(err.responseText)
        })

}
function toggle_multiplying_only () {
    let list = jQuery('#generation_map .li-gen-1:not(:has(li.li-gen-2))')
    let button = jQuery('#multiplying-only')
    if( button.hasClass('hollow') ) {
        list.hide()
        button.removeClass('hollow')
    } else {
        button.addClass('hollow')
        list.show()
    }
}

function highlight_active() {
    let list = jQuery('.inactive')
    let button = jQuery('#highlight-active')
    if( button.hasClass('hollow') ) {
        list.addClass('inactive-gray')
        button.removeClass('hollow')
    } else {
        button.addClass('hollow')
        list.removeClass('inactive-gray')
    }
}

function highlight_churches() {
    let list = jQuery('#generation_map span:not(.church)')
    let button = jQuery('#highlight-churches')
    if( button.hasClass('hollow') ) {
        list.addClass('not-church-gray')
        button.removeClass('hollow')
    } else {
        button.addClass('hollow')
        list.removeClass('not-church-gray')
    }
}

function project_baptism_tree() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data

    let height = $(window).height()
    let chartHeight = height - ( height * .15 )
    let translations = dtMetricsProject.data.translations

    chartDiv.empty().html(`
        <span class="section-header">${ translations.title_baptism_tree }</span><hr>
        
        <br clear="all">
        <div class="grid-x grid-padding-x">
            <div class="cell">
                <div class="scrolling-wrapper" id="generation_map"><img src="${dtMetricsProject.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" /></div>
            </div>
        </div>
        <div id="modal" class="reveal" data-reveal></div>
    `)

    jQuery.ajax({
        type: "POST",
        contentType: "application/json; charset=utf-8",
        data:JSON.stringify({ "type": "baptisms" }),
        dataType: "json",
        url: dtMetricsProject.root + 'dt/v1/metrics/project/tree/',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
        },
    })
        .done(function (data) {
            if( data ) {
                jQuery('#generation_map').empty().html(data)
                jQuery('#generation_map li:last-child').addClass('last');
            }
        })
        .fail(function (err) {
            console.log("error")
            console.log(err)
            jQuery("#errors").append(err.responseText)
        })

    new Foundation.Reveal(jQuery('#modal'))
}

function project_coaching_tree() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data

    let height = $(window).height()
    let chartHeight = height - ( height * .15 )

    chartDiv.empty().html(`
        <span class="section-header">${ dtMetricsProject.data.translations.title_coaching_tree }</span><hr>
        
        <br clear="all">
        <div class="grid-x grid-padding-x">
            <div class="cell">
                <div class="scrolling-wrapper" id="generation_map"><img src="${dtMetricsProject.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" /></div>
            </div>
        </div>
        <div id="modal" class="reveal" data-reveal></div>
    `)

    jQuery.ajax({
        type: "POST",
        contentType: "application/json; charset=utf-8",
        data:JSON.stringify({ "type": "coaching" }),
        dataType: "json",
        url: dtMetricsProject.root + 'dt/v1/metrics/project/tree/',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
        },
    })
        .done(function (data) {
            if( data ) {
                jQuery('#generation_map').empty().html(data)
                jQuery('#generation_map li:last-child').addClass('last');
            }
        })
        .fail(function (err) {
            console.log("error")
            console.log(err)
            jQuery("#errors").append(err.responseText)
        })

}
