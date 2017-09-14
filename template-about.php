<?php
/*
Template Name: About
*/
?>

<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "about", __( "About" ) ],
    ],
    get_the_title(),
    false
); ?>
    
    <div id="content">
        
        <div id="inner-content" class="grid-x grid-margin-x">
            
            <main id="main" class="large-12 medium-12 cell" role="main">
                
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    
                    <?php get_template_part( 'parts/loop', 'page' );  ?>
                
                <?php endwhile; ?>
                <?php endif; ?>
            
            </main> <!-- end #main -->
        
        </div> <!-- end #inner-content -->
    
    </div> <!-- end #content -->

<?php get_footer(); ?>
