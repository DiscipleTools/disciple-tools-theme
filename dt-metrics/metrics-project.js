jQuery(document).ready(function() {

    if( ! window.location.hash || '#project_overview' === window.location.hash  ) {
        project_overview()
    }
    if( '#project_timeline' === window.location.hash  ) {
        project_timeline()
    }
    if( '#project_critical_path' === window.location.hash  ) {
        project_critical_path()
    }
    if( '#project_outreach' === window.location.hash  ) {
        project_outreach()
    }
    if( '#project_follow_up' === window.location.hash  ) {
        project_follow_up()
    }
    if( '#project_training' === window.location.hash  ) {
        project_training()
    }
    if( '#project_multiplication' === window.location.hash  ) {
        project_multiplication()
    }

})

function project_overview() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let label = dtMetricsProject.data.translations

    console.log( sourceData )

    chartDiv.empty().html(`
        <span class="section-header">`+ label.title_overview +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" class="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                <p><span class="section-subheader">`+ label.title_contacts +`</span></p>
                <div class="grid-x">
                    <div class="medium-4 cell center">
                        <h4>`+ label.title_total_contacts +`<br><span id="total_contacts">0</span></h4>
                    </div>
                    <div class="medium-4 cell center left-border-grey">
                        <h4>`+ label.title_waiting_on_accept +`<br><span id="needs_accepted">0</span></h4>
                    </div>
                    <div class="medium-4 cell center left-border-grey">
                        <h4>`+ label.title_waiting_on_update +`<br><span id="updates_needed">0</span></h4>
                    </div>
                </div>
            </div>
            <div class="cell">
                <div id="my_contacts_progress" style="height: 350px;"></div>
            </div>
            <div class="cell">
            <br>
                <div class="cell center callout">
                    <p><span class="section-subheader">`+ label.title_project_groups +`</span></p>
                    <div class="grid-x">
                        <div class="medium-4 cell center">
                            <h4>`+ label.title_total_groups +`<br><span id="total_groups">0</span></h4>
                        </div>
                        <div class="medium-4 cell center left-border-grey">
                            <h4>`+ label.title_needs_training +`<br><span id="needs_training">0</span></h4>
                        </div>
                        <!--<div class="medium-4 cell center left-border-grey">
                            <h4>`+ label.title_generations +`<br><span id="generations">0</span></h4>
                        </div>-->
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
                        <span class="section-subheader">`+ label.title_group_types +`</span>
                        <div id="group_types" style="height: 400px;"></div>
                    </div>
                    <div class="cell medium-6">
                        <div id="group_generations" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
            
            <div class="cell">
            <hr>
                <div id="streams" style="height: 500px;"></div>
            </div>
        </div>
        `)

    let hero = sourceData.hero_stats
    jQuery('#total_contacts').html( numberWithCommas( hero.total_contacts ) )
    jQuery('#needs_accepted').html( numberWithCommas( hero.needs_accepted ) )
    jQuery('#updates_needed').html( numberWithCommas( hero.updates_needed ) )

    jQuery('#total_groups').html( numberWithCommas( hero.total_groups ) )
    jQuery('#needs_training').html( numberWithCommas( hero.needs_training ) )
    // jQuery('#generations').html( numberWithCommas( hero.generations ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});

    google.charts.setOnLoadCallback(drawMyContactsProgress);
    google.charts.setOnLoadCallback(drawMyGroupHealth);
    google.charts.setOnLoadCallback(drawGroupTypes);
    google.charts.setOnLoadCallback(drawGroupGenerations);
    google.charts.setOnLoadCallback(drawStreams);

    function drawMyContactsProgress() {

        let data = google.visualization.arrayToDataTable( sourceData.contacts_progress );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            hAxis: {
                title: label.label_number_of_contacts,
            },
            title: label.label_follow_up_progress,
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
                title: label.label_groups,
                format: '0',
            },
            hAxis: {

                format: '0',
            },
            title: label.label_group_needs_training,
            legend: {position: "none"},
            colors: ['green' ],
        };

        let chart = new google.visualization.ColumnChart(document.getElementById('my_groups_health'));

        chart.draw(data, options);
    }

    function drawGroupTypes() {
        let data = google.visualization.arrayToDataTable( sourceData.group_types );

        let options = {
            legend: 'bottom',
            pieSliceText: 'groups',
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
            fontSize: '20',
        };

        let chart = new google.visualization.PieChart(document.getElementById('group_types'));
        chart.draw(data, options);
    }

    function drawGroupGenerations() {

        let data = google.visualization.arrayToDataTable( sourceData.group_generations );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            vAxis: {
                title: label.label_generations,
                format: '0',
            },
            hAxis: {
                title: label.label_groups_by_type,
                format: '0',
            },
            title: label.title_generations,
            legend: { position: 'bottom', maxLines: 3 },
            isStacked: true,
            colors: [ 'lightgreen', 'limegreen', 'darkgreen' ],
        };

        let chart = new google.visualization.BarChart(document.getElementById('group_generations'));
        chart.draw(data, options);
    }

    function drawStreams() {

        let data = google.visualization.arrayToDataTable( sourceData.streams );

        let options = {
            chartArea: {
                left: '10%',
                top: '10%',
                width: "85%",
                height: "75%" },
            vAxis: {
                title: label.label_streams,
                format: '0',
            },
            hAxis: {
                title: label.label_generations,
                format: '0',
            },
            title: "Streams",
            legend: {position: "none"},
            colors: ['limegreen' ],
        };

        let chart = new google.visualization.ColumnChart(document.getElementById('streams'));
        chart.draw(data, options);
    }

    new Foundation.Reveal(jQuery('.dt-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( `+ label.label_stats_as_of +` )</span> 
            <a onclick="refresh_stats_data( 'show_zume_groups' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+dtMetricsProject.theme_uri+`/dt-assets/images/ajax-loader.gif" /></span> 
            </div>`)
}

function project_timeline() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let label = dtMetricsProject.data.translations

    chartDiv.empty().html(`
        <span class="section-header">`+ sourceData.translations.title_timeline +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <br>
        <hr>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell">
                <div class="page">
                  <div class="page__demo">
                    <div class="main-container page__container">
                      <div class="timeline">
                      </div>
                    </div>
                  </div>
                </div>
                <style>
                .timeline{
                          --uiTimelineMainColor: var(--timelineMainColor, #222);
                          --uiTimelineSecondaryColor: var(--timelineSecondaryColor, #fff);
                        
                          position: relative;
                          padding-top: 3rem;
                          padding-bottom: 3rem;
                        }
                        
                        .timeline:before{
                          content: "";
                          width: 4px;
                          height: 100%;
                          background-color: var(--uiTimelineMainColor);
                        
                          position: absolute;
                          top: 0;
                        }
                        
                        .timeline__group{
                          position: relative;
                        }
                        
                        .timeline__group:not(:first-of-type){
                          margin-top: 4rem;
                        }
                        
                        .timeline__month{
                          padding: .5rem 1.5rem;
                          color: var(--uiTimelineSecondaryColor);
                          background-color: var(--uiTimelineMainColor);
                        
                          position: absolute;
                          left: 0;
                          top: 0;
                        }
                        
                        .timeline__box{
                          position: relative;
                        }
                        
                        .timeline__box:not(:last-of-type){
                          margin-bottom: 40px;
                        }
                        
                        .timeline__box:before{
                          content: "";
                          width: 100%;
                          height: 2px;
                          background-color: var(--uiTimelineMainColor);
                        
                          position: absolute;
                          left: 0;
                          z-index: -1;
                        }
                        
                        .timeline__date{
                          min-width: 65px;
                          position: absolute;
                          left: 0;
                        
                          box-sizing: border-box;
                          padding: .5rem 1.5rem;
                          text-align: center;
                        
                          background-color: var(--uiTimelineMainColor);
                          color: var(--uiTimelineSecondaryColor);
                        }
                        
                        .timeline__day{
                          font-size: 2rem;
                          font-weight: 700;
                          display: block;
                        }
                        
                        
                        
                        .timeline__post{
                          padding: 1.5rem 3rem;
                          margin-left: 2rem;
                          border-radius: 2px;
                          border-left: 3px solid var(--uiTimelineMainColor);
                          box-shadow: 0 1px 3px 0 rgba(0, 0, 0, .12), 0 1px 2px 0 rgba(0, 0, 0, .24);
                          background-color: var(--uiTimelineSecondaryColor);
                        }
                        
                        @media screen and (min-width: 641px){
                        
                          .timeline:before{
                            left: 40px;
                          }
                        
                          .timeline__group{
                            padding-top: 55px;
                          }
                        
                          .timeline__box{
                            padding-left: 80px;
                          }
                        
                          .timeline__box:before{
                            top: 50%;
                            transform: translateY(-50%);  
                          }  
                        
                          .timeline__date{
                            top: 50%;
                            margin-top: -27px;
                          }
                        }
                        
                        @media screen and (max-width: 640px){
                        
                          .timeline:before{
                            left: 0;
                          }
                        
                          .timeline__group{
                            padding-top: 40px;
                          }
                        
                          .timeline__box{
                            padding-left: 20px;
                            padding-top: 70px;
                          }
                        
                          .timeline__box:before{
                            top: 90px;
                          }    
                        
                          .timeline__date{
                            top: 0;
                          }
                        }
                        
                        .timeline{
                          --timelineMainColor: #4557bb;
                          font-size: 16px;
                        }
                        
                        @media screen and (min-width: 768px){
                        
                          
                        }
                        
                        @media screen and (max-width: 767px){
                        
                          
                        }
                        
                        /*
                        * demo page
                        */
                        
                        @media screen and (min-width: 768px){
                        
                          
                        }
                        
                        @media screen and (max-width: 767px){
                        
                          
                        }
                        
                        body{
                          /*font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Open Sans, Ubuntu, Fira Sans, Helvetica Neue, sans-serif;*/
                          /*font-size: 1.6rem;*/
                          /*color: #222;*/
                          /**/
                          /*background-color: #f0f0f0;*/
                          margin: 0;
                          /*-webkit-overflow-scrolling: touch;   */
                          /*overflow-y: scroll;*/
                        }
                        
                        p{
                          margin-top: 0;
                          margin-bottom: 1.5rem;
                          line-height: 1.5;
                        }
                        
                        p:last-child{
                          margin-bottom: 0;
                        }
                        
                        .page{
                          min-height: 100vh;
                          display: flex;
                          flex-direction: column;
                          justify-content: space-around;
                        }
                        
                        .page__demo{
                          flex-grow: 1;
                        }
                        
                        .main-container{
                          max-width: 960px;
                          padding-left: 2rem;
                          padding-right: 2rem;
                        
                          margin-left: auto;
                          margin-right: auto;
                        }
                        
                        .page__container{
                          padding-top: 0px;
                          padding-bottom: 30px;
                          max-width: 800px;
                        }
                        
                        .footer{
                          padding-top: 1rem;
                          padding-bottom: 1rem;
                          text-align: center;  
                          font-size: 1.4rem;
                        }
                        
                        .footer__link{
                          text-decoration: none;
                          color: inherit;
                        }
                        
                        @media screen and (min-width: 361px){
                        
                          .footer__container{
                            display: flex;
                            justify-content: space-between;
                          }
                        }
                        
                        @media screen and (max-width: 360px){
                        
                          .melnik909{
                            display: none;
                          } 
                        }
                </style>
            </div>
        </div>
        `)

    let timeline = jQuery('.timeline')
    jQuery.each( sourceData.timeline, function( index, value) {
        let id = index.replace(/\W/g,'_')
        timeline.append(`<div id="`+id+`" class="timeline__group"></div>`)

        jQuery('#'+id).append(`<span class="timeline__month">` + index + `</span>`)

        jQuery.each( value, function( i, v ) {
            let dayContentId = id + `_` + v.day
            jQuery('#'+id).append(`
                          <div class="timeline__box">
                            <div class="timeline__date">
                                <span class="timeline__day">
                                        ` + v.day + `
                                </span>
                            </div>
                            <div class="timeline__post">
                              <div class="timeline__content">
                                <p id="` + dayContentId + `"></p>
                              </div>
                            </div>
                          </div>`)

            jQuery.each( v.content, function( line, list ) {
                jQuery('#' + dayContentId ).append( '<strong>' + list.count + '</strong> ' + list.tag + '<br>' )
            })
        })
    })

    new Foundation.Reveal(jQuery('.dt-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( stats as of  )</span> 
            <a onclick="refresh_stats_data( 'show_zume_groups' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+dtMetricsProject.theme_uri+`/dt-assets/images/ajax-loader.gif" /></span> 
            </div>`)
}

function project_critical_path() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let label = dtMetricsProject.data.translations

    console.log( sourceData )

    chartDiv.empty().html(`
        <span class="section-header">`+ label.title_critical_path +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <br>
        <div class="grid-x grid-padding-x">
            <div class="cell">
                <div id="my_critical_path" style="height: 750px;"></div>
            </div>
        </div>
        `)

    let hero = sourceData.hero_stats
    jQuery('#total_contacts').html( numberWithCommas( hero.total_contacts ) )
    jQuery('#updates_needed').html( numberWithCommas( hero.updates_needed ) )
    jQuery('#attempts_needed').html( numberWithCommas( hero.attempts_needed ) )

    jQuery('#total_groups').html( numberWithCommas( hero.total_groups ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});

    google.charts.setOnLoadCallback(drawCriticalPath);

    function drawCriticalPath() {

        let data = google.visualization.arrayToDataTable( sourceData.critical_path );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            title: "Critical Path (Jan 1 - May 10)",
            legend: { position: "none"},
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_critical_path'));
        chart.draw(data, options);
    }

    new Foundation.Reveal(jQuery('.dt-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( stats as of  )</span> 
            <a onclick="refresh_stats_data( 'show_zume_groups' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+dtMetricsProject.theme_uri+`/dt-assets/images/ajax-loader.gif" /></span> 
            </div>`)
}

function project_outreach() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let label = dtMetricsProject.data.translations

    console.log( sourceData )

    chartDiv.empty().html(`
        <span class="section-header">`+ sourceData.translations.title_outreach +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                These are outreach activities that are collecting contacts. Media lead generation. Other lead generation.
            </div>
            <div class="cell">
            </div>
            
        </div>
        `)

    let hero = sourceData.hero_stats
    jQuery('#total_contacts').html( numberWithCommas( hero.total_contacts ) )
    jQuery('#updates_needed').html( numberWithCommas( hero.updates_needed ) )
    jQuery('#attempts_needed').html( numberWithCommas( hero.attempts_needed ) )

    jQuery('#total_groups').html( numberWithCommas( hero.total_groups ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});



    new Foundation.Reveal(jQuery('.dt-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( stats as of  )</span> 
            <a onclick="refresh_stats_data( 'show_zume_groups' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+dtMetricsProject.theme_uri+`/dt-assets/images/ajax-loader.gif" /></span> 
            </div>`)
}

function project_follow_up() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let label = dtMetricsProject.data.translations

    console.log( sourceData )

    chartDiv.empty().html(`
        <span class="section-header">`+ sourceData.translations.title_follow_up +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                These are follow-up activities statistics.
            </div>
            <div class="cell">
            Recently Accepted
            Waiting to be Accepted
            
            </div>
            
        </div>
        `)

    let hero = sourceData.hero_stats
    jQuery('#total_contacts').html( numberWithCommas( hero.total_contacts ) )
    jQuery('#updates_needed').html( numberWithCommas( hero.updates_needed ) )
    jQuery('#attempts_needed').html( numberWithCommas( hero.attempts_needed ) )

    jQuery('#total_groups').html( numberWithCommas( hero.total_groups ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});

    google.charts.setOnLoadCallback(drawCriticalPath);

    function drawCriticalPath() {

        let data = google.visualization.arrayToDataTable( sourceData.critical_path );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            title: "Critical Path",
            legend: {position: "none"},
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_critical_path'));
        chart.draw(data, options);
    }

    new Foundation.Reveal(jQuery('.dt-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( stats as of  )</span> 
            <a onclick="refresh_stats_data( 'show_zume_groups' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+dtMetricsProject.theme_uri+`/dt-assets/images/ajax-loader.gif" /></span> 
            </div>`)
}

function project_training() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let label = dtMetricsProject.data.translations

    console.log( sourceData )

    chartDiv.empty().html(`
        <span class="section-header">`+ sourceData.translations.title_training +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                This is coaching, group training, and group health.
            </div>
            <div class="cell">
            </div>
            
        </div>
        `)

    let hero = sourceData.hero_stats
    jQuery('#total_contacts').html( numberWithCommas( hero.total_contacts ) )
    jQuery('#updates_needed').html( numberWithCommas( hero.updates_needed ) )
    jQuery('#attempts_needed').html( numberWithCommas( hero.attempts_needed ) )

    jQuery('#total_groups').html( numberWithCommas( hero.total_groups ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});

    google.charts.setOnLoadCallback(drawCriticalPath);

    function drawCriticalPath() {

        let data = google.visualization.arrayToDataTable( sourceData.critical_path );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            title: "Critical Path",
            legend: {position: "none"},
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_critical_path'));
        chart.draw(data, options);
    }

    new Foundation.Reveal(jQuery('.dt-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( stats as of  )</span> 
            <a onclick="refresh_stats_data( 'show_zume_groups' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+dtMetricsProject.theme_uri+`/dt-assets/images/ajax-loader.gif" /></span> 
            </div>`)
}

function project_multiplication() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#project-menu'));
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsProject.data
    let label = dtMetricsProject.data.translations

    console.log( sourceData )

    chartDiv.empty().html(`
        <span class="section-header">`+ sourceData.translations.title_multiplication +`</span>
        <span style="float:right; font-size:1.5em;color:#3f729b;"><a data-open="dt-project-legend"><i class="fi-info"></i></a></span>
        <div class="medium reveal" id="dt-project-legend" data-reveal>`+ legend() +`<button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button></div>
        <br><br>
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell center callout">
                This includes multiplication statistics.
            </div>
            <div class="cell">
            </div>
            
        </div>
        `)

    let hero = sourceData.hero_stats
    jQuery('#total_contacts').html( numberWithCommas( hero.total_contacts ) )
    jQuery('#updates_needed').html( numberWithCommas( hero.updates_needed ) )
    jQuery('#attempts_needed').html( numberWithCommas( hero.attempts_needed ) )

    jQuery('#total_groups').html( numberWithCommas( hero.total_groups ) )

    // build charts
    google.charts.load('current', {'packages':['corechart', 'bar']});

    google.charts.setOnLoadCallback(drawCriticalPath);

    function drawCriticalPath() {

        let data = google.visualization.arrayToDataTable( sourceData.critical_path );

        let options = {
            bars: 'horizontal',
            chartArea: {
                left: '20%',
                top: '7%',
                width: "75%",
                height: "85%" },
            title: "Critical Path",
            legend: {position: "none"},
        };

        let chart = new google.visualization.BarChart(document.getElementById('my_critical_path'));
        chart.draw(data, options);
    }

    new Foundation.Reveal(jQuery('.dt-project-legend'));

    chartDiv.append(`<hr><div><span class="small grey">( stats as of  )</span> 
            <a onclick="refresh_stats_data( 'show_zume_groups' ); jQuery('.spinner').show();">Refresh</a>
            <span class="spinner" style="display: none;"><img src="`+dtMetricsProject.theme_uri+`/dt-assets/images/ajax-loader.gif" /></span> 
            </div>`)
}

function legend() {
    return `<h2>Chart Legend</h2><hr>
            <dl>
            <dt>Registered</dt><dd>Groups or people who have registered on Zumeproject.com</dd>
            <dt>Engaged</dt><dd>Groups or people who have registered on Zumeproject.com</dd>
            <dt>Trained</dt><dd>Trained groups and people have been through the entire Zúme training.</dd>
            <dt>Active</dt><dd>Active groups and people have finished a session in the last 30 days. Active in month charts measure according to the month listed. It is the same 'active' behavior, but broken up into different time units.</dd>
            <dt>Hours of Training</dt><dd>Hours of completed sessions for groups or people.</dd>
            <dt>Countries</dt><dd>In the overview page, "Countries" counts number of countries with trained groups.</dd>
            <dt>Translations</dt><dd>Translations counts the number of translations installed in ZúmeProject.com.</dd>
            </dl>`
}

function numberWithCommas(x) {
  x = (x || 0).toString();
    let pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(x))
        x = x.replace(pattern, "$1,$2");
    return x;
}
