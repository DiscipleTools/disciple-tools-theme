<?php
/*
Template Name: Notifications
*/
?>

<?php get_header(); ?>
    
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
