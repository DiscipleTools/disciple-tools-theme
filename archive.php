<?php if (is_front_page() ) {dt_route_front_page();}  ?>

<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-8 medium-8 columns" role="main">

                <section class="bordered-box">

                    <header>
                        <h1 class="page-title"><?php the_archive_title();?></h1>
                        <?php the_archive_description( '<div class="taxonomy-description">', '</div>' );?>
                    </header>

                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                        <!-- To see additional archive styles, visit the /parts directory -->
                        <?php get_template_part( 'parts/loop', 'archive' ); ?>


                    <?php endwhile; ?>

                        <?php disciple_tools_page_navi(); ?>

                    <?php else : ?>

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
