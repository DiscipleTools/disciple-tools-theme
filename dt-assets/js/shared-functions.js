/* global jQuery:false, wpApiShare:false */
_ = _ || window.lodash // make sure lodash is defined so plugins like gutenberg don't break it.

jQuery(document).ready(function($) {
// Adds an active state to the top bar navigation
  let ref = "";
  if (wpApiShare && wpApiShare.site_url) {
    ref = window.location.href.replace(wpApiShare.site_url + '/', "");
  } else {
    ref = window.location.pathname
  }
  $(`div.top-bar-left ul.menu [href*=${ref.split('/')[0]}]`).parent().addClass('active');
})
window.API = {
  get_post(type, postId){
    return jQuery.ajax({
      type:"GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  create_contact(fields){
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify(fields),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/contact/create`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },

  save_field_api(type, postId, post_data){
    return jQuery.ajax({
      type:"POST",
      data:JSON.stringify(post_data),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  post_comment(type, postId, comment) {
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({comment}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}/comment`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  delete_comment(type, postId, comment_ID){
    return jQuery.ajax({
      type: "DELETE",
      data: JSON.stringify({comment_ID}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}/comment`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  update_comment(type, postId, comment_ID, comment_content){
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({comment_ID, comment_content}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}/comment/update`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  get_comments(type, postId) {
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}/comments`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  get_activity(type, postId) {
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}/activity`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  get_single_activity(type, postId, activityId) {
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}/activity/${activityId}`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  revert_activity(type, postId, activityId) {
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}/revert/${activityId}`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  get_shared(type, postId){
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}/shared-with`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  add_shared(type, postId, userId){
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({user_id:userId}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}/add-shared`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  remove_shared(type, postId, userId){
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({user_id:userId}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}/remove-shared`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },

  create_group(title, created_from_contact_id, parent_group_id){
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify({title, created_from_contact_id, parent_group_id}),
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/group/create`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  search_users(query){
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/users/get_users?s=${query}`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  get_filters(){
    return jQuery.ajax({
      type: "GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/users/get_filters`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  save_filters( filters ){
    return jQuery.ajax({
      type: "POST",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      data: JSON.stringify( {filters} ),
      url: wpApiShare.root + `dt/v1/users/save_filters`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  }
}

function handelAjaxError(err) {
  if (_.get(err, "statusText") !== "abortPromise" && err.responseText){
    console.trace("error")
    console.log(err)
    jQuery("#errors").append(err.responseText)
  }
}

jQuery( document ).ajaxComplete(function(event, xhr, settings) {
  if (_.get(xhr, "responseJSON.data.status") === 401){
    window.location.replace("/login");
  }
}).ajaxError(function (event, xhr) {
    handelAjaxError(xhr)
  })
jQuery( document ).on("click", ".help-button", function () {
    jQuery('#help-modal').foundation('open');
    let section = jQuery(this).data("section")
    jQuery(".help-section").hide()
    jQuery(`#${section}`).show()
})

window.TYPEAHEADS = {

  typeaheadSource : function (field, url) {
    return {
      contacts: {
        display: "name",
        ajax: {
          url: wpApiShare.root + url,
          data: {
            s: "{{query}}"
          },
          beforeSend: function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
          },
          callback: {
            done: function (data) {
              return data.posts
            }
          }
        }
      }
    }
  },
  typeaheadUserSource : function (field, ur) {
    return {
      users: {
        display: ["name", "user"],
        ajax: {
          url: wpApiShare.root + 'dt/v1/users/get_users',
          data: {
            s: "{{query}}"
          },
          beforeSend: function (xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
          },
          callback: {
            done: function (data) {
              typeaheadTotals["assigned_id"] = data.total || data.length
              return data.posts || data
            }
          }
        }
      }
    }
  },
  typeaheadHelpText : function (resultCount, query, result){
    var text = "";
    if (result.length > 0 && result.length < resultCount) {
      text = "Showing <strong>" + result.length + "</strong> of <strong>" + resultCount + '</strong> ' + (query ? 'elements matching "' + query + '"' : '');
    } else if (result.length > 0 && query) {
      text = 'Showing <strong>' + result.length + '</strong> items matching "' + query + '"';
    } else if (result.length > 0) {
      text = 'Showing <strong>' + result.length + '</strong> items';
    } else {
      text = 'No results matching "' + query + '"';
    }
    return text
  },

  share(type, id){
    return $.typeahead({
      input: '.js-typeahead-share',
      minLength: 0,
      accent: true,
      // searchOnFocus: true,
      source: this.typeaheadSource('share', 'dt/v1/users/get_users'),
      display: "name",
      templateValue: "{{name}}",
      dynamic: true,
      multiselect: {
        matchOn: ["ID"],
        data: function () {
          var deferred = $.Deferred();
          return window.API.get_shared(type, id).then(sharedResult => {
            return deferred.resolve(sharedResult.map(g => {
              return {ID: g.user_id, name: g.display_name}
            }))
          })
        },
        callback: {
          onCancel: function (node, item) {
            $('#share-result-container').html("");
            window.API.remove_shared(type, id, item.ID).catch(err=>{
              Typeahead['.js-typeahead-share'].addMultiselectItemLayout(
                {ID:item.ID, name:item.name}
              )
              $('#share-result-container').html(_.get(err, "responseJSON.message"));
            })
          }
        },
      },
      callback: {
        onClick: function (node, a, item, event) {
          window.API.add_shared(type, id, item.ID)
        },
        onResult: function (node, query, result, resultCount) {
          if (query) {
            let text = window.TYPEAHEADS.typeaheadHelpText(resultCount, query, result)
            $('#share-result-container').html(text);
          }
        },
        onHideLayout: function () {
          $('#share-result-container').html("");
        }
      }
    });
  }
}
