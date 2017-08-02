<?php
/**
 * Admin dashboard configurations
 */



/**
 * Remove menu items
 * @see https://codex.wordpress.org/Function_Reference/remove_menu_page
 */
function dt_remove_post_admin_menus(){
    remove_menu_page( 'edit.php' ); //Posts (Not using posts as a content channel for Disciple Tools, so that no data is automatically exposed by switching themes or plugin.
}
add_action( 'admin_menu', 'dt_remove_post_admin_menus' );

/*
 * Set the admin area color scheme
 */
function dt_change_admin_color( $result ) {
    return 'light';
}
add_filter( 'get_user_option_admin_color', 'dt_change_admin_color' ); // sets the theme to "light"
remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' ); // Remove options for admin area color scheme