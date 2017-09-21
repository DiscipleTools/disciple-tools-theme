<?php
/*
Template Name: Workers
*/
?>

<?php get_header(); ?>

<?php
dt_print_breadcrumbs(
    [
        [ home_url( '/' ), __( "Dashboard" ) ],
        [ home_url( '/' ) . "workers", __( "Workers" ) ],
    ],
    get_the_title(),
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
    
            <div class="large-6 medium-12 small-12 cell ">
        
                <section id="" class="medium-12 cell">
            
                    <div class="bordered-box">
    
                        <ul class="tabs" data-tabs id="team-tabs">
                            <li class="tabs-title is-active"><a href="#team-panel1" aria-selected="true">Team</a></li>
                        </ul>
    
                        <div class="tabs-content" data-tabs-content="team-tabs">
                            <div class="tabs-panel is-active" id="team-panel1">
            
                                <h2>Team</h2>
                                <p>Team Name: Team 1</p>
                                <div class="callout" >
                                    <img src="http://placehold.it/150x150/1779ba/ffffff" />
                                    <span>Chris Wynn</span>
                                    <button class="button float-right">Send Message</button>
                                </div>
                                <div class="callout" >
                                    <img src="http://placehold.it/150x150/1779ba/ffffff" />
                                    <span>Chris Wynn</span>
                                    <button class="button float-right">Send Message</button>
                                </div>
                                <div class="callout" >
                                    <img src="http://placehold.it/150x150/1779ba/ffffff" />
                                    <span>Chris Wynn</span>
                                    <button class="button float-right">Send Message</button>
                                </div>
        
                            </div>
                        </div>
            
                    </div>
        
                </section>
    
            </div>
    
            <div class="large-3 medium-12 small-12 cell ">
        
                <section id="" class="medium-12 cell">
            
                    <div class="bordered-box">
            
                    </div>
        
                </section>
    
            </div>
        
        </div> <!-- end #inner-content -->
    
    </div> <!-- end #content -->

<?php get_footer(); ?>
