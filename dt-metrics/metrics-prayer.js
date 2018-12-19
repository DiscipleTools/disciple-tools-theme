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
            <div class="cell medium-4">
                <button class="button" style="float:right;" onclick="printElem('prayer-list-print')" ><i class="fi-print"></i> Print</button>
            </div>
        </div>
        <hr style="padding:0;">
        <div class="grid-x grid-padding-x grid-padding-y" id="prayer-list-print">
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
                    <div class="cell" id="list-3-section">
                        <strong>Baptisms</strong><br>
                       <div id="list-3"><img src="${dt.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" /></div>
                    </div>
                    <div class="cell" id="list-1-section">
                        <strong>Meetings</strong><br>
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
                    <div class="cell" id="list-2-section">
                        <strong>Meetings</strong><br>
                       <div id="list-2"><img src="${dt.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" /></div>
                    </div>
                </div>
            </div>
        </div>
        `)

    function list_spinners() {
        jQuery('#list-1').empty().html(`<img src="${dt.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" />`)
        jQuery('#list-2').empty().html(`<img src="${dt.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" />`)
        jQuery('#list-3').empty().html(`<img src="${dt.theme_uri}/dt-assets/images/ajax-loader.gif" width="20px" />`)
    }

    window.get_lists = function get_lists( ) {
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
        let list3 = ''

        if ( prayer_list.praise_meetings.length > 0 ) {
            jQuery.each( prayer_list.praise_meetings, function(i,v) {
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
            jQuery('#list-1').empty().html(list1)
        } else {
            jQuery('#list-1-section').hide()
        }

        if ( prayer_list.request_meetings.length > 0 ) {
            jQuery.each(prayer_list.request_meetings, function (i, v) {
                let message = ``
                if (v.type === 'scheduled') {
                    message = `has a meeting scheduled.`
                } else if (v.type === 'attempted') {
                    message = `needs to respond to connection attempts by worker.`
                }

                let location = ''
                if (v.location_name) {
                    location = `(${v.location_name})`
                }
                list2 += `<a href="/contacts/${v.id}">${v.text}</a> ${message} ${location}<br>`
            })
            jQuery('#list-2').empty().html(list2)
        } else {
            jQuery('#list-2-section').hide()
        }

        if ( prayer_list.baptisms.length > 0 ) {
            jQuery.each(prayer_list.baptisms, function (i, v) {

                let location = ''
                if (v.location_name) {
                    location = `(${v.location_name})`
                }
                list3 += `<a href="/contacts/${v.id}">${v.text}</a> was baptized. ${location}<br>`
            })
            jQuery('#list-3').empty().html(list3)
        } else {
            jQuery('#list-3-section').hide()
        }
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

function printElem(elem)
{
    let today = new Date();
    let start = new Date();
    let days = jQuery('#days').val()
    start.setDate(today.getDate() - days);

    let mywindow = window.open('', 'PRINT', 'height=1200,width=800');
    let div = jQuery('#'+elem+' a')

    div.contents().unwrap();

    mywindow.document.write('<html><head><title>Prayer List</title>');
    mywindow.document.write('</head><body >');
    mywindow.document.write('<h1>Prayer List</h1>');
    mywindow.document.write(`List covers: ${start.getMonth()}-${start.getDate()}-${start.getFullYear()} to ${today.getMonth()}-${today.getDate()}-${today.getFullYear()}`);
    mywindow.document.write(document.getElementById(elem).innerHTML);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    mywindow.print();
    mywindow.close();

    window.get_lists()

    return true;
}