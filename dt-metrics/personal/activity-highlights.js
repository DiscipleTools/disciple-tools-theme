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

    const title = window.lodash.escape( translations.title )

    /* highlights */
    chartDiv.empty().html(`
      <div class="cell center">
        <h3>${ title }</h3>
      </div>
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

  const {
    field_I_changed,
    field_I_made,
    baptism_by_me,
    field_others_changed,
    baptism_by_others,
    comments_I_liked,
    comments_I_posted,
  } = SHAREDFUNCTIONS.escapeObject(dtMetricsActivity.translations)

  const chartDiv = jQuery('#activity_highlights')

  chartDiv.html(`
    <div class="grid-x grid-margin-x">
      <div class="cell large-6">
        <h4>${makeTitle(contacts_created, field_I_made)}</h4>
          <div class="left-margin">
            ${makeRecordsCreatedSection(contacts_created)}
          </div>

        <h4>${makeTitle(quick_actions_done, field_I_changed)}</h4>
          ${makeDataTable(quick_actions_done)}

        <h4>${makeTitle(milestones_added, field_I_changed)}</h4>
          ${makeDataTable(milestones_added)}

        <h4>${makeTitle(milestones_added_by_others, field_others_changed)}</h4>
          ${makeDataTable(milestones_added_by_others)}

        <h4>${makeTitle(seeker_path_changed, field_I_changed)}</h4>
          ${makeDataTable(seeker_path_changed)}

        <h4>${makeTitle(seeker_path_changed_by_others, field_others_changed)}</h4>
          ${makeDataTable(seeker_path_changed_by_others)}

        <h4>${baptism_by_me}</h4>
          ${makeBaptismsSection(baptisms)}

        <h4>${baptism_by_others}</h4>
          ${makeBaptismsByOthersSection(baptisms_by_others)}

        <h4>${makeTitle(groups_created, field_I_made)}</h4>
          <div class="left-margin">
            ${makeRecordsCreatedSection(groups_created)}
          </div>

        <h4>${makeTitle(group_type_changed, field_I_changed)}</h4>
          ${makeDataTable(group_type_changed)}

        <h4>${makeTitle(group_type_changed_by_others, field_others_changed)}</h4>
          ${makeDataTable(group_type_changed_by_others)}

        <h4>${makeTitle(health_metrics_added, field_I_changed)}</h4>
          ${makeDataTable(health_metrics_added)}

        <h4>${makeTitle(health_metrics_added_by_others, field_others_changed)}</h4>
          ${makeDataTable(health_metrics_added_by_others)}

      </div>
      <div class="cell large-6">
        <h4>${comments_I_posted}</h4>
          <div class="left-margin">
            ${makeCommentsSection(comments_posted)}
          </div>
        <h4>${comments_I_liked}</h4>
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

function makeTitle(data, title_text) {
  const { field_label, post_type_label } = data

  let title = title_text.replace('%1$s', field_label);

  if (post_type_label) {
    title = title.replace('%2$s', post_type_label)
  }

  return title;
}

function makeDataTable(data) {
  if (empty(data.rows)) {
    return `
    <div class="left-margin">
      ${none()}
    </div>
    `
  }

  return `
    <table class="highlights-table striped">
      <tbody>
        ${data.rows.reduce((html, info) => {
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
    return none()
  }

  const { date, contact } = SHAREDFUNCTIONS.escapeObject( dtMetricsActivity.translations )

  return `
    <div>
      <p class="left-margin">${data.length}</p>
      <table class="striped">
        <thead>
          <tr>
            <td>${date}</td>
            <td>${contact}</td>
          </tr>
        </thead>
        <tbody>
          ${data.reduce((html, info) => {
            return `
              ${html}
              <tr>
                <td>${window.SHAREDFUNCTIONS.formatDate(info.baptism_date)}</td>
                <td><a href="/contacts/${info.ID}">${info.contact}</a></td>
              </tr>
            `
          }, '')}
        </tbody>
      </table>
    </div>
  `
}

function makeBaptismsByOthersSection(data) {
  if (empty(data)) {
    return none()
  }

  const { date, contact, baptized_by } = SHAREDFUNCTIONS.escapeObject( dtMetricsActivity.translations )

  return `
    <div>
      <table class="striped">
        <thead>
          <tr>
            <td>${date}</td>
            <td>${contact}</td>
            <td>${baptized_by}</td>
          </tr>
        </thead>
        <tbody>
          ${data.reduce((html, info) => {
            return `
              ${html}
              <tr>
                <td>${window.SHAREDFUNCTIONS.formatDate(info.baptism_date)}</td>
                <td><a href="/contacts/${info.ID}">${info.contact}</a></td>
                <td>${info.baptizer_name}</td>
              </tr>
            `
          }, '')}
        </tbody>
      </table>
    </div>
  `
}

function makeCommentsSection(data) {
  if (empty(data)) {
    return none()
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
            <div>
              ${postTypeLabels[info.post_type]}:
              <a href="/${info.post_type}/${info.ID}">${info.post_title}</a>
              <span class="comment-date">${SHAREDFUNCTIONS.formatDate(epochDateTime, false, true)}</span>
            </div>
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
    return none();
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

function none() {
  const { none } = SHAREDFUNCTIONS.escapeObject(dtMetricsActivity.translations)

  return none;
}