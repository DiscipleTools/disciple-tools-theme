<?php

add_action( 'wp_enqueue_scripts', 'dt_chart_enqueue' );
function dt_chart_enqueue () {
    wp_register_script( 'dt_google_chart', 'https://www.gstatic.com/charts/loader.js' );
    wp_enqueue_script( 'dt_google_chart', 'https://www.gstatic.com/charts/loader.js' );


    wp_register_script( 'dt_jquery_lists', 'https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js', array( 'jquery' ) );
    wp_enqueue_script( 'dt_jquery_lists', 'https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js', array( 'jquery' ) );
}

/**
 * Setup JavaScript
 */
//add_action( 'wp_enqueue_scripts', function() {
//
//    //load script
//    wp_enqueue_script( 'dt-post-submitter', get_stylesheet_directory_uri( ) . '/assets/js/post-submitter.js', array( 'jquery' ) );
//
//    //localize data for script
//    wp_localize_script( 'dt-post-submitter', 'POST_SUBMITTER', array(
//            'root' => esc_url_raw( rest_url() ),
//            'nonce' => wp_create_nonce( 'wp_rest' ),
//            'success' => __( 'Thanks for your submission!', 'your-text-domain' ),
//            'failure' => __( 'Your submission could not be processed.', 'your-text-domain' ),
//            'current_user_id' => get_current_user_id()
//        )
//    );
//
//});


/**
 * Setup JavaScript
 */
add_action(
    'wp_enqueue_scripts', function() {

    //load script
        wp_enqueue_script( 'dt-comment-submitter', get_stylesheet_directory_uri( ) . '/assets/js/post-submitter.js', array( 'jquery' ) );

    //localize data for script
        wp_localize_script(
            'dt-comment-submitter', 'POST_SUBMITTER', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'success' => __( 'Thanks for your submission!', 'disciple_tools' ),
            'failure' => __( 'Your submission could not be processed.', 'disciple_tools' ),
            'current_user_id' => get_current_user_id()
            )
        );

    }
);

/**
 * Route Front Page depending on login role
 */
function dt_route_front_page () {
    if (user_can( get_current_user_id(), 'access_contacts' )) {
        include( get_stylesheet_directory() . '/dashboard.php' );
    } else {
        include( get_stylesheet_directory() . '/archive-prayer.php' );
    }
}
