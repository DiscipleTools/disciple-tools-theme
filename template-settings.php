<?php
/*
Template Name: Settings
*/
?>

<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "settings", __( "Settings" ) ],
    ],
    get_the_title(),
    false
); ?>
    
    <div id="content">
        
        <div id="inner-content" class="grid-x grid-margin-x">
    
            <div class="large-3 medium-12 small-12 cell ">
        
                <section id="" class="medium-12 cell">
            
                    <div class="bordered-box">
    
                        <ul class="menu expanded" data-magellan>
                            <li><a href="#first">First Arrival</a></li>
                            <li><a href="#second">Second Arrival</a></li>
                            <li><a href="#third">Third Arrival</a></li>
                        </ul>
            
                    </div>
        
                </section>
    
            </div>
    
            <div class="large-9 medium-12 small-12 cell ">
        
                <section id="" class="medium-12 cell">
            
                    <div class="bordered-box" id="first" data-magellan-target="first">
                        <p>Test</p>
                    </div>
                    
                    <div class="bordered-box" id="second" data-magellan-target="second">
                        <p>Test</p>
                    </div>
                    
                    <div class="bordered-box" id="third" data-magellan-target="third">
                        <p>Test</p>
                    </div>
                    
                    <div class="bordered-box" id="fourth" data-magellan-target="fourth">
                        <p>Test</p>
                    </div>
        
                </section>
    
            </div>
    
        </div> <!-- end #inner-content -->
    
    </div> <!-- end #content -->

<?php get_footer(); ?>
