jQuery(document).ready(function($) {

  let post_id = window.detailsSettings.post_id
  let post_type = window.detailsSettings.post_type
  let post = window.detailsSettings.post_fields


  //@todo access
  function updateCriticalPath(key) {
    $('#seeker_path').val(key)
    let seekerPathKeys = _.keys(post.seeker_path.default)
    let percentage = (_.indexOf(seekerPathKeys, key) || 0) / (seekerPathKeys.length-1) * 100
    $('#seeker-progress').css("width", `${percentage}%`)
  }

  $('.quick-action-menu').on("click", function () {
    let fieldKey = $(this).data("id")

    let data = {}
    let numberIndicator = $(`span.${fieldKey}`)
    let newNumber = parseInt(numberIndicator.first().text() || "0" ) + 1
    data[fieldKey] = newNumber
    API.update_post('contacts', post_id, data)
    .then(data=>{
      if (fieldKey.indexOf("quick_button")>-1){
        if (_.get(data, "seeker_path.key")){
          updateCriticalPath(data.seeker_path.key)
        }
      }
      // contactUpdated(false) //update needed
    }).catch(err=>{
      console.log("error")
      console.log(err)
    })

    if (fieldKey.indexOf("quick_button")>-1){
      numberIndicator.text(newNumber)
    }
  })

})
