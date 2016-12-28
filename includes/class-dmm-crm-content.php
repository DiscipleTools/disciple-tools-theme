<?php
	
if ( ! defined( 'ABSPATH' ) ) exit;

function dmm_crm_contacts_contacts () {
	$html_content = '
	
		<br>
		        <ul class="subsubsub">
					<li class="all"><a href="users.php" class="current">All <span class="count">(2)</span></a> |</li>
					<li class="administrator"><a href="users.php?role=administrator">Administrator <span class="count">(1)</span></a> |</li>
					<li class="contributor"><a href="users.php?role=contributor">Contributor <span class="count">(1)</span></a></li>
				</ul>
		        <form method="get">
						<p class="search-box">
							<label class="screen-reader-text" for="user-search-input">Search Users:</label>
							<input type="search" id="user-search-input" name="s" value="">
							<input type="submit" id="search-submit" class="button" value="Search Users"></p>
						
						<input type="hidden" id="_wpnonce" name="_wpnonce" value="0f9ceb0774"><input type="hidden" name="_wp_http_referer" value="/wp-admin/users.php">	<div class="tablenav top">
						
										<div class="alignleft actions bulkactions">
									<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label><select name="action" id="bulk-action-selector-top">
						<option value="-1">Bulk Actions</option>
							<option value="delete">Delete</option>
						</select>
						<input type="submit" id="doaction" class="button action" value="Apply">
								</div>
									<div class="alignleft actions">
										<label class="screen-reader-text" for="new_role">Change role to…</label>
								<select name="new_role" id="new_role">
									<option value="">Change role to…</option>
									
							<option value="subscriber">Subscriber</option>
							<option value="contributor">Contributor</option>
							<option value="author">Author</option>
							<option value="editor">Editor</option>
							<option value="administrator">Administrator</option>		</select>
							<input type="submit" name="changeit" id="changeit" class="button" value="Change"></div><div class="tablenav-pages one-page"><span class="displaying-num">2 items</span>
						<span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
						<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
						<span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Current Page</label><input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging"><span class="tablenav-paging-text"> of <span class="total-pages">1</span></span></span>
						<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
						<span class="tablenav-pages-navspan" aria-hidden="true">»</span></span></div>
								<br class="clear">
							</div>
						<h2 class="screen-reader-text">Users list</h2><table class="wp-list-table widefat fixed striped users">
							<thead>
							<tr>
								<td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td><th scope="col" id="username" class="manage-column column-username column-primary sortable desc"><a href="http://plugins:8888/wp-admin/users.php?orderby=login&amp;order=asc"><span>Username</span><span class="sorting-indicator"></span></a></th><th scope="col" id="name" class="manage-column column-name">Name</th><th scope="col" id="email" class="manage-column column-email sortable desc"><a href="http://plugins:8888/wp-admin/users.php?orderby=email&amp;order=asc"><span>Email</span><span class="sorting-indicator"></span></a></th><th scope="col" id="role" class="manage-column column-role">Role</th><th scope="col" id="posts" class="manage-column column-posts num">Posts</th>	</tr>
							</thead>
						
							<tbody id="the-list" data-wp-lists="list:user">
								
							<tr id="user-2"><th scope="row" class="check-column"><label class="screen-reader-text" for="user_2">Select js</label><input type="checkbox" name="users[]" id="user_2" class="contributor" value="2"></th><td class="username column-username has-row-actions column-primary" data-colname="Username"><img alt="" src="http://0.gravatar.com/avatar/0279fdb0dd9c93d8f27dcf30d53a1a20?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/0279fdb0dd9c93d8f27dcf30d53a1a20?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> <strong><a href="http://plugins:8888/wp-admin/user-edit.php?user_id=2&amp;wp_http_referer=%2Fwp-admin%2Fusers.php">js</a></strong><br><div class="row-actions"><span class="edit"><a href="http://plugins:8888/wp-admin/user-edit.php?user_id=2&amp;wp_http_referer=%2Fwp-admin%2Fusers.php">Edit</a> | </span><span class="delete"><a class="submitdelete" href="users.php?action=delete&amp;user=2&amp;_wpnonce=0f9ceb0774">Delete</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="name column-name" data-colname="Name">J S</td><td class="email column-email" data-colname="Email"><a href="mailto:himayrunner@hushmail.com">himayrunner@hushmail.com</a></td><td class="role column-role" data-colname="Role">Contributor</td><td class="posts column-posts num" data-colname="Posts">0</td></tr>
							<tr id="user-1"><th scope="row" class="check-column"><label class="screen-reader-text" for="user_1">Select plugins</label><input type="checkbox" name="users[]" id="user_1" class="administrator" value="1"></th><td class="username column-username has-row-actions column-primary" data-colname="Username"><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> <strong><a href="http://plugins:8888/wp-admin/profile.php?wp_http_referer=%2Fwp-admin%2Fusers.php">plugins</a></strong><br><div class="row-actions"><span class="edit"><a href="http://plugins:8888/wp-admin/profile.php?wp_http_referer=%2Fwp-admin%2Fusers.php">Edit</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="name column-name" data-colname="Name"> </td><td class="email column-email" data-colname="Email"><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a></td><td class="role column-role" data-colname="Role">Administrator</td><td class="posts column-posts num" data-colname="Posts"><a href="edit.php?author=1" class="edit"><span aria-hidden="true">1</span><span class="screen-reader-text">1 post by this author</span></a></td></tr>	</tbody>
						
							<tfoot>
							<tr>
								<td class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-2">Select All</label><input id="cb-select-all-2" type="checkbox"></td><th scope="col" class="manage-column column-username column-primary sortable desc"><a href="http://plugins:8888/wp-admin/users.php?orderby=login&amp;order=asc"><span>Username</span><span class="sorting-indicator"></span></a></th><th scope="col" class="manage-column column-name">Name</th><th scope="col" class="manage-column column-email sortable desc"><a href="http://plugins:8888/wp-admin/users.php?orderby=email&amp;order=asc"><span>Email</span><span class="sorting-indicator"></span></a></th><th scope="col" class="manage-column column-role">Role</th><th scope="col" class="manage-column column-posts num">Posts</th>	</tr>
							</tfoot>
						
						</table>
							<div class="tablenav bottom">
						
										<div class="alignleft actions bulkactions">
									<label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label><select name="action2" id="bulk-action-selector-bottom">
						<option value="-1">Bulk Actions</option>
							<option value="delete">Delete</option>
						</select>
						<input type="submit" id="doaction2" class="button action" value="Apply">
								</div>
									<div class="alignleft actions">
										<label class="screen-reader-text" for="new_role2">Change role to…</label>
								<select name="new_role2" id="new_role2">
									<option value="">Change role to…</option>
									
							<option value="subscriber">Subscriber</option>
							<option value="contributor">Contributor</option>
							<option value="author">Author</option>
							<option value="editor">Editor</option>
							<option value="administrator">Administrator</option>		</select>
							<input type="submit" name="changeit" id="changeit" class="button" value="Change"></div><div class="tablenav-pages one-page"><span class="displaying-num">2 items</span>
						<span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
						<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
						<span class="screen-reader-text">Current Page</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">1 of <span class="total-pages">1</span></span></span>
						<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
						<span class="tablenav-pages-navspan" aria-hidden="true">»</span></span></div>
								<br class="clear">
							</div>
						</form>
	
	';
	
	return $html_content;
}

