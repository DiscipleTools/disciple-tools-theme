<?php
/**
 * Functions supporting the content of the profile page.
 * All model and view should live in the plugin for generating the content, but the presenter can live in the theme
 *
 */



function dt_theme_page_profile_content ( $content ) {

    // Check and set Reports Page Title in options
    // TODO: This is temporary until I build the options page to manage the name of the Reports page.
    if(! get_option( 'dt_page_profile' )  ) {
        update_option( 'dt_page_profile', 'Profile' ); // This is the name of the reports page selected through the options page. Can be a different name than 'Reports', but 'Reports' is recommended.
    }


    if(is_page( get_option( 'dt_page_profile' ) )) {
        return 'This page will be for the management of your personal profile and setting preferences.';
    }

    return $content;
}
add_filter( 'the_content', 'dt_theme_page_profile_content' );
