<!doctype html>

  <html class="no-js"  <?php language_attributes(); ?>>

    <head>

        <?php dt_header_icon_and_meta(); ?>

        <title>
        <?php
        $instance_name = get_bloginfo( 'name' );
        if ( is_single() ) {
            $post_type_key = get_post_type();
            if ( DT_Posts::can_view( get_post_type(), GET_THE_ID() ) ){
                $post_type_label = isset( $post_type_settings['label_plural'] ) ? $post_type_settings['label_plural'] : $post_type_key;
                $title_string = single_post_title( '' ) . ' - ' . ucwords( $post_type_label );
                echo esc_html( $title_string . ' - ' .$instance_name );
            } else {
                echo esc_html( __( 'D.T Record', 'disciple_tools' ) );
            }
        } else if ( is_archive() ){
            echo post_type_archive_title();
        } else {
            $title_string = str_replace( '/', ' - ', untrailingslashit( dt_get_url_path( true ) ) );
            $post_type_settings = apply_filters( 'dt_get_post_type_settings', array(), $title_string );
            $label = isset( $post_type_settings['label_plural'] ) ? $post_type_settings['label_plural'] : ucwords( $title_string );
            echo esc_html( $label . ' - ' . $instance_name );
        }
        ?>
        </title>

        <?php wp_head(); ?>

    </head>

    <!-- Uncomment this line if using the Off-Canvas Menu -->

    <body <?php body_class(); ?>>

        <div class="off-canvas-wrapper">

            <?php get_template_part( 'dt-assets/parts/nav', 'offcanvas' ); ?>

            <div class="off-canvas-content" data-off-canvas-content>

                <header class="header" role="banner">

                     <!-- This navs will be applied to the topbar, above all content
                          To see additional nav styles, visit the /parts directory -->
                        <?php get_template_part( 'dt-assets/parts/nav', 'topbar' ); ?>

                </header> <!-- end .header -->

                <noscript>
                    <header class="header"><?php esc_html_e( 'Javascript must be enabled for this site to function correctly.', 'disciple_tools' ); ?></header>
                </noscript>

                <div id="js-missing-required-browser-features-notice" hidden>
                    <header class="header">
                        <br><br><br>
                        <?php esc_html_e( 'You seem to be using an out-of-date web browser. Without the most up-to-date version of your browser, this may site may not function correctly. Please note that Internet Explorer is not supported.', 'disciple_tools' ); ?>
                        <a href="https://whatbrowser.org" rel="nofollow">
                            <?php esc_html_e( 'See what browser you are using.', 'disciple_tools' ); ?>
                        </a>
                    </header>
                </div>
