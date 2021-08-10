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


  /**
   * new fields
   */
  //show more fields when connection option selected

  $('#new_field_type_select').on('change', function (){
    if ( this.value === "connection" ){
      $('.connection_field_target_row').show()
      $('#private_field_row').hide()
      $('#connection_field_target').prop('required', true);
    } else {
      $('.connection_field_reverse_row').hide()
      $('.connection_field_target_row').hide()
      $('#private_field_row').show()
      $('#connection_field_target').prop('required', false);
    }
  })

  //show the reverse connection field name row if the post type is not "self"
  $('#connection_field_target').on("change", function (){
    if ( this.value === $('#current_post_type').val()){
      $('.connection_field_reverse_row').hide()
    } else {
      $('.connection_field_reverse_row').show()
    }
  })


})
