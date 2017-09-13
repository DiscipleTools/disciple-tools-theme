<?php declare(strict_types=1); ?>

<?php if ((isset( $_POST['dt_groups_noonce'] ) && wp_verify_nonce( $_POST['dt_groups_noonce'], 'update_dt_groups' ))) { dt_save_group( $_POST ); } // Catch and save update info ?>

<?php get_header(); ?>

<?php dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . 'peoplegroups/', __( "People Groups" ) ],
    ],
    get_the_title()
); ?>

<div id="content">
    
    <div id="inner-content" class="grid-x grid-margin-x">
        
        <div id="main" class="large-8 small-12 cell" role="main">
            
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                
                <?php get_template_part( 'parts/loop', 'single-location' ); ?>
            
            <?php endwhile; else : ?>
                
                <?php get_template_part( 'parts/content', 'missing' ); ?>
            
            <?php endif; ?>
        
        </div> <!-- end #main -->
        
        <div class="large-4 small-12 cell ">
            
            <?php
            global $wp_query, $post_id;
            
            // Find connected pages (for all posts)
            p2p_type( 'contacts_to_peoplegroups' )->each_connected( $wp_query, array(), 'contacts' );
            p2p_type( 'groups_to_peoplegroups' )->each_connected( $wp_query, array(), 'groups' );
            p2p_type( 'team_member_peoplegroups' )->each_connected( $wp_query, array(), 'users' );
            p2p_type( 'peoplegroups_to_locations' )->each_connected( $wp_query, array(), 'locations' );
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
                    
                    <h3>Workers</h3>
                    
                    <?php foreach ( $post->users as $post ) : setup_postdata( $post ); ?>
                        
                        <li><a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a></li>
                    
                    <?php endforeach; ?>
                    
                    <?php  wp_reset_postdata(); // set $post back to original post ?>
                
                <?php endwhile; ?>
            
            </section>
    
            <section class="bordered-box">
        
                <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
            
                    <h3>Locations</h3>
            
                    <?php foreach ( $post->locations as $post ) : setup_postdata( $post ); ?>
                
                        <li><a href="<?php the_permalink(); ?>"><?php the_title_attribute(); ?></a></li>
            
                    <?php endforeach; ?>
            
                    <?php  wp_reset_postdata(); // set $post back to original post ?>
        
                <?php endwhile; ?>
    
            </section>
        
        </div> <!-- end #aside -->
    
    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
