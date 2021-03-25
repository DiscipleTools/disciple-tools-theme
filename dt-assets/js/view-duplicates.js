$(document).on("click", '.dismiss-all', function () {
  $(this).addClass('loading')
  let id = $(this).data('id')
  makeRequestOnPosts('POST', `contacts/${id}/dismiss-duplicates`, {'id': 'all'}).then(() => {
    $(`#contact_${id}`).remove()
    $(this).removeClass('loading')
  })
})

let get_duplicates = (limit = 0) => {
  window.makeRequest("GET", "contacts/all-duplicates", {limit: limit}, "dt-posts/v2/").then(response => {
    let html = ``
    response.posts_with_matches.forEach(post => {
      let inner_html = ``
      window.lodash.forOwn(post.dups, (dup_values, dup_key) => {
        inner_html += `<div style="display: flex"><div style="flex-basis: 100px"><strong>${window.lodash.escape(dup_key)}</strong></div><div>`;
        dup_values.forEach(dup => {
          inner_html += `<a target="_blank" href="contacts/${window.lodash.escape(dup.ID)}" style="margin: 0 10px 0 5px">
            ${window.lodash.escape(dup.post_title)}: ${dup.field==="post_title" ? "":window.lodash.escape(dup.value)} (#${window.lodash.escape(dup.ID)})
          </a>`;
        })
        inner_html += `</div></div>`
      })
      let channels = post.info.map(info => info.value ? info.value:null).join(", ")
      html += `
        <div class="bordered-box cell" id="contact_${window.lodash.escape(post.ID)}">
          <a style="color: #3f729b; font-size: 1.5rem;" target="_blank" href="contacts/${window.lodash.escape(post.ID)}">
            ${window.lodash.escape(post.post_title)} (#${window.lodash.escape(post.ID)})
          </a>
          <div class="label" style="display:inline-block; background: ${window.lodash.escape(post.overall_status.color)}">${window.lodash.escape(post.overall_status.label)}</div>
          <div style="display:inline-block;">${window.lodash.escape(channels)}</div>
          <h4 style="margin-top:20px">${window.lodash.escape(window.view_duplicates_settings.translations.matches_found)}</h4>
          <div>${inner_html}</div>

          <button class="button hollow dismiss-all loader" data-id="${window.lodash.escape(post.ID)}"  style="margin-top:20px">
            ${window.lodash.escape(window.view_duplicates_settings.translations.dismiss_all.replace('%s', post.post_title ))}
          </button>
        </div>
      `
    })
    $('#duplicates-content').append(html)
    $('#scanned_number').html(window.lodash.escape(response.scanned))
    let found = $('#duplicates-content .bordered-box').length
    $('#found_text').html(window.lodash.escape(found));
    if (found < 100) {
      get_duplicates(response.scanned)
    } else {
      $('.loading-spinner').removeClass("active")
    }
  })

}
get_duplicates(0)

