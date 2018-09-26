<?php
// Register menus
register_nav_menus(
    [
        //      'main-nav' => __( 'The Main Menu', 'disciple_tools' ),   // Main nav in header
        'footer-links' => __( 'Footer Links', 'disciple_tools' ) // Secondary nav in footer
    ]
);

// The Top Menu
function disciple_tools_top_nav_desktop() {
    if ( user_can( get_current_user_id(), 'access_contacts' ) ) {
        ?><li><a href="<?php echo esc_url( site_url( '/contacts/' ) ); ?>"><?php esc_html_e( "Contacts" ); ?></a></li><?php
    }
    if ( user_can( get_current_user_id(), 'access_contacts' ) ) {
        ?><li><a href="<?php echo esc_url( site_url( '/groups/' ) ); ?>"><?php esc_html_e( "Groups" ); ?></a></li><?php
    }
    if ( user_can( get_current_user_id(), 'view_any_contacts' ) || user_can( get_current_user_id(), 'view_project_metrics' ) ) {
        ?><li><a href="<?php echo esc_url( site_url( '/metrics/' ) ); ?>"><?php esc_html_e( "Metrics" ); ?></a></li><?php
    }
    /**
     * Fires after the top menu
     */
    do_action( 'dt_top_nav_desktop' );
}

function disciple_tools_top_nav_mobile() {
    if ( user_can( get_current_user_id(), 'access_contacts' ) ) {
        ?>
        <li><a href="<?php echo esc_url( site_url( '/groups/' ) ); ?>"><i class="fi-torsos-all"></i></a></li>
        <li><a href="<?php echo esc_url( site_url( '/contacts/' ) ); ?>"><i class="fi-address-book"></i></a></li>
        <li><a href="<?php echo esc_url( site_url( '/' ) ); ?>"><i class="fi-graph-pie"></i></a></li>
        <?php
        /**
         * Fires after the mobile nav menu
         */
        do_action( 'dt_top_nav_mobile' );
    }
}

// Big thanks to Brett Mason (https://github.com/brettsmason) for the awesome walker

/**
 * Class DT_Topbar_Menu_Walker
 */
class DT_Topbar_Menu_Walker extends Walker_Nav_Menu
{
    /**
     * @param string $output
     * @param int    $depth
     * @param array  $args
     */
    public function start_lvl( &$output, $depth = 0, $args = [] ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "\n$indent<ul class=\"menu\">\n";
    }
}

// The Off Canvas Menu
function disciple_tools_off_canvas_nav() {
    ?>
    <ul class="vertical menu sticky is-stuck is-at-top" data-accordion-menu>

        <li>
            <span class="title"><?php esc_html_e( 'Disciple Tools', 'disciple_tools' )  ?></span>
        </li>
        <li>
            <hr/>
        </li>

        <?php
        if ( user_can( get_current_user_id(), 'access_contacts' ) ) {
            ?><li><a href="<?php echo esc_url( site_url( '/contacts/' ) ); ?>"><?php esc_html_e( "Contacts" ); ?></a></li><?php
        }
        if ( user_can( get_current_user_id(), 'access_contacts' ) ) {
            ?><li><a href="<?php echo esc_url( site_url( '/groups/' ) ); ?>"><?php esc_html_e( "Groups" ); ?></a></li><?php
        }
        if ( user_can( get_current_user_id(), 'view_any_contacts' ) ) {
            ?><li><a href="<?php echo esc_url( site_url( '/metrics/' ) ); ?>"><?php esc_html_e( "Metrics" ); ?></a></li><?php
        }
        /**
         * Fires at the end of the off canvas menu
         */
        do_action( 'dt_off_canvas_nav' );

        ?>
        <li>&nbsp;<!-- Spacer--></li>
        <li>
            <a href="<?php echo esc_url( site_url( '/notifications/' ) ); ?>"><?php esc_html_e( "Notifications" ); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url( site_url( '/settings/' ) ); ?>"><?php esc_html_e( "Settings" ); ?></a>
        </li>
        <?php if ( user_can( get_current_user_id(), 'manage_dt' ) ) : ?>
            <li><a href="<?php echo esc_url( get_admin_url() ); ?>"><?php esc_html_e( "Admin" ); ?></a></li>
        <?php endif; ?>
        <li>
            <a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( "Log Off" ); ?></a>
        </li>

    </ul>

    <?php
}

/**
 * Class DT_Off_Canvas_Menu_Walker
 */
class DT_Off_Canvas_Menu_Walker extends Walker_Nav_Menu
{
    /**
     * @param string $output
     * @param int    $depth
     * @param array  $args
     */
    public function start_lvl( &$output, $depth = 0, $args = [] ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "\n$indent<ul class=\"vertical menu\">\n";
    }
}

// The Footer Menu
function disciple_tools_footer_links() {
    wp_nav_menu(
        [
            'container'      => 'false',                         // Remove nav container
            'menu'           => __( 'Footer Links', 'disciple_tools' ),       // Nav name
            'menu_class'     => 'menu',                          // Adding custom nav class
            'theme_location' => 'footer-links',             // Where it's located in the theme
            'depth'          => 0,                                   // Limit the depth of the nav
            'fallback_cb'    => ''                              // Fallback function
        ]
    );
} /* End Footer Menu */

// Header Fallback Menu
function disciple_tools_main_nav_fallback() {
    wp_page_menu(
        [
            'show_home'   => true,
            'menu_class'  => '',                              // Adding custom nav class
            'include'     => '',
            'exclude'     => '',
            'echo'        => true,
            'link_before' => '',                           // Before each link
            'link_after'  => ''                             // After each link
        ]
    );
}

/**
 * Footer Fallback Menu
 */
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
