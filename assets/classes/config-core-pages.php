<?php

if (is_admin()) {
    add_action( 'init', 'dt_pages_check' );
}


/**
 * Checks the existence of core pages for Disciple Tools
 * @return mixed
 */
function dt_pages_check () {

    $postarr = array(
        'About Us',
        'Reports',
        'Profile'
    );

    foreach ($postarr as $item) {
        if (! post_exists( $item )) {
            dt_add_core_pages();
        }
    }
    return false;
}

/**
 * Installs or Resets Core Pages
 *
 */
function dt_add_core_pages ()
{
    $html = '';

    if ( true == get_post_status( 2 ) ) {    wp_delete_post( 2 );  } // Delete default page

    $postarr = array(
        array(
            'post_title'    =>  'Reports',
            'post_name'     =>  'reports',
            'post_content'  =>  'The content of the page is controlled by the Disciple Tools plugin, but this page is required by the plugin to display the dashboard.',
            'post_status'   =>  'Publish',
            'comment_status'    =>  'closed',
            'ping_status'   =>  'closed',
            'menu_order'    =>  '4',
            'post_type'     =>  'page',
        ),
        array(
            'post_title'    =>  'Profile',
            'post_name'     =>  'profile',
            'post_content'  =>  'The content of the page is controlled by the Disciple Tools plugin, but this page is required by the plugin to display the dashboard.',
            'post_status'   =>  'Publish',
            'comment_status'    =>  'closed',
            'ping_status'   =>  'closed',
            'menu_order'    =>  '4',
            'post_type'     =>  'page',
        ),
        array(
            'post_title'    =>  'About Us',
            'post_name'     =>  'about-us',
            'post_content'  =>  'You can replace this content with whatever you would like to say about your media-to-movement project and it will publish on the "About Us" page.',
            'post_status'   =>  'Publish',
            'comment_status'    =>  'closed',
            'ping_status'   =>  'closed',
            'menu_order'    =>  '4',
            'post_type'     =>  'page',
        ),

    );

    foreach ($postarr as $item) {
        if (! post_exists( $item['post_title'] ) ) {
            wp_insert_post( $item, false );
        } else {
            $page = get_page_by_title( $item['post_title'] );
            wp_delete_post( $page->ID );
            wp_insert_post( $item, false );
        }

    }

    return $html;
}