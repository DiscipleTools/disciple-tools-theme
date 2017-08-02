<?php get_header(); ?>

<div id="content">

    <div id="inner-content" class="row">

        <main id="main" class="large-8 medium-8 columns" role="main">

            <section class="bordered-box">

                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                    <?php get_template_part( 'parts/loop', 'single' ); ?>

                <?php endwhile; else : ?>

                    <?php get_template_part( 'parts/content', 'missing' ); ?>

                <?php endif; ?>

            </section>

        </main> <!-- end #main -->

        <aside class="large-4 medium-4 columns ">

            <section class="bordered-box">

                <p>Sidebar</p>

            </section>

        </aside> <!-- end #aside -->

    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
