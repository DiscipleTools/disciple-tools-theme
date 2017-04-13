<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-8 medium-8 columns" role="main">

                <header>
                    <?php the_archive_title();?>

                </header>

                <?php

                $args = array(
                    'post_type' => 'contacts',
                    'nopaging' => true,
                    'meta_query' => dt_get_user_scope(),
                );
                $query = new WP_Query( $args );
                ?>
                <?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>

                    <!-- To see additional archive styles, visit the /parts directory -->
                <?php get_template_part( 'parts/loop', 'contacts' ); ?>


                <?php endwhile; ?>

                    <?php //disciple_tools_page_navi(); ?>

                <?php else : ?>

                    <?php get_template_part( 'parts/content', 'missing' ); ?>

                <?php endif; ?>

            </main> <!-- end #main -->

            <?php //get_sidebar(); ?>

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>