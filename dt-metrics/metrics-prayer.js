jQuery(document).ready(function() {

    if( ! window.location.hash || '#prayer_overview' === window.location.hash  ) {
        prayer_overview()
    }


})

function prayer_overview() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#prayer-menu'));
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
}