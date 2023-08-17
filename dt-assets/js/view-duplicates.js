jQuery(document).on("click", '.dismiss-all', function () {
  jQuery(this).addClass('loading')
  let id = jQuery(this).data('id')
  window.makeRequestOnPosts('POST', `contacts/${id}/dismiss-duplicates`, {'id': 'all'}).then(() => {
    jQuery(`#contact_${id}`).remove()
    jQuery(this).removeClass('loading')
  })
})

let get_duplicates = (limit = 0) => {
  window.makeRequest("GET", "contacts/all-duplicates", {limit: limit}, "dt-posts/v2/").then(response => {
    let html = ``
    response.posts_with_matches.forEach(post => {
      let inner_html = ``
      window.lodash.forOwn(post.dups, (dup_values, dup_key) => {
        inner_html += `<div style="display: flex"><div style="flex-basis: 100px"><strong>${window.SHAREDFUNCTIONS.escapeHTML(dup_key)}</strong></div><div>`;
        dup_values.forEach(dup => {
          inner_html += `<a target="_blank" href="contacts/${window.SHAREDFUNCTIONS.escapeHTML(dup.ID)}" style="margin: 0 10px 0 5px">
            ${window.SHAREDFUNCTIONS.escapeHTML(dup.post_title)}: ${dup.field==="post_title" ? "":window.SHAREDFUNCTIONS.escapeHTML(dup.value)} (#${window.SHAREDFUNCTIONS.escapeHTML(dup.ID)})
          </a>`;
        })
        inner_html += `</div></div>`
      })
      let channels = post.info.map(info => info.value ? info.value:null).join(", ")
      html += `
        <div class="bordered-box cell" id="contact_${window.SHAREDFUNCTIONS.escapeHTML(post.ID)}">
          <a style="color: #3f729b; font-size: 1.5rem;" target="_blank" href="contacts/${window.SHAREDFUNCTIONS.escapeHTML(post.ID)}">
            ${window.SHAREDFUNCTIONS.escapeHTML(post.post_title)} (#${window.SHAREDFUNCTIONS.escapeHTML(post.ID)})
          </a>
          <div class="label" style="display:inline-block; background: ${window.SHAREDFUNCTIONS.escapeHTML(post.overall_status.color)}">${window.SHAREDFUNCTIONS.escapeHTML(post.overall_status.label)}</div>
          <div style="display:inline-block;">${window.SHAREDFUNCTIONS.escapeHTML(channels)}</div>
          <h4 style="margin-top:20px">${window.SHAREDFUNCTIONS.escapeHTML(window.view_duplicates_settings.translations.matches_found)}</h4>
          <div>${inner_html}</div>

          <button class="button hollow dismiss-all loader" data-id="${window.SHAREDFUNCTIONS.escapeHTML(post.ID)}"  style="margin-top:20px">
            ${window.SHAREDFUNCTIONS.escapeHTML(window.view_duplicates_settings.translations.dismiss_all.replace('%s', post.post_title ))}
          </button>
        </div>
      `
    })
    jQuery('#duplicates-content').append(html)
    jQuery('#scanned_number').html(window.SHAREDFUNCTIONS.escapeHTML(response.scanned))
    let found = jQuery('#duplicates-content .bordered-box').length
    jQuery('#found_text').html(window.SHAREDFUNCTIONS.escapeHTML(found));
    if (found < 100 && !response.reached_the_end) {
      get_duplicates(response.scanned)
    } else {
      jQuery('.loading-spinner').removeClass("active")
    }
  })

}
get_duplicates(0)

