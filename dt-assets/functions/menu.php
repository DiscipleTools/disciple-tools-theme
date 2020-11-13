<?php
// Register menus
//register_nav_menus(
//    [
//        //      'main-nav' => __( 'The Main Menu', 'disciple_tools' ),   // Main nav in header
////        'footer-links' => __( 'Footer Links', 'disciple_tools' ) // Secondary nav in footer
//    ]
//);

// The Top Menu
function disciple_tools_top_nav_desktop() {

    /**
     * Loads top row menu
     * @note Main post types (Contacts, Groups, Metrics) fire between 20-30. If you want to add an item before the
     * main post types, load before 20, if you want to load after the list, load after 30.
     */
    do_action( 'dt_top_nav_desktop' );
}

// The Off Canvas Menu
function disciple_tools_off_canvas_nav() {
    ?>
    <ul class="vertical menu sticky is-stuck is-at-top" data-accordion-menu>

        <li class="nav-title">
            <div>
                <span class="title"><?php esc_html_e( 'Disciple.Tools', 'disciple_tools' )  ?></span>
            </div>
            <hr/>
        </li>

        <?php

        /**
         * Loads main menu items
         * @note Main post types (Contacts, Groups, Metrics) fire between 20-30. If you want to add an item before the
         * main post types, load before 20, if you want to load after the list, load after 30.
         */
        do_action( 'dt_off_canvas_nav' );

        ?>
        <li>&nbsp;<!-- Spacer--></li>
        <li>
            <a href="<?php echo esc_url( site_url( '/notifications/' ) ); ?>"><?php esc_html_e( "Notifications", 'disciple_tools' ); ?></a>
        </li>
        <li>
            <a href="<?php echo esc_url( site_url( '/settings/' ) ); ?>"><?php esc_html_e( "Settings", 'disciple_tools' ); ?></a>
        </li>
        <?php if ( current_user_can( 'manage_dt' ) || current_user_can( 'list_users' ) ) : ?>
            <li><a href="<?php echo esc_url( site_url( '/user-management/users/' ) ); ?>"><?php esc_html_e( "Users", "disciple_tools" ); ?></a></li>
        <?php endif; ?>
        <?php if ( user_can( get_current_user_id(), 'manage_dt' ) ) : ?>
            <li><a href="<?php echo esc_url( get_admin_url() ); ?>"><?php esc_html_e( "Admin", 'disciple_tools' ); ?></a></li>
        <?php endif; ?>
        <li>
            <a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( "Log Off", 'disciple_tools' ); ?></a>
        </li>

    </ul>

    <?php
}

// The Footer Menu
function disciple_tools_footer_links() {
    wp_nav_menu(
        [
            'container'      => 'false',                         // Remove nav container
            'menu'           => 'Footer Links',       // Nav name
            'menu_class'     => 'menu',                          // Adding custom nav class
            'theme_location' => 'footer-links',             // Where it's located in the theme
            'depth'          => 0,                                   // Limit the depth of the nav
            'fallback_cb'    => ''                              // Fallback function
        ]
    );
} /* End Footer Menu */
