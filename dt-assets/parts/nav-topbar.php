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


/**
 * Loads nav bar menu items
 * @note Main post types (Contacts, Groups, Metrics) fire between 20-30. If you want to add an item before the
 * main post types, load before 20, if you want to load after the list, load after 30.
 */
$tabs = apply_filters( "dt_nav", dt_default_menu_array() );


?>
<!--  /* TOP LEFT SIDE MENU AREA */ -->
<div data-sticky-container>
    <div class="title-bar hide-for-large" data-sticky data-responsive-toggle="top-bar-menu" data-margin-top="0" data-sticky-on="medium">

        <div class="title-bar-left">

            <!-- menu -->
            <?php if ( ! $tabs['admin']['menu']['hidden'] ?? ! false ) : ?>
            <button class="" type="button" data-open="off-canvas">
                <img src="<?php echo esc_url( $tabs['admin']['menu']['icon'] ?? get_template_directory_uri() . '/dt-assets/images/hamburger.svg' ) ?>">
            </button>
            <?php endif; ?>

            <!-- site logo -->
            <?php if ( ! $tabs['admin']['site']['hidden'] ?? ! false ) : ?>
            <div class="title-bar-title" style="margin-left: 5px">
                <a href="<?php echo esc_url( $tabs['admin']['site']['link'] ?? site_url() )?>" style="padding-left:0;vertical-align: middle" ><img src="<?php echo esc_url( $tabs['admin']['site']['link'] ?? get_template_directory_uri() . "/dt-assets/images/disciple-tools-logo-white.png" ); ?>" style="margin:0; height: 20px" alt="logo-image"></a>
            </div>
            <?php endif; ?>

        </div>

        <div class="title-bar-right">

            <!-- add new-->
            <?php if ( isset( $tabs['admin']['add_new'] ) && ! empty( $tabs['admin']['add_new'] ) ) : ?>
            <ul class="dropdown menu" data-dropdown-menu style="display:inline-block; margin-left: 10px">
                <li class="has-submenu center-items" style="width:21px;">
                    <button>
                        <img title="<?php esc_html( $tabs['admin']['add_new']['label'] ?? '' ); ?>" src="<?php echo esc_url( $tabs['admin']['add_new']['icon'] ?? '' ?? get_template_directory_uri() . "/dt-assets/images/circle-add-plus.svg" ) ?>" style="width:24px;">
                    </button>
                    <ul class="submenu menu vertical add-new-items-dropdown " style="text-align:left;">
                        <?php do_action( 'dt_nav_add_post_menu' ) ?>
                    </ul>
                </li>
            </ul>
            <?php endif; ?>

            <!-- notifications -->
            <?php if ( isset( $tabs['admin']['notifications'] ) && ! empty( $tabs['admin']['notifications'] ) ) : ?>
            <a href="<?php echo esc_url( site_url( '/notifications' ) ); ?>" style="margin-left: 10px">
                <img title="<?php esc_html( $tabs['admin']['notifications']['label'] ?? '' ); ?>" src="<?php echo esc_url( $icons['notifications']['link'] ?? get_template_directory_uri() ) . "/dt-assets/images/bell.svg" ?>">
                <span class="badge alert notification-count" style="display:none"></span>
            </a>
            <?php endif; ?>

            <!-- settings -->
            <?php if ( isset( $tabs['admin']['settings'] ) && ! empty( $tabs['admin']['settings'] ) ) : ?>
            <a href="<?php echo esc_url( $tabs['admin']['settings']['link'] ?? site_url( '/settings/' ) ); ?>" style="margin-left: 10px">
                <img title="<?php esc_html( $tabs['admin']['settings']['label'] ?? __( "Settings", 'disciple_tools' ) ); ?>" src="<?php echo esc_url( $tabs['admin']['settings']['icon'] ?? get_template_directory_uri() . "/dt-assets/images/gear.svg" ) ?>">
            </a>
            <?php endif; ?>

        </div>
    </div>
</div>

