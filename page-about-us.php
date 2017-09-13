<?php declare(strict_types=1); ?>
<?php get_header(); ?>

<?php dt_print_breadcrumbs( null, __( "ABOUT US" ) ); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-12 medium-12 columns" role="main">

                <section class="bordered-box">

                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                        <?php get_template_part( 'parts/loop', 'page' ); ?>

                    <?php endwhile; ?>
                    <?php endif; ?>

                </section>

            </main> <!-- end #main -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
