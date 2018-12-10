jQuery(document).ready(function() {

    if( ! window.location.hash || '#project_overview' === window.location.hash  ) {
        project_overview()
    }
    if( '#project_critical_path' === window.location.hash  ) {
        project_critical_path()
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
    if( '#project_locations' === window.location.hash  ) {
        project_locations()
    }

})

function project_overview() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let translations = dtMetricsProject.data.translations

    console.log( sourceData )

    chartDiv.empty().html(`
        <div class="cell center">
            <h3 >${ translations.title_overview }</h3>
        </div>
        <!--<span class="section-header">${ translations.title_overview }</span>-->
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
                    <!--<p><span class="section-subheader">${ translations.title_project_groups }</span></p>-->
                    <div class="grid-x">
                        <div class="medium-4 cell center">
                            <h5>${ translations.title_total_groups }<br><span id="total_groups">0</span></h5>
                        </div>
                        <div class="medium-4 cell center left-border-grey">
                            <h5>${ translations.title_needs_training }<br><span id="needs_training">0</span></h5>
                        </div>
                        <div class="medium-4 cell center left-border-grey">
                            <h5>${ translations.title_fully_practicing }<br><span id="fully_practicing">0</span></h5>
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
                        <!--<span class="section-subheader">${ translations.title_group_types }</span>-->
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
    jQuery('#fully_practicing').html( numberWithCommas( hero.fully_practicing ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});

    google.charts.setOnLoadCallback(drawMyContactsProgress);
    google.charts.setOnLoadCallback(drawMyGroupHealth);
    google.charts.setOnLoadCallback(drawGroupTypes);
    google.charts.setOnLoadCallback(drawGroupGenerations);

    function drawMyContactsProgress() {
        let formattedData = [ [ 'Step', 'Contacts', {role: 'annotation'} ]]
        sourceData.contacts_progress.forEach(row=>{
          formattedData.push( [row.label, parseInt(row.value), row.value] );

        })
      let data = google.visualization.arrayToDataTable( formattedData );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '15%',
                width: "75%",
                height: "85%" },
            hAxis: {
                title: translations.label_number_of_contacts,
            },
            title: translations.label_follow_up_progress,
            legend: {position: "none"},
        };

      let chart = new google.visualization.BarChart(document.getElementById('my_contacts_progress'));
        chart.draw(data, options);
    }

    function drawMyGroupHealth() {

        let data = google.visualization.arrayToDataTable( sourceData.group_health );

        let options = {
            chartArea: {
                left: '10%',
                top: '10%',
                width: "85%",
                height: "75%" },
            vAxis: {
                format: '0',
            },
            hAxis: {

                format: '0',
            },
            title: translations.label_group_needs_training,
            legend: {position: "bottom"},
            isStacked: true,
        };

        let chart = new google.visualization.ColumnChart(document.getElementById('my_groups_health'));

        chart.draw(data, options);
    }

    function drawGroupTypes() {
        let data = google.visualization.arrayToDataTable( sourceData.group_types );

        let options = {
            legend: 'bottom',
            pieSliceText: 'label',
            pieStartAngle: 135,
            slices: {
                0: { color: 'lightgreen' },
                1: { color: 'limegreen' },
                2: { color: 'darkgreen' },
            },
            pieHole: 0.4,
            chartArea: {
                left: '0%',
                top: '7%',
                width: "100%",
                height: "80%" },
          title: translations.label_group_types,
        };

        let chart = new google.visualization.PieChart(document.getElementById('group_types'));
        chart.draw(data, options);
    }

    function drawGroupGenerations() {

      let formattedData = [sourceData.group_generations[0]]
      sourceData.group_generations.forEach( (row, index)=>{
        if ( index !== 0 ){
          formattedData.push( [row["generation"], row["pre-group"], row["group"], row["church"], ''] )
        }
      })

        let data = google.visualization.arrayToDataTable( formattedData );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            vAxis: {
                format: '0',
            },
            hAxis: {
                title: translations.label_groups_by_type,
                format: '0',
            },
            title: translations.title_generations,
            legend: { position: 'bottom', maxLines: 3 },
            isStacked: true,
            colors: [ 'lightgreen', 'limegreen', 'darkgreen' ],
        };

        let chart = new google.visualization.BarChart(document.getElementById('group_generations'));
        chart.draw(data, options);
    }


    new Foundation.Reveal(jQuery('#dt-project-legend'));
}

function project_critical_path() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let translations = dtMetricsProject.data.translations

    let height = $(window).height()
    let chartHeight = height - ( height * .15 )

    chartDiv.empty().html(`
        <span class="section-header">${translations.title_critical_path}</span>
        <div style="width:fit-content">
        ${translations.label_select_year} 
        <select id="year_select" onchange="change_critical_path_year($(this).val())">
            ${year_list()}
        </select>
        </div>
        
        <br clear="all">
        <div class="grid-x grid-padding-x">
            <div class="cell">
                <div id="dashboard_div">
                    <div id="my_critical_path" style="min-height: 700px; height: ` + chartHeight + `px;"></div>
                    <hr>
                    <div id="filter_div"></div>
                </div>
            </div>
        </div>
    `)


    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar', 'controls']});
    google.charts.setOnLoadCallback(drawCriticalPath);

    new Foundation.Reveal(jQuery('.dt-project-legend'));
}

function drawCriticalPath( cp_data ) {
    let sourceData = dtMetricsProject.data
    let translations = dtMetricsProject.data.translations
    let path_data = []

    if ( cp_data ) {
        path_data = cp_data
    } else {
        path_data = sourceData.critical_path
    }
    let formattedData = [ [ 'Step', 'Contacts', {role: 'annotation'} ]]
    path_data.forEach(row=>{
      formattedData.push( [row.label, parseInt(row.value), row.value] );

    })
    let data = google.visualization.arrayToDataTable( formattedData );
    let dashboard = new google.visualization.Dashboard(
        document.getElementById('dashboard_div')
    );

    let barChart = new google.visualization.ChartWrapper({
        'chartType': 'BarChart',
        'containerId': 'my_critical_path',
        'options': {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            hAxis: { scaleType: 'mirrorLog' },
            title: translations.title_critical_path,
            legend: { position: "none"},
            animation:{
                duration: 400,
                easing: 'out',
            },
        }
    });

    var crit_keys = []
    jQuery.each( path_data, function( index, value ) {
      crit_keys.push( value["label"] )
    })

    let categoryFilter = new google.visualization.ControlWrapper({
        'controlType': 'CategoryFilter',
        'containerId': 'filter_div',
        'options': {
            'filterColumnLabel': 'Step'
        },
        'ui': {
            'allowMultiple': true,
            'caption': "Select Path Step...",
        },
        'state': { 'selectedValues': crit_keys },

    });

    dashboard.bind(categoryFilter, barChart);

    dashboard.draw( data )
}

function year_list() {
    // create array with descending dates
    let i = 0
    let fullDate = new Date()
    let date = fullDate.getFullYear()
    let currentYear = fullDate.getFullYear()
    let options = `<option value="all">${dtMetricsProject.data.translations.label_all_time}</option>`
    while (i < 15) {
        options += `<option value="${date}" ${ date === currentYear && 'selected'}>${date}</option>`;
        i++;
        date--;
    }

    return options
}

function change_critical_path_year( year ) {
    jQuery.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtMetricsProject.root + 'dt/v1/metrics/critical_path_by_year/'+year,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
        },
    })
        .done(function (data) {
            if ( data ) {
                drawCriticalPath( data )
            }
        })
        .fail(function (err) {
            console.log("error")
            console.log(err)
            jQuery("#errors").append(err.responseText)
        })
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
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu')).foundation('down', jQuery('#trees'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let translations = dtMetricsProject.data.translations

    let height = $(window).height()
    let chartHeight = height - ( height * .15 )

    chartDiv.empty().html(`
        <span class="section-header">${translations.title_group_tree}</span><hr>
        
        <br clear="all">
        <div class="grid-x grid-padding-x">
        <div class="cell">
            <span>
                <button class="button hollow toggle-singles" id="multiplying-only" onclick="toggle_multiplying_only();">Multiplying Only</button>
            </span>
             <span>
                <button class="button hollow toggle-singles" id="highlight-active" onclick="highlight_active();">Highlight Active</button>
            </span>
            <span>
                <button class="button hollow toggle-singles" id="highlight-churches" onclick="highlight_churches();">Highlight Churches</button>
            </span>
        </div>
            <div class="cell">
                <div class="scrolling-wrapper" id="generation_map">
                </div>
            </div>
        </div>
        
        <style>
            .scrolling-wrapper {
                overflow-x: scroll;
                overflow-y: hidden;
                white-space: nowrap;
            }
            #generation_map ul {
                list-style: none;
                background: url(${dtMetricsProject.theme_uri}/dt-assets/images/vline.png) repeat-y;
                margin: 0;
                padding: 0;
                margin-left:50px;
            }
           #generation_map ul.ul-gen-0 {
                margin-left: 0;
           }
            #generation_map li {
                margin: 0;
                padding: 0 12px;
                line-height: 20px;
                background: url(${dtMetricsProject.theme_uri}/dt-assets/images/node.png) no-repeat;
                color: #369;
                font-weight: bold;
            }
            #generation_map ul li.last {
                background: #fff url(${dtMetricsProject.theme_uri}/dt-assets/images/lastnode.png) no-repeat;
            }
            #generation_map .li-gen-1 {
                margin-top:.5em;
            }
            .inactive-gray, .inactive-gray a {
                color:lightgray;
            }
            .not-church-gray, .not-church-gray a {
                color:lightgray;
            }
        </style>
        <div id="modal" class="reveal" data-reveal></div>
    `)

    jQuery('#generation_map').html(sourceData.group_generation_tree)
    jQuery('#generation_map li:last-child').addClass('last');


    new Foundation.Reveal(jQuery('#modal'))
    new Foundation.Reveal(jQuery('.dt-project-legend'));
}
function open_modal_details( id ) {
    let modal = jQuery('#modal')
    let spinner = `<img src="${dtMetricsProject.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" />`
    modal.empty().html(spinner).foundation('open')
    jQuery.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtMetricsProject.root + 'dt/v1/group/'+id,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
        },
    })
        .done(function (data) {
            if( data ) {
                console.log(data)
                let list = '<dt>Members</dt><ul>'
                jQuery.each(data.members, function(i, v)  { list += '<li><a href="/contacts/'+data.members[i].ID+'">' + data.members[i].post_title + '</a></li>' } )
                list += '</ul>'
                let content = `
                <div class="grid-x">
                    <div class="cell"><span class="section-header">${data.name}</span><hr style="max-width:100%;"></div>
                    <div class="cell">
                        <dl>
                            <dd><strong>Status: </strong>${data.group_status.label}</dd>
                            <dd><strong>Assigned to: </strong>${data.assigned_to['display']}</dd>
                            <dd><strong>Total Members: </strong>${data.member_count}</dd>
                            ${list}
                        </dl>
                    </div>
                    <div class="cell center"><hr><a href="/groups/${id}">View Group</a></div>
                </div>
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
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu')).foundation('down', jQuery('#trees'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let translations = dtMetricsProject.data.translations

    let height = $(window).height()
    let chartHeight = height - ( height * .15 )

    chartDiv.empty().html(`
        <span class="section-header">${translations.title_baptism_tree}</span><hr>
        
        <br clear="all">
        <div class="grid-x grid-padding-x">
        <div class="cell">
            <button class="button hollow toggle-singles" id="multiplying-only" onclick="toggle_multiplying_only();">Multiplying Only</button>
        </div>
            <div class="cell">
                <div class="scrolling-wrapper" id="generation_map">
                </div>
            </div>
        </div>
        
        <style>
            .scrolling-wrapper {
                overflow-x: scroll;
                overflow-y: hidden;
                white-space: nowrap;
            }
            #generation_map ul {
                list-style: none;
                background: url(${dtMetricsProject.theme_uri}/dt-assets/images/vline.png) repeat-y;
                margin: 0;
                padding: 0;
                margin-left:50px;
            }
           #generation_map ul.ul-gen-0 {
                margin-left: 0;
           }
            #generation_map li {
                margin: 0;
                padding: 0 12px;
                line-height: 20px;
                background: url(${dtMetricsProject.theme_uri}/dt-assets/images/node.png) no-repeat;
                color: #369;
                font-weight: bold;
            }
            #generation_map ul li.last {
                background: #fff url(${dtMetricsProject.theme_uri}/dt-assets/images/lastnode.png) no-repeat;
            }
            #generation_map .li-gen-1 {
                margin-top:.5em;
            }
            
    </style>
    `)

    jQuery('#generation_map').html(sourceData.baptism_generation_tree)
    jQuery('#generation_map li:last-child').addClass('last');

    new Foundation.Reveal(jQuery('.dt-project-legend'));
}

