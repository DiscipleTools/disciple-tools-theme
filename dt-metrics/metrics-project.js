jQuery(document).ready(function() {
  if( ! window.location.hash || '#project_overview' === window.location.hash  ) {
        project_overview()
    }
    if( '#group_tree' === window.location.hash  ) {
        project_group_tree()
    }
    if( '#baptism_tree' === window.location.hash  ) {
        project_baptism_tree()
    }
    if( '#coaching_tree' === window.location.hash  ) {
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
            <div class="cell">
                <div id="my_groups_health" style="height: 500px;"></div>
            </div>
            <div class="cell">
                <hr>
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
    drawMyGroupHealth();
    drawGroupTypes();
    drawGroupGenerations();

    function drawMyContactsProgress() {
      console.log(sourceData.contacts_progress);
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
