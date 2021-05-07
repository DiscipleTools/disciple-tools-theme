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
$dt_nav_tabs = dt_default_menu_array();


?>

<script>

    jQuery(document).ready(function () {
        get_notifications(false, true, dropdown = true, limit = 5);
    });

</script>

<!--  /* TOP LEFT SIDE MENU AREA */ -->
<div data-sticky-container>
    <div class="title-bar hide-for-large" data-sticky data-responsive-toggle="top-bar-menu" data-margin-top="0" data-sticky-on="medium">

        <div class="title-bar-left">

            <!-- menu -->
            <?php if ( ! $dt_nav_tabs['admin']['menu']['hidden'] ?? ! false ) : ?>
                <button class="" type="button" data-open="off-canvas">
                    <img src="<?php echo esc_url( $dt_nav_tabs['admin']['menu']['icon'] ?? get_template_directory_uri() . '/dt-assets/images/hamburger.svg' ) ?>">
                </button>
            <?php endif; ?>

            <!-- site logo -->
            <?php if ( ! $dt_nav_tabs['admin']['site']['hidden'] ?? ! false ) : ?>
                <div class="title-bar-title" style="margin-left: 5px">
                    <a href="<?php echo esc_url( $dt_nav_tabs['admin']['site']['link'] ?? site_url() )?>" style="padding-left:0;vertical-align: middle" ><img src="<?php echo esc_url( $dt_nav_tabs['admin']['site']['icon'] ?? get_template_directory_uri() . "/dt-assets/images/disciple-tools-logo-white.png" ); ?>" style="margin:0; height: 20px" alt="logo-image"></a>
                </div>
            <?php endif; ?>

        </div>

        <div class="title-bar-right">

            <!-- advanced search -->
            <?php if ( isset( $dt_nav_tabs['admin']['advanced_search'] ) && ! empty( $dt_nav_tabs['admin']['advanced_search'] ) ) : ?>
                <a class="advanced-search-nav-button" href="<?php echo esc_url( $dt_nav_tabs['admin']['advanced_search']['link'] ?? $dt_nav_tabs['admin']['advanced_search']['icon'] ); ?>" style="margin-left: 10px">
                    <img title="<?php echo esc_html( $dt_nav_tabs['admin']['advanced_search']['label'] ); ?>" src="<?php echo esc_url( $dt_nav_tabs['admin']['advanced_search']['icon'] ); ?>">
                </a>
            <?php endif; // end advanced search ?>

            <!-- add new-->
            <?php if ( isset( $dt_nav_tabs['admin']['add_new'] ) && ! empty( $dt_nav_tabs['admin']['add_new'] ) ) : ?>
                <ul class="dropdown menu" data-dropdown-menu style="display:inline-block; margin-left: 10px">
                    <li class="has-submenu center-items" style="width:21px;">
                        <button>
                            <img title="<?php esc_html( $dt_nav_tabs['admin']['add_new']['label'] ?? '' ); ?>" src="<?php echo esc_url( $dt_nav_tabs['admin']['add_new']['icon'] ?? '' ?? get_template_directory_uri() . "/dt-assets/images/circle-add-plus.svg" ) ?>" style="width:24px;">
                        </button>
                        <ul class="submenu menu vertical add-new-items-dropdown is-dropdown-submenu" style="text-align:left;">
                            <?php if ( isset( $dt_nav_tabs['admin']['add_new']['submenu'] ) && ! empty( $dt_nav_tabs['admin']['add_new']['submenu'] ) ) : ?>
                                <?php foreach ( $dt_nav_tabs['admin']['add_new']['submenu'] as $dt_nav_submenu ) : ?>
                                    <li>
                                        <a class="add-new-menu-item" href="<?php echo esc_url( $dt_nav_submenu['link'] ) ?>">
                                            <img title="<?php echo esc_html( $dt_nav_submenu['label'] ); ?>" src="<?php echo esc_url( $dt_nav_submenu['icon'] ?? get_template_directory_uri()  . "/dt-assets/images/circle-add-plus.svg" ) ?>">
                                            <?php echo esc_html( $dt_nav_submenu['label'] ); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
            <?php endif; ?>

            <!-- notifications -->
            <?php if ( isset( $dt_nav_tabs['admin']['notifications'] ) && ! empty( $dt_nav_tabs['admin']['notifications'] ) ) : ?>
                <a href="#" class="notifications-menu-item" type="button" data-toggle="notification-dropdown" style="margin-left: 15px; margin-bottom: 0">
                    <img title="<?php esc_html( $dt_nav_tabs['admin']['notifications']['label'] ?? __( "Notifications", 'disciple_tools' ) ); ?>" src="<?php echo esc_url( $dt_nav_tabs['admin']['notifications']['icon'] ?? get_template_directory_uri() . "/dt-assets/images/bell.svg" ); ?>">
                    <span class="badge alert notification-count" style="display:none"></span>
                </a>
            <?php endif; ?>

            <!-- settings -->
            <?php if ( isset( $dt_nav_tabs['admin']['settings'] ) && ! empty( $dt_nav_tabs['admin']['settings'] ) ) : ?>
                <a href="<?php echo esc_url( $dt_nav_tabs['admin']['settings']['link'] ?? site_url( '/settings/' ) ); ?>" style="margin-left: 10px">
                    <img title="<?php esc_html( $dt_nav_tabs['admin']['settings']['label'] ?? __( "Settings", 'disciple_tools' ) ); ?>" src="<?php echo esc_url( $dt_nav_tabs['admin']['settings']['icon'] ?? get_template_directory_uri() . "/dt-assets/images/gear.svg" ) ?>">
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
            <a href="<?php echo esc_url( $dt_nav_tabs['admin']['site']['link'] ?? site_url() )?>" style="padding-left:0;vertical-align: middle"><img src="<?php echo esc_url( $dt_nav_tabs['admin']['site']['icon'] ?? get_template_directory_uri() . "/dt-assets/images/disciple-tools-logo-white.png" ); ?>" style="margin:0 17px; height: 20px" alt="logo-image"></a>
        </div>
        <div class="top-bar-left">
            <ul class="dropdown menu" data-dropdown-menu>
                <?php
                foreach ( $dt_nav_tabs['main'] as $dt_main_tabs ) :
                    ?>
                    <li><a href="<?php echo esc_url( $dt_main_tabs['link'] ) ?>"><?php echo esc_html( $dt_main_tabs['label'] ) ?>&nbsp;</a>
                        <?php
                        if ( isset( $dt_main_tabs['submenu'] ) && ! empty( $dt_main_tabs['submenu'] ) ) {
                            ?>
                            <ul class="menu vertical nested is-dropdown-submenu">
                                <?php
                                foreach ( $dt_main_tabs['submenu'] as $dt_nav_submenu ) :
                                    if ( ! $dt_nav_submenu['hidden'] ?? false ) : ?>
                                        <li><a href="<?php echo esc_url( $dt_nav_submenu['link'] ) ?>"><?php echo esc_html( $dt_nav_submenu['label'] ) ?></a></li>
                                        <?php
                                    endif;
                                endforeach;
                                ?>
                            </ul>
                            <?php
                        }
                        ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="top-bar-right">
            <ul class="dropdown menu" data-dropdown-menu>

                <!-- core update -->
                <?php
                if ( current_user_can( "update_core" ) ){
                    $theme = wp_get_theme();
                    $update = maybe_unserialize( get_site_option( "puc_external_updates_theme-" . $theme->get_stylesheet(), "" ) );
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
                <?php if ( isset( $dt_nav_tabs['admin']['profile']['hidden'] ) && empty( $dt_nav_tabs['admin']['profile']['hidden'] ) ) : ?>
                    <li class="image-menu-nav">
                        <a href="<?php echo esc_url( $dt_nav_tabs['admin']['profile']['link'] ?? get_template_directory_uri() . "/dt-assets/images/profile.svg" ); ?>">
                            <img title="<?php echo esc_html( $dt_nav_tabs['admin']['profile']['label'] ); ?>" src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/profile.svg" ) ?>">
                            <span dir="auto"><?php echo esc_html( $dt_nav_tabs['admin']['profile']['label'] ); ?></span>
                        </a>
                    </li>
                <?php endif; // end profile ?>


                <!-- advanced search -->
                <?php if ( isset( $dt_nav_tabs['admin']['advanced_search']['hidden'] ) && empty( $dt_nav_tabs['admin']['advanced_search']['hidden'] ) ) : ?>
                    <li class="image-menu-nav">
                        <a class="advanced-search-nav-button" href="<?php echo esc_url( $dt_nav_tabs['admin']['advanced_search']['link'] ?? $dt_nav_tabs['admin']['advanced_search']['icon'] ); ?>">
                            <img title="<?php echo esc_html( $dt_nav_tabs['admin']['advanced_search']['label'] ); ?>" src="<?php echo esc_url( $dt_nav_tabs['admin']['advanced_search']['icon'] ); ?>">
                        </a>
                    </li>
                <?php endif; // end advanced search ?>


                <!-- add new -->
                <?php if ( isset( $dt_nav_tabs['admin']['add_new']['hidden'] ) && empty( $dt_nav_tabs['admin']['add_new']['hidden'] ) ) : ?>
                    <li class="has-submenu center-items add-buttons">
                        <button>
                            <img title="<?php esc_html( $dt_nav_tabs['admin']['add_new']['label'] ?? '' ); ?>" src="<?php echo esc_url( $dt_nav_tabs['admin']['add_new']['icon'] ?? '' ?? get_template_directory_uri() . "/dt-assets/images/circle-add-plus.svg" ) ?>" style="width:24px;">
                        </button>
                        <!--  /* HEADER add menu */ -->
                        <ul class="submenu menu vertical title-bar-right add-new-items-dropdown">
                            <?php if ( isset( $dt_nav_tabs['admin']['add_new']['submenu'] ) && ! empty( $dt_nav_tabs['admin']['add_new']['submenu'] ) ) : ?>
                                <?php foreach ( $dt_nav_tabs['admin']['add_new']['submenu'] as $dt_nav_submenu ) : ?>
                                    <li>
                                        <a class="add-new-menu-item" href="<?php echo esc_url( $dt_nav_submenu['link'] ) ?>">
                                            <img title="<?php echo esc_html( $dt_nav_submenu['label'] ); ?>" src="<?php echo esc_url( $dt_nav_submenu['icon'] ?? get_template_directory_uri()  . "/dt-assets/images/circle-add-plus.svg" ) ?>">
                                            <?php echo esc_html( $dt_nav_submenu['label'] ); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; // end add new ?>


                <!--  notifications -->
                <?php if ( isset( $dt_nav_tabs['admin']['notifications']['hidden'] ) && empty( $dt_nav_tabs['admin']['notifications']['hidden'] ) ) : ?>
                    <li class="image-menu-nav">
                        <a href="#" class="notifications-menu-item" type="button" data-toggle="notification-dropdown" style="margin-bottom: 0">
                            <img title="<?php esc_html( $dt_nav_tabs['admin']['notifications']['label'] ?? __( "Notifications", 'disciple_tools' ) ); ?>" src="<?php echo esc_url( $dt_nav_tabs['admin']['notifications']['icon'] ?? get_template_directory_uri() . "/dt-assets/images/bell.svg" ); ?>">
                            <span class="badge alert notification-count" style="display:none"></span>
                        </a>
                    </li>
                <?php endif; // end notifications ?>


                <!-- settings -->
                <?php if ( isset( $dt_nav_tabs['admin']['settings']['hidden'] ) && empty( $dt_nav_tabs['admin']['settings']['hidden'] ) ) : ?>
                    <li class="has-submenu center-items">
                        <button>
                            <img src="<?php echo esc_url( $dt_nav_tabs['admin']['settings']['icon'] ?? get_template_directory_uri() . "/dt-assets/images/settings.svg" ) ?>">
                        </button>
                        <ul class="submenu menu vertical">

                            <?php foreach ( $dt_nav_tabs['admin']['settings']['submenu'] as $dt_nav_submenu ) :
                                if ( ! $dt_nav_submenu['hidden'] ?? false ) : ?>
                                    <li><a href="<?php echo esc_url( $dt_nav_submenu['link'] ) ?>"><?php echo esc_html( $dt_nav_submenu['label'] ) ?></a></li>
                                <?php endif;
                            endforeach; ?>

                            <?php do_action( 'dt_settings_menu_post' ) ?>

                            <li><a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Log Off', 'disciple_tools' )?></a></li>
                        </ul>
                    </li>
                <?php endif; // end settings ?>

                <?php do_action( 'dt_nav_add_post_settings' ) ?>

            </ul>
        </div>
    </div>
</div>

<?php if ( isset( $dt_nav_tabs['admin']['notifications'] ) && ! empty( $dt_nav_tabs['admin']['notifications'] ) ) : ?>

    <div class="dropdown-pane" id="notification-dropdown" data-position="bottom" data-alignment="right" data-dropdown data-hover="true" data-hover-pane="true" >

        <div class="sized-box"></div>

        <div class="bordered-box">

            <div class="grid-x header">
                <div class="cell">
                    <div class="grid-x grid-margin-x align-middle">
                        <div class="small-3 medium-3 cell grid-x align-middle">
                            <span class="badge alert notification-count" style="display:none;">&nbsp;</span>
                            <div class="new-notification-label"><strong><?php esc_html_e( 'New', 'disciple_tools' )?></strong></div>
                        </div>
                        <div class="small-6 medium-6 cell">
                            <div class="expanded small button-group">
                                <button id="dropdown-all" type="button"
                                        onclick="toggle_dropdown_buttons('all'); get_notifications( all = true, true, true, 5 );"
                                        class="button hollow"><?php echo esc_html_x( 'All', 'List Filters', 'disciple_tools' ) ?>
                                </button>
                                <button id="dropdown-new" type="button"
                                        onclick="toggle_dropdown_buttons('new'); get_notifications( all = false, true, true, 5 );"
                                        class="button"><?php esc_html_e( 'Unread', 'disciple_tools' )?>
                                </button>
                            </div>
                        </div>
                        <div class="small-3 medium-3 cell" style="text-align:right;">
                            <span>
                                <a id="see-all" href="<?php echo esc_url( $dt_nav_tabs['admin']['notifications']['link'] ?? site_url( '/notifications/' ) ); ?>" >
                                    <?php esc_html_e( 'See All', 'disciple_tools' ) ?>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="notification-list" class="grid-x" style="border-top: 1px solid #ccc;"><span class="loading-spinner active" style="margin:1em;"></span></div>


            <div class="" style="text-align:center;">
                <span>
                    <a onclick="mark_all_viewed()"><?php esc_html_e( 'Mark all as read', 'disciple_tools' ) ?></a>  -
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>settings/#notifications">
                        <?php esc_html_e( 'Settings', 'disciple_tools' )?>
                    </a>
                </span=>
                <!-- <span class="show-for-small-only">
                    <a onclick="mark_all_viewed()"><?php esc_html_e( 'Mark All', 'disciple_tools' ) ?></a>
                </span> -->
            </div>
        </div>
    </div>

<?php endif; ?>

<!-- Load advanced search model template part -->
<?php get_template_part( 'dt-assets/parts/modals/modal', 'advanced-search' ); ?>
