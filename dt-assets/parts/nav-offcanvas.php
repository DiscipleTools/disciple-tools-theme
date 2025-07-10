<div class="off-canvas position-left <?php echo dt_is_mobile_request() ? 'mobile-enhanced' : ''; ?>" id="off-canvas" data-off-canvas>
    <?php if ( dt_is_mobile_request() ) : ?>
        <!-- Mobile Enhanced Header -->
        <div class="mobile-offcanvas-header">
            <div class="flex items-center justify-between p-4 border-b border-white border-opacity-20">
                <div class="flex items-center">
                    <?php 
                    $dt_nav_tabs = dt_default_menu_array();
                    $logo_url = $dt_nav_tabs['admin']['site']['icon'] ?? get_template_directory_uri() . '/dt-assets/images/disciple-tools-logo-white.png';
                    ?>
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="h-8 w-auto mr-3">
                    <span class="text-white font-semibold"><?php bloginfo( 'name' ); ?></span>
                </div>
                <button class="close-button text-white" aria-label="Close menu" type="button" data-close>
                    <span aria-hidden="true" class="text-2xl">&times;</span>
                </button>
            </div>
        </div>
    <?php else : ?>
        <?php $dt_nav_tabs = dt_default_menu_array(); ?>
    <?php endif; ?>
    
    <ul class="vertical menu sticky is-stuck is-at-top <?php echo dt_is_mobile_request() ? 'mobile-menu' : ''; ?>" data-accordion-menu>
        <?php

        /**
         * Loads offcanvas menu items for mobile
         * @note Main post types (Contacts, Groups, Metrics) fire between 20-30. If you want to add an item before the
         * main post types, load before 20, if you want to load after the list, load after 30.
         */
        if ( !isset( $dt_nav_tabs ) ) {
            $dt_nav_tabs = dt_default_menu_array();
        }
        ?>

        <!-- profile name -->
        <?php if ( isset( $dt_nav_tabs['admin']['profile']['hidden'] ) && empty( $dt_nav_tabs['admin']['profile']['hidden'] ) ) : ?>
            <li class="image-menu-nav">
                <a href="<?php echo esc_url( $dt_nav_tabs['admin']['profile']['link'] ?? get_template_directory_uri() . '/dt-assets/images/profile.svg' ); ?>">
                    <img class="dt-white-icon" title="<?php echo esc_html( $dt_nav_tabs['admin']['profile']['label'] ); ?>" src="<?php echo esc_url( $dt_nav_tabs['admin']['profile']['icon'] ?? get_template_directory_uri() . '/dt-assets/images/profile.svg?v=2' ) ?>" style="vertical-align: middle;">
                    <span dir="auto"><?php echo esc_html( $dt_nav_tabs['admin']['profile']['label'] ); ?></span>
                </a>
            </li>
        <?php endif; // end profile ?>

        <li><hr ><!-- Spacer--></li>

        <?php if ( dt_is_mobile_request() ) : ?>
            <!-- Mobile Enhanced Navigation -->
            <li class="mobile-nav-section-header">
                <span class="mobile-nav-section-title"><?php esc_html_e( 'Main Navigation', 'disciple_tools' ); ?></span>
            </li>
        <?php endif; ?>

        <?php
        foreach ( $dt_nav_tabs['main'] as $key => $dt_main_tabs ) :
            if ( ! ( isset( $dt_main_tabs['hidden'] ) && $dt_main_tabs['hidden'] ) ) {
                ?>
                <li class="<?php echo dt_is_mobile_request() ? 'mobile-nav-item' : ''; ?>">
                    <a href="<?php echo esc_url( $dt_main_tabs['link'] ) ?>" class="<?php echo dt_is_mobile_request() ? 'mobile-nav-link' : ''; ?>">
                        <?php if ( dt_is_mobile_request() ) : ?>
                            <div class="mobile-nav-item-content">
                                <div class="mobile-nav-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php if ( strpos( strtolower( $dt_main_tabs['label'] ), 'contact' ) !== false ) : ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        <?php elseif ( strpos( strtolower( $dt_main_tabs['label'] ), 'group' ) !== false ) : ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        <?php elseif ( strpos( strtolower( $dt_main_tabs['label'] ), 'metric' ) !== false ) : ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        <?php else : ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                        <?php endif; ?>
                                    </svg>
                                </div>
                                <span class="mobile-nav-text"><?php echo esc_html( $dt_main_tabs['label'] ); ?></span>
                            </div>
                        <?php else : ?>
                            <?php echo esc_html( $dt_main_tabs['label'] ) ?>&nbsp;
                        <?php endif; ?>
                    </a>
                    <?php
                    if ( isset( $dt_main_tabs['submenu'] ) && ! empty( $dt_main_tabs['submenu'] ) ) : ?>
                        <ul class="is-active menu vertical nested <?php echo dt_is_mobile_request() ? 'mobile-submenu' : ''; ?>">
                            <?php foreach ( $dt_main_tabs['submenu'] as $dt_nav_submenu ) :
                                if ( ! $dt_nav_submenu['hidden'] ?? false ) : ?>
                                    <li><a href="<?php echo esc_url( $dt_nav_submenu['link'] ) ?>" class="<?php echo dt_is_mobile_request() ? 'mobile-submenu-link' : ''; ?>"><?php echo esc_html( $dt_nav_submenu['label'] ) ?></a></li>
                                <?php endif;
                            endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php } ?>
        <?php endforeach; ?>

        <?php if ( dt_is_mobile_request() ) : ?>
            <!-- Mobile Record Types Section -->
            <li class="mobile-nav-section-header">
                <span class="mobile-nav-section-title"><?php esc_html_e( 'Record Types', 'disciple_tools' ); ?></span>
            </li>
            
            <?php 
            // Get all main post types for mobile menu
            foreach ( $dt_nav_tabs['main'] as $key => $nav_item ) :
                if ( !( $nav_item['hidden'] ?? false ) ) :
            ?>
                <li class="mobile-nav-item mobile-post-type-item">
                    <a href="<?php echo esc_url( $nav_item['link'] ); ?>" class="mobile-nav-link mobile-post-type-link">
                        <div class="mobile-nav-item-content">
                            <div class="mobile-nav-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?php if ( strpos( strtolower( $nav_item['label'] ), 'contact' ) !== false ) : ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    <?php elseif ( strpos( strtolower( $nav_item['label'] ), 'group' ) !== false ) : ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    <?php elseif ( strpos( strtolower( $nav_item['label'] ), 'training' ) !== false ) : ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    <?php else : ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    <?php endif; ?>
                                </svg>
                            </div>
                            <span class="mobile-nav-text"><?php echo esc_html( $nav_item['label'] ); ?></span>
                            <svg class="mobile-nav-arrow w-4 h-4 ml-auto opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                </li>
            <?php 
                endif;
            endforeach; 
            ?>
        <?php endif; ?>

        <li><hr ><!-- Spacer--></li>

        <!--  notifications -->
        <?php if ( isset( $dt_nav_tabs['admin']['notifications']['hidden'] ) && empty( $dt_nav_tabs['admin']['notifications']['hidden'] ) ) : ?>
            <li class="image-menu-nav">
                <a href="<?php echo esc_url( $dt_nav_tabs['admin']['notifications']['link'] ?? site_url( '/notifications/' ) ); ?>">
                    <?php echo esc_html( $dt_nav_tabs['admin']['notifications']['label'] ?? __( 'Notifications', 'disciple_tools' ) ); ?> <span class="badge alert notification-count notification-count-offcanvas" style="display:none; font-size: .8rem;"></span>
                </a>

            </li>
        <?php endif; // end notifications ?>

        <?php
        if ( isset( $dt_nav_tabs['admin']['settings']['submenu'] ) ){
            foreach ( $dt_nav_tabs['admin']['settings']['submenu'] as $dt_nav_submenu ) :
                if ( !$dt_nav_submenu['hidden'] ?? false ) : ?>
                    <li><a href="<?php echo esc_url( $dt_nav_submenu['link'] ) ?>"><?php echo esc_html( $dt_nav_submenu['label'] ) ?></a></li>
                <?php endif;
            endforeach;
        }
        ?>

        <li><a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Log Out', 'disciple_tools' )?></a></li>

    </ul>
</div>
