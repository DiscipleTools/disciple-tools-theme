function get_map_start( token ) {
  let c = Cookies.get(token)
  if ( c === undefined ) {
    return false;
  }
  return JSON.parse(c)
}

function set_map_start( token, bounds ) {
  let b = [
    [
      standardize_coordinates( bounds._ne.lng ),
      standardize_coordinates( bounds._ne.lat ),
    ],
    [
      standardize_coordinates( bounds._sw.lng ),
      standardize_coordinates( bounds._sw.lat ),
    ]
  ]
  Cookies.set( token, JSON.stringify(b) )
}

function standardize_coordinates( coord ) {
  if (coord > 180) {
    coord = coord - 180
    coord = -Math.abs(coord)
  } else if (coord < -180) {
    coord = coord + 180
    coord = Math.abs(coord)
  }
  return coord
}
