<?php
/*
Template Name: Metrics
*/
?>

<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "metrics", __( "Metrics" ) ],
    ],
    get_the_title(),
    false
); ?>
    
    <div id="content">
        
        <div id="inner-content" class="grid-x grid-margin-x">
            
            <div id="main" class="large-7 medium-12 small-12 cell " role="main">
    
                <section id="" class="medium-12 cell">
                    
                    <div class="bordered-box">
                    
                    </div>
                    
                </section>
            
            </div> <!-- end #main -->
    
            <div class="large-5 medium-12 small-12 cell ">
    
                <section id="" class="medium-12 cell">
        
                    <div class="bordered-box">
        
                    </div>
    
                </section>
                
            </div>
        
        </div> <!-- end #inner-content -->
    
    </div> <!-- end #content -->

<?php get_footer(); ?>
