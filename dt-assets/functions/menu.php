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

    $tabs = [];
    if ( user_can( get_current_user_id(), 'access_contacts' ) ){
        $tabs = [
            [
                "link" => site_url( '/contacts/' ),
                "label" => __( "Contacts", 'disciple_tools' )
            ],
            [
                "link" => site_url( '/groups/' ),
                "label" => __( "Groups", 'disciple_tools' )
            ],
            [
                "link" => site_url( '/metrics/' ),
                "label" => __( "Metrics", 'disciple_tools' )
            ],
        ];
    }
    $tabs = apply_filters( "desktop_navbar_menu_options", $tabs );

    if ( apply_filters( 'dt_show_default_top_menu', true ) ) {
        foreach ( $tabs as $tab ) : ?>
            <li><a href="<?php echo esc_url( $tab["link"] ) ?>"> <?php echo esc_html( $tab["label"] ) ?> </a></li>
        <?php endforeach;
    }

    /**
     * Fires after the top menu
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
        $tabs = [];
        if ( user_can( get_current_user_id(), 'access_contacts' ) ){
            $tabs = [
                [
                    "link" => site_url( '/contacts/' ),
                    "label" => __( "Contacts", 'disciple_tools' )
                ],
                [
                    "link" => site_url( '/groups/' ),
                    "label" => __( "Groups", 'disciple_tools' )
                ],
                [
                    "link" => site_url( '/metrics/' ),
                    "label" => __( "Metrics", 'disciple_tools' )
                ],
            ];
        }
        $tabs = apply_filters( "off_canvas_menu_options", $tabs );
        foreach ( $tabs as $tab ) : ?>
            <li><a href="<?php echo esc_url( $tab["link"] ) ?>"> <?php echo esc_html( $tab["label"] ) ?> </a></li>
        <?php endforeach;

        /**
         * Fires at the end of the off canvas menu
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
