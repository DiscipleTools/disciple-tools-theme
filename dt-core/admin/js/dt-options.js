jQuery(document).ready(function ($) {
  $('.expand_translations').click(function() {
    event.preventDefault()
    $(this).siblings().toggleClass("hide");

    var buttonText = $(this).text();

    if (buttonText === '+') {
      $(this).text('-')
    }
    if (buttonText === '-') {
      $(this).text('+')
    }

  })

  /**
   * Sorting code for tiles
   */
  $( ".connectedSortable" ).sortable({
    connectWith: ".connectedSortable",
    placeholder: "ui-state-highlight"
  }).disableSelection();

  $( "#sort-tiles" ).sortable({
    items: "div.sort-tile:not(.disabled-drag)",
    placeholder: "ui-state-highlight",
    cancel: ".connectedSortable",
  }).disableSelection();

  $(".save-drag-changes").on( "click", function (){
    let order = [];
    $(".sort-tile").each((a, b)=>{
      let tile_key = $(b).attr("id")
      let tile = {
        key: tile_key,
        fields: []
      }
      $(`#${tile_key} .connectedSortable li`).each((field_index, field)=>{
        tile.fields.push($(field).attr('id'))
      })
      order.push(tile)
    })
    let input = $("<input>")
               .attr("type", "hidden")
               .attr("name", "order").val(JSON.stringify(order));
    $('#tile-order-form').append(input).submit();

  })


})
