<!doctype html>

  <html class="no-js"  <?php language_attributes(); ?>>

    <head>
        <meta charset="utf-8">

        <!-- Force IE to use the latest rendering engine available -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <!-- Mobile Meta -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta class="foundation-mq">

        <!-- If Site Icon isn't set in customizer -->
        <?php if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) { ?>

            <meta name="apple-mobile-web-app-title" content="Disciple.Tools">
            <meta name="application-name" content="Disciple.Tools">
            <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/apple-touch-icon.png">
            <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/favicon-32x32.png">
            <link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/favicon-16x16.png">
            <link rel="manifest" href="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/site.webmanifest">
            <link rel="mask-icon" href="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/safari-pinned-tab.svg" color="#3f729b">
            <link rel="shortcut icon" href="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/favicon.ico">
            <meta name="msapplication-TileColor" content="#3f729b">
            <meta name="msapplication-TileImage" content="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/mstile-144x144.png">
            <meta name="msapplication-config" content="<?php echo esc_url( get_template_directory_uri() ); ?>/dt-assets/favicons/browserconfig.xml">
            <meta name="theme-color" content="#3f729b">
        <?php } ?>

        <title>
        <?php
        $instance_name = get_bloginfo( 'name' );
        if ( is_single() ) {
            if ( DT_Posts::can_view( get_post_type(), GET_THE_ID() ) ){
                $title_string = single_post_title( '' ) . ' - ' . ucwords( get_post_type() );
                echo esc_html( $title_string . ' - ' .$instance_name );
            } else {
                echo esc_html( __( "D.T Record", 'disciple_tools' ) );
            }
        } else if ( is_archive() ){
            echo post_type_archive_title();
        } else {
            $title_string = ucwords( str_replace( '/', ' - ', dt_get_url_path() ) );
            echo esc_html( $title_string . ' - ' . $instance_name );
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
                    <header class="header"><?php esc_html_e( "Javascript must be enabled for this site to function correctly.", 'disciple_tools' ); ?></header>
                </noscript>

                <div id="js-missing-required-browser-features-notice" hidden>
                    <header class="header">
                        <br><br><br>
                        <?php esc_html_e( "You seem to be using an out-of-date web browser. Without the most up-to-date version of your browser, this may site may not function correctly. Please note that Internet Explorer is not supported.", 'disciple_tools' ); ?>
                        <a href="https://whatbrowser.org" rel="nofollow">
                            <?php esc_html_e( "See what browser you are using.", 'disciple_tools' ); ?>
                        </a>
                    </header>
                </div>
