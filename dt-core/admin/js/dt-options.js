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
    let post_type_label = $( "#connection_field_target option:selected" ).text();
    $('.connected_post_type').html(post_type_label)
    if ( this.value === $('#current_post_type').val()){
      $('.same_post_type_other_field_name').toggle(!$('#multidirectional_checkbox').is(':checked'))
      $('.connection_field_reverse_row').hide()
      $('.same_post_type_row').show()
    } else {
      $('.same_post_type_other_field_name').hide()
      $('.connection_field_reverse_row').show()
      $('.same_post_type_row').hide()
    }
  })


  $('#multidirectional_checkbox').on("change", function (){
    $('.same_post_type_other_field_name').toggle(!this.checked)
  })

  /**
   * Sorting code for field options
   */

  $('.sortable-field-options').sortable({
    connectWith: '.sortable-field-options',
    placeholder: 'ui-state-highlight',
    update: function (evt, ui) {

      let updated_field_options_ordering = [];

      // Snapshot updated field options ordering by key.
      $('.sortable-field-options').find('.sortable-field-options-key').each(function (idx, key_div) {
        let key = $(key_div).text().trim();
        if (key) {
          updated_field_options_ordering.push(encode_field_key_special_characters(key));
        }
      });

      // Persist updated field options ordering.
      $('#sortable_field_options_ordering').val(JSON.stringify(updated_field_options_ordering));

    }
  }).disableSelection();

  function encode_field_key_special_characters(key) {
    key = window.lodash.replace(key, '<', '_less_than_');
    key = window.lodash.replace(key, '>', '_more_than_');

    return key;
  }

})
