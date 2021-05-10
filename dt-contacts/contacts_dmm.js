jQuery(document).ready(function($) {

  let post_id = window.detailsSettings.post_id
  let post_type = window.detailsSettings.post_type
  let post = window.detailsSettings.post_fields


  $('.quick-action-menu').on("click", function () {
    let fieldKey = $(this).data("id")

    let data = {}
    let numberIndicator = $(`span.${fieldKey}`)
    let newNumber = parseInt(numberIndicator.first().text() || "0" ) + 1
    data[fieldKey] = newNumber
    API.update_post('contacts', post_id, data).then(()=>{
      record_updated(false)
    })
    .catch(err=>{
      console.log("error")
      console.log(err)
    })

    if (fieldKey.indexOf("quick_button")>-1){
      numberIndicator.text(newNumber)
    }
  })

  // Baptism date
  let modalBaptismDatePicker = $('input#modal-baptism-date-picker');
  modalBaptismDatePicker.datepicker({
    constrainInput: false,
    dateFormat: 'yy-mm-dd',
    onSelect: function (date) {
      API.update_post('contacts', post_id, { baptism_date: date }).then((resp)=>{
        if (this.value) {
          this.value = window.SHAREDFUNCTIONS.formatDate(resp["baptism_date"]["timestamp"]);
        }
      }).catch(handleAjaxError)
    },
    changeMonth: true,
    changeYear: true,
    yearRange: "-20:+10",
  })
  let openBaptismModal = function( newContact ){
    if ( !post.baptism_date || !(post.milestones || []).includes('milestone_baptized') || (post.baptized_by || []).length === 0 ){
      $('#baptism-modal').foundation('open');
      if (!window.Typeahead['.js-typeahead-modal_baptized_by']) {
        $.typeahead({
          input: '.js-typeahead-modal_baptized_by',
          minLength: 0,
          accent: true,
          searchOnFocus: true,
          source: TYPEAHEADS.typeaheadContactsSource(),
          templateValue: "{{name}}",
          template: window.TYPEAHEADS.contactListRowTemplate,
          matcher: function (item) {
            return parseInt(item.ID) !== parseInt(post.ID)
          },
          dynamic: true,
          hint: true,
          emptyTemplate: window.lodash.escape(window.wpApiShare.translations.no_records_found),
          multiselect: {
            matchOn: ["ID"],
            data: function () {
              return (post["baptized_by"] || [] ).map(g=>{
                return {ID:g.ID, name:g.post_title}
              })
            }, callback: {
              onCancel: function (node, item) {
                API.update_post('contacts', post_id, {"baptized_by": {values:[{value:item.ID, delete:true}]}})
                .catch(err => { console.error(err) })
              }
            },
            href: window.lodash.escape( window.wpApiShare.site_url ) + "/contacts/{{ID}}"
          },
          callback: {
            onClick: function (node, a, item) {
              API.update_post('contacts', post_id, {"baptized_by": {values:[{"value":item.ID}]}})
              .catch(err => { console.error(err) })
              this.addMultiselectItemLayout(item)
              event.preventDefault()
              this.hideLayout();
              this.resetInput();
            },
            onResult: function (node, query, result, resultCount) {
              let text = TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
              $('#modal_baptized_by-result-container').html(text);
            },
            onHideLayout: function () {
              $('.modal_baptized_by-result-container').html("");
            },
          },
        });
      }
      if ( window.lodash.get(newContact, "baptism_date.timestamp", 0) > 0){
        modalBaptismDatePicker.datepicker('setDate', moment.unix(newContact['baptism_date']["timestamp"]).format("YYYY-MM-DD"));
        modalBaptismDatePicker.val(window.SHAREDFUNCTIONS.formatDate(newContact['baptism_date']["timestamp"]) )
      }
    }
    post = newContact
  }
  $('#close-baptism-modal').on('click', function () {
    location.reload()
  })

  /**
   * detect if an update is made on the baptized_by field.
   */
  $( document ).on( 'dt_record_updated', function (e, response, request ){
    post = response
    if ( window.lodash.get(request, "baptized_by" ) && window.lodash.get( response, "baptized_by[0]" ) ) {
      openBaptismModal( response )
    }
  })

  /**
   * detect if an update is made on the milestone field for baptized.
   */
  $( document ).on( 'dt_multi_select-updated', function (e, newContact, fieldKey, optionKey, action) {
    if ( optionKey === 'milestone_baptized' && action === 'add' ){
      openBaptismModal(newContact)
    }
  })
  /**
   * If a baptism date is added
   */
  $( document ).on( 'dt_date_picker-updated', function (e, newContact, id, date){
    if (id === 'baptism_date' && newContact.baptism_date && newContact.baptism_date.timestamp) {
      openBaptismModal(newContact)
    }
  })
})
