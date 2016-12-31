<?php
	
if ( ! defined( 'ABSPATH' ) ) exit;

/*
*
* Sample framework functions
*/
function dmm_crm_2_column ($field1, $field2, $field3, $field4) {
	
	$html_content = '
	<div class="wrap">
		<div id="icon-options-general" class="icon32"></div>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
	
				<!-- main content -->
				<div id="post-body-content">
	
					<div class="meta-box-sortables ui-sortable">
	
						<div class="postbox">
	
							<h2 class="hndle"><span>';
	
	$html_content .= $field1;						
	
	$html_content .=						'</span></h2>
	
							<div class="inside">
								<p>';
	
	$html_content .= $field2;						
	
	$html_content .=						'</p>
							</div>
							<!-- .inside -->
	
						</div>
						<!-- .postbox -->
	
					</div>
					<!-- .meta-box-sortables .ui-sortable -->
	
				</div>
				<!-- post-body-content -->
	
				<!-- sidebar -->
				<div id="postbox-container-1" class="postbox-container">
	
					<div class="meta-box-sortables">
	
						<div class="postbox">
	
							<h2 class="hndle"><span>';
	
	$html_content .= $field3;						
	
	$html_content .=						'</span></h2>
	
							<div class="inside">
								<p>';
	
	$html_content .= $field4;						
	
	$html_content .=						'</p>
							</div>
							<!-- .inside -->
	
						</div>
						<!-- .postbox -->
	
					</div>
					<!-- .meta-box-sortables -->
	
				</div>
				<!-- #postbox-container-1 .postbox-container -->
	
			</div>
			<!-- #post-body .metabox-holder .columns-2 -->
	
			<br class="clear">
		</div>
		<!-- #poststuff -->
	
	</div> <!-- .wrap -->
	';
	
	return $html_content;
}

function dmm_crm_2_column_open ($field1, $field2) {
	
	$html_content = '
	<div class="wrap">
		<div id="icon-options-general" class="icon32"></div>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
	
				<!-- main content -->
				<div id="post-body-content">
	
					<div class="meta-box-sortables ui-sortable">
	
						';
	
	$html_content .= $field1;						
	
	$html_content .=	'
	
					</div>
					<!-- .meta-box-sortables .ui-sortable -->
	
				</div>
				<!-- post-body-content -->
	
				<!-- sidebar -->
				<div id="postbox-container-1" class="postbox-container">
	
					<div class="meta-box-sortables">
	
						';
	
	$html_content .= $field2;						
	
	$html_content .=	'						
						
					</div>
					<!-- .meta-box-sortables -->
	
				</div>
				<!-- #postbox-container-1 .postbox-container -->
	
			</div>
			<!-- #post-body .metabox-holder .columns-2 -->
	
			<br class="clear">
		</div>
		<!-- #poststuff -->
	
	</div> <!-- .wrap -->
	';
	
	return $html_content;
}

function dmm_crm_2_column_placeholder () {
	
	$field1 = 'Main Header';
	$field2 = 'Main content';
	$field3 = 'Side Header';
	$field4 = 'Side content';
	
	$html_content = dmm_crm_2_column ($field1, $field2, $field3, $field4);
	
	return $html_content;
}

function dmm_crm_post_box ($field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8 ) {
	$html_content = '
	<div class="wrap">
	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables ">
					<div class="postbox">
						<h2 class="hndle">';
	
	$html_content .= $field1;						
	
	$html_content .=						'</h2>
						<div class="inside">
							<p>';
	
	$html_content .= $field2;						
	
	$html_content .=						'</p>
						</div>
					</div>
				</div>
				<div class="meta-box-sortables ">
					<div class="postbox">
						<h2 class="hndle">';
	
	$html_content .= $field3;						
	
	$html_content .=						'</h2>
						<div class="inside">
							
							<p>';
	
	$html_content .= $field4;						
	
	$html_content .=						'</p>
						</div>
					</div>
				</div>
			</div>
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ">
					<div class="postbox">
						<h2 class="hndle">';
	
	$html_content .= $field5;						
	
	$html_content .=						'</h2>
						<div class="inside">
							<p>';
	
	$html_content .= $field6;						
	
	$html_content .=						'</p>
						</div>
					</div>
				</div>
				
			</div>
			<div id="postbox-container-3" class="postbox-container">
				<div class="meta-box-sortables ">
					<div class="postbox">
						<h2 class="hndle">';
	
	$html_content .= $field7;						
	
	$html_content .=						'</h2>
						<div class="inside">
							<p>';
	
	$html_content .= $field8;						
	
	$html_content .=						'</p>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
	</div>
	';
	
	return $html_content;
}

function dmm_crm_post_box_placeholder () {
	
	$field1 = 'Header 1';
	$field2 = 'Content';
	$field3 = 'Header 2';
	$field4 = 'Content';
	$field5 = 'Header 3';
	$field6 = 'Content';
	$field7 = 'Header 4';
	$field8 = 'Content';
	
	$html_content = dmm_crm_post_box ($field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8 );
	
	return $html_content;
}

function dmm_crm_table () {
	$html_content = '
	<p>table content section</p>
	';
	
	return $html_content;
}

/*
*
* Specific content frameworks
*/

function dmm_crm_dashboard () {
	
	$field1 = 'New Contacts';
	$field2 = '
				<table class="form-table striped ">
					<tbody>
						<tr>
							<td class="row-title"><a href="/wp-admin/admin.php?page=dmm_contacts&tab=single&id=123">Mohammed P.</a></td>
							<td>720-212-8535</td>
							<td>Assigned</td>
							<td>Aug. 26, 2016</td>
						</tr>
						<tr>
							<td class="row-title"><a href="/wp-admin/admin.php?page=dmm_contacts&tab=single&id=124">Sherif A.</a></td>
							<td>720-212-8535</td>
							<td>Unassigned</td>
							<td>Aug. 26, 2016</td>
						</tr>
					</tbody>
				</table>
	
	';
	$field3 = 'Quick Stats';
	$field4 = '
				<table class="widefat striped ">
					<thead>
						<tr>
							<th>Name</th>
							<th>Count</th>
							
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Prayers Network</td>
							<td>132,811</td>
							
						</tr>
						<tr>
							<td>Facebook Engagment</td>
							<td>447,239</td>
							
						</tr>
						<tr>
							<td>Website Visitors</td>
							<td>182,994</td>
							
						</tr>
						<tr>
							<td>New Inquirer</td>
							<td>2,243</td>
						</tr>
						<tr>
							<td>Contact Attempted</td>
							<td>866</td>
						</tr>
						<tr>
							<td>Contact Established</td>
							<td>725</td>
						</tr>
						<tr>
							<td>First Meeting Complete</td>
							<td>458</td>
						</tr>
						<tr>
							<td>Baptisms</td>
							<td>72</td>
						</tr>
						<tr>
							<td>Baptizers</td>
							<td>37</td>
						</tr>
						<tr>
							<td>Active Churches</td>
							<td>7</td>
						</tr>
						<tr>
							<td>Church Planters</td>
							<td>23</td>
						</tr>
						
					</tbody>
				</table>
				
				
			';
	$field5 = 'Status';
	$field6 = '
			<iframe src="/wp-content/plugins/dmm-crm/includes/charts/pie-chart.html" width="100%" height="300px" border="0"></iframe><br>
			<iframe src="/wp-content/plugins/dmm-crm/includes/charts/prayer-chart.html" width="100%" height="220px" border="0"></iframe><br>
			<iframe src="/wp-content/plugins/dmm-crm/includes/charts/seeking-chart.html" width="100%" height="220px" border="0"></iframe><br>
			<iframe src="/wp-content/plugins/dmm-crm/includes/charts/training-chart.html" width="100%" height="220px" border="0"></iframe><br>
			<iframe src="/wp-content/plugins/dmm-crm/includes/charts/multiplying-chart.html" width="100%" height="220px" border="0"></iframe>
	
	';
	$field7 = 'Quick Links';
	$field8 = '<a href="#">Link</a><br><a href="#">Link</a><br><a href="#">Link</a><br>';
	
	$html_content = dmm_crm_post_box ($field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8 );
	
	return $html_content;
}

function dmm_crm_prayer_dashboard () {
	
	$field1 = 'Header 1';
	$field2 = 'Content';
	$field3 = 'Header 2';
	$field4 = 'Content';
	$field5 = 'Prayers Status';
	$field6 = '<iframe src="/wp-content/plugins/dmm-crm/includes/charts/prayer-chart.html" width="100%" height="220px" border="0"></iframe>';
	$field7 = 'Header 4';
	$field8 = 'Content';
	
	$html_content = dmm_crm_post_box ($field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8 );
	
	return $html_content;
}

function dmm_crm_prayer_apps () {
	$field1 = 'Integrations';
	$field2 = '
			<!-- Integrations Box -->
			<table class="form-table ">
				<tbody>
					<tr>
						<th scope="row">Mailchimp API</th>
						<td><input type="text" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row">Facebook API</th>
						<td><input type="text" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row">Twitter API</th>
						<td><input type="text" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row">Website Analytics API</th>
						<td><input type="text" class="regular-text" /></td>
					</tr>
					
					<tr>
						<th scope="row"></th>
						<td><input class="button-primary" type="submit" name="Save" value="Save" /></td>
					</tr>
					
				</tbody>
			</table>
	';
	$field3 = 'Notes';
	$field4 = 'Side content';
	
	$html_content = dmm_crm_2_column ($field1, $field2, $field3, $field4);
	
	return $html_content;
}

