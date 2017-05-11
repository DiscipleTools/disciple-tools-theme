<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation">
                <ul class="breadcrumbs">
                    <li><a href="/">Dashboard</a></li>
                    <li>
                        <a href="/progress">PROGRESS UPDATES</a>
                    </li>
                    <li>
                        <span class="show-for-sr">Current: </span> <?php the_title(); ?>
                    </li>
                </ul>
            </nav>

            <main id="main" class="large-8 medium-8 columns" role="main">

                <section class="block">


                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                        <?php get_template_part( 'parts/loop', 'single-progress' ); ?>

                    <?php endwhile; else : ?>

                        <?php get_template_part( 'parts/content', 'missing' ); ?>

                    <?php endif; ?>

                </section>


            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns ">


                    <section class="block">

                        <?php include 'searchform.php'; ?>

                    </section>

                    <section class="block">

                        <h4>Recent Posts</h4>

                        <?php $args = array(
                            'numberposts' => 10,
                            'offset' => 0,
                            'category' => 0,
                            'orderby' => 'post_date',
                            'order' => 'DESC',
                            'include' => '',
                            'exclude' => '',
                            'meta_key' => '',
                            'meta_value' =>'',
                            'post_type' => 'progress',
                            'post_status' => 'draft, publish, future, pending, private',
                            'suppress_filters' => true
                        );

                        $recent_posts = wp_get_recent_posts( $args, ARRAY_A );

                        echo '<ul>';
                        foreach ($recent_posts as $recent_post) {
                            echo '<li><a href="'. $recent_post['guid'] .'">' . $recent_post['post_title'] . '</a></li>';
                        }
                        echo '</ul>';

                        //                    print_r($recent_posts);?>

                    </section>

                    <section class="block">

                        <p>Archives</p>

                        <?php
                        $args = array(
                            'type'            => 'monthly',
                            'limit'           => '',
                            'format'          => 'html',
                            'before'          => '',
                            'after'           => '',
                            'show_post_count' => false,
                            'echo'            => 1,
                            'order'           => 'DESC',
                            'post_type'     => 'progress'
                        );
                        wp_get_archives( $args );

                        ?>

                    </section>


            </aside> <!-- end #aside -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>