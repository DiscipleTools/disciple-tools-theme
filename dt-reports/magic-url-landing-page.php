<?php
if ( !defined( 'ABSPATH' ) ){
    exit;
} // Exit if accessed directly
?>

<!doctype html>

<html class="no-js" <?php language_attributes(); ?>>
    <head>

        <?php dt_header_icon_and_meta(); ?>


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
            <p><?php printf( esc_html( __( 'Please %s, or locate a more recent key, or request a new one.', 'disciple_tools' ) ), '<a href="' . esc_attr( site_url( 'wp-login.php' ) ) . '">' . esc_html( __( 'login', 'disciple_tools' ) ) . '</a>' ) ?></p>
        </div>
    </body>
</html>
