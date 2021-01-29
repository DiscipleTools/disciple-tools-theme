<?php
/*
 * Name: Metrics Wide
 * @note Full width template for plugin or extension use.
*/
dt_please_log_in();

if ( ! current_user_can( 'access_contacts' ) ) {
    wp_safe_redirect( '/settings' );
    exit();
}
?>

<?php get_header(); ?>

<div style="padding:15px" class="template-metrics-wide">

    <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

        <div class="small-12 cell ">

            <section id="container" class="medium-12 cell">

                <div class="bordered-box">

                    <div id="chart"><span class="loading-spinner active"></span></div><!-- Target container -->

                </div>

            </section>

        </div>

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