function project_coaching_tree() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let translations = dtMetricsProject.data.translations

    let height = $(window).height()
    let chartHeight = height - ( height * .15 )

    chartDiv.empty().html(`
        <span class="section-header">${translations.title_coaching_tree}</span><hr>
        
        <br clear="all">
        <div class="grid-x grid-padding-x">
        <div class="cell">
            <button class="button hollow toggle-singles" id="multiplying-only" onclick="toggle_multiplying_only();">Multiplying Only</button>
        </div>
            <div class="cell">
                <div class="scrolling-wrapper" id="generation_map">
                </div>
            </div>
        </div>
        
        <style>
            .scrolling-wrapper {
                overflow-x: scroll;
                overflow-y: hidden;
                white-space: nowrap;
            }
            #generation_map ul {
                list-style: none;
                background: url(${dtMetricsProject.theme_uri}/dt-assets/images/vline.png) repeat-y;
                margin: 0;
                padding: 0;
                margin-left:50px;
            }
           #generation_map ul.ul-gen-0 {
                margin-left: 0;
           }
            #generation_map li {
                margin: 0;
                padding: 0 12px;
                line-height: 20px;
                background: url(${dtMetricsProject.theme_uri}/dt-assets/images/node.png) no-repeat;
                color: #369;
                font-weight: bold;
            }
            #generation_map ul li.last {
                background: #fff url(${dtMetricsProject.theme_uri}/dt-assets/images/lastnode.png) no-repeat;
            }
            #generation_map .li-gen-1 {
                margin-top:.5em;
            }
            
    </style>
    `)

    jQuery('#generation_map').html(sourceData.coaching_generation_tree)
    jQuery('#generation_map li:last-child').addClass('last');


    new Foundation.Reveal(jQuery('.dt-project-legend'));
}