function dmm_crm_prayer_blog () {
	
	$html_content = '
	<div class="wrap">
<h1 class="wp-heading-inline">Posts</h1>

 <a href="http://plugins:8888/wp-admin/post-new.php" class="page-title-action">Add New</a>
<hr class="wp-header-end">


<h2 class="screen-reader-text">Filter posts list</h2><ul class="subsubsub">
	<li class="all"><a href="edit.php?post_type=post">All <span class="count">(5)</span></a> |</li>
	<li class="publish"><a href="edit.php?post_status=publish&amp;post_type=post">Published <span class="count">(5)</span></a> |</li>
	<li class="trash"><a href="edit.php?post_status=trash&amp;post_type=post">Trash <span class="count">(1)</span></a></li>
</ul>
<form id="posts-filter" method="get">

<p class="search-box">
	<label class="screen-reader-text" for="post-search-input">Search Posts:</label>
	<input type="search" id="post-search-input" name="s" value="">
	<input type="submit" id="search-submit" class="button" value="Search Posts"></p>

<input type="hidden" name="post_status" class="post_status_page" value="all">
<input type="hidden" name="post_type" class="post_type_page" value="post">

<input type="hidden" id="_wpnonce" name="_wpnonce" value="ed9292b99c"><input type="hidden" name="_wp_http_referer" value="/wp-admin/edit.php?ids=1">	<div class="tablenav top">

				<div class="alignleft actions bulkactions">
			<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label><select name="action" id="bulk-action-selector-top">
<option value="-1">Bulk Actions</option>
	<option value="edit" class="hide-if-no-js">Edit</option>
	<option value="trash">Move to Trash</option>
</select>
<input type="submit" id="doaction" class="button action" value="Apply">
		</div>
				<div class="alignleft actions">
		<label for="filter-by-date" class="screen-reader-text">Filter by date</label>
		<select name="m" id="filter-by-date">
			<option selected="selected" value="0">All dates</option>
<option value="201612">December 2016</option>
		</select>
<label class="screen-reader-text" for="cat">Filter by category</label><select name="cat" id="cat" class="postform">
	<option value="0">All Categories</option>
	<option class="level-0" value="1">Uncategorized</option>
</select>
<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter">		</div>
<div class="tablenav-pages one-page"><span class="displaying-num">5 items</span>
<span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
<span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Current Page</label><input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging"><span class="tablenav-paging-text"> of <span class="total-pages">1</span></span></span>
<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
<span class="tablenav-pages-navspan" aria-hidden="true">»</span></span></div>
		<br class="clear">
	</div>
<h2 class="screen-reader-text">Posts list</h2><table class="wp-list-table widefat fixed striped posts">
	<thead>
	<tr>
		<td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td><th scope="col" id="title" class="manage-column column-title column-primary sortable desc"><a href="http://plugins:8888/wp-admin/edit.php?ids=1&amp;orderby=title&amp;order=asc"><span>Title</span><span class="sorting-indicator"></span></a></th><th scope="col" id="author" class="manage-column column-author">Author</th><th scope="col" id="categories" class="manage-column column-categories">Categories</th><th scope="col" id="tags" class="manage-column column-tags">Tags</th><th scope="col" id="comments" class="manage-column column-comments num sortable desc"><a href="http://plugins:8888/wp-admin/edit.php?ids=1&amp;orderby=comment_count&amp;order=asc"><span><span class="vers comment-grey-bubble" title="Comments"><span class="screen-reader-text">Comments</span></span></span><span class="sorting-indicator"></span></a></th><th scope="col" id="date" class="manage-column column-date sortable asc"><a href="http://plugins:8888/wp-admin/edit.php?ids=1&amp;orderby=date&amp;order=desc"><span>Date</span><span class="sorting-indicator"></span></a></th>	</tr>
	</thead>

	<tbody id="the-list">
				<tr id="post-17" class="iedit author-self level-0 post-17 type-post status-publish format-standard hentry category-uncategorized">
			<th scope="row" class="check-column">			<label class="screen-reader-text" for="cb-select-17">Select Mohammed P.</label>
			<input id="cb-select-17" type="checkbox" name="post[]" value="17">
			<div class="locked-indicator">
				<span class="locked-indicator-icon" aria-hidden="true"></span>
				<span class="screen-reader-text">“Mohammed P.” is locked</span>
			</div>
		</th><td class="title column-title has-row-actions column-primary page-title" data-colname="Title"><div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
<strong><a class="row-title" href="http://plugins:8888/wp-admin/post.php?post=17&amp;action=edit" aria-label="“Mohammed P.” (Edit)">Mohammed P.</a></strong>

<div class="hidden" id="inline_17">
	<div class="post_title">Mohammed P.</div><div class="post_name">mohammed-p</div>
	<div class="post_author">1</div>
	<div class="comment_status">open</div>
	<div class="ping_status">open</div>
	<div class="_status">publish</div>
	<div class="jj">29</div>
	<div class="mm">12</div>
	<div class="aa">2016</div>
	<div class="hh">21</div>
	<div class="mn">00</div>
	<div class="ss">48</div>
	<div class="post_password"></div><div class="page_template">default</div><div class="post_category" id="category_17">1</div><div class="tags_input" id="post_tag_17"></div><div class="sticky"></div><div class="post_format"></div></div><div class="row-actions"><span class="edit"><a href="http://plugins:8888/wp-admin/post.php?post=17&amp;action=edit" aria-label="Edit “Mohammed P.”">Edit</a> | </span><span class="inline hide-if-no-js"><a href="#" class="editinline" aria-label="Quick edit “Mohammed P.” inline">Quick&nbsp;Edit</a> | </span><span class="trash"><a href="http://plugins:8888/wp-admin/post.php?post=17&amp;action=trash&amp;_wpnonce=db1f30ed23" class="submitdelete" aria-label="Move “Mohammed P.” to the Trash">Trash</a> | </span><span class="view"><a href="http://plugins:8888/2016/12/29/mohammed-p/" rel="permalink" aria-label="View “Mohammed P.”">View</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="author column-author" data-colname="Author"><a href="edit.php?post_type=post&amp;author=1">plugins</a></td><td class="categories column-categories" data-colname="Categories"><a href="edit.php?category_name=uncategorized">Uncategorized</a></td><td class="tags column-tags" data-colname="Tags"><span aria-hidden="true">—</span><span class="screen-reader-text">No tags</span></td><td class="comments column-comments" data-colname="Comments">		<div class="post-com-count-wrapper">
		<a href="http://plugins:8888/wp-admin/edit-comments.php?p=17&amp;comment_status=approved" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">3</span><span class="screen-reader-text">3 comments</span></a><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No pending comments</span></span>		</div>
		</td><td class="date column-date" data-colname="Date">Published<br><abbr title="2016/12/29 9:00:48 pm">2016/12/29</abbr></td>		</tr>
			<tr id="post-15" class="iedit author-self level-0 post-15 type-post status-publish format-standard hentry category-uncategorized">
			<th scope="row" class="check-column">			<label class="screen-reader-text" for="cb-select-15">Select Cassandra Ali</label>
			<input id="cb-select-15" type="checkbox" name="post[]" value="15">
			<div class="locked-indicator">
				<span class="locked-indicator-icon" aria-hidden="true"></span>
				<span class="screen-reader-text">“Cassandra Ali” is locked</span>
			</div>
		</th><td class="title column-title has-row-actions column-primary page-title" data-colname="Title"><div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
<strong><a class="row-title" href="http://plugins:8888/wp-admin/post.php?post=15&amp;action=edit" aria-label="“Cassandra Ali” (Edit)">Cassandra Ali</a></strong>

<div class="hidden" id="inline_15">
	<div class="post_title">Cassandra Ali</div><div class="post_name">cassandra-ali</div>
	<div class="post_author">1</div>
	<div class="comment_status">open</div>
	<div class="ping_status">open</div>
	<div class="_status">publish</div>
	<div class="jj">29</div>
	<div class="mm">12</div>
	<div class="aa">2016</div>
	<div class="hh">21</div>
	<div class="mn">00</div>
	<div class="ss">37</div>
	<div class="post_password"></div><div class="page_template">default</div><div class="post_category" id="category_15">1</div><div class="tags_input" id="post_tag_15"></div><div class="sticky"></div><div class="post_format"></div></div><div class="row-actions"><span class="edit"><a href="http://plugins:8888/wp-admin/post.php?post=15&amp;action=edit" aria-label="Edit “Cassandra Ali”">Edit</a> | </span><span class="inline hide-if-no-js"><a href="#" class="editinline" aria-label="Quick edit “Cassandra Ali” inline">Quick&nbsp;Edit</a> | </span><span class="trash"><a href="http://plugins:8888/wp-admin/post.php?post=15&amp;action=trash&amp;_wpnonce=e6671c5155" class="submitdelete" aria-label="Move “Cassandra Ali” to the Trash">Trash</a> | </span><span class="view"><a href="http://plugins:8888/2016/12/29/cassandra-ali/" rel="permalink" aria-label="View “Cassandra Ali”">View</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="author column-author" data-colname="Author"><a href="edit.php?post_type=post&amp;author=1">plugins</a></td><td class="categories column-categories" data-colname="Categories"><a href="edit.php?category_name=uncategorized">Uncategorized</a></td><td class="tags column-tags" data-colname="Tags"><span aria-hidden="true">—</span><span class="screen-reader-text">No tags</span></td><td class="comments column-comments" data-colname="Comments">		<div class="post-com-count-wrapper">
		<span aria-hidden="true">—</span><span class="screen-reader-text">No comments</span><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No comments</span></span>		</div>
		</td><td class="date column-date" data-colname="Date">Published<br><abbr title="2016/12/29 9:00:37 pm">2016/12/29</abbr></td>		</tr>
			<tr id="post-13" class="iedit author-self level-0 post-13 type-post status-publish format-standard hentry category-uncategorized">
			<th scope="row" class="check-column">			<label class="screen-reader-text" for="cb-select-13">Select Marina P</label>
			<input id="cb-select-13" type="checkbox" name="post[]" value="13">
			<div class="locked-indicator">
				<span class="locked-indicator-icon" aria-hidden="true"></span>
				<span class="screen-reader-text">“Marina P” is locked</span>
			</div>
		</th><td class="title column-title has-row-actions column-primary page-title" data-colname="Title"><div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
<strong><a class="row-title" href="http://plugins:8888/wp-admin/post.php?post=13&amp;action=edit" aria-label="“Marina P” (Edit)">Marina P</a></strong>

<div class="hidden" id="inline_13">
	<div class="post_title">Marina P</div><div class="post_name">marina-p</div>
	<div class="post_author">1</div>
	<div class="comment_status">open</div>
	<div class="ping_status">open</div>
	<div class="_status">publish</div>
	<div class="jj">29</div>
	<div class="mm">12</div>
	<div class="aa">2016</div>
	<div class="hh">21</div>
	<div class="mn">00</div>
	<div class="ss">19</div>
	<div class="post_password"></div><div class="page_template">default</div><div class="post_category" id="category_13">1</div><div class="tags_input" id="post_tag_13"></div><div class="sticky"></div><div class="post_format"></div></div><div class="row-actions"><span class="edit"><a href="http://plugins:8888/wp-admin/post.php?post=13&amp;action=edit" aria-label="Edit “Marina P”">Edit</a> | </span><span class="inline hide-if-no-js"><a href="#" class="editinline" aria-label="Quick edit “Marina P” inline">Quick&nbsp;Edit</a> | </span><span class="trash"><a href="http://plugins:8888/wp-admin/post.php?post=13&amp;action=trash&amp;_wpnonce=30fceff70f" class="submitdelete" aria-label="Move “Marina P” to the Trash">Trash</a> | </span><span class="view"><a href="http://plugins:8888/2016/12/29/marina-p/" rel="permalink" aria-label="View “Marina P”">View</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="author column-author" data-colname="Author"><a href="edit.php?post_type=post&amp;author=1">plugins</a></td><td class="categories column-categories" data-colname="Categories"><a href="edit.php?category_name=uncategorized">Uncategorized</a></td><td class="tags column-tags" data-colname="Tags"><span aria-hidden="true">—</span><span class="screen-reader-text">No tags</span></td><td class="comments column-comments" data-colname="Comments">		<div class="post-com-count-wrapper">
		<a href="http://plugins:8888/wp-admin/edit-comments.php?p=13&amp;comment_status=approved" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">1</span><span class="screen-reader-text">1 comment</span></a><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No pending comments</span></span>		</div>
		</td><td class="date column-date" data-colname="Date">Published<br><abbr title="2016/12/29 9:00:19 pm">2016/12/29</abbr></td>		</tr>
			<tr id="post-11" class="iedit author-self level-0 post-11 type-post status-publish format-standard hentry category-uncategorized">
			<th scope="row" class="check-column">			<label class="screen-reader-text" for="cb-select-11">Select Sharif A</label>
			<input id="cb-select-11" type="checkbox" name="post[]" value="11">
			<div class="locked-indicator">
				<span class="locked-indicator-icon" aria-hidden="true"></span>
				<span class="screen-reader-text">“Sharif A” is locked</span>
			</div>
		</th><td class="title column-title has-row-actions column-primary page-title" data-colname="Title"><div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
<strong><a class="row-title" href="http://plugins:8888/wp-admin/post.php?post=11&amp;action=edit" aria-label="“Sharif A” (Edit)">Sharif A</a></strong>

<div class="hidden" id="inline_11">
	<div class="post_title">Sharif A</div><div class="post_name">sharif-a</div>
	<div class="post_author">1</div>
	<div class="comment_status">open</div>
	<div class="ping_status">open</div>
	<div class="_status">publish</div>
	<div class="jj">29</div>
	<div class="mm">12</div>
	<div class="aa">2016</div>
	<div class="hh">21</div>
	<div class="mn">00</div>
	<div class="ss">10</div>
	<div class="post_password"></div><div class="page_template">default</div><div class="post_category" id="category_11">1</div><div class="tags_input" id="post_tag_11"></div><div class="sticky"></div><div class="post_format"></div></div><div class="row-actions"><span class="edit"><a href="http://plugins:8888/wp-admin/post.php?post=11&amp;action=edit" aria-label="Edit “Sharif A”">Edit</a> | </span><span class="inline hide-if-no-js"><a href="#" class="editinline" aria-label="Quick edit “Sharif A” inline">Quick&nbsp;Edit</a> | </span><span class="trash"><a href="http://plugins:8888/wp-admin/post.php?post=11&amp;action=trash&amp;_wpnonce=a7585a5520" class="submitdelete" aria-label="Move “Sharif A” to the Trash">Trash</a> | </span><span class="view"><a href="http://plugins:8888/2016/12/29/sharif-a/" rel="permalink" aria-label="View “Sharif A”">View</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="author column-author" data-colname="Author"><a href="edit.php?post_type=post&amp;author=1">plugins</a></td><td class="categories column-categories" data-colname="Categories"><a href="edit.php?category_name=uncategorized">Uncategorized</a></td><td class="tags column-tags" data-colname="Tags"><span aria-hidden="true">—</span><span class="screen-reader-text">No tags</span></td><td class="comments column-comments" data-colname="Comments">		<div class="post-com-count-wrapper">
		<a href="http://plugins:8888/wp-admin/edit-comments.php?p=11&amp;comment_status=approved" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">1</span><span class="screen-reader-text">1 comment</span></a><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No pending comments</span></span>		</div>
		</td><td class="date column-date" data-colname="Date">Published<br><abbr title="2016/12/29 9:00:10 pm">2016/12/29</abbr></td>		</tr>
			<tr id="post-9" class="iedit author-self level-0 post-9 type-post status-publish format-standard hentry category-uncategorized">
			<th scope="row" class="check-column">			<label class="screen-reader-text" for="cb-select-9">Select Jonathan D</label>
			<input id="cb-select-9" type="checkbox" name="post[]" value="9">
			<div class="locked-indicator">
				<span class="locked-indicator-icon" aria-hidden="true"></span>
				<span class="screen-reader-text">“Jonathan D” is locked</span>
			</div>
		</th><td class="title column-title has-row-actions column-primary page-title" data-colname="Title"><div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
<strong><a class="row-title" href="http://plugins:8888/wp-admin/post.php?post=9&amp;action=edit" aria-label="“Jonathan D” (Edit)">Jonathan D</a></strong>

<div class="hidden" id="inline_9">
	<div class="post_title">Jonathan D</div><div class="post_name">jonathan-d</div>
	<div class="post_author">1</div>
	<div class="comment_status">open</div>
	<div class="ping_status">open</div>
	<div class="_status">publish</div>
	<div class="jj">29</div>
	<div class="mm">12</div>
	<div class="aa">2016</div>
	<div class="hh">20</div>
	<div class="mn">59</div>
	<div class="ss">56</div>
	<div class="post_password"></div><div class="page_template">default</div><div class="post_category" id="category_9">1</div><div class="tags_input" id="post_tag_9"></div><div class="sticky"></div><div class="post_format"></div></div><div class="row-actions"><span class="edit"><a href="http://plugins:8888/wp-admin/post.php?post=9&amp;action=edit" aria-label="Edit “Jonathan D”">Edit</a> | </span><span class="inline hide-if-no-js"><a href="#" class="editinline" aria-label="Quick edit “Jonathan D” inline">Quick&nbsp;Edit</a> | </span><span class="trash"><a href="http://plugins:8888/wp-admin/post.php?post=9&amp;action=trash&amp;_wpnonce=0973b49d37" class="submitdelete" aria-label="Move “Jonathan D” to the Trash">Trash</a> | </span><span class="view"><a href="http://plugins:8888/2016/12/29/jonathan-d/" rel="permalink" aria-label="View “Jonathan D”">View</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="author column-author" data-colname="Author"><a href="edit.php?post_type=post&amp;author=1">plugins</a></td><td class="categories column-categories" data-colname="Categories"><a href="edit.php?category_name=uncategorized">Uncategorized</a></td><td class="tags column-tags" data-colname="Tags"><span aria-hidden="true">—</span><span class="screen-reader-text">No tags</span></td><td class="comments column-comments" data-colname="Comments">		<div class="post-com-count-wrapper">
		<span aria-hidden="true">—</span><span class="screen-reader-text">No comments</span><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No comments</span></span>		</div>
		</td><td class="date column-date" data-colname="Date">Published<br><abbr title="2016/12/29 8:59:56 pm">2016/12/29</abbr></td>		</tr>
		</tbody>

	<tfoot>
	<tr>
		<td class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-2">Select All</label><input id="cb-select-all-2" type="checkbox"></td><th scope="col" class="manage-column column-title column-primary sortable desc"><a href="http://plugins:8888/wp-admin/edit.php?ids=1&amp;orderby=title&amp;order=asc"><span>Title</span><span class="sorting-indicator"></span></a></th><th scope="col" class="manage-column column-author">Author</th><th scope="col" class="manage-column column-categories">Categories</th><th scope="col" class="manage-column column-tags">Tags</th><th scope="col" class="manage-column column-comments num sortable desc"><a href="http://plugins:8888/wp-admin/edit.php?ids=1&amp;orderby=comment_count&amp;order=asc"><span><span class="vers comment-grey-bubble" title="Comments"><span class="screen-reader-text">Comments</span></span></span><span class="sorting-indicator"></span></a></th><th scope="col" class="manage-column column-date sortable asc"><a href="http://plugins:8888/wp-admin/edit.php?ids=1&amp;orderby=date&amp;order=desc"><span>Date</span><span class="sorting-indicator"></span></a></th>	</tr>
	</tfoot>

</table>
	<div class="tablenav bottom">

				<div class="alignleft actions bulkactions">
			<label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label><select name="action2" id="bulk-action-selector-bottom">
<option value="-1">Bulk Actions</option>
	<option value="edit" class="hide-if-no-js">Edit</option>
	<option value="trash">Move to Trash</option>
</select>
<input type="submit" id="doaction2" class="button action" value="Apply">
		</div>
				<div class="alignleft actions">
		</div>
<div class="tablenav-pages one-page"><span class="displaying-num">5 items</span>
<span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
<span class="screen-reader-text">Current Page</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">1 of <span class="total-pages">1</span></span></span>
<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
<span class="tablenav-pages-navspan" aria-hidden="true">»</span></span></div>
		<br class="clear">
	</div>

</form>


	<form method="get"><table style="display: none"><tbody id="inlineedit">
		
		<tr id="inline-edit" class="inline-edit-row inline-edit-row-post inline-edit-post quick-edit-row quick-edit-row-post inline-edit-post" style="display: none"><td colspan="7" class="colspanchange">

		<fieldset class="inline-edit-col-left">
			<legend class="inline-edit-legend">Quick Edit</legend>
			<div class="inline-edit-col">
	
			<label>
				<span class="title">Title</span>
				<span class="input-text-wrap"><input type="text" name="post_title" class="ptitle" value=""></span>
			</label>

			<label>
				<span class="title">Slug</span>
				<span class="input-text-wrap"><input type="text" name="post_name" value=""></span>
			</label>

	
				<fieldset class="inline-edit-date">
			<legend><span class="title">Date</span></legend>
				<div class="timestamp-wrap"><label><span class="screen-reader-text">Month</span><select name="mm">
			<option value="01" data-text="Jan">01-Jan</option>
			<option value="02" data-text="Feb">02-Feb</option>
			<option value="03" data-text="Mar">03-Mar</option>
			<option value="04" data-text="Apr">04-Apr</option>
			<option value="05" data-text="May">05-May</option>
			<option value="06" data-text="Jun">06-Jun</option>
			<option value="07" data-text="Jul">07-Jul</option>
			<option value="08" data-text="Aug">08-Aug</option>
			<option value="09" data-text="Sep">09-Sep</option>
			<option value="10" data-text="Oct">10-Oct</option>
			<option value="11" data-text="Nov">11-Nov</option>
			<option value="12" data-text="Dec" selected="selected">12-Dec</option>
</select></label> <label><span class="screen-reader-text">Day</span><input type="text" name="jj" value="29" size="2" maxlength="2" autocomplete="off"></label>, <label><span class="screen-reader-text">Year</span><input type="text" name="aa" value="2016" size="4" maxlength="4" autocomplete="off"></label> @ <label><span class="screen-reader-text">Hour</span><input type="text" name="hh" value="21" size="2" maxlength="2" autocomplete="off"></label>:<label><span class="screen-reader-text">Minute</span><input type="text" name="mn" value="00" size="2" maxlength="2" autocomplete="off"></label></div><input type="hidden" id="ss" name="ss" value="48">			</fieldset>
			<br class="clear">
	
	<label class="inline-edit-author"><span class="title">Author</span><select name="post_author" class="authors">
	<option value="2">J S (js)</option>
	<option value="1">plugins (plugins)</option>
</select></label>
			<div class="inline-edit-group wp-clearfix">
				<label class="alignleft">
					<span class="title">Password</span>
					<span class="input-text-wrap"><input type="text" name="post_password" class="inline-edit-password-input" value=""></span>
				</label>

				<em class="alignleft inline-edit-or">
					–OR–				</em>
				<label class="alignleft inline-edit-private">
					<input type="checkbox" name="keep_private" value="private">
					<span class="checkbox-title">Private</span>
				</label>
			</div>

	
		</div></fieldset>

	
		<fieldset class="inline-edit-col-center inline-edit-categories"><div class="inline-edit-col">

	
			<span class="title inline-edit-categories-label">Categories</span>
			<input type="hidden" name="post_category[]" value="0">
			<ul class="cat-checklist category-checklist">
				
<li id="category-1" class="popular-category"><label class="selectit"><input value="1" type="checkbox" name="post_category[]" id="in-category-1"> Uncategorized</label></li>
			</ul>

	
		</div></fieldset>

	
		<fieldset class="inline-edit-col-right"><div class="inline-edit-col">

	
	
	
						<label class="inline-edit-tags">
				<span class="title">Tags</span>
				<textarea data-wp-taxonomy="post_tag" cols="22" rows="1" name="tax_input[post_tag]" class="tax_input_post_tag"></textarea>
			</label>
		
	
	
	
			<div class="inline-edit-group wp-clearfix">
							<label class="alignleft">
					<input type="checkbox" name="comment_status" value="open">
					<span class="checkbox-title">Allow Comments</span>
				</label>
							<label class="alignleft">
					<input type="checkbox" name="ping_status" value="open">
					<span class="checkbox-title">Allow Pings</span>
				</label>
						</div>

	
			<div class="inline-edit-group wp-clearfix">
				<label class="inline-edit-status alignleft">
					<span class="title">Status</span>
					<select name="_status">
												<option value="publish">Published</option>
						<option value="future">Scheduled</option>
												<option value="pending">Pending Review</option>
						<option value="draft">Draft</option>
					</select>
				</label>

	
	
				<label class="alignleft">
					<input type="checkbox" name="sticky" value="sticky">
					<span class="checkbox-title">Make this post sticky</span>
				</label>

	
	
			</div>

	
		</div></fieldset>

			<p class="submit inline-edit-save">
			<button type="button" class="button cancel alignleft">Cancel</button>
			<input type="hidden" id="_inline_edit" name="_inline_edit" value="b753ca22ca">				<button type="button" class="button button-primary save alignright">Update</button>
				<span class="spinner"></span>
						<input type="hidden" name="post_view" value="list">
			<input type="hidden" name="screen" value="edit-post">
						<span class="error" style="display:none"></span>
			<br class="clear">
		</p>
		</td></tr>
	
		<tr id="bulk-edit" class="inline-edit-row inline-edit-row-post inline-edit-post bulk-edit-row bulk-edit-row-post bulk-edit-post" style="display: none"><td colspan="7" class="colspanchange">

		<fieldset class="inline-edit-col-left">
			<legend class="inline-edit-legend">Bulk Edit</legend>
			<div class="inline-edit-col">
				<div id="bulk-title-div">
				<div id="bulk-titles"></div>
			</div>

	
	
	
		</div></fieldset><fieldset class="inline-edit-col-center inline-edit-categories"><div class="inline-edit-col">

	
			<span class="title inline-edit-categories-label">Categories</span>
			<input type="hidden" name="post_category[]" value="0">
			<ul class="cat-checklist category-checklist">
				
<li id="category-1" class="popular-category"><label class="selectit"><input value="1" type="checkbox" name="post_category[]" id="in-category-1"> Uncategorized</label></li>
			</ul>

	
		</div></fieldset>

	
		<fieldset class="inline-edit-col-right"><label class="inline-edit-tags">
				<span class="title">Tags</span>
				<textarea data-wp-taxonomy="post_tag" cols="22" rows="1" name="tax_input[post_tag]" class="tax_input_post_tag"></textarea>
			</label><div class="inline-edit-col">

	<label class="inline-edit-author"><span class="title">Author</span><select name="post_author" class="authors">
	<option value="-1">— No Change —</option>
	<option value="2">J S (js)</option>
	<option value="1">plugins (plugins)</option>
</select></label>
	
	
	
			<div class="inline-edit-group wp-clearfix">
					<label class="alignleft">
				<span class="title">Comments</span>
				<select name="comment_status">
					<option value="">— No Change —</option>
					<option value="open">Allow</option>
					<option value="closed">Do not allow</option>
				</select>
			</label>
					<label class="alignright">
				<span class="title">Pings</span>
				<select name="ping_status">
					<option value="">— No Change —</option>
					<option value="open">Allow</option>
					<option value="closed">Do not allow</option>
				</select>
			</label>
					</div>

	
			<div class="inline-edit-group wp-clearfix">
				<label class="inline-edit-status alignleft">
					<span class="title">Status</span>
					<select name="_status">
							<option value="-1">— No Change —</option>
												<option value="publish">Published</option>
						
							<option value="private">Private</option>
												<option value="pending">Pending Review</option>
						<option value="draft">Draft</option>
					</select>
				</label>

	
	
				<label class="alignright">
					<span class="title">Sticky</span>
					<select name="sticky">
						<option value="-1">— No Change —</option>
						<option value="sticky">Sticky</option>
						<option value="unsticky">Not Sticky</option>
					</select>
				</label>

	
	
			</div>

			<label class="alignleft">
		<span class="title">Format</span>
		<select name="post_format">
			<option value="-1">— No Change —</option>
			<option value="0">Standard</option>
								<option value="aside">Aside</option>
										<option value="image">Image</option>
										<option value="video">Video</option>
										<option value="quote">Quote</option>
										<option value="link">Link</option>
										<option value="gallery">Gallery</option>
										<option value="status">Status</option>
										<option value="audio">Audio</option>
										<option value="chat">Chat</option>
							</select></label>
	
		</div></fieldset>

			<p class="submit inline-edit-save">
			<button type="button" class="button cancel alignleft">Cancel</button>
			<input type="submit" name="bulk_edit" id="bulk_edit" class="button button-primary alignright" value="Update">			<input type="hidden" name="post_view" value="list">
			<input type="hidden" name="screen" value="edit-post">
						<span class="error" style="display:none"></span>
			<br class="clear">
		</p>
		</td></tr>
			</tbody></table></form>

<div id="ajax-response"></div>
<br class="clear">
</div>
';

return $html_content;
}

