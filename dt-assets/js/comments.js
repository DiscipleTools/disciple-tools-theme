/* global moment:false, _:false, commentsSettings:false */
jQuery(document).ready(function ($) {
  let commentPostedEvent = document.createEvent('Event');
  commentPostedEvent.initEvent('comment_posted', true, true);

  let postId = window.detailsSettings.post_id;
  let postType = window.detailsSettings.post_type;
  let rest_api = window.API;
  let { formatComment } = window.SHAREDFUNCTIONS;

  let comments = [];
  let activity = []; // not guaranteed to be in any particular order
  let langcode = document.querySelector('html').getAttribute('lang')
    ? document.querySelector('html').getAttribute('lang').replace('_', '-')
    : 'en'; // get the language attribute from the HTML or default to english if it doesn't exists.

  function post_comment(postId) {
    let commentInput = jQuery('#comment-input');
    let commentButton = jQuery('#add-comment-button');
    let commentType = $('#comment_type_selector').val();
    getCommentWithMentions((comment_plain_text) => {
      if (comment_plain_text) {
        commentButton.toggleClass('loading');
        commentInput.attr('disabled', true);
        commentButton.attr('disabled', true);
        rest_api
          .post_comment(postType, postId, comment_plain_text, commentType)
          .then((data) => {
            let updated_comment = data.comment || data;
            commentInput.val('').trigger('change');
            commentButton.toggleClass('loading');
            updated_comment.date = window.moment(
              updated_comment.comment_date_gmt + 'Z',
            );
            comments.push(updated_comment);
            display_activity_comment();
            // fire comment posted event
            $('#content')[0].dispatchEvent(commentPostedEvent);
            commentInput.attr('disabled', false);
            commentButton.attr('disabled', false);
            $('textarea.mention').mentionsInput('reset');
          })
          .catch((err) => {
            console.log('error');
            console.log(err);
            jQuery('#errors').append(err.responseText);
          });
      }
    });
  }

  function prepareActivityData(activityData) {
    /* Insert a "created contact" item in the activity, even though it is not
     * stored in the database. It is not stored as an activity in the database,
     * to avoid duplicating data with the post's metadata. */
    let settings = commentsSettings;
    const currentContact = settings.post;
    let createdDate = window.moment.utc(
      currentContact.post_date_gmt,
      'YYYY-MM-DD HH:mm:ss',
      true,
    );
    const createdContactActivityItem = {
      hist_time: createdDate.unix(),
      object_note: window.detailsSettings.translations.created_on.replace(
        '%s',
        window.SHAREDFUNCTIONS.formatDate(createdDate.unix(), true),
      ),
      name: settings.contact_author_name,
      user_id: currentContact.post_author,
    };
    activityData.push(createdContactActivityItem);
    if (window.lodash.get(settings, 'post_with_fields.initial_comments')) {
      const initialComments = {
        hist_time: createdDate.unix() + 1,
        object_note: settings.post_with_fields.initial_comments,
        name: settings.contact_author_name,
        user_id: currentContact.post_author,
      };
      activityData.push(initialComments);
    }

    activityData.forEach((item) => {
      item.date = window.moment.unix(item.hist_time);
      let field = item.meta_key;

      if (field && field.includes('quick_button_')) {
        if (window.detailsSettings) {
          field = window.lodash.get(
            window.detailsSettings,
            `post_settings.fields[${item.meta_key}].name`,
          );
        }
        item.action = `<a class="revert-activity dt-tooltip" data-id="${window.SHAREDFUNCTIONS.escapeHTML(item.histid)}">
          <img class="revert-arrow-img" src="${commentsSettings.template_dir}/dt-assets/images/undo.svg">
          <span class="tooltiptext">${window.SHAREDFUNCTIONS.escapeHTML(field || item.meta_key)} </span>
        </a>`;
      } else {
        item.action = '';
      }
    });

    let tab = $(`[data-id="activity"].tab-button-label`);
    let text = tab.text();
    text = text.substring(0, text.indexOf('(')) || text;
    text += ` (${formatNumber(activityData.length, langcode)})`;
    tab.text(text);
    tab.parent().parent('.hide').removeClass('hide');
  }
  $('.show-tabs').on('click', function () {
    let id = $(this).attr('id');
    $('input.tabs-section').prop('checked', id === 'show-all-tabs');
    saveTabs();
  });

  /* We use the CSS 'white-space:pre-wrap' and '<div dir=auto>' HTML elements
   * to match the behaviour that the user sees when editing the comment in an
   * input with dir=auto set, especially when using a right-to-left language
   * with multiple paragraphs. */
  let commentTemplate = window.lodash.template(`
  <div class="activity-block" >
    <div class="comment-header" style="">
        <div class="gravatar">
        <% if( $.trim( gravatar ) ) { %>
            <img src="<%- gravatar  %>"/>
        <% } else { %>
            <i class="mdi mdi-robot-outline"></i>
        <% } %>
        </div>
        <span><strong><%- name %></strong></span>
        <span class="comment-date"> <%- date %> </span>
      </div>
    <div class="activity-text">
      <% var is_Comment; var has_Comment_ID; %>
      <% window.lodash.forEach(activity, function(a){
        if (a.comment){ %>
          <% is_Comment = true; %>
            <div dir="auto" class="comment-bubble <%- a.comment_ID %>" data-comment-id="<%- a.comment_ID %>">
              <div class="comment-text" title="<%- a.date_formatted %>" dir=auto>
                  <%= a.text.replace(/\\n/g, '<br>') /* not escaped on purpose */ %>
              </div>
            </div>
            <% if ( commentsSettings.google_translate_key !== ""  && is_Comment && !has_Comment_ID && activity[0].comment_type !== 'duplicate' ) { %>
              <div class="translation-bubble" dir=auto></div>
            <% } %>
            <div  class="comment-controls">
              <% if ( a.meta && a.meta.audio_url ) { %>
                <% window.lodash.forEach(a.meta.audio_url, function(meta){ %>
                  <audio controls><source src="<%- meta.value %>" /></audio>
                <% }) %>
              <% } %>
              <div class="comment-reactions">
                <div class="reaction-controls">
                  <button class="icon-button reactions__button" aria-label="Add your reaction" aria-haspopup="menu" role="button" data-toggle="react-to-<%- a.comment_ID %>">
                    <span class="add-reaction-svg"><svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0zM8 0a8 8 0 100 16A8 8 0 008 0zM5 8a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zM5.32 9.636a.75.75 0 011.038.175l.007.009c.103.118.22.222.35.31.264.178.683.37 1.285.37.602 0 1.02-.192 1.285-.371.13-.088.247-.192.35-.31l.007-.008a.75.75 0 111.222.87l-.614-.431c.614.43.614.431.613.431v.001l-.001.002-.002.003-.005.007-.014.019a1.984 1.984 0 01-.184.213c-.16.166-.338.316-.53.445-.63.418-1.37.638-2.127.629-.946 0-1.652-.308-2.126-.63a3.32 3.32 0 01-.715-.657l-.014-.02-.005-.006-.002-.003v-.002h-.001l.613-.432-.614.43a.75.75 0 01.183-1.044h.001z"></path></svg>
                  </button>
                  <div class="dropdown-pane reactions__dropdown" data-position="bottom" data-alignment="right" id="react-to-<%- a.comment_ID %>" data-comment-id="<%- a.comment_ID %>" data-dropdown></div>
                </div>
                <% Object.entries(a.reactions).forEach(([reactionKey, users]) => {
                  if (users.length === 0) return
                  const reactionAlias = reactionKey.replace(/reaction_/, '')
                  const reactionMeta = commentsSettings.reaction_options[reactionAlias]
                  if (!reactionMeta) return // there is no reaction matching this alias, maybe the reactions have been changed
                  let reactionTitle = users.length === 1 ? commentsSettings.translations.reaction_title_1 : commentsSettings.translations.reaction_title_many
                  reactionTitle = reactionTitle.replace('%1$s', users[users.length - 1].name).replace('%2$s', reactionMeta.name)
                  if (users.length > 1) reactionTitle = reactionTitle.replace('%3$s', users.slice(0, users.length - 1).map((user) => user.name).join(', '))
                  const hasOwnReaction = users.map((user) => user.user_id).includes(commentsSettings.current_user_id)
                %>
                  <div class="comment-reaction" title="<%- reactionTitle %>" data-own-reaction="<%- hasOwnReaction %>" data-reaction-value="<%- reactionKey %>" data-comment-id="<%- a.comment_ID %>">
                    <span>
                      <% if (reactionMeta.emoji && reactionMeta.emoji !== '') { %>
                        <%- reactionMeta.emoji %>
                      <% } else { %>
                        <img class="emoji" src="<%- reactionMeta.path %>" >
                      <% } %>
                    </span>
                    <span><%- users.length %></span>
                  </div>
                <% }) %>
              </div>
              <% if ( a.is_own_comment ) { %>
                <% has_Comment_ID = true %>
                  <div class="edit-comment-controls">
                    <a class="open-edit-comment" data-id="<%- a.comment_ID %>" data-type="<%- a.comment_type %>" style="margin-right:5px">
                        <img class="dt-blue-icon" src="${commentsSettings.template_dir}/dt-assets/images/edit.svg">
                        ${window.SHAREDFUNCTIONS.escapeHTML(commentsSettings.translations.edit)}
                    </a>
                    <a class="open-delete-comment" data-id="<%- a.comment_ID %>">
                        <img class="dt-blue-icon" src="${commentsSettings.template_dir}/dt-assets/images/trash.svg">
                        ${window.SHAREDFUNCTIONS.escapeHTML(commentsSettings.translations.delete)}
                    </a>
                  </div>
                <% } %>
              </div>

        <% } else { %>
            <p class="activity-bubble" title="<%- a.date_formatted %>" dir="auto"><%= a.text %> <% print(a.action) /* not escaped on purpose */ %></p>
        <%  }
    }); %>
    <% if ( commentsSettings.google_translate_key !== ""  && is_Comment && !has_Comment_ID && activity[0].comment_type !== 'duplicate'
    ) { %>
        <a class="translate-button showTranslation">${window.SHAREDFUNCTIONS.escapeHTML(commentsSettings.translations.translate)}</a>
        <a class="translate-button hideTranslation hide">${window.SHAREDFUNCTIONS.escapeHTML(commentsSettings.translations.hide_translation)}</a>
        </div>
    <% } %>
    </div>
  </div>`);

  $(document).on('click', '.translate-button.showTranslation', function () {
    let combinedArray = [];
    jQuery(this)
      .siblings('.comment-bubble')
      .each(function (index, comment) {
        let sourceText = $(comment).text();
        sourceText = sourceText.replace(/\s+/g, ' ').trim();
        combinedArray[index] = sourceText;
      });

    let translation_bubble = $(this).siblings('.translation-bubble');
    let translation_hide = $(this).siblings(
      '.translate-button.hideTranslation',
    );

    let url = `https://translation.googleapis.com/language/translate/v2?key=${window.SHAREDFUNCTIONS.escapeHTML(commentsSettings.google_translate_key)}`;
    let targetLang;

    if (langcode !== 'zh-TW') {
      targetLang = langcode.substr(0, 2);
    } else {
      targetLang = langcode;
    }

    function google_translate_fetch(
      postData,
      translate_button,
      arrayStartPos = 0,
    ) {
      fetch(url, {
        method: 'POST',
        body: JSON.stringify(postData),
      })
        .then((response) => response.json())
        .then((result) => {
          $.each(result.data.translations, function (index, translation) {
            $(translation_bubble[index + arrayStartPos]).append(
              translation.translatedText,
            );
          });
          translation_hide.removeClass('hide');
          $(translate_button).addClass('hide');
        });
    }

    if (combinedArray.length <= 128) {
      let postData = {
        q: combinedArray,
        target: targetLang,
      };
      google_translate_fetch(postData, this);
    } else {
      var i,
        j,
        temparray,
        chunk = 128;
      for (i = 0, j = combinedArray.length; i < j; i += chunk) {
        temparray = combinedArray.slice(i, i + chunk);

        let postData = {
          q: temparray,
          target: targetLang,
        };
        google_translate_fetch(postData, this, i);
      }
    }
  });

  $(document).on('click', '.translate-button.hideTranslation', function () {
    let translation_bubble = $(this).siblings('.translation-bubble');
    let translate_button = $(this).siblings(
      '.translate-button.showTranslation',
    );

    translation_bubble.empty();
    $(this).addClass('hide');
    translate_button.removeClass('hide');
  });

  $(document).on('click', '.open-delete-comment', function () {
    let id = $(this).data('id');
    $('#comment-to-delete').html($(`.comment-bubble.${id}`).html());
    $('.delete-comment.callout').hide();
    $('#delete-comment-modal').foundation('open');
    $('#confirm-comment-delete').data('id', id);
  });
  $('#confirm-comment-delete').on('click', function () {
    let id = $(this).data('id');
    $(this).toggleClass('loading');
    rest_api
      .delete_comment(postType, postId, id)
      .then((response) => {
        $(this).toggleClass('loading');
        if (response) {
          $('#delete-comment-modal').foundation('close');
        } else {
          $('.delete-comment.callout').show();
        }
      })
      .catch((err) => {
        $(this).toggleClass('loading');
        if (window.lodash.get(err, 'responseJSON.message')) {
          $('.delete-comment.callout').show();
          $('#delete-comment-error').html(err.responseJSON.message);
        }
      });
  });

  $(document).on('click', '.open-edit-comment', function () {
    let id = $(this).data('id');
    let comment_type = $(this).data('type');
    let comment = window.lodash.find(comments, { comment_ID: id.toString() });

    let comment_html = comment.comment_content; // eg: "Tom &amp; Jerry"

    /**
     * .DT - while previewing submitted comments, enhance the presentation of special characters with a helper function below
     */

    function unescapeHtml(safe) {
      return (
        safe
          .replace(/&amp;/g, '&')
          //.replace(/&lt;/g, '<')
          //.replace(/&gt;/g, '>')
          .replace(/&quot;/g, '"')
          .replace(/&#39;/g, "'")
          .replace(/&#039;/g, "'")
      );
    }

    // textarea deos not render HTML, so using window.lodash.unescape is safe. Note that
    // window.lodash.unescape will silently ignore invalid HTML, for instance,
    // window.lodash.unescape("Tom & Jerry") will return "Tom & Jerry"
    $('#comment-to-edit').val(unescapeHtml(comment_html));

    $('#edit_comment_type_selector').val(comment_type);

    $('.edit-comment.callout').hide();
    $('#edit-comment-modal').foundation('open');
    $('#confirm-comment-edit').data('id', id);
  });
  $('#confirm-comment-edit').on('click', function () {
    $(this).toggleClass('loading');
    let id = $(this).data('id');
    let updated_comment = $('#comment-to-edit').val();
    let commentType = $('#edit_comment_type_selector').val();
    rest_api
      .update_comment(postType, postId, id, updated_comment, commentType)
      .then((response) => {
        $(this).toggleClass('loading');
        if (response === 1 || response === 0 || response.comment_ID) {
          $('#edit-comment-modal').foundation('close');
        } else {
          $('.edit-comment.callout').show();
        }
      })
      .catch((err) => {
        $(this).toggleClass('loading');
        if (window.lodash.get(err, 'responseJSON.message')) {
          $('.edit-comment.callout').show();
          $('#edit-comment-error').html(err.responseJSON.message);
        }
      });
  });

  function formatNumber(num, lang) {
    return num.toLocaleString(lang);
  }

  function display_activity_comment() {
    let hiddenTabs = [];
    try {
      hiddenTabs = JSON.parse(
        window.SHAREDFUNCTIONS.getCookie('dt_activity_comments_hidden_tabs'),
      );
    } catch (e) {}
    hiddenTabs.forEach((tab) => {
      $(`#tab-button-${tab}`).prop('checked', false);
    });
    let commentsWrapper = $('#comments-wrapper');
    commentsWrapper.empty();
    let displayed = [];
    if (!hiddenTabs.includes('activity')) {
      displayed = window.lodash.union(displayed, activity);
    }
    comments.forEach((comment) => {
      if (!hiddenTabs.includes(comment.comment_type)) {
        displayed.push(comment);
      }
    });
    displayed = displayed.sort((a, b) => {
      return (a.sort_date || a.date) < (b.sort_date || b.date) ? 1 : -1;
    });
    let array = [];

    displayed.forEach((d) => {
      let baptismDateRegex = /\{(\d+)\}+/;
      if (baptismDateRegex.test(d.object_note)) {
        if (d.field_type === 'datetime') {
          d.object_note = d.object_note.replace(
            baptismDateRegex,
            formatTimestampToDateTime,
          );
        } else {
          d.object_note = d.object_note.replace(
            baptismDateRegex,
            formatTimestampToDate,
          );
        }
      }
      if (d.object_note) {
        d.object_note = formatComment(d.object_note);
      }
      let first = window.lodash.first(array);
      let name = d.comment_author || d.name;
      let gravatar = d.gravatar || '';
      let obj = {
        name: name,
        date: d.date,
        date_formatted: window.SHAREDFUNCTIONS.formatDate(
          moment(d.date).unix(),
          true,
        ),
        gravatar,
        text: d.object_note || formatComment(d.comment_content),
        comment: !!d.comment_content,
        comment_ID: d.comment_ID,
        is_own_comment: d.user_id === commentsSettings.current_user_id,
        comment_type: d.comment_type,
        action: d.action,
        reactions: d.comment_reactions || {},
        meta: d.comment_meta || {},
      };

      let diff = first ? first.date.diff(obj.date, 'hours') : 0;
      if (!first || (first.name === name && diff < 1)) {
        array.push(obj);
      } else {
        commentsWrapper.append(
          commentTemplate({
            name: array[0].name,
            gravatar: array[0].gravatar,
            date: array[0].date_formatted,
            activity: array,
          }),
        );
        array = [obj];
      }
    });
    if (array.length > 0) {
      commentsWrapper.append(
        commentTemplate({
          gravatar: array[0].gravatar,
          name: array[0].name,
          date: array[0].date_formatted,
          activity: array,
        }),
      );
    }

    document.querySelectorAll('.reactions__dropdown').forEach((element) => {
      const commentId = element.dataset.commentId;
      const emojis = emojiButtons();
      const reactionForm = document.createElement('form');
      reactionForm.classList.add('pick-reaction-form');
      reactionForm.innerHTML = `
        ${emojis}
      `;
      reactionForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formDataEntries = new FormData(e.target).entries();
        const entries = Array.from(formDataEntries);
        //event submitter data is not available in Safari/Webkit browsers only in Chrome/Blink browsers. This checks if that data exists and falls back to the formDataEntries data if it doesn't exists.
        const reaction = e.submitter ? e.submitter.value : entries[0][1];
        rest_api.toggle_comment_reaction(postType, postId, commentId, reaction);
      });
      element.appendChild(reactionForm);
    });
    document
      .querySelectorAll('#comments-wrapper [data-toggle]')
      .forEach((element) => {
        const dropdownId = $(element).data('toggle');
        const dropdownElement = document.querySelector(`#${dropdownId}`);
        element.addEventListener('click', (e) => {
          const style = getComputedStyle(dropdownElement);
          element.toggleAttribute('open');
          if (style.visibility === 'hidden') {
            dropdownElement.style.visibility = 'visible';
            dropdownElement.style.display = 'block';
          } else {
            dropdownElement.style.visibility = 'hidden';
            dropdownElement.style.display = 'none';
          }
        });
      });
    document.querySelectorAll('.comment-reaction').forEach((element) => {
      element.addEventListener('click', (e) => {
        const commentId = e.target.dataset.commentId;
        const reaction = e.target.dataset.reactionValue;
        rest_api.toggle_comment_reaction(postType, postId, commentId, reaction);
      });
    });
  }

  function emojiButtons() {
    const reactions = commentsSettings.reaction_options;
    const emojiContainer = document.createElement('div');
    emojiContainer.classList.add('reactions-emoji-container');
    let emojis = '';
    Object.entries(reactions).forEach(([alias, reaction]) => {
      const reactionValue = `reaction_${alias}`;
      emojis += `
      <button class="add-reaction" type="submit" name="reaction" title="${window.SHAREDFUNCTIONS.escapeHTML(reaction.name)}" value="${reactionValue}">
        <img class="emoji" alt="${window.SHAREDFUNCTIONS.escapeHTML(reaction.name)}" src="${window.SHAREDFUNCTIONS.escapeHTML(reaction.path)}">
      </button>
      `;
    });
    emojiContainer.innerHTML = emojis;
    return emojiContainer.outerHTML;
  }

  function formatTimestampToDate(match, timestamp) {
    return window.SHAREDFUNCTIONS.formatDate(timestamp);
  }
  function formatTimestampToDateTime(match, timestamp) {
    return window.SHAREDFUNCTIONS.formatDate(timestamp, true);
  }
  /**
   * Comments and activity
   */
  $(document).ajaxComplete(function (event, xhr, settings) {
    if (
      settings &&
      settings.type &&
      (settings.type === 'POST' || settings.type === 'DELETE')
    ) {
      if (!settings.url.includes('notifications')) {
        refreshActivity();
      }
    }
  });
  $(document).ajaxSend(function (event, xhr, settings) {
    if (
      settings &&
      settings.type &&
      (settings.type === 'POST' || settings.type === 'DELETE')
    ) {
      if (!settings.url.includes('notifications')) {
        $('#comments-activity-spinner.loading-spinner').addClass('active');
      }
    }
  });

  let refreshActivity = () => {
    get_all();
  };

  let getAllPromise = null;
  let getCommentsPromise = null;
  let getActivityPromise = null;
  function get_all() {
    //abort previous promise if it is not finished.
    if (getAllPromise && window.lodash.get(getAllPromise, 'readyState') !== 4) {
      getActivityPromise.abort();
      getCommentsPromise.abort();
    }
    getCommentsPromise = rest_api.get_comments(postType, postId);
    getActivityPromise = rest_api.get_activity(postType, postId);
    getAllPromise = $.when(getCommentsPromise, getActivityPromise);
    getAllPromise
      .then(function (commentDataStatusJQXHR, activityDataStatusJQXHR) {
        $('#comments-activity-spinner.loading-spinner').removeClass('active');
        const commentData = commentDataStatusJQXHR[0].comments;
        const activityData = activityDataStatusJQXHR[0].activity;

        prepareData(commentData, activityData);
      })
      .catch((err) => {
        if (!window.lodash.get(err, 'statusText') === 'abort') {
          console.error(err);
          jQuery('#errors').append(err.responseText);
        }
      });
  }

  let prepareData = function (commentData, activityData) {
    let typesCount = {};
    commentData.forEach((comment) => {
      comment.date = window.moment(comment.comment_date_gmt + 'Z');
      comment.sort_date = window
        .moment(comment.comment_date_gmt + 'Z')
        .add(5, 'seconds');

      /* comment_content should be HTML. However, we want to make sure that
       * HTML like "<div>Hello" gets transformed to "<div>Hello</div>", that
       * is, that all tags are closed, so that the comment_content can be
       * included in HTML without any nasty surprises. This is one way to do
       * that. This is not sufficient for malicious input, but hopefully we
       * can trust the contents of the database to have been sanitized
       * thanks to wp_new_comment . */

      // .DT lets strip out the tags provided from the submited comment and treat it as pure text.
      comment.comment_content = $('<div>').text(comment.comment_content).text();

      if (!typesCount[comment.comment_type]) {
        typesCount[comment.comment_type] = 0;
      }
      typesCount[comment.comment_type]++;
    });
    $('#comment-activity-tabs .tabs-title[data-always-show!="true"]').addClass(
      'hide',
    );
    window.lodash.forOwn(typesCount, (val, key) => {
      let tab = $(`[data-id="${key}"].tab-button-label`);
      let text = tab.text();
      text = text.substring(0, text.indexOf('(')) || text;
      text += ` (${formatNumber(val, langcode)})`;
      tab.text(text);
      tab.parent().parent('.hide').removeClass('hide');
    });
    comments = commentData;
    activity = activityData;
    prepareActivityData(activity);
    display_activity_comment('all');
  };
  prepareData(
    commentsSettings.comments.comments,
    commentsSettings.activity.activity,
  );

  jQuery('#add-comment-button').on('click', function () {
    post_comment(postId);
  });

  $('#comment-activity-tabs .tabs-section').on('change', function () {
    saveTabs();
  });
  let saveTabs = () => {
    let hiddenTabs = $('#comment-activity-tabs .tabs-section:not(:checked)');
    let hiddenTabIds = [];
    hiddenTabs.each((i, e) => {
      hiddenTabIds.push($(e).data('id'));
    });
    document.cookie = `dt_activity_comments_hidden_tabs=${JSON.stringify(hiddenTabIds)};path=/;expires=Fri, 31 Dec 9999 23:59:59 GMT"`;
    display_activity_comment();
  };

  let searchUsersPromise = null;

  $('textarea.mention').mentionsInput({
    onDataRequest: function (mode, query, callback) {
      $('#comment-input').addClass('loading-gif');
      if (
        searchUsersPromise &&
        window.lodash.get(searchUsersPromise, 'readyState') !== 4
      ) {
        searchUsersPromise.abort('abortPromise');
      }
      searchUsersPromise = window.API.search_users(query);
      searchUsersPromise
        .then((responseData) => {
          $('#comment-input').removeClass('loading-gif');
          let data = [];
          responseData.forEach((user) => {
            data.push({
              id: user.ID,
              name: user.name,
              type: postType,
              avatar: user.avatar,
            });
            callback.call(this, data);
          });
        })
        .catch((err) => {
          console.error(err);
        });
    },
    templates: {
      mentionItemSyntax: function (data) {
        return `[${data.value}](${data.id})`;
      },
    },
    showAvatars: true,
    minChars: 0,
  });

  let getMentionedUsers = (callback) => {
    $('textarea.mention').mentionsInput('getMentions', function (data) {
      callback(data);
    });
  };

  let getCommentWithMentions = (callback) => {
    $('textarea.mention').mentionsInput('val', function (text) {
      callback(text);
    });
  };

  //
  $(document).on('click', '.revert-activity', function () {
    let id = $(this).data('id');
    $('#revert-modal').foundation('open');
    $('#confirm-revert').data('id', id);
    window.API.get_single_activity(postType, postId, id)
      .then((a) => {
        let field = a.meta_key;
        if (window.detailsSettings.post_settings) {
          field = window.lodash.get(
            window.detailsSettings,
            `post_settings.fields[${a.meta_key}].name`,
          );
        }

        $('.revert-field').html(field || a.meta_key);
        $('.revert-current-value').html(a.meta_value);
        $('.revert-old-value').html(a.old_value || 0);
      })
      .catch((err) => {
        console.error(err);
      });
  });

  // confirm going back to the old version on the activity
  $('#confirm-revert').on('click', function () {
    let id = $(this).data('id');
    window.API.revert_activity(postType, postId, id)
      .then((contactResponse) => {
        refreshActivity();
        $('#revert-modal').foundation('close');
        if (typeof refresh_quick_action_buttons === 'function') {
          window.refresh_quick_action_buttons(contactResponse);
        }
      })
      .catch((err) => {
        console.error(err);
      });
  });

  window.onbeforeunload = function () {
    if ($('textarea.mention').val()) {
      return true;
    }
  };

  // Voice Recording Variables
  let mediaRecorder = null;
  let audioChunks = [];
  let recordingStartTime = null;
  let recordingTimer = null;
  let recordedAudioBlob = null;
  let audioContext = null;
  let analyser = null;
  let microphone = null;
  let visualizerCanvas = null;
  let visualizerCtx = null;
  let animationId = null;

  // Voice Recording Functions
  function initializeVoiceRecording() {
    const recordButton = $('#voice-record-button');
    const recordingControls = $('#audio-recording-controls');
    const startBtn = $('#start-recording-btn');
    const stopBtn = $('#stop-recording-btn');
    const playBtn = $('#play-recording-btn');
    const saveBtn = $('#save-recording-btn');
    const cancelBtn = $('#cancel-recording-btn');
    const preview = $('#recording-preview');

    // Initialize audio visualization
    visualizerCanvas = document.getElementById('audio-visualizer');
    if (visualizerCanvas) {
      visualizerCtx = visualizerCanvas.getContext('2d');
      drawInitialVisualization();
      // Hide visualization container initially - only show when recording starts
      $('.audio-visualization-container').hide();
    }

    // Toggle recording controls
    recordButton.on('click', function () {
      const recordingControls = $('#audio-recording-controls');
      if (recordingControls.hasClass('show')) {
        // Slide up and hide
        recordingControls.removeClass('show').addClass('hide');
        setTimeout(function () {
          recordingControls.hide();
        }, 600); // Increased from 300ms to 600ms for slower animation
      } else {
        // Show and slide down
        recordingControls.show().removeClass('hide').addClass('show');
        console.log('Voice recording controls displayed');
      }
    });

    // Start recording
    startBtn.on('click', function () {
      startRecording();
    });

    // Stop recording
    stopBtn.on('click', function () {
      stopRecording();
    });

    // Play recording
    playBtn.on('click', function () {
      playRecording();
    });

    // Save recording (Phase 1: just log to console)
    saveBtn.on('click', function () {
      saveRecording();
    });

    // Cancel recording
    cancelBtn.on('click', function () {
      cancelRecording();
    });
  }

  function drawInitialVisualization() {
    if (!visualizerCtx) return;

    const canvas = visualizerCanvas;
    const ctx = visualizerCtx;
    const width = canvas.width;
    const height = canvas.height;

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    // Draw background
    ctx.fillStyle = '#f8f9fa';
    ctx.fillRect(0, 0, width, height);

    // Draw center line
    /*ctx.strokeStyle = '#dee2e6';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(0, height / 2);
    ctx.lineTo(width, height / 2);
    ctx.stroke();*/

    // Draw placeholder text
    ctx.fillStyle = '#6c757d';
    ctx.font = '14px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('Click "Start Recording" to begin', width / 2, height / 2 + 5);
  }

  function drawVisualization(dataArray) {
    if (!visualizerCtx) return;

    const canvas = visualizerCanvas;
    const ctx = visualizerCtx;
    const width = canvas.width;
    const height = canvas.height;

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    // Draw background
    ctx.fillStyle = '#f8f9fa';
    ctx.fillRect(0, 0, width, height);

    // Calculate bar width and spacing
    const barWidth = (width / dataArray.length) * 2.5;
    const barSpacing = 2;
    let x = 0;

    // Draw frequency bars
    for (let i = 0; i < dataArray.length; i++) {
      const barHeight = (dataArray[i] / 255) * height;

      // Create gradient
      const gradient = ctx.createLinearGradient(
        0,
        height - barHeight,
        0,
        height,
      );
      gradient.addColorStop(0, '#007bff');
      gradient.addColorStop(1, '#0056b3');

      ctx.fillStyle = gradient;
      ctx.fillRect(x, height - barHeight, barWidth - barSpacing, barHeight);

      x += barWidth;
    }
  }

  function startRecording() {
    navigator.mediaDevices
      .getUserMedia({ audio: true })
      .then(function (stream) {
        console.log('Microphone access granted, starting recording...');

        // Initialize Web Audio API for visualization
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
        analyser = audioContext.createAnalyser();
        microphone = audioContext.createMediaStreamSource(stream);

        // Configure analyser
        analyser.fftSize = 256;
        const bufferLength = analyser.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);

        // Connect audio nodes
        microphone.connect(analyser);

        // Show visualization container and start visualization
        $('.audio-visualization-container').fadeIn('fast');

        // Start visualization
        function visualize() {
          if (analyser && visualizerCtx) {
            analyser.getByteFrequencyData(dataArray);
            drawVisualization(dataArray);
            animationId = requestAnimationFrame(visualize);
          }
        }
        visualize();

        // Start MediaRecorder for actual recording
        audioChunks = [];

        // Choose the best available audio format
        let mimeType = 'audio/webm';
        if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
          mimeType = 'audio/webm;codecs=opus';
        } else if (MediaRecorder.isTypeSupported('audio/ogg;codecs=opus')) {
          mimeType = 'audio/ogg;codecs=opus';
        } else if (MediaRecorder.isTypeSupported('audio/mp4')) {
          mimeType = 'audio/mp4';
        }

        console.log('Initializing MediaRecorder with MIME type:', mimeType);
        mediaRecorder = new MediaRecorder(stream, { mimeType: mimeType });

        mediaRecorder.addEventListener('dataavailable', function (event) {
          audioChunks.push(event.data);
        });

        mediaRecorder.addEventListener('stop', function () {
          // Check what audio formats are supported
          console.log('Supported audio formats:');
          console.log(
            'audio/webm;codecs=opus:',
            MediaRecorder.isTypeSupported('audio/webm;codecs=opus'),
          );
          console.log(
            'audio/webm:',
            MediaRecorder.isTypeSupported('audio/webm'),
          );
          console.log('audio/mp4:', MediaRecorder.isTypeSupported('audio/mp4'));
          console.log(
            'audio/ogg;codecs=opus:',
            MediaRecorder.isTypeSupported('audio/ogg;codecs=opus'),
          );

          // Use a more compatible audio format
          let mimeType = 'audio/webm';
          if (MediaRecorder.isTypeSupported('audio/webm;codecs=opus')) {
            mimeType = 'audio/webm;codecs=opus';
          } else if (MediaRecorder.isTypeSupported('audio/ogg;codecs=opus')) {
            mimeType = 'audio/ogg;codecs=opus';
          } else if (MediaRecorder.isTypeSupported('audio/mp4')) {
            mimeType = 'audio/mp4';
          }

          console.log('Using MIME type:', mimeType);
          recordedAudioBlob = new Blob(audioChunks, { type: mimeType });
          console.log(
            'Recording stopped, audio blob created:',
            recordedAudioBlob,
          );

          // Stop visualization
          if (animationId) {
            cancelAnimationFrame(animationId);
            animationId = null;
          }

          // Stop all audio tracks
          stream.getAudioTracks().forEach((track) => track.stop());

          // Close audio context
          if (audioContext) {
            audioContext.close();
            audioContext = null;
          }

          // Show play and save buttons
          $('#play-recording-btn, #save-recording-btn').show();
          $('.audio-preview-container').show();
          $('#recording-preview').attr(
            'src',
            URL.createObjectURL(recordedAudioBlob),
          );

          // Update status
          $('#recording-status-text').text('Recording completed');
          $('#voice-record-button').removeClass('recording');

          // Draw final visualization
          drawInitialVisualization();
        });

        mediaRecorder.start();
        recordingStartTime = Date.now();

        // Update UI
        $('#start-recording-btn').hide();
        $('#stop-recording-btn').show();
        $('#recording-status-text').text('Recording...');
        $('#voice-record-button').addClass('recording');

        // Reset timer display and start timer
        $('#recording-timer').text('00:00');
        startRecordingTimer();
      })
      .catch(function (error) {
        console.error('Error accessing microphone:', error);
        alert(
          "Error accessing microphone. Please ensure your microphone is connected and you've granted permission to use it.",
        );
      });
  }

  function stopRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      mediaRecorder.stop();
      stopRecordingTimer();

      $('#start-recording-btn').show();
      $('#stop-recording-btn').hide();
    }
  }

  function playRecording() {
    if (recordedAudioBlob) {
      console.log('Attempting to play audio blob:', recordedAudioBlob);
      console.log('Audio blob size:', recordedAudioBlob.size, 'bytes');
      console.log('Audio blob type:', recordedAudioBlob.type);

      // Create a new audio element for each playback to avoid conflicts
      const audioUrl = URL.createObjectURL(recordedAudioBlob);
      console.log('Created audio URL:', audioUrl);

      const audio = new Audio();

      // Set up event listeners for better debugging
      audio.addEventListener('loadstart', function () {
        console.log('Audio loading started');
      });

      audio.addEventListener('canplay', function () {
        console.log('Audio can start playing');
      });

      audio.addEventListener('canplaythrough', function () {
        console.log('Audio can play through without buffering');
      });

      audio.addEventListener('play', function () {
        console.log('Audio playback started successfully');
      });

      audio.addEventListener('error', function (e) {
        console.error('Audio error event:', e);
        console.error('Audio error details:', audio.error);
        console.error(
          'Audio error code:',
          audio.error ? audio.error.code : 'No error code',
        );
        console.error(
          'Audio error message:',
          audio.error ? audio.error.message : 'No error message',
        );
      });

      audio.addEventListener('ended', function () {
        console.log('Audio playback ended');
        // Clean up the object URL
        URL.revokeObjectURL(audioUrl);
      });

      // Set the source and attempt to play
      audio.src = audioUrl;

      // Wait a moment for the audio to load, then play
      setTimeout(function () {
        console.log('Attempting to play audio after loading...');
        audio
          .play()
          .then(function () {
            console.log('Audio play promise resolved successfully');
          })
          .catch(function (error) {
            console.error('Audio play promise rejected:', error);
            console.error('Error name:', error.name);
            console.error('Error message:', error.message);
            console.error('Full error object:', error);

            // Try to provide more specific error messages
            if (error.name === 'NotAllowedError') {
              alert(
                "Audio playback was blocked. Please check your browser's autoplay settings and try again.",
              );
            } else if (error.name === 'NotSupportedError') {
              alert(
                "Your browser doesn't support this audio format. Please try recording again.",
              );
            } else if (error.name === 'AbortError') {
              alert('Audio playback was aborted. Please try again.');
            } else {
              alert('Unable to play audio. Error: ' + error.message);
            }

            // Clean up the object URL
            URL.revokeObjectURL(audioUrl);
          });
      }, 100); // Small delay to ensure audio is loaded
    } else {
      console.error('No audio blob available for playback');
      alert('No recording available to play.');
    }
  }

  function saveRecording() {
    if (recordedAudioBlob) {
      console.log('Saving recording...');
      console.log('Audio blob size:', recordedAudioBlob.size, 'bytes');
      console.log('Audio blob type:', recordedAudioBlob.type);

      // Validate current post information
      if (!postId || !postType) {
        console.error('Could not determine post ID or post type for upload');
        alert('Error: Could not determine post information for upload');
        return;
      }

      console.log('Uploading to post:', postType, postId);

      // Create FormData for upload
      const formData = new FormData();

      // Convert blob to file
      const audioFile = new File([recordedAudioBlob], 'voice-recording.webm', {
        type: recordedAudioBlob.type,
        lastModified: Date.now(),
      });

      // Add the audio file
      formData.append('storage_upload_files[]', audioFile);

      // Add required parameters
      formData.append('meta_key', 'audio_url');
      formData.append('key_prefix', postType);
      formData.append('upload_type', 'audio_comment');

      // Add custom parameters for voice recording
      formData.append('audio_duration', $('#recording-timer').text());
      formData.append('audio_timestamp', new Date().toISOString());
      formData.append('audio_format', recordedAudioBlob.type);
      formData.append('audio_size', recordedAudioBlob.size.toString());

      // Show loading state
      const saveBtn = $('#save-recording-btn');
      const originalText = saveBtn.text();
      saveBtn.text('Uploading...').prop('disabled', true);

      // Upload to storage endpoint
      $.ajax({
        url: `${window.wpApiShare.root}dt-posts/v2/${postType}/${postId}/storage_upload`,
        type: 'POST',
        data: formData,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function (xhr) {
          xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
        },
        success: function (response) {
          console.log('Upload response:', response);
          saveBtn
            .text('Uploaded!')
            .removeClass('save-btn')
            .addClass('save-btn-success');
          setTimeout(function () {
            saveBtn
              .text(originalText)
              .prop('disabled', false)
              .removeClass('save-btn-success')
              .addClass('save-btn');
          }, 2000);

          // Reset audio capturing area and close the recording window.
          cancelRecording();
        },
        error: function (xhr, status, error) {
          console.error('Upload failed:', { xhr, status, error });
          console.error('Response text:', xhr.responseText);
          saveBtn.text(originalText).prop('disabled', false);
          alert('Upload failed. Check console for details.');
        },
      });
    } else {
      console.error('No audio blob available for upload');
      alert('No recording available to upload.');
    }
  }

  function cancelRecording() {
    // Stop recording if active
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      mediaRecorder.stop();
      stopRecordingTimer();
    }

    // Stop visualization
    if (animationId) {
      cancelAnimationFrame(animationId);
      animationId = null;
    }

    // Close audio context
    if (audioContext) {
      audioContext.close();
      audioContext = null;
    }

    // Reset UI with slide animation
    const recordingControls = $('#audio-recording-controls');
    recordingControls.removeClass('show').addClass('hide');
    setTimeout(function () {
      recordingControls.hide();
      $('#start-recording-btn').show();
      $('#stop-recording-btn, #play-recording-btn, #save-recording-btn').hide();
      $('.audio-preview-container').hide();
      $('#recording-status-text').text('Ready to record');
      $('#voice-record-button').removeClass('recording');
    }, 600); // Increased from 300ms to 600ms for slower animation

    // Clear recorded audio
    recordedAudioBlob = null;
    audioChunks = [];

    // Reset timer display
    $('#recording-timer').text('00:00');

    // Hide visualization container and reset visualization
    $('.audio-visualization-container').hide();
    drawInitialVisualization();

    console.log('Recording cancelled');
  }

  function startRecordingTimer() {
    recordingTimer = setInterval(function () {
      const elapsed = Date.now() - recordingStartTime;
      const seconds = Math.floor(elapsed / 1000);
      const minutes = Math.floor(seconds / 60);
      const remainingSeconds = seconds % 60;

      $('#recording-timer').text(
        `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`,
      );
    }, 1000);
  }

  function stopRecordingTimer() {
    if (recordingTimer) {
      clearInterval(recordingTimer);
      recordingTimer = null;
    }
    // Reset timer display
    //$('#recording-timer').text('00:00');
  }

  // Test function for debugging audio playback
  window.testAudioPlayback = function () {
    console.log('Testing audio playback...');
    console.log('recordedAudioBlob:', recordedAudioBlob);

    if (recordedAudioBlob) {
      console.log('Blob size:', recordedAudioBlob.size);
      console.log('Blob type:', recordedAudioBlob.type);

      // Test creating a simple audio element
      const testAudio = new Audio();
      const testUrl = URL.createObjectURL(recordedAudioBlob);

      testAudio.addEventListener('canplay', function () {
        console.log('Test audio can play');
        testAudio
          .play()
          .then(function () {
            console.log('Test audio played successfully');
          })
          .catch(function (error) {
            console.error('Test audio play failed:', error);
          });
      });

      testAudio.addEventListener('error', function (e) {
        console.error('Test audio error:', e);
        console.error('Test audio error details:', testAudio.error);
      });

      testAudio.src = testUrl;
    } else {
      console.log('No recorded audio blob available');
    }
  };

  // Initialize voice recording
  initializeVoiceRecording();
});
