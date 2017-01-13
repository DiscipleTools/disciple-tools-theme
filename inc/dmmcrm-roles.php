<?php
/**
 *  Setup Roles and Capabilities on Theme install and Remove custom Roles and Capabilities on Theme Switch
 *
 * 
 *
 * @package dmmcrm
 */
 
/* 
* run Once class
* This class is used to check whether the key has run one time since installation. 
* 
*/
if (!class_exists('run_once')){
    class run_once{
        function run($key){
            $test_case = get_option('run_once');
            if (isset($test_case[$key]) && $test_case[$key]){
                return false;
            }else{
                $test_case[$key] = true;
                update_option('run_once',$test_case);
                return true;
            }
        }
         
        function clear($key){
            $test_case = get_option('run_once');
            if (isset($test_case[$key])){
                unset($test_case[$key]);
            }
            update_option('run_once',$test_case);
        }
    }
}

// Create a new instance of the run_once class
$run_once = new run_once;

/* 
* Check is the run_once key 'create_roles' has already run. If not, then run these role additions and deletions.
*/
if ($run_once->run('set_roles')){
    
    if ( get_role( 'marketer' )) { remove_role( 'marketer' ); } 
    add_role( 'marketer', 'Marketer', 
		    	array( 
		    		'delete_others_posts' => true,
					'delete_pages' => true,
					'delete_posts' => true,
					'delete_private_pages' => true,
					'delete_private_posts' => true,
					'delete_published_pages' => true,
					'delete_published_posts' => true,
					'edit_others_pages' => true,
					'edit_others_posts' => true,
					'edit_pages' => true,
					'edit_posts' => true,
					'edit_private_pages' => true,
					'edit_private_posts' => true,
					'edit_published_pages' => true,
					'edit_published_posts' => true,
					'manage_categories' => true,
					'manage_links' => true,
					'moderate_comments' => true,
					'publish_pages' => true,
					'publish_posts' => true,
					'read' => true,
					'read_private_pages' => true,
					'read_private_posts' => true,
					'upload_files' => true,
		    		'level_0' => true 
					) );
	
	
	if ( get_role( 'multiplier' )) { remove_role( 'multiplier' );}
	add_role( 'multiplier', 'Multiplier', 
		    	array( 
		    		'delete_others_posts' => true,
					'delete_pages' => true,
					'delete_posts' => true,
					'delete_private_pages' => true,
					'delete_private_posts' => true,
					'delete_published_pages' => true,
					'delete_published_posts' => true,
					'edit_others_pages' => true,
					'edit_others_posts' => true,
					'edit_pages' => true,
					'edit_posts' => true,
					'edit_private_pages' => true,
					'edit_private_posts' => true,
					'edit_published_pages' => true,
					'edit_published_posts' => true,
					'manage_categories' => true,
					'manage_links' => true,
					'moderate_comments' => true,
					'publish_pages' => true,
					'publish_posts' => true,
					'read' => true,
					'read_private_pages' => true,
					'read_private_posts' => true,
					'upload_files' => true, 
		    		'level_0' => true 
					) );
	
	if ( get_role( 'multiplier_leader' )) { remove_role( 'multiplier_leader' );}
	add_role( 'multiplier_leader', 'Multiplier Leader', 
		    	array( 
		    		'delete_others_posts' => true,
					'delete_pages' => true,
					'delete_posts' => true,
					'delete_private_pages' => true,
					'delete_private_posts' => true,
					'delete_published_pages' => true,
					'delete_published_posts' => true,
					'edit_others_pages' => true,
					'edit_others_posts' => true,
					'edit_pages' => true,
					'edit_posts' => true,
					'edit_private_pages' => true,
					'edit_private_posts' => true,
					'edit_published_pages' => true,
					'edit_published_posts' => true,
					'manage_categories' => true,
					'manage_links' => true,
					'moderate_comments' => true,
					'publish_pages' => true,
					'publish_posts' => true,
					'read' => true,
					'read_private_pages' => true,
					'read_private_posts' => true,
					'upload_files' => true, 
		    		'level_0' => true 
					) );
	
	if ( get_role( 'dispatcher' )) { remove_role( 'dispatcher' );}
	add_role( 'dispatcher', 'Dispatcher', 
		    	array( 
		    		'delete_others_posts' => true,
					'delete_pages' => true,
					'delete_posts' => true,
					'delete_private_pages' => true,
					'delete_private_posts' => true,
					'delete_published_pages' => true,
					'delete_published_posts' => true,
					'edit_others_pages' => true,
					'edit_others_posts' => true,
					'edit_pages' => true,
					'edit_posts' => true,
					'edit_private_pages' => true,
					'edit_private_posts' => true,
					'edit_published_pages' => true,
					'edit_published_posts' => true,
					'manage_categories' => true,
					'manage_links' => true,
					'moderate_comments' => true,
					'publish_pages' => true,
					'publish_posts' => true,
					'read' => true,
					'read_private_pages' => true,
					'read_private_posts' => true,
					'upload_files' => true, 
		    		'level_0' => true 
					) );
	
	if ( get_role( 'prayer_supporter' )) { remove_role( 'prayer_supporter' );}
	add_role( 'prayer_supporter', 'Prayer Supporter', 
		    	array( 
		    		'read' => true, 
		    		'level_0' => true 
					) );
	
	if ( get_role( 'project_supporter' )) { remove_role( 'project_supporter' );}
	add_role( 'project_supporter', 'Project Supporter', 
		    	array( 
		    		'read' => true, 
		    		'level_0' => true 
					) );				
	
	remove_role( 'subscriber' );
	remove_role( 'contributor' );
	remove_role( 'editor' );
	remove_role( 'author' );
	
}

