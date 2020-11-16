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

                        <div class="section-header"><?php esc_html_e( 'List Exports', 'disciple_tools' )?>&nbsp;
                            <button class="float-right" data-open="export-help-text">
                                <img class="help-icon" src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/help.svg' ) ?>" alt="help"/>
                            </button>
                            <button class="section-chevron chevron_down">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_down.svg' ) ?>" alt="expand"/>
                            </button>
                            <button class="section-chevron chevron_up">
                                <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/chevron_up.svg' ) ?>" alt="collapse"/>
                            </button>
                        </div>
                        <div class="section-body" style="padding-top:1em;">

                        <ul id="metrics-sidemenu" class="vertical menu accordion-menu" data-accordion-menu>

                            <?php

                            // WordPress.XSS.EscapeOutput.OutputNotEscaped
                            // @phpcs:ignore
                            echo apply_filters( 'dt_metrics_menu', '' );

                            ?>

                        </ul>

                        </div>

                    </div>

                </section>

            </div>

            <div class="large-10 medium-9 small-12 cell ">

                <section id="metrics-container" class="medium-12 cell">

                    <div class="bordered-box">

                        <div id="chart"><span class="loading-spinner active"></span></div><!-- Container for charts -->

                    </div>

                </section>

            </div>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