function project_locations() {
    "use strict";
    let chartDiv = jQuery('#chart')
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let sourceData = dtMetricsProject.data
    chartDiv.empty().html(`
        <span class="section-header">${sourceData.translations.title_locations}</span><hr>
        
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                <div class="grid-x">
                    <div class="medium-3 cell center">
                        <h4>Total Locations<br><span id="total_locations">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Active Locations<br><span id="total_active_locations">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Inactive Locations<br><span id="total_inactive_locations">0</span></h4>
                    </div>
                    <div class="medium-3 cell center left-border-grey">
                        <h4>Countries/States<br><span id="total_countries">0</span>/<span id="total_states">0</span></h4>
                    </div>
                </div>
            </div>
            <div class="cell">
                <span class="section-subheader">Location Tree</span>
                <div id="location_tree" class="scrolling-wrapper"></div>
            </div>
        </div>
        <div id="modal" class="reveal" data-reveal></div>
        <style>
            .scrolling-wrapper {
                overflow-x: scroll;
                overflow-y: hidden;
                white-space: nowrap;
            }
            #location_tree ul {
                list-style: none;
                background: url(${dtMetricsProject.theme_uri}/dt-assets/images/vline.png) repeat-y;
                margin: 0;
                padding: 0;
                margin-left:50px;
            }
           #location_tree ul.ul-gen-0 {
                margin-left: 0;
           }
            #location_tree li {
                margin: 0;
                padding: 0 12px;
                line-height: 20px;
                background: url(${dtMetricsProject.theme_uri}/dt-assets/images/node.png) no-repeat;
                color: #369;
                font-weight: bold;
            }
            #location_tree ul li.last {
                background: #fff url(${dtMetricsProject.theme_uri}/dt-assets/images/lastnode.png) no-repeat;
            }
            #location_tree .li-gen-1 {
                margin-top:.5em;
            }
        `)

    let hero = sourceData.location_hero_stats
    jQuery('#total_locations').html( numberWithCommas( hero.total_locations ) )
    jQuery('#total_active_locations').html( numberWithCommas( hero.total_active_locations ) )
    jQuery('#total_inactive_locations').html( numberWithCommas( hero.total_inactive_locations ) )
    jQuery('#total_countries').html( numberWithCommas( hero.total_countries ) )

    new Foundation.Reveal(jQuery('#modal'))
    jQuery('#location_tree').empty().html( sourceData.location_tree )


}
function open_location_modal_details( id ) {
    let modal = jQuery('#modal')
    let spinner = `<img src="${dtMetricsProject.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" />`
    modal.empty().html(spinner).foundation('open')
    jQuery.ajax({
        type: "GET",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        url: dtMetricsProject.root + 'dt/v1/group/'+id,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
        },
    })
        .done(function (data) {
            if( data ) {
                console.log(data)
                let content = `
                <div class="grid-x">
                    <div class="cell"><span class="section-header">Name</span><hr style="max-width:100%;"></div>
                    <div class="cell">
                        Roll Up Data
                    </div>
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