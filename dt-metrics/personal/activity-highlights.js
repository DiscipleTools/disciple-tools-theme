jQuery(document).ready(function() {
  if ( window.wpApiShare.url_path.startsWith( 'metrics/personal/activity-highlights' ) ) {
    my_stats()
  }

  function my_stats() {
    "use strict";
    let chartDiv = jQuery('#chart')
    let sourceData = dtMetricsActivity.data
    let translations = dtMetricsActivity.translations

    jQuery('#metrics-sidemenu').foundation('down', jQuery('#personal-menu'));

    const title = makeTitle(window.lodash.escape( translations.title ))

    /* highlights */
    chartDiv.empty().html(`
      ${title}
      <div class="section-subheader">${window.lodash.escape(translations.filter_contacts_to_date_range)}</div>
      <div class="date_range_picker">
          <i class="fi-calendar"></i>&nbsp;
          <span>${window.lodash.escape(translations.all_time)}</span>
          <i class="dt_caret down"></i>
      </div>
      <div style="display: inline-block" class="loading-spinner"></div>
      <hr>

      <div id="activity_highlights"></div>
    `)

    window.METRICS.setupDatePicker(
      `${dtMetricsActivity.rest_endpoints_base}/highlights_data/`,
      function (data, label) {
        if (data) {
          $('.date_range_picker span').html(label);
          buildHighlights(data, label)
        }
      }
    )

    buildHighlights(sourceData.highlights)
  }
})

function buildHighlights(data, label = "all time") {
  console.log(data, label)

  const {
    baptisms,
    baptisms_by_others,
    comments_liked,
    comments_posted,
    contacts_created,
    group_type_changed,
    group_type_changed_by_others,
    groups_created,
    health_metrics_added,
    health_metrics_added_by_others,
    milestones_added,
    milestones_added_by_others,
    quick_actions_done,
    seeker_path_changed,
    seeker_path_changed_by_others,
  } = data

  const chartDiv = jQuery('#activity_highlights')

  chartDiv.html(`
    <div class="grid-x grid-margin-x">
      <div class="cell large-6">
        <h4>Contacts I Created</h4>
          <div class="left-margin">
            ${makeRecordsCreatedSection(contacts_created)}
          </div>
        <h4>Quick actions I made</h4>
          ${makeDataTable(quick_actions_done)}
        <h4>Milestones I added</h4>
          ${makeDataTable(milestones_added)}
        <h4>Milestones added by others on my contacts</h4>
          ${makeDataTable(milestones_added_by_others)}
        <h4>Seeker paths I changed</h4>
          ${makeDataTable(seeker_path_changed)}
        <h4>Seeker paths changed by others on my contacts</h4>
          ${makeDataTable(seeker_path_changed_by_others)}
        <h4>Contacts I Baptized</h4>
          <div class="left-margin">
            ${makeBaptismsSection(baptisms)}
          </div>
        <h4>Baptisms by others on my contacts</h4>
          <div class="left-margin">
            ${makeBaptismsByOthersSection(baptisms_by_others)}
          </div>
        <h4>Groups I Created</h4>
          <div class="left-margin">
            ${makeRecordsCreatedSection(groups_created)}
          </div>
        <h4>Group Types I changed</h4>
          ${makeDataTable(group_type_changed)}
        <h4>Group Types changed by others on my groups</h4>
          ${makeDataTable(group_type_changed_by_others)}
        <h4>Health Metrics I added</h4>
          ${makeDataTable(health_metrics_added)}
        <h4>Health Metrics added by others on my groups</h4>
          ${makeDataTable(health_metrics_added_by_others)}
      </div>
      <div class="cell large-6">
        <h4>Comments I posted</h4>
          <div class="left-margin">
            ${makeCommentsSection(comments_posted)}
          </div>
        <h4>Comments I liked</h4>
          <div class="left-margin">
            ${makeCommentFilterSelect()}
            ${makeCommentsSection(comments_liked)}
          </div>
      </div>
    </div>
    `)

    const filterComments = (e) => {
      const { value } = e.target

      if (value === 'all') {
        jQuery('.liked-comments').show()
      } else {
        jQuery('.liked-comments').hide()
        jQuery(`.comment.${value}`).show()
      }
    }

    document.querySelector('#comment-filter').addEventListener('change' , filterComments)
}

