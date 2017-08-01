/* global jQuery:false, List:false, wpApiSettings:false */


function save(contactId, fieldKey, fieldValue){
  var data = {}
  var field = jQuery("#" + fieldKey)
  field.addClass("submitting-select-button")
  if (field.hasClass("selected-select-button")){
    fieldValue = 0
  } else {
    field.removeClass("empty-select-button")
    field.addClass("selected-select-button")
    fieldValue = 1
  }
  data[fieldKey] = fieldValue
  jQuery.ajax({
    type:"POST",
    data:JSON.stringify(data),
    contentType: "application/json; charset=utf-8",
    dataType: "text",
    url: wpApiSettings.root + 'dt-hooks/v1/contact/'+contactId,
    beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    },
    success: function(data) {
      field.removeClass("submitting-select-button selected-select-button")
      field.addClass( fieldValue === 0 ? "empty-select-button" : "selected-select-button")
    },
    error: function(err) {
      console.log("error")
      console.log(err)
      jQuery("#errors").text(err.responseText)
      field.removeClass("submitting-select-button selected-select-button")
      field.addClass( fieldValue === 1 ? "empty-select-button" : "selected-select-button")
    },
  })
}
