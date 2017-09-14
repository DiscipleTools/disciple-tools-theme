<?php declare(strict_types=1); ?>
<?php get_header(); ?>

<?php dt_print_breadcrumbs( null, __( "Locations" ) ); ?>

<div id="content">
    
    <div id="inner-content" class="grid-x grid-margin-x">
    
        <div class="large-3 medium-12 small-12 cell ">
        
            <section id="" class="medium-12 cell">
            
                <div class="bordered-box">
            
                </div>
        
            </section>
    
        </div>
        
        <div id="main" class="large-6 small-12 cell" role="main">
            
            <?php
            $args = array(
                'post_type' => 'locations',
            
            );
            $query1 = new WP_Query( $args );
            ?>
            
            <?php if ( $query1->have_posts() ) : while ( $query1->have_posts() ) : $query1->the_post(); ?>
                
                <!-- To see additional archive styles, visit the /parts directory -->
                <?php get_template_part( 'parts/loop', 'prayer' ); ?>
            
            <?php endwhile; ?>
                
                <?php disciple_tools_page_navi(); ?>
            
            <?php else : ?>
    
                <section class="bordered-box">
        
                    <h3>No Locations found in the system.</h3>
    
                </section>
            
            <?php endif; ?>
        
        </div> <!-- end #main -->
        
        <div class="large-3 small-12 cell">
            
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
                    'post_type'     => 'locations'
                );
                wp_get_archives( $args );
                
                ?>
            
            </section>
        
        
        </div> <!-- end #aside -->
    
    </div> <!-- end #inner-content -->

</div> <!-- end #content -->

<?php get_footer(); ?>
