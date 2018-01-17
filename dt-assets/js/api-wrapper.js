/* global jQuery:false, wpApiSettings:false */


let API = {
  get_post(type, postId){
    return jQuery.ajax({
      type:"GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/${type}/${postId}`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  save_field_api(type, postId, post_data){
    return jQuery.ajax({
      type:"POST",
      data:JSON.stringify(post_data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/${type}/${postId}`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  add_item_to_field(type, postId, post_data) {
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify(post_data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root +`dt/v1/${type}/${postId}/details`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  update_contact_method_detail(type, postId, fieldKey, values) {
    let data = {key: fieldKey, values: values}
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify(data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/${type}/${postId}/details_update`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  remove_item_from_field(type, postId, fieldKey, valueId) {
    let data = {key: fieldKey, value: valueId}
    return jQuery.ajax({
      type: "DELETE",
      data: JSON.stringify(data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/${type}/${postId}/details`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  remove_field(type, postId, fieldKey) {
    let data = {key: fieldKey}
    return jQuery.ajax({
      type: "DELETE",
      data: JSON.stringify(data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/${type}/${postId}/field`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  post_comment(type, postId, comment) {
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({comment}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/${type}/${postId}/comment`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  get_comments(type, postId) {
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/${type}/${postId}/comments`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  get_activity(type, postId) {
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/${type}/${postId}/activity`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  add_shared(type, postId, userId){
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({user_id:userId}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/${type}/${postId}/add-shared`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  remove_shared(type, postId, userId){
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({user_id:userId}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/${type}/${postId}/remove-shared`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },

  create_group(title, created_from_contact_id){
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({title, created_from_contact_id}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/group/create`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  search_users(query){
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt/v1/users/get_users?s=${query}`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  }

}
