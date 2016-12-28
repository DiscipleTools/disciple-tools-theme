<?php
/*
	Plugin Name: DMM CRM
	Plugin URI: http://chasm.solutions
	Description: This is the DMM CRM for digital marketing to disciple making movements.
	Version: 0.1
	Author: By Us
	Author URI: http://chasm.solutions
	License: GPL2
	
*/

/** Add content functions (Used to keep this page cleaner) */
require_once( 'includes/class-dmm-crm-content.php' );
	
/** Add action and call new menu function */
add_action( 'admin_menu', 'dmm_menu' );

/** DMM CRM menu creation */
function dmm_menu() {
	//add_menu_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', string $icon_url = '', int $position = null );
	add_menu_page( 'Dashboard', 'DMM CRM', 'manage_options', 'dmm', 'dmm_dash_options', '' , '2' );
	
	//add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' );
	add_submenu_page( 'dmm', 'Contacts', 'Contacts', 'manage_options', 'dmm_contacts', 'dmm_contacts_options' );
	add_submenu_page( 'dmm', 'Reports', 'Reports', 'manage_options', 'dmm_reports', 'dmm_reports_options' );
	add_submenu_page( 'dmm', 'Maps', 'Maps', 'manage_options', 'dmm_maps', 'dmm_maps_options' );
	add_submenu_page( 'dmm', 'Library', 'Library', 'manage_options', 'dmm_library', 'dmm_library_options' );
	add_submenu_page( 'dmm', 'Help', 'Help', 'manage_options', 'dmm_help', 'dmm_help_options' );
	add_submenu_page( 'dmm', 'Settings', 'Settings', 'manage_options', 'dmm_settings', 'dmm_settings_options' );
	
	
}

/** Main Menu functions and page content */
function dmm_dash_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>DMM CRM DASHBOARD</h1><p>Here is where shortcuts, training, rollup statistics, quick forms could be placed.</p>';
	echo '</div>';
	
}

function dmm_contacts_options() {
	if ( !current_user_can( 'manage_options' ) )  {
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
			 	<h2>DMM CRM CONTACTS</h2>
			 	<p>List of recent leads.</p>
				<h2 class="nav-tab-wrapper">';
		
		$html .= $tab_link_pre . 'contacts' . $tab_link_post;
		if ($tab == 'contacts' || !isset($tab)) {$html .= 'nav-tab-active';}  
		$html .= '">Contacts</a>';
		
		$html .= $tab_link_pre . 'add' . $tab_link_post;
		if ($tab == 'add') {$html .= 'nav-tab-active';}  
		$html .= '">Add</a>';
		
		$html .= $tab_link_pre . 'activity' . $tab_link_post;
		if ($tab == 'activity') {$html .= 'nav-tab-active';}  
		$html .= '">Activity</a>';
		
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
		    case "add":
		        $html .= dmm_crm_contacts_add ();
		        break;
		    case "activity":
		        $html .= dmm_crm_contacts_activity();
		        break;
		    case "tools":
		        $html .= dmm_crm_contacts_tools ();
		        break;
		    default:
		        $html .= dmm_crm_contacts_contacts();
		        
		}
	
		$html .= '</div>'; // end div class wrap
		
	echo $html;
	
}


function dmm_reports_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/**
	*
	* Begin Header & Tab Bar
	*/
		$tab = $_GET["tab"];
		$tab_link_pre = '<a href="admin.php?page=dmm_reports&tab=';
		$tab_link_post = '" class="nav-tab ';
		
		$html = '<div class="wrap">
			 	<h2>DMM CRM REPORTS</h2>
			 	<p>List of visuals and statistics from the DMM effort.</p>
				<h2 class="nav-tab-wrapper">';
		
		$html .= $tab_link_pre . 'overview' . $tab_link_post;
		if ($tab == 'overview' || !isset($tab)) {$html .= 'nav-tab-active';}  
		$html .= '">Overview</a>';
		
		$html .= $tab_link_pre . 'charts' . $tab_link_post;
		if ($tab == 'charts') {$html .= 'nav-tab-active';}  
		$html .= '">Charts</a>';
		
		$html .= $tab_link_pre . 'generations' . $tab_link_post;
		if ($tab == 'generations') {$html .= 'nav-tab-active';}  
		$html .= '">Generations</a>';
		
		$html .= '</h2>'; 
	// End Tab Bar
	
	/**
	*
	* Begin Page Content
	*/
		switch ($tab) {
		    case "charts":
		        $html .= dmm_crm_reports_charts() ;
		        break;
		    case "generations":
		        $html .= dmm_crm_reports_generations ();
		        break;
		    default:
		        $html .= dmm_crm_reports_overview ();
		}
		
		$html .= '</div>'; // end div class wrap
		
	echo $html;	
}

function dmm_maps_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/**
	*
	* Begin Header & Tab Bar
	*/
		$tab = $_GET["tab"];
		$tab_link_pre = '<a href="admin.php?page=dmm_maps&tab=';
		$tab_link_post = '" class="nav-tab ';
		
		$html = '<div class="wrap">
			 	<h2>DMM CRM MAPS</h2>
			 	<p>Geo location maps.</p>
				<h2 class="nav-tab-wrapper">';
		
		$html .= $tab_link_pre . 'tracts' . $tab_link_post;
		if ($tab == 'tracts' || !isset($tab)) {$html .= 'nav-tab-active';}  
		$html .= '">Tracts</a>';
		
		$html .= $tab_link_pre . 'charts' . $tab_link_post;
		if ($tab == 'charts') {$html .= 'nav-tab-active';}  
		$html .= '">Charts</a>';
		
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
		    case "charts":
		        $html .= dmm_crm_maps_charts ();
		        break;
		    case "tools":
		        $html .= dmm_crm_maps_tools ();
		        break;
		    default:
		        $html .= dmm_crm_maps_tracts ();
		}
		
		$html .= '</div>'; // end div class wrap
		
	echo $html;
		
}

