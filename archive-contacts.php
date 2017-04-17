<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation">
                <ul class="breadcrumbs">
                    <li><a href="/">Dashboard</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> Contacts
                    </li>
                </ul>
            </nav>

            <main id="main" class="large-8 medium-8 columns frame" role="main">

                <section class="block">

                    <header>
                        <?php the_archive_title();?>

                    </header>

                    <?php
                    $args = array(
                        'post_type' => 'contacts',
                        'nopaging' => true,
                        'meta_query' => dt_get_user_associations(),
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

                </section>

            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns ">

                <section class="block">

                    <p>Sidebar</p>

                </section>

            </aside> <!-- end #aside -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>