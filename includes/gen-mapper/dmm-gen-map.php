<?php 
	
	/*
	Plugin Name: DMM GEN MAP
	Plugin URI: http://chasm.solutions
	Description: This is the DMM GEN MAP for tracking simple church generations.
	Version: 0.1
	Author: By Us
	Author URI: http://chasm.solutions
	License: GPL2
	
*/



/** ADD MENU **/

/** Add action and call new menu function */
add_action( 'admin_menu', 'dmm_gen_map_menu' );

/** DMM CRM menu creation */
function dmm_gen_map_menu() {
	//add_menu_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', string $icon_url = '', int $position = null );
	add_menu_page( 'Dashboard', 'DMM GEN-MAP', 'manage_options', 'dmm_gen_map', 'dmm_gen_map_options', '' , '3' );
	//add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' );
		
}

function dmm_gen_map_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo "<h1>DMM GEN-MAP</h1><p><iframe src='" . plugins_url( 'index.html', __FILE__ ) . "' border='0' width='100%' height='2000px'></iframe></p>";
	echo '</div>';
	
}

/* END ADD MENU */


/** INSTALL DB SCRIPT **/

/** Register database and install starting data */
register_activation_hook( __FILE__, 'dmm_gen_map_install' );
register_activation_hook( __FILE__, 'dmm_gen_map_install_data' );

// Set db version for future upgrades
global $dmm_gen_map_db_version;
$dmm_gen_map_db_version = '1.0';

// Install function
function dmm_gen_map_install() {
	global $wpdb;
	global $dmm_gen_map_db_version;

	$table_name = $wpdb->prefix . 'dmm_gen_map';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		parentId int(11),
		name varchar(55),
		coach varchar(55),
		field1 int(11),
		field2 int(11),
		field3 int(11),
		field4 int(11),
		field5 int(11),
		placeDate varchar(55),
		active varchar(55),
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'dmm_gen_map_db_version', $dmm_gen_map_db_version );
}

// Install starting data function
function dmm_gen_map_install_data() {
	global $wpdb;
	
	$parentId = '';
	$name = 'Home Name';
	$coach = 'Coach';
	$field1 = '0';
	$field2 = '0';
	$field3 = '0';
	$field4 = '0';
	$field5 = '0';
	$placeDate = 'Location, Date';
	$active = TRUE;
	
	$table_name = $wpdb->prefix . 'dmm_gen_map';
	
	$wpdb->insert( 
		$table_name, 
		array( 
				'parentId' => $parentId, 
				'name' => $name, 
				'coach' => $coach, 
				'field1' => $field1,
				'field2' => $field2,
				'field3' =>	$field3,
				'field4' => $field4,
				'field5' => $field5, 
				'placeDate' => $placeDate,
				'active' => $active,
			) 
	);
}
/** End Install Script **/
	
?>