function dmm_library_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/**
	*
	* Begin Header & Tab Bar
	*/
		$tab = $_GET["tab"];
		$tab_link_pre = '<a href="admin.php?page=dmm_library&tab=';
		$tab_link_post = '" class="nav-tab ';
		
		$html = '<div class="wrap">
			 	<h2>DMM CRM LIBRARY</h2>
			 	<p>Shared media and campaign resources.</p>
				<h2 class="nav-tab-wrapper">';
		
		$html .= $tab_link_pre . 'find' . $tab_link_post;
		if ($tab == 'find' || !isset($tab)) {$html .= 'nav-tab-active';}  
		$html .= '">Find</a>';
		
		$html .= $tab_link_pre . 'saved' . $tab_link_post;
		if ($tab == 'saved') {$html .= 'nav-tab-active';}  
		$html .= '">Saved</a>';
		
		$html .= $tab_link_pre . 'used' . $tab_link_post;
		if ($tab == 'used') {$html .= 'nav-tab-active';}  
		$html .= '">Used</a>';
		
		$html .= $tab_link_pre . 'shared' . $tab_link_post;
		if ($tab == 'shared') {$html .= 'nav-tab-active';}  
		$html .= '">Shared</a>';
		
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
		    case "saved":
		        $html .= dmm_crm_library_saved ();
		        break;
		    case "used":
		        $html .= dmm_crm_library_used ();
		        break;
		    case "shared":
		        $html .= dmm_crm_library_shared ();
		        break;
		    case "tools":
		        $html .= dmm_crm_library_tools ();
		        break;
		    default:
		        $html .= dmm_crm_library_find ();
		}
		
		$html .= '</div>'; // end div class wrap
	
	echo $html;
		
}

function dmm_help_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/**
	*
	* Begin Header & Tab Bar
	*/
		$tab = $_GET["tab"];
		$tab_link_pre = '<a href="admin.php?page=dmm_help&tab=';
		$tab_link_post = '" class="nav-tab ';
		
		$html = '<div class="wrap">
			 	<h2>DMM CRM HELP</h2>
			 	<p>A shared training library for media to disciple making movements.</p>
				<h2 class="nav-tab-wrapper">';
		
		$html .= $tab_link_pre . 'help' . $tab_link_post;
		if ($tab == 'help' || !isset($tab)) {$html .= 'nav-tab-active';}  
		$html .= '">DMM CRM</a>';
		
		$html .= $tab_link_pre . 'media' . $tab_link_post;
		if ($tab == 'media') {$html .= 'nav-tab-active';}  
		$html .= '">Media Training</a>';
		
		$html .= $tab_link_pre . 'dmm' . $tab_link_post;
		if ($tab == 'dmm') {$html .= 'nav-tab-active';}  
		$html .= '">DMM Training</a>';
		
		$html .= '</h2>'; 
	// End Tab Bar
	
	/**
	*
	* Begin Page Content
	*/
		switch ($tab) {
		    case "media":
		        $html .= dmm_crm_help_media ();
		        break;
		    case "dmm":
		        $html .= dmm_crm_help_dmm ();
		        break;
		    default:
		        $html .= dmm_crm_help_dmmcrm ();
		}
		
		$html .= '</div>'; // end div class wrap
		
	echo $html;
		
}

function dmm_settings_options() {
	if ( !current_user_can( 'manage_options' ) )  {
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
		
		$html .= $tab_link_pre . 'contacts' . $tab_link_post;
		if ($tab == 'contacts') {$html .= 'nav-tab-active';}  
		$html .= '">Contacts</a>';
		
		$html .= $tab_link_pre . 'reports' . $tab_link_post;
		if ($tab == 'reports') {$html .= 'nav-tab-active';}  
		$html .= '">Reports</a>';
		
		$html .= $tab_link_pre . 'maps' . $tab_link_post;
		if ($tab == 'maps') {$html .= 'nav-tab-active';}  
		$html .= '">Maps</a>';
		
		$html .= $tab_link_pre . 'library' . $tab_link_post;
		if ($tab == 'library') {$html .= 'nav-tab-active';}  
		$html .= '">Library</a>';
		
		$html .= $tab_link_pre . 'help' . $tab_link_post;
		if ($tab == 'help') {$html .= 'nav-tab-active';}  
		$html .= '">Help</a>';
		
		$html .= '</h2>'; 
	// End Tab Bar
	
	/**
	*
	* Begin Page Content
	*/
		switch ($tab) {
		    case "contacts":
		        $html .= dmm_crm_settings_general ();
		        break;
		    case "reports":
		        $html .= dmm_crm_settings_contacts ();
		        break;
		    case "maps":
		        $html .= dmm_crm_settings_reports ();
		        break;
		    case "library":
		        $html .= dmm_crm_settings_maps ();
		        break;
		    case "help":
		        $html .= dmm_crm_settings_library ();
		        break;
		    default:
		        $html .= dmm_crm_settings_help ();
		}
		
		$html .= '</div>'; // end div class wrap
		
	echo $html;
	
}

	
?>