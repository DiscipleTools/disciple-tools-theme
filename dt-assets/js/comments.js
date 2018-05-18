
jQuery(document).ready(function($) {
  let postId = $("#post-id").text()
  let postType = $("#post-type").text()

  let comments = []
  let activity = [] // not guaranteed to be in any particular order
  function post_comment(postId) {
    let commentInput = jQuery("#comment-input")
    let commentButton = jQuery("#add-comment-button")
    getCommentWithMentions(comment=>{
      if (comment) {
        commentButton.toggleClass('loading')
        commentInput.attr("disabled", true)
        commentButton.attr("disabled", true)
        API.post_comment(postType, postId, comment).then(data => {
          commentInput.val("").trigger( "change" )
          commentButton.toggleClass('loading')
          data.comment.date = moment(data.comment.comment_date_gmt + "Z")
          comments.push(data.comment)
          display_activity_comment()
          if ( typeof contactUpdated === "function"){
            contactUpdated(false);
          }
          commentInput.attr("disabled", false)
          commentButton.attr("disabled", false)
          $('textarea.mention').mentionsInput('reset')
        }).catch(err => {
          console.log("error")
          console.log(err)
          jQuery("#errors").append(err.responseText)
        })
      }
    });
  }


  function prepareActivityData(activityData) {
    /* Insert a "created contact" item in the activity, even though it is not
     * stored in the database. It is not stored as an activity in the database,
     * to avoid duplicating data with the post's metadata. */
    let settings = commentsSettings
    const currentContact = settings.post
    let createdDate = moment.utc(currentContact.post_date_gmt, "YYYY-MM-DD HH:mm:ss", true)
    if (_.get(settings, "post_with_fields.created_on")){
      createdDate = moment.utc(settings.post_with_fields.created_on)
    }
    const createdContactActivityItem = {
      hist_time: createdDate.unix(),
      object_note: settings.txt_created.replace("{}", formatDate(createdDate.local())),
      name: settings.contact_author_name,
      user_id: currentContact.post_author,
    }
    activityData.push(createdContactActivityItem)
    if (_.get(settings, "post_with_fields.initial_comments")){
      const initialComments = {
        hist_time: createdDate.unix()+1,
        object_note: settings.post_with_fields.initial_comments,
        name: settings.contact_author_name,
        user_id: currentContact.post_author,
      }
      activityData.push(initialComments)
    }

    activityData.forEach(item => {
      item.date = moment.unix(item.hist_time)
      let field = item.meta_key

      if (field && field.includes("quick_button_")){
        if (contactsDetailsWpApiSettings){
          field = _.get(contactsDetailsWpApiSettings, `contacts_custom_fields_settings[${item.meta_key}].name`)
        }
        item.action = `<a class="revert-activity dt_tooltip" data-id="${item.histid}">
          <img class="revert-arrow-img" src="${commentsSettings.template_dir}/dt-assets/images/undo.svg">
          <span class="tooltiptext">${field || item.meta_key} </span>
        </a>`
      } else {
        item.action = ''
      }
    })
  }

  let commentTemplate = _.template(`
  <div class="activity-block">
    <div>
        <span class="gravatar"><img src="<%- gravatar  %>"/></span>
        <span><strong><%- name %></strong></span>
        <span class="comment-date"> <%- date %> </span>
      </div>
    <div class="activity-text">
    <% _.forEach(activity, function(a){
        if (a.comment){ %>
            <p dir="auto" class="comment-bubble" style="white-space: pre-line"> <%= a.text %> </p>
      <% } else { %>
            <p class="activity-bubble">  <%- a.text %> <% print(a.action) %> </p>
    <%  }
    }); %>
    </div>
  </div>`
  )


  function formatDate(date) {
    return date.format("YYYY-MM-DD h:mm a")
  }

  let current_section = "all"
  function display_activity_comment(section) {
    current_section = section || current_section

    let commentsWrapper = $("#comments-wrapper")
    commentsWrapper.empty()
    let displayed = []
    if (current_section === "all"){
      displayed = _.union(comments, activity)
    } else if (current_section === "comments"){
      displayed = comments
    } else if ( current_section === "activity"){
      displayed = activity
    }
    displayed = _.orderBy(displayed, "date", "desc")
    let array = []

    displayed.forEach(d=>{
      let first = _.first(array)
      let name = d.comment_author || d.name
      let gravatar = d.gravatar || ""
      let obj = {
        name: name,
        date: d.date,
        gravatar,
        text:d.object_note || formatComment(d.comment_content),
        comment: !!d.comment_content,
        action: d.action
      }


      let diff = first ? first.date.diff(obj.date, "hours") : 0
      if (!first || (first.name === name && diff < 1) ){
        array.push(obj)
      } else {
        commentsWrapper.append(commentTemplate({
          name: array[0].name,
          gravatar: array[0].gravatar,
          date:formatDate(array[0].date),
          activity: array
        }))
        array = [obj]
      }
    })
    if (array.length > 0){
      commentsWrapper.append(commentTemplate({
        gravatar: array[0].gravatar,
        name: array[0].name,
        date:formatDate(array[0].date),
        activity: array
      }))
    }
  }


  /**
   * Comments and activity
   */
  $( document ).ajaxComplete(function(event, xhr, settings) {
    if (settings && settings.type && (settings.type === "POST" || settings.type === "DELETE")){
      refreshActivity()
    }
  });

  let refreshActivity = ()=>{
    get_all();
  }

  let formatComment = (comment=>{
    if(comment){
      let mentionRegex = /\@\[(.*?)\]\((.+?)\)/g
      comment = comment.replace(mentionRegex, (match, text, id)=>{
        return `<a>@${text}</a>`
      })
      let linkRegex = /\[(.*?)\]\((.+?)\)/g
      comment = comment.replace(linkRegex, (match, text, url)=>{
        if (text.includes("http") && !url.includes("http")){
          [url, text] = [text, url]
        }
        return `<a href="${url}">${text}</a>`
      })
        let urlRegex = /(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g
        comment = comment.replace(urlRegex, (match, ext)=>{
            let url = match
            if(match.indexOf("@") == -1) {
                if (match.indexOf("http") == 0 && match.indexOf("www.") == -1) {
                    url = match
                }
                else if (match.indexOf("http") == -1 && match.indexOf("www.") == 0) {
                    url = "http://" + match
                }
                else if (match.indexOf("www.") == -1) {
                    url = "http://www." + match
                }
                return `<a href="${url}" rel="noopener noreferrer" target="_blank">${match}</a>`
            }
            return match
        })

    }
    return comment
  })

  function get_all() {
    $.when(
      API.get_comments(postType, postId),
      API.get_activity(postType, postId)
    ).then(function(commentDataStatusJQXHR, activityDataStatusJQXHR) {
      const commentData = commentDataStatusJQXHR[0];
      const activityData = activityDataStatusJQXHR[0];
      commentData.forEach(comment => {
        comment.date = moment(comment.comment_date_gmt + "Z")
        comment.comment_content = _.escape(comment.comment_content)
      })
      comments = commentData
      activity = activityData
      prepareActivityData(activity)
      display_activity_comment("all")
    }).catch(err => {
      console.error(err);
      jQuery("#errors").append(err.responseText)
    })
  }
  get_all();


  jQuery('#add-comment-button').on('click', function () {
    post_comment(postId)
  })

  $('#comment-activity-tabs').on("change.zf.tabs", function () {
    var tabId = $('#comment-activity-tabs').find('.tabs-title.is-active').data('tab');
    display_activity_comment(tabId)
  })

  $('textarea.mention').mentionsInput({
    onDataRequest:function (mode, query, callback) {
      $('#comment-input').addClass('loading-gif')
      API.search_users(query).then(responseData=>{
        $('#comment-input').removeClass('loading-gif')
        let data = []
        responseData.forEach(user=>{
          data.push({id:user.ID, name:user.name, type:postType, avatar:user.avatar})
          callback.call(this, data);
        })
      })
    },
    templates : {
      mentionItemSyntax : function (data) {
        return `[${data.value}](${data.id})`
      }
    },
    showAvatars: true,
    minChars: 0
  });

  let getMentionedUsers = (callback)=>{
    $('textarea.mention').mentionsInput('getMentions', function(data) {
      callback(data);
    });
  }

  let getCommentWithMentions = (callback)=>{
    $('textarea.mention').mentionsInput('val', function(text) {
      callback(text);
    });
  }

  //
  $(document).on('click', '.revert-activity', function () {
    let id = $(this).data('id')
    $("#revert-modal").foundation('open')
    $("#confirm-revert").data("id", id)
    API.get_single_activity(postType, postId, id).then(a => {
      let field = a.meta_key
      if (contactsDetailsWpApiSettings){
        field = _.get(contactsDetailsWpApiSettings, `contacts_custom_fields_settings[${a.meta_key}].name`)
      }

      $(".revert-field").html(field || a.meta_key)
      $(".revert-current-value").html(a.meta_value)
      $(".revert-old-value").html(a.old_value || 0)
    })
  })

  // confirm going back to the old version on the activity
  $('#confirm-revert').on("click", function () {
    let id = $(this).data('id')
    API.revert_activity(postType, postId, id).then(contactResponse => {
      refreshActivity()
      $("#revert-modal").foundation('close')
      if (typeof refresh_quick_action_buttons === 'function'){
        refresh_quick_action_buttons(contactResponse)
      }
    })
  })

});