<!--  /* LOGO AREA */ -->
<div data-sticky-container class="show-for-large">
    <div class="top-bar" id="top-bar-menu"
         data-sticky style="width:100%;margin-top:0">
        <div>
            <a href="<?php echo esc_url( $site['link'] ?? site_url() )?>" style="padding-left:0;vertical-align: middle"><img src="<?php echo esc_url( $icons['logo']['link'] ?? get_template_directory_uri() . "/dt-assets/images/disciple-tools-logo-white.png" ); ?>" style="margin:0 17px; height: 20px" alt="logo-image"></a>
        </div>
        <div class="top-bar-left">
            <ul class="dropdown menu" data-dropdown-menu>
                <?php
                foreach ( $tabs as $tab ) :
                    ?>
                    <li><a href="<?php echo esc_url( $tab["link"] ) ?>"><?php echo esc_html( $tab["label"] ) ?>&nbsp;</a>
                        <?php
                        if ( isset( $tab['submenu'] ) && ! empty( $tab['submenu'] ) ) {
                            ?><ul><?php
                            foreach( $tab['submenu'] as $submenu ) {
                                ?>
                                <li><a href="<?php echo esc_url( $submenu["link"] ) ?>"><?php echo esc_html( $submenu["label"] ) ?></a></li>
                                <?php
                            }
                            ?></ul><?php
                        }
                        ?></li>
                <?php endforeach;

                //append a non standard menu item at the end
                do_action( 'dt_top_nav_desktop' );
                ?>
            </ul>
        </div>
        <div class="top-bar-right">
            <ul class="dropdown menu" data-dropdown-menu>
                <?php
                /* core update */
                if ( current_user_can( "update_core" ) ){
                    $update = maybe_unserialize( get_site_option( "puc_external_updates_theme-disciple-tools-theme", "" ) );
                    if ( !empty( $update ) && isset( $update->update->version ) && version_compare( $update->update->version, wp_get_theme()->version, '>' ) ) : ?>
                        <li class="image-menu-nav">
                            <a href="<?php echo esc_url( network_admin_url( 'update-core.php' ) ); ?>">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/broken.svg' )?>" />
                                <span><?php esc_html_e( 'Theme Update Available!', 'disciple_tools' ); ?></span>
                            </a>
                        </li>
                    <?php endif;
                }
                ?>

                <!-- profile name -->
                <?php if ( isset( $tabs['admin']['profile']['hidden'] ) && empty( $tabs['admin']['profile']['hidden'] ) ) : ?>
                <li class="image-menu-nav">
                    <a href="<?php echo esc_url( $tabs['admin']['profile']['link'] ?? get_template_directory_uri() . "/dt-assets/images/profile.svg" ); ?>">
                        <img title="<?php echo esc_html( $tabs['admin']['profile']['label'] ); ?>" src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/profile.svg" ) ?>">
                        <span dir="auto"><?php echo esc_html( $tabs['admin']['profile']['label'] ); ?></span>
                    </a>
                </li>
                <?php endif; ?>


                <li class="has-submenu center-items add-buttons">
                    <button>
                        <img title="<?php esc_html_e( "Add New", "disciple_tools" ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/circle-add-plus.svg" ?>" style="width:24px;">
                    </button>
                    <!--  /* HEADER add menu */ -->
                    <ul class="submenu menu vertical title-bar-right add-new-items-dropdown">
                        <?php do_action( 'dt_nav_add_post_menu' ) ?>
                    </ul>
                </li>
                <!--  /* HEADER notifications */ -->
                <li class="image-menu-nav">
                    <a href="<?php echo esc_url( site_url( '/notifications' ) ); ?>">
                        <img title="<?php esc_html_e( "Notifications", 'disciple_tools' ); ?>" src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/bell.svg" ?>">
                        <span class="badge alert notification-count" style="display:none"></span>
                    </a>
                </li>

                <!-- settings -->
                <?php if ( isset( $tabs['admin']['settings'] ) && ! empty( $tabs['admin']['settings'] ) ) : ?>
                <li class="has-submenu center-items">
                    <button>
                        <img src="<?php echo esc_url( get_template_directory_uri() ) . "/dt-assets/images/settings.svg" ?>">
                    </button>
                    <ul class="submenu menu vertical">

                        <?php do_action( 'dt_settings_menu_pre' ) ?>

                        <li><a href="<?php echo esc_url( site_url( '/' ) ) . 'settings/'; ?>"><?php esc_html_e( 'Settings', 'disciple_tools' )?></a></li>

                        <?php if ( current_user_can( 'manage_dt' ) ) : ?>
                            <li><a href="<?php echo esc_url( get_admin_url() ); ?>"><?php esc_html_e( "Admin", "disciple_tools" ); ?></a></li>
                        <?php endif; ?>

                        <?php if ( current_user_can( 'manage_dt' ) || current_user_can( 'list_users' ) ) : ?>
                            <li><a href="<?php echo esc_url( site_url( '/user-management/users/' ) ); ?>"><?php esc_html_e( "Users", "disciple_tools" ); ?></a></li>
                        <?php endif; ?>

                        <li><a href="https://disciple.tools/user-docs" target="_blank" rel="noreferrer"><?php esc_html_e( 'Help', 'disciple_tools' ) ?></a></li>

                        <?php do_action( 'dt_settings_menu_post' ) ?>

                        <li><a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Log Off', 'disciple_tools' )?></a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php do_action( 'dt_nav_add_post_settings' ) ?>

            </ul>
        </div>
    </div>
</div>
