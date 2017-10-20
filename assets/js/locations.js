
function overviewMap( target ) {
  "use strict";
  let mapDiv = '#map';
  let windowHeight = jQuery(window).height();

  jQuery(mapDiv).attr('style', 'height:'+ windowHeight + ';');

  let zoom = 5;
  let centerLat = 39.5383414;
  let centerLng = -105.0464165;
  let map = new google.maps.Map(document.getElementById('map'), {
    zoom: zoom,
    center: {lat: centerLat, lng: centerLng },
    mapTypeId: 'terrain'
  });
}
