


/**
 * @see https://stackoverflow.com/questions/6048975/google-maps-v3-how-to-calculate-the-zoom-level-for-a-given-bounds
 * @param bounds
 * @param mapDim
 * @returns {number}
 */
function getBoundsZoomLevel(bounds, mapDim) {
  let WORLD_DIM = { height: 256, width: 256 };
  let ZOOM_MAX = 21;

  function latRad(lat) {
    let sin = Math.sin(lat * Math.PI / 180);
    let radX2 = Math.log((1 + sin) / (1 - sin)) / 2;
    return Math.max(Math.min(radX2, Math.PI), -Math.PI) / 2;
  }

  function zoom(mapPx, worldPx, fraction) {
    return Math.floor(Math.log(mapPx / worldPx / fraction) / Math.LN2);
  }

  let ne = bounds.getNorthEast();
  let sw = bounds.getSouthWest();

  let latFraction = (latRad(ne.lat()) - latRad(sw.lat())) / Math.PI;

  let lngDiff = ne.lng() - sw.lng();
  let lngFraction = ((lngDiff < 0) ? (lngDiff + 360) : lngDiff) / 360;

  let latZoom = zoom(mapDim.height, WORLD_DIM.height, latFraction);
  let lngZoom = zoom(mapDim.width, WORLD_DIM.width, lngFraction);

  return Math.min(latZoom, lngZoom, ZOOM_MAX);
}
