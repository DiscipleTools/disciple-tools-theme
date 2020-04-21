jQuery(document).ready(function ($) {
  $('.expand_translations').click(function() {
    event.preventDefault()
    console.log("clicked");
    $(this).siblings().toggleClass("hide");

    var buttonText = $(this).text();

    if (buttonText === '+') {
      $(this).text('-')
    }

  })
})
