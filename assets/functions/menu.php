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

    if ( user_can( get_current_user_id(), 'access_contacts' ) ) {
        // User is multiplier or role of higher position
        /* <li><a href="<   ?php echo esc_url( home_url( '/' ) ); ?    >">Dashboard</a></li> */
        ?>
        <li><a href="<?php echo esc_url( home_url( '/contacts/' ) ); ?>">Contacts</a></li>
        <li><a href="<?php echo esc_url( home_url( '/groups/' ) ); ?>">Groups</a></li>

        <?php
    } elseif ( user_can( get_current_user_id(), 'read_progress' ) ) {
        ?>
        <li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">About Us</a></li>
        <li><a href="<?php echo esc_url( home_url( '/prayer/' ) ); ?>">Prayer Guide</a></li>
        <li><a href="<?php echo esc_url( home_url( '/progress/' ) ); ?>">Project Updates</a></li>
        <li><a href="<?php echo esc_url( home_url( '/settings/' ) ); ?>">Settings</a></li>

        <?php
    } elseif ( user_can( get_current_user_id(), 'read_prayer' )) {
        /* user is prayer supporter */
        ?>
        <li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">About Us</a></li>
        <li><a href="<?php echo esc_url( home_url( '/prayer/' ) ); ?>">Prayer Guide</a></li>
        <li><a href="<?php echo esc_url( home_url( '/settings/' ) ); ?>">Settings</a></li>

        <?php
    } else {
        /* redirect to registered page */
    }


    //    wp_nav_menu(array(
    //        'container' => false,                           // Remove nav container
    //        'menu_class' => 'vertical medium-horizontal menu',       // Adding custom nav class
    //        'items_wrap' => '<ul id="%1$s" class="%2$s" data-responsive-menu="accordion medium-dropdown">%3$s</ul>',
    //        'theme_location' => 'main-nav',        			// Where it's located in the theme
    //        'depth' => 5,                                   // Limit the depth of the nav
    //        'fallback_cb' => false,                         // Fallback function (see below)
    //        'walker' => new DT_Topbar_Menu_Walker()
    //    ));

}


function disciple_tools_top_nav_mobile() {
    if ( user_can( get_current_user_id(), 'access_contacts' ) ) {
        ?>
        <li><a href="<?php echo esc_url( home_url( '/groups/' ) ); ?>"><i class="fi-torsos-all"></i></a></li>
        <li><a href="<?php echo esc_url( home_url( '/contacts/' ) ); ?>"><i class="fi-address-book"></i></a></li>
        <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fi-graph-pie"></i></a></li>
        <?php
    }
}

// Big thanks to Brett Mason (https://github.com/brettsmason) for the awesome walker

/**
 * Class DT_Topbar_Menu_Walker
 */
class DT_Topbar_Menu_Walker extends Walker_Nav_Menu {
    /**
     * @param string $output
     * @param int    $depth
     * @param array  $args
     */
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "\n$indent<ul class=\"menu\">\n";
    }
}

// The Off Canvas Menu
function disciple_tools_off_canvas_nav() {

    if ( user_can( get_current_user_id(), 'access_contacts' ) ) {
        ?>
        <ul class="vertical menu sticky is-stuck is-at-top" data-accordion-menu>

            <li><span class="title">Disciple Tools</span></li>
            <li><hr /></li>

        <?php
    // User is multiplier or role of higher position
//        <li><a href="<   ?php echo esc_url( home_url( '/' ) ); ?    >">Dashboard</a></li>
        ?>
            <li><a href="<?php echo esc_url( home_url( '/contacts/' ) ); ?>">Contacts</a></li>
            <li><a href="<?php echo esc_url( home_url( '/groups/' ) ); ?>">Groups</a></li>
            <li><a href="<?php echo esc_url( home_url( '/locations/' ) ); ?>">Locations</a></li>
            <?php if (dt_get_user_team_members_list( get_current_user_id() )) : ?>
            <li><a href="<?php echo esc_url( home_url( '/team/' ) ); ?>">Team</a></li>
            <?php endif; ?>
            <li>&nbsp;</li>
            <li><a href="<?php echo esc_url( home_url( '/metrics/' ) ); ?>">Metrics</a></li>
            <li><a href="<?php echo esc_url( home_url( '/notifications/' ) ); ?>">Notifications</a></li>
            <li><a href="<?php echo esc_url( home_url( '/settings/' ) ); ?>">Settings</a></li>
            <li>&nbsp;</li>


            <li>
                <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <input type="search" class="small" placeholder="<?php echo esc_attr_x( 'Search...', 'disciple_tools' ) ?>" value="<?php echo get_search_query() ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'disciple_tools' ) ?>" />
                    <input type="hidden" class=" button small" value="<?php echo esc_attr_x( 'Search', 'disciple_tools' ) ?>" />
                </form>
            </li>
       </ul>

<?php

    } elseif ( user_can( get_current_user_id(), 'read_progress' ) ) {

        ?>
        <div class="menu-centered">
                    <ul class="vertical medium-horizontal menu sticky is-stuck is-at-top" data-accordion-menu>

        <li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">About Us</a></li>
        <li><a href="<?php echo esc_url( home_url( '/prayer/' ) ); ?>">Prayer Guide</a></li>
        <li><a href="<?php echo esc_url( home_url( '/project/' ) ); ?>">Project Updates</a></li>
        <li><a href="<?php echo esc_url( home_url( '/settings/' ) ); ?>">Settings</a></li>

        </ul></div>
        <?php

    } elseif ( user_can( get_current_user_id(), 'read_prayer' )) {

        /* user is prayer supporter */
        ?>

        <div class="menu-centered">
                    <ul class="vertical medium-horizontal menu sticky is-stuck is-at-top" data-accordion-menu>

        <li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">About Us</a></li>
        <li><a href="<?php echo esc_url( home_url( '/prayer/' ) ); ?>">Prayer Guide</a></li>
        <li><a href="<?php echo esc_url( home_url( '/settings/' ) ); ?>">Settings</a></li>

        </ul></div>

        <?php

    } else {
        /* redirect to registered page */
    }

}

/**
 * Class DT_Off_Canvas_Menu_Walker
 */
class DT_Off_Canvas_Menu_Walker extends Walker_Nav_Menu {
    /**
     * @param string $output
     * @param int    $depth
     * @param array  $args
     */
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

/**
 *  Add Foundation active class to menu
 *
 * @param $classes
 * @param $item
 *
 * @return array
 */
function dt_required_active_nav_class( $classes, $item ) {
    if ( $item->current == 1 || $item->current_item_ancestor == true ) {
        $classes[] = 'active';
    }
    return $classes;
}
add_filter( 'nav_menu_css_class', 'dt_required_active_nav_class', 10, 2 );
