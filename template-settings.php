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
        
                <section id="" class="medium-12 cell sticky" data-sticky data-margin-top="6.5">
            
                    <div class="bordered-box hide-for-small-only">
                    
                        <ul class="menu vertical expanded" data-smooth-scroll data-offset="100">
                            <li><a href="#profile">Profile</a></li>
                            <li><a href="#availability">Availability</a></li>
                            <li><a href="#notifications">Notifications</a></li>
                        </ul>
                    
                    </div>
        
                </section>
                
    
            </div>
    
            <div class="large-9 medium-12 small-12 cell ">
        
                <section id="" class="medium-12 cell">
            
                    <div class="bordered-box" id="profile" data-magellan-target="profile">
                        <button class="float-right" onclick=""><i class="fi-pencil"></i> Edit</button>
                        <span class="section-header">Profile</span>
                        
                        <?php
                        
                        
                        
                        ?>
                        
                    </div>
                    
                    <div class="bordered-box" id="availability" data-magellan-target="availability">
                        <button class="float-right" onclick=""><i class="fi-pencil"></i> Edit</button>
                        <span class="section-header">Availability</span>
                       
                    </div>
                    
                    <div class="bordered-box" id="notifications" data-magellan-target="notifications">
                        <button class="float-right" onclick=""><i class="fi-pencil"></i> Edit</button>
                        <span class="section-header">Notifications</span>
                        
                    </div>
                    
                </section>
    
            </div>
    
        </div> <!-- end #inner-content -->
    
    </div> <!-- end #content -->

<?php get_footer(); ?>