function makeTitle(title) {
  return `
    <div class="cell center">
      <h3>${ title }</h3>
    </div>
  `
}

function makeDataTable(data) {
  if (empty(data)) {
    return 'None'
  }

  return `
    <table class="highlights-table striped">
      <tbody>
        ${data.reduce((html, info) => {
          if (empty(info.label)) {
            return html;
          }

          return `
            ${html}
            <tr>
              <td>${info.label}</td>
              <td>${info.count}</td>
            </tr>
          `
        }, '')}
      </tbody>
    </table>
  `
}

function makeSentence(data) {
  return `
    <div>
      ${data.reduce((html, info) => {
        return `
          ${html}
          <p>
            <span>${info.count}</span> ${info.label}
          </p>
        `
      }, '')}
    </div>
  `
}

function makeBaptismsSection(data) {
  if (empty(data)) {
    return 'None'
  }

  return data
}

function makeBaptismsByOthersSection(data) {
  if (empty(data)) {
    return 'None'
  }

  return `
    <div>
      ${data.reduce((html, info) => {
        return `
          ${html}
          <p>
            <span>${window.SHAREDFUNCTIONS.formatDate(info.baptism_date)}</span> by ${info.baptizer_name}
          </p>
        `
      }, '')}
    </div>
  `
}

function makeCommentsSection(data) {
  if (empty(data)) {
    return 'None'
  }
  const { group, contact } = dtMetricsActivity.translations

  const postTypeLabels = {
    'contacts': lodash.escape(contact),
    'groups': lodash.escape(group),
  }

  return `
    <div id="comment-activity-section">
      ${data.reduce((html, info) => {
        const reactionClasses = info.reactions
          ? [{key: 'liked-comments'}, ...info.reactions].map((reaction) => reaction.key).join(' ')
          : ''

        const epochDateTime = (new Date(info.comment_date)).getTime() / 1000;

        return `
          ${html}
          <div class="comment ${reactionClasses}">
            <div>${postTypeLabels[info.post_type]}: ${info.post_title} <span class="comment-date">${SHAREDFUNCTIONS.formatDate(epochDateTime, false, true)}</span></div>
            <div>
              <div class="comment-bubble">${window.SHAREDFUNCTIONS.formatComment(info.comment_content)}</div>
              <div class="comment-controls">
                <div class="comment-reactions">
                  ${info.reactions
                      ? info.reactions.reduce((reactionsHtml, { name, emoji, path }) => {
                          return `
                            ${reactionsHtml}
                            <div class="comment-reaction" title="${name}">
                              <span>
                                ${displayReaction({ path, emoji })}
                              </span>
                            </div>`
                        }, '')
                      : ''}
                </div>
              </div>
            </div>
          </div>
        `
      }, '')}
    </div>
  `
}

function makeRecordsCreatedSection(data) {
  if (empty(data.count)) {
    return 'None';
  }

  return data.label;
}

function makeCommentFilterSelect() {
  const { reaction_options, translations } = dtMetricsActivity

  const { all } = translations

  return `
    <select id="comment-filter">
      <option value="all">${lodash.escape(all)}</option>
      ${Object.entries(reaction_options).map(([key, reaction]) => `
        <option value="${key}">${displayReaction(reaction)}</option>
      `)}
    </select>
  `
}

function displayReaction({ emoji, path }) {
  return (emoji && emoji !== '') ? emoji : `<img class="emoji" src="${path}">`
}

function empty(data) {
  return !data || data.length === 0
}
