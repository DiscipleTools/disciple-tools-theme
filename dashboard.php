<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
    ],
    '',
    false
); ?>

<div id="content">

    <div id="inner-content" class="grid-x grid-margin-x">
    
        <div class="large-3 medium-12 small-12 cell ">
        
            <section id="" class="medium-12 cell">
            
                <div class="bordered-box">
            
                </div>
        
            </section>
    
        </div>

        <div id="main" class="small-12 large-6  cell" role="main">

            <div class="show-for-small-only">
                
                <section class="bordered-box">
                    <?php include( 'searchform.php' ); ?>
                </section>
                
            </div>

            <?php get_template_part( 'parts/content', 'required-updates' ); ?>

            <div class="row column padding-bottom">

                <div class="bordered-box">
                    <p><?php _e( "Welcome to Disciple.Tools!" ); ?></p>
                </div>

            </div>

        </div> <!-- end #main -->
    
        <div class="large-3 medium-12 small-12 cell ">
        
            <section id="" class="medium-12 cell">
            
                <div class="bordered-box">
            
                </div>
        
            </section>
    
        </div>

    </div> <!-- end #inner-content -->


</div> <!-- end #content -->

<?php get_footer(); ?>
