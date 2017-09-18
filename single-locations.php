<?php declare(strict_types=1); ?>
<?php if ((isset( $_POST['dt_groups_noonce'] ) && wp_verify_nonce( $_POST['dt_groups_noonce'], 'update_dt_groups' ))) { dt_save_group( $_POST ); } // Catch and save update info ?>

<?php get_header(); ?>

<?php dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . 'locations/', __( "Locations" ) ],
    ],
    get_the_title()
); ?>

    <div id="content">

        <div id="inner-content" class="row">

            <main id="main" class="large-8 medium-8 columns" role="main">

                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                    <?php get_template_part( 'parts/loop', 'single-location' ); ?>

                <?php endwhile; else : ?>

                    <?php get_template_part( 'parts/content', 'missing' ); ?>

                <?php endif; ?>

            </main> <!-- end #main -->

            <aside class="large-4 medium-4 columns ">

                <?php
                global $wp_query, $post_id;

                // Find connected pages (for all posts)
                p2p_type( 'contacts_to_locations' )->each_connected( $wp_query, array(), 'contacts' );
                p2p_type( 'groups_to_locations' )->each_connected( $wp_query, array(), 'groups' );
                p2p_type( 'assetmapping_to_locations' )->each_connected( $wp_query, array(), 'assetmapping' );
                ?>

                <section class="bordered-box">

                    <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

                        <h3>Contacts</h3>

                        <?php foreach ( $post->contacts as $post ) : setup_postdata( $post ); ?>

                            <li><a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a></li>

                        <?php endforeach; ?>

                        <?php  wp_reset_postdata(); // set $post back to original post ?>

                    <?php endwhile; ?>

                </section>



                <section class="bordered-box">

                    <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

                        <h3>Groups</h3>

                        <?php foreach ( $post->groups as $post ) : setup_postdata( $post ); ?>

                            <li><a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a> </li>

                        <?php endforeach; ?>

                        <?php  wp_reset_postdata(); // set $post back to original post ?>

                    <?php endwhile; ?>

                </section>


                <section class="bordered-box">

                    <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>

                        <h3>Assets</h3>

                        <?php foreach ( $post->assetmapping as $post ) : setup_postdata( $post ); ?>

                            <li><a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a></li>

                        <?php endforeach; ?>

                        <?php  wp_reset_postdata(); // set $post back to original post ?>

                    <?php endwhile; ?>

                </section>

            </aside> <!-- end #aside -->

        </div> <!-- end #inner-content -->

    </div> <!-- end #content -->

<?php get_footer(); ?>
