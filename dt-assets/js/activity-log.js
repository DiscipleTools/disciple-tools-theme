jQuery(function() {
  function makeActivityList(userActivity, translations) {
    const sortedActivities = groupActivityByDayAndRecord(userActivity)
    const moreLabel = window.lodash.escape(translations.more)
    const lessLabel = window.lodash.escape(translations.less)

    let activityHtml = ``;
    Object.entries(sortedActivities).forEach(([date, daysActivities]) => {
      activityHtml += `<div class="day-activities">`
      activityHtml += `<h4 class="day-activities__title">${date}</h4>`
      Object.entries(daysActivities).forEach(([postTitle, postActivities]) => {
        const firstPostActivity = postActivities[0]
        const icon = window.lodash.escape(firstPostActivity.icon)
        if (!firstPostActivity.post_type_label) return
        const iconHtml = firstPostActivity.icon ? `<i class="${icon} medium post-activities__icon"></i> ` : window.lodash.escape(firstPostActivity.post_type_label) + ':'
        activityHtml += `<div class="post-activities">`
        const requiresTitle = ['field_update', 'created', 'comment'].includes(firstPostActivity.action)
        if (requiresTitle) {
          activityHtml += `
            <h5 class="post-activities__title">
              <a href="/${firstPostActivity.post_type}/${firstPostActivity.object_id}">${iconHtml} ${postTitle}</a>
            </h5>`
        }

        const groupedActivities = groupActivityTypes(postActivities)
        Object.entries(groupedActivities).forEach(([action, activities]) => {
          const { fields, object_note_short, object_note, count, hist_time } = activities
          if (action === 'field_update') {
            // TODO: escape field names also
            const escapedFields = fields.map((field) => window.lodash.escape(field))
            if (fields.length === 0) return
            const hasMoreFields = fields.slice(2).length > 0

            if (hasMoreFields) {
              const forID = `activity${hist_time}${activities.object_id}`
              activityHtml += `
                <div class="post-activities__item">
                  <input type="checkbox" class="activity__more-state" id="${forID}" />
                  ${window.lodash.escape(object_note_short)}: ${escapedFields.slice(0, 2).join(', ')}<span class="activity__more-details">, ${escapedFields.slice(2).join(', ')}</span>
                  <label for="${forID}" class="activity__more-link">+&nbsp;${fields.slice(2).length}&nbsp;${moreLabel}</label>
                  <label for="${forID}" class="activity__less-link">-&nbsp;${lessLabel}</label>
                </div>
                `
            } else {
              activityHtml += `
                <div class="post-activities__item">
                  ${window.lodash.escape(object_note_short)}: ${fields.join(', ')}
                </div>
                `
            }
          } else {
            const note = object_note_short
              ? window.lodash.escape(object_note_short.replace('%n', count))
              : window.lodash.escape(object_note)
            activityHtml += `<div class="post-activities__item${requiresTitle ? '' : '--no-title'}">
                ${note}
              </div>`
          }
        })
        activityHtml += `</div>`
      })
      activityHtml += `</div>`
    })

    return activityHtml
  }

  function groupActivityByDayAndRecord(data) {
    const sortedByDayAndRecord = []
    let currentDay = ''
    let daysActivity = {}
    data.forEach(activity => {
      // data is already in date order, so we can go day by day
      const date = moment.unix(activity.hist_time).format('YYYY-MM-DD')
      if (date !== currentDay) {
        currentDay = date
        daysActivity = {}
        sortedByDayAndRecord[currentDay] = daysActivity
      }
      if (!daysActivity[activity.object_name]) {
        daysActivity[activity.object_name] = []
      }
      daysActivity[activity.object_name].push(activity)
    });

    return sortedByDayAndRecord
  }

  function groupActivityTypes(postActivities) {
    const groupedActivities = {}

    postActivities.forEach((activity) => {
      const action = activity.action
      if (!groupedActivities[action]) {
        groupedActivities[action] = {
          count: 0,
          ...activity,
          fields: [],
        }
        delete groupedActivities[action].field
      }
      groupedActivities[action].count += 1
      if (activity.field) {
        groupedActivities[action].fields.push(activity.field)
      } else {
        groupedActivities[action].fields.push(activity.meta_key)
      }
    })
    return groupedActivities
  }

  window.dtActivityLogs = {
    makeActivityList,
  }
})