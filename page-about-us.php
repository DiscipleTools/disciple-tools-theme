<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation">
                <ul class="breadcrumbs">
                    <li><a href="/">Dashboard</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> ABOUT US
                    </li>
                </ul>
            </nav>

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
