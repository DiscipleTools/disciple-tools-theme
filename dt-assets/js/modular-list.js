(function($, wpApiListSettings, Foundation) {
  "use strict";
  let listSettings = window.listSettings

  window.makeRequestOnPosts( 'GET', `${listSettings.post_type}`).then(response=>{

    let records = response.posts
    let header_fields = '<th>Name</th>'
    let table_rows = ``
    _.forOwn( listSettings.post_type_settings.fields, (field_settings, field_key)=> {
      if (_.get(field_settings, 'show_in_table') === true) {
        header_fields += `
          <th class="section-subheader">
            <img src="${_.escape( field_settings.icon )}">
            ${ _.escape( field_settings.name )}
          </th>
        `
      }
    })

    records.forEach( record =>{
      let row_fields_html = ''
      _.forOwn( listSettings.post_type_settings.fields, (field_settings, field_key)=>{
        if ( _.get( field_settings, 'show_in_table' ) === true ) {
          let field_value = _.get( record, field_key, false )
          let values_html = '';
          if ( field_value !== false ) {
            if (field_settings.type === 'text') {
              values_html = _.escape(field_value)
            } else if (field_settings.type === 'date') {
              values_html = _.escape(field_value.formatted)
            } else if (field_settings.type === 'key_select') {
              values_html = _.escape(field_value.label)
            } else if (field_settings.type === 'multi_select') {
              field_value.push('test')
              values_html = field_value.map(v => {
                return `<li st>${_.escape(_.get(field_settings, `default[${v}].label`, v))}</li>`;
              }).join('')
            }
          }

          row_fields_html += `
            <td>
              <ul style="margin: 0; list-style: none">
                ${values_html}
              </ul>
            </td>
          `
        }
      })

      table_rows += `<tr>
        <td><a href="${ _.escape( record.permalink ) }">${ _.escape( record.post_title ) }</a></td>
          ${ row_fields_html }
    `
    })

    let table_html = `
      <table>
        <thead>
          <tr>
            ${header_fields}
          </tr>
        </thead>
        <tbody>
          ${table_rows}
        </tbody>
      </table>
    `
    $('#table-content').html(table_html)

    // $("#table-content").click(function(event) {
    //   event.stopPropagation();
    //   var $target = $(event.target);
    //   if ( $target.closest("tr").hasClass("fields") ) {
    //       $target.closest("tr").toggle()
    //   } else {
    //       $target.closest("tr").next().toggle();
    //   }
    // });

  })



})(window.jQuery, window.wpApiListSettings, window.Foundation);
