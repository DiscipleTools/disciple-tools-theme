/* global jQuery:false, wpApiSettings:false */


let API = {
  get_post: function(type, postId){
    return jQuery.ajax({
      type:"GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}`
    })
  },
  save_field_api: function(type, postId, post_data){
    return jQuery.ajax({
      type:"POST",
      data:JSON.stringify(post_data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}`
    })
  },
  add_item_to_field: function(type, postId, post_data) {
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify(post_data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root +`dt-hooks/v1/${type}/${postId}/details`
    })
  },
  update_contact_method_detail: function (type, postId, fieldKey, values) {
    let data = {key: fieldKey, values: values}
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify(data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/details_update`,
    })
  },
  remove_item_from_field: function(type, postId, fieldKey, valueId) {
    let data = {key: fieldKey, value: valueId}
    return jQuery.ajax({
      type: "DELETE",
      data: JSON.stringify(data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/details`
    })
  },
  post_comment: function(type, postId, comment) {
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({comment}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/comment`,
    })
  },
  get_comments: function(type, postId) {
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/comments`,
    })
  },
  get_activity: function(type, postId) {
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/activity`,
    })
  },
  add_shared(type, postId, userId){
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({user_id:userId}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/add-shared`,
    })
  },
  remove_shared(type, postId, userId){
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({user_id:userId}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/remove-shared`,
    })
  }

}






