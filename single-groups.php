<?php if ((isset($_POST['dt_groups_noonce']) && wp_verify_nonce( $_POST['dt_groups_noonce'], 'update_dt_groups' ))) { dt_save_group($_POST); } // Catch and save update info ?>

<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation">
                <ul class="breadcrumbs">
                    <li><a href="/">Dashboard</a></li>
                    <li><a href="/groups/">Groups</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> Current Group
                    </li>
                </ul>
            </nav>

            <main id="main" class="large-8 medium-8 columns" role="main">

                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                        <div class="padding-bottom">

                            <ul class="tabs" data-tabs id="contact-tabs">
                                <li class="tabs-title is-active"><a href="#contact-panel1" aria-selected="true">Group</a></li>
                                <li class="tabs-title"><a href="#contact-panel2" aria-selected="true">Edit</a></li>
                            </ul>

                            <div class="tabs-content" data-tabs-content="contact-tabs">
                                <div class="tabs-panel is-active" id="contact-panel1">

                                    <?php get_template_part( 'parts/loop', 'single-group' ); ?>

                                </div>
                                <div class="tabs-panel" id="contact-panel2">

                                    <?php get_template_part( 'parts/edit', 'group' ); ?>

                                </div>
                            </div>

                        </div>

                        <?php get_template_part( 'parts/loop', 'activity-comment' ); ?>

                    <?php endwhile; else : ?>

                        <?php get_template_part( 'parts/content', 'missing' ); ?>

                    <?php endif; ?>

            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns ">

                <?php
                global $wp_query, $post_id;

                // Find connected pages (for all posts)
                p2p_type( 'contacts_to_groups' )->each_connected( $wp_query, array(), 'contacts' );
                p2p_type( 'groups_to_groups' )->each_connected( $wp_query, array(), 'groups' );
                p2p_type( 'groups_to_locations' )->each_connected( $wp_query, array(), 'locations' );
                ?>

                <section class="block">

                    <form method="get" action="<?php echo get_permalink(); ?>">
                            <span class="float-right">
                                <input type="hidden" name="action" value="edit"/>
                                <input type="submit" value="Add" class="button" />
                            </span>
                    </form>

                    <h3>Members</h3>

                    <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>



                        <?php foreach ( $post->contacts as $post ) : setup_postdata( $post ); ?>

                            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>

                        <?php endforeach; ?>

                        <?php  wp_reset_postdata(); // set $post back to original post ?>

                    <?php endwhile; ?>

                </section>



                <section class="block">

                    <form method="get" action="<?php echo get_permalink(); ?>">
                        <span class="float-right">
                            <input type="hidden" name="action" value="edit"/>
                            <input type="submit" value="Add" class="button" />
                        </span>
                    </form>

                    <h3>Groups</h3>

                    <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

                        <?php foreach ( $post->groups as $post ) : setup_postdata( $post ); ?>

                            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> </li>

                        <?php endforeach; ?>

                        <?php  wp_reset_postdata(); // set $post back to original post ?>

                    <?php endwhile; ?>


                </section>


                <section class="block">

                    <form method="get" action="<?php echo get_permalink(); ?>">
                            <span class="float-right">
                                <input type="hidden" name="action" value="edit"/>
                                <input type="submit" value="Add" class="button" />
                            </span>
                    </form>

                    <h3>Locations</h3>

                    <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

                        <?php foreach ( $post->locations as $post ) : setup_postdata( $post ); ?>

                            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>

                        <?php endforeach; ?>

                        <?php  wp_reset_postdata(); // set $post back to original post ?>

                    <?php endwhile; ?>



                </section>

            </aside> <!-- end #aside -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>