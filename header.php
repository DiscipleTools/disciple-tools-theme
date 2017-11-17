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
            <link rel="apple-touch-icon-precomposed" sizes="57x57" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/apple-touch-icon-57x57.png">
            <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/apple-touch-icon-114x114.png">
            <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/apple-touch-icon-72x72.png">
            <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/apple-touch-icon-144x144.png">
            <link rel="apple-touch-icon-precomposed" sizes="60x60" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/apple-touch-icon-60x60.png">
            <link rel="apple-touch-icon-precomposed" sizes="120x120" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/apple-touch-icon-120x120.png">
            <link rel="apple-touch-icon-precomposed" sizes="76x76" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/apple-touch-icon-76x76.png">
            <link rel="apple-touch-icon-precomposed" sizes="152x152" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/apple-touch-icon-152x152.png">
            <link rel="icon" type="image/png" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/favicon-196x196.png" sizes="196x196">
            <link rel="icon" type="image/png" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/favicon-96x96.png" sizes="96x96">
            <link rel="icon" type="image/png" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/favicon-32x32.png" sizes="32x32">
            <link rel="icon" type="image/png" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/favicon-16x16.png" sizes="16x16">
            <link rel="icon" type="image/png" href="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/favicon-128.png" sizes="128x128">
            <meta name="application-name" content="DT">
            <meta name="msapplication-TileColor" content="#FFFFFF">
            <meta name="msapplication-TileImage" content="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/mstile-144x144.png">
            <meta name="msapplication-square70x70logo" content="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/mstile-70x70.png">
            <meta name="msapplication-square150x150logo" content="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/mstile-150x150.png">
            <meta name="msapplication-wide310x150logo" content="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/mstile-310x150.png">
            <meta name="msapplication-square310x310logo" content="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/favicon/mstile-310x310.png">
        <?php } ?>

<!--		<link rel="pingback" href="--><?php //bloginfo('pingback_url'); ?><!--">-->

        <!-- Start wp_head -->
        <?php wp_head(); ?>
        <!-- End wp_head -->

        <!-- Drop Google Analytics here -->
        <!-- end analytics -->

    </head>

    <!-- Uncomment this line if using the Off-Canvas Menu -->

    <body <?php body_class(); ?>>

        <div class="off-canvas-wrapper">

            <?php get_template_part( 'parts/content', 'offcanvas' ); ?>

            <div class="off-canvas-content" data-off-canvas-content>

                <header class="header" role="banner">

                     <!-- This navs will be applied to the topbar, above all content
                          To see additional nav styles, visit the /parts directory -->
                        <?php get_template_part( 'parts/nav', 'offcanvas-topbar' ); ?>

                </header> <!-- end .header -->