function dmm_crm_prayer_tools () {
	
	$field1 = '
	       <div class="postbox">

				<h2 class="hndle"><span>Google Analytics</span> <span style="float:right;"><input type="submit" class="button button-small" value="add" /></span></h2>

				<div class="inside">
					<table class="form-table striped ">
											
						<tbody>
							<tr class="row-title">
								<td>Site Name</td>
								<td>Analytics Id</td>
								
								<td>Remove</td>
							</tr>
							<tr>
								<td><a href="#">Pray4City</a></td>
								<td>897047005527492</td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">FindJesus</a></td>
								<td>897047005527492</td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">FindForgiveness</a></td>
								<td>897047005527492</td>
								<td><input type="submit" value="delete" /></td>
							</tr>
						</tbody>
					</table>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox --> 
	       
	       <div class="postbox">

				<h2 class="hndle"><span>API Integrations</span> <span style="float:right;"><input type="submit" class="button button-small" value="add" /></span></h2>

				<div class="inside">
					<table class="form-table striped ">
											
						<tbody>
							<tr class="row-title">
								<td>Service</td>
								<td>API Key</td>
								<td>Remove</td>
							</tr>
							<tr>
								<td><a href="#">Mailchimp</a></td>
								<td>897047005527492</td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">Buffer</a></td>
								<td>897047005527492</td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">Klout</a></td>
								<td>897047005527492</td>
								<td><input type="submit" value="delete" /></td>
							</tr>
						</tbody>
					</table>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox --> 
	       
	       <div class="postbox">

				<h2 class="hndle"><span>Facebook Page Integrations</span> <span style="float:right;"><input type="submit" class="button button-small" value="add" /></span></h2>

				<div class="inside">
					<table class="form-table striped ">
											
						<tbody>
							<tr class="row-title">
								<td>Page Name</td>
								<td>Page Id</td>
								<td>Podio Link</td>
								<td>Remove</td>
							</tr>
							<tr>
								<td><a href="#">Pray4City</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">FindJesus</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">FindForgiveness</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">MessiahTunisia</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">BigPrayers</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
						</tbody>
					</table>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox --> 
			
			
			
			<div class="postbox">

				<h2 class="hndle"><span>Twitter Integration</span> <span style="float:right;"><input type="submit" class="button button-small" value="add" /></span></h2>

				<div class="inside">
					<table class="form-table striped ">
											
						<tbody>
							<tr class="row-title">
								<td>Page Name</td>
								<td>Page Id</td>
								<td>Podio Link</td>
								<td>Remove</td>
							</tr>
							<tr>
								<td><a href="#">Pray4City</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">FindJesus</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">FindForgiveness</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							
						</tbody>
					</table>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox -->
	';
	$field2 = '
	        <div class="postbox">

				<h2 class="hndle"><span>Side Header</span></h2>

				<div class="inside">
					<p>Content</p>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox -->
	';
	
	$html_content = dmm_crm_2_column_open ($field1, $field2);
	
	return $html_content;
}

function dmm_crm_prayer_map () {
	$html_content = '
	<div class="wrap">
	<h1>Prayer Walking Map</a><br>
	<iframe src="/wp-content/plugins/dmm-crm/includes/tracts/index.php" width="100%" height="700px" border="0"></iframe><br>
	</div>
	';
	
	return $html_content;
}

function dmm_crm_outreach_dashboard () {
	
	$field1 = 'Recent Campaigns';
	$field2 = 'Content';
	$field3 = 'Analytics';
	$field4 = 'Content';
	$field5 = 'Outreach Status';
	$field6 = '<iframe src="/wp-content/plugins/dmm-crm/includes/charts/seeking-chart.html" width="100%" height="220px" border="0"></iframe><br>';
	$field7 = 'Location Shortcuts';
	$field8 = '';
	
	$html_content = dmm_crm_post_box ($field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8 );
	
	return $html_content;
}

function dmm_crm_outreach_apps () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_outreach_analytics () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}



function dmm_crm_outreach_tools () {
	
	$field1 = '
	       <div class="postbox">

				<h2 class="hndle"><span>Facebook Page Integrations</span> <span style="float:right;"><input type="submit" class="button button-small" value="add" /></span></h2>

				<div class="inside">
					<table class="form-table striped ">
											
						<tbody>
							<tr class="row-title">
								<td>Page Name</td>
								<td>Page Id</td>
								<td>Podio Link</td>
								<td>Remove</td>
							</tr>
							<tr>
								<td><a href="#">Pray4City</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">FindJesus</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">FindForgiveness</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">MessiahTunisia</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">BigPrayers</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
						</tbody>
					</table>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox --> 
			
			<div class="postbox">

				<h2 class="hndle"><span>Website Leadforms</span> <span style="float:right;"><input type="submit" class="button button-small" value="add" /></span></h2>

				<div class="inside">
					<table class="form-table striped ">
											
						<tbody>
							<tr class="row-title">
								<td>Page Name</td>
								<td>Page Id</td>
								<td>Podio Link</td>
								<td>Remove</td>
							</tr>
							<tr>
								<td><a href="#">Pray4City</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">FindJesus</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							
						</tbody>
					</table>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox --> 
			
			<div class="postbox">

				<h2 class="hndle"><span>Twitter Integration</span> <span style="float:right;"><input type="submit" class="button button-small" value="add" /></span></h2>

				<div class="inside">
					<table class="form-table striped ">
											
						<tbody>
							<tr class="row-title">
								<td>Page Name</td>
								<td>Page Id</td>
								<td>Podio Link</td>
								<td>Remove</td>
							</tr>
							<tr>
								<td><a href="#">Pray4City</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">FindJesus</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							<tr>
								<td><a href="#">FindForgiveness</a></td>
								<td>897047005527492</td>
								<td><input type="checkbox" checked /></td>
								<td><input type="submit" value="delete" /></td>
							</tr>
							
						</tbody>
					</table>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox -->
	';
	$field2 = '
	        <div class="postbox">

				<h2 class="hndle"><span>Side Header</span></h2>

				<div class="inside">
					<p>Content</p>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox -->
	';
	
	$html_content = dmm_crm_2_column_open ($field1, $field2);
	
	return $html_content;
}


