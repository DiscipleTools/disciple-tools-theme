<?php get_header(); ?>
			
	<div id="content">
	
		<div id="inner-content" class="row">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation" class="hide-for-small-only">
                <ul class="breadcrumbs">
                    <li><a href="<?php echo home_url('/'); ?>">Dashboard</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> Prayer Guide
                    </li>
                </ul>
            </nav>
	
		    <main id="main" class="large-8 medium-8 columns" role="main">

                <?php
                $args = array(
                    'post_type' => 'prayer',

                );
                $query1 = new WP_Query( $args );
                ?>
                <?php if ( $query1->have_posts() ) : while ( $query1->have_posts() ) : $query1->the_post(); ?>

                        <!-- To see additional archive styles, visit the /parts directory -->
                        <?php get_template_part( 'parts/loop', 'prayer' ); ?>

                    <?php endwhile; ?>

                        <?php disciple_tools_page_navi(); ?>

                    <?php else : ?>

                        <?php get_template_part( 'parts/content', 'missing' ); ?>

                    <?php endif; ?>

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
                        'post_type' => 'prayer',
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
                        'post_type'     => 'prayer'
                    );
                    wp_get_archives( $args );

                    ?>

                </section>


            </aside> <!-- end #aside -->
		    
		</div> <!-- end #inner-content -->

	</div> <!-- end #content -->

<?php get_footer(); ?>