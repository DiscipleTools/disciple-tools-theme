<?php if ((isset($_POST['dt_contacts_noonce']) && wp_verify_nonce( $_POST['dt_contacts_noonce'], 'update_dt_contacts' ))) { dt_save_contact($_POST); } // Catch and save update info ?>

<?php get_header(); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <!-- Breadcrumb Navigation-->
            <nav aria-label="You are here:" role="navigation">
                <ul class="breadcrumbs">
                    <li><a href="/">Dashboard</a></li>
                    <li><a href="/contacts/">Contacts</a></li>
                    <li>
                        <span class="show-for-sr">Current: </span> <?php the_title(); ?>
                    </li>
                </ul>
            </nav>

            <main id="main" class="large-8 medium-8 columns" role="main">



                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                        <?php

                            if(isset($_GET['action']) && $_GET['action'] == 'edit') { // check if edit screen

                               get_template_part( 'parts/edit', 'contact' );

                            } else {

                                get_template_part( 'parts/loop', 'single-contact' );
                            }

                        ?>

                    <?php endwhile; else : ?>

                        <?php get_template_part( 'parts/content', 'missing' ); ?>

                    <?php endif; ?>

            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns ">

                <?php
                    global $wp_query, $post_id;

                    // Find connected pages (for all posts)
                    p2p_type( 'contacts_to_contacts' )->each_connected( $wp_query, array(), 'disciple' );
                    p2p_type( 'contacts_to_groups' )->each_connected( $wp_query, array(), 'groups' );
                    p2p_type( 'contacts_to_locations' )->each_connected( $wp_query, array(), 'locations' );
                ?>

                <section class="block">

                    <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

                        <h3>Relationships</h3>

                        <?php foreach ( $post->disciple as $post ) : setup_postdata( $post ); ?>

                            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>

                        <?php endforeach; ?>

                        <?php  wp_reset_postdata(); // set $post back to original post ?>

                    <?php endwhile; ?>

                </section>



                <section class="block">

                    <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

                        <h3>Groups</h3>

                        <?php foreach ( $post->groups as $post ) : setup_postdata( $post ); ?>

                            <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> </li>

                        <?php endforeach; ?>

                       <?php  wp_reset_postdata(); // set $post back to original post ?>

                    <?php endwhile; ?>

                </section>


                <section class="block">

                    <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

                        <h3>Locations</h3>

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