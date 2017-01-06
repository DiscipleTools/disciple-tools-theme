<?php
/*
	Plugin Name: DMM CRM
	Plugin URI: http://chasm.solutions
	Description: This is the DMM CRM for digital marketing to disciple making movements.
	Version: 0.1
	Author: Chasm.Solutions & Kingdom.Training
	Author URI: http://chasm.solutions
	License: GPL2
	
*/

/** Add content functions (Used to keep this page cleaner) */
require_once( 'includes/class-dmm-crm-content.php' );
	
/** Add action and call new menu function */
add_action( 'admin_menu', 'dmm_menu' );

/** DMM CRM menu creation */
function dmm_menu() {
	// Main Menus
	//add_menu_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', string $icon_url = '', int $position = null );
	add_menu_page(  'Dashboard', 'Dashboard', 'dmm_team', 'dmm_dash', 'dmm_dash_options', 'dashicons-welcome-widgets-menus' , '1' );
	add_menu_page(       'Prayer', 'Prayer', 'dmm_team', 'dmm_prayer', 'dmm_prayer_options', 'dashicons-smiley' , '2' );
	add_menu_page(  'Outreach', 'Outreach', 'dmm_team', 'dmm_outreach', 'dmm_outreach_options', 'dashicons-admin-links' , '3' );
	add_menu_page(  'Contacts', 'Contacts', 'dmm_team', 'dmm_contacts', 'dmm_contacts_options', 'dashicons-admin-users' , '4' );
	add_menu_page(  'Coaching', 'Coaching', 'dmm_team', 'dmm_coaching', 'dmm_coaching_options', 'dashicons-admin-multisite' , '5' );
	add_menu_page(  'Settings', 'Settings', 'dmm_team', 'dmm_settings', 'dmm_settings_options', 'dashicons-forms' , '6' );
	
	//add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' );
	// Dashboard Submenu
	add_submenu_page( 'dmm_dash', 'Stats', 'Stats', 'dmm_team', 'dmm_dash_stats', 'dmm_options' );
	add_submenu_page( 'dmm_dash', 'Maps', 'Maps', 'dmm_team', 'dmm_dash_maps', 'dmm_options' );
	
	// Prayer Submenu
	add_submenu_page( 'dmm_prayer', 'Guide', 'Guide', 'dmm_team', 'dmm_prayer_guide', 'dmm_options' );
	add_submenu_page( 'dmm_prayer', 'Map', 'Map', 'dmm_team', 'dmm_prayer_map', 'dmm_options' );
	add_submenu_page( 'dmm_prayer', 'Updates', 'Updates', 'dmm_team', 'dmm_prayer_updates', 'dmm_options' );
	add_submenu_page( 'dmm_prayer', 'Training', 'Training', 'dmm_team', 'dmm_prayer_training', 'dmm_options' );
	
	// Outreach Submenu
	add_submenu_page( 'dmm_outreach', 'Campaigns', 'Campaigns', 'dmm_team', 'dmm_outreach_campaigns', 'dmm_options' );
	add_submenu_page( 'dmm_outreach', 'Analytics', 'Analytics', 'dmm_team', 'dmm_outreach_analytics', 'dmm_options' );
	add_submenu_page( 'dmm_outreach', 'Library', 'Library', 'dmm_team', 'dmm_outreach_library', 'dmm_options' );
	add_submenu_page( 'dmm_outreach', 'Training', 'Training', 'dmm_team', 'dmm_outreach_training', 'dmm_options' );
	
	// Contacts Submenu
	add_submenu_page( 'dmm_contacts', 'Contacts', 'Contacts', 'dmm_team', 'dmm_outreach_campaigns', 'dmm_options' );
	add_submenu_page( 'dmm_contacts', 'Comments', 'Comments', 'dmm_team', 'dmm_outreach_campaigns', 'dmm_options' );
	add_submenu_page( 'dmm_contacts', 'Needs Update', 'Needs Update', 'dmm_team', 'dmm_outreach_library', 'dmm_options' );
	add_submenu_page( 'dmm_contacts', 'Needs Assigned', 'Needs Assigned', 'dmm_router', 'dmm_outreach_analytics', 'dmm_options' );
	add_submenu_page( 'dmm_contacts', 'Training', 'Training', 'dmm_team', 'dmm_outreach_training', 'dmm_options' );
	
	// Coaching Submenu
	add_submenu_page( 'dmm_coaching', 'Campaigns', 'Campaigns', 'dmm_team', 'dmm_outreach_campaigns', 'dmm_options' );
	add_submenu_page( 'dmm_coaching', 'Analytics', 'Analytics', 'dmm_team', 'dmm_outreach_analytics', 'dmm_options' );
	add_submenu_page( 'dmm_coaching', 'Library', 'Library', 'dmm_team', 'dmm_outreach_library', 'dmm_options' );
	add_submenu_page( 'dmm_coaching', 'Training', 'Training', 'dmm_team', 'dmm_outreach_training', 'dmm_options' );
	
	
	// Settings Submenu
	add_submenu_page( 'dmm_settings', 'General', 'General', 'dmm_team', 'dmm_settings_general', 'dmm_options' );
	add_submenu_page( 'dmm_settings', 'Maps', 'Maps', 'dmm_team', 'dmm_settings_maps', 'dmm_options' );
	add_submenu_page( 'dmm_settings', 'Integrations', 'Integrations', 'dmm_team', 'dmm_settings_integrations', 'dmm_options' );
	add_submenu_page( 'dmm_settings', 'Sites', 'Sites', 'dmm_team', 'dmm_settings_sites', 'dmm_options' );
	add_submenu_page( 'dmm_settings', 'About', 'About', 'dmm_team', 'dmm_settings_about', 'dmm_options' );
	
	
}

