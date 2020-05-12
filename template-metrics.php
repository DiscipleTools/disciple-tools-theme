<?php
/*
Template Name: Metrics
*/
if ( !current_user_can( 'access_contacts' ) && !current_user_can( "view_project_metrics" ) ) {
    wp_safe_redirect( '/settings' );
}
?>

<?php get_header(); ?>

    <div style="padding:15px" class="template-metrics">

        <div id="inner-content" class="grid-x grid-margin-x grid-margin-y">

            <div class="large-2 medium-3 small-12 cell" id="side-nav-container">

                <section id="metrics-side-section" class="medium-12 cell">

                    <div class="bordered-box">

                        <ul id="metrics-sidemenu" class="vertical menu accordion-menu" data-accordion-menu>

                            <?php

                            // WordPress.XSS.EscapeOutput.OutputNotEscaped
                            // @phpcs:ignore
                            echo apply_filters( 'dt_metrics_menu', '' );

                            ?>

                        </ul>

                    </div>

                </section>

            </div>

            <div class="large-10 medium-9 small-12 cell ">

                <section id="metrics-container" class="medium-12 cell">

                    <div class="bordered-box">

                        <div id="chart"></div><!-- Container for charts -->

                    </div>

                </section>

            </div>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