function dmm_crm_contacts_activity () {
	$html_content = '
	<div class="wrap">



<h2 class="screen-reader-text">Filter comments list</h2><ul class="subsubsub">
	<li class="all"><a href="http://plugins:8888/wp-admin/edit-comments.php?comment_status=all" class="current">All <span class="count">(<span class="all-count">7</span>)</span></a> |</li>
	<li class="moderated"><a href="http://plugins:8888/wp-admin/edit-comments.php?comment_status=moderated">Pending <span class="count">(<span class="pending-count">0</span>)</span></a> |</li>
	<li class="approved"><a href="http://plugins:8888/wp-admin/edit-comments.php?comment_status=approved">Approved <span class="count">(<span class="approved-count">7</span>)</span></a> |</li>
	<li class="spam"><a href="http://plugins:8888/wp-admin/edit-comments.php?comment_status=spam">Spam <span class="count">(<span class="spam-count">0</span>)</span></a> |</li>
	<li class="trash"><a href="http://plugins:8888/wp-admin/edit-comments.php?comment_status=trash">Trash <span class="count">(<span class="trash-count">1</span>)</span></a></li>
</ul>
<form id="comments-form" method="get">

<p class="search-box">
	<label class="screen-reader-text" for="comment-search-input">Search Comments:</label>
	<input type="search" id="comment-search-input" name="s" value="">
	<input type="submit" id="search-submit" class="button" value="Search Comments"></p>

<input type="hidden" name="comment_status" value="all">
<input type="hidden" name="pagegen_timestamp" value="2016-12-29 21:06:41">

<input type="hidden" name="_total" value="7">
<input type="hidden" name="_per_page" value="20">
<input type="hidden" name="_page" value="1">


<input type="hidden" id="_ajax_fetch_list_nonce" name="_ajax_fetch_list_nonce" value="c402ad8b4c"><input type="hidden" name="_wp_http_referer" value="/wp-admin/edit-comments.php"><input type="hidden" id="_wpnonce" name="_wpnonce" value="fe8885fd76"><input type="hidden" name="_wp_http_referer" value="/wp-admin/edit-comments.php">	<div class="tablenav top">

				<div class="alignleft actions bulkactions">
			<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label><select name="action" id="bulk-action-selector-top">
<option value="-1">Bulk Actions</option>
	<option value="unapprove">Unapprove</option>
	<option value="approve">Approve</option>
	<option value="spam">Mark as Spam</option>
	<option value="trash">Move to Trash</option>
</select>
<input type="submit" id="doaction" class="button action" value="Apply">
		</div>
				<div class="alignleft actions">
			<label class="screen-reader-text" for="filter-by-comment-type">Filter by comment type</label>
			<select id="filter-by-comment-type" name="comment_type">
				<option value="">All comment types</option>
	<option value="comment">Comments</option>
	<option value="pings">Pings</option>
			</select>
<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter"></div><div class="tablenav-pages one-page"><span class="displaying-num">7 items</span>
<span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
<span class="paging-input"><label for="current-page-selector" class="screen-reader-text">Current Page</label><input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging"><span class="tablenav-paging-text"> of <span class="total-pages">1</span></span></span>
<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
<span class="tablenav-pages-navspan" aria-hidden="true">»</span></span></div>
		<br class="clear">
	</div>
<h2 class="screen-reader-text">Comments list</h2><table class="wp-list-table widefat fixed striped comments">
	<thead>
	<tr>
		<td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td><th scope="col" id="author" class="manage-column column-author sortable desc"><a href="http://plugins:8888/wp-admin/edit-comments.php?orderby=comment_author&amp;order=asc"><span>Author</span><span class="sorting-indicator"></span></a></th><th scope="col" id="comment" class="manage-column column-comment column-primary">Comment</th><th scope="col" id="response" class="manage-column column-response sortable desc"><a href="http://plugins:8888/wp-admin/edit-comments.php?orderby=comment_post_ID&amp;order=asc"><span>In Response To</span><span class="sorting-indicator"></span></a></th><th scope="col" id="date" class="manage-column column-date sortable desc"><a href="http://plugins:8888/wp-admin/edit-comments.php?orderby=comment_date&amp;order=asc"><span>Submitted On</span><span class="sorting-indicator"></span></a></th>	</tr>
	</thead>

	<tbody id="the-comment-list" data-wp-lists="list:comment">
		<tr id="comment-8" class="comment byuser comment-author-plugins bypostauthor even thread-even depth-1 approved"><th scope="row" class="check-column">		<label class="screen-reader-text" for="cb-select-8">Select comment</label>
		<input id="cb-select-8" type="checkbox" name="delete_comments[]" value="8">
		</th><td class="author column-author" data-colname="Author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></td><td class="comment column-comment has-row-actions column-primary" data-colname="Comment"><div class="comment-author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></div><p>He is starting a discovery group! First meeting tomorrow.</p>
		<div id="inline-8" class="hidden">
		<textarea class="comment" rows="1" cols="1">He is starting a discovery group! First meeting tomorrow.</textarea>
		<div class="author-email">chris@chasm.solutions</div>
		<div class="author">plugins</div>
		<div class="author-url"></div>
		<div class="comment_status">1</div>
		</div>
		<div class="row-actions"><span class="approve"><a href="comment.php?c=8&amp;action=approvecomment&amp;_wpnonce=483b84f3d1" data-wp-lists="dim:the-comment-list:comment-8:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" aria-label="Approve this comment">Approve</a></span><span class="unapprove"><a href="comment.php?c=8&amp;action=unapprovecomment&amp;_wpnonce=483b84f3d1" data-wp-lists="dim:the-comment-list:comment-8:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" aria-label="Unapprove this comment">Unapprove</a></span><span class="reply hide-if-no-js"> | <a data-comment-id="8" data-post-id="17" data-action="replyto" class="vim-r comment-inline" aria-label="Reply to this comment" href="#">Reply</a></span><span class="quickedit hide-if-no-js"> | <a data-comment-id="8" data-post-id="17" data-action="edit" class="vim-q comment-inline" aria-label="Quick edit this comment inline" href="#">Quick&nbsp;Edit</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=8" aria-label="Edit this comment">Edit</a></span><span class="spam"> | <a href="comment.php?c=8&amp;action=spamcomment&amp;_wpnonce=4e4b56b39b" data-wp-lists="delete:the-comment-list:comment-8::spam=1" class="vim-s vim-destructive" aria-label="Mark this comment as spam">Spam</a></span><span class="trash"> | <a href="comment.php?c=8&amp;action=trashcomment&amp;_wpnonce=4e4b56b39b" data-wp-lists="delete:the-comment-list:comment-8::trash=1" class="delete vim-d vim-destructive" aria-label="Move this comment to the Trash">Trash</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="response column-response" data-colname="In Response To"><div class="response-links"><a href="http://plugins:8888/wp-admin/post.php?post=17&amp;action=edit" class="comments-edit-item-link">Mohammed P.</a><a href="http://plugins:8888/2016/12/29/mohammed-p/" class="comments-view-item-link">View Post</a><span class="post-com-count-wrapper post-com-count-17"><a href="http://plugins:8888/wp-admin/edit-comments.php?p=17&amp;comment_status=approved" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">3</span><span class="screen-reader-text">3 comments</span></a><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No pending comments</span></span></span> </div></td><td class="date column-date" data-colname="Submitted On"><div class="submitted-on"><a href="http://plugins:8888/2016/12/29/mohammed-p/#comment-8">2016/12/29 at 9:04 pm</a></div></td></tr>
<tr id="comment-7" class="comment byuser comment-author-plugins bypostauthor odd alt thread-odd thread-alt depth-1 approved"><th scope="row" class="check-column">		<label class="screen-reader-text" for="cb-select-7">Select comment</label>
		<input id="cb-select-7" type="checkbox" name="delete_comments[]" value="7">
		</th><td class="author column-author" data-colname="Author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></td><td class="comment column-comment has-row-actions column-primary" data-colname="Comment"><div class="comment-author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></div><p>He was baptized a few years ago, but has fallen away since then. He wants to get his life back and is willing to start being coached again.</p>
		<div id="inline-7" class="hidden">
		<textarea class="comment" rows="1" cols="1">He was baptized a few years ago, but has fallen away since then. He wants to get his life back and is willing to start being coached again.</textarea>
		<div class="author-email">chris@chasm.solutions</div>
		<div class="author">plugins</div>
		<div class="author-url"></div>
		<div class="comment_status">1</div>
		</div>
		<div class="row-actions"><span class="approve"><a href="comment.php?c=7&amp;action=approvecomment&amp;_wpnonce=223f3e5b76" data-wp-lists="dim:the-comment-list:comment-7:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" aria-label="Approve this comment">Approve</a></span><span class="unapprove"><a href="comment.php?c=7&amp;action=unapprovecomment&amp;_wpnonce=223f3e5b76" data-wp-lists="dim:the-comment-list:comment-7:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" aria-label="Unapprove this comment">Unapprove</a></span><span class="reply hide-if-no-js"> | <a data-comment-id="7" data-post-id="11" data-action="replyto" class="vim-r comment-inline" aria-label="Reply to this comment" href="#">Reply</a></span><span class="quickedit hide-if-no-js"> | <a data-comment-id="7" data-post-id="11" data-action="edit" class="vim-q comment-inline" aria-label="Quick edit this comment inline" href="#">Quick&nbsp;Edit</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=7" aria-label="Edit this comment">Edit</a></span><span class="spam"> | <a href="comment.php?c=7&amp;action=spamcomment&amp;_wpnonce=f236167e7e" data-wp-lists="delete:the-comment-list:comment-7::spam=1" class="vim-s vim-destructive" aria-label="Mark this comment as spam">Spam</a></span><span class="trash"> | <a href="comment.php?c=7&amp;action=trashcomment&amp;_wpnonce=f236167e7e" data-wp-lists="delete:the-comment-list:comment-7::trash=1" class="delete vim-d vim-destructive" aria-label="Move this comment to the Trash">Trash</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="response column-response" data-colname="In Response To"><div class="response-links"><a href="http://plugins:8888/wp-admin/post.php?post=11&amp;action=edit" class="comments-edit-item-link">Sharif A</a><a href="http://plugins:8888/2016/12/29/sharif-a/" class="comments-view-item-link">View Post</a><span class="post-com-count-wrapper post-com-count-11"><a href="http://plugins:8888/wp-admin/edit-comments.php?p=11&amp;comment_status=approved" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">1</span><span class="screen-reader-text">1 comment</span></a><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No pending comments</span></span></span> </div></td><td class="date column-date" data-colname="Submitted On"><div class="submitted-on"><a href="http://plugins:8888/2016/12/29/sharif-a/#comment-7">2016/12/29 at 9:03 pm</a></div></td></tr>
<tr id="comment-6" class="comment byuser comment-author-plugins bypostauthor even thread-even depth-1 approved"><th scope="row" class="check-column">		<label class="screen-reader-text" for="cb-select-6">Select comment</label>
		<input id="cb-select-6" type="checkbox" name="delete_comments[]" value="6">
		</th><td class="author column-author" data-colname="Author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></td><td class="comment column-comment has-row-actions column-primary" data-colname="Comment"><div class="comment-author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></div><p>She just wanted the bible. No contact expected in the future.</p>
		<div id="inline-6" class="hidden">
		<textarea class="comment" rows="1" cols="1">She just wanted the bible. No contact expected in the future.</textarea>
		<div class="author-email">chris@chasm.solutions</div>
		<div class="author">plugins</div>
		<div class="author-url"></div>
		<div class="comment_status">1</div>
		</div>
		<div class="row-actions"><span class="approve"><a href="comment.php?c=6&amp;action=approvecomment&amp;_wpnonce=03c703013e" data-wp-lists="dim:the-comment-list:comment-6:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" aria-label="Approve this comment">Approve</a></span><span class="unapprove"><a href="comment.php?c=6&amp;action=unapprovecomment&amp;_wpnonce=03c703013e" data-wp-lists="dim:the-comment-list:comment-6:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" aria-label="Unapprove this comment">Unapprove</a></span><span class="reply hide-if-no-js"> | <a data-comment-id="6" data-post-id="13" data-action="replyto" class="vim-r comment-inline" aria-label="Reply to this comment" href="#">Reply</a></span><span class="quickedit hide-if-no-js"> | <a data-comment-id="6" data-post-id="13" data-action="edit" class="vim-q comment-inline" aria-label="Quick edit this comment inline" href="#">Quick&nbsp;Edit</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=6" aria-label="Edit this comment">Edit</a></span><span class="spam"> | <a href="comment.php?c=6&amp;action=spamcomment&amp;_wpnonce=b2384c2817" data-wp-lists="delete:the-comment-list:comment-6::spam=1" class="vim-s vim-destructive" aria-label="Mark this comment as spam">Spam</a></span><span class="trash"> | <a href="comment.php?c=6&amp;action=trashcomment&amp;_wpnonce=b2384c2817" data-wp-lists="delete:the-comment-list:comment-6::trash=1" class="delete vim-d vim-destructive" aria-label="Move this comment to the Trash">Trash</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="response column-response" data-colname="In Response To"><div class="response-links"><a href="http://plugins:8888/wp-admin/post.php?post=13&amp;action=edit" class="comments-edit-item-link">Marina P</a><a href="http://plugins:8888/2016/12/29/marina-p/" class="comments-view-item-link">View Post</a><span class="post-com-count-wrapper post-com-count-13"><a href="http://plugins:8888/wp-admin/edit-comments.php?p=13&amp;comment_status=approved" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">1</span><span class="screen-reader-text">1 comment</span></a><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No pending comments</span></span></span> </div></td><td class="date column-date" data-colname="Submitted On"><div class="submitted-on"><a href="http://plugins:8888/2016/12/29/marina-p/#comment-6">2016/12/29 at 9:02 pm</a></div></td></tr>
<tr id="comment-5" class="comment byuser comment-author-plugins bypostauthor odd alt thread-odd thread-alt depth-1 approved"><th scope="row" class="check-column">		<label class="screen-reader-text" for="cb-select-5">Select comment</label>
		<input id="cb-select-5" type="checkbox" name="delete_comments[]" value="5">
		</th><td class="author column-author" data-colname="Author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></td><td class="comment column-comment has-row-actions column-primary" data-colname="Comment"><div class="comment-author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></div><p>Meet for a second time. He has decided to be baptized.</p>
		<div id="inline-5" class="hidden">
		<textarea class="comment" rows="1" cols="1">Meet for a second time. He has decided to be baptized.</textarea>
		<div class="author-email">chris@chasm.solutions</div>
		<div class="author">plugins</div>
		<div class="author-url"></div>
		<div class="comment_status">1</div>
		</div>
		<div class="row-actions"><span class="approve"><a href="comment.php?c=5&amp;action=approvecomment&amp;_wpnonce=2f1b1bea54" data-wp-lists="dim:the-comment-list:comment-5:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" aria-label="Approve this comment">Approve</a></span><span class="unapprove"><a href="comment.php?c=5&amp;action=unapprovecomment&amp;_wpnonce=2f1b1bea54" data-wp-lists="dim:the-comment-list:comment-5:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" aria-label="Unapprove this comment">Unapprove</a></span><span class="reply hide-if-no-js"> | <a data-comment-id="5" data-post-id="17" data-action="replyto" class="vim-r comment-inline" aria-label="Reply to this comment" href="#">Reply</a></span><span class="quickedit hide-if-no-js"> | <a data-comment-id="5" data-post-id="17" data-action="edit" class="vim-q comment-inline" aria-label="Quick edit this comment inline" href="#">Quick&nbsp;Edit</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=5" aria-label="Edit this comment">Edit</a></span><span class="spam"> | <a href="comment.php?c=5&amp;action=spamcomment&amp;_wpnonce=2e031ffc48" data-wp-lists="delete:the-comment-list:comment-5::spam=1" class="vim-s vim-destructive" aria-label="Mark this comment as spam">Spam</a></span><span class="trash"> | <a href="comment.php?c=5&amp;action=trashcomment&amp;_wpnonce=2e031ffc48" data-wp-lists="delete:the-comment-list:comment-5::trash=1" class="delete vim-d vim-destructive" aria-label="Move this comment to the Trash">Trash</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="response column-response" data-colname="In Response To"><div class="response-links"><a href="http://plugins:8888/wp-admin/post.php?post=17&amp;action=edit" class="comments-edit-item-link">Mohammed P.</a><a href="http://plugins:8888/2016/12/29/mohammed-p/" class="comments-view-item-link">View Post</a><span class="post-com-count-wrapper post-com-count-17"><a href="http://plugins:8888/wp-admin/edit-comments.php?p=17&amp;comment_status=approved" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">3</span><span class="screen-reader-text">3 comments</span></a><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No pending comments</span></span></span> </div></td><td class="date column-date" data-colname="Submitted On"><div class="submitted-on"><a href="http://plugins:8888/2016/12/29/mohammed-p/#comment-5">2016/12/29 at 9:01 pm</a></div></td></tr>
<tr id="comment-4" class="comment byuser comment-author-plugins bypostauthor even thread-even depth-1 approved"><th scope="row" class="check-column">		<label class="screen-reader-text" for="cb-select-4">Select comment</label>
		<input id="cb-select-4" type="checkbox" name="delete_comments[]" value="4">
		</th><td class="author column-author" data-colname="Author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></td><td class="comment column-comment has-row-actions column-primary" data-colname="Comment"><div class="comment-author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></div><p>Just met with Mohammed and he is very interested in following up.</p>
		<div id="inline-4" class="hidden">
		<textarea class="comment" rows="1" cols="1">Just met with Mohammed and he is very interested in following up.</textarea>
		<div class="author-email">chris@chasm.solutions</div>
		<div class="author">plugins</div>
		<div class="author-url"></div>
		<div class="comment_status">1</div>
		</div>
		<div class="row-actions"><span class="approve"><a href="comment.php?c=4&amp;action=approvecomment&amp;_wpnonce=a933963b2a" data-wp-lists="dim:the-comment-list:comment-4:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" aria-label="Approve this comment">Approve</a></span><span class="unapprove"><a href="comment.php?c=4&amp;action=unapprovecomment&amp;_wpnonce=a933963b2a" data-wp-lists="dim:the-comment-list:comment-4:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" aria-label="Unapprove this comment">Unapprove</a></span><span class="reply hide-if-no-js"> | <a data-comment-id="4" data-post-id="17" data-action="replyto" class="vim-r comment-inline" aria-label="Reply to this comment" href="#">Reply</a></span><span class="quickedit hide-if-no-js"> | <a data-comment-id="4" data-post-id="17" data-action="edit" class="vim-q comment-inline" aria-label="Quick edit this comment inline" href="#">Quick&nbsp;Edit</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=4" aria-label="Edit this comment">Edit</a></span><span class="spam"> | <a href="comment.php?c=4&amp;action=spamcomment&amp;_wpnonce=416c190a25" data-wp-lists="delete:the-comment-list:comment-4::spam=1" class="vim-s vim-destructive" aria-label="Mark this comment as spam">Spam</a></span><span class="trash"> | <a href="comment.php?c=4&amp;action=trashcomment&amp;_wpnonce=416c190a25" data-wp-lists="delete:the-comment-list:comment-4::trash=1" class="delete vim-d vim-destructive" aria-label="Move this comment to the Trash">Trash</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="response column-response" data-colname="In Response To"><div class="response-links"><a href="http://plugins:8888/wp-admin/post.php?post=17&amp;action=edit" class="comments-edit-item-link">Mohammed P.</a><a href="http://plugins:8888/2016/12/29/mohammed-p/" class="comments-view-item-link">View Post</a><span class="post-com-count-wrapper post-com-count-17"><a href="http://plugins:8888/wp-admin/edit-comments.php?p=17&amp;comment_status=approved" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">3</span><span class="screen-reader-text">3 comments</span></a><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No pending comments</span></span></span> </div></td><td class="date column-date" data-colname="Submitted On"><div class="submitted-on"><a href="http://plugins:8888/2016/12/29/mohammed-p/#comment-4">2016/12/29 at 9:01 pm</a></div></td></tr>
<tr id="comment-3" class="comment byuser comment-author-plugins bypostauthor odd alt thread-odd thread-alt depth-1 approved"><th scope="row" class="check-column">		<label class="screen-reader-text" for="cb-select-3">Select comment</label>
		<input id="cb-select-3" type="checkbox" name="delete_comments[]" value="3">
		</th><td class="author column-author" data-colname="Author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></td><td class="comment column-comment has-row-actions column-primary" data-colname="Comment"><div class="comment-author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></div><p>One more comment about this thing that we were talking about.</p>
		<div id="inline-3" class="hidden">
		<textarea class="comment" rows="1" cols="1">One more comment about this thing that we were talking about.</textarea>
		<div class="author-email">chris@chasm.solutions</div>
		<div class="author">plugins</div>
		<div class="author-url"></div>
		<div class="comment_status">1</div>
		</div>
		<div class="row-actions"><span class="approve"><a href="comment.php?c=3&amp;action=approvecomment&amp;_wpnonce=70c2774a92" data-wp-lists="dim:the-comment-list:comment-3:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" aria-label="Approve this comment">Approve</a></span><span class="unapprove"><a href="comment.php?c=3&amp;action=unapprovecomment&amp;_wpnonce=70c2774a92" data-wp-lists="dim:the-comment-list:comment-3:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" aria-label="Unapprove this comment">Unapprove</a></span><span class="reply hide-if-no-js"> | <a data-comment-id="3" data-post-id="1" data-action="replyto" class="vim-r comment-inline" aria-label="Reply to this comment" href="#">Reply</a></span><span class="quickedit hide-if-no-js"> | <a data-comment-id="3" data-post-id="1" data-action="edit" class="vim-q comment-inline" aria-label="Quick edit this comment inline" href="#">Quick&nbsp;Edit</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=3" aria-label="Edit this comment">Edit</a></span><span class="spam"> | <a href="comment.php?c=3&amp;action=spamcomment&amp;_wpnonce=21c4987ee0" data-wp-lists="delete:the-comment-list:comment-3::spam=1" class="vim-s vim-destructive" aria-label="Mark this comment as spam">Spam</a></span><span class="trash"> | <a href="comment.php?c=3&amp;action=trashcomment&amp;_wpnonce=21c4987ee0" data-wp-lists="delete:the-comment-list:comment-3::trash=1" class="delete vim-d vim-destructive" aria-label="Move this comment to the Trash">Trash</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="response column-response" data-colname="In Response To"><div class="response-links"><a href="http://plugins:8888/wp-admin/post.php?post=1&amp;action=edit" class="comments-edit-item-link">Hello world!</a><a href="http://plugins:8888/2016/12/13/hello-world/" class="comments-view-item-link">View Post</a><span class="post-com-count-wrapper post-com-count-1"><a href="http://plugins:8888/wp-admin/edit-comments.php?p=1&amp;comment_status=approved" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">2</span><span class="screen-reader-text">2 comments</span></a><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No pending comments</span></span></span> </div></td><td class="date column-date" data-colname="Submitted On"><div class="submitted-on"><a href="http://plugins:8888/2016/12/13/hello-world/#comment-3">2016/12/29 at 8:56 pm</a></div></td></tr>
<tr id="comment-2" class="comment byuser comment-author-plugins bypostauthor even thread-even depth-1 approved"><th scope="row" class="check-column">		<label class="screen-reader-text" for="cb-select-2">Select comment</label>
		<input id="cb-select-2" type="checkbox" name="delete_comments[]" value="2">
		</th><td class="author column-author" data-colname="Author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></td><td class="comment column-comment has-row-actions column-primary" data-colname="Comment"><div class="comment-author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> plugins</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></div><p>Response to that thing that he said.</p>
		<div id="inline-2" class="hidden">
		<textarea class="comment" rows="1" cols="1">Response to that thing that he said.</textarea>
		<div class="author-email">chris@chasm.solutions</div>
		<div class="author">plugins</div>
		<div class="author-url"></div>
		<div class="comment_status">1</div>
		</div>
		<div class="row-actions"><span class="approve"><a href="comment.php?c=2&amp;action=approvecomment&amp;_wpnonce=346d910d41" data-wp-lists="dim:the-comment-list:comment-2:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" aria-label="Approve this comment">Approve</a></span><span class="unapprove"><a href="comment.php?c=2&amp;action=unapprovecomment&amp;_wpnonce=346d910d41" data-wp-lists="dim:the-comment-list:comment-2:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" aria-label="Unapprove this comment">Unapprove</a></span><span class="reply hide-if-no-js"> | <a data-comment-id="2" data-post-id="1" data-action="replyto" class="vim-r comment-inline" aria-label="Reply to this comment" href="#">Reply</a></span><span class="quickedit hide-if-no-js"> | <a data-comment-id="2" data-post-id="1" data-action="edit" class="vim-q comment-inline" aria-label="Quick edit this comment inline" href="#">Quick&nbsp;Edit</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=2" aria-label="Edit this comment">Edit</a></span><span class="spam"> | <a href="comment.php?c=2&amp;action=spamcomment&amp;_wpnonce=d274707721" data-wp-lists="delete:the-comment-list:comment-2::spam=1" class="vim-s vim-destructive" aria-label="Mark this comment as spam">Spam</a></span><span class="trash"> | <a href="comment.php?c=2&amp;action=trashcomment&amp;_wpnonce=d274707721" data-wp-lists="delete:the-comment-list:comment-2::trash=1" class="delete vim-d vim-destructive" aria-label="Move this comment to the Trash">Trash</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="response column-response" data-colname="In Response To"><div class="response-links"><a href="http://plugins:8888/wp-admin/post.php?post=1&amp;action=edit" class="comments-edit-item-link">Hello world!</a><a href="http://plugins:8888/2016/12/13/hello-world/" class="comments-view-item-link">View Post</a><span class="post-com-count-wrapper post-com-count-1"><a href="http://plugins:8888/wp-admin/edit-comments.php?p=1&amp;comment_status=approved" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">2</span><span class="screen-reader-text">2 comments</span></a><span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">0</span><span class="screen-reader-text">No pending comments</span></span></span> </div></td><td class="date column-date" data-colname="Submitted On"><div class="submitted-on"><a href="http://plugins:8888/2016/12/13/hello-world/#comment-2">2016/12/29 at 8:56 pm</a></div></td></tr>
	</tbody>

	<tbody id="the-extra-comment-list" data-wp-lists="list:comment" style="display: none;">
		<tr class="no-items"><td class="colspanchange" colspan="5">No comments found.</td></tr>	</tbody>

	<tfoot>
	<tr>
		<td class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-2">Select All</label><input id="cb-select-all-2" type="checkbox"></td><th scope="col" class="manage-column column-author sortable desc"><a href="http://plugins:8888/wp-admin/edit-comments.php?orderby=comment_author&amp;order=asc"><span>Author</span><span class="sorting-indicator"></span></a></th><th scope="col" class="manage-column column-comment column-primary">Comment</th><th scope="col" class="manage-column column-response sortable desc"><a href="http://plugins:8888/wp-admin/edit-comments.php?orderby=comment_post_ID&amp;order=asc"><span>In Response To</span><span class="sorting-indicator"></span></a></th><th scope="col" class="manage-column column-date sortable desc"><a href="http://plugins:8888/wp-admin/edit-comments.php?orderby=comment_date&amp;order=asc"><span>Submitted On</span><span class="sorting-indicator"></span></a></th>	</tr>
	</tfoot>

</table>
	<div class="tablenav bottom">

				<div class="alignleft actions">
</div><div class="tablenav-pages one-page"><span class="displaying-num">7 items</span>
<span class="pagination-links"><span class="tablenav-pages-navspan" aria-hidden="true">«</span>
<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
<span class="screen-reader-text">Current Page</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">1 of <span class="total-pages">1</span></span></span>
<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
<span class="tablenav-pages-navspan" aria-hidden="true">»</span></span></div>
		<br class="clear">
	</div>
</form>
</div>
	';
	
	return $html_content;
}

