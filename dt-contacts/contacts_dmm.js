jQuery(document).ready(function ($) {
  let post_id = window.detailsSettings.post_id;

  $('.quick-action-menu').on('click', function () {
    let fieldKey = $(this).data('id');

    let data = {};
    let numberIndicator = $(`span.${fieldKey}`);
    let newNumber = parseInt(numberIndicator.first().text() || '0') + 1;
    data[fieldKey] = newNumber;
    window.API.update_post('contacts', post_id, data)
      .then(() => {
        window.record_updated(false);
      })
      .catch((err) => {
        console.log('error');
        console.log(err);
      });

    if (fieldKey.indexOf('quick_button') > -1) {
      numberIndicator.text(newNumber);
    }
  });
});
