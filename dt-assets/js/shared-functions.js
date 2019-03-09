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

  create_group(fields){
    return jQuery.ajax({
      type: "POST",
      data: JSON.stringify(fields),
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
  },
  get_duplicates_on_post(type, postId){
    return jQuery.ajax({
      type:"GET",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      url: wpApiShare.root + `dt/v1/${type}/${postId}/duplicates`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  create_user( user ){
    return jQuery.ajax({
      type:"POST",
      contentType: "application/json; charset=utf-8",
      dataType: "json",
      data: JSON.stringify( user ),
      url: wpApiShare.root + `dt/v1/users/create`,
      beforeSend: function(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
      }
    })
  },
  transfer_contact( contactId, siteId ){
      return jQuery.ajax({
          type:"POST",
          contentType: "application/json; charset=utf-8",
          dataType: "json",
          data: JSON.stringify( { "contact_id": contactId, "site_post_id": siteId } ),
          url: wpApiShare.root + `dt/v1/contact/transfer`,
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
              return data.posts || data
            }
          }
        }
      }
    }
  },
  typeaheadContactsSource : function (){
    return {
      contacts: {
        display: [ "name", "ID" ],
        ajax: {
          url: wpApiShare.root + 'dt/v1/contacts/compact',
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
  typeaheadHelpText : function (resultCount, query, result){
    let text = "";
    if (result.length > 0 && result.length < resultCount) {
      text = `Showing <strong>${result.length}</strong> of <strong>${resultCount}</strong>(${query ? 'elements matching ' + query : ''})`
    } else if (result.length > 0 && query) {
      text = `Showing <strong>${result.length}</strong> items matching ${query}`;
    } else if (result.length > 0) {
      text = `Showing <strong>${result.length}</strong> items`;
    } else {
      text = `No results matching ${query}`
    }
    return text
  },
  contactListRowTemplate: function (query, item){
    let img = item.user ? `<img src="${wpApiShare.template_dir}/dt-assets/images/profile.svg">` : ''
    return `<span dir="auto">
      <span class="typeahead-user-row" style="width:20px">${img}</span>
      ${_.escape(item.name)} 
      <span dir="auto">(#${item.ID})</span>
    </span>`
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

window.SHAREDFUNCTIONS = {
  getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }
}

window.METRICS = {
  setupDatePicker : function (endpoint_url, callback, startDate, endDate) {

    $('.date_range_picker').daterangepicker({
      "showDropdowns": true,
      ranges: {
        'All time': [moment(0),  moment().endOf('year')],
        [moment().format("MMMM YYYY")]: [moment().startOf('month'), moment().endOf('month')],
        [moment().subtract(1, 'month').format("MMMM YYYY")]: [moment().subtract(1, 'month').startOf('month'),
          moment().subtract(1, 'month').endOf('month')],
        [moment().format("YYYY")]: [moment().startOf('year'), moment().endOf('year')],
        [moment().subtract(1, 'year').format("YYYY")]: [moment().subtract(1, 'year').startOf('year'),
          moment().subtract(1, 'year').endOf('year')],
        [moment().subtract(2, 'year').format("YYYY")]: [moment().subtract(2, 'year').startOf('year'),
          moment().subtract(2, 'year').endOf('year')]
      },
      "linkedCalendars": false,
      locale: {
        format: 'YYYY-MM-DD'
      },
      "startDate": startDate || moment(0),
      "endDate": endDate || moment().endOf('year').format('YYYY-MM-DD'),
    }, function(start, end, label) {
      $(".loading-spinner").addClass("active")
        jQuery.ajax({
          type: "GET",
          contentType: "application/json; charset=utf-8",
          dataType: "json",
          url: `${endpoint_url}?start=${start.format('YYYY-MM-DD')}&end=${end.format('YYYY-MM-DD')}`,
          beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
          },
        })
        .done(function (data) {
          $(".loading-spinner").removeClass("active")
          if ( label === "Custom Range" ){
            label = start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY')
          }
          callback(data, label, start, end )
        })
        .fail(function (err) {
          console.log("error")
          console.log(err)
          jQuery("#errors").append(err.responseText)
        })
      // console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
    });
  }
}


// nonce timeout fix
// every 5 minutes will check if nonce timed out
// if it did then it will redirect to login
setInterval(function() {
  //check if timed out
  get_new_notification_count()
  .fail(function(x) {
      window.location.href = wpApiShare.site_url;
  });
}, 300000); //300000 = five minutes
