/* global moment:false, _:false, commentsSettings:false */
jQuery(document).ready(function($) {

  let commentPostedEvent = document.createEvent('Event');
  commentPostedEvent.initEvent('comment_posted', true, true);

  let postId = window.detailsSettings.post_id
  let postType = window.detailsSettings.post_type
  let rest_api = window.API

  let comments = []
  let activity = [] // not guaranteed to be in any particular order
  let langcode = document.querySelector('html').getAttribute('lang') ? document.querySelector('html').getAttribute('lang').replace('_', '-') : "en";// get the language attribute from the HTML or default to english if it doesn't exists.

  function post_comment(postId) {
    let commentInput = jQuery("#comment-input")
    let commentButton = jQuery("#add-comment-button")
    let commentType = $('#comment_type_selector').val()
    getCommentWithMentions(comment_plain_text=>{
      if (comment_plain_text) {
        commentButton.toggleClass('loading')
        commentInput.attr("disabled", true)
        commentButton.attr("disabled", true)
        rest_api.post_comment(postType, postId, comment_plain_text, commentType ).then(data => {
          let updated_comment = data.comment || data
          commentInput.val("").trigger( "change" )
          commentButton.toggleClass('loading')
          updated_comment.date = moment(updated_comment.comment_date_gmt + "Z")
          comments.push(updated_comment)
          display_activity_comment()
          // fire comment posted event
          $('#content')[0].dispatchEvent(commentPostedEvent);
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
    const createdContactActivityItem = {
      hist_time: createdDate.unix(),
      object_note: settings.txt_created.replace("{}", window.SHAREDFUNCTIONS.formatDate(createdDate.unix())),
      name: settings.contact_author_name,
      user_id: currentContact.post_author,
    }
    activityData.push(createdContactActivityItem)
    if (window.lodash.get(settings, "post_with_fields.initial_comments")){
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
        if (window.detailsSettings){
          field = window.lodash.get(window.detailsSettings,`post_fields[${item.meta_key}].name`)
        }
        item.action = `<a class="revert-activity dt_tooltip" data-id="${window.lodash.escape( item.histid )}">
          <img class="revert-arrow-img" src="${commentsSettings.template_dir}/dt-assets/images/undo.svg">
          <span class="tooltiptext">${window.lodash.escape( field || item.meta_key )} </span>
        </a>`
      } else {
        item.action = ''
      }
    })

    let tab = $(`[data-id="activity"].tab-button-label`)
    let text = tab.text()
    text = text.substring(0, text.indexOf('(')) || text
    text += ` (${formatNumber(activityData.length, langcode)})`
    tab.text(text)
    tab.parent().parent('.hide').removeClass('hide')
  }
  $(".show-tabs").on("click", function () {
    let id = $(this).attr("id")
    $('input.tabs-section').prop('checked', id === 'show-all-tabs')
    saveTabs()
  })

  /* We use the CSS 'white-space:pre-wrap' and '<div dir=auto>' HTML elements
   * to match the behaviour that the user sees when editing the comment in an
   * input with dir=auto set, especially when using a right-to-left language
   * with multiple paragraphs. */
  let commentTemplate = window.lodash.template(`
  <div class="activity-block">
    <div>
        <span class="gravatar"><img src="<%- gravatar  %>"/></span>
        <span><strong><%- name %></strong></span>
        <span class="comment-date"> <%- date %> </span>
      </div>
    <div class="activity-text">
    <% var is_Comment; var has_Comment_ID; %>
    <% window.lodash.forEach(activity, function(a){
        if (a.comment){ %>
          <% is_Comment = true; %>
            <div dir="auto" class="comment-bubble <%- a.comment_ID %>">
              <div class="comment-text" title="<%- date %>" dir=auto><%= a.text.replace(/\\n/g, '</div><div class="comment-text" dir=auto>') /* not escaped on purpose */ %></div>
            </div>
            <% if ( commentsSettings.google_translate_key !== ""  && is_Comment && !has_Comment_ID && activity[0].comment_type !== 'duplicate' ) { %>
              <div class="translation-bubble" dir=auto></div>
            <% } %>
            <p class="comment-controls">
               <% if ( a.comment_ID ) { %>
                <% has_Comment_ID = true %>
                  <a class="open-edit-comment" data-id="<%- a.comment_ID %>" data-type="<%- a.comment_type %>" style="margin-right:5px">
                      <img src="${commentsSettings.template_dir}/dt-assets/images/edit-blue.svg">
                      ${window.lodash.escape(commentsSettings.translations.edit)}
                  </a>
                  <a class="open-delete-comment" data-id="<%- a.comment_ID %>">
                      <img src="${commentsSettings.template_dir}/dt-assets/images/trash-blue.svg">
                      ${window.lodash.escape(commentsSettings.translations.delete)}
                  </a>
               <% } %>
            </p>
        <% } else { %>
            <p class="activity-bubble" title="<%- date %>">  <%- a.text %> <% print(a.action) %> </p>
        <%  }
    }); %>
    <% if ( commentsSettings.google_translate_key !== ""  && is_Comment && !has_Comment_ID && activity[0].comment_type !== 'duplicate'
    ) { %>
        <a class="translate-button showTranslation">${window.lodash.escape(commentsSettings.translations.translate)}</a>
        <a class="translate-button hideTranslation hide">${window.lodash.escape(commentsSettings.translations.hide_translation)}</a>
        </div>
    <% } %>
    </div>
  </div>`
  )

  $(document).on("click", '.translate-button.showTranslation', function() {
    let combinedArray = [];
    jQuery(this).siblings('.comment-bubble').each(function(index, comment) {
      let sourceText = $(comment).text();
      sourceText = sourceText.replace(/\s+/g, ' ').trim();
      combinedArray[index] = sourceText;
    })

    let translation_bubble = $(this).siblings('.translation-bubble');
    let translation_hide = $(this).siblings('.translate-button.hideTranslation');

    let url = `https://translation.googleapis.com/language/translate/v2?key=${window.lodash.escape(commentsSettings.google_translate_key)}`
    let targetLang;

    if (langcode !== "zh-TW") {
      targetLang = langcode.substr(0,2);
    } else {
      targetLang = langcode;
    }

    function google_translate_fetch(postData, translate_button, arrayStartPos = 0) {
      fetch(url, {
            method: 'POST',
            body: JSON.stringify(postData),
        })
        .then(response => response.json())
        .then((result) => {

          $.each(result.data.translations, function( index, translation ) {
            $(translation_bubble[index + arrayStartPos]).append(translation.translatedText);
          });
          translation_hide.removeClass('hide');
          $(translate_button).addClass('hide');
        })
    }

    if( combinedArray.length <= 128) {
      let postData = {
        "q": combinedArray,
        "target": targetLang
      }
      google_translate_fetch(postData, this);
    } else {
      var i,j,temparray,chunk = 128;
      for (i=0,j=combinedArray.length; i<j; i+=chunk) {
          temparray = combinedArray.slice(i,i+chunk);

          let postData = {
            "q": temparray,
            "target": targetLang
          }
          google_translate_fetch(postData, this, i);
      }
    }

  })

  $(document).on("click", '.translate-button.hideTranslation', function() {
    let translation_bubble = $(this).siblings('.translation-bubble');
    let translate_button = $(this).siblings('.translate-button.showTranslation')

    translation_bubble.empty();
    $(this).addClass('hide');
    translate_button.removeClass('hide');
  })

  $(document).on("click", ".open-delete-comment", function () {
    let id = $(this).data("id")
    $('#comment-to-delete').html($(`.comment-bubble.${id}`).html())
    $('.delete-comment.callout').hide()
    $('#delete-comment-modal').foundation('open')
    $('#confirm-comment-delete').data("id", id)
  })
  $('#confirm-comment-delete').on("click", function () {
    let id = $(this).data("id")
    $(this).toggleClass('loading')
    rest_api.delete_comment( postType, postId, id ).then(response=>{
      $(this).toggleClass('loading')
      if (response){
        $('#delete-comment-modal').foundation('close')
      } else {
        $('.delete-comment.callout').show()
      }
    }).catch(err=>{
      $(this).toggleClass('loading')
      if (window.lodash.get(err, "responseJSON.message")){
        $('.delete-comment.callout').show()
        $('#delete-comment-error').html(err.responseJSON.message)
      }
    })
  })

  $(document).on("click", ".open-edit-comment", function () {
    let id = $(this).data("id")
    let comment_type = $(this).data("type");
    let comment = window.lodash.find(comments, {comment_ID:id.toString()})

    let comment_html = comment.comment_content // eg: "Tom &amp; Jerry"


    /**
     * .DT - while previewing submitted comments, enhance the presentation of special characters with a helper function below
     */

    function unescapeHtml(safe) {
      return safe.replace(/&amp;/g, '&')
          //.replace(/&lt;/g, '<')
          //.replace(/&gt;/g, '>')
          .replace(/&quot;/g, '"')
          .replace(/&#39;/g, "'")
          .replace(/&#039;/g, "'");
    }

    // textarea deos not render HTML, so using window.lodash.unescape is safe. Note that
    // window.lodash.unescape will silently ignore invalid HTML, for instance,
    // window.lodash.unescape("Tom & Jerry") will return "Tom & Jerry"
    $('#comment-to-edit').val(unescapeHtml(comment_html));

    $('#edit_comment_type_selector').val(comment_type);

    $('.edit-comment.callout').hide()
    $('#edit-comment-modal').foundation('open')
    $('#confirm-comment-edit').data("id", id)
  })
  $('#confirm-comment-edit').on("click", function () {
    $(this).toggleClass('loading')
    let id = $(this).data("id")
    let updated_comment = $('#comment-to-edit').val()
    let commentType = $('#edit_comment_type_selector').val();
    rest_api.update_comment( postType, postId, id, updated_comment, commentType).then((response)=>{
      $(this).toggleClass('loading')
      if (response === 1 || response === 0 || response.comment_ID){
        $('#edit-comment-modal').foundation('close')
      } else {
        $('.edit-comment.callout').show()
      }
    }).catch(err=>{
      $(this).toggleClass('loading')
      if (window.lodash.get(err, "responseJSON.message")){
        $('.edit-comment.callout').show()
        $('#edit-comment-error').html(err.responseJSON.message)
      }
    })
  })

  function formatNumber(num, lang) {
    return num.toLocaleString(lang);
  }

  function display_activity_comment() {
    let hiddenTabs = [];
    try {
      hiddenTabs = JSON.parse( window.SHAREDFUNCTIONS.getCookie("dt_activity_comments_hidden_tabs") )
    } catch (e) {}
    hiddenTabs.forEach(tab=>{
      $(`#tab-button-${tab}`).prop('checked', false)
    })
    let commentsWrapper = $("#comments-wrapper")
    commentsWrapper.empty()
    let displayed = []
    if ( !hiddenTabs.includes("activity")){
      displayed = window.lodash.union(displayed, activity)
    }
    comments.forEach(comment=>{
      if (!hiddenTabs.includes(comment.comment_type)){
        displayed.push(comment)
      }
    })
    displayed = window.lodash.orderBy(displayed, "date", "desc")
    let array = []

    displayed.forEach(d=>{
      baptismDateRegex = /\{(\d+)\}+/;

      if (baptismDateRegex.test(d.object_note)) {
        d.object_note = d.object_note.replace(baptismDateRegex, baptismTimestamptoDate);
      }
      let first = window.lodash.first(array)
      let name = d.comment_author || d.name
      let gravatar = d.gravatar || ""
      let obj = {
        name: name,
        date: d.date,
        gravatar,
        text:d.object_note || formatComment(d.comment_content),
        comment: !!d.comment_content,
        comment_ID : d.user_id === commentsSettings.current_user_id ? d.comment_ID : false,
        comment_type : d.comment_type,
        action: d.action
      }


      let diff = first ? first.date.diff(obj.date, "hours") : 0
      if (!first || (first.name === name && diff < 1) ){
        array.push(obj)
      } else {
        commentsWrapper.append(commentTemplate({
          name: array[0].name,
          gravatar: array[0].gravatar,
          date:window.SHAREDFUNCTIONS.formatDate(moment(array[0].date).unix(), true),
          activity: array
        }))
        array = [obj]
      }
    })
    if (array.length > 0){
      commentsWrapper.append(commentTemplate({
        gravatar: array[0].gravatar,
        name: array[0].name,
        date:window.SHAREDFUNCTIONS.formatDate(moment(array[0].date).unix(), true),
        activity: array
      }))
    }
  }

  function baptismTimestamptoDate(match, timestamp) {
    return window.SHAREDFUNCTIONS.formatDate(timestamp)
  }

  /**
   * Comments and activity
   */
  $( document ).ajaxComplete(function(event, xhr, settings) {
    if (settings && settings.type && (settings.type === "POST" || settings.type === "DELETE")){
      if (!settings.url.includes("notifications")){
        refreshActivity()
      }
    }
  });
  $( document ).ajaxSend(function(event, xhr, settings) {
    if (settings && settings.type && (settings.type === "POST" || settings.type === "DELETE")){
      if (!settings.url.includes("notifications")){
        $("#comments-activity-spinner.loading-spinner").addClass("active")
      }
    }
  });

  let refreshActivity = ()=>{
    get_all();
  }

  let formatComment = (comment=>{
    if(comment){
      let mentionRegex = /\@\[(.*?)\]\((.+?)\)/g
      comment = comment.replace(mentionRegex, (match, text, id)=>{
        /* dir=auto means that @ will be put to the left of the name if the
          * mentioned name is LTR, and to the right if the mentioned name is
          * RTL, instead of letting the surrounding dir determine the placement
          * of @ */
        return `<a dir="auto">@${text}</a>`
      })
      let urlRegex = /((href=('|"))|(\[|\()?|(http(s)?:((\/)|(\\))*.))*(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//\\=]*)/g
      comment = comment.replace(urlRegex, (match)=>{
        let url = match
        if(match.indexOf("@") === -1 && match.indexOf("[") === -1 && match.indexOf("(") === -1 && match.indexOf("href") === -1) {
          if (match.indexOf("http") === 0 && match.indexOf("www.") === -1) {
            url = match
          }
          else if (match.indexOf("http") === -1 && match.indexOf("www.") === 0) {
            url = "http://" + match
          }
          else if (match.indexOf("www.") === -1) {
            url = "http://www." + match
          }
          return `<a href="${url}" rel="noopener noreferrer" target="_blank">${match}</a>`
        }
        return match
      })
      let linkRegex = /\[(.*?)\]\((.+?)\)/g
      comment = comment.replace(linkRegex, (match, text, url)=>{
        if (text.includes("http") && !url.includes("http")){
          [url, text] = [text, url]
        }
        url = url.includes('http') ? url : `${window.wpApiShare.site_url}/${window.wpApiShare.post_type}/${url}`
        return `<a href="${url}">${text}</a>`
      })

    }
    return comment
  })

  let getAllPromise = null
  let getCommentsPromise = null
  let getActivityPromise = null
  function get_all() {
    //abort previous promise if it is not finished.
    if (getAllPromise && window.lodash.get(getAllPromise, "readyState") !== 4){
      getActivityPromise.abort()
      getCommentsPromise.abort()
    }
    getCommentsPromise =  rest_api.get_comments(postType, postId)
    getActivityPromise = rest_api.get_activity(postType, postId)
    getAllPromise = $.when(
      getCommentsPromise,
      getActivityPromise
    )
    getAllPromise.then(function(commentDataStatusJQXHR, activityDataStatusJQXHR) {
      $("#comments-activity-spinner.loading-spinner").removeClass("active")
      const commentData = commentDataStatusJQXHR[0].comments;
      const activityData = activityDataStatusJQXHR[0].activity;
      prepareData(commentData, activityData)
    }).catch(err => {
      if ( !window.lodash.get( err, "statusText" ) === "abort" ) {
        console.error(err);
        jQuery("#errors").append(err.responseText)
      }
    })
  }


  let prepareData = function(commentData, activityData){
    let typesCount = {};
    commentData.forEach(comment => {
      comment.date = moment(comment.comment_date_gmt + "Z")

      /* comment_content should be HTML. However, we want to make sure that
       * HTML like "<div>Hello" gets transformed to "<div>Hello</div>", that
       * is, that all tags are closed, so that the comment_content can be
       * included in HTML without any nasty surprises. This is one way to do
       * that. This is not sufficient for malicious input, but hopefully we
       * can trust the contents of the database to have been sanitized
       * thanks to wp_new_comment . */

        // .DT lets strip out the tags provided from the submited comment and treat it as pure text.
       comment.comment_content = $("<div>").text(comment.comment_content).text()

      if (!typesCount[comment.comment_type]){
        typesCount[comment.comment_type] = 0;
      }
      typesCount[comment.comment_type]++;
    })
    $('#comment-activity-tabs .tabs-title').addClass('hide')
    window.lodash.forOwn(typesCount, (val, key)=>{
      let tab = $(`[data-id="${key}"].tab-button-label`)
      let text = tab.text()
      text = text.substring(0, text.indexOf('(')) || text
      text += ` (${formatNumber(val, langcode)})`
      tab.text(text)
      tab.parent().parent('.hide').removeClass('hide')
    })
    comments = commentData
    activity = activityData
    prepareActivityData(activity)
    display_activity_comment("all")
  }
  prepareData( commentsSettings.comments.comments, commentsSettings.activity.activity )


  jQuery('#add-comment-button').on('click', function () {
    post_comment(postId)
  })

  $('#comment-activity-tabs .tabs-section').on("change", function () {
    saveTabs()
  })
  let saveTabs = ()=>{
    let hiddenTabs = $('#comment-activity-tabs .tabs-section:not(:checked)')
    let hiddenTabIds = [];
    hiddenTabs.each((i, e)=>{
      hiddenTabIds.push($(e).data("id"))
    })
    document.cookie = `dt_activity_comments_hidden_tabs=${JSON.stringify(hiddenTabIds)};path=/;expires=Fri, 31 Dec 9999 23:59:59 GMT"`
    display_activity_comment()
  }


  let searchUsersPromise = null

  $('textarea.mention').mentionsInput({
    onDataRequest:function (mode, query, callback) {
      $('#comment-input').addClass('loading-gif')
      if ( searchUsersPromise && window.lodash.get(searchUsersPromise, 'readyState') !== 4 ){
        searchUsersPromise.abort("abortPromise")
      }
      searchUsersPromise = API.search_users(query)
      searchUsersPromise.then(responseData=>{
        $('#comment-input').removeClass('loading-gif')
        let data = []
        responseData.forEach(user=>{
          data.push({id:user.ID, name:user.name, type:postType, avatar:user.avatar})
          callback.call(this, data);
        })
      }).catch(err => { console.error(err) })
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
      if (window.detailsSettings.post_settings){
        field = window.lodash.get(window.detailsSettings, `post_settings.fields[${a.meta_key}].name`)
      }

      $(".revert-field").html(field || a.meta_key)
      $(".revert-current-value").html(a.meta_value)
      $(".revert-old-value").html(a.old_value || 0)
    }).catch(err => { console.error(err) })
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
    }).catch(err => { console.error(err) })
  })

  window.onbeforeunload = function() {
    if ( $('textarea.mention').val() ){
      return true;
    }
  };

});
