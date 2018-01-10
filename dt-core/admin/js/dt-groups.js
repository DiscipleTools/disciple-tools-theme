/*
 This javascript file is enqueued on the groups page. These are scripts specific to the groups page.
 @see /includes/functions/enqueue-scripts.php
 @since 0.1.0
 */

"use strict";
jQuery(document).ready(function () {
  jQuery('.datepicker').datepicker({
    dateFormat: 'yy-mm-dd'
  });
});
