<?php
/*
Template Name: Blank
*/

if ( ! apply_filters( 'dt_blank_access', false ) ){
    esc_html_e( 'Access to this page not permitted', 'disciple_tools' );
    exit;
}
?>
<!doctype html>

<html class="no-js" <?php language_attributes(); ?>>

<head>
    <meta charset="utf-8">

    <!-- Force IE to use the latest rendering engine available -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Mobile Meta -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta class="foundation-mq">

    <title><?php echo esc_html( apply_filters( 'dt_blank_title', __( 'Form', 'disciple_tools' ) ) ) ?></title>

    <?php do_action( 'dt_blank_head' ) ?>

</head>
<body>

<!-- Page Body -->
<?php do_action( 'dt_blank_body' ); ?>

<!-- Page Footer-->
<?php do_action( 'dt_blank_footer' ) ?>

</body>
</html>
