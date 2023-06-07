<?php
if ( !defined( 'ABSPATH' ) ){
    exit;
} // Exit if accessed directly
?>

<!doctype html>

<html class="no-js" <?php language_attributes(); ?>>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport"
              content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no">
        <meta class="foundation-mq">

        <?php
        $dt_override_header_meta = apply_filters( 'dt_override_header_meta', false );
        if ( !$dt_override_header_meta ) : ?>
            <?php if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) { ?>
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
            <?php } elseif ( function_exists( 'get_site_icon_url' ) ) {  ?>
                <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url( get_site_icon_url( 180 ) ); ?>">
                <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( get_site_icon_url( 32 ) ); ?>">
                <link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url( get_site_icon_url( 16 ) ); ?>">
                <link rel="shortcut icon" href="<?php echo esc_url( get_site_icon_url() ); ?>">
            <?php } ?>
        <?php endif; ?>

        <style>
            body {
                font-family: Arial, Helvetica, sans-serif;
            }
            h2 {
                text-align: center;
                font-weight: bold;
            }
            hr {
                border: 1px solid #808080;
                max-width: 75%;
            }
        </style>

        <title><?php echo esc_html( __( 'Invalid Link', 'disciple_tools' ) ) ?></title>
    </head>
    <body>
        <h2>
            <b><?php echo esc_html( __( 'Invalid Link', 'disciple_tools' ) ) ?></b>
        </h2>
        <hr/>
        <br><br>
        <div style="text-align: center;">
            <p><?php echo esc_html( __( 'Link key is no longer valid or has expired.', 'disciple_tools' ) ) ?></p>
            <p><?php echo sprintf( esc_html( __( 'Please %s, or locate a more recent key, or request a new one.', 'disciple_tools' ) ), '<a href="' . esc_attr( site_url( 'wp-login.php' ) ) . '">' . esc_html( __( 'login', 'disciple_tools' ) ) . '</a>' ) ?></p>
        </div>
    </body>
</html>
