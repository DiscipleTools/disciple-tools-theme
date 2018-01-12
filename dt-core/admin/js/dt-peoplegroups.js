/* Scripts loaded specifically for peoplegroups post type pages in the admin */
jQuery(document).ready(function () {
  "use strict";

  //changes the 'add new' link to the custom page.
  jQuery('.page-title-action').attr('href', 'edit.php?post_type=peoplegroups&page=disciple_tools_people_groups');
  //removes the edit slug box.
  jQuery('#edit-slug-box').hide();


});
