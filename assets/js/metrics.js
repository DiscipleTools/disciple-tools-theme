/* this is for dedicated scripting for metrics */

function show_fake_chart( text ) { // TODO: Remove this placeholder function
  "use strict";
  jQuery('#chart').html(`<img src="http://via.placeholder.com/1000x600?text=` + text + `" width="1000px" height="600px"/>`)
}
show_fake_chart( 'Critical+Path' )
