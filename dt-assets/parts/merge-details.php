<?php

( function () {

    ?>
    <div class="reveal" id="merge-dupe-edit-modal" style="border-radius:10px; padding:0px; padding-bottom:20px; border: 1px solid #3f729b;;" data-reveal>
        <div class="merge-modal-header" style="background-color:#3f729b; color:white; text-align:center;">
            <h1 style="font-size:1.5rem; padding:10px 0px;"><?php esc_html_e( "Duplicate Contacts", 'disciple_tools' ) ?></h1>
        </div>
        <div class="display-fields" style="padding:10px;">
        </div>
    </div>

    <script type='text/javascript'>
        let possible_duplicates = [];
        let site_url = "<?php echo esc_url( site_url() );?>";
        let contact_id = "<?php echo get_the_ID(); ?>";

        let dup_row = (dupe, dismissed_row = false)=>{
            let html = ``;
            let dups_on_fields = dupe.fields.map(field=>{
              return ( field.field === 'title' ? "Name" : null ) || _.get(window.contactsDetailsWpApiSettings, `contacts_custom_fields_settings[${field.field}].name`) ||
                _.get(window.contactsDetailsWpApiSettings, `channels[${field.field.replace('contact_', '')}].label`)
            })
            let matched_values = dupe.fields.map(f=>f.meta_value)
            html += `<div style='background-color: #f2f2f2; padding:2%; overflow: hidden;'>
                <h5 style='font-weight: bold; color: #3f729b;'>
                <a href="${window.wpApiShare.site_url}/contacts/${_.escape(dupe.ID)}" target=_blank>
                ${ _.escape(dupe.contact.title) }
                <span style="font-weight: normal; font-size:16px"> #${dupe.ID} (${_.get(dupe.contact, "overall_status.label") ||""}) </span>
                </a>
            </h5>`
            html += `<strong><?php esc_html_e( 'Duplicates on', 'disciple_tools' ); ?>: ${dups_on_fields.join( ', ')}</strong><br />`

            _.forOwn(window.contactsDetailsWpApiSettings.channels, (channel, key)=>{
              if ( dupe.contact['contact_' + key] ){
                dupe.contact['contact_' + key].forEach( contact_info=>{
                  html +=`<img src='${_.escape(channel.icon)}'><span ${matched_values.includes(contact_info.value) ? 'style="font-weight:bold;"' : ''}>&nbsp;${_.escape(contact_info.value)}</span>&nbsp;`
                })
              }
            })
            html += `<br>`
            if ( !dismissed_row ){
                html += `<button class='mergelinks' onclick='dismiss(${_.escape(dupe.ID)})' style='float: right; padding-left: 10%;'><a><?php echo esc_html__( "Dismiss", 'disciple_tools' ) ?></a></button>`
            }
            html += `<form action='${site_url}/contacts/mergedetails' method='post'>
                <input type='hidden' name='dt_contact_nonce' value='<?php echo esc_attr( wp_create_nonce() ); ?>'/>
                <input type='hidden' name='currentid' value='${_.escape(contact_id)}'/>
                <input type='hidden' name='dupeid' value='${_.escape(dupe.ID)}'/>
                <button type='submit' style='float:right; padding-left: 10%;'>
                    <a><?php echo esc_html__( 'Merge', 'disciple_tools' ) ?></a>
                </button>
            </form>`

            html += `</div>`
            return html;
        }
        function loadDuplicates() {
            let dups_with_data = possible_duplicates
            if (dups_with_data) {
              let $duplicates = $('#duplicates_list');
              $duplicates.html(
                "<h4 style='text-align: center; font-size: 1.25rem; font-weight: bold; padding:20px 0px 0px; margin-bottom: 0px;'><?php esc_html_e( "Possible Duplicates", 'disciple_tools' ) ?></h4>");

              var unsure_dismiss_html = `<div style='display:inline-block; width: 100%;'>
                <form method='POST' id='form-unsure-dismiss' action='${site_url}/contacts/${contact_id}'>
                <input type='hidden' name='dt_contact_nonce' value='<?php echo esc_attr( wp_create_nonce() );?>'/>
                <input type='hidden' name='id' value='<?php echo get_the_ID(); ?>'/>
                <a style='float: right; margin-left: 10%;' onclick='dismiss_all();'><?php esc_html_e( "Dismiss All", 'disciple_tools' ) ?></a>
                <input type='submit' id='unsure-dismiss-submit' style='display: none;' value='submit'/></form></div>`;

              $duplicates.append(unsure_dismiss_html);

              let already_dismissed = _.get(contact, 'duplicate_data.override', []).map(id=>parseInt(id))

              let html = ``
              dups_with_data.sort((a, b) => a.points > b.points ? -1:1).forEach((dupe) => {
                if (!already_dismissed.includes(parseInt(dupe.ID))) {
                  html += dup_row(dupe)
                }
              })
              $duplicates.append(html);
              let dismissed_html = ``;
              dups_with_data.sort((a, b) => a.points > b.points ? -1:1).forEach((dupe) => {
                if (already_dismissed.includes(parseInt(dupe.ID))) {
                  dismissed_html += dup_row(dupe, true)
                }
              })
              if (dismissed_html) {
                dismissed_html = `<h4 style='text-align: center; font-size: 1.25rem; font-weight: bold; padding:20px 0px 0px; margin-bottom: 0px;'><?php esc_html_e( "Dismissed Duplicates", 'disciple_tools' ) ?></h4>`
                  + dismissed_html
                $duplicates.append(dismissed_html);
              }
            }
        }

        let openedOnce = false
        jQuery(document).ready(function($) {
            $('#merge-dupe-edit-modal').on("open.zf.reveal", function () {
                if ( !openedOnce ){


                  let $display_fields = $("#merge-dupe-edit-modal .display-fields");
                  $display_fields.append("<h4 style='text-align: center; font-size: 1.25rem; font-weight: bold; padding:10px 0px 0px; margin-bottom: 0px;'><?php esc_html_e( "Original Contact", 'disciple_tools' ) ?></h4>");


                  let original_contact_html = `<div style='background-color: #f2f2f2; padding:2%; overflow: hidden;'>
                    <h5 style='font-weight: bold; color: #3f729b;'>
                    <a href="${window.wpApiShare.site_url}/contacts/${_.escape(contact_id)}" target=_blank>
                    ${ _.escape(contact.title) }
                    <span style="font-weight: normal; font-size:16px"> #${contact_id} (${_.get(contact, "overall_status.label") ||""}) </span>
                    </a>
                    </h5>`
                  _.forOwn(window.contactsDetailsWpApiSettings.channels, (channel, key)=>{
                    if ( contact['contact_' + key] ){
                      contact['contact_' + key].forEach( contact_info=>{
                        original_contact_html +=`<img src='${_.escape(channel.icon)}'><span>&nbsp;${_.escape(contact_info.value)}</span>&nbsp;`
                      })
                    }
                  })
                  original_contact_html += `</div>`
                  $display_fields.append(original_contact_html);
                  $display_fields.append(`<div style="display: inline-block" id="duplicates-spinner" class="loading-spinner active"></div>`)

                  $display_fields.append("<div id='duplicates_list'></div>");
                  window.API.get_duplicates_on_post("contacts", contact.ID).done(dups_with_data=> {
                    possible_duplicates = dups_with_data
                    $("#duplicates-spinner").removeClass("active")
                    loadDuplicates();
                  })

                  openedOnce = true;
                }
            })


            let check_dups = (duplicate_data)=>{
                if ( _.get(duplicate_data, "check_dups") === true ){
                  window.API.get_duplicates_on_post("contacts", contact.ID, {include_contacts:false, exact_match:true}).done(dups_with_data=> {
                    if ( dups_with_data.filter(a=>!duplicate_data.override.includes[a.ID])){
                        $('#duplicates').show()
                    }
                  })
                }
            }
            check_dups(contact.duplicate_data)
            window.contactDetailsEvents.subscribe('resetDetails', function(info) {
                check_dups( info.newContactDetails.duplicate_data )
            })
        })


        function dismiss(id) {
          makeRequestOnPosts('GET', `contacts/${window.contactsDetailsWpApiSettings.contact.ID}/dismiss-duplicates`, {'id':id}).then(resp=>{
            window.contactsDetailsWpApiSettings.contact.duplicate_data = resp;
            loadDuplicates()
          })
        }
        function dismiss_all() {
            makeRequestOnPosts('GET', `contacts/${window.contactsDetailsWpApiSettings.contact.ID}/dismiss-duplicates`, {'id':'all'}).then(resp=>{
                window.contactsDetailsWpApiSettings.contact.duplicate_data = resp;
                loadDuplicates()
            })
        }
    </script>
<?php } )(); ?>
