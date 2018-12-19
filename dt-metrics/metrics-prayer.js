jQuery(document).ready(function() {
    console.log( dtMetricsPrayer )
    if( ! window.location.hash || '#prayer_overview' === window.location.hash  ) {
        prayer_overview()
    }
})

function prayer_overview() {
    "use strict";
    jQuery('#metrics-sidemenu').foundation('down', jQuery('#prayer-menu'));
    let chartDiv = jQuery('#chart')
    let dt = dtMetricsPrayer
    let translations = dt.data.translations


    chartDiv.empty().html(`
        <span style="float:right;"><i class="fi-info primary-color"></i> </span>
        
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell medium-2"><span class="section-header">${translations.title_1}</span> </div>
            <div class="cell medium-2">
                <span><select id="days">
                    <option value="7">Last 7 Days</option>
                    <option value="30" selected>Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                    <option value="180">Last 180 Days</option>
                    <option value="365">Last 365 Days</option>
                </select>
                </span>
                
            </div> 
            <div class="cell medium-2">
                <button class="button" id="alias_names" style="width:100%;"  value="1" >Alias Names</button>
            </div>
            <div class="cell medium-2">
                <button class="button hollow" style="width:100%;" id="alias_locations" value="0" >Alias Locations</button>
            </div>
        </div>
        <hr>
        
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell medium-6">
                <div class="grid-x grid-padding-y">
                    <h3 class="section-header">${ translations.title_2 }</h3>
                    <div class="cell center callout" style="display:none;">
                        <div class="grid-x">
                            <div class="medium-4 cell center">
                                <h5>${ translations.title_waiting_on_accept }<br><span id="needs_accepted">0</span></h5>
                            </div>
                            <div class="medium-4 cell center left-border-grey">
                                <h5>${ translations.title_waiting_on_update }<br><span id="updates_needed">0</span></h5>
                            </div>
                            <div class="medium-4 cell center left-border-grey">
                                <h5>${ translations.title_active_contacts }<br><span id="active_contacts">0</span></h5>
                            </div>
                            
                        </div>
                    </div>
                    <div class="cell">
                       <div id="list-1"><img src="${dt.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" /></div>
                    </div>
                </div>
            </div>
            <div class="cell medium-6 left-border-grey">
                <div class="grid-x grid-padding-y">
                    <h3 class="section-header">${ translations.title_3 }</h3>
                    <div class="cell center callout" style="display:none;">
                        <div class="grid-x">
                            <div class="medium-4 cell center">
                                <h5>${ translations.title_waiting_on_accept }<br><span id="needs_accepted">0</span></h5>
                            </div>
                            <div class="medium-4 cell center left-border-grey">
                                <h5>${ translations.title_waiting_on_update }<br><span id="updates_needed">0</span></h5>
                            </div>
                            <div class="medium-4 cell center left-border-grey">
                                <h5>${ translations.title_active_contacts }<br><span id="active_contacts">0</span></h5>
                            </div>
                        </div>
                    </div>
                    <div class="cell">
                       <div id="list-2"><img src="${dt.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" /></div>
                    </div>
                    
                </div>
            </div>
        </div>
        `)

    function list_spinners() {
        jQuery('#list-1').empty().html(`<img src="${dt.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" />`)
        jQuery('#list-2').empty().html(`<img src="${dt.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" />`)
    }

    function get_lists( ) {
        let days = jQuery('#days').val()
        let alias_names = jQuery('#alias_names').val()
        let alias_locations = jQuery('#alias_locations').val()
        jQuery.ajax({
            type: "POST",
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify({"days": days, "alias_name": alias_names, "alias_location": alias_locations } ),
            dataType: "json",
            url: dtMetricsPrayer.root + 'dt/v1/metrics/prayer_list',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', dtMetricsPrayer.nonce);
            },
        })
            .done(function (response) {
                console.log(response)
                add_lists(response)
            })
            .fail(function (err) {
                console.log("error")
                console.log(err)
                jQuery("#errors").append(err.responseText)
            })
    }
    get_lists()

    function add_lists( prayer_list ) {
        let list1 = ''
        let list2 = ''

        jQuery.each( prayer_list.praise, function(i,v) {
            let message = ``
            if ( v.type === 'met' ) {
                message = `had a first meeting.`
            }

            let location = ''
            if ( v.location_name ) {
                location = `(${v.location_name})`
            }
            list1 += `<a href="/contacts/${v.id}">${v.text}</a> ${message} ${location}<br>`
        })

        jQuery.each( prayer_list.request, function(i,v) {
            let message = ``
            if ( v.type === 'scheduled' ) {
                message = `has a meeting scheduled.`
            } else if ( v.type === 'attempted') {
                message = `needs to respond to connection attempts by worker.`
            }

            let location = ''
            if ( v.location_name ) {
                location = `(${v.location_name})`
            }
            list2 += `<a href="/contacts/${v.id}">${v.text}</a> ${message} ${location}<br>`
        })

        jQuery('#list-1').empty().html(list1)
        jQuery('#list-2').empty().html(list2)
    }

    jQuery('#days').change( function() {
        list_spinners()
        get_lists()
    })

    jQuery('#alias_names').click(function() {
        let x = jQuery('#alias_names')
        if( x.hasClass('hollow') ) {
            list_spinners()
            x.removeClass('hollow')
            x.val(1)
            get_lists()
        } else {
            list_spinners()
            x.addClass('hollow')
            x.val(0)
            get_lists()
        }
    })

    jQuery('#alias_locations').click(function() {
        let x = jQuery('#alias_locations')
        if( x.hasClass('hollow') ) {
            list_spinners()
            x.removeClass('hollow')
            x.val(1)
            get_lists()
        } else {
            list_spinners()
            x.addClass('hollow')
            x.val(0)
            get_lists()
        }
    })


}