function dmm_crm_contacts_contacts () {
	$html_content = '
			
<br>
	<ul class="subsubsub">
		<li class="all">
			<a href="#" class="current">Active 
				<span class="count">(408)</span>
			</a> |
		</li>
		<li class="administrator">
			<a href="#role=administrator">Updates Needed 
				<span class="count">(23)</span>
			</a> |
		</li>
		<li class="administrator">
			<a href="#role=administrator">Closed
				<span class="count"></span>
			</a> |
		</li>
		<li class="contributor">
			<a href="#role=contributor">All 
				<span class="count"></span>
			</a>
		</li>
	</ul>
	<form method="get">
		<p class="search-box">
			<label class="screen-reader-text" for="user-search-input">Search Users:</label>
			<input type="search" id="user-search-input" name="s" value="">
				<input type="submit" id="search-submit" class="button" value="Search Users">
				</p>
				<input type="hidden" id="_wpnonce" name="_wpnonce" value="0f9ceb0774">
					<input type="hidden" name="_wp_http_referer" value="/wp-admin/users.php">
						<div class="tablenav top">
							<div class="alignleft actions bulkactions">
								<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
								<select name="action" id="bulk-action-selector-top">
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
										<option value="administrator">Administrator</option>
									</select>
									<input type="submit" name="changeit" id="changeit" class="button" value="Change">
									</div>
									<div class="tablenav-pages one-page">
										<span class="displaying-num">2 items</span>
										<span class="pagination-links">
											<span class="tablenav-pages-navspan" aria-hidden="true">«</span>
											<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
											<span class="paging-input">
												<label for="current-page-selector" class="screen-reader-text">Current Page</label>
												<input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
													<span class="tablenav-paging-text"> of 
														<span class="total-pages">1</span>
													</span>
												</span>
												<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
												<span class="tablenav-pages-navspan" aria-hidden="true">»</span>
											</span>
										</div>
										<br class="clear">
										</div>
										<h2 class="screen-reader-text">Users list</h2>
		<table class="wp-list-table widefat fixed striped users">
			<thead>
				<tr>
					<td id="cb" class="manage-column column-cb check-column">
						<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
						<input id="cb-select-all-1" type="checkbox">
						</td>
						<th scope="col" id="username" class="manage-column column-username column-primary sortable desc">
							<a href="/wp-admin/users.php?orderby=login&amp;order=asc">
								<span>Username</span>
								<span class="sorting-indicator"></span>
							</a>
						</th>
						<th scope="col" id="name" class="manage-column column-name">Name</th>
						<th scope="col" id="email" class="manage-column column-email sortable desc">
							<a href="/wp-admin/users.php?orderby=email&amp;order=asc">
								<span>Email</span>
								<span class="sorting-indicator"></span>
							</a>
						</th>
						<th scope="col" id="role" class="manage-column column-role">Role</th>
						<th scope="col" id="posts" class="manage-column column-posts num">Posts</th>
					</tr>
				</thead>
				<tbody id="the-list" data-wp-lists="list:user">
					<tr id="user-2">
						<th scope="row" class="check-column">
							<label class="screen-reader-text" for="user_2">Select js</label>
							<input type="checkbox" name="users[]" id="user_2" class="contributor" value="2">
							</th>
							<td class="username column-username has-row-actions column-primary" data-colname="Username">
								<img alt="" src="http://0.gravatar.com/avatar/0279fdb0dd9c93d8f27dcf30d53a1a20?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/0279fdb0dd9c93d8f27dcf30d53a1a20?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32">
									<strong>
										<a href="/wp-admin/admin.php?page=dmm_contacts&tab=single&id=123">Mohammed P.</a>
									</strong>
									<br>
										<div class="row-actions">
											<span class="edit">
												<a href="/wp-admin/user-edit.php?user_id=2&amp;wp_http_referer=%2Fwp-admin%2Fusers.php">Edit</a> | 
											</span>
											<span class="delete">
												<a class="submitdelete" href="users.php?action=delete&amp;user=2&amp;_wpnonce=0f9ceb0774">Delete</a>
											</span>
										</div>
										<button type="button" class="toggle-row">
											<span class="screen-reader-text">Show more details</span>
										</button>
									</td>
									<td class="name column-name" data-colname="Name">J S</td>
									<td class="email column-email" data-colname="Email">
										<a href="mailto:himayrunner@hushmail.com">himayrunner@hushmail.com</a>
									</td>
									<td class="role column-role" data-colname="Role">Contributor</td>
									<td class="posts column-posts num" data-colname="Posts">0</td>
								</tr>
								<tr id="user-1">
									<th scope="row" class="check-column">
										<label class="screen-reader-text" for="user_1">Select plugins</label>
										<input type="checkbox" name="users[]" id="user_1" class="administrator" value="1">
										</th>
										<td class="username column-username has-row-actions column-primary" data-colname="Username">
											<img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32">
												<strong>
													<a href="/wp-admin/admin.php?page=dmm_contacts&tab=single&id=124">Sherif A.</a>
												</strong>
												<br>
													<div class="row-actions">
														<span class="edit">
															<a href="/wp-admin/profile.php?wp_http_referer=%2Fwp-admin%2Fusers.php">Edit</a>
														</span>
													</div>
													<button type="button" class="toggle-row">
														<span class="screen-reader-text">Show more details</span>
													</button>
												</td>
												<td class="name column-name" data-colname="Name">Chris</td>
												<td class="email column-email" data-colname="Email">
													<a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a>
												</td>
												<td class="role column-role" data-colname="Role">Administrator</td>
												<td class="posts column-posts num" data-colname="Posts">
													<a href="edit.php?author=1" class="edit">
														<span aria-hidden="true">1</span>
														<span class="screen-reader-text">1 post by this author</span>
													</a>
												</td>
											</tr>
										</tbody>
										<tfoot>
											<tr>
												<td class="manage-column column-cb check-column">
													<label class="screen-reader-text" for="cb-select-all-2">Select All</label>
													<input id="cb-select-all-2" type="checkbox">
													</td>
													<th scope="col" class="manage-column column-username column-primary sortable desc">
														<a href="/wp-admin/users.php?orderby=login&amp;order=asc">
															<span>Username</span>
															<span class="sorting-indicator"></span>
														</a>
													</th>
													<th scope="col" class="manage-column column-name">Name</th>
													<th scope="col" class="manage-column column-email sortable desc">
														<a href="/wp-admin/users.php?orderby=email&amp;order=asc">
															<span>Email</span>
															<span class="sorting-indicator"></span>
														</a>
													</th>
													<th scope="col" class="manage-column column-role">Role</th>
													<th scope="col" class="manage-column column-posts num">Posts</th>
												</tr>
											</tfoot>
										</table>
										<div class="tablenav bottom">
											<div class="alignleft actions bulkactions">
												<label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
												<select name="action2" id="bulk-action-selector-bottom">
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
														<option value="administrator">Administrator</option>
													</select>
													<input type="submit" name="changeit" id="changeit" class="button" value="Change">
													</div>
													<div class="tablenav-pages one-page">
														<span class="displaying-num">2 items</span>
														<span class="pagination-links">
															<span class="tablenav-pages-navspan" aria-hidden="true">«</span>
															<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
															<span class="screen-reader-text">Current Page</span>
															<span id="table-paging" class="paging-input">
																<span class="tablenav-paging-text">1 of 
																	<span class="total-pages">1</span>
																</span>
															</span>
															<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
															<span class="tablenav-pages-navspan" aria-hidden="true">»</span>
														</span>
													</div>
													<br class="clear">
													</div>
													
													<div class="tablenav">
													<div class="tablenav-pages">
															<span class="displaying-num"></span>
															<a class="first-page disabled" title="Go to first page" href="#">&laquo;</a>
															<a class="prev-page disabled" title="Go to previous page" href="#">&lsaquo;</a>
															<span class="paging-input"><input class="current-page" title="Current page" type="text" 
													name="paged" value="1" size="1" /> of <span class="total-pages">5</span></span>
															<a class="next-page" title="Go to next page" href="#">&rsaquo;</a>
															<a class="last-page" title="Go to last page" href="#">&raquo;</a>
														</div>
													</div>
												</form>

	
	';
	
	return $html_content;
}

