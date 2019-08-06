<?php
/*
 * By default, Foundation will use .title-bar for small, and .top-bar for
 * medium up
 */
global $pagenow;
if ( is_multisite() && 'wp-activate.php' === $pagenow ) {
    /**
     * Removes blog header if user is activating.
     * @see wp-activate.php
     */
    return;
}

?>

<div class="title-bar show-for-small-only" data-responsive-toggle="top-bar-menu">
    <div class="title-bar-left">
        <button class="" type="button" data-open="off-canvas">
            <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/hamburger.svg" ?>">
        </button>
        <div class="title-bar-title" style="margin-left: 5px"><?php esc_html_e( "Disciple Tools" ); ?></div>
    </div>
    <div class="title-bar-right">
        <ul class="dropdown menu" data-dropdown-menu style="display:inline-block; margin-left: 10px">
            <li class="has-submenu center-items" style="width:21px;">
                <button>
                    <img title="<?php esc_html_e( "Add New", "disciple_tools" ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/circle-add.svg" ?>" style="width:21px;">
                </button>
                <ul class="submenu menu vertical">
                    <li><a href="<?php echo esc_url( site_url( '/' ) ) . 'contacts/new'; ?>"><?php esc_html_e( 'New Contact', 'disciple_tools' )?></a></li>
                    <li><a href="<?php echo esc_url( site_url( '/' ) ) . 'groups/new'; ?>"><?php esc_html_e( 'New Group', 'disciple_tools' )?></a></li>
                    <?php do_action( 'dt_nav_add_post_menu' ) ?>
                </ul>
            </li>
        </ul>

        <a href="<?php echo esc_url( site_url( '/notifications' ) ); ?>" style="margin-left: 10px">
            <img title="<?php esc_html_e( "Notifications", "disciple_tools" ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/bell.svg" ?>">
            <span class="badge alert notification-count" style="display:none"></span>
        </a>

        <a href="<?php echo esc_url( site_url( '/' ) ) . 'settings/'; ?>" style="margin-left: 10px">
            <img title="<?php esc_html_e( "Settings", "disciple_tools" ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/gear.svg" ?>">
        </a>

    </div>
</div>

<div data-sticky-container class="hide-for-small-only">
    <div class="top-bar" id="top-bar-menu"
         data-sticky style="width:100%;margin-top:0">
        <div>
            <img src="<?php
            /**
             * Filter for replacing the logo
             */
            $url = apply_filters( 'dt_default_logo', get_template_directory_uri() . "/dt-assets/images/disciple-tools-logo-beta.png" );
            echo esc_url( $url );
            ?>" style="margin:0 17px; height: 20px">
        </div>
        <div class="top-bar-left">
            <ul class="menu">
                <?php disciple_tools_top_nav_desktop(); ?>
            </ul>
        </div>
        <div class="top-bar-right">
            <ul class="dropdown menu" data-dropdown-menu>
                <li class="image-menu-nav">
                    <a href="<?php echo esc_url( site_url( '/' ) ) . 'settings/'; ?>">
                        <img title="<?php esc_html_e( "Profile", "disciple_tools" ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/profile.svg" ?>">
                        <span dir="auto"><?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
                    </a>
                </li>
                <li class="has-submenu center-items">
                    <button>
                        <img title="<?php esc_html_e( "Add New", "disciple_tools" ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/circle-add.svg" ?>" style="width:21px;">
                    </button>
                    <ul class="submenu menu vertical">
                        <li><a href="<?php echo esc_url( site_url( '/' ) ) . 'contacts/new'; ?>"><?php esc_html_e( 'New Contact', 'disciple_tools' )?></a></li>
                        <li><a href="<?php echo esc_url( site_url( '/' ) ) . 'groups/new'; ?>"><?php esc_html_e( 'New Group', 'disciple_tools' )?></a></li>
                        <?php do_action( 'dt_nav_add_post_menu' ) ?>
                    </ul>
                </li>
                <li class="image-menu-nav">
                    <a href="<?php echo esc_url( site_url( '/notifications' ) ); ?>">
                        <img title="<?php esc_html_e( "Notifications" ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/bell.svg" ?>">
                        <span class="badge alert notification-count" style="display:none"></span>
                    </a>
                </li>
                <li class="has-submenu center-items">
                    <button>
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/settings.svg" ?>">
                    </button>
                    <ul class="submenu menu vertical">

                        <?php do_action( 'dt_settings_menu_pre' ) ?>

                        <li><a href="<?php echo esc_url( site_url( '/' ) ) . 'settings/'; ?>"><?php esc_html_e( 'Settings', 'disciple_tools' )?></a></li>

                        <?php if ( user_can( get_current_user_id(), 'manage_dt' ) ) : ?>
                            <li><a href="<?php echo esc_url( get_admin_url() ); ?>"><?php esc_html_e( "Admin", "disciple_tools" ); ?></a></li>
                        <?php endif; ?>
                        <li><a href="https://disciple-tools.readthedocs.io/en/latest/index.html" target="_blank" rel="noreferrer"><?php esc_html_e( 'Help', "disciple_tools" ) ?></a></li>

                        <?php do_action( 'dt_settings_menu_post' ) ?>

                        <li><a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Log Off', 'disciple_tools' )?></a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
