<?php
// Register menus
register_nav_menus(
    array(
    //		'main-nav' => __( 'The Main Menu', 'disciple_tools' ),   // Main nav in header
        'footer-links' => __( 'Footer Links', 'disciple_tools' ) // Secondary nav in footer
    )
);

// The Top Menu
function disciple_tools_top_nav_desktop() {

    $html = '';

    if( user_can( get_current_user_id(), 'access_contacts' ) ) {


        // User is multiplier or role of higher position
        $html .= '<li><a href="'.home_url( '/' ).'">Dashboard</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'contacts/">Contacts</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'groups/">Groups</a></li>';


    } elseif ( user_can( get_current_user_id(), 'read_progress' ) ) {

        $html .= '<li><a href="'.home_url( '/' ).'about-us/">About Us</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'prayer/">Prayer Guide</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'progress/">Project Updates</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'settings/">Settings</a></li>';


    } elseif ( user_can( get_current_user_id(), 'read_prayer' )) {

        /* user is prayer supporter */

        $html .= '<li><a href="'.home_url( '/' ).'about-us/">About Us</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'prayer/">Prayer Guide</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'settings/">Settings</a></li>';


    } else {
        /* redirect to registered page */
    }

    echo $html;


    //    wp_nav_menu(array(
    //        'container' => false,                           // Remove nav container
    //        'menu_class' => 'vertical medium-horizontal menu',       // Adding custom nav class
    //        'items_wrap' => '<ul id="%1$s" class="%2$s" data-responsive-menu="accordion medium-dropdown">%3$s</ul>',
    //        'theme_location' => 'main-nav',        			// Where it's located in the theme
    //        'depth' => 5,                                   // Limit the depth of the nav
    //        'fallback_cb' => false,                         // Fallback function (see below)
    //        'walker' => new Topbar_Menu_Walker()
    //    ));

}


function disciple_tools_top_nav_mobile() {
    $html = '';

    if( user_can( get_current_user_id(), 'access_contacts' ) ) {
        $html .= '<li><a href="' . home_url( '/' ) . 'groups/"><i class="fi-torsos-all"></i></a></li>';
        $html .= '<li><a href="' . home_url( '/' ) . 'contacts/"><i class="fi-address-book"></i></a></li>';
        $html .= '<li><a href="' . home_url( '/' ) . '"><i class="fi-graph-pie"></i></a></li>';
    }

    echo $html;

}

// Big thanks to Brett Mason (https://github.com/brettsmason) for the awesome walker
class Topbar_Menu_Walker extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "\n$indent<ul class=\"menu\">\n";
    }
}

// The Off Canvas Menu
function disciple_tools_off_canvas_nav() {

    $html = '';

    if( user_can( get_current_user_id(), 'access_contacts' ) ) {

        $html .= '<ul class="vertical menu sticky is-stuck is-at-top" data-accordion-menu>';

        $html .= '<li><span class="title">Disciple Tools</span></li>
                    <li><hr /></li>';

    // User is multiplier or role of higher position
//        $html .= '<li><a href="'.home_url( '/' ).'">Dashboard</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'contacts/">Contacts</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'groups/">Groups</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'locations/">Locations</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'workers/">Workers</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'peoplegroups/">People Groups</a></li>';
        $html .= '<li>&nbsp;</li>';
        $html .= '<li><a href="'.home_url( '/' ).'metrics/">Metrics</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'resources/">Resources</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'notifications/">Notifications</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'settings/">Settings</a></li>';
        $html .= '<li>&nbsp;</li>';

        $html .= '
            <li>
                <form role="search" method="get" class="search-form" action="'. home_url( '/' ) .'">
                    <input type="search" class="small" placeholder="' . esc_attr_x( 'Search...', 'disciple_tools' ) . '" value="' . get_search_query() . '" name="s" title="'. esc_attr_x( 'Search for:', 'disciple_tools' ).'" />
                    <input type="hidden" class=" button small" value="'. esc_attr_x( 'Search', 'disciple_tools' ) .'" />
                </form>
            </li>';

        $html .= '</ul>';

    } elseif ( user_can( get_current_user_id(), 'read_progress' ) ) {

        $html .= '<div class="menu-centered">
                    <ul class="vertical medium-horizontal menu sticky is-stuck is-at-top" data-accordion-menu>';

        $html .= '<li><a href="'.home_url( '/' ).'about-us/">About Us</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'prayer/">Prayer Guide</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'project/">Project Updates</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'settings/">Profile</a></li>';

        $html .= '</ul></div>';

    } elseif ( user_can( get_current_user_id(), 'read_prayer' )) {

        /* user is prayer supporter */

        $html .= '<div class="menu-centered">
                    <ul class="vertical medium-horizontal menu sticky is-stuck is-at-top" data-accordion-menu>';

        $html .= '<li><a href="'.home_url( '/' ).'about-us/">About Us</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'prayer/">Prayer Guide</a></li>';
        $html .= '<li><a href="'.home_url( '/' ).'settings/">Profile</a></li>';

        $html .= '</ul></div>';

    } else {
        /* redirect to registered page */
    }

    echo $html;
    
}

class Off_Canvas_Menu_Walker extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "\n$indent<ul class=\"vertical menu\">\n";
    }
}

// The Footer Menu
function disciple_tools_footer_links() {
    wp_nav_menu(
        array(
        'container' => 'false',                         // Remove nav container
        'menu' => __( 'Footer Links', 'disciple_tools' ),       // Nav name
        'menu_class' => 'menu',                          // Adding custom nav class
        'theme_location' => 'footer-links',             // Where it's located in the theme
        'depth' => 0,                                   // Limit the depth of the nav
        'fallback_cb' => ''                              // Fallback function
        )
    );
} /* End Footer Menu */

// Header Fallback Menu
function disciple_tools_main_nav_fallback() {
    wp_page_menu(
        array(
        'show_home' => true,
        'menu_class' => '',                              // Adding custom nav class
        'include'     => '',
        'exclude'     => '',
        'echo'        => true,
        'link_before' => '',                           // Before each link
        'link_after' => ''                             // After each link
        )
    );
}

// Footer Fallback Menu
function disciple_tools_footer_links_fallback() {
    /* You can put a default here if you like */
}

// Add Foundation active class to menu
function required_active_nav_class( $classes, $item ) {
    if ( $item->current == 1 || $item->current_item_ancestor == true ) {
        $classes[] = 'active';
    }
    return $classes;
}
add_filter( 'nav_menu_css_class', 'required_active_nav_class', 10, 2 );
