<?php
/*
Template Name: Metrics Wide

@note Full width template for plugin or extension use.
*/
if ( ! current_user_can( 'access_contacts' ) ) {
    wp_safe_redirect( '/settings' );
}
?>

<?php get_header(); ?>

<div style="padding:15px">

    <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

        <div class="small-12 cell ">

            <section id="container" class="medium-12 cell">

                <div class="bordered-box">

                    <div id="chart"></div><!-- Target container -->

                </div>

            </section>

        </div>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>

