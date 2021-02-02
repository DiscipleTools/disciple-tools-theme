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
      _.forOwn(post.dups, (dup_values, dup_key) => {
        inner_html += `<div style="display: flex"><div style="flex-basis: 100px"><strong>${_.escape(dup_key)}</strong></div><div>`;
        dup_values.forEach(dup => {
          inner_html += `<a target="_blank" href="contacts/${_.escape(dup.ID)}" style="margin: 0 10px 0 5px">
            ${_.escape(dup.post_title)}: ${dup.field==="post_title" ? "":_.escape(dup.value)} (#${_.escape(dup.ID)})
          </a>`;
        })
        inner_html += `</div></div>`
      })
      let channels = post.info.map(info => info.value ? info.value:null).join(", ")
      html += `
        <div class="bordered-box cell" id="contact_${_.escape(post.ID)}">
          <a style="color: #3f729b; font-size: 1.5rem;" target="_blank" href="contacts/${_.escape(post.ID)}">
            ${_.escape(post.post_title)} (#${_.escape(post.ID)})
          </a>
          <div class="label" style="display:inline-block; background: ${_.escape(post.overall_status.color)}">${_.escape(post.overall_status.label)}</div>
          <div style="display:inline-block;">${_.escape(channels)}</div>
          <h4 style="margin-top:20px">${_.escape(window.view_duplicates_settings.translations.matches_found)}</h4>
          <div>${inner_html}</div>

          <button class="button hollow dismiss-all loader" data-id="${_.escape(post.ID)}"  style="margin-top:20px">
            ${_.escape(window.view_duplicates_settings.translations.dismiss_all.replace('%s', post.post_title ))}
          </button>
        </div>
      `
    })
    $('#duplicates-content').append(html)
    $('#scanned_number').html(_.escape(response.scanned))
    let found = $('#duplicates-content .bordered-box').length
    $('#found_text').html(_.escape(found));
    if (found < 100) {
      get_duplicates(response.scanned)
    } else {
      $('.loading-spinner').removeClass("active")
    }
  })

}
get_duplicates(0)