function dmm_crm_contacts_add () {
	$html_content = '
		<div class="wrap">
		
			<div id="icon-options-general" class="icon32"></div>
			<h1>Add Contact</h1>
			<ul class="subsubsub">
				<li class="all">
					<a href="users.php" class="current">Source: 
						<span class="count"></span>
					</a> 
				</li>
				<li class="all">
					<a href="users.php" class="current">Facebook 
						<span class="count"></span>
					</a> |
				</li>
				<li class="administrator">
					<a href="users.php?role=administrator">Twitter 
						<span class="count"></span>
					</a> |
				</li>
				<li class="contributor">
					<a href="users.php?role=contributor">Website 
						<span class="count"></span>
					</a> |
				</li> 
				<li class="contributor">
					<a href="users.php?role=contributor">Partner 
						<span class="count"></span>
					</a>
				</li>
			</ul>
			<div id="poststuff">
		
				<div id="post-body" class="metabox-holder columns-2">
		
					<!-- main content -->
					<div id="post-body-content">
		
						<div class="meta-box-sortables ui-sortable">
		
							<div class="postbox">
		
								<h2><span>Facebook Contact Form</span></h2>
		
								<div class="inside">
									<table class="form-table">
										<tbody>
											<tr>
												<th scope="row">Select Facebook Name:*</th>
												<td><input type="text" value="" class="regular-text" /></td>
											</tr>
											<tr>
												<th scope="row">Real Name</th>
												<td><input type="text" value="" class="regular-text" /></td>
											</tr>
											<tr>
												<th scope="row">State:</th>
												<td><select name="" id="">
													<option value="?" selected="selected"></option>
													<option label="Global" value="number:464240618">Global</option>
													<option label="Ben Arous" value="number:399447932">Ben Arous</option>
													<option label="Manouba" value="number:399447931">Manouba</option>
													<option label="Tunis" value="number:399447930">Tunis</option>
													<option label="Ariana" value="number:399447929">Ariana</option>
													<option label="Mednine" value="number:399447928">Mednine</option>
													<option label="Tatouine" value="number:399447926">Tatouine</option>
													<option label="Tozeur" value="number:399447925">Tozeur</option>
													<option label="Gbelli" value="number:399447924">Gbelli</option>
													<option label="Sfax" value="number:399447923">Sfax</option>
													<option label="Mahdia" value="number:399447922">Mahdia</option>
													<option label="Kasserine" value="number:399447921">Kasserine</option>
													<option label="Gafsa" value="number:399447920">Gafsa</option>
													<option label="Gabes" value="number:399447919">Gabes</option>
													<option label="Sidi Bouzid" value="number:399447918">Sidi Bouzid</option>
													<option label="Nabeul" value="number:399447917">Nabeul</option>
													<option label="Monastir" value="number:399447915">Monastir</option>
													<option label="Sousse" value="number:399447914">Sousse</option>
													<option label="Kairouan" value="number:399447913">Kairouan</option>
													<option label="Zaghouan" value="number:399447912">Zaghouan</option>
													<option label="Jendouba" value="number:399447911">Jendouba</option>
													<option label="Siliana" value="number:399447909">Siliana</option>
													<option label="Kef" value="number:399447908">Kef</option>
													<option label="Bizerte" value="number:399447907">Bizerte</option>
													<option label="Béja" value="number:399447906">Béja</option>
												</select></td>
											</tr>
											<tr>
												<th scope="row">Phone:</th>
												<td><input type="text" value="" class="regular-text" /></td>
											</tr>
											<tr>
												<th scope="row">Email:</th>
												<td><input type="text" value="" class="regular-text" /></td>
											</tr>
											<tr>
												<th scope="row">Street:</th>
												<td><input type="text" value="" class="regular-text" /></td>
											</tr>
											<tr>
												<th scope="row">City:</th>
												<td><input type="text" value="" class="regular-text" /></td>
											</tr>
											<tr>
												<th scope="row">State:</th>
												<td><input type="text" value="" class="regular-text" /></td>
											</tr>
											<tr>
												<th scope="row">Postal Code:</th>
												<td><input type="text" value="" class="regular-text" /></td>
											</tr>
											<tr>
												<th scope="row">Gender:</th>
												<td><input type="text" value="" class="regular-text" /></td>
											</tr>
											<tr>
												<th scope="row">Age:</th>
												<td><input type="text" value="" class="regular-text" /></td>
											</tr>
											<tr>
												<th scope="row">Select one of the options for the Bible.</th>
												<td><fieldset>
													<legend class="screen-reader-text"><span>input type="radio"</span></legend>
													<label title="bible">
														<input type="radio" name="bible" value="" />
														<span>They only want a bible mailed.</span>
													</label><br>
													<label title="bible">
														<input type="radio" name="bible" value="" />
														<span>They want to meet with someone.</span>
													</label><br>
													<label title="bible">
														<input type="radio" name="bible" value="" />
														<span>They want a bible mailed and someone to follow up with them.</span>
													</label><br>
													<label title="bible">
														<input type="radio" name="bible" value="" />
														<span>Directed them to a pdf or online link.</span>
													</label><br>
													<label title="bible">
														<input type="radio" name="bible" value="" />
														<span>They do not want the bible yet/unknown.</span>
													</label>
												</fieldset></td>
											</tr>
											<tr>
												<th scope="row">Comments:</th>
												<td><textarea id="" name="" cols="80" rows="6" class="all-options"></textarea></td>
											</tr>
											<tr>
												<th scope="row">Contact is ready to meet someone:</th>
												<td>
													<fieldset>
														<legend class="screen-reader-text"><span>Ready to meet</span></legend>
														<label for="ready_to_meet">
															<input name="ready_to_meet" type="checkbox" id="ready_to_meet" value="1" checked/>
															
														</label>
													</fieldset>
												</td>
											</tr>
											
											<tr>
												<th scope="row"></th>
												<td><input class="button-primary" type="submit" name="Submit" /></td>
											</tr>
										</tbody>
									</table>
									
								</div>
								<!-- .inside -->
		
							</div>
							<!-- .postbox -->
		
						</div>
						<!-- .meta-box-sortables .ui-sortable -->
		
					</div>
					<!-- post-body-content -->
		
					<!-- sidebar -->
					<div id="postbox-container-1" class="postbox-container">
		
						<div class="meta-box-sortables">
		
							<div class="postbox">
		
								<h2><span>Notes</span></h2>
		
								<div class="inside">
									<p>(1) This form connects a Facebook contact to the CRM database for tracking.</p>
									<p>(2) The contact added here is a "qualified" lead. Meaning, the people entered here are showing a high likely interest in moving forward. Filtering out people with little interest improves the overall quality of your system.</p>
								</div>
								<!-- .inside -->
		
							</div>
							<!-- .postbox -->
		
						</div>
						<!-- .meta-box-sortables -->
		
					</div>
					<!-- #postbox-container-1 .postbox-container -->
		
				</div>
				<!-- #post-body .metabox-holder .columns-2 -->
		
				<br class="clear">
			</div>
			<!-- #poststuff -->
		
		</div> <!-- .wrap -->
	
	';
	
	return $html_content;
}


function dmm_crm_contacts_tools () {
	$html_content = '
	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="postbox-container-1" class="postbox-container">
				<div class="meta-box-sortables ">
					<div class="postbox">
						<div class="inside">
							<h2>Header</h2>
							<p>1 Content of some kind.</p>
						</div>
					</div>
				</div>
				<div class="meta-box-sortables ">
					<div class="postbox">
						<div class="inside">
							<h2>Header</h2>
							<p>1 Content of some kind.</p>
						</div>
					</div>
				</div>
			</div>
			<div id="postbox-container-2" class="postbox-container">
				<div class="meta-box-sortables ">
					<div class="postbox">
						<div class="inside">
							<h2>Header</h2>
							<p>2 Content of some kind.</p>
						</div>
					</div>
				</div>
				<div class="meta-box-sortables ">
					<div class="postbox">
						<div class="inside">
							<h2>Header</h2>
							<p>2 Content of some kind.</p>
						</div>
					</div>
				</div>
			</div>
			
		</div>
	</div>
	';
	
	return $html_content;
}

