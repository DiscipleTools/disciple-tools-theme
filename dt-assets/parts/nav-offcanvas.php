<div class="off-canvas position-left" id="off-canvas" data-off-canvas>
    <ul class="vertical menu sticky is-stuck is-at-top" data-accordion-menu>
        <?php

        /**
         * Loads offcanvas menu items for mobile
         * @note Main post types (Contacts, Groups, Metrics) fire between 20-30. If you want to add an item before the
         * main post types, load before 20, if you want to load after the list, load after 30.
         */
        $tabs = dt_default_menu_array();
        ?>

        <!-- profile name -->
        <?php if ( isset( $tabs['admin']['profile']['hidden'] ) && empty( $tabs['admin']['profile']['hidden'] ) ) : ?>
        <li class="image-menu-nav">
            <a href="<?php echo esc_url( $tabs['admin']['profile']['link'] ?? get_template_directory_uri() . "/dt-assets/images/profile.svg" ); ?>">
                <img title="<?php echo esc_html( $tabs['admin']['profile']['label'] ); ?>" src="<?php echo esc_url( get_template_directory_uri() . "/dt-assets/images/profile.svg" ) ?>">
                <span dir="auto"><?php echo esc_html( $tabs['admin']['profile']['label'] ); ?></span>
            </a>
        </li>
        <?php endif; // end profile ?>

        <li>&nbsp;<!-- Spacer--></li>

        <?php
        foreach ( $tabs['main'] as $tab ) :
            ?>
            <li><a href="<?php echo esc_url( $tab['link'] ) ?>"><?php echo esc_html( $tab['label'] ) ?>&nbsp;</a>
                <?php
                if ( isset( $tab['submenu'] ) && ! empty( $tab['submenu'] ) ) {
                    ?><ul class="is-active"><?php
                    foreach( $tab['submenu'] as $submenu ) {
                        ?>
                        <li><a href="<?php echo esc_url( $submenu['link'] ) ?>"><?php echo esc_html( $submenu['label'] ) ?></a></li>
                        <?php
                    }
                    ?></ul><?php
                }
                ?>
            </li>
        <?php endforeach; ?>

        <?php //do_action( 'dt_off_canvas_nav' ); // @todo remove ?>

        <li>&nbsp;<!-- Spacer--></li>

        <!--  notifications -->
        <?php if ( isset( $tabs['admin']['notifications']['hidden'] ) && empty( $tabs['admin']['notifications']['hidden'] ) ) : ?>
            <li class="image-menu-nav">
                <a href="<?php echo esc_url( $tabs['admin']['notifications']['link'] ?? site_url( '/notifications/' ) ); ?>">
                    <img title="<?php echo esc_html( $tabs['admin']['notifications']['label'] ?? __( "Notifications", 'disciple_tools' ) ); ?>" src="<?php echo esc_url( $tabs['admin']['notifications']['icon'] ?? get_template_directory_uri() . "/dt-assets/images/bell.svg" ) ?>">
                    <span class="badge alert notification-count" style="display:none"></span>
                </a>
            </li>
        <?php endif; // end notifications ?>


        <?php
        /* settings */
        if ( isset( $tabs['admin']['settings']['hidden'] ) && empty( $tabs['admin']['settings']['hidden'] ) ) : ?>
            <li><a href="<?php echo esc_url( $tabs['admin']['settings']['link'] ?? '' ); ?>"><?php echo esc_html( $tabs['admin']['settings']['label'] ?? '' ); ?></a></li>
        <?php endif; ?>


        <?php
        /* user management */
        if ( ( current_user_can( 'manage_dt' ) || current_user_can( 'list_users' ) )
            && ( isset( $tabs['admin']['settings']['submenu']['user_management']['link'] ) && ! empty( $tabs['admin']['settings']['submenu']['user_management']['link'] ) ) ) : ?>
            <li><a href="<?php echo esc_url( $tabs['admin']['settings']['submenu']['user_management']['link'] ?? '' ); ?>"><?php echo esc_html( $tabs['admin']['settings']['submenu']['user_management']['label'] ?? '' ); ?></a></li>
        <?php endif; ?>


        <?php
        /* Admin */
        if ( ( user_can( get_current_user_id(), 'manage_dt' ) )
        && (isset( $tabs['admin']['settings']['submenu']['admin']['link'] ) && ! empty( $tabs['admin']['settings']['submenu']['admin']['link'] ) ) ) : ?>
            <li><a href="<?php echo esc_url( $tabs['admin']['settings']['submenu']['admin']['link'] ?? '' ); ?>"><?php echo esc_html( $tabs['admin']['settings']['submenu']['admin']['label'] ?? '' ); ?></a></li>
        <?php endif; ?>

        <li><a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Log Off', 'disciple_tools' )?></a></li>

    </ul>
</div>
