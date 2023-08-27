<?php
/*
Template Name: Blank
*/

/**
 * Access control for non-logged in
 */
if ( ! apply_filters( 'dt_blank_access', false ) ){
    esc_html_e( 'Access to this page not permitted', 'disciple_tools' );
    exit;
}
?>
<!doctype html>

<html class="no-js" <?php language_attributes(); ?>>

<head>
    <?php dt_header_icon_and_meta(); ?>

    <title><?php echo esc_html( apply_filters( 'dt_blank_title', __( 'Form', 'disciple_tools' ) ) ) ?></title>

    <!-- Page Custom Header-->
    <?php do_action( 'dt_blank_head' ) ?>

</head>
<body id="blank-template-body">

<!-- Page Body -->
<?php do_action( 'dt_blank_body' ); ?>

<!-- Page Footer-->
<?php do_action( 'dt_blank_footer' ) ?>

</body>
</html>