/** Main Menu functions and page content */

function dmm_options() {
	if ( !current_user_can( 'dmm_team' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	$html = '<div class="wrap">
			 	<h2>DMM OPTIONS PLACEHOLDER</h2>';
	
	
	
	$html .= '</div>'; // end div class wrap
	
	echo $html;
	
}


function dmm_dash_options() {
	if ( !current_user_can( 'dmm_team' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	$html = '<div class="wrap">
			 	<h2>DMM CRM DASHBOARD</h2>';
	
	$html .= dmm_crm_dashboard ();
	
	$html .= '</div>'; // end div class wrap
	
	echo $html;
	
}

function dmm_prayer_options() {
	if ( !current_user_can( 'dmm_team' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/**
	*
	* Begin Header & Tab Bar
	*/
		$tab = $_GET["tab"];
		$tab_link_pre = '<a href="admin.php?page=dmm_prayer&tab=';
		$tab_link_post = '" class="nav-tab ';
		
		$html = '<div class="wrap">
			 	<h2>DMM CRM PRAYER</h2>
			 	<p>"Launch a world changing prayer campaign"</p>
			 	<h2 class="nav-tab-wrapper">';
		
		$html .= $tab_link_pre . 'dashboard' . $tab_link_post;
		if ($tab == 'dashboard' || !isset($tab)) {$html .= 'nav-tab-active';}  
		$html .= '">Dashboard</a>';
		/*
		$html .= $tab_link_pre . 'apps' . $tab_link_post;
		if ($tab == 'apps') {$html .= 'nav-tab-active';}  
		$html .= '">Apps</a>';
		*/
		$html .= $tab_link_pre . 'blog' . $tab_link_post;
		if ($tab == 'blog') {$html .= 'nav-tab-active';}  
		$html .= '">Partner Blog</a>';
		
		$html .= $tab_link_pre . 'map' . $tab_link_post;
		if ($tab == 'map') {$html .= 'nav-tab-active';}  
		$html .= '">Map</a>';
		
		$html .= $tab_link_pre . 'tools' . $tab_link_post;
		if ($tab == 'tools') {$html .= 'nav-tab-active';}  
		$html .= '">Tools</a>';
		
		$html .= '</h2>'; 
	// End Tab Bar
	
	/**
	*
	* Begin Page Content
	*/
		switch ($tab) {
		    case "apps":
		        $html .= dmm_crm_prayer_apps ();
		        break;
		    case "blog":
		        $html .= dmm_crm_prayer_blog ();
		        break;
		    case "map":
		        $html .= dmm_crm_prayer_map ();
		        break;
		    case "tools":
		        $html .= dmm_crm_prayer_tools ();
		        break;
		    default:
		        $html .= dmm_crm_prayer_dashboard ();
		        
		}
	
		$html .= '</div>'; // end div class wrap
		
	echo $html;
	
}

function dmm_outreach_options() {
	if ( !current_user_can( 'dmm_team' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/**
	*
	* Begin Header & Tab Bar
	*/
		$tab = $_GET["tab"];
		$tab_link_pre = '<a href="admin.php?page=dmm_outreach&tab=';
		$tab_link_post = '" class="nav-tab ';
		
		$html = '<div class="wrap">
			 	<h2>DMM CRM OUTREACH</h2>
			 	<p>"Sow the seeds of the gospel generously"</p>
			 	<h2 class="nav-tab-wrapper">';
		
		$html .= $tab_link_pre . 'dashboard' . $tab_link_post;
		if ($tab == 'dashboard' || !isset($tab)) {$html .= 'nav-tab-active';}  
		$html .= '">Dashboard</a>';
		
		$html .= $tab_link_pre . 'apps' . $tab_link_post;
		if ($tab == 'apps') {$html .= 'nav-tab-active';}  
		$html .= '">Apps</a>';
		
		$html .= $tab_link_pre . 'analytics' . $tab_link_post;
		if ($tab == 'analytics') {$html .= 'nav-tab-active';}  
		$html .= '">Analytics</a>';
		
		$html .= $tab_link_pre . 'library' . $tab_link_post;
		if ($tab == 'library') {$html .= 'nav-tab-active';}  
		$html .= '">DMM Library</a>';
		
		$html .= $tab_link_pre . 'tools' . $tab_link_post;
		if ($tab == 'tools') {$html .= 'nav-tab-active';}  
		$html .= '">Tools</a>';
		
		$html .= '</h2>'; 
	// End Tab Bar
	
	/**
	*
	* Begin Page Content
	*/
		switch ($tab) {
		    case "apps":
		        $html .= dmm_crm_2_column_placeholder ();
		        break;
		    case "analytics":
		        $html .= dmm_crm_2_column_placeholder ();
		        break;
		    case "library":
		        $html .= dmm_crm_2_column_placeholder ();
		        break;
		    case "tools":
		        $html .= dmm_crm_outreach_tools ();
		        break;
		    default:
		        $html .= dmm_crm_outreach_dashboard ();
		        
		}
	
		$html .= '</div>'; // end div class wrap
		
	echo $html;
	
}

function dmm_contacts_options() {
	if ( !current_user_can( 'dmm_team' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/**
	*
	* Begin Header & Tab Bar
	*/
		$tab = $_GET["tab"];
		$tab_link_pre = '<a href="admin.php?page=dmm_contacts&tab=';
		$tab_link_post = '" class="nav-tab ';
		
		$html = '<div class="wrap">
			 	<h2>DMM CRM CONTACTS <a href="/wp-admin/admin.php?page=dmm_contacts&tab=add" class="page-title-action">Add New</a></h2>
			 	<p>"Steward the relationships the Spirit brings"</p>
			 	<h2 class="nav-tab-wrapper">';
		
		$html .= $tab_link_pre . 'activity' . $tab_link_post;
		if ($tab == 'activity' || !isset($tab)) {$html .= 'nav-tab-active';}  
		$html .= '">Activity </a>';
		
		$html .= $tab_link_pre . 'contacts' . $tab_link_post;
		if ($tab == 'contacts') {$html .= 'nav-tab-active';}  
		$html .= '">Contacts</a>';
		
		$html .= $tab_link_pre . 'tools' . $tab_link_post;
		if ($tab == 'tools') {$html .= 'nav-tab-active';}  
		$html .= '">Tools</a>';
		
		if ($tab == 'add') {$html .= $tab_link_pre . 'add' . $tab_link_post; $html .= 'nav-tab-active'; $html .= '">Add</a>';}  // tab appears if a selected
		
		if ($tab == 'single') {$html .= $tab_link_pre . 'single' . $tab_link_post; $html .= 'nav-tab-active'; $html .= '">Single Contact</a>';}  // tab appears if a selected
		
		$html .= '</h2>'; 
	// End Tab Bar
	
	/**
	*
	* Begin Page Content
	*/
		switch ($tab) {
		    case "contacts":
		        $html .= dmm_crm_contacts_contacts();
		        break;
		    case "tools":
		        $html .= dmm_crm_post_box_placeholder ();
		        break;
		    case "add":
		        $html .= dmm_crm_contacts_add ();
		        break;
		    case "single":
		        $html .= dmm_crm_contacts_single ();
		        break;
		    default:
		        $html .= dmm_crm_contacts_activity();
		        
		}
	
		$html .= '</div>'; // end div class wrap
		
	echo $html;
	
}


function dmm_coaching_options() {
	if ( !current_user_can( 'dmm_team' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/**
	*
	* Begin Header & Tab Bar
	*/
		$tab = $_GET["tab"];
		$tab_link_pre = '<a href="admin.php?page=dmm_coaching&tab=';
		$tab_link_post = '" class="nav-tab ';
		
		$html = '<div class="wrap">
			 	<h2>DMM CRM COACHING</h2>
			 	<p>"Train obedience and see where the kingdom is not"</p>
			 	<h2 class="nav-tab-wrapper">';
		
		$html .= $tab_link_pre . 'dash' . $tab_link_post;
		if ($tab == 'dash' || !isset($tab)) {$html .= 'nav-tab-active';}  
		$html .= '">Dashboard</a>';
		
		$html .= $tab_link_pre . 'maps' . $tab_link_post;
		if ($tab == 'maps') {$html .= 'nav-tab-active';}  
		$html .= '">Maps</a>';
		
		$html .= $tab_link_pre . 'generations' . $tab_link_post;
		if ($tab == 'generations') {$html .= 'nav-tab-active';}  
		$html .= '">Generations</a>';
		
		$html .= $tab_link_pre . 'charts' . $tab_link_post;
		if ($tab == 'charts') {$html .= 'nav-tab-active';}  
		$html .= '">Charts</a>';
		
		$html .= $tab_link_pre . 'stats' . $tab_link_post;
		if ($tab == 'stats') {$html .= 'nav-tab-active';}  
		$html .= '">Statistics</a>';
		
		$html .= $tab_link_pre . 'tools' . $tab_link_post;
		if ($tab == 'tools') {$html .= 'nav-tab-active';}  
		$html .= '">Tools</a>';
		
		
		
		$html .= '</h2>'; 
	// End Tab Bar
	
	/**
	*
	* Begin Page Content
	*/
		switch ($tab) {
		    case "maps":
		        $html .= dmm_crm_coaching_map () ;
		        break;
		    case "generations":
		        $html .= dmm_crm_coaching_generations ();
		        break;
		    case "charts":
		        $html .= dmm_crm_coaching_charts ();
		        break;
		    case "stats":
		        $html .= dmm_crm_coaching_statistics ();
		        break;
			case "tools":
		        $html .= dmm_crm_2_column_placeholder ();
		        break;
		    default:
		        $html .= dmm_crm_post_box_placeholder ();
		}
		
		$html .= '</div>'; // end div class wrap
		
	echo $html;	
}

function dmm_settings_options() {
	if ( !current_user_can( 'dmm_team' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/**
	*
	* Begin Header & Tab Bar
	*/
		$tab = $_GET["tab"];
		$tab_link_pre = '<a href="admin.php?page=dmm_settings&tab=';
		$tab_link_post = '" class="nav-tab ';
		
		$html = '<div class="wrap">
			 	<h2>DMM CRM SETTINGS</h2>
			 	<p>The core integrations and configurations for the DMM CRM.</p>
				<h2 class="nav-tab-wrapper">';
		
		$html .= $tab_link_pre . 'general' . $tab_link_post;
		if ($tab == 'general' || !isset($tab)) {$html .= 'nav-tab-active';}  
		$html .= '">General</a>';
		
		$html .= $tab_link_pre . 'maps' . $tab_link_post;
		if ($tab == 'maps') {$html .= 'nav-tab-active';}  
		$html .= '">Maps</a>';
		
		$html .= $tab_link_pre . 'users' . $tab_link_post;
		if ($tab == 'users') {$html .= 'nav-tab-active';}  
		$html .= '">Users</a>';
		
		$html .= $tab_link_pre . 'integrations' . $tab_link_post;
		if ($tab == 'integrations') {$html .= 'nav-tab-active';}  
		$html .= '">Integrations</a>';
		
		$html .= $tab_link_pre . 'shortcodes' . $tab_link_post;
		if ($tab == 'shortcodes') {$html .= 'nav-tab-active';}  
		$html .= '">Short Codes</a>';
		
		$html .= $tab_link_pre . 'api' . $tab_link_post;
		if ($tab == 'api') {$html .= 'nav-tab-active';}  
		$html .= '">API</a>';
		
		$html .= '</h2>'; 
	// End Tab Bar
	
	/**
	*
	* Begin Page Content
	*/
		switch ($tab) {
		    case "maps":
		        $html .= dmm_crm_settings_maps ();
		        break;
		    case "users":
		        $html .= dmm_crm_settings_users ();
		        break;
		    case "integrations":
		        $html .= dmm_crm_settings_integrations ();
		        break;
		    case "shortcodes":
		        $html .= dmm_crm_settings_shortcodes ();
		        break;
		    case "api":
		        $html .= dmm_crm_settings_api ();
		        break;
		    default:
		        $html .= dmm_crm_settings_general ();
		}
		
		$html .= '</div>'; // end div class wrap
		
	echo $html;
	
}

	
?>