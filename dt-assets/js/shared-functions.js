/* global wpApiShare:false */
_ = _ || window.lodash // make sure lodash is defined so plugins like gutenberg don't break it.
const { __, _x, _n, _nx } = wp.i18n;

jQuery(document).ready(function($) {
// Adds an active state to the top bar navigation
    let ref = "";
    if (wpApiShare && wpApiShare.site_url) {
        ref = window.location.href.replace(wpApiShare.site_url + '/', "");
    } else {
        ref = window.location.pathname
    }
    $(`div.top-bar-left ul.menu [href*=${ref.replace(wpApiShare.site_url, '').split('/')[0]}]`).parent().addClass('active');
})

function makeRequest (type, url, data) {
    const options = {
        type: type,
        contentType: 'application/json; charset=utf-8',
        dataType: 'json',
        url: url.startsWith('http') ? url : `${wpApiShare.root}dt/v1/${url}`,
        beforeSend: xhr => {
            xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
        }
    }

    if (data) {
        options.data = JSON.stringify(data)
    }

    return jQuery.ajax(options)
}

function makeRequestOnPosts (type, url, data) {
  const options = {
    type: type,
    contentType: 'application/json; charset=utf-8',
    dataType: 'json',
    url: url.startsWith('http') ? url : `${wpApiShare.root}dt-posts/v2/${url}`,
    beforeSend: xhr => {
      xhr.setRequestHeader('X-WP-Nonce', wpApiShare.nonce);
    }
  }
  if (data) {
    options.data = JSON.stringify(data)
  }
  return jQuery.ajax(options)
}

window.API = {
    get_post: (post_type, postId) => makeRequestOnPosts('GET', `${post_type}/${postId}`),

    create_post: (post_type, fields) => makeRequestOnPosts('POST', `${post_type}`, fields),

    update_post: (post_type, postId, postData) => makeRequestOnPosts('POST', `${post_type}/${postId}`, postData),

    post_comment: (post_type, postId, comment) => makeRequestOnPosts('POST', `${post_type}/${postId}/comments`, { comment }),

    delete_comment: (post_type, postId, comment_ID) => makeRequestOnPosts('DELETE', `${post_type}/${postId}/comments/${comment_ID}`),

    update_comment: (post_type, postId, comment_ID, comment_content) => makeRequestOnPosts('POST', `${post_type}/${postId}/comments/${comment_ID}`, {  comment: comment_content }),

    get_comments: (post_type, postId) => makeRequestOnPosts('GET', `${post_type}/${postId}/comments`),

    get_activity: (post_type, postId) => makeRequestOnPosts('GET', `${post_type}/${postId}/activity`),

    get_single_activity: (post_type, postId, activityId) => makeRequestOnPosts('GET', `${post_type}/${postId}/activity/${activityId}`),

    get_shared: (post_type, postId)=> makeRequestOnPosts('GET', `${post_type}/${postId}/shares`),

    add_shared: (post_type, postId, userId) => makeRequestOnPosts('POST', `${post_type}/${postId}/shares`, { user_id: userId }),

    remove_shared: (post_type, postId, userId)=> makeRequestOnPosts('DELETE', `${post_type}/${postId}/shares`, { user_id: userId }),

    create_contact: fields => makeRequest('POST', `contact/create`, fields),

    save_field_api: (post_type, postId, postData) => makeRequestOnPosts('POST', `${post_type}/${postId}`, postData),

    revert_activity: (post_type, postId, activityId) => makeRequestOnPosts('GET', `${post_type}/${postId}/revert/${activityId}`),

    create_group: fields => makeRequest('POST', 'group/create', fields),

    search_users: query => makeRequest('GET', `users/get_users?s=${query}`),

    get_filters: () => makeRequest('GET', 'users/get_filters'),

    save_filters: filters => makeRequest('POST', 'users/save_filters', { filters }),

    get_duplicates_on_post: (post_type, postId) => makeRequestOnPosts('GET', `${post_type}/${postId}/duplicates`),

    create_user: user => makeRequest('POST', 'users/create', user),

    transfer_contact: (contactId, siteId) => makeRequest('POST', 'contact/transfer', { contact_id: contactId, site_post_id: siteId }),
}

function handleAjaxError (err) {
    if (_.get(err, "statusText") !== "abortPromise" && err.responseText){
        console.trace("error")
        console.log(err)
        // jQuery("#errors").append(err.responseText)
    }
}

jQuery(document).ajaxComplete((event, xhr, settings) => {
    if (_.get(xhr, 'responseJSON.data.status') === 401) {
        window.location.replace('/login');
    }
}).ajaxError((event, xhr) => {
    handleAjaxError(xhr)
})

jQuery(document).on('click', '.help-button', function () {
    jQuery('#help-modal').foundation('open')
    let section = jQuery(this).data("section")
    jQuery(".help-section").hide()
    jQuery(`#${section}`).show()
})

window.TYPEAHEADS = {
    typeaheadSource : function (field, url) {
        return {
            contacts: {
                display: [ "name", "ID" ],
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
                            if ( typeof typeaheadTotals !== "undefined" ){
                               typeaheadTotals.field = data.total
                            }
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
                    url: wpApiShare.root + 'dt-posts/v2/contacts/compact',
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
  typeaheadPostsSource : function (post_type){
    return {
      contacts: {
        display: [ "name", "ID" ],
        ajax: {
          url: wpApiShare.root + `dt-posts/v2/${post_type}/compact`,
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
      text = `Showing <strong>${_.escape( result.length )}</strong> of <strong>${_.escape( resultCount )}</strong>(${_.escape( query ? 'elements matching ' + query : '' )})`
    } else if (result.length > 0 && query) {
      text = `Showing <strong>${_.escape( result.length )}</strong> items matching ${_.escape( query )}`;
    } else if (result.length > 0) {
      text = `Showing <strong>${_.escape( result.length )}</strong> items`;
    } else {
      text = `No results matching ${_.escape( query )}`
    }
    return text
  },
  contactListRowTemplate: function (query, item){
    let img = item.user ? `<img src="${wpApiShare.template_dir}/dt-assets/images/profile.svg">` : ''
    let statusStyle = item.status === "closed" ? 'style="color:gray"' : ''
    return `<span dir="auto" ${statusStyle}>
      <span class="typeahead-user-row" style="width:20px">${img}</span>
      ${_.escape(item.name)}
      <span dir="auto">(#${_.escape( item.ID )})</span>
    </span>`
    },


    share(post_type, id, v2){
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
                    return window.API.get_shared(post_type, id).then(sharedResult => {
                        return deferred.resolve(sharedResult.map(g => {
                            return {ID: g.user_id, name: g.display_name}
                        }))
                    })
                },
                callback: {
                    onCancel: function (node, item) {
                        $('#share-result-container').html("");
                        window.API.remove_shared(post_type, id, item.ID).catch(err=>{
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
                    window.API.add_shared(post_type, id, item.ID)
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
                    // jQuery("#errors").append(err.responseText)
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
            window.location.reload()
        });
}, 300000); //300000 = five minutes
