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
    // if( '#project_locations' === window.location.hash  ) {
    //     project_locations()
    // }

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
    jQuery('#teams').html( numberWithCommas( hero.teams ) )

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
        <span class="section-header">${translations.title_group_tree}</span><hr>
        
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
        url: dtMetricsProject.root + 'dt/v1/group/'+id,
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
        },
    })
        .done(function (data) {
            if( data ) {
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
    let translations = dtMetricsProject.data.translations

    let height = $(window).height()
    let chartHeight = height - ( height * .15 )

    chartDiv.empty().html(`
        <span class="section-header">${translations.title_baptism_tree}</span><hr>
        
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
    let translations = dtMetricsProject.data.translations

    let height = $(window).height()
    let chartHeight = height - ( height * .15 )

    chartDiv.empty().html(`
        <span class="section-header">${translations.title_coaching_tree}</span><hr>
        
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

// function project_locations() {
//     "use strict";
//     let chartDiv = jQuery('#chart')
//     jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
//     let sourceData = dtMetricsProject.data
//     chartDiv.empty().html(`
//         <span class="section-header">${sourceData.translations.title_locations}</span><br><br>
//
//         <div class="grid-x grid-padding-x grid-padding-y">
//             <div class="cell center callout">
//                 <div class="grid-x">
//                     <div class="medium-4 cell center">
//                         <h4>${sourceData.translations.label_total_locations}<br><span id="total_locations">0</span></h4>
//                     </div>
//                     <div class="medium-4 cell center left-border-grey">
//                         <h4>${sourceData.translations.label_active_locations}<br><span id="total_active_locations">0</span></h4>
//                     </div>
//                     <div class="medium-4 cell center left-border-grey">
//                         <h4>${sourceData.translations.label_inactive_locations}<br><span id="total_inactive_locations">0</span></h4>
//                     </div>
//                 </div>
//             </div>
//             <div class="cell">
//                 <span class="section-subheader">${sourceData.translations.title_locations_tree}</span>
//                 <div id="generation_map" class="scrolling-wrapper"><img src="${dtMetricsProject.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" /></div>
//             </div>
//         </div>
//         <div id="modal" class="large reveal" data-reveal data-v-offset="20px"></div>
//
//         `)
//
//     /* Load hero stats */
//     let hero = sourceData.location_hero_stats
//     jQuery('#total_locations').html( numberWithCommas( hero.total_locations ) )
//     jQuery('#total_active_locations').html( numberWithCommas( hero.total_active_locations ) )
//     jQuery('#total_inactive_locations').html( numberWithCommas( hero.total_inactive_locations ) )
//
//     /* Get tree data */
//     jQuery.ajax({
//         type: "POST",
//         contentType: "application/json; charset=utf-8",
//         data:JSON.stringify({ "type": "location" }),
//         dataType: "json",
//         url: dtMetricsProject.root + 'dt/v1/metrics/project/tree/',
//         beforeSend: function(xhr) {
//             xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
//         },
//     })
//         .done(function (data) {
//             if( data ) {
//                 jQuery('#generation_map').empty().html(data)
//                 jQuery('#generation_map li:last-child').addClass('last');
//             }
//         })
//         .fail(function (err) {
//             console.log("error")
//             console.log(err)
//             jQuery("#errors").append(err.responseText)
//         })
//
//     new Foundation.Reveal(jQuery('#modal') )
//
// }
// function open_location_modal_details( id ) {
//     let modal = jQuery('#modal')
//     let spinner = `<img src="${dtMetricsProject.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" />`
//     modal.empty().html(spinner).foundation('open')
//     jQuery.ajax({
//         type: "POST",
//         contentType: "application/json; charset=utf-8",
//         data: JSON.stringify({"id": id } ),
//         dataType: "json",
//         url: dtMetricsProject.root + 'dt/v1/locations/get_location_with_connections/',
//         beforeSend: function(xhr) {
//             xhr.setRequestHeader('X-WP-Nonce', dtMetricsProject.nonce);
//         },
//     })
//         .done(function (data) {
//             if( data ) {
//                 console.log(data)
//                 let columns = 4
//                 let map = ''
//                 if ( data.latitude && data.longitude && data.api_key ) {
//                     map += `<div class="cell medium-3">
//                                 <img src="https://maps.googleapis.com/maps/api/staticmap?center=${data.latitude},${data.longitude}&zoom=${data.zoom}&size=400x500&markers=color:red|${data.latitude},${data.longitude}&key=${data.api_key}"/>
//                             </div>`
//                     columns = 3
//                 }
//
//                 let groups = ''
//                 if ( data.groups ) {
//                     jQuery.each( data.groups, function(i,v) {
//                         groups += `- <a href="/groups/${v.id}">` + v.name + `</a>`
//                         if ( v.type === 'church' ) {
//                             groups += ` <i class="fi-home"></i>`
//                         }
//                         groups += `<br>`
//                     })
//                 }
//
//                 let contacts = ''
//                 if ( data.contacts ) {
//                     jQuery.each( data.contacts, function(i,v) {
//                         contacts += `- <a href="/contacts/${v.id}">` + v.name + `</a><br>`
//                     })
//                 }
//
//                 let workers = ''
//                 if ( data.workers ) {
//                     workers += `<div class="cell medium-${columns}">
//                                 <strong>Workers (${data.total_workers})</strong><br><br>`
//                     jQuery.each( data.workers, function(i,v) {
//                         workers += `- <a href="/contacts/${v.id}">` + v.name + `</a><br>`
//                     })
//                     workers += `</div>`
//                 }
//
//                 let content = `
//                 <div class="grid-x">
//                     <div class="cell"><span class="section-header">${data.post_title}</span><hr style="max-width:100%;"></div>
//
//                     <div class="cell">
//                         <div class="grid-x grid-padding-x grid-padding-y">
//                             ${map}
//                             ${workers}
//                             <div class="cell medium-${columns}">
//                                 <strong>Groups (${data.total_groups})</strong><br><br>
//                                 ${groups}
//                             </div>
//                             <div class="cell medium-${columns}">
//                                 <strong>Contacts (${data.total_contacts})</strong><br><br>
//                                  ${contacts}
//                             </div>
//                         </div>
//                     </div>
//
//                     <div class="cell center">
//                         <hr>
//                         <button data-close aria-label="Close modal" class="button" type="button">
//                             <span aria-hidden="true">Close</span>
//                           </button>
//                     </div>
//                 </div>
//                 <button class="close-button" data-close aria-label="Close modal" type="button">
//                     <span aria-hidden="true">&times;</span>
//                   </button>
//                 `
//
//                 modal.empty().html(content)
//             }
//         })
//         .fail(function (err) {
//             console.log("error")
//             console.log(err)
//             jQuery("#errors").append(err.responseText)
//         })
// }
