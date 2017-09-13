<?php declare(strict_types=1); ?>
<?php get_header(); ?>

<?php dt_print_breadcrumbs( null, __( "Reports" ) ); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-12 medium-12 columns" role="main">

                <section class="bordered-box">
                    <?php dt_chart_bargraph(); ?>
                </section>

            </main>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
