/*
This javascript file is enqueued on the contacts, groups, locations, and assets pages. The scripts here are
shared scripts applicable to all these sections.
@see /includes/functions/enqueue-scripts.php
@since 0.1.0
 */
"use strict";
// user interface utilities
jQuery(document).ready(function ($) {

  // removes elements of the public metabox from visible.
  $('#minor-publishing-actions').hide();
  $('.misc-pub-visibility').hide();
  $('.misc-pub-post-status').hide();
  $('.misc-pub-revisions').hide();
});