function dmm_crm_contacts_add () {
	$html_content = '
	<p>add content section</p>
	';
	
	return $html_content;
}

function dmm_crm_contacts_activity () {
	$html_content = '
	<p>activity content section</p>
	';
	
	return $html_content;
}

function dmm_crm_contacts_tools () {
	$html_content = '
	<p>tools content section</p>
	';
	
	return $html_content;
}

function dmm_crm_reports_overview () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_reports_charts() {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_reports_generations () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_maps_tracts () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_maps_charts () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_maps_tools () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_library_find () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_library_saved () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_library_used () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_library_shared () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_library_tools () {
	$html_content = '
					<p>tools content section</p>
			        <table class="form-table"><tbody>
					<tr>
						<th scope="row">Vision</th>
						<td>The purpose of the content library is to facilitate sharing of resources between DMM and media teams. Because God is leading people 
						</td>
					</tr>
					<tr>
					
						<th scope="row">New Content Available!</th>
						<td>
							<ul><li>Islam Pack version 1.6 Available <a href="#">Update</a></li>
							<li>Hindu Pack version 2.6 Available <a href="#">Update</a></li>
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row">Usage</th>
						<td>Here are the usage rules for the written and visual media included in the library.
						</td>
					</tr>
					
					<tr><th scope="row">Upload Your Own Content</th><td><img id="dm_an_image_preview" class="image_preview" src=""><br>
						<input id="dm_an_image_button" type="button" data-uploader_title="Upload an image" data-uploader_button_text="Use image" class="image_upload_button button" value="Upload new image">
						<input id="dm_an_image_delete" type="button" class="image_delete_button button" value="Remove image">
						<input id="dm_an_image" class="image_data_field" type="hidden" name="dm_an_image" value=""><br>
						<label for="an_image">
						<span class="description">This will upload an image to your media library and store the attachment ID in the option field. Once you have uploaded an image the thumbnail will display above these buttons.</span>
						</label>
						</td></tr>
					
				</tbody></table>
	';
	
	return $html_content;
}

function dmm_crm_help_dmmcrm () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_help_media () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_help_dmm () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_settings_general () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_settings_contacts () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_settings_reports () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_settings_maps () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_settings_library () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_settings_help () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}




	
?>