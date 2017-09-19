/* global jQuery:false, wpApiSettings:false */


let API = {
  get_post: function(type, postId){
    return jQuery.ajax({
      type:"GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  save_field_api: function(type, postId, post_data){
    return jQuery.ajax({
      type:"POST",
      data:JSON.stringify(post_data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  add_item_to_field: function(type, postId, post_data) {
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify(post_data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root +`dt-hooks/v1/${type}/${postId}/details`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
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
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  remove_item_from_field: function(type, postId, fieldKey, valueId) {
    let data = {key: fieldKey, value: valueId}
    return jQuery.ajax({
      type: "DELETE",
      data: JSON.stringify(data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/details`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  post_comment: function(type, postId, comment) {
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({comment}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/comment`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  get_comments: function(type, postId) {
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/comments`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },
  get_activity: function(type, postId) {
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/activity`,
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
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/add-shared`,
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
      url: wpApiSettings.root + `dt-hooks/v1/${type}/${postId}/remove-shared`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
      }
    })
  },


  typeaheadPrefetchPrepare(options){
    options.beforeSend = function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    }
    return options
  },
  typeaheadRemotePrepare(query, options){
    options.url = options.url.replace("%QUERY", query)
    options.beforeSend = function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
    }
    return options
  },
  filterTypeahead(array, existing = []){
    return _.differenceBy(array, existing.map(l=>{
      return {ID:l.ID, name:l.display_name}
    }), "ID")
  },
  defaultFilter(q, sync, async, local, existing=[]) {
    if (q === '') {
      sync(this.filterTypeahead(local.all(), existing));
    }
    else {
      local.search(q, sync, async);
    }
  },
  searchAnyPieceOfWord(d) {
    var tokens = [];
    //the available string is 'name' in your datum
    var stringSize = d.name.length;
    //multiple combinations for every available size
    //(eg. dog = d, o, g, do, og, dog)
    for (var size = 1; size <= stringSize; size++) {
      for (var i = 0; i + size <= stringSize; i++) {
        tokens.push(d.name.substr(i, size));
      }
    }
    return tokens;
  }
}