function dmm_crm_contacts_single () {
	$html_content = '
	<style type="text/css">
		.form-table th {border-right: solid 1px #ccc; text-align: right; vertical-align: top;}
	</style>
	<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box-sortables ui-sortable">

					<div class="postbox">
						<h2 class="hndle">Contact Info</h2>
						<div class="inside">
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row">Name</th>';
										
		switch ($_GET["id"]) {
			case "123":
		        $html_content .=  '<td>Mohammed P.</td>';
		        break;
		    default:
		        $html_content .=  '<td>Sherif A..</td>';
		}
		$html_content .= '								
										<td></td>
									</tr>
									<tr>
										<th scope="row">Phone</th>
										<td>720-212-8535 (mobile)<br>720-283-2000 (work)</td>
									</tr>
									<tr>
										<th scope="row">Overall Status</th>
										<td><fieldset>
											<legend class="screen-reader-text"><span>input type="radio"</span></legend>
											<label title="overall" class="button-secondary">
												<input type="radio" name="overall" style="display:none;"  value="Unassignable" />
												<span>Unassignable</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="radio" name="overall" style="display:none;"  value="" checked />
												<span>Unassigned</span>
											</label>
											<label title="overall" class="button-primary">
												<input type="radio" name="overall" style="display:none;"  value="" />
												<span>Assigned</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="radio" name="overall" style="display:none;"  value="" />
												<span>Accepted</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="radio" name="overall" style="display:none;"  value="" />
												<span>On Pause</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="radio" name="overall" style="display:none;"  value="" />
												<span>Closed</span>
											</label>
										</fieldset></td>
									</tr>
									<tr>
										<th scope="row">Seeker Path</th>
										<td><fieldset>
											<legend class="screen-reader-text"><span>input type="radio"</span></legend>
											<label title="overall" class="button-secondary">
												<input type="radio" name="overall" style="display:none;"  value="Unassignable" />
												<span>Contact Attempted</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="radio" name="overall" style="display:none;"  value="" checked />
												<span>Contact Established</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="radio" name="overall" style="display:none;"  value="" />
												<span>Confirms Interest</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="radio" name="overall" style="display:none;"  value="" />
												<span>Meeting Scheduled</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="radio" name="overall" style="display:none;"  value="" />
												<span>First Meeting Complete</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="radio" name="overall" style="display:none;"  value="" />
												<span>Ongoing meetings</span>
											</label>
											<label title="overall" class="button-primary">
												<input type="radio" name="overall" style="display:none;" value="" />
												<span>Being Coached</span>
											</label>
										</fieldset></td>
									</tr>
									<tr>
										<th scope="row">Seeker Milestones</th>
										<td><fieldset>
											<legend class="screen-reader-text"><span>input type="radio"</span></legend>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="Unassignable" />
												<span>States Belief</span>
											</label>
											<label title="overall" class="button-primary">
												<input type="checkbox" name="overall" style="display:none;"  value="" checked />
												<span>Can share Gospel / Testimony</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Sharing Gospel / Testimony</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Baptized</span>
											</label>
											<label title="overall" class="button-primary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Baptizing</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>In Church / Group</span>
											</label>
											<label title="overall" class="button-primary">
												<input type="checkbox" name="overall" style="display:none;" value="" />
												<span>Starting Churches</span>
											</label>
										</fieldset></td>
									</tr>
									<tr>
										<th scope="row">Flags</th>
										<td><fieldset>
											<legend class="screen-reader-text"><span>input type="radio"</span></legend>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="Unassignable" />
												<span>On automatic re-engagement</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" checked />
												<span>Update Needed</span>
											</label>
											
										</fieldset></td>
									</tr>
									<tr>
										<th scope="row">Assigned to</th>
										<td style="vertical-align:top;"><p style="border: solid 1px #ccc; padding:.2em; background-color: #f1f1f1;"><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> <br><a href="#">Chris Wynn</a><p><p><input type="submit" name="add" value="add" class="button button-small" /></p></td>
									</tr>
									<tr>
										<th scope="row">Preferred Contact Method</th>
										<td><select name="select" class="regular-text">
												<option value="1" selected>Phone</option>
												<option value="2" >Skype</option>
												<option value="3" >Facebook</option>
												<option value="4" >Mail</option>
												<option value="5" >Email</option>
											</select></td>
									</tr>
									<tr>
										<th scope="row">Bible</th>
										<td><fieldset>
											<legend class="screen-reader-text"><span>input type="radio"</span></legend>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="Unassignable" />
												<span>Yes - Given by hand</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" checked />
												<span>Yes - Already had one</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Yes - Receipt by mail confirmed</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Bible mailed</span>
											</label>
											<label title="overall" class="button-primary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Needs / Requests Bible</span>
											</label>
										</fieldset></td>
									</tr>
									<tr>
										<th scope="row">Email</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									<tr>
										<th scope="row">Skype</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									<tr>
										<th scope="row">Facebook URL</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									<tr>
										<th scope="row">Initial Contact</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									<tr>
										<th scope="row">Date of last actual contant</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									<tr>
										<th scope="row">Region</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									<tr>
										<th scope="row">Current Understanding / Comprehension</th>
										<td><fieldset>
											<legend class="screen-reader-text"><span>input type="radio"</span></legend>
											<label title="overall" class="button-primary">
												<input type="checkbox" name="overall" style="display:none;"  value="Unassignable" />
												<span>Very Strong</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" checked />
												<span>Strong</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Unknown/Unclear</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Weak</span>
											</label>
											
										</fieldset></td>
									</tr>
									<tr>
										<th scope="row">Investigation with others</th>
										<td><fieldset>
											<legend class="screen-reader-text"><span>input type="radio"</span></legend>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="Unassignable" />
												<span>Not exploring with others</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" checked />
												<span>Only with a few people</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Openly sharing with many</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Studying in a group</span>
											</label>
											
										</fieldset></td>
									</tr>
									<tr>
										<th scope="row">Gender</th>
										<td><fieldset>
											<legend class="screen-reader-text"><span>input type="radio"</span></legend>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="Unassignable" />
												<span>Male</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" checked />
												<span>Female</span>
											</label>
											
										</fieldset></td>
									</tr>
									<tr>
										<th scope="row">Age</th>
										<td><fieldset>
											<legend class="screen-reader-text"><span>input type="radio"</span></legend>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="Unassignable" />
												<span>Under 18</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" checked />
												<span>18 - 25</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>26 - 40</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Over 40</span>
											</label>
											
										</fieldset></td>
									</tr>
									<tr>
										<th scope="row">Mailing Address</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									<tr>
										<th scope="row">Baptism Date</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									<tr>
										<th scope="row">Baptizer(s)</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									<tr>
										<th scope="row">Contact Generation</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									<tr>
										<th scope="row">Preferred Language</th>
										<td><fieldset>
											<legend class="screen-reader-text"><span>input type="radio"</span></legend>
											<label title="overall" class="button-primary">
												<input type="checkbox" name="overall" style="display:none;"  value="Unassignable" />
												<span>English</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" checked />
												<span>French</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Arabic</span>
											</label>
											<label title="overall" class="button-secondary">
												<input type="checkbox" name="overall" style="display:none;"  value="" />
												<span>Spanish</span>
											</label>
											
										</fieldset></td>
									</tr>
									<tr>
										<th scope="row">Source</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									<tr>
										<th scope="row">Reason Closed</th>
										<td><select name="select" class="regular-text">
												<option value="0" ></option>
												<option value="1" >Duplicate</option>
												<option value="2" >Hostile/playing games/self-gain</option>
												<option value="3" >No or bad contact information</option>
												<option value="4" >Already in Church / Connected with others</option>
												<option value="5" >Unknown</option>
												<option value="6" >No longer interested</option>
												<option value="7" >Just wanted book</option>
												<option value="8" >Islamic defender / Muslim evangelist</option>
												
											</select></td>
									</tr>
									
									<tr>
										<th scope="row">Notes</th>
										<td><textarea id="" name="" cols="80" rows="10" class="regular-text" placeholder="..notes"></textarea></td>
									</tr>
									
									<tr>
										<th scope="row">Longitude/Latitude</th>
										<td><input type="submit" name="add" value="add" class="button button-small" /></td>
									</tr>
									
									<tr>
										<th scope="row"></th>
										<td><input class="button-primary" type="submit" name="Submit" /></td>
									</tr>
									
								</tbody>
							</table>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables .ui-sortable -->

			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					
							<div id="commentsdiv" class="postbox " style="display: block;">
								<button type="button" class="handlediv button-link" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Comments</span><span class="toggle-indicator" aria-hidden="true"></span></button><h2 class="hndle ui-sortable-handle"><span>Comments</span></h2>
								<div class="inside">
								<input type="hidden" id="add_comment_nonce" name="add_comment_nonce" value="f3baa215e8">	<p class="hide-if-no-js" id="add-new-comment" style=""><a class="button" href="#commentstatusdiv" onclick="window.commentReply &amp;&amp; commentReply.addcomment(17);return false;">Add comment</a></p>
									<input type="hidden" id="_ajax_fetch_list_nonce" name="_ajax_fetch_list_nonce" value="b4cd5fb83b"><input type="hidden" name="_wp_http_referer" value="/wp-admin/post.php?post=17&amp;action=edit"><table class="widefat fixed striped comments wp-list-table comments-box" style="">
									<tbody id="the-comment-list" data-wp-lists="list:comment">
											<tr id="comment-8" class="comment byuser comment-author-plugins bypostauthor even thread-even depth-1 approved"><td class="author column-author" data-colname="Author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> chris</strong></td><td class="comment column-comment has-row-actions column-primary" data-colname="Comment"><div class="comment-author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> chris</strong></div><p>He is starting a discovery group! First meeting tomorrow.</p>
										<div id="inline-8" class="hidden">
										<textarea class="comment" rows="1" cols="1">He is starting a discovery group! First meeting tomorrow.</textarea>
										<div class="author-email">chris@chasm.solutions</div>
										<div class="author">plugins</div>
										<div class="author-url"></div>
										<div class="comment_status">1</div>
										</div>
										<div class="row-actions"><span class="approve"><a href="comment.php?c=8&amp;action=approvecomment&amp;_wpnonce=483b84f3d1" data-wp-lists="dim:the-comment-list:comment-8:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" aria-label="Approve this comment">Approve</a></span><span class="unapprove"><a href="comment.php?c=8&amp;action=unapprovecomment&amp;_wpnonce=483b84f3d1" data-wp-lists="dim:the-comment-list:comment-8:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" aria-label="Unapprove this comment">Unapprove</a></span><span class="reply"> | <a data-comment-id="8" data-post-id="17" data-action="replyto" class="vim-r comment-inline" aria-label="Reply to this comment" href="#">Reply</a></span><span class="quickedit"> | <a data-comment-id="8" data-post-id="17" data-action="edit" class="vim-q comment-inline" aria-label="Quick edit this comment inline" href="#">Quick&nbsp;Edit</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=8" aria-label="Edit this comment">Edit</a></span><span class="spam"> | <a href="comment.php?c=8&amp;action=spamcomment&amp;_wpnonce=4e4b56b39b" data-wp-lists="delete:the-comment-list:comment-8::spam=1" class="vim-s vim-destructive" aria-label="Mark this comment as spam">Spam</a></span><span class="trash"> | <a href="comment.php?c=8&amp;action=trashcomment&amp;_wpnonce=4e4b56b39b" data-wp-lists="delete:the-comment-list:comment-8::trash=1" class="delete vim-d vim-destructive" aria-label="Move this comment to the Trash">Trash</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td></tr>
								<tr id="comment-5" class="comment byuser comment-author-plugins bypostauthor odd alt thread-odd thread-alt depth-1 approved"><td class="author column-author" data-colname="Author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> chris</strong></td><td class="comment column-comment has-row-actions column-primary" data-colname="Comment"><div class="comment-author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> chris</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></div><p>Meet for a second time. He has decided to be baptized.</p>
										<div id="inline-5" class="hidden">
										<textarea class="comment" rows="1" cols="1">Meet for a second time. He has decided to be baptized.</textarea>
										<div class="author-email">chris@chasm.solutions</div>
										<div class="author">plugins</div>
										<div class="author-url"></div>
										<div class="comment_status">1</div>
										</div>
										<div class="row-actions"><span class="approve"><a href="comment.php?c=5&amp;action=approvecomment&amp;_wpnonce=2f1b1bea54" data-wp-lists="dim:the-comment-list:comment-5:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" aria-label="Approve this comment">Approve</a></span><span class="unapprove"><a href="comment.php?c=5&amp;action=unapprovecomment&amp;_wpnonce=2f1b1bea54" data-wp-lists="dim:the-comment-list:comment-5:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" aria-label="Unapprove this comment">Unapprove</a></span><span class="reply"> | <a data-comment-id="5" data-post-id="17" data-action="replyto" class="vim-r comment-inline" aria-label="Reply to this comment" href="#">Reply</a></span><span class="quickedit"> | <a data-comment-id="5" data-post-id="17" data-action="edit" class="vim-q comment-inline" aria-label="Quick edit this comment inline" href="#">Quick&nbsp;Edit</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=5" aria-label="Edit this comment">Edit</a></span><span class="spam"> | <a href="comment.php?c=5&amp;action=spamcomment&amp;_wpnonce=2e031ffc48" data-wp-lists="delete:the-comment-list:comment-5::spam=1" class="vim-s vim-destructive" aria-label="Mark this comment as spam">Spam</a></span><span class="trash"> | <a href="comment.php?c=5&amp;action=trashcomment&amp;_wpnonce=2e031ffc48" data-wp-lists="delete:the-comment-list:comment-5::trash=1" class="delete vim-d vim-destructive" aria-label="Move this comment to the Trash">Trash</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td></tr>
								<tr id="comment-4" class="comment byuser comment-author-plugins bypostauthor even thread-even depth-1 approved"><td class="author column-author" data-colname="Author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> chris</strong></td><td class="comment column-comment has-row-actions column-primary" data-colname="Comment"><div class="comment-author"><strong><img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"> chris</strong><br><a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a><br><a href="http://plugins:8888/wp-admin/edit-comments.php?s=::1&amp;mode=detail">::1</a></div><p>Just met with Mohammed and he is very interested in following up.</p>
										<div id="inline-4" class="hidden">
										<textarea class="comment" rows="1" cols="1">Just met with Mohammed and he is very interested in following up.</textarea>
										<div class="author-email">chris@chasm.solutions</div>
										<div class="author">plugins</div>
										<div class="author-url"></div>
										<div class="comment_status">1</div>
										</div>
										<div class="row-actions"><span class="approve"><a href="comment.php?c=4&amp;action=approvecomment&amp;_wpnonce=a933963b2a" data-wp-lists="dim:the-comment-list:comment-4:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" aria-label="Approve this comment">Approve</a></span><span class="unapprove"><a href="comment.php?c=4&amp;action=unapprovecomment&amp;_wpnonce=a933963b2a" data-wp-lists="dim:the-comment-list:comment-4:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" aria-label="Unapprove this comment">Unapprove</a></span><span class="reply"> | <a data-comment-id="4" data-post-id="17" data-action="replyto" class="vim-r comment-inline" aria-label="Reply to this comment" href="#">Reply</a></span><span class="quickedit"> | <a data-comment-id="4" data-post-id="17" data-action="edit" class="vim-q comment-inline" aria-label="Quick edit this comment inline" href="#">Quick&nbsp;Edit</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=4" aria-label="Edit this comment">Edit</a></span><span class="spam"> | <a href="comment.php?c=4&amp;action=spamcomment&amp;_wpnonce=416c190a25" data-wp-lists="delete:the-comment-list:comment-4::spam=1" class="vim-s vim-destructive" aria-label="Mark this comment as spam">Spam</a></span><span class="trash"> | <a href="comment.php?c=4&amp;action=trashcomment&amp;_wpnonce=416c190a25" data-wp-lists="delete:the-comment-list:comment-4::trash=1" class="delete vim-d vim-destructive" aria-label="Move this comment to the Trash">Trash</a></span></div><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td></tr>
								</tbody>
								</table>
											<script type="text/javascript">jQuery(document).ready(function(){commentsBox.get(3, 10);});</script>
													<p class="hide-if-no-js" id="show-comments" style="display: none;"><a href="#commentstatusdiv" onclick="commentsBox.load(3);return false;">Show comments</a> <span class="spinner"></span></p>
										<div class="hidden" id="trash-undo-holder">
									<div class="trash-undo-inside">Comment by <strong></strong> moved to the trash. <span class="undo untrash"><a href="#">Undo</a></span></div>
								</div>
								<div class="hidden" id="spam-undo-holder">
									<div class="spam-undo-inside">Comment by <strong></strong> marked as spam. <span class="undo unspam"><a href="#">Undo</a></span></div>
								</div>
								
						</div>
						<!-- .inside -->

					</div>
					<div class="meta-box-sortables ">
						<div class="postbox " ">
						<h2 class="hndle">Activity</h2>
							<div class="inside">
								<p style="font-size: .9em;">Dec. 1, 2016 - Milestone changed to Baptized</p>
								<p style="font-size: .9em;">Nov. 1, 2016 - status changed to Assigned</p>
								<p style="font-size: .9em;">Aug. 26, 2016 - created</p>
							</div>
						</div>
						<div class="postbox">
						<h2 class="hndle">Admin</h2>
							<div class="inside">
								<p><a href="#">delete contact</a></p>
							</div>
						</div>
					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->

			</div>
			<!-- #postbox-container-1 .postbox-container -->

		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div>
	<!-- #poststuff -->

</div> <!-- .wrap -->
	';
	
	return $html_content;
}

function dmm_crm_coaching_dashboard () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_coaching_map () {
	$html_content = '
	<div class="wrap">
	<h1>Coaching Maps</a><br>
	<ul class="subsubsub">
		<li class="all"><a href="edit.php?post_type=post">All <span class="count">(5)</span></a> |</li>
		<li class="publish"><a href="edit.php?post_status=publish&amp;post_type=post">Coaches <span class="count">(80)</span></a> |</li>
		<li class="trash"><a href="edit.php?post_status=trash&amp;post_type=post">Church Planters <span class="count">(23)</span></a></li>
		<li class="trash"><a href="edit.php?post_status=trash&amp;post_type=post">Discovery Groups <span class="count">(3)</span></a></li>
		<li class="trash"><a href="edit.php?post_status=trash&amp;post_type=post">Churches <span class="count">(7)</span></a></li>
	</ul>
	<iframe src="/wp-content/plugins/dmm-crm/includes/tracts/index.php" width="100%" height="700px" border="0"></iframe><br>
	</div>
	';
	
	return $html_content;
}

function dmm_crm_coaching_generations () {
	$html_content = '
	<div class="wrap">
		
		<p>
			<iframe src="';
	
	$html_content .=  plugins_url( 'gen-mapper/index.html', __FILE__ );
	
	$html_content .= '
		" border="0" width="100%" height="2000px"></iframe>
	</p>
	</div>
	';
	
	return $html_content;
}

function dmm_crm_coaching_charts () {
	$field1 = 'Charts';
	$field2 = '
		<p><img src="' . plugins_url( 'img/chart.png', dirname(__FILE__) ) . '" width="100%" ></p>
		
	';
	$field3 = 'Legend';
	$field4 = 'Side content';
	
	$html_content = dmm_crm_2_column ($field1, $field2, $field3, $field4);
	
	return $html_content;
}

function dmm_crm_coaching_statistics () {
	$field1 = 'Statistics';
	$field2 = '
		<p><img src="' . plugins_url( 'img/contact-statuss.png', dirname(__FILE__) ) . '" width="100%" ></p>
		<p><img src="' . plugins_url( 'img/faith-statss.png', dirname(__FILE__) ) . '" width="100%" ></p>
		<p><img src="' . plugins_url( 'img/created.png', dirname(__FILE__) ) . '" width="100%" ></p>
		<p><img src="' . plugins_url( 'img/source-statss.png', dirname(__FILE__) ) . '" width="100%" ></p>
		<p><img src="' . plugins_url( 'img/closed-status.png', dirname(__FILE__) ) . '" width="100%" ></p>
		<p><img src="' . plugins_url( 'img/bible-stats.png', dirname(__FILE__) ) . '" width="100%" ></p>
	
	';
	$field3 = 'Legend';
	$field4 = 'Side content';
	
	$html_content = dmm_crm_2_column ($field1, $field2, $field3, $field4);
	
	return $html_content;
}

function dmm_crm_coaching_tools () {
	$html_content = '
	<p> content section</p>
	';
	
	return $html_content;
}

function dmm_crm_settings_general () {
	$html_content = '
	<div class="wrap">
	
		<div id="icon-options-general" class="icon32"></div>
		
	
		<div id="poststuff">
	
			<div id="post-body" class="metabox-holder columns-2">
	
				<!-- main content -->
				<div id="post-body-content">
	
					<div class="meta-box-sortables ui-sortable">
	
						<div class="postbox">
	
							<h2 class="hndle">General Settings</h2>
	
							<div class="inside">
								<table class="form-table">
									<tbody>
										<tr>
											<th scope="row">Make Site Private</th>
											<td><input type="checkbox" value="1" name="checkbox" checked /> <em>Requires login for entire site (Recommended).</em></td>
										</tr>
										<tr>
											<th scope="row">Discourage Search Engines</th>
											<td><input type="checkbox" value="1" name="checkbox" checked /> <em>Sets robotstxt to No Index/No Follow (Recommended).</em></td>
										</tr>
										<tr>
											<th scope="row">Require approval for new users</th>
											<td><input type="checkbox" value="1" name="checkbox" checked /> <em>Controls signup access (Recommended).</em></td>
										</tr>
										<tr>
											<th scope="row">Field 2</th>
											<td><input type="text" value="" class="regular-text" /> <em>Configures something.</em></td>
										</tr>
										<tr>
											<th scope="row">Field 3</th>
											<td><input type="text" value=""  class="regular-text" /> </td>
										</tr>
										<tr>
											<th scope="row">Field 4</th>
											<td><input type="text" value=""  class="regular-text" /> <em></em></td>
										</tr>
										
										<tr>
											<th scope="row"></th>
											<td><button type="submit" name="Save" class="button-primary" >Save</button></td>
										</tr>
										
									</tbody>
								</table>
							</div>
							<!-- .inside -->
	
						</div>
						<!-- .postbox -->
	
					</div>
					<!-- .meta-box-sortables .ui-sortable -->
					<div class="meta-box-sortables ui-sortable">
	
						<div class="postbox">
	
							<h2 class="hndle"><span>Prayer Settings</span></h2>
	
							<div class="inside">
								<table class="form-table">
									<tbody>
										<tr>
											<th scope="row">Field 1</th>
											<td><input type="checkbox" value="1" name="checkbox" checked /> <em>Configures something.</em></td>
										</tr>
										<tr>
											<th scope="row">Field 2</th>
											<td><input type="text" value="" class="regular-text" /> <em>Configures something.</em></td>
										</tr>
										<tr>
											<th scope="row">Field 3</th>
											<td><input type="text" value=""  class="regular-text" /> </td>
										</tr>
										<tr>
											<th scope="row">Field 4</th>
											<td><input type="text" value=""  class="regular-text" /> <em></em></td>
										</tr>
										
										<tr>
											<th scope="row"></th>
											<td><button type="submit" name="Save" class="button-primary" >Save</button></td>
										</tr>
										
									</tbody>
								</table>
							</div>
							<!-- .inside -->
	
						</div>
						<!-- .postbox -->
	
					</div>
					<!-- .meta-box-sortables .ui-sortable -->
					<div class="meta-box-sortables ui-sortable">
	
						<div class="postbox">
	
							<h2 class="hndle"><span>Outreach Settings</span></h2>
	
							<div class="inside">
								<table class="form-table">
									<tbody>
										<tr>
											<th scope="row">Field 1</th>
											<td><input type="checkbox" value="1" name="checkbox" checked /> <em>Configures something.</em></td>
										</tr>
										<tr>
											<th scope="row">Field 2</th>
											<td><input type="text" value="" class="regular-text" /> <em>Configures something.</em></td>
										</tr>
										<tr>
											<th scope="row">Field 3</th>
											<td><input type="text" value=""  class="regular-text" /> </td>
										</tr>
										<tr>
											<th scope="row">Field 4</th>
											<td><input type="text" value=""  class="regular-text" /> <em></em></td>
										</tr>
										
										<tr>
											<th scope="row"></th>
											<td><button type="submit" name="Save" class="button-primary" >Save</button></td>
										</tr>
										
									</tbody>
								</table>
							</div>
							<!-- .inside -->
	
						</div>
						<!-- .postbox -->
	
					</div>
					<!-- .meta-box-sortables .ui-sortable -->

					<!-- .meta-box-sortables .ui-sortable -->
					<div class="meta-box-sortables ui-sortable">
	
						<div class="postbox">
	
							<h2 class="hndle"><span>Contacts Settings</span></h2>
	
							<div class="inside">
								<table class="form-table">
									<tbody>
										<tr>
											<th scope="row">Field 1</th>
											<td><input type="checkbox" value="1" name="checkbox" checked /> <em>Configures something.</em></td>
										</tr>
										<tr>
											<th scope="row">Field 2</th>
											<td><input type="text" value="" class="regular-text" /> <em>Configures something.</em></td>
										</tr>
										<tr>
											<th scope="row">Field 3</th>
											<td><input type="text" value=""  class="regular-text" /> </td>
										</tr>
										<tr>
											<th scope="row">Field 4</th>
											<td><input type="text" value=""  class="regular-text" /> <em></em></td>
										</tr>
										
										<tr>
											<th scope="row"></th>
											<td><button type="submit" name="Save" class="button-primary" >Save</button></td>
										</tr>
										
									</tbody>
								</table>
							</div>
							<!-- .inside -->
	
						</div>
						<!-- .postbox -->
	
					</div>
					<!-- .meta-box-sortables .ui-sortable -->
					<div class="meta-box-sortables ui-sortable">
	
						<div class="postbox">
	
							<h2 class="hndle"><span>Coaching Settings</span></h2>
	
							<div class="inside">
								<table class="form-table">
									<tbody>
										<tr>
											<th scope="row">Field 1</th>
											<td><input type="checkbox" value="1" name="checkbox" checked /> <em>Configures something.</em></td>
										</tr>
										<tr>
											<th scope="row">Field 2</th>
											<td><input type="text" value="" class="regular-text" /> <em>Configures something.</em></td>
										</tr>
										<tr>
											<th scope="row">Field 3</th>
											<td><input type="text" value=""  class="regular-text" /> </td>
										</tr>
										<tr>
											<th scope="row">Field 4</th>
											<td><input type="text" value=""  class="regular-text" /> <em></em></td>
										</tr>
										
										<tr>
											<th scope="row"></th>
											<td><button type="submit" name="Save" class="button-primary" >Save</button></td>
										</tr>
										
									</tbody>
								</table>
							</div>
							<!-- .inside -->
	
						</div>
						<!-- .postbox -->
	
					</div>
					
					
										
					
	
				</div>
				<!-- post-body-content -->
	
				<!-- sidebar -->
				<div id="postbox-container-1" class="postbox-container">
	
					<div class="meta-box-sortables">
	
						<div class="postbox">
	
							<h2 class="hndle"><span>Recommendations</span></h2>
	
							<div class="inside">
								<p>&#9989; Plugin Installed<br>
								&#9989; Site Private<br>
								&#9989; Signup Controlled<br>
								&#9989; Security Installed<br>
								&#9989; Server OS: CentOS<br>
								&#9989; Apache Installed<br>
								&#9989; PHP version: 5.6<br>
								&#9989; MySQL Running<br>
								&#10071; Admin password strength<br>
								&#10071; RSS disabled<br>
								&#9989; Mobile API Running</p>
							</div>
							<!-- .inside -->
	
						</div>
						<!-- .postbox -->
	
					</div>
					<!-- .meta-box-sortables -->
	
				</div>
				<!-- #postbox-container-1 .postbox-container -->
	
			</div>
			<!-- #post-body .metabox-holder .columns-2 -->
	
			<br class="clear">
		</div>
		<!-- #poststuff -->
	
	</div> <!-- .wrap -->
	
	';
	
	return $html_content;
}

function dmm_crm_settings_users () {
	$html_content = '
	<br>
	<ul class="subsubsub">
		<li class="all">
			<a href="users.php" class="current">All 
				
				<span class="count">(2)</span>
			</a> |
		
		</li>
		<li class="administrator">
			<a href="users.php?role=administrator">Administrator 
				
				<span class="count">(1)</span>
			</a> |
		
		</li>
		<li class="contributor">
			<a href="users.php?role=contributor">Contributor 
				
				<span class="count">(1)</span>
			</a>
		</li>
	</ul>
	<form method="get">
		<p class="search-box">
			<label class="screen-reader-text" for="user-search-input">Search Users:</label>
			<input type="search" id="user-search-input" name="s" value="">
				<input type="submit" id="search-submit" class="button" value="Search Users">
				</p>
				<input type="hidden" id="_wpnonce" name="_wpnonce" value="0f9ceb0774">
					<input type="hidden" name="_wp_http_referer" value="/wp-admin/users.php">
						<div class="tablenav top">
							<div class="alignleft actions bulkactions">
								<label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
								<select name="action" id="bulk-action-selector-top">
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
										<option value="administrator">Administrator</option>
									</select>
									<input type="submit" name="changeit" id="changeit" class="button" value="Change">
									</div>
									<div class="tablenav-pages one-page">
										<span class="displaying-num">2 items</span>
										<span class="pagination-links">
											<span class="tablenav-pages-navspan" aria-hidden="true">«</span>
											<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
											<span class="paging-input">
												<label for="current-page-selector" class="screen-reader-text">Current Page</label>
												<input class="current-page" id="current-page-selector" type="text" name="paged" value="1" size="1" aria-describedby="table-paging">
													<span class="tablenav-paging-text"> of 
														
														<span class="total-pages">1</span>
													</span>
												</span>
												<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
												<span class="tablenav-pages-navspan" aria-hidden="true">»</span>
											</span>
										</div>
										<br class="clear">
										</div>
										<h2 class="screen-reader-text">Users list</h2>
										<table class="wp-list-table widefat fixed striped users">
											<thead>
												<tr>
													<td id="cb" class="manage-column column-cb check-column">
														<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
														<input id="cb-select-all-1" type="checkbox">
														</td>
														<th scope="col" id="username" class="manage-column column-username column-primary sortable desc">
															<a href="http://plugins:8888/wp-admin/users.php?orderby=login&amp;order=asc">
																<span>Username</span>
																<span class="sorting-indicator"></span>
															</a>
														</th>
														<th scope="col" id="name" class="manage-column column-name">Name</th>
														<th scope="col" id="email" class="manage-column column-email sortable desc">
															<a href="http://plugins:8888/wp-admin/users.php?orderby=email&amp;order=asc">
																<span>Email</span>
																<span class="sorting-indicator"></span>
															</a>
														</th>
														<th scope="col" id="role" class="manage-column column-role">Role</th>
														<th scope="col" id="posts" class="manage-column column-posts num">Posts</th>
													</tr>
												</thead>
												<tbody id="the-list" data-wp-lists="list:user">
													<tr id="user-2">
														<th scope="row" class="check-column">
															<label class="screen-reader-text" for="user_2">Select js</label>
															<input type="checkbox" name="users[]" id="user_2" class="contributor" value="2">
															</th>
															<td class="username column-username has-row-actions column-primary" data-colname="Username">
																<img alt="" src="http://0.gravatar.com/avatar/0279fdb0dd9c93d8f27dcf30d53a1a20?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/0279fdb0dd9c93d8f27dcf30d53a1a20?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32">
																	<strong>
																		<a href="http://plugins:8888/wp-admin/user-edit.php?user_id=2&amp;wp_http_referer=%2Fwp-admin%2Fusers.php">js</a>
																	</strong>
																	<br>
																		<div class="row-actions">
																			<span class="edit">
																				<a href="http://plugins:8888/wp-admin/user-edit.php?user_id=2&amp;wp_http_referer=%2Fwp-admin%2Fusers.php">Edit</a> | 
											
																			</span>
																			<span class="delete">
																				<a class="submitdelete" href="users.php?action=delete&amp;user=2&amp;_wpnonce=0f9ceb0774">Delete</a>
																			</span>
																		</div>
																		<button type="button" class="toggle-row">
																			<span class="screen-reader-text">Show more details</span>
																		</button>
																	</td>
																	<td class="name column-name" data-colname="Name">J S</td>
																	<td class="email column-email" data-colname="Email">
																		<a href="mailto:himayrunner@hushmail.com">himayrunner@hushmail.com</a>
																	</td>
																	<td class="role column-role" data-colname="Role">Contributor</td>
																	<td class="posts column-posts num" data-colname="Posts">0</td>
																</tr>
																<tr id="user-1">
																	<th scope="row" class="check-column">
																		<label class="screen-reader-text" for="user_1">Select plugins</label>
																		<input type="checkbox" name="users[]" id="user_1" class="administrator" value="1">
																		</th>
																		<td class="username column-username has-row-actions column-primary" data-colname="Username">
																			<img alt="" src="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=32&amp;d=mm&amp;r=g" srcset="http://0.gravatar.com/avatar/31737ec41c41f003d9a3f30338442829?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32">
																				<strong>
																					<a href="http://plugins:8888/wp-admin/profile.php?wp_http_referer=%2Fwp-admin%2Fusers.php">plugins</a>
																				</strong>
																				<br>
																					<div class="row-actions">
																						<span class="edit">
																							<a href="http://plugins:8888/wp-admin/profile.php?wp_http_referer=%2Fwp-admin%2Fusers.php">Edit</a>
																						</span>
																					</div>
																					<button type="button" class="toggle-row">
																						<span class="screen-reader-text">Show more details</span>
																					</button>
																				</td>
																				<td class="name column-name" data-colname="Name"></td>
																				<td class="email column-email" data-colname="Email">
																					<a href="mailto:chris@chasm.solutions">chris@chasm.solutions</a>
																				</td>
																				<td class="role column-role" data-colname="Role">Administrator</td>
																				<td class="posts column-posts num" data-colname="Posts">
																					<a href="edit.php?author=1" class="edit">
																						<span aria-hidden="true">1</span>
																						<span class="screen-reader-text">1 post by this author</span>
																					</a>
																				</td>
																			</tr>
																		</tbody>
																		<tfoot>
																			<tr>
																				<td class="manage-column column-cb check-column">
																					<label class="screen-reader-text" for="cb-select-all-2">Select All</label>
																					<input id="cb-select-all-2" type="checkbox">
																					</td>
																					<th scope="col" class="manage-column column-username column-primary sortable desc">
																						<a href="http://plugins:8888/wp-admin/users.php?orderby=login&amp;order=asc">
																							<span>Username</span>
																							<span class="sorting-indicator"></span>
																						</a>
																					</th>
																					<th scope="col" class="manage-column column-name">Name</th>
																					<th scope="col" class="manage-column column-email sortable desc">
																						<a href="http://plugins:8888/wp-admin/users.php?orderby=email&amp;order=asc">
																							<span>Email</span>
																							<span class="sorting-indicator"></span>
																						</a>
																					</th>
																					<th scope="col" class="manage-column column-role">Role</th>
																					<th scope="col" class="manage-column column-posts num">Posts</th>
																				</tr>
																			</tfoot>
																		</table>
																		<div class="tablenav bottom">
																			<div class="alignleft actions bulkactions">
																				<label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
																				<select name="action2" id="bulk-action-selector-bottom">
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
																						<option value="administrator">Administrator</option>
																					</select>
																					<input type="submit" name="changeit" id="changeit" class="button" value="Change">
																					</div>
																					<div class="tablenav-pages one-page">
																						<span class="displaying-num">2 items</span>
																						<span class="pagination-links">
																							<span class="tablenav-pages-navspan" aria-hidden="true">«</span>
																							<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>
																							<span class="screen-reader-text">Current Page</span>
																							<span id="table-paging" class="paging-input">
																								<span class="tablenav-paging-text">1 of 
																	
																									<span class="total-pages">1</span>
																								</span>
																							</span>
																							<span class="tablenav-pages-navspan" aria-hidden="true">›</span>
																							<span class="tablenav-pages-navspan" aria-hidden="true">»</span>
																						</span>
																					</div>
																					<br class="clear">
																					</div>
																					<div class="tablenav">
																						<div class="tablenav-pages">
																							<span class="displaying-num"></span>
																							<a class="first-page disabled" title="Go to first page" href="#">&laquo;</a>
																							<a class="prev-page disabled" title="Go to previous page" href="#">&lsaquo;</a>
																							<span class="paging-input">
																								<input class="current-page" title="Current page" type="text" 
													name="paged" value="1" size="1" /> of 
																								<span class="total-pages">5</span>
																							</span>
																							<a class="next-page" title="Go to next page" href="#">&rsaquo;</a>
																							<a class="last-page" title="Go to last page" href="#">&raquo;</a>
																						</div>
																					</div>
																				</form>
	';
	
	return $html_content;
}

function dmm_crm_settings_maps () {
	$html_content = '
	<div class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">
				
					<div class="meta-box-sortables ui-sortable">
	
						<div class="postbox">
	
							<h2 class="hndle"><span>Maps Settings</span></h2>
	
							<div class="inside">
								<table class="form-table">
									<tbody>
										<tr>
											<th scope="row">Location</th>
											<td><input type="submit" value="add" class="button button-small" /></td>
										</tr>
										<tr>
											<th scope="row"></th>
											<td><button type="submit" name="Save" class="button-primary" >Save</button></td>
										</tr>
										
									</tbody>
								</table>
							</div>
							<!-- .inside -->
	
						</div>
						<!-- .postbox -->
	
					</div>
				

			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<div class="postbox">

						<h2 class="hndle"><span>Sidebar Header</span></h2>

						<div class="inside">
							<p>Sidebar content</p>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->

			</div>
			<!-- #postbox-container-1 .postbox-container -->

		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div>
	<!-- #poststuff -->

</div> <!-- .wrap -->
	
	';
	
	return $html_content;
}

function dmm_crm_settings_integrations () {
	$field1 = '
	       <div class="postbox">

				<h2 class="hndle"><span>Chat Integrations</span></h2>

				<div class="inside">
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">Slack</th>
								<td><input type="text" class="regular-text" /></td>
							</tr>
							<tr>
								<th scope="row">WhatsApp</th>
								<td><input type="text" class="regular-text" /></td>
							</tr>
							<tr>
								<th scope="row">Office Team</th>
								<td><input type="text" class="regular-text" /></td>
							</tr>
							<tr>
								<th scope="row"></th>
								<td><input class="button-primary" type="submit" name="Submit" value="Save" /></td>
							</tr>
							
						</tbody>
					</table>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox --> 
	';
	$field2 = '
	        <div class="postbox">

				<h2 class="hndle"><span>Side Header</span></h2>

				<div class="inside">
					<p>Content</p>
				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox -->
	';
	
	$html_content = dmm_crm_2_column_open ($field1, $field2);
	
	return $html_content;
}

function dmm_crm_settings_shortcodes () {
	
	$field1 = 'Main Header';
	$field2 = 'Main content';
	$field3 = 'Side Header';
	$field4 = 'Side content';
	
	$html_content = dmm_crm_2_column ($field1, $field2, $field3, $field4);
	
	return $html_content;
}

function dmm_crm_settings_api () {
	
	$field1 = 'Main Header';
	$field2 = 'Main content';
	$field3 = 'Side Header';
	$field4 = 'Side content';
	
	$html_content = dmm_crm_2_column ($field1, $field2, $field3, $field4);
	
	return $html_content;
}


	
?>