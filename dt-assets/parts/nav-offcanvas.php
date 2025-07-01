<div class="off-canvas position-left" id="off-canvas" data-off-canvas>
    <ul class="vertical menu sticky is-stuck is-at-top" data-accordion-menu>
        <?php

        /**
         * Loads offcanvas menu items for mobile
         * @note Main post types (Contacts, Groups, Metrics) fire between 20-30. If you want to add an item before the
         * main post types, load before 20, if you want to load after the list, load after 30.
         */
        $dt_nav_tabs = dt_default_menu_array();
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

        <?php
        foreach ( $dt_nav_tabs['main'] as $dt_main_tabs ) :
            if ( ! ( isset( $dt_main_tabs['hidden'] ) && $dt_main_tabs['hidden'] ) ) {
                ?>
                <li><a href="<?php echo esc_url( $dt_main_tabs['link'] ) ?>"><?php echo esc_html( $dt_main_tabs['label'] ) ?>&nbsp;</a>
                    <?php
                    if ( isset( $dt_main_tabs['submenu'] ) && ! empty( $dt_main_tabs['submenu'] ) ) : ?>
                        <ul class="is-active menu vertical nested">
                            <?php foreach ( $dt_main_tabs['submenu'] as $dt_nav_submenu ) :
                                if ( ! $dt_nav_submenu['hidden'] ?? false ) : ?>
                                    <li><a href="<?php echo esc_url( $dt_nav_submenu['link'] ) ?>"><?php echo esc_html( $dt_nav_submenu['label'] ) ?></a></li>
                                <?php endif;
                            endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php } ?>
        <?php endforeach; ?>

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
