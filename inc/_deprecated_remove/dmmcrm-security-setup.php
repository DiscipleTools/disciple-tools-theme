<?php
/**
 * 
 *
 * 
 *
 * @package dmmcrm
 */


 
/*
 * Various Modifications to the Login Page
 * 
 */
 
// Replace default login logo on login page
function my_login_logo() { ?>
	    <style type="text/css">
	        #login h1 a, .login h1 a {
	            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/img/dmm-crm-logo.png);
	        }
	    </style>
	<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );

// Change default home URL on login page
function my_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

// Change default login title on login page
function my_login_logo_url_title() {
    return 'DMM CRM';
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );
 
 
 
 ?>