// Set the default role to "prayer_supporter"
add_filter('pre_option_default_role', function($default_role){
    // You can also add conditional tags here and return whatever
    return 'prayer_supporter';  
    
});


/* 
* Reset Role on Theme Switch
*/
function reset_dmm_roles () {
	delete_option('run_once');
	
	remove_role( 'dispatcher' );
	remove_role( 'multiplier' );
	remove_role( 'multiplier_leader' );
	remove_role( 'marketer' );
	remove_role( 'prayer_supporter' );
	remove_role( 'project_supporter' );
	
	add_role( 'subscriber', 'Subscriber', 
		    	array( 
		    		'delete_others_posts' => true,
					'delete_pages' => true,
					'delete_posts' => true,
					'delete_private_pages' => true,
					'delete_private_posts' => true,
					'delete_published_pages' => true,
					'delete_published_posts' => true,
					'edit_others_pages' => true,
					'edit_others_posts' => true,
					'edit_pages' => true,
					'edit_posts' => true,
					'edit_private_pages' => true,
					'edit_private_posts' => true,
					'edit_published_pages' => true,
					'edit_published_posts' => true,
					'manage_categories' => true,
					'manage_links' => true,
					'moderate_comments' => true,
					'publish_pages' => true,
					'publish_posts' => true,
					'read' => true,
					'read_private_pages' => true,
					'read_private_posts' => true,
					'upload_files' => true 
		    		) );
	
	add_role( 'editor', 'Editor', 
		    	array( 
		    		'delete_others_posts' => true,
					'delete_pages' => true,
					'delete_posts' => true,
					'delete_private_pages' => true,
					'delete_private_posts' => true,
					'delete_published_pages' => true,
					'delete_published_posts' => true,
					'edit_others_pages' => true,
					'edit_others_posts' => true,
					'edit_pages' => true,
					'edit_posts' => true,
					'edit_private_pages' => true,
					'edit_private_posts' => true,
					'edit_published_pages' => true,
					'edit_published_posts' => true,
					'manage_categories' => true,
					'manage_links' => true,
					'moderate_comments' => true,
					'publish_pages' => true,
					'publish_posts' => true,
					'read' => true,
					'read_private_pages' => true,
					'read_private_posts' => true,
					'upload_files' => true, 
		    		'level_0' => true 
					) );
	add_role( 'author', 'Author', 
		    	array( 
		    		'delete_posts' => true,
					'delete_published_posts' => true,
					'edit_posts' => true,
					'edit_published_posts' => true,
					'publish_posts' => true,
					'read' => true,
					'upload_files' => true
		    		) );
		    		
	add_role( 'contributor', 'Contributor', 
		    	array( 
		    		'delete_posts' => true,
					'edit_posts' => true,
					'read' => true
					) );
	
	add_filter('pre_option_default_role', function($default_role){
    	return 'subscriber';
    	});
}

add_action('switch_theme', 'reset_dmm_roles');

 
 
 ?>