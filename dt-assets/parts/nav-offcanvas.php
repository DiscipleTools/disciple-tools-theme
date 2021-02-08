<div class="off-canvas position-left" id="off-canvas" data-off-canvas>
    <ul class="vertical menu sticky is-stuck is-at-top" data-accordion-menu>
        <?php

        /**
         * Loads offcanvas menu items for mobile
         * @note Main post types (Contacts, Groups, Metrics) fire between 20-30. If you want to add an item before the
         * main post types, load before 20, if you want to load after the list, load after 30.
         */
        $tabs = apply_filters( "dt_nav", dt_default_menu_array() );
        $admin = $tabs['admin'] ?? [];
        unset($tabs['admin']);
        $icons = $tabs['icons'] ?? [];
        unset($tabs['icons']);
        $site = $tabs['site'] ?? [];
        unset($tabs['site']);

        foreach ( $tabs as $tab ) :
            ?>
            <li><a href="<?php echo esc_url( $tab['link'] ?? '' ) ?>"><?php echo esc_html( $tab['label'] ?? '' ) ?>&nbsp;</a>
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

        <?php
        /* Notifications */
        if ( isset( $admin['notifications'] ) && ! empty( $admin['notifications'] ) ) : ?>
            <li><a href="<?php echo esc_url( $admin['notifications']['link'] ?? '' ); ?>"><?php echo esc_html( $admin['notifications']['label'] ?? '' ); ?></a></li>
        <?php endif; ?>

        <?php
        /* Settings */
        if ( isset( $admin['settings'] ) && ! empty( $admin['settings'] ) ) : ?>
            <li><a href="<?php echo esc_url( $admin['settings']['link'] ?? '' ); ?>"><?php echo esc_html( $admin['settings']['label'] ?? '' ); ?></a></li>
        <?php endif; ?>


        <?php
        /* User Management */
        if ( ( current_user_can( 'manage_dt' ) || current_user_can( 'list_users' )
            && ( isset( $admin['user_management'] ) && ! empty( $admin['user_management'] ) ) ) ) : ?>
            <li><a href="<?php echo esc_url( $admin['user_management']['link'] ?? '' ); ?>"><?php echo esc_html( $admin['user_management']['label'] ?? '' ); ?></a></li>
        <?php endif; ?>


        <?php
        /* Admin */
        if ( ( user_can( get_current_user_id(), 'manage_dt' ) )
        && (isset( $admin['admin'] ) && ! empty( $admin['admin'] ) ) ) : ?>
            <li><a href="<?php echo esc_url( $admin['admin']['link'] ?? '' ); ?>"><?php echo esc_html( $admin['admin']['label'] ?? '' ); ?></a></li>
        <?php endif; ?>

        <?php
        /* Logoff */
        if ( isset( $admin['logoff'] ) && ! empty( $admin['logoff'] ) ) : ?>
            <li><a href="<?php echo esc_url( $admin['logoff']['link'] ?? '' ); ?>"><?php echo esc_html( $admin['logoff']['label'] ?? '' ); ?></a></li>
        <?php endif; ?>

    </ul>
</div>
