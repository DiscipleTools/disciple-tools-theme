<div class="off-canvas position-left" id="off-canvas" data-off-canvas>
    <ul class="vertical menu sticky is-stuck is-at-top" data-accordion-menu>
        <?php

        /**
         * Loads offcanvas menu items for mobile
         * @note Main post types (Contacts, Groups, Metrics) fire between 20-30. If you want to add an item before the
         * main post types, load before 20, if you want to load after the list, load after 30.
         */
        $tabs = apply_filters( "off_canvas_menu_options", [] );

        foreach ( $tabs as $tab ) : ?>
            <li><a href="<?php echo esc_url( $tab["link"] ) ?>"><?php echo esc_html( $tab["label"] ) ?>&nbsp;</a>
                <?php
                if ( isset( $tab['submenu'] ) && ! empty( $tab['submenu'] ) ) {
                    ?><ul class="is-active"><?php
                    foreach( $tab['submenu'] as $submenu ) {
                        ?>
                        <li><a href="<?php echo esc_url( $submenu["link"] ) ?>"> <?php echo esc_html( $submenu["label"] ) ?></a></li>
                        <?php
                    }
                    ?></ul><?php
                }
                ?></li>
        <?php endforeach;

        //append a non standard menu item at the end
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
</div>
