<?php
/**
 * These are cosmetic clean up functions for the wp environment
 */

// Removes the admin bar
add_filter( 'show_admin_bar', '__return_false' );

/**
 * Fire all our initial functions at the start
 */
add_action( 'after_setup_theme', 'dt_start', 99 );

/**
 * Loads initial functions
 */
function dt_start() {

    // Remove pesky injected css for recent comments widget
    add_filter( 'wp_head', 'dt_remove_wp_widget_recent_comments_style', 1 );

    // Clean up comment styles in the head
    add_action( 'wp_head', 'dt_remove_recent_comments_style', 1 );

    // Clean up gallery output in wp
    add_filter( 'gallery_style', 'dt_gallery_style' );

    // Cleaning up excerpt
    add_filter( 'excerpt_more', 'dt_excerpt_more' );

    // Removes WP sticky class in favor or foundations sticky class
    add_filter( 'post_class', 'dt_remove_sticky_class' );

    // Sets the theme to "light"
    add_filter( 'get_user_option_admin_color', 'dt_change_admin_color' );
    remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' ); // Remove options for admin area color scheme

    // Cleanup admin menus
    add_action( 'admin_menu', 'dt_remove_post_admin_menus' );

    // Disable emoji
    add_action( 'init', 'dt_disable_wp_emoji' );
}

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
    if ( isset( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'] ) ) {
        remove_action( 'wp_head', array(
            $wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
            'recent_comments_style'
        ) );
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

    // Edit here if you like
    return '<a class="excerpt-read-more" href="' . get_permalink( $post->ID ) . '" title="Read' . esc_html( get_the_title( $post->ID ) ) . '"> ... Read more &raquo; </a>';
}

/**
 * Stop WordPress from using the sticky class (which conflicts with Foundation), and style WordPress sticky posts using the .wp-sticky class instead
 *
 * @param $classes
 *
 * @return array
 */
function dt_remove_sticky_class( $classes ) {
    if ( in_array( 'sticky', $classes ) ) {
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
        esc_attr( sprintf( 'Posts by %s', get_the_author() ) ), // No further l10n needed, core will take care of this one
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

    // remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
    // remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');

    // remove_meta_box('dashboard_right_now', 'dashboard', 'core');    // Right Now Widget
    remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'core' ); // Comments Widget
    remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );  // Incoming Links Widget
    remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );         // Plugins Widget

    // remove_meta_box('dashboard_quick_press', 'dashboard', 'core');  // Quick Press Widget
    remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );   // Recent Drafts Widget
    remove_meta_box( 'dashboard_primary', 'dashboard', 'core' );
    remove_meta_box( 'dashboard_secondary', 'dashboard', 'core' );

    // Remove plugin dashboard boxes
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
    return 'midnight';
}

/**
 * Removes Post WP Admin menu item
 *
 * @note Removing the posts menu is to clean the admin menu and because it is unnecissary to the disciple tools system.
 */
function dt_remove_post_admin_menus() {

    remove_menu_page( 'edit.php' );                     // Posts
    remove_menu_page( 'upload.php' );                   // Media
    remove_menu_page( 'edit.php?post_type=page' );      // Pages
    remove_menu_page( 'edit-comments.php' );            // Comments

    if ( ! current_user_can( 'manage_dt' ) ) {          // Add menu items to hide from all but admin
        remove_menu_page( 'tools.php' );                // Tools
    }
}


/**
 * Disable default emoji features of Wordpress
 */
function dt_disable_wp_emoji() {

    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    add_filter( 'tiny_mce_plugins', 'dt_disable_emojis_tinymce' );
    add_filter( 'wp_resource_hints', 'dt_disable_emojis_remove_dns_prefetch', 10, 2 );
}

function dt_disable_emojis_tinymce( $plugins ) {
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
    } else {
        return array();
    }
}
function dt_disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
    if ( 'dns-prefetch' == $relation_type ) {
        /** This filter is documented in wp-includes/formatting.php */
        $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

        $urls = array_diff( $urls, array( $emoji_svg_url ) );
    }

    return $urls;
}