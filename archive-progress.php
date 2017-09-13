<?php declare(strict_types=1); ?>
<?php get_header(); ?>

<?php dt_print_breadcrumbs( null, __( "Progress Updates" ) ); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-8 medium-8 columns" role="main">

                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                        <!-- To see additional archive styles, visit the /parts directory -->
                        <?php get_template_part( 'parts/loop', 'progress' ); ?>


                    <?php endwhile; ?>

                        <?php disciple_tools_page_navi(); ?>

                    <?php else : ?>

                        <?php get_template_part( 'parts/content', 'missing' ); ?>

                    <?php endif; ?>


            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns ">


                    <section class="bordered-box">

                        <?php include 'searchform.php'; ?>

                    </section>

                    <section class="bordered-box">

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
                        ?>
                        <?php foreach ($recent_posts as $recent_post): ?>
                            <li><a href="<?php echo $recent_post['guid'] ?>"><?php echo $recent_post['post_title'] ?></a></li>
                        <?php endforeach; ?>
                        <?php
                        echo '</ul>';

                        //                    print_r($recent_posts);?>

                    </section>

                    <section class="bordered-box">

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
