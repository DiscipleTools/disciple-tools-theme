<?php

/**
 * Fire all our initial functions at the start
 */
add_action( 'after_setup_theme','dt_start', 16 );

/**
 * Loads initial functions
 */
function dt_start() {

    // launching operation cleanup
    add_action( 'init', 'dt_head_cleanup' );

    // remove pesky injected css for recent comments widget
    add_filter( 'wp_head', 'dt_remove_wp_widget_recent_comments_style', 1 );

    // clean up comment styles in the head
    add_action( 'wp_head', 'dt_remove_recent_comments_style', 1 );

    // clean up gallery output in wp
    add_filter( 'gallery_style', 'dt_gallery_style' );

    // cleaning up excerpt
    add_filter( 'excerpt_more', 'dt_excerpt_more' );

    // Removes WP sticky class in favor or foundations sticky class
    add_filter( 'post_class','dt_remove_sticky_class' );

    // sets the theme to "light"
    add_filter( 'get_user_option_admin_color', 'dt_change_admin_color' );
    remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' ); // Remove options for admin area color scheme

    // Cleanup admin menus
    add_action( 'admin_menu', 'dt_remove_post_admin_menus' );

    // Disable emoji
    add_action( 'init', 'dt_disable_wp_emoji' );

}

/**
 * The default wordpress head is a mess. Let's clean it up by removing all the junk we don't need.
 */
function dt_head_cleanup() {
    // Remove category feeds
     remove_action( 'wp_head', 'feed_links_extra', 3 );
    // Remove post and comment feeds
     remove_action( 'wp_head', 'feed_links', 2 );
    // Remove EditURI link
    remove_action( 'wp_head', 'rsd_link' );
    // Remove Windows live writer
    remove_action( 'wp_head', 'wlwmanifest_link' );
    // Remove index link
    remove_action( 'wp_head', 'index_rel_link' );
    // Remove previous link
    remove_action( 'wp_head', 'parent_post_rel_link', 10 );
    // Remove start link
    remove_action( 'wp_head', 'start_post_rel_link', 10 );
    // Remove links for adjacent posts
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
    // Remove WP version
    remove_action( 'wp_head', 'wp_generator' );
} /* end Joints head cleanup */

/**
 * Remove injected CSS for recent comments widget
 */
function dt_remove_wp_widget_recent_comments_style() {
    if ( has_filter( 'wp_head', 'wp_widget_recent_comments_style' ) ) {
        remove_filter( 'wp_head', 'wp_widget_recent_comments_style' );
    }
}

/**
 * Remove injected CSS from recent comments widget
 */
function dt_remove_recent_comments_style() {
    global $wp_widget_factory;
    if (isset( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'] )) {
        remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
    }
}

/**
 * Remove injected CSS from gallery
 *
 * @param $css
 *
 * @return mixed
 */
function dt_gallery_style( $css ) {
    return preg_replace( "!<style type='text/css'>(.*?)</style>!s", '', $css );
}

/**
 * This removes the annoying […] to a Read More link
 *
 * @param $more
 *
 * @return string
 */
function dt_excerpt_more( $more ) {
    global $post;
    // edit here if you like
    return '<a class="excerpt-read-more" href="'. get_permalink( $post->ID ) . '" title="'. __( 'Read', 'disciple_tools' ) . esc_html( get_the_title( $post->ID ) ).'">'. __( '... Read more &raquo;', 'disciple_tools' ) .'</a>';
}

/**
 * Stop WordPress from using the sticky class (which conflicts with Foundation), and style WordPress sticky posts using the .wp-sticky class instead
 *
 * @param $classes
 *
 * @return array
 */
function dt_remove_sticky_class( $classes ) {
    if (in_array( 'sticky', $classes )) {
        $classes = array_diff( $classes, array( "sticky" ) );
        $classes[] = 'wp-sticky';
    }

    return $classes;
}


/**
 * This is a modified the_author_posts_link() which just returns the link. This is necessary to allow usage of the usual l10n process with printf()
 *
 * @return bool|string
 */
function dt_get_the_author_posts_link() {
    global $authordata;
    if ( !is_object( $authordata ) ) {
        return false;
    }
    $link = sprintf(
        '<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
        get_author_posts_url( $authordata->ID, $authordata->user_nicename ),
        esc_attr( sprintf( __( 'Posts by %s', 'disciple_tools' ), get_the_author() ) ), // No further l10n needed, core will take care of this one
        get_the_author()
    );
    return $link;
}

/**
 * Cleans up the admin dashboard defaults
 */
function dt_remove_dashboard_meta() {

    remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
    remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );

    //remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
    //remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');

    // Remove_meta_box('dashboard_right_now', 'dashboard', 'core');    // Right Now Widget
    remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'core' ); // Comments Widget
    remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );  // Incoming Links Widget
    remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );         // Plugins Widget

    // Remove_meta_box('dashboard_quick_press', 'dashboard', 'core');  // Quick Press Widget
    remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );   // Recent Drafts Widget
    remove_meta_box( 'dashboard_primary', 'dashboard', 'core' );         //
    remove_meta_box( 'dashboard_secondary', 'dashboard', 'core' );       //

    // Removing plugin dashboard boxes
    remove_meta_box( 'yoast_db_widget', 'dashboard', 'normal' );         // Yoast's SEO Plugin Widget
}

/**
 * Sets the admin area color scheme to lightness
 *
 * @param $result
 *
 * @return string
 */
function dt_change_admin_color( $result ) {
    return 'light';
}


/**
 * Removes Post WP Admin menu item
 *
 * @note Removing the posts menu is to clean the admin menu and because it is unnecissary to the disciple tools system.
 */
function dt_remove_post_admin_menus(){
    remove_menu_page( 'edit.php' ); //Posts (Not using posts as a content channel for Disciple Tools, so that no data is automatically exposed by switching themes or plugin.
}


/**
 * Disable default emoji features of Wordpress
 */
function dt_disable_wp_emoji() {

    // all actions related to emojis
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

    // filter to remove TinyMCE emojis
    add_filter( 'tiny_mce_plugins', 'dt_disable_emoji_tinymce' );
}


/**
 * Disables emoji support in the tinymce.
 *
 * @param $plugins
 *
 * @return array
 */
function dt_disable_emoji_tinymce( $plugins ) {
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
    } else {
        return array();
    }
}
