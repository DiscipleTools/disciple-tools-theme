<?php
function site_scripts() {
  global $wp_styles; // Call global $wp_styles variable to add conditional wrapper around ie stylesheet the WordPress way

    /**
     * Force new version of jQuery.
     * Forcing newest version of jquery and jquery ui because of the themes use of controlgroups and checkboxradio widget. Once Wordpress core updates to 1.12, then
     * the next section could be removed.
     */
    wp_enqueue_style( 'jquery-ui-site-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css', array(), '', 'all' );

    // comment out the next two lines to load the local copy of jQuery
    wp_deregister_script('jquery');
    wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js', false, '1.12.4');
    wp_enqueue_script('jquery');

    // comment out the next two lines to load the local copy of jQuery
    wp_register_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', false, '1.12.1');
    wp_enqueue_script('jquery-ui');
    /**
     * End jQuery force new version
     */

    // Load What-Input files in footer
    wp_enqueue_script( 'what-input', get_template_directory_uri() . '/vendor/what-input/dist/what-input.min.js', array(), '', true );
    
    // Adding Foundation scripts file in the footer
    wp_enqueue_script( 'foundation-js', get_template_directory_uri() . '/assets/js/foundation.js', array( 'jquery' ), '6.2.3', true );

    // Adding scripts file in the header, also adds jquery and jquery ui to the head.
    wp_enqueue_script( 'header-site-js', get_template_directory_uri() . '/assets/js/header-scripts.js', array( 'jquery', 'jquery-ui' ), '', false );

    // Adding scripts file in the footer
    wp_enqueue_script( 'footer-site-js', get_template_directory_uri() . '/assets/js/footer-scripts.js', array( 'jquery' ), '', true );

    // Register main stylesheet
    wp_enqueue_style( 'site-css', get_template_directory_uri() . '/assets/css/style.css', array(), '', 'all' );

    // Comment reply script for threaded comments
    if ( is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) {
      wp_enqueue_script( 'comment-reply' );
    }
}
add_action('wp_enqueue_scripts', 'site_scripts', 999);



